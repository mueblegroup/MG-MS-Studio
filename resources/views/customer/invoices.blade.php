<x-customer-layout>
    <div class="space-y-6">
        <div class="flex flex-col gap-4 rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-500">Client Portal</p>
                <h1 class="mt-2 text-3xl font-black text-slate-950 dark:text-white">Invoices</h1>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">Platform subscription invoices for the client account. Student/class payment history belongs inside the studio admin area only.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Back to Overview</a>
                <a href="{{ route('customer.billing') }}" class="rounded-2xl bg-slate-950 px-4 py-2 text-sm font-black text-white transition hover:bg-slate-800 dark:bg-white dark:text-slate-950">Billing</a>
            </div>
        </div>

        <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">Platform Invoice Records</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Generated from SaaS subscription payment records only.</p>
                </div>
                <div class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-black text-slate-700 dark:bg-slate-800 dark:text-slate-200">{{ $payments->count() }} records</div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100 text-sm dark:divide-slate-800">
                    <thead class="text-left text-xs font-black uppercase tracking-wider text-slate-500">
                        <tr>
                            <th class="py-3 pr-4">Invoice</th>
                            <th class="px-4 py-3">Studio</th>
                            <th class="px-4 py-3">Plan</th>
                            <th class="px-4 py-3">Period</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="py-3 pl-4 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($payments as $payment)
                            <tr>
                                <td class="py-4 pr-4">
                                    <p class="font-black text-slate-950 dark:text-white">{{ $payment->reference ?: 'INV-' . str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</p>
                                    <p class="text-xs text-slate-500">{{ optional($payment->paid_at)->format('d M Y') ?? optional($payment->created_at)->format('d M Y') }}</p>
                                </td>
                                <td class="px-4 py-4 text-slate-500">{{ $studio?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-slate-500">{{ $payment->plan?->name ?? 'Platform Subscription' }}</td>
                                <td class="px-4 py-4 text-slate-500">
                                    {{ optional($payment->period_start)->format('d M Y') ?? '-' }}
                                    @if ($payment->period_end)
                                        - {{ $payment->period_end->format('d M Y') }}
                                    @endif
                                </td>
                                <td class="px-4 py-4"><span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black uppercase text-slate-700 dark:bg-slate-800 dark:text-slate-300">{{ $payment->status }}</span></td>
                                <td class="py-4 pl-4 text-right font-black text-slate-950 dark:text-white">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-14 text-center">
                                    <p class="text-lg font-black text-slate-950 dark:text-white">No platform invoices yet</p>
                                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Invoices will appear here after SaaS subscription payments are recorded.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-customer-layout>
