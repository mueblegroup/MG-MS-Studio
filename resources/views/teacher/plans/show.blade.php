<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $planModel->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Sessions for this plan.
                </p>
            </div>

            <a href="{{ route('teacher.plans.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Session</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Start</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">End</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Venue</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($sessions as $s)
                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $s->session_name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($s->start_time)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ \Carbon\Carbon::parse($s->end_time)->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $s->venue_name ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('teacher.plans.sessions.attendance.show', [$planModel->id, $s->id]) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                              text-white bg-green-600 hover:bg-green-700 transition">
                                        Attendance
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No sessions found for this plan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</x-app-layout>