<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="min-h-screen overflow-x-hidden bg-[#f7f2ea]"
      :class="{ 'dark': darkMode }"
      x-data="sidebarState()">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="min-h-screen overflow-x-hidden bg-[#f7f2ea] font-sans text-[#171717] antialiased dark:bg-gray-950 dark:text-gray-100">
    @php
        $cartItemCount = 0;
        $unreadNotificationCount = 0;

        try {
            $cartItemCount = app(\App\Services\CartService::class)->currentCartItemCount();
        } catch (\Throwable $e) {
            $cartItemCount = 0;
        }

        try {
            if (auth()->check() && \Illuminate\Support\Facades\Schema::hasTable('app_notifications')) {
                $unreadNotificationCount = \App\Models\AppNotification::query()
                    ->where('user_id', auth()->id())
                    ->whereNull('read_at')
                    ->count();
            }
        } catch (\Throwable $e) {
            $unreadNotificationCount = 0;
        }
    @endphp

    <div class="min-h-screen w-full overflow-x-hidden md:flex">
        <aside class="hidden shrink-0 border-r border-[#eadfce] bg-white text-gray-600 transition-all duration-300 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-100 md:sticky md:top-0 md:flex md:h-screen md:flex-col"
               :class="collapsed ? 'md:w-20' : 'md:w-64'">
            @include('layouts.sidebar')
        </aside>

        <div class="fixed inset-0 z-50 flex md:hidden"
             x-cloak
             x-show="sidebarOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="-translate-x-full"
             x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0"
             x-transition:leave-end="-translate-x-full">
            <aside class="flex h-full w-72 max-w-[85vw] shrink-0 flex-col bg-white text-gray-700 shadow-xl dark:bg-gray-900 dark:text-gray-100">
                @include('layouts.sidebar')
            </aside>
            <button type="button" class="min-w-0 flex-1 bg-black/50" aria-label="Close sidebar" @click="sidebarOpen = false"></button>
        </div>

        <div class="min-w-0 flex-1">
            <header class="sticky top-0 z-30 flex items-center justify-between border-b border-[#eadfce] bg-white/95 p-4 shadow-sm backdrop-blur md:hidden dark:border-gray-800 dark:bg-gray-900/95">
                <button @click="sidebarOpen = !sidebarOpen"
                        class="rounded-xl p-2 text-[#31261d] transition hover:bg-[#fff3df] focus:outline-none focus:ring-2 focus:ring-[#d97706] dark:text-gray-200 dark:hover:bg-gray-800"
                        aria-label="Toggle sidebar">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <span class="truncate px-3 text-sm font-extrabold text-[#171717] dark:text-white">Studio System</span>

                <div class="flex items-center gap-1.5">
                    <a href="{{ url('/notifications') }}" class="relative rounded-xl p-2 text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#d97706] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-300" aria-label="Notifications">
                        <i class="bx bx-bell text-xl"></i>
                        @if($unreadNotificationCount > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-extrabold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ url('/shop') }}" class="rounded-xl p-2 text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#d97706] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-300" aria-label="Shop">
                        <i class="bx bx-store text-xl"></i>
                    </a>

                    <a href="{{ url('/shop/cart') }}" class="relative rounded-xl p-2 text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#d97706] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-300" aria-label="Cart">
                        <i class="bx bx-cart text-xl"></i>
                        @if($cartItemCount > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d97706] px-1.5 text-[10px] font-extrabold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                                {{ $cartItemCount > 99 ? '99+' : $cartItemCount }}
                            </span>
                        @endif
                    </a>

                    <button @click="toggleDarkMode()" aria-label="Toggle dark mode" class="rounded-xl p-2 text-[#d97706] transition hover:bg-[#fff3df] focus:outline-none focus:ring-2 focus:ring-[#d97706] dark:hover:bg-gray-800">
                        <template x-if="!darkMode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.485-8.485h-1M4.515 12.515h-1M16.95 7.05l-.707-.707M7.757 16.243l-.707-.707M16.95 16.95l-.707.707M7.757 7.757l-.707.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            </svg>
                        </template>
                        <template x-if="darkMode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </template>
                    </button>
                </div>
            </header>

            <header class="sticky top-0 z-30 hidden h-16 items-center justify-between border-b border-[#eadfce] bg-white/95 px-4 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95 md:flex">
                <div class="flex min-w-0 items-center gap-3">
                    <button @click="collapsed = !collapsed" aria-label="Toggle sidebar" class="rounded-xl p-2 text-[#31261d] transition hover:bg-[#fff3df] focus:outline-none focus:ring-2 focus:ring-[#d97706] dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg x-show="collapsed" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="!collapsed" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <div class="hidden truncate font-semibold text-[#31261d] dark:text-gray-100 lg:block">
                        Welcome, {{ Auth::user()->name }}
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 md:gap-3">
                    <a href="{{ url('/notifications') }}" class="relative rounded-full p-2 text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#d97706] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-300" aria-label="Notifications">
                        <i class="bx bx-bell text-xl"></i>
                        @if($unreadNotificationCount > 0)
                            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[10px] font-extrabold leading-none text-white ring-2 ring-white dark:ring-gray-900">
                                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ url('/shop') }}" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-[#eadfce] bg-white px-3 py-2 text-xs font-bold text-[#31261d] shadow-sm transition hover:bg-[#fff3df] hover:text-[#d97706] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800" aria-label="Shop">
                        <i class="bx bx-store text-lg"></i>
                        <span class="hidden lg:inline">Shop</span>
                    </a>

                    <a href="{{ url('/shop/cart') }}" class="relative inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-[#eadfce] bg-white px-3 py-2 text-xs font-bold text-[#31261d] shadow-sm transition hover:bg-[#fff3df] hover:text-[#d97706] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800" aria-label="Cart">
                        <i class="bx bx-cart text-lg"></i>
                        <span class="hidden lg:inline">Cart</span>
                        @if($cartItemCount > 0)
                            <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-[#d97706] px-1.5 text-[10px] font-extrabold leading-none text-white">
                                {{ $cartItemCount > 99 ? '99+' : $cartItemCount }}
                            </span>
                        @endif
                    </a>

                    <button @click="toggleDarkMode()" class="rounded-full p-2 text-[#d97706] transition hover:bg-[#fff3df] dark:hover:bg-gray-800">
                        <template x-if="!darkMode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.485-8.485h-1M4.515 12.515h-1M16.95 7.05l-.707-.707M7.757 16.243l-.707-.707M16.95 16.95l-.707.707M7.757 7.757l-.707.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                            </svg>
                        </template>
                        <template x-if="darkMode">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </template>
                    </button>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="flex items-center rounded-xl p-2 text-sm font-medium text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#31261d] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                <span class="hidden sm:inline-block mr-1"><i class="bx bx-user text-xl"></i></span>
                                <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                            <x-dropdown-link :href="url('/notifications')">Notifications</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">Log Out</x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            </header>

            <main class="w-full min-w-0 bg-[#f7f2ea] dark:bg-gray-950">
                <div class="w-full min-w-0 px-4 py-5 sm:px-6 lg:px-8">
                    @isset($header)
                        <div class="mb-6 rounded-2xl border border-[#eadfce] bg-white px-4 py-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <h2 class="text-xl font-bold text-[#171717] dark:text-gray-100">{{ $header }}</h2>
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        function sidebarState() {
            return {
                sidebarOpen: false,
                darkMode: localStorage.getItem('darkMode') === 'true',
                collapsed: false,
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                }
            }
        }
    </script>
    @stack('scripts')
</body>

</html>
