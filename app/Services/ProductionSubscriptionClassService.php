<?php

namespace App\Services;

use App\Models\StudioSubscription;

class ProductionSubscriptionClassService extends HitPayRecurringSubscriptionClassService
{
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
