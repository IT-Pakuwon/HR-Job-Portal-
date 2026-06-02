<x-app-layout>

<div class="max-w-9xl mx-auto w-full space-y-8 p-4">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white">Chart Card Catalog</h1>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">All reusable chart templates — preview with sample data.</p>
        </div>
        <span class="rounded-full bg-violet-100 px-3 py-1 text-xs font-bold text-violet-700 dark:bg-violet-500/20 dark:text-violet-300">
            26 Templates
        </span>
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
            />

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
            />

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
            />

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
