<x-customer-layout>
    <div class="space-y-6">
        <div class="rounded-[2rem] bg-gradient-to-br from-slate-950 via-slate-900 to-orange-900 p-8 text-white shadow-xl shadow-slate-900/20">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.22em] text-orange-200">Client Portal</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Account Settings</h1>
                    <p class="mt-3 max-w-2xl text-sm font-medium leading-6 text-slate-200">Manage the client account used for SaaS billing, studio ownership, and platform access.</p>
                </div>
                @if($studio)
                    <div class="rounded-3xl bg-white/10 px-5 py-4 ring-1 ring-white/15">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-slate-300">Assigned Studio</p>
                        <p class="mt-1 text-lg font-black">{{ $studio->name }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if(session('warning'))
            <div class="rounded-3xl border border-amber-300 bg-amber-50 px-5 py-4 text-sm font-bold text-amber-900 dark:border-amber-800 dark:bg-amber-950/40 dark:text-amber-100">{{ session('warning') }}</div>
        @endif
        @if(!$user->hasCompleteClientProfile())
            <div class="rounded-3xl border border-red-200 bg-red-50 px-5 py-5 text-red-900 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-100">
                <p class="font-black">Client portal access is restricted</p>
                <p class="mt-1 text-sm font-semibold">Complete the required fields below. Missing: {{ collect($user->missingClientProfileFields())->map(fn($field) => str_replace('_', ' ', $field))->implode(', ') }}.</p>
            </div>
        @endif
        @if (session('status') === 'profile-updated')
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">Profile updated successfully.</div>
        @endif
        @if (session('status') === 'password-updated')
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/40 dark:text-emerald-200">Password updated successfully.</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <div class="space-y-6">
                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="max-w-3xl">@include('profile.partials.update-profile-information-form')</div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Password</h2>
                        <p class="mt-1 text-sm font-semibold text-slate-500 dark:text-slate-400">Change the password used for the client portal and studio admin area.</p>
                    </div>
                    <div class="max-w-2xl">@include('profile.partials.update-password-form')</div>
                </section>

                <section class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    @include('profile.partials.two-factor-authentication-form')
                </section>
            </div>

            <aside class="space-y-6">
                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">Profile Status</h3>
                    <div class="mt-4 rounded-2xl {{ $user->hasCompleteClientProfile() ? 'bg-emerald-50 text-emerald-800' : 'bg-amber-50 text-amber-900' }} p-4 text-sm font-bold">
                        {{ $user->hasCompleteClientProfile() ? 'Complete — portal access enabled' : 'Incomplete — portal access locked' }}
                    </div>
                    <div class="mt-3 rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-600 dark:bg-slate-950/50 dark:text-slate-300">
                        Phone: {{ $user->phone_verified_at ? 'Verified' : 'Not verified yet' }}
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">Portal Boundary</h3>
                    <div class="mt-5 space-y-4 text-sm font-semibold leading-6 text-slate-600 dark:text-slate-300">
                        <div class="rounded-3xl bg-slate-50 p-4 dark:bg-slate-950/50"><p class="font-black text-slate-950 dark:text-white">Main domain</p><p class="mt-1">Client portal, SaaS billing, invoices, and studio setup.</p></div>
                        <div class="rounded-3xl bg-orange-50 p-4 text-orange-900 dark:bg-orange-950/30 dark:text-orange-200"><p class="font-black">Studio subdomain</p><p class="mt-1">Studio LMS operations, classes, teachers, students, and schedules.</p></div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <h3 class="text-lg font-black text-slate-950 dark:text-white">Danger Zone</h3>
                    <p class="mt-2 text-sm font-semibold leading-6 text-slate-500 dark:text-slate-400">Deleting the client owner account is restricted because it controls studio ownership and billing.</p>
                    @if(auth()->user()->role === 'admin')
                        <div class="mt-5 rounded-3xl border border-red-100 bg-red-50 p-4 dark:border-red-900/50 dark:bg-red-950/30">@include('profile.partials.delete-user-form')</div>
                    @endif
                </div>
            </aside>
        </div>
    </div>
</x-customer-layout>
