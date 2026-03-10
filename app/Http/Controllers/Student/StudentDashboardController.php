<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentDashboardController extends Controller
{
    public function index()
    {
        $studentId = Auth::id();

        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // -----------------------
        // Today's class sessions (assigned)
        // -----------------------
        $todayClass = DB::table('class_session_assignments as a')
            ->join('class_sessions as s', 'a.class_session_id', '=', 's.id')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->whereNull('a.deleted_at')
            ->where('a.user_id', $studentId)
            ->whereDate('s.start_time', $today)
            ->orderBy('s.start_time')
            ->select([
                's.id as session_id',
                's.start_time',
                's.end_time',
                's.venue_name',
                'c.name as title',
                DB::raw("'class' as type"),
            ])
            ->get();

        $tomorrowClass = DB::table('class_session_assignments as a')
            ->join('class_sessions as s', 'a.class_session_id', '=', 's.id')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->whereNull('a.deleted_at')
            ->where('a.user_id', $studentId)
            ->whereDate('s.start_time', $tomorrow)
            ->orderBy('s.start_time')
            ->select([
                's.id as session_id',
                's.start_time',
                's.end_time',
                's.venue_name',
                'c.name as title',
                DB::raw("'class' as type"),
            ])
            ->get();

        // -----------------------
        // Today's plan sessions (active plan membership)
        // -----------------------
        $todayPlan = DB::table('user_plans as up')
            ->join('plans as p', 'up.plan_id', '=', 'p.id')
            ->join('plan_sessions as ps', 'ps.plan_id', '=', 'p.id')
            ->where('up.user_id', $studentId)
            ->where('up.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('p.is_active', 1)
            ->whereNull('ps.deleted_at')
            ->whereDate('ps.start_time', $today)
            ->where(function ($q) {
                // (optional) constrain to plan membership window if you use starts_on/ends_on
                $q->whereNull('up.starts_on')->orWhereColumn('ps.start_time', '>=', 'up.starts_on');
            })
            ->where(function ($q) {
                $q->whereNull('up.ends_on')->orWhereColumn('ps.start_time', '<=', 'up.ends_on');
            })
            ->orderBy('ps.start_time')
            ->select([
                'ps.id as session_id',
                'ps.start_time',
                'ps.end_time',
                'ps.venue_name',
                DB::raw("COALESCE(NULLIF(ps.session_name,''), p.name) as title"),
                DB::raw("'plan' as type"),
            ])
            ->get();

        $tomorrowPlan = DB::table('user_plans as up')
            ->join('plans as p', 'up.plan_id', '=', 'p.id')
            ->join('plan_sessions as ps', 'ps.plan_id', '=', 'p.id')
            ->where('up.user_id', $studentId)
            ->where('up.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('p.is_active', 1)
            ->whereNull('ps.deleted_at')
            ->whereDate('ps.start_time', $tomorrow)
            ->where(function ($q) {
                $q->whereNull('up.starts_on')->orWhereColumn('ps.start_time', '>=', 'up.starts_on');
            })
            ->where(function ($q) {
                $q->whereNull('up.ends_on')->orWhereColumn('ps.start_time', '<=', 'up.ends_on');
            })
            ->orderBy('ps.start_time')
            ->select([
                'ps.id as session_id',
                'ps.start_time',
                'ps.end_time',
                'ps.venue_name',
                DB::raw("COALESCE(NULLIF(ps.session_name,''), p.name) as title"),
                DB::raw("'plan' as type"),
            ])
            ->get();

        // Merge lists
        $todaySessions = collect()->merge($todayClass)->merge($todayPlan)->sortBy('start_time')->values();
        $tomorrowSessions = collect()->merge($tomorrowClass)->merge($tomorrowPlan)->sortBy('start_time')->values();

        // -----------------------
        // Attendance rate (past sessions only)
        // Denominator: all assigned/active sessions with start_time < now
        // Numerator: attended status
        // -----------------------
        $now = Carbon::now();

        $pastClassAssignments = DB::table('class_session_assignments as a')
            ->join('class_sessions as s', 'a.class_session_id', '=', 's.id')
            ->whereNull('a.deleted_at')
            ->where('a.user_id', $studentId)
            ->where('s.start_time', '<', $now)
            ->pluck('a.id'); // assignment ids

        $pastPlanSessionIds = DB::table('user_plans as up')
            ->join('plans as p', 'up.plan_id', '=', 'p.id')
            ->join('plan_sessions as ps', 'ps.plan_id', '=', 'p.id')
            ->where('up.user_id', $studentId)
            ->where('up.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('p.is_active', 1)
            ->whereNull('ps.deleted_at')
            ->where('ps.start_time', '<', $now)
            ->where(function ($q) {
                $q->whereNull('up.starts_on')->orWhereColumn('ps.start_time', '>=', 'up.starts_on');
            })
            ->where(function ($q) {
                $q->whereNull('up.ends_on')->orWhereColumn('ps.start_time', '<=', 'up.ends_on');
            })
            ->pluck('ps.id');

        $totalPast = $pastClassAssignments->count() + $pastPlanSessionIds->count();

        $attendedClass = DB::table('attendances')
            ->where('user_id', $studentId)
            ->whereIn('class_session_assignment_id', $pastClassAssignments->all())
            ->where('status', 'attended')
            ->count();

        $attendedPlan = DB::table('attendances')
            ->where('user_id', $studentId)
            ->whereIn('plan_session_id', $pastPlanSessionIds->all())
            ->where('status', 'attended')
            ->count();

        $attendedTotal = $attendedClass + $attendedPlan;

        $attendanceRate = $totalPast > 0 ? round(($attendedTotal / $totalPast) * 100, 1) : null;

        return view('student.dashboard', compact(
            'todaySessions',
            'tomorrowSessions',
            'attendanceRate',
            'totalPast',
            'attendedTotal'
        ));
    }
}