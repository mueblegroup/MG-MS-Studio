<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Billing</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">SaaS Payments</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Track studio subscription payments separately from student, class card and studio-level payments.</p>
                </div>
                <div class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">
                    Paid Revenue: RM {{ number_format((float) $paidRevenue, 2) }}
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @foreach($statusCounts as $statusName => $count)
                <a href="{{ route('superadmin.platform-payments.index', ['status' => $statusName]) }}" class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">{{ $statusName ?: 'Unknown' }}</p>
                    <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ number_format((int) $count) }}</div>
                </a>
            @endforeach
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('superadmin.platform-payments.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-7">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Studio, provider or reference" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                </div>
                <div class="md:col-span-3">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Status</label>
                    <select name="status" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">All statuses</option>
                        @foreach($statusCounts->keys() as $statusName)
                            <option value="{{ $statusName }}" @selected($status === $statusName)>{{ ucfirst($statusName) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end md:col-span-2">
                    <button class="w-full rounded-2xl bg-[#d97706] px-4 py-3 text-sm font-extrabold text-white shadow-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">
                            <th class="py-3 pr-4">Studio</th>
                            <th class="py-3 pr-4">Plan</th>
                            <th class="py-3 pr-4">Provider</th>
                            <th class="py-3 pr-4">Reference</th>
                            <th class="py-3 pr-4">Amount</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3">Paid At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                        @forelse($payments as $payment)
                            <tr class="text-[#31261d] dark:text-gray-200">
                                <td class="py-4 pr-4">
                                    <div class="font-extrabold">{{ $payment->studio?->name ?? 'Unknown studio' }}</div>
                                    <div class="text-xs font-bold text-[#9a8c7d] dark:text-gray-500">{{ $payment->studio?->slug ?? '-' }}</div>
                                </td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $payment->plan?->name ?? 'Manual / legacy' }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $payment->provider ?? '-' }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $payment->reference ?? '-' }}</td>
                                <td class="py-4 pr-4 font-extrabold">{{ $payment->currency ?? 'MYR' }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-4 pr-4"><span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $payment->status }}</span></td>
                                <td class="py-4 text-[#6b5f52] dark:text-gray-400">{{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No platform payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $payments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
