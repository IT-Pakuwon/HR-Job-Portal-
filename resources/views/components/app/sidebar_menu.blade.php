<style>
    /* ================= SIDEBAR LINKS ================= */
    .sidebar-link {
        display: block;
        padding: 6px 12px;
        font-size: 14px;
        color: rgb(75 85 99);
        /* gray-600 */
    }

    .sidebar-link:hover {
        color: rgb(17 24 39);
        /* gray-900 */
    }

    /* Dark mode */
    .dark .sidebar-link {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .sidebar-link:hover {
        color: rgb(243 244 246);
        /* gray-100 */
    }

    /* ================= SETTINGS SUBHEADERS ================= */
    .settings-subheader {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        padding: 8px 16px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-align: left;
        color: rgb(107 114 128);
        /* gray-500 */
    }

    .settings-subheader:hover {
        color: rgb(55 65 81);
        /* gray-700 */
    }

    /* Dark mode */
    .dark .settings-subheader {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    .dark .settings-subheader:hover {
        color: rgb(243 244 246);
        /* gray-100 */
    }

    /* ================= SUBMENU ================= */
    .submenu {
        padding-left: 1rem;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    /* ================= CHEVRON (VISIBLE FIX) ================= */
    .chevron {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        color: rgb(107 114 128);
        /* gray-500 */
    }

    /* Dark mode chevron */
    .dark .chevron {
        color: rgb(209 213 219);
        /* gray-300 */
    }

    /* Hover feedback */
    .settings-subheader:hover .chevron {
        color: rgb(55 65 81);
        /* gray-700 */
    }

    .dark .settings-subheader:hover .chevron {
        color: rgb(243 244 246);
        /* gray-100 */
    }

    /* ===== GLOBAL SIDEBAR CHEVRON VISIBILITY FIX ===== */
    aside svg[viewBox="0 0 12 12"],
    aside svg.chevron {
        color: rgb(156 163 175);
        /* gray-400 */
    }

    /* Dark mode */
    .dark aside svg[viewBox="0 0 12 12"],
    .dark aside svg.chevron {
        color: rgb(229 231 235);
        /* gray-200 */
    }
</style>



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
                    <li class="mt-4 text-xs font-semibold uppercase tracking-wider text-gray-400">
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
                                        {{ $menu->menu_name }}
                                    </div>

                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                        viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
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
                    <li class="mt-4 text-xs font-semibold uppercase tracking-wider text-gray-400">
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
                                        {{ $menu->menu_name }}
                                    </div>

                                    <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                        viewBox="0 0 12 12">
                                        <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
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

                        <li x-data="{ open: {{ in_array(Request::segment(1), $settingsSegments) ? 'true' : 'false' }} }" class="mt-6">


                            {{-- <li class="text-xs font-semibold uppercase tracking-wider text-gray-400">
                            SETTINGS
                        </li> --}}
                            <!-- ================= SETTINGS CONTENT ================= -->
                            <ul x-show="open" x-collapse class="mt-2 space-y-3 pl-2">

                                <!-- ================================================= -->
                                <!-- USER & ACCESS -->
                                <!-- ================================================= -->
                                @php $ua = ['users','roles','access_rights','role_menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $ua) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">

                                        <span>User & Access</span>

                                        <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 12 12">
                                            <path d="M5.9 11.4L.5 6l1.4-1.4 4 4 4-4L11.3 6z" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('users') }}"
                                                class="{{ Request::segment(1) === 'users' ? 'text-violet-600' : '' }} sidebar-link">Users</a>
                                        </li>
                                        <li><a href="{{ route('roles') }}"
                                                class="{{ Request::segment(1) === 'roles' ? 'text-violet-600' : '' }} sidebar-link">Roles</a>
                                        </li>
                                        <li><a href="{{ route('access_rights') }}"
                                                class="{{ Request::segment(1) === 'access_rights' ? 'text-violet-600' : '' }} sidebar-link">Access
                                                Rights</a></li>
                                        <li><a href="{{ route('role_menus') }}"
                                                class="{{ Request::segment(1) === 'role_menus' ? 'text-violet-600' : '' }} sidebar-link">Role
                                                Menus</a></li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- APPLICATION -->
                                <!-- ================================================= -->
                                @php $app = ['applications','screens','menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $app) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="settings-subheader flex w-full items-center justify-between px-4 py-2 text-xs font-semibold uppercase text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <span>Application</span>
                                        <svg class="chevron" :class="open ? 'rotate-180' : ''"></svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="submenu">
                                        <li><a href="{{ route('applications') }}"
                                                class="{{ Request::segment(1) === 'applications' ? 'text-violet-600' : '' }} sidebar-link">Applications</a>
                                        </li>
                                        <li><a href="{{ route('screens') }}"
                                                class="{{ Request::segment(1) === 'screens' ? 'text-violet-600' : '' }} sidebar-link">Screens</a>
                                        </li>
                                        <li><a href="{{ route('menus') }}"
                                                class="{{ Request::segment(1) === 'menus' ? 'text-violet-600' : '' }} sidebar-link">Menus</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- ORGANIZATION -->
                                <!-- ================================================= -->
                                @php $org = ['companies','department','tenants','locations']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $org) ? 'true' : 'false' }} }">

                                    <button @click="open = !open" class="settings-subheader">
                                        <span>Organization</span>
                                        <svg class="chevron" :class="open ? 'rotate-180' : ''"></svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="submenu">
                                        <li><a href="{{ route('companies') }}"
                                                class="{{ Request::segment(1) === 'companies' ? 'text-violet-600' : '' }} sidebar-link">Company</a>
                                        </li>
                                        <li><a href="{{ route('department') }}"
                                                class="{{ Request::segment(1) === 'department' ? 'text-violet-600' : '' }} sidebar-link">Department</a>
                                        </li>
                                        <li><a href="{{ route('tenants') }}"
                                                class="{{ Request::segment(1) === 'tenants' ? 'text-violet-600' : '' }} sidebar-link">Tenant</a>
                                        </li>
                                        <li><a href="{{ route('locations') }}"
                                                class="{{ Request::segment(1) === 'locations' ? 'text-violet-600' : '' }} sidebar-link">Location</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- MASTER DATA -->
                                <!-- ================================================= -->
                                @php $md = ['categories','vendors','inventories','autonbrs','tops']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $md) ? 'true' : 'false' }} }">

                                    <button @click="open = !open" class="settings-subheader">
                                        <span>Master Data</span>
                                        <svg class="chevron" :class="open ? 'rotate-180' : ''"></svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="submenu">
                                        <li><a href="{{ route('categories') }}"
                                                class="{{ Request::segment(1) === 'categories' ? 'text-violet-600' : '' }} sidebar-link">Category</a>
                                        </li>
                                        <li><a href="{{ route('vendors') }}"
                                                class="{{ Request::segment(1) === 'vendors' ? 'text-violet-600' : '' }} sidebar-link">Vendor</a>
                                        </li>
                                        <li><a href="{{ route('inventories') }}"
                                                class="{{ Request::segment(1) === 'inventories' ? 'text-violet-600' : '' }} sidebar-link">Inventory</a>
                                        </li>
                                        <li><a href="{{ route('autonbrs') }}"
                                                class="{{ Request::segment(1) === 'autonbrs' ? 'text-violet-600' : '' }} sidebar-link">Autonbr</a>
                                        </li>
                                        <li><a href="{{ route('tops') }}"
                                                class="{{ Request::segment(1) === 'tops' ? 'text-violet-600' : '' }} sidebar-link">TOP</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- WORKFLOW -->
                                <!-- ================================================= -->

                                @php $workflowSegments = ['approvals','ifcaintegration']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $workflowSegments) ? 'true' : 'false' }} }">

                                    <button @click="open = !open" class="settings-subheader">
                                        <span>Workflow</span>
                                        <svg class="chevron" :class="open ? 'rotate-180' : ''"></svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="submenu">
                                        <li><a href="{{ route('approvals') }}"
                                                class="{{ Request::segment(1) === 'approvals' ? 'text-violet-600' : '' }} sidebar-link">Approvals</a>
                                        </li>
                                        {{-- <li><a href="{{ route('ifcaintegration') }}"
                                                class="{{ Request::segment(1) === 'ifcaintegration' ? 'text-violet-600' : '' }} sidebar-link">IFCA
                                                Integration</a>
                                        </li> --}}
                                    </ul>
                                </li>

                            </ul>
                        </li>
                    @endif
                @endauth

            </ul>
        </div>
    </aside>
</div>
