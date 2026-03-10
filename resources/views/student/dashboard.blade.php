<x-app-layout>
    <div class="p-4 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Welcome back {{ Auth::user()->name }}!</p>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Today Sessions</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $todaySessions->count() }}</h3>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Tomorrow Sessions</p>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $tomorrowSessions->count() }}</h3>
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Attendance Rate</p>

                @if($attendanceRate === null)
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">—</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">No past sessions yet.</p>
                @else
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white">{{ $attendanceRate }}%</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $attendedTotal }} attended out of {{ $totalPast }}
                    </p>
                @endif
            </div>
        </div>

        {{-- Lists --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Today --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">Today</h3>

                @if($todaySessions->count())
                    <div class="space-y-3">
                        @foreach($todaySessions as $s)
                            <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="font-semibold text-gray-800 dark:text-white">
                                        {{ $s->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center justify-between">
                                    <div class="text-xs text-gray-400">
                                        {{ $s->venue_name ?? '—' }}
                                    </div>
                                    <span class="text-[10px] px-2 py-1 rounded-full
                                        {{ $s->type === 'plan' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-200' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-200' }}">
                                        {{ strtoupper($s->type) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No sessions scheduled for today.</p>
                @endif
            </div>

            {{-- Tomorrow --}}
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <h3 class="font-bold text-gray-800 dark:text-white mb-4">Tomorrow</h3>

                @if($tomorrowSessions->count())
                    <div class="space-y-3">
                        @foreach($tomorrowSessions as $s)
                            <div class="p-4 rounded-xl border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <div class="font-semibold text-gray-800 dark:text-white">
                                        {{ $s->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($s->start_time)->format('g:i A') }}
                                    </div>
                                </div>

                                <div class="mt-1 flex items-center justify-between">
                                    <div class="text-xs text-gray-400">
                                        {{ $s->venue_name ?? '—' }}
                                    </div>
                                    <span class="text-[10px] px-2 py-1 rounded-full
                                        {{ $s->type === 'plan' ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-200' : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-200' }}">
                                        {{ strtoupper($s->type) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">No sessions scheduled for tomorrow.</p>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>