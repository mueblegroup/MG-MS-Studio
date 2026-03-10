<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class TeacherController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();
        $currency  = config('app.currency', 'RM');

        // -----------------------
        // Stats (Teacher-based)
        // -----------------------
        $totalClasses = DB::table('classes')
            ->where('teacher_id', $teacherId)
            ->count();

        $totalPlans = DB::table('plans')
            ->where('teacher_id', $teacherId)
            ->whereNull('deleted_at')
            ->where('is_active', 1)
            ->count();

        // Optional (later, when payments are teacher-linked)
        $totalProfit = 0;

        // -----------------------
        // Today / Tomorrow sessions
        // -----------------------
        $today    = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        // Class sessions filtered by teacher via classes.teacher_id
        $todayClassSessions = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacherId)
            ->whereDate('class_sessions.start_time', $today)
            ->orderBy('class_sessions.start_time')
            ->select([
                'class_sessions.*',
                'classes.name as class_name',
                'classes.type as class_type',
            ])
            ->limit(20)
            ->get();

        $tomorrowClassSessions = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacherId)
            ->whereDate('class_sessions.start_time', $tomorrow)
            ->orderBy('class_sessions.start_time')
            ->select([
                'class_sessions.*',
                'classes.name as class_name',
                'classes.type as class_type',
            ])
            ->limit(20)
            ->get();

        // Plan sessions filtered by teacher via plans.teacher_id
        $todayPlanSessions = DB::table('plan_sessions')
            ->join('plans', 'plan_sessions.plan_id', '=', 'plans.id')
            ->where('plans.teacher_id', $teacherId)
            ->whereNull('plans.deleted_at')
            ->where('plans.is_active', 1)
            ->whereNull('plan_sessions.deleted_at')
            ->whereDate('plan_sessions.start_time', $today)
            ->orderBy('plan_sessions.start_time')
            ->select([
                'plan_sessions.*',
                'plans.name as plan_name',
            ])
            ->limit(20)
            ->get();

        $tomorrowPlanSessions = DB::table('plan_sessions')
            ->join('plans', 'plan_sessions.plan_id', '=', 'plans.id')
            ->where('plans.teacher_id', $teacherId)
            ->whereNull('plans.deleted_at')
            ->where('plans.is_active', 1)
            ->whereNull('plan_sessions.deleted_at')
            ->whereDate('plan_sessions.start_time', $tomorrow)
            ->orderBy('plan_sessions.start_time')
            ->select([
                'plan_sessions.*',
                'plans.name as plan_name',
            ])
            ->limit(20)
            ->get();

        // Combine for display lists (today/tomorrow)
        $todaySessions = collect()
            ->merge($todayClassSessions->map(fn ($s) => (object) [
                'title'    => $s->class_name,
                'subtitle' => $s->venue_name ?? null,
                'start'    => $s->start_time,
                'end'      => $s->end_time,
                'type'     => 'class',
            ]))
            ->merge($todayPlanSessions->map(fn ($s) => (object) [
                'title'    => $s->session_name ?: ($s->plan_name . ' Session'),
                'subtitle' => $s->venue_name ?? $s->plan_name,
                'start'    => $s->start_time,
                'end'      => $s->end_time,
                'type'     => 'plan',
            ]))
            ->sortBy('start')
            ->values();

        $tomorrowSessions = collect()
            ->merge($tomorrowClassSessions->map(fn ($s) => (object) [
                'title'    => $s->class_name,
                'subtitle' => $s->venue_name ?? null,
                'start'    => $s->start_time,
                'end'      => $s->end_time,
                'type'     => 'class',
            ]))
            ->merge($tomorrowPlanSessions->map(fn ($s) => (object) [
                'title'    => $s->session_name ?: ($s->plan_name . ' Session'),
                'subtitle' => $s->venue_name ?? $s->plan_name,
                'start'    => $s->start_time,
                'end'      => $s->end_time,
                'type'     => 'plan',
            ]))
            ->sortBy('start')
            ->values();

        // -----------------------
        // Calendar events (this month)
        // -----------------------
        $startMonth = $today->copy()->startOfMonth();
        $endMonth   = $today->copy()->endOfMonth();

        $monthClassSessions = DB::table('class_sessions')
            ->join('classes', 'class_sessions.class_id', '=', 'classes.id')
            ->where('classes.teacher_id', $teacherId)
            ->whereBetween('class_sessions.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->orderBy('class_sessions.start_time')
            ->select([
                'class_sessions.start_time',
                'class_sessions.end_time',
                'class_sessions.venue_name',
                'classes.name as class_name',
            ])
            ->get();

        $monthPlanSessions = DB::table('plan_sessions')
            ->join('plans', 'plan_sessions.plan_id', '=', 'plans.id')
            ->where('plans.teacher_id', $teacherId)
            ->whereNull('plans.deleted_at')
            ->where('plans.is_active', 1)
            ->whereNull('plan_sessions.deleted_at')
            ->whereBetween('plan_sessions.start_time', [$startMonth->toDateTimeString(), $endMonth->toDateTimeString()])
            ->orderBy('plan_sessions.start_time')
            ->select([
                'plan_sessions.session_name',
                'plan_sessions.start_time',
                'plan_sessions.end_time',
                'plan_sessions.venue_name',
                'plans.name as plan_name',
            ])
            ->get();

        $calendarEvents = collect()
            ->merge($monthClassSessions->map(fn ($s) => [
                'title' => $s->class_name . ($s->venue_name ? ' • ' . $s->venue_name : ''),
                'start' => $s->start_time,
                'end'   => $s->end_time,
            ]))
            ->merge($monthPlanSessions->map(fn ($s) => [
                'title' => ($s->session_name ?: $s->plan_name) . ($s->venue_name ? ' • ' . $s->venue_name : ''),
                'start' => $s->start_time,
                'end'   => $s->end_time,
            ]))
            ->values()
            ->all();

        // Doughnut chart
        $distLabels = ['Classes', 'Plans'];
        $distData   = [$totalClasses, $totalPlans];

        return view('teacher.dashboard', compact(
            'currency',
            'totalProfit',
            'totalClasses',
            'totalPlans',
            'todaySessions',
            'tomorrowSessions',
            'calendarEvents',
            'distLabels',
            'distData'
        ));
    }
    public function classes()
    {
        return view('teacher.classes.index'); // create this view later
    }

    public function plans()
    {
        return view('teacher.plans.index'); // create this view later
    }

}
