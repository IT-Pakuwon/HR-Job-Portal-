<!-- ================= OFF CANVAS SIDEBAR ================= -->
<div x-cloak>

    <!-- BACKDROP -->
    <div x-show="sidebarOpen" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/40">
    </div>


    <!-- SIDEBAR -->
    <aside x-show="sidebarOpen" x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full" @keydown.escape.window="sidebarOpen = false"
        class="fixed left-0 top-0 z-50 h-[100dvh] w-72 overflow-y-auto bg-white shadow-xl dark:bg-gray-800">


        <!-- SIDEBAR HEADER -->
        <div class="border-b border-gray-200 px-4 py-4 text-center dark:border-gray-700">
            <div class="flex flex-row items-center justify-center gap-5">
                <img src="{{ asset('images/Logo Pakuwon.png') }}" alt="Pakuwon Logo" class="h-8 w-auto opacity-90" />

                <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                    {{ config('app.name', 'Pakuwon System') }}
                </span>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="space-y-6 p-4">
            <ul class="space-y-1">

                <!-- DASHBOARD -->
                <li
                    class="{{ Request::segment(1) === 'dashboard'
                        ? 'bg-violet-500/10 text-violet-600'
                        : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3v18h18M7 13v6m4-10v10m4-14v14" />
                        </svg>
                        Dashboard
                    </a>
                </li>

                <!-- ================= MODUL HR ================= -->
                @php
                    $hrMenu = $rootMenus->firstWhere('menu_id', 'HR');
                    $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
                @endphp

                @if ($hrMenu)
                    <li
                        class="mt-4 whitespace-normal break-words text-xs font-semibold uppercase leading-snug tracking-wider text-gray-400">
                        {{ $hrMenu->menu_name }}
                    </li>

                    @foreach ($allowedIds ? $hrMenu->children->whereIn('menu_id', $allowedIds) : $hrMenu->children as $menu)
                        @php
                            $children = $allowedIds
                                ? $menu->children->whereIn('menu_id', $allowedIds)
                                : $menu->children;
                        @endphp

                        @if ($children->isEmpty())
                            <!-- SINGLE MENU -->
                            <li
                                class="{{ Route::is($menu->menu_route . '*')
                                    ? 'bg-violet-500/10 text-violet-600'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg">
                                <a href="{{ $menu->menu_route ? route($menu->menu_route) : '#' }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="{{ $menu->menu_icon }}" />
                                    </svg>
                                    {{ $menu->menu_name }}
                                </a>
                            </li>
                        @else
                            <!-- MENU WITH SUB -->
                            @php
                                $isActive = $children->contains(fn($c) => Route::is($c->menu_route . '*'));
                            @endphp

                            <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="{{ $isActive ? 'bg-violet-500/10 text-violet-600' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm">

                                    <div class="flex items-center gap-3">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="{{ $menu->menu_icon }}" />
                                        </svg>
                                        <span class="flex-1 whitespace-normal break-words text-left leading-snug">
                                            {{ $menu->menu_name }}
                                        </span>
                                    </div>

                                    <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>

                                <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-9">
                                    @foreach ($children as $child)
                                        <li>
                                            <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                class="{{ Route::is($child->menu_route . '*')
                                                    ? 'text-violet-600'
                                                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} block rounded-md px-3 py-1.5 text-sm">
                                                {{ $child->menu_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                @endif

                <!-- ================= MODUL PURCHASING ================= -->

                @php
                    $purchasingMenu = $rootMenus->firstWhere('menu_id', 'PURCH');
                    $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];
                @endphp

                @if ($purchasingMenu)
                    <li
                        class="mt-4 whitespace-normal break-words text-xs font-semibold uppercase leading-snug tracking-wider text-gray-400">
                        {{ $purchasingMenu->menu_name }}
                    </li>

                    @foreach ($allowedIds ? $purchasingMenu->children->whereIn('menu_id', $allowedIds) : $purchasingMenu->children as $menu)
                        @php
                            $children = $allowedIds
                                ? $menu->children->whereIn('menu_id', $allowedIds)
                                : $menu->children;
                        @endphp

                        @if ($children->isEmpty())
                            <!-- SINGLE MENU -->
                            <li
                                class="{{ Route::is($menu->menu_route . '*')
                                    ? 'bg-violet-500/10 text-violet-600'
                                    : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} rounded-lg">
                                <a href="{{ $menu->menu_route ? route($menu->menu_route) : '#' }}"
                                    class="flex items-center gap-3 px-3 py-2 text-sm">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="{{ $menu->menu_icon }}" />
                                    </svg>
                                    {{ $menu->menu_name }}
                                </a>
                            </li>
                        @else
                            <!-- MENU WITH SUB -->
                            @php
                                $isActive = $children->contains(fn($c) => Route::is($c->menu_route . '*'));
                            @endphp

                            <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">
                                <button @click="open = !open"
                                    class="{{ $isActive ? 'bg-violet-500/10 text-violet-600' : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm">

                                    <div class="flex items-center gap-3">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="{{ $menu->menu_icon }}" />
                                        </svg>
                                        <span class="flex-1 whitespace-normal break-words text-left leading-snug">
                                            {{ $menu->menu_name }}
                                        </span>
                                    </div>

                                    <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 9l6 6 6-6" />
                                    </svg>
                                </button>

                                <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-9">
                                    @foreach ($children as $child)
                                        <li>
                                            <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                class="{{ Route::is($child->menu_route . '*')
                                                    ? 'text-violet-600'
                                                    : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }} block rounded-md px-3 py-1.5 text-sm">
                                                {{ $child->menu_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                    @endforeach
                @endif

                <!-- ================= MODUL SETTING ================= -->
                @auth
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

                        <li class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            SETTINGS
                        </li>

                        <li class="mt-2">
                            <ul class="mt-1 space-y-1 pl-2">

                                <!-- ================================================= -->
                                <!-- USER & ACCESS -->
                                <!-- ================================================= -->
                                @php $ua = ['users','roles','access_rights','role_menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $ua) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span class="flex-1 whitespace-normal break-words text-left leading-snug">User &
                                            Access</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('users') }}"
                                                class="{{ Request::segment(1) === 'users' ? 'text-violet-600' : '' }} sidebar-link text-sm">Users</a>
                                        </li>
                                        <li><a href="{{ route('roles') }}"
                                                class="{{ Request::segment(1) === 'roles' ? 'text-violet-600' : '' }} sidebar-link text-sm">Roles</a>
                                        </li>
                                        <li><a href="{{ route('access_rights') }}"
                                                class="{{ Request::segment(1) === 'access_rights' ? 'text-violet-600' : '' }} sidebar-link text-sm">Access
                                                Rights</a></li>
                                        <li><a href="{{ route('role_menus') }}"
                                                class="{{ Request::segment(1) === 'role_menus' ? 'text-violet-600' : '' }} sidebar-link text-sm">Role
                                                Menus</a></li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- APPLICATION -->
                                <!-- ================================================= -->
                                @php $app = ['applications','screens','menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $app) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal break-words text-left leading-snug">Application</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('applications') }}"
                                                class="{{ Request::segment(1) === 'applications' ? 'text-violet-600' : '' }} sidebar-link text-sm">Applications</a>
                                        </li>
                                        <li><a href="{{ route('screens') }}"
                                                class="{{ Request::segment(1) === 'screens' ? 'text-violet-600' : '' }} sidebar-link text-sm">Screens</a>
                                        </li>
                                        <li><a href="{{ route('menus') }}"
                                                class="{{ Request::segment(1) === 'menus' ? 'text-violet-600' : '' }} sidebar-link text-sm">Menus</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- ORGANIZATION -->
                                <!-- ================================================= -->
                                @php $org = ['companies','department','tenants','locations']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $org) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal break-words text-left leading-snug">Organization</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('companies') }}"
                                                class="{{ Request::segment(1) === 'companies' ? 'text-violet-600' : '' }} sidebar-link text-sm">Companies</a>
                                        </li>
                                        <li><a href="{{ route('department') }}"
                                                class="{{ Request::segment(1) === 'department' ? 'text-violet-600' : '' }} sidebar-link text-sm">Department</a>
                                        </li>
                                        <li><a href="{{ route('business-units') }}"
                                                class="{{ Request::segment(1) === 'business-units' ? 'text-violet-600' : '' }} sidebar-link text-sm">Business Units</a>
                                        </li>
                                        <li><a href="{{ route('tenants') }}"
                                                class="{{ Request::segment(1) === 'tenants' ? 'text-violet-600' : '' }} sidebar-link text-sm">Tenants</a>
                                        </li>
                                        <li><a href="{{ route('locations') }}"
                                                class="{{ Request::segment(1) === 'locations' ? 'text-violet-600' : '' }} sidebar-link text-sm">Locations</a>
                                        </li>


                                    </ul>
                                </li>
                                <!-- ================================================= -->
                                <!-- MASTER DATA -->
                                <!-- ================================================= -->
                                @php $md = ['categories','vendors','inventories','autonbrs','tops']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $md) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span class="flex-1 whitespace-normal break-words text-left leading-snug">Master
                                            Data</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('categories') }}"
                                                class="{{ Request::segment(1) === 'categories' ? 'text-violet-600' : '' }} sidebar-link text-sm">Categories</a>
                                        </li>
                                        <li><a href="{{ route('vendors') }}"
                                                class="{{ Request::segment(1) === 'vendors' ? 'text-violet-600' : '' }} sidebar-link text-sm">Vendors</a>
                                        </li>
                                        <li><a href="{{ route('inventories') }}"
                                                class="{{ Request::segment(1) === 'inventories' ? 'text-violet-600' : '' }} sidebar-link text-sm">Inventories</a>
                                        </li>
                                        <li><a href="{{ route('autonbrs') }}"
                                                class="{{ Request::segment(1) === 'autonbrs' ? 'text-violet-600' : '' }} sidebar-link text-sm">Autonbrs</a>
                                        </li>
                                        <li><a href="{{ route('tops') }}"
                                                class="{{ Request::segment(1) === 'tops' ? 'text-violet-600' : '' }} sidebar-link text-sm">TOPS</a>
                                        </li>
                                        <li><a href="{{ route('sys-calendar') }}"
                                                class="{{ Request::segment(1) === 'sys-calendar' ? 'text-violet-600' : '' }} sidebar-link text-sm">Calendar Exception</a>
                                        </li>
                                        <li><a href="{{ route('attachments-master') }}"
                                                    class="{{ Request::segment(1) === 'attachments-master' ? 'text-violet-600' : '' }} sidebar-link text-sm">Attachments Master</a>
                                        </li>
                                        <li><a href="{{ route('kendaraan') }}"
                                                class="{{ Request::segment(1) === 'kendaraan' ? 'text-violet-600' : '' }} sidebar-link text-sm">Kendaraan</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- ================================================= -->
                                <!-- WORKFLOW -->
                                <!-- ================================================= -->
                                @php $workflowSegments = ['approvals','ifcaintegration']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $workflowSegments) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal break-words text-left leading-snug">Workflow</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('approvals') }}"
                                                class="{{ Request::segment(1) === 'approvals' ? 'text-violet-600' : '' }} sidebar-link text-sm">Approvals</a>
                                        </li>
                                        <li>
                                            <a href="{{ route('integration.ifcaintegration') }}"
                                                class="{{ Request::segment(1) === 'ifcaintegration' ? 'text-violet-600' : '' }} sidebar-link text-sm">
                                                IFCA Integration
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('integration.acumvms.index') }}"
                                                class="{{ Request::segment(1) === 'acumvms' ? 'text-violet-600' : '' }} sidebar-link text-sm">
                                                ACUM VMS Integration
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('user_sync.index') }}"
                                                class="{{ Request::segment(1) === 'user_sync' ? 'text-violet-600' : '' }} sidebar-link text-sm">
                                                User Sync
                                            </a>
                                        </li>
                                         <li>
                                            <a href="{{ route('test-email.index') }}"
                                                class="{{ Request::segment(1) === 'test-email' ? 'text-violet-600' : '' }} sidebar-link text-sm">
                                                TESTING EMAIL
                                            </a>
                                        </li>


                                    </ul>
                                </li>
                    @endif
                @endauth

            </ul>
        </div>
    </aside>
</div>
