<?php

namespace App\Providers;

use App\Http\Controllers\ClassController;
use App\Http\Controllers\FilteredClassController;
use App\Http\Controllers\GroupedShopController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StudioSettingsController;
use App\Http\Controllers\TimezoneStudioSettingsController;
use App\Http\Controllers\Customer\StudioOnboardingController;
use App\Http\Controllers\Customer\ValidatedStudioOnboardingController;
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
        $this->app->bind(ShopController::class, GroupedShopController::class);
        $this->app->bind(ClassController::class, FilteredClassController::class);
        $this->app->bind(StudioOnboardingController::class, ValidatedStudioOnboardingController::class);
        $this->app->bind(StudioSettingsController::class, TimezoneStudioSettingsController::class);
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
