<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Payment Status</h1>

            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                Order #{{ $order->id }} — Status:
                <span class="font-semibold">{{ strtoupper($order->status) }}</span>
            </div>

            @if($order->status !== 'paid')
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm">
                    Stripe is still confirming the payment (webhook). Refresh in a few seconds.
                </div>
            @else
                <div class="mt-4 p-4 rounded-xl bg-green-50 border border-green-200 text-green-800 text-sm">
                    Payment confirmed! Your cart has been cleared.
                </div>
            @endif

            <div class="mt-6">
                <a href="{{ route('shop.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold
                          text-white bg-indigo-600 hover:bg-indigo-700 transition">
                    <i class="bx bx-store"></i> Back to Shop
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
