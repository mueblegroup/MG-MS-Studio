<?php

namespace App\Services;

use App\Models\StudioPaymentGateway;
use App\Support\TenantManager;
use RuntimeException;

class StudioPaymentGatewayService
{
    public function current(string $provider): ?StudioPaymentGateway
    {
        $studioId = app(TenantManager::class)->id();
        if (! $studioId) {
            return null;
        }

        return StudioPaymentGateway::query()
            ->where('studio_id', $studioId)
            ->where('provider', strtolower($provider))
            ->first();
    }

    public function stripe(): array
    {
        $studioId = app(TenantManager::class)->id();
        if (! $studioId) {
            return [
                'secret' => (string) config('services.stripe.secret'),
                'key' => (string) config('services.stripe.key'),
                'webhook_secret' => (string) config('services.stripe.webhook_secret'),
                'environment' => 'platform',
            ];
        }

        $gateway = $this->requiredEnabled('stripe');
        $credentials = (array) $gateway->credentials;

        return [
            'secret' => (string) ($credentials['secret_key'] ?? ''),
            'key' => (string) ($credentials['publishable_key'] ?? ''),
            'webhook_secret' => (string) ($gateway->webhook_secret ?? ''),
            'environment' => (string) $gateway->environment,
        ];
    }

    public function hitpay(): array
    {
        $studioId = app(TenantManager::class)->id();
        if (! $studioId) {
            return [
                'api_key' => (string) config('services.hitpay.api_key'),
                'salt' => (string) config('services.hitpay.salt'),
                'event_webhook_salt_key' => (string) config('services.hitpay.event_webhook_salt_key'),
                'base_url' => rtrim((string) config('services.hitpay.base_url'), '/'),
                'environment' => 'platform',
            ];
        }

        $gateway = $this->requiredEnabled('hitpay');
        $credentials = (array) $gateway->credentials;
        $environment = (string) $gateway->environment;

        return [
            'api_key' => (string) ($credentials['api_key'] ?? ''),
            'salt' => (string) ($credentials['salt'] ?? ''),
            'event_webhook_salt_key' => (string) ($gateway->webhook_secret ?? ''),
            'base_url' => $environment === 'production'
                ? 'https://api.hit-pay.com/v1'
                : 'https://api.sandbox.hit-pay.com/v1',
            'environment' => $environment,
        ];
    }

    public function requiredEnabled(string $provider): StudioPaymentGateway
    {
        $studioId = app(TenantManager::class)->id();
        if (! $studioId) {
            throw new RuntimeException('A studio context is required for tenant payment gateway access.');
        }

        $gateway = StudioPaymentGateway::query()
            ->where('studio_id', $studioId)
            ->where('provider', strtolower($provider))
            ->first();

        if (! $gateway || ! $gateway->enabled) {
            throw new RuntimeException(ucfirst($provider).' is not configured for this studio. Ask the studio administrator to configure it under Payment Gateways.');
        }

        return $gateway;
    }
}
