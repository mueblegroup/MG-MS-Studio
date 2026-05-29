<?php

use App\Http\Controllers\Api\V1\ApiLogController;
use App\Http\Controllers\Api\V1\AttendanceApiController;
use App\Http\Controllers\Api\V1\ClassApiController;
use App\Http\Controllers\Api\V1\ClassCardApiController;
use App\Http\Controllers\Api\V1\CommerceApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\PlanApiController;
use App\Http\Controllers\Api\V1\SettingsApiController;
use App\Http\Controllers\Api\V1\SystemApiController;
use App\Http\Controllers\Api\V1\UserApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'api.log', 'throttle:api'])
    ->group(function () {
        Route::get('me', [SystemApiController::class, 'me']);

        Route::get('reports/dashboard', [SystemApiController::class, 'dashboard'])
            ->middleware('api.ability:reports:read');

        Route::get('api-logs', [ApiLogController::class, 'index'])
            ->middleware('api.ability:api-logs:read');

        Route::get('users', [UserApiController::class, 'index'])->middleware('api.ability:users:read');
        Route::post('users', [UserApiController::class, 'store'])->middleware('api.ability:users:create');
        Route::get('users/{user}', [UserApiController::class, 'show'])->middleware('api.ability:users:read');
        Route::put('users/{user}', [UserApiController::class, 'update'])->middleware('api.ability:users:update');
        Route::patch('users/{user}', [UserApiController::class, 'update'])->middleware('api.ability:users:update');
        Route::delete('users/{user}', [UserApiController::class, 'destroy'])->middleware('api.ability:users:delete');

        Route::get('students', [UserApiController::class, 'index'])->defaults('role', 'student')->middleware('api.ability:students:read');
        Route::post('students', [UserApiController::class, 'store'])->defaults('role', 'student')->middleware('api.ability:students:create');

        Route::get('teachers', [UserApiController::class, 'index'])->defaults('role', 'teacher')->middleware('api.ability:teachers:read');
        Route::post('teachers', [UserApiController::class, 'store'])->defaults('role', 'teacher')->middleware('api.ability:teachers:create');

        Route::get('classes', [ClassApiController::class, 'index'])->middleware('api.ability:classes:read');
        Route::post('classes', [ClassApiController::class, 'store'])->middleware('api.ability:classes:create');
        Route::get('classes/{class}', [ClassApiController::class, 'show'])->middleware('api.ability:classes:read');
        Route::put('classes/{class}', [ClassApiController::class, 'update'])->middleware('api.ability:classes:update');
        Route::patch('classes/{class}', [ClassApiController::class, 'update'])->middleware('api.ability:classes:update');
        Route::delete('classes/{class}', [ClassApiController::class, 'destroy'])->middleware('api.ability:classes:delete');
        Route::get('classes/{class}/sessions', [ClassApiController::class, 'sessions'])->middleware('api.ability:classes:read');
        Route::post('classes/{class}/sessions', [ClassApiController::class, 'storeSession'])->middleware('api.ability:classes:create');
        Route::put('class-sessions/{session}', [ClassApiController::class, 'updateSession'])->middleware('api.ability:classes:update');
        Route::patch('class-sessions/{session}', [ClassApiController::class, 'updateSession'])->middleware('api.ability:classes:update');
        Route::delete('class-sessions/{session}', [ClassApiController::class, 'destroySession'])->middleware('api.ability:classes:delete');

        Route::get('plans', [PlanApiController::class, 'index'])->middleware('api.ability:plans:read');
        Route::post('plans', [PlanApiController::class, 'store'])->middleware('api.ability:plans:create');
        Route::get('plans/{plan}', [PlanApiController::class, 'show'])->middleware('api.ability:plans:read');
        Route::put('plans/{plan}', [PlanApiController::class, 'update'])->middleware('api.ability:plans:update');
        Route::patch('plans/{plan}', [PlanApiController::class, 'update'])->middleware('api.ability:plans:update');
        Route::delete('plans/{plan}', [PlanApiController::class, 'destroy'])->middleware('api.ability:plans:delete');
        Route::get('plans/{plan}/sessions', [PlanApiController::class, 'sessions'])->middleware('api.ability:plans:read');
        Route::post('plans/{plan}/sessions', [PlanApiController::class, 'storeSession'])->middleware('api.ability:plans:create');
        Route::put('plan-sessions/{session}', [PlanApiController::class, 'updateSession'])->middleware('api.ability:plans:update');
        Route::patch('plan-sessions/{session}', [PlanApiController::class, 'updateSession'])->middleware('api.ability:plans:update');
        Route::delete('plan-sessions/{session}', [PlanApiController::class, 'destroySession'])->middleware('api.ability:plans:delete');

        Route::get('classcards', [ClassCardApiController::class, 'index'])->middleware('api.ability:classcards:read');
        Route::post('classcards', [ClassCardApiController::class, 'store'])->middleware('api.ability:classcards:create');
        Route::get('classcards/{classcard}', [ClassCardApiController::class, 'show'])->middleware('api.ability:classcards:read');
        Route::put('classcards/{classcard}', [ClassCardApiController::class, 'update'])->middleware('api.ability:classcards:update');
        Route::patch('classcards/{classcard}', [ClassCardApiController::class, 'update'])->middleware('api.ability:classcards:update');
        Route::delete('classcards/{classcard}', [ClassCardApiController::class, 'destroy'])->middleware('api.ability:classcards:delete');

        Route::get('classcard-purchases', [ClassCardApiController::class, 'purchases'])->middleware('api.ability:classcards:read');
        Route::post('classcard-purchases', [ClassCardApiController::class, 'storePurchase'])->middleware('api.ability:classcards:create');
        Route::get('classcard-purchases/{purchase}', [ClassCardApiController::class, 'showPurchase'])->middleware('api.ability:classcards:read');
        Route::put('classcard-purchases/{purchase}', [ClassCardApiController::class, 'updatePurchase'])->middleware('api.ability:classcards:update');
        Route::patch('classcard-purchases/{purchase}', [ClassCardApiController::class, 'updatePurchase'])->middleware('api.ability:classcards:update');
        Route::delete('classcard-purchases/{purchase}', [ClassCardApiController::class, 'destroyPurchase'])->middleware('api.ability:classcards:delete');

        Route::get('attendance/class-assignments', [AttendanceApiController::class, 'classAssignments'])->middleware('api.ability:attendance:read');
        Route::get('attendance/classcards', [AttendanceApiController::class, 'classCardUsages'])->middleware('api.ability:attendance:read');
        Route::post('attendance/classcards/{userClassCard}/mark', [AttendanceApiController::class, 'markClassCard'])->middleware('api.ability:attendance:mark');

        Route::get('payments', [CommerceApiController::class, 'payments'])->middleware('api.ability:payments:read');
        Route::get('payments/{payment}', [CommerceApiController::class, 'payment'])->middleware('api.ability:payments:read');
        Route::get('orders', [CommerceApiController::class, 'orders'])->middleware('api.ability:orders:read');
        Route::get('orders/{order}', [CommerceApiController::class, 'order'])->middleware('api.ability:orders:read');
        Route::get('shop', [CommerceApiController::class, 'shop'])->middleware('api.ability:shop:read');

        Route::get('notifications', [NotificationApiController::class, 'index'])->middleware('api.ability:notifications:read');
        Route::post('notifications', [NotificationApiController::class, 'store'])->middleware('api.ability:notifications:create');
        Route::get('notifications/{notification}', [NotificationApiController::class, 'show'])->middleware('api.ability:notifications:read');
        Route::put('notifications/{notification}', [NotificationApiController::class, 'update'])->middleware('api.ability:notifications:update');
        Route::patch('notifications/{notification}', [NotificationApiController::class, 'update'])->middleware('api.ability:notifications:update');
        Route::delete('notifications/{notification}', [NotificationApiController::class, 'destroy'])->middleware('api.ability:notifications:delete');

        Route::get('settings/studio', [SettingsApiController::class, 'show'])->middleware('api.ability:settings:read');
        Route::put('settings/studio', [SettingsApiController::class, 'update'])->middleware('api.ability:settings:update');
        Route::patch('settings/studio', [SettingsApiController::class, 'update'])->middleware('api.ability:settings:update');
    });
