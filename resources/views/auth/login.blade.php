<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark') === 'true', showPassword: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val)); document.documentElement.classList.toggle('dark', darkMode)">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $studio?->name ?? 'Mueble Studio' }} - Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-slate-100 text-slate-900 transition-colors duration-500 dark:bg-gray-950 dark:text-gray-100">

    <main class="min-h-screen px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
        <div class="mx-auto flex min-h-[calc(100vh-4rem)] w-full max-w-6xl items-center justify-center">
            <div class="grid w-full overflow-hidden rounded-3xl bg-white shadow-2xl ring-1 ring-slate-200 dark:bg-gray-900 dark:ring-gray-800 lg:grid-cols-5">

                <section class="relative hidden bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-700 p-10 text-white lg:col-span-2 lg:flex lg:flex-col lg:justify-between">
                    <div class="absolute inset-0 opacity-20">
                        <div class="absolute -left-20 -top-20 h-64 w-64 rounded-full bg-white blur-3xl"></div>
                        <div class="absolute -bottom-24 right-0 h-72 w-72 rounded-full bg-cyan-300 blur-3xl"></div>
                    </div>

                    <div class="relative">
                        <div class="mb-8 inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 text-xl font-black shadow-lg ring-1 ring-white/20">
                            {{ strtoupper(substr($studio?->name ?? 'Mueble Studio', 0, 1)) }}
                        </div>

                        <p class="mb-3 inline-flex rounded-full bg-white/15 px-3 py-1 text-xs font-black uppercase tracking-[0.2em] ring-1 ring-white/20">
                            {{ $studio ? 'Studio Portal' : 'Client Portal' }}
                        </p>

                        <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                            {{ $loginTitle ?? ($studio ? $studio->name.' Studio Login' : 'Mueble Studio Client Portal') }}
                        </h1>
                        <p class="mt-4 text-sm leading-6 text-blue-50/90">
                            {{ $loginSubtitle ?? ($studio ? 'Login to manage this studio.' : 'Login to register and manage your studio portals.') }}
                        </p>
                    </div>

                    <div class="relative space-y-4 text-sm text-blue-50/90">
                        @if ($studio)
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">1</div>
                                <span>Manage teachers, classes and students</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">2</div>
                                <span>Track attendance and schedules</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">3</div>
                                <span>Review studio payments and records</span>
                            </div>
                        @else
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">1</div>
                                <span>Register and manage multiple studios</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">2</div>
                                <span>Control studio subdomains and setup</span>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-white/10 p-4 ring-1 ring-white/15 backdrop-blur">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 font-bold">3</div>
                                <span>Enter each studio only through its portal</span>
                            </div>
                        @endif
                    </div>
                </section>

                <section class="relative p-6 sm:p-8 lg:col-span-3 lg:p-10">
                    <div class="absolute right-5 top-5 sm:right-6 sm:top-6">
                        <button type="button"
                            @click="darkMode = !darkMode; document.documentElement.classList.toggle('dark', darkMode)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700"
                            aria-label="Toggle dark mode">
                            <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-7.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14a7 7 0 000-14z" />
                            </svg>
                            <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z" />
                            </svg>
                        </button>
                    </div>

                    <div class="mx-auto flex min-h-full w-full max-w-md flex-col justify-center py-8 lg:py-12">
                        <div class="mb-8 pr-12">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">
                                {{ $studio ? 'Studio Login' : 'Client Login' }}
                            </p>
                            <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                                {{ $studio ? 'Login to '.$studio->name : 'Login to your client admin' }}
                            </h2>
                            <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">
                                {{ $studio ? 'This login is only for this studio portal.' : 'This login is only for managing your studios. Studio users must login from their own studio subdomain.' }}
                            </p>
                        </div>

                        <form action="{{ url('/login') }}" method="POST" class="space-y-5">
                            @csrf

                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                    placeholder="you@example.com">
                                @error('email') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-semibold text-slate-700 dark:text-gray-300">Password</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-16 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:focus:border-blue-400 dark:focus:ring-blue-900/40"
                                        placeholder="••••••••">

                                    <button type="button"
                                        @click="showPassword = !showPassword"
                                        class="absolute inset-y-0 right-0 flex items-center px-4 text-xs font-bold text-slate-500 transition hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400"
                                        :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                        <span x-show="!showPassword">Show</span>
                                        <span x-show="showPassword">Hide</span>
                                    </button>
                                </div>
                                @error('password') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-gray-300">
                                    <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    Remember me
                                </label>
                                <a href="{{ url('/forgot-password') }}" class="text-sm font-semibold text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Forgot password?
                                </a>
                            </div>

                            <button type="submit"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-200 dark:focus:ring-blue-900/50">
                                Login
                            </button>
                        </form>

                        @if (! $studio)
                            <p class="mt-5 text-center text-sm text-slate-600 dark:text-gray-300">
                                New customer?
                                <a href="{{ url('/register') }}" class="font-bold text-blue-600 transition hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                    Create client admin account
                                </a>
                            </p>
                        @elseif ($studentSelfRegistrationEnabled ?? false)
                            <div class="mt-5 rounded-2xl border border-blue-200 bg-blue-50 p-4 text-center dark:border-blue-900/60 dark:bg-blue-950/30">
                                <p class="text-sm font-semibold text-slate-700 dark:text-gray-200">New student at {{ $studio->name }}?</p>
                                <a href="{{ url('/register') }}" class="mt-2 inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                                    Create Student Account
                                </a>
                            </div>
                        @else
                            <p class="mt-5 rounded-2xl bg-slate-50 p-4 text-center text-sm text-slate-600 ring-1 ring-slate-200 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-700">
                                Student registration is managed by this studio. Contact the studio administrator if you need an account.
                            </p>
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </main>

</body>

</html>
