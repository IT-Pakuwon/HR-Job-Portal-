<x-app-layout>

    <div class="max-w-9xl mx-auto w-full space-y-2 p-2">

        {{-- ── Page Header ──────────────────────────────────────────────────────── --}}
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div>
                <h1 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    GM Report Dashboard
                </h1>
                <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                Overview
                <span id="gmPeriodLabel" class="ml-1 text-violet-500"></span>
            </p>
            </div>

            {{-- ── Filter Bar ─────────────────────────────────────────────────── --}}
            <x-dashboard-filter.dashboard-filter />
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



    </div>

    {{-- ── Route registry (shared across all GM section scripts) ─────────────── --}}
    <script>
        window.gmRoutes = {
            companies  : "{{ route('gm.companies') }}",
            years      : "{{ route('gm.budget-years') }}",
            departments: "{{ route('gm.departments') }}",
            summary    : "{{ route('gm.budget-summary') }}",
            byDept     : "{{ route('gm.budget-by-department') }}",
            byActivity : "{{ route('gm.budget-by-activity') }}",
            byMonth    : "{{ route('gm.budget-by-month') }}",
        };
    </script>

    {{-- Load order: core → filter → [section scripts] --}}
    {{-- core  : shared state (gmState), utilities (gmUtils), event bus (gmDispatchFilter) --}}
    {{-- filter: filter bar UI — fires gm:filter when state changes --}}
    {{-- budget: budget section — listens for gm:filter, owns its own fetches & charts --}}
    <script src="{{ asset('assets/js/gm-report/gm-core.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-filter.js') }}"></script>
    <script src="{{ asset('assets/js/gm-report/gm-budget.js') }}"></script>

</x-app-layout>
