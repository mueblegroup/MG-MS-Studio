<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class SafeCancelledOrderCheckoutController extends ProductionRecurringHitPayCheckoutController
{
    protected function markOrderPaid(int $orderId, string $provider, ?string $providerReference, mixed $payload): bool
    {
        $didMarkPaid = false;

        DB::transaction(function () use ($orderId, $provider, $providerReference, $payload, &$didMarkPaid): void {
            $order = Order::lockForUpdate()->find($orderId);
            if (! $order) {
                return;
            }

            // An admin cancellation is final for this checkout. Ignore late provider
            // callbacks instead of resurrecting the order and fulfilling it.
            if (in_array($order->status, ['paid', 'cancelled'], true)) {
                return;
            }

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $paymentUpdate = [
                'status' => 'paid',
                'paid_at' => now(),
                'payload' => $this->normalizePayloadForDb($payload),
                'provider' => $provider,
                'method' => $provider,
            ];

            if ($providerReference) {
                $paymentUpdate['provider_reference'] = $providerReference;
            }

            Payment::query()
                ->where('order_id', $orderId)
                ->whereIn('status', ['pending', 'past_due'])
                ->latest('id')
                ->limit(1)
                ->update($paymentUpdate);

            $didMarkPaid = true;
        });

        return $didMarkPaid;
    }
}
