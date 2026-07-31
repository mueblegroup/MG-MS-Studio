<?php

namespace App\Services;

use App\Models\PlatformSsoProvider;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class PlatformSsoService
{
    public const PROVIDERS = ['google', 'microsoft', 'apple'];

    public function enabledProviders(): array
    {
        return PlatformSsoProvider::query()
            ->whereIn('provider', self::PROVIDERS)
            ->where('is_enabled', true)
            ->get()
            ->filter(function (PlatformSsoProvider $provider): bool {
                if (blank($provider->client_id) || blank($provider->client_secret)) {
                    return false;
                }

                return ! ($provider->provider === 'apple'
                    && $provider->secret_expires_at
                    && $provider->secret_expires_at->isPast());
            })
            ->keyBy('provider')
            ->all();
    }

    public function provider(string $provider): PlatformSsoProvider
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        $settings = PlatformSsoProvider::query()->where('provider', $provider)->first();

        if (! $settings || ! $settings->is_enabled) {
            throw new RuntimeException(ucfirst($provider).' login is currently disabled.');
        }

        if (blank($settings->client_id) || blank($settings->client_secret)) {
            throw new RuntimeException(ucfirst($provider).' login is not fully configured.');
        }

        if ($provider === 'apple' && $settings->secret_expires_at?->isPast()) {
            throw new RuntimeException('Apple login is temporarily unavailable because its client secret has expired.');
        }

        return $settings;
    }

    public function configure(string $provider): PlatformSsoProvider
    {
        $settings = $this->provider($provider);

        $configuration = [
            'client_id' => $settings->client_id,
            'client_secret' => $settings->client_secret,
            'redirect' => route('client-sso.callback', ['provider' => $provider]),
        ];

        if ($provider === 'microsoft' && filled($settings->tenant_id)) {
            $configuration['tenant'] = $settings->tenant_id;
        }

        Config::set('services.'.$provider, $configuration);

        return $settings;
    }
}
