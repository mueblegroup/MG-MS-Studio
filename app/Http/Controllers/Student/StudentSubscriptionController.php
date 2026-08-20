<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\Payment;
use App\Models\StudioSubscription;
use App\Services\SubscriptionClassService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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

        $stripeService = app(SubscriptionClassService::class);

        $initialPayments = Payment::query()
            ->where('user_id', Auth::id())
            ->whereIn('order_id', $subscriptions->pluck('initial_order_id')->filter())
            ->orderByDesc('id')
            ->get()
            ->unique('order_id')
            ->keyBy('order_id');

        $subscriptions->each(function (StudioSubscription $subscription) use ($stripeService, $initialPayments) {
            $subscription->setAttribute('stripe_sync_error', null);

            if ($subscription->provider === 'stripe' && $subscription->provider_subscription_id) {
                try {
                    $fresh = $stripeService->refreshStripeBillingPeriod($subscription);
                    $subscription->setRawAttributes($fresh->getAttributes(), true);
                } catch (\Throwable $exception) {
                    $subscription->setAttribute('stripe_sync_error', 'Live Stripe billing details could not be refreshed.');
                    Log::warning('Unable to refresh Stripe subscription for student view.', [
                        'studio_subscription_id' => $subscription->id,
                        'stripe_subscription_id' => $subscription->provider_subscription_id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            $classInterval = strtolower((string) ($subscription->classModel?->billing_interval ?? ''));
            $providerInterval = strtolower((string) ($subscription->billing_interval ?? ''));
            $subscription->setAttribute(
                'billing_interval_mismatch',
                $subscription->provider === 'stripe'
                && $classInterval !== ''
                && $providerInterval !== ''
                && $classInterval !== $providerInterval
            );
            $subscription->setAttribute('class_billing_interval', $classInterval);
            $subscription->setAttribute('provider_billing_interval', $providerInterval);

            $initialPayment = $initialPayments->get($subscription->initial_order_id);
            $subscription->setAttribute('initial_subscription_payment', $initialPayment);
            $subscription->setAttribute(
                'can_retry_initial_payment',
                in_array(strtolower((string) $subscription->status), ['pending', 'past_due'], true)
                && $initialPayment
                && ! in_array(strtolower((string) $initialPayment->status), ['paid', 'success', 'completed', 'complete'], true)
            );
        });

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
