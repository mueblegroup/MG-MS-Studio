<?php

namespace App\Http\Controllers;

use App\Jobs\FulfillOrderJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\CartService;
use App\Services\StudioSettingsService;
use App\Services\HitPayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index(CartService $cart, StudioSettingsService $settings)
    {
        $cartModel = $cart->currentCart()->load('items.purchasable');

        if ($cartModel->items->isEmpty()) {
            return redirect()->route('shop.cart.index')->with('error', 'Your cart is empty.');
        }
        $currency = strtoupper($settings->get('currency', 'MYR'));

        $summary = [
            'currency' => $currency,
            'subtotal' => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
            'total'    => (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price),
        ];

        return view('shop.checkout', compact('cartModel', 'summary'));
    }

    public function pay(Request $request, CartService $cart, StudioSettingsService $settings, HitPayService $hitpay)
    {
        $validated = $request->validate([
            'provider' => 'required|in:stripe,hitpay',
        ]);

        $cartModel = $cart->currentCart()->load('items.purchasable');

        if ($cartModel->items->isEmpty()) {
            return redirect()->route('shop.cart.index')->with('error', 'Your cart is empty.');
        }

        [$order, $payment] = DB::transaction(function () use ($cartModel, $validated, $settings) {
            $currency = strtoupper($settings->get('currency', 'MYR'));
            $subtotal = (float) $cartModel->items->sum(fn ($i) => $i->quantity * $i->unit_price);

            $order = Order::create([
                'user_id'          => auth()->id(),
                'currency'         => $currency,
                'subtotal'         => $subtotal,
                'total'            => $subtotal,
                'status'           => 'pending',
                'payment_provider' => $validated['provider'],
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
                'reference' => 'ORD-' . $order->id . '-' . Str::upper(Str::random(6)),
                'status'    => 'pending',
            ]);

            return [$order, $payment];
        });

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

        // store provider reference on BOTH order + payment
        $order->update(['provider_reference' => $session->id]);
        $payment->update(['provider_reference' => $session->id]);

        return redirect()->away($session->url);
    }

    protected function payWithHitpay(Order $order, Payment $payment, HitPayService $hitpay)
    {
        $amount = number_format((float) $order->total, 2, '.', '');
        $currency = strtoupper($order->currency ?? 'MYR');

        $resp = $hitpay->createPaymentRequest([
            'amount'            => $amount,
            'currency'          => $currency,
            'purpose'           => 'Order #' . $order->id,
            'reference_number'  => (string) $order->id, // easiest mapping
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

        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($order->status === 'paid') {
            $cart->clear();
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

    public function stripeWebhook(Request $request)
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

                if ($didMarkPaid) {
                    FulfillOrderJob::dispatch($orderId);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    public function hitpayWebhook(Request $request, HitPayService $hitpay)
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

            if ($didMarkPaid) {
                FulfillOrderJob::dispatch($orderId);
            }
        } elseif (in_array($status, ['failed', 'cancelled'], true)) {
            $this->markOrderCancelled($orderId);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Convert payload into something that is ALWAYS valid JSON for DB.
     */
    protected function normalizePayloadForDb(mixed $payload): array
    {
        // Stripe objects support ->toArray()
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

    /**
     * Marks order + payment as paid in an idempotent way.
     * Returns true only if it actually transitioned pending -> paid.
     */
    protected function markOrderPaid(int $orderId, string $provider, ?string $providerReference, mixed $payload): bool
    {
        $didMarkPaid = false;

        DB::transaction(function () use ($orderId, $provider, $providerReference, $payload, &$didMarkPaid) {
            $order = Order::lockForUpdate()->find($orderId);
            if (!$order) return;

            // already paid => idempotent
            if ($order->status === 'paid') {
                return;
            }

            $order->update([
                'status'  => 'paid',
                'paid_at' => now(),
            ]);

            $safePayload = $this->normalizePayloadForDb($payload);

            // Prefer exact match by provider + provider_reference
            $paymentQuery = Payment::query()
                ->where('provider', $provider);

            if ($providerReference) {
                $updated = (clone $paymentQuery)
                    ->where('provider_reference', $providerReference)
                    ->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                        'payload' => $safePayload,
                    ]);

                // fallback: latest pending payment for this order
                if ($updated === 0) {
                    Payment::where('order_id', $orderId)
                        ->where('status', 'pending')
                        ->orderByDesc('id')
                        ->limit(1)
                        ->update([
                            'status'             => 'paid',
                            'paid_at'            => now(),
                            'payload'            => $safePayload,
                            'provider'           => $provider,
                            'provider_reference' => $providerReference,
                        ]);
                }
            } else {
                Payment::where('order_id', $orderId)
                    ->where('status', 'pending')
                    ->orderByDesc('id')
                    ->limit(1)
                    ->update([
                        'status'  => 'paid',
                        'paid_at' => now(),
                        'payload' => $safePayload,
                        'provider' => $provider,
                    ]);
            }

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
}
