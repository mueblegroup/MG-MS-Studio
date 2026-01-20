<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\ClassSessionAssignment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClassAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);

        $assignments = ClassSessionAssignment::query()
            ->with([
                'student:id,name,email',
                'session:id,class_id,start_time,end_time,venue_name,capacity',
                'session.classModel:id,name,teacher_id,price,type',
                'session.classModel.teacher:id,name,email',
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->whereHas('student', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('session.classModel', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.class_assignments.index', compact('assignments', 'search'));
    }

    public function create()
    {
        $students = User::where('role', 'student')->orderBy('name')->get(['id', 'name', 'email']);

        $sessions = ClassSession::query()
            ->with(['classModel:id,name,teacher_id,price,type', 'classModel.teacher:id,name,email'])
            ->orderByDesc('start_time')
            ->get();

        return view('admin.class_assignments.create', compact('students', 'sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'class_session_id' => 'required|exists:class_sessions,id',
            'notes' => 'nullable|string|max:5000',
        ]);

        // Prevent duplicates (soft-deleted included)
        $existing = ClassSessionAssignment::withTrashed()
            ->where('user_id', $validated['user_id'])
            ->where('class_session_id', $validated['class_session_id'])
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->update([
                'assigned_by' => Auth::id(),
                'notes' => $validated['notes'] ?? null,
                'status' => 'assigned',
            ]);

            return redirect()
                ->route('admin.class-assignments.index')
                ->with('success', 'Assignment restored successfully.');
        }

        if ($existing) {
            return back()
                ->withErrors(['class_session_id' => 'This student is already assigned to that session.'])
                ->withInput();
        }

        ClassSessionAssignment::create([
            'user_id' => $validated['user_id'],
            'class_session_id' => $validated['class_session_id'],
            'assigned_by' => Auth::id(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'assigned',
        ]);

        return redirect()
            ->route('admin.class-assignments.index')
            ->with('success', 'Student assigned to class session successfully.');
    }

    public function destroy(ClassSessionAssignment $assignment)
    {
        $assignment->delete();

        return redirect()
            ->route('admin.class-assignments.index')
            ->with('success', 'Assignment removed.');
    }
}
