<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plans</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage plan products, sessions, and current registrations.</p>
            </div>
            <div class="grid grid-cols-1 gap-2 sm:flex sm:items-center">
                <a href="{{ route('admin.planassignments.index') }}" class="mg-btn-secondary"><i class="bx bx-user-plus"></i> Assigned Plans</a>
                <a href="{{ route('admin.plans.create') }}" class="mg-btn-primary"><i class="bx bx-plus"></i> Add Plan</a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('admin.plans') }}" class="mb-4 grid grid-cols-1 gap-2 sm:grid-cols-[minmax(0,1fr)_auto_auto_auto]">
            <input name="q" value="{{ $search ?? request('q','') }}" placeholder="Search plan name, description..." class="mg-input min-w-0" />
            <button type="submit" class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
            <a href="{{ route('admin.plans') }}" class="mg-btn-secondary"><i class="bx bx-reset"></i> Reset</a>
            <select name="per_page" onchange="this.form.submit()" class="mg-select">
                @foreach([10,25,50,100] as $size)
                    <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} rows</option>
                @endforeach
            </select>
        </form>

        {{-- Mobile cards --}}
        <div class="space-y-3 md:hidden">
            @forelse($plans as $plan)
                @php
                    $freqLabel = match($plan->recurrence_frequency) {
                        'everyday' => 'Daily', '7days' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly', 'custom' => 'Custom', default => '-'
                    };
                @endphp
                <article class="mg-card min-w-0 p-4">
                    <div class="flex min-w-0 items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="break-words font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h2>
                            <p class="mt-1 line-clamp-2 break-words text-xs text-gray-500 dark:text-gray-400">{{ $plan->description ?: 'No description.' }}</p>
                        </div>
                        <span class="mg-badge shrink-0">{{ $plan->registered_students_count }} students</span>
                    </div>

                    <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Teacher</dt>
                            <dd class="mt-1 break-words font-semibold">{{ $plan->teacher?->name ?? '-' }}</dd>
                        </div>
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Price</dt>
                            <dd class="mt-1 font-semibold">{{ $plan->currency ?? 'MYR' }} {{ number_format($plan->price ?? 0, 2) }}</dd>
                        </div>
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Sessions</dt>
                            <dd class="mt-1 font-semibold">{{ $plan->sessions_count ?? $plan->sessions()->count() }}</dd>
                        </div>
                        <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Recurring</dt>
                            <dd class="mt-1 font-semibold">{{ $plan->is_recurring ? 'Yes · '.$freqLabel : 'No' }}</dd>
                        </div>
                        <div class="col-span-2 rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                            <dt class="text-xs font-bold uppercase text-gray-500">Until</dt>
                            <dd class="mt-1 font-semibold">{{ optional($plan->until_date)->format('Y-m-d') ?: '-' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <a href="{{ route('admin.plans.show', $plan->id) }}" class="mg-btn-secondary"><i class="bx bx-show"></i> View</a>
                        <a href="{{ route('admin.plans.edit', $plan->id) }}" class="mg-btn-secondary"><i class="bx bx-edit"></i> Edit</a>
                        <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan? This will also remove its sessions.')" class="col-span-2">
                            @csrf @method('DELETE')
                            <button type="submit" class="mg-btn-danger w-full"><i class="bx bx-trash"></i> Remove</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="mg-card p-8 text-center text-sm text-gray-500 dark:text-gray-400">No plans found.</div>
            @endforelse
        </div>

        {{-- Desktop table --}}
        <div class="hidden overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800 md:block">
            <div class="max-h-[70vh] overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-gray-700/40">
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
                                <td class="px-4 py-4"><div class="font-semibold text-gray-900 dark:text-white">{{ $plan->name }}</div><div class="line-clamp-1 text-xs text-gray-500 dark:text-gray-400">{{ $plan->description ?? '' }}</div></td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->teacher?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->currency ?? 'MYR' }} {{ number_format($plan->price ?? 0, 2) }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $plan->sessions_count ?? $plan->sessions()->count() }}</td>
                                <td class="px-4 py-4"><span class="mg-badge">{{ $plan->registered_students_count }}</span></td>
                                <td class="px-4 py-4">@if($plan->is_recurring)<span class="mg-badge">Yes • {{ $freqLabel }}</span>@else<span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">No</span>@endif</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ optional($plan->until_date)->format('Y-m-d') ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-right">
                                    <a href="{{ route('admin.plans.show', $plan->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-show"></i> View</a>
                                    <a href="{{ route('admin.plans.edit', $plan->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-edit"></i> Edit</a>
                                    <form method="POST" action="{{ route('admin.plans.destroy', $plan->id) }}" onsubmit="return confirm('Delete this plan? This will also remove its sessions.')" class="inline">@csrf @method('DELETE')<button type="submit" class="mg-btn-danger"><i class="bx bx-trash"></i> Remove</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No plans found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 min-w-0 overflow-x-auto">{{ $plans->links() }}</div>
    </div>
</x-app-layout>
