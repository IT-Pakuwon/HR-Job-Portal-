{{--
    GM Report Filter Pill
    Usage: <x-gm-report.filter-pill />
    JS controllers: gm-core.js, gm-filter.js (must be loaded on the page)
--}}

{{-- ── Global styles for GM filter UI — emitted only once per page ─────────── --}}
@once
<style>
    /* Refresh pulse dot */
    @keyframes gm-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: .4; }
    }

    /* Active preset button */
    .gmPreset.is-active {
        border-color:     #8B5CF6 !important;
        background-color: #EDE9FE !important;
        color:            #7C3AED !important;
    }

    /* Department list items */
    .gm-dept-item {
        display:     flex;
        align-items: center;
        gap:         8px;
        padding:     6px 8px;
        border-radius: 10px;
        cursor:      pointer;
        transition:  background 0.12s;
    }
    .gm-dept-item:hover { background: rgba(139, 92, 246, 0.08); }
    .gm-dept-item input[type="checkbox"] {
        width: 14px; height: 14px;
        accent-color: #8B5CF6;
        cursor: pointer; flex-shrink: 0;
    }
    .gm-dept-item .dn {
        flex: 1; font-size: 11px; font-weight: 600;
        color: #374151; line-height: 1.3;
    }
    .gm-dept-item .di { font-size: 10px; font-family: monospace; color: #94A3B8; }

    /* Scrollbar in dept list */
    .gm-dept-scroll::-webkit-scrollbar { width: 4px; }
    .gm-dept-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }

    /* Pagination buttons */
    .gm-page-btn {
        display: flex; align-items: center; justify-content: center;
        height: 28px; min-width: 28px; padding: 0 6px;
        border-radius: 8px; border: 1px solid #E2E8F0;
        font-size: 11px; font-weight: 600; color: #64748B;
        background: white; cursor: pointer; transition: all 0.12s;
    }
    .gm-page-btn:hover  { background: #F8FAFC; }
    .gm-page-btn.active { background: #8B5CF6; color: white; border-color: #8B5CF6; }
</style>
@endonce

{{-- ── Outer flex wrapper — wraps to multiple lines instead of scrolling ──────── --}}
<div class="flex flex-wrap items-center gap-2">

    {{-- ── Main filter pill ─────────────────────────────────────────────────── --}}
    {{--
        Mobile  (< sm): flex-col — two rows: [Company | Date] / [Dept | Refresh]
        Desktop (sm+) : flex-row — single row pill as before
    --}}
    <div class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm sm:flex-row dark:border-slate-700/60 dark:bg-slate-900">

        {{-- ── Group 1: Company + Date ───────────────────────────────────────── --}}
        <div class="flex items-center divide-x divide-slate-200 border-b border-slate-200 sm:border-b-0 dark:divide-slate-700/60 dark:border-slate-700/60">

            {{-- 1a. Company — locked badge (shown by JS when user has only one company) --}}
            <div id="gmCompanyLocked"
                class="hidden items-center gap-1.5 px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span id="gmCompanyLockedText" class="uppercase tracking-wider"></span>
            </div>

            {{-- 1b. Company — select with native arrow removed, custom chevron added --}}
            <div id="gmCompanyDropdown"
                class="flex cursor-pointer items-center gap-1.5 px-3 py-2 transition hover:bg-slate-50 dark:hover:bg-slate-800/50">
                <svg class="h-3.5 w-3.5 shrink-0 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <select id="gmCompanyFilter"
                    class="cursor-pointer appearance-none border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Companies</option>
                </select>
                <svg class="pointer-events-none h-3 w-3 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- 2. Date — button + floating panel --}}
            <div class="relative">
                <button id="gmDateBtn" type="button"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/50"
                    style="white-space:nowrap">
                    <svg class="h-3.5 w-3.5 shrink-0 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span id="gmDateLabel">This Year</span>
                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Date Panel --}}
                <div id="gmDatePanel"
                    class="absolute left-0 top-full z-50 mt-1.5 hidden w-72 max-w-[calc(100vw-1rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:right-0 sm:left-auto dark:border-slate-700/60 dark:bg-slate-900">

                    <p class="mb-2.5 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Quick Select</p>
                    <div class="grid grid-cols-2 gap-1.5">
                        <button type="button" data-preset="today"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Today</button>
                        <button type="button" data-preset="this-week"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Week</button>
                        <button type="button" data-preset="this-month"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Month</button>
                        <button type="button" data-preset="last-month"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Last Month</button>
                        <button type="button" data-preset="this-year"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">This Year</button>
                        <button type="button" data-preset="last-year"
                            class="gmPreset rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-left text-xs font-semibold text-slate-700 transition hover:border-violet-400 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">Last Year</button>
                    </div>

                    <div class="mt-3 border-t border-slate-100 pt-3 dark:border-slate-700/60">
                        <p class="mb-2 text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Custom Range</p>
                        <div class="flex flex-col gap-2">
                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">From</p>
                                <input type="date" id="gmDateFrom"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            </div>
                            <div>
                                <p class="mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">To</p>
                                <input type="date" id="gmDateTo"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-1.5 text-xs font-semibold text-slate-700 outline-none focus:border-violet-400 focus:ring-1 focus:ring-violet-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            </div>
                        </div>
                        <div class="mt-2 flex gap-2">
                            <button id="gmClearCustom" type="button"
                                class="flex-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                Clear
                            </button>
                            <button id="gmApplyCustom" type="button"
                                class="flex-1 rounded-xl bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">
                                Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>{{-- /Group 1 --}}

        {{-- ── Group 2: Department + Refresh ────────────────────────────────── --}}
        {{-- On sm+: left border separates this group from Group 1 --}}
        <div class="flex items-center divide-x divide-slate-200 sm:border-l sm:border-slate-200 dark:divide-slate-700/60 dark:sm:border-slate-700/60">

            {{-- 3. Department — button + floating panel --}}
            <div class="relative">
                <button id="gmDeptBtn" type="button"
                    class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800/50"
                    style="white-space:nowrap">
                    <svg class="h-3.5 w-3.5 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                    <span id="gmDeptLabel">All Departments</span>
                    <svg class="h-3 w-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Department Panel --}}
                <div id="gmDeptPanel"
                    class="absolute left-0 top-full z-50 mt-1.5 hidden w-80 max-w-[calc(100vw-1rem)] rounded-2xl border border-slate-200 bg-white p-4 shadow-xl sm:right-0 sm:left-auto dark:border-slate-700/60 dark:bg-slate-900">
                    <div class="relative mb-2">
                        <svg class="absolute left-2.5 top-2.5 h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="gmDeptSearch" type="text" placeholder="Search departments…"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2 pl-8 pr-3 text-xs outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:placeholder-slate-500">
                    </div>
                    <div class="mb-2 flex gap-1.5">
                        <button id="gmDeptSelectAll" type="button"
                            class="flex-1 rounded-lg bg-slate-100 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            Select All
                        </button>
                        <button id="gmDeptClear" type="button"
                            class="flex-1 rounded-lg bg-slate-100 py-1.5 text-xs font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                            Clear
                        </button>
                    </div>
                    <div id="gmDeptList"
                        class="gm-dept-scroll max-h-52 overflow-y-auto rounded-xl border border-slate-100 bg-slate-50 p-1.5 dark:border-slate-700/60 dark:bg-slate-800/50">
                        <p class="py-4 text-center text-xs text-slate-400">Loading…</p>
                    </div>
                    <button id="gmDeptApply" type="button"
                        class="mt-3 w-full rounded-xl bg-violet-600 px-3 py-2 text-xs font-bold text-white transition hover:bg-violet-700">
                        Apply Filter
                    </button>
                </div>
            </div>

            {{-- Refresh --}}
            <button id="gmRefreshBtn" type="button"
                class="flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50 dark:text-slate-300 dark:hover:bg-slate-800/50">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>

        </div>{{-- /Group 2 --}}

    </div>{{-- /Main filter pill --}}

    {{-- ── Last-updated indicator ────────────────────────────────────────────── --}}
    <div class="flex-1 items-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" style="animation:gm-pulse 2s infinite"></span>
        <span id="gmRefreshTime" class="font-mono text-xs font-semibold text-slate-500 dark:text-slate-400">--:--</span>
    </div>

</div>
