<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use Carbon\Carbon;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\StripeClient;

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
                'interval' => match (strtolower((string) $plan->billing_interval)) {
                    'day', 'daily' => 'day',
                    'week', 'weekly' => 'week',
                    'year', 'yearly', 'annual', 'annually' => 'year',
                    default => 'month',
                },
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
