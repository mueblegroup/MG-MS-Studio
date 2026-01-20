<?php

use App\Services\StudioSettingsService;

if (! function_exists('studio_setting')) {
    function studio_setting(string $key, $default = null) {
        return app(StudioSettingsService::class)->get($key, $default);
    }
}
