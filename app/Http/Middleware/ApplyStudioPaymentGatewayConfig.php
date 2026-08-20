<?php

namespace App\Http\Middleware;

use App\Models\StudioPaymentGateway;
use App\Services\StudioSettingsService;
use App\Support\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyStudioPaymentGatewayConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        $studioId = app(TenantManager::class)->id();

        if (! $studioId) {
            return $next($request);
        }

        $gateways = StudioPaymentGateway::query()
            ->where('studio_id', $studioId)
            ->whereIn('provider', ['stripe', 'hitpay'])
            ->get()
            ->keyBy('provider');

        $stripe = $gateways->get('stripe');
        $stripeCredentials = $stripe && $stripe->enabled ? (array) $stripe->credentials : [];

        config([
            'services.stripe.key' => (string) ($stripeCredentials['publishable_key'] ?? ''),
            'services.stripe.secret' => (string) ($stripeCredentials['secret_key'] ?? ''),
            'services.stripe.webhook_secret' => $stripe && $stripe->enabled ? (string) ($stripe->webhook_secret ?? '') : '',
        ]);

        $hitpay = $gateways->get('hitpay');
        $hitpayCredentials = $hitpay && $hitpay->enabled ? (array) $hitpay->credentials : [];
        $hitpayEnvironment = $hitpay?->environment === 'production' ? 'production' : 'sandbox';

        config([
            'services.hitpay.api_key' => (string) ($hitpayCredentials['api_key'] ?? ''),
            'services.hitpay.salt' => (string) ($hitpayCredentials['salt'] ?? ''),
            'services.hitpay.event_webhook_salt_key' => $hitpay && $hitpay->enabled ? (string) ($hitpay->webhook_secret ?? '') : '',
            'services.hitpay.base_url' => $hitpayEnvironment === 'production'
                ? 'https://api.hit-pay.com/v1'
                : 'https://api.sandbox.hit-pay.com/v1',
        ]);

        // Keep the studio's selected provider aligned with an actually usable
        // gateway. This repairs older tenants where HitPay/Stripe was enabled
        // but default_payment_provider still pointed to the unavailable one.
        $ready = [
            'stripe' => $this->stripeReady($stripe, $stripeCredentials),
            'hitpay' => $this->hitpayReady($hitpay, $hitpayCredentials),
        ];

        $settings = app(StudioSettingsService::class);
        $selected = strtolower((string) $settings->get('default_payment_provider', 'stripe'));

        if (! ($ready[$selected] ?? false)) {
            $fallback = collect(['stripe', 'hitpay'])->first(fn (string $provider) => $ready[$provider]);
            if ($fallback) {
                $settings->setMany(['default_payment_provider' => $fallback]);
            }
        }

        return $next($request);
    }

    private function stripeReady(?StudioPaymentGateway $gateway, array $credentials): bool
    {
        return (bool) ($gateway?->enabled)
            && filled($credentials['publishable_key'] ?? null)
            && filled($credentials['secret_key'] ?? null)
            && filled($gateway?->webhook_secret);
    }

    private function hitpayReady(?StudioPaymentGateway $gateway, array $credentials): bool
    {
        return (bool) ($gateway?->enabled)
            && filled($credentials['api_key'] ?? null)
            && (filled($credentials['salt'] ?? null) || filled($gateway?->webhook_secret));
    }
}
