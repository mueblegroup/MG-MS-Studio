<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use App\Services\HitPayRecurringSubscriptionClassService;
use App\Services\HitPayService;
use App\Services\RecurringHitPayService;
use App\Services\SubscriptionClassService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RecurringHitPayCheckoutController extends CheckoutController
{
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
        $initialStart = $subscription->classModel?->sessions?->firstWhere('id', $initialSessionId)?->start_time;

        $sessions = $subscription->classModel?->sessions
            ?->filter(fn ($session) => $session->status !== 'cancelled')
            ->filter(fn ($session) => ! $initialStart || $session->start_time?->gte($initialStart))
            ->sortBy('start_time')
            ->values() ?? collect();

        $timesToCharge = max(1, $sessions->count());
        if ($timesToCharge > 100) {
            throw new RuntimeException('HitPay recurring billing supports a maximum of 100 charges per subscription. Split this class into a shorter subscription period.');
        }

        [$cycle, $cycleRepeat, $cycleFrequency] = $this->hitPayCycle($subscription->billing_interval ?: 'month');
        $reference = 'SUB:'.$subscription->id.':ORDER:'.$order->id;

        $payload = [
            'plan_id' => null,
            'customer_email' => (string) $subscription->user?->email,
            'customer_name' => (string) $subscription->user?->name,
            'name' => (string) ($subscription->classModel?->name ?? 'Subscription Class'),
            'description' => 'Recurring class subscription',
            'amount' => (float) $order->total,
            'currency' => strtoupper((string) ($order->currency ?? 'MYR')),
            'cycle' => $cycle,
            'start_date' => now('Asia/Singapore')->toDateString(),
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
            'meta' => array_merge((array) $subscription->meta, [
                'hitpay_reference' => $reference,
                'hitpay_recurring_url' => $checkoutUrl,
                'hitpay_times_to_be_charged' => $timesToCharge,
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
            ]),
        ]);

        return redirect()->away($checkoutUrl);
    }

    public function hitpayWebhook(Request $request, HitPayService $hitpay, SubscriptionClassService $subscriptions)
    {
        $signature = $request->header('Hitpay-Signature');
        $rawPayload = $request->getContent();
        $isEventWebhook = $signature && str_contains(strtolower((string) $request->header('Content-Type')), 'application/json');

        if (! $isEventWebhook) {
            return parent::hitpayWebhook($request, $hitpay, $subscriptions);
        }

        if (! $hitpay instanceof RecurringHitPayService || ! $hitpay->validateEventWebhook($rawPayload, $signature)) {
            return response()->json(['error' => 'Invalid HitPay signature'], 400);
        }

        $data = json_decode($rawPayload, true);
        if (! is_array($data)) {
            return response()->json(['error' => 'Invalid JSON payload'], 400);
        }

        if (! $subscriptions instanceof HitPayRecurringSubscriptionClassService) {
            return response()->json(['error' => 'Recurring subscription service unavailable'], 500);
        }

        $event = strtolower((string) (
            $request->header('Hitpay-Event-Type')
            ?: $request->header('Hitpay-Event-Object')
            ?: $data['event_type']
            ?? $data['event']
            ?? $data['type']
            ?? ''
        ));

        $subscription = $this->resolveHitPaySubscription($data);
        if (! $subscription) {
            Log::warning('HitPay recurring webhook could not be matched to a studio subscription.', [
                'event' => $event,
                'id' => $data['id'] ?? null,
                'reference' => $data['reference'] ?? null,
            ]);

            return response()->json(['ok' => true, 'matched' => false]);
        }

        if (str_contains($event, 'method_attached')) {
            $subscriptions->activateFromHitPayRecurringBilling($subscription, $data);
            return response()->json(['ok' => true]);
        }

        if (str_contains($event, 'subscription_updated')) {
            $subscriptions->syncHitPayRecurringBilling($subscription, $data);
            return response()->json(['ok' => true]);
        }

        $status = strtolower((string) ($data['status'] ?? ''));
        $looksLikeCharge = str_contains($event, 'charge') || (($data['channel'] ?? null) === 'recurrent');

        if ($looksLikeCharge && in_array($status, ['succeeded', 'completed', 'paid', 'success'], true)) {
            $subscriptions->handleHitPayRecurringCharge($subscription, $data);
        } elseif ($looksLikeCharge && in_array($status, ['failed', 'declined', 'error'], true)) {
            $subscriptions->handleHitPayRecurringFailure($subscription, $data);
        }

        return response()->json(['ok' => true]);
    }

    private function resolveHitPaySubscription(array $data): ?StudioSubscription
    {
        $recurringId = (string) ($data['recurring_billing_id']
            ?? $data['recurring_billing']['id']
            ?? $data['billing_session_id']
            ?? '');

        if ($recurringId !== '') {
            $subscription = StudioSubscription::query()
                ->where('provider', 'hitpay')
                ->where('provider_subscription_id', $recurringId)
                ->first();

            if ($subscription) {
                return $subscription;
            }
        }

        $reference = (string) ($data['reference'] ?? $data['reference_number'] ?? '');
        if (preg_match('/SUB:(\d+):ORDER:(\d+)/', $reference, $matches)) {
            return StudioSubscription::query()->whereKey((int) $matches[1])->where('provider', 'hitpay')->first();
        }

        $payloadId = (string) ($data['id'] ?? '');
        if ($payloadId !== '') {
            $subscription = StudioSubscription::query()
                ->where('provider', 'hitpay')
                ->where('provider_subscription_id', $payloadId)
                ->first();

            if ($subscription) {
                return $subscription;
            }
        }

        $email = strtolower(trim((string) ($data['customer']['email'] ?? $data['customer_email'] ?? '')));
        if ($email === '') {
            return null;
        }

        $matches = StudioSubscription::query()
            ->where('provider', 'hitpay')
            ->whereNotNull('provider_subscription_id')
            ->whereIn('status', ['pending', 'active', 'past_due'])
            ->whereHas('user', fn ($query) => $query->whereRaw('LOWER(email) = ?', [$email]))
            ->get();

        if ($matches->count() === 1) {
            return $matches->first();
        }

        $amount = isset($data['amount']) ? (float) $data['amount'] : null;
        if ($amount !== null) {
            $amountMatches = $matches->filter(fn ($subscription) => abs((float) $subscription->amount - $amount) < 0.001);
            if ($amountMatches->count() === 1) {
                return $amountMatches->first();
            }
        }

        return null;
    }

    private function hitPayCycle(string $interval): array
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
