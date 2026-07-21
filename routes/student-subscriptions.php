<?php

use App\Http\Controllers\Student\StudentSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/subscriptions', [StudentSubscriptionController::class, 'index'])
            ->name('subscriptions.index');
    });
