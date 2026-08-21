<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div>
                <h1 class="mg-title">My Subscriptions</h1>
                <p class="mg-subtitle mt-1">View your subscription classes, generated sessions, attendance eligibility, and payment status.</p>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">{{ session('error') }}</div>
            @endif

            @if($subscriptions->isNotEmpty())
                <div class="mg-card p-4">
                    <label for="subscription-search" class="mb-2 block text-xs font-extrabold uppercase tracking-wide text-[#6b5f52] dark:text-gray-400">Search subscriptions</label>
                    <div class="relative">
                        <i class="bx bx-search absolute left-3 top-1/2 -translate-y-1/2 text-lg text-[#9a8c7d]"></i>
                        <input
                            id="subscription-search"
                            type="search"
                            class="mg-input pl-10"
                            placeholder="Search class, teacher, status, provider, venue, date or payment reference..."
                            autocomplete="off"
                        >
                    </div>
                    <p class="mt-2 text-xs text-[#6b5f52] dark:text-gray-400">Subscriptions stay collapsed until you open them, making this page easier to scan on phones and smaller screens.</p>
                </div>
            @endif

            <div id="subscription-list" class="space-y-3">
                @forelse($subscriptions as $subscription)
                    @php
                        $subscriptionStatus = strtolower((string) $subscription->status);
                        $canAttendSubscription = in_array($subscriptionStatus, ['active', 'trialing'], true) && ! $subscription->billing_interval_mismatch;
                        $className = $subscription->classModel?->name ?? 'Subscription class';
                        $teacherName = $subscription->classModel?->teacher?->name ?? 'Teacher not assigned';
                        $provider = strtoupper($subscription->provider ?? '—');
                        $sessions = $subscription->classModel?->sessions ?? collect();
                        $sessionSearchText = $sessions->map(function ($session) {
                            $payment = $session->subscription_payment;
                            return implode(' ', array_filter([
                                $session->start_time?->format('d M Y Y-m-d'),
                                $session->start_time?->format('h:i A'),
                                $session->end_time?->format('h:i A'),
                                $session->venue_name,
                                $session->subscription_display_status,
                                $payment?->status,
                                $payment?->reference,
                            ]));
                        })->implode(' ');
                        $searchText = strtolower(implode(' ', [
                            $className,
                            $teacherName,
                            $provider,
                            $subscriptionStatus,
                            $subscription->billing_interval ?? 'recurring',
                            $subscription->currency ?? 'MYR',
                            number_format((float) $subscription->amount, 2),
                            $sessionSearchText,
                        ]));
                    @endphp

                    <details class="mg-card group overflow-hidden" data-subscription-card data-search="{{ $searchText }}">
                        <summary class="cursor-pointer list-none p-4 sm:p-5 [&::-webkit-details-marker]:hidden">
                            <div class="flex items-start gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h2 class="min-w-0 truncate text-base font-extrabold text-[#171717] dark:text-white sm:text-lg">{{ $className }}</h2>
                                        @if($subscription->can_retry_initial_payment)
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-[10px] font-extrabold text-amber-800 dark:bg-amber-900/30 dark:text-amber-200">PAYMENT REQUIRED</span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-xs font-semibold text-[#6b5f52] dark:text-gray-400 sm:text-sm">{{ $teacherName }}</p>
                                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="mg-badge">{{ $provider }}</span>
                                        <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-extrabold {{ $canAttendSubscription ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200' }}">{{ strtoupper($subscriptionStatus) }}</span>
                                        <span class="font-bold text-[#6b5f52] dark:text-gray-300">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</span>
                                        <span class="text-[#9a8c7d]">{{ ucfirst($subscription->billing_interval ?? 'recurring') }} billing</span>
                                    </div>
                                </div>

                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="hidden text-xs font-bold text-[#9a8c7d] sm:inline">{{ $sessions->count() }} session{{ $sessions->count() === 1 ? '' : 's' }}</span>
                                    <i class="bx bx-chevron-down text-2xl text-[#9a8c7d] transition-transform duration-150 group-open:rotate-180"></i>
                                </div>
                            </div>
                        </summary>

                        <div class="border-t border-[#eadfce] dark:border-gray-800">
                            <div class="p-4 sm:p-5">
                                @if($subscription->can_retry_initial_payment)
                                    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="font-extrabold">Subscription payment is incomplete</div>
                                            <p class="mt-1 text-xs">Your subscription is reserved, but the first payment has not completed. Retry using the studio's currently active payment gateway.</p>
                                        </div>
                                        <form method="POST" action="{{ route('student.subscriptions.retry-payment', $subscription) }}" class="shrink-0">
                                            @csrf
                                            <button type="submit" class="mg-btn-primary w-full sm:w-auto">Retry payment</button>
                                        </form>
                                    </div>
                                @endif

                                @if($subscription->billing_interval_mismatch)
                                    <div class="mb-4 rounded-2xl border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">
                                        <div class="font-extrabold">Billing configuration mismatch</div>
                                        <p class="mt-1">This class expects {{ strtoupper($subscription->class_billing_interval) }} billing, but the active Stripe subscription is {{ strtoupper($subscription->provider_billing_interval) }}. Stripe will only charge using its existing {{ $subscription->provider_billing_interval }} interval until the subscription is replaced or corrected by the studio.</p>
                                    </div>
                                @endif

                                @if($subscription->stripe_sync_error)
                                    <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">{{ $subscription->stripe_sync_error }}</div>
                                @endif

                                <div class="grid gap-3 text-sm sm:grid-cols-3">
                                    <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                        <div class="text-xs font-bold uppercase text-[#9a8c7d]">Amount</div>
                                        <div class="mt-1 font-extrabold">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</div>
                                    </div>
                                    <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                        <div class="text-xs font-bold uppercase text-[#9a8c7d]">Next billing</div>
                                        <div class="mt-1 font-extrabold">{{ $subscription->next_billing_at?->format('d M Y, h:i A') ?? '—' }}</div>
                                    </div>
                                    <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                        <div class="text-xs font-bold uppercase text-[#9a8c7d]">Attendance</div>
                                        <div class="mt-1 font-extrabold {{ $canAttendSubscription ? 'text-green-700' : 'text-red-700' }}">{{ $canAttendSubscription ? 'Eligible when session is paid' : 'Blocked until billing is valid and paid' }}</div>
                                    </div>
                                </div>
                            </div>

                            <details class="border-t border-[#eadfce] dark:border-gray-800" open>
                                <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3 font-extrabold text-[#31261d] dark:text-gray-100 sm:px-5 [&::-webkit-details-marker]:hidden">
                                    <span class="flex items-center gap-2"><i class="bx bx-calendar"></i> Session schedule & payment status</span>
                                    <i class="bx bx-chevron-down text-xl"></i>
                                </summary>

                                <div class="space-y-3 px-4 pb-4 md:hidden">
                                    @forelse($sessions as $session)
                                        @php
                                            $status = $session->subscription_display_status;
                                            $payment = $session->subscription_payment;
                                            $badge = match($status) {
                                                'paid' => 'bg-green-100 text-green-700',
                                                'assigned' => 'bg-blue-100 text-blue-700',
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                'payment_failed', 'unpaid' => 'bg-red-100 text-red-700',
                                                'cancelled' => 'bg-gray-200 text-gray-700',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <article class="rounded-2xl border border-[#eadfce] p-4 dark:border-gray-800">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="font-extrabold">{{ $session->start_time?->format('d M Y') }}</div>
                                                    <div class="text-sm text-[#6b5f52]">{{ $session->start_time?->format('h:i A') }} – {{ $session->end_time?->format('h:i A') }}</div>
                                                    @if($session->venue_name)
                                                        <div class="mt-1 text-xs text-[#6b5f52]"><i class="bx bx-map"></i> {{ $session->venue_name }}</div>
                                                    @endif
                                                </div>
                                                <span class="rounded-full px-2.5 py-1 text-[11px] font-extrabold {{ $badge }}">{{ strtoupper(str_replace('_', ' ', $status)) }}</span>
                                            </div>
                                            <div class="mt-3 text-xs text-[#6b5f52]">
                                                Payment: {{ strtoupper($payment?->status ?? 'Not billed') }}
                                                @if($payment?->reference) · {{ $payment->reference }} @endif
                                            </div>
                                            @if($session->status === 'cancelled' && $session->change_reason)
                                                <div class="mt-2 rounded-xl bg-gray-100 p-2 text-xs text-gray-700">Cancelled: {{ $session->change_reason }}</div>
                                            @endif
                                        </article>
                                    @empty
                                        <div class="py-8 text-center text-sm text-[#6b5f52]">No generated sessions found.</div>
                                    @endforelse
                                </div>

                                <div class="hidden overflow-x-auto md:block">
                                    <table class="min-w-full">
                                        <thead class="bg-[#fffaf3] dark:bg-gray-800">
                                            <tr>
                                                <th class="mg-th">Session</th>
                                                <th class="mg-th">Venue</th>
                                                <th class="mg-th">Payment</th>
                                                <th class="mg-th">Reference</th>
                                                <th class="mg-th">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-[#eadfce] dark:divide-gray-800">
                                            @forelse($sessions as $session)
                                                @php($payment = $session->subscription_payment)
                                                <tr>
                                                    <td class="mg-td"><div class="font-bold">{{ $session->start_time?->format('d M Y') }}</div><div class="text-xs text-[#6b5f52]">{{ $session->start_time?->format('h:i A') }} – {{ $session->end_time?->format('h:i A') }}</div></td>
                                                    <td class="mg-td">{{ $session->venue_name ?: '—' }}</td>
                                                    <td class="mg-td">{{ strtoupper($payment?->status ?? 'Not billed') }}</td>
                                                    <td class="mg-td">{{ $payment?->reference ?: '—' }}</td>
                                                    <td class="mg-td"><span class="mg-badge">{{ strtoupper(str_replace('_', ' ', $session->subscription_display_status)) }}</span></td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="5" class="px-4 py-10 text-center text-sm text-[#6b5f52]">No generated sessions found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>
                    </details>
                @empty
                    <div class="mg-card p-10 text-center text-sm text-[#6b5f52]">You do not have any subscription classes yet.</div>
                @endforelse
            </div>

            <div id="subscription-no-results" class="mg-card hidden p-10 text-center text-sm text-[#6b5f52]">
                <i class="bx bx-search-alt mb-2 block text-3xl"></i>
                No subscriptions match your search.
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const search = document.getElementById('subscription-search');
                const cards = Array.from(document.querySelectorAll('[data-subscription-card]'));
                const empty = document.getElementById('subscription-no-results');

                if (!search || cards.length === 0) return;

                const filter = () => {
                    const query = search.value.trim().toLowerCase();
                    let visible = 0;

                    cards.forEach((card) => {
                        const haystack = (card.dataset.search || '').toLowerCase();
                        const match = query === '' || haystack.includes(query);
                        card.classList.toggle('hidden', !match);
                        if (match) visible++;
                    });

                    if (empty) empty.classList.toggle('hidden', visible !== 0);
                };

                search.addEventListener('input', filter);
            });
        </script>
    @endpush
</x-app-layout>
