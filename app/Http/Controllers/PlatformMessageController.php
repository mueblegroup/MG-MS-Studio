<?php

namespace App\Http\Controllers;

use App\Models\PlatformMessage;
use App\Models\Studio;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlatformMessageController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $messages = PlatformMessage::query()
            ->with(['sender:id,name,role', 'recipient:id,name,role', 'studio:id,name,slug'])
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                    ->orWhere('recipient_id', $user->id);
            })
            ->whereNull('parent_id')
            ->latest('updated_at')
            ->paginate(20);

        return view($this->viewFor($user, 'index'), [
            'messages' => $messages,
            'recipients' => $this->availableRecipients($user),
            'unreadCount' => PlatformMessage::where('recipient_id', $user->id)->whereNull('read_at')->count(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'parent_id' => ['nullable', 'integer', 'exists:platform_messages,id'],
        ]);

        $recipient = User::query()->findOrFail($validated['recipient_id']);
        $studio = $this->authorizeRecipient($user, $recipient);
        $parent = null;

        if (! empty($validated['parent_id'])) {
            $parent = PlatformMessage::query()->findOrFail($validated['parent_id']);
            abort_unless($this->canAccess($user, $parent), 403);
            abort_unless(in_array($recipient->id, [$parent->sender_id, $parent->recipient_id], true), 422, 'Reply recipient does not belong to this conversation.');
        }

        PlatformMessage::create([
            'sender_id' => $user->id,
            'recipient_id' => $recipient->id,
            'studio_id' => $studio?->id ?? $parent?->studio_id,
            'parent_id' => $parent?->parent_id ?: $parent?->id,
            'subject' => $parent ? $parent->subject : $validated['subject'],
            'body' => $validated['body'],
        ]);

        if ($parent) {
            $root = $parent->parent_id ? PlatformMessage::find($parent->parent_id) : $parent;
            $root?->touch();
        }

        return redirect()
            ->route($user->role === 'superadmin' ? 'superadmin.messages.index' : 'customer.messages.index')
            ->with('success', $parent ? 'Reply sent successfully.' : 'Message sent successfully.');
    }

    public function show(Request $request, PlatformMessage $message): View
    {
        $user = $request->user();
        abort_unless($this->canAccess($user, $message), 403);

        $root = $message->parent_id ? PlatformMessage::findOrFail($message->parent_id) : $message;
        abort_unless($this->canAccess($user, $root), 403);

        PlatformMessage::query()
            ->where(function ($query) use ($root) {
                $query->whereKey($root->id)->orWhere('parent_id', $root->id);
            })
            ->where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        $conversation = PlatformMessage::query()
            ->with(['sender:id,name,role', 'recipient:id,name,role', 'studio:id,name,slug'])
            ->where(function ($query) use ($root) {
                $query->whereKey($root->id)->orWhere('parent_id', $root->id);
            })
            ->oldest()
            ->get();

        $otherUser = $root->sender_id === $user->id ? $root->recipient : $root->sender;

        return view($this->viewFor($user, 'show'), [
            'message' => $root->load(['sender:id,name,role', 'recipient:id,name,role', 'studio:id,name,slug']),
            'conversation' => $conversation,
            'otherUser' => $otherUser,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        PlatformMessage::query()
            ->where('recipient_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'All platform messages marked as read.');
    }

    private function availableRecipients(User $user)
    {
        if ($user->role === 'superadmin') {
            return User::query()
                ->where('role', 'admin')
                ->whereHas('ownedStudios')
                ->with('ownedStudios:id,name,owner_user_id')
                ->orderBy('name')
                ->get();
        }

        abort_unless($user->role === 'admin' && Studio::where('owner_user_id', $user->id)->exists(), 403);

        return User::query()
            ->where('role', 'superadmin')
            ->orderBy('name')
            ->get();
    }

    private function authorizeRecipient(User $sender, User $recipient): ?Studio
    {
        if ($sender->role === 'superadmin') {
            abort_unless($recipient->role === 'admin', 422, 'Superadmins can only message client portal administrators.');
            $studio = Studio::query()->where('owner_user_id', $recipient->id)->first();
            abort_unless($studio, 422, 'The selected administrator does not own a studio.');

            return $studio;
        }

        abort_unless($sender->role === 'admin', 403);
        $studio = Studio::query()->where('owner_user_id', $sender->id)->first();
        abort_unless($studio && $recipient->role === 'superadmin', 422, 'Client portal administrators can only message superadmins.');

        return $studio;
    }

    private function canAccess(User $user, PlatformMessage $message): bool
    {
        return in_array($user->id, [$message->sender_id, $message->recipient_id], true);
    }

    private function viewFor(User $user, string $screen): string
    {
        abort_unless(in_array($user->role, ['superadmin', 'admin'], true), 403);

        return $user->role === 'superadmin'
            ? "superadmin.messages.{$screen}"
            : "customer.messages.{$screen}";
    }
}
