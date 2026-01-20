<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Payment Details</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $payment->reference ?? ('PAY-' . $payment->id) }}
                </p>
            </div>

            <a href="{{ route('payments.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                      text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800
                      border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-3">Summary</h2>

                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Status</span><span class="font-semibold text-gray-900 dark:text-white">{{ strtoupper($payment->status ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Provider</span><span class="font-semibold text-gray-900 dark:text-white">{{ strtoupper($payment->provider ?? $payment->method ?? '-') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Amount</span><span class="font-semibold text-gray-900 dark:text-white">{{ $payment->currency ?? 'MYR' }} {{ number_format((float)$payment->amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>User</span>
                        <span class="font-semibold text-gray-900 dark:text-white">
                            {{ $payment->user->name ?? '-' }} {{ $payment->user?->email ? '(' . $payment->user->email . ')' : '' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Paid At</span><span class="font-semibold text-gray-900 dark:text-white">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Order ID</span><span class="font-semibold text-gray-900 dark:text-white">{{ $payment->order_id ?? '-' }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600 dark:text-gray-300">
                        <span>Provider Ref</span><span class="font-semibold text-gray-900 dark:text-white">{{ $payment->provider_reference ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-bold text-gray-900 dark:text-white mb-3">Raw Payload</h2>
                <pre class="text-xs whitespace-pre-wrap break-words text-gray-700 dark:text-gray-200 bg-gray-50 dark:bg-gray-900 rounded-xl p-3 border border-gray-200 dark:border-gray-700 max-h-[420px] overflow-auto">{{ json_encode($payment->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            </div>

            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5 mt-4">
                <h2 class="font-bold text-gray-900 dark:text-white mb-3">Purchased Items</h2>

                @php
                    $order = $payment->order;
                    $items = $order?->items ?? collect();
                @endphp

                @if(!$order)
                    <div class="text-sm text-gray-600 dark:text-gray-300">No order linked to this payment.</div>
                @elseif($items->isEmpty())
                    <div class="text-sm text-gray-600 dark:text-gray-300">No items found in this order.</div>
                @else
                    <div class="space-y-3">
                        @foreach($items as $it)
                            @php
                                $meta = $it->meta ?? [];
                                $type = class_basename($it->purchasable_type);
                                $typeLabel = match($type) {
                                    'ClassSession' => 'Class',
                                    'Plan' => 'Plan',
                                    'ClassCard' => 'Classcard',
                                    default => $type,
                                };

                                $label = $meta['label'] ?? ($it->purchasable->name ?? $typeLabel);
                                $qty = (int) ($it->quantity ?? 1);
                                $unit = (float) ($it->unit_price ?? 0);
                                $line = $qty * $unit;
                            @endphp

                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white">{{ $label }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            Type: {{ $typeLabel }} • Qty: {{ $qty }}
                                        </div>

                                        {{-- Show extra meta nicely --}}
                                        @if(!empty($meta))
                                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-2">
                                                @foreach($meta as $k => $v)
                                                    @if($v !== null && $v !== '')
                                                        <div>
                                                            <span class="text-gray-500 dark:text-gray-400">
                                                                {{ ucfirst(str_replace('_',' ', $k)) }}:
                                                            </span>
                                                            {{ is_array($v) ? json_encode($v) : $v }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $it->currency ?? 'MYR' }} {{ number_format($line, 2) }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Unit: {{ number_format($unit, 2) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>
</x-app-layout>
