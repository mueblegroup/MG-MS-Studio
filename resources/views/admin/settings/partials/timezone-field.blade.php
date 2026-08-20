<div>
    <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Studio Timezone</label>
    <select name="timezone" id="studio-timezone-select" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @foreach($timezoneOptions as $timezone => $label)
            <option value="{{ $timezone }}" @selected(old('timezone', $data['timezone']) === $timezone)>{{ $label }}</option>
        @endforeach
    </select>

    <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-900">
        <div class="text-[11px] font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Current studio time</div>
        <div id="studio-current-time" class="mt-1 text-sm font-extrabold text-gray-900 dark:text-white">Loading…</div>
        <div id="studio-current-timezone" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">{{ old('timezone', $data['timezone']) }}</div>
    </div>

    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">This is the owner-controlled timezone used by admins, teachers, students, schedules, billing displays, payment deadlines, and reminders. Stored timestamps should remain UTC where supported.</div>

    <script>
        (() => {
            const select = document.getElementById('studio-timezone-select');
            const clock = document.getElementById('studio-current-time');
            const zoneLabel = document.getElementById('studio-current-timezone');

            if (!select || !clock || !zoneLabel) return;

            let timer;
            const render = () => {
                const timezone = select.value || 'Asia/Kuala_Lumpur';

                try {
                    clock.textContent = new Intl.DateTimeFormat(undefined, {
                        timeZone: timezone,
                        weekday: 'short',
                        day: '2-digit',
                        month: 'short',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                    }).format(new Date());
                    zoneLabel.textContent = timezone;
                } catch (error) {
                    clock.textContent = 'Unable to display current time';
                    zoneLabel.textContent = timezone;
                }
            };

            select.addEventListener('change', render);
            render();
            clearInterval(timer);
            timer = setInterval(render, 1000);
        })();
    </script>
</div>
