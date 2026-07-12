<x-app-layout>
    <div class="min-h-screen space-y-6 bg-gray-50/60 p-6 dark:bg-gray-900 sm:p-8">
        <div class="rounded-3xl border border-amber-200 bg-gradient-to-r from-amber-50 to-orange-50 p-6 dark:border-amber-900/50 dark:from-amber-950/30 dark:to-orange-950/20">
            <p class="text-xs font-extrabold uppercase tracking-[0.25em] text-amber-700 dark:text-amber-300">Grow your studio</p>
            <h1 class="mt-2 text-2xl font-extrabold text-gray-900 dark:text-white">Upgrade your subscription</h1>
            <p class="mt-2 text-sm font-medium text-gray-600 dark:text-gray-300">Your current plan is <strong>{{ $studio->platformSubscriptionPlan?->name ?? $studio->plan_name ?? 'not assigned' }}</strong>. Choose a plan with more seats as your studio grows.</p>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            @foreach(['student' => 'Students', 'teacher' => 'Teachers', 'admin' => 'Admins'] as $role => $label)
                @php($seat = $usage[$role])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-200">{{ $label }}</span>
                        <span class="text-xs font-extrabold {{ $seat['full'] ? 'text-red-600' : 'text-amber-600' }}">{{ $seat['unlimited'] ? 'Unlimited' : $seat['used'].' / '.$seat['limit'] }}</span>
                    </div>
                    @unless($seat['unlimited'])
                        <div class="mt-3 h-2 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700"><div class="h-full rounded-full bg-amber-500" style="width: {{ $seat['percentage'] }}%"></div></div>
                    @endunless
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            @forelse($plans as $plan)
                @php($current = $studio->platform_subscription_plan_id === $plan->id)
                <div class="relative rounded-3xl border {{ $current ? 'border-amber-500 ring-2 ring-amber-200' : 'border-gray-200' }} bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    @if($current)<span class="absolute right-4 top-4 rounded-full bg-amber-100 px-3 py-1 text-xs font-extrabold text-amber-800">Current plan</span>@endif
                    <h2 class="text-xl font-extrabold text-gray-900 dark:text-white">{{ $plan->name }}</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                    <div class="mt-5 text-3xl font-extrabold text-gray-900 dark:text-white">{{ $plan->currency }} {{ number_format($plan->price, 2) }}</div>
                    <div class="text-xs font-bold uppercase text-gray-400">per {{ $plan->billing_interval }}</div>
                    <ul class="mt-5 space-y-2 text-sm font-semibold text-gray-600 dark:text-gray-300">
                        <li>{{ $plan->max_students === null ? 'Unlimited' : number_format($plan->max_students) }} students</li>
                        <li>{{ $plan->max_teachers === null ? 'Unlimited' : number_format($plan->max_teachers) }} teachers</li>
                        <li>{{ $plan->max_admins === null ? 'Unlimited' : number_format($plan->max_admins) }} admins</li>
                    </ul>
                    @unless($current)
                        <a href="mailto:{{ config('mail.from.address') }}?subject={{ rawurlencode('Plan upgrade request - '.$studio->name) }}&body={{ rawurlencode('I would like to upgrade to '.$plan->name.'. Studio: '.$studio->name) }}" class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-amber-600 px-4 py-3 text-sm font-extrabold text-white transition hover:bg-amber-700">Request upgrade</a>
                    @endunless
                </div>
            @empty
                <div class="lg:col-span-3 rounded-2xl border border-gray-200 bg-white p-8 text-center text-sm font-semibold text-gray-500 dark:border-gray-700 dark:bg-gray-800">No upgrade plans are currently available.</div>
            @endforelse
        </div>
    </div>
</x-app-layout>
