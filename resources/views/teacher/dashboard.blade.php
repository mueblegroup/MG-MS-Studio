<x-app-layout>
    <div class="p-4 sm:p-8 bg-gray-50/50 dark:bg-gray-900 min-h-screen">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Welcome back {{ Auth::user()->name }}!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @php
                $stats = [
                    // You can enable earnings later once payments is teacher-linked
                    // ['label' => 'Your Earnings', 'value' => $currency.' '.number_format($totalProfit, 2), 'icon' => 'bx-wallet', 'color' => 'indigo', 'link' => '#'],
                    ['label' => 'Your Classes', 'value' => $totalClasses, 'icon' => 'bx-calendar', 'color' => 'indigo', 'link' => route('teacher.classes.index')],
                    ['label' => 'Your Plans', 'value' => $totalPlans, 'icon' => 'bx-receipt', 'color' => 'indigo', 'link' => route('teacher.plans.index')],
                    ['label' => 'Today Sessions', 'value' => $todaySessions->count(), 'icon' => 'bx-time-five', 'color' => 'indigo', 'link' => '#today'],
                ];
            @endphp

            @foreach($stats as $stat)
                <a href="{{ $stat['link'] }}"
                   class="group bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-{{ $stat['color'] }}-400 transition-all duration-300 hover:shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">{{ $stat['label'] }}</p>
                            <h3 class="text-2xl font-bold text-gray-800 dark:text-white group-hover:text-{{ $stat['color'] }}-600 transition-colors">
                                {{ $stat['value'] }}
                            </h3>
                        </div>
                        <div class="p-3 bg-{{ $stat['color'] }}-50 dark:bg-{{ $stat['color'] }}-900/20 rounded-xl text-{{ $stat['color'] }}-600">
                            <i class="bx {{ $stat['icon'] }} text-3xl"></i>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">

            <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-gray-800 dark:text-white">Classes & Plans Distribution</h3>
                </div>
                <div class="h-[350px]">
                    <canvas id="teacherDistChart"></canvas>
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div id="today" class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
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
                                            {{ \Carbon\Carbon::parse($s->start)->format('g:i A') }}
                                        </div>
                                    </div>

                                    <div class="mt-1 flex items-center justify-between">
                                        <div class="text-xs text-gray-400">
                                            {{ $s->subtitle ?? '' }}
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
                                            {{ \Carbon\Carbon::parse($s->start)->format('g:i A') }}
                                        </div>
                                    </div>

                                    <div class="mt-1 flex items-center justify-between">
                                        <div class="text-xs text-gray-400">
                                            {{ $s->subtitle ?? '' }}
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

                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4">Class Schedule</h3>
                    <div id="calendar" class="modern-calendar text-xs"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .fc { --fc-border-color: transparent; --fc-button-bg-color: #4f46e5; --fc-button-border-color: transparent; }
        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; }
        .fc .fc-daygrid-day-number { font-size: 0.75rem; color: #9ca3af; }
        .fc .fc-button-primary:hover { background-color: #4338ca; }
    </style>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {

                // Doughnut Chart
                const ctx = document.getElementById('teacherDistChart').getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: @json($distLabels),
                        datasets: [{
                            data: @json($distData),
                            backgroundColor: ['#6366f1', '#3b82f6'],
                            borderWidth: 0,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                        }
                    }
                });

                // Calendar
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
