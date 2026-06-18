<x-app-layout>

    {{-- ── Section tab styles ──────────────────────────────────────────────────── --}}
    <style>
        .gm-section-tab {
            padding: 10px 2px;
            margin-right: 28px;
            font-size: 13.5px;
            font-weight: 500;
            color: #94a3b8;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            transition: color 0.15s, border-color 0.15s;
            white-space: nowrap;
        }
        .gm-section-tab:hover { color: #334155; }
        .dark .gm-section-tab:hover { color: #cbd5e1; }
        .gm-section-tab.active { color: #4f46e5; border-bottom-color: #4f46e5; font-weight: 600; }
        .dark .gm-section-tab.active { color: #818cf8; border-bottom-color: #818cf8; }
    </style>

    <div class="max-w-9xl mx-auto w-full space-y-2 p-2">

        {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
        {{-- gmPageHeader is observed: when it leaves the viewport the filter
             teleports into #gmFilterFloat (fixed); when it re-enters it comes back --}}
        <div id="gmPageHeader" class="flex flex-col">

            {{-- Row 1: Title (left) + Filter + Export (right) --}}
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                        GM DASBOARD
                    </h1>
                </div>

                <div class="flex flex-col items-stretch gap-2 sm:flex-row sm:flex-wrap sm:items-center">
                    <div id="gmFilterAnchor" class="flex w-full sm:w-auto">
                        <x-dashboard-filter.dashboard-filter />
                    </div>
                    <div class="relative w-full sm:w-auto" id="gmExportWrap">
                        <button id="gmExportBtn" type="button"
                                onclick="document.getElementById('gmExportDropdown').classList.toggle('hidden')"
                                class="flex w-full items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-white
                                       px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm
                                       transition hover:bg-slate-50 sm:w-auto sm:justify-start
                                       dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300
                                       dark:hover:bg-slate-800/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Export
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 opacity-60" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div id="gmExportDropdown"
                             class="hidden absolute right-0 top-full z-50 mt-1.5 min-w-35
                                    rounded-xl border border-slate-200/80 bg-white py-1 shadow-lg
                                    dark:border-slate-700/60 dark:bg-slate-800">
                            <a id="gmExport_pdf" href="#"
                               class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                                      hover:bg-red-50 hover:text-red-600
                                      dark:text-slate-300 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                                             0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Export PDF
                            </a>
                            <a id="gmExport_csv" href="#"
                               class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                                      hover:bg-emerald-50 hover:text-emerald-600
                                      dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                                             1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z" />
                                </svg>
                                Export CSV
                            </a>
                            <a id="gmExport_xlsx" href="#"
                               class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700
                                      hover:bg-violet-50 hover:text-violet-600
                                      dark:text-slate-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2
                                             2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Export XLSX
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: Tabs only — border-b is the underline track --}}
            <div class="flex items-end border-b border-slate-200 dark:border-slate-700/60">
                <button id="gmTab_all"    type="button" class="gm-section-tab">All</button>
                <button id="gmTab_budget" type="button" class="gm-section-tab">Budget</button>
                <button id="gmTab_pgcard" type="button" class="gm-section-tab">PG Card</button>
            </div>

        </div>{{-- /gmPageHeader --}}

        {{-- Fixed floating container — filter moves here once header scrolls away --}}
        <div id="gmFilterFloat"
             class="fixed right-4 top-15.5 z-50 hidden max-w-[calc(100vw-2rem)]
                    rounded-2xl border border-slate-200/80 bg-white/90 p-1.5
                    shadow-xl backdrop-blur-md
                    dark:border-slate-700/50 dark:bg-slate-900/90">
        </div>

        {{-- ── Budget Section ───────────────────────────────────────────────────── --}}
        {{--
            Breakpoint layout summary:
            default : 1 col  — all cards stacked
            sm      : 2 col  — Summary|Donut / Trend full / Tables stacked
            md      : 4 col  — Summary+Donut half|half / Trend full / Tables stacked
            lg      : 4 col  — [Summary|Donut|Trend(×2)] / [Dept(×2)|Activity(×2)]
            xl      : 5 col  — Charts sidebar col-1 (rows 1–3) | Dept cols 2–3 | Activity cols 4–5
        --}}
        <div id="gmSectionBudget" class="mt-2 space-y-1.5">

        <div class="grid grid-cols-1 gap-3
                    sm:grid-cols-2
                    md:grid-cols-4
                    lg:grid-cols-4
                    xl:grid-cols-5">

            {{-- 1. Summary --}}
            <x-card-chart.split-stat-card
                class="sm:col-span-1 md:col-span-2 lg:col-span-1 xl:col-span-1 xl:row-start-1"
                color="green"
                leftLabel="Total Budget"
                leftValueId="gmTotalBudget"
                leftDescription="Original + Additional"
                rightLabel="Remaining"
                rightValueId="gmTotalRemaining"
                rightBadgeId="gmUtilTrend"
                barLabel="Utilization"
                barPctId="gmUtilPct"
                barId="gmUtilBar"
            />

            {{-- 2. Donut Breakdown --}}
            <x-card-chart.card-shell
                class="sm:col-span-1 md:col-span-2 lg:col-span-1 xl:col-span-1 xl:row-start-2"
                subtitle="Breakdown"
                title="Used · Reserved · Remaining"
                gradient="linear-gradient(to right,#EF4444,#F59E0B,#10B981)"
            >
                <div class="px-2 pb-2 pt-0">
                    <div id="gmBudgetDonut" style="min-height:210px"></div>
                </div>
            </x-card-chart.card-shell>

            {{-- 3. Monthly Absorption Trend --}}
            <x-card-chart.card-shell
                class="sm:col-span-2 md:col-span-4 lg:col-span-2 xl:col-span-1 xl:row-start-3"
                subtitle="Monthly Absorption"
                title="Cumulative Budget Used"
                gradient="linear-gradient(to right,#8B5CF6,#06B6D4)"
            >
                <x-slot:headerEnd>
                    <span id="gmTrendYear"
                        class="rounded-full bg-violet-50 px-2 py-0.5 text-xs font-bold text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">
                    </span>
                </x-slot:headerEnd>
                <div class="pb-4 pt-0">
                    <div id="gmMonthlyTrend" style="min-height:210px"></div>
                </div>
            </x-card-chart.card-shell>

            {{-- 4. By Department --}}
            <x-card-chart.dynamic-table-card
                class="sm:col-span-2 md:col-span-4 lg:col-span-2 lg:row-start-2 xl:col-span-2 xl:col-start-2 xl:row-start-1 xl:row-span-3"
                subtitle="By Department"
                title="Budget Usage per Department"
                gradient="linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)"
                tableBodyId="gmDeptTableBody"
                countBadgeId="gmDeptCount"
                paginationPrefix="gmDept"
                :columns="[
                    ['label' => 'Department', 'key' => 'department_fin_id'],
                    ['label' => 'Budget',     'key' => 'total_final',     'numeric' => true],
                    ['label' => 'Reserved',   'key' => 'total_reserve',   'numeric' => true],
                    ['label' => 'Remaining',  'key' => 'total_remaining', 'numeric' => true],
                    ['label' => 'Usage %',    'key' => 'used_pct',        'numeric' => true],
                ]"
            />

            {{-- 5. By Activity --}}
            <x-card-chart.dynamic-table-card
                class="sm:col-span-2 md:col-span-4 lg:col-span-2 lg:row-start-2 xl:col-span-2 xl:col-start-4 xl:row-start-1 xl:row-span-3"
                subtitle="By Activity"
                title="Budget Usage by Activity"
                gradient="linear-gradient(to right,#06B6D4,#3B82F6,#8B5CF6)"
                tableBodyId="gmActTableBody"
                countBadgeId="gmActCount"
                paginationPrefix="gmAct"
                :columns="[
                    ['label' => 'Description', 'key' => 'activity_descr'],
                    ['label' => 'Budget',      'key' => 'total_final',     'numeric' => true],
                    ['label' => 'Reserved',    'key' => 'total_reserve',   'numeric' => true],
                    ['label' => 'Remaining',   'key' => 'total_remaining', 'numeric' => true],
                    ['label' => 'Usage %',     'key' => 'used_pct',        'numeric' => true],
                ]"
            />

        </div>

        </div>{{-- /Budget wrapper --}}

        {{-- ── PG Card Section ──────────────────────────────────────────────── --}}
        {{--
            Breakpoint layout summary:
            default : 1 col  — all cards stacked
            sm      : 2 col  — [Total Coupon | By Mall] / Top 10 charts full-width stacked
            md      : 4 col  — [Total Coupon (×2) | By Mall (×2)] / [Top10Cust (×2) | Top10Ten (×2)]
            lg      : 4 col  — same as md
            xl      : 5 col  — [Col 1: Total Coupon (row 1) + By Mall (row 2)] [Cols 2-3: Top 10 Customer (rows 1-2)] [Cols 4-5: Top 10 Tenant (rows 1-2)]
        --}}

        {{-- Tab + metric styles (scoped to PG Card section) --}}
        <style>
            .pgcard-tab-active  { background:rgb(238 233 255/1);color:#7c3aed;font-weight:700; }
            .dark .pgcard-tab-active  { background:rgb(139 92 246/.15);color:#a78bfa; }
            .pgcard-tab-idle    { color:#94a3b8; }
            .pgcard-tab-idle:hover    { background:rgb(241 245 249/1);color:#475569; }
            .dark .pgcard-tab-idle:hover { background:rgb(51 65 85/.5);color:#cbd5e1; }
            .pgcard-metric-active { background:rgb(220 252 231/1);color:#15803d;font-weight:700; }
            .dark .pgcard-metric-active { background:rgb(16 185 129/.15);color:#34d399; }
            .pgcard-metric-idle { color:#94a3b8; }
            .pgcard-metric-idle:hover { background:rgb(241 245 249/1);color:#475569; }
            .dark .pgcard-metric-idle:hover { background:rgb(51 65 85/.5);color:#cbd5e1; }
        </style>

        <div id="gmSectionPgcard" class="mt-2 space-y-1.5">

            {{-- Single responsive grid — mirrors Budget section pattern --}}
            <div class="grid grid-cols-1 gap-3
                        sm:grid-cols-2
                        md:grid-cols-4
                        lg:grid-cols-4
                        xl:grid-cols-5">

                {{-- 1. Total Coupon — sidebar top --}}
                <x-card-chart.card-shell
                    class="sm:col-span-1 md:col-span-2 xl:col-span-1 xl:row-start-1"
                    subtitle="PG Card · STYW 2026"
                    title="Total Coupon"
                    gradient="linear-gradient(to right,#8B5CF6,#EC4899)"
                >
                    <div class="px-5 pb-5 pt-2">
                        <p id="pgcardCouponTotal"
                           class="text-4xl font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">
                            …
                        </p>
                        <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Valid coupon records (filtered)</p>
                        <div id="pgcardCouponStatus" class="mt-3 flex flex-wrap gap-1.5"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- 2. By Mall donut — sidebar bottom --}}
                <x-card-chart.card-shell
                    class="sm:col-span-1 md:col-span-2 xl:col-span-1 xl:row-start-2"
                    subtitle="PG Card · STYW 2026"
                    title="By Mall"
                    gradient="linear-gradient(to right,#06B6D4,#8B5CF6)"
                >
                    <x-slot:headerEnd>
                        <select id="pgcardMallStatusFilter"
                            class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-[11px] font-medium text-slate-600 focus:outline-none focus:ring-1 focus:ring-cyan-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300">
                            <option value="VALID">Valid</option>
                            <option value="">All Status</option>
                            <option value="EXPIRED">Expired</option>
                            <option value="INVALID">Invalid</option>
                            <option value="-">Waiting for Processed</option>
                        </select>
                    </x-slot:headerEnd>
                    <div class="px-3 pb-3 pt-0">
                        <div id="pgcardCouponDonut" style="min-height:220px"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- 3. Top 10 Customer — centre column --}}
                <x-card-chart.card-shell
                    class="sm:col-span-2 md:col-span-2 xl:col-span-2 xl:col-start-2 xl:row-start-1 xl:row-span-2"
                    subtitle="PG Card"
                    title="Top 10 Customer"
                    gradient="linear-gradient(to right,#8B5CF6,#EC4899)"
                >
                    <x-slot:headerEnd>
                        <div class="flex items-center gap-2">
                            <div id="pgcardCustTab_container" class="flex items-center gap-1"></div>
                            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                            <div class="flex items-center gap-1">
                                <button id="pgcardCustMetric_transaction" type="button"
                                        class="pgcard-metric-idle rounded-lg px-2 py-1 text-[10px] font-semibold transition"
                                        title="Sort by transaction count">By Transaction</button>
                                <button id="pgcardCustMetric_spending" type="button"
                                        class="pgcard-metric-idle rounded-lg px-2 py-1 text-[10px] font-semibold transition"
                                        title="Sort by total spending (Rp)">By Spending</button>
                            </div>
                        </div>
                    </x-slot:headerEnd>
                    <div class="px-3 pb-3 pt-0">
                        <div id="pgcardCustomerChart" style="min-height:310px"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- 5. Query Comparison — full width row --}}
                <x-card-chart.card-shell
                    class="sm:col-span-2 md:col-span-4 xl:col-span-5 xl:row-start-3"
                    subtitle="PG Card · STYW 2026"
                    title="Query Performance Comparison"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444)"
                >
                    <div class="px-5 pb-5 pt-2">
                        <div class="flex items-center gap-3 mb-4">
                            <button id="pgcardRunCompare" type="button"
                                class="rounded-lg bg-amber-500 px-4 py-2 text-xs font-semibold text-white hover:bg-amber-600 active:scale-95 transition">
                                ▶ Run Comparison
                            </button>
                            <span id="pgcardCompareStatus" class="text-xs text-slate-400 dark:text-slate-500 italic"></span>
                        </div>
                        <div id="pgcardCompareResult" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Option A --}}
                            <div class="rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30 p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-amber-700 dark:text-amber-400">Option A — View Table</span>
                                    <span id="pgcardCompareTimeA" class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400">—</span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-mono mb-3">pgcard_detail_member_coupon_styw_2026</p>
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <p id="pgcardCompareTotalA" class="text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5 mb-2">VALID coupons</p>
                                        <div id="pgcardCompareStatusA" class="flex flex-wrap gap-1.5"></div>
                                    </div>
                                    <div id="pgcardCompareDonutA" style="min-width:160px;min-height:160px"></div>
                                </div>
                            </div>
                            {{-- Option B --}}
                            <div class="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/30 p-4">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400">Option B — Direct from Src Tables</span>
                                    <span id="pgcardCompareTimeB" class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">—</span>
                                </div>
                                <p class="text-[10px] text-slate-400 font-mono mb-3">pgcard_member_coupons_src + pgcard_member_transactions_src + pgcard_campaigns_src</p>
                                <div class="flex items-start gap-4">
                                    <div class="flex-1">
                                        <p id="pgcardCompareTotalB" class="text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white">—</p>
                                        <p class="text-[10px] text-slate-400 mt-0.5 mb-2">VALID coupons</p>
                                        <div id="pgcardCompareStatusB" class="flex flex-wrap gap-1.5"></div>
                                    </div>
                                    <div id="pgcardCompareDonutB" style="min-width:160px;min-height:160px"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card-chart.card-shell>

                {{-- 4. Top 10 Tenant — right column --}}
                <x-card-chart.card-shell
                    class="sm:col-span-2 md:col-span-2 xl:col-span-2 xl:col-start-4 xl:row-start-1 xl:row-span-2"
                    subtitle="PG Card"
                    title="Top 10 Tenant"
                    gradient="linear-gradient(to right,#06B6D4,#3B82F6)"
                >
                    <x-slot:headerEnd>
                        <div class="flex items-center gap-2">
                            <div id="pgcardTenTab_container" class="flex items-center gap-1"></div>
                            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                            <div class="flex items-center gap-1">
                                <button id="pgcardTenMetric_transaction" type="button"
                                        class="pgcard-metric-idle rounded-lg px-2 py-1 text-[10px] font-semibold transition"
                                        title="Sort by transaction count">By Transaction</button>
                                <button id="pgcardTenMetric_spending" type="button"
                                        class="pgcard-metric-idle rounded-lg px-2 py-1 text-[10px] font-semibold transition"
                                        title="Sort by total spending (Rp)">By Spending</button>
                            </div>
                        </div>
                    </x-slot:headerEnd>
                    <div class="px-3 pb-3 pt-0">
                        <div id="pgcardTenantChart" style="min-height:310px"></div>
                    </div>
                </x-card-chart.card-shell>

            </div>

        </div>

    </div>

    {{-- ── Route registry (shared across all GM section scripts) ─────────────── --}}
    <script>
        window.gmRoutes = {
            companies         : "{{ route('gm.companies') }}",
            years             : "{{ route('gm.budget-years') }}",
            departments       : "{{ route('gm.departments') }}",
            summary           : "{{ route('gm.budget-summary') }}",
            byDept            : "{{ route('gm.budget-by-department') }}",
            byActivity        : "{{ route('gm.budget-by-activity') }}",
            byMonth           : "{{ route('gm.budget-by-month') }}",
            pgcardTopCustomers: "{{ route('gm.pgcard-top-customers') }}",
            pgcardTopTenants  : "{{ route('gm.pgcard-top-tenants') }}",
            pgcardCouponStyw        : "{{ route('gm.pgcard-coupon-styw') }}",
            pgcardCouponStywCompare : "{{ route('gm.pgcard-coupon-styw-compare') }}",
        };
    </script>

    {{-- Load order: core → filter → [section scripts] --}}
    <script src="{{ asset('assets/js/gm-report/gm-core.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-filter.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-budget.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-pgcard.js') }}"></script>

    {{-- ── Export link updater ─────────────────────────────────────────────────
         Runs every time the filter changes so the download URL always carries
         the currently-selected company / date range / departments.
    --}}
    <script>
    (function () {
        var exportRoutes = {
            pdf  : '{{ route('gm.export.pdf') }}',
            csv  : '{{ route('gm.export.csv') }}',
            xlsx : '{{ route('gm.export.xlsx') }}',
        };

        function updateExportLinks() {
            var params = window.gmUtils ? window.gmUtils.buildParams() : '';
            ['pdf', 'csv', 'xlsx'].forEach(function (fmt) {
                var el = document.getElementById('gmExport_' + fmt);
                if (el) el.href = exportRoutes[fmt] + params;
            });
        }

        document.addEventListener('gm:filter', updateExportLinks);

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            var wrap = document.getElementById('gmExportWrap');
            var dd   = document.getElementById('gmExportDropdown');
            if (wrap && dd && !wrap.contains(e.target)) {
                dd.classList.add('hidden');
            }
        });
    })();
    </script>

    {{-- ── Floating filter teleport ────────────────────────────────────────────
         Watches the page header with IntersectionObserver (offset -56px for the
         sticky navbar). When the header leaves the viewport the filter DOM node
         is moved into #gmFilterFloat (fixed pill). When the header re-enters
         it moves back to #gmFilterAnchor so it looks integrated in the header.
    --}}
    <script>
    (function () {
        const anchor   = document.getElementById('gmFilterAnchor');
        const floatBox = document.getElementById('gmFilterFloat');
        const header   = document.getElementById('gmPageHeader');
        if (!anchor || !floatBox || !header) return;

        const obs = new IntersectionObserver(([entry]) => {
            if (!entry.isIntersecting) {
                // Header scrolled away — move filter into floating pill
                const el = anchor.firstElementChild;
                if (el) {
                    floatBox.appendChild(el);
                    floatBox.classList.remove('hidden');
                }
            } else {
                // Header visible again — return filter to inline position
                const el = floatBox.firstElementChild;
                if (el) {
                    anchor.appendChild(el);
                    floatBox.classList.add('hidden');
                }
            }
        }, {
            rootMargin: '-56px 0px 0px 0px', // account for sticky navbar height
            threshold: 0
        });

        obs.observe(header);
    })();
    </script>

    {{-- ── Section tab switcher ───────────────────────────────────────────────── --}}
    <script>
    (function () {
        var sections = { budget: 'gmSectionBudget', pgcard: 'gmSectionPgcard' };
        var headers  = { budget: 'gmSectionBudgetHeader', pgcard: 'gmSectionPgcardHeader' };
        var tabIds   = ['gmTab_all', 'gmTab_budget', 'gmTab_pgcard'];

        function switchTab(key) {
            tabIds.forEach(function (id) {
                var btn = document.getElementById(id);
                if (btn) btn.classList.toggle('active', id === 'gmTab_' + key);
            });
            Object.keys(sections).forEach(function (sec) {
                var section = document.getElementById(sections[sec]);
                var header  = document.getElementById(headers[sec]);
                var hidden  = key !== 'all' && key !== sec;
                if (section) section.classList.toggle('hidden', hidden);
                if (header)  header.classList.toggle('hidden', key !== 'all');
            });
            try { localStorage.setItem('gmActiveTab', key); } catch (e) {}
        }

        tabIds.forEach(function (id) {
            var btn = document.getElementById(id);
            if (btn) btn.addEventListener('click', function () {
                switchTab(id.replace('gmTab_', ''));
            });
        });

        var saved = 'all';
        try { saved = localStorage.getItem('gmActiveTab') || 'all'; } catch (e) {}
        switchTab(saved);
    })();
    </script>

</x-app-layout>
