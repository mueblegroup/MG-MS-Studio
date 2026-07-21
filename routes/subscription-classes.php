<?php

use App\Http\Controllers\SubscriptionClassManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/subscription-classes/{class}', [SubscriptionClassManagementController::class, 'show'])
        ->name('subscription-classes.show');
    Route::post('/subscription-classes/{class}/students/{subscription}/notify', [SubscriptionClassManagementController::class, 'notify'])
        ->name('subscription-classes.students.notify');
});

Route::middleware(['auth', 'role:student'])->group(function () {
    Route::post('/student/subscriptions/{subscription}/cancel', [SubscriptionClassManagementController::class, 'cancelStudentSubscription'])
        ->name('student.subscriptions.cancel');
});