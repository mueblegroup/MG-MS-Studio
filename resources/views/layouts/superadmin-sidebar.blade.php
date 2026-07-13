@php
    $brandName = config('app.name', 'Mueble LMS');
    $brandInitials = 'MG';

    $superadminLinks = [
        [
            'label' => 'Platform Dashboard',
            'route' => 'superadmin.dashboard',
            'active' => 'superadmin.dashboard',
            'icon' => 'bx-grid-alt',
            'description' => 'Owner overview',
        ],
        [
            'label' => 'Studios',
            'route' => 'superadmin.studios.index',
            'active' => 'superadmin.studios.*',
            'icon' => 'bx-buildings',
            'description' => 'Create, review and manage studios',
        ],
        [
            'label' => 'Platform Users',
            'route' => 'superadmin.users.index',
            'active' => 'superadmin.users.*',
            'icon' => 'bx-user-circle',
            'description' => 'Superadmins, studio admins, teachers and students',
        ],
        [
            'label' => 'Domains & Routing',
            'route' => 'superadmin.domains.index',
            'active' => 'superadmin.domains.*',
            'icon' => 'bx-git-branch',
            'description' => 'Subdomains, custom domains and verification',
        ],
        [
            'label' => 'SaaS Plans',
            'route' => 'superadmin.subscription-plans.index',
            'active' => 'superadmin.subscription-plans.*',
            'icon' => 'bx-purchase-tag-alt',
            'description' => 'Platform pricing and limits',
        ],
        [
            'label' => 'SaaS Payments',
            'route' => 'superadmin.platform-payments.index',
            'active' => 'superadmin.platform-payments.*',
            'icon' => 'bx-wallet',
            'description' => 'Studio subscription billing',
        ],
    ];

    $systemLinks = [
        ['label' => 'Tenant Isolation', 'icon' => 'bx-shield-quarter', 'note' => 'Owner routes stay on the central domain only'],
        ['label' => 'Billing Control', 'icon' => 'bx-credit-card', 'note' => 'Plans, subscriptions and payment status'],
        ['label' => 'Access Control', 'icon' => 'bx-lock-alt', 'note' => 'Superadmin role separated from studio roles'],
    ];
@endphp

<div class="flex h-full min-h-0 w-full flex-col overflow-hidden bg-[#111827] text-white transition-[width] duration-300 md:fixed md:inset-y-0 md:left-0 md:z-40 md:h-screen"
     :class="collapsed ? 'md:w-20' : 'md:w-64'">
    <div class="flex h-16 shrink-0 items-center border-b border-white/10 px-4">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#d97706] text-sm font-extrabold text-white shadow-sm shadow-amber-900/30">{{ $brandInitials }}</div>
            <div x-show="!collapsed" x-transition.opacity class="min-w-0">
                <div class="truncate text-sm font-extrabold text-white">{{ $brandName }}</div>
                <div class="truncate text-xs font-bold uppercase tracking-wider text-amber-300">Superadmin Console</div>
            </div>
        </div>
    </div>

    <div x-show="!collapsed" x-transition.opacity class="shrink-0 px-4 py-4">
        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 shadow-sm">
            <div class="text-xs font-extrabold uppercase tracking-[0.25em] text-amber-300">Owner Level</div>
            <p class="mt-2 text-xs font-medium leading-5 text-gray-300">This menu controls the whole SaaS platform. It is intentionally separated from the studio admin sidebar.</p>
        </div>
    </div>

    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-3 pb-4 [scrollbar-color:rgba(255,255,255,0.18)_transparent] [scrollbar-width:thin]">
        @foreach($superadminLinks as $link)
            @php
                $isActive = request()->routeIs($link['active']);
            @endphp
            <a href="{{ route($link['route']) }}" class="{{ $isActive ? 'bg-white text-[#111827] shadow-sm' : 'text-gray-300 hover:bg-white/10 hover:text-white' }} group flex items-center gap-3 rounded-2xl p-3 transition-all duration-200">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isActive ? 'bg-[#fff3df] text-[#d97706]' : 'bg-white/5 text-amber-300 group-hover:bg-white/10' }}">
                    <i class="bx {{ $link['icon'] }} text-xl"></i>
                </span>
                <span x-show="!collapsed" x-transition.opacity class="min-w-0">
                    <span class="block truncate text-sm font-extrabold">{{ $link['label'] }}</span>
                    <span class="block truncate text-xs font-medium {{ $isActive ? 'text-gray-500' : 'text-gray-400' }}">{{ $link['description'] }}</span>
                </span>
            </a>
        @endforeach

        <div x-show="!collapsed" x-transition.opacity class="px-1 pt-5">
            <div class="mb-2 px-2 text-[11px] font-extrabold uppercase tracking-[0.25em] text-gray-500">System Scope</div>
            <div class="space-y-2">
                @foreach($systemLinks as $item)
                    <div class="rounded-2xl border border-white/10 bg-white/[0.03] p-3">
                        <div class="flex items-center gap-2 text-xs font-extrabold text-gray-200">
                            <i class="bx {{ $item['icon'] }} text-base text-amber-300"></i>
                            {{ $item['label'] }}
                        </div>
                        <p class="mt-1 text-[11px] font-medium leading-4 text-gray-500">{{ $item['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </nav>

    <div class="shrink-0 border-t border-white/10 bg-[#111827] p-3">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-2xl p-3 text-gray-300 transition hover:bg-white/10 hover:text-white">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-amber-300"><i class="bx bx-user text-xl"></i></span>
            <span x-show="!collapsed" x-transition.opacity class="min-w-0">
                <span class="block truncate text-sm font-extrabold">{{ Auth::user()->name }}</span>
                <span class="block truncate text-xs font-medium text-gray-500">Superadmin Account</span>
            </span>
        </a>
    </div>
</div>