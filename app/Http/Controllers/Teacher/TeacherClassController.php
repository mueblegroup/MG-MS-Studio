<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Auth;

class TeacherClassController extends Controller
{
    public function index()
    {
        $teacherId = Auth::id();

        $classes = ClassModel::query()
            ->where('teacher_id', $teacherId)
            ->orderBy('id', 'desc')
            ->get();

        return view('teacher.classes.index', compact('classes'));
    }

    public function show(int $class)
    {
        $teacherId = Auth::id();

        $classModel = ClassModel::query()
            ->where('teacher_id', $teacherId)
            ->where('id', $class)
            ->firstOrFail();

        $sessions = $classModel->sessions()
            ->orderBy('start_time')
            ->get();

        return view('teacher.classes.show', compact('classModel', 'sessions'));
    }
}