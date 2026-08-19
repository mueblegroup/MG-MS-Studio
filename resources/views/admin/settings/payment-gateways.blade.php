<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Payment Gateways</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Connect {{ $studio->name }} to its own Stripe and HitPay merchant accounts.</p>
            </div>
            <a href="{{ route('settings.studio') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">Back to Studio Settings</a>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/50 dark:bg-emerald-950/30 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-800 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-gray-950 dark:text-white">Stripe</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Student checkout charges go directly to this studio's Stripe account.</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $stripe?->enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $stripe?->enabled ? 'Enabled' : 'Disabled' }}</span>
                </div>

                <form method="POST" action="{{ route('settings.payment-gateways.update', 'stripe') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <label class="flex items-center gap-3 rounded-2xl bg-gray-50 p-4 dark:bg-gray-900">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" class="rounded" @checked(old('enabled', $stripe?->enabled))>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Enable Stripe for this studio</span>
                    </label>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Environment</label>
                        <select name="environment" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="sandbox" @selected(old('environment', $stripe?->environment ?? 'sandbox') === 'sandbox')>Test / Sandbox</option>
                            <option value="production" @selected(old('environment', $stripe?->environment ?? 'sandbox') === 'production')>Live / Production</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Publishable key</label>
                        <input name="publishable_key" autocomplete="off" placeholder="{{ $stripe ? 'Leave blank to keep saved key' : 'pk_test_...' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Secret key</label>
                        <input type="password" name="secret_key" autocomplete="new-password" placeholder="{{ $stripe ? 'Leave blank to keep saved secret' : 'sk_test_...' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Webhook signing secret</label>
                        <input type="password" name="webhook_secret" autocomplete="new-password" placeholder="{{ $stripe ? 'Leave blank to keep saved secret' : 'whsec_...' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <p class="mt-2 break-all text-xs text-gray-500">Webhook URL: {{ $stripeWebhookUrl }}</p>
                    </div>

                    <button class="w-full rounded-xl bg-indigo-600 px-5 py-3 text-sm font-black text-white hover:bg-indigo-700">Save Stripe Settings</button>
                </form>

                @if($stripe)
                    <form method="POST" action="{{ route('settings.payment-gateways.test', 'stripe') }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 dark:border-gray-700 dark:text-gray-200">Test Stripe Connection</button>
                    </form>
                    @if($stripe->last_tested_at)
                        <p class="mt-3 text-xs text-gray-500">Last test: {{ $stripe->last_tested_at->format('d M Y H:i') }} · {{ $stripe->last_test_status }}</p>
                    @endif
                @endif
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5 flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-xl font-black text-gray-950 dark:text-white">HitPay</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">One-time and recurring class charges go directly to this studio's HitPay account.</p>
                    </div>
                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $hitpay?->enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-600' }}">{{ $hitpay?->enabled ? 'Enabled' : 'Disabled' }}</span>
                </div>

                <form method="POST" action="{{ route('settings.payment-gateways.update', 'hitpay') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <label class="flex items-center gap-3 rounded-2xl bg-gray-50 p-4 dark:bg-gray-900">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" class="rounded" @checked(old('enabled', $hitpay?->enabled))>
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">Enable HitPay for this studio</span>
                    </label>

                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Environment</label>
                        <select name="environment" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <option value="sandbox" @selected(old('environment', $hitpay?->environment ?? 'sandbox') === 'sandbox')>Sandbox</option>
                            <option value="production" @selected(old('environment', $hitpay?->environment ?? 'sandbox') === 'production')>Production</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Business API key</label>
                        <input type="password" name="api_key" autocomplete="new-password" placeholder="{{ $hitpay ? 'Leave blank to keep saved API key' : 'HitPay API key' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Salt key</label>
                        <input type="password" name="salt" autocomplete="new-password" placeholder="{{ $hitpay ? 'Leave blank to keep saved salt' : 'HitPay salt key' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Event webhook salt key</label>
                        <input type="password" name="event_webhook_salt_key" autocomplete="new-password" placeholder="{{ $hitpay ? 'Leave blank to keep saved webhook salt' : 'Event webhook salt key' }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        <p class="mt-2 break-all text-xs text-gray-500">Webhook URL: {{ $hitpayWebhookUrl }}</p>
                    </div>

                    <button class="w-full rounded-xl bg-orange-500 px-5 py-3 text-sm font-black text-white hover:bg-orange-600">Save HitPay Settings</button>
                </form>

                @if($hitpay)
                    <form method="POST" action="{{ route('settings.payment-gateways.test', 'hitpay') }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 dark:border-gray-700 dark:text-gray-200">Test HitPay Connection</button>
                    </form>
                    @if($hitpay->last_tested_at)
                        <p class="mt-3 text-xs text-gray-500">Last test: {{ $hitpay->last_tested_at->format('d M Y H:i') }} · {{ $hitpay->last_test_status }}</p>
                    @endif
                @endif
            </section>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 dark:border-amber-900/50 dark:bg-amber-950/20 dark:text-amber-100">
            <p class="font-black">Tenant isolation</p>
            <p class="mt-1">A studio never falls back to the platform Stripe or HitPay account. Platform credentials remain reserved for main-domain SaaS billing.</p>
        </div>
    </div>
</x-app-layout>
