<div>
    <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Studio Timezone</label>
    <select name="timezone" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
        @foreach($timezoneOptions as $timezone => $label)
            <option value="{{ $timezone }}" @selected(old('timezone', $data['timezone']) === $timezone)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used to display Stripe renewals, payment dates, schedules, and reports. Stored timestamps remain in UTC.</div>
</div>
