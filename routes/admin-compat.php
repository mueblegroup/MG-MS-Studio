<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Admin\AppNotificationController as AdminAppNotificationController;
use App\Http\Controllers\ClassAssignmentController;
use App\Http\Controllers\ClassCardController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanSessionController;
use App\Http\Controllers\UserClassCardController;
use App\Http\Controllers\UserPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/logout', [LogoutController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/payments', [AdminController::class, 'payments'])->name('admin.payments');

    Route::get('/admin/teachers', [AdminController::class, 'teachers'])->name('admin.teachers');
    Route::get('/admin/teachers/create', [AdminController::class, 'createTeacher'])->name('admin.teachers.create');
    Route::post('/admin/teachers/store', [AdminController::class, 'storeTeacher'])->name('admin.teachers.store');
    Route::get('/admin/teachers/{id}/edit', [AdminController::class, 'editTeacher'])->name('admin.teachers.edit');
    Route::put('/admin/teachers/{id}/update', [AdminController::class, 'updateTeacher'])->name('admin.teachers.update');
    Route::delete('/admin/teachers/{id}/destroy', [AdminController::class, 'destroyTeacher'])->name('admin.teachers.destroy');

    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/admin/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
    Route::post('/admin/students/store', [AdminController::class, 'storeStudent'])->name('admin.students.store');
    Route::get('/admin/students/{id}/edit', [AdminController::class, 'editStudent'])->name('admin.students.edit');
    Route::put('/admin/students/{id}/update', [AdminController::class, 'updateStudent'])->name('admin.students.update');
    Route::delete('/admin/students/{id}/destroy', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');

    Route::get('/admin/admins', [AdminController::class, 'admins'])->name('admin.admins');
    Route::get('/admin/admins/create', [AdminController::class, 'createAdmin'])->name('admin.admins.create');
    Route::post('/admin/admins/store', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
    Route::get('/admin/admins/{id}/edit', [AdminController::class, 'editAdmin'])->name('admin.admins.edit');
    Route::put('/admin/admins/{id}/update', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
    Route::delete('/admin/admins/{id}/destroy', [AdminController::class, 'destroyAdmin'])->name('admin.admins.destroy');

    Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users/store', [AdminController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
    Route::put('/admin/users/{id}/update', [AdminController::class, 'update'])->name('admin.users.update');
    Route::delete('/admin/users/{id}/destroy', [AdminController::class, 'destroy'])->name('admin.users.destroy');

    Route::get('/admin/classes', [ClassController::class, 'index'])->name('admin.classes');
    Route::get('/admin/classes/create', [ClassController::class, 'create'])->name('admin.classes.create');
    Route::post('/admin/classes/store', [ClassController::class, 'store'])->name('admin.classes.store');
    Route::delete('/admin/classes/{classSession}/destroy', [ClassController::class, 'destroy'])->name('admin.classes.destroy');
    Route::get('/admin/classes/{classSession}/edit', [ClassController::class, 'edit'])->name('admin.classes.edit');
    Route::put('/admin/classes/{classSession}/update', [ClassController::class, 'update'])->name('admin.classes.update');

    Route::get('/admin/plans', [PlanController::class, 'index'])->name('admin.plans');
    Route::get('/admin/plans/{plan}/show', [PlanController::class, 'show'])->name('admin.plans.show');
    Route::get('/admin/plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
    Route::post('/admin/plans/store', [PlanController::class, 'store'])->name('admin.plans.store');
    Route::delete('/admin/plans/{plan}/destroy', [PlanController::class, 'destroy'])->name('admin.plans.destroy');
    Route::get('/admin/plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('/admin/plans/{plan}/update', [PlanController::class, 'update'])->name('admin.plans.update');
    Route::get('/admin/plans/{plan}/sessions/{session}/edit', [PlanController::class, 'editSession'])->name('admin.plans.sessions.edit');
    Route::put('/admin/plans/{plan}/sessions/{session}', [PlanSessionController::class, 'update'])->name('admin.plans.sessions.update');
    Route::delete('/admin/plans/{plan}/sessions/{session}', [PlanSessionController::class, 'destroy'])->name('admin.plans.sessions.destroy');
    Route::get('/admin/plans/{plan}/sessions/{session}/create', [PlanController::class, 'createSession'])->name('admin.plans.sessions.create');
    Route::post('/admin/plans/{plan}/sessions/{session}/store', [PlanController::class, 'storeSession'])->name('admin.plans.sessions.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('class-assignments', [ClassAssignmentController::class, 'index'])->name('class-assignments.index');
    Route::get('class-assignments/create', [ClassAssignmentController::class, 'create'])->name('class-assignments.create');
    Route::post('class-assignments', [ClassAssignmentController::class, 'store'])->name('class-assignments.store');
    Route::delete('class-assignments/{assignment}', [ClassAssignmentController::class, 'destroy'])->name('class-assignments.destroy');

    Route::get('plan-assignments', [UserPlanController::class, 'index'])->name('planassignments.index');
    Route::get('plan-assignments/create', [UserPlanController::class, 'create'])->name('planassignments.create');
    Route::post('plan-assignments', [UserPlanController::class, 'store'])->name('planassignments.store');
    Route::delete('plan-assignments/{userPlan}', [UserPlanController::class, 'destroy'])->name('planassignments.destroy');

    Route::get('notifications', [AdminAppNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/show', [AdminAppNotificationController::class, 'show'])->name('notifications.show');
    Route::get('notifications/{notification}/edit', [AdminAppNotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('notifications/{notification}/update', [AdminAppNotificationController::class, 'update'])->name('notifications.update');
    Route::delete('notifications/{notification}/destroy', [AdminAppNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('notifications/create', [AdminAppNotificationController::class, 'create'])->name('notifications.create');
    Route::post('notifications/store', [AdminAppNotificationController::class, 'store'])->name('notifications.store');

    Route::get('classcards/classcard-purchases/{userClassCard}/show', [UserClassCardController::class, 'show'])->name('classcards.classcard-purchases.show');
    Route::get('classcards/classcard-purchases/{userClassCard}/edit', [UserClassCardController::class, 'edit'])->name('classcards.classcard-purchases.edit');
    Route::put('classcards/classcard-purchases/{userClassCard}', [UserClassCardController::class, 'update'])->name('classcards.classcard-purchases.update');
    Route::delete('classcards/classcard-purchases/{userClassCard}', [UserClassCardController::class, 'destroy'])->name('classcards.classcard-purchases.destroy');
    Route::resource('classcards/classcard-purchases', UserClassCardController::class);
    Route::get('classcards/classcard-purchases', [UserClassCardController::class, 'index'])->name('classcards.classcard-purchases');
    Route::get('classcards/classcard-purchases/create', [UserClassCardController::class, 'create'])->name('classcards.classcard-purchases.create');
    Route::post('classcards/classcard-purchases', [UserClassCardController::class, 'store'])->name('classcards.classcard-purchases.store');
    Route::resource('classcards', ClassCardController::class);
});
