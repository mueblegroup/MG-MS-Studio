<x-app-layout>
    <div class="p-4 sm:p-8 bg-gray-50/50 dark:bg-gray-900 min-h-screen">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Welcome back {{ Auth::user()->name }}!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
            @php
                $stats = [
                    ['label' => 'Total Earnings', 'value' => $currency.' '.number_format($total_profit, 2), 'icon' => 'bx-wallet', 'color' => 'indigo', 'link' => route('payments.index')],
                    ['label' => 'This Month', 'value' => $currency.' '.number_format($this_month_revenue ?? 0, 2), 'icon' => 'bx-line-chart', 'color' => 'emerald', 'link' => route('payments.index')],
                    ['label' => 'Pending Orders', 'value' => $pending_orders ?? 0, 'icon' => 'bx-time-five', 'color' => 'amber', 'link' => route('payments.index')],
                    ['label' => 'Total Teachers', 'value' => $total_teachers, 'icon' => 'bxs-user-voice', 'color' => 'blue', 'link' => route('admin.teachers')],
                    ['label' => 'Total Students', 'value' => $total_students, 'icon' => 'bxs-graduation', 'color' => 'violet', 'link' => route('admin.students')],
                ];
            @endphp

            @foreach($stats as $stat)
            <a href="{{ $stat['link'] }}" class="group bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-300 hover:shadow-lg">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">{{ $stat['label'] }}</p>
                        <h3 class="truncate text-2xl font-bold text-gray-800 dark:text-white transition-colors">{{ $stat['value'] }}</h3>
                    </div>
                    <div class="shrink-0 p-3 rounded-xl bg-gray-50 text-gray-700 dark:bg-gray-900 dark:text-gray-200">
                        <i class="bx {{ $stat['icon'] }} text-3xl"></i>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mt-6">
            <div class="lg:col-span-8 bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between mb-6">
                    <div>
                        <h3 class="font-bold text-gray-800 dark:text-white">Revenue Analytics</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Paid payments grouped by month for {{ now()->year }}.</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-600 dark:border-gray-700 dark:text-gray-300">
                        {{ now()->year }}
                    </div>
                </div>

                <div class="relative h-[350px]">
                    <canvas id="paymentHistoryChart"></canvas>

                    @if(collect($revenue_data ?? [])->sum() <= 0)
                        <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                            <div class="rounded-2xl bg-white/90 px-4 py-3 text-center shadow-sm ring-1 ring-gray-100 dark:bg-gray-900/90 dark:ring-gray-700">
                                <div class="text-sm font-bold text-gray-800 dark:text-white">No paid revenue yet</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">Paid orders will appear here automatically.</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4">User Distribution</h3>
                    <div class="h-[200px]">
                        <canvas id="userDistChart"></canvas>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-gray-800 dark:text-white mb-4">Class Schedule</h3>
                    <div id="calendar" class="modern-calendar text-xs"></div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Modern Calendar Styling */
        .fc { --fc-border-color: transparent; --fc-button-bg-color: #4f46e5; --fc-button-border-color: transparent; }
        .fc .fc-toolbar-title { font-size: 1rem !important; font-weight: 700; }
        .fc .fc-daygrid-day-number { font-size: 0.75rem; color: #9ca3af; }
        .fc .fc-button-primary:hover { background-color: #4338ca; }
    </style>

    @php
        $dashboardMonths = $months ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $dashboardRevenueData = $revenue_data ?? [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
        $dashboardCalendarEvents = $calendar_events ?? [];
        $dashboardUserDistribution = [
            (int) $total_teachers,
            (int) $total_students,
            (int) $total_unverified,
        ];
    @endphp

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const revenueLabels = @json($dashboardMonths);
            const revenueData = @json($dashboardRevenueData);
            const userDistributionData = @json($dashboardUserDistribution);
            const calendarEvents = @json($dashboardCalendarEvents);

            // 1. Line Chart (Revenue)
            const revenueCanvas = document.getElementById('paymentHistoryChart');
            if (revenueCanvas) {
                const ctxLine = revenueCanvas.getContext('2d');
                const gradient = ctxLine.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
                gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

                new Chart(ctxLine, {
                    type: 'line',
                    data: {
                        labels: revenueLabels,
                        datasets: [{
                            label: 'Revenue',
                            data: revenueData,
                            borderColor: '#10b981',
                            backgroundColor: gradient,
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return '{{ $currency }} ' + Number(context.parsed.y || 0).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(156, 163, 175, 0.14)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                        }
                    }
                });
            }

            // 2. Pie Chart (User Distribution)
            const userCanvas = document.getElementById('userDistChart');
            if (userCanvas) {
                const ctxPie = userCanvas.getContext('2d');
                new Chart(ctxPie, {
                    type: 'doughnut',
                    data: {
                        labels: ['Teachers', 'Students', 'Unverified'],
                        datasets: [{
                            data: userDistributionData,
                            backgroundColor: ['#3b82f6', '#6366f1', '#f43f5e'],
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
            }

            // 3. Calendar
            const calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                const calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                    height: 'auto',
                    events: calendarEvents
                });
                calendar.render();
            }
        });
    </script>
    @endpush
</x-app-layout>
