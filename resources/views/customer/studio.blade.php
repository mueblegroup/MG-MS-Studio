<x-app-layout>
    @php
        $studioUrl = $studio ? (($studio->custom_domain ?: ($studio->subdomain . '.' . $rootDomain))) : null;
    @endphp

    <div class="min-h-screen bg-slate-50 px-4 py-6 dark:bg-gray-950 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-500">Client Portal</p>
                    <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">My Studio</h1>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">Manage the SaaS-side identity of your studio. Day-to-day LMS operations are handled from the studio subdomain.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">Back to Overview</a>
                    @if ($studio)
                        <a href="{{ route('customer.studios.launch', $studio) }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950">Open Studio Login</a>
                    @else
                        <a href="{{ route('customer.studios.create') }}" class="rounded-2xl bg-orange-500 px-4 py-2 text-sm font-black text-white transition hover:bg-orange-600">Create Studio</a>
                    @endif
                </div>
            </div>

            @if ($studio)
                <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Studio Information</h2>
                        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-gray-950">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Studio Name</dt>
                                <dd class="mt-2 font-black text-slate-950 dark:text-white">{{ $studio->name }}</dd>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-gray-950">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Status</dt>
                                <dd class="mt-2 font-black text-slate-950 dark:text-white">{{ ucfirst($studio->status) }}</dd>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-gray-950 sm:col-span-2">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Studio Login URL</dt>
                                <dd class="mt-2 break-all font-black text-slate-950 dark:text-white">https://{{ $studioUrl }}/login</dd>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-gray-950">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Timezone</dt>
                                <dd class="mt-2 font-black text-slate-950 dark:text-white">{{ $studio->settings['timezone'] ?? config('app.timezone') }}</dd>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4 dark:bg-gray-950">
                                <dt class="text-xs font-bold uppercase tracking-wider text-slate-500">Currency</dt>
                                <dd class="mt-2 font-black text-slate-950 dark:text-white">{{ $studio->settings['currency'] ?? 'MYR' }}</dd>
                            </div>
                        </dl>
                    </section>

                    <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10">
                        <h2 class="text-xl font-black">What belongs here?</h2>
                        <div class="mt-5 space-y-4 text-sm leading-6 text-slate-300">
                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="font-black text-white">Client Portal</p>
                                <p class="mt-1">Studio subscription, billing, invoices, domain status, and account ownership.</p>
                            </div>
                            <div class="rounded-2xl bg-white/10 p-4">
                                <p class="font-black text-white">Studio Admin</p>
                                <p class="mt-1">Teachers, classes, students, attendance, schedules, products, and student payments.</p>
                            </div>
                        </div>
                    </section>
                </div>
            @else
                <section class="rounded-[2rem] border border-dashed border-slate-300 bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-2xl font-black text-slate-950 dark:text-white">No studio yet</p>
                    <p class="mx-auto mt-2 max-w-xl text-sm leading-6 text-slate-500 dark:text-slate-400">Create your studio from the client portal. Once created, your account will be assigned as the studio admin and the studio will get its own subdomain login.</p>
                    <a href="{{ route('customer.studios.create') }}" class="mt-6 inline-flex rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white transition hover:bg-orange-600">Create Studio</a>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
