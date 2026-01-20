<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Your Cart</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review items before checkout.</p>
            </div>

            <a href="{{ route('shop.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-store"></i> Continue Shopping
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Item</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Qty</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Unit</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($cart->items as $item)
                            @php
                                $label = $item->meta['label'] ?? class_basename($item->purchasable_type);
                                $type = class_basename($item->purchasable_type);
                                $currency = $item->currency ?? 'MYR';
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">
                                <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ $label }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $type }}
                                </td>
                                <td class="px-4 py-4">
                                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="item_id" value="{{ $item->id }}">
                                        <input type="number" name="qty" min="0" max="99" value="{{ $item->quantity }}"
                                               class="w-20 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                        <button type="submit"
                                                class="px-3 py-1.5 rounded-lg text-xs font-semibold
                                                       text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                                            Update
                                        </button>
                                    </form>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $currency }} {{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $currency }} {{ number_format($item->lineTotal(), 2) }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <form method="POST" action="{{ route('cart.remove', $item->id) }}"
                                          onsubmit="return confirm('Remove this item?')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl
                                                       text-xs font-semibold text-red-600 hover:bg-red-50
                                                       dark:hover:bg-red-900/20 transition">
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

            <div class="p-5 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Subtotal:
                    <span class="font-bold text-gray-900 dark:text-white">
                        MYR {{ number_format($cart->subtotal(), 2) }}
                    </span>
                </div>

                <a href="{{ route('checkout.index') }}"
                   class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-credit-card"></i> Checkout
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
