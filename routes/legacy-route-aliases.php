<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->group(function (): void {
        Route::get('/plans-overview', function () {
            return redirect()->route('admin.plans.index');
        })->name('admin.plans');
    });
