<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\AppNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationApiController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = AppNotification::query()->with(['user', 'creator'])->latest();

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->boolean('unread')) {
            $query->whereNull('read_at');
        }

        return $this->paginated($query->paginate($request->integer('per_page', 25)), 'Notifications loaded.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
        ]);

        $validated['created_by'] = $request->user()->id;

        $notification = AppNotification::create($validated);

        return $this->success($notification, 'Notification created.', 201);
    }

    public function show(AppNotification $notification): JsonResponse
    {
        return $this->success($notification->load(['user', 'creator']), 'Notification loaded.');
    }

    public function update(Request $request, AppNotification $notification): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
            'action_url' => ['nullable', 'string', 'max:255'],
            'data' => ['nullable', 'array'],
            'read_at' => ['nullable', 'date'],
        ]);

        $notification->update($validated);

        return $this->success($notification->fresh(['user', 'creator']), 'Notification updated.');
    }

    public function destroy(AppNotification $notification): JsonResponse
    {
        $notification->delete();

        return $this->success(null, 'Notification deleted.');
    }
}
