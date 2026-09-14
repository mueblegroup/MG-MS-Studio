<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\Payment;
use App\Models\StudioSubscription;
use App\Services\RecurringHitPayService;
use App\Services\SubscriptionClassService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentSubscriptionController extends Controller
{
    public function index(RecurringHitPayService $hitpay)
    {
        $subscriptions = StudioSubscription::query()
            ->with([
                'classModel.teacher:id,name,email',
                'classModel.sessions' => fn ($query) => $query->orderBy('start_time'),
                'initialOrder:id,status',
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

        $subscriptions->each(function (StudioSubscription $subscription) use ($stripeService, $hitpay, $initialPayments) {
            $subscription->setAttribute('stripe_sync_error', null);

            if (
                strtolower((string) $subscription->status) === 'pending'
                && strtolower((string) $subscription->provider) === 'hitpay'
                && $subscription->provider_subscription_id
            ) {
                try {
                    $billing = $hitpay->getRecurringBilling(
                        (string) $subscription->provider_subscription_id
                    );
                    $providerStatus = strtolower((string) ($billing['status'] ?? ''));

                    if (in_array($providerStatus, ['canceled', 'cancelled', 'inactive', 'expired'], true)) {
                        DB::transaction(function () use ($subscription, $providerStatus): void {
                            $subscription->updateQuietly([
                                'status' => 'cancelled',
                                'cancelled_at' => $subscription->cancelled_at ?: now(),
                                'next_billing_at' => null,
                                'meta' => array_merge((array) $subscription->meta, [
                                    'hitpay_recurring_status' => $providerStatus,
                                    'reconciled_from_hitpay_at' => now()->toIso8601String(),
                                ]),
                            ]);

                            if ($subscription->initial_order_id) {
                                $subscription->initialOrder()
                                    ->whereIn('status', ['pending', 'past_due'])
                                    ->update(['status' => 'cancelled']);

                                Payment::query()
                                    ->where('order_id', $subscription->initial_order_id)
                                    ->whereIn('status', ['pending', 'past_due'])
                                    ->update(['status' => 'cancelled']);
                            }
                        });
                    }
                } catch (\Throwable $exception) {
                    Log::warning('Unable to reconcile pending HitPay subscription for student view.', [
                        'studio_subscription_id' => $subscription->id,
                        'hitpay_recurring_billing_id' => $subscription->provider_subscription_id,
                        'message' => $exception->getMessage(),
                    ]);
                }
            }

            if (
                strtolower((string) $subscription->status) === 'pending'
                && in_array(strtolower((string) $subscription->initialOrder?->status), ['cancelled', 'canceled'], true)
            ) {
                $subscription->updateQuietly([
                    'status' => 'cancelled',
                    'cancelled_at' => $subscription->cancelled_at ?: now(),
                    'next_billing_at' => null,
                    'meta' => array_merge((array) $subscription->meta, [
                        'reconciled_from_cancelled_initial_order_at' => now()->toIso8601String(),
                    ]),
                ]);
            }

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

        // Eloquent reuses one eager-loaded ClassModel instance for records
        // belonging to the same class. Clone it and its sessions per
        // subscription before attaching display-only payment state, otherwise
        // one subscription card can show another subscription's payment.
        $subscriptions->each(function (StudioSubscription $subscription): void {
            if (! $subscription->classModel) {
                return;
            }

            $classModel = clone $subscription->classModel;
            $classModel->setRelation(
                'sessions',
                $subscription->classModel->sessions->map(fn (ClassSession $session) => clone $session)
            );
            $subscription->setRelation('classModel', $classModel);
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
