<?php

namespace App\Observers;

use App\Models\Studio;
use App\Models\StudioSetting;
use Illuminate\Support\Facades\Cache;

class StudioObserver
{
    public function created(Studio $studio): void
    {
        $this->syncLocaleSettings($studio);
    }

    public function updated(Studio $studio): void
    {
        if ($studio->wasChanged('settings')) {
            $this->syncLocaleSettings($studio);
        }
    }

    private function syncLocaleSettings(Studio $studio): void
    {
        $settings = (array) ($studio->settings ?? []);

        foreach (['timezone', 'currency'] as $key) {
            $value = $settings[$key] ?? null;
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            StudioSetting::query()->updateOrCreate(
                [
                    'studio_id' => $studio->id,
                    'key' => $key,
                ],
                ['value' => $value]
            );
        }

        Cache::forget('studio_settings_all:'.$studio->id);
    }
}
