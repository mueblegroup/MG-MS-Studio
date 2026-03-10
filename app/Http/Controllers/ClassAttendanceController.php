<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use Illuminate\Http\Request;

class ClassAttendanceController extends Controller
{
    public function show(int $classSessionId)
    {
        $session = ClassSession::with(['classModel.teacher'])->findOrFail($classSessionId);

        $assignments = ClassSessionAssignment::with(['student', 'attendance'])
            ->where('class_session_id', $classSessionId)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        return view('admin.attendance.class-session', compact('session', 'assignments'));
    }

    public function mark(Request $request, int $classSessionId, int $assignmentId)
    {
        $validated = $request->validate([
            'status' => 'required|in:attended,no_show,reset',
        ]);

        $assignment = ClassSessionAssignment::where('class_session_id', $classSessionId)
            ->where('id', $assignmentId)
            ->firstOrFail();

        if ($validated['status'] === 'reset') {
            Attendance::where('class_session_assignment_id', $assignment->id)->delete();
            return back()->with('success', 'Attendance reset.');
        }

        Attendance::updateOrCreate(
            ['class_session_assignment_id' => $assignment->id],
            [
                'user_id' => $assignment->user_id,
                'class_session_id' => $classSessionId,
                'class_session_assignment_id' => $assignmentId,
                'status' => $validated['status'],
                'attended_at' => $validated['status'] === 'attended' ? now() : null,
            ]
        );

        return back()->with('success', 'Attendance updated.');
    }
}
