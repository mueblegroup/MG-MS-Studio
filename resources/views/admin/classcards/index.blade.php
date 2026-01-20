<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Cards</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create and manage class card products.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.classcards.classcard-purchases') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                          hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Purchases
                </a>

                <a href="{{ route('admin.classcards.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    Add Class Card
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-4">
            <form method="GET" action="{{ route('admin.classcards.index') }}" class="flex flex-col sm:flex-row gap-2">
                <div class="flex-1">
                    <input
                        name="q"
                        value="{{ $search ?? request('q','') }}"
                        placeholder="Search class card name..."
                        class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                            focus:border-indigo-500 focus:ring-indigo-500"
                    />
                </div>

                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                                    text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                <i class="bx bx-search"></i> Search
                    </button>

                    <a href="{{ route('admin.classcards.index') }}"
                        class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold
                                    text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                <i class="bx bx-reset"></i> Reset
                    </a>

                    <select name="per_page" onchange="this.form.submit()"
                        class="inline-flex items-center gap-4 px-8 py-2 rounded-xl
                        text-xs font-semibold text-gray-700 dark:text-gray-300
                        bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
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
                    <thead class="bg-gray-50 dark:bg-gray-700/40 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Credits</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Validity</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($cards as $card)
                            <tr class="px-4 py-4">
                                <td class="px-4 py-4 font-semibold text-gray-900 dark:text-white">{{ $card->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $card->total_classes }}</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $card->validity_weeks }} weeks</td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">RM {{ number_format($card->price, 2) }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                        {{ $card->is_active ? 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                        {{ $card->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                            bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200 px-2">
                                        <a href="{{ route('admin.classcards.show', $card) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-indigo-600 hover:bg-indigo-50
                                              dark:hover:bg-indigo-900/20 transition mr-2">
                                            <i class="bx bx-show"></i> View
                                        </a>

                                    <a href="{{ route('admin.classcards.edit', $card) }}"
                                       class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                              text-xs font-semibold text-indigo-600 hover:bg-indigo-50
                                              dark:hover:bg-indigo-900/20 transition mr-2">
                                        <i class="bx bx-edit"></i> Edit
                                    </a>

                                    <form method="POST" action="{{ route('admin.classcards.destroy', $card) }}"
                                          onsubmit="return confirm('Delete this class card?')"
                                          class="inline">
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
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No class cards found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
                        {{-- Pagination --}}
            <div class="p-4 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    @if(method_exists($cards, 'firstItem') && $cards->total() > 0)
                        Showing <span class="font-semibold">{{ $cards->firstItem() }}</span>
                        to <span class="font-semibold">{{ $cards->lastItem() }}</span>
                        of <span class="font-semibold">{{ $cards->total() }}</span> results
                    @else
                        —
                    @endif
                </div>
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $cards->links() }}
            </div>
            </div>

        </div>
    </div>
</x-app-layout>
