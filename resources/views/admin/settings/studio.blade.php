<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Studio Settings</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Configure studio defaults used across the system.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 p-3 rounded-xl bg-green-50 text-green-700 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('settings.studio.update') }}"
              class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 max-w-3xl">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Studio Name</label>
                    <input name="studio_name" value="{{ old('studio_name', $data['studio_name']) }}"
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Currency</label>
                    <input name="currency" value="{{ old('currency', $data['currency']) }}"
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                           placeholder="MYR" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Used as default in shop/checkout/payment history.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">Default Payment Gateway</label>
                    <select name="default_payment_provider"
                            class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        @foreach(['stripe' => 'Stripe', 'hitpay' => 'HitPay'] as $k => $label)
                            <option value="{{ $k }}" @selected(old('default_payment_provider', $data['default_payment_provider']) === $k)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Used as default selection on checkout.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">
                        Class Early Cutoff Days
                    </label>
                    <input type="number" min="0" max="365"
                           name="shop_class_early_cutoff_days"
                           value="{{ old('shop_class_early_cutoff_days', $data['shop_class_early_cutoff_days']) }}"
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Hide classes that start within N days from today.
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-600 dark:text-gray-300 mb-2">
                        Plan Early Cutoff Days
                    </label>
                    <input type="number" min="0" max="365"
                           name="shop_plan_early_cutoff_days"
                           value="{{ old('shop_plan_early_cutoff_days', $data['shop_plan_early_cutoff_days']) }}"
                           class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Hide plans that end within N days from today.
                    </div>
                </div>

            </div>

            <div class="mt-6 flex items-center gap-2">
                <button class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-semibold
                               text-white bg-indigo-600 hover:bg-indigo-700 transition shadow">
                    <i class="bx bx-save"></i> Save Settings
                </button>
            </div>
        </form>

    </div>
</x-app-layout>
