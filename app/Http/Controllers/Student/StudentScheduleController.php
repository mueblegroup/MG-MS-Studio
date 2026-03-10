<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentScheduleController extends Controller
{
    public function index()
    {
        $studentId = Auth::id();

        $startMonth = Carbon::now()->startOfMonth();
        $endMonth   = Carbon::now()->endOfMonth();

        // Class sessions assigned
        $classItems = DB::table('class_session_assignments as a')
            ->join('class_sessions as s', 'a.class_session_id', '=', 's.id')
            ->join('classes as c', 's.class_id', '=', 'c.id')
            ->whereNull('a.deleted_at')
            ->where('a.user_id', $studentId)
            ->whereBetween('s.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->orderBy('s.start_time')
            ->select([
                's.start_time as start',
                's.end_time as end',
                's.venue_name as venue',
                'c.name as title',
                DB::raw("'class' as type"),
            ])
            ->get();

        // Plan sessions for active plans
        $planItems = DB::table('user_plans as up')
            ->join('plans as p', 'up.plan_id', '=', 'p.id')
            ->join('plan_sessions as ps', 'ps.plan_id', '=', 'p.id')
            ->where('up.user_id', $studentId)
            ->where('up.is_active', 1)
            ->whereNull('p.deleted_at')
            ->where('p.is_active', 1)
            ->whereNull('ps.deleted_at')
            ->whereBetween('ps.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->where(function ($q) {
                $q->whereNull('up.starts_on')->orWhereColumn('ps.start_time', '>=', 'up.starts_on');
            })
            ->where(function ($q) {
                $q->whereNull('up.ends_on')->orWhereColumn('ps.start_time', '<=', 'up.ends_on');
            })
            ->orderBy('ps.start_time')
            ->select([
                'ps.start_time as start',
                'ps.end_time as end',
                'ps.venue_name as venue',
                DB::raw("COALESCE(NULLIF(ps.session_name,''), p.name) as title"),
                DB::raw("'plan' as type"),
            ])
            ->get();

        $items = collect()->merge($classItems)->merge($planItems)->sortBy('start')->values();

        $calendarEvents = $items->map(function ($i) {
            return [
                'title' => $i->title . ($i->venue ? ' • ' . $i->venue : ''),
                'start' => $i->start,
                'end'   => $i->end,
            ];
        })->values()->all();

        return view('student.schedule.index', compact('items', 'calendarEvents'));
    }
}