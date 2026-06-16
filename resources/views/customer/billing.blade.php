<x-customer-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-500">Client Portal</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Billing & Plan</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">This page is for SaaS platform subscription billing only. Studio/student payment history is not shown here.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Overview</a>
        </div>

        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-300">Current Subscription</p>
                <h2 class="mt-3 text-3xl font-black">{{ $studio?->platformSubscriptionPlan?->name ?? ucfirst($studio?->plan_name ?? 'No active plan') }}</h2>
                <div class="mt-5 space-y-3 text-sm text-slate-300">
                    <div class="flex justify-between gap-4 rounded-2xl bg-white/10 p-4"><span>Studio</span><strong class="text-right text-white">{{ $studio?->name ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white/10 p-4"><span>Status</span><strong class="text-white">{{ $studio ? ucfirst($studio->status) : '-' }}</strong></div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white/10 p-4"><span>Trial Ends</span><strong class="text-white">{{ optional($studio?->trial_ends_at)->format('d M Y') ?? '-' }}</strong></div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-white/10 p-4"><span>Subscription Ends</span><strong class="text-white">{{ optional($studio?->subscription_ends_at)->format('d M Y') ?? '-' }}</strong></div>
                </div>
                <p class="mt-5 text-xs leading-5 text-slate-400">Payment gateway automation can be connected here later. This area should only contain SaaS subscription billing records.</p>
            </section>

            <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <h2 class="text-xl font-black text-slate-950 dark:text-white">Available Platform Plans</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @forelse ($plans as $plan)
                        <div class="rounded-3xl border border-slate-200 p-5 dark:border-slate-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-lg font-black text-slate-950 dark:text-white">{{ $plan->name }}</p>
                                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $plan->description ?: 'Platform subscription package for one studio.' }}</p>
                                </div>
                                @if ($studio?->platform_subscription_plan_id === $plan->id)
                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">Current</span>
                                @endif
                            </div>
                            <p class="mt-4 text-2xl font-black text-slate-950 dark:text-white">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} <span class="text-sm font-bold text-slate-500">/{{ $plan->billing_interval }}</span></p>
                            <div class="mt-4 grid gap-2 text-xs font-bold text-slate-500">
                                <span>Students: {{ $plan->max_students ?? 'Unlimited' }}</span>
                                <span>Teachers: {{ $plan->max_teachers ?? 'Unlimited' }}</span>
                                <span>Admins: {{ $plan->max_admins ?? 'Unlimited' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-3xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:text-slate-400 md:col-span-2">No active plans configured yet.</div>
                    @endforelse
                </div>
            </section>
        </div>

        <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">Platform Subscription Records</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Only client SaaS subscription payments are shown here.</p>
                </div>
                <a href="{{ route('customer.invoices') }}" class="text-sm font-black text-orange-600 hover:text-orange-700">View invoices</a>
            </div>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr><th class="py-3 pr-4">Plan</th><th class="px-4 py-3">Reference</th><th class="px-4 py-3">Date</th><th class="px-4 py-3">Status</th><th class="py-3 pl-4 text-right">Amount</th></tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="py-4 pr-4 font-black text-slate-950 dark:text-white">{{ $payment->plan?->name ?? 'Platform Subscription' }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ $payment->reference ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ optional($payment->paid_at)->format('d M Y') ?? optional($payment->created_at)->format('d M Y') }}</td>
                                <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $payment->status }}</span></td>
                                <td class="py-4 pl-4 text-right font-black text-slate-950 dark:text-white">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-10 text-center text-slate-500 dark:text-slate-400">No platform subscription payment records yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-customer-layout>
