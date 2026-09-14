<dialog id="calendar-event-dialog" class="m-auto w-[calc(100%-2rem)] max-w-lg overflow-hidden rounded-3xl bg-white p-0 text-gray-900 shadow-2xl backdrop:bg-black/50 dark:bg-gray-900 dark:text-white">
    <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800 sm:px-6">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <span id="calendar-event-type" class="mb-2 inline-flex rounded-full bg-violet-100 px-2.5 py-1 text-[10px] font-extrabold uppercase tracking-wide text-violet-700 dark:bg-violet-900/40 dark:text-violet-200"></span>
                <h2 id="calendar-event-title" class="text-xl font-extrabold leading-tight"></h2>
            </div>
            <button type="button" data-calendar-dialog-close class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xl text-gray-600 transition hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700" aria-label="Close event details">
                <i class="bx bx-x"></i>
            </button>
        </div>
    </div>

    <div class="space-y-4 px-5 py-5 sm:px-6">
        <div class="grid gap-3 sm:grid-cols-2">
            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                <div class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Date</div>
                <div id="calendar-event-date" class="mt-1 text-sm font-bold"></div>
            </div>
            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                <div class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Time</div>
                <div id="calendar-event-time" class="mt-1 text-sm font-bold"></div>
            </div>
            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                <div class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Teacher</div>
                <div id="calendar-event-teacher" class="mt-1 text-sm font-bold"></div>
            </div>
            <div class="rounded-2xl bg-gray-50 p-3 dark:bg-gray-800">
                <div class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Venue</div>
                <div id="calendar-event-venue" class="mt-1 text-sm font-bold"></div>
            </div>
        </div>

        <div id="calendar-event-billing-wrap" class="hidden rounded-2xl border p-4">
            <div class="flex items-start gap-3">
                <i id="calendar-event-billing-icon" class="bx mt-0.5 text-xl"></i>
                <div>
                    <div id="calendar-event-billing-title" class="text-sm font-extrabold"></div>
                    <p id="calendar-event-billing-message" class="mt-1 text-xs leading-5"></p>
                    <div id="calendar-event-payment-details" class="mt-3 hidden flex-wrap gap-2 text-[10px] font-extrabold uppercase tracking-wide">
                        <span id="calendar-event-payment-provider" class="rounded-full border border-current/20 px-2 py-1"></span>
                        <span id="calendar-event-payment-status" class="rounded-full border border-current/20 px-2 py-1"></span>
                        <span id="calendar-event-payment-reference" class="break-all rounded-full border border-current/20 px-2 py-1 normal-case tracking-normal"></span>
                    </div>
                </div>
            </div>
        </div>

        <div id="calendar-event-description-wrap" class="rounded-2xl border border-gray-100 p-4 dark:border-gray-800">
            <div class="text-[10px] font-extrabold uppercase tracking-wide text-gray-400">Details</div>
            <p id="calendar-event-description" class="mt-2 whitespace-pre-line text-sm leading-6 text-gray-600 dark:text-gray-300"></p>
        </div>
    </div>
</dialog>

