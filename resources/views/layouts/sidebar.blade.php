@php
$sidebarLinks = [];

switch (Auth::user()->role) {
    case 'admin':
        $sidebarLinks = [
            [
                'label' => 'Dashboard',
                'route' => 'admin.dashboard',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
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
                'icon'  => 'M21 11h-3V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v13c0 1.65 1.35 3 3 3h14c1.65 0 3-1.35 3-3v-6c0-.55-.45-1-1-1M5 19c-.55 0-1-.45-1-1V5h12v13a3 3 0 0 0 .17 1zm15-1c0 .55-.45 1-1 1s-1-.45-1-1v-5h2z',
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
            [
                'label' => 'Dashboard',
                'route' => 'teacher.dashboard',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'
            ],
            [
                'label' => 'My Classes',
                'route' => 'teacher.classes.index',
                'icon'  => 'M8 19h-3a2 2 0 0 1 -2 -2v-10a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v11a1 1 0 0 1 -1 1 M11 16m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v1a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z'
            ],
            [
                'label' => 'My Plans',
                'route' => 'teacher.plans.index',
                'icon'  => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'
            ],
            [
                'label' => 'Class Cards',
                'route' => 'teacher.classcards.index',
                'icon'  => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'
            ],
            [
                'label' => 'Schedule',
                'route' => 'teacher.schedule.index',
                'icon'  => 'M8 7V3m8 4V3M4 11h16M6 21h12a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'
            ],
        ];
        break;

    case 'student':
        $sidebarLinks = [
            ['label' => 'Dashboard', 'route' => 'student.dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['label' => 'Attendance', 'route' => 'student.attendance.index', 'icon' => 'M9 12h6m-6 4h6m-7 5h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2z'],
            ['label' => 'Schedule', 'route' => 'student.schedule.index', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z'],
            ['label' => 'Payments', 'route' => 'student.payments.index', 'icon' => 'M21 11h-3V5c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v13c0 1.65 1.35 3 3 3h14c1.65 0 3-1.35 3-3v-6c0-.55-.45-1-1-1M5 19c-.55 0-1-.45-1-1V5h12v13a3 3 0 0 0 .17 1zm15-1c0 .55-.45 1-1 1s-1-.45-1-1v-5h2z'],
            ['label' => 'My Profile', 'route' => 'profile.edit', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ];
        break;
}
@endphp

<div class="flex flex-col h-full bg-white dark:bg-gray-800 transition-all duration-300"
     :class="collapsed ? 'w-20' : 'w-64'">
    
    <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto">
        @foreach($sidebarLinks as $link)
            @if(isset($link['subLinks']))
                <div x-data="{ open: false }">
                    {{-- Dropdown Button --}}
                    <button @click="collapsed ? (collapsed = false, open = true) : open = !open" 
                        class="flex items-center justify-between w-full p-3 rounded-lg 
                               text-gray-600 dark:text-gray-300 
                               hover:bg-gray-600 hover:text-white 
                               dark:hover:bg-gray-500 dark:hover:text-white 
                               transition-all duration-200 group">
                        
                        <div class="flex items-center gap-3">
                            <svg class="w-6 h-6 shrink-0 text-gray-400 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="{{ $link['icon'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span x-show="!collapsed" x-transition.opacity class="font-medium whitespace-nowrap">{{ $link['label'] }}</span>
                        </div>

                        <svg x-show="!collapsed" :class="open ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    {{-- Dropdown Content --}}
                    <div x-show="open && !collapsed" x-cloak x-collapse class="mt-1 ml-10 space-y-1">
                        @foreach($link['subLinks'] as $sub)
                            <a href="{{ route($sub['route']) }}"
                               class="block p-2 text-sm text-gray-500 dark:text-gray-400 rounded-md 
                                      hover:text-indigo-600 dark:hover:text-white 
                                      hover:bg-indigo-50 dark:hover:bg-gray-700 transition-all">
                                {{ $sub['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                {{-- Standard Link --}}
                <a href="{{ $link['route'] == '#' ? '#' : route($link['route']) }}"
                   class="{{ request()->routeIs($link['route']) 
                        ? 'bg-indigo-50 text-indigo-600 dark:bg-indigo-900/20 dark:text-indigo-400' 
                        : 'text-gray-600 dark:text-gray-300 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500 dark:hover:text-white' 
                    }} flex items-center gap-3 p-3 rounded-lg transition-all duration-200 group">
                    
                    <svg class="w-6 h-6 shrink-0 {{ request()->routeIs($link['route']) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-white' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="{{ $link['icon'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                    <span x-show="!collapsed" x-transition.opacity class="font-medium whitespace-nowrap">{{ $link['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>
</div>