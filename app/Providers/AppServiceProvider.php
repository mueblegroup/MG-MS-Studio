<?php

namespace App\Providers;

use App\Models\ClassModel;
use App\Models\StudioSubscription;
use App\Observers\ClassModelObserver;
use App\Observers\StudioSubscriptionObserver;
use App\Services\PlatformStripeBillingService;
use App\Services\ReliableSubscriptionClassService;
use App\Services\SubscriptionClassService;
use App\Services\TrialAwarePlatformStripeBillingService;
use App\Support\TenantManager;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });

        $this->app->bind(PlatformStripeBillingService::class, TrialAwarePlatformStripeBillingService::class);
        $this->app->bind(SubscriptionClassService::class, ReliableSubscriptionClassService::class);
    }

    public function boot(): void
    {
        ClassModel::observe(ClassModelObserver::class);
        StudioSubscription::observe(StudioSubscriptionObserver::class);

        RateLimiter::for('api', function (Request $request) {
            $userKey = optional($request->user())->id;

            return Limit::perMinute(120)->by($userKey ?: $request->ip());
        });

        require base_path('routes/seat-limits.php');
    }
}
