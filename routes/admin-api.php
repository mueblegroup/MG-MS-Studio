<?php

use App\Http\Controllers\Admin\ApiTokenController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('api-tokens/docs', [ApiTokenController::class, 'docs'])->name('api-tokens.docs');
        Route::resource('api-tokens', ApiTokenController::class)->only(['index', 'create', 'store', 'destroy']);
    });
