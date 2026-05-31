<x-app-layout>

<div class="max-w-9xl mx-auto w-full space-y-8 p-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Chart Card Catalog</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All reusable chart templates — preview with sample data.</p>
        </div>
        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
            14 Templates
        </span>
    </div>

    {{-- ─── KPI / STAT CARDS ─────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">KPI / Stat Cards</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-card-chart.stat-card
                title="Total Revenue"
                subtitle="Finance"
                value="Rp 4.280.000"
                trend="+18.4%"
                :trendUp="true"
                description="vs last month"
                color="violet"
                icon="💰"
            />

            <x-card-chart.stat-card
                title="Active Employees"
                subtitle="HR"
                value="1,248"
                trend="+3.2%"
                :trendUp="true"
                description="vs last quarter"
                color="blue"
                icon="👥"
            />

            <x-card-chart.stat-card
                title="Open Purchase Orders"
                subtitle="Purchasing"
                value="87"
                trend="-5.1%"
                :trendUp="false"
                description="vs last month"
                color="orange"
                icon="📦"
            />

            <x-card-chart.stat-card
                title="Pending Approvals"
                subtitle="Workflow"
                value="23"
                trend="+12%"
                :trendUp="false"
                description="needs attention"
                color="red"
                icon="⚠️"
            />

        </div>
    </section>

    {{-- ─── LINE CHARTS ──────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Line Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.line-chart
                title="Revenue Trend"
                subtitle="Monthly"
                chartId="line-demo-1"
                color="violet"
                :height="280"
            />

            <x-card-chart.line-chart
                title="Multi-Series Comparison"
                subtitle="Year over Year"
                chartId="line-demo-2"
                color="blue"
                :height="280"
            />

        </div>
    </section>

    {{-- ─── AREA CHARTS ──────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Area Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.area-chart
                title="Revenue vs Expenses"
                subtitle="Year to Date"
                chartId="area-demo-1"
                color="green"
                :height="280"
            />

            <x-card-chart.area-chart
                title="Stacked Area"
                subtitle="Department Breakdown"
                chartId="area-demo-2"
                color="cyan"
                :height="280"
                :stacked="true"
            />

        </div>
    </section>

    {{-- ─── COLUMN & BAR CHARTS ─────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Column & Bar Charts</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.column-chart
                title="Quarterly Performance"
                subtitle="Clustered Column"
                chartId="col-demo-1"
                color="violet"
                :height="280"
            />

            <x-card-chart.bar-chart
                title="Department Targets"
                subtitle="Horizontal Bar"
                chartId="bar-demo-1"
                color="blue"
                :height="280"
            />

        </div>
    </section>

    {{-- ─── DONUT & PIE ─────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Donut & Pie Charts</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-card-chart.donut-chart
                title="Traffic Sources"
                subtitle="Donut"
                chartId="donut-demo-1"
                color="violet"
                :height="300"
            />

            <x-card-chart.donut-chart
                title="Budget Allocation"
                subtitle="Donut"
                chartId="donut-demo-2"
                color="orange"
                :height="300"
            />

            <x-card-chart.pie-chart
                title="Product Mix"
                subtitle="Pie"
                chartId="pie-demo-1"
                color="green"
                :height="300"
            />

            <x-card-chart.pie-chart
                title="Cost Breakdown"
                subtitle="Pie"
                chartId="pie-demo-2"
                color="pink"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── COMBO CHART ─────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Combo Chart (Column + Line)</h2>
        <div class="grid grid-cols-1 gap-4">

            <x-card-chart.combo-chart
                title="Revenue & Growth Rate"
                subtitle="Monthly Performance"
                chartId="combo-demo-1"
                color="blue"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── GAUGE ───────────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Gauge Charts</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-card-chart.gauge-chart
                title="Target Achievement"
                subtitle="Sales"
                chartId="gauge-demo-1"
                color="green"
                :value="82"
                :max="100"
                label="Achieved"
                :height="260"
            />

            <x-card-chart.gauge-chart
                title="Budget Utilization"
                subtitle="Finance"
                chartId="gauge-demo-2"
                color="orange"
                :value="65"
                :max="100"
                label="Used"
                :height="260"
            />

            <x-card-chart.gauge-chart
                title="Approval Rate"
                subtitle="Workflow"
                chartId="gauge-demo-3"
                color="violet"
                :value="91"
                :max="100"
                label="Approved"
                :height="260"
            />

            <x-card-chart.gauge-chart
                title="SLA Compliance"
                subtitle="IT"
                chartId="gauge-demo-4"
                color="cyan"
                :value="47"
                :max="100"
                label="On-time"
                :height="260"
            />

        </div>
    </section>

    {{-- ─── WATERFALL ────────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Waterfall Chart</h2>
        <div class="grid grid-cols-1 gap-4">

            <x-card-chart.waterfall-chart
                title="Cash Flow Breakdown"
                subtitle="Running Total"
                chartId="waterfall-demo-1"
                color="cyan"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── FUNNEL ───────────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Funnel Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.funnel-chart
                title="Sales Pipeline"
                subtitle="Lead Conversion"
                chartId="funnel-demo-1"
                color="orange"
                :height="320"
            />

            <x-card-chart.funnel-chart
                title="Recruitment Funnel"
                subtitle="HR Pipeline"
                chartId="funnel-demo-2"
                color="violet"
                :height="320"
            />

        </div>
    </section>

    {{-- ─── SCATTER ─────────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Scatter Chart</h2>
        <div class="grid grid-cols-1 gap-4">

            <x-card-chart.scatter-chart
                title="Cost vs Performance"
                subtitle="Correlation Analysis"
                chartId="scatter-demo-1"
                color="pink"
                xLabel="Cost"
                yLabel="Performance"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── TREEMAP ─────────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Treemap Chart</h2>
        <div class="grid grid-cols-1 gap-4">

            <x-card-chart.treemap-chart
                title="Department Headcount"
                subtitle="Proportional View"
                chartId="treemap-demo-1"
                color="violet"
                :height="320"
            />

        </div>
    </section>

    {{-- ─── TABLE CARD ──────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Table Card</h2>
        <div class="grid grid-cols-1 gap-4">

            <x-card-chart.table-card
                title="Recent Transactions"
                subtitle="Finance"
                color="blue"
                :columns="['Document', 'Date', 'Department', 'Amount', 'Status']"
                :rows="[
                    ['PO-2026-0881', '2026-05-31', 'Operations', 'Rp 4.500.000', 'Approved'],
                    ['PO-2026-0880', '2026-05-30', 'IT', 'Rp 12.000.000', 'Pending'],
                    ['PO-2026-0879', '2026-05-29', 'Marketing', 'Rp 2.750.000', 'Approved'],
                    ['PO-2026-0878', '2026-05-28', 'HR', 'Rp 8.300.000', 'Rejected'],
                    ['PO-2026-0877', '2026-05-27', 'Finance', 'Rp 1.200.000', 'Approved'],
                ]"
            />

        </div>
    </section>

</div>

</x-app-layout>
