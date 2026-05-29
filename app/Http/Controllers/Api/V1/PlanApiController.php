<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Plan;
use App\Models\PlanSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Plan::query()->with('teacher')->latest();

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Plans loaded.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'string', 'max:50'],
            'custom_frequency_days' => ['nullable', 'integer', 'min:1'],
            'until_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan = Plan::create($validated);

        return $this->success($plan, 'Plan created.', 201);
    }

    public function show(Plan $plan): JsonResponse
    {
        return $this->success($plan->load(['teacher', 'sessions']), 'Plan loaded.');
    }

    public function update(Request $request, Plan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'string', 'max:50'],
            'custom_frequency_days' => ['nullable', 'integer', 'min:1'],
            'until_date' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $plan->update($validated);

        return $this->success($plan->fresh(['teacher', 'sessions']), 'Plan updated.');
    }

    public function destroy(Plan $plan): JsonResponse
    {
        $plan->delete();

        return $this->success(null, 'Plan deleted.');
    }

    public function sessions(Request $request, Plan $plan): JsonResponse
    {
        return $this->paginated(
            $plan->sessions()->latest('start_time')->paginate($request->integer('per_page', 25)),
            'Plan sessions loaded.'
        );
    }

    public function storeSession(Request $request, Plan $plan): JsonResponse
    {
        $validated = $request->validate([
            'session_name' => ['nullable', 'string', 'max:255'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'venue_name' => ['nullable', 'string', 'max:255'],
        ]);

        $session = $plan->sessions()->create($validated);

        return $this->success($session, 'Plan session created.', 201);
    }

    public function updateSession(Request $request, PlanSession $session): JsonResponse
    {
        $validated = $request->validate([
            'session_name' => ['nullable', 'string', 'max:255'],
            'start_time' => ['sometimes', 'required', 'date'],
            'end_time' => ['sometimes', 'required', 'date', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'venue_name' => ['nullable', 'string', 'max:255'],
        ]);

        $session->update($validated);

        return $this->success($session->fresh(), 'Plan session updated.');
    }

    public function destroySession(PlanSession $session): JsonResponse
    {
        $session->delete();

        return $this->success(null, 'Plan session deleted.');
    }
}
