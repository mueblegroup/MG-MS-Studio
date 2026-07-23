<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('dark') === 'true', showPassword: false, showConfirmPassword: false }"
    x-init="$watch('darkMode', val => localStorage.setItem('dark', val)); document.documentElement.classList.toggle('dark', darkMode)">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - {{ $studio?->name ?? 'Mueble Studio' }}</title>
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
                            {{ $studio ? 'Student Registration' : 'Client Admin Registration' }}
                        </p>
                        <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                            {{ $studio ? 'Join '.$studio->name.'.' : 'Create your client account.' }}
                        </h1>
                        <p class="mt-4 text-sm leading-6 text-blue-50/90">
                            {{ $studio ? 'Create a complete student profile so the studio can manage your classes, attendance, payments and emergency contact information safely.' : 'Create the owner account used for studio setup, SaaS billing and platform administration.' }}
                        </p>
                    </div>
                    <div class="relative space-y-4 text-sm text-blue-50/90">
                        @if ($studio)
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">Your profile belongs only to this studio.</div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">Emergency contact details help staff respond appropriately.</div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">You can update your information later from your profile.</div>
                        @else
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">Used for studio ownership and billing.</div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">Your organisation details help identify the client account.</div>
                            <div class="rounded-2xl bg-white/10 p-4 ring-1 ring-white/15">You can create your studio after registration.</div>
                        @endif
                    </div>
                </section>

                <section class="relative p-6 sm:p-8 lg:col-span-3 lg:p-10">
                    <div class="absolute right-5 top-5 sm:right-6 sm:top-6">
                        <button type="button" @click="darkMode = !darkMode; document.documentElement.classList.toggle('dark', darkMode)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:bg-slate-200 dark:bg-gray-800 dark:text-gray-200 dark:ring-gray-700 dark:hover:bg-gray-700"
                            aria-label="Toggle dark mode">☾</button>
                    </div>

                    <div class="mb-7 pr-12">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600 dark:text-blue-400">{{ $studio ? 'Student Registration' : 'Client Admin Registration' }}</p>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $studio ? 'Create your student account' : 'Create your client admin account' }}</h2>
                        <p class="mt-2 text-sm text-slate-500 dark:text-gray-400">Fields marked required are needed for reliable account and studio operations.</p>
                    </div>

                    <form method="POST" action="{{ url('/register') }}" class="space-y-6">
                        @csrf

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-semibold">Full name</label>
                                <input type="text" id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                @error('name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email" class="mb-1.5 block text-sm font-semibold">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                @error('email') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="phone_number" class="mb-1.5 block text-sm font-semibold">Phone number</label>
                                <input type="tel" id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required autocomplete="tel" placeholder="+60 12-345 6789" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                @error('phone_number') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>

                            @if (! $studio)
                                <div>
                                    <label for="country" class="mb-1.5 block text-sm font-semibold">Country</label>
                                    <input type="text" id="country" name="country" value="{{ old('country', 'Malaysia') }}" required autocomplete="country-name" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    @error('country') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="organisation_name" class="mb-1.5 block text-sm font-semibold">Organisation name</label>
                                    <input type="text" id="organisation_name" name="organisation_name" value="{{ old('organisation_name') }}" required autocomplete="organization" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    @error('organisation_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="job_title" class="mb-1.5 block text-sm font-semibold">Job title <span class="text-slate-400">(optional)</span></label>
                                    <input type="text" id="job_title" name="job_title" value="{{ old('job_title') }}" autocomplete="organization-title" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    @error('job_title') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @else
                                <div>
                                    <label for="date_of_birth" class="mb-1.5 block text-sm font-semibold">Date of birth</label>
                                    <input type="date" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" required max="{{ now()->subDay()->format('Y-m-d') }}" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    @error('date_of_birth') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="gender" class="mb-1.5 block text-sm font-semibold">Gender <span class="text-slate-400">(optional)</span></label>
                                    <select id="gender" name="gender" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">
                                        <option value="">Select</option>
                                        <option value="female" @selected(old('gender') === 'female')>Female</option>
                                        <option value="male" @selected(old('gender') === 'male')>Male</option>
                                        <option value="non_binary" @selected(old('gender') === 'non_binary')>Non-binary</option>
                                        <option value="other" @selected(old('gender') === 'other')>Other</option>
                                        <option value="prefer_not_to_say" @selected(old('gender') === 'prefer_not_to_say')>Prefer not to say</option>
                                    </select>
                                    @error('gender') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                </div>
                            @endif
                        </div>

                        @if ($studio)
                            <div>
                                <label for="address" class="mb-1.5 block text-sm font-semibold">Residential address</label>
                                <textarea id="address" name="address" rows="3" required autocomplete="street-address" class="w-full rounded-xl border-slate-300 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800">{{ old('address') }}</textarea>
                                @error('address') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900/50 dark:bg-amber-950/20">
                                <h3 class="font-bold text-amber-950 dark:text-amber-100">Emergency contact</h3>
                                <p class="mt-1 text-xs text-amber-800 dark:text-amber-300">Provide someone the studio can contact in an urgent situation.</p>
                                <div class="mt-4 grid gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="emergency_contact_name" class="mb-1.5 block text-sm font-semibold">Contact name</label>
                                        <input type="text" id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required class="w-full rounded-xl border-amber-300 px-4 py-3 text-sm dark:border-amber-800 dark:bg-gray-800">
                                        @error('emergency_contact_name') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label for="emergency_contact_phone" class="mb-1.5 block text-sm font-semibold">Contact phone</label>
                                        <input type="tel" id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required class="w-full rounded-xl border-amber-300 px-4 py-3 text-sm dark:border-amber-800 dark:bg-gray-800">
                                        @error('emergency_contact_phone') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label for="password" class="mb-1.5 block text-sm font-semibold">Password</label>
                                <div class="relative">
                                    <input :type="showPassword ? 'text' : 'password'" id="password" name="password" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 px-4 py-3 pr-16 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-slate-500"><span x-text="showPassword ? 'Hide' : 'Show'"></span></button>
                                </div>
                                @error('password') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold">Confirm password</label>
                                <div class="relative">
                                    <input :type="showConfirmPassword ? 'text' : 'password'" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-xl border-slate-300 px-4 py-3 pr-16 text-sm dark:border-gray-700 dark:bg-gray-800">
                                    <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 px-4 text-xs font-bold text-slate-500"><span x-text="showConfirmPassword ? 'Hide' : 'Show'"></span></button>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg transition hover:bg-blue-700">
                            {{ $studio ? 'Create Student Account' : 'Create Client Admin Account' }}
                        </button>
                    </form>

                    <p class="mt-6 text-center text-sm text-slate-600 dark:text-gray-300">Already have an account? <a href="{{ url('/login') }}" class="font-bold text-blue-600">Login</a></p>
                </section>
            </div>
        </div>
    </main>
</body>
</html>
