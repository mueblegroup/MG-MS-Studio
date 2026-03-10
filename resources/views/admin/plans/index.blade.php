<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plans</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage plan products and their scheduled sessions.
                </p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Assign Plan --}}
                <a href="{{ route('admin.planassignments.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bx bx-user-plus"></i> Assigned Plans
                </a>
                {{-- Create Plan --}}
                <a href="{{ route('admin.plans.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-plus"></i> Add Plan
                </a>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.plans') }}" class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
                <div class="flex-1">
                    <input
                        name="q"
                        value="{{ $search ?? request('q','') }}"
                        placeholder="Search plan name, description..."
                        class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                               focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                        <i class="bx bx-search"></i> Search
                    </button>

                    <a href="{{ route('admin.plans') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                                    text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                <i class="bx bx-reset"></i> Reset
                    </a>

                    <select name="per_page" onchange="this.form.submit()"
                        class="inline-flex items-center gap-4 px-8 py-2 rounded-xl
                               text-xs font-semibold text-gray-700 dark:text-gray-300
                               bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>
                                {{ $size }} rows
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Teacher</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Sessions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Recurring</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Until</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($plans as $plan)
                            @php
                                $editUrl = route('admin.plans.edit', $plan->id);
                                $currency = $plan->currency ?? 'MYR';
                                $until = optional($plan->until_date)->format('Y-m-d');
                                $freq = $plan->recurrence_frequency;
                                $freqLabel = match($freq) {
                                    'everyday' => 'Daily',
                                    '7days' => 'Weekly',
                                    'monthly' => 'Monthly',
                                    'yearly' => 'Yearly',
                                    'custom' => 'Custom',
                                    default => '-'
                                };
                            @endphp

                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $plan->name }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">
                                        {{ $plan->description ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    @if($plan->teacher)
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $plan->teacher->name }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $currency }} {{ number_format($plan->price ?? 0, 2) }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $plan->sessions_count ?? $plan->sessions()->count() }}
                                </td>

                                <td class="px-4 py-4">
                                    @if($plan->is_recurring)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2">
                                            Yes • {{ $freqLabel }}
                                            @if($plan->recurrence_frequency === 'custom' && $plan->custom_frequency_days)
                                                ({{ $plan->custom_frequency_days }}d)
                                            @endif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200 px-2">
                                            No
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $until ?: '-' }}
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2">
                                    {{-- View --}}
                                    <a href="{{ route('admin.plans.show', $plan->id) }}"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-indigo-600 hover:bg-indigo-50
                                              dark:hover:bg-indigo-900/20 transition mr-2">
                                        <i class="bx bx-show"></i> View
                                    </a>

                                    {{-- Edit --}}
                                    <a href="{{ route('admin.plans.edit', $plan->id) }}"
                                       onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-indigo-600 hover:bg-indigo-50
                                              dark:hover:bg-indigo-900/20 transition mr-2">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>

                                    {{-- Remove --}}
                                    <form method="POST"
                                          action="{{ route('admin.plans.destroy', $plan->id) }}"
                                          onsubmit="event.stopPropagation(); return confirm('Delete this plan? This will also remove its sessions.')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-red-600 hover:bg-red-50
                                              dark:hover:bg-red-900/20 transition mr-2">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if(method_exists($plans, 'firstItem') && $plans->total() > 0)
                        Showing <span class="font-semibold">{{ $plans->firstItem() }}</span>
                        to <span class="font-semibold">{{ $plans->lastItem() }}</span>
                        of <span class="font-semibold">{{ $plans->total() }}</span> results
                    @else
                        —
                    @endif
                </div>

                <div>
                    {{ $plans->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
