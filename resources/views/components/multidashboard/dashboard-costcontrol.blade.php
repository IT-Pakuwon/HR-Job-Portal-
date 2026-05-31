<div class="space-y-4">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                Cost Control Dashboard
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Approval • PO Monitoring • Budget Monitoring • IM Budget
            </p>
        </div>

        <div
            class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <span class="h-2 w-2 rounded-lg bg-green-500"></span>
            <span class="text-slate-500 dark:text-slate-400">
                Last Refresh
            </span>
            <span id="dashboardRefreshTime" class="font-semibold text-slate-900 dark:text-white">
                --
            </span>
        </div>

    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Approval
                    </div>
                    <div id="approvalCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-emerald-500/10 p-2.5">
                    ✅
                </div>
            </div>
        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Pending PO Mapping
                    </div>
                    <div id="poCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-orange-500/10 p-2.5">
                    📦
                </div>
            </div>
        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Pending Issue Mapping
                    </div>
                    <div id="issueCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-rose-500/10 p-2.5">
                    🚚
                </div>
            </div>
        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Budget Monitoring
                    </div>
                    <div id="budgetCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-blue-500/10 p-2.5">
                    💰
                </div>
            </div>
        </div>

        <div
            class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        IM Budget
                    </div>
                    <div id="imBudgetCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-violet-500/10 p-2.5">
                    📊
                </div>
            </div>
        </div>

    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">
        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <div class="flex flex-wrap gap-3">
                <button id="tab-approval">
                    ✅ Waiting Approval
                </button>

                <button id="tab-approval-history">
                    📋 Approval History
                </button>

                <button id="tab-po">
                    📦 Pending PO Mapping
                </button>

                <button id="tab-issue">
                    🚚 Pending Issue Mapping
                </button>

                <button id="tab-budget">
                    💰 Search Budget
                </button>

                <button id="tab-imbudget">
                    📊 IM Budget
                </button>



            </div>
        </div>
        <div class="border-b border-slate-200 p-4 dark:border-slate-700">

            <div class="grid gap-3 lg:grid-cols-12">


                <div class="lg:col-span-5">

                    <select id="dashboardFilter"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                        <option value="ALL">
                            All
                        </option>

                    </select>

                </div>

                <div class="lg:col-span-5">

                    <input id="dashboardSearch" type="text" placeholder="Search..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                </div>

                <div class="lg:col-span-2">

                    <div class="flex justify-end gap-2">

                        <button id="openAllDocument"
                            class="rounded-lg w-full flex-1 bg-black px-4 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">

                            🚀 Open All

                        </button>

                        <button id="refreshDashboard"
                            class="rounded-lg w-full flex-1  border border-slate-300 px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">

                            🔄 Refresh

                        </button>

                    </div>

                </div>

            </div>

        </div>
        <div class="p-4">
            <div id="dashboardTableContainer">
                <table id="dashboardTable" class="display w-full text-xs"></table>
            </div>
        </div>
    </div>
</div>

    <script src="{{ asset('assets/js/multidashboard/dashcost.js') }}"></script>
