<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Pricing</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Subscription Plans</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Create, edit, deactivate, remove, and optionally attach a free trial to each SaaS subscription plan.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 xl:col-span-4">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Add New Plan</h2>
                <form method="POST" action="{{ route('superadmin.subscription-plans.store') }}" class="mt-6 space-y-4">
                    @csrf
                    @include('superadmin.subscription-plans.partials.form', ['plan' => null, 'button' => 'Create Plan'])
                </form>
                <p class="mt-4 text-xs font-medium leading-5 text-[#9a8c7d] dark:text-gray-500">After creating the plan, configure its optional free-trial period from the plan card.</p>
            </div>

            <div class="space-y-5 xl:col-span-8">
                @forelse($plans as $plan)
                    <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">{{ $plan->name }}</h2>
                                    <span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                                    @if((int) $plan->trial_days > 0)
                                        <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-extrabold uppercase text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">{{ $plan->trial_days }}-day trial</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-extrabold uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300">No trial</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm font-medium text-[#9a8c7d] dark:text-gray-500">
                                    {{ $plan->studios_count }} {{ Str::plural('studio', $plan->studios_count) }} using this plan
                                    <span class="mx-1">•</span>
                                    {{ $plan->payments_count }} payment {{ Str::plural('record', $plan->payments_count) }}
                                </p>
                            </div>
                            <div class="lg:text-right">
                                <div class="text-2xl font-extrabold text-[#171717] dark:text-white">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                                <div class="text-xs font-extrabold uppercase text-[#9a8c7d] dark:text-gray-500">{{ $plan->billing_interval }}</div>
                            </div>
                        </div>

                        @if($plan->description)
                            <p class="mt-4 text-sm font-medium leading-6 text-[#6b5f52] dark:text-gray-400">{{ $plan->description }}</p>
                        @endif

                        <form method="POST" action="{{ route('superadmin.subscription-plans.trial.update', $plan) }}" class="mt-5 flex flex-col gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900/60 dark:bg-emerald-950/20 sm:flex-row sm:items-end">
                            @csrf
                            @method('PATCH')
                            <label class="flex-1 space-y-2">
                                <span class="text-xs font-extrabold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Free trial period</span>
                                <input type="number" name="trial_days" min="0" max="365" value="{{ old('trial_days', $plan->trial_days ?? 0) }}" class="w-full rounded-2xl border-emerald-200 bg-white text-sm font-bold text-[#171717] shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-emerald-900 dark:bg-gray-950 dark:text-white">
                                <span class="block text-xs font-medium text-emerald-700 dark:text-emerald-400">Enter 0 to disable. Applied only when a studio starts a new Stripe subscription.</span>
                            </label>
                            <button type="submit" class="rounded-2xl bg-emerald-600 px-4 py-3 text-sm font-extrabold text-white shadow-sm transition hover:bg-emerald-700">Save Trial</button>
                        </form>

                        <div class="mt-5 flex flex-wrap items-center gap-3 border-t border-[#f0e6d8] pt-5 dark:border-gray-800">
                            <details class="group min-w-0 flex-1">
                                <summary class="inline-flex cursor-pointer list-none items-center rounded-2xl bg-[#171717] px-4 py-2.5 text-sm font-extrabold text-white shadow-sm transition hover:opacity-90 dark:bg-white dark:text-gray-950">
                                    <span class="group-open:hidden">Edit Plan</span>
                                    <span class="hidden group-open:inline">Close Editor</span>
                                </summary>

                                <form method="POST" action="{{ route('superadmin.subscription-plans.update', $plan) }}" class="mt-5 grid grid-cols-1 gap-4 rounded-2xl border border-[#eadfce] bg-[#fcfaf7] p-5 dark:border-gray-800 dark:bg-gray-950 md:grid-cols-2">
                                    @csrf
                                    @method('PATCH')
                                    @include('superadmin.subscription-plans.partials.form', ['plan' => $plan, 'button' => 'Save Changes'])
                                </form>
                            </details>

                            <form method="POST" action="{{ route('superadmin.subscription-plans.update', $plan) }}" onsubmit="return confirm('Delete {{ addslashes($plan->name) }}? This cannot be undone. Plans assigned to studios or linked to payment history cannot be deleted.');">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="delete_plan" value="1">
                                <button type="submit" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-extrabold text-red-700 transition hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-950/50">
                                    Delete Plan
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl border border-[#eadfce] bg-white p-8 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <p class="text-sm font-bold text-[#9a8c7d] dark:text-gray-500">No platform subscription plans yet. Create the first plan on the left.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
