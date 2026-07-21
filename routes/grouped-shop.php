<?php

use App\Http\Controllers\GroupedShopController;
use Illuminate\Support\Facades\Route;

Route::get('/shop', [GroupedShopController::class, 'index'])->name('shop.index');
Route::get('/shop/', [GroupedShopController::class, 'index']);
