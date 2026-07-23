<?php

use App\Http\Controllers\Customer\CustomerAccountController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\Customer\PlatformBillingController;
use App\Http\Controllers\Customer\StudioOnboardingController;
use App\Http\Controllers\Customer\StudioRegistrationSettingsController;
use App\Http\Controllers\PlatformMessageController;
use App\Models\PlatformSubscriptionPlan;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/platform-stripe', [PlatformBillingController::class, 'webhook'])
    ->name('webhooks.platform-stripe');

Route::middleware('central')->group(function (): void {
    Route::view('features', 'saas.marketing.features')->name('marketing.features');
    Route::view('solutions', 'saas.marketing.solutions')->name('marketing.solutions');
    Route::view('platform', 'saas.marketing.platform')->name('marketing.platform');
    Route::view('security', 'saas.marketing.security')->name('marketing.security');
    Route::get('pricing', function () {
        return view('saas.marketing.pricing', [
            'plans' => PlatformSubscriptionPlan::query()
                ->where('is_active', true)
                ->where('price', '>', 0)
                ->orderBy('sort_order')
                ->orderBy('price')
                ->get(),
        ]);
    })->name('marketing.pricing');
});

Route::get('/', function () {
    if (! auth()->check()) {
        return view('saas.marketing.home');
    }

    if (auth()->user()->role === 'superadmin') {
        return redirect()->route('superadmin.dashboard');
    }

    return redirect()->route('customer.dashboard');
})->middleware('central');

Route::middleware(['auth', 'central'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get('dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('studio', [CustomerPortalController::class, 'studio'])->name('studio');
        Route::patch('studio/{studio}/registration-settings', [StudioRegistrationSettingsController::class, 'update'])->name('studio.registration-settings.update');
        Route::get('billing', [CustomerPortalController::class, 'billing'])->name('billing');
        Route::get('invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::get('account', [CustomerAccountController::class, 'edit'])->name('account');

        Route::get('messages', [PlatformMessageController::class, 'index'])->name('messages.index');
        Route::post('messages', [PlatformMessageController::class, 'store'])->name('messages.store');
        Route::get('messages/{message}', [PlatformMessageController::class, 'show'])->name('messages.show');
        Route::post('messages/read-all', [PlatformMessageController::class, 'markAllRead'])->name('messages.read-all');

        Route::post('billing/checkout/{plan}', [PlatformBillingController::class, 'checkout'])->name('billing.checkout');
        Route::get('billing/upgrade/{plan}/confirm', [PlatformBillingController::class, 'confirmUpgrade'])->name('billing.upgrade.confirm');
        Route::post('billing/upgrade/{plan}', [PlatformBillingController::class, 'upgrade'])->name('billing.upgrade');
        Route::post('billing/cancel', [PlatformBillingController::class, 'cancel'])->name('billing.cancel');
        Route::post('billing/resume', [PlatformBillingController::class, 'resume'])->name('billing.resume');
        Route::post('billing/portal', [PlatformBillingController::class, 'portal'])->name('billing.portal');

        Route::get('studios/create', [StudioOnboardingController::class, 'create'])->name('studios.create');
        Route::post('studios', [StudioOnboardingController::class, 'store'])->name('studios.store');
        Route::get('studios/payment-success', [StudioOnboardingController::class, 'paymentSuccess'])->name('studios.payment-success');
        Route::get('studios/{studio}/open', [CustomerPortalController::class, 'launchStudio'])->name('studios.launch');
    });
