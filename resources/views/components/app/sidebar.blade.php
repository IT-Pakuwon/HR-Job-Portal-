<div class="min-w-fit">
    <!-- Sidebar backdrop (mobile only) -->
    <div class="fixed inset-0 z-40 bg-gray-900/30 transition-opacity duration-200 lg:z-auto lg:hidden"
        :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'" aria-hidden="true" x-cloak></div>

    <!-- Sidebar -->
    <div id="sidebar"
        class="lg:flex! no-scrollbar lg:sidebar-expanded:w-64 {{ $variant === 'v2' ? 'border-r border-gray-200 dark:border-gray-700/60' : '  ' }} absolute left-0 top-0 z-40 flex h-[100dvh] w-64 shrink-0 flex-col overflow-y-scroll whitespace-normal break-words bg-white p-4 leading-tight transition-all duration-200 ease-in-out lg:static lg:left-auto lg:top-auto lg:w-20 lg:translate-x-0 lg:overflow-y-auto dark:bg-gray-800"
        :class="{
            'max-lg:translate-x-0': sidebarOpen,
            'max-lg:-translate-x-64': !sidebarOpen,
            'lg:w-64': sidebarExpanded,
            'lg:w-21': !sidebarExpanded
        }"
        @click.outside="sidebarOpen = false" @keydown.escape.window="sidebarOpen = false">
        <!-- Sidebar header -->
        <div class="flex items-center justify-start gap-4 py-4 pr-3 sm:px-2">
            <button class="text-gray-500 hover:text-gray-400 lg:hidden" @click.stop="sidebarOpen = !sidebarOpen"
                aria-controls="sidebar" :aria-expanded="sidebarOpen">
                <span class="sr-only">Toggle sidebar</span>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16">
                    </path>
                </svg>
            </button>

            <button
                class="hidden items-center justify-center rounded-full bg-white p-2 text-gray-500 transition-colors duration-200 hover:bg-gray-100 lg:flex dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700"
                @click="sidebarExpanded = !sidebarExpanded; localStorage.setItem('sidebarExpanded', sidebarExpanded)">
                <span class="sr-only">Expand / collapse sidebar</span>
                <svg class="h-5 w-5 transform transition-transform duration-200"
                    :class="{ 'rotate-180': sidebarExpanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                    xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>

            <div class="flex items-center gap-3 transition-all duration-200">
                <span :class="{ 'hidden': !sidebarExpanded && window.innerWidth >= 1024 }"
                    class="break-wordstext-base whitespace-normal font-bold uppercase text-gray-700 transition-opacity duration-200 dark:text-gray-100"
                    x-show="sidebarExpanded || window.innerWidth < 1024" x-transition:enter="ease-out duration-200"
                    x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0">
                    APP System
                </span>
            </div>
        </div>
        <div class="space-y-8">
            <!-- Pages group -->
            <div>
                <ul class="mt-3">
                    <!-- Dashboard -->
                    <li class="bg-linear-to-r {{ in_array(Request::segment(1), ['dashboard'])
                        ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]'
                        : '' }} rounded-lg py-2 transition"
                        :class="{
                            'pl-4 pr-3': sidebarExpanded,
                            'px-2': !sidebarExpanded
                        }">
                        <a href="{{ route('dashboard') }}" class="group block truncate transition">
                            <div class="flex items-center gap-3 transition-all duration-200">

                                <!-- ICON (fixed column, color locked) -->
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                    <svg class="{{ in_array(Request::segment(1), ['dashboard']) ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor" stroke-width="1.5" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 3v18h18M7 13v6m4-10v10m4-14v14" />
                                    </svg>
                                </div>

                                <!-- LABEL (controls its own color, not parent) -->
                                <span x-show="sidebarExpanded" x-transition.opacity
                                    class="{{ in_array(Request::segment(1), ['dashboard']) ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} whitespace-normal break-words text-sm font-medium leading-tight">
                                    Dashboard
                                </span>

                            </div>
                        </a>
                    </li>

                    {{-- LABEL GROUP HUMAN RESOURCES --}}

                    @php
                        $hrMenu = $rootMenus->firstWhere('menu_id', 'HR');
                        $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
                    @endphp

                    {{-- ================= LABEL: HUMAN RESOURCES ================= --}}
                    @if ($hrMenu)
                        <li class="text-smm py-2 font-semibold uppercase tracking-wider text-gray-500 transition last:mb-0"
                            :class="sidebarExpanded ? 'pl-4 pr-3 block' : 'hidden'">
                            {{ $hrMenu->menu_name }}
                        </li>
                    @endif

                    @if ($hrMenu)
                        @php
                            $parentChildren = $allowedIds
                                ? $hrMenu->children->whereIn('menu_id', $allowedIds)
                                : $hrMenu->children;
                        @endphp

                        @foreach ($parentChildren as $menu)
                            @php
                                $children = $allowedIds
                                    ? $menu->children->whereIn('menu_id', $allowedIds)
                                    : $menu->children;
                            @endphp

                            {{-- ================= MENU TANPA SUB ================= --}}
                            @if ($children->isEmpty())
                                <li class="bg-linear-to-r {{ Route::is($menu->menu_route . '*')
                                    ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]'
                                    : '' }} rounded-lg py-2 transition"
                                    :class="{
                                        'pl-4 pr-3': sidebarExpanded,
                                        'px-2': !sidebarExpanded
                                    }">
                                    <a href="{{ $menu->menu_route ? route($menu->menu_route) : '#' }}"
                                        class="group block truncate transition">

                                        <div class="flex items-center gap-3 transition-all duration-200">

                                            <!-- ICON -->
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                                <svg class="{{ Route::is($menu->menu_route . '*') ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    width="16" height="16" fill="none" stroke="currentColor"
                                                    stroke-width="1.5">
                                                    <path d="{{ $menu->menu_icon }}" />
                                                </svg>
                                            </div>

                                            <!-- LABEL -->
                                            <span x-show="sidebarExpanded" x-transition.opacity
                                                class="{{ Route::is($menu->menu_route . '*') ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} text-smm whitespace-normal break-words font-medium leading-tight">
                                                {{ $menu->menu_name }}
                                            </span>

                                        </div>
                                    </a>
                                </li>

                                {{-- ================= MENU DENGAN SUB ================= --}}
                            @else
                                @php
                                    $isGroupActive = $children->contains(
                                        fn($child) => Route::is($child->menu_route . '*'),
                                    );
                                @endphp

                                <li class="bg-linear-to-r {{ $isGroupActive ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' : '' }} rounded-lg py-2 transition"
                                    :class="{
                                        'pl-4 pr-3': sidebarExpanded,
                                        'px-2': !sidebarExpanded
                                    }"
                                    x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }">
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="group block truncate transition">

                                        <div class="flex items-center justify-between">

                                            <div class="flex items-center gap-3 transition-all duration-200">

                                                <!-- ICON -->
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                                    <svg class="{{ $isGroupActive ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        width="16" height="16" fill="none"
                                                        stroke="currentColor" stroke-width="1.5">
                                                        <path d="{{ $menu->menu_icon }}" />
                                                    </svg>
                                                </div>

                                                <!-- LABEL -->
                                                <span x-show="sidebarExpanded" x-transition.opacity
                                                    class="{{ $isGroupActive ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} text-smm whitespace-normal break-words font-medium leading-tight">
                                                    {{ $menu->menu_name }}
                                                </span>
                                            </div>

                                            {{-- Arrow --}}
                                            <div class="ml-2 flex shrink-0 duration-200"
                                                :class="sidebarExpanded ? 'opacity-100' : 'opacity-0'">
                                                <svg class="ml-1 h-3 w-3 fill-current text-gray-400 dark:text-gray-500"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </div>

                                        </div>
                                    </a>

                                    {{-- ================= SUBMENU ================= --}}
                                    <div x-show="sidebarExpanded && open">
                                        <ul class="mt-1 pl-8">
                                            @foreach ($children as $child)
                                                <li class="mb-1 last:mb-0">
                                                    <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                        class="{{ Route::is($child->menu_route . '*')
                                                            ? 'text-violet-500'
                                                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} block truncate transition">
                                                        <span class="text-smm font-medium leading-tight">
                                                            {{ $child->menu_name }}
                                                        </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    @endif

                    {{-- ================= LABEL GROUP PURCHASING ================= --}}

                    @php
                        $purchasingMenu = $rootMenus->firstWhere('menu_id', 'PURCH');
                        $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
                    @endphp

                    {{-- ================= LABEL: PURCHASING ================= --}}
                    @if ($purchasingMenu)
                        <li class="text-smm py-2 font-semibold uppercase tracking-wider text-gray-500 transition last:mb-0"
                            :class="sidebarExpanded ? 'pl-4 pr-3 block' : 'hidden'">
                            {{ $purchasingMenu->menu_name }}
                        </li>
                    @endif

                    @if ($purchasingMenu)
                        @php
                            $parentChildren = $allowedIds
                                ? $purchasingMenu->children->whereIn('menu_id', $allowedIds)
                                : $purchasingMenu->children;
                        @endphp

                        {{-- ================= LOOP PURCHASING CHILDREN ================= --}}
                        @foreach ($parentChildren as $menu)
                            @php
                                $children = $allowedIds
                                    ? $menu->children->whereIn('menu_id', $allowedIds)
                                    : $menu->children;
                            @endphp

                            {{-- ================= MENU TANPA SUB ================= --}}
                            @if ($children->isEmpty())
                                <li class="bg-linear-to-r {{ Route::is($menu->menu_route . '*')
                                    ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]'
                                    : '' }} rounded-lg py-2 transition"
                                    :class="{
                                        'pl-4 pr-3': sidebarExpanded,
                                        'px-2': !sidebarExpanded
                                    }">
                                    <a href="{{ $menu->menu_route ? route($menu->menu_route) : '#' }}"
                                        class="group block truncate transition">

                                        <div class="flex items-center gap-3 transition-all duration-200">

                                            <!-- ICON -->
                                            <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                                <svg class="{{ Route::is($menu->menu_route . '*') ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                    width="16" height="16" fill="none"
                                                    stroke="currentColor" stroke-width="1.5">
                                                    <path d="{{ $menu->menu_icon }}" />
                                                </svg>
                                            </div>

                                            <!-- LABEL -->
                                            <span x-show="sidebarExpanded" x-transition.opacity
                                                class="{{ Route::is($menu->menu_route . '*') ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} text-smm whitespace-normal break-words font-medium leading-tight">
                                                {{ $menu->menu_name }}
                                            </span>

                                        </div>
                                    </a>
                                </li>

                                {{-- ================= MENU DENGAN SUB ================= --}}
                            @else
                                @php
                                    $isGroupActive = $children->contains(
                                        fn($child) => Route::is($child->menu_route . '*'),
                                    );
                                @endphp

                                <li class="bg-linear-to-r {{ $isGroupActive ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' : '' }} rounded-lg py-2 transition"
                                    :class="{
                                        'pl-4 pr-3': sidebarExpanded,
                                        'px-2': !sidebarExpanded
                                    }"
                                    x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }">
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="group block truncate transition">

                                        <div class="flex items-center justify-between">

                                            <div class="flex items-center gap-3 transition-all duration-200">

                                                <!-- ICON -->
                                                <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                                                    <svg class="{{ $isGroupActive ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0 group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                        width="16" height="16" fill="none"
                                                        stroke="currentColor" stroke-width="1.5">
                                                        <path d="{{ $menu->menu_icon }}" />
                                                    </svg>
                                                </div>

                                                <!-- LABEL -->
                                                <span x-show="sidebarExpanded" x-transition.opacity
                                                    class="{{ $isGroupActive ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} text-smm whitespace-normal break-words font-medium leading-tight">
                                                    {{ $menu->menu_name }}
                                                </span>
                                            </div>

                                            {{-- Arrow --}}
                                            <div class="ml-2 flex shrink-0 duration-200"
                                                :class="sidebarExpanded ? 'opacity-100' : 'opacity-0'">
                                                <svg class="ml-1 h-3 w-3 fill-current text-gray-400 dark:text-gray-500"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </div>

                                        </div>
                                    </a>

                                    {{-- ================= SUBMENU ================= --}}
                                    <div x-show="sidebarExpanded && open">
                                        <ul class="mt-1 pl-8">
                                            @foreach ($children as $child)
                                                <li class="mb-1 last:mb-0">
                                                    <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                        class="{{ Route::is($child->menu_route . '*')
                                                            ? 'text-violet-500'
                                                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }} block truncate transition">
                                                        <span class="text-smm font-medium leading-tight">
                                                            {{ $child->menu_name }}
                                                        </span>
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    @endif
                    {{-- ================= LABEL GROUP SETTINGS ================= --}}
                    @if (auth()->user()->user_role === 'admin')

                        @php
                            $settingsSegments = [
                                'users',
                                'roles',
                                'access_rights',
                                'role_menus',
                                'applications',
                                'screens',
                                'menus',
                                'companies',
                                'department',
                                'tenants',
                                'locations',
                                'categories',
                                'vendors',
                                'inventories',
                                'autonbrs',
                                'tops',
                                'approvals',
                                'budgetmonitor',
                                'ifcaintegration',
                            ];
                        @endphp

                        <li x-data="{ open: {{ in_array(Request::segment(1), $settingsSegments) ? 'true' : 'false' }} }" :class="sidebarExpanded ? 'block' : 'hidden'" class="mb-2">

                            {{-- SETTINGS HEADER --}}
                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                class="text-smm flex items-center justify-between px-4 py-2 font-semibold uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                                <span>Settings</span>
                                <svg class="h-3 w-3 fill-current transition-transform"
                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                </svg>
                            </a>

                            <ul x-show="open" class="mt-1 space-y-2">

                                {{-- =====================================================
            USER & ACCESS
        ===================================================== --}}
                                @php $userAccessSegments = ['users','roles','access_rights','role_menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $userAccessSegments) ? 'true' : 'false' }} }" class="ml-2">

                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="text-smm flex items-center justify-between px-4 py-2 font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span>User & Access</span>
                                        <svg class="h-3 w-3 fill-current transition-transform"
                                            :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </a>

                                    <ul x-show="open" class="mt-1">
                                        @foreach ([['users', 'Users', 'M18 18.72a9.094 9.094 0 00-6-2.22 9.094 9.094 0 00-6 2.22M15 7.5a3 3 0 11-6 0 3 3 0 016 0z'], ['roles', 'Role', 'M12 3l7.5 4.5v6c0 4.5-3.15 7.8-7.5 9-4.35-1.2-7.5-4.5-7.5-9v-6L12 3z'], ['access_rights', 'Access Right', 'M15 7a4 4 0 11-8 0 4 4 0 018 0zM2.25 21h4.5l1.5-4.5h4.5'], ['role_menus', 'Role Menu', 'M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008M3.75 12h.008M3.75 17.25h.008']] as [$seg, $label, $path])
                                            <li class="bg-linear-to-r {{ Request::segment(1) === $seg ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' : '' }} rounded-lg py-2 transition"
                                                :class="{ 'pl-4 pr-3': sidebarExpanded, 'px-2': !sidebarExpanded }">

                                                <a href="{{ route($seg) }}"
                                                    class="group block truncate transition">
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            class="flex h-10 w-10 shrink-0 items-center justify-center">
                                                            <svg class="{{ Request::segment(1) === $seg ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} group-hover:text-gray-600 dark:group-hover:text-gray-300"
                                                                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                width="16" height="16" fill="none"
                                                                stroke="currentColor" stroke-width="1.5">
                                                                <path d="{{ $path }}" />
                                                            </svg>
                                                        </div>
                                                        <span x-show="sidebarExpanded"
                                                            class="{{ Request::segment(1) === $seg ? 'text-violet-500' : 'text-gray-800 dark:text-gray-100' }} text-smm break-wordsfont-medium whitespace-normal">
                                                            {{ $label }}
                                                        </span>
                                                    </div>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>

                                {{-- =====================================================
            APPLICATION
        ===================================================== --}}
                                @php $appSegments = ['applications','screens','menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $appSegments) ? 'true' : 'false' }} }" class="ml-2">

                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="text-smm flex items-center justify-between px-4 py-2 font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span>Application</span>
                                        <svg class="h-3 w-3 fill-current transition-transform"
                                            :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </a>

                                    <ul x-show="open" class="mt-1">
                                        @foreach ([['applications', 'Application', 'M9 12h6m-6 4h6M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75z'], ['screens', 'Screen', 'M3.75 6.75h16.5v9H3.75v-9zM9 18.75h6'], ['menus', 'Menu', 'M3.75 4.5h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 13.5h16.5v6H3.75v-6z']] as [$seg, $label, $path])
                                            @include(
                                                'partials.sidebar-item',
                                                compact('seg', 'label', 'path'))
                                        @endforeach
                                    </ul>
                                </li>

                                {{-- =====================================================
            ORGANIZATION
        ===================================================== --}}
                                @php $orgSegments = ['companies','department','tenants','locations']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $orgSegments) ? 'true' : 'false' }} }" class="ml-2">
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="text-smm flex items-center justify-between px-4 py-2 font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span>Organizationxxx</span>
                                        <svg class="h-3 w-3 fill-current transition-transform"
                                            :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </a>

                                    <ul x-show="open" class="mt-1">
                                        @foreach ([['companies', 'Company', 'M3.75 21V3.75h16.5V21M9 9h.01M15 9h.01M9 15h.01M15 15h.01'], ['department', 'Department', 'M3.75 3.75h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 14.25h6v6h-6v-6zm10.5 0h6v6h-6v-6z'], ['tenants', 'Tenant', 'M3 10.5l9-7.5 9 7.5V21H3v-10.5zm6 10.5v-6h6v6'], ['locations', 'Location', 'M12 21s6-5.25 6-10a6 6 0 10-12 0c0 4.75 6 10 6 10z']] as [$seg, $label, $path])
                                            @include(
                                                'partials.sidebar-item',
                                                compact('seg', 'label', 'path'))
                                        @endforeach
                                    </ul>
                                </li>

                                {{-- =====================================================
            MASTER DATA
        ===================================================== --}}
                                @php $masterDataSegments = ['categories','vendors','inventories','autonbrs','tops','approvals']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $masterDataSegments) ? 'true' : 'false' }} }" class="ml-2">
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="text-smm flex items-center justify-between px-4 py-2 font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span>Master Data</span>
                                        <svg class="h-3 w-3 fill-current transition-transform"
                                            :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </a>

                                    <ul x-show="open" class="mt-1">
                                        @foreach ([['categories', 'Category', 'M7.5 7.5h.01M3 6.75V3h3.75l12 12-3.75 3.75-12-12z'], ['vendors', 'Vendor', 'M3 7.5h11.25v9H3v-9zm11.25 3.75H18l3 3v3.75h-6.75'], ['inventories', 'Inventory', 'M3.75 6.75h16.5v10.5H3.75V6.75zm6 3h4.5'], ['autonbrs', 'Autonbr', 'M10.5 3.75L9 20.25M15 3.75l-1.5 16.5M4.5 9.75h15M3.75 14.25h15'], ['tops', 'TOP', 'M11.48 3.5l2.16 4.38 4.83.7-3.5 3.41.83 4.82-4.32-2.27-4.32 2.27.83-4.82-3.5-3.41 4.83-.7L11.48 3.5z'], ['approvals', 'Approval', 'M2.25 12.75v-.75a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v.75']] as [$seg, $label, $path])
                                            @include(
                                                'partials.sidebar-item',
                                                compact('seg', 'label', 'path'))
                                        @endforeach
                                    </ul>
                                </li>

                            </ul>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Expand / collapse button -->
