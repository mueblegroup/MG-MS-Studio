<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen bg-slate-50" x-data="customerPortalShell()" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <title>{{ config('app.name', 'Mueble LMS') }} - Client Portal</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    @php
        $customerLinks = [
            ['label' => 'Overview', 'route' => 'customer.dashboard', 'icon' => 'bx-grid-alt'],
            ['label' => 'My Studio', 'route' => 'customer.studio', 'icon' => 'bx-building-house'],
            ['label' => 'Billing & Plan', 'route' => 'customer.billing', 'icon' => 'bx-credit-card'],
            ['label' => 'Invoices', 'route' => 'customer.invoices', 'icon' => 'bx-receipt'],
            ['label' => 'Account', 'route' => 'customer.account', 'icon' => 'bx-user-circle'],
        ];
    @endphp

    <div class="min-h-screen lg:flex">
        <aside class="fixed inset-y-0 left-0 z-40 hidden w-72 flex-col border-r border-slate-200 bg-white px-5 py-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:flex">
            <div class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-500 text-xl font-black text-white shadow-lg shadow-orange-500/25">M</div>
                <div>
                    <p class="text-base font-black leading-tight text-slate-950 dark:text-white">Mueble LMS</p>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Client Portal</p>
                </div>
            </div>

            <nav class="mt-9 space-y-2">
                @foreach ($customerLinks as $link)
                    @php $active = request()->routeIs($link['route']); @endphp
                    <a href="{{ route($link['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black transition {{ $active ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/10 dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' }}">
                        <i class="bx {{ $link['icon'] }} text-xl"></i>
                        <span>{{ $link['label'] }}</span>
                    </a>
                @endforeach
            </nav>

            <div class="mt-auto space-y-4">
                <div class="rounded-3xl bg-orange-50 p-4 text-sm leading-6 text-orange-900 ring-1 ring-orange-100 dark:bg-orange-950/30 dark:text-orange-200 dark:ring-orange-900/40">
                    <p class="font-black">Client portal only</p>
                    <p class="mt-1 text-xs font-semibold leading-5">Use this area for studio setup, SaaS subscription, billing, and invoices. LMS operations stay in the studio subdomain.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-2xl border border-slate-200 px-4 py-3 text-sm font-black text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
                        <i class="bx bx-log-out text-lg"></i>
                        Log Out
                    </button>
                </form>
            </div>
        </aside>

        <div class="min-w-0 flex-1 lg:pl-72">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 px-4 py-3 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex min-w-0 items-center gap-3">
                        <button type="button" class="rounded-2xl p-2 text-slate-600 transition hover:bg-slate-100 lg:hidden dark:text-slate-300 dark:hover:bg-slate-800" @click="mobileOpen = true" aria-label="Open client portal menu">
                            <i class="bx bx-menu text-2xl"></i>
                        </button>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-slate-950 dark:text-white">Client Portal</p>
                            <p class="truncate text-xs font-semibold text-slate-500 dark:text-slate-400">{{ auth()->user()->name }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('customer.account') }}" class="hidden rounded-2xl border border-slate-200 px-4 py-2 text-xs font-black text-slate-600 transition hover:bg-slate-100 hover:text-slate-950 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white sm:inline-flex">Account</a>
                        <button type="button" @click="toggleDarkMode()" class="rounded-2xl border border-slate-200 p-2 text-orange-500 transition hover:bg-orange-50 dark:border-slate-800 dark:hover:bg-slate-800" aria-label="Toggle dark mode">
                            <i class="bx text-xl" :class="darkMode ? 'bx-moon' : 'bx-sun'"></i>
                        </button>
                    </div>
                </div>
            </header>

            <div class="fixed inset-0 z-50 lg:hidden" x-cloak x-show="mobileOpen" x-transition.opacity>
                <button type="button" class="absolute inset-0 bg-slate-950/60" @click="mobileOpen = false" aria-label="Close client portal menu"></button>
                <aside class="relative flex h-full w-80 max-w-[85vw] flex-col bg-white px-5 py-6 shadow-2xl dark:bg-slate-900" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-500 font-black text-white">M</div>
                            <div>
                                <p class="font-black text-slate-950 dark:text-white">Mueble LMS</p>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-400">Client Portal</p>
                            </div>
                        </div>
                        <button type="button" class="rounded-xl p-2 text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-800" @click="mobileOpen = false" aria-label="Close menu"><i class="bx bx-x text-2xl"></i></button>
                    </div>
                    <nav class="mt-8 space-y-2">
                        @foreach ($customerLinks as $link)
                            @php $active = request()->routeIs($link['route']); @endphp
                            <a href="{{ route($link['route']) }}" class="flex items-center gap-3 rounded-2xl px-4 py-3 text-sm font-black transition {{ $active ? 'bg-slate-950 text-white dark:bg-white dark:text-slate-950' : 'text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                                <i class="bx {{ $link['icon'] }} text-xl"></i>
                                <span>{{ $link['label'] }}</span>
                            </a>
                        @endforeach
                    </nav>
                </aside>
            </div>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <script>
        function customerPortalShell() {
            return {
                mobileOpen: false,
                darkMode: localStorage.getItem('darkMode') === 'true',
                toggleDarkMode() {
                    this.darkMode = !this.darkMode;
                    localStorage.setItem('darkMode', this.darkMode);
                }
            };
        }
    </script>
    @stack('scripts')
</body>
</html>
