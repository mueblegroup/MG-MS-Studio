<x-app-layout>
    @php
        $canPurchase = auth()->check() && auth()->user()->role === 'student';
        $tabs = [
            'classes' => ['label' => 'Classes', 'icon' => 'bx-calendar'],
            'plans' => ['label' => 'Plans', 'icon' => 'bx-layer'],
            'classcards' => ['label' => 'Class Cards', 'icon' => 'bx-card'],
        ];
    @endphp

    <div class="mg-page">
        <div class="mg-page-inner">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="mg-title">Shop</h1>
                    <p class="mg-subtitle mt-1">Browse classes by programme, then open a card to view its available sessions.</p>
                </div>
                <form method="GET" action="{{ route('shop.index') }}" class="flex gap-2">
                    <input type="hidden" name="tab" value="{{ $tab }}">
                    <input name="q" value="{{ $q }}" placeholder="Search..." class="mg-input min-w-0">
                    <button class="mg-btn-primary"><i class="bx bx-search"></i> Search</button>
                </form>
            </div>

            @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-3 text-green-700">{{ session('success') }}</div>@endif
            @if(session('error'))<div class="rounded-xl border border-red-200 bg-red-50 p-3 text-red-700">{{ session('error') }}</div>@endif

            <div class="flex gap-2 overflow-x-auto pb-1">
                @foreach($tabs as $key => $item)
                    <a href="{{ route('shop.index', ['tab' => $key, 'q' => $q]) }}" class="{{ $tab === $key ? 'mg-btn-primary' : 'mg-btn-secondary' }} shrink-0">
                        <i class="bx {{ $item['icon'] }}"></i> {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            @if($tab === 'classes')
                <div class="grid gap-4 lg:grid-cols-2">
                    @forelse($classes as $class)
                        @php
                            $sessions = $class->sessions;
                            $firstSession = $sessions->first();
                            $isSubscription = $class->type === 'subscription';
                        @endphp
                        <section x-data="{ open: false }" class="mg-card overflow-hidden">
                            <div class="p-5">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <h2 class="text-lg font-extrabold text-[#171717] dark:text-white">{{ $class->name }}</h2>
                                        <p class="mt-1 line-clamp-2 text-sm text-[#6b5f52] dark:text-gray-400">{{ $class->description ?: 'No description.' }}</p>
                                        <p class="mt-2 text-xs font-semibold text-[#6b5f52] dark:text-gray-400">
                                            {{ $class->teacher?->name ?? 'Teacher not assigned' }} · {{ $sessions->count() }} upcoming session{{ $sessions->count() === 1 ? '' : 's' }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold uppercase text-[#9a8c7d]">{{ $isSubscription ? 'Recurring charge' : 'Price' }}</div>
                                        <div class="mt-1 text-lg font-extrabold">RM {{ number_format((float) $class->price, 2) }}</div>
                                        @if($isSubscription)<span class="mg-badge mt-2">{{ strtoupper($class->billing_interval) }}</span>@endif
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                                    <button type="button" @click="open = !open" class="mg-btn-secondary">
                                        <i class="bx bx-list-ul"></i>
                                        <span x-text="open ? 'Hide sessions' : 'View sessions'"></span>
                                    </button>

                                    @if($canPurchase && $isSubscription && $firstSession)
                                        <form method="POST" action="{{ route('shop.cart.add') }}">
                                            @csrf
                                            <input type="hidden" name="type" value="class_session">
                                            <input type="hidden" name="id" value="{{ $firstSession->id }}">
                                            <button class="mg-btn-primary"><i class="bx bx-cart-add"></i> Subscribe</button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div x-show="open" x-cloak class="border-t border-[#eadfce] dark:border-gray-800">
                                @forelse($sessions as $session)
                                    <div class="flex flex-col gap-3 border-b border-[#eadfce] p-4 last:border-b-0 dark:border-gray-800 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <div class="font-bold">{{ $session->start_time?->format('d M Y') }}</div>
                                            <div class="text-xs text-[#6b5f52]">{{ $session->start_time?->format('h:i A') }} – {{ $session->end_time?->format('h:i A') }} · {{ $session->venue_name ?: 'Venue not set' }}</div>
                                        </div>
                                        @if($canPurchase && !$isSubscription)
                                            <form method="POST" action="{{ route('shop.cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="type" value="class_session">
                                                <input type="hidden" name="id" value="{{ $session->id }}">
                                                <button class="mg-btn-primary"><i class="bx bx-cart-add"></i> Add session</button>
                                            </form>
                                        @elseif($isSubscription)
                                            <span class="mg-badge">Included by renewal</span>
                                        @endif
                                    </div>
                                @empty
                                    <div class="p-6 text-center text-sm text-[#6b5f52]">No upcoming sessions.</div>
                                @endforelse
                            </div>
                        </section>
                    @empty
                        <div class="mg-card col-span-full p-10 text-center text-sm text-[#6b5f52]">No classes found.</div>
                    @endforelse
                </div>
                <div>{{ $classes->links() }}</div>
            @elseif($tab === 'plans')
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($plans as $plan)
                        <section class="mg-card p-5">
                            <h2 class="font-extrabold">{{ $plan->name }}</h2>
                            <p class="mt-1 text-sm text-[#6b5f52]">{{ $plan->sessions->count() }} upcoming sessions</p>
                            <div class="mt-4 text-lg font-extrabold">{{ $plan->currency ?? 'MYR' }} {{ number_format((float) $plan->price, 2) }}</div>
                            @if($canPurchase)<form method="POST" action="{{ route('shop.cart.add') }}" class="mt-4">@csrf<input type="hidden" name="type" value="plan"><input type="hidden" name="id" value="{{ $plan->id }}"><button class="mg-btn-primary w-full"><i class="bx bx-cart-add"></i> Add plan</button></form>@endif
                        </section>
                    @empty<div class="mg-card col-span-full p-10 text-center">No plans found.</div>@endforelse
                </div>
                <div>{{ $plans->links() }}</div>
            @else
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @forelse($classcards as $card)
                        <section class="mg-card p-5">
                            <h2 class="font-extrabold">{{ $card->name }}</h2>
                            <p class="mt-1 text-sm text-[#6b5f52]">{{ $card->total_classes }} classes · {{ $card->validity_weeks }} weeks</p>
                            <div class="mt-4 text-lg font-extrabold">RM {{ number_format((float) $card->price, 2) }}</div>
                            @if($canPurchase)<form method="POST" action="{{ route('shop.cart.add') }}" class="mt-4">@csrf<input type="hidden" name="type" value="class_card"><input type="hidden" name="id" value="{{ $card->id }}"><button class="mg-btn-primary w-full"><i class="bx bx-cart-add"></i> Add class card</button></form>@endif
                        </section>
                    @empty<div class="mg-card col-span-full p-10 text-center">No class cards found.</div>@endforelse
                </div>
                <div>{{ $classcards->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
