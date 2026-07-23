<x-app-layout>
    @php
        $studioTimezone = app(\App\Services\StudioSettingsService::class)
            ->get('timezone', config('app.timezone', 'UTC'));
    @endphp

    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Payment History</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Your subscriptions, upcoming charges, payment records, and downloadable receipts.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                <ul class="ml-5 list-disc">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($activeSubscriptions->isNotEmpty())
            <section class="mb-5 overflow-hidden rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/20">
                <div class="border-b border-blue-200 px-5 py-4 dark:border-blue-900/50">
                    <h2 class="flex items-center gap-2 text-base font-extrabold text-blue-950 dark:text-blue-100">
                        <i class="bx bx-calendar-check text-xl"></i> My active subscriptions
                    </h2>
                    <p class="mt-1 text-xs font-semibold text-blue-800 dark:text-blue-300">Stripe renewal dates are refreshed from Stripe. Cancelling stops future billing and removes access to upcoming sessions.</p>
                </div>

                <div class="divide-y divide-blue-200 dark:divide-blue-900/40">
                    @foreach($activeSubscriptions as $subscription)
                        @php
                            $scheduledEndDate = $subscription->meta['scheduled_class_end_date'] ?? $subscription->classModel?->until_date;
                            $endAt = $scheduledEndDate
                                ? \Carbon\Carbon::parse($scheduledEndDate, 'UTC')->timezone($studioTimezone)->endOfDay()
                                : null;
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                            $nextBillingAt = $subscription->next_billing_at?->copy()->timezone($studioTimezone);
                        @endphp

                        <div class="grid gap-4 px-5 py-4 lg:grid-cols-[minmax(0,1fr)_auto_auto_minmax(260px,340px)] lg:items-start">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ ucfirst($subscription->status) }} · {{ ucfirst($subscription->billing_interval ?? 'recurring') }} billing</p>

                                @if($nextBillingAt)
                                    <p class="mt-1 text-xs font-semibold text-blue-700 dark:text-blue-300">
                                        {{ $isStripe ? 'Next Stripe renewal' : 'Next payment request' }}: {{ $nextBillingAt->format('d M Y, h:i A') }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-left lg:text-right">
                                <p class="text-sm font-extrabold text-gray-950 dark:text-white">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</p>
                                <p class="text-xs font-semibold text-gray-500">{{ strtoupper($subscription->provider ?? '—') }}</p>
                            </div>

                            <div class="lg:text-right">
                                @if($endAt)
                                    <span class="inline-flex rounded-full bg-blue-200 px-3 py-1 text-xs font-extrabold text-blue-900">Ends {{ $endAt->format('d M Y') }}</span>
                                @else
                                    <span class="inline-flex rounded-full bg-gray-200 px-3 py-1 text-xs font-extrabold text-gray-700">Continues until cancelled</span>
                                @endif
                            </div>

                            <form method="POST" action="{{ route('student.subscriptions.cancel', $subscription->id) }}" class="grid gap-2 rounded-xl border border-red-200 bg-red-50 p-3" onsubmit="return confirm('Cancel this subscription and remove all upcoming class access?')">
                                @csrf
                                <label class="text-xs font-extrabold text-red-900">Reason for cancellation</label>
                                <textarea name="cancellation_reason" required minlength="5" maxlength="1000" rows="2" class="mg-input" placeholder="Tell the studio why you are leaving"></textarea>
                                <label class="flex items-start gap-2 text-xs font-bold text-red-900">
                                    <input type="checkbox" name="confirm_cancel" value="1" required>
                                    I understand future billing and upcoming class access will be cancelled.
                                </label>
                                <button class="mg-btn-danger"><i class="bx bx-x-circle"></i> Cancel Subscription</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($upcomingSubscriptions->isNotEmpty())
            <section class="mb-5 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/20">
                <div class="border-b border-amber-200 px-5 py-4 dark:border-amber-900/50">
                    <h2 class="flex items-center gap-2 text-base font-extrabold text-amber-950 dark:text-amber-100"><i class="bx bx-time-five text-xl"></i> Due within the next 3 days</h2>
                    <p class="mt-1 text-xs font-semibold text-amber-800 dark:text-amber-300">Stripe dates come from Stripe’s current billing period. HitPay dates indicate when a payment request is due.</p>
                </div>

                <div class="divide-y divide-amber-200 dark:divide-amber-900/40">
                    @foreach($upcomingSubscriptions as $subscription)
                        @php
                            $dueAt = $subscription->next_billing_at?->copy()->timezone($studioTimezone);
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                            $nowLocal = now()->timezone($studioTimezone);
                            $dueLabel = $dueAt?->isToday()
                                ? 'Due today'
                                : ($dueAt?->isTomorrow()
                                    ? 'Due tomorrow'
                                    : 'Due in '.$nowLocal->copy()->startOfDay()->diffInDays($dueAt->copy()->startOfDay()).' days');
                        @endphp

                        <div class="grid gap-3 px-5 py-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:items-center">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</p>
                                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $isStripe ? 'Automatic Stripe renewal' : 'HitPay payment request' }} · {{ $subscription->billing_interval ?? 'subscription' }}</p>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-sm font-extrabold">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</p>
                                <p class="text-xs font-semibold text-gray-500">{{ strtoupper($subscription->provider ?? '—') }}</p>
                            </div>
                            <div class="sm:text-right">
                                <span class="inline-flex rounded-full bg-amber-200 px-3 py-1 text-xs font-extrabold text-amber-900">{{ $dueLabel }}</span>
                                <p class="mt-1 text-xs font-semibold text-gray-500">{{ $dueAt?->format('d M Y, h:i A') ?? '—' }}</p>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Reference</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Amount</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($payments as $p)
                            @php
                                $st = strtolower((string) ($p->status ?? ''));
                                $orderStatus = strtolower((string) ($p->order_status ?? ''));
                                $provider = strtolower((string) ($p->provider ?? $p->method ?? ''));
                                $badge = in_array($st, ['paid', 'success', 'completed', 'complete'], true)
                                    ? 'bg-green-50 text-green-700'
                                    : ((str_contains($st, 'fail') || str_contains($st, 'cancel'))
                                        ? 'bg-red-50 text-red-700'
                                        : (in_array($st, ['pending', 'past_due'], true)
                                            ? 'bg-yellow-50 text-yellow-700'
                                            : 'bg-gray-100 text-gray-700'));
                                $canDownload = in_array($st, ['paid', 'success', 'completed', 'complete'], true);
                                $canPay = in_array($st, ['pending', 'past_due'], true)
                                    && in_array($orderStatus, ['pending', 'past_due'], true)
                                    && $provider === 'hitpay';
                                $displayDate = $p->paid_at ?: $p->created_at;
                                $formattedDate = $displayDate
                                    ? \Carbon\Carbon::parse($displayDate, 'UTC')->timezone($studioTimezone)->format('Y-m-d H:i')
                                    : '—';
                            @endphp

                            <tr>
                                <td class="px-4 py-4 text-sm whitespace-nowrap">{{ $formattedDate }}</td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold">{{ $p->reference ?? ('PAY-'.$p->id) }}</div>

                                    @if($p->billing_reason === 'subscription_cycle')
                                        <div class="mt-1 text-xs font-semibold text-amber-600">Subscription renewal</div>
                                    @elseif($p->billing_reason === 'subscription_initial')
                                        <div class="mt-1 text-xs font-semibold text-amber-600">Subscription start</div>
                                    @endif

                                    @if($p->provider_reference)
                                        <div class="text-xs text-gray-500">{{ $p->provider_reference }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm font-semibold whitespace-nowrap">{{ strtoupper($p->currency ?? 'MYR') }} {{ number_format((float) ($p->amount ?? 0), 2) }}</td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap">{{ strtoupper($p->provider ?? $p->method ?? '—') }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ strtoupper($p->status ?? '—') }}</span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($canPay)
                                            <form method="POST" action="{{ route('shop.checkout.payments.retry', $p->id) }}">
                                                @csrf
                                                <button class="mg-btn-primary"><i class="bx bx-credit-card"></i> Pay Now</button>
                                            </form>
                                        @endif

                                        @if($canDownload)
                                            <a href="{{ route('student.payments.receipt.download', $p->id) }}" class="mg-btn-secondary"><i class="bx bx-download"></i> Receipt</a>
                                        @elseif(!$canPay)
                                            <span class="text-xs text-gray-400">Unavailable</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No payments found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $payments->links() }}</div>
        </div>
    </div>
</x-app-layout>
