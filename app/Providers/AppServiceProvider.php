<?php

namespace App\Providers;

use App\Support\TenantManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $userKey = optional($request->user())->id;

            return Limit::perMinute(120)->by($userKey ?: $request->ip());
        });

        Route::middleware('web')->group(base_path('routes/seat-limits.php'));
    }
}
