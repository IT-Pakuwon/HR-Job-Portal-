<x-app-layout>

<div class="max-w-9xl mx-auto w-full space-y-4 p-3">

    {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                GM Report Dashboard
            </h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">
                Consolidated overview — Budget & Financial Performance
            </p>
        </div>

        {{-- ── Filter Bar ─────────────────────────────────────────────────── --}}
        <div class="flex flex-wrap items-center gap-2">

            {{-- 1. Company — locked badge or dropdown (populated by JS) --}}
            <div id="gmCompanyLocked"
                class="hidden items-center gap-1.5 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-bold text-slate-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                <svg class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                <span id="gmCompanyLockedText" class="uppercase tracking-wider"></span>
            </div>

            <div id="gmCompanyDropdown"
                class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <select id="gmCompanyFilter"
                    class="border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="">All Companies</option>
                </select>
            </div>

            {{-- Divider --}}
            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

            {{-- 2. Single Date: Year + Month --}}
            <div class="flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Date</span>
                <select id="gmMonth"
                    class="border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    <option value="0">All Year</option>
                    <option value="1">Jan</option><option value="2">Feb</option><option value="3">Mar</option>
                    <option value="4">Apr</option><option value="5">May</option><option value="6">Jun</option>
                    <option value="7">Jul</option><option value="8">Aug</option><option value="9">Sep</option>
                    <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dec</option>
                </select>
                <select id="gmYear"
                    class="border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200">
                    {{-- populated by JS --}}
                </select>
            </div>

            {{-- Divider --}}
            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

            {{-- 3. Department multi-select (select2) --}}
            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                </svg>
                <select id="gmDeptFilter" multiple
                    class="border-0 bg-transparent text-xs font-semibold text-slate-700 outline-none dark:text-slate-200"
                    style="min-width:160px; max-width:260px">
                    {{-- populated by JS --}}
                </select>
            </div>

            {{-- Divider --}}
            <div class="h-6 w-px bg-slate-200 dark:bg-slate-700"></div>

            <button id="gmRefreshBtn"
                class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>

            <div class="flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                <span class="text-slate-400 dark:text-slate-500">Updated</span>
                <span id="gmRefreshTime" class="font-semibold text-slate-700 dark:text-slate-200">--:--:--</span>
            </div>

        </div>
    </div>

    {{-- ── Budget Section ───────────────────────────────────────────────────── --}}
    <div class="space-y-3">

        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
            Budget Overview
            <span id="gmPeriodLabel" class="ml-1 text-violet-500"></span>
        </p>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-2 gap-3 xl:grid-cols-4">

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#10B981,#0D9488)"></div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Budget</p>
                <p id="gmTotalBudget" class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Original + Additional</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#EF4444,#F43F5E)"></div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Budget Used</p>
                <p id="gmTotalUsed" class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                <div class="mt-1 flex items-center gap-1.5">
                    <span id="gmUtilTrend" class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold">—</span>
                    <span class="text-xs text-slate-400">utilization</span>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#F59E0B,#D97706)"></div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Reserved</p>
                <p id="gmTotalReserve" class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Committed / on-hold</p>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#8B5CF6,#7C3AED)"></div>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Remaining</p>
                <p id="gmTotalRemaining" class="mt-2 text-2xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Available to spend</p>
            </div>

        </div>

        {{-- Charts Row: Donut + Gauge + By Company --}}
        <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#EF4444,#10B981)"></div>
                <div class="px-5 pt-5 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Breakdown</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">Used · Reserved · Remaining</h3>
                </div>
                <div class="px-2 pb-3 pt-1">
                    <div id="gmBudgetDonut" style="min-height:300px"></div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#10B981,#06B6D4)"></div>
                <div class="px-5 pt-5 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Utilization Rate</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">Budget Used vs Total</h3>
                </div>
                <div class="px-2 pb-3 pt-1">
                    <div id="gmBudgetGauge" style="min-height:300px"></div>
                </div>
            </div>

            <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#3B82F6,#8B5CF6)"></div>
                <div class="px-5 pt-5 pb-1">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">By Company</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">Budget Split per Entity</h3>
                </div>
                <div class="px-2 pb-3 pt-1">
                    <div id="gmBudgetByCompany" style="min-height:300px"></div>
                </div>
            </div>

        </div>

        {{-- Department Table (full width) --}}
        <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
            <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)"></div>

            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">By Department</p>
                    <h3 class="mt-0.5 text-sm font-bold text-slate-800 dark:text-white">Budget Usage per Department</h3>
                </div>
                <span id="gmDeptCount" class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-500 dark:bg-slate-800 dark:text-slate-400"></span>
            </div>

            <div id="gmDeptTableWrap" class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="border-t border-slate-100 dark:border-slate-700/60">
                            <th class="bg-slate-50 px-5 py-2.5 text-left text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Department</th>
                            <th class="bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Budget</th>
                            <th class="bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Used</th>
                            <th class="bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Reserved</th>
                            <th class="bg-slate-50 px-4 py-2.5 text-right text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Remaining</th>
                            <th class="bg-slate-50 px-4 py-2.5 text-center text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">Usage %</th>
                        </tr>
                    </thead>
                    <tbody id="gmDeptTableBody" class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">
                                Loading…
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

{{-- select2 custom style for dark mode + compact --}}
<style>
    .select2-container--default .select2-selection--multiple {
        border: none !important;
        background: transparent !important;
        min-height: auto !important;
        padding: 0 !important;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 0 !important;
        display: flex;
        flex-wrap: wrap;
        gap: 2px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin: 0 !important;
        padding: 1px 6px !important;
        font-size: 11px;
        font-weight: 600;
        border-radius: 999px;
        background: #EDE9FE;
        border-color: #DDD6FE;
        color: #7C3AED;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #8B5CF6;
        margin-right: 2px;
    }
    .select2-container--default .select2-search--inline .select2-search__field {
        font-size: 11px;
        font-weight: 600;
        color: #374151;
    }
    .select2-dropdown {
        border-radius: 12px !important;
        border-color: #E2E8F0 !important;
        box-shadow: 0 8px 24px rgba(0,0,0,0.10) !important;
        font-size: 12px;
        overflow: hidden;
    }
    .select2-results__option {
        padding: 6px 12px;
    }
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: #8B5CF6;
    }
</style>

{{-- Route config for JS --}}
<script>
window.gmRoutes = {
    companies:   "{{ route('gm.companies') }}",
    summary:     "{{ route('gm.budget-summary') }}",
    byCompany:   "{{ route('gm.budget-by-company') }}",
    byDept:      "{{ route('gm.budget-by-department') }}",
    years:       "{{ route('gm.budget-years') }}",
    departments: "{{ route('gm.departments') }}",
};
</script>
<script src="{{ asset('assets/js/gm-report/dashgm.js') }}"></script>

</x-app-layout>
