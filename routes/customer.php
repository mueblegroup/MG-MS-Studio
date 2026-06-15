<?php

use App\Http\Controllers\Customer\CustomerPortalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function (): void {
        Route::get('dashboard', [CustomerPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('studios/create', [CustomerPortalController::class, 'createStudio'])->name('studios.create');
        Route::post('studios', [CustomerPortalController::class, 'storeStudio'])->name('studios.store');
        Route::get('studios/{studio}/open', [CustomerPortalController::class, 'launchStudio'])->name('studios.launch');
    });
