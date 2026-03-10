<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Your Classes</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage your class templates and sessions.</p>
            </div>

            <a href="{{ route('teacher.dashboard') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Class</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($classes as $c)
                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $c->name }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-normal mt-1 line-clamp-1">
                                        {{ $c->description }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $c->type ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $c->price ?? '—' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('teacher.classes.show', $c->id) }}"
                                       class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                              text-white bg-indigo-600 hover:bg-indigo-700 transition">
                                        View Sessions
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No classes found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>