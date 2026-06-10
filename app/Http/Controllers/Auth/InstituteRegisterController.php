<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Models\StudioDomain;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstituteRegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.institute-register', [
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $reserved = config('saas.reserved_subdomains', []);

        $validated = $request->validate([
            'studio_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'min:3',
                'max:40',
                'alpha_dash:ascii',
                Rule::notIn($reserved),
                Rule::unique('studios', 'subdomain'),
            ],
            'owner_name' => ['required', 'string', 'max:255'],
            'owner_email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'owner_phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'timezone' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $rootDomain = config('saas.root_domain');
        $subdomain = Str::lower($validated['subdomain']);
        $studioHost = $subdomain . '.' . $rootDomain;

        [$studio, $owner] = DB::transaction(function () use ($validated, $subdomain, $studioHost) {
            $studio = Studio::create([
                'name' => $validated['studio_name'],
                'slug' => Str::slug($validated['studio_name']) . '-' . Str::lower(Str::random(5)),
                'subdomain' => $subdomain,
                'status' => 'trial',
                'plan_name' => 'trial',
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
                'settings' => [
                    'timezone' => $validated['timezone'] ?: config('app.timezone'),
                    'currency' => strtoupper($validated['currency'] ?: 'MYR'),
                ],
            ]);

            $owner = User::create([
                'studio_id' => $studio->id,
                'name' => $validated['owner_name'],
                'email' => $validated['owner_email'],
                'phone_number' => $validated['owner_phone'] ?? null,
                'role' => 'admin',
                'password' => Hash::make($validated['password']),
            ]);

            $studio->update([
                'owner_user_id' => $owner->id,
            ]);

            StudioDomain::create([
                'studio_id' => $studio->id,
                'domain' => $studioHost,
                'type' => 'subdomain',
                'is_primary' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            return [$studio, $owner];
        });

        event(new Registered($owner));

        Auth::login($owner);

        $scheme = app()->environment('local') ? 'http' : 'https';

        return redirect('/admin/dashboard');
    }

    public function checkSubdomain(Request $request)
    {
        $subdomain = Str::lower((string) $request->query('subdomain'));

        $available = $subdomain !== ''
            && preg_match('/^[a-z0-9-]{3,40}$/', $subdomain)
            && ! in_array($subdomain, config('saas.reserved_subdomains', []), true)
            && ! Studio::where('subdomain', $subdomain)->exists();

        return response()->json([
            'available' => (bool) $available,
            'url' => $subdomain ? $subdomain . '.' . config('saas.root_domain') : null,
        ]);
    }
}