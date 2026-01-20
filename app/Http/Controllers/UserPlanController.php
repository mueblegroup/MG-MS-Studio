<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Illuminate\Http\Request;

class UserPlanController extends Controller
{
    public function index(Request $request)
    {
        $search = trim($request->query('q', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $assignments = UserPlan::query()
            ->with([
                'plan:id,name,price',
                'user:id,name,email',
            ])
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('plan', function ($p) use ($search) {
                    $p->where('name', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.planassignments.index', compact('assignments', 'search', 'perPage'));
    }

    public function create()
    {
        $students = User::where('role', 'student')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $plans = Plan::orderBy('name')
            ->get(['id', 'name', 'price']);

        return view('admin.planassignments.create', compact('students', 'plans'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'plan_id' => 'required|exists:plans,id',
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'is_active' => 'required|in:0,1',
        ]);

        UserPlan::create([
            'user_id' => $validated['user_id'],
            'plan_id' => $validated['plan_id'],
            'starts_on' => $validated['starts_on'] ?? null,
            'ends_on' => $validated['ends_on'] ?? null,
            'is_active' => (bool) $validated['is_active'],
        ]);

        return redirect()
            ->route('admin.planassignments.index')
            ->with('success', 'Plan assigned to student successfully.');
    }

    public function destroy(UserPlan $userPlan)
    {
        $userPlan->delete();

        return redirect()
            ->route('admin.planassignments.index')
            ->with('success', 'Plan assignment removed.');
    }
}
