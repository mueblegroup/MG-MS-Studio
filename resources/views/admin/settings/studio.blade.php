<x-app-layout>
    <div class="min-h-screen bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">

        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Studio Settings</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Configure studio defaults, checkout settings, and mail server settings.</p>
            </div>
            <a href="{{ route('settings.payment-gateways.index') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                <i class="bx bx-credit-card"></i> Payment Gateways
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('mail_test_success'))
            <div class="mb-4 rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">
                {{ session('mail_test_success') }}
            </div>
        @endif

        @if(session('mail_test_error'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                <div class="font-bold">Test email failed.</div>
                <div class="mt-1 break-words text-sm">{{ session('mail_test_error') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">
                <ul class="ml-5 list-disc">
                    @foreach($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(!empty($envIssues))
            <div class="mb-4 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100">
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 text-xl">
                        <i class="bx bx-error-circle"></i>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="font-extrabold">Studio setup is incomplete</div>
                        <p class="mt-1 text-sm">
                            Complete the studio settings below and configure the selected payment gateway using this studio's own merchant credentials.
                        </p>

                        <div class="mt-3 space-y-2">
                            @foreach($envIssues as $issue)
                                <div class="rounded-xl border border-amber-200 bg-white/70 p-3 text-sm dark:border-amber-800 dark:bg-gray-900/70">
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <span class="rounded-lg bg-amber-100 px-2 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900 dark:text-amber-100">
                                                {{ $issue['type'] }}
                                            </span>

                                            <code class="ml-2 rounded bg-gray-100 px-2 py-1 text-xs font-bold text-gray-800 dark:bg-gray-800 dark:text-gray-100">
                                                {{ $issue['key'] }}
                                            </code>
                                        </div>

                                        @if(!empty($issue['link']))
                                            <a href="{{ $issue['link'] }}"
                                               class="text-xs font-bold text-indigo-700 underline dark:text-indigo-300">
                                                Configure now
                                            </a>
                                        @endif
                                    </div>

                                    <div class="mt-2 text-xs text-amber-800 dark:text-amber-100">
                                        {{ $issue['message'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form id="studio-settings-form" method="POST" action="{{ route('settings.studio.update') }}" class="max-w-5xl space-y-6">
            @csrf

            <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5">
                    <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">General Settings</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Basic studio and checkout defaults.</p>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Studio Name</label>
                        <input name="studio_name" value="{{ old('studio_name', $data['studio_name']) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Studio Display Name</label>
                        <input name="studio_display_name" value="{{ old('studio_display_name', $data['studio_display_name']) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Currency</label>
                        <input name="currency" value="{{ old('currency', $data['currency']) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                               placeholder="MYR" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Used as default in shop, checkout, and payment history.</div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Default Payment Gateway</label>
                        <select name="default_payment_provider"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach(['stripe' => 'Stripe', 'hitpay' => 'HitPay'] as $k => $label)
                                <option value="{{ $k }}" @selected(old('default_payment_provider', $data['default_payment_provider']) === $k)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Choose the gateway used for student checkout. After saving, you will be taken to its credential setup if it is not configured yet.</div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Class Early Cutoff Days</label>
                        <input type="number" min="0" max="365"
                               name="shop_class_early_cutoff_days"
                               value="{{ old('shop_class_early_cutoff_days', $data['shop_class_early_cutoff_days']) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hide classes that start within N days from today.</div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Plan Early Cutoff Days</label>
                        <input type="number" min="0" max="365" name="shop_plan_early_cutoff_days"
                               value="{{ old('shop_plan_early_cutoff_days', $data['shop_plan_early_cutoff_days']) }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hide plans that end within N days from today.</div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-5 flex flex-col justify-between gap-3 sm:flex-row sm:items-start">
                    <div>
                        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Mail Server Settings</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Configure SMTP without editing server environment files.</p>
                    </div>

                    <label class="inline-flex items-center gap-2 rounded-xl bg-gray-50 px-3 py-2 text-sm font-bold text-gray-700 ring-1 ring-gray-200 dark:bg-gray-900 dark:text-gray-200 dark:ring-gray-700">
                        <input type="hidden" name="mail_enabled" value="0">
                        <input type="checkbox" name="mail_enabled" value="1" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                               @checked(old('mail_enabled', $data['mail_enabled']) == true)>
                        Enable custom mail server
                    </label>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Mailer</label>
                        <select name="mail_mailer"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach(['smtp' => 'SMTP', 'log' => 'Log only', 'array' => 'Array/testing', 'sendmail' => 'Sendmail'] as $k => $label)
                                <option value="{{ $k }}" @selected(old('mail_mailer', $data['mail_mailer']) === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">Encryption</label>
                        <select name="mail_encryption"
                                class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            @foreach(['tls' => 'TLS', 'ssl' => 'SSL', 'none' => 'None'] as $k => $label)
                                <option value="{{ $k }}" @selected(old('mail_encryption', $data['mail_encryption'] ?: 'none') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">SMTP Host</label>
                        <input name="mail_host" value="{{ old('mail_host', $data['mail_host']) }}" placeholder="smtp.office365.com"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">SMTP Port</label>
                        <input type="number" min="1" max="65535" name="mail_port" value="{{ old('mail_port', $data['mail_port']) }}" placeholder="587"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">SMTP Username</label>
                        <input name="mail_username" value="{{ old('mail_username', $data['mail_username']) }}" autocomplete="off"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">SMTP Password</label>
                        <input type="password" name="mail_password" value="{{ old('mail_password', $data['mail_password']) }}" autocomplete="new-password"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Stored in studio settings. Use an app password where possible.</div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">From Email</label>
                        <input name="mail_from_address" value="{{ old('mail_from_address', $data['mail_from_address']) }}" placeholder="noreply@example.com"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">From Name</label>
                        <input name="mail_from_name" value="{{ old('mail_from_name', $data['mail_from_name']) }}" placeholder="{{ config('app.name') }}"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-semibold text-gray-600 dark:text-gray-300">EHLO / Local Domain</label>
                        <input name="mail_ehlo_domain" value="{{ old('mail_ehlo_domain', $data['mail_ehlo_domain']) }}" placeholder="example.com"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Useful for Office365/cPanel SMTP. Usually your app domain without https://.</div>
                    </div>

                    <div class="md:col-span-2 rounded-2xl border border-dashed border-gray-300 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-900/60">
                        <div class="mb-3">
                            <h3 class="text-sm font-extrabold text-gray-900 dark:text-white">Send Test Email</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Save your mail settings first, then send a test email to confirm SMTP is working.</p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <input form="mail-test-form" type="email" name="test_email" value="{{ old('test_email', auth()->user()->email ?? '') }}" placeholder="test@example.com"
                                   class="min-w-0 flex-1 rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-950 dark:text-white" />
                            <button form="mail-test-form" type="submit"
                                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-xs font-semibold text-white shadow transition hover:bg-emerald-700">
                                <i class="bx bx-send"></i> Send Test Email
                            </button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-2">
                <button class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-3 text-xs font-semibold text-white shadow transition hover:bg-indigo-700">
                    <i class="bx bx-save"></i> Save Settings
                </button>
            </div>
        </form>

        <form id="mail-test-form" method="POST" action="{{ route('settings.studio.test-email') }}" class="hidden">
            @csrf
        </form>

    </div>
</x-app-layout>
