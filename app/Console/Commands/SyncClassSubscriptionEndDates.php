<?php

namespace App\Console\Commands;

use App\Models\StudioSubscription;
use Illuminate\Console\Command;

class SyncClassSubscriptionEndDates extends Command
{
    protected $signature = 'subscriptions:sync-class-end-dates {--studio= : Limit the sync to one studio ID}';

    protected $description = 'Schedule Stripe subscription cancellation using each subscription class end date.';

    public function handle(): int
    {
        $updated = 0;

        StudioSubscription::query()
            ->with('classModel')
            ->where('provider', 'stripe')
            ->whereNotNull('provider_subscription_id')
            ->whereIn('status', ['active', 'trialing', 'past_due'])
            ->when($this->option('studio'), fn ($query, $studioId) => $query->where('studio_id', (int) $studioId))
            ->whereHas('classModel', function ($query) {
                $query->where('type', 'subscription')->whereNotNull('until_date');
            })
            ->chunkById(50, function ($subscriptions) use (&$updated) {
                foreach ($subscriptions as $subscription) {
                    // Touching the model invokes StudioSubscriptionObserver, which
                    // safely applies Stripe cancel_at when it is not already synced.
                    $subscription->touch();
                    $updated++;
                }
            });

        $this->info("Checked {$updated} Stripe class subscription(s).");

        return self::SUCCESS;
    }
}
