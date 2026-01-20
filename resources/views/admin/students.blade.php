<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 dark:bg-gray-900 p-6 sm:p-8">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Students
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage students, permissions, and system access.
                </p>
            </div>

            <div class="flex items-center gap-2 bg-transparent dark:bg-transparent p-2">
                <button
                    id="send-mass-email"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bx bx-envelope"></i>
                    Mass Email
                </button>

                <a href="{{ route('admin.students.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-plus"></i>
                    Add Student
                </a>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm
                    border border-gray-200 dark:border-gray-700 overflow-hidden">

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="p-4 text-center">
                                <input type="checkbox" id="select-all"
                                    class="rounded border-gray-300 text-indigo-600
                                           focus:ring-indigo-500 focus:ring-offset-0">
                            </th>
                            <th class="p-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Student
                            </th>
                            <th class="p-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Email
                            </th>
                            <th class="p-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Phone
                            </th>
                            <th class="p-4 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                Actions
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($students as $student)
                            <tr id="row-{{ $student->id }}"
                                class="group hover:bg-indigo-50/40 dark:hover:bg-indigo-900/20 transition">

                                <!-- Checkbox -->
                                <td class="p-4 text-center align-middle">
                                    <input type="checkbox"
                                        value="{{ $student->email }}"
                                        class="student-checkbox rounded border-gray-300
                                               text-indigo-600 focus:ring-indigo-500">
                                </td>

                                <!-- Name -->
                                @php
                                // Simple logic to pick a gradient based on the first letter of the name
                                $firstLetter = strtolower(substr($student->name, 0, 1));
                                $gradient = 'from-indigo-500 to-purple-500'; // Default
                                
                                if (in_array($firstLetter, range('a', 'h'))) $gradient = 'from-emerald-500 to-teal-500';
                                if (in_array($firstLetter, range('i', 'p'))) $gradient = 'from-blue-500 to-indigo-500';
                                if (in_array($firstLetter, range('q', 'z'))) $gradient = 'from-rose-500 to-orange-500';
                                @endphp
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-9 w-9 rounded-full 
                                                    bg-gradient-to-br {{ $gradient }} 
                                                    dark:from-indigo-600 dark:to-purple-600
                                                    text-white flex items-center justify-center 
                                                    text-xs font-semibold shadow-sm
                                                    ring-2 ring-white dark:ring-gray-800">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ $student->name }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="p-4">
                                    <a href="mailto:{{ $student->email }}"
                                       class="flex items-center gap-2 text-sm
                                              text-gray-600 dark:text-gray-400
                                              hover:text-indigo-600 transition">
                                        <i class="bx bx-envelope text-base"></i>
                                        {{ $student->email }}
                                    </a>
                                </td>

                                <!-- Phone -->
                                <td class="p-4">
                                    <a href="https://wa.me/{{ $student->phone_number }}"
                                       target="_blank"
                                       class="flex items-center gap-2 text-sm
                                              text-gray-600 dark:text-gray-400
                                              hover:text-green-600 transition">
                                        <i class="bx bxl-whatsapp text-base"></i>
                                        {{ $student->phone_number }}
                                    </a>
                                </td>

                                <!-- Actions -->
                                <td class="p-4 text-right">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2">
                                    <form action="{{ route('admin.students.destroy', $student->id) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to remove this student?')"
                                        class="inline-block">
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
                                <td colspan="5"
                                    class="p-10 text-center text-sm text-gray-500">
                                    No students found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('send-mass-email').addEventListener('click', () => {
            const emails = [...document.querySelectorAll('.student-checkbox:checked')]
                .map(cb => cb.value);

            if (!emails.length) {
                alert('Please select at least one student.');
                return;
            }

            window.location.href = `mailto:${emails.join(',')}`;
        });

        document.getElementById('select-all').addEventListener('change', e => {
            document.querySelectorAll('.student-checkbox')
                .forEach(cb => cb.checked = e.target.checked);
        });
    </script>
    @endpush
</x-app-layout>
