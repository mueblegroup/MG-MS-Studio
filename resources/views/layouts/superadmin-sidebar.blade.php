@php
    $brandName = config('app.name', 'Mueble LMS');
    $brandInitials = 'MG';

    $superadminLinks = [
        ['label' => 'Dashboard', 'route' => 'superadmin.dashboard', 'active' => 'superadmin.dashboard', 'icon' => 'bx-grid-alt'],
        ['label' => 'Studios', 'route' => 'superadmin.studios.index', 'active' => 'superadmin.studios.*', 'icon' => 'bx-buildings'],
        ['label' => 'Platform Users', 'route' => 'superadmin.users.index', 'active' => 'superadmin.users.*', 'icon' => 'bx-user-circle'],
        ['label' => 'Domains & Routing', 'route' => 'superadmin.domains.index', 'active' => 'superadmin.domains.*', 'icon' => 'bx-git-branch'],
        ['label' => 'SaaS Plans', 'route' => 'superadmin.subscription-plans.index', 'active' => 'superadmin.subscription-plans.*', 'icon' => 'bx-purchase-tag-alt'],
        ['label' => 'SaaS Payments', 'route' => 'superadmin.platform-payments.index', 'active' => 'superadmin.platform-payments.*', 'icon' => 'bx-wallet'],
    ];
@endphp

<div class="flex h-full min-h-0 w-full flex-col overflow-hidden bg-white text-gray-600 dark:bg-gray-900 dark:text-gray-100 md:fixed md:inset-y-0 md:left-0 md:z-40 md:h-screen"
     :class="collapsed ? 'md:w-20' : 'md:w-64'">
    <div class="flex h-16 shrink-0 items-center border-b border-[#eadfce] px-4 dark:border-gray-800">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#d97706] text-sm font-extrabold text-white">
                {{ $brandInitials }}
            </div>
            <div x-show="!collapsed" class="min-w-0">
                <div class="truncate text-sm font-extrabold text-[#171717] dark:text-white">{{ $brandName }}</div>
                <div class="truncate text-xs font-medium text-[#6b5f52] dark:text-gray-400">Superadmin</div>
            </div>
        </div>
    </div>

    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach($superadminLinks as $link)
            @php($isActive = request()->routeIs($link['active']))
            <a href="{{ route($link['route']) }}"
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold {{ $isActive ? 'bg-[#fff3df] text-[#d97706] dark:bg-gray-800 dark:text-amber-300' : 'text-[#6b5f52] hover:bg-[#f7f2ea] hover:text-[#171717] dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white' }}">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center">
                    <i class="bx {{ $link['icon'] }} text-xl"></i>
                </span>
                <span x-show="!collapsed" class="truncate">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="shrink-0 border-t border-[#eadfce] p-3 dark:border-gray-800">
        <a href="{{ route('profile.edit') }}"
           class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-[#6b5f52] hover:bg-[#f7f2ea] hover:text-[#171717] dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center">
                <i class="bx bx-user text-xl"></i>
            </span>
            <span x-show="!collapsed" class="min-w-0">
                <span class="block truncate text-sm font-semibold">{{ Auth::user()->name }}</span>
                <span class="block truncate text-xs text-[#9a8c7d] dark:text-gray-500">Superadmin Account</span>
            </span>
        </a>
    </div>
</div>
