<?php

use App\Http\Controllers\Customer\CustomerAccountController;
use App\Http\Controllers\Customer\CustomerPortalController;
use App\Http\Controllers\Customer\PlatformBillingController;
use Illuminate\Support\Facades\Route;

Route::post('webhooks/platform-stripe', [PlatformBillingController::class, 'webhook'])
    ->middleware('central')
    ->name('webhooks.platform-stripe');

Route::get('/', function () {
    if (! auth()->check()) {
        return view('saas.landing');
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
        Route::get('billing', [CustomerPortalController::class, 'billing'])->name('billing');
        Route::get('invoices', [CustomerPortalController::class, 'invoices'])->name('invoices');
        Route::get('account', [CustomerAccountController::class, 'edit'])->name('account');

        Route::post('billing/checkout/{plan}', [PlatformBillingController::class, 'checkout'])->name('billing.checkout');
        Route::post('billing/upgrade/{plan}', [PlatformBillingController::class, 'upgrade'])->name('billing.upgrade');
        Route::post('billing/cancel', [PlatformBillingController::class, 'cancel'])->name('billing.cancel');
        Route::post('billing/resume', [PlatformBillingController::class, 'resume'])->name('billing.resume');
        Route::post('billing/portal', [PlatformBillingController::class, 'portal'])->name('billing.portal');

        Route::get('studios/create', [CustomerPortalController::class, 'createStudio'])->name('studios.create');
        Route::post('studios', [CustomerPortalController::class, 'storeStudio'])->name('studios.store');
        Route::get('studios/{studio}/open', [CustomerPortalController::class, 'launchStudio'])->name('studios.launch');
    });
