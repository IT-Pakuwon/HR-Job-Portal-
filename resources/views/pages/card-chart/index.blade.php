<x-app-layout>

<div class="max-w-9xl mx-auto w-full space-y-8 p-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Chart Card Catalog</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All reusable chart templates — preview with sample data.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
                32 Templates
            </span>
            <a href="{{ route('card-chart.drag-dashboard') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700 active:scale-95 dark:bg-violet-500 dark:hover:bg-violet-600">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 20 20">
                    <path d="M3 5h4M3 10h4M3 15h4M11 5h6M11 10h6M11 15h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M7 3v4M7 13v4M17 3v4M17 13v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" opacity=".5"/>
                </svg>
                Customize Drag Dashboard
            </a>
        </div>
    </div>

    {{-- ─── KPI / STAT CARDS ─────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">KPI / Stat Cards</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

            <x-card-chart.split-stat-card
                color="green"
                leftLabel="Total Budget"
                leftValue="Rp 24.5M"
                leftDescription="Original + Additional"
                rightLabel="Remaining"
                rightValue="Rp 8.2M"
                barLabel="Utilization"
                barPct="66.5%"
            />

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
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-6">

            {{-- legend: bottom (default) --}}
            <x-card-chart.donut-chart
                title="Traffic Sources"
                subtitle="Donut · Legend Bottom"
                chartId="donut-demo-1"
                color="violet"
                :height="300"
                legendPosition="bottom"
            />

            {{-- legend: top --}}
            <x-card-chart.donut-chart
                title="Budget Allocation"
                subtitle="Donut · Legend Top"
                chartId="donut-demo-2"
                color="orange"
                :height="300"
                legendPosition="top"
            />

            {{-- legend: left --}}
            <x-card-chart.donut-chart
                title="Cost Mix"
                subtitle="Donut · Legend Left"
                chartId="donut-demo-3"
                color="cyan"
                :height="300"
                legendPosition="left"
            />

            {{-- pie legend: top --}}
            <x-card-chart.pie-chart
                title="Product Mix"
                subtitle="Pie · Legend Top"
                chartId="pie-demo-1"
                color="green"
                :height="300"
                legendPosition="top"
            />

            {{-- pie legend: bottom (default) --}}
            <x-card-chart.pie-chart
                title="Cost Breakdown"
                subtitle="Pie · Legend Bottom"
                chartId="pie-demo-2"
                color="pink"
                :height="300"
                legendPosition="bottom"
            />

            {{-- pie legend: left --}}
            <x-card-chart.pie-chart
                title="Revenue Split"
                subtitle="Pie · Legend Left"
                chartId="pie-demo-3"
                color="blue"
                :height="300"
                legendPosition="right"
            />

        </div>
    </section>

    {{-- ─── BREAKDOWN DONUT CARD ────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Breakdown Donut Card</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Default: Used / Reserved / Remaining --}}
            <x-card-chart.breakdown-donut-card
                subtitle="Breakdown"
                chartId="breakdown-demo-1"
                color="green"
                :height="220"
                :labels="['Used','Reserved','Remaining']"
                :series="[1.3, 1.1, 97.6]"
                totalLabel="Total"
                totalValue="Rp 503,5M"
            />

            {{-- Budget breakdown --}}
            <x-card-chart.breakdown-donut-card
                subtitle="Budget"
                chartId="breakdown-demo-2"
                color="violet"
                :height="220"
                :labels="['Spent','Committed','Available']"
                :series="[42, 18, 40]"
                :colors="['#EF4444','#F59E0B','#8B5CF6']"
                totalLabel="Total"
                totalValue="Rp 1,2B"
            />

            {{-- Headcount breakdown --}}
            <x-card-chart.breakdown-donut-card
                subtitle="Headcount"
                chartId="breakdown-demo-3"
                color="blue"
                :height="220"
                :labels="['Active','On Leave','Contract']"
                :series="[280, 24, 38]"
                :colors="['#3B82F6','#F59E0B','#06B6D4']"
                totalLabel="Total"
                totalValue="342"
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

    {{-- ─── SPARKLINE STAT CARDS ───────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Sparkline Stat Cards</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            {{-- 1 wave --}}
            <x-card-chart.sparkline-stat-card
                title="Monthly Revenue"
                subtitle="Finance"
                value="Rp 128M"
                trend="+14.2%"
                :trendUp="true"
                description="vs last month"
                color="violet"
                chartId="spark-demo-1"
                icon="💰"
                :waves="1"
            />

            {{-- 1 wave --}}
            <x-card-chart.sparkline-stat-card
                title="Active Users"
                subtitle="Platform"
                value="3,481"
                trend="+8.7%"
                :trendUp="true"
                description="vs last week"
                color="blue"
                chartId="spark-demo-2"
                icon="👥"
                :waves="1"
            />

            {{-- 2 waves --}}
            <x-card-chart.sparkline-stat-card
                title="Open Tickets"
                subtitle="Support"
                value="57"
                trend="-12%"
                :trendUp="true"
                description="vs last month"
                color="green"
                chartId="spark-demo-3"
                icon="🎫"
                :waves="2"
            />

            {{-- 2 waves --}}
            <x-card-chart.sparkline-stat-card
                title="Failed Jobs"
                subtitle="Operations"
                value="14"
                trend="+31%"
                :trendUp="false"
                description="needs attention"
                color="red"
                chartId="spark-demo-4"
                icon="⚠️"
                :waves="2"
            />

        </div>
    </section>

    {{-- ─── KPI CARDS ───────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">KPI Cards (with Target Progress)</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-card-chart.kpi-card
                title="Sales Revenue"
                subtitle="Finance"
                value="Rp 84.5M"
                target="120"
                unit="M"
                trend="+18%"
                :trendUp="true"
                description="vs last quarter"
                color="green"
                icon="📈"
            />

            <x-card-chart.kpi-card
                title="Headcount"
                subtitle="HR"
                value="342"
                target="400"
                trend="+14"
                :trendUp="true"
                description="new hires this month"
                color="blue"
                icon="👥"
            />

            <x-card-chart.kpi-card
                title="Budget Spent"
                subtitle="Cost Control"
                value="Rp 67M"
                target="100"
                unit="M"
                trend="+5%"
                :trendUp="false"
                description="burn rate up"
                color="orange"
                icon="💳"
            />

            <x-card-chart.kpi-card
                title="SLA Compliance"
                subtitle="IT Operations"
                value="91%"
                target="100"
                trend="-2%"
                :trendUp="false"
                description="vs last month"
                color="violet"
                icon="✅"
            />

        </div>
    </section>

    {{-- ─── MULTI STAT CARDS ────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Multi-Stat Cards (Grid Metrics)</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <x-card-chart.multi-stat-card
                title="Finance Overview"
                subtitle="This Month"
                color="green"
                :cols="2"
            />

            <x-card-chart.multi-stat-card
                title="HR Summary"
                subtitle="Current Period"
                color="blue"
                :cols="2"
                :items="[
                    ['label' => 'Headcount',   'value' => '342',  'trend' => '+14',  'trendUp' => true,  'description' => 'new hires',    'color' => 'blue'],
                    ['label' => 'Attrition',   'value' => '5',    'trend' => '-2',   'trendUp' => true,  'description' => 'vs last month', 'color' => 'green'],
                    ['label' => 'Open Roles',  'value' => '28',   'trend' => '+6',   'trendUp' => false, 'description' => 'unfilled',      'color' => 'orange'],
                    ['label' => 'On Leave',    'value' => '19',   'trend' => '+4',   'trendUp' => false, 'description' => 'this week',     'color' => 'red'],
                ]"
            />

            <x-card-chart.multi-stat-card
                title="IT Dashboard"
                subtitle="Live"
                color="cyan"
                :cols="2"
                :items="[
                    ['label' => 'Uptime',      'value' => '99.8', 'unit' => '%',  'trend' => '+0.1%', 'trendUp' => true,  'description' => 'this week',  'color' => 'green'],
                    ['label' => 'Open Tickets','value' => '57',                   'trend' => '-8',    'trendUp' => true,  'description' => 'vs yesterday','color' => 'blue'],
                    ['label' => 'Incidents',   'value' => '3',                    'trend' => '+2',    'trendUp' => false, 'description' => 'active',      'color' => 'red'],
                    ['label' => 'Deployments', 'value' => '12',                   'trend' => '+3',    'trendUp' => true,  'description' => 'this week',   'color' => 'violet'],
                ]"
            />

        </div>
    </section>

    {{-- ─── PROGRESS LIST CARDS ─────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Progress List Cards</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <x-card-chart.progress-list-card
                title="Department Performance"
                subtitle="KPI Achievement"
                color="violet"
            />

            <x-card-chart.progress-list-card
                title="Budget Utilization"
                subtitle="By Cost Center"
                color="blue"
                :items="[
                    ['label' => 'Operations',  'value' => 78, 'badge' => 'Rp 78M',  'color' => 'blue'],
                    ['label' => 'Marketing',   'value' => 92, 'badge' => 'Rp 92M',  'color' => 'violet'],
                    ['label' => 'IT',          'value' => 45, 'badge' => 'Rp 45M',  'color' => 'cyan'],
                    ['label' => 'HR',          'value' => 61, 'badge' => 'Rp 61M',  'color' => 'green'],
                    ['label' => 'Finance',     'value' => 33, 'badge' => 'Rp 33M',  'color' => 'orange'],
                ]"
            />

            <x-card-chart.progress-list-card
                title="Project Completion"
                subtitle="Active Sprints"
                color="green"
                :items="[
                    ['label' => 'Portal Redesign',    'value' => 95, 'badge' => '95%', 'color' => 'green'],
                    ['label' => 'API Integration',    'value' => 72, 'badge' => '72%', 'color' => 'blue'],
                    ['label' => 'Mobile App',         'value' => 48, 'badge' => '48%', 'color' => 'violet'],
                    ['label' => 'Analytics Module',   'value' => 30, 'badge' => '30%', 'color' => 'orange'],
                    ['label' => 'Compliance Audit',   'value' => 15, 'badge' => '15%', 'color' => 'red'],
                ]"
            />

        </div>
    </section>

    {{-- ─── HEATMAP CHART ───────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Heatmap Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.heatmap-chart
                title="Weekly Activity Matrix"
                subtitle="Attendance by Day"
                chartId="heatmap-demo-1"
                color="blue"
                :height="280"
            />

            <x-card-chart.heatmap-chart
                title="Monthly Work Intensity"
                subtitle="Department Activity"
                chartId="heatmap-demo-2"
                color="violet"
                :height="280"
            />

        </div>
    </section>

    {{-- ─── RADIAL BAR CHART ────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Radial Bar Chart</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            <x-card-chart.radial-bar-chart
                title="KPI Achievement"
                subtitle="Multi-Metric Progress"
                chartId="radial-demo-1"
                color="violet"
                :height="300"
                legendPosition="bottom"
            />

            <x-card-chart.radial-bar-chart
                title="Department Goals"
                subtitle="Q2 2026"
                chartId="radial-demo-2"
                color="green"
                :height="300"
                :series="[92, 78, 65, 85]"
                :labels="['Sales','HR','IT','Finance']"
                totalLabel="Avg"
            />

            <x-card-chart.radial-bar-chart
                title="SLA Compliance"
                subtitle="Service Levels"
                chartId="radial-demo-3"
                color="cyan"
                :height="300"
                :series="[99, 87, 76]"
                :labels="['Uptime','Response','Resolution']"
                totalLabel="Avg"
            />

        </div>
    </section>

    {{-- ─── POLAR AREA CHART ────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Polar Area Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.polar-area-chart
                title="Project Phase Distribution"
                subtitle="Current Cycle"
                chartId="polar-demo-1"
                color="blue"
                :height="300"
            />

            <x-card-chart.polar-area-chart
                title="Cost Allocation by Division"
                subtitle="YTD Spend"
                chartId="polar-demo-2"
                color="orange"
                :height="300"
                :series="[120, 95, 75, 60, 45, 30]"
                :labels="['Operations','Marketing','IT','HR','Finance','Legal']"
            />

        </div>
    </section>

    {{-- ─── CANDLESTICK CHART ───────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Candlestick Chart (OHLC)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.candlestick-chart
                title="Stock Price Movement"
                subtitle="Daily OHLC"
                chartId="candle-demo-1"
                color="green"
                :height="300"
            />

            <x-card-chart.candlestick-chart
                title="Exchange Rate Fluctuation"
                subtitle="USD / IDR"
                chartId="candle-demo-2"
                color="cyan"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── RANGE BAR / TIMELINE ────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Range Bar / Timeline Chart</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.range-bar-chart
                title="Project Timeline"
                subtitle="Gantt View"
                chartId="rangebar-demo-1"
                color="cyan"
                :height="300"
            />

            <x-card-chart.range-bar-chart
                title="Leave Schedule"
                subtitle="Team Calendar"
                chartId="rangebar-demo-2"
                color="violet"
                :height="300"
                :series="[
                    ['name' => 'Alice', 'data' => [['x' => 'Leave', 'y' => [1751241600000, 1751500800000]]]],
                    ['name' => 'Bob',   'data' => [['x' => 'Leave', 'y' => [1751328000000, 1751673600000]]]],
                    ['name' => 'Citra', 'data' => [['x' => 'Leave', 'y' => [1751500800000, 1751760000000]]]],
                    ['name' => 'Doni',  'data' => [['x' => 'Leave', 'y' => [1751155200000, 1751328000000]]]],
                ]"
            />

        </div>
    </section>

    {{-- ─── BUBBLE CHART ───────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Bubble Chart (3-Variable Analysis)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.bubble-chart
                title="Property Portfolio Analysis"
                subtitle="Real Estate · Price vs Yield vs Area"
                chartId="bubble-demo-1"
                color="blue"
                xLabel="Price (M IDR)"
                yLabel="Gross Yield (%)"
                :height="300"
            />

            <x-card-chart.bubble-chart
                title="Campaign Performance Matrix"
                subtitle="Marketing · Spend vs Conversion vs Reach"
                chartId="bubble-demo-2"
                color="violet"
                xLabel="Ad Spend (M)"
                yLabel="Conversion Rate (%)"
                :series="[
                    ['name' => 'Instagram', 'data' => [['x'=>12,'y'=>4.5,'z'=>85],['x'=>25,'y'=>5.2,'z'=>140],['x'=>8,'y'=>3.8,'z'=>60]]],
                    ['name' => 'Google Ads','data' => [['x'=>18,'y'=>6.1,'z'=>110],['x'=>35,'y'=>7.4,'z'=>200],['x'=>10,'y'=>5.5,'z'=>75]]],
                    ['name' => 'TikTok',    'data' => [['x'=>15,'y'=>8.2,'z'=>95], ['x'=>28,'y'=>9.5,'z'=>160]]],
                ]"
                :height="300"
            />

        </div>
    </section>

    {{-- ─── RADAR / SPIDER CHART ────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Radar / Spider Chart (Multi-Attribute Comparison)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.radar-chart
                title="Property Feature Scoring"
                subtitle="Real Estate · Location Analysis"
                chartId="radar-demo-1"
                color="violet"
                :height="300"
                :series="[
                    ['name' => 'Villa Almyra', 'data' => [90, 85, 70, 95, 80, 75]],
                    ['name' => 'Grand Duta',   'data' => [75, 92, 85, 70, 65, 88]],
                ]"
                :categories="['Location','Amenities','ROI','Security','Connectivity','Views']"
            />

            <x-card-chart.radar-chart
                title="Marketing Channel Effectiveness"
                subtitle="Q2 2026 · Channel Performance"
                chartId="radar-demo-2"
                color="orange"
                :height="300"
                :series="[
                    ['name' => 'Q1 2026', 'data' => [80, 72, 65, 90, 58, 77]],
                    ['name' => 'Q2 2026', 'data' => [88, 60, 75, 82, 71, 83]],
                ]"
                :categories="['Social Media','Email','SEO','Paid Ads','Events','Referral']"
            />

        </div>
    </section>

    {{-- ─── BOX PLOT CHART ─────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Box Plot Chart (Distribution & Price Range)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.boxplot-chart
                title="Property Price Distribution"
                subtitle="Real Estate · By District (M IDR)"
                chartId="boxplot-demo-1"
                color="cyan"
                :height="300"
            />

            <x-card-chart.boxplot-chart
                title="Lease Value Distribution"
                subtitle="Leasing · By Property Type (M/yr)"
                chartId="boxplot-demo-2"
                color="green"
                :height="300"
                :series="[['data' => [
                    ['x' => 'Residential', 'y' => [80,  120, 180, 260, 420]],
                    ['x' => 'Commercial',  'y' => [150, 250, 380, 550, 900]],
                    ['x' => 'Industrial',  'y' => [200, 350, 500, 750, 1200]],
                    ['x' => 'Retail',      'y' => [100, 180, 280, 420, 700]],
                ]]]"
            />

        </div>
    </section>

    {{-- ─── CUSTOMER TRAFFIC CHART ──────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Customer Traffic Chart (Movement & Flow)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.traffic-chart
                title="Showroom Foot Traffic"
                subtitle="Real Estate · Hourly Visitor Count"
                chartId="traffic-demo-1"
                color="orange"
                :height="280"
            />

            <x-card-chart.traffic-chart
                title="Event Attendance Flow"
                subtitle="Event · Arrival Pattern by Hour"
                chartId="traffic-demo-2"
                color="violet"
                :height="280"
                :categories="['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00']"
                :series="[38, 72, 95, 148, 220, 180, 110, 95, 130, 160, 85]"
                :peakThreshold="120"
            />

        </div>
    </section>

    {{-- ─── SANKEY / CUSTOMER ZONE MOVEMENT ───────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Customer Zone Movement — Sankey / Alluvial Flow</h2>
        <p class="mb-4 -mt-1 text-xs text-slate-500 dark:text-slate-400">
            Bands show how customers transition between zones (floors, areas) over time.
            Band width = number of people making that move. Darker bands = cross-zone movement; lighter = staying in the same zone.
        </p>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            {{-- Mall: 3 floors, 4 hourly snapshots --}}
            <x-card-chart.sankey-chart
                title="Mall Customer Flow"
                subtitle="Real Estate · Zone Movement by Hour"
                chartId="sankey-demo-1"
                color="blue"
                :height="380"
            />

            {{-- Event venue: 4 zones, 5 time periods --}}
            <x-card-chart.sankey-chart
                title="Event Venue Visitor Flow"
                subtitle="Event · Zone Distribution Over Time"
                chartId="sankey-demo-2"
                color="violet"
                :height="380"
                :times="['Open', 'Hour 1', 'Hour 2', 'Hour 3', 'Close']"
                :zones="['Stage Area', 'Exhibition', 'F&B Zone', 'VIP Lounge']"
                :counts="[
                    [200, 250, 300, 220, 80],
                    [150, 180, 160, 200, 90],
                    [ 80, 120, 200, 250, 130],
                    [ 50,  50,  40,  30, 100],
                ]"
            />

        </div>

        {{-- Full-width: Leasing/Property — showing tenant movement across zones --}}
        <div class="mt-4">
            <x-card-chart.sankey-chart
                title="Shopping Mall Tenant Zone — Full Day"
                subtitle="Leasing · Customer Movement Pattern (9 AM – 5 PM)"
                chartId="sankey-demo-3"
                color="green"
                :height="360"
                :times="['9 AM', '10 AM', '11 AM', '12 PM', '1 PM', '2 PM', '3 PM', '4 PM', '5 PM']"
                :zones="['Ground Floor', 'Lower Ground', 'Upper Floor', 'Food Court']"
                :counts="[
                    [180, 150, 120, 160, 200, 170, 140, 130, 100],
                    [ 60,  90, 110,  70,  40,  55,  80,  70,  50],
                    [ 30,  50,  80, 100,  90,  70,  60,  80,  60],
                    [ 30,  60, 100, 170, 170, 105,  70,  70,  90],
                ]"
            />
        </div>
    </section>

    {{-- ─── EVENT TIMELINE CARD ─────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Event Timeline Card (Milestone Tracker)</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">

            {{-- Default: property marketing campaign --}}
            <x-card-chart.timeline-card
                title="Property Campaign Plan"
                subtitle="Marketing · Q1 2026"
                color="violet"
            />

            <x-card-chart.timeline-card
                title="Lease Renewal Schedule"
                subtitle="Leasing · Upcoming Milestones"
                color="cyan"
                :items="[
                    ['date' => '01 Jun', 'label' => 'Pre-Renewal Notice',  'description' => 'Send 90-day renewal reminders to tenants', 'color' => 'cyan',   'done' => true],
                    ['date' => '15 Jun', 'label' => 'Negotiation Window',  'description' => 'Rate review & new terms discussion',       'color' => 'blue',   'done' => true],
                    ['date' => '01 Aug', 'label' => 'Contract Signing',    'description' => 'Execute renewal agreements',               'color' => 'green',  'done' => false],
                    ['date' => '01 Sep', 'label' => 'New Lease Period',    'description' => 'Renewed leases officially take effect',    'color' => 'violet', 'done' => false],
                ]"
            />

            <x-card-chart.timeline-card
                title="Promo Event Roadmap"
                subtitle="Promotion · Events Calendar"
                color="orange"
                :items="[
                    ['date' => '05 Jun', 'label' => 'Grand Launch',      'description' => 'Project reveal & media briefing',       'color' => 'orange', 'done' => true],
                    ['date' => '20 Jun', 'label' => 'Referral Program',  'description' => 'Activate agent referral bonuses',       'color' => 'green',  'done' => false],
                    ['date' => '10 Jul', 'label' => 'Property Expo',     'description' => 'Booth at Jakarta Property Expo 2026',   'color' => 'blue',   'done' => false],
                    ['date' => '01 Aug', 'label' => 'Final Promo Ends',  'description' => 'Early-bird discount deadline closes',   'color' => 'red',    'done' => false],
                ]"
            />

        </div>
    </section>

    {{-- ─── TABLE CARD ──────────────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Table Card (Static)</h2>
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

    {{-- ─── DYNAMIC TABLE CARD ─────────────────────────────────────────────── --}}
    <section>
        <h2 class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Dynamic Table Card (JS-driven)</h2>
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">

            <x-card-chart.dynamic-table-card
                title="Budget by Department"
                subtitle="By Department"
                gradient="linear-gradient(to right,#F59E0B,#EF4444,#8B5CF6)"
                :columns="['Department', 'Budget', 'Remaining', 'Usage %']"
                tableBodyId="catalog-dept-body"
                countBadgeId="catalog-dept-count"
                paginationPrefix="catalogDept"
            />

            <x-card-chart.dynamic-table-card
                title="Budget by Activity"
                subtitle="By Activity"
                gradient="linear-gradient(to right,#06B6D4,#3B82F6,#8B5CF6)"
                :columns="['Description', 'Budget', 'Remaining', 'Usage %']"
                tableBodyId="catalog-act-body"
                countBadgeId="catalog-act-count"
                paginationPrefix="catalogAct"
            />

        </div>
    </section>

</div>

</x-app-layout>
