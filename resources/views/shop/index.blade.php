<x-app-layout>
    @php
        $cartCount = 0;

        try {
            $cartCount = app(\App\Services\CartService::class)->currentCartItemCount();
        } catch (\Throwable $e) {
            $cartCount = 0;
        }
    @endphp

    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Shop</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Browse classes, plans, and classcards. Add items to cart, then checkout.
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('shop.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input name="q" value="{{ $q }}" placeholder="Search…"
                        class="w-64 max-w-[60vw] rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white
                               focus:border-indigo-500 focus:ring-indigo-500" />
                    <button class="px-4 py-2 rounded-xl text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                                   border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Search
                    </button>
                </form>

                <a href="{{ route('shop.cart.index') }}"
                   class="relative inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-cart"></i>
                    Cart
                    @if($cartCount > 0)
                        <span class="ml-1 inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full bg-white px-1 text-[11px] font-extrabold text-[#d97706]">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>
            </div>
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

        {{-- Tabs --}}
        <div class="mb-5 flex flex-wrap gap-2 mb-4">
            @php
                $tabs = [
                    'classes' => ['label' => 'Classes', 'icon' => 'bx-calendar'],
                    'plans' => ['label' => 'Plans', 'icon' => 'bx-layer'],
                    'classcards' => ['label' => 'Classcards', 'icon' => 'bx-card'],
                ];
            @endphp

            @foreach($tabs as $key => $t)
                <a href="{{ route('shop.index', ['tab' => $key, 'q' => $q]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold transition
                          {{ $tab === $key
                              ? 'bg-indigo-600 text-white shadow'
                              : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700' }}">
                    <i class="bx {{ $t['icon'] }}"></i> {{ $t['label'] }}
                </a>
            @endforeach
        </div>

        {{-- Content --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @if($tab === 'classes')
                @forelse($classes as $s)
                    @php
                        $c = $s->classModel;
                        $teacher = $c?->teacher;
                        $date = optional($s->start_time)->format('Y-m-d');
                        $time = optional($s->start_time)->format('H:i') . ' - ' . optional($s->end_time)->format('H:i');
                        $price = $c?->price ?? 0;
                    @endphp

                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $c?->name ?? 'Class' }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                    {{ $c?->description ?? '—' }}
                                </div>
                            </div>

                            <div class="text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    RM {{ number_format($price, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-2 gap-3 text-xs text-gray-600 dark:text-gray-300">
                            <div class="flex items-center gap-2">
                                <i class="bx bx-calendar text-gray-400"></i>
                                <span>{{ $date }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="bx bx-time text-gray-400"></i>
                                <span>{{ $time }}</span>
                            </div>
                            <div class="flex items-center gap-2 col-span-2">
                                <i class="bx bx-user text-gray-400"></i>
                                <span>{{ $teacher?->name ?? '-' }} ({{ $teacher?->email ?? '-' }})</span>
                            </div>
                            <div class="flex items-center gap-2 col-span-2">
                                <i class="bx bx-map text-gray-400"></i>
                                <span>{{ $s->venue_name ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ ($c?->type ?? 'single') === 'recurring'
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ ucfirst($c?->type ?? 'single') }}
                            </span>

                            <form method="POST" action="{{ route('shop.cart.add') }}">
                                @csrf
                                <input type="hidden" name="type" value="class_session">
                                <input type="hidden" name="id" value="{{ $s->id }}">
                                <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                    <i class="bx bx-cart-add"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center p-10 text-sm text-gray-500 dark:text-gray-400">
                        No classes found.
                    </div>
                @endforelse

                <div class="col-span-full">
                    {{ $classes->links() }}
                </div>
            @endif

            @if($tab === 'plans')
                @forelse($plans as $p)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $p->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                    {{ $p->description ?: '—' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $p->currency ?? 'MYR' }} {{ number_format($p->price ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-2">
                            <div class="flex flex-col gap-1 bg-gray-100 dark:bg-gray-700 p-2 rounded px-4 mx-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Sessions: {{ $p->sessions->count() }}
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Dates: {{ $p->sessions->pluck('start_time')->map(fn($d) => $d->format('d M'))->implode(', ') ?: 'No dates' }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('shop.cart.add') }}">
                                @csrf
                                <input type="hidden" name="type" value="plan">
                                <input type="hidden" name="id" value="{{ $p->id }}"> 
                                <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                    <i class="bx bx-cart-add"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center p-10 text-sm text-gray-500 dark:text-gray-400">
                        No plans found.
                    </div>
                @endforelse

                <div class="col-span-full">
                    {{ $plans->links() }}
                </div>
            @endif

            @if($tab === 'classcards')
                @forelse($classcards as $card)
                    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $card->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ $card->total_classes }} classes • {{ $card->validity_weeks }} weeks validity
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    RM {{ number_format($card->price ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-end">
                            <form method="POST" action="{{ route('shop.cart.add') }}">
                                @csrf
                                <input type="hidden" name="type" value="class_card">
                                <input type="hidden" name="id" value="{{ $card->id }}">
                                <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                    <i class="bx bx-cart-add"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full text-center p-10 text-sm text-gray-500 dark:text-gray-400">
                        No classcards found.
                    </div>
                @endforelse

                <div class="col-span-full">
                    {{ $classcards->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
