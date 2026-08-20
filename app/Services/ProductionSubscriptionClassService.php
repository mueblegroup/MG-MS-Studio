<?php

namespace App\Services;

use App\Models\StudioSubscription;

class ProductionSubscriptionClassService extends HitPayRecurringSubscriptionClassService
{
    public function validateSubscriptionCart($cartModel): ?string
    {
        $subscriptionItems = $cartModel->items->filter(function ($item) {
            return class_basename($item->purchasable_type) === 'ClassSession'
                && ($item->purchasable?->classModel?->type === 'subscription');
        });

        if ($subscriptionItems->isEmpty()) {
            return null;
        }

        if ($cartModel->items->count() > 1 || $subscriptionItems->count() > 1) {
            return 'Subscription classes must be checked out alone. Please remove other items from the cart first.';
        }

        $session = $subscriptionItems->first()?->purchasable;
        if (! $session || ! $session->classModel) {
            return 'Subscription class is no longer available.';
        }

        $existing = StudioSubscription::query()
            ->where('user_id', auth()->id())
            ->where('class_id', $session->class_id)
            ->whereIn('status', ['pending', 'active', 'trialing', 'past_due'])
            ->latest('id')
            ->first();

        if (! $existing) {
            return null;
        }

        if (in_array(strtolower((string) $existing->status), ['pending', 'past_due'], true)) {
            return 'You already have an incomplete subscription for this class. Open My Subscriptions and use Retry payment to continue it.';
        }

        return 'You already have an active subscription for this class.';
    }

    public function handleStripeInvoiceFailure(object $invoice): void
    {
        parent::handleStripeInvoiceFailure($invoice);

        $providerSubscriptionId = null;
        $subscriptionValue = $invoice->subscription ?? null;

        if (is_string($subscriptionValue) && $subscriptionValue !== '') {
            $providerSubscriptionId = $subscriptionValue;
        } elseif (is_object($subscriptionValue) && ! empty($subscriptionValue->id)) {
            $providerSubscriptionId = (string) $subscriptionValue->id;
        }

        if (! $providerSubscriptionId) {
            return;
        }

        $subscription = StudioSubscription::query()
            ->with('classModel')
            ->where('provider', 'stripe')
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription || ! $subscription->classModel) {
            return;
        }

        $graceUntil = $subscription->classModel->subscriptionGraceUntil(now());
        $subscription->updateQuietly([
            'meta' => array_merge((array) $subscription->meta, [
                'payment_grace_until' => $graceUntil->toIso8601String(),
                'payment_grace_value' => $subscription->classModel->subscriptionGraceValue(),
                'payment_grace_unit' => $subscription->classModel->subscriptionGraceUnit(),
            ]),
        ]);
    }
}
