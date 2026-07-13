<?php

use App\Http\Controllers\AppNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/read-all', [AppNotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [AppNotificationController::class, 'show'])->name('notifications.show');
    Route::patch('/notifications/{notification}/read', [AppNotificationController::class, 'markRead'])->name('notifications.read');
});
