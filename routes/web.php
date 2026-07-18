<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Teacher\TeacherClassController;
use App\Http\Controllers\Teacher\TeacherPlanController;
use App\Http\Controllers\Teacher\TeacherScheduleController;
use App\Http\Controllers\Teacher\TeacherClassAttendanceController;
use App\Http\Controllers\Teacher\TeacherPlanAttendanceController;
use App\Http\Controllers\Teacher\TeacherClassCardController;
use App\Http\Controllers\Teacher\TeacherClassCardAttendanceController;
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
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentAttendanceController;
use App\Http\Controllers\Student\StudentScheduleController;
use App\Http\Controllers\Student\StudentPaymentController;
use App\Http\Controllers\Admin\AppNotificationController as AdminAppNotificationController;
use App\Http\Controllers\AppNotificationController;
use App\Http\Controllers\DocumentationController;
use App\Support\TenantManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/docs', [DocumentationController::class, 'index'])->name('docs.index');
Route::get('/docs/{page}', [DocumentationController::class, 'index'])->name('docs.show');

Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        $studio = app(TenantManager::class)->current();

        if (! $studio) {
            if ($user->role === 'superadmin') {
                return redirect()->route('superadmin.dashboard');
            }

            if ($user->role === 'admin') {
                return redirect()->route('customer.dashboard');
            }

            return redirect()->route('login');
        }

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

    return view('saas.landing');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile', function () {
    abort_unless(auth()->user()->role === 'admin', 403);
    return app(\App\Http\Controllers\ProfileController::class)->destroy(request());
})->middleware('auth')->name('profile.destroy');
});


/***** ADMIN ROUTES *****/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/admins', [AdminController::class, 'admins'])->name('admin.admins');
    Route::get('/admins/create', [AdminController::class, 'createAdmin'])->name('admin.admins.create');
    Route::post('/admins', [AdminController::class, 'storeAdmin'])->name('admin.admins.store');
    Route::get('/admins/{id}/edit', [AdminController::class, 'editAdmin'])->name('admin.admins.edit');
    Route::put('/admins/{id}', [AdminController::class, 'updateAdmin'])->name('admin.admins.update');
    Route::delete('/admins/{id}', [AdminController::class, 'destroyAdmin'])->name('admin.admins.destroy');

    Route::get('/teachers', [AdminController::class, 'teachers'])->name('admin.teachers');
    Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('admin.teachers.create');
    Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('admin.teachers.store');
    Route::get('/teachers/{id}/edit', [AdminController::class, 'editTeacher'])->name('admin.teachers.edit');
    Route::put('/teachers/{id}', [AdminController::class, 'updateTeacher'])->name('admin.teachers.update');
    Route::delete('/teachers/{id}', [AdminController::class, 'destroyTeacher'])->name('admin.teachers.destroy');

    Route::get('/students', [AdminController::class, 'students'])->name('admin.students');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('admin.students.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('admin.students.store');
    Route::get('/students/{id}/edit', [AdminController::class, 'editStudent'])->name('admin.students.edit');
    Route::put('/students/{id}', [AdminController::class, 'updateStudent'])->name('admin.students.update');
    Route::delete('/students/{id}', [AdminController::class, 'destroyStudent'])->name('admin.students.destroy');

    Route::get('/classes', [ClassController::class, 'index'])->name('admin.classes');
    Route::get('/classes/create', [ClassController::class, 'create'])->name('admin.classes.create');
    Route::post('/classes', [ClassController::class, 'store'])->name('admin.classes.store');
    Route::get('/classes/{classSession}/edit', [ClassController::class, 'edit'])->name('admin.classes.edit');
    Route::put('/classes/{classSession}', [ClassController::class, 'update'])->name('admin.classes.update');
    Route::delete('/classes/{classSession}', [ClassController::class, 'destroy'])->name('admin.classes.destroy');
    Route::get('/classes/data', [ClassController::class, 'data'])->name('admin.classes.data');

    Route::resource('plans', PlanController::class)->names('admin.plans');
    Route::resource('plans.sessions', PlanSessionController::class)->names('admin.plans.sessions');
    Route::post('/plans/{plan}/assign', [UserPlanController::class, 'store'])->name('admin.plans.assign');

    Route::post('classcards/classcard-purchases', [UserClassCardController::class, 'store'])
        ->name('classcards.classcard-purchases.store');

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
        Route::post('/checkout/payments/{payment}/retry', [CheckoutController::class, 'retryPendingPayment'])->name('checkout.payments.retry');
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
    Route::get('/payments/{id}/receipt', [PaymentHistoryController::class, 'downloadReceipt'])->name('payments.receipt.download');
});

