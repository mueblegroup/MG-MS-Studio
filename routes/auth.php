<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\InstituteRegisterController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\TwoFactorAuthenticationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('two-factor-challenge', [TwoFactorAuthenticationController::class, 'challenge'])
        ->name('two-factor.challenge');
    Route::post('two-factor-challenge', [TwoFactorAuthenticationController::class, 'verifyChallenge'])
        ->middleware('throttle:6,1')
        ->name('two-factor.challenge.verify');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');

    Route::get('institutes/register', [InstituteRegisterController::class, 'create'])->name('institutes.register');
    Route::post('institutes/register', [InstituteRegisterController::class, 'store'])->name('institutes.register.store');
    Route::get('institutes/check-subdomain', [InstituteRegisterController::class, 'checkSubdomain'])->name('institutes.check-subdomain');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('security/two-factor', [TwoFactorAuthenticationController::class, 'edit'])->name('two-factor.edit');
    Route::post('security/two-factor/enable', [TwoFactorAuthenticationController::class, 'enable'])->name('two-factor.enable');
    Route::delete('security/two-factor', [TwoFactorAuthenticationController::class, 'disable'])->name('two-factor.disable');
    Route::post('security/two-factor/recovery-codes', [TwoFactorAuthenticationController::class, 'regenerate'])->name('two-factor.recovery-codes');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout.post');
});
