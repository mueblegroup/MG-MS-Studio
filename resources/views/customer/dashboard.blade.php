<x-app-layout>
    @php
        $studioUrl = $studio ? (($studio->custom_domain ?: ($studio->subdomain . '.' . $rootDomain))) : null;
        $completedSteps = collect($setupSteps)->where('complete', true)->count();
        $totalSteps = count($setupSteps);
        $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
        $trialEnds = $studio?->trial_ends_at;
        $subscriptionEnds = $studio?->subscription_ends_at;
    @endphp

    <div class="min-h-screen bg-slate-50 dark:bg-gray-950">
        <div class="mx-auto grid max-w-7xl gap-6 px-4 py-6 sm:px-6 lg:grid-cols-[280px_1fr] lg:px-8">
            <aside class="rounded-[2rem] bg-slate-950 p-5 text-white shadow-xl shadow-slate-950/10 lg:sticky lg:top-6 lg:h-[calc(100vh-3rem)]">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-orange-500 text-lg font-black">M</div>
                    <div>
                        <p class="text-sm font-black leading-tight">Mueble LMS</p>
                        <p class="text-xs text-slate-400">Client Portal</p>
                    </div>
                </div>

                <nav class="mt-8 space-y-2 text-sm font-bold">
                    <a href="{{ route('customer.dashboard') }}" class="flex items-center justify-between rounded-2xl bg-white/10 px-4 py-3 text-white">
                        <span>Overview</span><span>→</span>
                    </a>
                    <a href="{{ route('customer.studio') }}" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/10 hover:text-white">My Studio</a>
                    <a href="{{ route('customer.billing') }}" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/10 hover:text-white">Billing & Plan</a>
                    <a href="{{ route('customer.invoices') }}" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/10 hover:text-white">Invoices</a>
                    <a href="{{ route('profile.edit') }}" class="block rounded-2xl px-4 py-3 text-slate-300 transition hover:bg-white/10 hover:text-white">Account</a>
                </nav>

                <div class="mt-8 rounded-3xl bg-white/10 p-4">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-orange-300">Portal Rule</p>
                    <p class="mt-2 text-sm leading-6 text-slate-200">Use this area for SaaS setup, subscription, billing, invoices, and studio launch. Use the studio subdomain for teachers, classes, students, and attendance.</p>
                </div>
            </aside>

            <main class="space-y-6">
                <section class="overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-xl shadow-slate-950/10">
                    <div class="grid gap-6 p-6 lg:grid-cols-[1fr_360px] lg:p-8">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-300">Client Admin Portal</p>
                            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Welcome back, {{ auth()->user()->name }}.</h1>
                            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Manage your studio subscription, billing, invoices, and launch access from the central SaaS portal. Operational LMS work stays inside your studio subdomain.</p>
                            <div class="mt-6 flex flex-wrap gap-3">
                                @if ($studio)
                                    <a href="{{ route('customer.studios.launch', $studio) }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600">Open Studio Login</a>
                                    <a href="{{ route('customer.billing') }}" class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/20">Manage Billing</a>
                                @else
                                    <a href="{{ route('customer.studios.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/25 transition hover:bg-orange-600">Create Studio</a>
                                    <a href="{{ route('customer.billing') }}" class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-black text-white transition hover:bg-white/20">View Plans</a>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/10">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Setup Progress</p>
                                    <p class="mt-2 text-4xl font-black">{{ $progress }}%</p>
                                </div>
                                <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-orange-500/20 text-xl font-black text-orange-200">{{ $completedSteps }}/{{ $totalSteps }}</div>
                            </div>
                            <div class="mt-5 h-3 overflow-hidden rounded-full bg-white/10">
                                <div class="h-full rounded-full bg-orange-500" style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="mt-4 text-sm text-slate-300">Complete setup and billing before using this with bigger institutes.</p>
                        </div>
                    </div>
                </section>

                @if (session('success'))
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>
                @endif
                @if (session('info'))
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm font-bold text-blue-700 dark:border-blue-900 dark:bg-blue-950/40 dark:text-blue-300">{{ session('info') }}</div>
                @endif

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Studio Status</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $studio ? ucfirst($studio->status) : 'Not created' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $studio ? 'Workspace is connected to your account.' : 'Create one studio to continue.' }}</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Current Plan</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $studio?->platformSubscriptionPlan?->name ?? ucfirst($studio?->plan_name ?? 'No plan') }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $studio?->platformSubscriptionPlan?->billing_interval ? 'Billed ' . $studio->platformSubscriptionPlan->billing_interval : 'Plan selection can be completed later.' }}</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Trial / Renewal</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $subscriptionEnds?->format('d M Y') ?? $trialEnds?->format('d M Y') ?? '-' }}</p>
                        <p class="mt-1 text-xs text-slate-500">{{ $subscriptionEnds ? 'Subscription period ends.' : ($trialEnds ? 'Trial period ends.' : 'No active period yet.') }}</p>
                    </div>
                    <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Recent Invoices</p>
                        <p class="mt-3 text-2xl font-black text-slate-950 dark:text-white">{{ $payments->count() }}</p>
                        <p class="mt-1 text-xs text-slate-500">Latest platform billing records.</p>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Studio Workspace</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Your customer account owns one studio portal.</p>
                            </div>
                            @if (! $studio)
                                <a href="{{ route('customer.studios.create') }}" class="rounded-2xl bg-orange-500 px-4 py-2 text-sm font-black text-white transition hover:bg-orange-600">Create Studio</a>
                            @endif
                        </div>

                        @if ($studio)
                            <div class="mt-5 rounded-3xl border border-slate-200 p-5 dark:border-gray-800">
                                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <p class="text-2xl font-black text-slate-950 dark:text-white">{{ $studio->name }}</p>
                                        <p class="mt-1 text-sm font-semibold text-slate-500">{{ $studioUrl }}</p>
                                        <div class="mt-3 flex flex-wrap gap-2 text-xs font-bold uppercase">
                                            <span class="rounded-full bg-orange-100 px-3 py-1 text-orange-700 dark:bg-orange-950 dark:text-orange-300">{{ $studio->status }}</span>
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-gray-800 dark:text-gray-300">{{ $studio->settings['currency'] ?? 'MYR' }}</span>
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-slate-700 dark:bg-gray-800 dark:text-gray-300">{{ $studio->settings['timezone'] ?? config('app.timezone') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('customer.studio') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-800">View Details</a>
                                        <a href="{{ route('customer.studios.launch', $studio) }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950">Open Login</a>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="mt-5 rounded-3xl border border-dashed border-slate-300 p-8 text-center dark:border-gray-700">
                                <p class="text-lg font-black text-slate-950 dark:text-white">No studio created yet</p>
                                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Create your studio to reserve a subdomain and start the LMS setup.</p>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Setup Checklist</h2>
                        <div class="mt-5 space-y-4">
                            @foreach ($setupSteps as $step)
                                <div class="flex gap-3">
                                    <div class="mt-1 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $step['complete'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-400 dark:bg-gray-800' }}">
                                        {{ $step['complete'] ? '✓' : $loop->iteration }}
                                    </div>
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $step['title'] }}</p>
                                        <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $step['description'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-2">
                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-black text-slate-950 dark:text-white">Billing Snapshot</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Central SaaS billing only. Student payments stay inside the studio app.</p>
                            </div>
                            <a href="{{ route('customer.billing') }}" class="text-sm font-black text-orange-600 hover:text-orange-700">View all</a>
                        </div>
                        <div class="mt-5 space-y-3">
                            @forelse ($payments as $payment)
                                <div class="flex items-center justify-between rounded-2xl border border-slate-100 p-4 dark:border-gray-800">
                                    <div>
                                        <p class="font-black text-slate-950 dark:text-white">{{ $payment->plan?->name ?? 'Platform Subscription' }}</p>
                                        <p class="text-xs text-slate-500">{{ optional($payment->paid_at)->format('d M Y') ?? optional($payment->created_at)->format('d M Y') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-black text-slate-950 dark:text-white">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
                                        <p class="text-xs font-bold uppercase text-slate-500">{{ $payment->status }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-gray-700 dark:text-slate-400">No platform billing records yet.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                        <h2 class="text-xl font-black text-slate-950 dark:text-white">Available Plans</h2>
                        <div class="mt-5 space-y-3">
                            @forelse ($plans->take(3) as $plan)
                                <div class="rounded-2xl border border-slate-100 p-4 dark:border-gray-800">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-black text-slate-950 dark:text-white">{{ $plan->name }}</p>
                                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $plan->description ?: 'Platform subscription package.' }}</p>
                                        </div>
                                        <p class="whitespace-nowrap text-sm font-black text-slate-950 dark:text-white">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500 dark:border-gray-700 dark:text-slate-400">No plans are active yet.</div>
                            @endforelse
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</x-app-layout>
