<?php

namespace App\Observers;

use App\Models\ClassModel;
use Illuminate\Validation\ValidationException;

class ClassModelObserver
{
    public function saving(ClassModel $class): void
    {
        if ($class->type !== 'subscription') {
            return;
        }

        $grace = (int) ($class->subscription_grace_days ?? 0);

        if ($class->billing_interval === 'day' && $grace > 23) {
            throw ValidationException::withMessages([
                'subscription_grace_days' => 'Daily subscriptions use an hourly grace period and it must be between 0 and 23 hours.',
            ]);
        }

        if ($class->billing_interval !== 'day' && $grace > 30) {
            throw ValidationException::withMessages([
                'subscription_grace_days' => 'Subscription grace period cannot exceed 30 days.',
            ]);
        }
    }

    public function updated(ClassModel $class): void
    {
        if (! $class->wasChanged('until_date') || $class->type !== 'subscription') {
            return;
        }

        $class->studioSubscriptions()
            ->where('provider', 'stripe')
            ->whereNotNull('provider_subscription_id')
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->chunkById(50, function ($subscriptions): void {
                foreach ($subscriptions as $subscription) {
                    // Touching the record triggers StudioSubscriptionObserver, which
                    // updates Stripe's cancel_at using the latest class end date.
                    $subscription->touch();
                }
            });
    }
}
