<?php

use App\Support\TenantManager;

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
