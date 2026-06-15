<x-app-layout>
    <div class="min-h-screen space-y-6 bg-[#f7f2ea] dark:bg-gray-950">
        <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
                <div>
                    <p class="text-xs font-extrabold uppercase tracking-[0.3em] text-[#d97706]">Platform Pricing</p>
                    <h1 class="mt-2 text-2xl font-extrabold text-[#171717] dark:text-white">Subscription Plans</h1>
                    <p class="mt-1 max-w-3xl text-sm font-medium text-[#6b5f52] dark:text-gray-400">Create and update the SaaS subscription prices used by studios. This is owner-level pricing, not class/student pricing inside a studio.</p>
                </div>
                <a href="{{ route('superadmin.dashboard') }}" class="rounded-2xl bg-[#171717] px-4 py-3 text-sm font-extrabold text-white shadow-sm dark:bg-white dark:text-gray-950">Back to Dashboard</a>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-4 rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">Add New Plan</h2>
                <form method="POST" action="{{ route('superadmin.subscription-plans.store') }}" class="mt-6 space-y-4">
                    @csrf
                    @include('superadmin.subscription-plans.partials.form', ['plan' => null, 'button' => 'Create Plan'])
                </form>
            </div>

            <div class="xl:col-span-8 space-y-5">
                @forelse($plans as $plan)
                    <div class="rounded-3xl border border-[#eadfce] bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                        <div class="flex flex-col justify-between gap-3 lg:flex-row lg:items-start">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">{{ $plan->name }}</h2>
                                    <span class="rounded-full bg-[#fff3df] px-3 py-1 text-xs font-extrabold uppercase text-[#9a4f00] dark:bg-amber-950/30 dark:text-amber-200">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                                </div>
                                <p class="mt-1 text-sm font-medium text-[#9a8c7d] dark:text-gray-500">{{ $plan->studios_count }} studios using this plan</p>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-extrabold text-[#171717] dark:text-white">{{ $plan->currency }} {{ number_format((float) $plan->price, 2) }}</div>
                                <div class="text-xs font-extrabold uppercase text-[#9a8c7d] dark:text-gray-500">{{ $plan->billing_interval }}</div>
                            </div>
                        </div>

                        <form method="POST" action="{{ route('superadmin.subscription-plans.update', $plan) }}" class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2">
                            @csrf
                            @method('PATCH')
                            @include('superadmin.subscription-plans.partials.form', ['plan' => $plan, 'button' => 'Update Plan'])
                        </form>
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
