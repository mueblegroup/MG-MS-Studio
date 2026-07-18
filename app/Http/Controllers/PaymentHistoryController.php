<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\StudioSubscription;
use Barryvdh\DomPDF\Facade\Pdf;
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
                'order.items.purchasable',
            ])
            ->when($status !== '', fn ($qq) => $qq->where('status', $status))
            ->when($provider !== '', fn ($qq) => $qq->where('provider', $provider))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($sub) use ($q) {
                    $sub->where('reference', 'like', "%{$q}%")
                        ->orWhere('provider_reference', 'like', "%{$q}%")
                        ->orWhereHas('user', function ($u) use ($q) {
                            $u->where('name', 'like', "%{$q}%")
                              ->orWhere('email', 'like', "%{$q}%");
                        })
                        ->orWhereHas('order.items', function ($oi) use ($q) {
                            $oi->where('meta', 'like', "%{$q}%")
                               ->orWhere('purchasable_type', 'like', "%{$q}%");
                        });
                });
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $upcomingSubscriptions = StudioSubscription::query()
            ->with(['user', 'classModel'])
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->whereNotNull('next_billing_at')
            ->whereBetween('next_billing_at', [now()->startOfMinute(), now()->addDays(3)->endOfDay()])
            ->orderBy('next_billing_at')
            ->get();

        return view('admin.payments.index', compact(
            'payments',
            'upcomingSubscriptions',
            'q',
            'status',
            'provider'
        ));
    }

    public function show(int $id)
    {
        $payment = Payment::query()
            ->with([
                'user',
                'order.items.purchasable',
            ])
            ->findOrFail($id);

        return view('admin.payments.show', compact('payment'));
    }

    public function downloadReceipt(int $id)
    {
        $payment = Payment::query()
            ->with([
                'user',
                'order.items.purchasable',
            ])
            ->findOrFail($id);

        $payload = $this->normalizePayload($payment->payload ?? null);

        $invoiceNumber = $this->makeInvoiceNumber($payment);

        $issuedAt = $payment->paid_at ?? $payment->created_at;

        $subtotal = (float) ($payment->order->items->sum(function ($item) {
            $qty = (int) ($item->quantity ?? 1);
            $price = (float) ($item->unit_price ?? $item->price ?? 0);
            return $qty * $price;
        }) ?? 0);

        $total = (float) ($payment->amount ?? $subtotal);
        $discount = max(0, $subtotal - $total);

        $data = [
            'payment' => $payment,
            'payload' => $payload,
            'invoiceNumber' => $invoiceNumber,
            'issuedAt' => $issuedAt,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => $total,
            'company' => [
                'name' => 'Mueble Solution',
                'email' => config('mail.from.address'),
                'phone' => null,
                'address' => null,
            ],
        ];

        $pdf = Pdf::loadView('admin.payments.receipt-pdf', $data)
            ->setPaper('a4', 'portrait');

        $filename = 'receipt-' . ($payment->reference ?: $payment->id) . '.pdf';

        return $pdf->download($filename);
    }

    protected function makeInvoiceNumber(Payment $payment): string
    {
        $date = ($payment->paid_at ?? $payment->created_at)?->format('Ymd') ?? now()->format('Ymd');
        return 'INV-' . $date . '-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
    }

    protected function normalizePayload($payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (is_string($payload) && $payload !== '') {
            $decoded = json_decode($payload, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
