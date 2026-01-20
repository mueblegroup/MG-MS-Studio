<?php

namespace App\Services;

use App\Models\StudioSetting;
use Illuminate\Support\Facades\Cache;

class StudioSettingsService
{
    private const CACHE_KEY = 'studio_settings_all';
    private const CACHE_TTL_SECONDS = 3600;

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function () {
            return StudioSetting::query()
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function get(string $key, $default = null)
    {
        $all = $this->all();
        return array_key_exists($key, $all) ? $this->castValue($all[$key]) : $default;
    }

    public function setMany(array $keyValue): void
    {
        foreach ($keyValue as $key => $value) {
            StudioSetting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : (string) $value]
            );
        }

        Cache::forget(self::CACHE_KEY);
    }

    public function currency(string $default = 'MYR'): string
    {
        return (string) $this->get('currency', $default);
    }

    private function castValue(?string $value)
    {
        if ($value === null) return null;

        // JSON?
        $trim = ltrim($value);
        if ($trim !== '' && ($trim[0] === '{' || $trim[0] === '[')) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) return $decoded;
        }

        // booleans
        if ($value === 'true') return true;
        if ($value === 'false') return false;

        // numbers
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        return $value;
    }
}
