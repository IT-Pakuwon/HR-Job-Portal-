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
        <div class="space-y-2">

            {{-- Charts Row --}}
            <div class="grid grid-cols-1 gap-3 lg:grid-cols-3">

                {{-- 1. Budget vs Remaining + Breakdown (stacked left column) --}}
                <div class="grid gap-3">
                    <x-card-chart.split-stat-card
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

                    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
                    <x-card-chart.card-shell
                        subtitle="Breakdown"
                        title="Used · Remaining"
                        gradient="linear-gradient(to right,#EF4444,#10B981)"
                        {{-- legendPosition="right" --}}
                    >
                        <div class="px-2 pb-2 pt-0">
                            <div id="gmBudgetDonut" style="min-height:210px"></div>
                        </div>
                    </x-card-chart.card-shell>

                    </div>

                </div>

                {{-- 2. By Department --}}
                <x-card-chart.dynamic-table-card
                    subtitle="By Department"
                    title="Budget Usage per Department"
                    gradient="linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)"
                    tableBodyId="gmDeptTableBody"
                    countBadgeId="gmDeptCount"
                    paginationPrefix="gmDept"
                    :columns="[
                        ['label' => 'Department', 'key' => 'department_fin_id'],
                        ['label' => 'Budget',     'key' => 'total_final',     'numeric' => true],
                        ['label' => 'Remaining',  'key' => 'total_remaining', 'numeric' => true],
                        ['label' => 'Usage %',    'key' => 'used_pct',        'numeric' => true],
                    ]"
                />

                {{-- 3. By Activity --}}
                <x-card-chart.dynamic-table-card
                    subtitle="By Activity"
                    title="Budget Usage by Activity"
                    gradient="linear-gradient(to right,#06B6D4,#3B82F6,#8B5CF6)"
                    tableBodyId="gmActTableBody"
                    countBadgeId="gmActCount"
                    paginationPrefix="gmAct"
                    :columns="[
                        ['label' => 'Description', 'key' => 'activity_descr'],
                        ['label' => 'Budget',      'key' => 'total_final',     'numeric' => true],
                        ['label' => 'Remaining',   'key' => 'total_remaining', 'numeric' => true],
                        ['label' => 'Usage %',     'key' => 'used_pct',        'numeric' => true],
                    ]"
                />

            </div>

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
