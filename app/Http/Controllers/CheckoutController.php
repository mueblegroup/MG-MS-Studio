<?php

namespace App\Http\Controllers;

use App\Jobs\FulfillOrderJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\CartService;
use App\Services\StudioSettingsService;
use App\Services\HitPayService;
use App\Services\SubscriptionClassService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(CartService $cart, StudioSettingsService $settings, SubscriptionClassService $subscriptions)
    {
        $cartModel = $cart->currentCart()->load('items.purchasable');

        if ($cartModel->items->isEmpty()) {
            return redirect()->route('shop.cart.index')->with('error', 'Your cart is empty.');
        }

        if ($message = $subscriptions->validateSubscriptionCart($cartModel)) {
            return redirect()->route('shop.cart.index')->with('error', $message);
        }

        $currency = strtoupper($settings->get('currency', 'MYR'));
        $enabledProviders = (array) $settings->get('default_payment_provider', ['stripe']);
        $defaultProvider = (string) $settings->get('default_payment_provider', $enabledProviders[0] ?? 'stripe');
        $hasSubscriptionClass = $subscriptions->cartHasSubscriptionClass($cartModel);

        $summary = [
            'currency' => $currency,
            'subtotal' => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            'total'    => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            'is_subscription' => $hasSubscriptionClass,
        ];

        return view('shop.checkout', compact('cartModel', 'summary', 'enabledProviders', 'defaultProvider', 'hasSubscriptionClass'));
    }

    public function pay(Request $request, CartService $cart, StudioSettingsService $settings, HitPayService $hitpay, SubscriptionClassService $subscriptions)
    {
        $enabledProviders = (array) $settings->get('default_payment_provider', ['stripe']);
        $validated = $request->validate([
            'provider' => 'required|string|in:' . implode(',', $enabledProviders),
        ]);

        $cartModel = $cart->currentCart()->load('items.purchasable');

        if ($cartModel->items->isEmpty()) {
            return redirect()->route('shop.cart.index')->with('error', 'Your cart is empty.');
        }

        if ($message = $subscriptions->validateSubscriptionCart($cartModel)) {
            return redirect()->route('shop.cart.index')->with('error', $message);
        }

        $hasSubscriptionClass = $subscriptions->cartHasSubscriptionClass($cartModel);

        [$order, $payment] = DB::transaction(function () use ($cartModel, $validated, $settings, $hasSubscriptionClass) {
            $currency = strtoupper($settings->get('currency', 'MYR'));
            $subtotal = (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price);

            $order = Order::create([
                'user_id'          => auth()->id(),
                'currency'         => $currency,
                'subtotal'         => $subtotal,
                'total'            => $subtotal,
                'status'           => 'pending',
                'payment_provider' => $validated['provider'],
                'billing_reason'   => $hasSubscriptionClass ? 'subscription_initial' : null,
            ]);

            foreach ($cartModel->items as $item) {
                OrderItem::create([
                    'order_id'         => $order->id,
                    'purchasable_type' => $item->purchasable_type,
                    'purchasable_id'   => $item->purchasable_id,
                    'quantity'         => $item->quantity,
                    'unit_price'       => $item->unit_price,
                    'currency'         => $currency,
                    'meta'             => $item->meta,
                ]);
            }

            $payment = Payment::create([
                'user_id'   => auth()->id(),
                'order_id'  => $order->id,
                'amount'    => $order->total,
                'currency'  => $order->currency,
                'method'    => $validated['provider'],
                'provider'  => $validated['provider'],
                'reference' => ($hasSubscriptionClass ? 'SUB-' : 'ORD-') . $order->id . '-' . Str::upper(Str::random(6)),
                'status'    => 'pending',
            ]);

            return [$order, $payment];
        });

        if ($hasSubscriptionClass) {
            $subscription = $subscriptions->createPendingSubscriptionFromOrder($order);

            return $validated['provider'] === 'stripe'
                ? $this->paySubscriptionWithStripe($order->fresh('items'), $payment->fresh(), $subscription, $subscriptions)
                : $this->payWithHitpay($order->fresh(), $payment->fresh(), $hitpay);
        }

        return $validated['provider'] === 'stripe'
            ? $this->payWithStripe($order, $payment)
            : $this->payWithHitpay($order, $payment, $hitpay);
    }

    protected function payWithStripe(Order $order, Payment $payment)
    {
        $items = $order->items()->get();

        $lineItems = $items->map(function ($i) {
            $label = $i->meta['label'] ?? class_basename($i->purchasable_type);

            return [
                'price_data' => [
                    'currency'     => strtolower($i->currency ?? 'myr'),
                    'unit_amount'  => (int) round(((float) $i->unit_price) * 100),
                    'product_data' => ['name' => $label],
                ],
                'quantity' => (int) $i->quantity,
            ];
        })->values()->all();

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = \Stripe\Checkout\Session::create([
            'mode'       => 'payment',
            'line_items' => $lineItems,
            'success_url' => route('shop.checkout.success', [], true) . '?order=' . $order->id,
            'cancel_url'  => route('shop.checkout.cancel', [], true) . '?order=' . $order->id,
            'metadata'    => [
                'order_id' => (string) $order->id,
            ],
        ]);

        $order->update(['provider_reference' => $session->id]);
        $payment->update(['provider_reference' => $session->id]);

        return redirect()->away($session->url);
    }

    protected function paySubscriptionWithStripe(Order $order, Payment $payment, $subscription, SubscriptionClassService $subscriptions)
    {
        if (!$subscription) {
            throw new \RuntimeException('Unable to create subscription record for this class.');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $session = $subscriptions->createStripeCheckoutSession($order, $payment, $subscription);

        $order->update(['provider_reference' => $session->id]);
        $payment->update(['provider_reference' => $session->id]);

        return redirect()->away($session->url);
    }

    protected function payWithHitpay(Order $order, Payment $payment, HitPayService $hitpay)
    {
        $amount = number_format((float) $order->total, 2, '.', '');
        $currency = strtoupper($order->currency ?? 'MYR');

        $purpose = $order->billing_reason === 'subscription_initial'
            ? 'Subscription class Order #' . $order->id
            : 'Order #' . $order->id;

        $resp = $hitpay->createPaymentRequest([
            'amount'            => $amount,
            'currency'          => $currency,
            'purpose'           => $purpose,
            'reference_number'  => (string) $order->id,
            'redirect_url'      => route('shop.checkout.success', [], true) . '?order=' . $order->id,
            'webhook'           => route('webhooks.hitpay', [], true),
        ]);

        $paymentRequestId = $resp['id'] ?? null;
        $checkoutUrl = $resp['url'] ?? null;

        if (!$paymentRequestId || !$checkoutUrl) {
            throw new \RuntimeException('HitPay response missing id/url: ' . json_encode($resp));
        }

        $order->update(['provider_reference' => $paymentRequestId]);
        $payment->update(['provider_reference' => $paymentRequestId]);

        return redirect()->away($checkoutUrl);
    }

    public function success(Request $request, CartService $cart)
    {
        $orderId = (int) $request->query('order');

        $order = Order::with('items')
            ->where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status === 'paid') {
            $cart->clearPurchasedItems($order);
        }

        return view('shop.checkout-success', compact('order'));
    }

    public function cancel(Request $request)
    {
        $orderId = (int) $request->query('order');

        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', auth()->id())
                ->first();

            if ($order && $order->status !== 'paid') {
                $this->markOrderCancelled($orderId);
            }
        }

        return view('shop.checkout-cancel');
    }

    public function stripeWebhook(Request $request, SubscriptionClassService $subscriptions)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $orderId = isset($session->metadata->order_id) ? (int) $session->metadata->order_id : null;
            $providerRef = $session->id ?? null;

            if ($orderId) {
                $didMarkPaid = $this->markOrderPaid(
                    orderId: $orderId,
                    provider: 'stripe',
                    providerReference: $providerRef,
                    payload: $session
                );

                $subscriptions->activateFromStripeSession($session);

                if ($didMarkPaid) {
                    FulfillOrderJob::dispatch($orderId);
                }
            }
        }

        if ($event->type === 'invoice.payment_succeeded') {
            $subscriptions->handleStripeInvoicePayment($event->data->object);
        }

        if (in_array($event->type, ['customer.subscription.updated', 'customer.subscription.deleted'], true)) {
            $subscriptions->syncFromStripeSubscription($event->data->object);
        }

        return response()->json(['ok' => true]);
    }

    public function hitpayWebhook(Request $request, HitPayService $hitpay, SubscriptionClassService $subscriptions)
    {
        $data = $request->all();

        if (!$hitpay->validateWebhook($data)) {
            return response()->json(['error' => 'Invalid HMAC'], 400);
        }

        $status = (string) ($data['status'] ?? '');
        $reference = (string) ($data['reference_number'] ?? '');

        $orderId = ctype_digit($reference) ? (int) $reference : null;
        if (!$orderId) {
            return response()->json(['error' => 'Invalid reference_number'], 400);
        }

        $providerRef = (string) ($data['payment_request_id'] ?? $data['id'] ?? '');

        if ($status === 'completed') {
            $didMarkPaid = $this->markOrderPaid(
                orderId: $orderId,
                provider: 'hitpay',
                providerReference: $providerRef ?: null,
                payload: $data
            );

            $this->activateHitpaySubscriptionIfNeeded($orderId, $subscriptions);

            if ($didMarkPaid) {
                FulfillOrderJob::dispatch($orderId);
            }
        } elseif (in_array($status, ['failed', 'cancelled'], true)) {
            $this->markOrderCancelled($orderId);
        }

        return response()->json(['ok' => true]);
    }

    protected function normalizePayloadForDb(mixed $payload): array
    {
        if (is_object($payload) && method_exists($payload, 'toArray')) {
            return (array) $payload->toArray();
        }

        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload)) {
            $decoded = json_decode($payload, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
            return ['raw' => $payload];
        }

        return ['raw' => (string) $payload];
    }

    protected function markOrderPaid(int $orderId, string $provider, ?string $providerReference, mixed $payload): bool
    {
        $didMarkPaid = false;

        DB::transaction(function () use ($orderId, $provider, $providerReference, $payload, &$didMarkPaid) {
            $order = Order::lockForUpdate()->find($orderId);
            if (!$order) return;

            if ($order->status === 'paid') {
                return;
            }

            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $safePayload = $this->normalizePayloadForDb($payload);

            $paymentUpdate = [
                'status'  => 'paid',
                'paid_at' => now(),
                'payload' => $safePayload,
                'provider' => $provider,
            ];

            if ($providerReference) {
                $paymentUpdate['provider_reference'] = $providerReference;
            }

            Payment::where('order_id', $orderId)
                ->where('status', 'pending')
                ->orderByDesc('id')
                ->limit(1)
                ->update($paymentUpdate);

            $didMarkPaid = true;
        });

        return $didMarkPaid;
    }

    protected function markOrderCancelled(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::lockForUpdate()->find($orderId);
            if (!$order) return;

            if ($order->status === 'paid') {
                return;
            }

            $order->update(['status' => 'cancelled']);

            Payment::where('order_id', $orderId)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);
        });
    }

    protected function activateHitpaySubscriptionIfNeeded(int $orderId, SubscriptionClassService $subscriptions): void
    {
        $order = Order::with('studioSubscription')->find($orderId);
        if (!$order || !$order->studioSubscription) {
            return;
        }

        $subscription = $order->studioSubscription;
        $interval = $subscription->billing_interval ?: 'month';
        $nextBillingAt = $subscriptions->nextBillingAt($interval);

        $subscription->update([
            'status' => 'active',
            'started_at' => $subscription->started_at ?: now(),
            'current_period_start' => now(),
            'current_period_end' => $nextBillingAt,
            'next_billing_at' => $nextBillingAt,
        ]);
    }
}
