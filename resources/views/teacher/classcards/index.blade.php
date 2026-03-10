<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Class Cards</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">View available class cards and mark attendance for students.</p>
            </div>

            <a href="{{ route('teacher.dashboard') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                      hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse($classCards as $card)
                <a href="{{ route('teacher.classcards.show', $card) }}"
                   class="group bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700
                          hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-700 transition">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-bold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition">
                                {{ $card->name }}
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 line-clamp-2">
                                {{ $card->description ?? '—' }}
                            </div>
                        </div>
                        <div class="p-2 rounded-xl bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-300">
                            <i class="bx bx-id-card text-xl"></i>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-2 text-xs">
                        <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/40">
                            <div class="text-gray-500 dark:text-gray-400">Classes</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $card->total_classes }}</div>
                        </div>
                        <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/40">
                            <div class="text-gray-500 dark:text-gray-400">Validity</div>
                            <div class="font-semibold text-gray-900 dark:text-white">{{ $card->validity_weeks }}w</div>
                        </div>
                        <div class="p-2 rounded-xl bg-gray-50 dark:bg-gray-700/40">
                            <div class="text-gray-500 dark:text-gray-400">Price</div>
                            <div class="font-semibold text-gray-900 dark:text-white">RM {{ number_format($card->price, 2) }}</div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full text-sm text-gray-500 dark:text-gray-400 text-center py-10">
                    No class cards found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $classCards->links() }}
        </div>

    </div>
</x-app-layout>