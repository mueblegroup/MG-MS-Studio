<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\StudioDomain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerPortalController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        $studios = Studio::query()
            ->where('owner_user_id', $user->id)
            ->with('platformSubscriptionPlan')
            ->latest()
            ->get();

        return view('customer.dashboard', [
            'studios' => $studios,
            'plans' => PlatformSubscriptionPlan::query()->where('is_active', true)->orderBy('sort_order')->orderBy('price')->get(),
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function createStudio(): View
    {
        return view('customer.studios.create', [
            'plans' => PlatformSubscriptionPlan::query()->where('is_active', true)->orderBy('sort_order')->orderBy('price')->get(),
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function storeStudio(Request $request): RedirectResponse
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
            'platform_subscription_plan_id' => ['nullable', 'exists:platform_subscription_plans,id'],
            'timezone' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $rootDomain = strtolower((string) config('saas.root_domain'));
        $subdomain = Str::lower($validated['subdomain']);
        $studioHost = $subdomain . '.' . $rootDomain;
        $plan = ! empty($validated['platform_subscription_plan_id'])
            ? PlatformSubscriptionPlan::query()->find($validated['platform_subscription_plan_id'])
            : null;

        DB::transaction(function () use ($request, $validated, $subdomain, $studioHost, $plan): void {
            $studio = Studio::create([
                'name' => $validated['studio_name'],
                'slug' => Str::slug($validated['studio_name']) . '-' . Str::lower(Str::random(5)),
                'subdomain' => $subdomain,
                'owner_user_id' => $request->user()->id,
                'status' => 'trial',
                'plan_name' => $plan?->name ?? 'trial',
                'platform_subscription_plan_id' => $plan?->id,
                'trial_ends_at' => now()->addDays((int) config('saas.trial_days', 14)),
                'settings' => [
                    'timezone' => $validated['timezone'] ?: config('app.timezone'),
                    'currency' => strtoupper($validated['currency'] ?: 'MYR'),
                ],
            ]);

            StudioDomain::create([
                'studio_id' => $studio->id,
                'domain' => $studioHost,
                'type' => 'subdomain',
                'is_primary' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);
        });

        return redirect()->route('customer.dashboard')->with('success', 'Studio created successfully. Open the studio portal and login from its own subdomain.');
    }

    public function launchStudio(Request $request, Studio $studio): RedirectResponse
    {
        abort_unless((int) $studio->owner_user_id === (int) $request->user()->id || $request->user()->role === 'superadmin', 403);

        $scheme = app()->environment('local') ? 'http' : 'https';
        $host = $studio->custom_domain ?: ($studio->subdomain . '.' . config('saas.root_domain'));

        return redirect()->away($scheme . '://' . $host . '/login');
    }
}
