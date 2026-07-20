<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mg-title">Classes</h1>
                    <p class="mg-subtitle mt-1">View class sessions and current student registrations.</p>
                </div>
                <div class="grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                    <a href="{{ route('admin.class-assignments.index') }}" class="mg-btn-secondary"><i class="bx bx-user-plus"></i> Assign Class</a>
                    <a href="{{ route('admin.classes.create') }}" class="mg-btn-primary"><i class="bx bx-plus"></i> Add Class</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.classes') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-[#eadfce] bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:grid-cols-[1fr_auto]">
                <input name="q" value="{{ $search }}" placeholder="Search class name, description, teacher..." class="mg-input min-w-0" />
                <div class="grid grid-cols-1 gap-2 sm:flex sm:flex-wrap">
                    <button type="submit" class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
                    <a href="{{ route('admin.classes') }}" class="mg-btn-secondary"><i class="bx bx-reset"></i> Reset</a>
                    <select name="per_page" onchange="this.form.submit()" class="mg-select">
                        @foreach([10,25,50,100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>{{ $size }} rows</option>
                        @endforeach
                    </select>
                </div>
            </form>

            {{-- Mobile cards. The desktop table component is intentionally hidden below md. --}}
            <div class="space-y-3 md:hidden">
                @forelse($sessions as $session)
                    @php($class = $session->classModel)
                    <article class="mg-card min-w-0 p-4">
                        <div class="flex min-w-0 items-start justify-between gap-3">
                            <div class="min-w-0">
                                <h2 class="break-words font-bold text-[#171717] dark:text-white">{{ $class->name ?? '-' }}</h2>
                                <p class="mt-1 line-clamp-2 break-words text-xs text-[#6b5f52] dark:text-gray-400">{{ $class->description ?: 'No description.' }}</p>
                            </div>
                            <span class="mg-badge shrink-0">{{ $class?->registered_students_count ?? 0 }} students</span>
                        </div>

                        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                            <div class="col-span-2 rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Teacher</dt>
                                <dd class="mt-1 break-words font-semibold text-[#31261d] dark:text-gray-200">{{ $class?->teacher?->name ?? '-' }}</dd>
                                <dd class="break-words text-xs text-[#6b5f52] dark:text-gray-400">{{ $class?->teacher?->email ?? '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Date</dt>
                                <dd class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">{{ optional($session->start_time)->format('Y-m-d') ?: '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Time</dt>
                                <dd class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">{{ optional($session->start_time)->format('H:i') ?: '-' }} - {{ optional($session->end_time)->format('H:i') ?: '-' }}</dd>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Capacity</dt>
                                <dd class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">{{ $session->capacity ?? ($class->capacity ?? '-') }}</dd>
                            </div>
                            <div class="rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Price</dt>
                                <dd class="mt-1 font-semibold text-[#31261d] dark:text-gray-200">RM {{ number_format($class->price ?? 0, 2) }}</dd>
                            </div>
                            <div class="col-span-2 rounded-xl bg-[#fffaf3] p-3 dark:bg-gray-800">
                                <dt class="text-xs font-bold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Type</dt>
                                <dd class="mt-1"><span class="mg-badge">{{ ucfirst($class->type ?? 'single') }}</span></dd>
                            </div>
                        </dl>

                        <div class="mt-4 grid grid-cols-2 gap-2">
                            <a href="{{ route('admin.classes.edit', $session->id) }}" class="mg-btn-secondary"><i class="bx bx-edit"></i> Edit</a>
                            <a href="{{ route('admin.classes.attendance', $session->id) }}" class="mg-btn-secondary"><i class="bx bx-check-square"></i> Attendance</a>
                            <form method="POST" action="{{ route('admin.classes.destroy', $session->id) }}" onsubmit="return confirm('Remove this session?')" class="col-span-2">
                                @csrf @method('DELETE')
                                <button type="submit" class="mg-btn-danger w-full"><i class="bx bx-trash"></i> Remove</button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="mg-card p-8 text-center text-sm text-[#6b5f52] dark:text-gray-400">No class sessions found.</div>
                @endforelse
            </div>

            {{-- Desktop/tablet table --}}
            <div class="mg-table-wrap">
                <div class="mg-table-scroll">
                    <table class="min-w-full table-auto border-collapse">
                        <thead class="bg-[#fffaf3] dark:bg-gray-800">
                            <tr>
                                <th class="mg-th">Class</th>
                                <th class="mg-th">Teacher</th>
                                <th class="mg-th">Date & Time</th>
                                <th class="mg-th">Capacity</th>
                                <th class="mg-th">Registered Students</th>
                                <th class="mg-th">Price</th>
                                <th class="mg-th">Type</th>
                                <th class="mg-th text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#f0e5d4] dark:divide-gray-800">
                            @forelse($sessions as $session)
                                @php($class = $session->classModel)
                                <tr class="hover:bg-[#fffaf3] dark:hover:bg-gray-800/70">
                                    <td class="mg-td"><div class="font-bold">{{ $class->name ?? '-' }}</div><div class="text-xs text-[#6b5f52]">{{ $class->description ?? '' }}</div></td>
                                    <td class="mg-td"><div class="font-semibold">{{ $class?->teacher?->name ?? '-' }}</div><div class="text-xs text-[#6b5f52]">{{ $class?->teacher?->email ?? '-' }}</div></td>
                                    <td class="mg-td whitespace-nowrap">{{ optional($session->start_time)->format('Y-m-d') }}<br><span class="text-xs">{{ optional($session->start_time)->format('H:i') }} - {{ optional($session->end_time)->format('H:i') }}</span></td>
                                    <td class="mg-td">{{ $session->capacity ?? ($class->capacity ?? '-') }}</td>
                                    <td class="mg-td"><span class="mg-badge">{{ $class?->registered_students_count ?? 0 }}</span></td>
                                    <td class="mg-td whitespace-nowrap">RM {{ number_format($class->price ?? 0, 2) }}</td>
                                    <td class="mg-td"><span class="mg-badge">{{ ucfirst($class->type ?? 'single') }}</span></td>
                                    <td class="mg-td">
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <a href="{{ route('admin.classes.edit', $session->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-edit"></i> Edit</a>
                                            <a href="{{ route('admin.classes.attendance', $session->id) }}" class="mg-btn-secondary min-h-9 px-3 py-1.5"><i class="bx bx-check-square"></i> Attendance</a>
                                            <form method="POST" action="{{ route('admin.classes.destroy', $session->id) }}" onsubmit="return confirm('Remove this session?')">@csrf @method('DELETE')<button type="submit" class="mg-btn-danger"><i class="bx bx-trash"></i> Remove</button></form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-[#6b5f52]">No class sessions found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="max-w-full overflow-x-auto">{{ $sessions->links() }}</div>
        </div>
    </div>
</x-app-layout>
