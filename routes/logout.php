<?php

use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/logout', [LogoutController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');
