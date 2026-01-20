<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PlanSessionController;
use App\Http\Controllers\UserClassCardController;
use App\Http\Controllers\ClassAssignmentController;
use App\Http\Controllers\ClassCardController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassSessionBookingController;
use App\Http\Controllers\UserPlanController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ClassAttendanceController;
use App\Http\Controllers\PlanAttendanceController;
use App\Http\Controllers\ClassCardAttendanceController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentHistoryController;
use App\Http\Controllers\StudioSettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        // Redirect based on role
        switch ($user->role) {
            case 'admin':
                return redirect()->route('admin.dashboard');
            case 'teacher':
                return redirect()->route('teacher.dashboard');
            case 'student':
                return redirect()->route('student.dashboard');
            default:
                return redirect()->route('login');
        }
    }

    // If not logged in, go to login page
    return redirect()->route('login');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');
});


/* -------------------------------- */
/*             ADMIN ROUTES         */
/* -------------------------------- */


/*----- Admin Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/payments', [AdminController::class, 'payments'])->name('admin.payments');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
});
/*----- Teacher Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/teachers', [AdminController::class, 'teachers'])->name('admin.teachers');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/teachers/create', [AdminController::class, 'createTeacher'])->name('admin.teachers.create');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/teachers/store', [AdminController::class, 'storeTeacher'])->name('admin.teachers.store');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/teachers/{id}/edit', [AdminController::class, 'editTeacher'])->name('admin.teachers.edit');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/admin/teachers/{id}/update', [AdminController::class, 'updateTeacher'])->name('admin.teachers.update');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/admin/teachers/{id}/destroy', [AdminController::class, 'destroyTeacher'])->name('admin.teachers.destroy');
});

/*----- Student Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/students', [AdminController::class, 'students'])->name('admin.students');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/students/store', [AdminController::class, 'storeStudent'])->name('admin.students.store');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/students/{id}/edit', [AdminController::class, 'editStudent'])->name('admin.students.edit');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/admin/students/{id}/update', [AdminController::class, 'updateStudent'])->name('admin.students.update');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/admin/students/{id}/destroy', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');
});

/*----- Admin Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/admins', [AdminController::class, 'admins'])->name('admin.admins');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/admins/create', [AdminController::class, 'createAdmin'])->name('admin.admins.create');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/admins/store', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/admins/{id}/edit', [AdminController::class, 'editAdmin'])->name('admin.admins.edit');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/admin/admins/{id}/update', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/admin/admins/{id}/destroy', [AdminController::class, 'destroyAdmin'])->name('admin.admins.destroy');
});

/*----- User Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/create', [AdminController::class, 'create'])->name('admin.users.create');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::post('/admin/users/store', [AdminController::class, 'store'])->name('admin.users.store');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/users/{id}/edit', [AdminController::class, 'edit'])->name('admin.users.edit');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/admin/users/{id}/update', [AdminController::class, 'update'])->name('admin.users.update');
});
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/admin/users/{id}/destroy', [AdminController::class, 'destroy'])->name('admin.users.destroy');
});

/*----- Class Routes (Admin)------*/
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/admin/classes', [ClassController::class, 'index'])->name('admin.classes');

    Route::get('/admin/classes/create', [ClassController::class, 'create'])->name('admin.classes.create');
    Route::post('/admin/classes/store', [ClassController::class, 'store'])->name('admin.classes.store');

    Route::delete('/admin/classes/{classSession}/destroy', [ClassController::class, 'destroy'])->name('admin.classes.destroy');
    Route::get('/admin/classes/{classSession}/edit', [ClassController::class, 'edit'])->name('admin.classes.edit');
    Route::put('/admin/classes/{classSession}/update', [ClassController::class, 'update'])->name('admin.classes.update');

});

Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('class-assignments', [ClassAssignmentController::class, 'index'])
            ->name('class-assignments.index');

        Route::get('class-assignments/create', [ClassAssignmentController::class, 'create'])
            ->name('class-assignments.create');

        Route::post('class-assignments', [ClassAssignmentController::class, 'store'])
            ->name('class-assignments.store');

        Route::delete('class-assignments/{assignment}', [ClassAssignmentController::class, 'destroy'])
            ->name('class-assignments.destroy');
    });

/*----- Plan Routes (Admin)------*/
Route::middleware(['auth','role:admin'])->group(function () {
    Route::get('/admin/plans', [PlanController::class, 'index'])->name('admin.plans');
    Route::get('/admin/plans/{plan}/show', [PlanController::class, 'show'])->name('admin.plans.show');
    Route::get('/admin/plans/create', [PlanController::class, 'create'])->name('admin.plans.create');
    Route::post('/admin/plans/store', [PlanController::class, 'store'])->name('admin.plans.store');

    Route::delete('/admin/plans/{plan}/destroy', [PlanController::class, 'destroy'])->name('admin.plans.destroy');
    Route::get('/admin/plans/{plan}/edit', [PlanController::class, 'edit'])->name('admin.plans.edit');
    Route::put('/admin/plans/{plan}/update', [PlanController::class, 'update'])->name('admin.plans.update');

    // Plan sessions (inside a plan)
    Route::get('/admin/plans/{plan}/sessions/{session}/edit', [PlanController::class, 'editSession'])->name('admin.plans.sessions.edit');
    Route::put('/admin/plans/{plan}/sessions/{session}', [PlanSessionController::class, 'update'])->name('admin.plans.sessions.update');
    Route::delete('/admin/plans/{plan}/sessions/{session}', [PlanSessionController::class, 'destroy'])->name('admin.plans.sessions.destroy');
    Route::get('/admin/plans/{plan}/sessions/{session}/create', [PlanController::class, 'createSession'])->name('admin.plans.sessions.create');
    Route::post('/admin/plans/{plan}/sessions/{session}/store', [PlanController::class, 'storeSession'])->name('admin.plans.sessions.store');
});

