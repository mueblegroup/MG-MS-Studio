<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Access</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Platform Users</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Owner-level visibility of every account across all studios. Studio admins remain scoped to their own studio.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            @foreach(['superadmin' => 'Superadmins', 'admin' => 'Studio Admins', 'teacher' => 'Teachers', 'student' => 'Students'] as $key => $label)
                <a href="{{ route('superadmin.users.index', ['role' => $key]) }}" class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm transition hover:-translate-y-0.5 dark:border-gray-800 dark:bg-gray-900">
                    <p class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">{{ $label }}</p>
                    <div class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ number_format((int) ($roleCounts[$key] ?? 0)) }}</div>
                </a>
            @endforeach
        </div>

        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('superadmin.users.index') }}" class="grid grid-cols-1 gap-3 md:grid-cols-12">
                <div class="md:col-span-6">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Search</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Name, email or phone" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                </div>
                <div class="md:col-span-4">
                    <label class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Role</label>
                    <select name="role" class="mt-2 w-full rounded-2xl border-[#eadfce] bg-white text-sm font-semibold text-[#31261d] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-700 dark:bg-gray-950 dark:text-gray-100">
                        <option value="">All roles</option>
                        @foreach(['superadmin' => 'Superadmin', 'admin' => 'Studio Admin', 'teacher' => 'Teacher', 'student' => 'Student'] as $key => $label)
                            <option value="{{ $key }}" @selected($role === $key)>{{ $label }}</option>
                        @endforeach
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
                            <th class="py-3 pr-4">User</th>
                            <th class="py-3 pr-4">Role</th>
                            <th class="py-3 pr-4">Studio</th>
                            <th class="py-3 pr-4">Phone</th>
                            <th class="py-3">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f0e5d5] dark:divide-gray-800">
                        @forelse($users as $user)
                            <tr class="text-[#31261d] dark:text-gray-200">
                                <td class="py-4 pr-4">
                                    <div class="font-extrabold">{{ $user->name }}</div>
                                    <div class="text-xs font-bold text-[#9a8c7d] dark:text-gray-500">{{ $user->email }}</div>
                                </td>
                                <td class="py-4 pr-4"><span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $user->role }}</span></td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $user->studio?->name ?? 'Platform / unassigned' }}</td>
                                <td class="py-4 pr-4 text-[#6b5f52] dark:text-gray-400">{{ $user->phone_number ?? '-' }}</td>
                                <td class="py-4 text-[#6b5f52] dark:text-gray-400">{{ optional($user->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
