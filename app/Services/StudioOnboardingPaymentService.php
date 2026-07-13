<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPayment;
use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Models\StudioDomain;
use App\Models\StudioOnboardingCheckout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\Checkout\Session;
use Stripe\Event;
use Stripe\StripeClient;

class StudioOnboardingPaymentService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $secret = (string) config('services.stripe.secret');

        if ($secret === '') {
            throw new RuntimeException('Stripe is not configured. Set STRIPE_SECRET before creating a studio.');
        }

        $this->stripe = new StripeClient($secret);
    }

    public function createCheckout(StudioOnboardingCheckout $onboarding, string $successUrl, string $cancelUrl): Session
    {
        $onboarding->loadMissing(['user', 'plan']);
        $plan = $onboarding->plan;

        if (! $plan || ! $plan->is_active) {
            throw new RuntimeException('The selected subscription plan is no longer available.');
        }

        $customer = $this->stripe->customers->create([
            'name' => $onboarding->studio_name,
            'email' => $onboarding->user?->email,
            'metadata' => [
                'onboarding_id' => (string) $onboarding->id,
                'owner_user_id' => (string) $onboarding->user_id,
            ],
        ]);

        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'subscription',
            'customer' => $customer->id,
            'line_items' => [[
                'price' => $this->ensureRecurringPrice($plan),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'billing_address_collection' => 'auto',
            'allow_promotion_codes' => true,
            'client_reference_id' => (string) $onboarding->id,
            'metadata' => [
                'onboarding_id' => (string) $onboarding->id,
                'plan_id' => (string) $plan->id,
                'owner_user_id' => (string) $onboarding->user_id,
            ],
            'subscription_data' => [
                'metadata' => [
                    'onboarding_id' => (string) $onboarding->id,
                    'plan_id' => (string) $plan->id,
                    'owner_user_id' => (string) $onboarding->user_id,
                ],
            ],
            'expires_at' => now()->addMinutes(30)->timestamp,
        ]);

        $onboarding->forceFill([
            'stripe_checkout_session_id' => (string) $session->id,
            'stripe_customer_id' => (string) $customer->id,
            'status' => 'checkout_created',
            'expires_at' => now()->addMinutes(30),
            'failure_reason' => null,
        ])->save();

        return $session;
    }

    public function handleEvent(Event $event): bool
    {
        if (! in_array($event->type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return false;
        }

        $session = $event->data->object;

        if (($session->mode ?? null) !== 'subscription' || empty($session->subscription)) {
            return false;
        }

        $onboardingId = (int) ($session->metadata->onboarding_id ?? $session->client_reference_id ?? 0);

        if ($onboardingId < 1) {
            return false;
        }

        $this->fulfillSession((string) $session->id);

        return true;
    }

    public function fulfillSession(string $sessionId, ?int $expectedUserId = null): Studio
    {
        $session = $this->stripe->checkout->sessions->retrieve($sessionId, [
            'expand' => ['subscription', 'subscription.latest_invoice'],
        ]);

        $onboardingId = (int) ($session->metadata->onboarding_id ?? $session->client_reference_id ?? 0);
        $onboarding = StudioOnboardingCheckout::query()->find($onboardingId);

        if (! $onboarding || $onboarding->stripe_checkout_session_id !== $sessionId) {
            throw new RuntimeException('The studio checkout record could not be verified.');
        }

        if ($expectedUserId !== null && (int) $onboarding->user_id !== $expectedUserId) {
            throw new RuntimeException('This checkout belongs to another account.');
        }

        if (($session->payment_status ?? null) !== 'paid') {
            throw new RuntimeException('The initial subscription payment has not been confirmed by Stripe.');
        }

        if (empty($session->subscription)) {
            throw new RuntimeException('Stripe did not create a subscription for this payment.');
        }

        $subscription = is_string($session->subscription)
            ? $this->stripe->subscriptions->retrieve($session->subscription, ['expand' => ['items.data.price', 'latest_invoice']])
            : $session->subscription;

        return DB::transaction(function () use ($onboarding, $session, $subscription): Studio {
            $locked = StudioOnboardingCheckout::query()->lockForUpdate()->findOrFail($onboarding->id);

            $existing = Studio::query()->where('owner_user_id', $locked->user_id)->first();

            if ($existing) {
                $locked->forceFill([
                    'status' => 'completed',
                    'completed_at' => $locked->completed_at ?: now(),
                ])->save();

                return $existing;
            }

            if ($locked->completed_at) {
                throw new RuntimeException('This checkout was already completed, but its studio could not be found.');
            }

            $plan = PlatformSubscriptionPlan::query()->findOrFail($locked->platform_subscription_plan_id);
            $user = $locked->user()->lockForUpdate()->firstOrFail();

            if ($user->studio_id) {
                throw new RuntimeException('This account is already assigned to a studio.');
            }

            if (Studio::query()->where('subdomain', $locked->subdomain)->exists()) {
                throw new RuntimeException('The selected subdomain is no longer available. Contact support to complete or refund this payment.');
            }

            $item = $subscription->items->data[0] ?? null;
            $periodEnd = $item?->current_period_end ?? $subscription->current_period_end ?? null;
            $stripeStatus = (string) ($subscription->status ?? 'active');
            $studioStatus = in_array($stripeStatus, ['active', 'trialing', 'past_due'], true) ? 'active' : 'suspended';

            $studio = Studio::create([
                'name' => $locked->studio_name,
                'slug' => Str::slug($locked->studio_name) . '-' . Str::lower(Str::random(5)),
                'subdomain' => $locked->subdomain,
                'owner_user_id' => $user->id,
                'status' => $studioStatus,
                'plan_name' => $plan->name,
                'platform_subscription_plan_id' => $plan->id,
                'stripe_customer_id' => (string) $session->customer,
                'stripe_subscription_id' => (string) $subscription->id,
                'stripe_subscription_item_id' => $item?->id,
                'subscription_status' => $stripeStatus,
                'subscription_ends_at' => $periodEnd ? Carbon::createFromTimestamp((int) $periodEnd) : null,
                'cancel_at_period_end' => (bool) ($subscription->cancel_at_period_end ?? false),
                'settings' => [
                    'timezone' => $locked->timezone ?: config('app.timezone'),
                    'currency' => strtoupper($locked->currency ?: 'MYR'),
                ],
            ]);

            StudioDomain::create([
                'studio_id' => $studio->id,
                'domain' => $locked->subdomain . '.' . strtolower((string) config('saas.root_domain')),
                'type' => 'subdomain',
                'is_primary' => true,
                'is_verified' => true,
                'verified_at' => now(),
            ]);

            $user->forceFill([
                'studio_id' => $studio->id,
                'role' => 'admin',
            ])->save();

            $locked->forceFill([
                'stripe_customer_id' => (string) $session->customer,
                'stripe_subscription_id' => (string) $subscription->id,
                'status' => 'completed',
                'completed_at' => now(),
                'failure_reason' => null,
            ])->save();

            $this->stripe->customers->update((string) $session->customer, [
                'metadata' => [
                    'studio_id' => (string) $studio->id,
                    'owner_user_id' => (string) $user->id,
                    'onboarding_id' => (string) $locked->id,
                ],
            ]);

            $this->stripe->subscriptions->update((string) $subscription->id, [
                'metadata' => [
                    'studio_id' => (string) $studio->id,
                    'plan_id' => (string) $plan->id,
                    'owner_user_id' => (string) $user->id,
                    'onboarding_id' => (string) $locked->id,
                ],
            ]);

            $this->recordInitialInvoice($studio, $plan, $subscription->latest_invoice ?? null);

            return $studio;
        });
    }

    public function markCheckoutFailed(StudioOnboardingCheckout $onboarding, string $reason): void
    {
        $onboarding->forceFill([
            'status' => 'failed',
            'failure_reason' => Str::limit($reason, 2000),
        ])->save();
    }

    private function ensureRecurringPrice(PlatformSubscriptionPlan $plan): string
    {
        if ($plan->stripe_price_id) {
            return (string) $plan->stripe_price_id;
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
            'recurring' => ['interval' => $this->stripeInterval($plan->billing_interval)],
            'metadata' => ['platform_plan_id' => (string) $plan->id],
        ]);

        $plan->forceFill([
            'stripe_product_id' => $productId,
            'stripe_price_id' => (string) $price->id,
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

    private function recordInitialInvoice(Studio $studio, PlatformSubscriptionPlan $plan, mixed $invoice): void
    {
        if (! $invoice) {
            return;
        }

        if (is_string($invoice)) {
            $invoice = $this->stripe->invoices->retrieve($invoice);
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
                'platform_subscription_plan_id' => $plan->id,
                'amount' => ((int) ($invoice->amount_paid ?? $invoice->amount_due ?? 0)) / 100,
                'currency' => strtoupper((string) ($invoice->currency ?? $plan->currency ?? 'MYR')),
                'billing_interval' => $plan->billing_interval,
                'paid_at' => ! empty($invoice->paid) ? now() : null,
                'period_start' => ! empty($period?->start) ? Carbon::createFromTimestamp((int) $period->start) : null,
                'period_end' => ! empty($period?->end) ? Carbon::createFromTimestamp((int) $period->end) : null,
                'status' => ! empty($invoice->paid) ? 'paid' : 'pending',
                'metadata' => [
                    'hosted_invoice_url' => $invoice->hosted_invoice_url ?? null,
                    'invoice_pdf' => $invoice->invoice_pdf ?? null,
                    'billing_reason' => $invoice->billing_reason ?? 'subscription_create',
                ],
            ]
        );
    }
}
