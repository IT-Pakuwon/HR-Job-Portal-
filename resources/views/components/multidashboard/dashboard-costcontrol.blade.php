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
            <div class="flex flex-wrap items-center justify-between gap-3">

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

                <div id="approvalFilterContainer" class="flex items-center gap-2">

                    <label class="text-sm font-medium text-slate-600 dark:text-slate-400">
                        Document Type
                    </label>

                    <select id="approvalDocFilter"
                        class="rounded-lg border border-slate-300 bg-white px-10 py-2 text-sm text-slate-700 shadow-sm focus:border-slate-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="">All Documents</option>
                    </select>

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
