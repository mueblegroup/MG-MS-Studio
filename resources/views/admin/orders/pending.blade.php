<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Pending Orders</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Review and cancel pending orders.</p>
            </div>
            <a href="{{ route('payments.index') }}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                <i class="bx bx-arrow-back"></i> Payment History
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm font-semibold text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm font-semibold text-red-700">{{ session('error') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-700/40">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Order</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300">Provider</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Amount</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($orders as $order)
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-extrabold text-gray-900 dark:text-white">#{{ $order->id }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at?->format('Y-m-d H:i') }}</div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ $order->user?->name ?? 'Unknown user' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $order->user?->email ?? '' }}</div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-200">{{ strtoupper($order->payment_provider ?? '—') }}</td>
                                <td class="px-4 py-4 text-right text-sm font-extrabold text-gray-900 dark:text-white">{{ strtoupper($order->currency ?? 'MYR') }} {{ number_format((float) $order->total, 2) }}</td>
                                <td class="px-4 py-4 text-right">
                                    <form method="POST" action="{{ route('admin.pending-orders.cancel', $order) }}">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-3 py-2 text-xs font-bold text-white hover:bg-red-700">
                                            <i class="bx bx-x-circle"></i> Cancel Order
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No pending orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-100 p-4 dark:border-gray-700">{{ $orders->links() }}</div>
        </div>
    </div>
</x-app-layout>
