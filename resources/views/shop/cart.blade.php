<x-app-layout>
    @php
        $cartCount = (int) $cartModel->items->sum('quantity');
    @endphp

    <div class="min-h-screen w-full max-w-full overflow-x-hidden bg-gray-50/60 px-4 py-6 dark:bg-gray-900 sm:px-6 lg:px-8">
        <div class="mx-auto w-full max-w-7xl">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Your cart</h1>
                        @if($cartCount > 0)
                            <span class="inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-indigo-600 px-2 text-xs font-extrabold text-white">
                                {{ $cartCount > 99 ? '99+' : $cartCount }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Review your selections and quantities before checkout.</p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('shop.index') }}"
                        class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 shadow-sm transition hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:border-indigo-800 dark:hover:bg-indigo-950/30">
                        <i class="bx bx-left-arrow-alt text-base"></i>
                        Continue shopping
                    </a>

                    @if($cartCount > 0)
                        <form method="POST" action="{{ route('shop.cart.clear') }}" onsubmit="return confirm('Remove all items from your cart?')">
                            @csrf
                            @method('DELETE')
                            <button class="inline-flex min-h-10 items-center justify-center gap-2 rounded-xl px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">
                                <i class="bx bx-trash"></i>
                                Clear cart
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div role="status" class="mb-5 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-medium text-green-700 dark:border-green-900/60 dark:bg-green-950/30 dark:text-green-300">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div role="alert" class="mb-5 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-medium text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300">
                    {{ session('error') }}
                </div>
            @endif

            @if($cartCount > 0)
                <div class="grid min-w-0 grid-cols-1 gap-5 lg:grid-cols-[minmax(0,1fr)_340px]">
                    <section class="min-w-0 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="flex items-center justify-between border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:px-5">
                            <div>
                                <h2 class="font-bold text-gray-900 dark:text-white">Selected items</h2>
                                <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ $cartCount }} {{ \Illuminate\Support\Str::plural('item', $cartCount) }} in your cart</p>
                            </div>
                            <i class="bx bx-shopping-bag text-2xl text-indigo-500"></i>
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach($cartModel->items as $item)
                                @php
                                    $line = ((float) $item->unit_price) * ((int) $item->quantity);
                                    $typeLabel = match(class_basename($item->purchasable_type)) {
                                        'ClassSession' => 'Class',
                                        'Plan' => 'Plan',
                                        'ClassCard' => 'Class card',
                                        default => class_basename($item->purchasable_type),
                                    };
                                    $typeIcon = match(class_basename($item->purchasable_type)) {
                                        'ClassSession' => 'bx-calendar',
                                        'Plan' => 'bx-layer',
                                        'ClassCard' => 'bx-card',
                                        default => 'bx-purchase-tag',
                                    };
                                    $meta = $item->meta ?? [];
                                    $label = $meta['label'] ?? ($item->purchasable->name ?? $typeLabel);
                                @endphp

                                <article class="min-w-0 p-4 sm:p-5">
                                    <div class="flex min-w-0 items-start gap-3 sm:gap-4">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                                            <i class="bx {{ $typeIcon }} text-xl"></i>
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <div class="flex min-w-0 flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                <div class="min-w-0">
                                                    <span class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                                        {{ $typeLabel }}
                                                    </span>
                                                    <h3 class="mt-1.5 break-words text-sm font-bold text-gray-900 dark:text-white">{{ $label }}</h3>
                                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ $item->currency ?? 'MYR' }} {{ number_format((float) $item->unit_price, 2) }} each
                                                    </p>
                                                </div>

                                                <div class="shrink-0 text-left sm:text-right">
                                                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Item total</div>
                                                    <div class="mt-0.5 text-base font-extrabold text-gray-900 dark:text-white">
                                                        {{ $item->currency ?? 'MYR' }} {{ number_format($line, 2) }}
                                                    </div>
                                                </div>
                                            </div>

                                            @if(!empty($meta))
                                                <dl class="mt-3 grid min-w-0 grid-cols-1 gap-2 rounded-xl bg-gray-50 p-3 text-xs dark:bg-gray-900/50 sm:grid-cols-2">
                                                    @foreach($meta as $key => $value)
                                                        @if($key !== 'label' && $value !== null && $value !== '' && is_scalar($value))
                                                            <div class="min-w-0">
                                                                <dt class="text-[10px] font-bold uppercase tracking-wide text-gray-400">
                                                                    {{ ucfirst(str_replace('_', ' ', $key)) }}
                                                                </dt>
                                                                <dd class="mt-0.5 break-words font-medium text-gray-700 dark:text-gray-300">{{ $value }}</dd>
                                                            </div>
                                                        @endif
                                                    @endforeach
                                                </dl>
                                            @endif

                                            <div class="mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4 dark:border-gray-700 sm:flex-row sm:items-end sm:justify-between">
                                                <form method="POST" action="{{ route('shop.cart.update', $item->id) }}" class="flex flex-wrap items-end gap-2">
                                                    @csrf
                                                    @method('PATCH')
                                                    <label class="block">
                                                        <span class="mb-1 block text-[10px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Quantity</span>
                                                        <input name="qty" type="number" min="1" max="99" value="{{ $item->quantity }}"
                                                            class="h-9 w-20 rounded-lg border-gray-200 bg-white px-3 py-1.5 text-sm font-semibold text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                                                    </label>
                                                    <button class="inline-flex h-9 items-center justify-center rounded-lg bg-gray-100 px-3 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                                                        Update
                                                    </button>
                                                </form>

                                                <form method="POST" action="{{ route('shop.cart.remove', $item->id) }}" onsubmit="return confirm('Remove this item from your cart?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="inline-flex h-9 items-center justify-center gap-1.5 rounded-lg px-3 text-xs font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/30">
                                                        <i class="bx bx-trash"></i>
                                                        Remove
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>

                    <aside class="min-w-0">
                        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800 lg:sticky lg:top-6">
                            <div class="flex items-center gap-2">
                                <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                                    <i class="bx bx-receipt text-lg"></i>
                                </div>
                                <div>
                                    <h2 class="font-bold text-gray-900 dark:text-white">Order summary</h2>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Review before payment</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3 text-sm">
                                <div class="flex items-center justify-between gap-4 text-gray-600 dark:text-gray-300">
                                    <span>Items</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $cartCount }}</span>
                                </div>
                                <div class="flex items-center justify-between gap-4 text-gray-600 dark:text-gray-300">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $summary['currency'] }} {{ number_format($summary['subtotal'], 2) }}</span>
                                </div>
                            </div>

                            <div class="my-5 border-t border-dashed border-gray-200 dark:border-gray-700"></div>

                            <div class="flex items-end justify-between gap-4">
                                <span class="text-sm font-bold text-gray-900 dark:text-white">Total</span>
                                <span class="text-2xl font-extrabold tracking-tight text-gray-900 dark:text-white">
                                    {{ $summary['currency'] }} {{ number_format($summary['total'], 2) }}
                                </span>
                            </div>

                            @if(auth()->check())
                                <a href="{{ route('shop.checkout') }}"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-900/50">
                                    Continue to checkout
                                    <i class="bx bx-right-arrow-alt text-lg"></i>
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-indigo-600/20 transition hover:bg-indigo-700">
                                    <i class="bx bx-lock"></i>
                                    Login to checkout
                                </a>
                                <p class="mt-2 text-center text-xs text-gray-500 dark:text-gray-400">Sign in with a student account to complete your purchase.</p>
                            @endif

                            <div class="mt-4 flex items-center justify-center gap-1.5 text-[11px] text-gray-400">
                                <i class="bx bx-lock-alt"></i>
                                Secure checkout
                            </div>
                        </div>
                    </aside>
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white px-6 py-16 text-center shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/40 dark:text-indigo-300">
                        <i class="bx bx-cart text-3xl"></i>
                    </div>
                    <h2 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Your cart is empty</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm text-gray-500 dark:text-gray-400">Browse available classes, plans and class cards, then add your selections here.</p>
                    <a href="{{ route('shop.index') }}"
                        class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-sm font-bold text-white shadow transition hover:bg-indigo-700">
                        <i class="bx bx-store"></i>
                        Browse the shop
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
