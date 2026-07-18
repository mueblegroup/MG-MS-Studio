<?php

use App\Models\Studio;
use App\Services\HitPayService;
use App\Services\PlatformSubscriptionDateSyncService;
use App\Services\SubscriptionClassService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:bill-due-hitpay', function (SubscriptionClassService $subscriptions, HitPayService $hitpay) {
    $count = $subscriptions->createDueHitpayRenewalOrders($hitpay);

    $this->info("Processed {$count} due HitPay subscription renewal item(s).");
})->purpose('Process due HitPay subscription renewals');

Artisan::command('platform-subscriptions:sync-dates', function (PlatformSubscriptionDateSyncService $sync) {
    $count = 0;

    Studio::query()
        ->whereNotNull('stripe_subscription_id')
        ->orderBy('id')
        ->chunkById(50, function ($studios) use ($sync, &$count) {
            foreach ($studios as $studio) {
                $sync->sync($studio);
                $count++;
            }
        });

    $this->info("Refreshed {$count} platform subscription(s) from Stripe.");
})->purpose('Refresh studio subscription renewal and trial dates from Stripe');

Schedule::command('subscriptions:bill-due-hitpay')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('platform-subscriptions:sync-dates')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
