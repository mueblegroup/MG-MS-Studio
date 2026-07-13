<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\StudioOnboardingCheckout;
use App\Services\StudioOnboardingPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class StudioOnboardingController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        return view('customer.studios.create', [
            'plans' => PlatformSubscriptionPlan::query()
                ->where('is_active', true)
                ->where('price', '>', 0)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
            'rootDomain' => config('saas.root_domain'),
        ]);
    }

    public function store(Request $request, StudioOnboardingPaymentService $payments): RedirectResponse
    {
        if ($redirect = $this->guard($request)) {
            return $redirect;
        }

        $activeCheckout = StudioOnboardingCheckout::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['pending', 'checkout_created'])
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        if ($activeCheckout) {
            return redirect()->route('customer.studios.create')->with(
                'error',
                'A studio payment checkout is already active for this account. Complete it or wait for the 30-minute reservation to expire before starting another checkout.'
            );
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
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $reservedByCheckout = StudioOnboardingCheckout::query()
                        ->where('subdomain', strtolower((string) $value))
                        ->whereIn('status', ['pending', 'checkout_created'])
                        ->where(function ($query): void {
                            $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                        })
                        ->exists();

                    if ($reservedByCheckout) {
                        $fail('This subdomain is currently reserved by another payment checkout.');
                    }
                },
            ],
            'platform_subscription_plan_id' => [
                'required',
                Rule::exists('platform_subscription_plans', 'id')->where(fn ($query) => $query->where('is_active', true)->where('price', '>', 0)),
            ],
            'timezone' => ['nullable', 'string', 'max:80'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $onboarding = StudioOnboardingCheckout::create([
            'user_id' => $request->user()->id,
            'platform_subscription_plan_id' => $validated['platform_subscription_plan_id'],
            'studio_name' => $validated['studio_name'],
            'subdomain' => strtolower($validated['subdomain']),
            'timezone' => $validated['timezone'] ?: config('app.timezone'),
            'currency' => strtoupper($validated['currency'] ?: 'MYR'),
            'status' => 'pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        try {
            $session = $payments->createCheckout(
                $onboarding,
                route('customer.studios.payment-success'),
                route('customer.studios.create', ['checkout' => 'cancelled']),
            );

            return redirect()->away((string) $session->url);
        } catch (Throwable $exception) {
            report($exception);
            $payments->markCheckoutFailed($onboarding, $exception->getMessage());

            return redirect()->route('customer.studios.create')
                ->withInput()
                ->with('error', $exception->getMessage());
        }
    }

    public function paymentSuccess(Request $request, StudioOnboardingPaymentService $payments): RedirectResponse
    {
        $sessionId = $request->string('session_id')->toString();

        if ($sessionId === '') {
            return redirect()->route('customer.studios.create')->with('error', 'Stripe did not return a checkout session.');
        }

        try {
            $studio = $payments->fulfillSession($sessionId, (int) $request->user()->id);

            return redirect()->route('customer.dashboard')->with(
                'success',
                sprintf('Payment confirmed and %s was created successfully.', $studio->name)
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.studios.create')->with('error', $exception->getMessage());
        }
    }

    private function guard(Request $request): ?RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->role === 'admin', 403, 'Only a client administrator can create a studio.');

        if ($user->studio_id || Studio::query()->where('owner_user_id', $user->id)->exists()) {
            return redirect()->route('customer.dashboard')
                ->with('info', 'Your account already has a studio.');
        }

        return null;
    }
}
