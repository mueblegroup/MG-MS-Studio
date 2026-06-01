<?php

use App\Services\HitPayService;
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

Schedule::command('subscriptions:bill-due-hitpay')
    ->dailyAt('08:00')
    ->withoutOverlapping()
    ->onOneServer();
