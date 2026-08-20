<?php

namespace App\Http\Middleware;

use App\Models\Studio;
use App\Models\StudioSetting;
use Closure;
use DateTimeZone;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ApplyOwnerStudioTimezone
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return $next($request);
        }

        $studio = Studio::query()
            ->where('owner_user_id', $user->id)
            ->latest('id')
            ->first();

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

        config([
            'app.timezone' => $timezone,
            'app.studio_timezone' => $timezone,
        ]);
        date_default_timezone_set($timezone);

        return $next($request);
    }
}
