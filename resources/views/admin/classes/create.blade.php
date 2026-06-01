<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Class</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Create a class template and generate one or many sessions.</p>
            </div>

            <a href="{{ route('admin.classes') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">
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
            <form method="POST" action="{{ route('admin.classes.store') }}" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Name</label>
                        <input name="class_name" required value="{{ old('class_name') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Teacher</label>
                        <select name="teacher_id" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                        <input name="description" value="{{ old('description') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Type</label>
                        <select name="class_type" id="class_type" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="single" @selected(old('class_type','single') === 'single')>Single Class</option>
                            <option value="recurring" @selected(old('class_type') === 'recurring')>Recurring Class</option>
                            <option value="subscription" @selected(old('class_type') === 'subscription')>Subscription Class</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Subscription classes use recurring billing and should generate future sessions.</p>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Price / Billing Amount (RM)</label>
                        <input name="price" required min="0" step="0.01" value="{{ old('price') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity (optional)</label>
                        <input name="capacity" min="1" max="1000" value="{{ old('capacity') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Venue (optional)</label>
                        <input name="venue_name" value="{{ old('venue_name') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" placeholder="e.g. Studio A, Main Hall" />
                    </div>
                </div>

                <div id="subscriptionPanel" class="hidden rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">Subscription Billing</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Stripe renews automatically. HitPay renewals are generated by command.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Billing Interval</label>
                            <select name="billing_interval" id="billing_interval" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="month" @selected(old('billing_interval','month') === 'month')>Monthly</option>
                                <option value="week" @selected(old('billing_interval') === 'week')>Weekly</option>
                                <option value="day" @selected(old('billing_interval') === 'day')>Daily</option>
                                <option value="year" @selected(old('billing_interval') === 'year')>Yearly</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Payment Grace Days</label>
                            <input name="subscription_grace_days" value="{{ old('subscription_grace_days', 3) }}" min="0" max="30" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-5 px-6 py-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">First Session</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">This date will always be created.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date</label>
                            <input name="date" required value="{{ old('date') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="date" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start Time</label>
                            <input name="start_time" required value="{{ old('start_time') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End Time</label>
                            <input name="end_time" required value="{{ old('end_time') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Recurrence</label>
                            <select name="recurrence" id="recurrence" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="no" @selected(old('recurrence','no') === 'no')>No (Single)</option>
                                <option value="yes" @selected(old('recurrence') === 'yes')>Yes (Generate More)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div id="recurrencePanel" class="hidden rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">Recurrence Settings</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Generate future sessions until the end date.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Frequency</label>
                            <select name="recurrence_frequency" id="recurrence_frequency" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="everyday" @selected(old('recurrence_frequency') === 'everyday')>Daily</option>
                                <option value="7days" @selected(old('recurrence_frequency') === '7days')>Weekly (7 days)</option>
                                <option value="monthly" @selected(old('recurrence_frequency','monthly') === 'monthly')>Monthly</option>
                                <option value="yearly" @selected(old('recurrence_frequency') === 'yearly')>Yearly</option>
                                <option value="custom" @selected(old('recurrence_frequency') === 'custom')>Custom (days)</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Until Date</label>
                            <input name="until_date" value="{{ old('until_date') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="date" />
                        </div>

                        <div id="customDaysWrap" class="hidden">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Custom Frequency (days)</label>
                            <input name="custom_frequency" value="{{ old('custom_frequency') }}" min="1" max="365" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" placeholder="e.g. 10" />
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">For subscription classes, generate enough future sessions for upcoming billing cycles.</div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.classes') }}" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">Cancel</a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">Create Class</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const classType = document.getElementById('class_type');
            const subscriptionPanel = document.getElementById('subscriptionPanel');
            const recurrence = document.getElementById('recurrence');
            const panel = document.getElementById('recurrencePanel');
            const freq = document.getElementById('recurrence_frequency');
            const customWrap = document.getElementById('customDaysWrap');

            function syncAll() {
                const isSubscription = classType.value === 'subscription';
                const isRecurringType = classType.value === 'recurring' || isSubscription;
                subscriptionPanel.classList.toggle('hidden', !isSubscription);
                if (isRecurringType) recurrence.value = 'yes';
                syncRecurrence();
            }

            function syncRecurrence() {
                const isOn = recurrence.value === 'yes';
                panel.classList.toggle('hidden', !isOn);
                if (!isOn) customWrap.classList.add('hidden');
                else syncCustom();
            }

            function syncCustom() {
                customWrap.classList.toggle('hidden', freq.value !== 'custom');
            }

            classType.addEventListener('change', syncAll);
            recurrence.addEventListener('change', syncRecurrence);
            freq.addEventListener('change', syncCustom);
            syncAll();
        });
    </script>
    @endpush
</x-app-layout>
