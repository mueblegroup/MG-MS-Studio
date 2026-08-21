<x-app-layout>
    @php
        $studioTimezone = app(\App\Services\StudioSettingsService::class)
            ->get('timezone', config('app.timezone', 'UTC'));
    @endphp

    <div class="min-h-screen bg-gray-50/60 p-4 dark:bg-gray-900 sm:p-6 lg:p-8" id="student-payments-page">
        <div class="mb-5 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">My Payments</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Subscriptions, upcoming charges, payment records, and receipts in one place.</p>
            </div>

            <label class="block w-full lg:max-w-md">
                <span class="sr-only">Search payments and subscriptions</span>
                <div class="relative">
                    <i class="bx bx-search pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-lg text-gray-400"></i>
                    <input
                        id="payment-search"
                        type="search"
                        autocomplete="off"
                        class="mg-input w-full pl-10 pr-10"
                        placeholder="Search class, reference, status, provider..."
                    >
                    <button id="payment-search-clear" type="button" class="absolute right-2 top-1/2 hidden -translate-y-1/2 rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-gray-200" aria-label="Clear search">
                        <i class="bx bx-x text-lg"></i>
                    </button>
                </div>
            </label>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
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

        <div id="payment-search-empty" class="mb-5 hidden rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center dark:border-gray-700 dark:bg-gray-800">
            <i class="bx bx-search-alt mb-2 text-3xl text-gray-400"></i>
            <p class="text-sm font-bold text-gray-700 dark:text-gray-200">No matching payments or subscriptions</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Try a class name, reference, provider, status, or amount.</p>
        </div>

        @if($activeSubscriptions->isNotEmpty())
            <section class="payment-search-section mb-5 overflow-hidden rounded-2xl border border-blue-200 bg-blue-50 dark:border-blue-900/50 dark:bg-blue-950/20">
                <div class="border-b border-blue-200 px-4 py-4 dark:border-blue-900/50 sm:px-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="flex min-w-0 items-center gap-2 text-base font-extrabold text-blue-950 dark:text-blue-100">
                            <i class="bx bx-calendar-check shrink-0 text-xl"></i>
                            <span>My active subscriptions</span>
                        </h2>
                        <span class="shrink-0 rounded-full bg-blue-200 px-2.5 py-1 text-xs font-extrabold text-blue-900 dark:bg-blue-900 dark:text-blue-100">{{ $activeSubscriptions->count() }}</span>
                    </div>
                    <p class="mt-1 text-xs font-semibold text-blue-800 dark:text-blue-300">Tap a class to view billing details or cancellation options.</p>
                </div>

                <div class="space-y-2 p-2 sm:p-3">
                    @foreach($activeSubscriptions as $subscription)
                        @php
                            $scheduledEndDate = $subscription->meta['scheduled_class_end_date'] ?? $subscription->classModel?->until_date;
                            $endAt = $scheduledEndDate
                                ? \Carbon\Carbon::parse($scheduledEndDate, 'UTC')->timezone($studioTimezone)->endOfDay()
                                : null;
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                            $isHitPay = strtolower((string) $subscription->provider) === 'hitpay';
                            $nextBillingAt = $subscription->next_billing_at?->copy()->timezone($studioTimezone);
                            $hitPayBillingDate = $subscription->meta['hitpay_next_charge_date_sgt']
                                ?? $subscription->meta['hitpay_start_date_sgt']
                                ?? ($isHitPay && $nextBillingAt ? $nextBillingAt->copy()->timezone('Asia/Singapore')->toDateString() : null);
                            $hitPayDate = $hitPayBillingDate ? \Carbon\Carbon::parse($hitPayBillingDate, 'Asia/Singapore') : null;
                            $hitPayToday = \Carbon\Carbon::now('Asia/Singapore')->toDateString();
                            $className = $subscription->classModel?->name ?? 'Subscription class';
                            $searchText = implode(' ', [
                                $className,
                                $subscription->status,
                                $subscription->billing_interval,
                                $subscription->provider,
                                $subscription->currency,
                                number_format((float) $subscription->amount, 2, '.', ''),
                                $endAt?->format('d M Y'),
                                $nextBillingAt?->format('d M Y H:i'),
                                $hitPayDate?->format('d M Y'),
                            ]);
                        @endphp

                        <details class="payment-search-item group overflow-hidden rounded-xl border border-blue-200 bg-white shadow-sm dark:border-blue-900/60 dark:bg-gray-900" data-search="{{ strtolower($searchText) }}">
                            <summary class="flex cursor-pointer list-none items-center gap-3 px-3 py-3 marker:hidden sm:px-4 [&::-webkit-details-marker]:hidden">
                                <div class="min-w-0 flex-1">
                                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                                        <p class="min-w-0 truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $className }}</p>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-extrabold uppercase {{ in_array(strtolower((string) $subscription->status), ['active', 'trialing'], true) ? 'bg-green-100 text-green-700 dark:bg-green-950/40 dark:text-green-300' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' }}">{{ $subscription->status }}</span>
                                    </div>
                                    <div class="mt-1 flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                        <span>{{ ucfirst($subscription->billing_interval ?? 'recurring') }} billing</span>
                                        <span aria-hidden="true">·</span>
                                        <span>{{ strtoupper($subscription->provider ?? '—') }}</span>
                                        <span aria-hidden="true">·</span>
                                        <span class="font-extrabold text-gray-800 dark:text-gray-200">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</span>
                                    </div>
                                </div>
                                <i class="bx bx-chevron-down shrink-0 text-2xl text-blue-700 transition-transform duration-150 group-open:rotate-180 dark:text-blue-300"></i>
                            </summary>

                            <div class="border-t border-blue-100 px-3 py-4 dark:border-blue-900/40 sm:px-4">
                                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                    <div class="rounded-xl bg-blue-50 p-3 dark:bg-blue-950/30">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wide text-blue-600 dark:text-blue-300">Next billing</p>
                                        @if($isStripe && $nextBillingAt)
                                            <p class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">{{ $nextBillingAt->format('d M Y, h:i A') }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Stripe renewal timestamp</p>
                                        @elseif($isHitPay && $hitPayDate)
                                            <p class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">{{ $hitPayDate->format('d M Y') }}</p>
                                            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">HitPay billing date{{ $hitPayBillingDate === $hitPayToday ? ' · scheduled today' : '' }}</p>
                                        @elseif($nextBillingAt)
                                            <p class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">{{ $nextBillingAt->format('d M Y, h:i A') }}</p>
                                        @else
                                            <p class="mt-1 text-sm font-extrabold text-gray-500">No further billing scheduled</p>
                                        @endif
                                    </div>

                                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-500 dark:text-gray-400">Subscription end</p>
                                        <p class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">{{ $endAt ? $endAt->format('d M Y') : 'Until cancelled' }}</p>
                                    </div>

                                    <div class="rounded-xl bg-gray-50 p-3 dark:bg-gray-800">
                                        <p class="text-[10px] font-extrabold uppercase tracking-wide text-gray-500 dark:text-gray-400">Provider</p>
                                        <p class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">{{ strtoupper($subscription->provider ?? '—') }}</p>
                                        @if($isHitPay)
                                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">HitPay controls processing time on the billing date.</p>
                                        @endif
                                    </div>
                                </div>

                                <details class="group/cancel mt-3 overflow-hidden rounded-xl border border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-950/20">
                                    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-3 py-2.5 text-xs font-extrabold text-red-800 marker:hidden dark:text-red-300 [&::-webkit-details-marker]:hidden">
                                        <span><i class="bx bx-x-circle mr-1"></i> Cancel subscription</span>
                                        <i class="bx bx-chevron-down text-lg transition-transform group-open/cancel:rotate-180"></i>
                                    </summary>
                                    <form method="POST" action="{{ route('student.subscriptions.cancel', $subscription->id) }}" class="grid gap-2 border-t border-red-200 p-3 dark:border-red-900/50" onsubmit="return confirm('Cancel this subscription and remove all upcoming class access?')">
                                        @csrf
                                        <label class="text-xs font-extrabold text-red-900 dark:text-red-200">Reason for cancellation</label>
                                        <textarea name="cancellation_reason" required minlength="5" maxlength="1000" rows="2" class="mg-input" placeholder="Tell the studio why you are leaving"></textarea>
                                        <label class="flex items-start gap-2 text-xs font-bold text-red-900 dark:text-red-200">
                                            <input type="checkbox" name="confirm_cancel" value="1" required class="mt-0.5">
                                            <span>I understand future billing and upcoming class access will be cancelled.</span>
                                        </label>
                                        <button class="mg-btn-danger justify-self-start"><i class="bx bx-x-circle"></i> Confirm cancellation</button>
                                    </form>
                                </details>
                            </div>
                        </details>
                    @endforeach
                </div>
            </section>
        @endif

        @if($upcomingSubscriptions->isNotEmpty())
            <details class="payment-search-section group mb-5 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-950/20">
                <summary class="flex cursor-pointer list-none items-center gap-3 px-4 py-4 marker:hidden sm:px-5 [&::-webkit-details-marker]:hidden">
                    <div class="min-w-0 flex-1">
                        <h2 class="flex items-center gap-2 text-base font-extrabold text-amber-950 dark:text-amber-100"><i class="bx bx-time-five text-xl"></i> Due within the next 3 days</h2>
                        <p class="mt-1 text-xs font-semibold text-amber-800 dark:text-amber-300">{{ $upcomingSubscriptions->count() }} upcoming billing {{ $upcomingSubscriptions->count() === 1 ? 'item' : 'items' }}</p>
                    </div>
                    <i class="bx bx-chevron-down shrink-0 text-2xl text-amber-700 transition-transform duration-150 group-open:rotate-180 dark:text-amber-300"></i>
                </summary>

                <div class="space-y-2 border-t border-amber-200 p-2 dark:border-amber-900/50 sm:p-3">
                    @foreach($upcomingSubscriptions as $subscription)
                        @php
                            $dueAt = $subscription->next_billing_at?->copy()->timezone($studioTimezone);
                            $isStripe = strtolower((string) $subscription->provider) === 'stripe';
                            $isHitPay = strtolower((string) $subscription->provider) === 'hitpay';
                            $hitPayBillingDate = $subscription->meta['hitpay_next_charge_date_sgt']
                                ?? $subscription->meta['hitpay_start_date_sgt']
                                ?? ($isHitPay && $dueAt ? $dueAt->copy()->timezone('Asia/Singapore')->toDateString() : null);
                            $providerDueAt = $isHitPay && $hitPayBillingDate ? \Carbon\Carbon::parse($hitPayBillingDate, 'Asia/Singapore') : $dueAt;
                            $nowForDue = $isHitPay ? \Carbon\Carbon::now('Asia/Singapore') : now()->timezone($studioTimezone);
                            $dueLabel = $providerDueAt?->isToday()
                                ? 'Due today'
                                : ($providerDueAt?->isTomorrow()
                                    ? 'Due tomorrow'
                                    : 'Due in '.$nowForDue->copy()->startOfDay()->diffInDays($providerDueAt->copy()->startOfDay()).' days');
                            $upcomingClassName = $subscription->classModel?->name ?? 'Subscription class';
                        @endphp

                        <div class="payment-search-item rounded-xl border border-amber-200 bg-white p-3 dark:border-amber-900/50 dark:bg-gray-900" data-search="{{ strtolower($upcomingClassName.' '.$subscription->provider.' '.$subscription->billing_interval.' '.$subscription->currency.' '.$subscription->amount.' '.$dueLabel.' '.($providerDueAt?->format('d M Y') ?? '')) }}">
                            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-extrabold text-gray-950 dark:text-white">{{ $upcomingClassName }}</p>
                                    <p class="mt-0.5 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ strtoupper($subscription->provider ?? '—') }} · {{ ucfirst($subscription->billing_interval ?? 'subscription') }} billing</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 sm:justify-end">
                                    <span class="rounded-full bg-amber-200 px-2.5 py-1 text-xs font-extrabold text-amber-900">{{ $dueLabel }}</span>
                                    <span class="text-xs font-extrabold text-gray-800 dark:text-gray-200">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</span>
                                </div>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-gray-500 dark:text-gray-400">{{ $isHitPay ? ($providerDueAt?->format('d M Y') ?? '—') : ($providerDueAt?->format('d M Y, h:i A') ?? '—') }}</p>
                        </div>
                    @endforeach
                </div>
            </details>
        @endif

        <section class="payment-search-section overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:px-5">
                <div>
                    <h2 class="text-base font-extrabold text-gray-900 dark:text-white">Payment history</h2>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Tap a payment on mobile to see full details.</p>
                </div>
            </div>

            <div class="space-y-2 p-2 md:hidden">
                @forelse($payments as $p)
                    @php
                        $st = strtolower((string) ($p->status ?? ''));
                        $orderStatus = strtolower((string) ($p->order_status ?? ''));
                        $provider = strtolower((string) ($p->provider ?? $p->method ?? ''));
                        $badge = in_array($st, ['paid', 'success', 'completed', 'complete'], true)
                            ? 'bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-300'
                            : ((str_contains($st, 'fail') || str_contains($st, 'cancel'))
                                ? 'bg-red-50 text-red-700 dark:bg-red-950/40 dark:text-red-300'
                                : (in_array($st, ['pending', 'past_due'], true)
                                    ? 'bg-yellow-50 text-yellow-700 dark:bg-yellow-950/40 dark:text-yellow-300'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'));
                        $canDownload = in_array($st, ['paid', 'success', 'completed', 'complete'], true);
                        $canPay = in_array($st, ['pending', 'past_due'], true)
                            && in_array($orderStatus, ['pending', 'past_due'], true)
                            && $provider === 'hitpay';
                        $displayDate = $p->paid_at ?: $p->created_at;
                        $formattedDate = $displayDate ? \Carbon\Carbon::parse($displayDate, 'UTC')->timezone($studioTimezone)->format('d M Y, h:i A') : '—';
                        $paymentReference = $p->reference ?? ('PAY-'.$p->id);
                        $billingLabel = $p->billing_reason === 'subscription_cycle' ? 'Subscription renewal' : ($p->billing_reason === 'subscription_initial' ? 'Subscription start' : 'Payment');
                        $paymentSearchText = implode(' ', [$formattedDate, $paymentReference, $p->provider_reference, $p->amount, $p->currency, $p->provider, $p->method, $p->status, $billingLabel]);
                    @endphp

                    <details class="payment-search-item group overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900" data-search="{{ strtolower($paymentSearchText) }}">
                        <summary class="flex cursor-pointer list-none items-center gap-3 px-3 py-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="min-w-0 flex-1">
                                <div class="flex min-w-0 items-center gap-2">
                                    <p class="truncate text-sm font-extrabold text-gray-900 dark:text-white">{{ $paymentReference }}</p>
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-extrabold {{ $badge }}">{{ strtoupper($p->status ?? '—') }}</span>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ $formattedDate }}</span>
                                    <span aria-hidden="true">·</span>
                                    <span class="font-extrabold text-gray-800 dark:text-gray-200">{{ strtoupper($p->currency ?? 'MYR') }} {{ number_format((float) ($p->amount ?? 0), 2) }}</span>
                                </div>
                            </div>
                            <i class="bx bx-chevron-down shrink-0 text-2xl text-gray-400 transition-transform duration-150 group-open:rotate-180"></i>
                        </summary>

                        <div class="grid gap-3 border-t border-gray-100 px-3 py-3 text-xs dark:border-gray-700">
                            <dl class="grid grid-cols-[110px_minmax(0,1fr)] gap-x-3 gap-y-2">
                                <dt class="font-bold text-gray-500 dark:text-gray-400">Type</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $billingLabel }}</dd>
                                <dt class="font-bold text-gray-500 dark:text-gray-400">Method</dt>
                                <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ strtoupper($p->provider ?? $p->method ?? '—') }}</dd>
                                <dt class="font-bold text-gray-500 dark:text-gray-400">Provider ref.</dt>
                                <dd class="break-all font-semibold text-gray-900 dark:text-gray-100">{{ $p->provider_reference ?: '—' }}</dd>
                            </dl>

                            <div class="flex flex-wrap gap-2">
                                @if($canPay)
                                    <form method="POST" action="{{ route('shop.checkout.payments.retry', $p->id) }}">
                                        @csrf
                                        <button class="mg-btn-primary"><i class="bx bx-credit-card"></i> Pay Now</button>
                                    </form>
                                @endif
                                @if($canDownload)
                                    <a href="{{ route('student.payments.receipt.download', $p->id) }}" class="mg-btn-secondary"><i class="bx bx-download"></i> Receipt</a>
                                @elseif(!$canPay)
                                    <span class="self-center text-xs text-gray-400">No action available</span>
                                @endif
                            </div>
                        </div>
                    </details>
                @empty
                    <div class="px-4 py-10 text-center text-sm text-gray-500">No payments found.</div>
                @endforelse
            </div>

            <div class="hidden overflow-x-auto md:block">
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
                                        : (in_array($st, ['pending', 'past_due'], true) ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-700'));
                                $canDownload = in_array($st, ['paid', 'success', 'completed', 'complete'], true);
                                $canPay = in_array($st, ['pending', 'past_due'], true) && in_array($orderStatus, ['pending', 'past_due'], true) && $provider === 'hitpay';
                                $displayDate = $p->paid_at ?: $p->created_at;
                                $formattedDate = $displayDate ? \Carbon\Carbon::parse($displayDate, 'UTC')->timezone($studioTimezone)->format('Y-m-d H:i') : '—';
                                $billingLabel = $p->billing_reason === 'subscription_cycle' ? 'Subscription renewal' : ($p->billing_reason === 'subscription_initial' ? 'Subscription start' : 'Payment');
                                $rowSearchText = implode(' ', [$formattedDate, $p->reference, $p->provider_reference, $p->amount, $p->currency, $p->provider, $p->method, $p->status, $billingLabel]);
                            @endphp

                            <tr class="payment-search-item" data-search="{{ strtolower($rowSearchText) }}">
                                <td class="whitespace-nowrap px-4 py-4 text-sm">{{ $formattedDate }}</td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold">{{ $p->reference ?? ('PAY-'.$p->id) }}</div>
                                    <div class="mt-1 text-xs font-semibold text-amber-600">{{ $billingLabel }}</div>
                                    @if($p->provider_reference)<div class="max-w-xs truncate text-xs text-gray-500">{{ $p->provider_reference }}</div>@endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold">{{ strtoupper($p->currency ?? 'MYR') }} {{ number_format((float) ($p->amount ?? 0), 2) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm">{{ strtoupper($p->provider ?? $p->method ?? '—') }}</td>
                                <td class="px-4 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ strtoupper($p->status ?? '—') }}</span></td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($canPay)
                                            <form method="POST" action="{{ route('shop.checkout.payments.retry', $p->id) }}">@csrf<button class="mg-btn-primary"><i class="bx bx-credit-card"></i> Pay Now</button></form>
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
                            <tr><td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $payments->links() }}</div>
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const input = document.getElementById('payment-search');
                const clear = document.getElementById('payment-search-clear');
                const empty = document.getElementById('payment-search-empty');
                const page = document.getElementById('student-payments-page');

                if (!input || !page) return;

                const filter = () => {
                    const query = input.value.trim().toLowerCase();
                    const items = Array.from(page.querySelectorAll('.payment-search-item'));
                    let visibleItems = 0;

                    items.forEach((item) => {
                        const searchable = (item.dataset.search || item.textContent || '').toLowerCase();
                        const match = query === '' || searchable.includes(query);
                        item.classList.toggle('hidden', !match);
                        if (match) visibleItems++;
                    });

                    page.querySelectorAll('.payment-search-section').forEach((section) => {
                        const sectionItems = Array.from(section.querySelectorAll('.payment-search-item'));
                        if (!sectionItems.length) return;
                        const hasVisible = sectionItems.some((item) => !item.classList.contains('hidden'));
                        section.classList.toggle('hidden', query !== '' && !hasVisible);
                    });

                    clear?.classList.toggle('hidden', query === '');
                    empty?.classList.toggle('hidden', query === '' || visibleItems > 0);
                };

                input.addEventListener('input', filter);
                clear?.addEventListener('click', () => {
                    input.value = '';
                    filter();
                    input.focus();
                });
            });
        </script>
    @endpush
</x-app-layout>
