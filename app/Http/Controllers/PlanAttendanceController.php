<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Plan;
use App\Models\PlanSession;
use App\Models\UserPlan;
use Illuminate\Http\Request;

class PlanAttendanceController extends Controller
{
    public function show(int $planId, int $planSessionId)
    {
        $plan = Plan::findOrFail($planId);

        $session = PlanSession::where('plan_id', $planId)
            ->where('id', $planSessionId)
            ->firstOrFail();

        $userPlans = UserPlan::with('user')
            ->where('plan_id', $planId)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        // Pull all attendances in ONE query
        $attendanceMap = Attendance::where('plan_session_id', $planSessionId)
            ->whereIn('user_id', $userPlans->pluck('user_id')->all())
            ->get()
            ->keyBy('user_id');

        // attach attendance onto each userPlan row for blade convenience
        $students = $userPlans->map(function ($up) use ($attendanceMap) {
            $up->attendance = $attendanceMap->get($up->user_id);
            return $up;
        });

        return view('admin.attendance.plan-session', compact('plan', 'session', 'students'));
    }

    public function mark(Request $request, int $planId, int $planSessionId, int $userId)
    {
        $validated = $request->validate([
            'status' => 'required|in:attended,no_show,reset',
        ]);

        // Verify: plan_session belongs to plan
        $sessionExists = PlanSession::where('plan_id', $planId)
            ->where('id', $planSessionId)
            ->exists();

        if (!$sessionExists) {
            return back()->with('error', 'Invalid plan session.');
        }

        // Verify: user is active in this plan
        $active = UserPlan::where('plan_id', $planId)
            ->where('user_id', $userId)
            ->where('is_active', 1)
            ->exists();

        if (!$active) {
            return back()->with('error', 'User is not active in this plan.');
        }

        if ($validated['status'] === 'reset') {
            Attendance::where('plan_session_id', $planSessionId)
                ->where('user_id', $userId)
                ->delete();

            return back()->with('success', 'Attendance reset.');
        }

        Attendance::updateOrCreate(
            [
                'plan_session_id' => $planSessionId,
                'user_id' => $userId,
            ],
            [
                'status' => $validated['status'],
                'attended_at' => $validated['status'] === 'attended' ? now() : null,
            ]
        );

        return back()->with('success', 'Attendance updated.');
    }
}
