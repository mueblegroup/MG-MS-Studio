<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Checkout</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose payment method.</p>
            </div>

            <a href="{{ route('cart.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back to Cart
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Payment Method</h2>

                <form method="POST" action="{{ route('checkout.pay') }}" class="space-y-3">
                    @csrf

                    <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                        <input type="radio" name="provider" value="stripe" checked>
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">Stripe</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Card payment (fastest to launch)</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-700 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-900/40 transition">
                        <input type="radio" name="provider" value="hitpay">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">HitPay</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">FPX / cards (Malaysia-friendly)</div>
                        </div>
                    </label>

                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold
                                   text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                        <i class="bx bx-lock"></i> Pay Now
                    </button>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
                <h2 class="font-bold text-gray-900 dark:text-white mb-4">Order Summary</h2>

                <div class="space-y-3">
                    @foreach($cart->items as $item)
                        @php $label = $item->meta['label'] ?? class_basename($item->purchasable_type); @endphp
                        <div class="flex items-start justify-between text-sm">
                            <div class="text-gray-700 dark:text-gray-200">
                                {{ $label }}
                                <div class="text-xs text-gray-500 dark:text-gray-400">x{{ $item->quantity }}</div>
                            </div>
                            <div class="text-gray-900 dark:text-white font-semibold">
                                MYR {{ number_format($item->lineTotal(), 2) }}
                            </div>
                        </div>
                    @endforeach

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between">
                        <div class="text-sm text-gray-600 dark:text-gray-300">Total</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-white">
                            MYR {{ number_format($cart->subtotal(), 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
