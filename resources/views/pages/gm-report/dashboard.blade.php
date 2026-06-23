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

        .gm-section-tab:hover {
            color: #334155;
        }

        .dark .gm-section-tab:hover {
            color: #cbd5e1;
        }

        .gm-section-tab.active {
            color: #4f46e5;
            border-bottom-color: #4f46e5;
            font-weight: 600;
        }

        .dark .gm-section-tab.active {
            color: #818cf8;
            border-bottom-color: #818cf8;
        }
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
                            class="flex w-full items-center justify-center gap-1.5 rounded-2xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 sm:w-auto sm:justify-start dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            class="min-w-35 absolute right-0 top-full z-50 mt-1.5 hidden rounded-xl border border-slate-200/80 bg-white py-1 shadow-lg dark:border-slate-700/60 dark:bg-slate-800">
                            <a id="gmExport_pdf" href="#"
                                class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-red-50 hover:text-red-600 dark:text-slate-300 dark:hover:bg-red-500/10 dark:hover:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0
                                             0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                Export PDF
                            </a>
                            <a id="gmExport_csv" href="#"
                                class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-emerald-50 hover:text-emerald-600 dark:text-slate-300 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1
                                             1 0 01.707.293l5.414 5.414A1 1 0 0120 9.414V19a2 2 0 01-2 2z" />
                                </svg>
                                Export CSV
                            </a>
                            <a id="gmExport_xlsx" href="#"
                                class="flex items-center gap-2.5 px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-violet-50 hover:text-violet-600 dark:text-slate-300 dark:hover:bg-violet-500/10 dark:hover:text-violet-400">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2
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
                <button id="gmTab_all" type="button" class="gm-section-tab">All</button>
                <button id="gmTab_budget" type="button" class="gm-section-tab">Budget</button>
                <button id="gmTab_pgcard" type="button" class="gm-section-tab">PG Card</button>
                <button id="gmTab_isort" type="button" class="gm-section-tab">Operation - Isort</button>
                <button id="gmTab_parking" type="button" class="gm-section-tab">Parking</button>
                <button id="gmTab_valet" type="button" class="gm-section-tab">Valet Parking</button>
            </div>

        </div>{{-- /gmPageHeader --}}

        {{-- Fixed floating container — filter moves here once header scrolls away --}}
        <div id="gmFilterFloat"
            class="top-15.5 fixed right-4 z-50 hidden max-w-[calc(100vw-2rem)] rounded-2xl border border-slate-200/80 bg-white/90 p-1.5 shadow-xl backdrop-blur-md dark:border-slate-700/50 dark:bg-slate-900/90">
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

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-4 lg:grid-cols-4 xl:grid-cols-5">

                {{-- 1. Summary --}}
                <x-card-chart.split-stat-card
                    class="sm:col-span-1 md:col-span-2 lg:col-span-1 xl:col-span-1 xl:row-start-1" color="green"
                    leftLabel="Total Budget" leftValueId="gmTotalBudget" leftDescription="Original + Additional"
                    rightLabel="Remaining" rightValueId="gmTotalRemaining" rightBadgeId="gmUtilTrend"
                    barLabel="Utilization" barPctId="gmUtilPct" barId="gmUtilBar" />

                {{-- 2. Donut Breakdown --}}
                <x-card-chart.card-shell class="sm:col-span-1 md:col-span-2 lg:col-span-1 xl:col-span-1 xl:row-start-2"
                    subtitle="Breakdown" title="Used · Reserved · Remaining"
                    gradient="linear-gradient(to right,#EF4444,#F59E0B,#10B981)">
                    <div class="px-2 pb-2 pt-0">
                        <div id="gmBudgetDonut" style="min-height:210px"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- 3. Monthly Absorption Trend --}}
                <x-card-chart.card-shell class="sm:col-span-2 md:col-span-4 lg:col-span-2 xl:col-span-1 xl:row-start-3"
                    subtitle="Monthly Absorption" title="Cumulative Budget Used"
                    gradient="linear-gradient(to right,#8B5CF6,#06B6D4)">
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
                    class="sm:col-span-2 md:col-span-4 lg:col-span-2 lg:row-start-2 xl:col-span-2 xl:col-start-2 xl:row-span-3 xl:row-start-1"
                    subtitle="By Department" title="Budget Usage per Department"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)" tableBodyId="gmDeptTableBody"
                    countBadgeId="gmDeptCount" paginationPrefix="gmDept" :columns="[
                        ['label' => 'Department', 'key' => 'department_fin_id'],
                        ['label' => 'Budget', 'key' => 'total_final', 'numeric' => true],
                        ['label' => 'Reserved', 'key' => 'total_reserve', 'numeric' => true],
                        ['label' => 'Remaining', 'key' => 'total_remaining', 'numeric' => true],
                        ['label' => 'Usage %', 'key' => 'used_pct', 'numeric' => true],
                    ]" />

                {{-- 5. By Activity --}}
                <x-card-chart.dynamic-table-card
                    class="sm:col-span-2 md:col-span-4 lg:col-span-2 lg:row-start-2 xl:col-span-2 xl:col-start-4 xl:row-span-3 xl:row-start-1"
                    subtitle="By Activity" title="Budget Usage by Activity"
                    gradient="linear-gradient(to right,#06B6D4,#3B82F6,#8B5CF6)" tableBodyId="gmActTableBody"
                    countBadgeId="gmActCount" paginationPrefix="gmAct" :columns="[
                        ['label' => 'Description', 'key' => 'activity_descr'],
                        ['label' => 'Budget', 'key' => 'total_final', 'numeric' => true],
                        ['label' => 'Reserved', 'key' => 'total_reserve', 'numeric' => true],
                        ['label' => 'Remaining', 'key' => 'total_remaining', 'numeric' => true],
                        ['label' => 'Usage %', 'key' => 'used_pct', 'numeric' => true],
                    ]" />

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
            .pgcard-tab-active {
                background: rgb(238 233 255/1);
                color: #7c3aed;
                font-weight: 700;
            }

            .dark .pgcard-tab-active {
                background: rgb(139 92 246/.15);
                color: #a78bfa;
            }

            .pgcard-tab-idle {
                color: #94a3b8;
            }

            .pgcard-tab-idle:hover {
                background: rgb(241 245 249/1);
                color: #475569;
            }

            .dark .pgcard-tab-idle:hover {
                background: rgb(51 65 85/.5);
                color: #cbd5e1;
            }

            .pgcard-metric-active {
                background: rgb(220 252 231/1);
                color: #15803d;
                font-weight: 700;
            }

            .dark .pgcard-metric-active {
                background: rgb(16 185 129/.15);
                color: #34d399;
            }

            .pgcard-metric-idle {
                color: #94a3b8;
            }

            .pgcard-metric-idle:hover {
                background: rgb(241 245 249/1);
                color: #475569;
            }

            .dark .pgcard-metric-idle:hover {
                background: rgb(51 65 85/.5);
                color: #cbd5e1;
            }
        </style>

        <div id="gmSectionPgcard" class="mt-2 space-y-3">

            {{-- ── Row 1: 3 cols — KPI Sidebar | Top 10 Customer | Top 10 Tenant ──────
                 lg+  : 3 equal columns
                 sm   : sidebar full-width (spans 2), customer + tenant side by side
                 xs   : all stacked
            --}}
            {{--
                xs  : 1 col  — all stacked
                sm  : 2 col  — sidebar full-width (span 2); Customer + Tenant side by side
                lg+ : 3 col  — sidebar ⅓ | Customer ⅓ | Tenant ⅓
            --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" style="align-items:stretch">

                {{-- Col 1: KPI Summary sidebar --}}
                <div class="flex flex-col gap-3 sm:col-span-2 lg:col-span-1 h-full">

                    <div class="relative flex flex-1 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700/60 dark:bg-slate-900">
                        <div class="absolute inset-x-0 top-0 h-0.75" style="background:linear-gradient(to right,#8B5CF6,#EC4899,#06B6D4,#10B981)"></div>
                        <div class="grid flex-1 grid-cols-1 divide-y divide-slate-100 dark:divide-slate-700/60">

                            <div class="flex items-start gap-3 px-4 py-3">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-violet-50 dark:bg-violet-900/30">
                                    <svg class="h-4 w-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Total Transactions</p>
                                    <p id="pgcardKpiTxn" class="text-base font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">…</p>
                                    <div id="pgcardKpiMallList" class="mt-1"></div>
                                    <div id="pgcardInsightTxn"></div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 px-4 py-3">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-pink-50 dark:bg-pink-900/30">
                                    <svg class="h-4 w-4 text-pink-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Total Spending</p>
                                    <p id="pgcardKpiSpending" class="text-base font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">…</p>
                                    <div id="pgcardKpiSpendingMallList" class="mt-1"></div>
                                    <div id="pgcardInsightSpending"></div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 px-4 py-3">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                                    <svg class="h-4 w-4 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Active Members</p>
                                    <p id="pgcardKpiMembers" class="text-base font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">…</p>
                                    <div id="pgcardKpiMembersMallList" class="mt-1"></div>
                                    <div id="pgcardInsightMembers"></div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3 px-4 py-3">
                                <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                                    <svg class="h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/></svg>
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[9px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Avg Transaction</p>
                                    <p id="pgcardKpiAvg" class="text-base font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">…</p>
                                    <div id="pgcardKpiAvgMallList" class="mt-1"></div>
                                    <div id="pgcardInsightAvg"></div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>{{-- /Col 1 --}}

                {{-- Col 2: Top 10 Customer --}}
                <x-card-chart.card-shell subtitle="PG Card" title="Top 10 Customer"
                    gradient="linear-gradient(to right,#8B5CF6,#EC4899)" class="h-full flex flex-col">
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
                                    title="Sort by total spending">By Spending</button>
                            </div>
                        </div>
                    </x-slot:headerEnd>
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="pgcardCustomerChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- Col 3: Top 10 Tenant --}}
                <x-card-chart.card-shell subtitle="PG Card" title="Top 10 Tenant"
                    gradient="linear-gradient(to right,#06B6D4,#3B82F6)" class="h-full flex flex-col">
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
                                    title="Sort by total spending">By Spending</button>
                            </div>
                        </div>
                    </x-slot:headerEnd>
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="pgcardTenantChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

            </div>{{-- /Row 1 --}}

            {{-- ── Row 2: 3 cols — Campaign | Monthly Trend | Total Coupon + By Mall ───
                 lg+  : 3 equal columns
                 sm   : campaign + trend side by side, coupon+mall full-width below
                 xs   : all stacked
            --}}
            {{--
                xs  : 1 col  — all stacked
                sm  : 2 col  — Trend + Campaign side by side; Coupon+Mall full-width below
                lg+ : 3 col  — Trend ⅓ | Campaign ⅓ | Coupon+Mall ⅓
            --}}
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" style="align-items:stretch">
                {{-- Col 1: Monthly Transaction Trend --}}
                <x-card-chart.card-shell subtitle="PG Card" title="Monthly Transaction Trend"
                    gradient="linear-gradient(to right,#8B5CF6,#3B82F6)" class="h-full flex flex-col">
                    <x-slot:headerEnd>
                        <div class="flex items-center gap-1">
                            <button id="pgcardTrendTab_txn" type="button"
                                class="pgcard-tab-active rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">
                                Transactions
                            </button>
                            <button id="pgcardTrendTab_members" type="button"
                                class="pgcard-tab-idle rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">
                                Members
                            </button>
                            <button id="pgcardTrendTab_spending" type="button"
                                class="pgcard-tab-idle rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">
                                Spending
                            </button>
                        </div>
                    </x-slot:headerEnd>
                    {{-- flex-1 makes this div fill the remaining card height --}}
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="pgcardTrendChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- Col 2: Campaign chart --}}
                <x-card-chart.card-shell subtitle="PG Card · STYW 2026" title="Campaign"
                    gradient="linear-gradient(to right,#10B981,#06B6D4)" class="h-full flex flex-col">
                    <x-slot:headerEnd>
                        <div class="flex items-center gap-1">
                            <button id="pgcardCmpTab_campaign" type="button"
                                class="pgcard-tab-active rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">
                                By Transaction
                            </button>
                            <button id="pgcardCmpTab_customer" type="button"
                                class="pgcard-tab-idle rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">
                                By Customer
                            </button>
                        </div>
                    </x-slot:headerEnd>
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="pgcardCampaignChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                {{-- Col 3: Total Coupon + By Mall stacked — full-width at sm, ⅓ at lg --}}
                <div class="flex flex-col gap-3 sm:col-span-2 lg:col-span-1 h-full">

                    <x-card-chart.card-shell subtitle="PG Card · STYW 2026" title="Total Coupon"
                        gradient="linear-gradient(to right,#8B5CF6,#EC4899)">
                        <div class="px-5 pb-5 pt-2">
                            <p id="pgcardCouponTotal"
                                class="text-4xl font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white">…</p>
                            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Valid coupon records (filtered)</p>
                            <div id="pgcardCouponStatus" class="mt-3 flex flex-wrap gap-1.5"></div>
                        </div>
                    </x-card-chart.card-shell>

                    {{-- By Mall grows to fill remaining column height --}}
                    <x-card-chart.card-shell subtitle="PG Card · STYW 2026" title="By Mall"
                        class="flex-1 flex flex-col"
                        gradient="linear-gradient(to right,#06B6D4,#8B5CF6)">
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
                        <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                            <div id="pgcardCouponDonut" class="flex-1"></div>
                        </div>
                    </x-card-chart.card-shell>

                </div>{{-- /Col 3 --}}

            </div>{{-- /Row 2 --}}


        </div>


        {{-- ── Isort Section ───────────────────────────────────────────────────── --}}
        <div id="gmSectionIsort" class="mt-2 space-y-3">

            {{-- ── KPI Strip — full width, 6 metrics ────────────────────────────── --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 shadow-sm dark:border-slate-700/60">
                <div class="absolute inset-x-0 top-0 z-10 h-0.75"
                    style="background:linear-gradient(to right,#3B82F6,#10B981,#F59E0B,#EF4444,#8B5CF6,#06B6D4)"></div>
                <div class="grid grid-cols-2 gap-px bg-slate-100 dark:bg-slate-700/50 sm:grid-cols-3 xl:grid-cols-6">

                    {{-- Total Issue --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Issue</p>
                            <p id="isortTotalCase" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Open Issue --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-amber-500">Open Issue</p>
                            <p id="isortTotalOpen" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-amber-600 dark:text-amber-400 sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Closed Issue --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-emerald-500">Closed Issue</p>
                            <p id="isortTotalClosed" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-emerald-600 dark:text-emerald-400 sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Overdue Issue --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-red-50 dark:bg-red-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-red-500">Overdue Issue</p>
                            <p id="isortTotalOverdue" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-red-600 dark:text-red-400 sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Avg Resolution Time --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-violet-500">Avg Resolution</p>
                            <p id="isortAvgResolution" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-violet-600 dark:text-violet-400 sm:text-2xl">—</p>
                            <p id="isortAvgResolutionUnit" class="text-[10px] text-slate-400 dark:text-slate-500">hrs to close</p>
                        </div>
                    </div>

                    {{-- Closure Rate --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-cyan-50 dark:bg-cyan-500/10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4.5 w-4.5 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-cyan-500">Closure Rate</p>
                            <p id="isortClosureRate" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-cyan-600 dark:text-cyan-400 sm:text-2xl">—</p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500">of total closed</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── 3-column chart grid ────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

                <x-card-chart.card-shell subtitle="Operation · Isort" title="Total Kaizen by Type"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#8B5CF6,#3B82F6)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="isortKaizenTypeChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.card-shell subtitle="Operation · Isort" title="Top 10 Kaizen by Incident Type"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#EF4444,#F59E0B)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="isortIncidentChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.card-shell subtitle="Operation · Isort" title="Kaizen by Department"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#06B6D4,#8B5CF6,#EC4899)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="isortDeptChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

            </div>

            {{-- ── Row 3: Monthly Trend + Top 10 Problem Areas ───────────────────── --}}
            {{-- <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

                <x-card-chart.card-shell subtitle="Operation · Isort" title="Monthly Issue Trend"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#3B82F6,#10B981,#F59E0B,#EF4444)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="isortMonthlyTrendChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.card-shell subtitle="Operation · Isort" title="Top 10 Problem Areas"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="isortTopAreasChart" class="flex-1"></div>
                    </div>
                </x-card-chart.card-shell>

            </div> --}}

        </div>{{-- /Isort Section --}}

        {{-- ── Parking Section ──────────────────────────────────────────────────── --}}
        <div id="gmSectionParking" class="mt-2 space-y-3">

            {{-- KPI Strip --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 shadow-sm dark:border-slate-700/60">
                <div class="absolute inset-x-0 top-0 z-10 h-0.75"
                    style="background:linear-gradient(to right,#0EA5E9,#10B981,#F59E0B,#6366F1)"></div>

                {{-- Site tabs row --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5 dark:border-slate-700/60">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Parking · Site</span>
                    <div id="parkingSiteTabsContainer" class="flex flex-wrap items-center gap-1">
                        <span class="text-[10px] text-slate-400">Loading…</span>
                    </div>
                </div>

                {{-- KPI metrics --}}
                <div class="grid grid-cols-2 gap-px bg-slate-100 dark:bg-slate-700/50 sm:grid-cols-4">

                    {{-- Total Income --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-50 dark:bg-sky-500/10">
                            <svg class="h-4.5 w-4.5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Income</p>
                            <p id="parkingKpiIncome" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Total Transactions --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                            <svg class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Transactions</p>
                            <p id="parkingKpiTxn" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Avg Duration --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 dark:bg-amber-500/10">
                            <svg class="h-4.5 w-4.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Avg Duration</p>
                            <p id="parkingKpiDuration" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Avg Income / Txn --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/10">
                            <svg class="h-4.5 w-4.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Avg / Txn</p>
                            <p id="parkingKpiAvg" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Row 1: Income Trend (full width) ──────────────────────────────── --}}
            <x-card-chart.card-shell subtitle="Parking" title="Income Trend"
                class="h-full flex flex-col"
                gradient="linear-gradient(to right,#0EA5E9,#6366F1)">
                <x-slot:headerEnd>
                    <div class="flex items-center gap-1">
                        <button id="parkingIncomePeriod_daily" type="button"
                            class="pgcard-tab-active rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">Daily</button>
                        <button id="parkingIncomePeriod_monthly" type="button"
                            class="pgcard-tab-idle rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">Monthly</button>
                    </div>
                </x-slot:headerEnd>
                <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                    <div id="parkingIncomeTrendChart" class="flex-1 min-h-65"></div>
                </div>
            </x-card-chart.card-shell>

            {{-- ── Row 2: Peak Hour Heatmap + Vehicle Type ────────────────────────── --}}
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

                <x-card-chart.card-shell subtitle="Parking" title="Peak Hour Heatmap"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="parkingPeakHourChart" class="flex-1 min-h-65"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.card-shell subtitle="Parking" title="Vehicle Type & Revenue Split"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#10B981,#0EA5E9)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="parkingVehicleTypeChart" class="flex-1 min-h-65"></div>
                    </div>
                </x-card-chart.card-shell>

            </div>

            {{-- ── Row 3: Payment Method + Member vs Non-member ───────────────────── --}}
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-2" style="align-items:stretch">

                <x-card-chart.card-shell subtitle="Parking" title="Payment Method Breakdown"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#8B5CF6,#EC4899)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="parkingPaymentChart" class="flex-1 min-h-55"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.card-shell subtitle="Parking" title="Member vs Non-Member"
                    class="h-full flex flex-col"
                    gradient="linear-gradient(to right,#6366F1,#10B981)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="parkingMemberChart" class="flex-1 min-h-55"></div>
                    </div>
                </x-card-chart.card-shell>

            </div>

            {{-- ── Row 4: Top Repetitive Nopol ────────────────────────────────────── --}}
            <x-card-chart.card-shell subtitle="Parking" title="Top Repetitive Vehicles (Nopol > 1 Visit)"
                class="h-full flex flex-col"
                gradient="linear-gradient(to right,#EF4444,#F59E0B,#10B981)">
                <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                    <div id="parkingRepetitiveChart" class="flex-1 min-h-60"></div>
                </div>
            </x-card-chart.card-shell>

        </div>{{-- /Parking Section --}}

        {{-- ── Valet Parking Section ──────────────────────────────────────────── --}}
        <div id="gmSectionValet" class="mt-2 space-y-3">

            {{-- KPI Strip --}}
            <div class="relative overflow-hidden rounded-2xl border border-slate-200 shadow-sm dark:border-slate-700/60">
                <div class="absolute inset-x-0 top-0 z-10 h-0.75"
                    style="background:linear-gradient(to right,#10B981,#3B82F6,#8B5CF6)"></div>
                <div class="grid grid-cols-1 gap-px bg-slate-100 dark:bg-slate-700/50 sm:grid-cols-3">

                    {{-- Total Income --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-500/10">
                            <svg class="h-4.5 w-4.5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Income</p>
                            <p id="valetKpiIncome" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Total Transactions --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 dark:bg-blue-500/10">
                            <svg class="h-4.5 w-4.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Total Transactions</p>
                            <p id="valetKpiTxn" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                    {{-- Avg Income / Txn --}}
                    <div class="flex min-w-0 items-center gap-3 bg-white px-4 py-3.5 dark:bg-slate-900 sm:gap-3.5 sm:px-5 sm:py-4">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-violet-50 dark:bg-violet-500/10">
                            <svg class="h-4.5 w-4.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Avg / Transaction</p>
                            <p id="valetKpiAvg" class="mt-0.5 text-lg font-extrabold tabular-nums tracking-tight text-slate-900 dark:text-white sm:text-2xl">—</p>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ── Row 1: Income Trend (full width, daily/monthly toggle) ─────────── --}}
            <x-card-chart.card-shell subtitle="Valet Parking" title="Income Trend"
                class="h-full flex flex-col"
                gradient="linear-gradient(to right,#10B981,#3B82F6)">
                <x-slot:headerEnd>
                    <div class="flex items-center gap-1">
                        <button id="valetTrendTab_daily" type="button"
                            class="pgcard-tab-active rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">Daily</button>
                        <button id="valetTrendTab_monthly" type="button"
                            class="pgcard-tab-idle rounded-lg px-2.5 py-1 text-[10px] font-semibold transition">Monthly</button>
                    </div>
                </x-slot:headerEnd>
                <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                    <div id="valetIncomeTrendChart" class="flex-1 min-h-65"></div>
                </div>
            </x-card-chart.card-shell>

            {{-- ── Row 2: Peak Hour Heatmap (2/3) + Repeat Visitors table (1/3) ───── --}}
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3" style="align-items:stretch">

                <x-card-chart.card-shell subtitle="Valet Parking" title="Peak Hour Heatmap"
                    class="h-full flex flex-col lg:col-span-2"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444)">
                    <div class="flex-1 px-3 pb-3 pt-0 flex flex-col min-h-0">
                        <div id="valetPeakHeatmap" class="flex-1 min-h-65"></div>
                    </div>
                </x-card-chart.card-shell>

                <x-card-chart.dynamic-table-card
                    subtitle="Valet Parking" title="Repeat Visitors"
                    gradient="linear-gradient(to right,#8B5CF6,#EC4899)"
                    tableBodyId="valetNopolBody"
                    countBadgeId="valetNopolCount"
                    paginationPrefix="valetNopol"
                    :columns="[
                        ['label' => '#'],
                        ['label' => 'Nopol',  'key' => 'nopol'],
                        ['label' => 'Owner',  'key' => 'owner'],
                        ['label' => 'Visits', 'key' => 'visit_count', 'numeric' => true],
                        ['label' => 'Total',  'key' => 'total_spent', 'numeric' => true],
                    ]" />

            </div>

            {{-- ── Row 3: Top 10 Transactions (full width) ─────────────────────── --}}
            <x-card-chart.dynamic-table-card
                subtitle="Valet Parking" title="Top 10 Transactions by Amount"
                gradient="linear-gradient(to right,#3B82F6,#10B981,#8B5CF6)"
                tableBodyId="valetTopTxnBody"
                countBadgeId="valetTopTxnCount"
                paginationPrefix=""
                :columns="[
                    ['label' => '#'],
                    ['label' => 'Nopol',      'key' => 'nopol'],
                    ['label' => 'Owner',      'key' => 'owner'],
                    ['label' => 'Location',   'key' => 'location'],
                    ['label' => 'Check-in',   'key' => 'checkin_date'],
                    ['label' => 'Duration',   'key' => 'duration_hour', 'numeric' => true],
                    ['label' => 'Total',      'key' => 'total_amount',  'numeric' => true],
                    ['label' => 'Voucher',    'key' => 'voucher_code'],
                ]" />

        </div>{{-- /Valet Section --}}

    </div>



    {{-- ── Route registry (shared across all GM section scripts) ─────────────── --}}
    <script>
        window.gmRoutes = {
            companies: "{{ route('gm.companies') }}",
            years: "{{ route('gm.budget-years') }}",
            departments: "{{ route('gm.departments') }}",
            summary: "{{ route('gm.budget-summary') }}",
            byDept: "{{ route('gm.budget-by-department') }}",
            byActivity: "{{ route('gm.budget-by-activity') }}",
            byMonth: "{{ route('gm.budget-by-month') }}",
            pgcardKpiSummary: "{{ route('gm.pgcard-kpi-summary') }}",
            pgcardMonthlyTrend: "{{ route('gm.pgcard-monthly-trend') }}",
            pgcardTopCustomers: "{{ route('gm.pgcard-top-customers') }}",
            pgcardTopTenants: "{{ route('gm.pgcard-top-tenants') }}",
            pgcardCouponStyw: "{{ route('gm.pgcard-coupon-styw') }}",
            pgcardCouponStywCompare: "{{ route('gm.pgcard-coupon-styw-compare') }}",
            pgcardCampaignSamples: "{{ route('gm.pgcard-campaign-samples') }}",
            isortSummary: "{{ route('gm.isort-summary') }}",
            isortKaizenByType: "{{ route('gm.isort-kaizen-by-type') }}",
            isortIncidents: "{{ route('gm.isort-incidents') }}",
            isortDeptSummary: "{{ route('gm.isort-dept-summary') }}",
            isortAvailableDepts: "{{ route('gm.isort-available-depts') }}",
            isortMonthlyTrend: "{{ route('gm.isort-monthly-trend') }}",
            isortTopAreas: "{{ route('gm.isort-top-areas') }}",
            parkingSites: "{{ route('gm.parking-sites') }}",
            valetIncomeTrend:    "{{ route('gm.valet-income-trend') }}",
            valetPeakHour:       "{{ route('gm.valet-peak-hour') }}",
            valetRepetitiveNopol:"{{ route('gm.valet-repetitive-nopol') }}",
            valetTopTransactions:"{{ route('gm.valet-top-transactions') }}",
        };
    </script>

    {{-- Load order: core → filter → [section scripts] --}}
    <script src="{{ asset('assets/js/gm-report/gm-core.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-filter.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-budget.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-pgcard.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-isort.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-parking.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-valet.js') }}"></script>

    {{-- ── Export link updater ─────────────────────────────────────────────────
         Runs every time the filter changes so the download URL always carries
         the currently-selected company / date range / departments.
    --}}
    <script>
        (function() {
            var exportRoutes = {
                pdf: '{{ route('gm.export.pdf') }}',
                csv: '{{ route('gm.export.csv') }}',
                xlsx: '{{ route('gm.export.xlsx') }}',
            };

            function updateExportLinks() {
                var params = window.gmUtils ? window.gmUtils.buildParams() : '';
                ['pdf', 'csv', 'xlsx'].forEach(function(fmt) {
                    var el = document.getElementById('gmExport_' + fmt);
                    if (el) el.href = exportRoutes[fmt] + params;
                });
            }

            document.addEventListener('gm:filter', updateExportLinks);

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                var wrap = document.getElementById('gmExportWrap');
                var dd = document.getElementById('gmExportDropdown');
                if (wrap && dd && !wrap.contains(e.target)) {
                    dd.classList.add('hidden');
                }
            });
        })();
    </script>

    <script>
        (function() {
            const anchor = document.getElementById('gmFilterAnchor');
            const floatBox = document.getElementById('gmFilterFloat');
            const header = document.getElementById('gmPageHeader');
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
        (function() {
            var sections = {
                budget:  'gmSectionBudget',
                pgcard:  'gmSectionPgcard',
                isort:   'gmSectionIsort',
                parking: 'gmSectionParking',
                valet:   'gmSectionValet'
            };
            var headers = {
                budget:  'gmSectionBudgetHeader',
                pgcard:  'gmSectionPgcardHeader',
                isort:   'gmSectionIsortHeader',
                parking: 'gmSectionParkingHeader',
                valet:   'gmSectionValetHeader'
            };
            var tabIds = ['gmTab_all', 'gmTab_budget', 'gmTab_pgcard', 'gmTab_isort', 'gmTab_parking', 'gmTab_valet'];

            function switchTab(key) {
                tabIds.forEach(function(id) {
                    var btn = document.getElementById(id);
                    if (btn) btn.classList.toggle('active', id === 'gmTab_' + key);
                });
                Object.keys(sections).forEach(function(sec) {
                    var section = document.getElementById(sections[sec]);
                    var header = document.getElementById(headers[sec]);
                    var hidden = key !== 'all' && key !== sec;
                    if (section) section.classList.toggle('hidden', hidden);
                    if (header) header.classList.toggle('hidden', key !== 'all');
                });
                try {
                    localStorage.setItem('gmActiveTab', key);
                } catch (e) {}
                document.dispatchEvent(new CustomEvent('gm:tab-switch', {
                    detail: {
                        tab: key
                    }
                }));
            }

            tabIds.forEach(function(id) {
                var btn = document.getElementById(id);
                if (btn) btn.addEventListener('click', function() {
                    switchTab(id.replace('gmTab_', ''));
                });
            });

            var saved = 'all';
            try {
                saved = localStorage.getItem('gmActiveTab') || 'all';
            } catch (e) {}
            switchTab(saved);
        })();
    </script>

</x-app-layout>
