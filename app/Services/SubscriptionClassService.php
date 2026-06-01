<?php

namespace App\Services;

use App\Jobs\FulfillOrderJob;
use App\Models\ClassSession;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionClassService
{
    public function cartHasSubscriptionClass($cartModel): bool
    {
        return $cartModel->items->contains(function ($item) {
            return class_basename($item->purchasable_type) === 'ClassSession'
                && ($item->purchasable?->classModel?->type === 'subscription');
        });
    }

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

        $item = $subscriptionItems->first();
        $session = $item->purchasable;
        if (!$session || !$session->classModel) {
            return 'Subscription class is no longer available.';
        }

        $existing = StudioSubscription::query()
            ->where('user_id', auth()->id())
            ->where('class_id', $session->class_id)
            ->whereIn('status', ['pending', 'active', 'trialing', 'past_due'])
            ->exists();

        if ($existing) {
            return 'You already have an active or pending subscription for this class.';
        }

        return null;
    }

    public function createPendingSubscriptionFromOrder(Order $order): ?StudioSubscription
    {
        $order->loadMissing('items.purchasable.classModel');
        $item = $order->items->first(function ($orderItem) {
            return class_basename($orderItem->purchasable_type) === 'ClassSession'
                && ($orderItem->purchasable?->classModel?->type === 'subscription');
        });

        if (!$item || !$item->purchasable || !$item->purchasable->classModel) {
            return null;
        }

        $session = $item->purchasable;
        $class = $session->classModel;

        $subscription = StudioSubscription::create([
            'user_id' => $order->user_id,
            'class_id' => $class->id,
            'current_class_session_id' => $session->id,
            'initial_order_id' => $order->id,
            'provider' => $order->payment_provider,
            'status' => 'pending',
            'currency' => $order->currency,
            'amount' => $order->total,
            'billing_interval' => $class->billing_interval ?: $this->mapClassFrequencyToBillingInterval($class->recurrence_frequency),
            'next_billing_at' => $this->nextBillingAt($class->billing_interval ?: 'month'),
            'meta' => [
                'initial_class_session_id' => $session->id,
                'class_name' => $class->name,
            ],
        ]);

        $order->update([
            'studio_subscription_id' => $subscription->id,
            'billing_reason' => 'subscription_initial',
        ]);

        Payment::where('order_id', $order->id)->update([
            'studio_subscription_id' => $subscription->id,
        ]);

        return $subscription;
    }

    public function createStripeCheckoutSession(Order $order, Payment $payment, StudioSubscription $subscription): \Stripe\Checkout\Session
    {
        $order->loadMissing('items');
        $lineItem = $order->items->first();
        $label = $lineItem?->meta['label'] ?? 'Subscription Class';
        $interval = $subscription->billing_interval ?: 'month';

        return \Stripe\Checkout\Session::create([
            'mode' => 'subscription',
            'line_items' => [[
                'price_data' => [
                    'currency' => strtolower($order->currency ?? 'myr'),
                    'unit_amount' => (int) round(((float) $order->total) * 100),
                    'recurring' => ['interval' => $interval],
                    'product_data' => ['name' => $label],
                ],
                'quantity' => 1,
            ]],
            'success_url' => route('shop.checkout.success', [], true) . '?order=' . $order->id,
            'cancel_url' => route('shop.checkout.cancel', [], true) . '?order=' . $order->id,
            'metadata' => [
                'order_id' => (string) $order->id,
                'studio_subscription_id' => (string) $subscription->id,
                'class_id' => (string) $subscription->class_id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'order_id' => (string) $order->id,
                    'studio_subscription_id' => (string) $subscription->id,
                    'class_id' => (string) $subscription->class_id,
                ],
            ],
        ]);
    }

    public function activateFromStripeSession($session): void
    {
        $subscriptionId = isset($session->metadata->studio_subscription_id)
            ? (int) $session->metadata->studio_subscription_id
            : null;

        if (!$subscriptionId) {
            return;
        }

        StudioSubscription::whereKey($subscriptionId)->update([
            'status' => 'active',
            'provider_subscription_id' => $session->subscription ?? null,
            'provider_customer_id' => $session->customer ?? null,
            'started_at' => now(),
        ]);
    }

    public function syncFromStripeSubscription($stripeSubscription): void
    {
        $subscriptionId = isset($stripeSubscription->metadata->studio_subscription_id)
            ? (int) $stripeSubscription->metadata->studio_subscription_id
            : null;

        if (!$subscriptionId) {
            return;
        }

        StudioSubscription::whereKey($subscriptionId)->update([
            'status' => (string) ($stripeSubscription->status ?? 'active'),
            'provider_subscription_id' => $stripeSubscription->id ?? null,
            'provider_customer_id' => $stripeSubscription->customer ?? null,
            'current_period_start' => isset($stripeSubscription->current_period_start) ? Carbon::createFromTimestamp($stripeSubscription->current_period_start) : null,
            'current_period_end' => isset($stripeSubscription->current_period_end) ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
            'next_billing_at' => isset($stripeSubscription->current_period_end) ? Carbon::createFromTimestamp($stripeSubscription->current_period_end) : null,
            'cancelled_at' => !empty($stripeSubscription->canceled_at) ? Carbon::createFromTimestamp($stripeSubscription->canceled_at) : null,
        ]);
    }

    public function handleStripeInvoicePayment($invoice): ?Order
    {
        $providerSubscriptionId = $invoice->subscription ?? null;
        if (!$providerSubscriptionId) {
            return null;
        }

        $subscription = StudioSubscription::query()
            ->where('provider', 'stripe')
            ->where('provider_subscription_id', $providerSubscriptionId)
            ->first();

        if (!$subscription) {
            return null;
        }

        // checkout.session.completed handles the first invoice/order.
        if (($invoice->billing_reason ?? '') === 'subscription_create') {
            return null;
        }

        if (Payment::where('provider', 'stripe')->where('provider_reference', $invoice->id)->exists()) {
            return null;
        }

        $nextSession = $this->nextUnfulfilledSession($subscription);
        if (!$nextSession) {
            return null;
        }

        $amount = ((float) ($invoice->amount_paid ?? 0)) / 100;
        if ($amount <= 0) {
            $amount = (float) $subscription->amount;
        }

        $order = $this->createSubscriptionRenewalOrder(
            subscription: $subscription,
            classSession: $nextSession,
            provider: 'stripe',
            amount: $amount,
            currency: strtoupper($invoice->currency ?? $subscription->currency ?? 'MYR'),
            providerReference: $invoice->id,
            payload: method_exists($invoice, 'toArray') ? $invoice->toArray() : (array) $invoice,
            billingPeriodStart: isset($invoice->period_start) ? Carbon::createFromTimestamp($invoice->period_start) : null,
            billingPeriodEnd: isset($invoice->period_end) ? Carbon::createFromTimestamp($invoice->period_end) : null,
        );

        FulfillOrderJob::dispatch($order->id);

        return $order;
    }

    public function createDueHitpayRenewalOrders(HitPayService $hitpay): int
    {
        $created = 0;

        StudioSubscription::query()
            ->where('provider', 'hitpay')
            ->whereIn('status', ['active', 'past_due'])
            ->whereNotNull('next_billing_at')
            ->where('next_billing_at', '<=', now())
            ->chunkById(50, function ($subscriptions) use ($hitpay, &$created) {
                foreach ($subscriptions as $subscription) {
                    $nextSession = $this->nextUnfulfilledSession($subscription);
                    if (!$nextSession) {
                        $subscription->update(['status' => 'completed']);
                        continue;
                    }

                    $order = $this->createSubscriptionRenewalOrder(
                        subscription: $subscription,
                        classSession: $nextSession,
                        provider: 'hitpay',
                        amount: (float) $subscription->amount,
                        currency: strtoupper($subscription->currency ?? 'MYR'),
                        providerReference: null,
                        payload: ['billing_cycle' => 'generated_due_payment_request'],
                        billingPeriodStart: now(),
                        billingPeriodEnd: $this->nextBillingAt($subscription->billing_interval ?: 'month'),
                        status: 'pending'
                    );

                    $resp = $hitpay->createPaymentRequest([
                        'amount' => number_format((float) $order->total, 2, '.', ''),
                        'currency' => strtoupper($order->currency ?? 'MYR'),
                        'purpose' => 'Subscription renewal Order #' . $order->id,
                        'reference_number' => (string) $order->id,
                        'redirect_url' => route('shop.checkout.success', [], true) . '?order=' . $order->id,
                        'webhook' => route('webhooks.hitpay', [], true),
                    ]);

                    $providerRef = $resp['id'] ?? null;
                    $checkoutUrl = $resp['url'] ?? null;

                    $order->update(['provider_reference' => $providerRef]);
                    Payment::where('order_id', $order->id)->update([
                        'provider_reference' => $providerRef,
                        'payload' => array_merge($resp, ['checkout_url' => $checkoutUrl]),
                    ]);

                    $subscription->update([
                        'status' => 'past_due',
                        'next_billing_at' => now()->addDays((int) ($subscription->classModel?->subscription_grace_days ?? 3)),
                    ]);

                    $created++;
                }
            });

        return $created;
    }

    public function createSubscriptionRenewalOrder(
        StudioSubscription $subscription,
        ClassSession $classSession,
        string $provider,
        float $amount,
        string $currency,
        ?string $providerReference,
        array $payload,
        ?Carbon $billingPeriodStart = null,
        ?Carbon $billingPeriodEnd = null,
        string $status = 'paid'
    ): Order {
        return DB::transaction(function () use ($subscription, $classSession, $provider, $amount, $currency, $providerReference, $payload, $billingPeriodStart, $billingPeriodEnd, $status) {
            $paidAt = $status === 'paid' ? now() : null;

            $order = Order::create([
                'user_id' => $subscription->user_id,
                'studio_subscription_id' => $subscription->id,
                'currency' => $currency,
                'subtotal' => $amount,
                'total' => $amount,
                'status' => $status,
                'payment_provider' => $provider,
                'billing_reason' => 'subscription_cycle',
                'provider_reference' => $providerReference,
                'paid_at' => $paidAt,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'purchasable_type' => get_class($classSession),
                'purchasable_id' => $classSession->id,
                'quantity' => 1,
                'unit_price' => $amount,
                'currency' => $currency,
                'meta' => [
                    'label' => $classSession->classModel?->name ?? 'Subscription Class',
                    'date' => optional($classSession->start_time)->format('Y-m-d'),
                    'time' => optional($classSession->start_time)->format('H:i') . ' - ' . optional($classSession->end_time)->format('H:i'),
                    'billing_reason' => 'subscription_cycle',
                    'studio_subscription_id' => $subscription->id,
                ],
            ]);

            Payment::create([
                'user_id' => $subscription->user_id,
                'order_id' => $order->id,
                'studio_subscription_id' => $subscription->id,
                'amount' => $amount,
                'currency' => $currency,
                'method' => $provider,
                'provider' => $provider,
                'reference' => 'SUB-' . $subscription->id . '-ORD-' . $order->id . '-' . Str::upper(Str::random(5)),
                'provider_reference' => $providerReference,
                'status' => $status,
                'paid_at' => $paidAt,
                'billing_period_start' => $billingPeriodStart,
                'billing_period_end' => $billingPeriodEnd,
                'payload' => $payload,
            ]);

            if ($status === 'paid') {
                $subscription->update([
                    'status' => 'active',
                    'current_period_start' => $billingPeriodStart,
                    'current_period_end' => $billingPeriodEnd,
                    'next_billing_at' => $billingPeriodEnd ?: $this->nextBillingAt($subscription->billing_interval ?: 'month'),
                ]);
            }

            return $order;
        });
    }

    public function nextUnfulfilledSession(StudioSubscription $subscription): ?ClassSession
    {
        $after = null;

        if ($subscription->last_fulfilled_class_session_id) {
            $after = ClassSession::whereKey($subscription->last_fulfilled_class_session_id)->value('start_time');
        }

        if (!$after && $subscription->current_class_session_id) {
            return ClassSession::with('classModel')
                ->whereKey($subscription->current_class_session_id)
                ->first();
        }

        return ClassSession::with('classModel')
            ->where('class_id', $subscription->class_id)
            ->when($after, fn ($q) => $q->where('start_time', '>', $after))
            ->orderBy('start_time')
            ->first();
    }

    public function mapClassFrequencyToBillingInterval(?string $frequency): string
    {
        return match ($frequency) {
            'everyday', '7days', 'custom' => 'week',
            'yearly' => 'year',
            default => 'month',
        };
    }

    public function nextBillingAt(string $interval): Carbon
    {
        return match ($interval) {
            'day' => now()->addDay(),
            'week' => now()->addWeek(),
            'year' => now()->addYear(),
            default => now()->addMonth(),
        };
    }
}
