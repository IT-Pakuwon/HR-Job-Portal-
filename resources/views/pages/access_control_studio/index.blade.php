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
        {{-- Stepper --}}
        <div class="mb-4 overflow-x-auto rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
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
        <div id="gaps_panel" class="mb-4 rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <button type="button" id="gaps_toggle" class="flex w-full items-center justify-between gap-3 px-5 py-3.5 text-left">
                <div class="flex items-center gap-2.5">
                    <span id="gaps_statusIcon" class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-700">
                        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9" opacity=".25"/><path d="M21 12a9 9 0 00-9-9"/></svg>
                    </span>
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">Setup Gaps</span>
                    <span id="gaps_summary" class="text-xs text-gray-400">Checking...</span>
                </div>
                <svg id="gaps_chevron" class="h-4 w-4 shrink-0 text-gray-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
            </button>
            <div id="gaps_body" class="hidden border-t border-gray-100 px-5 py-4 dark:border-gray-700">
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
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
                                <th class="w-24 px-4 py-3 text-center">Actions</th>
                                <th class="px-4 py-3 text-left">Role ID</th>
                                <th class="px-4 py-3 text-left">Name</th>
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
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[260px] flex-1">
                        <label class="studio-label">Role</label>
                        <select id="rm_roleSelect" class="studio-select2 w-full">
                            <option value="">-- Select a Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="rm_actions" class="hidden flex flex-wrap items-center gap-2">
                        <input id="rm_search" type="text" placeholder="Search menu..." class="studio-input !w-auto">
                        <button type="button" id="rm_selectAll" class="studio-mini-btn">Select All</button>
                        <button type="button" id="rm_selectNone" class="studio-mini-btn">Select None</button>
                        <button type="button" id="rm_save" class="studio-add-btn">{!! $icon('check', 'h-4 w-4') !!} Save Changes</button>
                    </div>
                </div>
                <div id="rm_empty" class="studio-empty">Pick a role above to manage its menus.</div>
                <p id="rm_hint" class="hidden text-xs text-gray-400 dark:text-gray-500">Grouped by top-level menu, matching the Menu step's tree.</p>
                <div id="rm_grid" class="hidden max-h-[30rem] grid grid-cols-1 gap-3 overflow-y-auto rounded-xl border border-gray-100 p-3 dark:border-gray-700 lg:grid-cols-2"></div>
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
                <div class="flex flex-wrap items-end gap-3">
                    <div class="min-w-[260px] flex-1">
                        <label class="studio-label">Role</label>
                        <select id="ar_roleSelect" class="studio-select2 w-full">
                            <option value="">-- Select a Role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="min-w-[220px]">
                        <label class="studio-label">Copy rights from...</label>
                        <select id="ar_copyFrom" class="studio-select2 w-full">
                            <option value="">-- optional, start blank --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->role_id }}">{{ $r->role_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="ar_actions" class="hidden flex flex-wrap items-center gap-2">
                        <input id="ar_search" type="text" placeholder="Search screen..." class="studio-input !w-auto">
                        <button type="button" id="ar_selectAll" class="studio-mini-btn">Select All</button>
                        <button type="button" id="ar_selectNone" class="studio-mini-btn">Select None</button>
                        <button type="button" id="ar_save" class="studio-add-btn">{!! $icon('check', 'h-4 w-4') !!} Save Changes</button>
                    </div>
                </div>
                <div id="ar_empty" class="studio-empty">Pick a role above to manage its access rights.</div>
                <p class="text-xs text-gray-400 dark:text-gray-500">Grouped by Application. Click a column header (e.g. VIEW) to toggle it for every screen currently shown.</p>
                <div id="ar_tableWrap" class="hidden max-h-[30rem] overflow-y-auto rounded-xl border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="studio-thead">
                                <th class="px-3 py-2.5 text-left">Screen</th>
                                @foreach ($accessNames as $an)
                                    <th class="ar-col-header cursor-pointer select-none px-3 py-2.5 text-center hover:text-indigo-600 dark:hover:text-indigo-400"
                                        data-name="{{ $an }}" title="Click to toggle this column">{{ $an }}</th>
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
                        <input type="text" id="mnu_menu_route" name="menu_route" class="studio-input">
                    </div>
                    <div>
                        <label class="studio-label">URL</label>
                        <input type="text" id="mnu_menu_url" name="menu_url" class="studio-input">
                    </div>
                </div>
                <div class="mb-1 grid grid-cols-2 gap-3">
                    <div>
                        <label class="studio-label">Icon (optional)</label>
                        <input type="text" id="mnu_menu_icon" name="menu_icon" class="studio-input">
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
            background-image: linear-gradient(135deg, #4f46e5, #4338ca); padding: 0.55rem 1.25rem; font-size: 0.85rem;
            font-weight: 600; color: #fff; box-shadow: 0 1px 3px rgba(79,70,229,.35); transition: filter .15s, transform .1s;
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
        .studio-next-btn { background-color: #eef2ff; color: #4338ca; border-color: #c7d2fe; }
        .studio-next-btn:hover { background-color: #e0e7ff; }
        .dark .studio-back-btn { border-color: #4b5563; color: #e5e7eb; }
        .dark .studio-next-btn { background-color: rgba(79,70,229,0.15); color: #a5b4fc; border-color: rgba(99,102,241,0.4); }
        .studio-save-btn { border-radius: 0.6rem; background: #4f46e5; padding: 0.55rem 1.35rem; font-size: 0.85rem; font-weight: 600; color: #fff; }
        .studio-save-btn:hover { background: #4338ca; }
        .studio-cancel-btn { border-radius: 0.6rem; border: 1px solid #d1d5db; padding: 0.55rem 1.1rem; font-size: 0.85rem; font-weight: 600; color: #374151; }
        .studio-cancel-btn:hover { background: #f3f4f6; }
        .dark .studio-cancel-btn { border-color: #4b5563; color: #e5e7eb; }
        .dark .studio-cancel-btn:hover { background: #374151; }

        /* ---- inputs ---- */
        .studio-input { width: 100%; border-radius: 0.6rem; border: 1px solid #d1d5db; padding: 0.55rem 0.75rem; font-size: 0.875rem; }
        .studio-input:focus { outline: none; border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
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
        .ar-group-header td { background-color: #f8fafc; }
        .dark .ar-group-header td { background-color: #111827; }

        /* ---- stepper ---- */
        .step-circle svg { width: 1.15rem; height: 1.15rem; }
        .step-circle-check { display: none; }
        .step-node.is-active .step-circle {
            border-color: #4f46e5; background-color: #4f46e5; color: #fff; box-shadow: 0 0 0 4px rgba(79,70,229,.15);
        }
        .step-node.is-active .step-label { color: #4338ca; }
        .dark .step-node.is-active .step-label { color: #a5b4fc; }
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
        .dark .select2-results__option--highlighted { background-color: #4f46e5 !important; }

        /* ---- menu tree (step 3) ---- */
        .mnu-tree-container { min-height: 4rem; }
        .mnu-drag-handle { cursor: grab; }
        .mnu-drag-handle:active { cursor: grabbing; }
        .mnu-tree-row { transition: background-color .12s; }
        .mnu-tree-row:hover { background-color: rgba(99, 102, 241, .06); }
        .mnu-tree-row.drop-before { box-shadow: inset 0 2px 0 0 #6366f1; }
        .mnu-tree-row.drop-after { box-shadow: inset 0 -2px 0 0 #6366f1; }
        .mnu-tree-row.drop-inside { background-color: rgba(99, 102, 241, .15); }
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
                $.ajax({
                    url: cfg.toggleUrl(id),
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { status: newStatus },
                    success: () => { dt.ajax.reload(null, false); if (cfg.onSaved) cfg.onSaved(); }
                });
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
                    const total = data.menus_missing_screen.length + data.screens_without_route.length + data.unregistered_screen_ids.length;

                    $('#gaps_statusIcon')
                        .removeClass('bg-gray-100 text-gray-400 dark:bg-gray-700 bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300 bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300')
                        .addClass(total
                            ? 'bg-amber-100 text-amber-600 dark:bg-amber-900/30 dark:text-amber-300'
                            : 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300')
                        .html(total
                            ? '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>'
                            : '<svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg>');

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

                    gapsRenderList('#gaps_screensWithRoute', data.screens_with_route, (s) =>
                        `<li>
                            <span class="min-w-0 flex-1 truncate text-gray-600 dark:text-gray-300">${s.screen_name}</span>
                            <span class="gaps-id-badge">${s.screen_id}</span>
                            <span class="shrink-0 text-xs text-green-600 dark:text-green-400">${s.actions.join(', ') || 'mapped'}</span>
                        </li>`);
                });
            }

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
            }

            function refreshRoleSelects() {
                const url = "{{ route('roles.json') }}";
                ['#rm_roleSelect', '#ar_roleSelect', '#ar_copyFrom'].forEach(sel => {
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
                                    </div>
                                    <div class="flex items-center space-x-1.5">
                                        <label class="switch" title="${isActive ? 'Active' : 'Inactive'}">
                                            <input type="checkbox" class="mnu-toggle-status" data-id="${m.id}" ${isActive ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button type="button" title="Edit menu" class="mnu-edit-btn flex h-7 w-7 items-center justify-center rounded bg-blue-500 text-sm text-white hover:bg-blue-600" data-id="${m.id}">
                                            <i class="fas fa-edit"></i>
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

            function mnuResetForm() {
                $('#mnu_form')[0].reset();
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
                    $('#mnu_menu_icon').val(data.menu_icon);
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
                $.ajax({
                    url: `/menus/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { status: newStatus },
                    success: () => mnuLoadTree()
                });
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
                columns: [{ data: 'role_id' }, { data: 'role_name' }, statusCol],
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

            // ===== Step 5: Role Menu matrix (grouped like the Menu tree) =====
            // Reads live from mnuTreeData (kept fresh by mnuLoadTree on every
            // menu add/edit/drag/toggle) instead of a static PHP-baked array,
            // so newly created menus show up here without a page reload.
            function rmChildrenOf(parentId) {
                return mnuTreeData
                    .filter(m => (m.parent_menu_id || null) === (parentId || null) && m.status === 'A')
                    .sort((a, b) => (a.menu_name || '').localeCompare(b.menu_name || ''));
            }

            function rmRenderNode(m, checkedSet) {
                const children = rmChildrenOf(m.menu_id);
                const hasChildren = children.length > 0;
                const childrenHtml = hasChildren
                    ? `<div class="ml-4 border-l border-gray-100 pl-2 dark:border-gray-700">${children.map(c => rmRenderNode(c, checkedSet)).join('')}</div>`
                    : '';

                return `
                    <div data-menu-id="${m.menu_id}">
                        <label class="rm-row flex items-center gap-2 rounded-lg px-2 py-1 text-sm hover:bg-gray-50 dark:hover:bg-gray-700/60"
                               data-name="${(m.menu_name + ' ' + m.menu_id).toLowerCase()}">
                            <input type="checkbox" class="rm-chk h-4 w-4 shrink-0" value="${m.menu_id}" ${checkedSet.has(m.menu_id) ? 'checked' : ''}>
                            <span class="${hasChildren ? 'font-semibold text-gray-800 dark:text-gray-100' : 'text-gray-600 dark:text-gray-300'}">${m.menu_name}</span>
                            <span class="text-xs text-gray-400">(${m.menu_id})</span>
                            ${hasChildren ? `<span class="ml-auto text-[10px] text-gray-400">${children.length} sub</span>` : ''}
                        </label>
                        ${childrenHtml}
                    </div>`;
            }

            function renderRoleMenuMatrix(checkedIds) {
                const checkedSet = new Set(checkedIds);
                const roots = rmChildrenOf(null);

                const html = roots.map(root => `
                    <div class="rm-group overflow-hidden rounded-xl border border-gray-100 dark:border-gray-700">
                        <div class="flex items-center justify-end gap-2 border-b border-gray-100 bg-gray-50 px-2 py-1 dark:border-gray-700 dark:bg-gray-900/40">
                            <button type="button" class="rm-group-all text-[10px] font-semibold text-indigo-600 hover:underline dark:text-indigo-400">select all</button>
                            <span class="text-gray-300 dark:text-gray-600">·</span>
                            <button type="button" class="rm-group-none text-[10px] font-semibold text-gray-400 hover:underline">none</button>
                        </div>
                        <div class="p-1.5">${rmRenderNode(root, checkedSet)}</div>
                    </div>`).join('');

                $('#rm_grid').html(html).removeClass('hidden');
                $('#rm_empty').addClass('hidden');
                $('#rm_hint').removeClass('hidden');
                $('#rm_actions').removeClass('hidden');
            }

            $('#rm_roleSelect').on('change', function() {
                const roleId = $(this).val();
                if (!roleId) {
                    $('#rm_grid').addClass('hidden').empty();
                    $('#rm_actions, #rm_hint').addClass('hidden');
                    $('#rm_empty').removeClass('hidden');
                    return;
                }
                showLoading();
                $.get(`/role-menus/by-role/${roleId}`, function(data) {
                    hideLoading();
                    renderRoleMenuMatrix(data.menu_ids || []);
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load menus' }); });
            });

            // A node stays visible if it matches, or any descendant matches.
            function rmApplyFilter(q) {
                function walk($node) {
                    const $row = $node.children('.rm-row').first();
                    let match = ($row.data('name') || '').includes(q);
                    $node.children('div').children('[data-menu-id]').each(function() {
                        if (walk($(this))) match = true;
                    });
                    $node.toggle(match);
                    return match;
                }
                $('#rm_grid > .rm-group').each(function() {
                    const $group = $(this);
                    let groupVisible = false;
                    $group.find('> div > [data-menu-id]').each(function() {
                        if (walk($(this))) groupVisible = true;
                    });
                    $group.toggle(groupVisible);
                });
            }

            $('#rm_search').on('input', function() {
                rmApplyFilter($(this).val().toLowerCase());
            });
            $('#rm_selectAll').on('click', () => $('.rm-row:visible .rm-chk').prop('checked', true));
            $('#rm_selectNone').on('click', () => $('.rm-row:visible .rm-chk').prop('checked', false));

            $(document).on('click', '.rm-group-all', function() {
                $(this).closest('.rm-group').find('.rm-chk').prop('checked', true);
            });
            $(document).on('click', '.rm-group-none', function() {
                $(this).closest('.rm-group').find('.rm-chk').prop('checked', false);
            });

            $('#rm_save').on('click', function() {
                const roleId = $('#rm_roleSelect').val();
                if (!roleId) return;
                const menuIds = $('.rm-chk:checked').map(function() { return $(this).val(); }).get();
                showLoading();
                $.ajax({
                    url: "{{ route('role_menus.save_by_role') }}", type: 'POST',
                    headers: { 'X-CSRF-TOKEN': STUDIO_CSRF },
                    data: { role_id: roleId, menu_id: menuIds },
                    success: () => { hideLoading(); Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }); },
                    error: () => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save' }); }
                });
            });

            // ===== Step 6: Access Rights matrix (grouped by Application) =====
            const accessColumns = @json($accessNames);
            // arAppNames is reassigned by refreshApplicationSelects() after an
            // Application is saved/toggled, so keep it mutable.
            let arAppNames = @json($applications->pluck('application_name', 'application_id'));

            // Same live source as the Role Menu matrix: menus with an
            // application_id assigned act as the "screens" row-set here, read
            // straight from mnuTreeData so new/edited menus show up without a
            // page reload.
            function getMatrixScreens() {
                return mnuTreeData
                    .filter(m => m.status === 'A' && m.application_id)
                    .map(m => ({ menu_id: m.menu_id, menu_name: m.menu_name, application_id: m.application_id }));
            }

            function renderAccessMatrix(rights) {
                const groups = {};
                getMatrixScreens().forEach(s => {
                    const key = s.application_id || '(no application)';
                    (groups[key] = groups[key] || []).push(s);
                });

                const appIds = Object.keys(groups).sort((a, b) =>
                    (arAppNames[a] || a).localeCompare(arAppNames[b] || b));

                const html = appIds.map(appId => {
                    const label = arAppNames[appId] ? `${arAppNames[appId]} (${appId})` : appId;

                    const headerRow = `
                        <tr class="ar-group-header" data-app="${appId}">
                            <td colspan="${accessColumns.length + 1}" class="px-3 py-1.5 text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                <div class="flex items-center gap-2">
                                    <span>${label}</span>
                                    <button type="button" class="ar-group-all text-[10px] font-semibold normal-case text-indigo-600 hover:underline dark:text-indigo-400" data-app="${appId}">select all</button>
                                    <button type="button" class="ar-group-none text-[10px] font-semibold normal-case text-gray-400 hover:underline" data-app="${appId}">none</button>
                                </div>
                            </td>
                        </tr>`;

                    const screenRows = groups[appId].map(s => {
                        const key = s.menu_id + '|' + s.application_id;
                        const checkedNames = new Set(rights[key] || []);
                        const cells = accessColumns.map(name => `
                            <td class="px-3 py-1.5 text-center">
                                <input type="checkbox" class="ar-chk h-4 w-4" data-screen="${s.menu_id}" data-app="${s.application_id}" data-name="${name}" ${checkedNames.has(name) ? 'checked' : ''}>
                            </td>`).join('');
                        return `<tr class="ar-row border-b dark:border-gray-700" data-search="${(s.menu_name + ' ' + s.menu_id).toLowerCase()}" data-app-group="${appId}">
                            <td class="whitespace-nowrap py-1.5 pl-7 pr-3">${s.menu_name} <span class="text-gray-400">(${s.menu_id})</span></td>
                            ${cells}
                        </tr>`;
                    }).join('');

                    return headerRow + screenRows;
                }).join('');

                $('#ar_tableBody').html(html);
                $('#ar_tableWrap').removeClass('hidden');
                $('#ar_empty').addClass('hidden');
                $('#ar_actions').removeClass('hidden');
            }

            $(document).on('click', '.ar-group-all', function() {
                $(`.ar-row[data-app-group="${$(this).data('app')}"] .ar-chk`).prop('checked', true);
            });
            $(document).on('click', '.ar-group-none', function() {
                $(`.ar-row[data-app-group="${$(this).data('app')}"] .ar-chk`).prop('checked', false);
            });

            $('#ar_roleSelect').on('change', function() {
                const roleId = $(this).val();
                if (!roleId) {
                    $('#ar_tableWrap').addClass('hidden');
                    $('#ar_tableBody').empty();
                    $('#ar_actions').addClass('hidden');
                    $('#ar_empty').removeClass('hidden');
                    return;
                }
                showLoading();
                $.get(`/access-rights/by-role/${roleId}`, function(data) {
                    hideLoading();
                    renderAccessMatrix(data.rights || {});
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load access rights' }); });
            });

            $('#ar_search').on('input', function() {
                const q = $(this).val().toLowerCase();
                $('.ar-row').each(function() { $(this).toggle($(this).data('search').includes(q)); });
                $('.ar-group-header').each(function() {
                    const app = $(this).data('app');
                    $(this).toggle($(`.ar-row[data-app-group="${app}"]:visible`).length > 0);
                });
            });

            $('#ar_selectAll').on('click', () => $('.ar-row:visible .ar-chk').prop('checked', true));
            $('#ar_selectNone').on('click', () => $('.ar-row:visible .ar-chk').prop('checked', false));

            $(document).on('click', '.ar-col-header', function() {
                const name = $(this).data('name');
                const $boxes = $(`.ar-row:visible .ar-chk[data-name="${name}"]`);
                if (!$boxes.length) return;
                const allChecked = $boxes.filter(':checked').length === $boxes.length;
                $boxes.prop('checked', !allChecked);
            });

            $('#ar_copyFrom').on('change', function() {
                const sourceRoleId = $(this).val();
                const targetRoleId = $('#ar_roleSelect').val();
                if (!sourceRoleId) return;

                if (!targetRoleId) {
                    Swal.fire({ icon: 'warning', title: 'Pick a role to edit first', text: 'Select the Role at the top before copying rights into it.' });
                    $(this).val(null).trigger('change');
                    return;
                }

                showLoading();
                $.get(`/access-rights/by-role/${sourceRoleId}`, function(data) {
                    hideLoading();
                    renderAccessMatrix(data.rights || {});
                    $('#ar_copyFrom').val(null).trigger('change');
                    Swal.fire({ icon: 'info', title: 'Copied', text: 'Review the checkboxes, then click Save Changes to apply them to the selected role.', timer: 2200, showConfirmButton: false });
                }).fail(() => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to load that role\'s rights' }); });
            });

            $('#ar_save').on('click', function() {
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
                    success: () => { hideLoading(); Swal.fire({ icon: 'success', title: 'Saved', timer: 1200, showConfirmButton: false }); },
                    error: () => { hideLoading(); Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to save' }); }
                });
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
