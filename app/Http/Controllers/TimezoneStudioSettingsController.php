<?php

namespace App\Http\Controllers;

use App\Services\StudioSettingsService;
use App\Support\StudioLocaleOptions;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TimezoneStudioSettingsController extends StudioSettingsController
{
    public function edit(StudioSettingsService $settings)
    {
        $response = parent::edit($settings);
        $viewData = $response->getData();
        $data = $viewData['data'] ?? [];
        $envIssues = $viewData['envIssues'] ?? [];
        $studio = app(TenantManager::class)->current();

        $data['timezone'] = $settings->get(
            'timezone',
            data_get($studio?->settings, 'timezone', config('app.timezone', 'UTC'))
        );

        $timezoneOptions = StudioLocaleOptions::timezones();

        return view('admin.settings.studio-timezone', compact('data', 'envIssues', 'timezoneOptions'));
    }

    public function update(Request $request, StudioSettingsService $settings)
    {
        $validated = $request->validate([
            'timezone' => ['required', 'string', Rule::in(array_keys(StudioLocaleOptions::timezones()))],
        ]);

        $response = parent::update($request, $settings);
        $timezone = (string) $validated['timezone'];

        $settings->setMany([
            'timezone' => $timezone,
        ]);

        $studio = app(TenantManager::class)->current();
        if ($studio) {
            $studioSettings = (array) ($studio->settings ?? []);
            $studioSettings['timezone'] = $timezone;
            $studio->forceFill(['settings' => $studioSettings])->saveQuietly();
        }

        // Make the new timezone effective immediately for the redirect response
        // and subsequent tenant requests without requiring config cache clears.
        config([
            'app.timezone' => $timezone,
            'app.studio_timezone' => $timezone,
        ]);
        date_default_timezone_set($timezone);

        return $response;
    }
}
