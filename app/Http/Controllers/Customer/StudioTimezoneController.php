<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Models\StudioSetting;
use App\Support\StudioLocaleOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;

class StudioTimezoneController extends Controller
{
    public function update(Request $request, Studio $studio): RedirectResponse
    {
        abort_unless(
            $request->user()
                && $request->user()->role === 'admin'
                && (int) $studio->owner_user_id === (int) $request->user()->id,
            403,
            'Only the studio owner can change the studio timezone.'
        );

        $validated = $request->validate([
            'timezone' => ['required', 'string', Rule::in(array_keys(StudioLocaleOptions::timezones()))],
        ]);

        $timezone = (string) $validated['timezone'];
        $settings = (array) ($studio->settings ?? []);
        $settings['timezone'] = $timezone;

        $studio->forceFill(['settings' => $settings])->saveQuietly();

        StudioSetting::query()->updateOrCreate(
            [
                'studio_id' => $studio->id,
                'key' => 'timezone',
            ],
            ['value' => $timezone]
        );

        Cache::forget('studio_settings_all:'.$studio->id);

        return back()->with(
            'success',
            'Studio timezone updated to '.$timezone.'. Admin, teacher and student schedules will use this timezone.'
        );
    }
}
