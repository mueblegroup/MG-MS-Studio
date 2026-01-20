<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Assignments</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Assign students to individual class sessions.</p>
            </div>

            <a href="{{ route('admin.class-assignments.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                <i class="bx bx-plus"></i> Assign Student
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <form method="GET" action="{{ route('admin.class-assignments.index') }}" class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1">
                    <input
                        name="q"
                        value="{{ $search }}"
                        placeholder="Search student or class..."
                        class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                               focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold
                               text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700
                               hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Search
                    </button>

                    <a href="{{ route('admin.class-assignments.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold
                              text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700
                              hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Reset
                    </a>

                    <select name="per_page" onchange="this.form.submit()"
                        class="px-4 py-2 rounded-lg text-xs font-semibold
                               text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700
                               hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected(request('per_page', 10) == $size)>
                                {{ $size }} rows
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Class</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Date</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Venue</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($assignments as $a)
                            @php
                                $student = $a->student;
                                $session = $a->session;
                                $class = $session?->classModel;

                                $date = optional($session?->start_time)->format('Y-m-d');
                                $start = optional($session?->start_time)->format('H:i');
                                $end = optional($session?->end_time)->format('H:i');
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $student->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $class->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        Teacher: {{ $class?->teacher?->email ?? '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $date }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $start }} - {{ $end }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $session?->venue_name ?? '-' }}</td>

                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <form method="POST"
                                          action="{{ route('admin.class-assignments.destroy', $a) }}"
                                          onsubmit="return confirm('Remove this assignment?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                                   text-xs font-semibold text-red-600 hover:bg-red-50
                                                   dark:hover:bg-red-900/20 transition">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No assignments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Showing <span class="font-semibold">{{ $assignments->firstItem() }}</span>
                    to <span class="font-semibold">{{ $assignments->lastItem() }}</span>
                    of <span class="font-semibold">{{ $assignments->total() }}</span>
                </div>

                <div>{{ $assignments->links() }}</div>
            </div>
        </div>

    </div>
</x-app-layout>
