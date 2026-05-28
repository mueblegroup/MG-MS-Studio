<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mg-title">Notifications</h1>
                    <p class="mg-subtitle mt-1">View your personal alerts, payment reminders, and system updates.</p>
                </div>

                @if($notifications->whereNull('read_at')->count() > 0)
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="mg-btn-secondary">
                            <i class="bx bx-check-double"></i>
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mg-card overflow-hidden">
                <div class="divide-y divide-[#f0e5d4] dark:divide-gray-800">
                    @forelse($notifications as $notification)
                        <a href="{{ route('notifications.show', $notification) }}"
                           class="block p-5 transition hover:bg-[#fffaf3] dark:hover:bg-gray-800/70 {{ !$notification->read_at ? 'bg-[#fffaf3]/70 dark:bg-amber-950/10' : '' }}">
                            <div class="flex items-start gap-4">
                                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl {{ !$notification->read_at ? 'bg-[#d97706] text-white' : 'bg-[#fff3df] text-[#d97706]' }}">
                                    <i class="bx {{ match($notification->type) {
                                        'payment_due' => 'bx-credit-card-front',
                                        'payment_success' => 'bx-check-circle',
                                        'class' => 'bx-calendar',
                                        'plan' => 'bx-layer',
                                        'class_card' => 'bx-card',
                                        default => 'bx-bell'
                                    } }} text-xl"></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h2 class="truncate font-bold text-[#171717] dark:text-white">{{ $notification->title }}</h2>
                                                @if(!$notification->read_at)
                                                    <span class="rounded-full bg-[#d97706] px-2 py-0.5 text-[10px] font-extrabold uppercase text-white">New</span>
                                                @endif
                                            </div>
                                            <p class="mt-1 line-clamp-2 text-sm text-[#6b5f52] dark:text-gray-400">{{ $notification->message }}</p>
                                        </div>

                                        <div class="shrink-0 text-xs font-semibold text-[#9a8c7d] dark:text-gray-500">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-12 text-center">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-[#fff3df] text-[#d97706]">
                                <i class="bx bx-bell-off text-2xl"></i>
                            </div>
                            <h2 class="mt-4 font-bold text-[#171717] dark:text-white">No notifications yet</h2>
                            <p class="mt-1 text-sm text-[#6b5f52] dark:text-gray-400">Your personal alerts will appear here.</p>
                        </div>
                    @endforelse
                </div>

                <div class="border-t border-[#eadfce] p-4 dark:border-gray-800">
                    {{ $notifications->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
