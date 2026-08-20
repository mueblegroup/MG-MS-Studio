<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Student\StudentSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/subscriptions', [StudentSubscriptionController::class, 'index'])
            ->name('subscriptions.index');

        Route::post('/subscriptions/{subscription}/retry-payment', [CheckoutController::class, 'retrySubscriptionStart'])
            ->name('subscriptions.retry-payment');
    });
