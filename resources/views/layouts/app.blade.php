<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-100"
    :class="{ 'dark': darkMode }"
    x-data="sidebarState()">

<head>
    <!-- ... head content ... -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body class="font-sans antialiased h-full">
    <div class="h-full flex">

        <!-- Desktop Sidebar -->
        <aside class="hidden md:flex flex-col h-screen bg-gray-100 dark:bg-gray-800 text-gray-400 dark:text-gray-100 transition-all duration-300"
            :class="collapsed ? 'w-20' : 'w-64'">
            @include('layouts.sidebar')
        </aside>

        <!-- Mobile Sidebar (overlay) -->
        <div class="md:hidden fixed inset-0 z-50 flex" x-show="sidebarOpen" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full">
            <aside class="flex-1 flex flex-col bg-gray-800 text-gray-100 w-64">
                @include('layouts.sidebar')
            </aside>
            <!-- Clickable overlay to close sidebar -->
            <div class="flex-shrink-0 w-full bg-black bg-opacity-50" @click="sidebarOpen = false"></div>
        </div>

        <!-- Main content -->
        <div class="flex-1 flex flex-col transition-all duration-300">

            <!-- Mobile Header -->
            <header class="flex items-center justify-between p-4 bg-white shadow md:hidden">
                <button @click="sidebarOpen = !sidebarOpen"
                    class="text-gray-500 hover:text-gray-600 focus:outline-none focus:text-gray-600"
                    aria-label="Toggle sidebar">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <span class="font-bold text-gray-800">Studio System</span>

                <button @click="toggleDarkMode()" aria-label="Toggle dark mode"
                    class="p-1 rounded bg-gray-700 hover:bg-gray-600 text-yellow-400">
                    <template x-if="!darkMode">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m8.485-8.485h-1M4.515 12.515h-1
                              M16.95 7.05l-.707-.707M7.757 16.243l-.707-.707
                              M16.95 16.95l-.707.707M7.757 7.757l-.707.707
                              M12 7a5 5 0 100 10 5 5 0 000-10z" />
                        </svg>
                    </template>
                    <template x-if="darkMode">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-200" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                        </svg>
                    </template>
                </button>
            </header>

            <!-- Desktop Header -->
 <header class="hidden md:flex items-center justify-between h-16 px-4 bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-30">
    
    <div class="flex items-center">
        <button @click="collapsed = !collapsed"
            aria-label="Toggle sidebar"
            class="p-2 rounded-md text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition focus:outline-none">
            <svg x-show="collapsed" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg x-show="!collapsed" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <div class="hidden lg:block font-semibold text-gray-800 dark:text-gray-100">
        Welcome, {{ Auth::user()->name }}
    </div>

    <div class="flex items-center space-x-2 md:space-x-4">

        <a href="{{ route('shop.index') }}"
        class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
            <svg  xmlns="http://www.w3.org/2000/svg" width="20" height="20"  
            fill="currentColor" viewBox="0 0 24 24" >
            <!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
            <path d="M19.1 2.8c-.38-.5-.97-.8-1.6-.8h-11c-.63 0-1.22.3-1.6.8L2.2 6.4c-.13.17-.2.38-.2.6v1c0 1.04.41 1.98 1.06 2.69-.03.1-.06.2-.06.31v9c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2v-9c0-.11-.03-.21-.06-.31C21.59 9.98 22 9.04 22 8V7c0-.22-.07-.43-.2-.6zm.9 4.53V8c0 1.1-.9 2-2 2s-2-.9-2-2V7q0-.12-.03-.24L15.28 4h2.22zM10.78 4h2.44L14 7.12V8c0 1.1-.9 2-2 2s-2-.9-2-2v-.88zM4 7.33 6.5 4h2.22l-.69 2.76Q8 6.88 8 7v1c0 1.1-.9 2-2 2s-2-.9-2-2zM10 20v-4h4v4zm6 0v-4c0-1.1-.9-2-2-2h-4c-1.1 0-2 .9-2 2v4H5v-8.14c.32.08.65.14 1 .14 1.2 0 2.27-.54 3-1.38.73.84 1.8 1.38 3 1.38s2.27-.54 3-1.38c.73.84 1.8 1.38 3 1.38.35 0 .68-.06 1-.14V20z"></path>
            </svg>
        </a>

        <button @click="toggleDarkMode()" 
            class="p-2 rounded-full text-yellow-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
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
                <button class="flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition">
                    <span class="hidden sm:inline-block mr-1">
                        <svg  xmlns="http://www.w3.org/2000/svg" width="20" height="20"  
fill="currentColor" viewBox="0 0 24 24" >
<!--Boxicons v3.0.8 https://boxicons.com | License  https://docs.boxicons.com/free-->
<path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5m0-8c1.65 0 3 1.35 3 3s-1.35 3-3 3-3-1.35-3-3 1.35-3 3-3M4 22h16c.55 0 1-.45 1-1v-1c0-3.86-3.14-7-7-7h-4c-3.86 0-7 3.14-7 7v1c0 .55.45 1 1 1m6-7h4c2.76 0 5 2.24 5 5H5c0-2.76 2.24-5 5-5"></path>
</svg>
                    
                    </span>
                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('profile.edit')">Profile</x-dropdown-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                        Log Out
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</header>

            <!-- Main Content -->
            <main class="flex-1 overflow-auto bg-gray-100 dark:bg-gray-900">
                <div class="container mx-auto px-6 py-8">
                    @isset($header)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md px-4 py-6 mb-6">
                        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100">{{ $header }}</h2>
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
                sidebarOpen: false, // mobile
                darkMode: localStorage.getItem('darkMode') === 'true',
                collapsed: false, // desktop
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