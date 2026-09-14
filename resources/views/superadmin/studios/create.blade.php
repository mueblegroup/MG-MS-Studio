<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Client Provisioning</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Create Client Studio</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Create or safely reuse a client-admin account, provision its studio and domain, and assign a platform plan in one operation.</p>
                </div>
                <a href="{{ route('superadmin.studios.index') }}" class="rounded-2xl border border-[#eadfce] px-5 py-3 text-sm font-extrabold text-[#6b5f52] dark:border-gray-800 dark:text-gray-300">Back to Studios</a>
            </div>
        </div>

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                <div class="font-extrabold">The studio could not be created.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.studios.store') }}" class="space-y-6">
            @csrf

            <div class="grid gap-6 xl:grid-cols-2">
                <section class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Client administrator</h2>
                    <p class="mt-1 text-sm text-[#6b5f52] dark:text-gray-400">An existing client-admin email is reused safely. Teacher and student accounts are never promoted automatically.</p>
                    <div class="mt-5 space-y-4">
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Full name</span><input name="owner_name" value="{{ old('owner_name') }}" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"></label>
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Email</span><input type="email" name="owner_email" value="{{ old('owner_email') }}" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"></label>
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Phone</span><input name="owner_phone" value="{{ old('owner_phone') }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"></label>
                        <label class="flex items-start gap-3 rounded-2xl bg-[#f7f2ea] p-4 dark:bg-gray-950">
                            <input type="checkbox" name="send_invitation" value="1" @checked(old('send_invitation', true)) class="mt-1 rounded border-[#d8c7b2] text-[#d97706] focus:ring-[#d97706]">
                            <span><span class="block text-sm font-extrabold text-[#171717] dark:text-white">Send password setup invitation</span><span class="mt-1 block text-xs leading-5 text-[#6b5f52] dark:text-gray-400">Uses the password-reset flow, so no password is exposed to the superadmin.</span></span>
                        </label>
                    </div>
                </section>

                <section class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                    <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Studio identity</h2>
                    <div class="mt-5 space-y-4">
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Studio name</span><input name="studio_name" value="{{ old('studio_name') }}" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"></label>
                        <label class="block space-y-2">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Subdomain</span>
                            <div class="flex rounded-2xl border border-[#eadfce] bg-white focus-within:border-[#d97706] dark:border-gray-700 dark:bg-gray-950">
                                <input name="subdomain" value="{{ old('subdomain') }}" required minlength="3" maxlength="40" pattern="[a-z0-9-]+" class="min-w-0 flex-1 rounded-l-2xl border-0 bg-transparent text-sm font-bold focus:ring-0 dark:text-white">
                                <span class="flex items-center border-l border-[#eadfce] px-3 text-xs font-bold text-[#9a8c7d] dark:border-gray-700">.{{ $rootDomain }}</span>
                            </div>
                        </label>
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Timezone</span><select name="timezone" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white">@foreach($timezoneOptions as $value => $label)<option value="{{ $value }}" @selected(old('timezone', 'Asia/Kuala_Lumpur') === $value)>{{ $label }}</option>@endforeach</select></label>
                        <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Currency</span><select name="currency" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white">@foreach($currencyOptions as $value => $label)<option value="{{ $value }}" @selected(old('currency', 'MYR') === $value)>{{ $label }}</option>@endforeach</select></label>
                    </div>
                </section>
            </div>

            <section class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Plan and access</h2>
                <p class="mt-1 text-sm text-[#6b5f52] dark:text-gray-400">This is a manual entitlement. It does not create a fake Stripe payment or provider subscription.</p>
                <div class="mt-5 grid gap-4 md:grid-cols-3">
                    <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Platform plan</span><select name="platform_subscription_plan_id" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"><option value="">Select a plan</option>@foreach($plans as $plan)<option value="{{ $plan->id }}" @selected((string) old('platform_subscription_plan_id') === (string) $plan->id)>{{ $plan->name }} — {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}/{{ $plan->billing_interval }}</option>@endforeach</select></label>
                    <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Initial status</span><select name="status" required class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white">@foreach(['trial' => 'Trial', 'active' => 'Active', 'inactive' => 'Inactive'] as $value => $label)<option value="{{ $value }}" @selected(old('status', 'trial') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label class="block space-y-2"><span class="text-xs font-extrabold uppercase tracking-wider text-[#9a8c7d]">Manual access end (optional)</span><input type="date" name="subscription_ends_at" value="{{ old('subscription_ends_at') }}" class="w-full rounded-2xl border-[#eadfce] bg-white text-sm font-bold dark:border-gray-700 dark:bg-gray-950 dark:text-white"></label>
                </div>
            </section>

            <div class="flex flex-wrap justify-end gap-3">
                <a href="{{ route('superadmin.studios.index') }}" class="rounded-2xl border border-[#eadfce] px-5 py-3 text-sm font-extrabold text-[#6b5f52] dark:border-gray-800 dark:text-gray-300">Cancel</a>
                <button class="rounded-2xl bg-[#d97706] px-6 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-[#b96305]">Create Client Studio</button>
            </div>
        </form>
    </div>
</x-app-layout>
