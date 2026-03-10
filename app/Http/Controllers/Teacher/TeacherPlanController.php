<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Support\Facades\Auth;

class TeacherPlanController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();

        $plans = Plan::query()
            ->where('teacher_id', $teacherId)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->get();

        return view('teacher.plans.index', compact('plans'));
    }

    public function show(int $plan)
    {
        $teacherId = Auth::id();

        $planModel = Plan::query()
            ->where('teacher_id', $teacherId)
            ->where('id', $plan)
            ->firstOrFail();

        $sessions = $planModel->sessions()
            ->whereNull('deleted_at')
            ->orderBy('start_time')
            ->get();

        return view('teacher.plans.show', compact('planModel', 'sessions'));
    }
}