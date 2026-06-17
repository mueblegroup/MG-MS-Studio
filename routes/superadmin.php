<?php

use App\Http\Controllers\Superadmin\SuperadminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'central', 'role:superadmin'])
    ->prefix('superadmin')
    ->name('superadmin.')
    ->group(function () {
        Route::get('/dashboard', [SuperadminController::class, 'dashboard'])->name('dashboard');

        Route::get('/studios', [SuperadminController::class, 'studios'])->name('studios.index');
        Route::get('/studios/{studio}/edit', [SuperadminController::class, 'editStudio'])->name('studios.edit');
        Route::patch('/studios/{studio}', [SuperadminController::class, 'updateStudio'])->name('studios.update');

        Route::get('/users', [SuperadminController::class, 'users'])->name('users.index');
        Route::get('/domains', [SuperadminController::class, 'domains'])->name('domains.index');

        Route::get('/subscription-plans', [SuperadminController::class, 'plans'])->name('subscription-plans.index');
        Route::post('/subscription-plans', [SuperadminController::class, 'storePlan'])->name('subscription-plans.store');
        Route::patch('/subscription-plans/{plan}', [SuperadminController::class, 'updatePlan'])->name('subscription-plans.update');

        Route::get('/platform-payments', [SuperadminController::class, 'platformPayments'])->name('platform-payments.index');
    });
