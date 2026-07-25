<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use Carbon\Carbon;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\StripeClient;
use Stripe\Webhook;

class TrialAwarePlatformStripeBillingService extends PlatformStripeBillingService
{
    private StripeClient $trialStripe;

    public function __construct()
    {
        parent::__construct();

        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET before using platform billing.');
        }

        $this->trialStripe = new StripeClient($secret);
    }

    public function constructWebhookEvent(string $payload, ?string $signature): Event
    {
        $secret = (string) config('services.stripe.platform_webhook_secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe platform webhook signing secret is not configured. Set STRIPE_PLATFORM_WEBHOOK_SECRET.');
        }

        return Webhook::constructEvent($payload, (string) $signature, $secret);
    }

    public function createCheckoutSession(Studio $studio, PlatformSubscriptionPlan $plan, string $successUrl, string $cancelUrl): Session
    {
        $customerId = $this->ensureTrialCustomer($studio);
        $priceId = $this->ensureTrialRecurringPrice($plan);

        $subscriptionData = [
            'metadata' => [
                'studio_id' => (string) $studio->id,
                'plan_id' => (string) $plan->id,
            ],
        ];

        if ((int) $plan->trial_days > 0 && ! $studio->stripe_subscription_id) {
            $subscriptionData['trial_period_days'] = (int) $plan->trial_days;
        }

        return $this->trialStripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl.'?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl.'?checkout=cancelled',
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'auto',
            'client_reference_id' => (string) $studio->id,
            'metadata' => [
                'studio_id' => (string) $studio->id,
                'plan_id' => (string) $plan->id,
                'trial_days' => (string) ((int) $plan->trial_days),
            ],
            'subscription_data' => $subscriptionData,
        ]);
    }

    public function previewUpgrade(Studio $studio, PlatformSubscriptionPlan $targetPlan): array
    {
        [$subscription, $item, $priceId, $intervalChanged, $targetInterval] = $this->intervalChangeContext($studio, $targetPlan);

        if (! $intervalChanged) {
            return parent::previewUpgrade($studio, $targetPlan) + [
                'interval_changed' => false,
                'current_interval' => $this->normaliseInterval($item->price?->recurring?->interval ?? null),
                'target_interval' => $targetInterval,
                'new_period_end' => null,
                'credit_amount' => 0.0,
            ];
        }

        $previewedAt = now()->timestamp;
        $preview = $this->trialStripe->invoices->createPreview([
            'customer' => $studio->stripe_customer_id,
            'subscription' => $subscription->id,
            'subscription_details' => [
                'items' => [[
                    'id' => $item->id,
                    'price' => $priceId,
                ]],
                'billing_cycle_anchor' => 'now',
                'proration_behavior' => 'always_invoice',
            ],
        ]);

        $paymentMethod = $subscription->default_payment_method ?? null;
        if (is_string($paymentMethod) && $paymentMethod !== '') {
            $paymentMethod = $this->trialStripe->paymentMethods->retrieve($paymentMethod, []);
        }

        return [
            'amount_due' => ((int) ($preview->amount_due ?? 0)) / 100,
            'credit_amount' => $this->previewCreditAmount($preview),
            'currency' => strtoupper((string) $preview->currency),
            'proration_date' => Carbon::createFromTimestamp($previewedAt),
            'period_end' => ! empty($item->current_period_end)
                ? Carbon::createFromTimestamp((int) $item->current_period_end)
                : null,
            'new_period_end' => $this->previewPeriodEnd($preview, $priceId),
            'payment_method_brand' => $paymentMethod?->card?->brand,
            'payment_method_last4' => $paymentMethod?->card?->last4,
            'interval_changed' => true,
            'current_interval' => $this->normaliseInterval($item->price?->recurring?->interval ?? null),
            'target_interval' => $targetInterval,
        ];
    }

    public function upgrade(Studio $studio, PlatformSubscriptionPlan $targetPlan): array
    {
        [$subscription, $item, $priceId, $intervalChanged, $targetInterval] = $this->intervalChangeContext($studio, $targetPlan);

        if (! $intervalChanged) {
            return parent::upgrade($studio, $targetPlan) + [
                'interval_changed' => false,
                'target_interval' => $targetInterval,
            ];
        }

        $preview = $this->trialStripe->invoices->createPreview([
            'customer' => $studio->stripe_customer_id,
            'subscription' => $subscription->id,
            'subscription_details' => [
                'items' => [[
                    'id' => $item->id,
                    'price' => $priceId,
                ]],
                'billing_cycle_anchor' => 'now',
                'proration_behavior' => 'always_invoice',
            ],
        ]);

        $updated = $this->trialStripe->subscriptions->update($subscription->id, [
            'items' => [[
                'id' => $item->id,
                'price' => $priceId,
            ]],
            'billing_cycle_anchor' => 'now',
            'proration_behavior' => 'always_invoice',
            'payment_behavior' => 'pending_if_incomplete',
            'expand' => ['items.data.price', 'latest_invoice.payment_intent'],
        ]);

        $latestInvoice = $updated->latest_invoice ?? null;
        if (is_string($latestInvoice)) {
            $latestInvoice = $this->trialStripe->invoices->retrieve($latestInvoice, [
                'expand' => ['payment_intent'],
            ]);
        }

        $invoiceStatus = (string) ($latestInvoice->status ?? '');
        $paymentUrl = $invoiceStatus === 'open'
            ? ($latestInvoice->hosted_invoice_url ?? null)
            : null;

        // Metadata is not part of Stripe pending updates. Update it only after the
        // interval-change invoice has succeeded and Stripe has applied the new item.
        if ($invoiceStatus === 'paid' && empty($updated->pending_update)) {
            $this->trialStripe->subscriptions->update($subscription->id, [
                'metadata' => [
                    'studio_id' => (string) $studio->id,
                    'plan_id' => (string) $targetPlan->id,
                ],
            ]);
        }

        $updatedItem = $updated->items->data[0] ?? null;

        return [
            'amount_due' => ((int) ($preview->amount_due ?? 0)) / 100,
            'credit_amount' => $this->previewCreditAmount($preview),
            'currency' => strtoupper((string) $preview->currency),
            'subscription_status' => (string) $updated->status,
            'invoice_status' => $invoiceStatus,
            'payment_url' => $paymentUrl,
            'paid_immediately' => $invoiceStatus === 'paid',
            'interval_changed' => true,
            'target_interval' => $targetInterval,
            'new_period_end' => ! empty($updatedItem?->current_period_end)
                ? Carbon::createFromTimestamp((int) $updatedItem->current_period_end)
                : $this->previewPeriodEnd($preview, $priceId),
        ];
    }

    public function handleWebhook(Event $event): void
    {
        parent::handleWebhook($event);

        if (in_array($event->type, ['invoice.paid', 'invoice.payment_failed'], true)) {
            $invoice = $event->data->object;
            $subscriptionId = is_string($invoice->subscription ?? null)
                ? $invoice->subscription
                : ($invoice->subscription->id ?? null);

            $studio = Studio::query()
                ->when($subscriptionId, fn ($query) => $query->where('stripe_subscription_id', $subscriptionId))
                ->when(! $subscriptionId && ! empty($invoice->customer), fn ($query) => $query->where('stripe_customer_id', $invoice->customer))
                ->first();

            if ($studio) {
                app(PlatformSubscriptionDateSyncService::class)->sync($studio);
            }

            return;
        }

        if (! in_array($event->type, [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ], true)) {
            return;
        }

        $subscription = $event->data->object;
        $studioId = (int) ($subscription->metadata->studio_id ?? 0);
        $studio = $studioId
            ? Studio::query()->find($studioId)
            : Studio::query()->where('stripe_subscription_id', $subscription->id ?? null)->first();

        if (! $studio) {
            return;
        }

        $trialEnd = ! empty($subscription->trial_end)
            ? Carbon::createFromTimestamp((int) $subscription->trial_end)
            : null;

        $studio->forceFill([
            'trial_ends_at' => $trialEnd,
        ])->save();
    }

    private function intervalChangeContext(Studio $studio, PlatformSubscriptionPlan $targetPlan): array
    {
        if (! $studio->stripe_subscription_id) {
            throw new RuntimeException('No active Stripe subscription was found for this studio.');
        }

        $subscription = $this->trialStripe->subscriptions->retrieve($studio->stripe_subscription_id, [
            'expand' => ['items.data.price', 'latest_invoice', 'default_payment_method'],
        ]);

        if (! in_array((string) $subscription->status, ['active', 'trialing', 'past_due'], true)) {
            throw new RuntimeException('The current Stripe subscription cannot be changed in its present status.');
        }

        if (! empty($subscription->pending_update)) {
            throw new RuntimeException('A previous subscription change is still waiting for payment. Complete or cancel that invoice before changing plans again.');
        }

        $item = $subscription->items->data[0] ?? null;
        if (! $item) {
            throw new RuntimeException('The Stripe subscription has no billable item.');
        }

        $currentPlan = $studio->platformSubscriptionPlan;
        if ($currentPlan && (int) $currentPlan->id === (int) $targetPlan->id) {
            throw new RuntimeException('The studio is already subscribed to this plan.');
        }

        $currentInterval = $this->normaliseInterval($item->price?->recurring?->interval ?? $currentPlan?->billing_interval);
        $targetInterval = $this->normaliseInterval($targetPlan->billing_interval);
        $intervalChanged = $currentInterval !== $targetInterval;

        if (! $intervalChanged && $currentPlan && (float) $targetPlan->price <= (float) $currentPlan->price) {
            throw new RuntimeException('Same-cycle changes must be upgrades to a higher-priced plan.');
        }

        return [
            $subscription,
            $item,
            $this->ensureTrialRecurringPrice($targetPlan),
            $intervalChanged,
            $targetInterval,
        ];
    }

    private function normaliseInterval(?string $interval): string
    {
        return match (strtolower((string) $interval)) {
            'day', 'daily' => 'day',
            'week', 'weekly' => 'week',
            'year', 'yearly', 'annual', 'annually' => 'year',
            default => 'month',
        };
    }

    private function previewCreditAmount(object $preview): float
    {
        $credit = 0;

        foreach ($preview->lines->data ?? [] as $line) {
            $isProration = (bool) ($line->proration
                ?? $line->parent?->subscription_item_details?->proration
                ?? false);
            $amount = (int) ($line->amount ?? 0);

            if ($isProration && $amount < 0) {
                $credit += abs($amount);
            }
        }

        return $credit / 100;
    }

    private function previewPeriodEnd(object $preview, string $targetPriceId): ?Carbon
    {
        $periodEnd = null;

        foreach ($preview->lines->data ?? [] as $line) {
            $linePriceId = is_string($line->price ?? null)
                ? $line->price
                : ($line->price?->id
                    ?? $line->pricing?->price_details?->price
                    ?? null);

            if ($linePriceId !== $targetPriceId || (int) ($line->amount ?? 0) <= 0) {
                continue;
            }

            $linePeriodEnd = (int) ($line->period?->end ?? 0);
            if ($linePeriodEnd > 0) {
                $periodEnd = max($periodEnd ?? 0, $linePeriodEnd);
            }
        }

        return $periodEnd ? Carbon::createFromTimestamp($periodEnd) : null;
    }

    private function ensureTrialCustomer(Studio $studio): string
    {
        if ($studio->stripe_customer_id) {
            return $studio->stripe_customer_id;
        }

        $studio->loadMissing('owner');
        $customer = $this->trialStripe->customers->create([
            'name' => $studio->name,
            'email' => $studio->owner?->email,
            'metadata' => [
                'studio_id' => (string) $studio->id,
                'owner_user_id' => (string) $studio->owner_user_id,
            ],
        ]);

        $studio->forceFill(['stripe_customer_id' => $customer->id])->save();

        return (string) $customer->id;
    }

    private function ensureTrialRecurringPrice(PlatformSubscriptionPlan $plan): string
    {
        if ($plan->stripe_price_id) {
            return $plan->stripe_price_id;
        }

        $productId = $plan->stripe_product_id;

        if (! $productId) {
            $product = $this->trialStripe->products->create([
                'name' => $plan->name,
                'description' => $plan->description ?: null,
                'metadata' => ['platform_plan_id' => (string) $plan->id],
            ]);
            $productId = (string) $product->id;
        }

        $price = $this->trialStripe->prices->create([
            'product' => $productId,
            'currency' => strtolower($plan->currency ?: 'MYR'),
            'unit_amount' => (int) round(((float) $plan->price) * 100),
            'recurring' => [
                'interval' => $this->normaliseInterval($plan->billing_interval),
            ],
            'metadata' => ['platform_plan_id' => (string) $plan->id],
        ]);

        $plan->forceFill([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $price->id,
        ])->save();

        return (string) $price->id;
    }
}
