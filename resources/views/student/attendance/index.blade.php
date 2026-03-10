<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">My Attendance</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Track your attendance for classes and plans.</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-5 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <form method="GET" class="flex flex-col lg:flex-row gap-3 lg:items-end">
                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Type</label>
                    <select name="type" class="mt-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="" @selected(!$type)>All</option>
                        <option value="class" @selected($type === 'class')>Class</option>
                        <option value="plan" @selected($type === 'plan')>Plan</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Status</label>
                    <select name="status" class="mt-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="" @selected(!$status)>All</option>
                        <option value="attended" @selected($status === 'attended')>Attended</option>
                        <option value="no_show" @selected($status === 'no_show')>No Show</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">Range</label>
                    <select name="range" class="mt-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="month" @selected($range === 'month')>This Month</option>
                        <option value="all" @selected($range === 'all')>All Time</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Apply
                    </button>

                    <a href="{{ route('student.attendance.index') }}"
                       class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Session</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Venue</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($items as $i)
                            @php
                                $status = $i->status ?? '—';
                                $badge = match($status) {
                                    'attended' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
                                    'no_show' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                };
                            @endphp

                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $i->title }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($i->start_time)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $i->venue_name ?? '—' }}
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-[10px] px-2 py-1 rounded-full
                                        {{ $i->type === 'plan' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-200' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-200' }}">
                                        {{ strtoupper($i->type) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                    @if($i->attended_at)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                            {{ \Carbon\Carbon::parse($i->attended_at)->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No attendance records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</x-app-layout>