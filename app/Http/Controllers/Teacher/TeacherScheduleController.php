<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeacherScheduleController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();
        $today = Carbon::today();

        $startMonth = $today->copy()->startOfMonth();
        $endMonth = $today->copy()->endOfMonth();

        // Class sessions for teacher
        $classSessions = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacherId)
            ->whereBetween('class_sessions.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->orderBy('class_sessions.start_time')
            ->select([
                'class_sessions.id as session_id',
                'class_sessions.start_time',
                'class_sessions.end_time',
                'class_sessions.venue_name',
                'classes.name as title',
            ])
            ->get()
            ->map(fn($s) => (object)[
                'type' => 'class',
                'title' => $s->title,
                'venue' => $s->venue_name,
                'start' => $s->start_time,
                'end' => $s->end_time,
                'attendance_url' => route('teacher.classes.attendance.show', $s->session_id),
            ]);

        // Plan sessions for teacher
        $planSessions = DB::table('plan_sessions')
            ->join('plans', 'plan_sessions.plan_id', '=', 'plans.id')
            ->where('plans.teacher_id', $teacherId)
            ->whereNull('plan_sessions.deleted_at')
            ->whereBetween('plan_sessions.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->orderBy('plan_sessions.start_time')
            ->select([
                'plan_sessions.id as session_id',
                'plan_sessions.plan_id',
                'plan_sessions.session_name',
                'plan_sessions.start_time',
                'plan_sessions.end_time',
                'plan_sessions.venue_name',
                'plans.name as plan_name',
            ])
            ->get()
            ->map(fn($s) => (object)[
                'type' => 'plan',
                'title' => ($s->session_name ?: $s->plan_name),
                'venue' => $s->venue_name,
                'start' => $s->start_time,
                'end' => $s->end_time,
                'attendance_url' => route('teacher.plans.sessions.attendance.show', [$s->plan_id, $s->session_id]),
            ]);

        $items = $classSessions->merge($planSessions)->sortBy('start')->values();

        $calendarEvents = $items->map(fn($i) => [
            'title' => $i->title . ($i->venue ? ' • ' . $i->venue : ''),
            'start' => $i->start,
            'end'   => $i->end,
            'url'   => $i->attendance_url,
        ])->values()->all();

        return view('teacher.schedule.index', compact('items', 'calendarEvents'));
    }
}