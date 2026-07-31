<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Client Portal Authentication</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Single Sign-On</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Manage Google, Microsoft, and Apple login for the central client portal only. Studio subdomains remain unchanged.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800">{{ $errors->first() }}</div>
        @endif

        <div class="grid gap-6 xl:grid-cols-3">
            @foreach($providers as $provider => $settings)
                <form method="POST" action="{{ route('superadmin.sso.update', $provider) }}" class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    @csrf
                    @method('PATCH')
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-xl font-extrabold text-[#171717] dark:text-white">{{ ucfirst($provider) }}</h2>
                            <p class="mt-1 text-xs font-semibold text-[#9a8c7d]">Callback URL</p>
                        </div>
                        <label class="inline-flex items-center gap-2 text-sm font-bold">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" @checked(old('is_enabled', $settings->is_enabled)) class="rounded border-gray-300 text-orange-600">
                            Enabled
                        </label>
                    </div>

                    <div class="mt-3 break-all rounded-2xl bg-[#fffaf3] p-3 text-xs font-semibold text-[#6b5f52]">{{ route('client-sso.callback', ['provider' => $provider]) }}</div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label class="text-sm font-bold">Client ID</label>
                            <input name="client_id" value="{{ old('client_id', $settings->client_id) }}" required class="mt-1 w-full rounded-xl border-[#eadfce]" autocomplete="off">
                        </div>
                        <div>
                            <label class="text-sm font-bold">Client Secret {{ $settings->client_secret ? '(leave blank to keep current)' : '' }}</label>
                            <textarea name="client_secret" rows="3" class="mt-1 w-full rounded-xl border-[#eadfce]" autocomplete="new-password"></textarea>
                        </div>

                        @if($provider === 'microsoft')
                            <div>
                                <label class="text-sm font-bold">Tenant ID</label>
                                <input name="tenant_id" value="{{ old('tenant_id', $settings->tenant_id ?: 'common') }}" class="mt-1 w-full rounded-xl border-[#eadfce]">
                                <p class="mt-1 text-xs text-[#9a8c7d]">Use <strong>common</strong> for personal and work accounts, or a tenant GUID to restrict access.</p>
                            </div>
                        @endif

                        @if($provider === 'apple')
                            <div>
                                <label class="text-sm font-bold">Client Secret Expiry</label>
                                <input type="date" name="secret_expires_at" value="{{ old('secret_expires_at', optional($settings->secret_expires_at)->format('Y-m-d')) }}" class="mt-1 w-full rounded-xl border-[#eadfce]">
                                <p class="mt-1 text-xs text-[#9a8c7d]">Apple client-secret JWTs expire and must be replaced before this date.</p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-bold">Internal Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 w-full rounded-xl border-[#eadfce]">{{ old('notes', $settings->notes) }}</textarea>
                        </div>
                    </div>

                    <button class="mt-6 w-full rounded-2xl bg-[#d97706] px-4 py-3 text-sm font-extrabold text-white hover:bg-[#b45309]">Save {{ ucfirst($provider) }}</button>
                </form>
            @endforeach
        </div>

        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6 text-sm font-semibold leading-6 text-amber-900">
            <strong>Security boundary:</strong> SSO callbacks are available only on the central domain. A social identity can create or access a client-owner account, but it cannot authenticate students, teachers, studio admins, or superadmins.
        </div>
    </div>
</x-app-layout>
