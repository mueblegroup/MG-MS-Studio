<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Card Purchases</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Assign / manage student class cards.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.classcards.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bx bx-credit-card"></i> Class Cards
                </a>

                <a href="{{ route('admin.classcards.classcard-purchases.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-plus"></i> Assign to Student
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-4">
            <form method="GET" action="{{ route('admin.classcards.classcard-purchases') }}" class="flex flex-col sm:flex-row gap-2">
                <input name="q" value="{{ $search }}" placeholder="Search student or card..."
                       class="flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:border-indigo-500 focus:ring-indigo-500" />
                <button class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    Search
                </button>

                <a href="{{ route('admin.classcards.classcard-purchases') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    Reset
                </a>
                <select name="per_page" onchange="this.form.submit()"
                        class="inline-flex items-center gap-4 px-8 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                    @foreach([10,25,50,100] as $size)
                        <option value="{{ $size }}" @selected($perPage == $size)>{{ $size }} rows</option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Card</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Purchased</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Expires</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($purchases as $p)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $p->user->name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p->user->email ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $p->card->name ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $p->classes_remaining }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($p->purchased_at)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($p->expires_at)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $p->status === 'active'
                                            ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200'
                                            : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ ucfirst($p->status) }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <form method="POST" action="{{ route('admin.classcards.classcard-purchases.destroy', $p) }}"
                                          onsubmit="return confirm('Remove this purchase record?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold
                                                       text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                        
                                        <a href="{{ route('admin.classcards.classcard-purchases.edit', $p) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold
                                                  text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition">
                                            <i class="bx bx-edit"></i> Edit
                                        </a>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">No purchases found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
