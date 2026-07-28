<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">My Classes</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your class details, teachers, venues, dates and times in one place.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
            <div class="xl:col-span-7 bg-white dark:bg-gray-800 p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800 dark:text-white">Calendar</h3>
                </div>
                <div id="calendar" class="modern-calendar text-sm"></div>
            </div>

            <div class="xl:col-span-5 bg-white dark:bg-gray-800 p-5 sm:p-6 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800 dark:text-white">This Month’s Classes</h3>
                </div>

                <div class="border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden">
                    <div class="hidden sm:grid grid-cols-12 gap-2 px-4 py-3 bg-gray-50 dark:bg-gray-700/40 text-xs font-semibold text-gray-600 dark:text-gray-300">
                        <div class="col-span-5">Class & Teacher</div>
                        <div class="col-span-5">Date, Time & Venue</div>
                        <div class="col-span-2 text-right">Type</div>
                    </div>

                    <div class="max-h-[520px] overflow-auto divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($items as $i)
                            <div class="p-4 sm:grid sm:grid-cols-12 sm:gap-2 sm:items-center hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <div class="sm:col-span-5">
                                    <div class="font-semibold text-gray-900 dark:text-white leading-snug">
                                        {{ $i->title }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Teacher:</span>
                                        {{ $i->teacher_name ?: 'Not assigned' }}
                                    </div>
                                    @if($i->description)
                                        <p class="mt-2 line-clamp-2 text-xs leading-relaxed text-gray-500 dark:text-gray-400">
                                            {{ $i->description }}
                                        </p>
                                    @endif
                                </div>

                                <div class="sm:col-span-5 mt-3 sm:mt-0">
                                    <div class="text-sm text-gray-800 dark:text-gray-100">
                                        {{ \Carbon\Carbon::parse($i->start)->format('D, d M Y') }}
                                    </div>
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="font-semibold text-gray-600 dark:text-gray-300">Venue:</span>
                                        {{ $i->venue ?: 'Not specified' }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ \Carbon\Carbon::parse($i->start)->format('g:i A') }}
                                        @if($i->end)
                                            – {{ \Carbon\Carbon::parse($i->end)->format('g:i A') }}
                                        @endif
                                    </div>
                                </div>

                                <div class="sm:col-span-2 mt-3 sm:mt-0 sm:text-right">
                                    <span class="inline-flex items-center justify-center text-[10px] px-2 py-1 rounded-full font-semibold
                                        {{ $i->type === 'plan'
                                            ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200'
                                            : 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200' }}">
                                        {{ strtoupper($i->type) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                No sessions found for this month.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    <style>
        .fc { --fc-border-color: transparent; --fc-button-bg-color: #4f46e5; --fc-button-border-color: transparent; }
        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; }
        .fc .fc-daygrid-day-number { font-size: 0.75rem; color: #9ca3af; }
        .fc .fc-button-primary:hover { background-color: #4338ca; }
        .fc .fc-daygrid-event { border-radius: 10px; padding: 2px 6px; }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const calendarEl = document.getElementById('calendar');
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                    height: 'auto',
                    events: @json($calendarEvents ?? [])
                });
                calendar.render();
            });
        </script>
    @endpush
</x-app-layout>
