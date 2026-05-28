<x-app-layout>
    <div class="p-4 sm:p-8 bg-gray-50/50 dark:bg-gray-900 min-h-screen">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Overview</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Welcome back {{ Auth::user()->name }}!</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @php
                $stats = [
                    ['label' => 'Total Earnings', 'value' => $currency.' '.number_format($total_profit, 2), 'icon' => 'bx-wallet', 'color' => 'indigo', 'link' => route('payments.index')],
                    ['label' => 'Total Teachers', 'value' => $total_teachers, 'icon' => 'bxs-user-voice', 'color' => 'indigo', 'link' => route('admin.teachers')],
                    ['label' => 'Total Students', 'value' => $total_students, 'icon' => 'bxs-graduation', 'color' => 'indigo', 'link' => route('admin.students')],
                ];
            @endphp

            @foreach($stats as $stat)
            <a href="{{ $stat['link'] }}" class="group bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 hover:border-{{ $stat['color'] }}-400 transition-all duration-300 hover:shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">{{ $stat['label'] }}</p>
                        <h3 class="text-2xl font-bold text-gray-800 dark:text-white group-hover:text-{{ $stat['color'] }}-600 transition-colors">{{ $stat['value'] }}</h3>
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
                    <h3 class="font-bold text-gray-800 dark:text-white">Revenue Analytics</h3>
                    <select class="text-xs border-gray-200 dark:bg-gray-700 dark:border-gray-600 rounded-lg">
                        <option>This Year</option>
                        <option>Last Year</option>
                    </select>
                </div>
                <div class="h-[350px]">
                    <canvas id="paymentHistoryChart"></canvas>
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

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.14/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Line Chart (Revenue)
            const ctxLine = document.getElementById('paymentHistoryChart').getContext('2d');
            const gradient = ctxLine.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.2)');
            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');

            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: {!! json_encode($months ?? ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']) !!},
                    datasets: [{
                        data: {!! json_encode($revenue_data ?? [0,0,0,0,0,0,0,0,0,0,0,0]) !!},
                        borderColor: '#10b981',
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.4,
                        borderWidth: 3,
                        pointRadius: 0,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { grid: { display: false }, ticks: { color: '#9ca3af' } },
                        x: { grid: { display: false }, ticks: { color: '#9ca3af' } }
                    }
                }
            });

            // 2. Pie Chart (User Distribution)
            const ctxPie = document.getElementById('userDistChart').getContext('2d');
            new Chart(ctxPie, {
                type: 'doughnut',
                data: {
                    labels: ['Teachers', 'Students', 'Unverified'],
                    datasets: [{
                        data: [{{ $total_teachers }}, {{ $total_students }}, {{ $total_unverified }}],
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

            // 3. Calendar
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: { left: 'prev', center: 'title', right: 'next' },
                height: 'auto',
                events: @json($calendar_events ?? [])
            });
            calendar.render();
        });
    </script>
    @endpush
</x-app-layout>