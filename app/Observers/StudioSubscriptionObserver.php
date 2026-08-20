<?php

namespace App\Observers;

use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StudioSubscriptionObserver
{
    public function created(StudioSubscription $subscription): void
    {
        $this->scheduleStripeCancellation($subscription);
    }

    public function updated(StudioSubscription $subscription): void
    {
        $this->scheduleStripeCancellation($subscription);
    }

    private function scheduleStripeCancellation(StudioSubscription $subscription): void
    {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return;
        }

        $subscription->loadMissing('classModel.sessions');
        $class = $subscription->classModel;

        if (! $class || $class->type !== 'subscription') {
            return;
        }

        // The real class-session timetable is authoritative. until_date is only
        // a session-generation boundary and must not replace the actual final
        // class end time (for example with an artificial 23:59:59 cancellation).
        $lastSession = $class->sessions
            ->where('status', '!=', 'cancelled')
            ->sortByDesc('start_time')
            ->first();

        $cancelAtDate = $lastSession
            ? Carbon::parse($lastSession->end_time ?: $lastSession->start_time)
            : ($class->until_date
                ? Carbon::parse($class->until_date, config('app.timezone'))->endOfDay()
                : null);

        if (! $cancelAtDate || $cancelAtDate->timestamp <= now()->timestamp) {
            return;
        }

        $cancelAt = $cancelAtDate->timestamp;
        $meta = $subscription->meta ?? [];

        if ((int) ($meta['stripe_cancel_at'] ?? 0) === $cancelAt) {
            return;
        }

        try {
            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
            \Stripe\Subscription::update($subscription->provider_subscription_id, [
                'cancel_at' => $cancelAt,
                'proration_behavior' => 'none',
                'metadata' => [
                    'studio_subscription_id' => (string) $subscription->id,
                    'class_id' => (string) $subscription->class_id,
                    'scheduled_final_session_id' => (string) ($lastSession?->id ?? ''),
                    'scheduled_class_end_at' => $cancelAtDate->toIso8601String(),
                ],
            ]);

            $meta['stripe_cancel_at'] = $cancelAt;
            $meta['final_class_session_id'] = $lastSession?->id;
            $meta['scheduled_class_end_at'] = $cancelAtDate->toIso8601String();
            $subscription->updateQuietly(['meta' => $meta]);
        } catch (\Throwable $exception) {
            Log::error('Unable to schedule Stripe class subscription cancellation.', [
                'studio_subscription_id' => $subscription->id,
                'stripe_subscription_id' => $subscription->provider_subscription_id,
                'cancel_at' => $cancelAt,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
