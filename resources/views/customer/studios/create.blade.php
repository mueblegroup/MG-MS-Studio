<x-customer-layout>
    <div class="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
        <section class="rounded-[2rem] bg-slate-950 p-6 text-white shadow-xl shadow-slate-950/10 lg:p-8">
            <p class="text-sm font-bold uppercase tracking-[0.25em] text-orange-300">Studio Onboarding</p>
            <h1 class="mt-3 text-3xl font-black tracking-tight sm:text-4xl">Subscribe first, then create your studio.</h1>
            <p class="mt-4 text-sm leading-6 text-slate-300">Choose a platform plan and complete the first Stripe payment. Your tenant workspace and subdomain are created only after Stripe confirms the payment.</p>

            <div class="mt-8 space-y-4">
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">No unpaid workspaces</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">Submitting this form redirects you to Stripe. The studio does not exist until payment succeeds.</p>
                </div>
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">Automatic renewal</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">The selected subscription renews automatically according to its monthly or annual billing interval.</p>
                </div>
                <div class="rounded-3xl bg-white/10 p-4 ring-1 ring-white/10">
                    <p class="font-black">1 user = 1 studio</p>
                    <p class="mt-1 text-sm leading-6 text-slate-300">Each client administrator account can own one studio workspace.</p>
                </div>
            </div>
        </section>

        <section class="rounded-[2rem] bg-white p-6 shadow-sm ring-1 ring-black/5 dark:bg-slate-900 dark:ring-white/10 lg:p-8">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-orange-500">Setup & Payment</p>
                    <h2 class="mt-2 text-2xl font-black text-slate-950 dark:text-white">Studio details</h2>
                </div>
                <a href="{{ route('customer.dashboard') }}" class="rounded-2xl border border-slate-200 px-4 py-2 text-sm font-black text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Cancel</a>
            </div>

            @if(session('error'))
                <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-200">{{ session('error') }}</div>
            @endif

            @if(request('checkout') === 'cancelled')
                <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm font-bold text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/30 dark:text-amber-200">Payment was cancelled. No studio was created.</div>
            @endif

            <form method="POST" action="{{ route('customer.studios.store') }}" class="mt-6 space-y-5">
                @csrf
                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Studio Name</label>
                    <input type="text" name="studio_name" value="{{ old('studio_name') }}" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white" placeholder="Etude Music Academy">
                    @error('studio_name') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Studio Subdomain</label>
                    <div class="flex rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-800">
                        <input type="text" name="subdomain" value="{{ old('subdomain') }}" required class="min-w-0 flex-1 rounded-l-2xl border-0 px-4 py-3 text-sm font-semibold dark:bg-slate-800 dark:text-white" placeholder="etude2">
                        <span class="inline-flex items-center rounded-r-2xl bg-slate-50 px-4 text-sm font-black text-slate-500 dark:bg-slate-950 dark:text-slate-400">.{{ $rootDomain }}</span>
                    </div>
                    <p class="mt-2 text-xs font-semibold text-slate-500">Reserved for 30 minutes while Stripe Checkout is open.</p>
                    @error('subdomain') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Platform Subscription Plan</label>
                    <select name="platform_subscription_plan_id" required class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                        <option value="">Select a paid plan</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('platform_subscription_plan_id') == $plan->id)>{{ $plan->name }} - {{ $plan->currency }} {{ number_format((float) $plan->price, 2) }} / {{ $plan->billing_interval }}</option>
                        @endforeach
                    </select>
                    @error('platform_subscription_plan_id') <p class="mt-1 text-sm font-semibold text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Timezone</label>
                        <input type="text" name="timezone" value="{{ old('timezone', config('app.timezone')) }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-black text-slate-700 dark:text-slate-300">Currency</label>
                        <input type="text" name="currency" maxlength="3" value="{{ old('currency', 'MYR') }}" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm font-semibold uppercase dark:border-slate-700 dark:bg-slate-800 dark:text-white">
                    </div>
                </div>

                <div class="rounded-3xl bg-orange-50 p-4 text-sm leading-6 text-orange-800 dark:bg-orange-950/40 dark:text-orange-200">
                    Clicking below opens Stripe Checkout. The first subscription charge is collected immediately. The studio and login domain are provisioned only after payment confirmation.
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black text-white shadow-lg shadow-orange-500/20 transition hover:bg-orange-600">Continue to Payment</button>
                </div>
            </form>
        </section>
    </div>
</x-customer-layout>
