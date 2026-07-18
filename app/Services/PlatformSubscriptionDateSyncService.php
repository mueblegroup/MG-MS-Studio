<?php

namespace App\Services;

use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class PlatformSubscriptionDateSyncService
{
    private ?StripeClient $stripe = null;

    public function sync(Studio $studio): Studio
    {
        if (! $studio->stripe_subscription_id) {
            return $studio;
        }

        $secret = (string) config('services.stripe.secret');
        if ($secret === '') {
            return $studio;
        }

        try {
            $subscription = $this->stripe($secret)->subscriptions->retrieve(
                $studio->stripe_subscription_id,
                ['expand' => ['items.data.price']]
            );

            $item = $subscription->items->data[0] ?? null;
            $priceId = is_string($item?->price ?? null)
                ? $item->price
                : ($item?->price?->id ?? null);

            $plan = $priceId
                ? PlatformSubscriptionPlan::query()->where('stripe_price_id', $priceId)->first()
                : null;

            if (! $plan && ! empty($subscription->metadata->plan_id)) {
                $plan = PlatformSubscriptionPlan::query()->find((int) $subscription->metadata->plan_id);
            }

            $periodEnd = $item?->current_period_end
                ?? $subscription->current_period_end
                ?? null;
            $trialEnd = $subscription->trial_end ?? null;
            $status = (string) ($subscription->status ?? $studio->subscription_status ?? 'incomplete');
            $serviceActive = in_array($status, ['active', 'trialing', 'past_due'], true);

            $studio->forceFill([
                'platform_subscription_plan_id' => $plan?->id ?? $studio->platform_subscription_plan_id,
                'plan_name' => $plan?->name ?? $studio->plan_name,
                'status' => $status === 'trialing' ? 'trial' : ($serviceActive ? 'active' : 'suspended'),
                'stripe_customer_id' => (string) ($subscription->customer ?? $studio->stripe_customer_id),
                'stripe_subscription_item_id' => $item?->id ?? $studio->stripe_subscription_item_id,
                'subscription_status' => $status,
                'trial_ends_at' => $trialEnd ? Carbon::createFromTimestamp((int) $trialEnd) : null,
                'subscription_ends_at' => $periodEnd ? Carbon::createFromTimestamp((int) $periodEnd) : $studio->subscription_ends_at,
                'cancel_at_period_end' => (bool) ($subscription->cancel_at_period_end ?? false),
                'canceled_at' => ! empty($subscription->canceled_at)
                    ? Carbon::createFromTimestamp((int) $subscription->canceled_at)
                    : null,
            ])->saveQuietly();
        } catch (\Throwable $exception) {
            Log::warning('Unable to refresh studio platform subscription dates from Stripe.', [
                'studio_id' => $studio->id,
                'stripe_subscription_id' => $studio->stripe_subscription_id,
                'message' => $exception->getMessage(),
            ]);
        }

        return $studio->refresh();
    }

    private function stripe(string $secret): StripeClient
    {
        return $this->stripe ??= new StripeClient($secret);
    }
}
