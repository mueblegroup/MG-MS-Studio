<x-customer-layout>
    @php
        $studioUrl = $studio ? (($studio->custom_domain ?: ($studio->subdomain . '.' . $rootDomain))) : null;
        $studentSelfRegistrationEnabled = $studio ? (bool) data_get($studio->settings, 'allow_student_self_registration', true) : false;
        $timezoneOptions = \App\Support\StudioLocaleOptions::timezones();
        $studioTimezone = $studio ? (string) data_get($studio->settings, 'timezone', config('app.timezone', 'UTC')) : 'UTC';
    @endphp

    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-500">Client Portal</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">My Studio</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">This page controls the SaaS-side identity and timezone of your studio. LMS operations are separated into the studio subdomain.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Overview</a>
                @if ($studio)
                    <a href="{{ route('customer.studios.launch', $studio) }}" target="_blank" rel="noopener noreferrer" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white hover:bg-slate-800 dark:bg-white dark:text-slate-950">Open Studio Admin <span aria-hidden="true">↗</span></a>
                @else
                    <a href="{{ route('customer.studios.create') }}" class="rounded-2xl bg-orange-500 px-4 py-2 text-sm font-black text-white hover:bg-orange-600">Create Studio</a>
                @endif
            </div>
        </div>

        @if ($studio)
            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">Studio Information</h2>
                    <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Studio Name</dt><dd class="mt-2 font-black">{{ $studio->name }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</dt><dd class="mt-2 font-black">{{ ucfirst($studio->effectiveStatus()) }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950 sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Studio Admin URL</dt><dd class="mt-2 break-all font-black">https://{{ $studioUrl }}/admin/dashboard</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Timezone</dt><dd class="mt-2 font-black">{{ $studioTimezone }}</dd></div>
                        <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950"><dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Currency</dt><dd class="mt-2 font-black">{{ $studio->settings['currency'] ?? 'MYR' }}</dd></div>
                    </dl>
                </section>

                <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl">
                    <h2 class="text-xl font-black">Portal boundaries</h2>
                    <div class="mt-5 space-y-4 text-sm leading-6 text-slate-300">
                        <div class="rounded-2xl bg-white/10 p-4"><p class="font-black text-white">Client Portal</p><p class="mt-1">Subscription, invoices, billing, domain status, ownership, and studio timezone.</p></div>
                        <div class="rounded-2xl bg-white/10 p-4"><p class="font-black text-white">Studio Admin</p><p class="mt-1">Teachers, classes, students, attendance, schedules, products, and payments.</p></div>
                    </div>
                </section>
            </div>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="grid gap-6 lg:grid-cols-[1fr_1.2fr] lg:items-end">
                    <div>
                        <h2 class="text-xl font-black">Studio Timezone</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">This is the canonical timezone for the studio. Admins, teachers and students in the studio portal use this timezone for schedules, class times, payment deadlines, billing displays and reminders.</p>
                    </div>
                    <form method="POST" action="{{ route('customer.studio.timezone.update', $studio) }}" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-end">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Timezone</label>
                            <select name="timezone" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                                @foreach($timezoneOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('timezone', $studioTimezone) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('timezone') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                        </div>
                        <button type="submit" class="rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white transition hover:bg-orange-600">Save Timezone</button>
                    </form>
                </div>
            </section>

            <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
                    <div><h2 class="text-xl font-black">Student Self-Registration</h2><p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">Allow students to create their own account from this studio's subdomain.</p></div>
                    <form method="POST" action="{{ route('customer.studio.registration-settings.update', $studio) }}">@csrf @method('PATCH')<input type="hidden" name="allow_student_self_registration" value="0"><label class="inline-flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 dark:border-slate-700 dark:bg-slate-950"><input type="checkbox" name="allow_student_self_registration" value="1" onchange="this.form.submit()" @checked($studentSelfRegistrationEnabled) class="rounded border-slate-300 text-orange-500"><span class="text-sm font-black">{{ $studentSelfRegistrationEnabled ? 'Registration enabled' : 'Registration disabled' }}</span></label></form>
                </div>
                <div class="mt-5 rounded-2xl {{ $studentSelfRegistrationEnabled ? 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-200' : 'bg-amber-50 text-amber-800 dark:bg-amber-950/30 dark:text-amber-200' }} p-4 text-sm font-semibold">@if($studentSelfRegistrationEnabled) Students can register at <span class="font-black">https://{{ $studioUrl }}/register</span>. @else The public student registration page is blocked. Existing students can still log in normally. @endif</div>
            </section>
        @else
            <section class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900"><p class="text-2xl font-black">No studio yet</p><a href="{{ route('customer.studios.create') }}" class="mt-6 inline-flex rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white">Create Studio</a></section>
        @endif
    </div>
</x-customer-layout>
