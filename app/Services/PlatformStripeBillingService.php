<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPayment;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\StripeClient;
use Stripe\Webhook;

class PlatformStripeBillingService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET before using platform billing.');
        }

        $this->stripe = new StripeClient($secret);
    }

    public function createCheckoutSession(Studio $studio, PlatformSubscriptionPlan $plan, string $successUrl, string $cancelUrl): Session
    {
        $customerId = $this->ensureCustomer($studio);
        $priceId = $this->ensureRecurringPrice($plan);

        return $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customerId,
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?checkout=success&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl . '?checkout=cancelled',
            'allow_promotion_codes' => true,
            'billing_address_collection' => 'auto',
            'client_reference_id' => (string) $studio->id,
            'metadata' => [
                'studio_id' => (string) $studio->id,
                'plan_id' => (string) $plan->id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'studio_id' => (string) $studio->id,
                    'plan_id' => (string) $plan->id,
                ],
            ],
        ]);
    }

    public function upgrade(Studio $studio, PlatformSubscriptionPlan $targetPlan): array
    {
        if (! $studio->stripe_subscription_id) {
            throw new RuntimeException('No active Stripe subscription was found for this studio.');
        }

        $currentPlan = $studio->platformSubscriptionPlan;

        if ($currentPlan && (float) $targetPlan->price <= (float) $currentPlan->price) {
            throw new RuntimeException('This action only supports immediate upgrades to a higher-priced plan.');
        }

        $subscription = $this->stripe->subscriptions->retrieve($studio->stripe_subscription_id, [
            'expand' => ['items.data.price', 'latest_invoice'],
        ]);

        $item = $subscription->items->data[0] ?? null;

        if (! $item) {
            throw new RuntimeException('The Stripe subscription has no billable item.');
        }

        $priceId = $this->ensureRecurringPrice($targetPlan);
        $prorationDate = now()->timestamp;

        $preview = $this->stripe->invoices->createPreview([
            'customer' => $studio->stripe_customer_id,
            'subscription' => $subscription->id,
            'subscription_details' => [
                'items' => [[
                    'id' => $item->id,
                    'price' => $priceId,
                ]],
                'proration_date' => $prorationDate,
            ],
        ]);

        $updated = $this->stripe->subscriptions->update($subscription->id, [
            'items' => [[
                'id' => $item->id,
                'price' => $priceId,
            ]],
            'proration_behavior' => 'always_invoice',
            'proration_date' => $prorationDate,
            'payment_behavior' => 'pending_if_incomplete',
            'cancel_at_period_end' => false,
            'metadata' => [
                'studio_id' => (string) $studio->id,
                'plan_id' => (string) $targetPlan->id,
            ],
            'expand' => ['latest_invoice'],
        ]);

        $this->syncSubscription($updated);

        return [
            'amount_due' => ((int) $preview->amount_due) / 100,
            'currency' => strtoupper((string) $preview->currency),
            'subscription_status' => (string) $updated->status,
        ];
    }

    public function cancelAtPeriodEnd(Studio $studio): void
    {
        if (! $studio->stripe_subscription_id) {
            throw new RuntimeException('No active Stripe subscription was found for this studio.');
        }

        $subscription = $this->stripe->subscriptions->update($studio->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $this->syncSubscription($subscription);
    }

    public function resume(Studio $studio): void
    {
        if (! $studio->stripe_subscription_id) {
            throw new RuntimeException('No Stripe subscription was found for this studio.');
        }

        $subscription = $this->stripe->subscriptions->update($studio->stripe_subscription_id, [
            'cancel_at_period_end' => false,
        ]);

        $this->syncSubscription($subscription);
    }

    public function createBillingPortalSession(Studio $studio, string $returnUrl): string
    {
        $customerId = $this->ensureCustomer($studio);
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return (string) $session->url;
    }

    public function constructWebhookEvent(string $payload, ?string $signature): Event
    {
        $secret = (string) config('services.stripe.webhook_secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe webhook signing secret is not configured.');
        }

        return Webhook::constructEvent($payload, (string) $signature, $secret);
    }

    public function handleWebhook(Event $event): void
    {
        $object = $event->data->object;

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($object),
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->syncSubscription($object),
            'invoice.paid',
            'invoice.payment_failed' => $this->syncInvoice($object, $event->type),
            default => null,
        };
    }

    private function ensureCustomer(Studio $studio): string
    {
        if ($studio->stripe_customer_id) {
            return $studio->stripe_customer_id;
        }

        $studio->loadMissing('owner');
        $customer = $this->stripe->customers->create([
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

    private function ensureRecurringPrice(PlatformSubscriptionPlan $plan): string
    {
        if ($plan->stripe_price_id) {
            return $plan->stripe_price_id;
        }

        $productId = $plan->stripe_product_id;

        if (! $productId) {
            $product = $this->stripe->products->create([
                'name' => $plan->name,
                'description' => $plan->description ?: null,
                'metadata' => ['platform_plan_id' => (string) $plan->id],
            ]);
            $productId = (string) $product->id;
        }

        $price = $this->stripe->prices->create([
            'product' => $productId,
            'currency' => strtolower($plan->currency ?: 'MYR'),
            'unit_amount' => (int) round(((float) $plan->price) * 100),
            'recurring' => [
                'interval' => $this->stripeInterval($plan->billing_interval),
            ],
            'metadata' => ['platform_plan_id' => (string) $plan->id],
        ]);

        $plan->forceFill([
            'stripe_product_id' => $productId,
            'stripe_price_id' => $price->id,
        ])->save();

        return (string) $price->id;
    }

    private function stripeInterval(?string $interval): string
    {
        return match (strtolower((string) $interval)) {
            'day', 'daily' => 'day',
            'week', 'weekly' => 'week',
            'year', 'yearly', 'annual', 'annually' => 'year',
            default => 'month',
        };
    }

    private function handleCheckoutCompleted(object $session): void
    {
        if (($session->mode ?? null) !== 'subscription' || empty($session->subscription)) {
            return;
        }

        $studioId = (int) ($session->metadata->studio_id ?? $session->client_reference_id ?? 0);
        $studio = Studio::query()->find($studioId);

        if (! $studio) {
            return;
        }

        $studio->forceFill([
            'stripe_customer_id' => (string) $session->customer,
            'stripe_subscription_id' => (string) $session->subscription,
        ])->save();

        $subscription = $this->stripe->subscriptions->retrieve((string) $session->subscription, [
            'expand' => ['items.data.price'],
        ]);

        $this->syncSubscription($subscription);
    }

    private function syncSubscription(object $subscription): void
    {
        $studioId = (int) ($subscription->metadata->studio_id ?? 0);
        $studio = $studioId
            ? Studio::query()->find($studioId)
            : Studio::query()->where('stripe_subscription_id', $subscription->id)->first();

        if (! $studio) {
            return;
        }

        $planId = (int) ($subscription->metadata->plan_id ?? 0);
        $plan = $planId ? PlatformSubscriptionPlan::query()->find($planId) : null;
        $item = $subscription->items->data[0] ?? null;
        $periodStart = $item?->current_period_start ?? $subscription->current_period_start ?? null;
        $periodEnd = $item?->current_period_end ?? $subscription->current_period_end ?? null;
        $status = (string) ($subscription->status ?? 'incomplete');
        $serviceActive = in_array($status, ['active', 'trialing', 'past_due'], true);

        $studio->forceFill([
            'platform_subscription_plan_id' => $plan?->id ?? $studio->platform_subscription_plan_id,
            'plan_name' => $plan?->name ?? $studio->plan_name,
            'status' => $status === 'trialing' ? 'trial' : ($serviceActive ? 'active' : 'suspended'),
            'stripe_customer_id' => (string) ($subscription->customer ?? $studio->stripe_customer_id),
            'stripe_subscription_id' => (string) $subscription->id,
            'stripe_subscription_item_id' => $item?->id,
            'subscription_status' => $status,
            'subscription_ends_at' => $periodEnd ? Carbon::createFromTimestamp((int) $periodEnd) : $studio->subscription_ends_at,
            'cancel_at_period_end' => (bool) ($subscription->cancel_at_period_end ?? false),
            'canceled_at' => ! empty($subscription->canceled_at) ? Carbon::createFromTimestamp((int) $subscription->canceled_at) : null,
        ])->save();
    }

    private function syncInvoice(object $invoice, string $eventType): void
    {
        $subscriptionId = is_string($invoice->subscription ?? null)
            ? $invoice->subscription
            : ($invoice->subscription->id ?? null);

        $studio = Studio::query()
            ->when($subscriptionId, fn ($query) => $query->where('stripe_subscription_id', $subscriptionId))
            ->when(! $subscriptionId && ! empty($invoice->customer), fn ($query) => $query->where('stripe_customer_id', $invoice->customer))
            ->first();

        if (! $studio) {
            return;
        }

        $line = $invoice->lines->data[0] ?? null;
        $period = $line?->period;

        PlatformSubscriptionPayment::query()->updateOrCreate(
            [
                'provider' => 'stripe',
                'reference' => (string) $invoice->id,
            ],
            [
                'studio_id' => $studio->id,
                'platform_subscription_plan_id' => $studio->platform_subscription_plan_id,
                'amount' => ((int) ($invoice->amount_paid ?? $invoice->amount_due ?? 0)) / 100,
                'currency' => strtoupper((string) ($invoice->currency ?? 'MYR')),
                'billing_interval' => $studio->platformSubscriptionPlan?->billing_interval,
                'paid_at' => $eventType === 'invoice.paid' ? now() : null,
                'period_start' => ! empty($period?->start) ? Carbon::createFromTimestamp((int) $period->start) : null,
                'period_end' => ! empty($period?->end) ? Carbon::createFromTimestamp((int) $period->end) : null,
                'status' => $eventType === 'invoice.paid' ? 'paid' : 'failed',
                'metadata' => [
                    'hosted_invoice_url' => $invoice->hosted_invoice_url ?? null,
                    'invoice_pdf' => $invoice->invoice_pdf ?? null,
                    'billing_reason' => $invoice->billing_reason ?? null,
                ],
            ]
        );
    }
}
