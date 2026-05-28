<?php

use App\Http\Controllers\Student\StudentClassCardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('/classcards', [StudentClassCardController::class, 'index'])->name('classcards.index');
    });
