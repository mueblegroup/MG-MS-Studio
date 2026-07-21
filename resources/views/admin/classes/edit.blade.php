<x-app-layout>
    <div class="p-6 sm:p-8 bg-gray-50/60 dark:bg-gray-900 min-h-screen">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Edit Class Session</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Template changes affect all sessions. Schedule changes affect only this session.</p>
            </div>
            <a href="{{ route('admin.subscription-classes.show', $class->id) }}" class="mg-btn-secondary"><i class="bx bx-arrow-back"></i> Back</a>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc ml-5">@foreach($errors->all() as $error)<li class="text-sm">{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($hasActiveSubscriptions)
            <div class="mb-5 rounded-2xl border border-red-300 bg-red-50 p-4 text-sm text-red-900">
                <strong>Active subscriptions detected.</strong> Class type, price and billing interval are locked. Rescheduling requires a reason and confirmation, and the new date must stay between the previous and next session.
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700">
            <form method="POST" action="{{ route('admin.classes.update', $session->id) }}" class="p-6 space-y-6" onsubmit="return confirm('Save these class changes?')">
                @csrf @method('PUT')

                <section>
                    <h2 class="mb-4 font-bold text-gray-900 dark:text-white">Class template</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><label class="text-xs font-semibold">Class Name</label><input name="class_name" required value="{{ old('class_name', $class->name) }}" class="mg-input mt-1"></div>
                        <div><label class="text-xs font-semibold">Teacher</label><select name="teacher_id" required class="mg-input mt-1">@foreach($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id', $class->teacher_id) == $t->id)>{{ $t->name }} ({{ $t->email }})</option>@endforeach</select></div>
                        <div class="md:col-span-2"><label class="text-xs font-semibold">Description</label><textarea name="description" rows="3" class="mg-input mt-1">{{ old('description', $class->description) }}</textarea></div>
                        <div>
                            <label class="text-xs font-semibold">Class Type</label>
                            <select name="class_type" required class="mg-input mt-1" @disabled($hasActiveSubscriptions)>
                                <option value="single" @selected($class->type === 'single')>Single Class</option>
                                <option value="recurring" @selected($class->type === 'recurring')>Recurring Class</option>
                                <option value="subscription" @selected($class->type === 'subscription')>Subscription Class</option>
                            </select>
                            @if($hasActiveSubscriptions)<input type="hidden" name="class_type" value="{{ $class->type }}">@endif
                        </div>
                        <div>
                            <label class="text-xs font-semibold">Price / Billing Amount (RM)</label>
                            <input name="price" required min="0" step="0.01" value="{{ old('price', $class->price) }}" class="mg-input mt-1" type="number" @readonly($hasActiveSubscriptions)>
                            @if($hasActiveSubscriptions)<p class="mt-1 text-xs text-red-600">Create a new subscription class to offer a new price.</p>@endif
                        </div>
                        <div><label class="text-xs font-semibold">Capacity</label><input name="capacity" min="1" max="1000" value="{{ old('capacity', $session->capacity ?? $class->capacity) }}" class="mg-input mt-1" type="number"></div>
                        <div><label class="text-xs font-semibold">Venue</label><input name="venue_name" value="{{ old('venue_name', $session->venue_name) }}" class="mg-input mt-1"></div>
                    </div>

                    @if($class->type === 'subscription')
                        <div class="mt-5 grid grid-cols-1 gap-5 rounded-2xl border border-amber-200 bg-amber-50 p-5 md:grid-cols-2">
                            <div>
                                <label class="text-xs font-semibold">Billing interval</label>
                                <select name="billing_interval" class="mg-input mt-1" @disabled($hasActiveSubscriptions)>
                                    @foreach(['day'=>'Daily','week'=>'Weekly','month'=>'Monthly','year'=>'Yearly'] as $value => $label)<option value="{{ $value }}" @selected($class->billing_interval === $value)>{{ $label }}</option>@endforeach
                                </select>
                                @if($hasActiveSubscriptions)<input type="hidden" name="billing_interval" value="{{ $class->billing_interval }}">@endif
                            </div>
                            <div><label class="text-xs font-semibold">Payment Grace Days</label><input name="subscription_grace_days" value="{{ old('subscription_grace_days', $class->subscription_grace_days ?? 3) }}" min="0" max="30" class="mg-input mt-1" type="number"></div>
                        </div>
                    @else
                        <input type="hidden" name="billing_interval" value="">
                    @endif
                </section>

                <section class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700">
                    <h2 class="mb-4 font-bold">This session only — {{ $session->start_time->format('d M Y') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div><label class="text-xs font-semibold">Date</label><input name="date" required value="{{ old('date', $session->start_time->format('Y-m-d')) }}" class="mg-input mt-1" type="date"></div>
                        <div><label class="text-xs font-semibold">Start Time</label><input name="start_time" required value="{{ old('start_time', $session->start_time->format('H:i')) }}" class="mg-input mt-1" type="time"></div>
                        <div><label class="text-xs font-semibold">End Time</label><input name="end_time" required value="{{ old('end_time', $session->end_time->format('H:i')) }}" class="mg-input mt-1" type="time"></div>
                    </div>

                    @if($hasActiveSubscriptions)
                        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4">
                            <label class="text-xs font-extrabold text-red-900">Reason for rescheduling</label>
                            <textarea name="change_reason" minlength="10" rows="3" class="mg-input mt-2" placeholder="Explain why this subscribed session is being changed">{{ old('change_reason') }}</textarea>
                            <label class="mt-3 flex items-start gap-2 text-xs font-bold text-red-900"><input type="checkbox" name="confirm_danger" value="1" @checked(old('confirm_danger'))> I understand this affects subscribed students and confirm the new date remains in chronological order.</label>
                        </div>
                    @endif
                </section>

                <div class="flex justify-end gap-2">
                    <a href="{{ route('admin.subscription-classes.show', $class->id) }}" class="mg-btn-secondary">Cancel</a>
                    <button type="submit" class="mg-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>