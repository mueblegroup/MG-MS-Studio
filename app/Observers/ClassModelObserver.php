<?php

namespace App\Observers;

use App\Models\ClassModel;

class ClassModelObserver
{
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
