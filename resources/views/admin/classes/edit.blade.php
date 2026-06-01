<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Class Session</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Update the class template and this specific session.</p>
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
            <h2 class="font-bold text-gray-900 dark:text-white mb-4 text-sm mt-4 px-6">Edit Class Template ({{ $class->name }}) This changes will affect all sessions</h2>

            <form method="POST" action="{{ route('admin.classes.update', $session->id) }}" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Name</label>
                        <input name="class_name" required value="{{ old('class_name', $class->name) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Teacher</label>
                        <select name="teacher_id" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}" @selected(old('teacher_id', $class->teacher_id) == $t->id)>{{ $t->name }} ({{ $t->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Description</label>
                        <input name="description" value="{{ old('description', $class->description) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Class Type</label>
                        <select name="class_type" id="class_type" required class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="single" @selected(old('class_type', $class->type ?? 'single') === 'single')>Single Class</option>
                            <option value="recurring" @selected(old('class_type', $class->type ?? 'single') === 'recurring')>Recurring Class</option>
                            <option value="subscription" @selected(old('class_type', $class->type ?? 'single') === 'subscription')>Subscription Class</option>
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Price / Billing Amount (RM)</label>
                        <input name="price" required min="0" step="0.01" value="{{ old('price', $class->price) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Capacity (optional)</label>
                        <input name="capacity" min="1" max="1000" value="{{ old('capacity', $session->capacity ?? $class->capacity) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Venue (optional)</label>
                        <input name="venue_name" value="{{ old('venue_name', $session->venue_name) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="text" />
                    </div>
                </div>

                <div id="subscriptionPanel" class="hidden rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50/60 dark:bg-amber-900/10 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-bold text-gray-900 dark:text-white">Subscription Billing</h2>
                        <span class="text-xs text-gray-500 dark:text-gray-400">Only affects new subscription checkouts and future renewal records.</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Billing Interval</label>
                            <select name="billing_interval" id="billing_interval" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <option value="month" @selected(old('billing_interval', $class->billing_interval ?? 'month') === 'month')>Monthly</option>
                                <option value="week" @selected(old('billing_interval', $class->billing_interval) === 'week')>Weekly</option>
                                <option value="day" @selected(old('billing_interval', $class->billing_interval) === 'day')>Daily</option>
                                <option value="year" @selected(old('billing_interval', $class->billing_interval) === 'year')>Yearly</option>
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Payment Grace Days</label>
                            <input name="subscription_grace_days" value="{{ old('subscription_grace_days', $class->subscription_grace_days ?? 3) }}" min="0" max="30" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="number" />
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-gray-200 dark:border-gray-700 p-5 px-6 py-4">
                    <h2 class="font-bold text-gray-900 dark:text-white mb-4 text-sm mt-4">Edit Session ({{ $session->start_time->format('d-m-Y') }}) This changes will affect this session only</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Date</label>
                            <input name="date" required value="{{ old('date', optional($session->start_time)->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="date" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">Start Time</label>
                            <input name="start_time" required value="{{ old('start_time', optional($session->start_time)->format('H:i')) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time" />
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-gray-600 dark:text-gray-300">End Time</label>
                            <input name="end_time" required value="{{ old('end_time', optional($session->end_time)->format('H:i')) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" type="time" />
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <a href="{{ route('admin.classes') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">Cancel</a>
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-xs font-semibold text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition mb-2">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const classType = document.getElementById('class_type');
            const subscriptionPanel = document.getElementById('subscriptionPanel');

            function syncSubscriptionPanel() {
                subscriptionPanel.classList.toggle('hidden', classType.value !== 'subscription');
            }

            classType.addEventListener('change', syncSubscriptionPanel);
            syncSubscriptionPanel();
        });
    </script>
    @endpush
</x-app-layout>
