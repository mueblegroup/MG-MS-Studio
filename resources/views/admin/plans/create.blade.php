<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">

        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Plan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Create a plan (RM300 etc.) and generate its scheduled sessions.
                </p>
            </div>

            <a href="{{ route('admin.plans') }}"
               class="inline-flex items-center px-4 py-2 rounded-lg
                           text-xs font-semibold text-gray-700 dark:text-gray-300
                           bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">
                    @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('admin.plans.store') }}" class="p-6 space-y-6">
                @csrf

                {{-- Plan details --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Plan Name</label>
                        <input name="name" required value="{{ old('name') }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               type="text" placeholder="Plan 1" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Price (RM)</label>
                        <input name="price" required min="0" step="0.01" value="{{ old('price') }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               type="number" placeholder="300.00" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                        <input name="description" value="{{ old('description') }}"
                               class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               type="text" placeholder="e.g. 3-session beginner series" />
                    </div>

                    <input type="hidden" name="currency" value="MYR" />
                </div>

                {{-- First session --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-5 px-6 py-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">First Session</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">This session is always created.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date</label>
                            <input name="date" required value="{{ old('date') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="date" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start Time</label>
                            <input name="start_time" required value="{{ old('start_time') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="time" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End Time</label>
                            <input name="end_time" required value="{{ old('end_time') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="time" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Venue (optional)</label>
                            <input name="venue_name" value="{{ old('venue_name') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="text" placeholder="Studio A" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity (optional)</label>
                            <input name="capacity" min="1" max="1000" value="{{ old('capacity') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="number" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Session Label (optional)</label>
                            <input name="session_name" value="{{ old('session_name') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="text" placeholder="Class 1" />
                        </div>
                    </div>
                </div>

                {{-- Recurrence --}}
                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-5 px-6 py-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">Recurrence</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Generate future sessions automatically.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Recurring?</label>
                            <select name="recurrence" id="recurrence" required
                                    class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="no" @selected(old('recurrence','no')==='no')>No (Single)</option>
                                <option value="yes" @selected(old('recurrence')==='yes')>Yes</option>
                            </select>
                        </div>

                        <div id="freqWrap" class="hidden">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Frequency</label>
                            <select name="recurrence_frequency" id="recurrence_frequency"
                                    class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="everyday" @selected(old('recurrence_frequency')==='everyday')>Daily</option>
                                <option value="7days" @selected(old('recurrence_frequency')==='7days')>Weekly</option>
                                <option value="monthly" @selected(old('recurrence_frequency')==='monthly')>Monthly</option>
                                <option value="yearly" @selected(old('recurrence_frequency')==='yearly')>Yearly</option>
                                <option value="custom" @selected(old('recurrence_frequency')==='custom')>Custom days</option>
                            </select>
                        </div>

                        <div id="untilWrap" class="hidden">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Until Date</label>
                            <input name="until_date" value="{{ old('until_date') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="date" />
                        </div>

                        <div id="customWrap" class="hidden">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Custom (days)</label>
                            <input name="custom_frequency" min="1" max="365" value="{{ old('custom_frequency') }}"
                                   class="mt-1 w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                                   type="number" placeholder="10" />
                        </div>
                    </div>

                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Example: Start 10 Jan, custom 10 days, until 30 Jan → sessions on 10, 20, 30 Jan.
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.plans') }}"
                       class="px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 dark:bg-gray-700 dark:text-gray-200">
                        Cancel
                    </a>

                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 transition">
                        Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const recurrence = document.getElementById('recurrence');
            const freqWrap = document.getElementById('freqWrap');
            const untilWrap = document.getElementById('untilWrap');
            const customWrap = document.getElementById('customWrap');
            const freq = document.getElementById('recurrence_frequency');

            function sync() {
                const isOn = recurrence.value === 'yes';
                freqWrap.classList.toggle('hidden', !isOn);
                untilWrap.classList.toggle('hidden', !isOn);

                if (!isOn) customWrap.classList.add('hidden');
                else syncCustom();
            }

            function syncCustom() {
                const show = freq.value === 'custom';
                customWrap.classList.toggle('hidden', !show);
            }

            recurrence.addEventListener('change', sync);
            freq.addEventListener('change', syncCustom);

            sync();
        });
    </script>
    @endpush
</x-app-layout>
