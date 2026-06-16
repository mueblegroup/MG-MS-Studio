<x-customer-layout>
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10 lg:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-300">Studio Onboarding</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Create your studio workspace.</h1>
            <p class="mt-4 text-sm leading-6 text-slate-300">This creates the tenant workspace, reserves the subdomain, and assigns your account as the studio owner/admin. After that, daily LMS management happens from the studio subdomain.</p>

            <div class="mt-8 space-y-4">
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">1 user = 1 studio</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">This portal intentionally prevents multiple studios per client account.</p>
                </div>
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">Client portal remains central</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">Billing, invoices, plans, domains, and studio setup stay here.</p>
                </div>
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">Studio admin is separate</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">Classes, teachers, students, schedules, attendance, and student payments stay inside the studio app.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-500">Setup Form</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Studio details</h2>
                </div>
                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
            </div>

            <form method="POST" action="{{ route('customer.studios.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Studio Name</label>
                    <input type="text" name="studio_name" value="{{ old('studio_name') }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="Etude Music Academy">
                    @error('studio_name') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Studio Subdomain</label>
                    <div class="flex rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" required class="min-w-0 flex-1 rounded-l-2xl border-0 px-4 py-3 text-sm font-semibold dark:bg-slate-800 dark:text-white" placeholder="etude2">
                        <span class="inline-flex items-center rounded-r-2xl bg-slate-50 px-4 text-sm font-black text-slate-500 dark:bg-slate-950 dark:text-slate-400">.{{ $rootDomain }}</span>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-500">This becomes the studio login domain.</p>
                    @error('subdomain') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Platform Subscription Plan</label>
                    <select name="platform_subscription_plan_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Start trial / assign plan later</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('platform_subscription_plan_id') == $plan->id)>{{ $plan->name }} - {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} / {{ $plan->billing_interval }}</option>
                        @endforeach
                    </select>
                    @error('platform_subscription_plan_id') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Timezone</label>
                        <input type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Currency</label>
                        <input type="text" name="currency" maxlength="3" value="{{ old('currency', 'MYR') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>

                <div class="rounded-3xl bg-orange-50 p-4 text-sm leading-6 text-orange-800 dark:bg-orange-950/40 dark:text-orange-200">
                    After creation, your account will be assigned to this studio and the studio admin login will be available from the subdomain only.
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">Create Studio Workspace</button>
                </div>
            </form>
        </section>
    </div>
</x-customer-layout>
