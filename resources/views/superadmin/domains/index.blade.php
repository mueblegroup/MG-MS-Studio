<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Tenant Routing</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Domains & Routing</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">
                        Owner-level visibility for studio subdomains, custom domains, primary domains and verification status.
                    </p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <a href="{{ route('superadmin.domains.index') }}" class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Total Domains</p>
                <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ number_format($domains->total()) }}</div>
            </a>
            <a href="{{ route('superadmin.domains.index', ['verified' => '1']) }}" class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Verified</p>
                <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ number_format((int) ($verifiedCounts[1] ?? 0)) }}</div>
            </a>
            <a href="{{ route('superadmin.domains.index', ['verified' => '0']) }}" class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Pending Verification</p>
                <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ number_format((int) ($verifiedCounts[0] ?? 0)) }}</div>
            </a>
            <div class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Domain Types</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    @forelse($typeCounts as $typeName => $count)
                        <a href="{{ route('superadmin.domains.index', ['type' => $typeName === 'unknown' ? '' : $typeName]) }}" class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">
                            {{ $typeName }}: {{ number_format((int) $count) }}
                        </a>
                    @empty
                        <span class="text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No domains</span>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('superadmin.domains.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-6">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Domain, studio name or slug" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Type</label>
                    <select name="type" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">All types</option>
                        @foreach($typeCounts->keys() as $typeName)
                            @continue($typeName === 'unknown')
                            <option value="{{ $typeName }}" @selected($type === $typeName)>{{ ucfirst($typeName) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Verification</label>
                    <select name="verified" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">All</option>
                        <option value="1" @selected($verified === '1')>Verified</option>
                        <option value="0" @selected($verified === '0')>Pending</option>
                    </select>
                </div>
                <div class="flex items-end md:col-span-2">
                    <button class="w-full rounded-2xl bg-[#d97706] px-4 py-3 text-sm font-extrabold text-white shadow-sm">Filter</button>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#eadfce] text-sm dark:divide-gray-800">
                    <thead>
                        <tr class="text-left text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">
                            <th class="py-3 pr-4">Domain</th>
                            <th class="py-3 pr-4">Studio</th>
                            <th class="py-3 pr-4">Type</th>
                            <th class="py-3 pr-4">Primary</th>
                            <th class="py-3 pr-4">Verified</th>
                            <th class="py-3">Verified At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                        @forelse($domains as $domain)
                            <tr class="text-[#31261d] dark:text-gray-200">
                                <td class="py-4 pr-4">
                                    <div class="font-extrabold">{{ $domain->domain }}</div>
                                    <div class="text-xs font-bold text-[#9a8c7d] dark:text-gray-500">{{ $domain->studio?->status ?? 'unknown studio status' }}</div>
                                </td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">
                                    <div class="font-bold text-[#31261d] dark:text-gray-200">{{ $domain->studio?->name ?? 'Unknown studio' }}</div>
                                    <div class="text-xs">{{ $domain->studio?->slug ?? '-' }}</div>
                                </td>
                                <td class="py-4 pr-4"><span class="rounded-full bg-[#f7f2ea] px-3 py-1 text-xs font-extrabold uppercase text-[#6b5f52] dark:bg-gray-950 dark:text-gray-300">{{ $domain->type ?? 'unknown' }}</span></td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $domain->is_primary ? 'Yes' : 'No' }}</td>
                                <td class="py-4 pr-4"><span class="rounded-full {{ $domain->is_verified ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-200' : 'bg-[#fff3df] text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200' }} px-3 py-1 text-xs font-extrabold uppercase">{{ $domain->is_verified ? 'Verified' : 'Pending' }}</span></td>
                                <td class="py-4 text-[#6b5f52] dark:text-gray-400">{{ optional($domain->verified_at)->format('d M Y') ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No domains found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $domains->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
