<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
    @endif

    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-orange-500">Conversation</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">{{ $message->subject }}</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                With {{ $otherUser?->name ?? 'Unknown user' }}
                @if($message->studio) · {{ $message->studio->name }} @endif
            </p>
        </div>
        <a href="{{ auth()->user()->role === 'superadmin' ? route('superadmin.messages.index') : route('customer.messages.index') }}" class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Messages</a>
    </div>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="space-y-4">
            @foreach($conversation as $item)
                @php($mine = $item->sender_id === auth()->id())
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-3xl rounded-3xl px-5 py-4 {{ $mine ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-900 dark:bg-slate-800 dark:text-slate-100' }}">
                        <div class="flex flex-wrap items-center gap-2 text-xs font-bold {{ $mine ? 'text-orange-100' : 'text-slate-500 dark:text-slate-400' }}">
                            <span>{{ $item->sender?->name ?? 'Unknown user' }}</span>
                            <span>·</span>
                            <span>{{ $item->created_at->format('d M Y, H:i') }}</span>
                        </div>
                        <div class="mt-2 whitespace-pre-wrap text-sm leading-6">{{ $item->body }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.messages.store') : route('customer.messages.store') }}" class="mt-6 border-t border-slate-100 pt-6 dark:border-slate-800">
            @csrf
            <input type="hidden" name="recipient_id" value="{{ $otherUser->id }}">
            <input type="hidden" name="subject" value="{{ $message->subject }}">
            <input type="hidden" name="parent_id" value="{{ $message->id }}">
            <label class="block space-y-2">
                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Reply</span>
                <textarea name="body" rows="5" maxlength="10000" required class="w-full rounded-2xl border-slate-200 bg-white text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" placeholder="Write your reply..."></textarea>
            </label>
            <div class="mt-4 flex justify-end">
                <button class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black text-white hover:bg-orange-600">Send Reply</button>
            </div>
        </form>
    </section>
</div>
