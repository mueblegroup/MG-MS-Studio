<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSsoProvider;
use App\Services\PlatformSsoService;
use Illuminate\View\View;

class PlatformSettingsController extends Controller
{
    public function index(): View
    {
        $providers = PlatformSsoProvider::query()
            ->whereIn('provider', PlatformSsoService::PROVIDERS)
            ->get()
            ->keyBy('provider');

        return view('superadmin.settings.index', [
            'providers' => $providers,
            'enabledSsoCount' => $providers->where('is_enabled', true)->count(),
        ]);
    }
}
