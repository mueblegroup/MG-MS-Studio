<x-customer-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-orange-500">Review Plan Change</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Confirm your plan change</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                @if (! empty($preview['interval_changed']))
                    This change switches your billing frequency from {{ $preview['current_interval'] }} to {{ $preview['target_interval'] }}. Stripe will start a fresh billing cycle now, credit unused paid time from the old cycle, and charge the resulting balance.
                @else
                    Nothing changes until you approve this page. Stripe will apply the higher plan immediately and charge only the prorated difference for the remaining time in the current billing period.
                @endif
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">What will change</p>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current plan</p>
                        <p class="mt-2 text-xl font-black">{{ $currentPlan?->name ?? $studio->plan_name }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-500">{{ $currentPlan?->currency ?? $targetPlan->currency }} {{ number_format((float) ($currentPlan?->price ?? 0), 2) }} /{{ $currentPlan?->billing_interval ?? 'period' }}</p>
                    </div>
                    <div class="text-center text-2xl text-orange-500">↓</div>
                    <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/50 dark:bg-orange-950/30">
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-300">New plan starts after payment confirmation</p>
                        <p class="mt-2 text-xl font-black">{{ $targetPlan->name }}</p>
                        <p class="mt-1 text-sm font-bold">{{ $targetPlan->currency }} {{ number_format((float) $targetPlan->price, 2) }} /{{ $targetPlan->billing_interval }}</p>
                        <div class="mt-4 grid gap-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                            <span>Students: {{ $targetPlan->max_students ?? 'Unlimited' }}</span>
                            <span>Teachers: {{ $targetPlan->max_teachers ?? 'Unlimited' }}</span>
                            <span>Admins: {{ $targetPlan->max_admins ?? 'Unlimited' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-amber-300 bg-amber-50 p-6 shadow-sm dark:border-amber-800 dark:bg-amber-950/30">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Payment preview</p>
                <h2 class="mt-2 text-2xl font-black text-amber-950 dark:text-white">Stripe may charge now</h2>
                <div class="mt-6 rounded-2xl bg-white p-5 ring-1 ring-amber-200 dark:bg-slate-900 dark:ring-amber-900/60">
                    <p class="text-sm font-bold text-slate-500">Estimated charge today</p>
                    <p class="mt-2 text-4xl font-black">{{ $preview['currency'] }} {{ number_format((float) $preview['amount_due'], 2) }}</p>
                    @if (($preview['credit_amount'] ?? 0) > 0)
                        <p class="mt-2 text-sm font-bold text-emerald-700 dark:text-emerald-300">Estimated unused-time credit: {{ $preview['currency'] }} {{ number_format((float) $preview['credit_amount'], 2) }}</p>
                    @endif
                </div>

                <div class="mt-5 rounded-2xl bg-amber-100/70 p-5 text-sm font-semibold leading-6 text-amber-950 dark:bg-amber-900/30 dark:text-amber-100">
                    @if (! empty($preview['interval_changed']))
                        <p class="font-black">Why the billing date changes</p>
                        <p class="mt-2">Your current {{ $preview['current_interval'] }} cycle is paid until <strong>{{ optional($preview['period_end'])->format('d M Y H:i') ?? 'the existing renewal date' }}</strong>. Because the new plan uses a {{ $preview['target_interval'] }} cycle, Stripe resets the billing anchor to today instead of forcing the new interval to end on the old date.</p>
                        <p class="mt-3">Unused paid time from the old cycle is credited. The new cycle is expected to run until <strong>{{ optional($preview['new_period_end'])->format('d M Y H:i') ?? 'the new Stripe renewal date' }}</strong>.</p>
                    @else
                        <p class="font-black">Why this amount is charged</p>
                        <p class="mt-2">Your current subscription has already paid for service until <strong>{{ optional($preview['period_end'])->format('d M Y H:i') ?? 'the current renewal date' }}</strong>. Stripe credits the unused value of the old plan, charges only the remaining-time value of {{ $targetPlan->name }}, and keeps the same renewal date.</p>
                        <p class="mt-3">Your next normal renewal will occur on that date at {{ $targetPlan->currency }} {{ number_format((float) $targetPlan->price, 2) }} per {{ $targetPlan->billing_interval }}.</p>
                    @endif
                </div>

                <div class="mt-5 space-y-3 text-sm font-semibold text-amber-950 dark:text-amber-100">
                    <div class="flex justify-between gap-4 rounded-2xl bg-amber-100/70 p-4 dark:bg-amber-900/30">
                        <span>Payment method</span>
                        <strong class="text-right">@if (! empty($preview['payment_method_last4'])) {{ strtoupper((string) $preview['payment_method_brand']) }} ending {{ $preview['payment_method_last4'] }} @else Saved Stripe payment method @endif</strong>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-amber-100/70 p-4 dark:bg-amber-900/30">
                        <span>{{ ! empty($preview['interval_changed']) ? 'New renewal date' : 'Renewal date remains' }}</span>
                        <strong class="text-right">{{ optional(! empty($preview['interval_changed']) ? $preview['new_period_end'] : $preview['period_end'])->format('d M Y H:i') ?? 'Stripe billing date' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <form method="POST" action="{{ route('customer.billing.upgrade', $targetPlan) }}" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 sm:p-8">
            @csrf
            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                <input type="checkbox" name="acknowledge_immediate_charge" value="1" required class="mt-1 rounded border-slate-300 text-orange-500">
                <span>
                    <span class="block font-black">I understand and approve this plan change.</span>
                    <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">
                        @if (! empty($preview['interval_changed']))
                            I approve the immediate billing-frequency change, understand that unused paid time will be credited, and understand that the new {{ $preview['target_interval'] }} renewal cycle starts after payment is confirmed.
                        @else
                            I approve the immediate plan upgrade, understand that Stripe may deduct the estimated prorated amount using my saved payment method, and understand that the existing renewal date remains unchanged.
                        @endif
                    </span>
                </span>
            </label>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('customer.billing') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Confirm change and charge</button>
            </div>
        </form>
    </div>
</x-customer-layout>
