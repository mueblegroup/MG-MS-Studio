<?php

namespace App\Services;

use App\Models\StudioSetting;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Cache;

class StudioSettingsService
{
    private const CACHE_TTL_SECONDS = 3600;

    public function all(): array
    {
        $studioId = $this->studioId();

        $cacheKey = $studioId
            ? "studio_settings_all:{$studioId}"
            : 'studio_settings_all:global';

        return Cache::remember($cacheKey, self::CACHE_TTL_SECONDS, function () use ($studioId) {
            $query = StudioSetting::query();

            if ($studioId) {
                $query->where(function ($q) use ($studioId) {
                    $q->where('studio_id', $studioId)
                      ->orWhereNull('studio_id');
                });
            } else {
                $query->whereNull('studio_id');
            }

            return $query
                ->orderByRaw('studio_id is null desc')
                ->pluck('value', 'key')
                ->toArray();
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
            StudioSetting::updateOrCreate(
                [
                    'studio_id' => $studioId,
                    'key' => $key,
                ],
                [
                    'value' => is_array($value) ? json_encode($value) : (string) $value,
                ]
            );
        }

        Cache::forget($studioId ? "studio_settings_all:{$studioId}" : 'studio_settings_all:global');
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