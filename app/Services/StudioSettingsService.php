<?php

namespace App\Services;

use App\Models\StudioSetting;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class StudioSettingsService
{
    private const TTL = 3600;
    private const ENCRYPTED_PREFIX = 'enc:';

    public function all(): array
    {
        $studioId = $this->studioId();

        return Cache::remember($this->cacheKey($studioId), self::TTL, function () use ($studioId) {
            $query = StudioSetting::query();

            if ($studioId) {
                $query->where(function ($q) use ($studioId) {
                    $q->where('studio_id', $studioId)->orWhereNull('studio_id');
                });
            } else {
                $query->whereNull('studio_id');
            }

            $values = $query
                ->orderByRaw('studio_id is null desc')
                ->pluck('value', 'key')
                ->toArray();

            if (array_key_exists('mail_password', $values)) {
                $values['mail_password'] = $this->decryptSensitiveValue((string) $values['mail_password']);
            }

            return $values;
        });
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();

        return array_key_exists($key, $all)
            ? $this->castValue($all[$key])
            : $default;
    }

    public function setMany(array $keyValue): void
    {
        $studioId = $this->studioId();

        foreach ($keyValue as $key => $value) {
            $storedValue = is_array($value) ? json_encode($value) : (string) $value;

            if ($key === 'mail_password' && $storedValue !== '') {
                $storedValue = self::ENCRYPTED_PREFIX . Crypt::encryptString($storedValue);
            }

            StudioSetting::updateOrCreate(
                [
                    'studio_id' => $studioId,
                    'key' => $key,
                ],
                [
                    'value' => $storedValue,
                ]
            );
        }

        Cache::forget($this->cacheKey($studioId));
    }

    public function currency(string $default = 'MYR'): string
    {
        return (string) $this->get('currency', $default);
    }

    public function defaultPaymentProvider(): string
    {
        return (string) $this->get('default_payment_provider', 'stripe');
    }

    private function studioId(): ?int
    {
        return app(TenantManager::class)->id();
    }

    private function cacheKey(?int $studioId): string
    {
        return $studioId
            ? 'studio_settings_all:' . $studioId
            : 'studio_settings_all:global';
    }

    private function decryptSensitiveValue(string $value): string
    {
        if (! str_starts_with($value, self::ENCRYPTED_PREFIX)) {
            // Backward compatibility for credentials saved before encryption
            // was introduced. They are encrypted the next time settings save.
            return $value;
        }

        try {
            return Crypt::decryptString(substr($value, strlen(self::ENCRYPTED_PREFIX)));
        } catch (Throwable $exception) {
            report($exception);
            return '';
        }
    }

    private function castValue(?string $value)
    {
        if ($value === null) {
            return null;
        }

        $trim = ltrim($value);

        if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        if ($value === 'true') {
            return true;
        }

        if ($value === 'false') {
            return false;
        }

        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }
}
