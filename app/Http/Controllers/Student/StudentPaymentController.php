<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StudioSubscription;
use App\Services\ReliableSubscriptionClassService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPaymentController extends Controller
{
    public function index(Request $request, ReliableSubscriptionClassService $subscriptions)
    {
        $studentId = Auth::id();

        $payments = DB::table('payments')
            ->leftJoin('orders', 'orders.id', '=', 'payments.order_id')
            ->where('payments.user_id', $studentId)
            ->select([
                'payments.*',
                'orders.status as order_status',
                'orders.billing_reason',
            ])
            ->orderByDesc('payments.created_at')
            ->paginate(15);

        $activeSubscriptions = StudioSubscription::query()
            ->with('classModel')
            ->where('user_id', $studentId)
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->get()
            ->map(function (StudioSubscription $subscription) use ($subscriptions) {
                if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
                    return $subscription;
                }

                try {
                    return $subscriptions->refreshStripeBillingPeriod($subscription);
                } catch (\Throwable $exception) {
                    Log::warning('Unable to refresh Stripe class subscription period for student display.', [
                        'studio_subscription_id' => $subscription->id,
                        'stripe_subscription_id' => $subscription->provider_subscription_id,
                        'message' => $exception->getMessage(),
                    ]);

                    return $subscription;
                }
            })
            ->sortBy(fn (StudioSubscription $subscription) => $subscription->next_billing_at?->timestamp ?? PHP_INT_MAX)
            ->values();

        $upcomingSubscriptions = $activeSubscriptions
            ->filter(function (StudioSubscription $subscription): bool {
                if (! $subscription->next_billing_at) {
                    return false;
                }

                if (strtolower((string) $subscription->provider) === 'hitpay') {
                    $providerDate = $subscription->meta['hitpay_next_charge_date_sgt']
                        ?? $subscription->meta['hitpay_start_date_sgt']
                        ?? $subscription->next_billing_at->copy()->timezone('Asia/Singapore')->toDateString();

                    try {
                        $dueDate = Carbon::parse($providerDate, 'Asia/Singapore')->startOfDay();
                    } catch (\Throwable) {
                        return false;
                    }

                    $today = Carbon::now('Asia/Singapore')->startOfDay();

                    return $dueDate->betweenIncluded(
                        $today,
                        $today->copy()->addDays(3)
                    );
                }

                return $subscription->next_billing_at->between(
                    now()->startOfMinute(),
                    now()->copy()->addDays(3)->endOfDay()
                );
            })
            ->values();

        return view('student.payments.index', compact(
            'payments',
            'activeSubscriptions',
            'upcomingSubscriptions'
        ));
    }

    public function downloadReceipt(int $id)
    {
        $payment = Payment::query()
            ->with([
                'user',
                'order.items.purchasable',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $payload = $this->normalizePayload($payment->payload ?? null);
        $invoiceNumber = $this->makeInvoiceNumber($payment);
        $issuedAt = $payment->paid_at ?? $payment->created_at;

        $subtotal = (float) ($payment->order?->items?->sum(function ($item) {
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

        $pdf = Pdf::loadView('receipts.payment-receipt-pdf', $data)
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
