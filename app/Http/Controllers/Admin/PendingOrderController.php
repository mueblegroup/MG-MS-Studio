<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PendingOrderController extends Controller
{
    public function index(): View
    {
        $orders = Order::query()
            ->with(['user', 'items.purchasable'])
            ->where('status', 'pending')
            ->latest('id')
            ->paginate(20);

        return view('admin.orders.pending', compact('orders'));
    }

    public function cancel(Order $order): RedirectResponse
    {
        if ($order->status !== 'pending') {
            return back()->with('error', 'Only pending orders can be cancelled.');
        }

        DB::transaction(function () use ($order): void {
            // Updating through the model intentionally fires the existing Order
            // status notification so the student is informed of the cancellation.
            $order->update([
                'status' => 'cancelled',
            ]);

            Payment::query()
                ->where('order_id', $order->id)
                ->whereIn('status', ['pending', 'past_due'])
                ->update([
                    'status' => 'cancelled',
                ]);

            if ($order->billing_reason === 'subscription_initial' && $order->studio_subscription_id) {
                $subscription = StudioSubscription::query()
                    ->whereKey($order->studio_subscription_id)
                    ->whereIn('status', ['pending', 'past_due'])
                    ->first();

                if ($subscription) {
                    $subscription->updateQuietly([
                        'status' => 'cancelled',
                        'cancelled_at' => now(),
                        'next_billing_at' => null,
                        'meta' => array_merge((array) $subscription->meta, [
                            'admin_cancelled_order_id' => $order->id,
                            'admin_cancelled_at' => now()->toIso8601String(),
                        ]),
                    ]);
                }
            }
        });

        return back()->with('success', "Order #{$order->id} has been cancelled.");
    }
}
