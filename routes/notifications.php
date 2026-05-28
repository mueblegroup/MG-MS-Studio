<?php

use App\Http\Controllers\Admin\AppNotificationController as AdminAppNotificationController;
use App\Http\Controllers\AppNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/{notification}', [AppNotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/{notification}/read', [AppNotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [AppNotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/notifications', [AdminAppNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications', [AdminAppNotificationController::class, 'store'])->name('notifications.store');
    });
