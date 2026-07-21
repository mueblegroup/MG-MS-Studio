<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mg-title">Classes</h1>
                    <p class="mg-subtitle mt-1">One row per class. Open a class to manage its sessions and attending students.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.class-assignments.index') }}" class="mg-btn-secondary"><i class="bx bx-user-plus"></i> Assign Class</a>
                    <a href="{{ route('admin.classes.create') }}" class="mg-btn-primary"><i class="bx bx-plus"></i> Add Class</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.classes') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-[#eadfce] bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:grid-cols-[minmax(0,1fr)_220px_auto]">
                <input name="q" value="{{ $search }}" placeholder="Search class name, description, type or teacher..." class="mg-input" />
                <select name="class_type" class="mg-select" onchange="this.form.submit()">
                    <option value="all" @selected(($classType ?? 'all') === 'all')>All class types</option>
                    <option value="single" @selected(($classType ?? 'all') === 'single')>One-time classes</option>
                    <option value="recurring" @selected(($classType ?? 'all') === 'recurring')>Repeating classes</option>
                    <option value="subscription" @selected(($classType ?? 'all') === 'subscription')>Subscription classes</option>
                </select>
                <div class="flex flex-wrap gap-2">
                    <button type="submit" class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
                    <a href="{{ route('admin.classes') }}" class="mg-btn-secondary"><i class="bx bx-reset"></i> Reset</a>
                    <select name="per_page" onchange="this.form.submit()" class="mg-select">
                        @foreach([10,25,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} rows</option>
                        @endforeach
                    </select>
                </div>
            </form>

            <div class="grid grid-cols-1 gap-4 md:hidden">
                @forelse($classes as $class)
                    <article class="mg-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="break-words font-extrabold text-gray-950 dark:text-white">{{ $class->name }}</h2>
                                <p class="mt-1 text-xs text-[#6b5f52] dark:text-gray-400">{{ $class->teacher?->name ?? 'No teacher' }}</p>
                            </div>
                            <span class="mg-badge shrink-0">{{ ucfirst($class->type) }}</span>
                        </div>
                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div><dt class="text-xs font-bold text-gray-500">Sessions</dt><dd class="font-extrabold">{{ $class->sessions_count }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-500">Subscribers</dt><dd class="font-extrabold">{{ $class->active_subscriptions_count }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-500">Price</dt><dd class="font-extrabold">RM {{ number_format($class->price, 2) }}</dd></div>
                            <div><dt class="text-xs font-bold text-gray-500">Billing</dt><dd class="font-extrabold">{{ $class->billing_interval ? ucfirst($class->billing_interval) : 'One-time' }}</dd></div>
                        </dl>
                        <a href="{{ route('admin.subscription-classes.show', $class->id) }}" class="mg-btn-primary mt-4 w-full"><i class="bx bx-show"></i> Open Class</a>
                    </article>
                @empty
                    <div class="mg-card p-8 text-center text-sm text-gray-500">No classes found.</div>
                @endforelse
            </div>

            <div class="mg-table-wrap">
                <div class="mg-table-scroll">
                    <table class="min-w-full table-auto border-collapse">
                        <thead class="bg-[#fffaf3] dark:bg-gray-800">
                            <tr>
                                <th class="mg-th">Class</th>
                                <th class="mg-th">Teacher</th>
                                <th class="mg-th">Type</th>
                                <th class="mg-th">Sessions</th>
                                <th class="mg-th">Subscribers</th>
                                <th class="mg-th">Schedule Range</th>
                                <th class="mg-th">Price / Billing</th>
                                <th class="mg-th text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e5d4] dark:divide-gray-800">
                            @forelse($classes as $class)
                                <tr class="hover:bg-[#fffaf3] dark:hover:bg-gray-800/70">
                                    <td class="mg-td"><div class="font-bold">{{ $class->name }}</div><div class="max-w-xs truncate text-xs text-[#6b5f52]">{{ $class->description }}</div></td>
                                    <td class="mg-td"><div class="font-semibold">{{ $class->teacher?->name ?? '-' }}</div><div class="text-xs text-[#6b5f52]">{{ $class->teacher?->email ?? '-' }}</div></td>
                                    <td class="mg-td"><span class="mg-badge">{{ ucfirst($class->type) }}</span></td>
                                    <td class="mg-td">{{ $class->sessions_count }}</td>
                                    <td class="mg-td"><span class="mg-badge">{{ $class->active_subscriptions_count }}</span></td>
                                    <td class="mg-td text-xs">{{ $class->first_session_at ? \Carbon\Carbon::parse($class->first_session_at)->format('d M Y') : '-' }}<br>{{ $class->last_session_at ? \Carbon\Carbon::parse($class->last_session_at)->format('d M Y') : '-' }}</td>
                                    <td class="mg-td">RM {{ number_format($class->price, 2) }}<br><span class="text-xs text-[#6b5f52]">{{ $class->billing_interval ? ucfirst($class->billing_interval) : 'One-time' }}</span></td>
                                    <td class="mg-td text-right"><a href="{{ route('admin.subscription-classes.show', $class->id) }}" class="mg-btn-primary min-h-9 px-3 py-1.5"><i class="bx bx-show"></i> Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-[#6b5f52]">No classes found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">{{ $classes->links() }}</div>
        </div>
    </div>
</x-app-layout>
