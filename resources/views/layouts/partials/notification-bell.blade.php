@php
    $notifications = $notifications ?? collect();
    $unreadCount = $unreadCount ?? 0;
    $buttonClass = $buttonClass ?? 'relative rounded-full p-2 text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#d97706] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-300';
    $panelClass = $panelClass ?? 'absolute right-0 top-12 w-[min(24rem,calc(100vw-2rem))]';
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false">
    <button type="button"
            class="{{ $buttonClass }}"
            aria-label="Notifications"
            @click="open = !open">
        <i class="bx bx-bell text-xl"></i>
        @if($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-extrabold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-cloak
         x-show="open"
         x-transition.opacity
         class="fixed inset-0 z-40"
         @click="open = false"></div>

    <div x-cloak
         x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="translate-y-2 opacity-0 scale-95"
         x-transition:enter-end="translate-y-0 opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="translate-y-0 opacity-100 scale-100"
         x-transition:leave-end="translate-y-2 opacity-0 scale-95"
         class="{{ $panelClass }} z-50 overflow-hidden rounded-3xl border border-[#eadfce] bg-white shadow-2xl shadow-black/10 ring-1 ring-black/5 dark:border-gray-800 dark:bg-gray-900 dark:shadow-black/30 dark:ring-white/10">
        <div class="border-b border-[#f0e4d4] bg-gradient-to-r from-[#fff8ee] to-white px-4 py-4 dark:border-gray-800 dark:from-gray-900 dark:to-gray-900">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-sm font-extrabold text-[#171717] dark:text-white">Notifications</div>
                    <div class="mt-0.5 text-xs font-medium text-[#7a6a59] dark:text-gray-400">
                        {{ $unreadCount > 0 ? $unreadCount . ' unread notification' . ($unreadCount > 1 ? 's' : '') : 'You are all caught up' }}
                    </div>
                </div>

                @if($unreadCount > 0)
                    <form method="POST" action="{{ url('/notifications/read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-full bg-[#fff3df] px-3 py-1.5 text-[11px] font-extrabold text-[#9a4f00] transition hover:bg-[#ffe4b8] dark:bg-amber-950/30 dark:text-amber-200 dark:hover:bg-amber-950/50">
                            Read all
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="max-h-[60vh] overflow-y-auto py-2">
            @forelse($notifications as $notification)
                <a href="{{ url('/notifications/' . $notification->id) }}"
                   class="group mx-2 flex gap-3 rounded-2xl px-3 py-3 transition hover:bg-[#fff8ee] dark:hover:bg-gray-800/80"
                   @click="open = false">
                    <div class="mt-1 flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $notification->read_at ? 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400' : 'bg-[#fff3df] text-[#d97706] dark:bg-amber-950/40 dark:text-amber-300' }}">
                        @switch($notification->type)
                            @case('payment_due')
                                <i class="bx bx-time-five text-lg"></i>
                                @break
                            @case('payment_success')
                                <i class="bx bx-check-circle text-lg"></i>
                                @break
                            @case('class')
                                <i class="bx bx-calendar text-lg"></i>
                                @break
                            @case('plan')
                                <i class="bx bx-list-check text-lg"></i>
                                @break
                            @case('class_card')
                                <i class="bx bx-card text-lg"></i>
                                @break
                            @default
                                <i class="bx bx-bell text-lg"></i>
                        @endswitch
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="truncate text-sm font-extrabold text-[#171717] group-hover:text-[#9a4f00] dark:text-white dark:group-hover:text-amber-200">
                                {{ $notification->title }}
                            </p>
                            @unless($notification->read_at)
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-red-500"></span>
                            @endunless
                        </div>
                        <p class="mt-0.5 line-clamp-2 text-xs font-medium leading-5 text-[#7a6a59] dark:text-gray-400">
                            {{ $notification->message }}
                        </p>
                        <p class="mt-1 text-[11px] font-bold text-[#b08a5b] dark:text-gray-500">
                            {{ $notification->created_at?->diffForHumans() }}
                        </p>
                    </div>
                </a>
            @empty
                <div class="px-6 py-8 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff3df] text-[#d97706] dark:bg-amber-950/40 dark:text-amber-300">
                        <i class="bx bx-bell-off text-2xl"></i>
                    </div>
                    <div class="mt-3 text-sm font-extrabold text-[#171717] dark:text-white">No notifications yet</div>
                    <div class="mt-1 text-xs font-medium text-[#7a6a59] dark:text-gray-400">New updates will appear here.</div>
                </div>
            @endforelse
        </div>

        <div class="border-t border-[#f0e4d4] bg-[#fffaf3] p-3 dark:border-gray-800 dark:bg-gray-950/40">
            <a href="{{ url('/notifications') }}"
               class="flex w-full items-center justify-center gap-2 rounded-2xl bg-[#d97706] px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#b45309]">
                View all notifications
                <i class="bx bx-right-arrow-alt text-lg"></i>
            </a>
        </div>
    </div>
</div>
