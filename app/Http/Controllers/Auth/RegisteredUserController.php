<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\TenantManager;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $studio = app(TenantManager::class)->current();

        if ($studio) {
            abort_unless(
                (bool) data_get($studio->settings, 'allow_student_self_registration', true),
                403,
                'Student self-registration is disabled for this studio. Please contact the studio administrator.'
            );
        }

        return view('auth.register', [
            'studio' => $studio,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $studio = app(TenantManager::class)->current();

        if (! $studio) {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'studio_id' => null,
                'name' => $request->name,
                'email' => $request->email,
                'role' => 'admin',
                'password' => Hash::make($request->password),
            ]);

            event(new Registered($user));
            Auth::login($user);

            return redirect()->route('customer.dashboard');
        }

        abort_unless(
            (bool) data_get($studio->settings, 'allow_student_self_registration', true),
            403,
            'Student self-registration is disabled for this studio. Please contact the studio administrator.'
        );

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('studio_id', $studio->id)),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'studio_id' => $studio->id,
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'student',
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('student.dashboard', absolute: false));
    }
}
