<?php

namespace App\Services;

use App\Jobs\FulfillOrderJob;
use App\Models\AppNotification;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
        $periodStart = $stripeSubscription->current_period_start ?? $item?->current_period_start ?? null;
        $periodEnd = $stripeSubscription->current_period_end ?? $item?->current_period_end ?? null;
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
            StudioSubscription::query()->whereKey($subscriptionId)->update($updates);
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

    public function activateFromStripeSession($session): void
    {
        parent::activateFromStripeSession($session);

        $subscriptionId = isset($session->metadata->studio_subscription_id)
            ? (int) $session->metadata->studio_subscription_id
            : null;

        if (! $subscriptionId) {
            return;
        }

        $subscription = StudioSubscription::query()
            ->with(['classModel.sessions' => fn ($query) => $query->orderBy('start_time')])
            ->find($subscriptionId);

        if (! $subscription) {
            return;
        }

        $this->createScheduledSessionOrders($subscription);
        $this->alignStripeChargeToNextSession($subscription);
    }

    /**
     * Create the student's local schedule/payment records immediately.
     *
     * These are local pending orders only. Stripe invoices remain responsible
     * for collecting money, so creating the schedule cannot double-charge.
     */
    public function createScheduledSessionOrders(StudioSubscription $subscription): int
    {
        $subscription->loadMissing('classModel.sessions');
        $initialSessionId = (int) ($subscription->meta['initial_class_session_id']
            ?? $subscription->current_class_session_id);
        $initialStart = ClassSession::query()->whereKey($initialSessionId)->value('start_time');
        $created = 0;

        $sessions = $subscription->classModel?->sessions
            ?->filter(fn (ClassSession $session) => $session->status !== 'cancelled')
            ->filter(fn (ClassSession $session) => ! $initialStart || $session->start_time?->gt($initialStart))
            ->sortBy('start_time') ?? collect();

        foreach ($sessions as $session) {
            if ($this->renewalOrderForSession($subscription, $session)) {
                continue;
            }

            $chargeAt = $this->chargeAtForSession($session);
            $this->createSubscriptionRenewalOrder(
                subscription: $subscription,
                classSession: $session,
                provider: $subscription->provider,
                amount: (float) $subscription->amount,
                currency: strtoupper((string) ($subscription->currency ?? 'MYR')),
                providerReference: null,
                payload: [
                    'billing_cycle' => 'scheduled_before_class',
                    'charge_at' => $chargeAt->toIso8601String(),
                    'class_starts_at' => $session->start_time?->toIso8601String(),
                ],
                billingPeriodStart: $chargeAt,
                billingPeriodEnd: $session->start_time,
                status: 'pending',
            );
            $created++;
        }

        return $created;
    }

    public function handleStripeInvoicePayment($invoice): ?Order
    {
        $invoiceId = (string) ($invoice->id ?? '');
        $providerSubscriptionId = $this->stripeSubscriptionIdFromInvoice($invoice);

        if ($providerSubscriptionId === null) {
            Log::warning('Stripe class renewal skipped: invoice has no subscription ID.', ['invoice_id' => $invoiceId ?: null]);
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

        if ($invoiceId !== '' && Payment::query()->where('provider', 'stripe')->where('provider_reference', $invoiceId)->exists()) {
            return null;
        }

        $nextSession = $this->nextUnfulfilledSession($subscription);
        if (! $nextSession) {
            $subscription->updateQuietly(['status' => 'completed', 'next_billing_at' => null]);
            return null;
        }

        $amount = ((float) ($invoice->amount_paid ?? 0)) / 100;
        if ($amount <= 0) {
            $amount = (float) $subscription->amount;
        }

        [$periodStart, $periodEnd] = $this->billingPeriodFromInvoice($invoice);
        $invoicePayload = method_exists($invoice, 'toArray') ? $invoice->toArray() : (array) $invoice;
        $order = $this->renewalOrderForSession($subscription, $nextSession);

        if ($order) {
            DB::transaction(function () use ($order, $amount, $invoiceId, $invoicePayload, $periodStart, $periodEnd): void {
                $lockedOrder = Order::query()->lockForUpdate()->find($order->id);
                if (! $lockedOrder || $lockedOrder->status === 'paid') {
                    return;
                }

                $lockedOrder->update([
                    'subtotal' => $amount,
                    'total' => $amount,
                    'status' => 'paid',
                    'provider_reference' => $invoiceId !== '' ? $invoiceId : $lockedOrder->provider_reference,
                    'paid_at' => now(),
                ]);

                $payment = Payment::query()->where('order_id', $lockedOrder->id)->latest('id')->first();
                if ($payment) {
                    $payment->update([
                        'amount' => $amount,
                        'provider' => 'stripe',
                        'method' => 'stripe',
                        'provider_reference' => $invoiceId !== '' ? $invoiceId : $payment->provider_reference,
                        'status' => 'paid',
                        'paid_at' => now(),
                        'billing_period_start' => $periodStart,
                        'billing_period_end' => $periodEnd,
                        'payload' => array_merge((array) $payment->payload, $invoicePayload),
                    ]);
                }
            });
            $order->refresh();
        } else {
            // Compatibility path for subscriptions created before scheduled
            // orders were introduced.
            $order = $this->createSubscriptionRenewalOrder(
                subscription: $subscription,
                classSession: $nextSession,
                provider: 'stripe',
                amount: $amount,
                currency: strtoupper((string) ($invoice->currency ?? $subscription->currency ?? 'MYR')),
                providerReference: $invoiceId !== '' ? $invoiceId : null,
                payload: $invoicePayload,
                billingPeriodStart: $periodStart,
                billingPeriodEnd: $periodEnd,
            );
        }

        FulfillOrderJob::dispatch($order->id);
        $this->alignStripeChargeToSessionAfter($subscription, $nextSession);

        Log::info('Stripe class renewal order created.', [
            'invoice_id' => $invoiceId ?: null,
            'studio_subscription_id' => $subscription->id,
            'order_id' => $order->id,
            'class_session_id' => $nextSession->id,
        ]);

        return $order;
    }

    public function handleStripeInvoiceFailure(object $invoice): void
    {
        $providerSubscriptionId = $this->stripeSubscriptionIdFromInvoice($invoice);
        if (! $providerSubscriptionId) {
            return;
        }

        $subscription = StudioSubscription::query()
            ->with(['user:id,name,email', 'classModel:id,name'])
            ->where('provider', 'stripe')
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->first();

        if (! $subscription) {
            return;
        }

        $subscription->updateQuietly(['status' => 'past_due']);

        $nextSession = $this->nextUnfulfilledSession($subscription);
        if ($nextSession && ($order = $this->renewalOrderForSession($subscription, $nextSession))) {
            $order->updateQuietly([
                'status' => 'past_due',
                'provider_reference' => $invoice->id ?? $order->provider_reference,
            ]);
            Payment::query()->where('order_id', $order->id)->update([
                'status' => 'past_due',
                'provider_reference' => $invoice->id ?? null,
                'payload' => method_exists($invoice, 'toArray') ? $invoice->toArray() : (array) $invoice,
            ]);
        }

        AppNotification::create([
            'studio_id' => $subscription->studio_id,
            'user_id' => $subscription->user_id,
            'created_by' => null,
            'title' => 'Subscription payment failed',
            'message' => 'Your renewal payment for '.$subscription->classModel?->name.' failed. You cannot attend the next session until Stripe successfully retries the payment.',
            'type' => 'subscription_payment_failed',
            'action_url' => route('student.payments.index'),
            'data' => [
                'studio_subscription_id' => $subscription->id,
                'stripe_invoice_id' => $invoice->id ?? null,
            ],
        ]);
    }

    public function nextUnfulfilledSession(StudioSubscription $subscription): ?ClassSession
    {
        $after = null;

        if ($subscription->last_fulfilled_class_session_id) {
            $after = ClassSession::whereKey($subscription->last_fulfilled_class_session_id)->value('start_time');
        }

        return ClassSession::with('classModel')
            ->where('class_id', $subscription->class_id)
            ->where('status', '!=', 'cancelled')
            ->when($after, fn ($query) => $query->where('start_time', '>', $after))
            ->when(! $after && $subscription->current_class_session_id, function ($query) use ($subscription) {
                $query->where('id', $subscription->current_class_session_id);
            })
            ->orderBy('start_time')
            ->first();
    }

    private function intendedBillingInterval(?ClassModel $class): string
    {
        $configured = strtolower((string) ($class?->billing_interval ?? ''));
        if (in_array($configured, ['day', 'week', 'month', 'year'], true)) {
            return $configured;
        }

        return $this->mapClassFrequencyToBillingInterval($class?->recurrence_frequency);
    }

    private function renewalOrderForSession(StudioSubscription $subscription, ClassSession $session): ?Order
    {
        return Order::query()
            ->where('studio_subscription_id', $subscription->id)
            ->where('billing_reason', 'subscription_cycle')
            ->whereHas('items', fn ($query) => $query
                ->where('purchasable_type', ClassSession::class)
                ->where('purchasable_id', $session->id))
            ->latest('id')
            ->first();
    }

    private function chargeAtForSession(ClassSession $session): Carbon
    {
        return Carbon::parse($session->start_time)->subDay();
    }

    private function alignStripeChargeToNextSession(StudioSubscription $subscription): void
    {
        $nextSession = $this->nextUnfulfilledSession($subscription);
        $initialSessionId = (int) ($subscription->meta['initial_class_session_id']
            ?? $subscription->current_class_session_id);

        if ($nextSession && (int) $nextSession->id === $initialSessionId) {
            $nextSession = ClassSession::query()
                ->where('class_id', $subscription->class_id)
                ->where('status', '!=', 'cancelled')
                ->where('start_time', '>', $nextSession->start_time)
                ->orderBy('start_time')
                ->first();
        }

        if ($nextSession) {
            $this->updateStripeBillingAnchor($subscription, $nextSession);
        }
    }

    private function alignStripeChargeToSessionAfter(
        StudioSubscription $subscription,
        ClassSession $paidSession
    ): void {
        $followingSession = ClassSession::query()
            ->where('class_id', $subscription->class_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '>', $paidSession->start_time)
            ->orderBy('start_time')
            ->first();

        if ($followingSession) {
            $this->updateStripeBillingAnchor($subscription, $followingSession);
        }
    }

    private function updateStripeBillingAnchor(
        StudioSubscription $subscription,
        ClassSession $session
    ): void {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return;
        }

        $chargeAt = $this->chargeAtForSession($session);
        $anchor = $chargeAt->isFuture() ? $chargeAt->getTimestamp() : 'now';

        try {
            \Stripe\Stripe::setApiKey((string) config('services.stripe.secret'));
            \Stripe\Subscription::update($subscription->provider_subscription_id, [
                'billing_cycle_anchor' => $anchor,
                'proration_behavior' => 'none',
                'metadata' => [
                    'next_class_session_id' => (string) $session->id,
                    'next_class_starts_at' => $session->start_time?->toIso8601String(),
                    'charge_policy' => '24_hours_before_class',
                ],
            ]);

            $subscription->updateQuietly(['next_billing_at' => $chargeAt->isFuture() ? $chargeAt : now()]);
        } catch (\Throwable $exception) {
            // Never fail a paid webhook after Stripe has already collected
            // money. Keep the event retry-safe and expose the scheduling issue
            // in logs for immediate operational attention.
            Log::error('Unable to align Stripe class charge 24 hours before session.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->provider_subscription_id,
                'class_session_id' => $session->id,
                'charge_at' => $chargeAt->toIso8601String(),
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function stripeSubscriptionIdFromInvoice(object $invoice): ?string
    {
        foreach ([
            $invoice->subscription ?? null,
            $invoice->parent->subscription_details->subscription ?? null,
            $invoice->lines->data[0]->parent->subscription_item_details->subscription ?? null,
        ] as $subscription) {
            if (is_string($subscription) && $subscription !== '') {
                return $subscription;
            }
            if (is_object($subscription) && ! empty($subscription->id)) {
                return (string) $subscription->id;
            }
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
