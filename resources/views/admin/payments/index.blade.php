<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Payment History</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Completed transactions and subscriptions due within the next three days.</p>
            </div>
        </div>

        @if($upcomingSubscriptions->isNotEmpty())
            <section class="mb-5 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/20">
                <div class="flex flex-col gap-2 border-b border-amber-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-amber-900/50">
                    <div>
                        <h2 class="flex items-center gap-2 text-base font-extrabold text-amber-950 dark:text-amber-100">
                            <i class="bx bx-time-five text-xl"></i>
                            Upcoming subscription payments
                        </h2>
                        <p class="mt-1 text-xs font-semibold text-amber-800 dark:text-amber-300">These are scheduled dues, not payment records yet. Stripe or HitPay will create the transaction when billing occurs.</p>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full bg-amber-200 px-3 py-1 text-xs font-extrabold text-amber-900 dark:bg-amber-900/50 dark:text-amber-100">
                        {{ $upcomingSubscriptions->count() }} due soon
                    </span>
                </div>

                <div class="divide-y divide-amber-200 dark:divide-amber-900/40">
                    @foreach($upcomingSubscriptions as $subscription)
                        @php
                            $dueAt = $subscription->next_billing_at;
                            $dueLabel = $dueAt->isToday()
                                ? 'Due today'
                                : ($dueAt->isTomorrow()
                                    ? 'Due tomorrow'
                                    : 'Due in '.now()->startOfDay()->diffInDays($dueAt->copy()->startOfDay()).' days');
                        @endphp
                        <div class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</p>
                                <p class="mt-1 truncate text-xs font-semibold text-gray-600 dark:text-gray-300">
                                    {{ $subscription->user?->name ?? 'Unknown student' }}
                                    @if($subscription->user?->email)
                                        · {{ $subscription->user->email }}
                                    @endif
                                </p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-extrabold text-gray-950 dark:text-white">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</p>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ strtoupper($subscription->provider ?? '—') }}</p>
                            </div>
                            <div class="sm:text-right">
                                <span class="inline-flex rounded-full bg-amber-200 px-3 py-1 text-xs font-extrabold text-amber-900 dark:bg-amber-900/50 dark:text-amber-100">{{ $dueLabel }}</span>
                                <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $dueAt->format('d M Y, h:i A') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4 mb-4">
            <form method="GET" action="{{ route('payments.index') }}" class="flex flex-col sm:flex-row gap-2">
                <input name="q" value="{{ $q }}" placeholder="Search reference / provider ref…"
                       class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />

                <select name="status"
                        class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    @foreach(['pending','paid','cancelled','failed'] as $s)
                        <option value="{{ $s }}" @selected($status === $s)>{{ strtoupper($s) }}</option>
                    @endforeach
                </select>

                <select name="provider"
                        class="rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    <option value="">All Providers</option>
                    @foreach(['stripe','hitpay'] as $p)
                        <option value="{{ $p }}" @selected($provider === $p)>{{ strtoupper($p) }}</option>
                    @endforeach
                </select>

                <button class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    Filter
                </button>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">User</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Provider</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($payments as $pay)
                            @php
                                $paymentStatus = $pay->status ?? 'pending';
                                $badge = match($paymentStatus) {
                                    'paid' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
                                    'failed' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                    'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                    default => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200',
                                };
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $pay->paid_at?->format('Y-m-d H:i') ?? $pay->created_at->format('Y-m-d H:i') }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $pay->reference ?? ('PAY-' . $pay->id) }}
                                    </div>
                                    @if($pay->provider_reference)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Provider Ref: {{ $pay->provider_reference }}
                                        </div>
                                    @endif
                                    @php
                                        $itemCount = $pay->order?->items?->sum('quantity') ?? 0;
                                    @endphp
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Items: {{ $itemCount }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $pay->user->name ?? '-' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $pay->user->email ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ strtoupper($pay->provider ?? $pay->method ?? '-') }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ strtoupper($paymentStatus) }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $pay->currency ?? 'MYR' }} {{ number_format((float)$pay->amount, 2) }}
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('payments.show', $pay->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                        <i class="bx bx-receipt"></i> View
                                    </a>
                                    <a href="{{ route('payments.receipt.download', $pay->id) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                        <i class="bx bx-receipt"></i> Receipt
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No payments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $payments->links() }}
            </div>
        </div>

    </div>
</x-app-layout>
