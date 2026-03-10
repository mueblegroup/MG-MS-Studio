<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $studentId = Auth::id();

        $type = $request->get('type');     // class|plan|null
        $status = $request->get('status'); // attended|no_show|null
        $range = $request->get('range', 'month'); // month|all

        $from = null;
        if ($range === 'month') {
            $from = Carbon::now()->startOfMonth();
        }

        // ---------
        // Class attendance rows
        // ---------
        $classRows = DB::table('class_session_assignments as a')
            ->join('class_sessions as s', 'a.class_session_id', '=', 's.id')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->leftJoin('attendances as at', 'at.class_session_assignment_id', '=', 'a.id')
            ->whereNull('a.deleted_at')
            ->where('a.user_id', $studentId)
            ->when($from, fn($q) => $q->where('s.start_time', '>=', $from))
            ->when($status, fn($q) => $q->where('at.status', $status))
            ->select([
                's.start_time',
                's.end_time',
                's.venue_name',
                'c.name as title',
                DB::raw("'class' as type"),
                'at.status',
                'at.attended_at',
            ]);

        // ---------
        // Plan attendance rows
        // ---------
        $planRows = DB::table('user_plans as up')
            ->join('plans as p', 'up.plan_id', '=', 'p.id')
            ->join('plan_sessions as ps', 'ps.plan_id', '=', 'p.id')
            ->leftJoin('attendances as at', function ($join) use ($studentId) {
                $join->on('at.plan_session_id', '=', 'ps.id')
                    ->where('at.user_id', '=', $studentId);
            })
            ->where('up.user_id', $studentId)
            ->where('up.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('p.is_active', 1)
            ->whereNull('ps.deleted_at')
            ->when($from, fn($q) => $q->where('ps.start_time', '>=', $from))
            ->when($status, fn($q) => $q->where('at.status', $status))
            ->where(function ($q) {
                $q->whereNull('up.starts_on')->orWhereColumn('ps.start_time', '>=', 'up.starts_on');
            })
            ->where(function ($q) {
                $q->whereNull('up.ends_on')->orWhereColumn('ps.start_time', '<=', 'up.ends_on');
            })
            ->select([
                'ps.start_time',
                'ps.end_time',
                'ps.venue_name',
                DB::raw("COALESCE(NULLIF(ps.session_name,''), p.name) as title"),
                DB::raw("'plan' as type"),
                'at.status',
                'at.attended_at',
            ]);

        $items = collect();

        if ($type !== 'plan') {
            $items = $items->merge($classRows->get());
        }
        if ($type !== 'class') {
            $items = $items->merge($planRows->get());
        }

        $items = $items
            ->sortByDesc('start_time')
            ->values();

        return view('student.attendance.index', compact('items', 'type', 'status', 'range'));
    }
}