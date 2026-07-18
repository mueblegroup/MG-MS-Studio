<x-customer-layout>
    @php
        $currentPlan = $studio?->platformSubscriptionPlan;
        $effectiveStatus = $studio?->effectiveStatus();
        $hasLiveSubscription = $studio?->stripe_subscription_id && in_array($studio?->subscription_status, ['active', 'trialing', 'past_due'], true) && $effectiveStatus !== 'inactive';
        $studentCount = $studio ? $studio->users()->where('role', 'student')->count() : 0;
        $teacherCount = $studio ? $studio->users()->where('role', 'teacher')->count() : 0;
        $adminCount = $studio ? $studio->users()->where('role', 'admin')->count() : 0;
        $usagePercentages = collect([
            $currentPlan?->max_students ? ($studentCount / max(1, $currentPlan->max_students)) * 100 : 0,
            $currentPlan?->max_teachers ? ($teacherCount / max(1, $currentPlan->max_teachers)) * 100 : 0,
            $currentPlan?->max_admins ? ($adminCount / max(1, $currentPlan->max_admins)) * 100 : 0,
        ]);
        $seatUsagePercent = (int) round($usagePercentages->max() ?? 0);
        $shouldRecommendUpgrade = $hasLiveSubscription && $seatUsagePercent >= 70;
        $highestPlanPrice = (float) ($plans->max('price') ?? 0);
        $isHighestPlan = $currentPlan && (float) $currentPlan->price >= $highestPlanPrice;
        $autoRenewEnabled = $hasLiveSubscription && ! $studio?->cancel_at_period_end;
        $periodDateLabel = $studio?->cancel_at_period_end ? 'Access Ends' : 'Next Renewal';
    @endphp

    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">{{ session('error') }}</div>
        @endif

        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-500">Client Portal</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Billing & Plan</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">Renew expired access, manage payment details, and review Stripe-calculated charges before approving an upgrade.</p>
            </div>
            <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Overview</a>
        </div>

        @if ($effectiveStatus === 'inactive')
            <div class="rounded-[2rem] border border-red-200 bg-red-50 p-6 dark:border-red-900/50 dark:bg-red-950/30">
                <h2 class="text-xl font-black text-red-900 dark:text-red-100">Studio access is inactive</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-red-700 dark:text-red-200">The paid period or trial has ended. The studio domain is paused until a studio owner renews or subscribes to another available plan below. Existing studio data remains safely stored and will return after successful payment.</p>
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
            <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl">
                <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-300">Current Subscription</p>
                <h2 class="mt-3 text-3xl font-black">{{ $currentPlan?->name ?? ucfirst($studio?->plan_name ?? 'No active plan') }}</h2>
                <div class="mt-5 space-y-3 text-sm text-slate-300">
                    <div class="flex justify-between rounded-2xl bg-white/10 p-4"><span>Status</span><strong class="text-white">{{ ucfirst($effectiveStatus ?? '-') }}</strong></div>
                    <div class="flex justify-between rounded-2xl bg-white/10 p-4"><span>{{ $periodDateLabel }}</span><strong class="text-white">{{ optional($studio?->subscription_ends_at)->format('d M Y H:i') ?? '-' }}</strong></div>
                    <div class="flex justify-between rounded-2xl bg-white/10 p-4"><span>Auto-renewal</span><strong class="{{ $autoRenewEnabled ? 'text-emerald-300' : 'text-amber-300' }}">{{ $autoRenewEnabled ? 'Enabled' : ($hasLiveSubscription ? 'Disabled' : '-') }}</strong></div>
                    <div class="flex justify-between rounded-2xl bg-white/10 p-4"><span>Highest seat usage</span><strong class="text-white">{{ min($seatUsagePercent, 100) }}%</strong></div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="rounded-xl bg-white/10 p-3"><strong class="block text-white">{{ $studentCount }}</strong>Students</div>
                        <div class="rounded-xl bg-white/10 p-3"><strong class="block text-white">{{ $teacherCount }}</strong>Teachers</div>
                        <div class="rounded-xl bg-white/10 p-3"><strong class="block text-white">{{ $adminCount }}</strong>Admins</div>
                    </div>
                </div>

                @if ($studio?->stripe_customer_id)
                    <form method="POST" action="{{ route('customer.billing.portal') }}" class="mt-5">@csrf<button class="w-full rounded-2xl bg-white px-4 py-3 text-sm font-black text-slate-950 hover:bg-slate-100">Manage payment method</button></form>
                @endif

                @if ($hasLiveSubscription)
                    @if ($studio?->cancel_at_period_end)
                        <div class="mt-4 rounded-2xl border border-amber-300/40 bg-amber-400/10 p-4 text-sm leading-6 text-amber-100">
                            Auto-renewal is off. Your studio remains active until {{ optional($studio?->subscription_ends_at)->format('d M Y H:i') ?? 'the current paid period ends' }}.
                        </div>
                        <form method="POST" action="{{ route('customer.billing.resume') }}" class="mt-3">
                            @csrf
                            <button class="w-full rounded-2xl bg-emerald-500 px-4 py-3 text-sm font-black text-white hover:bg-emerald-600">Resume auto-renewal</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('customer.billing.cancel') }}" class="mt-4" onsubmit="return confirm('Turn off auto-renewal? Your studio will remain active until the current paid period ends, then access will stop unless you resume or subscribe again.')">
                            @csrf
                            <button class="w-full rounded-2xl border border-red-400/60 bg-red-500/10 px-4 py-3 text-sm font-black text-red-200 hover:bg-red-500/20">Turn off auto-renewal</button>
                        </form>
                        <p class="mt-2 text-xs leading-5 text-slate-400">This does not immediately close the studio or issue a refund. It schedules cancellation at the end of the current paid period.</p>
                    @endif
                @endif
            </section>

            <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <h2 class="text-xl font-black text-slate-950 dark:text-white">Available Platform Plans</h2>
                @if ($isHighestPlan)
                    <p class="mt-2 rounded-2xl bg-emerald-50 p-4 text-sm font-bold text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-300">You are already on the highest available plan.</p>
                @elseif ($shouldRecommendUpgrade)
                    <p class="mt-2 rounded-2xl bg-orange-50 p-4 text-sm font-bold text-orange-700 dark:bg-orange-950/30 dark:text-orange-300">Your highest seat usage is {{ $seatUsagePercent }}%. Upgrading is recommended before the studio reaches its current limits.</p>
                @elseif ($hasLiveSubscription)
                    <p class="mt-2 rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-600 dark:bg-slate-950 dark:text-slate-300">Your current highest seat usage is {{ $seatUsagePercent }}%. You may still upgrade at any time; the app will actively recommend it after usage reaches 70%.</p>
                @endif

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @forelse ($plans as $plan)
                        @php
                            $isCurrent = (int) $studio?->platform_subscription_plan_id === (int) $plan->id;
                            $isUpgrade = $currentPlan && (float) $plan->price > (float) $currentPlan->price;
                            $shouldShowPlan = ! $hasLiveSubscription || $isCurrent || ($isUpgrade && ! $isHighestPlan);
                        @endphp
                        @continue(! $shouldShowPlan)
                        <div class="rounded-3xl border {{ $isCurrent ? 'border-emerald-400' : 'border-slate-200 dark:border-slate-800' }} p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div><p class="text-lg font-black text-slate-950 dark:text-white">{{ $plan->name }}</p><p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $plan->description ?: 'Platform subscription package for one studio.' }}</p></div>
                                @if ($isCurrent)<span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-black uppercase text-emerald-700">Current</span>@endif
                            </div>
                            <p class="mt-4 text-2xl font-black text-slate-950 dark:text-white">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} <span class="text-sm font-bold text-slate-500">/{{ $plan->billing_interval }}</span></p>
                            <div class="mt-4 grid gap-2 text-xs font-bold text-slate-500"><span>Students: {{ $plan->max_students ?? 'Unlimited' }}</span><span>Teachers: {{ $plan->max_teachers ?? 'Unlimited' }}</span><span>Admins: {{ $plan->max_admins ?? 'Unlimited' }}</span></div>
                            <div class="mt-5">
                                @if (! $hasLiveSubscription)
                                    <form method="POST" action="{{ route('customer.billing.checkout', $plan) }}">@csrf<button class="w-full rounded-2xl bg-orange-500 px-4 py-3 text-sm font-black text-white hover:bg-orange-600">{{ $effectiveStatus === 'inactive' ? 'Renew with this plan' : 'Subscribe with Stripe' }}</button></form>
                                @elseif ($isUpgrade)
                                    <a href="{{ route('customer.billing.upgrade.confirm', $plan) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-4 py-3 text-sm font-black text-white hover:bg-orange-600">Review upgrade & charge</a>
                                @elseif ($isCurrent)
                                    <button disabled class="w-full rounded-2xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-500 dark:bg-slate-800">Current plan</button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="md:col-span-2 p-8 text-center text-sm text-slate-500">No active plans configured yet.</div>
                    @endforelse
                </div>
                @if ($hasLiveSubscription)
                    <p class="mt-5 text-xs leading-5 text-slate-400">Downgrades are not offered in self-service because reducing seat limits can affect existing users and studio operations. Contact platform support when a downgrade is genuinely required.</p>
                @endif
            </section>
        </div>

        <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
            <div class="flex items-center justify-between"><h2 class="text-xl font-black text-slate-950 dark:text-white">Platform Subscription Records</h2><a href="{{ route('customer.invoices') }}" class="text-sm font-black text-orange-600">View invoices</a></div>
            <div class="mt-5 overflow-x-auto"><table class="min-w-full text-sm"><tbody>@forelse ($payments as $payment)<tr class="border-t border-slate-100 dark:border-slate-800"><td class="py-4 font-black">{{ $payment->plan?->name ?? 'Platform Subscription' }}</td><td class="px-4 text-slate-500">{{ $payment->status }}</td><td class="py-4 text-right font-black">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td></tr>@empty<tr><td class="py-10 text-center text-slate-500">No payment records yet.</td></tr>@endforelse</tbody></table></div>
        </section>
    </div>
</x-customer-layout>