<div class="mt-auto hidden justify-end pt-3 lg:inline-flex 2xl:hidden">
    <div class="w-12 py-2" :class="sidebarExpanded ? 'pl-4 pr-3' : 'px-2'">
        <button class="text-gray-400 transition-colors hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
            @click="sidebarExpanded = !sidebarExpanded; localStorage.setItem('sidebarExpanded', sidebarExpanded)">
            <span class="sr-only">Expand / collapse sidebar</span>
            <svg class="shrink-0 fill-current text-gray-400 transition-transform duration-200 dark:text-gray-500"
                :class="sidebarExpanded ? 'rotate-180' : 'rotate-0'" xmlns="http://www.w3.org/2000/svg"
                width="16" height="16" viewBox="0 0 16 16">
                <path
                    d="M15 16a1 1 0 0 1-1-1V1a1 1 0 1 1 2 0v14a1 1 0 0 1-1 1ZM8.586 7H1a1 1 0 1 0 0 2h7.586l-2.793 2.793a1 1 0 1 0 1.414 1.414l4.5-4.5A.997.997 0 0 0 12 8.01M11.924 7.617a.997.997 0 0 0-.217-.324l-4.5-4.5a1 1 0 0 0-1.414 1.414L8.586 7M12 7.99a.996.996 0 0 0-.076-.373Z" />
            </svg>
        </button>
    </div>
</div>
