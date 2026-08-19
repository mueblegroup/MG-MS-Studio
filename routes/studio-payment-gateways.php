<?php

use App\Http\Controllers\StudioPaymentGatewayController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin/settings/payment-gateways')
    ->name('settings.payment-gateways.')
    ->group(function (): void {
        Route::get('/', [StudioPaymentGatewayController::class, 'index'])->name('index');
        Route::patch('/{provider}', [StudioPaymentGatewayController::class, 'update'])
            ->whereIn('provider', ['stripe', 'hitpay'])
            ->name('update');
        Route::post('/{provider}/test', [StudioPaymentGatewayController::class, 'test'])
            ->whereIn('provider', ['stripe', 'hitpay'])
            ->name('test');
    });
