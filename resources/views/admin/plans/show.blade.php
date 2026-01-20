<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $plan->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $plan->description ?: 'No description.' }}
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.plans') }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                    <i class="bx bx-arrow-back"></i> Back
                </a>

                <a href="{{ route('admin.plans.edit', $plan->id) }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                    <i class="bx bx-edit"></i> Edit Plan
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $plan->currency ?? 'MYR' }} {{ number_format((float)($plan->price ?? 0), 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Sessions</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $plan->sessions->count() }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Recurring</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $plan->is_recurring ? 'Yes' : 'No' }}
                </div>

                @if($plan->is_recurring)
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 px-2">
                        {{ $plan->recurrence_frequency ?? '-' }}
                        @if($plan->recurrence_frequency === 'custom' && $plan->custom_frequency_days)
                            ({{ $plan->custom_frequency_days }} days)
                        @endif
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Until</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ optional($plan->until_date)->format('Y-m-d') ?: '-' }}
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 dark:text-white">Plan Sessions</h2>
                <div class="text-xs text-gray-500 dark:text-gray-400">Listed in date order</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Label</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Venue</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>

                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($plan->sessions as $s)
                            @php
                                $date = optional($s->start_time)->format('Y-m-d');
                                $start = optional($s->start_time)->format('H:i');
                                $end = optional($s->end_time)->format('H:i');
                            @endphp

                            <tr class="bg-gray-50 dark:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $date }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $start }} - {{ $end }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $s->session_name ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $s->venue_name ?: '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $s->capacity ?? '-' }}</td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2">
                                    <a href="{{ route('admin.plans.sessions.edit', [$plan->id, $s->id]) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-indigo-600 hover:bg-indigo-50
                                              dark:hover:bg-indigo-900/20 transition mr-2">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>
                                    <a href="{{ route('admin.plans.sessions.attendance', [$plan->id, $s->id]) }}"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                                text-xs font-semibold text-emerald-600 hover:bg-emerald-50
                                                dark:hover:bg-emerald-900/20 transition mr-2">
                                            <i class="bx bx-check-circle"></i> Attendance
                                    </a>


                                    <form method="POST"
                                          action="{{ route('admin.plans.sessions.destroy', [$plan->id, $s->id]) }}"
                                          onsubmit="return confirm('Delete this session?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                                   text-xs font-semibold text-red-600 hover:bg-red-50
                                                   dark:hover:bg-red-900/20 transition">
                                            Delete
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No sessions in this plan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