/******** Studio Settings Routes (Admin) ********/
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/settings/studio', [StudioSettingsController::class, 'edit'])->name('settings.studio');
    Route::post('/settings/studio', [StudioSettingsController::class, 'update'])->name('settings.studio.update');
    Route::post('/settings/studio/test-email', [StudioSettingsController::class, 'sendTestEmail'])->name('settings.studio.test-email');
});



/* -------------------------------- */
/*             TEACHER ROUTES       */
/* -------------------------------- */

/*----- Teacher Routes (Teacher)------*/

Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {

    Route::get('/dashboard', [TeacherController::class, 'index'])->name('dashboard');

    // Classes
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [TeacherClassController::class, 'show'])->name('classes.show');

    // Class session attendance
    Route::get('/classes/sessions/{session}/attendance', [TeacherClassAttendanceController::class, 'show'])
        ->name('classes.attendance.show');
    Route::post('/classes/sessions/{session}/attendance/{assignment}/mark', [TeacherClassAttendanceController::class, 'mark'])
        ->name('classes.attendance.mark');

    // Plans
    Route::get('/plans', [TeacherPlanController::class, 'index'])->name('plans.index');
    Route::get('/plans/{plan}', [TeacherPlanController::class, 'show'])->name('plans.show');

    // Plan session attendance
    Route::get('/plans/{plan}/sessions/{session}/attendance', [TeacherPlanAttendanceController::class, 'show'])
        ->name('plans.sessions.attendance.show');
    Route::post('/plans/{plan}/sessions/{session}/attendance/{user}/mark', [TeacherPlanAttendanceController::class, 'mark'])
        ->name('plans.sessions.attendance.mark');

    // Teacher Class Cards (view all)
    Route::get('/classcards', [TeacherClassCardController::class, 'index'])->name('classcards.index');
    Route::get('/classcards/{classCard}', [TeacherClassCardController::class, 'show'])->name('classcards.show');

    // Mark Class Card usage (-1)
    Route::post('/classcards/usage/{userClassCard}/mark', [TeacherClassCardAttendanceController::class, 'mark'])
        ->name('classcards.usage.mark');

    // Schedule
    Route::get('/schedule', [TeacherScheduleController::class, 'index'])->name('schedule.index');

    /*---- Notification Routes (Teacher)------*/
    Route::get('notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
    Route::get('notifications/{notification}/show', [AppNotificationController::class, 'show'])->name('notifications.show');
    Route::get('notifications/{notification}/edit', [AppNotificationController::class, 'edit'])->name('notifications.edit');
    Route::put('notifications/{notification}/update', [AppNotificationController::class, 'update'])->name('notifications.update');
    Route::delete('notifications/{notification}/destroy', [AppNotificationController::class, 'destroy'])->name('notifications.destroy');
    Route::get('notifications/create', [AppNotificationController::class, 'create'])->name('notifications.create');
    Route::post('notifications/store', [AppNotificationController::class, 'store'])->name('notifications.store');
});



/*----- Student Routes (Student)------*/
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance', [StudentAttendanceController::class, 'index'])->name('attendance.index');

    Route::get('/schedule', [StudentScheduleController::class, 'index'])->name('schedule.index');

    Route::get('/payments', [StudentPaymentController::class, 'index'])->name('payments.index');
    Route::get('/payments/{id}/receipt', [StudentPaymentController::class, 'downloadReceipt'])->name('payments.receipt.download');


    // recommended extras:
    // Route::get('/my-products', [StudentProductsController::class, 'index'])->name('products.index');
    // Route::get('/bookings', [StudentProductsController::class, 'index'])->name('products.index');
});

/*---- Notification Routes (Student)------*/
Route::middleware(['auth', 'role:student'])
    ->prefix('student')
    ->name('student.')
    ->group(function () {
        Route::get('notifications', [AppNotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{notification}/show', [AppNotificationController::class, 'show'])->name('notifications.show');
        Route::get('notifications/{notification}/edit', [AppNotificationController::class, 'edit'])->name('notifications.edit');
        Route::put('notifications/{notification}/update', [AppNotificationController::class, 'update'])->name('notifications.update');
        Route::delete('notifications/{notification}/destroy', [AppNotificationController::class, 'destroy'])->name('notifications.destroy');
        Route::get('notifications/create', [AppNotificationController::class, 'create'])->name('notifications.create');
        Route::post('notifications/store', [AppNotificationController::class, 'store'])->name('notifications.store');
    });


require __DIR__ . '/auth.php';
