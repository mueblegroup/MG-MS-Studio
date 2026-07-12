<?php

use App\Models\PlatformSubscriptionPlan;
use App\Models\Studio;
use App\Services\StudioSeatLimitService;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/admin/subscription/seat-usage', function (TenantManager $tenants, StudioSeatLimitService $limits) {
        $studioId = $tenants->id() ?: auth()->user()?->studio_id;
        abort_if(! $studioId, 403, 'Studio context is required.');

        $studio = Studio::query()->with('platformSubscriptionPlan')->findOrFail($studioId);

        return response()->json([
            'plan' => $studio->platformSubscriptionPlan?->name ?? $studio->plan_name ?? 'No plan assigned',
            'usage' => $limits->usage($studio),
            'upgrade_url' => route('admin.subscription.upgrade'),
        ]);
    })->name('admin.subscription.seat-usage');

    Route::get('/admin/subscription/upgrade', function (TenantManager $tenants, StudioSeatLimitService $limits) {
        $studioId = $tenants->id() ?: auth()->user()?->studio_id;
        abort_if(! $studioId, 403, 'Studio context is required.');

        $studio = Studio::query()->with('platformSubscriptionPlan')->findOrFail($studioId);
        $plans = PlatformSubscriptionPlan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        return view('admin.subscription-upgrade', [
            'studio' => $studio,
            'usage' => $limits->usage($studio),
            'plans' => $plans,
        ]);
    })->name('admin.subscription.upgrade');
});
