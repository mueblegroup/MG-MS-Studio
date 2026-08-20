<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Add Class</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">For subscription classes, billing automatically matches the class recurrence.</p>
            </div>
            <a href="{{ route('admin.classes') }}" class="mg-btn-secondary"><i class="bx bx-arrow-back"></i> Back</a>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700"><ul class="ml-5 list-disc">@foreach($errors->all() as $error)<li class="text-sm">{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('admin.classes.store') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="recurrence" id="recurrence" value="{{ old('recurrence', 'no') }}">

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-5 font-bold">1. Class details</h2>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div><label class="text-xs font-semibold">Class Name</label><input name="class_name" required value="{{ old('class_name') }}" class="mg-input mt-1"></div>
                    <div><label class="text-xs font-semibold">Teacher</label><select name="teacher_id" required class="mg-input mt-1">@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id') == $teacher->id)>{{ $teacher->name }} ({{ $teacher->email }})</option>@endforeach</select></div>
                    <div class="md:col-span-2"><label class="text-xs font-semibold">Description</label><textarea name="description" rows="3" class="mg-input mt-1">{{ old('description') }}</textarea></div>
                    <div><label class="text-xs font-semibold">Class Type</label><select name="class_type" id="class_type" required class="mg-input mt-1"><option value="single" @selected(old('class_type','single')==='single')>One-time class</option><option value="recurring" @selected(old('class_type')==='recurring')>Repeating class</option><option value="subscription" @selected(old('class_type')==='subscription')>Subscription class</option></select><p id="classTypeHelp" class="mt-1 text-xs text-gray-500"></p></div>
                    <div><label id="priceLabel" class="text-xs font-semibold">Price (RM)</label><input name="price" id="price" required min="0" step="0.01" value="{{ old('price') }}" class="mg-input mt-1" type="number"></div>
                    <div><label class="text-xs font-semibold">Capacity</label><input name="capacity" min="1" max="1000" value="{{ old('capacity') }}" class="mg-input mt-1" type="number"></div>
                    <div><label class="text-xs font-semibold">Venue</label><input name="venue_name" value="{{ old('venue_name') }}" class="mg-input mt-1" placeholder="Studio A, Main Hall"></div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-1 font-bold">2. Class schedule</h2>
                <p class="mb-5 text-xs text-gray-500">Subscription billing uses this same interval. One successful payment assigns one generated session.</p>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <div><label class="text-xs font-semibold">First class date</label><input name="date" id="date" required value="{{ old('date') }}" class="mg-input mt-1" type="date"></div>
                    <div><label class="text-xs font-semibold">Start time</label><input name="start_time" required value="{{ old('start_time') }}" class="mg-input mt-1" type="time"></div>
                    <div><label class="text-xs font-semibold">End time</label><input name="end_time" required value="{{ old('end_time') }}" class="mg-input mt-1" type="time"></div>
                    <div id="graceWrap" class="hidden">
                        <label id="graceLabel" class="text-xs font-semibold">Payment grace days</label>
                        <input name="subscription_grace_days" id="subscription_grace_days" value="{{ old('subscription_grace_days',3) }}" min="0" max="30" class="mg-input mt-1" type="number">
                        <p id="graceHelp" class="mt-1 text-xs text-gray-500">Used after a failed renewal before access is considered overdue.</p>
                    </div>
                </div>

                <div id="recurrencePanel" class="mt-6 hidden rounded-2xl bg-gray-50 p-5 dark:bg-gray-900/60">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        <div><label class="text-xs font-semibold">Repeat and bill</label><select name="recurrence_frequency" id="recurrence_frequency" class="mg-input mt-1"><option value="everyday" @selected(old('recurrence_frequency')==='everyday')>Every day</option><option value="7days" @selected(old('recurrence_frequency','7days')==='7days')>Every week</option><option value="monthly" @selected(old('recurrence_frequency')==='monthly')>Every month</option><option value="yearly" @selected(old('recurrence_frequency')==='yearly')>Every year</option><option id="customOption" value="custom" @selected(old('recurrence_frequency')==='custom')>Every custom number of days</option></select><p id="billingPreview" class="mt-1 text-xs font-bold text-amber-700"></p></div>
                        <div><label class="text-xs font-semibold">Generate sessions until</label><input name="until_date" id="until_date" value="{{ old('until_date') }}" class="mg-input mt-1" type="date"></div>
                        <div id="customDaysWrap" class="hidden"><label class="text-xs font-semibold">Repeat every</label><div class="mt-1 flex items-center gap-2"><input name="custom_frequency" value="{{ old('custom_frequency') }}" min="1" max="365" class="mg-input" type="number"><span class="text-sm text-gray-500">days</span></div></div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <h2 class="font-bold text-blue-950">Setup summary</h2><p id="setupSummary" class="mt-2 text-sm leading-6 text-blue-900"></p>
            </section>

            <div class="flex justify-end gap-3"><a href="{{ route('admin.classes') }}" class="mg-btn-secondary">Cancel</a><button class="mg-btn-primary">Create Class</button></div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const type = document.getElementById('class_type');
                const recurrence = document.getElementById('recurrence');
                const panel = document.getElementById('recurrencePanel');
                const frequency = document.getElementById('recurrence_frequency');
                const until = document.getElementById('until_date');
                const customWrap = document.getElementById('customDaysWrap');
                const customOption = document.getElementById('customOption');
                const graceWrap = document.getElementById('graceWrap');
                const graceInput = document.getElementById('subscription_grace_days');
                const graceLabel = document.getElementById('graceLabel');
                const graceHelp = document.getElementById('graceHelp');
                const summary = document.getElementById('setupSummary');
                const billingPreview = document.getElementById('billingPreview');
                const price = document.getElementById('price');
                const date = document.getElementById('date');
                const typeHelp = document.getElementById('classTypeHelp');
                const priceLabel = document.getElementById('priceLabel');
                const labels = { everyday:'daily', '7days':'weekly', monthly:'monthly', yearly:'yearly', custom:'custom-day' };
                let graceAutoValue = null;

                function syncGrace(subscription) {
                    if (!subscription) return;
                    const daily = frequency.value === 'everyday';
                    const desiredDefault = daily ? 6 : 3;
                    const current = Number(graceInput.value || 0);

                    if (graceAutoValue === null || current === graceAutoValue) {
                        graceInput.value = desiredDefault;
                        graceAutoValue = desiredDefault;
                    }

                    graceInput.max = daily ? 23 : 30;
                    graceLabel.textContent = daily ? 'Payment grace hours' : 'Payment grace days';
                    graceHelp.textContent = daily
                        ? 'Daily billing uses hours so the recovery window stays shorter than the 24-hour billing cycle. Recommended: 6 hours.'
                        : 'Recovery window after a failed renewal. It does not add another billing cycle.';
                }

                graceInput.addEventListener('input', () => { graceAutoValue = Number(graceInput.value || 0); });

                function sync() {
                    const repeats = type.value !== 'single';
                    const subscription = type.value === 'subscription';
                    recurrence.value = repeats ? 'yes' : 'no';
                    panel.classList.toggle('hidden', !repeats);
                    graceWrap.classList.toggle('hidden', !subscription);
                    until.required = repeats;
                    frequency.required = repeats;
                    customOption.disabled = subscription;
                    if (subscription && frequency.value === 'custom') frequency.value = '7days';
                    customWrap.classList.toggle('hidden', !repeats || frequency.value !== 'custom');
                    syncGrace(subscription);
                    billingPreview.textContent = subscription ? `Recurring billing: ${labels[frequency.value]}. Stripe and HitPay use this same interval.` : '';
                    typeHelp.textContent = subscription ? 'Fixed recurring course: one successful billing cycle purchases one session.' : (type.value === 'recurring' ? 'Generated sessions without automatic billing.' : 'One session and one payment.');
                    priceLabel.textContent = subscription ? 'Recurring charge per session (RM)' : 'Price (RM)';
                    const amount = price.value ? `RM${Number(price.value).toFixed(2)}` : 'the entered amount';
                    const graceUnit = frequency.value === 'everyday' ? 'hours' : 'days';
                    summary.textContent = subscription
                        ? `Sessions repeat ${labels[frequency.value]} from ${date.value || 'the start date'} until ${until.value || 'the end date'}. The selected gateway charges ${amount} on that interval, billing stops after the final scheduled session, and failed payments use a ${graceInput.value || 0}-${graceUnit} recovery window.`
                        : (repeats ? `Sessions repeat ${labels[frequency.value]} until ${until.value || 'the end date'}.` : `One class session will be created on ${date.value || 'the selected date'}.`);
                }

                [type, frequency, until, price, date].forEach(el => { el.addEventListener('change', sync); el.addEventListener('input', sync); });
                sync();
            });
        </script>
    @endpush
</x-app-layout>