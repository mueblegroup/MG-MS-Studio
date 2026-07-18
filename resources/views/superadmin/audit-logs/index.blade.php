<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Security</p>
                    <h1 class="mt-3 text-2xl font-extrabold text-[#171717] dark:text-white">Audit Logs</h1>
                    <p class="mt-1 text-sm font-medium text-[#6b5f52] dark:text-gray-400">Review security-sensitive and account-changing activity across the platform. This page is restricted to superadmins.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        <form method="GET" class="grid gap-3 rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:grid-cols-4">
            <input type="search" name="search" value="{{ $search }}" placeholder="User, email, route or IP" class="rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-gray-950">
            <select name="event" class="rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-gray-950">
                <option value="">All events</option>
                @foreach ($events as $event)
                    <option value="{{ $event }}" @selected($selectedEvent === $event)>{{ str($event)->replace(['action.', '_'], ['', ' '])->headline() }}</option>
                @endforeach
            </select>
            <select name="studio_id" class="rounded-2xl border-gray-300 dark:border-gray-700 dark:bg-gray-950">
                <option value="">All studios</option>
                @foreach ($studios as $studio)
                    <option value="{{ $studio->id }}" @selected($selectedStudioId === $studio->id)>{{ $studio->name }}</option>
                @endforeach
            </select>
            <button class="rounded-2xl bg-[#d97706] px-4 py-3 text-sm font-extrabold text-white">Filter Logs</button>
        </form>

        <div class="overflow-hidden rounded-3xl border border-[#eadfce] bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                    <thead class="bg-gray-50 text-left text-xs font-extrabold uppercase tracking-wider text-gray-500 dark:bg-gray-950">
                        <tr><th class="px-5 py-4">Date</th><th class="px-5 py-4">User</th><th class="px-5 py-4">Studio</th><th class="px-5 py-4">Event</th><th class="px-5 py-4">Route</th><th class="px-5 py-4">IP</th><th class="px-5 py-4">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                <td class="px-5 py-4"><div class="font-bold text-gray-950 dark:text-white">{{ $log->user?->name ?? 'System' }}</div><div class="text-xs text-gray-500">{{ $log->user?->email ?? '-' }}</div></td>
                                <td class="px-5 py-4 text-gray-600 dark:text-gray-300">{{ $log->studio?->name ?? 'Central' }}</td>
                                <td class="px-5 py-4 font-bold text-gray-950 dark:text-white">{{ str($log->event)->replace(['action.', '_'], ['', ' '])->headline() }}</td>
                                <td class="px-5 py-4 text-gray-500"><div>{{ $log->method }} {{ $log->route ?: '-' }}</div><div class="text-xs">{{ data_get($log->metadata, 'fields') ? 'Fields: '.implode(', ', data_get($log->metadata, 'fields', [])) : '' }}</div></td>
                                <td class="whitespace-nowrap px-5 py-4 text-gray-500">{{ $log->ip_address ?: '-' }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-extrabold dark:bg-gray-800">{{ data_get($log->metadata, 'status', '-') }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-gray-500">No audit activity matches the selected filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 p-5 dark:border-gray-800">{{ $logs->links() }}</div>
        </div>
    </div>
</x-app-layout>
