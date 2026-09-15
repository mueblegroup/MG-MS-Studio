<?php

use App\Http\Controllers\Admin\PendingOrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/pending-orders', [PendingOrderController::class, 'index'])->name('pending-orders.index');
    Route::post('/pending-orders/{order}/cancel', [PendingOrderController::class, 'cancel'])->name('pending-orders.cancel');
});
