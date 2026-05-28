<x-app-layout>
    <x-slot name="header">
        My Class Cards
    </x-slot>

    <div class="space-y-6">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-extrabold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Total Cards</div>
                <div class="mt-2 text-3xl font-extrabold text-[#171717] dark:text-white">{{ $summary['total_cards'] }}</div>
            </div>

            <div class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-extrabold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Active Cards</div>
                <div class="mt-2 text-3xl font-extrabold text-[#171717] dark:text-white">{{ $summary['active_cards'] }}</div>
            </div>

            <div class="rounded-3xl border border-[#eadfce] bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="text-xs font-extrabold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Remaining Classes</div>
                <div class="mt-2 text-3xl font-extrabold text-[#171717] dark:text-white">{{ $summary['total_remaining_classes'] }}</div>
            </div>
        </div>

        @if($activeCards->isNotEmpty())
            <section class="space-y-4">
                <div>
                    <h3 class="text-lg font-extrabold text-[#171717] dark:text-white">Active Class Cards</h3>
                    <p class="text-sm font-medium text-[#7a6a59] dark:text-gray-400">View your remaining lessons, usage, and expiry date.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2 xl:grid-cols-3">
                    @foreach($activeCards as $card)
                        @php
                            $classCard = $card->classCard;
                            $totalClasses = max(1, (int) ($classCard?->total_classes ?? ($card->classes_remaining + $card->usages->count())));
                            $remaining = max(0, (int) $card->classes_remaining);
                            $used = max(0, $totalClasses - $remaining);
                            $percentRemaining = min(100, max(0, ($remaining / $totalClasses) * 100));
                            $daysLeft = $card->expires_at ? now()->startOfDay()->diffInDays($card->expires_at->copy()->startOfDay(), false) : null;
                        @endphp

                        <div class="overflow-hidden rounded-3xl border border-[#eadfce] bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                            <div class="bg-gradient-to-br from-[#31261d] to-[#d97706] p-5 text-white">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="inline-flex rounded-full bg-white/20 px-3 py-1 text-[11px] font-extrabold uppercase tracking-wide">Active</div>
                                        <h4 class="mt-4 truncate text-xl font-black">{{ $classCard?->name ?? 'Class Card' }}</h4>
                                        <p class="mt-1 text-xs font-bold text-white/75">ID: {{ str_pad((string) $card->id, 5, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white/20">
                                        <i class="bx bx-card text-2xl"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl bg-[#fff8ee] p-4 dark:bg-gray-950">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Remaining</div>
                                        <div class="mt-1 text-3xl font-black text-[#171717] dark:text-white">{{ $remaining }}</div>
                                    </div>
                                    <div class="rounded-2xl bg-[#fff8ee] p-4 dark:bg-gray-950">
                                        <div class="text-[11px] font-extrabold uppercase tracking-wide text-[#9a8c7d] dark:text-gray-500">Used</div>
                                        <div class="mt-1 text-3xl font-black text-[#171717] dark:text-white">{{ $used }}</div>
                                    </div>
                                </div>

                                <div class="mt-5">
                                    <div class="mb-2 flex justify-between text-xs font-bold text-[#7a6a59] dark:text-gray-400">
                                        <span>{{ $remaining }} of {{ $totalClasses }} classes left</span>
                                        <span>{{ number_format($percentRemaining, 0) }}%</span>
                                    </div>
                                    <div class="h-3 overflow-hidden rounded-full bg-[#f0e4d4] dark:bg-gray-800">
                                        <div class="h-full rounded-full bg-[#d97706]" style="width: {{ $percentRemaining }}%"></div>
                                    </div>
                                </div>

                                <div class="mt-5 space-y-3 rounded-2xl border border-[#f0e4d4] p-4 dark:border-gray-800">
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-bold text-[#7a6a59] dark:text-gray-400">Purchased</span>
                                        <span class="font-extrabold text-[#171717] dark:text-white">{{ $card->purchased_at?->format('d M Y') ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-bold text-[#7a6a59] dark:text-gray-400">Expires</span>
                                        <span class="font-extrabold text-[#171717] dark:text-white">{{ $card->expires_at?->format('d M Y') ?? '-' }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3 text-sm">
                                        <span class="font-bold text-[#7a6a59] dark:text-gray-400">Time Left</span>
                                        <span class="font-extrabold text-[#171717] dark:text-white">
                                            @if($daysLeft === null)
                                                No expiry
                                            @elseif($daysLeft < 0)
                                                Expired
                                            @elseif($daysLeft === 0)
                                                Expires today
                                            @else
                                                {{ $daysLeft }} days
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($expiredCards->isNotEmpty())
            <section class="space-y-4">
                <h3 class="text-lg font-extrabold text-[#171717] dark:text-white">Past Cards</h3>

                <div class="overflow-hidden rounded-3xl border border-[#eadfce] bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-[#f0e4d4] dark:divide-gray-800">
                            <thead class="bg-[#fff8ee] dark:bg-gray-950/40">
                                <tr>
                                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#7a6a59] dark:text-gray-400">Card</th>
                                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#7a6a59] dark:text-gray-400">Remaining</th>
                                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#7a6a59] dark:text-gray-400">Purchased</th>
                                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#7a6a59] dark:text-gray-400">Expiry</th>
                                    <th class="px-5 py-3 text-left text-xs font-extrabold uppercase tracking-wide text-[#7a6a59] dark:text-gray-400">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#f0e4d4] dark:divide-gray-800">
                                @foreach($expiredCards as $card)
                                    <tr>
                                        <td class="px-5 py-4 text-sm font-extrabold text-[#171717] dark:text-white">{{ $card->classCard?->name ?? 'Class Card' }}</td>
                                        <td class="px-5 py-4 text-sm font-bold text-[#7a6a59] dark:text-gray-400">{{ $card->classes_remaining }}</td>
                                        <td class="px-5 py-4 text-sm font-bold text-[#7a6a59] dark:text-gray-400">{{ $card->purchased_at?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-5 py-4 text-sm font-bold text-[#7a6a59] dark:text-gray-400">{{ $card->expires_at?->format('d M Y') ?? '-' }}</td>
                                        <td class="px-5 py-4">
                                            <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-extrabold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                {{ ucfirst($card->status ?? 'inactive') }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        @endif

        @if($classCards->isEmpty())
            <div class="rounded-3xl border border-dashed border-[#d6c5ad] bg-white p-10 text-center shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-[#fff3df] text-[#d97706] dark:bg-amber-950/40 dark:text-amber-300">
                    <i class="bx bx-card text-3xl"></i>
                </div>
                <h3 class="mt-4 text-xl font-extrabold text-[#171717] dark:text-white">No class cards yet</h3>
                <p class="mt-2 text-sm font-medium text-[#7a6a59] dark:text-gray-400">Purchased class cards will appear here with remaining classes and expiry details.</p>
                <a href="{{ url('/shop') }}" class="mt-6 inline-flex items-center justify-center gap-2 rounded-2xl bg-[#d97706] px-5 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-[#b45309]">
                    Browse Shop
                    <i class="bx bx-store text-lg"></i>
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
