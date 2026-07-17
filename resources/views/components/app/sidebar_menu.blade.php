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
                        class="w-full rounded-lg border border-gray-200 bg-gray-50 py-2 pl-9 pr-8 text-sm text-gray-700 placeholder-gray-400 focus:border-indigo-400 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-900">

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
                <li class="mt-0" x-data="sidebarFavourites()" x-init="init()" x-show="items.length > 0" x-cloak>

                    <button @click="open = !open"
                        class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-50 transition-all duration-200 hover:bg-amber-100 dark:text-amber-300 dark:bg-amber-900/20 dark:hover:bg-amber-900/30">
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

                    <ul x-show="open" x-collapse class="mt-1 space-y-1">
                        <template x-for="item in items" :key="item.id">
                            <li class="flex items-center justify-between rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                <a :href="item.url" class="flex min-w-0 flex-1 items-center gap-3 px-3 py-2 text-sm">
                                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" :d="item.menu_icon" />
                                    </svg>
                                    <span class="truncate" x-text="item.menu_name"></span>
                                </a>

                                <button type="button" @click.prevent.stop="removeItem(item)"
                                    class="mr-2 shrink-0 rounded p-1 text-amber-400 transition-colors hover:text-amber-500"
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
                                class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5"><svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $rootMenu->menu_icon }}" /></svg><span class="whitespace-normal wrap-break-word leading-snug">{{ $rootMenu->menu_name }}</span></div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-1">

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
                                        class="flex min-w-0 flex-1 items-center gap-3 px-3 py-2 text-sm">

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
                                        flex w-full items-center justify-between rounded-lg px-3 py-2 text-sm">

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

                                    <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-9">

                                        @foreach ($children as $child)

                                            <li class="flex items-center justify-between rounded-md">

                                                <a href="{{ $child->menu_route ? route($child->menu_route) : '#' }}"
                                                    class="{{ Route::is([$child->menu_route, $child->menu_route . '.*'])
                                                        ? 'text-indigo-600'
                                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}
                                                    block min-w-0 flex-1 truncate px-3 py-1.5 text-sm">

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
                    @if (auth()->user()->user_role === 'admin')
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
                                'business-units',
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
                                'approvalsgroupbiaya',
                                'budgetmonitor',
                                'ifcaintegration',
                            ];
                        @endphp

                        <li class="mt-4" x-data="{ open: true }">

                            <button @click="open = !open"
                                class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5"><svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z"/></svg><span class="whitespace-normal wrap-break-word leading-snug">SETTINGS</span></div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-2">

                                <!-- ================================================= -->
                                <!-- ACCESS CONTROL STUDIO (guided one-page console) -->
                                <!-- ================================================= -->
                                <li>
                                    <a href="{{ route('access_control_studio') }}"
                                        class="{{ Request::segment(1) === 'access_control_studio' ? 'text-white bg-indigo-600 dark:bg-indigo-500' : 'text-indigo-700 bg-indigo-50 dark:text-indigo-300 dark:bg-indigo-900/20' }} mb-2 flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition-colors duration-200 hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500">
                                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <path d="M13 2 3 14h7l-1 8 10-12h-7l1-8z" />
                                        </svg>
                                        <span>Access Control Studio</span>
                                    </a>
                                </li>

                                <!-- ================================================= -->
                                <!-- USER & ACCESS -->
                                <!-- ================================================= -->
                                @php $ua = ['users','roles','access_rights','role_menus','group-acc-specific']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $ua) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">User &
                                            Access</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('users') }}"
                                                class="{{ Request::segment(1) === 'users' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Users</a>
                                        </li>
                                        <li><a href="{{ route('group_acc_specific') }}"
                                                class="{{ Request::segment(1) === 'group-acc-specific' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Group
                                                Access Specific</a>
                                        </li>
                                        {{-- <li><a href="{{ route('roles') }}"
                                                class="{{ Request::segment(1) === 'roles' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Roles</a>
                                        </li>
                                        <li><a href="{{ route('access_rights') }}"
                                                class="{{ Request::segment(1) === 'access_rights' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Access
                                                Rights</a></li>
                                        <li><a href="{{ route('role_menus') }}"
                                                class="{{ Request::segment(1) === 'role_menus' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Role
                                                Menus</a></li> --}}
                                    </ul>
                                </li>

                                <!-- ================================================= -->
                                <!-- APPLICATION -->
                                <!-- ================================================= -->
                                {{-- @php $app = ['applications','screens','menus']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $app) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">Application</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
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
                                <!-- ORGANIZATION -->
                                <!-- ================================================= -->
                                @php $org = ['companies','department','tenants','locations']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $org) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">Organization</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('companies') }}"
                                                class="{{ Request::segment(1) === 'companies' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Companies</a>
                                        </li>
                                        <li><a href="{{ route('department') }}"
                                                class="{{ Request::segment(1) === 'department' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Department</a>
                                        </li>
                                        <li><a href="{{ route('business-units') }}"
                                                class="{{ Request::segment(1) === 'business-units' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Business
                                                Units</a>
                                        </li>
                                        <li><a href="{{ route('tenants') }}"
                                                class="{{ Request::segment(1) === 'tenants' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Tenants</a>
                                        </li>
                                        <li><a href="{{ route('locations') }}"
                                                class="{{ Request::segment(1) === 'locations' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Locations</a>
                                        </li>


                                    </ul>
                                </li>
                                <!-- ================================================= -->
                                <!-- MASTER DATA -->
                                <!-- ================================================= -->
                                @php $md = ['categories','vendors','autonbrs','tops']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $md) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">Master
                                            Data</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('categories') }}"
                                                class="{{ Request::segment(1) === 'categories' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Categories</a>
                                        </li>
                                        <li><a href="{{ route('vendors') }}"
                                                class="{{ Request::segment(1) === 'vendors' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Vendors</a>
                                        </li>
                                        {{-- <li><a href="{{ route('inventories') }}"
                                                class="{{ Request::segment(1) === 'inventories' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Inventories</a>
                                        </li> --}}
                                        <li><a href="{{ route('autonbrs') }}"
                                                class="{{ Request::segment(1) === 'autonbrs' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Autonbrs</a>
                                        </li>
                                        <li><a href="{{ route('tops') }}"
                                                class="{{ Request::segment(1) === 'tops' ? 'text-indigo-600' : '' }} sidebar-link text-sm">TOPS</a>
                                        </li>
                                        <li><a href="{{ route('sys-calendar') }}"
                                                class="{{ Request::segment(1) === 'sys-calendar' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Calendar
                                                Exception</a>
                                        </li>
                                        <li><a href="{{ route('kendaraan') }}"
                                                class="{{ Request::segment(1) === 'kendaraan' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Kendaraan</a>
                                        </li>
                                        <li><a href="{{ route('groupbiayanonpurch') }}"
                                                class="{{ Request::segment(1) === 'groupbiayanonpurch' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Group
                                                Biaya Non Purch</a>
                                        </li>
                                    </ul>
                                </li>
                                <!-- ================================================= -->
                                <!-- WORKFLOW -->
                                <!-- ================================================= -->
                                @php $workflowSegments = ['approvals','manage-approvals','attachments-master','ifcaintegration']; @endphp
                                <li x-data="{ open: {{ in_array(Request::segment(1), $workflowSegments) ? 'true' : 'false' }} }">

                                    <button @click="open = !open"
                                        class="flex w-full items-center justify-between rounded-lg px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-400 transition-colors duration-150 hover:bg-gray-50 hover:text-gray-600 dark:hover:bg-gray-700/40 dark:hover:text-gray-300">

                                        <span
                                            class="flex-1 whitespace-normal wrap-break-word text-left leading-snug">Workflow</span>

                                        <svg class="chevron h-4 w-4 transition-transform" :class="open ? 'rotate-180' : ''"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M6 9l6 6 6-6" />
                                        </svg>
                                    </button>

                                    <ul x-show="open" x-collapse class="space-y-1 pl-4">
                                        <li><a href="{{ route('approvals') }}"
                                                class="{{ Request::segment(1) === 'approvals' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Approvals</a>
                                        </li>
                                        <li><a href="{{ route('manage-approvals') }}"
                                                class="{{ Request::segment(1) === 'manage-approvals' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Manage
                                                Approval</a>
                                        </li>
                                        <li><a href="{{ route('approvalsgroupbiaya') }}"
                                                class="{{ Request::segment(1) === 'approvalsgroupbiaya' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Approvals
                                                Group Biaya</a>
                                        </li>
                                        <li><a href="{{ route('attachments-master') }}"
                                                class="{{ Request::segment(1) === 'attachments-master' ? 'text-indigo-600' : '' }} sidebar-link text-sm">Attachments
                                                Master</a>
                                        </li>
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
                                class="flex w-full items-center justify-between rounded-xl px-4 py-2.5 text-xs font-bold uppercase tracking-widest text-indigo-700 bg-indigo-50 transition-all duration-200 hover:bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30">
                                <div class="flex items-center gap-2.5">
                                    <svg class="h-4 w-4 shrink-0 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8z M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9c.14.32.22.66.22 1H21a2 2 0 0 1 0 4h-.09c-.34 0-.68.08-1 .22z"/></svg>
                                    <span class="whitespace-normal wrap-break-word leading-snug">SETTINGS</span>
                                </div>
                                <svg class="h-3 w-3 transition-transform" :class="open ? 'rotate-180' : ''"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6" />
                                </svg>
                            </button>

                            <ul x-show="open" x-collapse class="mt-1 space-y-1 pl-2">
                                <li>
                                    <a href="{{ route('groupbiayanonpurch') }}"
                                        class="{{ Request::segment(1) === 'groupbiayanonpurch' ? 'text-indigo-600' : '' }} sidebar-link text-sm">
                                        Group Biaya Non Purch
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
function sidebarMenuSearch(index) {
    return {
        index: index,
        query: '',
        open: false,

        get results() {
            const term = this.query.trim().toLowerCase();
            if (!term) return [];

            return this.index.filter(item =>
                item.menu_name.toLowerCase().includes(term) ||
                (item.parent_name || '').toLowerCase().includes(term)
            ).slice(0, 8);
        }
    };
}

function sidebarFavourites() {
    return {
        items: [],
        open: true,

        init() {
            this.load();
            window.addEventListener('favourites-changed', () => this.load());
        },

        async load() {
            try {
                const res = await fetch('{{ route('menu-favourites.index') }}', { headers: { 'Accept': 'application/json' } });
                if (!res.ok) return;
                const { data = [] } = await res.json();
                this.items = data;
            } catch (e) {
                console.error('sidebar favourites load failed', e);
            }
        },

        async removeItem(item) {
            try {
                await fetch('{{ route('menu-favourites.toggle') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ screen_id: item.screen_id, application_id: item.application_id })
                });
                this.items = this.items.filter(i => i.id !== item.id);
                window.dispatchEvent(new CustomEvent('favourites-changed'));
            } catch (e) {
                console.error('sidebar remove favourite failed', e);
            }
        }
    };
}

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
