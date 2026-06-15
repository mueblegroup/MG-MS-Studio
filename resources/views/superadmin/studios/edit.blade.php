<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Studio Subscription</p>
            <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">{{ $studio->name }}</h1>
            <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Assign the studio platform plan, subscription status and renewal/expiry dates. This does not change the studio admin's own dashboard.</p>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-4 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Studio Details</h2>
                <div class="mt-5 space-y-3 text-sm">
                    <div class="rounded-2xl bg-[#f7f2ea] p-4 dark:bg-gray-950">
                        <div class="text-xs font-extrabold uppercase text-[#9a8c7d] dark:text-gray-500">Owner</div>
                        <div class="mt-1 font-extrabold text-[#171717] dark:text-white">{{ $studio->owner?->name ?? 'Not assigned' }}</div>
                        <div class="text-[#6b5f52] dark:text-gray-400">{{ $studio->owner?->email }}</div>
                    </div>
                    <div class="rounded-2xl bg-[#f7f2ea] p-4 dark:bg-gray-950">
                        <div class="text-xs font-extrabold uppercase text-[#9a8c7d] dark:text-gray-500">Domain</div>
                        <div class="mt-1 font-extrabold text-[#171717] dark:text-white">{{ $studio->custom_domain ?? $studio->subdomain ?? $studio->slug ?? '-' }}</div>
                    </div>
                    <div class="rounded-2xl bg-[#f7f2ea] p-4 dark:bg-gray-950">
                        <div class="text-xs font-extrabold uppercase text-[#9a8c7d] dark:text-gray-500">Current Plan</div>
                        <div class="mt-1 font-extrabold text-[#171717] dark:text-white">{{ $studio->platformSubscriptionPlan?->name ?? $studio->plan_name ?? 'No plan assigned' }}</div>
                    </div>
                </div>
            </div>

            <div class="xl:col-span-8 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Manage Subscription</h2>
                <form method="POST" action="{{ route('superadmin.studios.update', $studio) }}" class="mt-6 grid grid-cols-1 gap-5 md:grid-cols-2">
                    @csrf
                    @method('PATCH')

                    <label class="space-y-2 md:col-span-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Studio Name</span>
                        <input name="name" value="{{ old('name', $studio->name) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Platform Plan</span>
                        <select name="platform_subscription_plan_id" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                            <option value="">No plan</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" @selected((string) old('platform_subscription_plan_id', $studio->platform_subscription_plan_id) === (string) $plan->id)>{{ $plan->name }} — {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}/{{ $plan->billing_interval }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Status</span>
                        <select name="status" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white" required>
                            @foreach(['active' => 'Active', 'trial' => 'Trial', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $studio->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Trial Ends At</span>
                        <input type="date" name="trial_ends_at" value="{{ old('trial_ends_at', optional($studio->trial_ends_at)->format('Y-m-d')) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </label>

                    <label class="space-y-2">
                        <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d] dark:text-gray-500">Subscription Ends At</span>
                        <input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at', optional($studio->subscription_ends_at)->format('Y-m-d')) }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-[#d97706] focus:ring-[#d97706] dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                    </label>

                    <div class="md:col-span-2 flex flex-wrap justify-end gap-3">
                        <a href="{{ route('superadmin.studios.index') }}" class="rounded-2xl border border-[#eadfce] px-5 py-3 text-sm font-extrabold text-[#6b5f52] dark:border-gray-800 dark:text-gray-300">Cancel</a>
                        <button class="rounded-2xl bg-[#d97706] px-5 py-3 text-sm font-extrabold text-white shadow-sm">Save Subscription</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
