<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\ClassModel;
use App\Models\ClassSessionAssignment;
use App\Models\StudioSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubscriptionClassManagementController extends Controller
{
    public function show(ClassModel $class)
    {
        $class->load(['teacher:id,name,email']);

        $sessions = $class->sessions()
            ->with('changedBy:id,name')
            ->orderBy('start_time')
            ->paginate(20, ['*'], 'sessions_page');

        $subscriptions = StudioSubscription::query()
            ->with(['user:id,name,email', 'lastFulfilledClassSession:id,start_time'])
            ->where('class_id', $class->id)
            ->orderByRaw("FIELD(status, 'past_due', 'active', 'trialing', 'pending', 'cancelled', 'completed')")
            ->orderByDesc('id')
            ->get()
            ->map(function (StudioSubscription $subscription) {
                $latestPayment = $subscription->payments()->latest('id')->first();
                $subscription->setAttribute('latest_payment_status', $latestPayment?->status ?? 'unpaid');
                $subscription->setAttribute('latest_payment_reference', $latestPayment?->reference);
                $subscription->setAttribute('can_attend', in_array($subscription->status, ['active', 'trialing'], true)
                    && in_array(strtolower((string) ($latestPayment?->status ?? '')), ['paid', 'success', 'completed', 'complete'], true));

                return $subscription;
            });

        return view('admin.classes.show', compact('class', 'sessions', 'subscriptions'));
    }

    public function notify(Request $request, ClassModel $class, StudioSubscription $subscription)
    {
        abort_unless((int) $subscription->class_id === (int) $class->id, 404);

        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        AppNotification::create([
            'studio_id' => $class->studio_id,
            'user_id' => $subscription->user_id,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => 'subscription_payment',
            'action_url' => route('student.payments.index'),
            'data' => [
                'class_id' => $class->id,
                'studio_subscription_id' => $subscription->id,
                'subscription_status' => $subscription->status,
            ],
        ]);

        return back()->with('success', 'Notification sent to the student.');
    }

    public function cancelStudentSubscription(Request $request, StudioSubscription $subscription)
    {
        abort_unless((int) $subscription->user_id === (int) auth()->id(), 404);

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|min:5|max:1000',
            'confirm_cancel' => 'accepted',
        ]);

        DB::transaction(function () use ($subscription, $validated) {
            if ($subscription->provider === 'stripe' && $subscription->provider_subscription_id) {
                \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
                \Stripe\Subscription::cancel($subscription->provider_subscription_id, [
                    'prorate' => false,
                ]);
            }

            $meta = $subscription->meta ?? [];
            $meta['student_cancellation_reason'] = $validated['cancellation_reason'];
            $meta['student_cancelled_at'] = now()->toIso8601String();

            $subscription->updateQuietly([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'next_billing_at' => null,
                'meta' => $meta,
            ]);

            $futureSessionIds = $subscription->classModel->sessions()
                ->where('start_time', '>', now())
                ->pluck('id');

            ClassSessionAssignment::query()
                ->where('user_id', $subscription->user_id)
                ->whereIn('class_session_id', $futureSessionIds)
                ->update([
                    'status' => 'cancelled',
                    'notes' => 'Subscription cancelled by student: '.$validated['cancellation_reason'],
                ]);

            AppNotification::create([
                'studio_id' => $subscription->studio_id,
                'user_id' => $subscription->user_id,
                'created_by' => $subscription->user_id,
                'title' => 'Subscription cancelled',
                'message' => 'Your subscription to '.$subscription->classModel->name.' has been cancelled. Upcoming class access has been removed.',
                'type' => 'subscription_cancelled',
                'action_url' => route('student.payments.index'),
                'data' => ['studio_subscription_id' => $subscription->id],
            ]);
        });

        return redirect()->route('student.payments.index')->with('success', 'Subscription cancelled successfully.');
    }
}