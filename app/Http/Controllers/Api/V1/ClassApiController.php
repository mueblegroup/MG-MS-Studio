<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\ClassModel;
use App\Models\ClassSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = ClassModel::query()->with('teacher')->latest();

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->integer('teacher_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where('name', 'like', "%{$search}%");
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Classes loaded.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'string', 'max:50'],
            'custom_frequency_days' => ['nullable', 'integer', 'min:1'],
            'until_date' => ['nullable', 'date'],
        ]);

        $class = ClassModel::create($validated);

        return $this->success($class, 'Class created.', 201);
    }

    public function show(ClassModel $class): JsonResponse
    {
        return $this->success($class->load(['teacher', 'sessions']), 'Class loaded.');
    }

    public function update(Request $request, ClassModel $class): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'teacher_id' => ['nullable', 'exists:users,id'],
            'type' => ['nullable', 'string', 'max:100'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurrence_frequency' => ['nullable', 'string', 'max:50'],
            'custom_frequency_days' => ['nullable', 'integer', 'min:1'],
            'until_date' => ['nullable', 'date'],
        ]);

        $class->update($validated);

        return $this->success($class->fresh(['teacher', 'sessions']), 'Class updated.');
    }

    public function destroy(ClassModel $class): JsonResponse
    {
        $class->delete();

        return $this->success(null, 'Class deleted.');
    }

    public function sessions(Request $request, ClassModel $class): JsonResponse
    {
        return $this->paginated(
            $class->sessions()->latest('start_time')->paginate($request->integer('per_page', 25)),
            'Class sessions loaded.'
        );
    }

    public function storeSession(Request $request, ClassModel $class): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'venue_name' => ['nullable', 'string', 'max:255'],
        ]);

        $session = $class->sessions()->create($validated);

        return $this->success($session, 'Class session created.', 201);
    }

    public function updateSession(Request $request, ClassSession $session): JsonResponse
    {
        $validated = $request->validate([
            'start_time' => ['sometimes', 'required', 'date'],
            'end_time' => ['sometimes', 'required', 'date', 'after:start_time'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'venue_name' => ['nullable', 'string', 'max:255'],
        ]);

        $session->update($validated);

        return $this->success($session->fresh(), 'Class session updated.');
    }

    public function destroySession(ClassSession $session): JsonResponse
    {
        $session->delete();

        return $this->success(null, 'Class session deleted.');
    }
}
