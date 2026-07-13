<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Services\PlatformStripeBillingService;
use App\Services\StudioOnboardingPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class PlatformBillingController extends Controller
{
    public function checkout(Request $request, PlatformSubscriptionPlan $plan, PlatformStripeBillingService $billing): RedirectResponse
    {
        $studio = $this->ownedStudio($request);
        abort_unless($plan->is_active, 404);

        if ($studio->stripe_subscription_id && in_array($studio->subscription_status, ['active', 'trialing', 'past_due'], true)) {
            return redirect()->route('customer.billing')->with('error', 'You already have a subscription. Use Upgrade for a higher plan or cancel the existing subscription first.');
        }

        try {
            $session = $billing->createCheckoutSession(
                $studio,
                $plan,
                route('customer.billing'),
                route('customer.billing'),
            );

            return redirect()->away((string) $session->url);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.billing')->with('error', $exception->getMessage());
        }
    }

    public function upgrade(Request $request, PlatformSubscriptionPlan $plan, PlatformStripeBillingService $billing): RedirectResponse
    {
        $studio = $this->ownedStudio($request);
        abort_unless($plan->is_active, 404);

        try {
            $result = $billing->upgrade($studio, $plan);

            return redirect()->route('customer.billing')->with(
                'success',
                sprintf(
                    'Upgrade submitted. Stripe calculated an immediate prorated amount of %s %s. The plan changes after Stripe confirms payment.',
                    $result['currency'],
                    number_format((float) $result['amount_due'], 2),
                )
            );
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.billing')->with('error', $exception->getMessage());
        }
    }

    public function cancel(Request $request, PlatformStripeBillingService $billing): RedirectResponse
    {
        $studio = $this->ownedStudio($request);

        try {
            $billing->cancelAtPeriodEnd($studio);

            return redirect()->route('customer.billing')->with('success', 'Auto-renewal has been cancelled. Your studio remains active until the current paid period ends.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.billing')->with('error', $exception->getMessage());
        }
    }

    public function resume(Request $request, PlatformStripeBillingService $billing): RedirectResponse
    {
        $studio = $this->ownedStudio($request);

        try {
            $billing->resume($studio);

            return redirect()->route('customer.billing')->with('success', 'Auto-renewal has been restored for the current subscription.');
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.billing')->with('error', $exception->getMessage());
        }
    }

    public function portal(Request $request, PlatformStripeBillingService $billing): RedirectResponse
    {
        $studio = $this->ownedStudio($request);

        try {
            return redirect()->away($billing->createBillingPortalSession($studio, route('customer.billing')));
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('customer.billing')->with('error', $exception->getMessage());
        }
    }

    public function webhook(
        Request $request,
        PlatformStripeBillingService $billing,
        StudioOnboardingPaymentService $onboarding,
    ): JsonResponse {
        try {
            $event = $billing->constructWebhookEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
            );

            $handledByOnboarding = $onboarding->handleEvent($event);

            if (! $handledByOnboarding) {
                $billing->handleWebhook($event);
            }

            return response()->json(['received' => true]);
        } catch (Throwable $exception) {
            Log::error('Stripe platform webhook failed.', [
                'event_id' => $event->id ?? null,
                'event_type' => $event->type ?? null,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);
            report($exception);

            return response()->json(['received' => false], 400);
        }
    }

    private function ownedStudio(Request $request): Studio
    {
        $studio = Studio::query()
            ->where('owner_user_id', $request->user()->id)
            ->with(['owner', 'platformSubscriptionPlan'])
            ->latest()
            ->first();

        abort_unless($request->user()->role === 'admin' && $studio, 403, 'Only a studio owner can manage platform billing.');

        return $studio;
    }
}
