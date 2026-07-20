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

Artisan::command('subscriptions:recover-stripe-invoice {invoice}', function (string $invoice, SubscriptionClassService $subscriptions) {
    \Stripe\Stripe::setApiKey((string) config('services.stripe.secret'));

    $stripeInvoice = \Stripe\Invoice::retrieve([
        'id' => $invoice,
        'expand' => ['lines.data.parent.subscription_item_details.subscription'],
    ]);

    $order = $subscriptions->handleStripeInvoicePayment($stripeInvoice);

    if ($order) {
        $this->info("Recovered Stripe renewal invoice {$invoice} into order #{$order->id}.");
        return;
    }

    $this->warn('No renewal order was created. The invoice may be the initial payment, already processed, unmatched, or the class may have no remaining session.');
})->purpose('Recover a paid Stripe class-subscription invoice that missed webhook processing');

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
