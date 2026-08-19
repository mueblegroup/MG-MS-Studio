<?php

use App\Models\Studio;
use App\Models\StudioPaymentGateway;
use App\Models\StudioSubscription;
use App\Services\HitPayService;
use App\Services\PlatformSubscriptionDateSyncService;
use App\Services\SubscriptionClassService;
use App\Support\TenantManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:bill-due-hitpay', function (SubscriptionClassService $subscriptions, HitPayService $hitpay) {
    $count = $subscriptions->createDueHitpayRenewalOrders($hitpay);

    $this->info("Processed {$count} due legacy HitPay subscription renewal item(s).");
})->purpose('Process legacy HitPay subscription renewals that have not migrated to recurring billing');

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

Artisan::command('subscriptions:sync-stripe-end-dates', function (SubscriptionClassService $subscriptions, TenantManager $tenants) {
    if (! method_exists($subscriptions, 'scheduleStripeCancellationAfterFinalSession')) {
        $this->error('The active subscription billing service does not support final-session cancellation.');
        return 1;
    }

    $processed = 0;
    $skipped = 0;

    StudioSubscription::query()
        ->where('provider', 'stripe')
        ->whereNotNull('provider_subscription_id')
        ->whereIn('status', ['pending', 'active', 'trialing', 'past_due'])
        ->orderBy('id')
        ->chunkById(50, function ($studioSubscriptions) use ($subscriptions, $tenants, &$processed, &$skipped): void {
            foreach ($studioSubscriptions as $studioSubscription) {
                $studio = Studio::query()->find($studioSubscription->studio_id);
                $gateway = $studio ? StudioPaymentGateway::query()
                    ->where('studio_id', $studio->id)
                    ->where('provider', 'stripe')
                    ->where('enabled', true)
                    ->first() : null;

                $credentials = (array) ($gateway?->credentials ?? []);
                $secret = (string) ($credentials['secret_key'] ?? '');

                if (! $studio || ! $gateway || $secret === '') {
                    $skipped++;
                    continue;
                }

                $tenants->set($studio);
                config([
                    'services.stripe.key' => (string) ($credentials['publishable_key'] ?? ''),
                    'services.stripe.secret' => $secret,
                    'services.stripe.webhook_secret' => (string) ($gateway->webhook_secret ?? ''),
                ]);

                try {
                    $subscriptions->scheduleStripeCancellationAfterFinalSession($studioSubscription);
                    $processed++;
                } finally {
                    $tenants->clear();
                }
            }
        });

    $this->info("Reconciled {$processed} tenant Stripe class subscription end date(s); skipped {$skipped} without an enabled tenant Stripe configuration.");

    return 0;
})->purpose('Schedule tenant Stripe class subscriptions to stop after their final class session');

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
})->purpose('Refresh studio SaaS subscription renewal and trial dates from platform Stripe');

Schedule::command('subscriptions:sync-stripe-end-dates')
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::command('platform-subscriptions:sync-dates')
    ->hourly()
    ->withoutOverlapping()
    ->onOneServer();
