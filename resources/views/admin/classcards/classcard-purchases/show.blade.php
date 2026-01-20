<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $userClassCard->card->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Class Card Details
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.classcards.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                          border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bx bx-arrow-back"></i> Back
                </a>

                <a href="{{ route('admin.classcards.edit', $userClassCard->card) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-edit"></i> Edit
                </a>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Classes</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $userClassCard->card->total_classes }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Validity</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $userClassCard->card->validity_weeks }} weeks
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">
                    RM {{ number_format($userClassCard->card->price, 2) }}
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Created</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $userClassCard->created_at->format('Y-m-d') }}
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-2">
                    Description
                </h2>

                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $userClassCard->card->description ?? 'No description provided.' }}
                </p>
            </div>
        </div>

        {{-- Purchases / Assigned Cards --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 dark:text-white">
                    Purchased / Assigned Cards
                </h2>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    Students holding this card
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Student
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Remaining Classes
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Expires At
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">
                                Purchased On
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($userClassCard->card->purchases as $purchase)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $purchase->user->name ?? '-' }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $purchase->user->email ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $purchase->classes_remaining }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($purchase->expires_at)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $purchase->created_at->format('Y-m-d') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No purchases yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>