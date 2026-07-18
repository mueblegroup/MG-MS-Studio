<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plans</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage plan products, sessions, and current registrations.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.planassignments.index') }}" class="mg-btn-secondary"><i class="bx bx-user-plus"></i> Assigned Plans</a>
                <a href="{{ route('admin.plans.create') }}" class="mg-btn-primary"><i class="bx bx-plus"></i> Add Plan</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.plans') }}" class="mb-4 flex flex-col md:flex-row gap-2">
            <input name="q" value="{{ $search ?? request('q','') }}" placeholder="Search plan name, description..." class="mg-input flex-1" />
            <button type="submit" class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
            <a href="{{ route('admin.plans') }}" class="mg-btn-secondary"><i class="bx bx-reset"></i> Reset</a>
            <select name="per_page" onchange="this.form.submit()" class="mg-select">
                @foreach([10,25,50,100] as $size)
                    <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} rows</option>
                @endforeach
            </select>
        </form>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto max-h-[70vh]">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Teacher</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Sessions</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Registered Students</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Recurring</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Until</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($plans as $plan)
                            @php
                                $freqLabel = match($plan->recurrence_frequency) {
                                    'everyday' => 'Daily', '7days' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'custom' => 'Custom', default => '-'
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-4"><div class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</div><div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $plan->description ?? '' }}</div></td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->teacher?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->currency ?? 'MYR' }} {{ number_format($plan->price ?? 0, 2) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->sessions_count ?? $plan->sessions()->count() }}</td>
                                <td class="px-4 py-4"><span class="mg-badge">{{ $plan->registered_students_count }}</span></td>
                                <td class="px-4 py-4">
                                    @if($plan->is_recurring)
                                        <span class="mg-badge">Yes • {{ $freqLabel }}</span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ optional($plan->until_date)->format('Y-m-d') ?: '-' }}</td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('admin.plans.show', $plan->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-show"></i> View</a>
                                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-edit"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan? This will also remove its sessions.')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="mg-btn-danger"><i class="bx bx-trash"></i> Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No plans found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">{{ $plans->links() }}</div>
        </div>
    </div>
</x-app-layout>
