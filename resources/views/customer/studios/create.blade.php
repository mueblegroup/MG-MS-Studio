<x-app-layout>
    <div class="min-h-screen bg-[#f7f2ea] px-4 py-6 dark:bg-gray-950 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-3xl space-y-6">
            <div class="rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-500">Studio Setup</p>
                <h1 class="mt-2 text-3xl font-black text-gray-900 dark:text-white">Register a studio portal</h1>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Each studio uses its own subdomain for day-to-day LMS operations.</p>
            </div>

            <form method="POST" action="{{ route('customer.studios.store') }}" class="space-y-5 rounded-3xl bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-gray-900 dark:ring-white/10">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-300">Studio Name</label>
                    <input type="text" name="studio_name" value="{{ old('studio_name') }}" required class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    @error('studio_name') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-300">Subdomain</label>
                    <div class="flex rounded-2xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" required class="min-w-0 flex-1 rounded-l-2xl border-0 px-4 py-3 text-sm dark:bg-gray-800 dark:text-white" placeholder="yourstudio">
                        <span class="inline-flex items-center rounded-r-2xl bg-gray-50 px-4 text-sm font-semibold text-gray-500 dark:bg-gray-900 dark:text-gray-400">.{{ $rootDomain }}</span>
                    </div>
                    @error('subdomain') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-300">Subscription Plan</label>
                    <select name="platform_subscription_plan_id" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Trial / assign later</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('platform_subscription_plan_id') == $plan->id)>{{ $plan->name }} - {{ $plan->currency }} {{ number_format($plan->price, 2) }} / {{ $plan->billing_interval }}</option>
                        @endforeach
                    </select>
                    @error('platform_subscription_plan_id') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-300">Timezone</label>
                        <input type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-bold text-gray-700 dark:text-gray-300">Currency</label>
                        <input type="text" name="currency" maxlength="3" value="{{ old('currency', 'MYR') }}" class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm uppercase dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                    </div>
                </div>
                <div class="flex items-center justify-between gap-3 pt-2">
                    <a href="{{ route('customer.dashboard') }}" class="rounded-2xl px-5 py-3 text-sm font-bold text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">Cancel</a>
                    <button type="submit" class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">Save Studio</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
