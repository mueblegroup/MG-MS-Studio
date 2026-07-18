<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Class</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose the class type first. The form will only show the settings that matter.</p>
            </div>
            <a href="{{ route('admin.classes') }}" class="inline-flex items-center gap-2 rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <i class="bx bx-arrow-back"></i> Back
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                <ul class="ml-5 list-disc">
                    @foreach($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="recurrence" id="recurrence" value="{{ old('recurrence', 'no') }}">

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5">
                    <h2 class="font-bold text-gray-900 dark:text-white">1. Class details</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Basic information shown to students.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Name</label>
                        <input name="class_name" required value="{{ old('class_name') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Teacher</label>
                        <select name="teacher_id" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($teachers as $teacher)
                                <option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }} ({{ $teacher->email }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                        <textarea name="description" rows="3" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">{{ old('description') }}</textarea>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Type</label>
                        <select name="class_type" id="class_type" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="single" @selected(old('class_type', 'single') === 'single')>One-time class</option>
                            <option value="recurring" @selected(old('class_type') === 'recurring')>Repeating class</option>
                            <option value="subscription" @selected(old('class_type') === 'subscription')>Subscription class</option>
                        </select>
                        <p id="classTypeHelp" class="mt-1 text-xs text-gray-500 dark:text-gray-400"></p>
                    </div>
                    <div>
                        <label id="priceLabel" class="text-xs font-semibold text-gray-600 dark:text-gray-300">Price (RM)</label>
                        <input name="price" id="price" required min="0" step="0.01" value="{{ old('price') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity <span class="font-normal">(optional)</span></label>
                        <input name="capacity" min="1" max="1000" value="{{ old('capacity') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Venue <span class="font-normal">(optional)</span></label>
                        <input name="venue_name" value="{{ old('venue_name') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" placeholder="Studio A, Main Hall">
                    </div>
                </div>
            </section>

            <section id="subscriptionPanel" class="hidden rounded-2xl border border-amber-200 bg-amber-50/60 p-6 dark:border-amber-800 dark:bg-amber-900/10">
                <div class="mb-5">
                    <h2 class="font-bold text-gray-900 dark:text-white">2. Payment schedule</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">This controls how often Stripe charges the student. It is separate from how often the class happens.</p>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Charge student</label>
                        <select name="billing_interval" id="billing_interval" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="month" @selected(old('billing_interval', 'month') === 'month')>Every month</option>
                            <option value="week" @selected(old('billing_interval') === 'week')>Every week</option>
                            <option value="day" @selected(old('billing_interval') === 'day')>Every day</option>
                            <option value="year" @selected(old('billing_interval') === 'year')>Every year</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Payment grace period</label>
                        <input name="subscription_grace_days" value="{{ old('subscription_grace_days', 3) }}" min="0" max="30" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number">
                        <p class="mt-1 text-xs text-gray-500">Days allowed after a failed renewal before access is treated as overdue.</p>
                    </div>
                </div>
                <div class="mt-4 rounded-xl border border-amber-200 bg-white/70 p-4 text-sm text-amber-900 dark:border-amber-800 dark:bg-gray-900/50 dark:text-amber-100">
                    <strong>Important:</strong> The session end date below does not cancel Stripe billing. A subscription continues until the student or admin cancels it.
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5">
                    <h2 id="scheduleHeading" class="font-bold text-gray-900 dark:text-white">2. Class schedule</h2>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Set the first class date and time.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">First class date</label>
                        <input name="date" id="date" required value="{{ old('date') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="date">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start time</label>
                        <input name="start_time" required value="{{ old('start_time') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End time</label>
                        <input name="end_time" required value="{{ old('end_time') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time">
                    </div>
                </div>

                <div id="recurrencePanel" class="mt-6 hidden rounded-2xl bg-gray-50 p-5 dark:bg-gray-900/60">
                    <div class="mb-4">
                        <h3 class="font-bold text-gray-900 dark:text-white">How often does the class happen?</h3>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This creates the class sessions students can attend.</p>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Repeat</label>
                            <select name="recurrence_frequency" id="recurrence_frequency" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="everyday" @selected(old('recurrence_frequency') === 'everyday')>Every day</option>
                                <option value="7days" @selected(old('recurrence_frequency', '7days') === '7days')>Every week</option>
                                <option value="monthly" @selected(old('recurrence_frequency') === 'monthly')>Every month</option>
                                <option value="yearly" @selected(old('recurrence_frequency') === 'yearly')>Every year</option>
                                <option value="custom" @selected(old('recurrence_frequency') === 'custom')>Every custom number of days</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Generate sessions until</label>
                            <input name="until_date" id="until_date" value="{{ old('until_date') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="date">
                            <p class="mt-1 text-xs text-gray-500">The last date on which a class session may be created.</p>
                        </div>
                        <div id="customDaysWrap" class="hidden">
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Repeat every</label>
                            <div class="mt-1 flex items-center gap-2">
                                <input name="custom_frequency" value="{{ old('custom_frequency') }}" min="1" max="365" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number">
                                <span class="text-sm text-gray-500">days</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="summaryPanel" class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-900 dark:bg-blue-950/30">
                <h2 class="font-bold text-blue-950 dark:text-blue-100">Setup summary</h2>
                <p id="setupSummary" class="mt-2 text-sm leading-6 text-blue-900 dark:text-blue-200"></p>
            </section>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('admin.classes') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300">Cancel</a>
                <button type="submit" class="rounded-lg bg-orange-500 px-5 py-2 text-xs font-bold text-white hover:bg-orange-600">Create Class</button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const classType = document.getElementById('class_type');
                const recurrence = document.getElementById('recurrence');
                const subscriptionPanel = document.getElementById('subscriptionPanel');
                const recurrencePanel = document.getElementById('recurrencePanel');
                const frequency = document.getElementById('recurrence_frequency');
                const customDaysWrap = document.getElementById('customDaysWrap');
                const billingInterval = document.getElementById('billing_interval');
                const price = document.getElementById('price');
                const date = document.getElementById('date');
                const untilDate = document.getElementById('until_date');
                const summary = document.getElementById('setupSummary');
                const typeHelp = document.getElementById('classTypeHelp');
                const priceLabel = document.getElementById('priceLabel');
                const scheduleHeading = document.getElementById('scheduleHeading');

                const typeDescriptions = {
                    single: 'One class session with a one-time payment.',
                    recurring: 'Several class sessions with a one-time payment for each selected session.',
                    subscription: 'Students are charged automatically on a billing schedule and attend generated class sessions.'
                };

                const billingLabels = { day: 'every day', week: 'every week', month: 'every month', year: 'every year' };
                const frequencyLabels = { everyday: 'every day', '7days': 'every week', monthly: 'every month', yearly: 'every year', custom: 'on a custom day interval' };

                function syncForm() {
                    const type = classType.value;
                    const repeats = type !== 'single';
                    const subscription = type === 'subscription';

                    recurrence.value = repeats ? 'yes' : 'no';
                    subscriptionPanel.classList.toggle('hidden', !subscription);
                    recurrencePanel.classList.toggle('hidden', !repeats);
                    customDaysWrap.classList.toggle('hidden', !repeats || frequency.value !== 'custom');
                    untilDate.required = repeats;
                    frequency.required = repeats;
                    billingInterval.required = subscription;

                    typeHelp.textContent = typeDescriptions[type];
                    priceLabel.textContent = subscription ? 'Recurring charge amount (RM)' : 'Price (RM)';
                    scheduleHeading.textContent = subscription ? '3. Class schedule' : '2. Class schedule';
                    updateSummary();
                }

                function updateSummary() {
                    const type = classType.value;
                    const amount = price.value ? `RM${Number(price.value).toFixed(2)}` : 'the entered amount';
                    const firstDate = date.value || 'the selected start date';
                    const endDate = untilDate.value || 'the selected end date';

                    if (type === 'single') {
                        summary.textContent = `A one-time class will be created on ${firstDate}. The student pays ${amount} once.`;
                        return;
                    }

                    if (type === 'recurring') {
                        summary.textContent = `Class sessions will repeat ${frequencyLabels[frequency.value]} from ${firstDate} until ${endDate}. This is not an automatic subscription.`;
                        return;
                    }

                    summary.textContent = `The student is charged ${amount} ${billingLabels[billingInterval.value]}. Class sessions repeat ${frequencyLabels[frequency.value]} from ${firstDate} until ${endDate}. Stripe billing continues until the subscription is cancelled.`;
                }

                [classType, frequency, billingInterval, price, date, untilDate].forEach(element => {
                    element.addEventListener('change', syncForm);
                    element.addEventListener('input', updateSummary);
                });

                syncForm();
            });
        </script>
    @endpush
</x-app-layout>
