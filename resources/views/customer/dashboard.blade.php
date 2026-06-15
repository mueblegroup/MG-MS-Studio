<x-app-layout>
    <div class="min-h-screen bg-[#f7f2ea] px-4 py-6 dark:bg-gray-950 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-7xl space-y-6">
            <div class="flex flex-col gap-4 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-500">Mueble LMS Portal</p>
                    <h1 class="mt-2 text-3xl font-black text-gray-900 dark:text-white">Your Studios</h1>
                    <p class="mt-2 max-w-2xl text-sm text-gray-500 dark:text-gray-400">Manage your studio subscriptions here. Enter each studio portal from its own subdomain.</p>
                </div>
                <a href="{{ route('customer.studios.create') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">+ Register Studio</a>
            </div>

            @if (session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">{{ session('success') }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Studios</p><p class="mt-3 text-4xl font-black text-gray-900 dark:text-white">{{ $studios->count() }}</p></div>
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Active / Trial</p><p class="mt-3 text-4xl font-black text-gray-900 dark:text-white">{{ $studios->whereIn('status', ['active', 'trial'])->count() }}</p></div>
                <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10"><p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Available Plans</p><p class="mt-3 text-4xl font-black text-gray-900 dark:text-white">{{ $plans->count() }}</p></div>
            </div>

            <div class="rounded-3xl bg-white shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                <div class="border-b border-gray-100 p-6 dark:border-gray-800">
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">Studio Portals</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Use the portal button to enter the correct studio app.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wider text-gray-500 dark:bg-gray-950/50 dark:text-gray-400"><tr><th class="px-6 py-4">Studio</th><th class="px-6 py-4">Portal</th><th class="px-6 py-4">Plan</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Action</th></tr></thead>
                        <tbody class="divide-y divide-gray-100 text-sm dark:divide-gray-800">
                            @forelse ($studios as $studio)
                                <tr>
                                    <td class="px-6 py-4"><div class="font-bold text-gray-900 dark:text-white">{{ $studio->name }}</div><div class="text-xs text-gray-500">Created {{ optional($studio->created_at)->format('d M Y') }}</div></td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $studio->subdomain }}.{{ $rootDomain }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ $studio->platformSubscriptionPlan?->name ?? ucfirst($studio->plan_name ?? 'Trial') }}</td>
                                    <td class="px-6 py-4"><span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase text-orange-700 dark:bg-orange-950 dark:text-orange-300">{{ $studio->status }}</span></td>
                                    <td class="px-6 py-4 text-right"><a href="{{ route('customer.studios.launch', $studio) }}" class="inline-flex rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white transition hover:bg-gray-700 dark:bg-white dark:text-gray-900">Enter Portal</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No studios yet. Register your first studio to start using the LMS.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
