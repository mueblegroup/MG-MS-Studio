<x-customer-layout>
    @php
        $studioUrl = $studio ? (($studio->custom_domain ?: ($studio->subdomain . '.' . $rootDomain))) : null;
        $completedSteps = collect($setupSteps)->where('complete', true)->count();
        $totalSteps = count($setupSteps);
        $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;
        $trialEnds = $studio?->trial_ends_at;
        $subscriptionEnds = $studio?->subscription_ends_at;
        $latestPayment = $payments->first();
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-3xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="rounded-3xl border border-sky-200 bg-sky-50 p-4 text-sm font-bold text-sky-800 dark:border-sky-900 dark:bg-sky-950/30 dark:text-sky-200">{{ session('info') }}</div>
        @endif

        <section class="overflow-hidden rounded-[2rem] bg-slate-950 text-white shadow-xl">
            <div class="grid gap-6 p-6 lg:grid-cols-[1fr_360px] lg:p-8">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-300">Client Admin Portal</p>
                    <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Welcome back, {{ auth()->user()->name }}.</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-300">Manage SaaS onboarding, subscription, invoices, and studio launch access from here.</p>
                    <div class="mt-6 flex flex-wrap gap-3">
                        @if ($studio)
                            <a href="{{ route('customer.studios.launch', $studio) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Open Studio Admin <span class="ml-1" aria-hidden="true">↗</span></a>
                            <a href="{{ route('customer.billing') }}" class="inline-flex items-center justify-center rounded-2xl bg-white/10 px-5 py-3 text-sm font-black text-white hover:bg-white/20">Manage Plan</a>
                        @else
                            <a href="{{ route('customer.studios.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white">Create Studio</a>
                        @endif
                    </div>
                </div>
                <div class="rounded-3xl bg-white/10 p-5 ring-1 ring-white/10"><p class="text-xs font-bold uppercase tracking-[0.2em] text-slate-400">Setup Progress</p><p class="mt-2 text-4xl font-black">{{ $progress }}%</p><div class="mt-4 h-3 overflow-hidden rounded-full bg-white/10"><div class="h-full rounded-full bg-orange-400" style="width: {{ $progress }}%"></div></div><p class="mt-4 text-sm leading-6 text-slate-300">{{ $completedSteps }} of {{ $totalSteps }} SaaS setup steps completed.</p></div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"><p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Studio</p><p class="mt-3 text-2xl font-black">{{ $studio?->name ?? 'Not created' }}</p><p class="mt-2 text-sm font-semibold text-slate-500">{{ $studio ? ucfirst($studio->effectiveStatus()) : 'Create a studio to activate your LMS workspace.' }}</p></div>
            <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"><p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Plan</p><p class="mt-3 text-2xl font-black">{{ $studio?->platformSubscriptionPlan?->name ?? ucfirst($studio?->plan_name ?? 'Trial') }}</p><p class="mt-2 text-sm font-semibold text-slate-500">Platform subscription only.</p></div>
            <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"><p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Access Ends</p><p class="mt-3 text-2xl font-black">{{ optional($subscriptionEnds ?? $trialEnds)->format('d M Y') ?? '-' }}</p><p class="mt-2 text-sm font-semibold text-slate-500">Renew before this date to avoid portal interruption.</p></div>
            <div class="rounded-[2rem] bg-white p-5 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"><p class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">Latest Invoice</p><p class="mt-3 text-2xl font-black">{{ $latestPayment ? $latestPayment->currency . ' ' . number_format((float) $latestPayment->amount, 2) : '-' }}</p><p class="mt-2 text-sm font-semibold text-slate-500">{{ $latestPayment ? ucfirst($latestPayment->status) : 'No platform invoice yet.' }}</p></div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
            <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <h2 class="text-xl font-black">SaaS setup checklist</h2>
                <div class="mt-6 space-y-3">@foreach ($setupSteps as $step)<div class="flex gap-4 rounded-3xl border border-slate-100 p-4 dark:border-slate-800"><div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-2xl {{ $step['complete'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}"><i class="bx {{ $step['complete'] ? 'bx-check' : 'bx-time-five' }} text-xl"></i></div><div><p class="font-black">{{ $step['title'] }}</p><p class="mt-1 text-sm leading-6 text-slate-500">{{ $step['description'] }}</p></div></div>@endforeach</div>
            </div>
            <div class="space-y-6">
                <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10"><h2 class="text-xl font-black">Studio access</h2>@if ($studio && $studioUrl)<p class="mt-2 break-all text-sm font-bold text-slate-500">https://{{ $studioUrl }}/admin/dashboard</p><a href="{{ route('customer.studios.launch', $studio) }}" target="_blank" rel="noopener noreferrer" class="mt-5 inline-flex rounded-2xl bg-slate-950 px-5 py-3 text-sm font-black text-white dark:bg-white dark:text-slate-950">Open Studio Admin <span class="ml-1" aria-hidden="true">↗</span></a>@else<p class="mt-2 text-sm text-slate-500">Create your studio to activate the studio admin portal.</p>@endif</div>
                <div class="rounded-[2rem] bg-orange-50 p-6 text-orange-900 ring-1 ring-orange-100 dark:bg-orange-950/30 dark:text-orange-200"><h2 class="text-lg font-black">Permission separation</h2><p class="mt-2 text-sm font-semibold leading-6">Client portal pages remain on the main SaaS domain. Studio operations remain in the studio subdomain.</p></div>
            </div>
        </section>
    </div>
</x-customer-layout>