@once
    @push('scripts')
        <script>
            window.openCalendarEventDetails = function(info) {
                const dialog = document.getElementById('calendar-event-dialog');
                if (!dialog) return;

                const event = info.event;
                const details = event.extendedProps || {};
                const start = event.start;
                const end = event.end;
                const dateOptions = { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' };
                const timeOptions = { hour: 'numeric', minute: '2-digit' };

                document.getElementById('calendar-event-type').textContent =
                    (details.kind || 'class').replaceAll('_', ' ');
                document.getElementById('calendar-event-title').textContent =
                    details.name || event.title || 'Scheduled event';
                document.getElementById('calendar-event-date').textContent =
                    start ? new Intl.DateTimeFormat(undefined, dateOptions).format(start) : 'Not specified';
                document.getElementById('calendar-event-time').textContent =
                    start
                        ? new Intl.DateTimeFormat(undefined, timeOptions).format(start) +
                            (end ? ' – ' + new Intl.DateTimeFormat(undefined, timeOptions).format(end) : '')
                        : 'Not specified';
                document.getElementById('calendar-event-teacher').textContent =
                    details.teacher || 'Not assigned';
                document.getElementById('calendar-event-venue').textContent =
                    details.venue || 'Not specified';

                const billingWrap = document.getElementById('calendar-event-billing-wrap');
                const billingStatus = details.billingStatus || '';
                const isConfirmed = billingStatus === 'confirmed';
                billingWrap.classList.toggle('hidden', !billingStatus);
                billingWrap.classList.toggle('border-emerald-200', isConfirmed);
                billingWrap.classList.toggle('bg-emerald-50', isConfirmed);
                billingWrap.classList.toggle('text-emerald-800', isConfirmed);
                billingWrap.classList.toggle('dark:border-emerald-900/60', isConfirmed);
                billingWrap.classList.toggle('dark:bg-emerald-950/30', isConfirmed);
                billingWrap.classList.toggle('dark:text-emerald-200', isConfirmed);
                billingWrap.classList.toggle('border-amber-200', billingStatus && !isConfirmed);
                billingWrap.classList.toggle('bg-amber-50', billingStatus && !isConfirmed);
                billingWrap.classList.toggle('text-amber-800', billingStatus && !isConfirmed);
                billingWrap.classList.toggle('dark:border-amber-900/60', billingStatus && !isConfirmed);
                billingWrap.classList.toggle('dark:bg-amber-950/30', billingStatus && !isConfirmed);
                billingWrap.classList.toggle('dark:text-amber-200', billingStatus && !isConfirmed);
                document.getElementById('calendar-event-billing-icon').className =
                    'bx mt-0.5 text-xl ' + (isConfirmed ? 'bx-check-circle' : 'bx-time-five');
                document.getElementById('calendar-event-billing-title').textContent =
                    isConfirmed ? 'Session confirmed' : 'Confirmation pending';
                document.getElementById('calendar-event-billing-message').textContent =
                    details.billingMessage || '';

                const paymentDetails = document.getElementById('calendar-event-payment-details');
                const paymentProvider = details.paymentProvider || '';
                const paymentStatus = details.paymentStatus || '';
                const paymentReference = details.paymentReference || '';
                const hasPaymentDetails = paymentProvider || paymentStatus || paymentReference;
                paymentDetails.classList.toggle('hidden', !hasPaymentDetails);
                paymentDetails.classList.toggle('flex', Boolean(hasPaymentDetails));
                document.getElementById('calendar-event-payment-provider').textContent = paymentProvider;
                document.getElementById('calendar-event-payment-provider').classList.toggle('hidden', !paymentProvider);
                document.getElementById('calendar-event-payment-status').textContent = paymentStatus;
                document.getElementById('calendar-event-payment-status').classList.toggle('hidden', !paymentStatus);
                document.getElementById('calendar-event-payment-reference').textContent = paymentReference;
                document.getElementById('calendar-event-payment-reference').classList.toggle('hidden', !paymentReference);

                const description = (details.description || '').trim();
                document.getElementById('calendar-event-description-wrap').classList.toggle('hidden', !description);
                document.getElementById('calendar-event-description').textContent = description;

                if (typeof dialog.showModal === 'function') dialog.showModal();
                else dialog.setAttribute('open', 'open');
            };

            document.addEventListener('DOMContentLoaded', function() {
                const dialog = document.getElementById('calendar-event-dialog');
                if (!dialog) return;

                dialog.querySelectorAll('[data-calendar-dialog-close]').forEach((button) => {
                    button.addEventListener('click', () => dialog.close());
                });

                dialog.addEventListener('click', (event) => {
                    if (event.target === dialog) dialog.close();
                });
            });
        </script>
    @endpush
@endonce
