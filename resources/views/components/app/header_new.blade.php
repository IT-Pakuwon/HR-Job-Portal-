<header
    class="sticky top-0 z-30 flex h-14 items-center gap-2 bg-white px-2 sm:gap-3 sm:px-4 dark:bg-gray-800">

    <!-- SIDEBAR BUTTON -->
    <button @click="sidebarOpen = true"
        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700">
        <span class="sr-only">Open menu</span>
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    @php
        // A page can @push('header-title') to show its own title here (large screens).
        // Otherwise fall back to a breadcrumb built from the sidebar menu tree:
        // the deepest menu item whose route matches the current route (same test the
        // sidebar uses for its active state), plus its parent group(s).
        $headerTitle = trim($__env->yieldPushContent('header-title'));
        $crumbs = [];

        // Menu item -> trail, used both for route matching and for resolving a page's own trail.
        $menuTrails = [];
        // Each crumb is ['label' => ..., 'url' => ...|null]; url is null for pure groups.
        $menuCrumb = function ($m) {
            $url = null;
            if ($m->menu_route && Route::has($m->menu_route)) {
                try {
                    $url = route($m->menu_route);
                } catch (\Throwable $e) {
                    $url = null; // route needs parameters
                }
            }
            return ['label' => $m->menu_name, 'url' => $url];
        };
        $collect = function ($menus, array $trail) use (&$collect, &$menuTrails, $menuCrumb) {
            foreach ($menus as $m) {
                $path = array_merge($trail, [$menuCrumb($m)]);
                if ($m->menu_route && Route::has($m->menu_route)) {
                    $menuTrails[] = ['route' => $m->menu_route, 'trail' => $path];
                }
                if ($m->relationLoaded('children') && $m->children->isNotEmpty()) {
                    $collect($m->children, $path);
                }
            }
        };
        $collect($rootMenus ?? collect(), []);

        // A detail page that renders <x-breadcrumb> (e.g. Home > CALR Non Purchase > Show Details)
        // hands its trail over: find the menu item its list link points at, use that menu
        // trail (Purchasing > Non Purchase > CALR List), then append the page's remaining crumbs.
        $pageItems = app()->bound('page.breadcrumb') ? app('page.breadcrumb') : [];
        if ($headerTitle === '' && !empty($pageItems)) {
            $norm = fn ($u) => trim((string) parse_url((string) $u, PHP_URL_PATH), '/');
            $uriOf = fn ($name) => trim((string) optional(Route::getRoutes()->getByName($name))->uri(), '/');

            $matchAt = null;
            $matchTrail = [];
            foreach ($pageItems as $idx => $it) {
                if (empty($it['url'])) {
                    continue;
                }
                $p = $norm($it['url']);
                foreach ($menuTrails as $mt) {
                    if ($p !== '' && $uriOf($mt['route']) === $p) {
                        $matchAt = $idx;
                        $matchTrail = $mt['trail'];
                    }
                }
            }

            if ($matchAt !== null) {
                $crumbs = array_merge($matchTrail, array_map(fn ($i) => ['label' => $i['label'], 'url' => $i['url'] ?? null], array_slice($pageItems, $matchAt + 1)));
            } else {
                // No menu item behind this page: show the page's own trail minus "Home".
                $crumbs = array_values(array_filter(
                    array_map(fn ($i) => ['label' => $i['label'], 'url' => $i['url'] ?? null], $pageItems),
                    fn ($c) => strcasecmp($c['label'], 'Home') !== 0
                ));
            }
        } elseif ($headerTitle === '') {
            $bestScore = 0;
            $walk = function ($menus, array $trail) use (&$walk, &$crumbs, &$bestScore, $menuCrumb) {
                foreach ($menus as $m) {
                    $path = array_merge($trail, [$menuCrumb($m)]);
                    $r = $m->menu_route;

                    if ($r && Route::has($r) && Route::is([$r, $r . '.*'])) {
                        // exact route match beats a "route.*" prefix match; deeper beats shallower
                        $score = (Route::is($r) ? 100 : 50) + count($path);
                        if ($score > $bestScore) {
                            $bestScore = $score;
                            $crumbs = $path;
                        }
                    }

                    if ($m->relationLoaded('children') && $m->children->isNotEmpty()) {
                        $walk($m->children, $path);
                    }
                }
            };
            $walk($rootMenus ?? collect(), []);
        }
    @endphp

    <div class="hidden min-w-0 flex-1 items-center pl-2 lg:flex">
        @if ($headerTitle !== '')
            {!! $headerTitle !!}
        @elseif (!empty($crumbs))
            <nav class="min-w-0" aria-label="Breadcrumb">
                <ol class="flex flex-nowrap items-center gap-1.5 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    @foreach ($crumbs as $i => $crumb)
                        <li class="flex items-center gap-1.5">
                            @if ($i > 0)
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-gray-400 dark:text-gray-600">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            @endif
                            @if (!$loop->last && !empty($crumb['url']))
                                <a href="{{ $crumb['url'] }}" class="font-medium hover:text-indigo-600 dark:hover:text-indigo-400">{{ $crumb['label'] }}</a>
                            @else
                                <span class="{{ $loop->last ? 'font-semibold text-gray-800 dark:text-gray-100' : 'font-medium' }}">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>

    <div class="ml-auto flex shrink-0 items-center gap-1 sm:gap-3">
        <!-- Global document search: a magnifier that expands in place (jump to a document by ID or name) -->
        <x-app.global-search />

        <!-- Document status notifications (revised / rejected) -->
        <x-app.document-notifications />

        <a href="{{ route('manual', ['root' => 'faq']) }}"
            class="relative hidden h-11 w-11 items-center justify-center rounded-lg text-gray-600 hover:bg-gray-100 sm:flex dark:text-gray-300 dark:hover:bg-gray-700"
            title="Manual Book"
            x-data="{ seen: localStorage.getItem('manualBookSeen') === 'true' }"
            @click="seen = true; localStorage.setItem('manualBookSeen', 'true')">
            <i class="fas fa-book text-lg"></i>
            <span x-show="!seen" class="absolute right-2 top-2 flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-indigo-400 opacity-75"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-indigo-500 ring-2 ring-white dark:ring-gray-800"></span>
            </span>
        </a>
        <!-- Dark mode toggle -->
        <div class="relative z-[50]">
            <x-theme-toggle />
        </div>

        <hr class="hidden h-6 w-px border-none bg-gray-200 sm:block dark:bg-gray-700/60" />

        <x-dropdown-profile align="right" />

    </div>
</header>
