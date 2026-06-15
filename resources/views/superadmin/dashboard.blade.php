<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Control</p>
            <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Superadmin Dashboard</h1>
            <p class="mt-1 max-w-2xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Monitor studios, users, revenue and platform activity across the full Mueble LMS installation.</p>
        </div>

        @php
            $stats = [
                ['label' => 'Total Studios', 'value' => number_format($totalStudios), 'icon' => 'bx-buildings'],
                ['label' => 'Active Studios', 'value' => number_format($activeStudios), 'icon' => 'bx-check-shield'],
                ['label' => 'Trial Studios', 'value' => number_format($trialStudios), 'icon' => 'bx-time-five'],
                ['label' => 'Total Users', 'value' => number_format($totalUsers), 'icon' => 'bx-group'],
                ['label' => 'Paid Revenue', 'value' => 'RM '.number_format((float) $paidRevenue, 2), 'icon' => 'bx-wallet'],
                ['label' => 'Pending Orders', 'value' => number_format($pendingOrders), 'icon' => 'bx-receipt'],
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
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Role Distribution</h2>
                <div class="mt-5 space-y-3">
                    @foreach(['Superadmins' => $totalSuperadmins, 'Admins' => $totalAdmins, 'Teachers' => $totalTeachers, 'Students' => $totalStudents] as $label => $count)
                        <div class="flex items-center justify-between rounded-2xl bg-[#f7f2ea] px-4 py-3 dark:bg-gray-950">
                            <span class="text-sm font-bold text-[#6b5f52] dark:text-gray-300">{{ $label }}</span>
                            <span class="text-sm font-extrabold text-[#171717] dark:text-white">{{ number_format($count) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="xl:col-span-8 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Recent Studios</h2>
                <div class="mt-5 overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                        <thead><tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500"><th class="py-3 pr-4">Studio</th><th class="py-3 pr-4">Owner</th><th class="py-3 pr-4">Users</th><th class="py-3">Status</th></tr></thead>
                        <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                            @forelse($recentStudios as $studio)
                                <tr class="text-[#31261d] dark:text-gray-200"><td class="py-4 pr-4 font-extrabold">{{ $studio->name ?? $studio->slug ?? 'Untitled Studio' }}</td><td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $studio->owner?->name ?? 'Not assigned' }}</td><td class="py-4 pr-4">{{ number_format($studio->users_count ?? 0) }}</td><td class="py-4"><span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $studio->status ?? 'unknown' }}</span></td></tr>
                            @empty
                                <tr><td colspan="4" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No studios found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
