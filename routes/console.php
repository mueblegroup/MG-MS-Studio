<?php

use App\Models\Studio;
use App\Models\StudioPaymentGateway;
use App\Models\StudioSubscription;
use App\Services\HitPayService;
use App\Services\PlatformSubscriptionDateSyncService;
use App\Services\ProductionSubscriptionClassService;
use App\Services\RecurringHitPayService;
use App\Services\StudioSettingsService;
use App\Services\SubscriptionClassService;
use App\Support\TenantManager;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('subscriptions:sync-hitpay-recurring {--studio= : Limit reconciliation to one studio ID} {--subscription= : Limit reconciliation to one local subscription ID}', function (
    ProductionSubscriptionClassService $subscriptions,
    RecurringHitPayService $hitpay,
    TenantManager $tenants
) {
    $studioFilter = (int) ($this->option('studio') ?: 0);
    $subscriptionFilter = (int) ($this->option('subscription') ?: 0);
    $originalTimezone = (string) config('app.timezone', 'Asia/Kuala_Lumpur');

    $items = StudioSubscription::query()
        ->with(['classModel', 'user'])
        ->where('provider', 'hitpay')
        ->whereNotNull('provider_subscription_id')
        ->whereIn('status', ['pending', 'active', 'trialing', 'past_due'])
        ->when($studioFilter > 0, fn ($query) => $query->where('studio_id', $studioFilter))
        ->when($subscriptionFilter > 0, fn ($query) => $query->whereKey($subscriptionFilter))
        ->orderBy('studio_id')
        ->orderBy('id')
        ->get();

    if ($items->isEmpty()) {
        $this->info('No active HitPay recurring subscriptions require reconciliation.');
        return 0;
    }

    $synced = 0;
    $realigned = 0;
    $attention = 0;
    $skipped = 0;
    $errors = 0;
    $rows = [];

    foreach ($items as $subscription) {
        $studio = Studio::query()->find($subscription->studio_id);
        if (! $studio) {
            $rows[] = [$subscription->id, $subscription->studio_id, '—', '—', 'ERROR', 'Studio missing'];
            $errors++;
            continue;
        }

        $gateway = StudioPaymentGateway::query()
            ->where('studio_id', $studio->id)
            ->where('provider', 'hitpay')
            ->where('enabled', true)
            ->first();

        $credentials = (array) ($gateway?->credentials ?? []);
        if (! $gateway || empty($credentials['api_key'])) {
            $rows[] = [$subscription->id, $studio->id, '—', '—', 'SKIPPED', 'HitPay gateway/API key unavailable'];
            $skipped++;
            continue;
        }

        $tenants->set($studio);

        try {
            $studioTimezone = (string) app(StudioSettingsService::class)->get(
                'timezone',
                data_get($studio->settings, 'timezone', $originalTimezone)
            );
            if ($studioTimezone === '') {
                $studioTimezone = $originalTimezone;
            }

            config(['app.timezone' => $studioTimezone]);
            date_default_timezone_set($studioTimezone);

            $live = $hitpay->getRecurringBilling((string) $subscription->provider_subscription_id);
            $subscription = $subscriptions->syncHitPayRecurringBilling($subscription, $live);
            $synced++;

            $expected = $subscriptions->expectedUpcomingBilling($subscription);
            $expectedDate = $expected['hitpay_start_date_sgt'] ?? null;
            $providerDate = null;

            foreach (['next_charge_date', 'next_billing_date', 'start_date'] as $field) {
                if (! empty($live[$field])) {
                    $providerDate = Carbon::parse((string) $live[$field], 'Asia/Singapore')->toDateString();
                    break;
                }
            }

            $todaySgt = Carbon::now('Asia/Singapore')->toDateString();
            $result = 'SYNCED';
            $note = 'Provider state refreshed';

            if ($expectedDate && $providerDate && $expectedDate !== $providerDate) {
                // Only auto-realign when the class-policy billing date is still
                // safely in the future. When the target is today or already
                // passed, changing an active recurring plan could cause an
                // unexpected immediate collection or conflict with a charge
                // HitPay is already processing.
                if ($expectedDate > $todaySgt) {
                    $subscriptions->repairHitPayUpcomingBilling($subscription);
                    $realigned++;
                    $result = 'REALIGNED';
                    $note = $providerDate.' → '.$expectedDate;
                } else {
                    $attention++;
                    $result = 'ATTENTION';
                    $note = 'Expected '.$expectedDate.'; provider '.$providerDate.'; billing target is today/past';

                    Log::warning('HitPay recurring billing requires manual attention because the expected class billing date is no longer safely in the future.', [
                        'studio_subscription_id' => $subscription->id,
                        'studio_id' => $studio->id,
                        'hitpay_recurring_billing_id' => $subscription->provider_subscription_id,
                        'expected_billing_date_sgt' => $expectedDate,
                        'provider_billing_date_sgt' => $providerDate,
                    ]);
                }
            } elseif ($expectedDate && ! $providerDate) {
                $attention++;
                $result = 'ATTENTION';
                $note = 'Provider did not expose a next billing date';
            }

            $rows[] = [
                $subscription->id,
                $studio->id,
                $expectedDate ?: '—',
                $providerDate ?: 'unknown',
                $result,
                $note,
            ];
        } catch (Throwable $exception) {
            $errors++;
            $rows[] = [$subscription->id, $studio->id, '—', '—', 'ERROR', $exception->getMessage()];

            Log::error('HitPay recurring subscription reconciliation failed.', [
                'studio_subscription_id' => $subscription->id,
                'studio_id' => $studio->id,
                'hitpay_recurring_billing_id' => $subscription->provider_subscription_id,
                'message' => $exception->getMessage(),
            ]);
        } finally {
            $tenants->clear();
            config(['app.timezone' => $originalTimezone]);
            date_default_timezone_set($originalTimezone);
        }
    }

    $this->table(
        ['Sub ID', 'Studio', 'Expected SGT', 'Provider SGT', 'Result', 'Note'],
        $rows
    );

    $this->info("HitPay reconciliation: synced {$synced}; realigned {$realigned}; attention {$attention}; skipped {$skipped}; errors {$errors}.");

    return $errors > 0 ? 2 : 0;
})->purpose('Reconcile tenant HitPay recurring subscriptions with provider state and safely re-align future class billing dates');

