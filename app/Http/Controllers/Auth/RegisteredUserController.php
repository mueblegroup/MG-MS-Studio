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
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
                'phone_number' => ['required', 'string', 'max:40'],
                'organisation_name' => ['required', 'string', 'max:255'],
                'job_title' => ['nullable', 'string', 'max:255'],
                'country' => ['required', 'string', 'max:100'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);

            $user = User::create([
                'studio_id' => null,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone_number' => $validated['phone_number'],
                'organisation_name' => $validated['organisation_name'],
                'job_title' => $validated['job_title'] ?? null,
                'country' => $validated['country'],
                'role' => 'admin',
                'password' => Hash::make($validated['password']),
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

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(fn ($query) => $query->where('studio_id', $studio->id)),
            ],
            'phone_number' => ['required', 'string', 'max:40'],
            'date_of_birth' => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'gender' => ['nullable', Rule::in(['female', 'male', 'non_binary', 'prefer_not_to_say', 'other'])],
            'address' => ['required', 'string', 'max:2000'],
            'emergency_contact_name' => ['required', 'string', 'max:255'],
            'emergency_contact_phone' => ['required', 'string', 'max:40'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'studio_id' => $studio->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone_number' => $validated['phone_number'],
            'date_of_birth' => $validated['date_of_birth'],
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'role' => 'student',
            'password' => Hash::make($validated['password']),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('student.dashboard', absolute: false));
    }
}
