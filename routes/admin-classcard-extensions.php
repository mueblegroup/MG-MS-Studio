<?php

use App\Http\Controllers\UserClassCardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::post(
            '/classcards/classcard-purchases/{userClassCard}/extend-expiry',
            [UserClassCardController::class, 'extendExpiry']
        )->name('classcards.classcard-purchases.extend-expiry');
    });
