<?php

use App\Http\Controllers\Customer\CustomerAccountController;
use App\Http\Controllers\Customer\CustomerPortalController;
use Illuminate\Support\Facades\Route;

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

        Route::get('studios/create', [CustomerPortalController::class, 'createStudio'])->name('studios.create');
        Route::post('studios', [CustomerPortalController::class, 'storeStudio'])->name('studios.store');
        Route::get('studios/{studio}/open', [CustomerPortalController::class, 'launchStudio'])->name('studios.launch');
    });
