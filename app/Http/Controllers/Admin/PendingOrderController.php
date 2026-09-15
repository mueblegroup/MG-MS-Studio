<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
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
                ->where('status', 'pending')
                ->update([
                    'status' => 'cancelled',
                ]);
        });

        return back()->with('success', "Order #{$order->id} has been cancelled.");
    }
}
