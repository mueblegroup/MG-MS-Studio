<?php

namespace App\Services;

use App\Jobs\FulfillOrderJob;
use App\Models\ClassModel;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReliableSubscriptionClassService extends SubscriptionClassService
{
    public function createPendingSubscriptionFromOrder(Order $order): ?StudioSubscription
    {
        $subscription = parent::createPendingSubscriptionFromOrder($order);

        if (! $subscription) {
            return null;
        }

        $subscription->loadMissing('classModel');
        $interval = $this->intendedBillingInterval($subscription->classModel);

        $subscription->updateQuietly([
            'billing_interval' => $interval,
            'next_billing_at' => $this->nextBillingAt($interval),
        ]);

        return $subscription->fresh();
    }

    public function createStripeCheckoutSession(Order $order, Payment $payment, StudioSubscription $subscription): \Stripe\Checkout\Session
    {
        $subscription->loadMissing('classModel');
        $interval = $this->intendedBillingInterval($subscription->classModel);

        if ($subscription->billing_interval !== $interval) {
            $subscription->updateQuietly([
                'billing_interval' => $interval,
                'next_billing_at' => $this->nextBillingAt($interval),
            ]);
            $subscription->refresh();
        }

        return parent::createStripeCheckoutSession($order, $payment, $subscription);
    }

    public function syncFromStripeSubscription($stripeSubscription): void
    {
        parent::syncFromStripeSubscription($stripeSubscription);

        $subscriptionId = isset($stripeSubscription->metadata->studio_subscription_id)
            ? (int) $stripeSubscription->metadata->studio_subscription_id
            : null;

        if (! $subscriptionId) {
            return;
        }

        $item = $stripeSubscription->items->data[0] ?? null;
        $periodStart = $stripeSubscription->current_period_start
            ?? $item?->current_period_start
            ?? null;
        $periodEnd = $stripeSubscription->current_period_end
            ?? $item?->current_period_end
            ?? null;
        $interval = $item?->price?->recurring?->interval ?? null;

        $updates = [];

        if ($periodStart) {
            $updates['current_period_start'] = Carbon::createFromTimestamp((int) $periodStart);
        }

        if ($periodEnd) {
            $updates['current_period_end'] = Carbon::createFromTimestamp((int) $periodEnd);
            $updates['next_billing_at'] = Carbon::createFromTimestamp((int) $periodEnd);
        }

        if (is_string($interval) && in_array($interval, ['day', 'week', 'month', 'year'], true)) {
            $updates['billing_interval'] = $interval;
        }

        if ($updates !== []) {
            StudioSubscription::query()
                ->whereKey($subscriptionId)
                ->update($updates);
        }
    }

    public function refreshStripeBillingPeriod(StudioSubscription $subscription): StudioSubscription
    {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return $subscription;
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSubscription = \Stripe\Subscription::retrieve([
            'id' => $subscription->provider_subscription_id,
            'expand' => ['items.data.price'],
        ]);

        $this->syncFromStripeSubscription($stripeSubscription);

        return $subscription->fresh(['classModel']);
    }

    public function handleStripeInvoicePayment($invoice): ?Order
    {
        $invoiceId = (string) ($invoice->id ?? '');
        $providerSubscriptionId = $this->stripeSubscriptionIdFromInvoice($invoice);

        if ($providerSubscriptionId === null) {
            Log::warning('Stripe class renewal skipped: invoice has no subscription ID.', [
                'invoice_id' => $invoiceId ?: null,
                'billing_reason' => $invoice->billing_reason ?? null,
            ]);

            return null;
        }

        $subscription = StudioSubscription::query()
            ->where('provider', 'stripe')
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            Log::warning('Stripe class renewal skipped: local subscription was not found.', [
                'invoice_id' => $invoiceId ?: null,
                'stripe_subscription_id' => $providerSubscriptionId,
            ]);

            return null;
        }

        if (($invoice->billing_reason ?? '') === 'subscription_create') {
            return null;
        }

        if ($invoiceId !== '' && Payment::query()
            ->where('provider', 'stripe')
            ->where('provider_reference', $invoiceId)
            ->exists()) {
            return null;
        }

        $nextSession = $this->nextUnfulfilledSession($subscription);

        if (! $nextSession) {
            $subscription->update([
                'status' => 'completed',
                'next_billing_at' => null,
            ]);

            Log::info('Stripe class subscription completed because no future session remains.', [
                'invoice_id' => $invoiceId ?: null,
                'studio_subscription_id' => $subscription->id,
                'class_id' => $subscription->class_id,
            ]);

            return null;
        }

        $amount = ((float) ($invoice->amount_paid ?? 0)) / 100;
        if ($amount <= 0) {
            $amount = (float) $subscription->amount;
        }

        [$periodStart, $periodEnd] = $this->billingPeriodFromInvoice($invoice);

        $order = $this->createSubscriptionRenewalOrder(
            subscription: $subscription,
            classSession: $nextSession,
            provider: 'stripe',
            amount: $amount,
            currency: strtoupper((string) ($invoice->currency ?? $subscription->currency ?? 'MYR')),
            providerReference: $invoiceId !== '' ? $invoiceId : null,
            payload: method_exists($invoice, 'toArray') ? $invoice->toArray() : (array) $invoice,
            billingPeriodStart: $periodStart,
            billingPeriodEnd: $periodEnd,
        );

        FulfillOrderJob::dispatch($order->id);

        Log::info('Stripe class renewal order created.', [
            'invoice_id' => $invoiceId ?: null,
            'stripe_subscription_id' => $providerSubscriptionId,
            'studio_subscription_id' => $subscription->id,
            'order_id' => $order->id,
            'class_session_id' => $nextSession->id,
        ]);

        return $order;
    }

    private function intendedBillingInterval(?ClassModel $class): string
    {
        $configured = strtolower((string) ($class?->billing_interval ?? ''));

        if (in_array($configured, ['day', 'week', 'month', 'year'], true)) {
            return $configured;
        }

        return $this->mapClassFrequencyToBillingInterval($class?->recurrence_frequency);
    }

    private function stripeSubscriptionIdFromInvoice(object $invoice): ?string
    {
        $subscription = $invoice->subscription ?? null;

        if (is_string($subscription) && $subscription !== '') {
            return $subscription;
        }

        if (is_object($subscription) && ! empty($subscription->id)) {
            return (string) $subscription->id;
        }

        $parentSubscription = $invoice->parent->subscription_details->subscription ?? null;

        if (is_string($parentSubscription) && $parentSubscription !== '') {
            return $parentSubscription;
        }

        if (is_object($parentSubscription) && ! empty($parentSubscription->id)) {
            return (string) $parentSubscription->id;
        }

        $lineSubscription = $invoice->lines->data[0]->parent->subscription_item_details->subscription ?? null;

        if (is_string($lineSubscription) && $lineSubscription !== '') {
            return $lineSubscription;
        }

        if (is_object($lineSubscription) && ! empty($lineSubscription->id)) {
            return (string) $lineSubscription->id;
        }

        return null;
    }

    private function billingPeriodFromInvoice(object $invoice): array
    {
        $linePeriod = $invoice->lines->data[0]->period ?? null;
        $start = $linePeriod->start ?? $invoice->period_start ?? null;
        $end = $linePeriod->end ?? $invoice->period_end ?? null;

        return [
            $start ? Carbon::createFromTimestamp((int) $start) : null,
            $end ? Carbon::createFromTimestamp((int) $end) : null,
        ];
    }
}
