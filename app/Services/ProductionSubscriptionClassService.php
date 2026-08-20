<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Order;
use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use RuntimeException;

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

    public function expectedUpcomingBilling(StudioSubscription $subscription): array
    {
        $subscription->loadMissing(['classModel', 'user']);
        $session = $this->nextBillableSessionForInspection($subscription);

        if (! $session) {
            return [
                'session' => null,
                'target_charge_at' => null,
                'hitpay_start_date_sgt' => null,
            ];
        }

        $target = Carbon::parse($session->start_time)->subDay();

        return [
            'session' => $session,
            'target_charge_at' => $target,
            'hitpay_start_date_sgt' => $target->copy()->timezone('Asia/Singapore')->toDateString(),
        ];
    }

    public function repairHitPayUpcomingBilling(StudioSubscription $subscription): array
    {
        if (strtolower((string) $subscription->provider) !== 'hitpay') {
            throw new RuntimeException('Only HitPay subscriptions can be repaired by this method.');
        }

        if (! $subscription->provider_subscription_id) {
            throw new RuntimeException('This HitPay subscription does not have a recurring billing ID.');
        }

        $subscription->loadMissing(['classModel', 'user']);
        $expected = $this->expectedUpcomingBilling($subscription);
        /** @var ClassSession|null $session */
        $session = $expected['session'];

        if (! $session) {
            return [
                'repaired' => false,
                'reason' => 'no_remaining_session',
                'expected' => $expected,
            ];
        }

        $startDate = (string) $expected['hitpay_start_date_sgt'];
        $todaySgt = Carbon::now('Asia/Singapore')->toDateString();
        if ($startDate < $todaySgt) {
            $startDate = $todaySgt;
        }

        [$cycle, $repeat, $frequency] = $this->productionHitPayCycle($subscription->billing_interval ?: 'month');

        $payload = [
            'plan_id' => null,
            'name' => (string) ($subscription->classModel?->name ?? 'Subscription Class'),
            'description' => 'Recurring class subscription',
            'payment_methods' => ['card'],
            'cycle' => $cycle,
            'customer_email' => (string) $subscription->user?->email,
            'customer_name' => (string) $subscription->user?->name,
            'start_date' => $startDate,
            'reference' => (string) ($subscription->meta['hitpay_reference'] ?? ('SUB:'.$subscription->id)),
            'amount' => (float) $subscription->amount,
            'send_email' => 'true',
        ];

        if ($cycle === 'custom') {
            $payload['cycle_repeat'] = $repeat;
            $payload['cycle_frequency'] = $frequency;
        }

        $response = app(RecurringHitPayService::class)->updateRecurringBilling(
            (string) $subscription->provider_subscription_id,
            $payload
        );

        $providerDate = Carbon::parse($startDate, 'Asia/Singapore')->startOfDay();
        $subscription->updateQuietly([
            'next_billing_at' => $providerDate,
            'current_period_end' => $providerDate,
            'meta' => array_merge((array) $subscription->meta, [
                'hitpay_next_charge_date_sgt' => $startDate,
                'target_next_charge_at' => $expected['target_charge_at']?->toIso8601String(),
                'next_class_session_id' => $session->id,
                'hitpay_timing_precision' => 'date_only_sgt',
                'billing_schedule_repaired_at' => now()->toIso8601String(),
            ]),
        ]);

        return [
            'repaired' => true,
            'expected' => $expected,
            'provider_start_date_sgt' => $startDate,
            'provider_response' => $response,
        ];
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

    private function nextBillableSessionForInspection(StudioSubscription $subscription): ?ClassSession
    {
        $session = $this->nextUnfulfilledSession($subscription);
        $initialSessionId = (int) ($subscription->meta['initial_class_session_id'] ?? $subscription->current_class_session_id);

        if (! $session) {
            return null;
        }

        if ((int) $session->id !== $initialSessionId) {
            return $session;
        }

        $initialOrderPaid = Order::query()
            ->where('studio_subscription_id', $subscription->id)
            ->where('billing_reason', 'subscription_initial')
            ->where('status', 'paid')
            ->exists();

        if (! $initialOrderPaid) {
            return $session;
        }

        return ClassSession::query()
            ->where('class_id', $subscription->class_id)
            ->where('status', '!=', 'cancelled')
            ->where('start_time', '>', $session->start_time)
            ->orderBy('start_time')
            ->first();
    }

    private function productionHitPayCycle(string $interval): array
    {
        return match (strtolower($interval)) {
            'week', 'weekly' => ['weekly', null, null],
            'month', 'monthly' => ['monthly', null, null],
            'year', 'yearly', 'annual', 'annually' => ['yearly', null, null],
            'day', 'daily' => ['custom', 1, 'day'],
            default => ['monthly', null, null],
        };
    }
}
