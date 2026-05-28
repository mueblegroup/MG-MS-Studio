<x-app-layout>
    @php
        $cartCount = (int) $cartModel->items->sum('quantity');
    @endphp

    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Cart
                    @if($cartCount > 0)
                        <span class="ml-2 inline-flex h-7 min-w-7 items-center justify-center rounded-full bg-[#d97706] px-2 text-xs font-extrabold text-white">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review your items before checkout.</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                          text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                          border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    <i class="bx bx-store"></i> Continue Shopping
                </a>

                @if($cartCount > 0)
                    <form method="POST" action="{{ route('shop.cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                                       text-red-600 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                                       hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                            <i class="bx bx-trash"></i> Clear
                        </button>
                    </form>
                @endif
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

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700">
                    <h2 class="font-bold text-gray-900 dark:text-white">
                        Items
                        @if($cartCount > 0)
                            <span class="ml-2 text-sm font-semibold text-[#d97706]">{{ $cartCount }} total</span>
                        @endif
                    </h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead class="bg-gray-50 dark:bg-gray-700/40">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Type</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Price</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Total</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                        @forelse($cartModel->items as $item)
                            @php
                                $line = ((float)$item->unit_price) * ((int)$item->quantity);

                                $typeLabel = match(class_basename($item->purchasable_type)) {
                                    'ClassSession' => 'Class',
                                    'Plan' => 'Plan',
                                    'ClassCard' => 'Classcard',
                                    default => class_basename($item->purchasable_type),
                                };

                                $meta = $item->meta ?? [];
                                $label = $meta['label'] ?? ($item->purchasable->name ?? $typeLabel);
                            @endphp

                            <tr>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        {{ $label }}
                                    </div>

                                    @if(!empty($meta))
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            @foreach($meta as $k => $v)
                                                @if($v !== null && $v !== '')
                                                    <span class="inline-flex mr-2">
                                                        {{ ucfirst(str_replace('_',' ', $k)) }}: {{ $v }}
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $typeLabel }}</td>

                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $item->currency ?? 'MYR' }} {{ number_format($item->unit_price, 2) }}
                                </td>

                                <td class="px-4 py-4">
                                    <form method="POST" action="{{ route('shop.cart.update', $item->id) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')

                                        <input name="qty" type="number" min="1" max="20" value="{{ $item->quantity }}"
                                            class="w-20 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />

                                        <button class="px-3 py-2 rounded-xl text-xs font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-200
                                                    hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                            Update
                                        </button>
                                    </form>
                                </td>

                                <td class="px-4 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $item->currency ?? 'MYR' }} {{ number_format($line, 2) }}
                                </td>

                                <td class="px-4 py-4 text-right">
                                    <form method="POST" action="{{ route('shop.cart.remove', $item->id) }}" class="inline">
                                        @csrf
                                        @method('DELETE')

                                        <button class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-semibold
                                                    text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            <i class="bx bx-trash"></i> Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                                    Your cart is empty.
                                </td>
                            </tr>
                        @endforelse
                        </tbody>

                    </table>
                </div>
            </div>

            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm sticky top-6">
                    <h2 class="font-bold text-gray-900 dark:text-white">Summary</h2>

                    <div class="mt-4 space-y-2 text-sm mb-6">
                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                            <span>Items</span>
                            <span class="font-semibold text-gray-900 dark:text-white">{{ $cartCount }}</span>
                        </div>

                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                            <span>Subtotal</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ $summary['currency'] }} {{ number_format($summary['subtotal'], 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-gray-600 dark:text-gray-300">
                            <span>Total</span>
                            <span class="font-semibold text-gray-900 dark:text-white">
                                {{ $summary['currency'] }} {{ number_format($summary['total'], 2) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-5">
                        @if(auth()->check())
                            <a href="{{ route('shop.checkout') }}"
                               class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-xl
                                      text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                <i class="bx bx-credit-card"></i> Checkout
                            </a>
                        @else
                            <a href="{{ route('login') }}"
                               class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-xl
                                      text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                <i class="bx bx-lock"></i> Login to Checkout
                            </a>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                                You can browse as guest, but must login to purchase.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
