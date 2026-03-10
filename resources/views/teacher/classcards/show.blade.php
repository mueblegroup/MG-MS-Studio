<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $classCard->name }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Class Card Details
                </p>
            </div>

            <a href="{{ route('teacher.classcards.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Total Classes</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $classCard->total_classes }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Validity</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $classCard->validity_weeks }} weeks</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                <div class="text-lg font-bold text-gray-900 dark:text-white">RM {{ number_format($classCard->price, 2) }}</div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="text-xs text-gray-500 dark:text-gray-400">Created</div>
                <div class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ optional($classCard->created_at)->format('Y-m-d') ?? '-' }}
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
            <div class="p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-2">Description</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    {{ $classCard->description ?? 'No description provided.' }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <h2 class="font-bold text-gray-900 dark:text-white">Purchased / Assigned Cards</h2>
                <div class="text-xs text-gray-500 dark:text-gray-400">Students holding this card</div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Remaining</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Expires</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Purchased</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($classCard->purchases as $ucc)
                            @php
                                $disabled = (($ucc->classes_remaining ?? 0) <= 0)
                                    || ($ucc->expires_at && now()->gt($ucc->expires_at))
                                    || (($ucc->status ?? 'active') !== 'active');
                            @endphp

                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm text-gray-900 dark:text-white">
                                    {{ $ucc->user->name ?? '-' }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ $ucc->user->email ?? '' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $ucc->classes_remaining }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($ucc->expires_at)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ optional($ucc->created_at)->format('Y-m-d') ?? '-' }}
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    <form method="POST"
                                          action="{{ route('teacher.classcards.usage.mark', $ucc->id) }}"
                                          onsubmit="return confirm('Deduct 1 class for this student?')"
                                          class="inline-flex items-center gap-2">
                                        @csrf

                                        <button type="submit"
                                                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-semibold
                                                       text-white bg-emerald-600 hover:bg-emerald-700 transition
                                                       disabled:opacity-50 disabled:cursor-not-allowed"
                                                @disabled($disabled)>
                                            <i class="bx bx-check-circle"></i> Mark Attendance (-1)
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
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