Artisan::command('subscriptions:upcoming-billings {--subscription= : Inspect one local studio subscription ID} {--studio= : Limit to one studio ID} {--provider= : Limit to stripe or hitpay} {--days=14 : Only show expected charge targets within this many days} {--repair : Re-align mismatched HitPay recurring billing dates to the class schedule}', function (
    ProductionSubscriptionClassService $subscriptions,
    RecurringHitPayService $hitpay,
    TenantManager $tenants
) {
    $subscriptionId = (int) ($this->option('subscription') ?: 0);
    $studioId = (int) ($this->option('studio') ?: 0);
    $providerFilter = strtolower(trim((string) $this->option('provider')));
    $days = max(1, min(90, (int) $this->option('days')));
    $repair = (bool) $this->option('repair');

    if ($providerFilter !== '' && ! in_array($providerFilter, ['stripe', 'hitpay'], true)) {
        $this->error('The --provider option must be stripe or hitpay.');
        return 1;
    }

    $query = StudioSubscription::query()
        ->with(['classModel', 'user'])
        ->whereIn('status', ['pending', 'active', 'trialing', 'past_due'])
        ->whereNotNull('provider_subscription_id')
        ->when($subscriptionId > 0, fn ($q) => $q->whereKey($subscriptionId))
        ->when($studioId > 0, fn ($q) => $q->where('studio_id', $studioId))
        ->when($providerFilter !== '', fn ($q) => $q->where('provider', $providerFilter))
        ->orderBy('studio_id')
        ->orderBy('id');

    $items = $query->get();

    if ($items->isEmpty()) {
        $this->warn('No matching active recurring class subscriptions were found.');
        return 0;
    }

    if ($repair) {
        $this->warn('REPAIR MODE: only mismatched HitPay recurring billing dates will be updated. No charge is created by this command.');
    } else {
        $this->comment('READ-ONLY MODE: no provider subscription will be modified.');
    }

    $originalTimezone = (string) config('app.timezone', 'Asia/Kuala_Lumpur');
    $rows = [];
    $mismatches = 0;
    $repairs = 0;
    $errors = 0;

    foreach ($items as $subscription) {
        $studio = Studio::query()->find($subscription->studio_id);

        if (! $studio) {
            $rows[] = [$subscription->id, $subscription->studio_id, strtoupper((string) $subscription->provider), '—', '—', '—', 'ERROR: studio missing'];
            $errors++;
            continue;
        }

        $tenants->set($studio);

        try {
            $studioTimezone = (string) app(StudioSettingsService::class)->get(
                'timezone',
                data_get($studio->settings, 'timezone', $originalTimezone)
            );
            if ($studioTimezone === '') {
                $studioTimezone = $originalTimezone;
            }

            config(['app.timezone' => $studioTimezone]);
            date_default_timezone_set($studioTimezone);

            $expected = $subscriptions->expectedUpcomingBilling($subscription);
            $session = $expected['session'] ?? null;
            /** @var Carbon|null $target */
            $target = $expected['target_charge_at'] ?? null;

            if ($target && $target->gt(now()->copy()->addDays($days)->endOfDay())) {
                continue;
            }

            $provider = strtolower((string) $subscription->provider);
            $providerValue = '—';
            $status = 'OK';

            if ($provider === 'hitpay') {
                try {
                    $live = $hitpay->getRecurringBilling((string) $subscription->provider_subscription_id);
                    $providerDate = null;
                    foreach (['next_charge_date', 'next_billing_date', 'start_date'] as $field) {
                        if (! empty($live[$field])) {
                            $providerDate = Carbon::parse((string) $live[$field], 'Asia/Singapore')->toDateString();
                            break;
                        }
                    }

                    $providerValue = $providerDate ?: 'unknown';
                    $expectedDate = $expected['hitpay_start_date_sgt'] ?? null;

                    if ($expectedDate && $providerDate && $expectedDate !== $providerDate) {
                        $mismatches++;
                        $status = 'MISMATCH';

                        if ($repair) {
                            $subscriptions->repairHitPayUpcomingBilling($subscription);
                            $refreshed = $hitpay->getRecurringBilling((string) $subscription->provider_subscription_id);
                            $refreshedDate = null;
                            foreach (['next_charge_date', 'next_billing_date', 'start_date'] as $field) {
                                if (! empty($refreshed[$field])) {
                                    $refreshedDate = Carbon::parse((string) $refreshed[$field], 'Asia/Singapore')->toDateString();
                                    break;
                                }
                            }
                            $providerValue = ($providerDate ?: 'unknown').' → '.($refreshedDate ?: $expectedDate);
                            $status = 'REPAIRED';
                            $repairs++;
                        }
                    } elseif ($expectedDate && ! $providerDate) {
                        $status = 'CHECK PROVIDER';
                    }
                } catch (Throwable $exception) {
                    $providerValue = 'lookup failed';
                    $status = 'ERROR: '.$exception->getMessage();
                    $errors++;
                }
            } elseif ($provider === 'stripe') {
                try {
                    $gateway = StudioPaymentGateway::query()
                        ->where('studio_id', $studio->id)
                        ->where('provider', 'stripe')
                        ->where('enabled', true)
                        ->first();
                    $credentials = (array) ($gateway?->credentials ?? []);
                    $secret = (string) ($credentials['secret_key'] ?? '');

                    if ($secret === '') {
                        throw new RuntimeException('tenant Stripe secret is missing');
                    }

                    $client = new \Stripe\StripeClient($secret);
                    $live = $client->subscriptions->retrieve((string) $subscription->provider_subscription_id, [
                        'expand' => ['items.data.price'],
                    ]);
                    $item = $live->items->data[0] ?? null;
                    $periodEnd = $live->current_period_end ?? $item?->current_period_end ?? null;
                    $providerAt = $periodEnd ? Carbon::createFromTimestamp((int) $periodEnd)->timezone($studioTimezone) : null;
                    $providerValue = $providerAt?->format('Y-m-d H:i T') ?? 'unknown';

                    if ($target && $providerAt && abs($providerAt->timestamp - $target->timestamp) > 300) {
                        $mismatches++;
                        $status = 'MISMATCH';
                    }
                } catch (Throwable $exception) {
                    $providerValue = 'lookup failed';
                    $status = 'ERROR: '.$exception->getMessage();
                    $errors++;
                }
            }

            $expectedDisplay = $provider === 'hitpay'
                ? (($expected['hitpay_start_date_sgt'] ?? '—').' SGT')
                : ($target?->format('Y-m-d H:i T') ?? '—');

            $rows[] = [
                $subscription->id,
                $studio->id,
                strtoupper($provider),
                $session ? '#'.$session->id.' '.$session->start_time?->format('Y-m-d H:i') : 'none',
                $expectedDisplay,
                $providerValue,
                $status,
            ];
        } finally {
            $tenants->clear();
            config(['app.timezone' => $originalTimezone]);
            date_default_timezone_set($originalTimezone);
        }
    }

    $this->table(
        ['Sub ID', 'Studio', 'Provider', 'Next class', 'Expected charge', 'Provider schedule', 'Result'],
        $rows
    );

    $this->newLine();
    $this->info('Checked '.count($rows).' subscription(s); mismatches '.$mismatches.'; repaired '.$repairs.'; errors '.$errors.'.');

    if (! $repair && $mismatches > 0) {
        $this->comment('Run the same command with --repair to re-align HitPay mismatches. Stripe mismatches are inspection-only and should be investigated before changing provider billing anchors.');
    }

    return $errors > 0 ? 2 : 0;
})->purpose('Inspect upcoming recurring class charges against class-session timing and optionally repair HitPay schedule mismatches');

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

Schedule::command('subscriptions:sync-hitpay-recurring')
    ->hourlyAt(15)
    ->withoutOverlapping()
    ->onOneServer();
