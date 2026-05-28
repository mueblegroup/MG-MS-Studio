<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">

            <div class="flex flex-col gap-4 rounded-3xl bg-gradient-to-br from-[#fffaf3] to-white p-4 shadow-sm border border-[#eadfce] dark:from-gray-900 dark:to-gray-950 dark:border-gray-800 sm:p-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2 rounded-full bg-[#fff3df] px-3 py-1 text-xs font-bold text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">
                        <i class="bx bx-book-open"></i>
                        Studio Management
                    </div>

                    <h1 class="mg-title mt-3">Classes</h1>

                    <p class="mg-subtitle mt-1">
                        View, filter, and manage class sessions without breaking mobile layout.
                    </p>
                </div>

                <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:flex-wrap sm:items-center lg:justify-end">
                    <a href="{{ route('admin.class-assignments.index') }}" class="mg-btn-secondary w-full sm:w-auto">
                        <i class="bx bx-user-plus"></i>
                        Assign Class
                    </a>

                    <a href="{{ route('admin.classes.create') }}" class="mg-btn-primary w-full sm:w-auto">
                        <i class="bx bx-plus"></i>
                        Add Class
                    </a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700 dark:border-green-900/50 dark:bg-green-950/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mg-card-soft p-4">
                <form method="GET" action="{{ route('admin.classes') }}" class="grid grid-cols-1 gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                    <div class="min-w-0">
                        <input
                            name="q"
                            value="{{ $search }}"
                            placeholder="Search class name, description, teacher..."
                            class="mg-input"
                        />
                    </div>

                    <div class="grid grid-cols-2 gap-2 sm:flex sm:flex-wrap sm:items-center sm:justify-end">
                        <button type="submit" class="mg-btn-primary">
                            <i class="bx bx-search"></i>
                            Search
                        </button>

                        <a href="{{ route('admin.classes') }}" class="mg-btn-secondary">
                            <i class="bx bx-reset"></i>
                            Reset
                        </a>

                        <select
                            name="per_page"
                            onchange="this.form.submit()"
                            class="mg-select col-span-2 sm:col-span-1"
                        >
                            @foreach([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>
                                    {{ $size }} rows
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            {{-- Mobile Card View --}}
            <div class="space-y-3 md:hidden">
                @forelse($sessions as $session)
                    @php
                        $class = $session->classModel;
                        $teacher = $class?->teacher;
                        $date = optional($session->start_time)->format('Y-m-d');
                        $start = optional($session->start_time)->format('H:i');
                        $end = optional($session->end_time)->format('H:i');
                    @endphp

                    <div class="mg-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-bold text-[#171717] dark:text-white">
                                    {{ $class->name ?? '-' }}
                                </h2>

                                <p class="mt-1 line-clamp-2 text-xs text-[#6b5f52] dark:text-gray-400">
                                    {{ $class->description ?? 'No description available.' }}
                                </p>
                            </div>

                            <span class="mg-badge shrink-0">
                                {{ ucfirst($class->type ?? 'single') }}
                            </span>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-3 text-sm">
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <div class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">
                                    Teacher
                                </div>
                                <div class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">
                                    {{ $teacher->name ?? '-' }}
                                </div>
                                <div class="break-words text-xs text-[#6b5f52] dark:text-gray-400">
                                    {{ $teacher->email ?? '-' }}
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                    <div class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">
                                        Date
                                    </div>
                                    <div class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">
                                        {{ $date ?? '-' }}
                                    </div>
                                </div>

                                <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                    <div class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">
                                        Time
                                    </div>
                                    <div class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">
                                        {{ $start ?? '-' }} - {{ $end ?? '-' }}
                                    </div>
                                </div>

                                <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                    <div class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">
                                        Capacity
                                    </div>
                                    <div class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">
                                        {{ $session->capacity ?? ($class->capacity ?? '-') }}
                                    </div>
                                </div>

                                <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                    <div class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">
                                        Price
                                    </div>
                                    <div class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">
                                        RM {{ number_format($class->price ?? 0, 2) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">
                            <a href="{{ route('admin.classes.edit', $session->id) }}" class="mg-btn-secondary">
                                <i class="bx bx-edit"></i>
                                Edit
                            </a>

                            <a href="{{ route('admin.classes.attendance', $session->id) }}" class="mg-btn-secondary">
                                <i class="bx bx-check-square"></i>
                                Attendance
                            </a>

                            <form
                                method="POST"
                                action="{{ route('admin.classes.destroy', $session->id) }}"
                                onsubmit="return confirm('Remove this session?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="mg-btn-danger w-full border border-red-100 bg-white dark:border-red-900/40 dark:bg-gray-900">
                                    <i class="bx bx-trash"></i>
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="mg-card p-8 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#fff3df] text-[#d97706]">
                            <i class="bx bx-calendar-x text-2xl"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-[#31261d] dark:text-gray-200">
                            No class sessions found.
                        </p>
                    </div>
                @endforelse
            </div>

            {{-- Desktop / Tablet Table View --}}
            <div class="mg-table-wrap">
                <div class="mg-table-scroll">
                    <table class="min-w-full table-auto border-collapse">
                        <thead class="sticky top-0 z-10 bg-[#fffaf3] dark:bg-gray-800">
                            <tr>
                                <th class="mg-th">Class</th>
                                <th class="mg-th">Teacher</th>
                                <th class="mg-th">Date</th>
                                <th class="mg-th">Time</th>
                                <th class="mg-th">Capacity</th>
                                <th class="mg-th">Price</th>
                                <th class="mg-th">Type</th>
                                <th class="mg-th text-right">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#f0e5d4] dark:divide-gray-800">
                            @forelse($sessions as $session)
                                @php
                                    $class = $session->classModel;
                                    $teacher = $class?->teacher;
                                    $date = optional($session->start_time)->format('Y-m-d');
                                    $start = optional($session->start_time)->format('H:i');
                                    $end = optional($session->end_time)->format('H:i');
                                @endphp

                                <tr class="transition hover:bg-[#fffaf3] dark:hover:bg-gray-800/70">
                                    <td class="mg-td">
                                        <div class="max-w-[260px]">
                                            <div class="truncate font-bold text-[#171717] dark:text-white">
                                                {{ $class->name ?? '-' }}
                                            </div>

                                            <div class="line-clamp-1 text-xs text-[#6b5f52] dark:text-gray-400">
                                                {{ $class->description ?? '' }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="mg-td">
                                        <div class="max-w-[220px]">
                                            <div class="truncate font-semibold text-[#31261d] dark:text-gray-200">
                                                {{ $teacher->name ?? '-' }}
                                            </div>

                                            <div class="truncate text-xs text-[#6b5f52] dark:text-gray-400">
                                                {{ $teacher->email ?? '-' }}
                                            </div>
                                        </div>
                                    </td>

                                    <td class="mg-td whitespace-nowrap">
                                        {{ $date ?? '-' }}
                                    </td>

                                    <td class="mg-td whitespace-nowrap">
                                        {{ $start ?? '-' }} - {{ $end ?? '-' }}
                                    </td>

                                    <td class="mg-td whitespace-nowrap">
                                        {{ $session->capacity ?? ($class->capacity ?? '-') }}
                                    </td>

                                    <td class="mg-td whitespace-nowrap font-semibold">
                                        RM {{ number_format($class->price ?? 0, 2) }}
                                    </td>

                                    <td class="mg-td">
                                        <span class="mg-badge">
                                            {{ ucfirst($class->type ?? 'single') }}
                                        </span>
                                    </td>

                                    <td class="mg-td">
                                        <div class="flex flex-wrap items-center justify-end gap-2">
                                            <a href="{{ route('admin.classes.edit', $session->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5">
                                                <i class="bx bx-edit"></i>
                                                Edit
                                            </a>

                                            <a href="{{ route('admin.classes.attendance', $session->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5">
                                                <i class="bx bx-check-square"></i>
                                                Attendance
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('admin.classes.destroy', $session->id) }}"
                                                onsubmit="return confirm('Remove this session?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="mg-btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                    Remove
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-12 text-center text-sm text-[#6b5f52] dark:text-gray-400">
                                        No class sessions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mg-card flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="text-xs text-[#6b5f52] dark:text-gray-400">
                    @if($sessions->total() > 0)
                        Showing
                        <span class="font-bold text-[#31261d] dark:text-gray-200">{{ $sessions->firstItem() }}</span>
                        to
                        <span class="font-bold text-[#31261d] dark:text-gray-200">{{ $sessions->lastItem() }}</span>
                        of
                        <span class="font-bold text-[#31261d] dark:text-gray-200">{{ $sessions->total() }}</span>
                        results
                    @else
                        No results found.
                    @endif
                </div>

                <div class="max-w-full overflow-x-auto">
                    {{ $sessions->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>