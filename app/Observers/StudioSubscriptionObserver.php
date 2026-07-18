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

        $cancelAtDate = null;

        if ($class->until_date) {
            $cancelAtDate = Carbon::parse($class->until_date, config('app.timezone'))->endOfDay();
        } else {
            $lastSessionEnd = $class->sessions
                ->pluck('end_time')
                ->filter()
                ->map(fn ($endTime) => Carbon::parse($endTime, config('app.timezone')))
                ->sortDesc()
                ->first();

            if ($lastSessionEnd) {
                $cancelAtDate = $lastSessionEnd;
            }
        }

        if (! $cancelAtDate || $cancelAtDate->timestamp <= now()->timestamp) {
            return;
        }

        $cancelAt = $cancelAtDate->timestamp;
        $scheduledEndDate = $cancelAtDate->toDateString();
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
                    'scheduled_class_end_date' => $scheduledEndDate,
                ],
            ]);

            $meta['stripe_cancel_at'] = $cancelAt;
            $meta['scheduled_class_end_date'] = $scheduledEndDate;
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
