<x-app-layout>
    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="mg-title">Classes</h1>
                    <p class="mg-subtitle mt-1">View class sessions and current student registrations.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('admin.class-assignments.index') }}" class="mg-btn-secondary"><i class="bx bx-user-plus"></i> Assign Class</a>
                    <a href="{{ route('admin.classes.create') }}" class="mg-btn-primary"><i class="bx bx-plus"></i> Add Class</a>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.classes') }}" class="grid grid-cols-1 gap-3 rounded-2xl border border-[#eadfce] bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900 lg:grid-cols-[1fr_auto]">
                <input name="q" value="{{ $search }}" placeholder="Search class name, description, teacher..." class="mg-input" />
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
                                            <form method="POST" action="{{ route('admin.classes.destroy', $session->id) }}" onsubmit="return confirm('Remove this session?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="mg-btn-danger"><i class="bx bx-trash"></i> Remove</button>
                                            </form>
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
