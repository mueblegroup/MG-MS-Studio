<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
    @endif

    <div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-orange-500">Platform Communication</p>
            <h1 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Messages</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Private communication between superadmins and studio client portal administrators.</p>
        </div>
        @if($unreadCount > 0)
            <form method="POST" action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.messages.read-all') : route('customer.messages.read-all') }}">
                @csrf
                <button class="rounded-2xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Mark all read ({{ $unreadCount }})</button>
            </form>
        @endif
    </div>

    @if(!($messagingAvailable ?? true) && auth()->user()->role !== 'superadmin')
        <section class="overflow-hidden rounded-[2rem] border border-orange-200 bg-gradient-to-br from-orange-50 via-white to-amber-50 shadow-sm dark:border-orange-900/50 dark:from-orange-950/30 dark:via-slate-900 dark:to-amber-950/20">
            <div class="grid gap-8 p-7 lg:grid-cols-[1fr_auto] lg:items-center lg:p-10">
                <div>
                    <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-500 text-white shadow-lg shadow-orange-500/20">
                        <i class="bx bx-message-square-dots text-3xl"></i>
                    </div>
                    <p class="mt-6 text-xs font-black uppercase tracking-[0.24em] text-orange-600 dark:text-orange-300">Studio setup required</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-950 dark:text-white">Messaging will unlock after your first studio is created.</h2>
                    <p class="mt-4 max-w-2xl text-sm font-semibold leading-7 text-slate-600 dark:text-slate-300">Platform messages are linked to a studio so the support team can identify the correct subscription, billing account, and portal. Complete your studio setup first, then return here to contact the superadmin team.</p>
                </div>
                <div class="flex flex-col gap-3 lg:min-w-64">
                    <a href="{{ route('customer.studios.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-orange-500 px-5 py-3.5 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">
                        <i class="bx bx-plus-circle text-lg"></i>
                        Create your studio
                    </a>
                    <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3.5 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">Back to dashboard</a>
                </div>
            </div>
        </section>
    @else
        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">New Message</h2>
                <form method="POST" action="{{ auth()->user()->role === 'superadmin' ? route('superadmin.messages.store') : route('customer.messages.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Recipient</span>
                        <select name="recipient_id" required class="w-full rounded-2xl border-slate-200 bg-white text-sm font-semibold text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                            <option value="">Select recipient</option>
                            @foreach($recipients as $recipient)
                                <option value="{{ $recipient->id }}" @selected(old('recipient_id') == $recipient->id)>
                                    {{ $recipient->name }}
                                    @if(auth()->user()->role === 'superadmin' && $recipient->ownedStudios->isNotEmpty())
                                        — {{ $recipient->ownedStudios->first()->name }}
                                    @else
                                        — Superadmin
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Subject</span>
                        <input name="subject" value="{{ old('subject') }}" maxlength="255" required class="w-full rounded-2xl border-slate-200 bg-white text-sm font-semibold text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                    </label>
                    <label class="block space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Message</span>
                        <textarea name="body" rows="6" maxlength="10000" required class="w-full rounded-2xl border-slate-200 bg-white text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">{{ old('body') }}</textarea>
                    </label>
                    <button class="w-full rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Send Message</button>
                </form>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-black text-slate-950 dark:text-white">Inbox & Sent</h2>
                <div class="mt-5 divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse($messages as $message)
                        @php
                            $isUnread = $message->recipient_id === auth()->id() && ! $message->read_at;
                            $other = $message->sender_id === auth()->id() ? $message->recipient : $message->sender;
                            $showRoute = auth()->user()->role === 'superadmin' ? route('superadmin.messages.show', $message) : route('customer.messages.show', $message);
                        @endphp
                        <a href="{{ $showRoute }}" class="flex gap-4 rounded-2xl px-3 py-4 hover:bg-slate-50 dark:hover:bg-slate-800/60">
                            <span class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full {{ $isUnread ? 'bg-orange-500' : 'bg-slate-200 dark:bg-slate-700' }}"></span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                    <p class="truncate text-sm font-black {{ $isUnread ? 'text-slate-950 dark:text-white' : 'text-slate-700 dark:text-slate-300' }}">{{ $message->subject }}</p>
                                    <span class="shrink-0 text-xs text-slate-400">{{ $message->updated_at->diffForHumans() }}</span>
                                </div>
                                <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $message->sender_id === auth()->id() ? 'To' : 'From' }}: {{ $other?->name ?? 'Unknown user' }}
                                    @if($message->studio) · {{ $message->studio->name }} @endif
                                </p>
                                <p class="mt-2 line-clamp-2 text-sm text-slate-500 dark:text-slate-400">{{ $message->body }}</p>
                            </div>
                        </a>
                    @empty
                        <div class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">No platform messages yet.</div>
                    @endforelse
                </div>
                <div class="mt-5">{{ $messages->links() }}</div>
            </section>
        </div>
    @endif
</div>
