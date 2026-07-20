<x-app-layout>
    @php
        $canPurchase = auth()->check() && auth()->user()->role === 'student';
        $cartCount = 0;

        if ($canPurchase) {
            try {
                $cartCount = app(\App\Services\CartService::class)->currentCartItemCount();
            } catch (\Throwable $e) {
                $cartCount = 0;
            }
        }
    @endphp

    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-gray-50/60 p-4 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex min-w-0 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Shop</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Browse classes, plans, and class cards.
                    @if($canPurchase)
                        Add items to your cart, then checkout.
                    @endif
                </p>
            </div>

            <div class="grid w-full min-w-0 grid-cols-1 gap-2 sm:flex sm:w-auto sm:items-center">
                <form method="GET" action="{{ route('shop.index') }}" class="grid min-w-0 grid-cols-[minmax(0,1fr)_auto] items-center gap-2 sm:flex">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input name="q" value="{{ $q }}" placeholder="Search…"
                        class="min-w-0 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white sm:w-64
                               focus:border-indigo-500 focus:ring-indigo-500" />
                    <button class="shrink-0 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                        Search
                    </button>
                </form>

                @if($canPurchase)
                    <a href="{{ route('shop.cart.index') }}"
                       class="relative inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-indigo-700">
                        <i class="bx bx-cart"></i>
                        Cart
                        @if($cartCount > 0)
                            <span class="ml-1 inline-flex h-[22px] min-w-[22px] items-center justify-center rounded-full bg-white px-1 text-[11px] font-extrabold text-[#d97706]">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </a>
                @endif
            </div>
        </div>

        @unless($canPurchase)
            <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-200">
                This shop is view-only for administrator and teacher accounts. Purchases can only be made using a student account.
            </div>
        @endunless

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-5 flex max-w-full gap-2 overflow-x-auto pb-1">
            @php
                $tabs = [
                    'classes' => ['label' => 'Classes', 'icon' => 'bx-calendar'],
                    'plans' => ['label' => 'Plans', 'icon' => 'bx-layer'],
                    'classcards' => ['label' => 'Class Cards', 'icon' => 'bx-card'],
                ];
            @endphp

            @foreach($tabs as $key => $t)
                <a href="{{ route('shop.index', ['tab' => $key, 'q' => $q]) }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold transition
                          {{ $tab === $key
                              ? 'bg-indigo-600 text-white shadow'
                              : 'border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                    <i class="bx {{ $t['icon'] }}"></i> {{ $t['label'] }}
                </a>
            @endforeach
        </div>

        <div class="grid w-full min-w-0 grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @if($tab === 'classes')
                @forelse($classes as $s)
                    @php
                        $c = $s->classModel;
                        $teacher = $c?->teacher;
                        $date = optional($s->start_time)->format('Y-m-d');
                        $time = optional($s->start_time)->format('H:i') . ' - ' . optional($s->end_time)->format('H:i');
                        $price = $c?->price ?? 0;
                    @endphp

                    <div class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <div class="min-w-0">
                                <div class="break-words text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $c?->name ?? 'Class' }}
                                </div>
                                <div class="mt-1 line-clamp-2 break-words text-xs text-gray-500 dark:text-gray-400">
                                    {{ $c?->description ?? '—' }}
                                </div>
                            </div>

                            <div class="shrink-0 text-left sm:text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    RM {{ number_format($price, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 grid min-w-0 grid-cols-1 gap-3 text-xs text-gray-600 dark:text-gray-300 sm:grid-cols-2">
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bx bx-calendar shrink-0 text-gray-400"></i>
                                <span class="break-words">{{ $date }}</span>
                            </div>
                            <div class="flex min-w-0 items-center gap-2">
                                <i class="bx bx-time shrink-0 text-gray-400"></i>
                                <span class="break-words">{{ $time }}</span>
                            </div>
                            <div class="flex min-w-0 items-start gap-2 sm:col-span-2">
                                <i class="bx bx-user mt-0.5 shrink-0 text-gray-400"></i>
                                <span class="min-w-0 break-words">{{ $teacher?->name ?? '-' }} ({{ $teacher?->email ?? '-' }})</span>
                            </div>
                            <div class="flex min-w-0 items-start gap-2 sm:col-span-2">
                                <i class="bx bx-map mt-0.5 shrink-0 text-gray-400"></i>
                                <span class="min-w-0 break-words">{{ $s->venue_name ?: '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold
                                {{ ($c?->type ?? 'single') === 'recurring'
                                    ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                                {{ ucfirst($c?->type ?? 'single') }}
                            </span>

                            @if($canPurchase)
                                <form method="POST" action="{{ route('shop.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="class_session">
                                    <input type="hidden" name="id" value="{{ $s->id }}">
                                    <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-indigo-700">
                                        <i class="bx bx-cart-add"></i> Add
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No classes found.
                    </div>
                @endforelse

                <div class="col-span-full min-w-0 overflow-x-auto">
                    {{ $classes->links() }}
                </div>
            @endif

            @if($tab === 'plans')
                @forelse($plans as $p)
                    <div class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <div class="min-w-0">
                                <div class="break-words text-sm font-bold text-gray-900 dark:text-white">{{ $p->name }}</div>
                                <div class="mt-1 line-clamp-2 break-words text-xs text-gray-500 dark:text-gray-400">
                                    {{ $p->description ?: '—' }}
                                </div>
                            </div>
                            <div class="shrink-0 text-left sm:text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $p->currency ?? 'MYR' }} {{ number_format($p->price ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 flex min-w-0 flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                            <div class="min-w-0 rounded-xl bg-gray-100 p-3 dark:bg-gray-700 sm:flex-1">
                                <span class="block text-xs text-gray-500 dark:text-gray-400">
                                    Sessions: {{ $p->sessions->count() }}
                                </span>
                                <span class="mt-1 block break-words text-xs text-gray-500 dark:text-gray-400">
                                    Dates: {{ $p->sessions->pluck('start_time')->map(fn($d) => $d->format('d M'))->implode(', ') ?: 'No dates' }}
                                </span>
                            </div>

                            @if($canPurchase)
                                <form method="POST" action="{{ route('shop.cart.add') }}" class="shrink-0">
                                    @csrf
                                    <input type="hidden" name="type" value="plan">
                                    <input type="hidden" name="id" value="{{ $p->id }}">
                                    <button class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-indigo-700 sm:w-auto">
                                        <i class="bx bx-cart-add"></i> Add
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No plans found.
                    </div>
                @endforelse

                <div class="col-span-full min-w-0 overflow-x-auto">
                    {{ $plans->links() }}
                </div>
            @endif

            @if($tab === 'classcards')
                @forelse($classcards as $card)
                    <div class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800 sm:p-5">
                        <div class="flex min-w-0 flex-col gap-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                            <div class="min-w-0">
                                <div class="break-words text-sm font-bold text-gray-900 dark:text-white">{{ $card->name }}</div>
                                <div class="mt-1 break-words text-xs text-gray-500 dark:text-gray-400">
                                    {{ $card->total_classes }} classes • {{ $card->validity_weeks }} weeks validity
                                </div>
                            </div>
                            <div class="shrink-0 text-left sm:text-right">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Price</div>
                                <div class="text-lg font-bold text-gray-900 dark:text-white">
                                    RM {{ number_format($card->price ?? 0, 2) }}
                                </div>
                            </div>
                        </div>

                        @if($canPurchase)
                            <div class="mt-4 flex items-center justify-end">
                                <form method="POST" action="{{ route('shop.cart.add') }}">
                                    @csrf
                                    <input type="hidden" name="type" value="class_card">
                                    <input type="hidden" name="id" value="{{ $card->id }}">
                                    <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow transition hover:bg-indigo-700">
                                        <i class="bx bx-cart-add"></i> Add
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full p-10 text-center text-sm text-gray-500 dark:text-gray-400">
                        No class cards found.
                    </div>
                @endforelse

                <div class="col-span-full min-w-0 overflow-x-auto">
                    {{ $classcards->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
