<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div>
                <h1 class="mg-title">My Subscriptions</h1>
                <p class="mg-subtitle mt-1">View your subscription classes, generated sessions, attendance eligibility, and payment status.</p>
            </div>

            @forelse($subscriptions as $subscription)
                @php
                    $subscriptionStatus = strtolower((string) $subscription->status);
                    $canAttendSubscription = in_array($subscriptionStatus, ['active', 'trialing'], true) && ! $subscription->billing_interval_mismatch;
                @endphp

                <section class="mg-card overflow-hidden">
                    <div class="border-b border-[#eadfce] p-5 dark:border-gray-800">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">{{ $subscription->classModel?->name ?? 'Subscription class' }}</h2>
                                <p class="mt-1 text-sm text-[#6b5f52] dark:text-gray-400">
                                    {{ $subscription->classModel?->teacher?->name ?? 'Teacher not assigned' }}
                                    · {{ ucfirst($subscription->billing_interval ?? 'recurring') }} provider billing
                                </p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="mg-badge">{{ strtoupper($subscription->provider ?? '—') }}</span>
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $canAttendSubscription ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-200' }}">
                                    {{ strtoupper($subscriptionStatus) }}
                                </span>
                            </div>
                        </div>

                        @if($subscription->billing_interval_mismatch)
                            <div class="mt-4 rounded-2xl border border-red-300 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">
                                <div class="font-extrabold">Billing configuration mismatch</div>
                                <p class="mt-1">
                                    This class expects {{ strtoupper($subscription->class_billing_interval) }} billing, but the active Stripe subscription is {{ strtoupper($subscription->provider_billing_interval) }}. Stripe will only charge using its existing {{ $subscription->provider_billing_interval }} interval until the subscription is replaced or corrected by the studio.
                                </p>
                            </div>
                        @endif

                        @if($subscription->stripe_sync_error)
                            <div class="mt-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-800">{{ $subscription->stripe_sync_error }}</div>
                        @endif

                        <div class="mt-4 grid gap-3 text-sm sm:grid-cols-3">
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <div class="text-xs font-bold uppercase text-[#9a8c7d]">Amount</div>
                                <div class="mt-1 font-extrabold">{{ strtoupper($subscription->currency ?? 'MYR') }} {{ number_format((float) $subscription->amount, 2) }}</div>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <div class="text-xs font-bold uppercase text-[#9a8c7d]">Next Stripe billing</div>
                                <div class="mt-1 font-extrabold">{{ $subscription->next_billing_at?->format('d M Y, h:i A') ?? '—' }}</div>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <div class="text-xs font-bold uppercase text-[#9a8c7d]">Attendance</div>
                                <div class="mt-1 font-extrabold {{ $canAttendSubscription ? 'text-green-700' : 'text-red-700' }}">{{ $canAttendSubscription ? 'Eligible when session is paid' : 'Blocked until billing is valid and paid' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 p-4 md:hidden">
                        @forelse($subscription->classModel?->sessions ?? collect() as $session)
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
                                @forelse($subscription->classModel?->sessions ?? collect() as $session)
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
                </section>
            @empty
                <div class="mg-card p-10 text-center text-sm text-[#6b5f52]">You do not have any subscription classes yet.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
