<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Owner Control</p>
            <div class="mt-3 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <h1 class="text-2xl font-extrabold text-[#171717] dark:text-white">Superadmin Dashboard</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">
                        Manage the full platform, studios and SaaS subscription revenue. This area is not attached to any single studio.
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('superadmin.studios.index') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-0.5 dark:bg-white dark:text-gray-950">Manage Studios</a>
                    <a href="{{ route('superadmin.subscription-plans.index') }}" class="rounded-2xl bg-[#d97706] px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:-translate-y-0.5">Subscription Pricing</a>
                </div>
            </div>
        </div>

        @php
            $stats = [
                ['label' => 'Total Studios', 'value' => number_format($totalStudios), 'icon' => 'bx-buildings'],
                ['label' => 'Subscribed Studios', 'value' => number_format($subscribedStudios), 'icon' => 'bx-badge-check'],
                ['label' => 'Active Plans', 'value' => number_format($activePlatformPlans), 'icon' => 'bx-purchase-tag-alt'],
                ['label' => 'Platform Revenue', 'value' => 'RM '.number_format((float) $paidPlatformRevenue, 2), 'icon' => 'bx-wallet'],
                ['label' => 'This Month SaaS Revenue', 'value' => 'RM '.number_format((float) $monthlyPlatformRevenue, 2), 'icon' => 'bx-line-chart'],
                ['label' => 'Normal Studio Admins', 'value' => number_format($totalAdmins), 'icon' => 'bx-user-check'],
            ];
        @endphp

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
            @foreach($stats as $stat)
                <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">{{ $stat['label'] }}</p>
                            <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ $stat['value'] }}</div>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#fff3df] text-[#d97706] dark:bg-amber-950/30 dark:text-amber-300">
                            <i class="bx {{ $stat['icon'] }} text-2xl"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-4 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Platform Roles</h2>
                <p class="mt-1 text-sm font-medium text-[#9a8c7d] dark:text-gray-500">Superadmin is the owner-level role. Normal admins stay scoped to their own studio.</p>
                <div class="mt-5 space-y-3">
                    @foreach(['Superadmins' => $totalSuperadmins, 'Studio Admins' => $totalAdmins, 'Teachers' => $totalTeachers, 'Students' => $totalStudents] as $label => $count)
                        <div class="flex items-center justify-between rounded-2xl bg-[#f7f2ea] px-4 py-3 dark:bg-gray-950">
                            <span class="text-sm font-bold text-[#6b5f52] dark:text-gray-300">{{ $label }}</span>
                            <span class="text-sm font-extrabold text-[#171717] dark:text-white">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="xl:col-span-8 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="flex items-center justify-between gap-4">
                    <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Recent Studios</h2>
                    <a href="{{ route('superadmin.studios.index') }}" class="text-sm font-extrabold text-[#d97706]">View all</a>
                </div>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                        <thead>
                            <tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">
                                <th class="py-3 pr-4">Studio</th>
                                <th class="py-3 pr-4">Owner</th>
                                <th class="py-3 pr-4">Plan</th>
                                <th class="py-3 pr-4">Users</th>
                                <th class="py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                            @forelse($recentStudios as $studio)
                                <tr class="text-[#31261d] dark:text-gray-200">
                                    <td class="py-4 pr-4 font-extrabold">{{ $studio->name ?? $studio->slug ?? 'Untitled Studio' }}</td>
                                    <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $studio->owner?->name ?? 'Not assigned' }}</td>
                                    <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $studio->platformSubscriptionPlan?->name ?? $studio->plan_name ?? 'No plan' }}</td>
                                    <td class="py-4 pr-4">{{ number_format($studio->users_count ?? 0) }}</td>
                                    <td class="py-4"><span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $studio->status ?? 'unknown' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No studios found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Recent Platform Subscription Payments</h2>
            <p class="mt-1 text-sm font-medium text-[#9a8c7d] dark:text-gray-500">Only SaaS subscription payments from studios appear here. Studio/student payments are intentionally excluded.</p>
            <div class="mt-5 overflow-x-auto">
                <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">
                            <th class="py-3 pr-4">Studio</th>
                            <th class="py-3 pr-4">Plan</th>
                            <th class="py-3 pr-4">Amount</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3">Paid At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                        @forelse($recentPlatformPayments as $payment)
                            <tr class="text-[#31261d] dark:text-gray-200">
                                <td class="py-4 pr-4 font-extrabold">{{ $payment->studio?->name ?? 'Unknown studio' }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $payment->plan?->name ?? 'Manual / legacy' }}</td>
                                <td class="py-4 pr-4 font-extrabold">{{ $payment->currency ?? 'MYR' }} {{ number_format((float) $payment->amount, 2) }}</td>
                                <td class="py-4 pr-4"><span class="rounded-full bg-[#f7f2ea] px-3 py-1 text-xs font-extrabold uppercase text-[#6b5f52] dark:bg-gray-950 dark:text-gray-300">{{ $payment->status }}</span></td>
                                <td class="py-4">{{ optional($payment->paid_at ?? $payment->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No platform subscription payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
