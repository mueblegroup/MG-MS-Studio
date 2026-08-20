<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Checkout</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Confirm your order and continue to secure payment.</p>
            </div>

            <a href="{{ route('shop.cart.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back to Cart
            </a>
        </div>

        @if(!empty($hasSubscriptionClass))
            <div class="mb-5 rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-900/20 p-4 text-sm text-amber-800 dark:text-amber-100">
                <div class="font-bold">Subscription checkout</div>
                <div class="mt-1">
                    This class uses recurring billing. Your studio's configured payment gateway will manage the subscription and future eligible charges.
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12">
            <div class="lg:col-span-8 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-bold text-gray-900 dark:text-white">Order Items</h2>
                    <div class="mt-4 space-y-3 my-4 gap-4 flex flex-col">
                        @foreach($cartModel->items as $item)
                            @php
                                $meta = $item->meta ?? [];
                                $label = $meta['label'] ?? ($item->purchasable->name ?? class_basename($item->purchasable_type));
                                $line = ((float)$item->unit_price) * ((int)$item->quantity);
                            @endphp

                            <div class="flex items-start justify-between gap-4 p-4 rounded-2xl bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-700">
                                <div>
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $label }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        Qty: {{ $item->quantity }}
                                        @if(($meta['class_type'] ?? null) === 'subscription')
                                            <span class="ml-2 inline-flex rounded-full bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 font-semibold text-amber-700 dark:text-amber-100">Subscription</span>
                                        @endif
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
                                </div>

                                <div class="text-right font-semibold text-gray-900 dark:text-white">
                                    {{ $item->currency ?? 'MYR' }} {{ number_format($line, 2) }}
                                    @if(($meta['class_type'] ?? null) === 'subscription')
                                        <div class="text-xs font-normal text-gray-500 dark:text-gray-400">recurring</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

            </div>

            <div class="lg:col-span-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 shadow-sm sticky top-6">
                    <h2 class="font-bold text-gray-900 dark:text-white">Total</h2>
                    <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $summary['currency'] }} {{ number_format($summary['total'], 2) }}
                    </div>
                    @if(!empty($hasSubscriptionClass))
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Recurring subscription amount</div>
                    @endif

                    <div class="mt-5 space-y-2">
                            @php
                                $enabled = $enabledProviders ?? ['stripe'];
                                $ctaLabel = !empty($hasSubscriptionClass) ? 'Subscribe' : 'Pay';
                            @endphp

                            <form method="POST" action="{{ route('shop.checkout.pay') }}" class="space-y-2">
                                @csrf

                                @if(in_array('stripe', $enabled, true))
                                    <button name="provider" value="stripe"
                                        aria-label="{{ $ctaLabel }}"
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-xl
                                            text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                                        <i class="bx bx-lock-alt"></i> {{ $ctaLabel }}
                                    </button>
                                @endif

                                @if(in_array('hitpay', $enabled, true))
                                    <button name="provider" value="hitpay"
                                        aria-label="{{ $ctaLabel }}"
                                        class="w-full inline-flex justify-center items-center gap-2 px-4 py-3 rounded-xl
                                            text-sm font-semibold text-white bg-gray-800 hover:bg-gray-900 transition shadow">
                                        <i class="bx bx-lock-alt"></i> {{ $ctaLabel }}
                                    </button>
                                @endif
                            </form>

                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
