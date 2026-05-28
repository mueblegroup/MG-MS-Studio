<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppNotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::with(['user', 'creator'])
            ->latest()
            ->paginate(20);

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role']);

        return view('admin.notifications.index', compact('notifications', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_type' => 'required|in:all,specific',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'type' => 'nullable|string|max:60',
            'action_url' => 'nullable|string|max:2048',
        ]);

        $recipientType = $validated['recipient_type'];

        $userIds = $recipientType === 'all'
            ? User::query()->pluck('id')
            : collect($validated['user_ids'] ?? [])->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return back()->with('error', 'Please choose at least one user.')->withInput();
        }

        $now = now();

        $rows = $userIds->map(fn ($userId) => [
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
}
