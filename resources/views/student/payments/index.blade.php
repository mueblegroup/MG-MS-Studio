<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Payment History</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Your subscriptions, upcoming charges, payment records, and downloadable receipts.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700 dark:border-green-900/40 dark:bg-green-900/20 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if($activeSubscriptions->isNotEmpty())
            <section class="mb-5 overflow-hidden rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/20">
                <div class="border-b border-blue-200 px-5 py-4 dark:border-blue-900/50">
                    <h2 class="flex items-center gap-2 text-base font-extrabold text-blue-950 dark:text-blue-100">
                        <i class="bx bx-calendar-check text-xl"></i>
                        My active subscriptions
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-blue-800 dark:text-blue-300">Stripe renewal dates are refreshed from the active Stripe subscription. The end date is when recurring billing is scheduled to stop.</p>
                </div>

                <div class="divide-y divide-blue-200 dark:divide-blue-900/40">
                    @foreach($activeSubscriptions as $subscription)
                        @php
                            $scheduledEndDate = $subscription->meta['scheduled_class_end_date'] ?? $subscription->classModel?->until_date;
                            $endAt = $scheduledEndDate ? \Carbon\Carbon::parse($scheduledEndDate)->endOfDay() : null;
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                        @endphp
                        <div class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ ucfirst($subscription->status) }} · {{ ucfirst($subscription->billing_interval ?? 'recurring') }} billing</p>
                                @if($subscription->next_billing_at)
                                    <p class="mt-1 text-xs font-semibold text-blue-700 dark:text-blue-300">
                                        {{ $isStripe ? 'Next Stripe renewal' : 'Next payment request' }}:
                                        {{ $subscription->next_billing_at->format('d M Y, h:i A') }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-extrabold text-gray-950 dark:text-white">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</p>
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">{{ strtoupper($subscription->provider ?? '—') }}</p>
                            </div>
                            <div class="sm:text-right">
                                @if($endAt)
                                    <span class="inline-flex rounded-full bg-blue-200 px-3 py-1 text-xs font-extrabold text-blue-900 dark:bg-blue-900/50 dark:text-blue-100">Ends {{ $endAt->format('d M Y') }}</span>
                                    <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Billing stops after the final day</p>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-200 px-3 py-1 text-xs font-extrabold text-gray-700 dark:bg-gray-700 dark:text-gray-200">No scheduled end date</span>
                                    <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">Continues until cancelled</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($upcomingSubscriptions->isNotEmpty())
            <section class="mb-5 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/20">
                <div class="border-b border-amber-200 px-5 py-4 dark:border-amber-900/50">
                    <h2 class="flex items-center gap-2 text-base font-extrabold text-amber-950 dark:text-amber-100">
                        <i class="bx bx-time-five text-xl"></i>
                        Due within the next 3 days
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-amber-800 dark:text-amber-300">Stripe dates come from Stripe’s current billing period. HitPay dates indicate when a payment request is due.</p>
                </div>

                <div class="divide-y divide-amber-200 dark:divide-amber-900/40">
                    @foreach($upcomingSubscriptions as $subscription)
                        @php
                            $dueAt = $subscription->next_billing_at;
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                            $dueLabel = $dueAt->isToday()
                                ? 'Due today'
                                : ($dueAt->isTomorrow()
                                    ? 'Due tomorrow'
                                    : 'Due in '.now()->startOfDay()->diffInDays($dueAt->copy()->startOfDay()).' days');
                        @endphp
                        <div class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $isStripe ? 'Automatic Stripe renewal' : 'HitPay payment request' }} · {{ $subscription->billing_interval ?? 'subscription' }}</p>
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

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($payments as $p)
                            @php
                                $st = strtolower((string) ($p->status ?? ''));
                                $orderStatus = strtolower((string) ($p->order_status ?? ''));
                                $provider = strtolower((string) ($p->provider ?? $p->method ?? ''));

                                $badge = match (true) {
                                    str_contains($st, 'success'),
                                    str_contains($st, 'paid'),
                                    str_contains($st, 'complete'),
                                    str_contains($st, 'completed') => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',

                                    str_contains($st, 'fail'),
                                    str_contains($st, 'cancel') => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',

                                    str_contains($st, 'pending'),
                                    str_contains($st, 'past_due') => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-200',

                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                };

                                $canDownload = in_array($st, ['paid', 'success', 'completed', 'complete'], true);
                                $canPay = in_array($st, ['pending', 'past_due'], true)
                                    && in_array($orderStatus, ['pending', 'past_due'], true)
                                    && $provider === 'hitpay';

                                $displayDate = $p->paid_at ?: $p->created_at;
                                $formattedDate = $displayDate
                                    ? \Carbon\Carbon::parse($displayDate)->format('Y-m-d H:i')
                                    : '—';
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ $formattedDate }}
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ $p->reference ?? ('PAY-' . $p->id) }}
                                    </div>

                                    @if($p->billing_reason === 'subscription_cycle')
                                        <div class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-300">Subscription renewal</div>
                                    @elseif($p->billing_reason === 'subscription_initial')
                                        <div class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-300">Subscription start</div>
                                    @endif

                                    @if($p->provider_reference)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p->provider_reference }}</div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ strtoupper($p->currency ?? 'MYR') }} {{ number_format((float) ($p->amount ?? 0), 2) }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200 whitespace-nowrap">
                                    {{ strtoupper($p->provider ?? $p->method ?? '—') }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ strtoupper($p->status ?? '—') }}</span>
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex flex-col items-end gap-2 sm:flex-row sm:justify-end">
                                        @if($canPay)
                                            <form method="POST" action="{{ route('shop.checkout.payments.retry', $p->id) }}">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                                    <i class="bx bx-credit-card"></i> Pay Now
                                                </button>
                                            </form>
                                        @endif

                                        @if($canDownload)
                                            <a href="{{ route('student.payments.receipt.download', $p->id) }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition">
                                                <i class="bx bx-download"></i> Receipt
                                            </a>
                                        @elseif(!$canPay)
                                            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-semibold text-gray-400 dark:text-gray-500 bg-gray-50 dark:bg-gray-800">Unavailable</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No payments found.</td>
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
