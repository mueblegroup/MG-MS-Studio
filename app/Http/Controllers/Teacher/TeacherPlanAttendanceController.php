<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Plan;
use App\Models\PlanSession;
use App\Models\UserPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherPlanAttendanceController extends Controller
{
    public function show(int $plan, int $session)
    {
        $teacherId = Auth::id();

        $planModel = Plan::query()
            ->where('id', $plan)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        $sessionModel = PlanSession::query()
            ->where('plan_id', $planModel->id)
            ->where('id', $session)
            ->firstOrFail();

        $userPlans = UserPlan::with('user')
            ->where('plan_id', $planModel->id)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        $attendanceMap = Attendance::where('plan_session_id', $sessionModel->id)
            ->whereIn('user_id', $userPlans->pluck('user_id')->all())
            ->get()
            ->keyBy('user_id');

        $students = $userPlans->map(function ($up) use ($attendanceMap) {
            $up->attendance = $attendanceMap->get($up->user_id);
            return $up;
        });

        return view('teacher.attendance.plan-session', [
            'plan' => $planModel,
            'session' => $sessionModel,
            'students' => $students,
        ]);
    }

    public function mark(Request $request, int $plan, int $session, int $user)
    {
        $teacherId = Auth::id();

        $validated = $request->validate([
            'status' => 'required|in:attended,no_show,reset',
        ]);

        // Verify plan belongs to teacher
        $planModel = Plan::query()
            ->where('id', $plan)
            ->where('teacher_id', $teacherId)
            ->firstOrFail();

        // Verify session belongs to plan
        $sessionExists = PlanSession::where('plan_id', $planModel->id)
            ->where('id', $session)
            ->exists();

        if (!$sessionExists) {
            return back()->with('error', 'Invalid plan session.');
        }

        // Verify user is active in this plan
        $active = UserPlan::where('plan_id', $planModel->id)
            ->where('user_id', $user)
            ->where('is_active', 1)
            ->exists();

        if (!$active) {
            return back()->with('error', 'User is not active in this plan.');
        }

        if ($validated['status'] === 'reset') {
            Attendance::where('plan_session_id', $session)
                ->where('user_id', $user)
                ->delete();

            return back()->with('success', 'Attendance reset.');
        }

        Attendance::updateOrCreate(
            [
                'plan_session_id' => $session,
                'user_id' => $user,
            ],
            [
                'status' => $validated['status'],
                'attended_at' => $validated['status'] === 'attended' ? now() : null,
            ]
        );

        return back()->with('success', 'Attendance updated.');
    }
}