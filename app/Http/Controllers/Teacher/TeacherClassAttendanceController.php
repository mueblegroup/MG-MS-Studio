<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeacherClassAttendanceController extends Controller
{
    public function show(int $session)
    {
        $teacherId = Auth::id();

        $sessionModel = ClassSession::with(['classModel.teacher'])
            ->where('id', $session)
            ->whereHas('classModel', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->firstOrFail();

        $assignments = ClassSessionAssignment::with(['student', 'attendance'])
            ->where('class_session_id', $sessionModel->id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        return view('teacher.attendance.class-session', [
            'session' => $sessionModel,
            'assignments' => $assignments,
        ]);
    }

    public function mark(Request $request, int $session, int $assignment)
    {
        $teacherId = Auth::id();

        $validated = $request->validate([
            'status' => 'required|in:attended,no_show,reset',
        ]);

        // Ensure session belongs to this teacher
        $sessionModel = ClassSession::query()
            ->where('id', $session)
            ->whereHas('classModel', function ($q) use ($teacherId) {
                $q->where('teacher_id', $teacherId);
            })
            ->firstOrFail();

        $assignmentModel = ClassSessionAssignment::query()
            ->where('class_session_id', $sessionModel->id)
            ->where('id', $assignment)
            ->firstOrFail();

        if ($validated['status'] === 'reset') {
            Attendance::where('class_session_assignment_id', $assignmentModel->id)->delete();
            return back()->with('success', 'Attendance reset.');
        }

        Attendance::updateOrCreate(
            ['class_session_assignment_id' => $assignmentModel->id],
            [
                'user_id' => $assignmentModel->user_id,
                'class_session_id' => $sessionModel->id,
                'class_session_assignment_id' => $assignmentModel->id,
                'status' => $validated['status'],
                'attended_at' => $validated['status'] === 'attended' ? now() : null,
            ]
        );

        return back()->with('success', 'Attendance updated.');
    }
}