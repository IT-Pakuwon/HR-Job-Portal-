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
        class="fixed left-0 top-0 z-50 h-[100dvh] w-72 max-w-[85vw] overflow-y-auto bg-white shadow-xl dark:bg-gray-800">


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
        <div class="space-y-4 p-4">

            @php
                $menuSearchIndex = collect();
                $allowedIdsForSearch = collect($allowedMenuIds ?? [])->all();

                foreach (($rootMenus ?? collect()) as $rootMenu) {
                    foreach ($rootMenu->children as $lvl1) {
                        $lvl2 = $lvl1->children->whereIn('menu_id', $allowedIdsForSearch);

                        if ($lvl2->isEmpty() && !empty($lvl1->menu_route)) {
                            $menuSearchIndex->push([
                                'menu_name'   => $lvl1->menu_name,
                                'menu_icon'   => $lvl1->menu_icon,
                                'parent_name' => $rootMenu->menu_name,
                                'url'         => Route::has($lvl1->menu_route) ? route($lvl1->menu_route) : '#',
                            ]);
                        } elseif ($lvl2->isNotEmpty()) {
                            foreach ($lvl2 as $leaf) {
                                if (!empty($leaf->menu_route)) {
                                    $menuSearchIndex->push([
                                        'menu_name'   => $leaf->menu_name,
                                        'menu_icon'   => $leaf->menu_icon,
                                        'parent_name' => $lvl1->menu_name,
                                        'url'         => Route::has($leaf->menu_route) ? route($leaf->menu_route) : '#',
                                    ]);
                                }
                            }
                        }
                    }
                }

                // ================= SETTINGS MENU (hardcoded, role-gated) =================
                // Mirrors the same role checks used to render the GLOBAL SETTINGS block below,
                // so search never surfaces a link the current user can't actually see/click.
                $settingsIcon = 'M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z';

                $addSearchItem = function ($routeName, $label, $parent) use ($menuSearchIndex, $settingsIcon) {
                    if (Route::has($routeName)) {
                        $menuSearchIndex->push([
                            'menu_name'   => $label,
                            'menu_icon'   => $settingsIcon,
                            'parent_name' => $parent,
                            'url'         => route($routeName),
                        ]);
                    }
                };

                if (auth()->check()) {
                    $userRolesForSearch = auth()->user()->roles();

                    if (in_array('admin', $userRolesForSearch)) {
                        $addSearchItem('access_control_studio', 'Access Control Studio', 'Global Settings');

                        $addSearchItem('users', 'Users', 'Organization Setup');
                        $addSearchItem('autonbrs', 'Autonbrs', 'Organization Setup');
                        $addSearchItem('companies', 'Companies', 'Organization Setup');
                        $addSearchItem('locations', 'Locations', 'Organization Setup');
                        $addSearchItem('categories', 'Categories', 'Organization Setup');
                        $addSearchItem('department', 'Department', 'Organization Setup');
                        $addSearchItem('sys-calendar', 'Calendar Exception', 'Organization Setup');

                        $addSearchItem('approvals', 'Approvals', 'Workflow');
                        $addSearchItem('manage-approvals', 'Manage Approval', 'Workflow');
                        $addSearchItem('attachments-master', 'Attachments Master', 'Workflow');

                        $addSearchItem('grading', 'Grading', 'HRGA Setup');
                        $addSearchItem('kendaraan', 'Kendaraan', 'HRGA Setup');
                        $addSearchItem('group_acc_specific', 'Group Access Specific', 'HRGA Setup');

                        $addSearchItem('tops', 'TOPS', 'Purchasing Setup');
                        $addSearchItem('tenants', 'Tenants', 'Purchasing Setup');
                        $addSearchItem('vendors', 'Vendors', 'Purchasing Setup');
                        $addSearchItem('groupbiayanonpurch', 'Group Biaya Non Purch', 'Purchasing Setup');

                        $addSearchItem('integration.ifcaintegration', 'IFCA Integration', 'Integration Setup');
                        $addSearchItem('integration.acumvms.index', 'ACUM VMS Integration', 'Integration Setup');
                    } elseif (\App\Models\SysUserRole::where('username', auth()->user()->username ?? '')
                            ->whereIn('role_id', ['COSTCTRLACCESS', 'APFINACCESS'])
                            ->where(function ($q) { $q->whereNull('status')->orWhere('status', 'A'); })
                            ->exists()) {
                        $addSearchItem('groupbiayanonpurch', 'Group Biaya Non Purch', 'Global Settings');
                    }

                    if (in_array('adminsby', $userRolesForSearch)) {
                        $addSearchItem('users-sby', 'Users', 'Setting');
                        $addSearchItem('approvals-sby', 'Approvals', 'Setting');
                    }
                }

                $menuSearchIndex = $menuSearchIndex->values();
            @endphp

            <!-- ================= MENU SEARCH ================= -->
            <div class="relative" x-data="sidebarMenuSearch({{ $menuSearchIndex->toJson() }})">
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35m1.35-5.15a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
                    </svg>

                    <input type="text" x-model="query" @input="open = query.length > 0"
                        @focus="open = query.length > 0" @keydown.escape="query = ''; open = false"
                        placeholder="Search menu..."
                        class="min-h-11 w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-8 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-400 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900">

                    <button type="button" x-show="query.length > 0" @click="query = ''; open = false"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div x-show="open" @click.outside="open = false" x-transition
                    class="absolute z-20 mt-1 max-h-72 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">

                    <template x-if="results.length === 0">
                        <p class="px-3 py-4 text-center text-sm text-gray-400 dark:text-gray-500">No menu found.</p>
                    </template>

                    <template x-for="item in results" :key="item.url + item.menu_name">
                        <a :href="item.url" @click="open = false"
                            class="flex items-center gap-3 px-3 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.menu_icon"></path>
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-gray-700 dark:text-gray-200" x-text="item.menu_name"></span>
                                <span class="block truncate text-[11px] text-gray-400 dark:text-gray-500" x-text="item.parent_name"></span>
                            </span>
                        </a>
                    </template>
                </div>
            </div>

            <ul class="space-y-1">

                <!-- ================= FAVORITES ================= -->
                <li class="mt-0" x-data="sidebarFavourites('{{ route('menu-favourites.index') }}', '{{ route('menu-favourites.toggle') }}')" x-init="init()" x-show="items.length > 0" x-cloak>

                    <button @click="open = !open"
                        class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-50 transition-all duration-200 hover:bg-amber-100 dark:text-amber-300 dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
                        <div class="flex items-center gap-2.5">
                            <svg class="h-4 w-4 shrink-0 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 2.5l2.29 4.64 5.12.74-3.7 3.61.87 5.1L10 14.9l-4.58 2.4.87-5.1-3.7-3.61 5.12-.74L10 2.5z" />
                            </svg>
                            <span class="whitespace-normal wrap-break-word leading-snug">Favorites</span>
                        </div>
                        <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </button>

                    <ul x-show="open" x-collapse class="mt-1 space-y-0.5">
                        <template x-for="item in items" :key="item.id">
                            <li class="flex items-center justify-between rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <a :href="item.url" class="flex min-h-9 min-w-0 flex-1 items-center gap-3 px-3 py-1.5 text-sm">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.menu_icon" />
                                    </svg>
                                    <span class="truncate" x-text="item.menu_name"></span>
                                </a>

                                <button type="button" @click.prevent.stop="removeItem(item)"
                                    class="mr-2 flex h-11 w-11 shrink-0 items-center justify-center rounded p-1 text-amber-400 transition-colors hover:text-amber-500"
                                    title="Remove from favourites">
                                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 2.5l2.29 4.64 5.12.74-3.7 3.61.87 5.1L10 14.9l-4.58 2.4.87-5.1-3.7-3.61 5.12-.74L10 2.5z" />
                                    </svg>
                                </button>
                            </li>
                        </template>
                    </ul>
                </li>

                <!-- ================= DYNAMIC MENU MODULES (order = menu_sort_order, from Sys Menu Tree) ================= -->
                @foreach ($rootMenus as $rootMenu)

                    @php
                        $allowedIds = isset($allowedMenuIds) ? $allowedMenuIds->toArray() : [];

                        $visibleMenus = $rootMenu->children->filter(function ($menu) use ($allowedIds) {

                            if ($menu->status !== 'A') {
                                return false;
                            }

                            $children = $menu->children->whereIn('menu_id', $allowedIds);

                            return $children->isNotEmpty()
                                || (
                                    in_array($menu->menu_id, $allowedIds)
                                    && !empty($menu->menu_route)
                                );
                        });
                    @endphp

                    @if ($visibleMenus->isNotEmpty())

                        <li class="mt-4" x-data="{ open: true }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5"><svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $rootMenu->menu_icon }}" /></svg><span class="whitespace-normal wrap-break-word leading-snug">{{ $rootMenu->menu_name }}</span></div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-0.5">

                        @foreach ($visibleMenus as $menu)

                            @php
                                $children = $menu->children->whereIn('menu_id', $allowedIds);
                                $isDirectMenu = !empty($menu->menu_route);
                            @endphp

                            {{-- SINGLE MENU --}}
                            @if ($children->isEmpty() && $isDirectMenu)

                                <li
                                    class="{{ Route::is([$menu->menu_route, $menu->menu_route . '.*'])
                                        ? 'bg-indigo-500/10 text-indigo-600'
                                        : 'hover:bg-gray-100 dark:hover:bg-gray-700' }} flex items-center justify-between rounded-lg">

                                    <a href="{{ route($menu->menu_route) }}"
                                        class="flex min-h-9 min-w-0 flex-1 items-center gap-3 px-3 py-1.5 text-sm">

                                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="1.5"
                                                d="{{ $menu->menu_icon }}" />
                                        </svg>

                                        <span class="truncate">{{ $menu->menu_name }}</span>
                                    </a>

                                    <x-app.favourite-star :screen-id="$menu->screen_id" :application-id="$menu->application_id" :favourite-keys="$favouriteKeys ?? []" />

                                </li>

                            {{-- MENU WITH SUB --}}
                            @elseif ($children->isNotEmpty())

                                @php
                                    $isActive = $children->contains(
                                        fn ($c) => $c->menu_route && Route::is([$c->menu_route, $c->menu_route . '.*'])
                                    );
                                @endphp

                                <li x-data="{ open: {{ $isActive ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="{{ $isActive
                                            ? 'bg-indigo-500/10 text-indigo-600'
                                            : 'hover:bg-gray-100 dark:hover:bg-gray-700' }}
                                        flex min-h-9 w-full items-center justify-between rounded-lg px-3 py-1.5 text-sm">

                                        <div class="flex items-center gap-3">

                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="{{ $menu->menu_icon }}" />
                                            </svg>

                                            <span class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">
                                                {{ $menu->menu_name }}
                                            </span>

                                        </div>

                                        <svg class="chevron h-4 w-4 transition-transform"
                                            :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round">

                                            <path d="M6 9l6 6 6-6" />

                                        </svg>

                                    </button>

                                    <ul x-show="open" x-collapse class="mt-1 space-y-0.5 pl-9">

                                        @foreach ($children as $child)

                                            <li class="flex items-center justify-between rounded-md">

                                                <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                    class="{{ Route::is([$child->menu_route, $child->menu_route . '.*'])
                                                        ? 'text-indigo-600'
                                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}
                                                    flex min-h-8 min-w-0 flex-1 items-center truncate px-3 py-1 text-sm">

                                                    {{ $child->menu_name }}

                                                </a>

                                                <x-app.favourite-star :screen-id="$child->screen_id" :application-id="$child->application_id" :favourite-keys="$favouriteKeys ?? []" />

                                            </li>

                                        @endforeach

                                    </ul>

                                </li>

                            @endif

                        @endforeach

                            </ul>

                        </li>

                    @endif

                @endforeach


                <!-- ================= MODUL SETTING ================= -->
                @auth
                    @php
                        $userRoles = auth()->user()->roles();
                    @endphp
                    @if (in_array('admin', $userRoles))
                        @php
                            $settingsSegments = [
                                'access_control_studio',
                                'users',
                                'roles',
                                'access_rights',
                                'role_menus',
                                'group-acc-specific',
                                'applications',
                                'screens',
                                'menus',
                                'companies',
                                'department',
                                'tenants',
                                'locations',
                                'categories',
                                'vendors',
                                'autonbrs',
                                'tops',
                                'sys-calendar',
                                'attachments-master',
                                'kendaraan',
                                'groupbiayanonpurch',
                                'approvals',
                                'manage-approvals',
                                'budgetmonitor',
                                'ifcaintegration',
                            ];
                        @endphp

                        <li class="mt-4" x-data="{ open: true }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5"><svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z"/></svg><span class="whitespace-normal wrap-break-word leading-snug">GLOBAL SETTINGS</span></div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-2 space-y-0.5 pl-2">

                                <!-- ================================================= -->
                                <!-- ACCESS CONTROL STUDIO (guided one-page console) -->
                                <!-- ================================================= -->
                                <li class="mb-2">
                                    <a href="{{ route('access_control_studio') }}"
                                        class="{{ Request::segment(1) === 'access_control_studio' ? 'bg-indigo-100/70 dark:bg-indigo-500/15' : 'bg-indigo-50/70 dark:bg-indigo-500/10' }} group flex items-center gap-3 rounded-2xl px-3.5 py-2.5 transition-colors duration-200 hover:bg-indigo-100/70 dark:hover:bg-indigo-500/15 border-indigo-700 dark:border-indigo-800 borderIn">
                                        <span class="flex min-w-0 flex-col leading-tight">
                                            <span class="truncate text-sm font-semibold text-indigo-700 dark:text-indigo-200">Access Control Studio</span>
                                        </span>
                                    </a>
                                </li>

                                <!-- ================================================= -->
                                <!-- APPLICATION -->
                                <!-- ================================================= -->
                                {{-- @php $app = ['applications','screens','menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $app) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">Application</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li><a href="{{ route('applications') }}"
                                                class="{{ Request::segment(1) === 'applications' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Applications</a>
                                        </li>
                                        <li><a href="{{ route('screens') }}"
                                                class="{{ Request::segment(1) === 'screens' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Screens</a>
                                        </li>
                                        <li><a href="{{ route('menus') }}"
                                                class="{{ Request::segment(1) === 'menus' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Menus</a>
                                        </li>
                                    </ul>
                                </li> --}}

                                <!-- ================================================= -->
                                <!-- ORGANIZATION SETUP (Organization + Master Data merged) -->
                                <!-- ================================================= -->
                                @php $orgSetup = ['users', 'autonbrs', 'companies', 'locations', 'categories', 'department', 'sys-calendar']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $orgSetup) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex flex-1 items-center gap-2 text-left">
                                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M5 21V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v17M3 21h18M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2" /></svg>
                                            <span class="whitespace-normal wrap-break-word leading-snug">Organization Setup</span>
                                        </span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li><a href="{{ route('users') }}"
                                                class="{{ Request::segment(1) === 'users' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Users</a>
                                        </li>
                                        <li><a href="{{ route('autonbrs') }}"
                                                class="{{ Request::segment(1) === 'autonbrs' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Autonbrs</a>
                                        </li>
                                        <li><a href="{{ route('companies') }}"
                                                class="{{ Request::segment(1) === 'companies' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Companies</a>
                                        </li>
                                        <li><a href="{{ route('locations') }}"
                                                class="{{ Request::segment(1) === 'locations' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Locations</a>
                                        </li>
                                        <li><a href="{{ route('categories') }}"
                                                class="{{ Request::segment(1) === 'categories' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Categories</a>
                                        </li>
                                        <li><a href="{{ route('department') }}"
                                                class="{{ Request::segment(1) === 'department' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Department</a>
                                        </li>
                                        <li><a href="{{ route('sys-calendar') }}"
                                                class="{{ Request::segment(1) === 'sys-calendar' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Calendar
                                                Exception</a>
                                        </li>
                                        {{-- <li><a href="{{ route('inventories') }}"
                                                class="{{ Request::segment(1) === 'inventories' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Inventories</a>
                                        </li> --}}
                                    </ul>
                                </li>
                                <!-- ================================================= -->
                                <!-- WORKFLOW -->
                                <!-- ================================================= -->
                                @php $workflowSegments = ['approvals','manage-approvals','attachments-master']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $workflowSegments) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex flex-1 items-center gap-2 text-left">
                                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="2" /><circle cx="6" cy="18" r="2" /><circle cx="18" cy="12" r="2" /><path d="M6 8v8M8 6h4l6 6M8 18h4l6-6" /></svg>
                                            <span class="whitespace-normal wrap-break-word leading-snug">Workflow</span>
                                        </span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li><a href="{{ route('approvals') }}"
                                                class="{{ Request::segment(1) === 'approvals' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Approvals</a>
                                        </li>
                                        <li><a href="{{ route('manage-approvals') }}"
                                                class="{{ Request::segment(1) === 'manage-approvals' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Manage
                                                Approval</a>
                                        </li>
                                        <li><a href="{{ route('attachments-master') }}"
                                                class="{{ Request::segment(1) === 'attachments-master' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Attachments
                                                Master</a>
                                        </li>
                                        {{-- <li>
                                            <a href="{{ route('user_sync.index') }}"
                                                class="{{ Request::segment(1) === 'user_sync' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                                User Sync
                                            </a>
                                        </li> --}}
                                        {{-- <li>
                                            <a href="{{ route('test-email.index') }}"
                                                class="{{ Request::segment(1) === 'test-email' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                                TESTING EMAIL
                                            </a>
                                        </li> --}}


                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- HRGA SETUP -->
                                <!-- ================================================= -->
                                @php $hrgaSetup = ['grading', 'kendaraan', 'group-acc-specific', 'performance-management']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $hrgaSetup) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex flex-1 items-center gap-2 text-left">
                                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8h16v10a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V8ZM9 8V6a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2" /></svg>
                                            <span class="whitespace-normal wrap-break-word leading-snug">HRGA Setup</span>
                                        </span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li><a href="{{ route('grading') }}"
                                                class="{{ Request::segment(1) === 'grading' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Grading</a>
                                        </li>
                                        <li><a href="{{ route('kendaraan') }}"
                                                class="{{ Request::segment(1) === 'kendaraan' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Kendaraan</a>
                                        </li>
                                        <li><a href="{{ route('group_acc_specific') }}"
                                                class="{{ Request::segment(1) === 'group-acc-specific' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Group
                                                Access Specific</a>
                                        </li>
                                        <li><a href="{{ route('performance-management') }}"
                                                class="{{ Request::segment(1) === 'performance-management' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Performance
                                                Management</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- PURCHASING SETUP -->
                                <!-- ================================================= -->
                                @php $purchasingSetup = ['tops', 'tenants', 'vendors', 'groupbiayanonpurch']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $purchasingSetup) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex flex-1 items-center gap-2 text-left">
                                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 8ZM9 8V6a3 3 0 0 1 6 0v2" /></svg>
                                            <span class="whitespace-normal wrap-break-word leading-snug">Purchasing Setup</span>
                                        </span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li><a href="{{ route('tops') }}"
                                                class="{{ Request::segment(1) === 'tops' ? 'text-indigo-600' : '' }} sidebar-link text-sm">TOPS</a>
                                        </li>
                                        <li><a href="{{ route('tenants') }}"
                                                class="{{ Request::segment(1) === 'tenants' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Tenants</a>
                                        </li>
                                        <li><a href="{{ route('vendors') }}"
                                                class="{{ Request::segment(1) === 'vendors' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Vendors</a>
                                        </li>
                                        <li><a href="{{ route('groupbiayanonpurch') }}"
                                                class="{{ Request::segment(1) === 'groupbiayanonpurch' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Group
                                                Biaya Non Purch</a>
                                        </li>
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- INTEGRATION SETUP -->
                                <!-- ================================================= -->
                                @php $integrationSetup = ['ifcaintegration', 'acumvms']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $integrationSetup) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between min-h-9 rounded-lg px-4 py-1.5 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex flex-1 items-center gap-2 text-left">
                                            <svg class="section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h13M14 4l3 3-3 3M20 17H7M10 14l-3 3 3 3" /></svg>
                                            <span class="whitespace-normal wrap-break-word leading-snug">Integration Setup</span>
                                        </span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-0.5 pl-4">
                                        <li>
                                            <a href="{{ route('integration.ifcaintegration') }}"
                                                class="{{ Request::segment(1) === 'ifcaintegration' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                                IFCA Integration
                                            </a>
                                        </li>
                                        <li>
                                            <a href="{{ route('integration.acumvms.index') }}"
                                                class="{{ Request::segment(1) === 'acumvms' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                                ACUM VMS Integration
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                    @elseif(\App\Models\SysUserRole::where('username', auth()->user()->username ?? '')
                            ->whereIn('role_id', ['COSTCTRLACCESS', 'APFINACCESS'])
                            ->where(function ($q) { $q->whereNull('status')->orWhere('status', 'A'); })
                            ->exists())
                        {{-- Cost Control: only Group Biaya Non Purch under Settings --}}
                        <li class="mt-4"
                            x-data="{ open: {{ Request::segment(1) === 'groupbiayanonpurch' ? 'true' : 'false' }} }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z"/></svg>
                                    <span class="whitespace-normal wrap-break-word leading-snug">GLOBAL SETTINGS</span>
                                </div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                                <li>
                                    <a href="{{ route('groupbiayanonpurch') }}"
                                        class="{{ Request::segment(1) === 'groupbiayanonpurch' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                        Group Biaya Non Purch
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (!in_array('admin', $userRoles) && auth()->user()->hasRole('PURCHACCESS'))
                        {{-- PURCHACCESS (non-admin): scoped access to Vendors under Setting --}}
                        <li class="mt-4"
                            x-data="{ open: {{ Request::segment(1) === 'vendors' ? 'true' : 'false' }} }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h12l-1 12a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1L6 8ZM9 8V6a3 3 0 0 1 6 0v2" /></svg>
                                    <span class="whitespace-normal wrap-break-word leading-snug">SETTING</span>
                                </div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                                <li>
                                    <a href="{{ route('vendors') }}"
                                        class="{{ Request::segment(1) === 'vendors' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                        Vendors
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if (in_array('adminsby', $userRoles))
                        {{-- Admin Surabaya: only Users and Approvals under Setting --}}
                        <li class="mt-4"
                            x-data="{ open: {{ in_array(Request::segment(1), ['users-sby', 'approvals-sby']) ? 'true' : 'false' }} }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between min-h-11 rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z"/></svg>
                                    <span class="whitespace-normal wrap-break-word leading-snug">SETTING</span>
                                </div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-0.5 pl-2">
                                <li>
                                    <a href="{{ route('users-sby') }}"
                                        class="{{ Request::segment(1) === 'users-sby' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                        Users
                                    </a>
                                </li>
                                <li>
                                    <a href="{{ route('approvals-sby') }}"
                                        class="{{ Request::segment(1) === 'approvals-sby' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                        Approvals
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

<script>
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.favourite-star');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const icon = btn.querySelector('.favourite-star-icon');

    fetch('{{ route('menu-favourites.toggle') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            screen_id: btn.dataset.screenId,
            application_id: btn.dataset.applicationId
        })
    })
        .then(res => res.json())
        .then(data => {
            const favourited = data.favourited;
            btn.dataset.favourited = favourited ? '1' : '0';
            btn.classList.toggle('text-amber-400', favourited);
            btn.classList.toggle('hover:text-amber-500', favourited);
            btn.classList.toggle('text-gray-300', !favourited);
            btn.classList.toggle('dark:text-gray-600', !favourited);
            btn.title = favourited ? 'Remove from favourites' : 'Add to favourites';
            icon.setAttribute('fill', favourited ? 'currentColor' : 'none');
            window.dispatchEvent(new CustomEvent('favourites-changed'));
        })
        .catch(err => console.error('favourite toggle failed', err));
});
</script>
