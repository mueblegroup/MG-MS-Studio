@php
$sidebarLinks = [];
$brandName = config('app.name', 'Mueble LMS');

try {
    if (\Illuminate\Support\Facades\Schema::hasTable('studio_settings')) {
        $brandName = app(\App\Services\StudioSettingsService::class)->get('studio_name', $brandName) ?: $brandName;
    }
} catch (\Throwable $e) {
    $brandName = config('app.name', 'Mueble LMS');
}

$brandInitials = collect(explode(' ', preg_replace('/[^A-Za-z0-9 ]/', ' ', $brandName)))
    ->filter()
    ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
    ->take(2)
    ->implode('') ?: 'APP';

switch (Auth::user()->role) {
    case 'admin':
        $sidebarLinks = [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 001 1v4a1 1 0 001 1m-6 0h6'
            ],
            [
                'label' => 'Manage Users',
                'icon'  => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'subLinks' => [
                    ['label' => 'Admins', 'route' => 'admin.admins'],
                    ['label' => 'Teachers', 'route' => 'admin.teachers'],
                    ['label' => 'Students', 'route' => 'admin.students'],
                ]
            ],
            [
                'label' => 'Manage Classes',
                'icon'  => 'M8 19h-3a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v11a1 1 0 0 1 -1 1 M11 16m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z',
                'subLinks' => [
                    ['label' => 'Classes', 'route' => 'admin.classes'],
                    ['label' => 'Plans', 'route' => 'admin.plans'],
                    ['label' => 'Class Cards', 'route' => 'admin.classcards.index'],
                ]
            ],
            [
                'label' => 'Payments',
                'route' => 'payments.index',
                'icon'  => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
            ],
            [
                'label' => 'Notifications',
                'url' => '/admin/notifications',
                'active' => 'admin.notifications.*',
                'icon'  => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9'
            ],
            [
                'label' => 'API Management',
                'route' => 'admin.api-tokens.index',
                'active' => 'admin.api-tokens.*',
                'icon'  => 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'
            ],
            [
                'label' => 'Studio Settings',
                'route' => 'settings.studio',
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'
            ],
        ];
        break;

    case 'teacher':
        $sidebarLinks = [
            ['label' => 'Dashboard', 'route' => 'teacher.dashboard', 'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['label' => 'My Classes', 'route' => 'teacher.classes.index', 'icon'  => 'M8 19h-3a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v11a1 1 0 0 1 -1 1 M11 16m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z'],
            ['label' => 'My Plans', 'route' => 'teacher.plans.index', 'icon'  => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'],
            ['label' => 'Class Cards', 'route' => 'teacher.classcards.index', 'icon'  => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'],
            ['label' => 'Schedule', 'route' => 'teacher.schedule.index', 'icon'  => 'M8 7V3m8 4V3M4 11h16M6 21h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
            ['label' => 'Notifications', 'url' => '/notifications', 'active' => 'notifications.*', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9'],
        ];
        break;

    case 'student':
        $sidebarLinks = [
            ['label' => 'Dashboard', 'route' => 'student.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['label' => 'Attendance', 'route' => 'student.attendance.index', 'icon' => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'],
            ['label' => 'Schedule', 'route' => 'student.schedule.index', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
            ['label' => 'My Class Cards', 'route' => 'student.classcards.index', 'icon' => 'M3 7a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7zm3 3h6m-6 4h10'],
            ['label' => 'Payments', 'route' => 'student.payments.index', 'icon' => 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
            ['label' => 'Notifications', 'url' => '/notifications', 'active' => 'notifications.*', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9'],
            ['label' => 'My Profile', 'route' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ];
        break;
}
@endphp

<div class="flex h-full min-h-0 flex-col bg-white transition-all duration-300 dark:bg-gray-900">
    <div class="flex h-16 shrink-0 items-center border-b border-[#eadfce] px-4 dark:border-gray-800">
        <div class="flex min-w-0 items-center gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-[#d97706] text-sm font-extrabold text-white shadow-sm">{{ $brandInitials }}</div>
            <div x-show="!collapsed" x-transition.opacity class="min-w-0">
                <div class="truncate text-sm font-extrabold text-[#171717] dark:text-white">{{ $brandName }}</div>
                <div class="truncate text-xs font-medium text-[#6b5f52] dark:text-gray-400">Studio System</div>
            </div>
        </div>
    </div>

    <nav class="min-h-0 flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @foreach($sidebarLinks as $link)
            @if(isset($link['subLinks']))
                <div x-data="{ open: false }">
                    <button @click="collapsed ? (collapsed = false, open = true) : open = !open" class="group flex w-full items-center justify-between rounded-xl p-3 text-[#6b5f52] transition-all duration-200 hover:bg-[#fff3df] hover:text-[#9a4f00] dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-amber-200">
                        <div class="flex min-w-0 items-center gap-3">
                            <svg class="h-5 w-5 shrink-0 text-[#9a8c7d] group-hover:text-[#d97706] dark:text-gray-500 dark:group-hover:text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $link['icon'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-show="!collapsed" x-transition.opacity class="truncate text-sm font-bold">{{ $link['label'] }}</span>
                        </div>
                        <svg x-show="!collapsed" :class="open ? 'rotate-180' : ''" class="h-4 w-4 shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div x-show="open && !collapsed" x-cloak x-collapse class="mt-1 space-y-1 pl-10">
                        @foreach($link['subLinks'] as $sub)
                            <a href="{{ route($sub['route']) }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-[#6b5f52] transition hover:bg-[#fff3df] hover:text-[#9a4f00] dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-amber-200">
                                {{ $sub['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $isActive = isset($link['active']) ? request()->routeIs($link['active']) : request()->routeIs($link['route']);
                    $href = isset($link['url']) ? url($link['url']) : ($link['route'] == '#' ? '#' : route($link['route']));
                @endphp

                <a href="{{ $href }}" class="{{ $isActive ? 'bg-[#fff3df] text-[#9a4f00] ring-1 ring-[#f4d7ae] dark:bg-amber-950/30 dark:text-amber-200 dark:ring-amber-900/40' : 'text-[#6b5f52] hover:bg-[#fff3df] hover:text-[#9a4f00] dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-amber-200' }} group flex items-center gap-3 rounded-xl p-3 transition-all duration-200">
                    <svg class="h-5 w-5 shrink-0 {{ $isActive ? 'text-[#d97706] dark:text-amber-300' : 'text-[#9a8c7d] group-hover:text-[#d97706] dark:text-gray-500 dark:group-hover:text-amber-300' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="{{ $link['icon'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>

                    <span x-show="!collapsed" x-transition.opacity class="truncate text-sm font-bold">{{ $link['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</div>
