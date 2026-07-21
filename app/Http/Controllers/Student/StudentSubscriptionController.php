<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StudioSubscription;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class StudentSubscriptionController extends Controller
{
    public function index()
    {
        $subscriptions = StudioSubscription::query()
            ->with([
                'classModel.teacher:id,name,email',
                'classModel.sessions' => fn ($query) => $query->orderBy('start_time'),
            ])
            ->where('user_id', Auth::id())
            ->orderByRaw("FIELD(status, 'past_due', 'active', 'trialing', 'pending', 'cancelled', 'completed')")
            ->orderByDesc('id')
            ->get();

        $subscriptionIds = $subscriptions->pluck('id');
        $sessionIds = $subscriptions
            ->flatMap(fn (StudioSubscription $subscription) => $subscription->classModel?->sessions?->pluck('id') ?? collect())
            ->unique()
            ->values();

        $paymentsBySubscriptionAndSession = $this->paymentsBySubscriptionAndSession($subscriptionIds, $sessionIds);
        $assignments = ClassSessionAssignment::query()
            ->where('user_id', Auth::id())
            ->whereIn('class_session_id', $sessionIds)
            ->get()
            ->keyBy('class_session_id');

        $subscriptions->each(function (StudioSubscription $subscription) use ($paymentsBySubscriptionAndSession, $assignments) {
            $sessions = $subscription->classModel?->sessions ?? collect();

            $sessions->each(function (ClassSession $session) use ($subscription, $paymentsBySubscriptionAndSession, $assignments) {
                $key = $subscription->id.':'.$session->id;
                $payment = $paymentsBySubscriptionAndSession->get($key);
                $assignment = $assignments->get($session->id);
                $sessionStatus = strtolower((string) ($session->status ?? 'scheduled'));
                $paymentStatus = strtolower((string) ($payment?->status ?? 'not_billed'));

                if ($sessionStatus === 'cancelled') {
                    $displayStatus = 'cancelled';
                } elseif ($payment) {
                    $displayStatus = match (true) {
                        in_array($paymentStatus, ['paid', 'success', 'completed', 'complete'], true) => 'paid',
                        str_contains($paymentStatus, 'fail') => 'payment_failed',
                        in_array($paymentStatus, ['past_due', 'unpaid'], true) => 'unpaid',
                        default => 'pending',
                    };
                } elseif ($assignment && ! in_array(strtolower((string) $assignment->status), ['cancelled', 'inactive'], true)) {
                    $displayStatus = 'assigned';
                } elseif ($session->start_time?->isPast()) {
                    $displayStatus = 'not_purchased';
                } else {
                    $displayStatus = 'not_billed';
                }

                $session->setAttribute('subscription_payment', $payment);
                $session->setAttribute('subscription_assignment', $assignment);
                $session->setAttribute('subscription_display_status', $displayStatus);
            });
        });

        return view('student.subscriptions.index', compact('subscriptions'));
    }

    private function paymentsBySubscriptionAndSession(Collection $subscriptionIds, Collection $sessionIds): Collection
    {
        if ($subscriptionIds->isEmpty() || $sessionIds->isEmpty()) {
            return collect();
        }

        return Payment::query()
            ->select('payments.*', 'orders.studio_subscription_id', 'order_items.purchasable_id as class_session_id')
            ->join('orders', 'orders.id', '=', 'payments.order_id')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->whereIn('orders.studio_subscription_id', $subscriptionIds)
            ->whereIn('order_items.purchasable_id', $sessionIds)
            ->where(function ($query) {
                $query->where('order_items.purchasable_type', ClassSession::class)
                    ->orWhere('order_items.purchasable_type', 'like', '%ClassSession');
            })
            ->orderByDesc('payments.id')
            ->get()
            ->unique(fn ($payment) => $payment->studio_subscription_id.':'.$payment->class_session_id)
            ->keyBy(fn ($payment) => $payment->studio_subscription_id.':'.$payment->class_session_id);
    }
}
