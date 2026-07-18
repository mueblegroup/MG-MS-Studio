<?php

use App\Http\Controllers\PlatformMessageController;
use App\Http\Controllers\Superadmin\AuditLogController;
use App\Http\Controllers\Superadmin\DomainController;
use App\Http\Controllers\Superadmin\PlanTrialController;
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
        Route::get('/domains', [DomainController::class, 'index'])->name('domains.index');
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/subscription-plans', [SuperadminController::class, 'plans'])->name('subscription-plans.index');
        Route::post('/subscription-plans', [SuperadminController::class, 'storePlan'])->name('subscription-plans.store');
        Route::patch('/subscription-plans/{plan}', [SuperadminController::class, 'updatePlan'])->name('subscription-plans.update');
        Route::patch('/subscription-plans/{plan}/trial', [PlanTrialController::class, 'update'])->name('subscription-plans.trial.update');

        Route::get('/platform-payments', [SuperadminController::class, 'platformPayments'])->name('platform-payments.index');

        Route::get('/messages', [PlatformMessageController::class, 'index'])->name('messages.index');
        Route::post('/messages', [PlatformMessageController::class, 'store'])->name('messages.store');
        Route::get('/messages/{message}', [PlatformMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/read-all', [PlatformMessageController::class, 'markAllRead'])->name('messages.read-all');
    });
