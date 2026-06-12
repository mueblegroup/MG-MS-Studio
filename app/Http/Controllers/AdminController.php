<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Payment;
use App\Models\ClassModel;
use App\Models\ClassSession;
use App\Models\Order;
use App\Models\Studio;
use App\Services\StudioSettingsService;
use App\Support\TenantManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    private function tenantId(): int
    {
        $studioId = app(TenantManager::class)->id() ?: auth()->user()?->studio_id;

        abort_if(! $studioId, 403, 'Studio context is required.');

        return (int) $studioId;
    }

    private function usersForTenant(string $role)
    {
        return User::query()
            ->where('studio_id', $this->tenantId())
            ->where('role', $role);
    }

    private function uniqueEmailRule(?int $ignoreUserId = null): mixed
    {
        $rule = Rule::unique('users', 'email')
            ->where(fn ($query) => $query->where('studio_id', $this->tenantId()));

        return $ignoreUserId ? $rule->ignore($ignoreUserId) : $rule;
    }

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
            'total_teachers'     => $this->usersForTenant('teacher')->count(),
            'total_students'     => $this->usersForTenant('student')->count(),
            'total_unverified'   => User::where('studio_id', $this->tenantId())->whereNull('email_verified_at')->count(),
            'currency'           => strtoupper($settings->get('currency', config('app.currency', 'MYR'))),
            'months'             => $monthLabels,
            'revenue_data'       => $revenueData,
            'calendar_events'    => $calendarEvents,
        ]);
    }

    public function payments()
    {
        $payments = Payment::all();
        return view('admin.payments', compact('payments'));
    }

    public function students()
    {
        $students = $this->usersForTenant('student')->get();
        return view('admin.students', compact('students'));
    }

    public function createStudent()
    {
        return view('admin.create-student');
    }

    public function storeStudent(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule()],
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'studio_id'    => $this->tenantId(),
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

    public function editStudent($id)
    {
        $student = $this->usersForTenant('student')->findOrFail($id);

        return view('admin.edit-student', compact('student'));
    }

    public function updateStudent(Request $request, $id)
    {
        $student = $this->usersForTenant('student')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule($student->id)],
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

    public function destroyStudent($id)
    {
        $student = $this->usersForTenant('student')->findOrFail($id);
        $student->delete();

        return redirect()
            ->route('admin.students')
            ->with('success', 'Student deleted successfully');
    }

    public function teachers()
    {
        $teachers = $this->usersForTenant('teacher')->get();
        return view('admin.teachers', compact('teachers'));
    }

    public function createTeacher()
    {
        return view('admin.create-teacher');
    }

    public function storeTeacher(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule()],
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'studio_id'    => $this->tenantId(),
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

    public function editTeacher($id)
    {
        $teacher = $this->usersForTenant('teacher')->findOrFail($id);

        return view('admin.edit-teacher', compact('teacher'));
    }

    public function updateTeacher(Request $request, $id)
    {
        $teacher = $this->usersForTenant('teacher')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule($teacher->id)],
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

    public function destroyTeacher($id)
    {
        $teacher = $this->usersForTenant('teacher')->findOrFail($id);
        $teacher->delete();

        return redirect()
            ->route('admin.teachers')
            ->with('success', 'Teacher deleted successfully');
    }

    public function admins()
    {
        $admins = $this->usersForTenant('admin')->get();
        return view('admin.admins', compact('admins'));
    }

    public function studioSettings()
    {
        return view('admin.studio-settings');
    }

    public function updateStudioSettings(Request $request)
    {
        $studio = Studio::where('id', $this->tenantId())->firstOrFail();
        $studio->update($request->all());
        session()->flash('success', 'Studio settings updated successfully');
        return redirect()->route('admin.studio-settings');
    }

    public function create()
    {
        return view('admin.create-admin');
    }

    public function storeAdmin(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule()],
            'phone_number'  => 'nullable|string|max:20',
            'password'      => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'studio_id'    => $this->tenantId(),
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

    public function edit($id)
    {
        $admin = $this->usersForTenant('admin')->findOrFail($id);

        return view('admin.edit-admin', compact('admin'));
    }

    public function update(Request $request, $id)
    {
        $admin = $this->usersForTenant('admin')->findOrFail($id);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => ['required', 'email', $this->uniqueEmailRule($admin->id)],
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

    public function destroy($id)
    {
        $admin = $this->usersForTenant('admin')->findOrFail($id);
        $admin->delete();

        return redirect()
            ->route('admin.admins')
            ->with('success', 'Admin deleted successfully');
    }
}
