<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\Order;
use App\Services\StudioSettingsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /* ============================
     * Show Dashboard
     * ============================ */
    public function dashboard(StudioSettingsService $settings)
    {
        $year = (int) now()->year;
        $monthLabels = collect(range(1, 12))
            ->map(fn ($month) => Carbon::create($year, $month, 1)->format('M'))
            ->values();

        $paidPayments = Payment::query()
            ->where('status', 'paid')
            ->where(function ($query) use ($year) {
                $query->whereYear('paid_at', $year)
                    ->orWhere(function ($fallback) use ($year) {
                        $fallback->whereNull('paid_at')
                            ->whereYear('created_at', $year);
                    });
            })
            ->get(['amount', 'paid_at', 'created_at']);

        $monthlyRevenue = array_fill(1, 12, 0.0);

        foreach ($paidPayments as $payment) {
            $date = $payment->paid_at ?: $payment->created_at;

            if (!$date) {
                continue;
            }

            $monthlyRevenue[(int) $date->month] += (float) $payment->amount;
        }

        $revenueData = array_values(array_map(
            fn ($amount) => round((float) $amount, 2),
            $monthlyRevenue
        ));

        $totalProfit = Payment::where('status', 'paid')->sum('amount');
        $thisMonthRevenue = Payment::where('status', 'paid')
            ->where(function ($query) {
                $query->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->orWhere(function ($fallback) {
                        $fallback->whereNull('paid_at')
                            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    });
            })
            ->sum('amount');

        $pendingOrders = Order::where('status', 'pending')->count();

        $calendarEvents = ClassSession::query()
            ->with('classModel:id,name')
            ->whereNotNull('start_time')
            ->where('start_time', '>=', now()->subMonth())
            ->where('start_time', '<=', now()->addMonths(2))
            ->orderBy('start_time')
            ->limit(100)
            ->get()
            ->map(function (ClassSession $session) {
                return [
                    'title' => $session->classModel?->name ?? 'Class Session',
                    'start' => optional($session->start_time)->toIso8601String(),
                    'end' => optional($session->end_time)->toIso8601String(),
                ];
            })
            ->values();

        return view('admin.dashboard', [
            'total_profit'       => $totalProfit,
            'this_month_revenue' => $thisMonthRevenue,
            'pending_orders'     => $pendingOrders,
            'total_teachers'     => User::where('role', 'teacher')->count(),
            'total_students'     => User::where('role', 'student')->count(),
            'total_unverified'   => User::whereNull('email_verified_at')->count(),
            'currency'           => strtoupper($settings->get('currency', config('app.currency', 'MYR'))),
            'months'             => $monthLabels,
            'revenue_data'       => $revenueData,
            'calendar_events'    => $calendarEvents,
        ]);
    }

    /* ============================
     * Show Payments
     * ============================ */
    public function payments()
    {
        $payments = Payment::all();
        return view('admin.payments', compact('payments'));
    }

    /* ============================
     * Show Students
     * ============================ */
    public function students()
    {
        $students = User::where('role', 'student')->get();
        return view('admin.students', compact('students'));
    }

    /* ============================
     * Show Create Student Form
     * ============================ */
    public function createStudent()
    {
        return view('admin.create-student');
    }
    /* ============================
     * Store Student
     * ============================ */
    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password'     => Hash::make($validated['password']),
            'role'         => 'student',
        ]);

        return redirect()
            ->route('admin.students')
            ->with('success', 'Student created successfully');
    }

    /* ============================
     * Edit Student
     * ============================ */
    public function editStudent($id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        return view('admin.edit-student', compact('student'));
    }
    /* ============================
     * Update Student
     * ============================ */
    public function updateStudent(Request $request, $id)
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$student->id}",
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $student->update([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? $student->phone_number,
        ]);

        if (!empty($validated['password'])) {
            $student->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()
            ->route('admin.students')
            ->with('success', 'Student updated successfully');
    }
    /* ============================
     * Destroy Student
     * ============================ */
    public function destroyStudent($id)
    {
        $student = User::findOrFail($id);
        $student->delete(); // <-- soft delete

        return redirect()
            ->route('admin.students')
            ->with('success', 'Student deleted successfully');
    }

    /* ============================
     * Show Teachers
     * ============================ */
    public function teachers()
    {
        $teachers = User::where('role', 'teacher')->get();
        return view('admin.teachers', compact('teachers'));
    }

    /* ============================
     * Show Create Teacher Form
     * ============================ */
    public function createTeacher()
    {
        return view('admin.create-teacher');
    }

    /* ============================
     * Store Teacher
     * ============================ */
    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password'     => Hash::make($validated['password']),
            'role'         => 'teacher',
        ]);

        return redirect()
            ->route('admin.teachers')
            ->with('success', 'Teacher created successfully');
    }

    /* ============================
     * Edit Teacher
     * ============================ */
    public function editTeacher($id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        return view('admin.edit-teacher', compact('teacher'));
    }

    /* ============================
     * Update Teacher
     * ============================ */
    public function updateTeacher(Request $request, $id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$teacher->id}",
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $teacher->update([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? $teacher->phone_number,
        ]);

        if (!empty($validated['password'])) {
            $teacher->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()
            ->route('admin.teachers')
            ->with('success', 'Teacher updated successfully');
    }

    /* ============================
     * Destroy Teacher
     * ============================ */
    public function destroyTeacher($id)
    {
        $teacher = User::findOrFail($id);
        $teacher->delete(); // <-- soft delete
        
        return redirect()
            ->route('admin.teachers')
            ->with('success', 'Teacher deleted successfully');
    }

    /* ============================
     * Show Admins
     * ============================ */
    public function admins()
    {
        $admins = User::where('role', 'admin')->get();
        return view('admin.admins', compact('admins'));
    }

    /* ============================
     * Show Studio Settings
     * ============================ */
    public function studioSettings()
    {
        return view('admin.studio-settings');
    }

    /* ============================
     * Update Studio Settings
     * ============================ */
    public function updateStudioSettings(Request $request)
    {
        $studio = Studio::first();
        $studio->update($request->all());
        session()->flash('success', 'Studio settings updated successfully');
        return redirect()->route('admin.studio-settings');  
    }
    /* ============================
     * Show Create Admin Form
     * ============================ */
    public function create()
    {
        return view('admin.create-admin');
    }

    /* ============================
     * Store Admin
     * ============================ */
    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|unique:users,email',
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? null,
            'password'     => Hash::make($validated['password']),
            'role'         => 'admin',
        ]);

        return redirect()
            ->route('admin.admins')
            ->with('success', 'Admin created successfully');
    }

    /* ============================
     * Edit Admin
     * ============================ */
    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        return view('admin.edit-admin', compact('admin'));
    }

    /* ============================
     * Update Admin
     * ============================ */
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$admin->id}",
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'nullable|string|min:8|confirmed',
        ]);

        $admin->update([
            'name'         => $validated['name'],
            'email'        => $validated['email'],
            'phone_number' => $validated['phone_number'] ?? $admin->phone_number,
        ]);

        if (!empty($validated['password'])) {
            $admin->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()
            ->route('admin.admins')
            ->with('success', 'Admin updated successfully');
    }

    /* ============================
     * Delete Admin
     * ============================ */
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $admin->delete(); // <-- soft delete
        
        return redirect()
            ->route('admin.admins')
            ->with('success', 'Admin deleted successfully');
    }

}