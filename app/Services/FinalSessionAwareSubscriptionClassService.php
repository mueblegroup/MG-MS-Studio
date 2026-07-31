<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Throwable;

class FinalSessionAwareSubscriptionClassService extends ReliableSubscriptionClassService
{
    public function activateFromStripeSession($session): void
    {
        parent::activateFromStripeSession($session);

        $subscriptionId = isset($session->metadata->studio_subscription_id)
            ? (int) $session->metadata->studio_subscription_id
            : null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = StudioSubscription::query()->find($subscriptionId);

        if ($subscription) {
            $this->scheduleStripeCancellationAfterFinalSession($subscription);
        }
    }

    public function handleStripeInvoicePayment($invoice): ?\App\Models\Order
    {
        $order = parent::handleStripeInvoicePayment($invoice);

        if (($invoice->billing_reason ?? '') === 'subscription_create') {
            return $order;
        }

        $providerSubscriptionId = $this->subscriptionIdFromInvoice($invoice);

        if (! $providerSubscriptionId) {
            return $order;
        }

        $subscription = StudioSubscription::query()
            ->where('provider', 'stripe')
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            return $order;
        }

        if (! $this->nextUnfulfilledSession($subscription)) {
            $this->cancelStripeSubscriptionNow($subscription, 'invoice_after_final_session');
        } else {
            $this->scheduleStripeCancellationAfterFinalSession($subscription);
        }

        return $order;
    }

    public function scheduleStripeCancellationAfterFinalSession(StudioSubscription $subscription): void
    {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return;
        }

        $finalSession = ClassSession::query()
            ->where('class_id', $subscription->class_id)
            ->where('status', '!=', 'cancelled')
            ->orderByDesc('start_time')
            ->first();

        if (! $finalSession) {
            $this->cancelStripeSubscriptionNow($subscription, 'class_has_no_remaining_sessions');
            return;
        }

        $endsAt = Carbon::parse($finalSession->end_time ?: $finalSession->start_time);

        if ($endsAt->isPast()) {
            $this->cancelStripeSubscriptionNow($subscription, 'final_session_already_ended');
            return;
        }

        try {
            $stripe = new StripeClient((string) config('services.stripe.secret'));
            $stripeSubscription = $stripe->subscriptions->update(
                $subscription->provider_subscription_id,
                [
                    'cancel_at' => $endsAt->getTimestamp(),
                    'proration_behavior' => 'none',
                    'metadata' => [
                        'studio_subscription_id' => (string) $subscription->id,
                        'class_id' => (string) $subscription->class_id,
                        'scheduled_final_session_id' => (string) $finalSession->id,
                    ],
                ]
            );

            $subscription->updateQuietly([
                'next_billing_at' => $subscription->next_billing_at && $subscription->next_billing_at->lt($endsAt)
                    ? $subscription->next_billing_at
                    : null,
                'meta' => array_merge((array) $subscription->meta, [
                    'stripe_cancel_at' => $endsAt->toIso8601String(),
                    'final_class_session_id' => $finalSession->id,
                ]),
            ]);

            Log::info('Stripe class subscription scheduled to stop after final session.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $stripeSubscription->id,
                'final_class_session_id' => $finalSession->id,
                'cancel_at' => $endsAt->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to schedule Stripe class subscription cancellation.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->provider_subscription_id,
                'message' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }

    private function cancelStripeSubscriptionNow(StudioSubscription $subscription, string $reason): void
    {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return;
        }

        try {
            $stripe = new StripeClient((string) config('services.stripe.secret'));
            $stripe->subscriptions->cancel($subscription->provider_subscription_id, [
                'invoice_now' => false,
                'prorate' => false,
            ]);

            $subscription->updateQuietly([
                'status' => 'completed',
                'next_billing_at' => null,
                'current_period_end' => now(),
                'cancelled_at' => now(),
                'meta' => array_merge((array) $subscription->meta, [
                    'stripe_cancel_reason' => $reason,
                    'stripe_cancelled_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Stripe class subscription cancelled after class completion.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->provider_subscription_id,
                'reason' => $reason,
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to cancel completed Stripe class subscription.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->provider_subscription_id,
                'reason' => $reason,
                'message' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }

    private function subscriptionIdFromInvoice(object $invoice): ?string
    {
        $subscription = $invoice->subscription ?? null;

        if (is_string($subscription) && $subscription !== '') {
            return $subscription;
        }

        if (is_object($subscription) && ! empty($subscription->id)) {
            return (string) $subscription->id;
        }

        return null;
    }
}
