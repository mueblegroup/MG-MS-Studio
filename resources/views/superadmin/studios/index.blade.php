<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Studio Control</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Manage Studios</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Owner-level view of every studio in the platform. Normal admins only manage their assigned studio.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">
                            <th class="py-3 pr-4">Studio</th>
                            <th class="py-3 pr-4">Owner</th>
                            <th class="py-3 pr-4">Plan</th>
                            <th class="py-3 pr-4">Users</th>
                            <th class="py-3 pr-4">Subscription Ends</th>
                            <th class="py-3 pr-4">Status</th>
                            <th class="py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                        @forelse($studios as $studio)
                            <tr class="text-[#31261d] dark:text-gray-200">
                                <td class="py-4 pr-4">
                                    <div class="font-extrabold">{{ $studio->name ?? 'Untitled Studio' }}</div>
                                    <div class="text-xs font-bold text-[#9a8c7d] dark:text-gray-500">{{ $studio->subdomain ?? $studio->custom_domain ?? $studio->slug }}</div>
                                </td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $studio->owner?->name ?? 'Not assigned' }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $studio->platformSubscriptionPlan?->name ?? $studio->plan_name ?? 'No plan' }}</td>
                                <td class="py-4 pr-4 font-bold">{{ number_format($studio->users_count ?? 0) }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ optional($studio->subscription_ends_at)->format('d M Y') ?? '-' }}</td>
                                <td class="py-4 pr-4"><span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $studio->status ?? 'unknown' }}</span></td>
                                <td class="py-4 text-right"><a href="{{ route('superadmin.studios.edit', $studio) }}" class="rounded-xl bg-[#171717] px-4 py-2 text-xs font-extrabold text-white dark:bg-white dark:text-gray-950">Manage</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No studios found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $studios->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
