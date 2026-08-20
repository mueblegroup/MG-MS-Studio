<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use App\Services\HitPayService;
use App\Services\RecurringHitPayService;
use App\Services\StudioSettingsService;
use App\Services\SubscriptionClassService;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;

class ProductionRecurringHitPayCheckoutController extends RecurringHitPayCheckoutController
{
    public function retrySubscriptionStart(
        Request $request,
        StudioSubscription $subscription,
        StudioSettingsService $settings,
        HitPayService $hitpay,
        SubscriptionClassService $subscriptions
    ) {
        $studioId = app(TenantManager::class)->id();

        abort_unless(
            $studioId
            && (int) $subscription->studio_id === (int) $studioId
            && (int) $subscription->user_id === (int) auth()->id(),
            404
        );

        if (! in_array(strtolower((string) $subscription->status), ['pending', 'past_due'], true)) {
            return redirect()->route('student.subscriptions.index')
                ->with('error', 'This subscription no longer needs an initial payment retry.');
        }

        $order = Order::query()
            ->whereKey($subscription->initial_order_id)
            ->where('studio_subscription_id', $subscription->id)
            ->where('billing_reason', 'subscription_initial')
            ->where('user_id', auth()->id())
            ->where('studio_id', $studioId)
            ->first();

        if (! $order || $order->status === 'paid') {
            return redirect()->route('student.subscriptions.index')
                ->with('error', 'The original subscription payment is not retryable.');
        }

        $payment = Payment::query()
            ->where('order_id', $order->id)
            ->where('user_id', auth()->id())
            ->latest('id')
            ->first();

        if (! $payment || $payment->status === 'paid') {
            return redirect()->route('student.subscriptions.index')
                ->with('error', 'The original subscription payment is not retryable.');
        }

        $provider = strtolower((string) $settings->get('default_payment_provider', 'stripe'));
        if (! in_array($provider, ['stripe', 'hitpay'], true)) {
            return redirect()->route('student.subscriptions.index')
                ->with('error', 'The studio has no valid payment gateway selected.');
        }

        // If HitPay already created a recurring checkout and the customer only
        // abandoned the external page, reuse it instead of creating another
        // recurring agreement.
        $existingCheckoutUrl = (string) (($payment->payload['checkout_url'] ?? '') ?: '');
        if ($provider === 'hitpay'
            && ($payment->provider ?: $payment->method) === 'hitpay'
            && $existingCheckoutUrl !== ''
            && $subscription->provider_subscription_id) {
            return redirect()->away($existingCheckoutUrl);
        }

        $order->update([
            'status' => 'pending',
            'payment_provider' => $provider,
            'provider_reference' => null,
        ]);

        $payment->update([
            'status' => 'pending',
            'provider' => $provider,
            'method' => $provider,
            'provider_reference' => null,
            'payload' => array_merge((array) $payment->payload, [
                'retried_at' => now()->toIso8601String(),
                'retry_provider' => $provider,
            ]),
        ]);

        $subscription->updateQuietly([
            'provider' => $provider,
            'provider_subscription_id' => null,
            'provider_customer_id' => null,
            'status' => 'pending',
            'cancelled_at' => null,
        ]);

        if ($provider === 'stripe') {
            return $this->paySubscriptionWithStripe(
                $order->fresh('items'),
                $payment->fresh(),
                $subscription->fresh(),
                $subscriptions
            );
        }

        return $this->payWithHitpay($order->fresh(), $payment->fresh(), $hitpay);
    }

