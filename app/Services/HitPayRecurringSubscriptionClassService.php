<?php

namespace App\Services;

use App\Jobs\FulfillOrderJob;
use App\Models\AppNotification;
use App\Models\ClassSession;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class HitPayRecurringSubscriptionClassService extends FinalSessionAwareSubscriptionClassService
{
    public function activateFromHitPayRecurringBilling(StudioSubscription $subscription, array $billing): StudioSubscription
    {
        $status = strtolower((string) ($billing['status'] ?? 'scheduled'));
        $providerId = (string) ($billing['id'] ?? $subscription->provider_subscription_id ?? '');
        $nextBilling = $this->hitPayNextBillingAt($billing, $subscription);

        $subscription->updateQuietly([
            'provider_subscription_id' => $providerId !== '' ? $providerId : $subscription->provider_subscription_id,
            'status' => $this->localHitPayStatus($status),
            'started_at' => $subscription->started_at ?: now(),
            'current_period_start' => $subscription->current_period_start ?: now(),
            'current_period_end' => $nextBilling,
            'next_billing_at' => $nextBilling,
            'meta' => array_merge((array) $subscription->meta, [
                'hitpay_recurring_status' => $status,
                'hitpay_times_to_be_charged' => $billing['times_to_be_charged'] ?? null,
                'hitpay_times_charged' => $billing['times_charged'] ?? null,
                'hitpay_recurring_url' => $billing['url'] ?? null,
            ]),
        ]);

        return $subscription->fresh(['classModel']);
    }

    public function syncHitPayRecurringBilling(StudioSubscription $subscription, array $billing): StudioSubscription
    {
        $status = strtolower((string) ($billing['status'] ?? ''));
        $updates = [
            'status' => $this->localHitPayStatus($status),
            'meta' => array_merge((array) $subscription->meta, [
                'hitpay_recurring_status' => $status,
                'hitpay_times_to_be_charged' => $billing['times_to_be_charged'] ?? null,
                'hitpay_times_charged' => $billing['times_charged'] ?? null,
            ]),
        ];

        if (in_array($status, ['canceled', 'cancelled', 'inactive', 'expired'], true)) {
            $updates['next_billing_at'] = null;
            $updates['cancelled_at'] = $subscription->cancelled_at ?: now();
        } else {
            $nextBilling = $this->hitPayNextBillingAt($billing, $subscription);
            if ($nextBilling) {
                $updates['current_period_end'] = $nextBilling;
                $updates['next_billing_at'] = $nextBilling;
            }
        }

        $subscription->updateQuietly($updates);

        return $subscription->fresh(['classModel']);
    }

    public function handleHitPayRecurringCharge(StudioSubscription $subscription, array $charge): ?Order
    {
        $status = strtolower((string) ($charge['status'] ?? ''));
        if (! in_array($status, ['completed', 'paid', 'succeeded', 'success'], true)) {
            $this->handleHitPayRecurringFailure($subscription, $charge);
            return null;
        }

        $chargeId = (string) ($charge['payment_id'] ?? $charge['id'] ?? '');
        if ($chargeId !== '' && Payment::query()->where('provider', 'hitpay')->where('provider_reference', $chargeId)->exists()) {
            return null;
        }

        $initialOrder = Order::query()
            ->where('studio_subscription_id', $subscription->id)
            ->where('billing_reason', 'subscription_initial')
            ->where('status', '!=', 'paid')
            ->oldest('id')
            ->first();

        if ($initialOrder) {
            DB::transaction(function () use ($initialOrder, $charge, $chargeId): void {
                $order = Order::query()->lockForUpdate()->find($initialOrder->id);
                if (! $order || $order->status === 'paid') {
                    return;
                }

                $amount = (float) ($charge['amount'] ?? $order->total);
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'provider_reference' => $chargeId !== '' ? $chargeId : $order->provider_reference,
                ]);

                Payment::query()->where('order_id', $order->id)->latest('id')->limit(1)->update([
                    'amount' => $amount,
                    'status' => 'paid',
                    'paid_at' => now(),
                    'provider' => 'hitpay',
                    'method' => 'hitpay',
                    'provider_reference' => $chargeId !== '' ? $chargeId : null,
                    'payload' => $charge,
                ]);
            });

            $subscription->updateQuietly(['status' => 'active', 'started_at' => $subscription->started_at ?: now()]);
            FulfillOrderJob::dispatch($initialOrder->id);
            $this->completeHitPayIfFinalCharge($subscription, $charge);

            return $initialOrder->fresh();
        }

        $nextSession = $this->nextUnfulfilledSession($subscription);
        if (! $nextSession) {
            $this->cancelHitPayRecurringBilling($subscription, 'charge_after_final_session');
            return null;
        }

        $amount = (float) ($charge['amount'] ?? $subscription->amount);
        $order = $this->createSubscriptionRenewalOrder(
            subscription: $subscription,
            classSession: $nextSession,
            provider: 'hitpay',
            amount: $amount,
            currency: strtoupper((string) ($charge['currency'] ?? $subscription->currency ?? 'MYR')),
            providerReference: $chargeId !== '' ? $chargeId : null,
            payload: $charge,
            billingPeriodStart: now(),
            billingPeriodEnd: $this->nextBillingAt($subscription->billing_interval ?: 'month'),
        );

        FulfillOrderJob::dispatch($order->id);
        $subscription->updateQuietly([
            'status' => 'active',
            'current_period_start' => now(),
            'current_period_end' => $this->nextBillingAt($subscription->billing_interval ?: 'month'),
            'next_billing_at' => $this->nextBillingAt($subscription->billing_interval ?: 'month'),
        ]);

        $this->completeHitPayIfFinalCharge($subscription, $charge);

        return $order;
    }

    public function handleHitPayRecurringFailure(StudioSubscription $subscription, array $payload): void
    {
        $subscription->updateQuietly(['status' => 'past_due']);

        $nextSession = $this->nextUnfulfilledSession($subscription);
        if ($nextSession) {
            $order = Order::query()
                ->where('studio_subscription_id', $subscription->id)
                ->where('billing_reason', 'subscription_cycle')
                ->whereHas('items', fn ($query) => $query->where('purchasable_type', ClassSession::class)->where('purchasable_id', $nextSession->id))
                ->latest('id')
                ->first();

            if ($order && $order->status !== 'paid') {
                $order->updateQuietly(['status' => 'past_due']);
                Payment::query()->where('order_id', $order->id)->update([
                    'status' => 'past_due',
                    'payload' => $payload,
                ]);
            }
        }

        AppNotification::create([
            'studio_id' => $subscription->studio_id,
            'user_id' => $subscription->user_id,
            'created_by' => null,
            'title' => 'Subscription payment failed',
            'message' => 'Your HitPay recurring payment failed. HitPay may retry the charge or ask you to update your payment method. Access to the next class remains pending until payment succeeds.',
            'type' => 'subscription_payment_failed',
            'action_url' => route('student.payments.index'),
            'data' => ['studio_subscription_id' => $subscription->id],
        ]);
    }

    public function cancelHitPayRecurringBilling(StudioSubscription $subscription, string $reason = 'completed'): void
    {
        if ($subscription->provider !== 'hitpay' || ! $subscription->provider_subscription_id) {
            return;
        }

        try {
            app(RecurringHitPayService::class)->cancelRecurringBilling($subscription->provider_subscription_id);
            $subscription->updateQuietly([
                'status' => 'completed',
                'next_billing_at' => null,
                'current_period_end' => now(),
                'cancelled_at' => now(),
                'meta' => array_merge((array) $subscription->meta, [
                    'hitpay_cancel_reason' => $reason,
                    'hitpay_cancelled_at' => now()->toIso8601String(),
                ]),
            ]);
        } catch (Throwable $exception) {
            Log::error('Unable to cancel HitPay recurring class subscription.', [
                'studio_subscription_id' => $subscription->id,
                'hitpay_recurring_billing_id' => $subscription->provider_subscription_id,
                'reason' => $reason,
                'message' => $exception->getMessage(),
            ]);
            report($exception);
        }
    }

    public function createDueHitpayRenewalOrders(HitPayService $hitpay): int
    {
        // Real HitPay recurring subscriptions are charged by HitPay. Keep the
        // legacy scheduler only for older subscriptions that do not yet have a
        // recurring-billing ID.
        $legacy = StudioSubscription::query()
            ->where('provider', 'hitpay')
            ->whereNull('provider_subscription_id')
            ->whereIn('status', ['active', 'past_due'])
            ->exists();

        return $legacy ? parent::createDueHitpayRenewalOrders($hitpay) : 0;
    }

    private function completeHitPayIfFinalCharge(StudioSubscription $subscription, array $charge): void
    {
        $timesCharged = (int) ($charge['times_charged'] ?? 0);
        $timesToCharge = (int) ($charge['times_to_be_charged'] ?? 0);

        if (($timesToCharge > 0 && $timesCharged >= $timesToCharge) || ! $this->nextUnfulfilledSession($subscription)) {
            $this->cancelHitPayRecurringBilling($subscription, 'final_class_charge_completed');
        }
    }

    private function localHitPayStatus(string $status): string
    {
        return match ($status) {
            'active' => 'active',
            'scheduled' => 'pending',
            'retrying' => 'past_due',
            'paused', 'inactive' => 'past_due',
            'canceled', 'cancelled', 'expired' => 'completed',
            default => 'pending',
        };
    }

    private function hitPayNextBillingAt(array $billing, StudioSubscription $subscription): ?Carbon
    {
        foreach (['next_charge_date', 'next_billing_date', 'start_date'] as $field) {
            if (! empty($billing[$field])) {
                try {
                    return Carbon::parse($billing[$field]);
                } catch (Throwable) {
                }
            }
        }

        return $subscription->next_billing_at ?: $this->nextBillingAt($subscription->billing_interval ?: 'month');
    }
}
