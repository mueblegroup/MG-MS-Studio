<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        {{-- Header --}}
        <div class="flex flex-col md:flex-row gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Plan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Update plan details and recurrence settings.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.plans.show', $plan->id) }}"
                   class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                    <i class="bx bx-arrow-back"></i> Back
                </a>
            </div>
        </div>

        {{-- Errors --}}
        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <form method="POST" action="{{ route('admin.plans.update', $plan->id) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                {{-- Basics --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Plan Details</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Name, description and pricing.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Plan Name</label>
                        <input
                            name="name"
                            required
                            value="{{ old('name', $plan->name) }}"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            type="text"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                        <textarea
                            name="description"
                            rows="3"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Optional description..."
                        >{{ old('description', $plan->description) }}</textarea>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Price</label>
                        <input
                            name="price"
                            required
                            min="0"
                            step="0.01"
                            value="{{ old('price', $plan->price) }}"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            type="number"
                        />
                    </div>

                    <!-- <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Currency</label>
                        <input
                            name="currency"
                            maxlength="3"
                            value="{{ old('currency', $plan->currency ?? 'MYR') }}"
                            class="mt-1 w-full uppercase rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            type="text"
                            placeholder="MYR"
                        />
                        <p class="text-[11px] text-gray-400 mt-1">3-letter ISO currency code (e.g. MYR, SGD).</p>
                    </div> -->
                </div>

                <hr class="border-gray-100 dark:border-gray-700">

                {{-- Recurrence --}}
                <div>
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Recurrence</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        If recurring is enabled, sessions can be generated up to the “until date”.
                    </p>
                </div>

                @php
                    $isRecurringOld = old('is_recurring', $plan->is_recurring ? 'yes' : 'no');
                    $freqOld = old('recurrence_frequency', $plan->recurrence_frequency);
                    $showRecurring = $isRecurringOld === 'yes';
                    $showCustom = $freqOld === 'custom';
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Is Recurring?</label>
                        <select
                            name="is_recurring"
                            id="is_recurring"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            required
                        >
                            <option value="no" @selected($isRecurringOld === 'no')>No</option>
                            <option value="yes" @selected($isRecurringOld === 'yes')>Yes</option>
                        </select>
                    </div>

                    <div id="freq_wrap" class="{{ $showRecurring ? '' : 'hidden' }}">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Recurrence Frequency</label>
                        <select
                            name="recurrence_frequency"
                            id="recurrence_frequency"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">Select frequency</option>
                            <option value="everyday" @selected($freqOld === 'everyday')>Daily</option>
                            <option value="7days" @selected($freqOld === '7days')>Weekly (7 days)</option>
                            <option value="monthly" @selected($freqOld === 'monthly')>Monthly</option>
                            <option value="yearly" @selected($freqOld === 'yearly')>Yearly</option>
                            <option value="custom" @selected($freqOld === 'custom')>Custom (days)</option>
                        </select>
                    </div>

                    <div id="custom_wrap" class="{{ ($showRecurring && $showCustom) ? '' : 'hidden' }}">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Custom Frequency (days)</label>
                        <input
                            name="custom_frequency"
                            id="custom_frequency"
                            min="1"
                            max="365"
                            value="{{ old('custom_frequency', $plan->custom_frequency_days) }}"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            type="number"
                            placeholder="e.g. 10"
                        />
                    </div>

                    <div id="until_wrap" class="{{ $showRecurring ? '' : 'hidden' }}">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Until Date</label>
                        <input
                            name="until_date"
                            id="until_date"
                            value="{{ old('until_date', optional($plan->until_date)->format('Y-m-d')) }}"
                            class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white
                                   focus:border-indigo-500 focus:ring-indigo-500"
                            type="date"
                        />
                    </div>
                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.plans.show', $plan->id) }}"
                       class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                        Cancel
                    </a>

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const isRecurring = document.getElementById('is_recurring');
                const freq = document.getElementById('recurrence_frequency');

                const freqWrap = document.getElementById('freq_wrap');
                const untilWrap = document.getElementById('until_wrap');
                const customWrap = document.getElementById('custom_wrap');

                function syncRecurringUI() {
                    const recurringYes = isRecurring.value === 'yes';

                    freqWrap.classList.toggle('hidden', !recurringYes);
                    untilWrap.classList.toggle('hidden', !recurringYes);

                    const customYes = recurringYes && (freq.value === 'custom');
                    customWrap.classList.toggle('hidden', !customYes);
                }

                isRecurring.addEventListener('change', syncRecurringUI);
                freq.addEventListener('change', syncRecurringUI);

                syncRecurringUI();
            });
        </script>
    @endpush
</x-app-layout>
