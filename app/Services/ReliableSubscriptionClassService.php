<?php

namespace App\Services;

use App\Jobs\FulfillOrderJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ReliableSubscriptionClassService extends SubscriptionClassService
{
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

        // The initial class purchase is fulfilled by checkout.session.completed.
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
