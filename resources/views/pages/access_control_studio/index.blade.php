<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'access_control_studio' ? 'Access Control Studio' : '';

        $stepDefs = [
            ['n' => 1, 'k' => 'application', 'label' => 'Application', 'icon' => 'grid'],
            ['n' => 2, 'k' => 'screen', 'label' => 'Screen', 'icon' => 'screen'],
            ['n' => 3, 'k' => 'menu', 'label' => 'Menu', 'icon' => 'menu'],
            ['n' => 4, 'k' => 'role', 'label' => 'Role', 'icon' => 'role'],
            ['n' => 5, 'k' => 'rolemenu', 'label' => 'Role Menu', 'icon' => 'link'],
            ['n' => 6, 'k' => 'accessrights', 'label' => 'Access Rights', 'icon' => 'shield'],
        ];
    @endphp

    @php
        $icon = function (string $name, string $class = 'h-5 w-5') {
            $paths = [
                'grid'   => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
                'screen' => '<rect x="3" y="4" width="18" height="12" rx="2"/><path d="M8 20h8M12 16v4"/>',
                'menu'   => '<path d="M4 6h16M4 12h16M4 18h10"/>',
                'role'   => '<circle cx="9" cy="8" r="3"/><path d="M2.5 20c0-3.3 2.9-5.5 6.5-5.5s6.5 2.2 6.5 5.5"/><circle cx="17.5" cy="9" r="2.3"/><path d="M16 14.2c2.4.2 4.6 1.7 5 4.3"/>',
                'link'   => '<rect x="2" y="8" width="10" height="8" rx="4"/><rect x="12" y="8" width="10" height="8" rx="4"/>',
                'shield' => '<path d="M12 3l7 3v5.5c0 4.6-3 8.3-7 9.5-4-1.2-7-4.9-7-9.5V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
                'plus'   => '<path d="M12 5v14M5 12h14"/>',
                'route'  => '<path d="M4 4v6a4 4 0 004 4h8a4 4 0 014 4v2"/><circle cx="4" cy="4" r="2"/><circle cx="20" cy="20" r="2"/>',
                'check'  => '<path d="M20 6L9 17l-5-5"/>',
            ];
            return '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">' . ($paths[$name] ?? '') . '</svg>';
        };
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- Hero --}}
        <section class="acs-hero">
            <div class="acs-hero-glow"></div>
            <div class="relative flex flex-wrap items-center gap-x-5 gap-y-3">
                <div class="min-w-0">
                    <div class="acs-eyebrow"><i class="fas fa-shield-halved"></i> RBAC Console</div>
                    <h1 class="acs-hero-title">Access Control Studio</h1>
                </div>
                <div class="acs-tiles relative">
                    <button type="button" class="acs-tile" data-step="application" style="--t:#56b1f3" title="Applications"><i class="fas fa-cubes"></i><b id="tile_app">{{ count($applications) }}</b><span>Apps</span></button>
                    <button type="button" class="acs-tile" data-step="screen" style="--t:#0ea5e9" title="Screens"><i class="fas fa-display"></i><b id="tile_scr">{{ count($screens) }}</b><span>Screens</span></button>
                    <button type="button" class="acs-tile" data-step="menu" style="--t:#10b981" title="Menus"><i class="fas fa-bars-staggered"></i><b id="tile_mnu">{{ count($menus) }}</b><span>Menus</span></button>
                    <button type="button" class="acs-tile" data-step="role" style="--t:#f59e0b" title="Roles"><i class="fas fa-user-shield"></i><b id="tile_rol">{{ count($roles) }}</b><span>Roles</span></button>
                    <button type="button" class="acs-tile acs-tile-gaps" id="tile_gapsBtn" style="--t:#ef4444" title="Open setup gaps"><i class="fas fa-triangle-exclamation"></i><b id="tile_gaps">…</b><span>Gaps</span></button>
                </div>
                <button type="button" id="acs_historyBtn" class="acs-ghost-btn ml-auto"><i class="fas fa-clock-rotate-left"></i> History</button>
            </div>
        </section>

        {{-- Stepper --}}
        <div class="mb-3 overflow-x-auto rounded-2xl border border-gray-100 bg-white px-4 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <ol class="flex min-w-[900px] items-center">
                @foreach ($stepDefs as $step)
                    <li class="flex flex-1 items-center {{ !$loop->last ? '' : 'flex-none' }}">
                        <button type="button" data-step="{{ $step['k'] }}"
                            class="step-node group flex flex-col items-center gap-2 px-1 focus:outline-none">
                            <span class="step-circle flex h-11 w-11 items-center justify-center rounded-full border-2 border-gray-200 text-gray-400 transition-all duration-200 group-hover:border-indigo-300 dark:border-gray-600 dark:text-gray-500">
                                <span class="step-circle-icon">{!! $icon($step['icon']) !!}</span>
                                <span class="step-circle-check hidden">{!! $icon('check') !!}</span>
                            </span>
                            <span class="step-label whitespace-nowrap text-xs font-semibold text-gray-400 dark:text-gray-500">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-300 dark:text-gray-600">Step {{ $step['n'] }}</span><br>
                                {{ $step['label'] }}
                            </span>
                        </button>
                        @if (!$loop->last)
                            <div class="step-connector mx-1 mb-6 h-0.5 flex-1 bg-gray-200 dark:bg-gray-700"></div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>

        {{-- Setup Gaps (read-only coverage report — no table, no middleware, just introspection) --}}
        <div id="gaps_panel" class="mb-3 rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <button type="button" id="gaps_toggle" class="flex w-full items-center justify-between gap-3 px-4 py-2 text-left">
                <div class="flex items-center gap-2.5">
                    <span id="gaps_statusIcon" class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 00-9-9"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Setup Gaps</span>
                    <span id="gaps_summary" class="text-xs text-gray-400">Checking...</span>
                    <span id="gaps_chips" class="ml-1 hidden flex-wrap items-center gap-1.5 sm:flex"></span>
                </div>
                <svg id="gaps_chevron" class="h-4 w-4 shrink-0 text-gray-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div id="gaps_body" class="hidden border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                    <div class="gaps-card gaps-card-red ring-1 ring-red-200 dark:ring-red-900/50">
                        <h3 class="text-xs font-bold uppercase tracking-wide text-red-600 dark:text-red-400"><i class="fas fa-link-slash mr-1"></i> Menus with a missing Route <span id="gaps_cnt_route" class="gaps-count"></span></h3>
                        <p class="mt-0.5 text-xs text-gray-400">The menu's route name isn't defined in this codebase (DB is ahead of the code, or a typo). The sidebar shows these disabled — click to fix.</p>
                        <ul id="gaps_menusRouteMissing" class="gaps-list mt-2"></ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wide text-amber-600 dark:text-amber-400">Menus missing a Screen</h3>
                        <p class="mt-0.5 text-xs text-gray-400">Leaf menus (no sub-menus) with no Screen set — jump to the Menu step and edit them.</p>
                        <ul id="gaps_menusMissingScreen" class="gaps-list mt-2"></ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wide text-red-600 dark:text-red-400">Screens with no route wired</h3>
                        <p class="mt-0.5 text-xs text-gray-400">No <code>access:SCREEN,ACTION</code> middleware anywhere in web.php uses this screen yet.</p>
                        <ul id="gaps_screensWithoutRoute" class="gaps-list mt-2"></ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Unregistered screen IDs in routes</h3>
                        <p class="mt-0.5 text-xs text-gray-400">Used in <code>access:</code> middleware but not found in Screen master data — check for typos/naming mismatches.</p>
                        <ul id="gaps_unregisteredScreens" class="gaps-list mt-2"></ul>
                    </div>
                    <div>
                        <h3 class="text-xs font-bold uppercase tracking-wide text-fuchsia-600 dark:text-fuchsia-400">Menu/Screen application mismatch</h3>
                        <p class="mt-0.5 text-xs text-gray-400">Menu's Application differs from its own Screen's Application — the menu ends up filed under the wrong app.</p>
                        <ul id="gaps_menusAppMismatch" class="gaps-list mt-2"></ul>
                    </div>
                </div>
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Show screens that ARE wired up correctly</summary>
                    <ul id="gaps_screensWithRoute" class="gaps-list mt-2"></ul>
                </details>
            </div>
        </div>

        {{-- ============================== STEP 1: APPLICATION ============================== --}}
        <div class="step-panel" data-panel="application">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">{!! $icon('grid') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Application</h2>
                            <p class="studio-card-desc">The top-level system a screen belongs to (e.g. PURCHAPP, GAAPP).</p>
                        </div>
                    </div>
                    <button id="app_addBtn" class="studio-add-btn">{!! $icon('plus', 'h-4 w-4') !!} Add Application</button>
                </div>
                <div class="rounded-base relative overflow-x-auto">
                    <table id="app_table" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="studio-thead">
                            <tr>
                                <th class="w-24 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Application ID</th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="w-28 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 flex justify-end">
                <button type="button" class="studio-next-btn" data-next="screen">Next: Screen →</button>
            </div>
        </div>

        {{-- ============================== STEP 2: SCREEN ============================== --}}
        <div class="step-panel hidden" data-panel="screen">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300">{!! $icon('screen') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Screen</h2>
                            <p class="studio-card-desc">One page/feature inside an Application. Access Rights are granted per screen.</p>
                        </div>
                    </div>
                    <button id="scr_addBtn" class="studio-add-btn">{!! $icon('plus', 'h-4 w-4') !!} Add Screen</button>
                </div>
                <div class="rounded-base relative overflow-x-auto">
                    <table id="scr_table" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="studio-thead">
                            <tr>
                                <th class="w-24 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Screen ID</th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="px-4 py-3 text-left">Application</th>
                                <th class="w-28 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 flex justify-between">
                <button type="button" class="studio-back-btn" data-back="application">← Back</button>
                <button type="button" class="studio-next-btn" data-next="menu">Next: Menu →</button>
            </div>
        </div>

        {{-- ============================== STEP 3: MENU (tree) ============================== --}}
        <div class="step-panel hidden" data-panel="menu">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">{!! $icon('menu') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Menu</h2>
                            <p class="studio-card-desc">The sidebar entry users click. One menu = one screen. Drag rows to reorder or re-parent.</p>
                        </div>
                    </div>
                    <button id="mnu_addBtn" class="studio-add-btn">{!! $icon('plus', 'h-4 w-4') !!} Add Menu</button>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div class="relative w-full sm:max-w-xs">
                        <input type="text" id="mnu_searchInput" autocomplete="off"
                            placeholder="Search by menu id, name, or route..." class="studio-input">
                        <button type="button" id="mnu_clearSearchBtn"
                            class="absolute right-2 top-1/2 hidden -translate-y-1/2 text-gray-400 hover:text-gray-600"
                            title="Clear search">✕</button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="mnu_resultInfo" class="text-xs text-gray-400"></span>
                        <button type="button" id="mnu_expandAllBtn" class="studio-mini-btn">Expand All</button>
                        <button type="button" id="mnu_collapseAllBtn" class="studio-mini-btn">Collapse All</button>
                    </div>
                </div>

                <div id="mnu_treeContainer" class="mnu-tree-container mt-1 rounded-xl border border-gray-100 p-2 dark:border-gray-700">
                    <div class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Loading menus...</div>
                </div>
            </div>
            <div class="mt-3 flex justify-between">
                <button type="button" class="studio-back-btn" data-back="screen">← Back</button>
                <button type="button" class="studio-next-btn" data-next="role">Next: Role →</button>
            </div>
        </div>

        {{-- ============================== STEP 4: ROLE ============================== --}}
        <div class="step-panel hidden" data-panel="role">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">{!! $icon('role') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Role</h2>
                            <p class="studio-card-desc">A named permission set. Users get assigned one or more roles later, from the Users page.</p>
                        </div>
                    </div>
                    <button id="rol_addBtn" class="studio-add-btn">{!! $icon('plus', 'h-4 w-4') !!} Add Role</button>
                </div>
                <div class="rounded-base relative overflow-x-auto">
                    <table id="rol_table" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead class="studio-thead">
                            <tr>
                                <th class="w-48 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Role ID</th>
                                <th class="px-4 py-3 text-left">Name</th>
                                <th class="w-24 px-4 py-3 text-center">Users</th>
                                <th class="w-24 px-4 py-3 text-center">Menus</th>
                                <th class="w-28 px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 flex justify-between">
                <button type="button" class="studio-back-btn" data-back="menu">← Back</button>
                <button type="button" class="studio-next-btn" data-next="rolemenu">Next: Role Menu →</button>
            </div>
        </div>

        {{-- ============================== STEP 5: ROLE MENU (matrix) ============================== --}}
        <div class="step-panel hidden" data-panel="rolemenu">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-fuchsia-50 text-fuchsia-600 dark:bg-fuchsia-900/30 dark:text-fuchsia-300">{!! $icon('link') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Role Menu</h2>
                            <p class="studio-card-desc">Pick a role, tick the menus it should see in the sidebar, save.</p>
                        </div>
                    </div>
                </div>
                <div class="rm-toolbar">
                    <div class="min-w-[260px] flex-1">
                        <label class="studio-label">Role</label>
                        <select id="rm_roleSelect" class="studio-select2 w-full">
                            <option value="">-- Select a Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="rm_actions" class="hidden flex flex-1 flex-wrap items-end justify-end gap-2" style="min-width:320px">
                        <div class="acs-search" style="margin:0;min-width:200px;flex:1;max-width:320px"><i class="fas fa-magnifying-glass"></i><input id="rm_search" type="text" placeholder="Search menu or ID…"></div>
                        <div class="rm-seg" id="rm_filter">
                            <button type="button" class="active" data-mode="all">All</button>
                            <button type="button" data-mode="granted">Granted</button>
                            <button type="button" data-mode="none">Not granted</button>
                        </div>
                        <div class="rm-copy" title="Copy another role's menus into this one (you can review before saving)">
                            <i class="fas fa-copy"></i>
                            <select id="rm_copyFrom">
                                <option value="">Copy from role…</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div id="rm_empty" class="studio-empty"><div class="acs-empty-ico"><i class="fas fa-user-shield"></i></div>Pick a role above to manage which menus it sees.</div>
                <div id="rm_summary" class="rm-summary hidden">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <span class="rm-count"><b id="rm_granted">0</b><small> / <span id="rm_total">0</span> menus granted</small></span>
                            <span id="rm_dirtyChip" class="rm-dirty hidden"></span>
                        </div>
                        <div class="rm-progress"><i id="rm_bar"></i></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="rm_foldAll" class="studio-mini-btn" title="Expand / collapse all groups"><i class="fas fa-angles-down"></i></button>
                        <button type="button" id="rm_selectAll" class="studio-mini-btn">Select visible</button>
                        <button type="button" id="rm_selectNone" class="studio-mini-btn">Clear visible</button>
                        <button type="button" id="rm_reset" class="studio-mini-btn hidden"><i class="fas fa-rotate-left"></i> Undo</button>
                        <button type="button" id="rm_save" class="studio-add-btn">{!! $icon('check', 'h-4 w-4') !!} Save Changes <kbd class="rm-kbd">Ctrl S</kbd></button>
                    </div>
                </div>
                <p id="rm_hint" class="hidden text-xs text-gray-400 dark:text-gray-500">Grouped by top-level menu. Green = about to be granted, red = about to be removed. Nothing is saved until you press Save.</p>
                <div id="rm_grid" class="rm-grid hidden"></div>
            </div>
            <div class="mt-3 flex justify-between">
                <button type="button" class="studio-back-btn" data-back="role">← Back</button>
                <button type="button" class="studio-next-btn" data-next="accessrights">Next: Access Rights →</button>
            </div>
        </div>

        {{-- ============================== STEP 6: ACCESS RIGHTS (matrix) ============================== --}}
        <div class="step-panel hidden" data-panel="accessrights">
            <div class="studio-card">
                <div class="studio-card-header">
                    <div class="flex items-center gap-3">
                        <span class="studio-icon-badge bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-300">{!! $icon('shield') !!}</span>
                        <div>
                            <h2 class="studio-card-title">Access Rights</h2>
                            <p class="studio-card-desc">Pick a role, tick VIEW/CREATE/EDIT/DELETE per screen, save.</p>
                        </div>
                    </div>
                </div>
                <div class="rm-toolbar">
                    <div class="min-w-[260px] flex-1">
                        <label class="studio-label">Role</label>
                        <select id="ar_roleSelect" class="studio-select2 w-full">
                            <option value="">-- Select a Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="ar_actions" class="hidden flex flex-1 flex-wrap items-end justify-end gap-2" style="min-width:320px">
                        <div class="acs-search" style="margin:0;min-width:200px;flex:1;max-width:320px"><i class="fas fa-magnifying-glass"></i><input id="ar_search" type="text" placeholder="Search screen or ID…"></div>
                        <div class="rm-copy" title="Copy another role's rights into this one (you can review before saving)">
                            <i class="fas fa-copy" style="color:#f43f5e"></i>
                            <select id="ar_copyFrom">
                                <option value="">Copy from role…</option>
                                @foreach ($roles as $r)
                                    <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div id="ar_empty" class="studio-empty"><div class="acs-empty-ico"><i class="fas fa-shield-halved"></i></div>Pick a role above to manage what it is allowed to do on each screen.</div>
                <div id="ar_summary" class="rm-summary ar-summary hidden">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                            <span class="rm-count"><b id="ar_screens">0</b><small> / <span id="ar_totalScreens">0</span> screens with access</small></span>
                            <span id="ar_dirtyChip" class="rm-dirty hidden"></span>
                        </div>
                        <div id="ar_chips" class="mt-2 flex flex-wrap gap-1.5"></div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <button type="button" id="ar_presetView" class="studio-mini-btn" title="On the screens currently shown: allow VIEW only"><i class="fas fa-eye"></i> Read-only</button>
                        <button type="button" id="ar_presetFull" class="studio-mini-btn" title="On the screens currently shown: allow everything"><i class="fas fa-key"></i> Full access</button>
                        <button type="button" id="ar_selectNone" class="studio-mini-btn" title="On the screens currently shown: clear everything">Clear</button>
                        <button type="button" id="ar_reset" class="studio-mini-btn hidden"><i class="fas fa-rotate-left"></i> Undo</button>
                        <button type="button" id="ar_save" class="studio-add-btn">{!! $icon('check', 'h-4 w-4') !!} Save Changes <kbd class="rm-kbd">Ctrl S</kbd></button>
                    </div>
                </div>
                <p id="ar_hint" class="hidden text-xs text-gray-400 dark:text-gray-500">Grouped by Application. Click a column header (e.g. VIEW) to toggle it for every screen shown, or the box next to a screen name to toggle the whole row. Green ring = about to be granted, red = about to be removed. Nothing is saved until you press Save.</p>
                <div id="ar_tableWrap" class="ar-wrap hidden">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="studio-thead">
                                <th class="px-3 py-2.5 text-left">Screen</th>
                                @foreach ($accessNames as $an)
                                    <th class="ar-col-header cursor-pointer select-none px-3 py-2.5 text-center"
                                        data-name="{{ $an }}" title="Click to toggle {{ $an }} for every screen shown"><span class="ar-col-pill">{{ $an }} <small class="ar-col-n"></small></span></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody id="ar_tableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="mt-3 flex flex-wrap justify-between gap-3">
                <button type="button" class="studio-back-btn" data-back="rolemenu">← Back</button>
                <div class="flex items-center gap-2 rounded-lg bg-green-50 px-4 py-2 text-sm font-semibold text-green-700 dark:bg-green-900/20 dark:text-green-300">
                    {!! $icon('check', 'h-4 w-4') !!} That's the full chain — the feature is wired end-to-end.
                </div>
            </div>
        </div>
    </div>

    {{-- ============================== SHARED MODALS ============================== --}}

    {{-- Application modal --}}
    <div id="app_modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="studio-modal max-w-md">
            <div class="studio-modal-header">
                <span class="studio-icon-badge bg-indigo-50 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-300">{!! $icon('grid') !!}</span>
                <h2 id="app_modalTitle" class="studio-card-title">Add Application</h2>
            </div>
            <form id="app_form" class="studio-modal-body">
                @csrf
                <input type="hidden" id="app_id">
                <div class="mb-3">
                    <label class="studio-label">Application ID</label>
                    <input type="text" id="app_application_id" name="application_id" class="studio-input" required>
                </div>
                <div class="mb-1">
                    <label class="studio-label">Application Name</label>
                    <input type="text" id="app_application_name" name="application_name" class="studio-input" required>
                </div>
                <div class="mt-5 flex justify-end space-x-2">
                    <button type="button" id="app_closeBtn" class="studio-cancel-btn">Cancel</button>
                    <button type="submit" class="studio-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Screen modal --}}
    <div id="scr_modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="studio-modal max-w-md">
            <div class="studio-modal-header">
                <span class="studio-icon-badge bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-300">{!! $icon('screen') !!}</span>
                <h2 id="scr_modalTitle" class="studio-card-title">Add Screen</h2>
            </div>
            <form id="scr_form" class="studio-modal-body">
                @csrf
                <input type="hidden" id="scr_id">
                <div class="mb-3">
                    <label class="studio-label">Screen ID</label>
                    <input type="text" id="scr_screen_id" name="screen_id" class="studio-input" required>
                </div>
                <div class="mb-3">
                    <label class="studio-label">Screen Name</label>
                    <input type="text" id="scr_screen_name" name="screen_name" class="studio-input" required>
                </div>
                <div class="mb-1">
                    <label class="studio-label">Application</label>
                    <select id="scr_application_id" name="application_id" class="studio-input studio-select2" required>
                        <option value="">-- Select Application --</option>
                        @foreach ($applications as $app)
                            <option value="{{ $app->application_id }}">{{ $app->application_id }} - {{ $app->application_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-5 flex justify-end space-x-2">
                    <button type="button" id="scr_closeBtn" class="studio-cancel-btn">Cancel</button>
                    <button type="submit" class="studio-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Menu modal --}}
    <div id="mnu_modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="studio-modal max-w-lg">
            <div class="studio-modal-header">
                <span class="studio-icon-badge bg-emerald-50 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-300">{!! $icon('menu') !!}</span>
                <h2 id="mnu_modalTitle" class="studio-card-title">Add Menu</h2>
            </div>
            <form id="mnu_form" class="studio-modal-body">
                @csrf
                <input type="hidden" id="mnu_id">
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="studio-label">Menu ID</label>
                        <input type="text" id="mnu_menu_id" name="menu_id" class="studio-input" required>
                    </div>
                    <div>
                        <label class="studio-label">Menu Name</label>
                        <input type="text" id="mnu_menu_name" name="menu_name" class="studio-input" required>
                    </div>
                </div>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="studio-label">Application</label>
                        <select id="mnu_application_id" name="application_id" class="studio-input studio-select2">
                            <option value="">-- Select Application --</option>
                            @foreach ($applications as $app)
                                <option value="{{ $app->application_id }}">{{ $app->application_id }} - {{ $app->application_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="studio-label">Screen (optional)</label>
                        <select id="mnu_screen_id" name="screen_id" class="studio-input studio-select2">
                            <option value="">-- Select Screen --</option>
                            @foreach ($screens as $s)
                                <option value="{{ $s->screen_id }}" data-app="{{ $s->application_id }}">{{ $s->screen_id }} - {{ $s->screen_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="studio-label">Parent Menu (optional)</label>
                    <select id="mnu_parent_menu_id" name="parent_menu_id" class="studio-input studio-select2">
                        <option value="">None (top-level)</option>
                        @foreach ($menus as $m)
                            <option value="{{ $m->menu_id }}">{{ $m->menu_name }} ({{ $m->menu_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="studio-label">Route Name</label>
                        <input type="text" id="mnu_menu_route" name="menu_route" class="studio-input" list="mnu_route_list" autocomplete="off" placeholder="Start typing a route name…">
                        <datalist id="mnu_route_list">
                            @foreach ($routeNames as $rn)
                                <option value="{{ $rn }}"></option>
                            @endforeach
                        </datalist>
                        <p id="mnu_route_hint" class="mt-1 hidden text-[11px] font-medium"></p>
                    </div>
                    <div>
                        <label class="studio-label">URL</label>
                        <input type="text" id="mnu_menu_url" name="menu_url" class="studio-input">
                    </div>
                </div>
                <div class="mb-1 grid grid-cols-2 gap-3">
                    <div class="relative" id="mnu_icon_picker">
                        <label class="studio-label">Icon (optional)</label>
                        <input type="hidden" id="mnu_menu_icon" name="menu_icon">
                        <div id="mnu_icon_toggle" role="button" tabindex="0" class="studio-input flex cursor-pointer items-center gap-2 text-left">
                            <span id="mnu_icon_preview" class="flex h-6 w-6 items-center justify-center text-indigo-500"></span>
                            <span id="mnu_icon_label" class="flex-1 truncate text-gray-400">Choose icon</span>
                            <button type="button" id="mnu_icon_clear" class="hidden text-base leading-none text-gray-400 hover:text-red-500" title="Clear">&times;</button>
                        </div>
                        <div id="mnu_icon_panel" class="absolute bottom-full left-0 z-50 mb-1 hidden w-80 rounded-xl border border-gray-200 bg-white p-3 shadow-xl dark:border-gray-600 dark:bg-gray-800">
                            <input type="text" id="mnu_icon_search" placeholder="Search icon..." class="studio-input mb-2">
                            <div id="mnu_icon_grid" class="grid max-h-56 grid-cols-6 gap-1 overflow-y-auto"></div>
                        </div>
                    </div>
                    <div>
                        <label class="studio-label">Sort Order</label>
                        <input type="number" id="mnu_menu_sort_order" name="menu_sort_order" class="studio-input" value="0">
                    </div>
                </div>
                <div class="mt-5 flex justify-end space-x-2">
                    <button type="button" id="mnu_closeBtn" class="studio-cancel-btn">Cancel</button>
                    <button type="submit" class="studio-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Role modal --}}
    <div id="rol_modal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50 p-4">
        <div class="studio-modal max-w-md">
            <div class="studio-modal-header">
                <span class="studio-icon-badge bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300">{!! $icon('role') !!}</span>
                <h2 id="rol_modalTitle" class="studio-card-title">Add Role</h2>
            </div>
            <form id="rol_form" class="studio-modal-body">
                @csrf
                <input type="hidden" id="rol_id">
                <div class="mb-3">
                    <label class="studio-label">Role ID</label>
                    <input type="text" id="rol_role_id" name="role_id" class="studio-input" required>
                </div>
                <div class="mb-1">
                    <label class="studio-label">Role Name</label>
                    <input type="text" id="rol_role_name" name="role_name" class="studio-input" required>
                </div>
                <div class="mt-5 flex justify-end space-x-2">
                    <button type="button" id="rol_closeBtn" class="studio-cancel-btn">Cancel</button>
                    <button type="submit" class="studio-save-btn">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Shared right-hand drawer: role overview / menu->roles / change history --}}
    <div id="acs_drawerMask" class="acs-mask"></div>
    <aside id="acs_drawer" class="acs-drawer" aria-hidden="true">
        <header class="acs-drawer-head">
            <div class="min-w-0">
                <div id="acs_drawerKicker" class="text-[10px] font-bold uppercase tracking-widest text-indigo-500"></div>
                <h2 id="acs_drawerTitle" class="truncate text-lg font-bold text-gray-800 dark:text-gray-100"></h2>
                <p id="acs_drawerSub" class="truncate text-xs text-gray-400"></p>
            </div>
            <button type="button" id="acs_drawerClose" class="acs-x" title="Close"><i class="fas fa-xmark"></i></button>
        </header>
        <div id="acs_drawerBody" class="acs-drawer-body"></div>
    </aside>

    <div id="loadingOverlay" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black/40">
        <div class="flex items-center gap-3 rounded-xl bg-white px-6 py-4 shadow-lg dark:bg-gray-800">
            <svg class="h-6 w-6 animate-spin text-indigo-600" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Processing...</span>
        </div>
    </div>

    <style>
        /* ---- layout building blocks ---- */
        .studio-card {
            display: flex; flex-direction: column; gap: 1rem; border-radius: 1rem;
            background: #fff; padding: 1.25rem; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(0,0,0,.03);
        }
        .dark .studio-card { background: #1f2937; border-color: #374151; }
        .studio-card-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .studio-card-title { font-size: 0.95rem; font-weight: 700; color: #1f2937; }
        .dark .studio-card-title { color: #f9fafb; }
        .studio-card-desc { font-size: 0.8rem; color: #6b7280; margin-top: 0.125rem; }
        .dark .studio-card-desc { color: #9ca3af; }
        .studio-icon-badge {
            display: flex; height: 2.5rem; width: 2.5rem; flex-shrink: 0; align-items: center; justify-content: center;
            border-radius: 0.75rem;
        }
        .studio-label { display: block; margin-bottom: 0.25rem; font-size: 0.75rem; font-weight: 600; color: #6b7280; }
        .dark .studio-label { color: #9ca3af; }
        .studio-empty {
            border-radius: 0.75rem; border: 1px dashed #e5e7eb; padding: 1.25rem; text-align: center;
            font-size: 0.8rem; color: #9ca3af; background: #fafafa;
        }
        .dark .studio-empty { border-color: #4b5563; background: rgba(255,255,255,.02); color: #9ca3af; }

        /* ---- buttons ---- */
        .studio-add-btn {
            display: inline-flex; align-items: center; gap: 0.375rem; white-space: nowrap; border-radius: 0.6rem;
            background-image: linear-gradient(135deg, #3193da, #1c71ae); padding: 0.55rem 1.25rem; font-size: 0.85rem;
            font-weight: 600; color: #fff; box-shadow: 0 1px 3px rgba(49, 147, 218,.35); transition: filter .15s, transform .1s;
        }
        .studio-add-btn:hover { filter: brightness(1.08); }
        .studio-add-btn:active { transform: scale(.98); }
        .studio-mini-btn {
            border-radius: 0.5rem; border: 1px solid #d1d5db; padding: 0.375rem 0.75rem; font-size: 0.8rem; color: #374151; background: #fff;
        }
        .studio-mini-btn:hover { background-color: #f3f4f6; }
        .dark .studio-mini-btn { border-color: #4b5563; color: #e5e7eb; background: transparent; }
        .dark .studio-mini-btn:hover { background-color: #374151; }
        .studio-next-btn, .studio-back-btn {
            border-radius: 0.6rem; padding: 0.55rem 1.25rem; font-size: 0.85rem; font-weight: 600;
            border: 1px solid #d1d5db; color: #374151; transition: background-color .15s;
        }
        .studio-next-btn { background-color: #e3f3ff; color: #1c71ae; border-color: #b6e1ff; }
        .studio-next-btn:hover { background-color: #d1ecff; }
        .dark .studio-back-btn { border-color: #4b5563; color: #e5e7eb; }
        .dark .studio-next-btn { background-color: rgba(49, 147, 218,0.15); color: #a0d7ff; border-color: rgba(86, 177, 243,0.4); }
        .studio-save-btn { border-radius: 0.6rem; background: #3193da; padding: 0.55rem 1.35rem; font-size: 0.85rem; font-weight: 600; color: #fff; }
        .studio-save-btn:hover { background: #1c71ae; }
        .studio-cancel-btn { border-radius: 0.6rem; border: 1px solid #d1d5db; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; color: #374151; }
        .studio-cancel-btn:hover { background: #f3f4f6; }
        .dark .studio-cancel-btn { border-color: #4b5563; color: #e5e7eb; }
        .dark .studio-cancel-btn:hover { background: #374151; }

        /* ---- inputs ---- */
        .studio-input { width: 100%; border-radius: 0.6rem; border: 1px solid #d1d5db; padding: 0.55rem 0.75rem; font-size: 0.875rem; }
        .studio-input:focus { outline: none; border-color: #56b1f3; box-shadow: 0 0 0 3px rgba(86, 177, 243,.15); }
        .dark .studio-input { background-color: #374151; border-color: #4b5563; color: #f3f4f6; }

        /* ---- modal ---- */
        .studio-modal { width: 100%; border-radius: 1rem; background: #fff; box-shadow: 0 20px 40px rgba(0,0,0,.2); overflow: hidden; }
        .dark .studio-modal { background: #1f2937; }
        .studio-modal-header { display: flex; align-items: center; gap: 0.75rem; padding: 1.1rem 1.25rem; border-bottom: 1px solid #f1f5f9; }
        .dark .studio-modal-header { border-bottom-color: #374151; }
        .studio-modal-body { padding: 1.25rem; }

        /* ---- table header ---- */
        .studio-thead th, tr.studio-thead th {
            background-color: #f8fafc; color: #475569; font-weight: 700; font-size: 0.7rem;
            text-transform: uppercase; letter-spacing: .04em; border-bottom: 1px solid #e5e7eb;
            position: sticky; top: 0; z-index: 10;
        }
        .dark .studio-thead th, .dark tr.studio-thead th {
            background-color: #111827; color: #cbd5e1; border-bottom-color: #374151;
        }

        /* ---- matrix group headers (Role Menu / Access Rights) ---- */
        .ar-group-header td {
            background: linear-gradient(90deg, rgba(49, 147, 218,.09), rgba(49, 147, 218,0) 55%), #eef0f4;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.5rem; padding-bottom: 0.5rem;
        }
        .ar-group-header:first-child td { border-top: none; }
        .dark .ar-group-header td {
            background: linear-gradient(90deg, rgba(86, 177, 243,.16), rgba(86, 177, 243,0) 55%), #161c28;
            border-top-color: #374151;
        }
        .ar-group-icon {
            display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;
            width: 1.25rem; height: 1.25rem; border-radius: 0.35rem; background: #b6e1ff; color: #1c71ae;
        }
        .dark .ar-group-icon { background: rgba(86, 177, 243,.25); color: #b6e1ff; }
        .ar-group-label { font-size: 0.72rem; font-weight: 800; letter-spacing: .04em; color: #124d79; text-transform: uppercase; }
        .dark .ar-group-label { color: #a0d7ff; }
        .ar-group-pill {
            border-radius: 9999px; padding: 0.15rem 0.6rem; font-size: 0.65rem; font-weight: 700;
            letter-spacing: normal; text-transform: none; white-space: nowrap;
        }
        .ar-group-pill-all { color: #1c71ae; background: #d1ecff; }
        .ar-group-pill-all:hover { background: #b6e1ff; }
        .ar-group-pill-none { color: #6b7280; background: #eef0f4; }
        .ar-group-pill-none:hover { background: #e2e8f0; }
        .dark .ar-group-pill-all { color: #a0d7ff; background: rgba(86, 177, 243,.18); }
        .dark .ar-group-pill-all:hover { background: rgba(86, 177, 243,.3); }
        .dark .ar-group-pill-none { color: #d1d5db; background: rgba(255,255,255,.06); }
        .dark .ar-group-pill-none:hover { background: rgba(255,255,255,.12); }

        /* ---- matrix folder sub-headers (organizational menu nodes, not real
           screens — not checkable, just group screens visually by menu tree) ---- */
        .ar-folder-header td { background-color: #fbfbfc; border-top: 1px solid #f1f5f9; }
        .dark .ar-folder-header td { background-color: rgba(255,255,255,.01); border-top-color: #262f3d; }
        .ar-folder-label {
            position: relative; display: inline-flex; align-items: center; padding-left: 1rem;
            font-size: 0.7rem; font-weight: 700; letter-spacing: .02em; color: #94a3b8;
        }
        .dark .ar-folder-label { color: #64748b; }
        .ar-folder-label::before {
            content: ''; position: absolute; left: 0.15rem; top: -0.05rem; width: 0.5rem; height: 0.65rem;
            border-left: 1.5px solid #d8dee7; border-bottom: 1.5px solid #d8dee7; border-bottom-left-radius: 4px;
        }
        .dark .ar-folder-label::before { border-color: #374151; }

        /* ---- matrix orphan sub-headers (screens with no menu leaf pointing
           at them yet — same idea as a folder header, but flagged amber so
           it reads as "needs setup" rather than plain organization) ---- */
        .ar-orphan-header td { background-color: #fffbeb; border-top: 1px solid #fde68a; }
        .dark .ar-orphan-header td { background-color: rgba(217,119,6,.08); border-top-color: rgba(217,119,6,.25); }
        .ar-orphan-label { font-size: 0.7rem; font-weight: 700; letter-spacing: .02em; color: #b45309; }
        .dark .ar-orphan-label { color: #fbbf24; }

        /* ---- matrix child rows (screens under an application group) ---- */
        .ar-screen-name { position: relative; display: inline-flex; align-items: center; padding-left: 1rem; }
        .ar-screen-name::before {
            content: ''; position: absolute; left: 0.15rem; top: -0.15rem; width: 0.55rem; height: 0.8rem;
            border-left: 1.5px solid #cbd5e1; border-bottom: 1.5px solid #cbd5e1; border-bottom-left-radius: 4px;
        }
        .dark .ar-screen-name::before { border-color: #4b5563; }
        .ar-row-alt td { background-color: #fafbfc; }
        .dark .ar-row-alt td { background-color: rgba(255,255,255,.015); }
        .ar-row:hover td { background-color: #e3f3ff; }
        .dark .ar-row:hover td { background-color: rgba(86, 177, 243,.08); }

        /* ---- stepper ---- */
        .step-circle svg { width: 1.15rem; height: 1.15rem; }
        .step-circle-check { display: none; }
        .step-node.is-active .step-circle {
            border-color: #3193da; background-color: #3193da; color: #fff; box-shadow: 0 0 0 4px rgba(49, 147, 218,.15);
        }
        .step-node.is-active .step-label { color: #1c71ae; }
        .dark .step-node.is-active .step-label { color: #a0d7ff; }
        .step-node.is-done .step-circle { border-color: #16a34a; background-color: #16a34a; color: #fff; }
        .step-node.is-done .step-circle-icon { display: none; }
        .step-node.is-done .step-circle-check { display: block; }
        .step-connector.is-done { background-color: #16a34a; }
        .step-connector { transition: background-color .3s; }
        .step-circle { transition: all .2s; }

        /* ---- step panel transition ---- */
        .step-panel { animation: studioFadeIn .18s ease-out; }
        @keyframes studioFadeIn { from { opacity: 0; transform: translateY(4px); } to { opacity: 1; transform: translateY(0); } }

        /* ---- setup gaps listing ---- */
        .gaps-list {
            max-height: 14rem; overflow-y: auto; font-size: 0.8125rem;
            border: 1px solid #f3f4f6; border-radius: 0.6rem;
        }
        .dark .gaps-list { border-color: #374151; }
        .gaps-list li {
            padding: 0.4rem 0.6rem; border-bottom: 1px solid #f3f4f6;
            display: flex; align-items: center; gap: 0.4rem; line-height: 1.3;
        }
        .dark .gaps-list li { border-color: #374151; }
        .gaps-list li:last-child { border-bottom: none; }
        .gaps-list li:hover { background-color: #f9fafb; }
        .dark .gaps-list li:hover { background-color: rgba(255,255,255,0.03); }
        .gaps-list::-webkit-scrollbar { width: 6px; }
        .gaps-list::-webkit-scrollbar-thumb { background-color: #d1d5db; border-radius: 3px; }
        .dark .gaps-list::-webkit-scrollbar-thumb { background-color: #4b5563; }
        /* =====================  REDESIGN LAYER  ===================== */
        /* ---- hero ---- */
        .acs-hero { position: relative; overflow: hidden; margin-bottom: 1rem; border-radius: 1.5rem; padding: 1.6rem 1.75rem 1.4rem;
            color: #fff; background: linear-gradient(120deg, #124d79 0%, #3193da 45%, #3193da 100%);
            box-shadow: 0 18px 40px -18px rgba(49, 147, 218,.65); }
        .acs-hero-glow { position: absolute; inset: 0; pointer-events: none;
            background: radial-gradient(600px 220px at 85% -10%, rgba(255,255,255,.28), transparent 60%),
                        radial-gradient(420px 260px at 0% 110%, rgba(56,189,248,.28), transparent 60%); }
        .acs-eyebrow { display: inline-flex; align-items: center; gap: .45rem; border-radius: 999px; padding: .2rem .75rem; font-size: .68rem; font-weight: 700;
            letter-spacing: .12em; text-transform: uppercase; background: rgba(255,255,255,.16); backdrop-filter: blur(6px); }
        .acs-hero-title { margin-top: .6rem; font-size: 1.65rem; font-weight: 800; letter-spacing: -.02em; line-height: 1.15; }
        .acs-hero-sub { margin-top: .35rem; max-width: 46rem; font-size: .84rem; line-height: 1.5; color: rgba(255,255,255,.78); }
        .acs-ghost-btn { display: inline-flex; align-items: center; gap: .5rem; border-radius: .85rem; padding: .55rem 1rem; font-size: .78rem; font-weight: 600;
            background: rgba(255,255,255,.16); border: 1px solid rgba(255,255,255,.28); backdrop-filter: blur(8px); transition: .15s; }
        .acs-ghost-btn:hover { background: rgba(255,255,255,.28); transform: translateY(-1px); }
        .acs-tiles { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: .75rem; margin-top: 1.25rem; }
        @media (max-width: 900px) { .acs-tiles { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        .acs-tile { position: relative; display: grid; grid-template-columns: auto 1fr; grid-template-rows: auto auto; column-gap: .75rem; align-items: center; text-align: left;
            border-radius: 1rem; padding: .75rem .9rem; background: rgba(255,255,255,.13); border: 1px solid rgba(255,255,255,.2); backdrop-filter: blur(8px); transition: .18s; }
        .acs-tile:hover { background: rgba(255,255,255,.22); transform: translateY(-2px); box-shadow: 0 10px 24px -12px rgba(0,0,0,.5); }
        .acs-tile i { grid-row: 1 / 3; display: grid; place-items: center; height: 2.3rem; width: 2.3rem; border-radius: .8rem; font-size: .95rem; color: #fff;
            background: var(--t); box-shadow: 0 6px 14px -6px var(--t); }
        .acs-tile b { font-size: 1.35rem; font-weight: 800; line-height: 1; }
        .acs-tile span { font-size: .68rem; color: rgba(255,255,255,.75); }
        .acs-tile-gaps.is-clear { --t: #10b981; }
        .acs-tile-gaps:not(.is-clear) i { animation: acsPulse 2.2s ease-in-out infinite; }
        @keyframes acsPulse { 0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,.55); } 50% { box-shadow: 0 0 0 8px rgba(239,68,68,0); } }

        /* ---- stepper ---- */
        .step-circle { height: 2.75rem !important; width: 2.75rem !important; border-width: 2px; background: #fff; }
        .dark .step-circle { background: #1f2937; }
        .step-node.is-active .step-circle { background-image: linear-gradient(135deg, #56b1f3, #3193da); border-color: transparent;
            box-shadow: 0 0 0 5px rgba(86, 177, 243,.18), 0 10px 20px -8px rgba(86, 177, 243,.7); transform: scale(1.06); }
        .step-node.is-done .step-circle { background-image: linear-gradient(135deg, #22c55e, #16a34a); border-color: transparent; }
        .step-connector { height: 3px !important; border-radius: 999px; }
        .step-connector.is-done { background-image: linear-gradient(90deg, #22c55e, #16a34a); }

        /* ---- cards with per-step accent ---- */
        .step-panel[data-panel="application"] { --acc: #56b1f3; --acc2: #7bc8ff; }
        .step-panel[data-panel="screen"] { --acc: #0ea5e9; --acc2: #38bdf8; }
        .step-panel[data-panel="menu"] { --acc: #10b981; --acc2: #34d399; }
        .step-panel[data-panel="role"] { --acc: #f59e0b; --acc2: #fbbf24; }
        .step-panel[data-panel="rolemenu"] { --acc: #56b1f3; --acc2: #7bc8ff; }
        .step-panel[data-panel="accessrights"] { --acc: #f43f5e; --acc2: #fb7185; }
        .studio-card { position: relative; overflow: hidden; border-radius: 1.4rem; padding: 1.5rem; border: 1px solid #eef0f6;
            box-shadow: 0 1px 2px rgba(15,23,42,.04), 0 18px 40px -26px rgba(15,23,42,.25); }
        .studio-card::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: linear-gradient(90deg, var(--acc, #56b1f3), var(--acc2, #7bc8ff)); }
        .studio-card::after { content: ''; position: absolute; right: -70px; top: -70px; height: 220px; width: 220px; border-radius: 50%; pointer-events: none;
            background: radial-gradient(circle, var(--acc, #56b1f3), transparent 70%); opacity: .08; }
        .studio-card-title { font-size: 1.1rem; font-weight: 800; letter-spacing: -.01em; }
        .studio-icon-badge { height: 2.9rem; width: 2.9rem; border-radius: 1rem; box-shadow: inset 0 0 0 1px rgba(0,0,0,.04); }
        .studio-add-btn { background-image: linear-gradient(135deg, var(--acc, #3193da), var(--acc2, #56b1f3)); border-radius: .9rem; padding: .65rem 1.3rem;
            box-shadow: 0 10px 20px -10px var(--acc, #3193da); }
        .studio-add-btn:hover { transform: translateY(-1px); filter: brightness(1.06); }
        .studio-next-btn, .studio-back-btn { border-radius: .9rem; padding: .65rem 1.4rem; background: #fff; transition: .15s; }
        .studio-next-btn { background: linear-gradient(135deg, #e3f3ff, #e3f3ff); }
        .studio-next-btn:hover, .studio-back-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px -10px rgba(49, 147, 218,.5); }
        .dark .studio-back-btn { background: transparent; }
        .studio-card .studio-input, .studio-modal .studio-input { border-radius: .8rem; background: #f8fafc; border-color: #e5e7eb; }
        .dark .studio-card .studio-input, .dark .studio-modal .studio-input { background: #374151; }
        .studio-input:focus { border-color: var(--acc, #56b1f3); box-shadow: 0 0 0 4px rgba(86, 177, 243,.14); background: #fff; }

        /* ---- tables ---- */
        .studio-card .rounded-base { border: 1px solid #eef0f6; border-radius: 1rem; }
        .dark .studio-card .rounded-base { border-color: #374151; }
        .studio-thead th, tr.studio-thead th { background: #f6f7fb; font-size: .66rem; letter-spacing: .09em; padding-top: .8rem; padding-bottom: .8rem; }
        .studio-card table.dataTable tbody tr, .studio-card table tbody tr { transition: background-color .12s; }
        .studio-card table.dataTable tbody tr:hover td { background-color: rgba(86, 177, 243,.06); }
        .studio-card table td { border-top: 1px solid #f3f4f8; padding-top: .7rem; padding-bottom: .7rem; }
        .dark .studio-card table td { border-color: #2d3748; }
        .studio-card .studio-edit-btn, .studio-card .acs-role-view, .studio-card .acs-role-clone {
            height: 1.9rem; width: 1.9rem; padding: 0 !important; border-radius: .6rem !important; display: inline-grid; place-items: center; transition: .15s; }
        .studio-card .studio-edit-btn:hover, .studio-card .acs-role-view:hover, .studio-card .acs-role-clone:hover { transform: translateY(-1px) scale(1.06); box-shadow: 0 8px 14px -8px rgba(0,0,0,.45); }
        .studio-card .mnu-edit-btn, .studio-card .mnu-child-btn, .studio-card .acs-menu-roles { border-radius: .6rem !important; transition: .15s; }
        .studio-card .mnu-edit-btn:hover, .studio-card .mnu-child-btn:hover, .studio-card .acs-menu-roles:hover { transform: translateY(-1px) scale(1.06); }
        .mnu-tree-row { border-radius: .8rem !important; }

        /* ---- DataTables chrome ---- */
        .studio-card .dataTables_wrapper .dataTables_filter input, .studio-card .dataTables_wrapper .dataTables_length select {
            border: 1px solid #e5e7eb; border-radius: .75rem; padding: .4rem .8rem; background: #f8fafc; outline: none; }
        .studio-card .dataTables_wrapper .dataTables_filter input:focus { border-color: var(--acc, #56b1f3); box-shadow: 0 0 0 4px rgba(86, 177, 243,.14); background: #fff; }
        .studio-card .dataTables_wrapper .dataTables_paginate .paginate_button { border-radius: .7rem !important; border: 0 !important; margin: 0 2px; }
        .studio-card .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .studio-card .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: linear-gradient(135deg, var(--acc, #56b1f3), var(--acc2, #7bc8ff)) !important; color: #fff !important; box-shadow: 0 8px 16px -8px var(--acc, #56b1f3); }
        .studio-card .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) { background: rgba(86, 177, 243,.1) !important; color: #1c71ae !important; }

        /* ---- gaps panel + modals ---- */
        #gaps_panel { border-radius: 1.2rem; overflow: hidden; border-color: #eef0f6; box-shadow: 0 14px 34px -26px rgba(15,23,42,.35); scroll-margin-top: 1rem; }
        .studio-modal { border-radius: 1.4rem; box-shadow: 0 30px 70px -20px rgba(15,23,42,.55); border: 1px solid rgba(255,255,255,.4); }
        .studio-modal-header { background: linear-gradient(135deg, rgba(86, 177, 243,.09), transparent 65%); }
        #rol_modal, #app_modal, #scr_modal, #mnu_modal { backdrop-filter: blur(3px); }
        .studio-save-btn { border-radius: .85rem; background-image: linear-gradient(135deg, #3193da, #3193da); box-shadow: 0 10px 20px -10px #3193da; }
        .studio-cancel-btn { border-radius: .85rem; }
        .count-pill { box-shadow: inset 0 0 0 1px rgba(0,0,0,.04); }

        /* ---- compact header (overrides the roomier redesign values above) ---- */
        .acs-hero { margin-bottom: .75rem; border-radius: 1.1rem; padding: .65rem 1rem; box-shadow: 0 12px 26px -18px rgba(49, 147, 218,.65); }
        .acs-eyebrow { display: none; }
        .acs-hero-title { margin: 0; font-size: 1.05rem; font-weight: 800; white-space: nowrap; }
        .acs-hero-title::before { content: '\f3ed'; font-family: 'Font Awesome 6 Free', 'Font Awesome 5 Free'; font-weight: 900; margin-right: .5rem; opacity: .85; }
        .acs-tiles { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: 0; }
        .acs-tile { display: inline-flex; align-items: center; gap: .45rem; border-radius: .75rem; padding: .3rem .65rem .3rem .35rem; }
        .acs-tile:hover { transform: translateY(-1px); }
        .acs-tile i { height: 1.55rem; width: 1.55rem; border-radius: .5rem; font-size: .7rem; box-shadow: 0 4px 10px -5px var(--t); }
        .acs-tile b { font-size: .95rem; }
        .acs-tile span { font-size: .68rem; }
        .acs-ghost-btn { padding: .4rem .8rem; border-radius: .7rem; font-size: .74rem; }
        .step-circle { height: 2.1rem !important; width: 2.1rem !important; }
        .step-circle svg { width: .95rem; height: .95rem; }
        .step-node { gap: .25rem !important; }
        .step-node .step-label { font-size: 11px; line-height: 1.1; }
        .step-node .step-label > span { display: none; }
        .step-node .step-label br { display: none; }
        .step-connector { margin-bottom: 1.15rem !important; height: 2.5px !important; }
        .step-node.is-active .step-circle { transform: none; box-shadow: 0 0 0 4px rgba(86, 177, 243,.18), 0 6px 14px -6px rgba(86, 177, 243,.7); }

        /* ---- Role Menu step ---- */
        .rm-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .75rem; }
        .rm-seg { display: inline-flex; padding: 3px; border-radius: .8rem; background: #f1f3f9; }
        .dark .rm-seg { background: rgba(255,255,255,.07); }
        .rm-seg button { border-radius: .6rem; padding: .38rem .8rem; font-size: .75rem; font-weight: 600; color: #6b7280; transition: .15s; white-space: nowrap; }
        .rm-seg button.active { background: #fff; color: #3193da; box-shadow: 0 2px 6px rgba(0,0,0,.1); }
        .dark .rm-seg button.active { background: #374151; color: #a0d7ff; }
        .rm-copy { position: relative; }
        .rm-copy i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #56b1f3; pointer-events: none; }
        .rm-copy select { appearance: none; border: 1px solid #e5e7eb; background: #f8fafc; border-radius: .8rem; padding: .5rem 1rem .5rem 2.1rem; font-size: .8rem; cursor: pointer; max-width: 190px; }
        .rm-copy select:hover { border-color: #a0d7ff; }
        .dark .rm-copy select { background: #374151; border-color: #4b5563; color: #f3f4f6; }

        .rm-summary { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 1rem; padding: .9rem 1.1rem;
            background: linear-gradient(135deg, rgba(86, 177, 243,.10), rgba(86, 177, 243,.05)); border: 1px solid rgba(86, 177, 243,.18); }
        .rm-count b { font-size: 1.6rem; font-weight: 800; color: #1c71ae; line-height: 1; }
        .dark .rm-count b { color: #a0d7ff; }
        .rm-count small { font-size: .8rem; color: #6b7280; }
        .rm-progress { margin-top: .5rem; height: 6px; border-radius: 999px; background: rgba(86, 177, 243,.15); overflow: hidden; max-width: 28rem; }
        .rm-progress i { display: block; height: 100%; border-radius: 999px; background: linear-gradient(90deg, #56b1f3, #56b1f3); transition: width .35s cubic-bezier(.2,.8,.2,1); }
        .rm-dirty { border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 700; background: #fef3c7; color: #92400e; }
        .rm-d-add { color: #15803d; } .rm-d-del { color: #b91c1c; }
        .rm-kbd { margin-left: 6px; border-radius: 6px; padding: 1px 6px; font-size: 9.5px; font-family: inherit; background: rgba(255,255,255,.22); }
        .rm-pulse { animation: rmPulse 1.8s ease-in-out infinite; }
        @keyframes rmPulse { 0%,100% { box-shadow: 0 10px 20px -10px var(--acc); } 50% { box-shadow: 0 0 0 6px rgba(86, 177, 243,.22), 0 10px 20px -10px var(--acc); } }

        .rm-grid { display: grid; grid-template-columns: 1fr; gap: .9rem; max-height: 68vh; overflow-y: auto; padding: .2rem .25rem .5rem; align-items: start; }
        @media (min-width: 1024px) { .rm-grid { grid-template-columns: 1fr 1fr; } }
        .rm-group { border-radius: 1.1rem; border: 1px solid #eceef6; background: #fff; overflow: hidden; transition: box-shadow .2s, border-color .2s; }
        .rm-group:hover { box-shadow: 0 14px 30px -22px rgba(86, 177, 243,.6); border-color: #b6e1ff; }
        .dark .rm-group { background: rgba(255,255,255,.025); border-color: #374151; }
        .rm-group-head { display: flex; align-items: center; gap: .6rem; padding: .65rem .85rem; background: linear-gradient(90deg, rgba(86, 177, 243,.10), rgba(86, 177, 243,0) 70%); border-bottom: 1px solid #f1f2f8; }
        .dark .rm-group-head { border-color: #374151; }
        .rm-group-title { font-size: .82rem; font-weight: 800; letter-spacing: .01em; color: #124d79; flex: none; }
        .dark .rm-group-title { color: #b6e1ff; }
        .rm-group-pill { flex: none; border-radius: 999px; padding: 1px 9px; font-size: 10.5px; font-weight: 700; background: #d1ecff; color: #1c71ae; }
        .rm-pill-full { background: #dcfce7; color: #15803d; }
        .rm-group-bar { flex: 1; height: 4px; border-radius: 999px; background: rgba(86, 177, 243,.15); overflow: hidden; }
        .rm-group-bar i { display: block; height: 100%; background: linear-gradient(90deg, #56b1f3, #22c55e); transition: width .3s; }
        .rm-group-fold, .rm-fold { flex: none; height: 22px; width: 22px; border-radius: 7px; color: #9ca3af; font-size: 10px; transition: .15s; margin-left: auto; }
        .rm-group-fold { margin-left: 0; order: -1; }
        .rm-group-fold:hover, .rm-fold:hover { background: rgba(86, 177, 243,.12); color: #3193da; }
        .rm-group-fold i, .rm-fold i { transition: transform .2s; }
        .rm-folded > .rm-group-head .rm-group-fold i, .rm-node.rm-folded > .rm-row .rm-fold i { transform: rotate(-90deg); }
        .rm-group.rm-folded .rm-group-body { display: none; }
        .rm-group.rm-folded .rm-group-head { border-bottom-color: transparent; }
        .rm-node.rm-folded > .rm-kids { display: none; }
        .rm-group-body { padding: .45rem; }
        .rm-kids { margin-left: 1.15rem; padding-left: .5rem; border-left: 1.5px dashed #e3e5f0; }
        .dark .rm-kids { border-color: #374151; }

        .rm-row { display: flex; align-items: center; gap: .55rem; padding: .38rem .55rem; border-radius: .7rem; cursor: pointer; transition: background-color .12s, box-shadow .12s; font-size: .83rem; }
        .rm-row:hover { background: rgba(86, 177, 243,.07); }
        .rm-ico { width: 14px; font-size: 12px; color: #a0d7ff; text-align: center; }
        .rm-on .rm-ico { color: #56b1f3; }
        .rm-name { color: #6b7280; transition: color .12s; }
        .rm-name-parent { font-weight: 700; }
        .rm-on .rm-name { color: #1f2937; }
        .dark .rm-name { color: #9ca3af; } .dark .rm-on .rm-name { color: #f3f4f6; }
        .rm-state { margin-left: auto; font-size: 10px; font-weight: 700; }
        .rm-fold + .rm-state, .rm-state + .rm-fold { margin-left: 4px; }
        .rm-added { background: rgba(16,185,129,.10); box-shadow: inset 3px 0 0 #10b981; }
        .rm-added .rm-state { color: #059669; }
        .rm-removed { background: rgba(239,68,68,.08); box-shadow: inset 3px 0 0 #ef4444; }
        .rm-removed .rm-state { color: #dc2626; }

        /* custom checkbox with tri-state */
        .rm-cb { appearance: none; -webkit-appearance: none; flex: none; position: relative; height: 18px; width: 18px; border-radius: 6px; border: 1.6px solid #cbd5e1; background: #fff; cursor: pointer; transition: .15s; }
        .rm-cb:hover { border-color: #7bc8ff; }
        .rm-cb:checked, .rm-cb:indeterminate { background: linear-gradient(135deg, #56b1f3, #56b1f3); border-color: transparent; box-shadow: 0 4px 10px -4px #3193da; }
        .rm-cb:checked::after { content: ''; position: absolute; inset: 4px 3px 3.5px 3px; background: #fff; clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%); animation: rmTick .18s ease-out; }
        .rm-cb:indeterminate::after { content: ''; position: absolute; left: 3.5px; right: 3.5px; top: 50%; height: 2px; margin-top: -1px; background: #fff; border-radius: 2px; }
        .dark .rm-cb { background: #374151; border-color: #6b7280; }
        @keyframes rmTick { from { transform: scale(.3); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* ---- Access Rights step ---- */
        .ar-wrap { max-height: 68vh; overflow: auto; border-radius: 1rem; border: 1px solid #eceef6; background: #fff; }
        .dark .ar-wrap { background: rgba(255,255,255,.02); border-color: #374151; }
        .ar-summary { background: linear-gradient(135deg, rgba(244,63,94,.09), rgba(251,113,133,.04)); border-color: rgba(244,63,94,.18); }
        .ar-summary .rm-count b { color: #e11d48; }
        .dark .ar-summary .rm-count b { color: #fda4af; }
        .ar-col-pill { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; padding: 3px 11px; font-size: 11px; font-weight: 800; letter-spacing: .05em; background: #fff; border: 1px solid #e5e7eb; transition: .15s; }
        .ar-col-header:hover .ar-col-pill { background: #fff1f2; border-color: #fda4af; color: #e11d48; transform: translateY(-1px); }
        .dark .ar-col-pill { background: #1f2937; border-color: #4b5563; }
        .ar-col-n { font-size: 10px; font-weight: 700; color: #e11d48; }
        .ar-cb:checked, .ar-cb:indeterminate { background: linear-gradient(135deg, #f43f5e, #fb7185); box-shadow: 0 4px 10px -4px #e11d48; }
        .ar-cb:hover { border-color: #fb7185; }
        .ar-chk.ar-added { box-shadow: 0 0 0 3px rgba(16,185,129,.4); }
        .ar-chk.ar-removed { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.28); }
        .ar-row .ar-screen-name { display: inline-flex; align-items: center; gap: .5rem; }
        .ar-row-cb { height: 16px !important; width: 16px !important; border-radius: 5px !important; }
        .ar-row td { transition: background-color .12s; }
        .ar-row-on td:first-child { box-shadow: inset 3px 0 0 #fb7185; }
        .ar-fold-hide { display: none !important; }
        .ar-group-fold { height: 22px; width: 22px; border-radius: 7px; color: #6b7280; font-size: 10px; transition: .15s; }
        .ar-group-fold:hover { background: rgba(49, 147, 218,.12); color: #1c71ae; }
        .ar-group-fold i { transition: transform .2s; }
        .ar-folded .ar-group-fold i { transform: rotate(-90deg); }
        .ar-group-header td { padding-top: .6rem; padding-bottom: .6rem; }

        /* ---- drawer (role overview / menu roles / history) ---- */
        .acs-mask { position: fixed; inset: 0; z-index: 60; background: rgba(15,23,42,.45); opacity: 0; pointer-events: none; transition: opacity .2s; backdrop-filter: blur(2px); }
        .acs-mask.open { opacity: 1; pointer-events: auto; }
        .acs-drawer { position: fixed; top: 0; right: 0; bottom: 0; z-index: 61; width: min(560px, 100vw); display: flex; flex-direction: column; background: #fff; box-shadow: -20px 0 50px rgba(15,23,42,.18); transform: translateX(105%); transition: transform .25s cubic-bezier(.2,.8,.2,1); }
        .dark .acs-drawer { background: #1f2937; }
        .acs-drawer.open { transform: none; }
        .acs-drawer-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 20px 22px 16px; border-bottom: 1px solid #eef0f4; background: linear-gradient(135deg, rgba(86, 177, 243,.10), rgba(86, 177, 243,.04) 60%, transparent); }
        .dark .acs-drawer-head { border-color: #374151; }
        .acs-drawer-body { flex: 1; overflow-y: auto; padding: 18px 22px 28px; }
        .acs-x { height: 32px; width: 32px; border-radius: 10px; color: #6b7280; transition: .15s; }
        .acs-x:hover { background: rgba(0,0,0,.06); color: #111827; }
        .acs-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .acs-stat { border-radius: 14px; padding: 12px 14px; background: linear-gradient(135deg, #e3f3ff, #e3f3ff); }
        .dark .acs-stat { background: rgba(86, 177, 243,.14); }
        .acs-stat b { display: block; font-size: 22px; line-height: 1.1; color: #1c71ae; }
        .dark .acs-stat b { color: #a0d7ff; }
        .acs-stat span { font-size: 11px; color: #6b7280; }
        .acs-alert { display: flex; gap: 8px; align-items: flex-start; border-radius: 12px; padding: 9px 12px; font-size: 12px; margin-bottom: 10px; }
        .acs-alert-warn { background: #fef3c7; color: #92400e; }
        .acs-alert-bad { background: #fee2e2; color: #991b1b; }
        .acs-tabs { display: flex; gap: 4px; margin: 14px 0 12px; padding: 4px; border-radius: 12px; background: #f3f4f6; }
        .dark .acs-tabs { background: rgba(255,255,255,.06); }
        .acs-tab { flex: 1; border-radius: 9px; padding: 6px 8px; font-size: 12px; font-weight: 600; color: #6b7280; transition: .15s; white-space: nowrap; }
        .acs-tab.active { background: #fff; color: #3193da; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
        .dark .acs-tab.active { background: #374151; color: #a0d7ff; }
        .acs-empty { padding: 28px 12px; text-align: center; font-size: 13px; color: #9ca3af; }
        .acs-mono { font-family: ui-monospace, monospace; font-size: 10.5px; color: #9ca3af; }
        .acs-tree, .acs-tree ul { list-style: none; margin: 0; padding: 0; }
        .acs-tree ul { margin-left: 14px; padding-left: 12px; border-left: 1px dashed #d1d5db; }
        .dark .acs-tree ul { border-color: #4b5563; }
        .acs-node { display: flex; align-items: center; gap: 8px; padding: 5px 8px; border-radius: 8px; font-size: 13px; }
        .acs-node:hover { background: rgba(86, 177, 243,.07); }
        .acs-node-ico { width: 14px; font-size: 12px; color: #7bc8ff; }
        .acs-node-bad { background: rgba(254,226,226,.6); }
        .acs-warn { margin-left: auto; border-radius: 999px; background: #fee2e2; color: #b91c1c; padding: 1px 8px; font-size: 10px; font-weight: 700; }
        .acs-table { width: 100%; font-size: 13px; border-collapse: collapse; }
        .acs-table th { text-align: left; font-size: 10.5px; text-transform: uppercase; letter-spacing: .06em; color: #9ca3af; padding: 6px 8px; }
        .acs-table td { padding: 9px 8px; border-top: 1px solid #f1f2f6; vertical-align: top; }
        .dark .acs-table td { border-color: #374151; }
        .acs-act { display: inline-block; margin: 1px 4px 1px 0; border-radius: 999px; padding: 1px 9px; font-size: 10.5px; font-weight: 700; background: #e5e7eb; color: #374151; }
        .acs-act-view { background: #dbeafe; color: #1d4ed8; } .acs-act-create { background: #dcfce7; color: #15803d; }
        .acs-act-edit { background: #fef3c7; color: #b45309; } .acs-act-delete { background: #fee2e2; color: #b91c1c; }
        .acs-empty-ico { display: grid; place-items: center; height: 56px; width: 56px; margin: 0 auto 10px; border-radius: 18px; font-size: 22px; color: #a0d7ff; background: linear-gradient(135deg, #e3f3ff, #e3f3ff); }
        .dark .acs-empty-ico { background: rgba(86, 177, 243,.15); }
        .acs-search { position: relative; margin-bottom: 12px; }
        .acs-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); font-size: 12px; color: #9ca3af; }
        .acs-search input { width: 100%; border-radius: 12px; border: 1px solid #e5e7eb; background: #f8fafc; padding: 9px 12px 9px 34px; font-size: 13px; outline: none; transition: .15s; }
        .acs-search input:focus { border-color: #56b1f3; background: #fff; box-shadow: 0 0 0 4px rgba(86, 177, 243,.14); }
        .dark .acs-search input { background: #374151; border-color: #4b5563; color: #f3f4f6; }
        .acs-people { display: grid; gap: 8px; }
        .acs-person { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 14px; border: 1px solid #eef0f6; background: #fff; transition: .15s; }
        .acs-person:hover { transform: translateY(-1px); border-color: #b6e1ff; box-shadow: 0 10px 22px -16px rgba(49, 147, 218,.6); }
        .dark .acs-person { background: rgba(255,255,255,.03); border-color: #374151; }
        .acs-avatar { flex: none; display: grid; place-items: center; height: 38px; width: 38px; border-radius: 13px; font-size: 13px; font-weight: 800; color: #fff; letter-spacing: .02em; box-shadow: 0 8px 16px -8px rgba(0,0,0,.4); }
        .acs-seen { flex: none; border-radius: 999px; padding: 2px 9px; font-size: 10.5px; font-weight: 600; background: #dcfce7; color: #15803d; }
        .acs-seen-never { background: #f3f4f6; color: #9ca3af; }
        .acs-badge-off { margin-left: 4px; border-radius: 999px; padding: 0 7px; font-size: 9.5px; font-weight: 700; background: #fee2e2; color: #b91c1c; }
        .acs-chips { display: flex; flex-wrap: wrap; gap: 6px; }
        .acs-chip { border-radius: 999px; background: #e3f3ff; color: #1c71ae; padding: 3px 11px; font-size: 12px; }
        .dark .acs-chip { background: rgba(86, 177, 243,.18); color: #b6e1ff; }
        .acs-rolelist { list-style: none; margin: 0; padding: 0; }
        .acs-rolelist li { display: flex; align-items: center; justify-content: space-between; padding: 10px 4px; border-top: 1px solid #f1f2f6; }
        .dark .acs-rolelist li { border-color: #374151; }
        .acs-link { font-size: 12px; font-weight: 600; color: #3193da; }
        .acs-timeline { list-style: none; margin: 0; padding: 0; }
        .acs-timeline li { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f1f2f6; }
        .dark .acs-timeline li { border-color: #374151; }
        .acs-dot { flex: none; height: 28px; width: 28px; display: grid; place-items: center; border-radius: 999px; font-size: 11px; background: #e5e7eb; color: #4b5563; }
        .acs-dot-create, .acs-dot-grant, .acs-dot-enable { background: #dcfce7; color: #15803d; }
        .acs-dot-disable, .acs-dot-revoke { background: #fee2e2; color: #b91c1c; }
        .acs-dot-update { background: #dbeafe; color: #1d4ed8; } .acs-dot-clone { background: #ccfbf1; color: #0f766e; }
        .acs-diff { margin-top: 3px; font-size: 11.5px; color: #6b7280; word-break: break-word; }
        .acs-diff-add { color: #15803d; } .acs-diff-del { color: #b91c1c; }
        .gaps-card { border-radius: 14px; padding: 12px 14px; background: linear-gradient(180deg, rgba(248,250,252,.9), rgba(255,255,255,0)); }
        .dark .gaps-card { background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,0)); }
        .gaps-card-red { background: linear-gradient(180deg, rgba(254,226,226,.55), rgba(255,255,255,0)); }
        .dark .gaps-card-red { background: linear-gradient(180deg, rgba(127,29,29,.25), rgba(0,0,0,0)); }
        .gaps-count { margin-left: 4px; border-radius: 999px; background: #dc2626; color: #fff; padding: 0 7px; font-size: 10px; line-height: 16px; }
        .gaps-count:empty { display: none; }
        .gaps-chip { border-radius: 999px; padding: 1px 9px; font-size: 11px; font-weight: 600; }
        .gaps-chip-red { background: #fee2e2; color: #b91c1c; }
        .gaps-chip-amber { background: #fef3c7; color: #b45309; }
        .gaps-chip-rose { background: #ffe4e6; color: #be123c; }
        .gaps-chip-indigo { background: #d1ecff; color: #1c71ae; }
        .gaps-chip-fuchsia { background: #fae8ff; color: #a21caf; }
        .count-pill { display: inline-flex; align-items: center; gap: 5px; border-radius: 999px; padding: 2px 10px; font-size: 11px; font-weight: 600; }
        .count-pill i { font-size: 9px; opacity: .8; }
        .count-pill-blue { background: #dbeafe; color: #1d4ed8; }
        .count-pill-violet { background: #d1ecff; color: #1c71ae; }
        .count-pill-zero { background: #f3f4f6; color: #9ca3af; }
        .count-pill-warn { background: #fef3c7; color: #b45309; }
        .dark .count-pill-blue { background: rgba(86, 177, 243,.2); color: #93c5fd; }
        .dark .count-pill-violet { background: rgba(86, 177, 243,.2); color: #a0d7ff; }
        .dark .count-pill-zero { background: rgba(255,255,255,.06); color: #6b7280; }
        .dark .count-pill-warn { background: rgba(245,158,11,.2); color: #fcd34d; }
        .gaps-id-badge {
            font-family: ui-monospace, monospace; font-size: 0.6875rem; flex-shrink: 0;
            padding: 0.05rem 0.4rem; border-radius: 0.35rem; background-color: #f3f4f6; color: #4b5563;
        }
        .dark .gaps-id-badge { background-color: rgba(255,255,255,0.06); color: #d1d5db; }

        /* ---- select2 sizing + dark mode ---- */
        .studio-select2 + .select2-container .select2-selection--single {
            height: 2.6rem; border-radius: 0.6rem; border-color: #d1d5db; display: flex; align-items: center;
        }
        .studio-select2 + .select2-container .select2-selection__rendered { line-height: normal; }
        .studio-select2 + .select2-container .select2-selection__arrow { height: 2.6rem; }
        .dark .studio-select2 + .select2-container .select2-selection--single {
            background-color: #374151; border-color: #4b5563; color: #f3f4f6;
        }
        .dark .studio-select2 + .select2-container .select2-selection__rendered { color: #f3f4f6; }
        .dark .select2-dropdown { background-color: #374151; border-color: #4b5563; color: #f3f4f6; }
        .dark .select2-search__field { background-color: #4b5563; color: #f3f4f6; border-color: #6b7280; }
        .dark .select2-results__option { color: #f3f4f6; }
        .dark .select2-results__option--highlighted { background-color: #3193da !important; }

        /* ---- menu tree (step 3) ---- */
        .mnu-tree-container { min-height: 4rem; }
        .mnu-drag-handle { cursor: grab; }
        .mnu-drag-handle:active { cursor: grabbing; }
        .mnu-tree-row { transition: background-color .12s; }
        .mnu-tree-row:hover { background-color: rgba(86, 177, 243, .06); }
        .mnu-tree-row.drop-before { box-shadow: inset 0 2px 0 0 #56b1f3; }
        .mnu-tree-row.drop-after { box-shadow: inset 0 -2px 0 0 #56b1f3; }
        .mnu-tree-row.drop-inside { background-color: rgba(86, 177, 243, .15); }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const STUDIO_CSRF = '{{ csrf_token() }}';

        function showLoading() { $('#loadingOverlay').removeClass('hidden'); }
        function hideLoading() { $('#loadingOverlay').addClass('hidden'); }

        function studioMiniCrud(cfg) {
            let dt = $(cfg.tableSel).DataTable({
                ajax: { url: cfg.jsonUrl, type: 'GET', dataSrc: 'data' },
                processing: true,
                serverSide: false,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lf>rtip',
                columns: [{
                    data: 'id',
                    orderable: false,
                    render: function(data, type, row) {
                        return `<div class="flex items-center justify-center gap-2">
                            <label class="switch"><input type="checkbox" class="studio-toggle" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}><span class="slider round"></span></label>
                            <button type="button" class="studio-edit-btn bg-blue-500 text-white px-2 py-1 rounded text-xs" data-id="${data}"><i class="fas fa-edit"></i></button>
                            ${cfg.extraActions ? cfg.extraActions(row) : ''}
                        </div>`;
                    }
                }, ...cfg.columns],
                initComplete: cfg.initComplete || undefined,
            });

            $(cfg.addBtnSel).on('click', function() {
                $(cfg.titleSel).text(cfg.addLabel);
                $(cfg.formSel)[0].reset();
                $(cfg.idSel).val('');
                if (cfg.onOpenAdd) cfg.onOpenAdd();
                $(cfg.modalSel).removeClass('hidden');
            });

            $(document).on('click', cfg.tableSel + ' .studio-edit-btn', function() {
                let id = $(this).data('id');
                $(cfg.titleSel).text('Loading...');
                $(cfg.formSel)[0].reset();
                $(cfg.modalSel).removeClass('hidden');
                showLoading();

                $.get(cfg.editUrl(id), function(data) {
                    $(cfg.titleSel).text(cfg.editLabel);
                    $(cfg.idSel).val(data.id);
                    cfg.fields.forEach(f => $('#' + f).val(data[f.replace(cfg.prefix + '_', '')]));
                    if (cfg.onLoad) cfg.onLoad(data);
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $(cfg.modalSel).addClass('hidden');
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load data' });
                    console.error(xhr.responseText);
                });
            });

            $(document).on('change', cfg.tableSel + ' .studio-toggle', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                const $cb = $(this);
                const doToggle = () => $.ajax({
                    url: cfg.toggleUrl(id),
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { status: newStatus },
                    success: () => { dt.ajax.reload(null, false); if (cfg.onSaved) cfg.onSaved(); }
                });
                // Optional impact confirmation when switching OFF
                if (newStatus === 'X' && cfg.confirmDisable) {
                    const row = dt.row($cb.closest('tr')).data();
                    cfg.confirmDisable(row).then(ok => { if (ok) doToggle(); else $cb.prop('checked', true); });
                } else {
                    doToggle();
                }
            });

            $(cfg.formSel).on('submit', function(e) {
                e.preventDefault();
                let id = $(cfg.idSel).val();
                let url = id ? cfg.updateUrl(id) : cfg.storeUrl;
                let formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $(this).find('button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        hideLoading();
                        $(cfg.formSel).find('button[type="submit"]').prop('disabled', false);
                        $(cfg.modalSel).addClass('hidden');
                        dt.ajax.reload();
                        if (cfg.onSaved) cfg.onSaved(resp);
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $(cfg.formSel).find('button[type="submit"]').prop('disabled', false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Save failed' });
                        console.error(xhr.responseText);
                    }
                });
            });

            $(cfg.closeBtnSel).on('click', function() {
                $(cfg.formSel)[0].reset();
                $(cfg.idSel).val('');
                $(cfg.modalSel).addClass('hidden');
            });

            return dt;
        }

        function initStudioSelect2(scope) {
            $(scope || document).find('.studio-select2').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) return;
                const $modal = $(this).closest('.fixed');
                $(this).select2({
                    width: '100%',
                    dropdownParent: $modal.length ? $modal : $(document.body)
                });
            });
        }

        $(document).ready(function() {
            initStudioSelect2();

            // ===== Setup Gaps panel =====
            function gapsRenderList(sel, items, render) {
                const $ul = $(sel);
                if (!items.length) {
                    $ul.html('<li class="italic text-gray-400">None — all clear.</li>');
                    return;
                }
                $ul.html(items.map(render).join(''));
            }

            function gapsLoad() {
                $.get("{{ route('access_control_studio.coverage') }}", function(data) {
                    const routeMissing = data.menus_route_missing || [];
                    const total = routeMissing.length + data.menus_missing_screen.length + data.screens_without_route.length + data.unregistered_screen_ids.length + data.menus_app_mismatch.length;

                    // Per-category chips in the collapsed header + counts on each card
                    const chip = (n, label, cls) => n ? `<span class="gaps-chip ${cls}">${n} ${label}</span>` : '';
                    $('#gaps_chips').html(
                        chip(routeMissing.length, 'route missing', 'gaps-chip-red') +
                        chip(data.menus_missing_screen.length, 'no screen', 'gaps-chip-amber') +
                        chip(data.screens_without_route.length, 'unwired', 'gaps-chip-rose') +
                        chip(data.unregistered_screen_ids.length, 'unregistered', 'gaps-chip-indigo') +
                        chip(data.menus_app_mismatch.length, 'app mismatch', 'gaps-chip-fuchsia'));
                    $('#gaps_cnt_route').text(routeMissing.length || '');

                    gapsRenderList('#gaps_menusRouteMissing', routeMissing, (m) =>
                        `<li>
                            <button type="button" class="gaps-jump-menu flex min-w-0 flex-1 items-center gap-1.5 text-left text-indigo-600 hover:underline dark:text-indigo-400" data-menu-id="${m.id}">
                                <span class="truncate">${m.menu_name}</span>
                                <span class="gaps-id-badge" style="color:#b91c1c">${m.menu_route}</span>
                            </button>
                        </li>`);

                    $('#gaps_statusIcon')
                        .removeClass('bg-gray-100 text-gray-400 dark:bg-gray-700 bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300 bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300')
                        .addClass(total
                            ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300'
                            : 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300')
                        .html(total
                            ? '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>'
                            : '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>');

                    $('#tile_gaps').text(total);
                    $('#tile_gapsBtn').toggleClass('is-clear', total === 0).find('span').text(total === 0 ? 'All wired up' : 'Open gaps');
                    $('#gaps_summary').text(total ? `${total} thing${total === 1 ? '' : 's'} need attention` : 'Everything is wired up');

                    gapsRenderList('#gaps_menusMissingScreen', data.menus_missing_screen, (m) =>
                        `<li>
                            <button type="button" class="gaps-jump-menu flex min-w-0 flex-1 items-center gap-1.5 text-left text-indigo-600 hover:underline dark:text-indigo-400" data-menu-id="${m.id}">
                                <span class="truncate">${m.menu_name}</span>
                                <span class="gaps-id-badge">${m.menu_id}</span>
                            </button>
                        </li>`);

                    gapsRenderList('#gaps_screensWithoutRoute', data.screens_without_route, (s) =>
                        `<li>
                            <span class="min-w-0 flex-1 truncate text-gray-700 dark:text-gray-200">${s.screen_name}</span>
                            <span class="gaps-id-badge">${s.screen_id}</span>
                        </li>`);

                    gapsRenderList('#gaps_unregisteredScreens', data.unregistered_screen_ids, (id) =>
                        `<li><span class="gaps-id-badge">${id}</span></li>`);

                    gapsRenderList('#gaps_menusAppMismatch', data.menus_app_mismatch, (m) =>
                        `<li>
                            <button type="button" class="gaps-jump-menu flex min-w-0 flex-1 items-center gap-1.5 text-left text-indigo-600 hover:underline dark:text-indigo-400" data-menu-id="${m.id}">
                                <span class="truncate">${m.menu_name}</span>
                                <span class="gaps-id-badge">${m.menu_application_id || '(none)'} ≠ ${m.screen_application_id}</span>
                            </button>
                        </li>`);

                    gapsRenderList('#gaps_screensWithRoute', data.screens_with_route, (s) =>
                        `<li>
                            <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300">${s.screen_name}</span>
                            <span class="gaps-id-badge">${s.screen_id}</span>
                            <span class="shrink-0 text-xs text-green-600 dark:text-green-400">${s.actions.join(', ') || 'mapped'}</span>
                        </li>`);
                });
            }

            // Hero tiles: jump to a step / open gaps panel
            $(document).on('click', '.acs-tile[data-step]', function() { goToStep($(this).data('step')); });
            $('#tile_gapsBtn').on('click', function() {
                $('#gaps_body').removeClass('hidden'); $('#gaps_chevron').addClass('rotate-180');
                document.getElementById('gaps_panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            // Keep tile counts live as tables reload
            const acsLiveCount = (sel, tile) => $(sel).on('xhr.dt', (e, s, json) => { if (json?.data) $(tile).text(json.data.filter(r => r.status === 'A').length); });

            $('#gaps_toggle').on('click', function() {
                $('#gaps_body').toggleClass('hidden');
                $('#gaps_chevron').toggleClass('rotate-180');
            });

            $(document).on('click', '.gaps-jump-menu', function() {
                const id = $(this).data('menu-id');
                goToStep('menu');
                $(`.mnu-edit-btn[data-id="${id}"]`).trigger('click');
            });

            gapsLoad();

            // Live "does this route exist?" hint under the Menu Route Name input
            const knownRoutes = new Set(@json($routeNames));
            function mnuRouteHint() {
                const v = ($('#mnu_menu_route').val() || '').trim();
                const $h = $('#mnu_route_hint');
                if (!v) return $h.addClass('hidden');
                // Param routes (e.g. foo.show) aren't in the picker list, so "unknown" is a soft warning
                const ok = knownRoutes.has(v);
                $h.removeClass('hidden text-green-600 text-amber-600')
                  .addClass(ok ? 'text-green-600' : 'text-amber-600')
                  .html(ok ? '<i class="fas fa-check-circle"></i> Route exists'
                           : '<i class="fas fa-exclamation-triangle"></i> Not a known route — the sidebar will show this menu disabled');
            }
            $(document).on('input change', '#mnu_menu_route', mnuRouteHint);
            $(document).on('click', '.mnu-edit-btn, #mnu_addBtn, .mnu-child-btn', () => setTimeout(mnuRouteHint, 400));

            // ===== Role overview / menu->roles / history drawer, clone, impact confirm =====
            const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

            function acsOpenDrawer(kicker, title, sub, html) {
                $('#acs_drawerKicker').text(kicker); $('#acs_drawerTitle').text(title); $('#acs_drawerSub').text(sub || '');
                $('#acs_drawerBody').html(html);
                $('#acs_drawerMask').addClass('open'); $('#acs_drawer').addClass('open').attr('aria-hidden', 'false');
            }
            function acsCloseDrawer() { $('#acs_drawerMask').removeClass('open'); $('#acs_drawer').removeClass('open').attr('aria-hidden', 'true'); }
            $('#acs_drawerClose, #acs_drawerMask').on('click', acsCloseDrawer);
            $(document).on('keydown', e => { if (e.key === 'Escape') acsCloseDrawer(); });
            const acsLoadingHtml = '<div class="acs-empty"><i class="fas fa-circle-notch fa-spin"></i> Loading…</div>';

            // --- Role overview ("what can this role see?")
            function acsMenuTreeHtml(menus) {
                const ids = new Set(menus.map(m => m.menu_id));
                const kids = (pid) => menus.filter(m => (m.parent_menu_id || null) === pid);
                // Menus whose parent isn't granted still show, as roots
                const roots = menus.filter(m => !m.parent_menu_id || !ids.has(m.parent_menu_id));
                const node = (m) => {
                    const c = kids(m.menu_id);
                    return `<li><div class="acs-node ${m.route_missing ? 'acs-node-bad' : ''}">
                            <i class="fas ${c.length ? 'fa-folder-open' : 'fa-file-lines'} acs-node-ico"></i>
                            <span class="font-medium">${esc(m.menu_name)}</span>
                            <span class="acs-mono">${esc(m.menu_id)}</span>
                            ${m.route_missing ? '<span class="acs-warn"><i class="fas fa-triangle-exclamation"></i> route missing</span>' : ''}
                        </div>${c.length ? `<ul>${c.map(node).join('')}</ul>` : ''}</li>`;
                };
                return roots.length ? `<ul class="acs-tree">${roots.map(node).join('')}</ul>` : '<div class="acs-empty"><div class="acs-empty-ico"><i class="fas fa-bars-staggered"></i></div>No menus granted yet.<br><span class="text-xs">Grant them in the Role Menu step.</span></div>';
            }
            // People list: gradient initials avatar, name, @username, title/dept, last login
            const acsAvatarGrads = ['#56b1f3,#56b1f3', '#0ea5e9,#56b1f3', '#10b981,#0ea5e9', '#f59e0b,#ef4444', '#ec4899,#56b1f3', '#14b8a6,#22c55e', '#f43f5e,#f59e0b'];
            function acsAgo(ts) {
                if (!ts) return 'never signed in';
                const d = new Date(String(ts).replace(' ', 'T')); if (isNaN(d)) return '';
                const days = Math.floor((Date.now() - d) / 864e5);
                return days < 1 ? 'active today' : days === 1 ? 'yesterday' : days < 30 ? `${days}d ago` : days < 365 ? `${Math.floor(days / 30)}mo ago` : `${Math.floor(days / 365)}y ago`;
            }
            function acsUsersHtml(users) {
                if (!users.length) return `<div class="acs-empty"><div class="acs-empty-ico"><i class="fas fa-user-slash"></i></div>No one has this role yet.<br><span class="text-xs">Assign it from the Users page.</span></div>`;
                const cards = users.map(u => {
                    const label = u.name || u.username;
                    const ini = label.split(/[\s._-]+/).filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase() || '?';
                    let h = 0; for (const c of u.username) h = (h * 31 + c.charCodeAt(0)) >>> 0;
                    const sub = [u.jabatan, u.department_id].filter(Boolean).map(esc).join(' · ');
                    return `<div class="acs-person" data-q="${esc((label + ' ' + u.username + ' ' + (u.department_id || '') + ' ' + (u.jabatan || '')).toLowerCase())}">
                        <span class="acs-avatar" style="background:linear-gradient(135deg,${acsAvatarGrads[h % acsAvatarGrads.length]})">${esc(ini)}</span>
                        <div class="min-w-0 flex-1">
                            <div class="truncate text-sm font-semibold text-gray-800 dark:text-gray-100">${esc(label)}${u.user_status && u.user_status !== 'A' ? ' <span class="acs-badge-off">inactive</span>' : ''}</div>
                            <div class="truncate text-[11px] text-gray-400">@${esc(u.username)}${sub ? ' · ' + sub : ''}</div>
                        </div>
                        <span class="acs-seen ${u.last_login_at ? '' : 'acs-seen-never'}">${acsAgo(u.last_login_at)}</span>
                    </div>`;
                }).join('');
                return `<div class="acs-search"><i class="fas fa-magnifying-glass"></i><input type="text" class="acs-user-search" placeholder="Search ${users.length} user${users.length > 1 ? 's' : ''}…"></div>
                    <div class="acs-people">${cards}</div><div class="acs-empty acs-no-match hidden">No matching users.</div>`;
            }
            $(document).on('input', '.acs-user-search', function() {
                const q = $(this).val().toLowerCase().trim(), $pane = $(this).closest('.acs-pane');
                let n = 0;
                $pane.find('.acs-person').each(function() { const ok = !q || String($(this).data('q')).includes(q); $(this).toggle(ok); if (ok) n++; });
                $pane.find('.acs-no-match').toggleClass('hidden', n > 0);
            });

            function acsRoleOverview(roleId) {
                acsOpenDrawer('Role overview', roleId, '', acsLoadingHtml);
                $.get(`/access-control-studio/role/${encodeURIComponent(roleId)}/overview`, function(d) {
                    $('#acs_drawerTitle').text(d.role.role_name);
                    $('#acs_drawerSub').text(d.role.role_id + (d.role.status === 'A' ? '' : ' · inactive'));
                    const bad = d.menus.filter(m => m.route_missing).length;
                    const rights = d.rights.map(r => `<tr><td><span class="font-medium">${esc(r.screen_name || r.screen_id)}</span><div class="acs-mono">${esc(r.screen_id)}</div></td>
                        <td>${r.actions.map(a => `<span class="acs-act acs-act-${esc(a).toLowerCase()}">${esc(a)}</span>`).join('')}</td></tr>`).join('');
                    const html = `
                        <div class="acs-stats">
                            <div class="acs-stat"><b>${d.users.length}</b><span>Users</span></div>
                            <div class="acs-stat"><b>${d.menus.length}</b><span>Menus</span></div>
                            <div class="acs-stat"><b>${d.rights.length}</b><span>Screens with rights</span></div>
                        </div>
                        ${d.menus.length === 0 ? '<div class="acs-alert acs-alert-warn"><i class="fas fa-triangle-exclamation"></i> No menus granted — users with only this role see an empty sidebar.</div>' : ''}
                        ${bad ? `<div class="acs-alert acs-alert-bad"><i class="fas fa-link-slash"></i> ${bad} granted menu${bad > 1 ? 's have' : ' has'} a route that doesn't exist in this codebase.</div>` : ''}
                        <div class="acs-tabs">
                            <button class="acs-tab active" data-tab="menus">Menus (${d.menus.length})</button>
                            <button class="acs-tab" data-tab="rights">Access rights (${d.rights.length})</button>
                            <button class="acs-tab" data-tab="users">Users (${d.users.length})</button>
                            <button class="acs-tab" data-tab="hist">History</button>
                        </div>
                        <div class="acs-pane" data-pane="menus">${acsMenuTreeHtml(d.menus)}</div>
                        <div class="acs-pane hidden" data-pane="rights">${rights ? `<table class="acs-table"><thead><tr><th>Screen</th><th>Allowed actions</th></tr></thead><tbody>${rights}</tbody></table>` : '<div class="acs-empty"><div class="acs-empty-ico"><i class="fas fa-shield-halved"></i></div>No access rights granted yet.<br><span class="text-xs">Set them in the Access Rights step.</span></div>'}</div>
                        <div class="acs-pane hidden" data-pane="users">${acsUsersHtml(d.users)}</div>
                        <div class="acs-pane hidden" data-pane="hist" data-entity="${esc(roleId)}">${acsLoadingHtml}</div>`;
                    $('#acs_drawerBody').html(html);
                }).fail(() => $('#acs_drawerBody').html('<div class="acs-empty">Failed to load role.</div>'));
            }
            $(document).on('click', '.acs-tab', function() {
                const tab = $(this).data('tab');
                $(this).addClass('active').siblings().removeClass('active');
                const $pane = $(this).closest('.acs-drawer-body').find(`.acs-pane[data-pane="${tab}"]`);
                $(this).closest('.acs-drawer-body').find('.acs-pane').addClass('hidden'); $pane.removeClass('hidden');
                if (tab === 'hist' && !$pane.data('loaded')) {
                    $pane.data('loaded', 1);
                    $.get("{{ route('access_control_studio.audit') }}", { entity: $pane.data('entity') }, d => $pane.html(acsAuditHtml(d.data)));
                }
            });
            $(document).on('click', '.acs-role-view', function() { acsRoleOverview($(this).data('role')); });

            // --- Reverse view: roles that can see a menu
            $(document).on('click', '.acs-menu-roles', function() {
                const id = $(this).data('menu'), name = $(this).data('name');
                acsOpenDrawer('Menu access', name, id, acsLoadingHtml);
                $.get(`/access-control-studio/menu/${encodeURIComponent(id)}/roles`, function(d) {
                    $('#acs_drawerBody').html(d.roles.length
                        ? `<p class="mb-3 text-sm text-gray-500">${d.roles.length} role${d.roles.length > 1 ? 's' : ''} can see this menu:</p>
                           <ul class="acs-rolelist">${d.roles.map(r => `<li><div><span class="font-medium">${esc(r.role_name)}</span><div class="acs-mono">${esc(r.role_id)}</div></div>
                              <button class="acs-role-view acs-link" data-role="${esc(r.role_id)}">View <i class="fas fa-arrow-right"></i></button></li>`).join('')}</ul>`
                        : '<div class="acs-empty">No active role can see this menu.</div>');
                });
            });

            // --- Change history
            const acsActionIcon = { create: 'fa-plus', update: 'fa-pen', enable: 'fa-toggle-on', disable: 'fa-toggle-off', clone: 'fa-copy', grant: 'fa-key', revoke: 'fa-ban' };
            function acsAuditHtml(rows) {
                if (!rows.length) return '<div class="acs-empty">No changes recorded yet. New role, menu and permission changes will show up here.</div>';
                return `<ul class="acs-timeline">${rows.map(r => {
                    let det = '';
                    if (r.changes?.added?.length) det += `<div class="acs-diff acs-diff-add">+ ${r.changes.added.map(esc).join(', ')}</div>`;
                    if (r.changes?.removed?.length) det += `<div class="acs-diff acs-diff-del">− ${r.changes.removed.map(esc).join(', ')}</div>`;
                    if (r.changes && !r.changes.added && !r.changes.removed) {
                        det = Object.entries(r.changes).filter(([, v]) => v && typeof v === 'object' && 'to' in v)
                            .map(([k, v]) => `<div class="acs-diff">${esc(k)}: <s>${esc(v.from ?? '—')}</s> → <b>${esc(v.to ?? '—')}</b></div>`).join('');
                    }
                    return `<li><span class="acs-dot acs-dot-${esc(r.action)}"><i class="fas ${acsActionIcon[r.action] || 'fa-circle'}"></i></span>
                        <div class="min-w-0"><div class="text-sm font-medium text-gray-800 dark:text-gray-100">${esc(r.description)}</div>${det}
                        <div class="mt-0.5 text-[11px] text-gray-400">${esc(r.created_by || 'system')} · ${esc(String(r.created_at).slice(0, 16))}</div></div></li>`;
                }).join('')}</ul>`;
            }
            $('#acs_historyBtn').on('click', function() {
                acsOpenDrawer('Audit trail', 'Change History', 'Latest 200 role, menu and permission changes', acsLoadingHtml);
                $.get("{{ route('access_control_studio.audit') }}", d => $('#acs_drawerBody').html(acsAuditHtml(d.data)));
            });

            // --- Clone role
            $(document).on('click', '.acs-role-clone', function() {
                const src = $(this).data('role'), srcName = $(this).data('name');
                Swal.fire({
                    title: 'Clone role',
                    html: `<p style="font-size:13px;color:#6b7280;margin-bottom:12px">Copies <b>${esc(src)}</b>'s menus and access rights. Users are <b>not</b> copied.</p>
                        <input id="clone_id" class="swal2-input" placeholder="New Role ID" value="${esc(src)}_COPY">
                        <input id="clone_name" class="swal2-input" placeholder="New Role Name" value="${esc(srcName)} (copy)">`,
                    showCancelButton: true, confirmButtonText: 'Clone', focusConfirm: false,
                    preConfirm: () => {
                        const role_id = $('#clone_id').val().trim(), role_name = $('#clone_name').val().trim();
                        if (!role_id || !role_name) { Swal.showValidationMessage('Role ID and name are required'); return false; }
                        return $.ajax({ url: "{{ route('access_control_studio.clone_role') }}", type: 'POST', headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                            data: { source_role_id: src, role_id, role_name } })
                            .catch(xhr => { Swal.showValidationMessage(xhr.responseJSON?.errors ? Object.values(xhr.responseJSON.errors).flat().join(' ') : (xhr.responseJSON?.message || 'Clone failed')); return false; });
                    }
                }).then(res => {
                    if (res.isConfirmed && res.value?.success) {
                        $('#rol_table').DataTable().ajax.reload(null, false);
                        if (typeof refreshRoleSelects === 'function') refreshRoleSelects();
                        Swal.fire({ icon: 'success', title: 'Role cloned', text: `${res.value.counts.menus} menus and ${res.value.counts.rights} access rights copied.`, timer: 1800, showConfirmButton: false });
                    }
                });
            });

            // --- Impact confirmation before disabling a role / menu
            function acsConfirmImpact(type, id, name) {
                return $.get(`/access-control-studio/impact/${type}/${encodeURIComponent(id)}`).then(d => {
                    const lines = type === 'role'
                        ? [`<b>${d.users}</b> user${d.users === 1 ? '' : 's'} will lose this role`, `<b>${d.menus}</b> menu grant${d.menus === 1 ? '' : 's'} stop applying`]
                        : [`<b>${d.roles}</b> role${d.roles === 1 ? '' : 's'} lose this menu`, d.children ? `<b>${d.children}</b> sub-menu${d.children === 1 ? '' : 's'} sit under it` : null].filter(Boolean);
                    return Swal.fire({
                        icon: 'warning', title: `Disable ${type} “${name || id}”?`,
                        html: `<ul style="text-align:left;display:inline-block;font-size:14px;line-height:1.8">${lines.map(l => `<li>• ${l}</li>`).join('')}</ul>`,
                        showCancelButton: true, confirmButtonText: 'Yes, disable', confirmButtonColor: '#dc2626',
                    }).then(r => r.isConfirmed);
                }).catch(() => Swal.fire({ icon: 'warning', title: `Disable ${type} “${name || id}”?`, showCancelButton: true, confirmButtonText: 'Yes, disable' }).then(r => r.isConfirmed));
            }

            const statusCol = {
                data: 'status',
                className: 'text-center',
                render: (d) => d === 'A' ?
                    '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded text-xs">Active</span>' :
                    '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded text-xs">Inactive</span>'
            };

            const lastCreated = { application: null, screen: null, menu: null, role: null };

            // Keeps cross-step <select> options (and the Access Rights app-name
            // labels) in sync after AJAX create/edit/toggle, so nothing needs a
            // full page reload to show up in a later step.
            function studioReloadSelectOptions($sel, url, buildOption) {
                const current = $sel.val();
                $.get(url, function(res) {
                    const rows = (res.data || []).filter(r => r.status === 'A');
                    const $placeholder = $sel.find('option').first();
                    $sel.empty();
                    if ($placeholder.length) $sel.append($placeholder);
                    rows.forEach(r => $sel.append(buildOption(r)));
                    $sel.val(current).trigger('change');
                });
            }

            function refreshApplicationSelects() {
                const url = "{{ route('applications.json') }}";
                ['#scr_application_id', '#mnu_application_id'].forEach(sel => {
                    studioReloadSelectOptions($(sel), url, r => `<option value="${r.application_id}">${r.application_id} - ${r.application_name}</option>`);
                });
                $.get(url, function(res) {
                    arAppNames = {};
                    (res.data || []).filter(r => r.status === 'A').forEach(r => arAppNames[r.application_id] = r.application_name);
                });
            }

            function refreshScreenSelects() {
                studioReloadSelectOptions($('#mnu_screen_id'), "{{ route('screens.json') }}",
                    r => `<option value="${r.screen_id}" data-app="${r.application_id}">${r.screen_id} - ${r.screen_name}</option>`);
                $.get("{{ route('screens.json') }}", function(res) {
                    arAllScreens = (res.data || []).filter(r => r.status === 'A');
                });
            }

            function refreshRoleSelects() {
                const url = "{{ route('roles.json') }}";
                ['#rm_roleSelect', '#rm_copyFrom', '#ar_roleSelect', '#ar_copyFrom'].forEach(sel => {
                    studioReloadSelectOptions($(sel), url, r => `<option value="${r.role_id}">${r.role_name}</option>`);
                });
            }

            // ===== Step 1: Application =====
            studioMiniCrud({
                tableSel: '#app_table', prefix: 'app', jsonUrl: "{{ route('applications.json') }}",
                columns: [{ data: 'application_id' }, { data: 'application_name' }, statusCol],
                modalSel: '#app_modal', formSel: '#app_form', titleSel: '#app_modalTitle', idSel: '#app_id',
                addBtnSel: '#app_addBtn', closeBtnSel: '#app_closeBtn',
                addLabel: 'Add Application', editLabel: 'Edit Application',
                fields: ['app_application_id', 'app_application_name'],
                storeUrl: "{{ route('applications.store') }}",
                editUrl: (id) => `/applications/${id}/edit`,
                updateUrl: (id) => `/applications/${id}`,
                toggleUrl: (id) => `/applications/${id}/toggle-status`,
                onSaved: (resp) => { if (resp?.data?.application_id) lastCreated.application = resp.data.application_id; refreshApplicationSelects(); }
            });

            // ===== Step 2: Screen =====
            studioMiniCrud({
                tableSel: '#scr_table', prefix: 'scr', jsonUrl: "{{ route('screens.json') }}",
                columns: [{ data: 'screen_id' }, { data: 'screen_name' }, { data: 'application_id' }, statusCol],
                modalSel: '#scr_modal', formSel: '#scr_form', titleSel: '#scr_modalTitle', idSel: '#scr_id',
                addBtnSel: '#scr_addBtn', closeBtnSel: '#scr_closeBtn',
                addLabel: 'Add Screen', editLabel: 'Edit Screen',
                fields: ['scr_screen_id', 'scr_screen_name', 'scr_application_id'],
                storeUrl: "{{ route('screens.store') }}",
                editUrl: (id) => `/screens/${id}/edit`,
                updateUrl: (id) => `/screens/${id}`,
                toggleUrl: (id) => `/screens/${id}/toggle-status`,
                onOpenAdd: () => { if (lastCreated.application) $('#scr_application_id').val(lastCreated.application).trigger('change'); },
                onSaved: (resp) => { if (resp?.data?.screen_id) lastCreated.screen = resp.data.screen_id; refreshScreenSelects(); }
            });

            // ===== Step 3: Menu (tree) =====
            let mnuTreeData = [];
            let mnuExpanded = new Set();
            let mnuSearchTerm = '';
            let mnuDraggedId = null;

            $('#mnu_screen_id').on('change', function() {
                const app = $(this).find(':selected').data('app');
                if (app && !$('#mnu_application_id').val()) {
                    $('#mnu_application_id').val(app).trigger('change');
                }
            });

            function mnuEscapeRegExp(s) { return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

            function mnuHighlight(text, term) {
                text = text ?? '';
                if (!term) return text;
                const re = new RegExp('(' + mnuEscapeRegExp(term) + ')', 'ig');
                return String(text).replace(re, '<mark class="bg-yellow-200 dark:bg-yellow-600 rounded px-0.5">$1</mark>');
            }

            function mnuAncestorIds(menuId) {
                const ids = new Set();
                let current = mnuTreeData.find(m => m.menu_id === menuId);
                while (current && current.parent_menu_id) {
                    ids.add(current.parent_menu_id);
                    current = mnuTreeData.find(m => m.menu_id === current.parent_menu_id);
                }
                return ids;
            }

            function mnuRefreshParentOptions(excludeMenuId) {
                const $sel = $('#mnu_parent_menu_id');
                const current = $sel.val();
                $sel.empty().append('<option value="">None (top-level)</option>');
                mnuTreeData
                    .filter(m => m.menu_id !== excludeMenuId)
                    .sort((a, b) => (a.menu_name || '').localeCompare(b.menu_name || ''))
                    .forEach(m => $sel.append(`<option value="${m.menu_id}">${m.menu_name} (${m.menu_id})</option>`));
                $sel.val(current).trigger('change');
            }

            function mnuLoadTree() {
                $('#mnu_treeContainer').css('opacity', 1).html('<div class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">Loading menus...</div>');
                $.get("{{ route('menus.json') }}", function(res) {
                    mnuTreeData = res.data || [];
                    $('#tile_mnu').text(mnuTreeData.filter(m => m.status === 'A').length);
                    mnuRefreshParentOptions();
                    mnuRenderTree();
                });
            }

            function mnuRenderTree() {
                const term = mnuSearchTerm.trim();
                let visibleIds = null;
                let matched = 0;

                if (term) {
                    const needle = term.toLowerCase();
                    visibleIds = new Set();
                    mnuTreeData.forEach(m => {
                        const haystack = `${m.menu_id} ${m.menu_name} ${m.menu_route || ''} ${m.menu_url || ''}`.toLowerCase();
                        if (haystack.includes(needle)) {
                            matched++;
                            visibleIds.add(m.menu_id);
                            mnuAncestorIds(m.menu_id).forEach(id => visibleIds.add(id));
                        }
                    });
                }

                const html = mnuBuildTree(null, term, visibleIds);
                $('#mnu_treeContainer').html(`<div class="menu-tree">${html || `<div class="py-6 text-center text-sm text-gray-500 dark:text-gray-400">${term ? 'No menus match your search.' : 'No menus yet — click + Add Menu to create the first one.'}</div>`}</div>`);
                $('#mnu_resultInfo').text(term ? `${matched} match` : '');
                $('#mnu_clearSearchBtn').toggleClass('hidden', !term);
            }

            function mnuBuildTree(parentId, term, visibleIds) {
                let children = mnuTreeData.filter(m => (m.parent_menu_id || null) === (parentId || null));
                if (visibleIds) children = children.filter(m => visibleIds.has(m.menu_id));
                children = children.sort((a, b) => {
                    const aSort = a.menu_sort_order ?? 0, bSort = b.menu_sort_order ?? 0;
                    if (aSort !== bSort) return aSort - bSort;
                    return (a.menu_id || '').localeCompare(b.menu_id || '');
                });
                if (!children.length) return '';

                let html = '<ul class="mt-1">';
                children.forEach(m => {
                    const hasChildren = mnuTreeData.some(x => (x.parent_menu_id || null) === m.menu_id);
                    const childCount = hasChildren ? mnuTreeData.filter(x => (x.parent_menu_id || null) === m.menu_id).length : 0;
                    const isExpanded = term ? true : mnuExpanded.has(m.menu_id);
                    const isActive = m.status === 'A';

                    html += `
                        <li data-menu-id="${m.menu_id}" data-parent="${m.parent_menu_id || ''}">
                            <div class="mnu-tree-row flex items-center space-x-2 rounded-lg px-2 py-1.5 ${isActive ? '' : 'opacity-50'}">
                                <div class="flex items-center">
                                    ${term ? '<span class="inline-block w-4"></span>' : `<span class="mnu-drag-handle px-1 text-gray-400" draggable="true" title="Drag to reorder / move"><i class="fas fa-grip-vertical"></i></span>`}
                                    ${hasChildren
                                        ? `<button type="button" class="mnu-tree-toggle w-4 text-gray-500 dark:text-gray-400" data-menu-id="${m.menu_id}">${isExpanded ? '▾' : '▸'}</button>`
                                        : `<span class="inline-block w-4"></span>`
                                    }
                                </div>
                                <div class="flex flex-1 flex-wrap items-center justify-between gap-y-1">
                                    <div>
                                        <span class="rounded bg-indigo-50 px-1.5 py-0.5 font-mono text-[11px] text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">${mnuHighlight(m.menu_id, term)}</span>
                                        <span class="ml-2 text-sm font-semibold text-gray-800 dark:text-gray-100">${mnuHighlight(m.menu_name, term)}</span>
                                        <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">${mnuHighlight(m.screen_id || '-', term)}</span>
                                        <span class="ml-1 text-[10px] text-gray-400">[Sort: ${m.menu_sort_order ?? 0}]</span>
                                        ${!isExpanded && hasChildren ? `<span class="ml-1 text-[10px] text-gray-400">(${childCount} sub)</span>` : ''}
                                        ${m.route_missing ? `<span class="ml-2 inline-flex items-center gap-1 rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300" title="Route '${m.menu_route}' is not defined in this codebase. The sidebar shows this menu as disabled."><i class="fas fa-exclamation-triangle"></i> Route missing: ${m.menu_route}</span>` : ''}
                                    </div>
                                    <div class="flex items-center space-x-1.5">
                                        <label class="switch" title="${isActive ? 'Active' : 'Inactive'}">
                                            <input type="checkbox" class="mnu-toggle-status" data-id="${m.id}" ${isActive ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button type="button" title="Edit menu" class="mnu-edit-btn flex h-7 w-7 items-center justify-center rounded bg-blue-500 text-sm text-white hover:bg-blue-600" data-id="${m.id}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" title="Which roles can see this menu?" class="acs-menu-roles flex h-7 w-7 items-center justify-center rounded bg-violet-500 text-sm text-white hover:bg-violet-600" data-menu="${m.menu_id}" data-name="${(m.menu_name || '').replace(/"/g, '&quot;')}">
                                            <i class="fas fa-users"></i>
                                        </button>
                                        <button type="button" title="Add child menu" class="mnu-child-btn flex h-7 w-7 items-center justify-center rounded bg-emerald-500 text-sm text-white hover:bg-emerald-600" data-parent-menu="${m.menu_id}">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="mnu-children-node ${isExpanded ? '' : 'hidden'} ml-5 border-l border-gray-100 pl-2 dark:border-gray-700" data-parent="${m.menu_id}">
                                ${mnuBuildTree(m.menu_id, term, visibleIds)}
                            </div>
                        </li>`;
                });
                html += '</ul>';
                return html;
            }

            // ---- Menu icon picker (stores a single SVG path "d", rendered by the sidebar) ----
            const MNU_ICONS = [
                ['Home', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['Dashboard', 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                ['User', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['Users', 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['Team', 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
                ['Settings', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Document', 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['Agreement', 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                ['Form', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['List', 'M8.25 6.75h12M8.25 12h12M8.25 17.25h12M3.75 6.75h.008M3.75 12h.008M3.75 17.25h.008'],
                ['Calendar', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Clock', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Chart', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['Briefcase', 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['Building', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-5 0h5'],
                ['Location', 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Mail', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['Chat', 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ['Bell', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                ['Lock', 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'],
                ['Key', 'M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z'],
                ['Shield', 'M12 3l7.5 4.5v6c0 4.5-3.15 7.8-7.5 9-4.35-1.2-7.5-4.5-7.5-9v-6L12 3z'],
                ['Approval', 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Folder', 'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z'],
                ['Tag', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z'],
                ['Ticket', 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                ['Money', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Cart', 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                ['Training', 'M12 14l9-5-9-5-9 5 9 5zM12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z'],
                ['Search', 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
                ['Database', 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4'],
                ['Globe', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
                ['Star', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
            ];
            const mnuIconSvg = (d, cls = 'h-5 w-5') => `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="${d}"/></svg>`;

            function mnuSetIcon(d) {
                d = d || '';
                $('#mnu_menu_icon').val(d);
                const known = MNU_ICONS.find(i => i[1] === d);
                $('#mnu_icon_preview').html(d ? mnuIconSvg(d) : '');
                $('#mnu_icon_label').text(d ? (known ? known[0] : 'Custom icon') : 'Choose icon')
                    .toggleClass('text-gray-400', !d).toggleClass('text-gray-700 dark:text-gray-200', !!d);
                $('#mnu_icon_clear').toggleClass('hidden', !d);
            }

            function mnuRenderIconGrid(term) {
                term = (term || '').toLowerCase();
                const current = $('#mnu_menu_icon').val();
                const html = MNU_ICONS.filter(i => i[0].toLowerCase().includes(term)).map(i =>
                    `<button type="button" class="mnu-icon-opt flex flex-col items-center gap-0.5 rounded-lg p-1.5 text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 dark:text-gray-300 dark:hover:bg-gray-700 ${i[1] === current ? 'bg-indigo-50 text-indigo-600 ring-1 ring-indigo-300 dark:bg-gray-700' : ''}" data-path="${i[1]}" title="${i[0]}">${mnuIconSvg(i[1])}<span class="w-full truncate text-[9px]">${i[0]}</span></button>`
                ).join('');
                $('#mnu_icon_grid').html(html || '<p class="col-span-6 py-3 text-center text-xs text-gray-400">No icon found</p>');
            }

            $('#mnu_icon_toggle').on('click keydown', function(e) {
                if (e.type === 'keydown' && e.key !== 'Enter' && e.key !== ' ') return;
                e.preventDefault();
                const panel = $('#mnu_icon_panel');
                if (panel.hasClass('hidden')) {
                    $('#mnu_icon_search').val('');
                    mnuRenderIconGrid('');
                    panel.removeClass('hidden');
                    $('#mnu_icon_search').trigger('focus');
                } else {
                    panel.addClass('hidden');
                }
            });
            $('#mnu_icon_search').on('input', function() { mnuRenderIconGrid(this.value); });
            $('#mnu_icon_grid').on('click', '.mnu-icon-opt', function() {
                mnuSetIcon($(this).data('path'));
                $('#mnu_icon_panel').addClass('hidden');
            });
            $('#mnu_icon_clear').on('click', function(e) {
                e.stopPropagation();
                mnuSetIcon('');
            });
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#mnu_icon_picker').length) $('#mnu_icon_panel').addClass('hidden');
            });

            function mnuResetForm() {
                $('#mnu_form')[0].reset();
                mnuSetIcon('');
                $('#mnu_icon_panel').addClass('hidden');
                $('#mnu_id').val('');
                $('#mnu_application_id').val(null).trigger('change');
                $('#mnu_screen_id').val(null).trigger('change');
                mnuRefreshParentOptions();
                $('#mnu_parent_menu_id').val(null).trigger('change');
            }

            $('#mnu_addBtn').on('click', function() {
                mnuResetForm();
                $('#mnu_modalTitle').text('Add Menu');
                if (lastCreated.screen) $('#mnu_screen_id').val(lastCreated.screen).trigger('change');
                $('#mnu_modal').removeClass('hidden');
            });

            $(document).on('click', '.mnu-child-btn', function() {
                const parentMenuId = $(this).data('parent-menu');
                mnuResetForm();
                $('#mnu_modalTitle').text('Add Child Menu');
                $('#mnu_parent_menu_id').val(parentMenuId).trigger('change');
                $('#mnu_modal').removeClass('hidden');
            });

            $(document).on('click', '.mnu-edit-btn', function() {
                let id = $(this).data('id');
                $('#mnu_modalTitle').text('Loading...');
                $('#mnu_modal').removeClass('hidden');
                showLoading();

                $.get(`/menus/${id}/edit`, function(data) {
                    $('#mnu_modalTitle').text('Edit Menu');
                    $('#mnu_id').val(data.id);
                    $('#mnu_menu_id').val(data.menu_id);
                    $('#mnu_menu_name').val(data.menu_name);
                    $('#mnu_menu_route').val(data.menu_route);
                    $('#mnu_menu_url').val(data.menu_url);
                    mnuSetIcon(data.menu_icon);
                    $('#mnu_menu_sort_order').val(data.menu_sort_order);
                    $('#mnu_application_id').val(data.application_id).trigger('change');
                    $('#mnu_screen_id').val(data.screen_id).trigger('change');
                    mnuRefreshParentOptions(data.menu_id);
                    $('#mnu_parent_menu_id').val(data.parent_menu_id).trigger('change');
                    hideLoading();
                }).fail(function(xhr) {
                    hideLoading();
                    $('#mnu_modal').addClass('hidden');
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load menu data' });
                    console.error(xhr.responseText);
                });
            });

            $('#mnu_closeBtn').on('click', function() {
                mnuResetForm();
                $('#mnu_modal').addClass('hidden');
            });

            $(document).on('change', '.mnu-toggle-status', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';
                const $cb = $(this);
                const doToggle = () => $.ajax({
                    url: `/menus/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { status: newStatus },
                    success: () => mnuLoadTree()
                });
                if (newStatus === 'X') {
                    const m = mnuTreeData.find(x => String(x.id) === String(id));
                    acsConfirmImpact('menu', m?.menu_id, m?.menu_name).then(ok => { if (ok) doToggle(); else $cb.prop('checked', true); });
                } else {
                    doToggle();
                }
            });

            $(document).on('click', '.mnu-tree-toggle', function() {
                const menuId = $(this).data('menu-id');
                if (mnuExpanded.has(menuId)) mnuExpanded.delete(menuId); else mnuExpanded.add(menuId);
                mnuRenderTree();
            });

            $('#mnu_expandAllBtn').on('click', function() {
                mnuTreeData.forEach(m => {
                    if (mnuTreeData.some(x => (x.parent_menu_id || null) === m.menu_id)) mnuExpanded.add(m.menu_id);
                });
                mnuRenderTree();
            });

            $('#mnu_collapseAllBtn').on('click', function() {
                mnuExpanded.clear();
                mnuRenderTree();
            });

            $('#mnu_searchInput').on('input', function() {
                mnuSearchTerm = $(this).val();
                mnuRenderTree();
            });

            $('#mnu_clearSearchBtn').on('click', function() {
                mnuSearchTerm = '';
                $('#mnu_searchInput').val('');
                mnuRenderTree();
            });

            function mnuIsDescendant(candidateId, ancestorId) {
                let current = mnuTreeData.find(m => m.menu_id === candidateId);
                while (current && current.parent_menu_id) {
                    if (current.parent_menu_id === ancestorId) return true;
                    current = mnuTreeData.find(m => m.menu_id === current.parent_menu_id);
                }
                return false;
            }

            $(document).on('dragstart', '.mnu-drag-handle', function(e) {
                mnuDraggedId = $(this).closest('li').data('menu-id');
                e.originalEvent.dataTransfer.effectAllowed = 'move';
                e.originalEvent.dataTransfer.setData('text/plain', mnuDraggedId);
            });

            $(document).on('dragend', '.mnu-drag-handle', function() {
                $('.mnu-tree-row').removeClass('drop-before drop-after drop-inside');
                mnuDraggedId = null;
            });

            $(document).on('dragover', '.mnu-tree-row', function(e) {
                if (!mnuDraggedId) return;
                const targetMenuId = $(this).closest('li').data('menu-id');
                $('.mnu-tree-row').not(this).removeClass('drop-before drop-after drop-inside');
                if (targetMenuId === mnuDraggedId || mnuIsDescendant(targetMenuId, mnuDraggedId)) {
                    $(this).removeClass('drop-before drop-after drop-inside');
                    return;
                }
                e.preventDefault();
                const rect = this.getBoundingClientRect();
                const ratio = (e.originalEvent.clientY - rect.top) / rect.height;
                $(this).removeClass('drop-before drop-after drop-inside');
                if (ratio < 0.25) $(this).addClass('drop-before');
                else if (ratio > 0.75) $(this).addClass('drop-after');
                else $(this).addClass('drop-inside');
            });

            $(document).on('dragleave', '.mnu-tree-row', function() {
                $(this).removeClass('drop-before drop-after drop-inside');
            });

            $(document).on('drop', '.mnu-tree-row', function(e) {
                e.preventDefault();
                const $row = $(this);
                const zone = $row.hasClass('drop-before') ? 'before' : $row.hasClass('drop-after') ? 'after' : $row.hasClass('drop-inside') ? 'inside' : null;
                $('.mnu-tree-row').removeClass('drop-before drop-after drop-inside');

                const targetMenuId = $row.closest('li').data('menu-id');
                const sourceMenuId = mnuDraggedId;
                mnuDraggedId = null;

                if (!zone || !sourceMenuId || targetMenuId === sourceMenuId || mnuIsDescendant(targetMenuId, sourceMenuId)) return;
                mnuMove(sourceMenuId, targetMenuId, zone);
            });

            function mnuMove(draggedId, targetId, zone) {
                const dragged = mnuTreeData.find(m => m.menu_id === draggedId);
                const target = mnuTreeData.find(m => m.menu_id === targetId);
                if (!dragged || !target) return;

                const newParentId = zone === 'inside' ? target.menu_id : (target.parent_menu_id || null);
                let siblings = mnuTreeData.filter(m => (m.parent_menu_id || null) === (newParentId || null) && m.menu_id !== draggedId)
                    .sort((a, b) => (a.menu_sort_order ?? 0) - (b.menu_sort_order ?? 0));

                if (zone === 'before') siblings.splice(siblings.findIndex(m => m.menu_id === targetId), 0, dragged);
                else if (zone === 'after') siblings.splice(siblings.findIndex(m => m.menu_id === targetId) + 1, 0, dragged);
                else siblings.push(dragged);

                const updates = [];
                siblings.forEach((m, idx) => {
                    const isDragged = m.menu_id === draggedId;
                    const parent = isDragged ? newParentId : (m.parent_menu_id || null);
                    const parentChanged = isDragged && parent !== (dragged.parent_menu_id || null);
                    if (m.menu_sort_order !== idx || parentChanged) updates.push({ menu: m, sort: idx, parent });
                });
                if (!updates.length) return;
                if (zone === 'inside') mnuExpanded.add(newParentId);

                $('#mnu_treeContainer').css('opacity', 0.5);
                Promise.all(updates.map(u => mnuSaveOrder(u.menu, u.sort, u.parent)))
                    .then(mnuLoadTree)
                    .catch(() => { Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save menu order' }); mnuLoadTree(); });
            }

            function mnuSaveOrder(menu, sortOrder, parentMenuId) {
                const fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('menu_id', menu.menu_id);
                fd.append('menu_name', menu.menu_name);
                fd.append('parent_menu_id', parentMenuId || '');
                fd.append('menu_route', menu.menu_route || '');
                fd.append('menu_url', menu.menu_url || '');
                fd.append('menu_icon', menu.menu_icon || '');
                fd.append('menu_sort_order', sortOrder);
                fd.append('screen_id', menu.screen_id || '');
                fd.append('application_id', menu.application_id || '');
                return $.ajax({ url: `/menus/${menu.id}`, type: 'POST', headers: { 'X-CSRF-TOKEN': STUDIO_CSRF }, data: fd, processData: false, contentType: false });
            }

            $('#mnu_form').on('submit', function(e) {
                e.preventDefault();
                let id = $('#mnu_id').val();
                let url = id ? `/menus/${id}` : "{{ route('menus.store') }}";
                let formData = new FormData(this);
                if (id) formData.append('_method', 'PUT');

                showLoading();
                $(this).find('button[type="submit"]').prop('disabled', true);

                $.ajax({
                    url: url, type: 'POST',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: formData, processData: false, contentType: false,
                    success: function(resp) {
                        hideLoading();
                        $('#mnu_form button[type="submit"]').prop('disabled', false);
                        $('#mnu_modal').addClass('hidden');
                        if (resp?.data?.menu_id) lastCreated.menu = resp.data.menu_id;
                        mnuLoadTree();
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
                    },
                    error: function(xhr) {
                        hideLoading();
                        $('#mnu_form button[type="submit"]').prop('disabled', false);
                        Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Save failed' });
                        console.error(xhr.responseText);
                    }
                });
            });

            mnuLoadTree();

            // ===== Step 4: Role =====
            studioMiniCrud({
                tableSel: '#rol_table', prefix: 'rol', jsonUrl: "{{ route('roles.json') }}",
                extraActions: (row) => `
                    <button type="button" class="acs-role-view rounded bg-violet-500 px-2 py-1 text-xs text-white hover:bg-violet-600" data-role="${row.role_id}" title="What can this role see?"><i class="fas fa-eye"></i></button>
                    <button type="button" class="acs-role-clone rounded bg-teal-500 px-2 py-1 text-xs text-white hover:bg-teal-600" data-role="${row.role_id}" data-name="${(row.role_name || '').replace(/"/g, '&quot;')}" title="Clone this role"><i class="fas fa-copy"></i></button>`,
                confirmDisable: (row) => acsConfirmImpact('role', row.role_id, row.role_name),
                columns: [{ data: 'role_id' }, { data: 'role_name' },
                    { data: 'user_count', className: 'text-center', render: (d) => `<span class="count-pill ${d ? 'count-pill-blue' : 'count-pill-zero'}" title="Active users with this role"><i class="fas fa-user"></i> ${d ?? 0}</span>` },
                    { data: 'menu_count', className: 'text-center', render: (d) => `<span class="count-pill ${d ? 'count-pill-violet' : 'count-pill-warn'}" title="${d ? 'Menus granted to this role' : 'No menus granted — this role sees an empty sidebar'}"><i class="fas fa-bars"></i> ${d ?? 0}</span>` },
                    statusCol],
                modalSel: '#rol_modal', formSel: '#rol_form', titleSel: '#rol_modalTitle', idSel: '#rol_id',
                addBtnSel: '#rol_addBtn', closeBtnSel: '#rol_closeBtn',
                addLabel: 'Add Role', editLabel: 'Edit Role',
                fields: ['rol_role_id', 'rol_role_name'],
                storeUrl: "{{ route('roles.store') }}",
                editUrl: (id) => `/roles/${id}/edit`,
                updateUrl: (id) => `/roles/${id}`,
                toggleUrl: (id) => `/roles/${id}/toggle-status`,
                onSaved: (resp) => { if (resp?.data?.role_id) lastCreated.role = resp.data.role_id; refreshRoleSelects(); }
            });

            acsLiveCount('#app_table', '#tile_app'); acsLiveCount('#scr_table', '#tile_scr'); acsLiveCount('#rol_table', '#tile_rol');

            // ===== Step 5: Role Menu matrix (grouped like the Menu tree) =====
            // Reads live from mnuTreeData (kept fresh by mnuLoadTree on every
            // menu add/edit/drag/toggle) instead of a static PHP-baked array,
            // so newly created menus show up here without a page reload.
            function rmChildrenOf(parentId) {
                return mnuTreeData
                    .filter(m => (m.parent_menu_id || null) === (parentId || null) && m.status === 'A')
                    .sort((a, b) => (a.menu_name || '').localeCompare(b.menu_name || ''));
            }

            // Baseline = what's saved in the DB; everything else is an unsaved change.
            let rmBase = new Set(), rmRole = '', rmFilterMode = 'all', rmAllFolded = false;
            const rmChecked = () => new Set($('.rm-chk:checked').map(function() { return $(this).val(); }).get());

            function rmRenderNode(m, checkedSet) {
                const children = rmChildrenOf(m.menu_id);
                const hasChildren = children.length > 0;
                const childrenHtml = hasChildren
                    ? `<div class="rm-kids">${children.map(c => rmRenderNode(c, checkedSet)).join('')}</div>`
                    : '';
                return `
                    <div data-menu-id="${esc(m.menu_id)}" class="rm-node">
                        <label class="rm-row" data-name="${esc((m.menu_name + ' ' + m.menu_id).toLowerCase())}" data-id="${esc(m.menu_id)}">
                            <input type="checkbox" class="rm-chk rm-cb" value="${esc(m.menu_id)}" ${checkedSet.has(m.menu_id) ? 'checked' : ''}>
                            <i class="fas ${hasChildren ? 'fa-folder' : 'fa-file-lines'} rm-ico"></i>
                            <span class="rm-name ${hasChildren ? 'rm-name-parent' : ''}">${esc(m.menu_name)}</span>
                            <span class="acs-mono">${esc(m.menu_id)}</span>
                            ${m.route_missing ? `<span class="acs-warn" style="margin-left:4px" title="Route '${esc(m.menu_route)}' is not defined — shown disabled in the sidebar"><i class="fas fa-link-slash"></i></span>` : ''}
                            <span class="rm-state"></span>
                            ${hasChildren ? `<button type="button" class="rm-fold" title="Collapse / expand"><i class="fas fa-chevron-down"></i></button>` : ''}
                        </label>
                        ${childrenHtml}
                    </div>`;
            }

            function renderRoleMenuMatrix(checkedIds) {
                const checkedSet = new Set(checkedIds);
                rmBase = new Set(checkedIds);
                const roots = rmChildrenOf(null);

                const html = roots.map(root => `
                    <section class="rm-group">
                        <header class="rm-group-head">
                            <input type="checkbox" class="rm-group-cb rm-cb" title="Grant / clear the whole group">
                            <button type="button" class="rm-group-fold" title="Collapse / expand group"><i class="fas fa-chevron-down"></i></button>
                            <span class="rm-group-title">${esc(root.menu_name)}</span>
                            <span class="rm-group-pill">0/0</span>
                            <span class="rm-group-bar"><i></i></span>
                        </header>
                        <div class="rm-group-body">${rmRenderNode(root, checkedSet)}</div>
                    </section>`).join('');

                $('#rm_grid').html(html || '<div class="acs-empty">No active menus yet.</div>').removeClass('hidden');
                $('#rm_empty').addClass('hidden');
                $('#rm_hint, #rm_summary').removeClass('hidden');
                $('#rm_actions').removeClass('hidden');
                $('#rm_search').val(''); rmFilterMode = 'all';
                $('#rm_filter button').removeClass('active').filter('[data-mode="all"]').addClass('active');
                rmRefresh();
            }

            // Recompute counts, tri-state checkboxes, progress and the unsaved-diff highlight.
            function rmRefresh() {
                const now = rmChecked();
                const total = $('.rm-chk').length;
                $('#rm_granted').text(now.size); $('#rm_total').text(total);
                $('#rm_bar').css('width', total ? (now.size / total * 100) + '%' : 0);

                let added = 0, removed = 0;
                $('.rm-row').each(function() {
                    const id = String($(this).data('id')), on = now.has(id), was = rmBase.has(id);
                    $(this).toggleClass('rm-added', on && !was).toggleClass('rm-removed', !on && was).toggleClass('rm-on', on);
                    $(this).find('.rm-state').text(on && !was ? 'will grant' : (!on && was ? 'will remove' : ''));
                    if (on && !was) added++;
                    if (!on && was) removed++;
                });
                $('.rm-group').each(function() {
                    const $c = $(this).find('.rm-chk'), n = $c.length, k = $c.filter(':checked').length;
                    $(this).find('.rm-group-pill').text(`${k}/${n}`).toggleClass('rm-pill-full', n > 0 && k === n);
                    $(this).find('.rm-group-bar i').css('width', n ? (k / n * 100) + '%' : 0);
                    const gcb = $(this).find('.rm-group-cb')[0];
                    gcb.checked = n > 0 && k === n; gcb.indeterminate = k > 0 && k < n;
                });
                const dirty = added || removed;
                $('#rm_dirtyChip').toggleClass('hidden', !dirty).html(dirty
                    ? `<span class="rm-d-add">+${added}</span> <span class="rm-d-del">−${removed}</span> unsaved` : '');
                $('#rm_reset').toggleClass('hidden', !dirty);
                $('#rm_save').toggleClass('rm-pulse', !!dirty);
                return !!dirty;
            }

            // Role switch — guard unsaved changes
            let rmSwitching = false;
            $('#rm_roleSelect').on('change', async function() {
                if (rmSwitching) return;
                const roleId = $(this).val();
                // Re-triggered by a role-list refresh for the same role: keep the grid (and unsaved ticks) as is
                if (roleId && roleId === rmRole && $('#rm_grid').children().length) return;
                if ($('#rm_grid').children().length && rmRefresh()) {
                    const r = await Swal.fire({ icon: 'question', title: 'Discard unsaved changes?', text: 'You have unsaved menu changes for the current role.', showCancelButton: true, confirmButtonText: 'Discard', cancelButtonText: 'Keep editing' });
                    if (!r.isConfirmed) { rmSwitching = true; $(this).val(rmRole).trigger('change.select2'); rmSwitching = false; return; }
                }
                rmRole = roleId;
                if (!roleId) {
                    $('#rm_grid').addClass('hidden').empty();
                    $('#rm_actions, #rm_hint, #rm_summary').addClass('hidden');
                    $('#rm_empty').removeClass('hidden');
                    return;
                }
                showLoading();
                $.get(`/role-menus/by-role/${roleId}`, function(data) {
                    hideLoading();
                    renderRoleMenuMatrix(data.menu_ids || []);
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load menus' }); });
            });

            // Search + Granted/Not-granted filter. A node stays visible if it matches, or any descendant does.
            function rmApplyFilter() {
                const q = ($('#rm_search').val() || '').toLowerCase().trim(), mode = rmFilterMode;
                const rowOk = ($row) => {
                    if (q && !String($row.data('name')).includes(q)) return false;
                    const on = $row.find('.rm-chk').prop('checked');
                    return mode === 'all' || (mode === 'granted' ? on : !on);
                };
                function walk($node) {
                    let match = rowOk($node.children('.rm-row').first());
                    $node.children('.rm-kids').children('[data-menu-id]').each(function() { if (walk($(this))) match = true; });
                    $node.toggle(match);
                    return match;
                }
                $('#rm_grid > .rm-group').each(function() {
                    let vis = false;
                    $(this).find('> .rm-group-body > [data-menu-id]').each(function() { if (walk($(this))) vis = true; });
                    $(this).toggle(vis);
                });
            }
            $('#rm_search').on('input', rmApplyFilter);
            $('#rm_filter').on('click', 'button', function() {
                $(this).addClass('active').siblings().removeClass('active');
                rmFilterMode = $(this).data('mode'); rmApplyFilter();
            });

            // Selection helpers
            $(document).on('change', '.rm-chk', rmRefresh);
            $(document).on('change', '.rm-group-cb', function() {
                $(this).closest('.rm-group').find('.rm-chk').prop('checked', this.checked); rmRefresh();
            });
            $('#rm_selectAll').on('click', () => { $('.rm-row:visible .rm-chk').prop('checked', true); rmRefresh(); });
            $('#rm_selectNone').on('click', () => { $('.rm-row:visible .rm-chk').prop('checked', false); rmRefresh(); });
            $('#rm_reset').on('click', () => { $('.rm-chk').each(function() { this.checked = rmBase.has($(this).val()); }); rmRefresh(); });

            // Collapse / expand
            $(document).on('click', '.rm-fold', function(e) {
                e.preventDefault(); e.stopPropagation();
                $(this).closest('.rm-node').toggleClass('rm-folded');
            });
            $(document).on('click', '.rm-group-fold', function() { $(this).closest('.rm-group').toggleClass('rm-folded'); });
            $('#rm_foldAll').on('click', function() {
                rmAllFolded = !rmAllFolded;
                $('.rm-group').toggleClass('rm-folded', rmAllFolded);
                $(this).find('i').attr('class', 'fas ' + (rmAllFolded ? 'fa-angles-up' : 'fa-angles-down'));
            });

            // Copy another role's menus in (as an unsaved change you can review/undo)
            $('#rm_copyFrom').on('change', async function() {
                const src = $(this).val(); $(this).val('');
                if (!src || !rmRole) return;
                if (src === rmRole) return Swal.fire({ icon: 'info', title: 'That is the same role', timer: 1400, showConfirmButton: false });
                const r = await Swal.fire({ icon: 'question', title: `Copy menus from ${src}?`, text: "Replace this role's selection, or add them on top of what's ticked? Nothing is saved until you press Save.",
                    showDenyButton: true, showCancelButton: true, confirmButtonText: 'Replace', denyButtonText: 'Add on top', confirmButtonColor: '#56b1f3', denyButtonColor: '#0ea5e9' });
                if (r.isDismissed) return;
                showLoading();
                $.get(`/role-menus/by-role/${encodeURIComponent(src)}`, function(data) {
                    hideLoading();
                    const ids = new Set(data.menu_ids || []);
                    $('.rm-chk').each(function() { if (ids.has($(this).val())) this.checked = true; else if (r.isConfirmed) this.checked = false; });
                    rmRefresh();
                    Swal.fire({ icon: 'success', title: 'Copied — review and press Save', timer: 1600, showConfirmButton: false });
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Failed to load that role' }); });
            });

            function rmSave() {
                const roleId = $('#rm_roleSelect').val();
                if (!roleId) return;
                const menuIds = [...rmChecked()];
                showLoading();
                $.ajax({
                    url: "{{ route('role_menus.save_by_role') }}", type: 'POST',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { role_id: roleId, menu_id: menuIds },
                    success: () => {
                        hideLoading(); rmBase = new Set(menuIds); rmRefresh();
                        $('#rol_table').DataTable().ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false });
                    },
                    error: () => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save' }); }
                });
            }
            $('#rm_save').on('click', rmSave);
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's' && $('.step-panel[data-panel="rolemenu"]').is(':visible') && $('#rm_roleSelect').val()) {
                    e.preventDefault(); rmSave();
                }
            });

            // ===== Step 6: Access Rights matrix (grouped by Application) =====
            const accessColumns = @json($accessNames);
            // arAppNames is reassigned by refreshApplicationSelects() after an
            // Application is saved/toggled, so keep it mutable.
            let arAppNames = @json($applications->pluck('application_name', 'application_id'));
            // Every Screen should have a Menu leaf pointing at it, but nothing
            // enforces that — screens created in Step 2 and never wired into a
            // Step 3 menu would otherwise silently vanish from this matrix (the
            // tree walk below only knows about menus). Keep a raw screen list
            // around so those "orphan" screens can still be shown and granted
            // rights. Reassigned by refreshScreenSelects() after a Screen is
            // saved/toggled, so keep it mutable.
            let arAllScreens = @json($screens);

            // Menus are a real parent/child tree (parent_menu_id), and only the
            // leaf nodes carry a screen_id — those are the actual "screens" that
            // access:SCREEN,ACTION middleware checks. Folder-only nodes (e.g. "Human
            // Resources" > "Recruitment" > "PRF") have screen_id = null and exist
            // purely to organize the sidebar; they must never become a checkable
            // row here, or the checkboxes save rights for a screen_id nothing
            // ever reads. We still walk the tree (not a flat filter) so folders
            // render as non-checkable sub-headers at their real depth instead of
            // looking like flat siblings of the screens they contain.
            function getMatrixTree() {
                return mnuTreeData.filter(m => m.status === 'A' && m.application_id);
            }

            // Screens that have no active menu leaf pointing at them (see the
            // comment on arAllScreens above) — grouped by application so they
            // still surface in their app's section instead of disappearing.
            function getOrphanScreensByApp() {
                const wiredScreenIds = new Set(
                    mnuTreeData.filter(m => m.status === 'A' && m.screen_id).map(m => m.screen_id)
                );
                const byApp = {};
                arAllScreens
                    .filter(s => s.status === 'A' && !wiredScreenIds.has(s.screen_id))
                    .forEach(s => {
                        const key = s.application_id || '(no application)';
                        (byApp[key] = byApp[key] || []).push(s);
                    });
                return byApp;
            }

            function arBuildOrphanRows(appId, screens, rights, accessColumns) {
                if (!screens.length) return '';
                const header = `<tr class="ar-orphan-header" data-app="${appId}">
                    <td colspan="${accessColumns.length + 1}" class="py-1 pr-3" style="padding-left:0.75rem">
                        <span class="ar-orphan-label">Not in any menu yet — add one in Step 3 to also show these in the sidebar</span>
                    </td>
                </tr>`;
                const rows = screens
                    .slice()
                    .sort((a, b) => (a.screen_name || '').localeCompare(b.screen_name || ''))
                    .map((s, i) => {
                        const key = s.screen_id + '|' + appId;
                        const checkedNames = new Set(rights[key] || []);
                        const cells = accessColumns.map(name => `
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" class="ar-chk rm-cb ar-cb" data-screen="${s.screen_id}" data-app="${appId}" data-name="${name}" ${checkedNames.has(name) ? 'checked' : ''}>
                            </td>`).join('');
                        return `<tr class="ar-row ar-orphan-row${i % 2 === 1 ? ' ar-row-alt' : ''} border-b dark:border-gray-700" data-search="${(s.screen_name + ' ' + s.screen_id).toLowerCase()}" data-app-group="${appId}" data-ancestors="">
                            <td class="whitespace-nowrap py-1.5 pr-3" style="padding-left:0.75rem">
                                <span class="ar-screen-name"><input type="checkbox" class="ar-row-cb rm-cb ar-cb" title="Toggle every action for this screen"> ${esc(s.screen_name)} <span class="acs-mono">${esc(s.screen_id)}</span></span>
                            </td>
                            ${cells}
                        </tr>`;
                    }).join('');
                return header + rows;
            }

            function arBuildAppRows(appId, appMenus, rights, accessColumns) {
                const byId = new Map(appMenus.map(m => [m.menu_id, m]));
                const childrenOf = (parentId) => appMenus
                    .filter(m => (m.parent_menu_id || null) === (parentId || null))
                    .sort((a, b) => (a.menu_sort_order ?? 0) - (b.menu_sort_order ?? 0) || a.menu_id.localeCompare(b.menu_id));
                // A node's parent may be null, or may point outside this app's
                // menu set (cross-app edge case) — either way it's a root here.
                const roots = appMenus.filter(m => !m.parent_menu_id || !byId.has(m.parent_menu_id));

                function hasLeafDescendant(m) {
                    if (m.screen_id) return true;
                    return childrenOf(m.menu_id).some(hasLeafDescendant);
                }

                let leafIndex = 0;
                function walk(nodes, depth, isRootLevel, ancestors) {
                    let rows = '';
                    nodes.forEach(m => {
                        if (!hasLeafDescendant(m)) return; // empty folder branch: nothing to show
                        const kids = childrenOf(m.menu_id);
                        let childAncestors = ancestors;
                        // A root node that is itself a screen (standalone top-level menu,
                        // e.g. Data Hub / Mailbox — no parent, no children) must still get
                        // a row: only root FOLDERS skip their own row, since the app-group
                        // header above already covers them.
                        if (!(isRootLevel && !m.screen_id)) {
                            const pad = (0.75 + depth * 1.15).toFixed(2) + 'rem';
                            if (m.screen_id) {
                                const key = m.menu_id + '|' + appId;
                                const checkedNames = new Set(rights[key] || []);
                                const cells = accessColumns.map(name => `
                                    <td class="px-3 py-1.5 text-center">
                                        <input type="checkbox" class="ar-chk rm-cb ar-cb" data-screen="${m.menu_id}" data-app="${appId}" data-name="${name}" ${checkedNames.has(name) ? 'checked' : ''}>
                                    </td>`).join('');
                                rows += `<tr class="ar-row${leafIndex % 2 === 1 ? ' ar-row-alt' : ''} border-b dark:border-gray-700" data-search="${(m.menu_name + ' ' + m.menu_id).toLowerCase()}" data-app-group="${appId}" data-ancestors="${ancestors.join(',')}">
                                    <td class="whitespace-nowrap py-1.5 pr-3" style="padding-left:${pad}">
                                        <span class="ar-screen-name"><input type="checkbox" class="ar-row-cb rm-cb ar-cb" title="Toggle every action for this screen"> ${esc(m.menu_name)} <span class="acs-mono">${esc(m.menu_id)}</span></span>
                                    </td>
                                    ${cells}
                                </tr>`;
                                leafIndex++;
                            } else {
                                rows += `<tr class="ar-folder-header" data-app="${appId}" data-folder="${m.menu_id}">
                                    <td colspan="${accessColumns.length + 1}" class="py-1 pr-3" style="padding-left:${pad}">
                                        <span class="ar-folder-label">${m.menu_name}</span>
                                    </td>
                                </tr>`;
                                childAncestors = ancestors.concat(m.menu_id);
                            }
                        }
                        if (kids.length) rows += walk(kids, isRootLevel ? 0 : depth + 1, false, childAncestors);
                    });
                    return rows;
                }

                return walk(roots, 0, true, []);
            }

            // Baseline = saved rights as "screen|app|ACTION" keys; anything else is an unsaved change.
            let arBase = new Set(), arRole = '';
            const arKey = ($c) => `${$c.data('screen')}|${$c.data('app')}|${$c.data('name')}`;
            const arCheckedKeys = () => new Set($('.ar-chk:checked').map(function() { return arKey($(this)); }).get());

            function renderAccessMatrix(rights) {
                const groups = {};
                getMatrixTree().forEach(m => {
                    const key = m.application_id || '(no application)';
                    (groups[key] = groups[key] || []).push(m);
                });

                const orphansByApp = getOrphanScreensByApp();

                const appIds = Array.from(new Set([...Object.keys(groups), ...Object.keys(orphansByApp)]))
                    .sort((a, b) => (arAppNames[a] || a).localeCompare(arAppNames[b] || b));

                const html = appIds.map(appId => {
                    const label = arAppNames[appId] ? `${arAppNames[appId]} (${appId})` : appId;

                    const headerRow = `
                        <tr class="ar-group-header" data-app="${esc(appId)}">
                            <td colspan="${accessColumns.length + 1}" class="px-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button" class="ar-group-fold" data-app="${esc(appId)}" title="Collapse / expand"><i class="fas fa-chevron-down"></i></button>
                                    <span class="ar-group-icon"><i class="fas fa-cubes" style="font-size:10px"></i></span>
                                    <span class="ar-group-label">${esc(label)}</span>
                                    <span class="ar-grp-pill rm-group-pill" data-app="${esc(appId)}">0/0</span>
                                    <span class="ml-auto flex items-center gap-1.5">
                                        <button type="button" class="ar-group-view ar-group-pill ar-group-pill-none" data-app="${esc(appId)}" title="Allow VIEW only for this application">Read-only</button>
                                        <button type="button" class="ar-group-all ar-group-pill ar-group-pill-all" data-app="${esc(appId)}">Full</button>
                                        <button type="button" class="ar-group-none ar-group-pill ar-group-pill-none" data-app="${esc(appId)}">None</button>
                                    </span>
                                </div>
                            </td>
                        </tr>`;

                    const screenRows = groups[appId] ? arBuildAppRows(appId, groups[appId], rights, accessColumns) : '';
                    const orphanRows = arBuildOrphanRows(appId, orphansByApp[appId] || [], rights, accessColumns);

                    return headerRow + screenRows + orphanRows;
                }).join('');

                $('#ar_tableBody').html(html);
                $('#ar_tableWrap').removeClass('hidden');
                $('#ar_empty').addClass('hidden');
                $('#ar_actions').removeClass('hidden');
                $('#ar_summary, #ar_hint').removeClass('hidden');
                $('#ar_search').val('');
                arBase = arCheckedKeys();
                arRefresh();
            }

            // Counts, per-action chips, tri-state row/group boxes and the unsaved-diff highlight.
            function arRefresh() {
                const now = arCheckedKeys();
                let added = 0, removed = 0;
                $('.ar-chk').each(function() {
                    const k = arKey($(this)), on = now.has(k), was = arBase.has(k);
                    $(this).toggleClass('ar-added', on && !was).toggleClass('ar-removed', !on && was);
                    if (on && !was) added++;
                    if (!on && was) removed++;
                });

                let withAccess = 0;
                const perApp = {};
                $('.ar-row').each(function() {
                    const $c = $(this).find('.ar-chk'), k = $c.filter(':checked').length, n = $c.length;
                    const rcb = $(this).find('.ar-row-cb')[0];
                    if (rcb) { rcb.checked = n > 0 && k === n; rcb.indeterminate = k > 0 && k < n; }
                    $(this).toggleClass('ar-row-on', k > 0);
                    if (k > 0) withAccess++;
                    const app = String($(this).data('app-group'));
                    (perApp[app] = perApp[app] || { on: 0, n: 0 });
                    perApp[app].n++; if (k > 0) perApp[app].on++;
                });
                $('.ar-grp-pill').each(function() {
                    const g = perApp[String($(this).data('app'))] || { on: 0, n: 0 };
                    $(this).text(`${g.on}/${g.n}`).toggleClass('rm-pill-full', g.n > 0 && g.on === g.n);
                });
                $('#ar_screens').text(withAccess); $('#ar_totalScreens').text($('.ar-row').length);

                const chips = accessColumns.map(name => {
                    const n = $(`.ar-chk[data-name="${name}"]:checked`).length;
                    $(`.ar-col-header[data-name="${name}"] .ar-col-n`).text(n || '');
                    return `<span class="acs-act acs-act-${esc(name).toLowerCase()}" style="opacity:${n ? 1 : .45}">${esc(name)} · ${n}</span>`;
                }).join('');
                $('#ar_chips').html(chips);

                const dirty = added || removed;
                $('#ar_dirtyChip').toggleClass('hidden', !dirty).html(dirty
                    ? `<span class="rm-d-add">+${added}</span> <span class="rm-d-del">−${removed}</span> unsaved` : '');
                $('#ar_reset').toggleClass('hidden', !dirty);
                $('#ar_save').toggleClass('rm-pulse', !!dirty);
                return !!dirty;
            }

            const arVisibleChecks = (extra = '') => $('.ar-row').filter(function() { return this.style.display !== 'none' && !$(this).hasClass('ar-fold-hide'); }).find('.ar-chk' + extra);
            const arSetPreset = ($scopeRows, mode) => {
                $scopeRows.find('.ar-chk').each(function() {
                    const isView = String($(this).data('name')).toUpperCase() === 'VIEW';
                    this.checked = mode === 'full' ? true : (mode === 'view' ? isView : false);
                });
                arRefresh();
            };

            // Group level actions + folding
            $(document).on('click', '.ar-group-all', function() { arSetPreset($(`.ar-row[data-app-group="${$(this).data('app')}"]`), 'full'); });
            $(document).on('click', '.ar-group-view', function() { arSetPreset($(`.ar-row[data-app-group="${$(this).data('app')}"]`), 'view'); });
            $(document).on('click', '.ar-group-none', function() { arSetPreset($(`.ar-row[data-app-group="${$(this).data('app')}"]`), 'none'); });
            $(document).on('click', '.ar-group-fold', function() {
                const app = $(this).data('app'), folded = !$(this).closest('tr').hasClass('ar-folded');
                $(this).closest('tr').toggleClass('ar-folded', folded);
                $(`.ar-row[data-app-group="${app}"], .ar-folder-header[data-app="${app}"], .ar-orphan-header[data-app="${app}"]`).toggleClass('ar-fold-hide', folded);
            });

            // Cell, row and column toggles
            $(document).on('change', '.ar-chk', arRefresh);
            $(document).on('change', '.ar-row-cb', function() {
                $(this).closest('tr').find('.ar-chk').prop('checked', this.checked); arRefresh();
            });
            $(document).on('click', '.ar-col-header', function() {
                const name = $(this).data('name');
                const $boxes = arVisibleChecks(`[data-name="${name}"]`);
                if (!$boxes.length) return;
                const allChecked = $boxes.filter(':checked').length === $boxes.length;
                $boxes.prop('checked', !allChecked); arRefresh();
            });
            $('#ar_presetView').on('click', () => arSetPreset($('.ar-row').filter(function() { return this.style.display !== 'none'; }), 'view'));
            $('#ar_presetFull').on('click', () => arSetPreset($('.ar-row').filter(function() { return this.style.display !== 'none'; }), 'full'));
            $('#ar_selectNone').on('click', () => arSetPreset($('.ar-row').filter(function() { return this.style.display !== 'none'; }), 'none'));
            $('#ar_reset').on('click', () => { $('.ar-chk').each(function() { this.checked = arBase.has(arKey($(this))); }); arRefresh(); });

            // Role switch — guard unsaved changes
            let arSwitching = false;
            $('#ar_roleSelect').on('change', async function() {
                if (arSwitching) return;
                const roleId = $(this).val();
                // Re-triggered by a role-list refresh for the same role: keep the grid (and unsaved ticks) as is
                if (roleId && roleId === arRole && $('#ar_tableBody').children().length) return;
                if ($('#ar_tableBody').children().length && arRefresh()) {
                    const r = await Swal.fire({ icon: 'question', title: 'Discard unsaved changes?', text: 'You have unsaved access-right changes for the current role.', showCancelButton: true, confirmButtonText: 'Discard', cancelButtonText: 'Keep editing' });
                    if (!r.isConfirmed) { arSwitching = true; $(this).val(arRole).trigger('change.select2'); arSwitching = false; return; }
                }
                arRole = roleId;
                if (!roleId) {
                    $('#ar_tableWrap').addClass('hidden');
                    $('#ar_tableBody').empty();
                    $('#ar_actions, #ar_summary, #ar_hint').addClass('hidden');
                    $('#ar_empty').removeClass('hidden');
                    return;
                }
                showLoading();
                $.get(`/access-rights/by-role/${roleId}`, function(data) {
                    hideLoading();
                    renderAccessMatrix(data.rights || {});
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load access rights' }); });
            });

            // Search: rows by name/id; folders, orphan headers and app headers follow their rows.
            // (Uses the inline display flag so collapsed groups don't make headers disappear.)
            $('#ar_search').on('input', function() {
                const q = $(this).val().toLowerCase().trim();
                const shown = (el) => el.style.display !== 'none';
                $('.ar-row').each(function() { $(this).toggle(!q || String($(this).data('search')).includes(q)); });
                $('.ar-folder-header').each(function() {
                    const folder = String($(this).data('folder')), app = $(this).data('app');
                    const any = $(`.ar-row[data-app-group="${app}"]`).filter(function() { return shown(this) && String($(this).data('ancestors') || '').split(',').includes(folder); }).length > 0;
                    $(this).toggle(any);
                });
                $('.ar-orphan-header').each(function() {
                    const app = $(this).data('app');
                    $(this).toggle($(`.ar-orphan-row[data-app-group="${app}"]`).filter(function() { return shown(this); }).length > 0);
                });
                $('.ar-group-header').each(function() {
                    const app = $(this).data('app');
                    $(this).toggle($(`.ar-row[data-app-group="${app}"]`).filter(function() { return shown(this); }).length > 0);
                });
            });

            // Copy another role's rights in (as an unsaved change you can review/undo)
            $('#ar_copyFrom').on('change', async function() {
                const src = $(this).val(); $(this).val('');
                if (!src) return;
                if (!arRole) return Swal.fire({ icon: 'warning', title: 'Pick a role to edit first', text: 'Select the Role at the top before copying rights into it.' });
                if (src === arRole) return Swal.fire({ icon: 'info', title: 'That is the same role', timer: 1400, showConfirmButton: false });
                const r = await Swal.fire({ icon: 'question', title: `Copy rights from ${src}?`, text: "Replace this role's rights, or add them on top of what's ticked? Nothing is saved until you press Save.",
                    showDenyButton: true, showCancelButton: true, confirmButtonText: 'Replace', denyButtonText: 'Add on top', confirmButtonColor: '#f43f5e', denyButtonColor: '#0ea5e9' });
                if (r.isDismissed) return;
                showLoading();
                $.get(`/access-rights/by-role/${encodeURIComponent(src)}`, function(data) {
                    hideLoading();
                    const keys = new Set();
                    Object.entries(data.rights || {}).forEach(([k, names]) => (names || []).forEach(n => keys.add(`${k}|${String(n).toUpperCase()}`)));
                    $('.ar-chk').each(function() {
                        if (keys.has(arKey($(this)))) this.checked = true; else if (r.isConfirmed) this.checked = false;
                    });
                    arRefresh();
                    Swal.fire({ icon: 'success', title: 'Copied — review and press Save', timer: 1600, showConfirmButton: false });
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Failed to load that role' }); });
            });

            function arSave() {
                const roleId = $('#ar_roleSelect').val();
                if (!roleId) return;
                const byScreen = {};
                $('.ar-chk:checked').each(function() {
                    const $chk = $(this);
                    const key = $chk.data('screen') + '|' + $chk.data('app');
                    if (!byScreen[key]) byScreen[key] = { screen_id: String($chk.data('screen')), application_id: String($chk.data('app')), access_names: [] };
                    byScreen[key].access_names.push(String($chk.data('name')));
                });
                showLoading();
                $.ajax({
                    url: "{{ route('access_rights.save_by_role') }}", type: 'POST',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { role_id: roleId, rights: Object.values(byScreen) },
                    success: () => { hideLoading(); arBase = arCheckedKeys(); arRefresh(); Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }); },
                    error: () => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save' }); }
                });
            }
            $('#ar_save').on('click', arSave);
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's' && $('.step-panel[data-panel="accessrights"]').is(':visible') && $('#ar_roleSelect').val()) {
                    e.preventDefault(); arSave();
                }
            });

            // ===== Stepper navigation =====
            const order = ['application', 'screen', 'menu', 'role', 'rolemenu', 'accessrights'];

            function goToStep(step) {
                $('.step-panel').addClass('hidden');
                $(`.step-panel[data-panel="${step}"]`).removeClass('hidden');

                const idx = order.indexOf(step);
                $('.step-node').each(function(i) {
                    $(this).toggleClass('is-active', i === idx).toggleClass('is-done', i < idx);
                });
                $('.step-connector').each(function(i) {
                    $(this).toggleClass('is-done', i < idx);
                });

                $('html, body').animate({ scrollTop: 0 }, 150);
            }

            $('.step-node').on('click', function() {
                goToStep($(this).data('step'));
            });
            $('.studio-next-btn').on('click', function() {
                goToStep($(this).data('next'));
            });
            $('.studio-back-btn').on('click', function() {
                goToStep($(this).data('back'));
            });

            goToStep('application');
        });
    </script>
</x-app-layout>
