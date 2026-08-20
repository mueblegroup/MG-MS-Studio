<?php

namespace App\Http\Middleware;

use App\Models\StudioSetting;
use App\Support\TenantManager;
use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApplyStudioTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $studio = app(TenantManager::class)->current();

        if (! $studio) {
            return $next($request);
        }

        $timezone = StudioSetting::query()
            ->where('studio_id', $studio->id)
            ->where('key', 'timezone')
            ->value('value');

        $timezone = is_string($timezone) && $timezone !== ''
            ? $timezone
            : (string) data_get($studio->settings, 'timezone', config('app.timezone', 'UTC'));

        try {
            new DateTimeZone($timezone);
        } catch (Throwable) {
            $timezone = 'UTC';
        }

        // The selected owner timezone is the canonical tenant timezone for the
        // current request. This keeps schedules, billing cutoffs, reminders,
        // Carbon parsing and rendered dates consistent for admins, teachers
        // and students inside the studio workspace.
        config([
            'app.timezone' => $timezone,
            'app.studio_timezone' => $timezone,
        ]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
