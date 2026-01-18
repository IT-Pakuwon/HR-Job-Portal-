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

            <div class="flex items-center gap-3">
                <span :class="{ 'hidden': !sidebarExpanded && window.innerWidth >= 1024 }"
                    class="whitespace-nowrap text-base font-bold uppercase text-gray-700 transition-opacity duration-200 dark:text-gray-100"
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
                    <li
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['dashboard'])) from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['dashboard'])) hover:text-gray-900 dark:hover:text-white @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('dashboard') }}">
                            <div class="flex items-center">

                                <!-- NEW DASHBOARD ICON -->
                                <svg class="@if (in_array(Request::segment(1), ['dashboard'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 3v18h18M7 13v6m4-10v10m4-14v14" />
                                </svg>

                                <span
                                    class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                    Dashboard
                                </span>
                            </div>
                        </a>
                    </li>

                    <!-- Structure Organization -->

                    <li class="py-2 pl-4 pr-3 text-xs font-semibold uppercase tracking-wider text-gray-500 last:mb-0"
                        :class="{ 'lg:block': sidebarExpanded, 'lg:hidden': !sidebarExpanded }">
                        Human Resources
                    </li>
                    {{-- <li
                        class="bg-linear-to-r @if (in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                        <a class="@if (!in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="{{ route('stos', 'showstos') }}">
                            <div class="flex items-center">
                                <svg class="@if (in_array(Request::segment(1), ['stos', 'showstos'])) {{ 'text-violet-500' }}@else{{ 'text-gray-400 dark:text-gray-500' }} @endif shrink-0 fill-current"
                                    xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    viewBox="0 0 16 16">
                                    <path
                                        d="M11.92 6.851c.044-.027.09-.05.137-.07.481-.275.758-.68.908-1.256.126-.55.169-.81.357-2.058.075-.498.144-.91.217-1.264-4.122.75-7.087 2.984-9.12 6.284a18.087 18.087 0 0 0-1.985 4.585 17.07 17.07 0 0 0-.354 1.506c-.05.265-.076.448-.086.535a1 1 0 0 1-1.988-.226c.056-.49.209-1.312.502-2.357a20.063 20.063 0 0 1 2.208-5.09C5.31 3.226 9.306.494 14.913.004a1 1 0 0 1 .954 1.494c-.237.414-.375.993-.567 2.267-.197 1.306-.244 1.586-.392 2.235-.285 1.094-.789 1.853-1.552 2.363-.748 3.816-3.976 5.06-8.515 4.326a1 1 0 0 1 .318-1.974c2.954.477 4.918.025 5.808-1.556-.628.085-1.335.121-2.127.121a1 1 0 1 1 0-2c1.458 0 2.434-.116 3.08-.429Z" />
                                </svg>
                                <span
                                    class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">ORG
                                    Structure</span>
                            </div>
                        </a>
                    </li> --}}
                    <!-- Dashboard -->
                    <li class="bg-linear-to-r @if (in_array(Request::segment(1), [
                            'personnels',
                            'createPersonnel',
                            'editPersonnel',
                            'showpersonnels',
                            'jobapplicant',
                            'changestos',
                            'showApplicant',
                            'showcareers',
                            'changestos',
                            'editChangesto',
                            'showChangesto',
                            'createChangesto',
                        ])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                        x-data="{ open: {{ in_array(Request::segment(1), [
                            'personnels',
                            'createPersonnel',
                            'editPersonnel',
                            'showpersonnels',
                            'jobapplicant',
                            'changestos',
                            'showApplicant',
                            'showcareers',
                            'changestos',
                            'editChangesto',
                            'showChangesto',
                            'createChangesto',
                        ])
                            ? 1
                            : 0 }} }">
                        <a class="@if (
                            !in_array(Request::segment(1), [
                                'personnels',
                                'createPersonnel',
                                'editPersonnel',
                                'showpersonnels',
                                'jobapplicant',
                                'showApplicant',
                                'showcareers',
                                'changestos',
                                'editChangesto',
                                'showChangesto',
                                'createChangesto',
                            ])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                            href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="@if (in_array(Request::segment(1), [
                                            'personnels',
                                            'createPersonnel',
                                            'editPersonnel',
                                            'jobapplicant',
                                            'changestos',
                                            'editChangesto',
                                            'showChangesto',
                                            'createChangesto',
                                            'showApplicant',
                                            'showcareers',
                                        ])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                        xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M18 18v-1a3 3 0 00-3-3h-.75m-4.5 0H9a3 3 0 00-3 3v1m12-10.5a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-7.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm0 10.5v-1a2.999 2.999 0 00-2.25-2.902m6.75 3.902v-1a2.999 2.999 0 00-2.25-2.902" />
                                    </svg>

                                    <span
                                        class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Recruitment</span>
                                </div>
                                <div
                                    class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                    <svg class="@if (in_array(Request::segment(1), ['personnels', 'jobpostings', 'changestos'])) {{ 'rotate-180' }} @endif ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                    </svg>
                                </div>
                            </div>
                        </a>
                        <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                            <ul class="@if (
                                !in_array(Request::segment(1), [
                                    'personnels',
                                    'createPersonnel',
                                    'editPersonnel',
                                    // 'showpersonnels/{hash}',
                                    'jobapplicant',
                                    'showcareers',
                                    'changestos',
                                    'editChangesto',
                                    'showChangesto',
                                    'createChangesto',
                                    'showApplicant',
                                ])) {{ 'hidden' }} @endif mt-1 pl-8"
                                :class="open ? 'block!' : 'hidden'">
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('personnels', 'createPersonnel', 'editPersonnel', 'showpersonnels/{hash}')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('personnels') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">PRF</span>
                                    </a>
                                </li>
                                <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('jobapplicant')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('jobapplicant') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Applicant
                                            Portal</span>
                                    </a>
                                </li>
                                {{-- <li class="mb-1 last:mb-0">
                                    <a class="@if (Route::is('changestos')) {{ 'text-violet-500!' }} @endif block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                        href="{{ route('changestos') }}">
                                        <span
                                            class="lg:sidebar-expanded:opacity-100 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Request
                                            Additional</span>
                                    </a>
                                </li> --}}
                            </ul>
                        </div>
                    </li>
                    @auth
                        {{-- @if (auth()->user()->username === 'rikiparahat' || auth()->user()->username === 'bedriamaail' || auth()->user()->username === 'sugiartoongkowijoyo' || auth()->user()->username === 'ariwibowo' || auth()->user()->username === 'junpianto' || auth()->user()->username === 'ariewibisono' || auth()->user()->username === 'adefahmi' || auth()->user()->username === 'williemhalim') --}}
                        {{-- LABEL GROUP PURCHASING --}}
                        <li class="py-2 pl-4 pr-3 text-xs font-semibold uppercase tracking-wider text-gray-500 last:mb-0"
                            :class="{ 'lg:block': sidebarExpanded, 'lg:hidden': !sidebarExpanded }">
                            Purchasing
                        </li>

                        @php
                            // cari menu parent "Purchasing"
                            $purchasingMenu = $rootMenus->firstWhere('menu_id', 'PURCH');

                            // siapkan helper array allowed IDs
                            $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
                        @endphp

                        @if ($purchasingMenu)
                            @php
                                // 🔹 FILTER anak-anak langsung dari PURCH (Budget, Request Form, CS, Purchase, dst)
                                $parentChildren = $allowedIds
                                    ? $purchasingMenu->children->whereIn('menu_id', $allowedIds)
                                    : $purchasingMenu->children;
                            @endphp

                            {{-- Loop children dari Purchasing: Budget, Request Form, Canvass Sheets, dll --}}
                            @foreach ($parentChildren as $menu)
                                @php
                                    // 🔹 FILTER anak level 2 (submenu) berdasarkan allowedMenuIds
                                    $children = $allowedIds
                                        ? $menu->children->whereIn('menu_id', $allowedIds)
                                        : $menu->children;
                                @endphp

                                @if ($children->isEmpty())
                                    {{-- MENU TANPA SUB --}}
                                    <li
                                        class="bg-linear-to-r {{ Route::is($menu->menu_route . '*') ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' : '' }} mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                        <a class="{{ !Route::is($menu->menu_route . '*') ? 'hover:text-gray-900 dark:hover:text-white' : '' }} block truncate text-gray-800 transition dark:text-gray-100"
                                            href="{{ $menu->menu_route ? route($menu->menu_route) : '#' }}">
                                            <div class="flex items-center">
                                                <svg class="{{ Route::is($menu->menu_route . '*') ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" width="16"
                                                    height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="{{ $menu->menu_icon }}" />
                                                </svg>
                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 ml-4 whitespace-normal break-words text-sm font-medium leading-tight duration-200 lg:opacity-0 2xl:opacity-100">
                                                    {{ $menu->menu_name }}
                                                </span>
                                            </div>
                                        </a>
                                    </li>
                                @else
                                    {{-- MENU DENGAN SUBMENU --}}
                                    @php
                                        $isGroupActive = $children->contains(function ($child) {
                                            return Route::is($child->menu_route . '*');
                                        });
                                    @endphp

                                    <li class="bg-linear-to-r {{ $isGroupActive ? 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' : '' }} mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0"
                                        x-data="{ open: {{ $isGroupActive ? 'true' : 'false' }} }">
                                        <a class="{{ !$isGroupActive ? 'hover:text-gray-900 dark:hover:text-white' : '' }} block truncate text-gray-800 transition dark:text-gray-100"
                                            href="#0" @click.prevent="open = !open; sidebarExpanded = true">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <svg class="{{ $isGroupActive ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        width="16" height="16">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="{{ $menu->menu_icon }}" />
                                                    </svg>
                                                    <span
                                                        class="lg:sidebar-expanded:opacity-100 ml-4 whitespace-normal break-words text-sm font-medium leading-tight duration-200 lg:opacity-0 2xl:opacity-100">
                                                        {{ $menu->menu_name }}
                                                    </span>
                                                </div>
                                                <div
                                                    class="lg:sidebar-expanded:opacity-100 ml-2 flex shrink-0 duration-200 lg:opacity-0 2xl:opacity-100">
                                                    <svg class="ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                        :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                    </svg>
                                                </div>
                                            </div>
                                        </a>
                                        <div class="lg:sidebar-expanded:block lg:hidden 2xl:block">
                                            <ul class="mt-1 pl-8" :class="open ? 'block!' : 'hidden'">
                                                @foreach ($children as $child)
                                                    <li class="mb-1 last:mb-0">
                                                        <a class="{{ Route::is($child->menu_route . '*') ? 'text-violet-500!' : '' }} block truncate text-gray-500/90 transition hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                                                            href="{{ $child->menu_route ? route($child->menu_route) : '#' }}">
                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">
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
                            {{-- @endif --}}

                            {{-- LABEL GROUP SETTINGS --}}
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
                                    ];
                                @endphp

                                <li class="mb-2" x-data="{ open: {{ in_array(Request::segment(1), $settingsSegments) ? 1 : 0 }} }"
                                    :class="{ 'lg:block': sidebarExpanded, 'lg:hidden': !sidebarExpanded }">

                                    {{-- SETTINGS HEADER --}}
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase tracking-wider text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">

                                        <span>Settings</span>

                                        <svg class="h-3 w-3 fill-current transition-transform"
                                            :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </a>

                                    {{-- EVERYTHING BELOW IS YOUR EXISTING CODE --}}
                                    <ul class="mt-1 space-y-2" :class="open ? 'block' : 'hidden'">


                                        <!-- User & Access -->
                                        @php
                                            $userAccessSegments = ['users', 'roles', 'access_rights', 'role_menus'];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $userAccessSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>User & Access</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">

                                                {{-- USERS --}}
                                                <li
                                                    class="bg-linear-to-r @if (Request::segment(1) === 'users') from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3">
                                                    <a href="{{ route('users') }}"
                                                        class="block truncate text-gray-800 transition dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            <svg class="{{ Request::segment(1) === 'users' ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M18 18.72a9.094 9.094 0 00-6-2.22 9.094 9.094 0 00-6 2.22M15 7.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                            </svg>
                                                            <span class="ml-4 font-medium">Users</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                {{-- ROLE --}}
                                                <li
                                                    class="bg-linear-to-r @if (Request::segment(1) === 'roles') from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3">
                                                    <a href="{{ route('roles') }}"
                                                        class="block truncate text-gray-800 dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            <svg class="{{ Request::segment(1) === 'roles' ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }}"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M12 3l7.5 4.5v6c0 4.5-3.15 7.8-7.5 9-4.35-1.2-7.5-4.5-7.5-9v-6L12 3z" />
                                                            </svg>
                                                            <span class="ml-4 font-medium">Role</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                {{-- ACCESS RIGHT --}}
                                                <li
                                                    class="bg-linear-to-r @if (Request::segment(1) === 'access_rights') from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3">
                                                    <a href="{{ route('access_rights') }}"
                                                        class="block truncate text-gray-800 dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            <svg class="{{ Request::segment(1) === 'access_rights' ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }}"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M15 7a4 4 0 11-8 0 4 4 0 018 0zM2.25 21h4.5l1.5-4.5h4.5" />
                                                            </svg>
                                                            <span class="ml-4 font-medium">Access Right</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                {{-- ROLE MENU --}}
                                                <li
                                                    class="bg-linear-to-r @if (Request::segment(1) === 'role_menus') from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3">
                                                    <a href="{{ route('role_menus') }}"
                                                        class="block truncate text-gray-800 dark:text-gray-100">
                                                        <div class="flex items-center">
                                                            <svg class="{{ Request::segment(1) === 'role_menus' ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }}"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008M3.75 12h.008M3.75 17.25h.008" />
                                                            </svg>
                                                            <span class="ml-4 font-medium">Role Menu</span>
                                                        </div>
                                                    </a>
                                                </li>

                                            </ul>
                                        </li>

                                        <!-- Application -->
                                        @php
                                            $ApplicationSegments = ['applications', 'screens', 'menus'];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $ApplicationSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>Application</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['applications'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['applications'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('applications') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['applications'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M9 12h6m-6 4h6M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75z" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Application</span>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['screens'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['screens'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('screens') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['screens'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 6.75h16.5v9H3.75v-9zM9 18.75h6" />
                                                            </svg>
                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Screen</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['menus'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['menus'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('menus') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['menus'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">

                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 4.5h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 13.5h16.5v6H3.75v-6z" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Menu</span>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <!-- Organization -->
                                        @php
                                            $organizationSegments = ['companies', 'department', 'tenants', 'locations'];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $organizationSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>Organization</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['companies'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['companies'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('companies') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['companies'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 21V3.75h16.5V21M9 9h.01M15 9h.01M9 15h.01M15 15h.01" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Company</span>
                                                        </div>
                                                    </a>
                                                </li>
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['department'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['department'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('department') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['department'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 3.75h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 14.25h6v6h-6v-6zm10.5 0h6v6h-6v-6z" />
                                                            </svg>
                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Department</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['tenants'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['tenants'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('tenants') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['tenants'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3 10.5l9-7.5 9 7.5V21H3v-10.5zm6 10.5v-6h6v6" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Tenant</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['locations'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['locations'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('locations') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['locations'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M12 21s6-5.25 6-10a6 6 0 10-12 0c0 4.75 6 10 6 10z" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Location</span>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <!-- Master Data -->
                                        @php
                                            $organizationSegments = [
                                                'categories',
                                                'vendors',
                                                'inventories',
                                                'autonbrs',
                                                'tops',
                                            ];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $organizationSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>Master Data</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['categories'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['categories'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('categories') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['categories'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="
                                                                                                                                                                                                                                                                                                                                                                                                                                                    M7.5 7.5h.01M3 6.75V3h3.75l12 12-3.75 3.75-12-12z" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Category</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['vendors'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['vendors'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('vendors') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['vendors'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3 7.5h11.25v9H3v-9zm11.25 3.75H18l3 3v3.75h-6.75M6.75 18a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Vendor</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['inventories'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['inventories'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('inventories') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['inventories'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 6.75h16.5v10.5H3.75V6.75zm6 3h4.5" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Inventory</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['autonbrs'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['autonbrs'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('autonbrs') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['autonbrs'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M10.5 3.75L9 20.25M15 3.75l-1.5 16.5M4.5 9.75h15M3.75 14.25h15" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Autonbr</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['tops'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['tops'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('tops') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['tops'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M11.48 3.5l2.16 4.38 4.83.7-3.5 3.41.83 4.82-4.32-2.27-4.32 2.27.83-4.82-3.5-3.41 4.83-.7L11.48 3.5z" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">TOP</span>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <!-- Workflow -->
                                        @php
                                            $workflowSegments = ['approvals'];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $workflowSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>Workflow</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['approvals'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['approvals'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('approvals') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['approvals'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 12.75v-.75a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v.75m-19.5 0v3a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-3m-19.5 0h19.5M6 9.75v-.75a3 3 0 013-3h6a3 3 0 013 3v.75" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Approval</span>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">
                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['budgetmonitor'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['budgetmonitor'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('budgetmonitor') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['budgetmonitor'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M2.25 12.75v-.75a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v.75m-19.5 0v3a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-3m-19.5 0h19.5M6 9.75v-.75a3 3 0 013-3h6a3 3 0 013 3v.75" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">TEST
                                                                Monitor</span>
                                                        </div>
                                                    </a>
                                                </li>
                                            </ul>
                                        </li>

                                        <!-- Integration -->
                                        @php
                                            $IntegrationSegments = ['ifcaintegration'];
                                        @endphp
                                        <li class="mb-2 ml-2" x-data="{ open: {{ in_array(Request::segment(1), $IntegrationSegments) ? 1 : 0 }} }">

                                            <!-- Header -->
                                            <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                                class="flex items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                                <span>Integration</span>

                                                <svg class="h-3 w-3 fill-current text-gray-400 transition-transform"
                                                    :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                    <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                                </svg>
                                            </a>

                                            <!-- Children -->
                                            <ul class="mt-1" :class="open ? 'block' : 'hidden'">

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['integration.ifcaintegration'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['integration.ifcaintegration'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('integration.ifcaintegration') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['ifcaintegration'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M9 12h6m-6 4h6M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75z" />
                                                            </svg>


                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">IFCA Integration</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <!-- <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['screens'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['screens'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('screens') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['screens'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 6.75h16.5v9H3.75v-9zM9 18.75h6" />
                                                            </svg>
                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Screen</span>
                                                        </div>
                                                    </a>
                                                </li>

                                                <li
                                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['menus'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                                    <a class="@if (!in_array(Request::segment(1), ['menus'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                                        href="{{ route('menus') }}">
                                                        <div class="flex items-center">
                                                            <svg class="@if (in_array(Request::segment(1), ['menus'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                                xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                viewBox="0 0 24 24" stroke-width="1.5"
                                                                stroke="currentColor" width="16" height="16">

                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M3.75 4.5h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 13.5h16.5v6H3.75v-6z" />
                                                            </svg>

                                                            <span
                                                                class="lg:sidebar-expanded:opacity-100 ml-4 text-sm font-medium duration-200 lg:opacity-0 2xl:opacity-100">Menu</span>
                                                        </div>
                                                    </a>
                                                </li> -->
                                            </ul>
                                        </li>

                                    </ul>
                                </li>


                                {{-- @php
                                    $settingsSegments = [
                                        'users',
                                        'roles',
                                        'access-rights',
                                        'role-menus',
                                        'applications',
                                        'screens',
                                        'menus',
                                        'companies',
                                        'departments',
                                        'tenants',
                                        'locations',
                                        'categories',
                                        'vendors',
                                        'inventories',
                                        'autonbrs',
                                        'tops',
                                        'approvals',
                                    ];
                                @endphp

                                <li class="bg-linear-to-r @if (in_array(Request::segment(1), $settingsSegments)) from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04] @endif mb-0.5 rounded-lg py-2 pl-4 pr-3"
                                    x-data="{ open: {{ in_array(Request::segment(1), $settingsSegments) ? 1 : 0 }} }">
                                    <!-- Parent -->
                                    <a href="#0" @click.prevent="open = !open; sidebarExpanded = true"
                                        class="block truncate text-gray-800 transition dark:text-gray-100">

                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">

                                                <!-- Icon -->
                                                <svg class="{{ in_array(Request::segment(1), $settingsSegments) ? 'text-violet-500' : 'text-gray-400 dark:text-gray-500' }} shrink-0"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="1.5" stroke="currentColor" width="16"
                                                    height="16">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M10.5 6h3m-3 6h3m-6 6h12A2.25 2.25 0 0021.75 15V6A2.25 2.25 0 0019.5 3h-15A2.25 2.25 0 002.25 6v9A2.25 2.25 0 004.5 18z" />
                                                </svg>

                                                <span
                                                    class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">
                                                    Settings
                                                </span>
                                            </div>

                                            <!-- Arrow -->
                                            <svg class="ml-1 h-3 w-3 shrink-0 fill-current text-gray-400 dark:text-gray-500"
                                                :class="open ? 'rotate-180' : 'rotate-0'" viewBox="0 0 12 12">
                                                <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                            </svg>
                                        </div>
                                    </a>

                                    <!-- Children -->

                                </li> --}}


                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['users'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['users'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('users') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['users'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M18 18.72a9.094 9.094 0 00-6-2.22 9.094 9.094 0 00-6 2.22M15 7.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Users</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['access_rights'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['access_rights'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('access_rights') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['access_rights'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 7a4 4 0 11-8 0 4 4 0 018 0zM2.25 21h4.5l1.5-4.5h4.5" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Access
                                                Right</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['roles'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['roles'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('roles') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['roles'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 3l7.5 4.5v6c0 4.5-3.15 7.8-7.5 9-4.35-1.2-7.5-4.5-7.5-9v-6L12 3z" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Role</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['role_menus'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['role_menus'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('role_menus') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['role_menus'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008M3.75 12h.008M3.75 17.25h.008" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Role
                                                Menu</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['applications'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['applications'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('applications') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['applications'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6M7.5 3.75h9A2.25 2.25 0 0118.75 6v12A2.25 2.25 0 0116.5 20.25h-9A2.25 2.25 0 015.25 18V6A2.25 2.25 0 017.5 3.75z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Application</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['screens'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['screens'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('screens') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['screens'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 6.75h16.5v9H3.75v-9zM9 18.75h6" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Screen</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['menus'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['menus'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('menus') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['menus'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 4.5h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 13.5h16.5v6H3.75v-6z" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Menu</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['approvals'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['approvals'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('approvals') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['approvals'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 12.75v-.75a2.25 2.25 0 012.25-2.25h15a2.25 2.25 0 012.25 2.25v.75m-19.5 0v3a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25v-3m-19.5 0h19.5M6 9.75v-.75a3 3 0 013-3h6a3 3 0 013 3v.75" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Approval</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['companies'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['companies'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('companies') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['companies'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 21V3.75h16.5V21M9 9h.01M15 9h.01M9 15h.01M15 15h.01" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Company</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['department'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['department'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('department') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['department'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 3.75h6v6h-6v-6zm10.5 0h6v6h-6v-6zM3.75 14.25h6v6h-6v-6zm10.5 0h6v6h-6v-6z" />
                                            </svg>
                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Department</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['categories'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['categories'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('categories') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['categories'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="
                                                                                                                                                                                                    M7.5 7.5h.01M3 6.75V3h3.75l12 12-3.75 3.75-12-12z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Category</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['autonbrs'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['autonbrs'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('autonbrs') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['autonbrs'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M10.5 3.75L9 20.25M15 3.75l-1.5 16.5M4.5 9.75h15M3.75 14.25h15" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Autonbr</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['vendors'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['vendors'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('vendors') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['vendors'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 7.5h11.25v9H3v-9zm11.25 3.75H18l3 3v3.75h-6.75M6.75 18a.75.75 0 100-1.5.75.75 0 000 1.5zm9 0a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Vendor</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['inventories'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['inventories'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('inventories') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['inventories'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3.75 6.75h16.5v10.5H3.75V6.75zm6 3h4.5" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Inventory</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['tenants'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['tenants'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('tenants') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['tenants'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 10.5l9-7.5 9 7.5V21H3v-10.5zm6 10.5v-6h6v6" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Tenant</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['locations'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['locations'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('locations') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['locations'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M12 21s6-5.25 6-10a6 6 0 10-12 0c0 4.75 6 10 6 10z" />
                                            </svg>

                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">Location</span>
                                        </div>
                                    </a>
                                </li> --}}
                                {{-- <li
                                    class="bg-linear-to-r @if (in_array(Request::segment(1), ['tops'])) {{ 'from-violet-500/[0.12] dark:from-violet-500/[0.24] to-violet-500/[0.04]' }} @endif mb-0.5 rounded-lg py-2 pl-4 pr-3 last:mb-0">
                                    <a class="@if (!in_array(Request::segment(1), ['tops'])) {{ 'hover:text-gray-900 dark:hover:text-white' }} @endif block truncate text-gray-800 transition dark:text-gray-100"
                                        href="{{ route('tops') }}">
                                        <div class="flex items-center">
                                            <svg class="@if (in_array(Request::segment(1), ['tops'])) text-violet-500 @else text-gray-400 dark:text-gray-500 @endif shrink-0"
                                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="1.5" stroke="currentColor" width="16" height="16">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M11.48 3.5l2.16 4.38 4.83.7-3.5 3.41.83 4.82-4.32-2.27-4.32 2.27.83-4.82-3.5-3.41 4.83-.7L11.48 3.5z" />
                                            </svg>


                                            <span
                                                class="lg:sidebar-expanded:opacity-100 text-sm ml-4 font-medium duration-200 lg:opacity-0 2xl:opacity-100">TOP</span>
                                        </div>
                                    </a>
                                </li> --}}
                            @endif
                        @endif
                    @endauth
                </ul>
            </div>
        </div>

        <!-- Expand / collapse button -->
        <div class="mt-auto hidden justify-end pt-3 lg:inline-flex 2xl:hidden">
            <div class="w-12 py-2 pl-4 pr-3">
                <button
                    class="text-gray-400 transition-colors hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
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
    </div>
</div>
