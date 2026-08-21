<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\Request;

class AppNotificationController extends Controller
{
    public function index()
    {
        $notifications = AppNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function show(AppNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return view('notifications.show', compact('notification'));
    }

    public function markRead(AppNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        AppNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Legacy teacher/student routes used to point at mutation methods on this
     * read-only controller. Keep them fail-closed so stale bookmarks cannot
     * produce a 500 or accidentally grow notification-authoring privileges.
     */
    public function create()
    {
        abort(403, 'Only studio administrators can create notifications.');
    }

    public function store(Request $request)
    {
        abort(403, 'Only studio administrators can create notifications.');
    }

    public function edit(AppNotification $notification)
    {
        abort(403, 'Only studio administrators can edit notifications.');
    }

    public function update(Request $request, AppNotification $notification)
    {
        abort(403, 'Only studio administrators can edit notifications.');
    }

    public function destroy(AppNotification $notification)
    {
        abort(403, 'Only studio administrators can delete notifications.');
    }
}
