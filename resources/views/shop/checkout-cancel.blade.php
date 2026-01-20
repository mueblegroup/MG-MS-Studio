<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Cancelled</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Your payment was cancelled. You can try again anytime.
            </p>

            <div class="mt-6 flex gap-2">
                <a href="{{ route('shop.cart') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    <i class="bx bx-cart"></i> Back to Cart
                </a>
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                          text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-gray-700">
                    <i class="bx bx-store"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
