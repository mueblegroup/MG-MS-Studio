<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPayment;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\StudioDomain;
use App\Models\User;
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
        $guard = $this->guardClientPortal($request);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $studio = $this->ownedStudio($request);
        $plans = $this->activePlans();
        $payments = $this->platformSubscriptionPayments($studio, 3);

        return view('customer.dashboard', [
            'studio' => $studio,
            'payments' => $payments,
            'plans' => $plans,
            'rootDomain' => config('saas.root_domain'),
            'setupSteps' => $this->setupSteps($studio),
        ]);
    }

    public function studio(Request $request): View|RedirectResponse
    {
        $guard = $this->guardClientPortal($request);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        return view('customer.studio', [
            'studio' => $this->ownedStudio($request),
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function billing(Request $request): View|RedirectResponse
    {
        $guard = $this->guardClientPortal($request);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $studio = $this->ownedStudio($request);

        return view('customer.billing', [
            'studio' => $studio,
            'plans' => $this->activePlans(),
            'payments' => $this->platformSubscriptionPayments($studio, 10),
        ]);
    }

    public function invoices(Request $request): View|RedirectResponse
    {
        $guard = $this->guardClientPortal($request);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $studio = $this->ownedStudio($request);

        return view('customer.invoices', [
            'studio' => $studio,
            'payments' => $this->platformSubscriptionPayments($studio, 20),
        ]);
    }

    public function createStudio(Request $request): View|RedirectResponse
    {
        $guard = $this->guardClientPortal($request, allowNoStudio: true);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $user = $request->user();

        if ($user->studio_id || Studio::query()->where('owner_user_id', $user->id)->exists()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'Your account already has a studio. Open your studio portal to manage it.');
        }

        return view('customer.studios.create', [
            'plans' => $this->activePlans(),
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function storeStudio(Request $request): RedirectResponse
    {
        $guard = $this->guardClientPortal($request, allowNoStudio: true);

        if ($guard instanceof RedirectResponse) {
            return $guard;
        }

        $user = $request->user();

        if ($user->studio_id || Studio::query()->where('owner_user_id', $user->id)->exists()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'Your account already has a studio. Open your studio portal to manage it.');
        }

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
            $user = $request->user();

            if ($user->fresh()->studio_id || Studio::query()->where('owner_user_id', $user->id)->exists()) {
                return;
            }

            $studio = Studio::create([
                'name' => $validated['studio_name'],
                'slug' => Str::slug($validated['studio_name']) . '-' . Str::lower(Str::random(5)),
                'subdomain' => $subdomain,
                'owner_user_id' => $user->id,
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

            $user->forceFill([
                'studio_id' => $studio->id,
                'role' => 'admin',
            ])->save();
        });

        return redirect()->route('customer.dashboard')->with('success', 'Studio created successfully. Your account is now connected to your studio. Use this client portal for billing and setup, and open the studio portal for day-to-day LMS operations.');
    }

    public function launchStudio(Request $request, Studio $studio): RedirectResponse
    {
        abort_unless((int) $studio->owner_user_id === (int) $request->user()->id, 403);

        $host = $studio->custom_domain ?: ($studio->subdomain . '.' . config('saas.root_domain'));
        $isLocalHost = in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test');
        $scheme = $isLocalHost ? 'http' : 'https';

        return redirect()->away($scheme . '://' . $host . '/login');
    }

    private function guardClientPortal(Request $request, bool $allowNoStudio = false): ?RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'superadmin') {
            return redirect()->route('superadmin.dashboard');
        }

        abort_unless($user->role === 'admin', 403, 'Client portal access is only available to client owner accounts.');

        $studio = $this->ownedStudio($request);

        if (! $studio) {
            abort_unless($allowNoStudio || ! $user->studio_id, 403, 'Only the studio owner can access the client portal.');

            return null;
        }

        abort_unless((int) $studio->owner_user_id === (int) $user->id, 403, 'Only the studio owner can access the client portal.');

        return null;
    }

    private function ownedStudio(Request $request): ?Studio
    {
        $user = $request->user();

        return Studio::query()
            ->where('owner_user_id', $user->id)
            ->with(['platformSubscriptionPlan', 'domains'])
            ->latest()
            ->first();
    }

    private function activePlans()
    {
        return PlatformSubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
    }

    private function platformSubscriptionPayments(?Studio $studio, int $limit)
    {
        if (! $studio) {
            return collect();
        }

        return PlatformSubscriptionPayment::query()
            ->where('studio_id', $studio->id)
            ->with('plan')
            ->latest('paid_at')
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function setupSteps(?Studio $studio): array
    {
        return [
            [
                'title' => 'Create studio workspace',
                'description' => 'Reserve the studio name, subdomain, timezone, and currency.',
                'complete' => (bool) $studio,
            ],
            [
                'title' => 'Choose platform plan',
                'description' => 'Select the SaaS subscription package for this studio.',
                'complete' => (bool) ($studio?->platform_subscription_plan_id),
            ],
            [
                'title' => 'Open studio portal',
                'description' => 'Login from the studio subdomain to manage classes, teachers, students, and schedules.',
                'complete' => (bool) ($studio?->isActive()),
            ],
            [
                'title' => 'Complete billing setup',
                'description' => 'Add payment method and keep subscription invoices in the client portal.',
                'complete' => (bool) ($studio?->subscription_ends_at || $studio?->platformSubscriptionPayments()->where('status', 'paid')->exists()),
            ],
        ];
    }
}
