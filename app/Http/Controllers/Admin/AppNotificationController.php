<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use App\Support\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppNotificationController extends Controller
{
    public function index()
    {
        $studioId = $this->currentStudioId();

        $notifications = AppNotification::with(['user', 'creator'])
            ->where('studio_id', $studioId)
            ->latest()
            ->paginate(20);

        $users = User::query()
            ->where('studio_id', $studioId)
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    public function create()
    {
        return redirect()->route('admin.notifications.index');
    }

    public function show(AppNotification $notification)
    {
        $this->assertCurrentStudioNotification($notification);

        return redirect()->route('admin.notifications.index')
            ->with('success', 'Notification: '.$notification->title.' — '.$notification->message);
    }

    public function edit(AppNotification $notification)
    {
        $this->assertCurrentStudioNotification($notification);

        return redirect()->route('admin.notifications.index')
            ->with('status', 'Notification editing is available through the notification management list.');
    }

    public function store(Request $request)
    {
        $studioId = $this->currentStudioId();

        $validated = $request->validate([
            'recipient_type' => 'required|in:all,specific',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'nullable|string|max:60',
            'action_url' => 'nullable|string|max:2048',
        ]);

        $eligibleUsers = User::query()->where('studio_id', $studioId);

        $userIds = $validated['recipient_type'] === 'all'
            ? $eligibleUsers->pluck('id')
            : $eligibleUsers
                ->whereIn('id', collect($validated['user_ids'] ?? [])->filter()->unique())
                ->pluck('id');

        if ($userIds->isEmpty()) {
            return back()->with('error', 'Please choose at least one user from this studio.')->withInput();
        }

        $now = now();
        $rows = $userIds->map(fn ($userId) => [
            'studio_id' => $studioId,
            'user_id' => $userId,
            'created_by' => auth()->id(),
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'] ?: 'general',
            'action_url' => $validated['action_url'] ?: null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::table('app_notifications')->insert($rows);

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', count($rows) . ' notification(s) sent successfully.');
    }

    public function update(Request $request, AppNotification $notification)
    {
        $this->assertCurrentStudioNotification($notification);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'message' => ['sometimes', 'required', 'string', 'max:5000'],
            'type' => ['nullable', 'string', 'max:60'],
            'action_url' => ['nullable', 'string', 'max:2048'],
        ]);

        $notification->update($validated);

        return redirect()->route('admin.notifications.index')->with('success', 'Notification updated.');
    }

    public function destroy(AppNotification $notification)
    {
        $this->assertCurrentStudioNotification($notification);
        $notification->delete();

        return redirect()->route('admin.notifications.index')->with('success', 'Notification deleted.');
    }

    private function assertCurrentStudioNotification(AppNotification $notification): void
    {
        abort_unless((int) $notification->studio_id === $this->currentStudioId(), 404);
    }

    private function currentStudioId(): int
    {
        $studio = app(TenantManager::class)->current();
        abort_unless($studio, 404, 'Studio context is required.');

        return (int) $studio->id;
    }
}