Route::middleware(['auth','role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
    Route::get('plan-assignments', [UserPlanController::class, 'index'])->name('planassignments.index');
    Route::get('plan-assignments/create', [UserPlanController::class, 'create'])->name('planassignments.create');
    Route::post('plan-assignments', [UserPlanController::class, 'store'])->name('planassignments.store');
    Route::delete('plan-assignments/{userPlan}', [UserPlanController::class, 'destroy'])->name('planassignments.destroy');
    });

    
/*----- Class Card Routes (Admin)------*/
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('classcards/classcard-purchases/{userClassCard}/show', [UserClassCardController::class, 'show'])
            ->name('classcards.classcard-purchases.show');

        Route::get('classcards/classcard-purchases/{userClassCard}/edit', [UserClassCardController::class, 'edit'])
            ->name('classcards.classcard-purchases.edit');

        Route::put('classcards/classcard-purchases/{userClassCard}', [UserClassCardController::class, 'update'])
            ->name('classcards.classcard-purchases.update');

        Route::delete('classcards/classcard-purchases/{userClassCard}', [UserClassCardController::class, 'destroy'])
            ->name('classcards.classcard-purchases.destroy');
        // Purchases CRUD
        Route::resource('classcards/classcard-purchases', UserClassCardController::class);
        Route::get('classcards/classcard-purchases', [UserClassCardController::class, 'index'])
            ->name('classcards.classcard-purchases');

        Route::get('classcards/classcard-purchases/create', [UserClassCardController::class, 'create'])
            ->name('classcards.classcard-purchases.create');

        Route::post('classcards/classcard-purchases', [UserClassCardController::class, 'store'])
            ->name('classcards.classcard-purchases.store');


        // ClassCard product CRUD (this already includes index/create/store/edit/update/destroy)
        Route::resource('classcards', ClassCardController::class);


    });

/*******SHOP ROUTES *******/

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('index');

    // Cart
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/{itemId}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{itemId}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/cart', [CartController::class, 'clear'])->name('cart.clear');

    // Checkout (login required)
    Route::middleware('auth')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout/pay', [CheckoutController::class, 'pay'])->name('checkout.pay');
        Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/failure', [CheckoutController::class, 'failure'])->name('checkout.failure');
        Route::get('/checkout/cancel', [CheckoutController::class, 'cancel'])->name('checkout.cancel');
        Route::get('/checkout/confirm', [CheckoutController::class, 'confirm'])->name('checkout.confirm');
    });
});


/**** Webhooks ****/
Route::post('/webhooks/stripe', [CheckoutController::class, 'stripeWebhook'])->name('webhooks.stripe');
Route::post('/webhooks/hitpay', [CheckoutController::class, 'hitpayWebhook'])->name('webhooks.hitpay');


/******** ATTENDANCE ROUTES ********/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/classes/{classSessionId}/attendance', [ClassAttendanceController::class, 'show'])
        ->name('admin.classes.attendance');

    Route::post('/classes/{classSessionId}/attendance/{assignmentId}', [ClassAttendanceController::class, 'mark'])
        ->name('admin.classes.attendance.mark');
});

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/plans/{planId}/sessions/{planSessionId}/attendance', [PlanAttendanceController::class, 'show'])
        ->name('admin.plans.sessions.attendance');

    Route::post('/plans/{planId}/sessions/{planSessionId}/attendance/{userId}', [PlanAttendanceController::class, 'mark'])
        ->name('admin.plans.sessions.attendance.mark');
});
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::post('/classcards/usage/{userClassCardId}', [ClassCardAttendanceController::class, 'mark'])
        ->name('admin.classcards.usage.mark');
});

/* Payment History Routes */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/payments', [PaymentHistoryController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}', [PaymentHistoryController::class, 'show'])->name('payments.show');
});

/******** Studio Settings Routes (Admin) ********/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/settings/studio', [StudioSettingsController::class, 'edit'])->name('settings.studio');
    Route::post('/settings/studio', [StudioSettingsController::class, 'update'])->name('settings.studio.update');
});



/* -------------------------------- */
/*             TEACHER ROUTES       */
/* -------------------------------- */

/*----- Teacher Routes (Teacher)------*/
Route::middleware(['auth', 'role:teacher'])->group(function () {
    Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
});
require __DIR__ . '/auth.php';
