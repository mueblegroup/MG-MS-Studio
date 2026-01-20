<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $provider = (string) $request->query('provider', '');

        $payments = Payment::query()
            ->with([
                'user',
                'order.items.purchasable', // ✅ purchased items + their model
            ])
            ->when($status !== '', fn($qq) => $qq->where('status', $status))
            ->when($provider !== '', fn($qq) => $qq->where('provider', $provider))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhere('provider_reference', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($u) use ($q) {
                            $u->where('name', 'like', "%{$q}%")
                              ->orWhere('email', 'like', "%{$q}%");
                        })
                        ->orWhereHas('order.items', function ($oi) use ($q) {
                            // optional: search item label stored in meta
                            $oi->where('meta', 'like', "%{$q}%")
                               ->orWhere('purchasable_type', 'like', "%{$q}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.payments.index', compact('payments', 'q', 'status', 'provider'));
    }

    public function show(int $id)
    {
        $payment = Payment::query()
            ->with([
                'user',
                'order.items.purchasable', // ✅ full purchased item detail page
            ])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }
}
