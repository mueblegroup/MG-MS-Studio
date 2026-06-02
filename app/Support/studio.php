<?php

use App\Services\StudioSettingsService;
use App\Support\TenantManager;

if (! function_exists('studio_setting')) {
    function studio_setting(string $key, $default = null) {
        return app(StudioSettingsService::class)->get($key, $default);
    }
}

if (! function_exists('current_studio')) {
    function current_studio(): ?\App\Models\Studio
    {
        return app(TenantManager::class)->current();
    }
}

if (! function_exists('current_studio_id')) {
    function current_studio_id(): ?int
    {
        return app(TenantManager::class)->id();
    }
}
