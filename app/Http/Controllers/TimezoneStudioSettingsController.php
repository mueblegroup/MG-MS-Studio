<?php

namespace App\Http\Controllers;

use App\Services\StudioSettingsService;
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

        $data['timezone'] = $settings->get('timezone', config('app.timezone', 'UTC'));
        $timezoneOptions = $this->timezoneOptions();

        return view('admin.settings.studio-timezone', compact('data', 'envIssues', 'timezoneOptions'));
    }

    public function update(Request $request, StudioSettingsService $settings)
    {
        $request->validate([
            'timezone' => ['required', 'string', Rule::in(array_keys($this->timezoneOptions()))],
        ]);

        $response = parent::update($request, $settings);
        $settings->setMany([
            'timezone' => (string) $request->input('timezone'),
        ]);

        return $response;
    }

    private function timezoneOptions(): array
    {
        $now = now();
        $options = [];

        foreach (\DateTimeZone::listIdentifiers() as $timezone) {
            $date = $now->copy()->timezone($timezone);
            $offset = $date->format('P');
            $options[$timezone] = $timezone . ' (GMT' . $offset . ')';
        }

        return $options;
    }
}
