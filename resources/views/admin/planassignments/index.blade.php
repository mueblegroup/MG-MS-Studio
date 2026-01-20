<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Plan Assignments</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Assign plans to students (admin). Later, this can be used for purchases too.
                </p>
            </div>
            <div class="flex items-center gap-2">
            <a href="{{ route('admin.plans') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-left-arrow-alt"></i> Back
            </a>
            <a href="{{ route('admin.planassignments.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                <i class="bx bx-plus"></i> Assign Plan
            </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <form method="GET" action="{{ route('admin.planassignments.index') }}" class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1">
                    <input name="q" value="{{ $search }}"
                           placeholder="Search student or plan..."
                           class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                  focus:border-indigo-500 focus:ring-indigo-500" />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                                   text-xs font-semibold text-gray-700 dark:text-gray-300
                                   bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Search
                    </button>

                    <a href="{{ route('admin.planassignments.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg
                              text-xs font-semibold text-gray-700 dark:text-gray-300
                              bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        Reset
                    </a>

                    <select name="per_page" onchange="this.form.submit()"
                            class="rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-xs font-semibold">
                        @foreach([10,25,50,100] as $size)
                            <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} rows</option>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Active</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Start</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">End</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($assignments as $a)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $a->user->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $a->user->email ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $a->plan->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $a->plan->currency ?? 'MYR' }} {{ number_format($a->plan->price ?? 0, 2) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $a->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $a->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($a->starts_on)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($a->ends_on)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <form method="POST"
                                          action="{{ route('admin.planassignments.destroy', $a) }}"
                                          onsubmit="return confirm('Remove this plan assignment?')"
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
                                    No plan assignments found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $assignments->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
