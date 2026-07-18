<x-customer-layout>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 sm:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.22em] text-orange-500">Review Upgrade</p>
            <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Confirm your plan upgrade</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                Review exactly what will change before Stripe updates the subscription. Confirming this page may immediately charge the saved payment method.
            </p>
        </div>

        @if ($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
                <p class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Plan change</p>
                <div class="mt-5 space-y-4">
                    <div class="rounded-2xl bg-slate-50 p-4 dark:bg-slate-950">
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Current plan</p>
                        <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">{{ $currentPlan?->name ?? $studio->plan_name }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-500">
                            {{ $currentPlan?->currency ?? $targetPlan->currency }} {{ number_format((float) ($currentPlan?->price ?? 0), 2) }} /{{ $currentPlan?->billing_interval ?? 'period' }}
                        </p>
                    </div>

                    <div class="flex justify-center text-2xl text-orange-500">
                        <i class="bx bx-down-arrow-alt"></i>
                    </div>

                    <div class="rounded-2xl border border-orange-200 bg-orange-50 p-4 dark:border-orange-900/50 dark:bg-orange-950/30">
                        <p class="text-xs font-bold uppercase tracking-wider text-orange-600 dark:text-orange-300">New plan</p>
                        <p class="mt-2 text-xl font-black text-slate-950 dark:text-white">{{ $targetPlan->name }}</p>
                        <p class="mt-1 text-sm font-bold text-slate-600 dark:text-slate-300">
                            {{ $targetPlan->currency }} {{ number_format((float) $targetPlan->price, 2) }} /{{ $targetPlan->billing_interval }}
                        </p>
                        <div class="mt-4 grid gap-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                            <span>Students: {{ $targetPlan->max_students ?? 'Unlimited' }}</span>
                            <span>Teachers: {{ $targetPlan->max_teachers ?? 'Unlimited' }}</span>
                            <span>Admins: {{ $targetPlan->max_admins ?? 'Unlimited' }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-[2rem] border border-amber-300 bg-amber-50 p-6 shadow-sm dark:border-amber-800 dark:bg-amber-950/30">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-amber-500 text-white">
                        <i class="bx bx-credit-card text-xl"></i>
                    </div>
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-700 dark:text-amber-300">Immediate payment warning</p>
                        <h2 class="mt-2 text-2xl font-black text-amber-950 dark:text-white">Stripe may charge now</h2>
                    </div>
                </div>

                <div class="mt-6 rounded-2xl bg-white p-5 ring-1 ring-amber-200 dark:bg-slate-900 dark:ring-amber-900/60">
                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Estimated prorated charge</p>
                    <p class="mt-2 text-4xl font-black text-slate-950 dark:text-white">
                        {{ $preview['currency'] }} {{ number_format((float) $preview['amount_due'], 2) }}
                    </p>
                    <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">
                        This estimate is calculated by Stripe for the unused portion of your current plan and the remaining portion of the new plan. The final amount can change slightly if the confirmation is delayed.
                    </p>
                </div>

                <div class="mt-5 space-y-3 text-sm font-semibold text-amber-950 dark:text-amber-100">
                    <div class="flex justify-between gap-4 rounded-2xl bg-amber-100/70 p-4 dark:bg-amber-900/30">
                        <span>Payment method</span>
                        <strong class="text-right">
                            @if (! empty($preview['payment_method_last4']))
                                {{ strtoupper((string) $preview['payment_method_brand']) }} ending {{ $preview['payment_method_last4'] }}
                            @else
                                Saved Stripe payment method
                            @endif
                        </strong>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-amber-100/70 p-4 dark:bg-amber-900/30">
                        <span>New recurring price</span>
                        <strong class="text-right">{{ $targetPlan->currency }} {{ number_format((float) $targetPlan->price, 2) }} /{{ $targetPlan->billing_interval }}</strong>
                    </div>
                    <div class="flex justify-between gap-4 rounded-2xl bg-amber-100/70 p-4 dark:bg-amber-900/30">
                        <span>Current period ends</span>
                        <strong class="text-right">{{ optional($preview['period_end'])->format('d M Y H:i') ?? 'Stripe billing date' }}</strong>
                    </div>
                </div>
            </section>
        </div>

        <form method="POST" action="{{ route('customer.billing.upgrade', $targetPlan) }}" class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 sm:p-8">
            @csrf

            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 p-4 dark:border-slate-700">
                <input type="checkbox" name="acknowledge_immediate_charge" value="1" required class="mt-1 rounded border-slate-300 text-orange-500 focus:ring-orange-500">
                <span>
                    <span class="block font-black text-slate-950 dark:text-white">I understand and approve this upgrade.</span>
                    <span class="mt-1 block text-sm leading-6 text-slate-500 dark:text-slate-400">
                        I understand that Stripe will use my saved payment method and may immediately deduct the prorated amount shown above. I also understand that future renewals will use the {{ $targetPlan->name }} price.
                    </span>
                </span>
            </label>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('customer.billing') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">
                    Confirm upgrade and charge
                </button>
            </div>
        </form>
    </div>
</x-customer-layout>
