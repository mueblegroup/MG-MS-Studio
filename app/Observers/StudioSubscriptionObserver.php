<?php

namespace App\Observers;

use App\Models\StudioSubscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class StudioSubscriptionObserver
{
    public function updated(StudioSubscription $subscription): void
    {
        if ($subscription->provider !== 'stripe' || ! $subscription->provider_subscription_id) {
            return;
        }

        $subscription->loadMissing('classModel');
        $class = $subscription->classModel;

        if (! $class || $class->type !== 'subscription' || ! $class->until_date) {
            return;
        }

        $cancelAt = Carbon::parse($class->until_date, config('app.timezone'))
            ->endOfDay()
            ->timestamp;

        if ($cancelAt <= now()->timestamp) {
            return;
        }

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
                    'scheduled_class_end_date' => Carbon::parse($class->until_date)->toDateString(),
                ],
            ]);

            $meta['stripe_cancel_at'] = $cancelAt;
            $meta['scheduled_class_end_date'] = Carbon::parse($class->until_date)->toDateString();
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
