<?php

namespace App\Http\Middleware;

use App\Jobs\FulfillOrderJob;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ReconcileStripeCheckoutReturn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->routeIs('shop.checkout.success') || ! $request->user()) {
            return $next($request);
        }

        $orderId = (int) $request->query('order');
        if ($orderId <= 0) {
            return $next($request);
        }

        $order = Order::query()
            ->whereKey($orderId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $order || $order->status === 'paid' || $order->payment_provider !== 'stripe' || ! $order->provider_reference) {
            return $next($request);
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            $session = \Stripe\Checkout\Session::retrieve([
                'id' => $order->provider_reference,
                'expand' => ['subscription'],
            ]);

            $metadataOrderId = isset($session->metadata->order_id)
                ? (int) $session->metadata->order_id
                : null;

            $paymentCompleted = in_array((string) ($session->payment_status ?? ''), ['paid', 'no_payment_required'], true)
                && (string) ($session->status ?? '') === 'complete';

            if ($metadataOrderId !== $order->id || ! $paymentCompleted) {
                return $next($request);
            }

            $didMarkPaid = false;

            DB::transaction(function () use ($order, $session, &$didMarkPaid): void {
                $lockedOrder = Order::query()->lockForUpdate()->find($order->id);
                if (! $lockedOrder || $lockedOrder->status === 'paid') {
                    return;
                }

                $lockedOrder->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Payment::query()
                    ->where('order_id', $lockedOrder->id)
                    ->whereIn('status', ['pending', 'past_due'])
                    ->latest('id')
                    ->limit(1)
                    ->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'provider' => 'stripe',
                        'method' => 'stripe',
                        'provider_reference' => $session->id,
                        'payload' => method_exists($session, 'toArray') ? $session->toArray() : (array) $session,
                    ]);

                $subscriptionRecordId = isset($session->metadata->studio_subscription_id)
                    ? (int) $session->metadata->studio_subscription_id
                    : null;

                if ($subscriptionRecordId) {
                    $stripeSubscription = $session->subscription;
                    StudioSubscription::query()->whereKey($subscriptionRecordId)->update([
                        'status' => 'active',
                        'provider_subscription_id' => is_object($stripeSubscription) ? $stripeSubscription->id : $stripeSubscription,
                        'provider_customer_id' => $session->customer ?? null,
                        'started_at' => now(),
                    ]);
                }

                $didMarkPaid = true;
            });

            if ($didMarkPaid) {
                FulfillOrderJob::dispatch($order->id);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $next($request);
    }
}
