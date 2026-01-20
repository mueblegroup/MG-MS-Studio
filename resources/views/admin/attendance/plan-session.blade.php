<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plan Session Attendance</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $plan->name }} • {{ optional($session->start_time)->format('Y-m-d H:i') }}
                    @if($session->session_name) • {{ $session->session_name }} @endif
                </p>
            </div>

            <a href="{{ route('admin.plans.show', $plan->id) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($students as $up)
                            @php
                                $u = $up->user;
                                $att = $up->attendance;
                                $status = $att->status ?? '—';
                                $badge = match($status) {
                                    'attended' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200',
                                    'no_show' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-200',
                                    default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200',
                                };
                            @endphp

                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $u->name ?? '—' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $u->email ?? '—' }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                        {{ strtoupper($status) }}
                                    </span>
                                    @if($att?->attended_at)
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $att->attended_at->format('Y-m-d H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <form method="POST" action="{{ route('admin.plans.sessions.attendance.mark', [$plan->id, $session->id, $u->id]) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="attended">
                                            <button class="px-3 py-2 rounded-xl text-xs font-semibold text-white bg-green-600 hover:bg-green-700 transition">
                                                Attended
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.plans.sessions.attendance.mark', [$plan->id, $session->id, $u->id]) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="no_show">
                                            <button class="px-3 py-2 rounded-xl text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                                                No Show
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.plans.sessions.attendance.mark', [$plan->id, $session->id, $u->id]) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="reset">
                                            <button class="px-3 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                                Reset
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No active users for this plan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>

    </div>
</x-app-layout>
