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