    protected function payWithHitpay(Order $order, Payment $payment, HitPayService $hitpay)
    {
        if ($order->billing_reason !== 'subscription_initial' || ! $order->studio_subscription_id) {
            return parent::payWithHitpay($order, $payment, $hitpay);
        }

        if (! $hitpay instanceof RecurringHitPayService) {
            throw new RuntimeException('HitPay recurring billing service is not available.');
        }

        $subscription = StudioSubscription::query()
            ->with(['user:id,name,email', 'classModel.sessions'])
            ->findOrFail($order->studio_subscription_id);

        $initialSessionId = (int) ($subscription->meta['initial_class_session_id'] ?? $subscription->current_class_session_id);
        $initialSession = $subscription->classModel?->sessions?->firstWhere('id', $initialSessionId);
        $initialStart = $initialSession?->start_time;

        $sessions = $subscription->classModel?->sessions
            ?->filter(fn ($session) => $session->status !== 'cancelled')
            ->filter(fn ($session) => ! $initialStart || $session->start_time?->gte($initialStart))
            ->sortBy('start_time')
            ->values() ?? collect();

        if ($sessions->isEmpty()) {
            throw new RuntimeException('This subscription class has no billable sessions.');
        }

        $timesToCharge = $sessions->count();
        if ($timesToCharge > 100) {
            throw new RuntimeException('HitPay recurring billing supports a maximum of 100 charges per subscription. Split this class into a shorter subscription period.');
        }

        [$cycle, $cycleRepeat, $cycleFrequency] = $this->productionHitPayCycle($subscription->billing_interval ?: 'month');
        $reference = 'SUB:'.$subscription->id.':ORDER:'.$order->id;

        $singaporeToday = Carbon::now('Asia/Singapore')->startOfDay();
        $targetChargeAt = Carbon::parse($sessions->first()->start_time)
            ->timezone('Asia/Singapore')
            ->subDay();
        $hitPayStartDate = $targetChargeAt->copy()->startOfDay()->lt($singaporeToday)
            ? $singaporeToday->toDateString()
            : $targetChargeAt->toDateString();

        $finalSession = $sessions->last();
        $finalEndsAt = Carbon::parse($finalSession->end_time ?: $finalSession->start_time);

        $payload = [
            'plan_id' => null,
            'customer_email' => (string) $subscription->user?->email,
            'customer_name' => (string) $subscription->user?->name,
            'name' => (string) ($subscription->classModel?->name ?? 'Subscription Class'),
            'description' => 'Recurring class subscription',
            'amount' => (float) $order->total,
            'currency' => strtoupper((string) ($order->currency ?? 'MYR')),
            'cycle' => $cycle,
            'start_date' => $hitPayStartDate,
            'redirect_url' => route('shop.checkout.success', [], true).'?order='.$order->id,
            'reference' => $reference,
            'payment_methods' => ['card'],
            'send_email' => 'true',
            'times_to_be_charged' => $timesToCharge,
            'save_card' => 'false',
            'save_payment_method' => 'false',
        ];

        if ($cycle === 'custom') {
            $payload['cycle_repeat'] = $cycleRepeat;
            $payload['cycle_frequency'] = $cycleFrequency;
        }

        $billing = $hitpay->createRecurringBilling($payload);
        $recurringId = (string) ($billing['id'] ?? '');
        $checkoutUrl = (string) ($billing['url'] ?? '');

        if ($recurringId === '' || $checkoutUrl === '') {
            throw new RuntimeException('HitPay recurring billing response is missing id/url: '.json_encode($billing));
        }

        $subscription->updateQuietly([
            'provider' => 'hitpay',
            'provider_subscription_id' => $recurringId,
            'status' => 'pending',
            'current_period_end' => $finalEndsAt,
            'meta' => array_merge((array) $subscription->meta, [
                'hitpay_reference' => $reference,
                'hitpay_recurring_url' => $checkoutUrl,
                'hitpay_times_to_be_charged' => $timesToCharge,
                'hitpay_start_date_sgt' => $hitPayStartDate,
                'target_first_charge_at' => $targetChargeAt->toIso8601String(),
                'final_class_session_id' => $finalSession->id,
                'final_class_session_ends_at' => $finalEndsAt->toIso8601String(),
                'hitpay_timing_precision' => 'date_only_sgt',
            ]),
        ]);

        $order->update(['provider_reference' => $recurringId]);
        $payment->update([
            'provider_reference' => $recurringId,
            'provider' => 'hitpay',
            'method' => 'hitpay',
            'status' => 'pending',
            'payload' => array_merge((array) $payment->payload, $billing, [
                'checkout_url' => $checkoutUrl,
                'hitpay_recurring_billing' => true,
                'hitpay_start_date_sgt' => $hitPayStartDate,
                'target_first_charge_at' => $targetChargeAt->toIso8601String(),
                'final_class_session_ends_at' => $finalEndsAt->toIso8601String(),
            ]),
        ]);

        return redirect()->away($checkoutUrl);
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
