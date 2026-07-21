<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <a href="{{ route('admin.classes') }}" class="text-xs font-bold text-amber-700 hover:underline">← Back to classes</a>
                    <h1 class="mg-title mt-2">{{ $class->name }}</h1>
                    <p class="mg-subtitle mt-1">{{ $class->teacher?->name ?? 'No teacher' }} · {{ ucfirst($class->type) }} · RM {{ number_format($class->price, 2) }}{{ $class->billing_interval ? ' / '.$class->billing_interval : '' }}</p>
                </div>
                <span class="mg-badge">{{ $subscriptions->whereIn('status', ['active','trialing','past_due'])->count() }} attending students</span>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700">{{ session('success') }}</div>
            @endif

            @if($class->type === 'subscription')
                <div class="rounded-2xl border border-amber-300 bg-amber-50 p-4 text-sm text-amber-950 dark:border-amber-800 dark:bg-amber-950/20 dark:text-amber-100">
                    <strong>Production rule:</strong> one successful billing cycle assigns one scheduled session. Price, class type and billing interval are locked after the first active subscription.
                </div>
            @endif

            <section class="mg-card overflow-hidden">
                <div class="border-b border-[#eadfce] p-4 dark:border-gray-800">
                    <h2 class="font-extrabold">Attending students and payment status</h2>
                    <p class="mt-1 text-xs text-gray-500">Students with failed or unpaid renewals are not eligible to attend until payment succeeds.</p>
                </div>
                <div class="divide-y divide-[#eadfce] dark:divide-gray-800">
                    @forelse($subscriptions as $subscription)
                        @php
                            $paymentStatus = strtolower((string) $subscription->latest_payment_status);
                            $paymentClass = in_array($paymentStatus, ['paid','success','completed','complete'])
                                ? 'bg-green-100 text-green-800'
                                : (in_array($paymentStatus, ['failed','payment_failed','past_due']) ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800');
                        @endphp
                        <div class="grid gap-4 p-4 lg:grid-cols-[minmax(0,1fr)_auto_auto_minmax(260px,360px)] lg:items-center">
                            <div>
                                <p class="font-extrabold">{{ $subscription->user?->name ?? 'Student' }}</p>
                                <p class="text-xs text-gray-500">{{ $subscription->user?->email }}</p>
                                <p class="mt-1 text-xs">Subscription: <strong>{{ strtoupper($subscription->status) }}</strong></p>
                            </div>
                            <div><span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $paymentClass }}">{{ strtoupper($subscription->latest_payment_status) }}</span></div>
                            <div>
                                @if($subscription->can_attend)
                                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-xs font-extrabold text-green-800">CAN ATTEND</span>
                                @else
                                    <span class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-extrabold text-red-800">BLOCKED</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('admin.subscription-classes.students.notify', [$class->id, $subscription->id]) }}" class="grid gap-2">
                                @csrf
                                <input name="title" required maxlength="150" value="Payment status for {{ $class->name }}" class="mg-input" />
                                <textarea name="message" required maxlength="2000" rows="2" class="mg-input">Your current payment status is {{ strtoupper($subscription->latest_payment_status) }}. Please review your payment page before attending the next class.</textarea>
                                <button class="mg-btn-secondary"><i class="bx bx-bell"></i> Send Notification</button>
                            </form>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500">No subscription students yet.</div>
                    @endforelse
                </div>
            </section>

            <section class="mg-card overflow-hidden">
                <div class="border-b border-[#eadfce] p-4 dark:border-gray-800">
                    <h2 class="font-extrabold">Class sessions</h2>
                    <p class="mt-1 text-xs text-gray-500">Rescheduling and cancellation require a reason when active subscriptions exist.</p>
                </div>
                <div class="divide-y divide-[#eadfce] dark:divide-gray-800">
                    @forelse($sessions as $session)
                        <div class="grid gap-4 p-4 md:grid-cols-[minmax(0,1fr)_auto] md:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-extrabold">{{ $session->start_time->format('d M Y, h:i A') }} – {{ $session->end_time->format('h:i A') }}</p>
                                    <span class="mg-badge">{{ strtoupper($session->status ?? 'scheduled') }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ $session->venue_name ?: 'No venue' }} · Capacity {{ $session->capacity ?: ($class->capacity ?: '—') }}</p>
                                @if($session->change_reason)
                                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs text-amber-950">
                                        <strong>{{ ucfirst($session->change_type) }} reason:</strong> {{ $session->change_reason }}
                                        @if($session->changedBy)
                                            <div class="mt-1 text-amber-700">By {{ $session->changedBy->name }} {{ $session->changed_at?->format('d M Y, h:i A') }}</div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="grid gap-2 sm:flex">
                                <a href="{{ route('admin.classes.edit', $session->id) }}" class="mg-btn-secondary"><i class="bx bx-edit"></i> Edit</a>
                                <a href="{{ route('admin.classes.attendance', $session->id) }}" class="mg-btn-secondary"><i class="bx bx-check-square"></i> Attendance</a>
                                @if(($session->status ?? 'scheduled') !== 'cancelled')
                                    <form method="POST" action="{{ route('admin.classes.destroy', $session->id) }}" class="grid gap-2 rounded-xl border border-red-200 bg-red-50 p-3" onsubmit="return confirm('This is a dangerous action. Cancel this session?')">
                                        @csrf @method('DELETE')
                                        <textarea name="change_reason" rows="2" minlength="10" required placeholder="Reason for cancelling this session" class="mg-input"></textarea>
                                        <label class="flex items-center gap-2 text-xs font-bold text-red-800"><input type="checkbox" name="confirm_danger" value="1" required> I understand subscribers may skip to the next session.</label>
                                        <button class="mg-btn-danger"><i class="bx bx-x-circle"></i> Cancel Session</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-sm text-gray-500">No sessions found.</div>
                    @endforelse
                </div>
                <div class="border-t border-[#eadfce] p-4">{{ $sessions->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>