<div class="space-y-4">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                Warehouse Dashboard
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Approval • SPPB • Solomon Integration
            </p>
        </div>

        <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">

            <span class="h-2 w-2 rounded-full bg-green-500"></span>

            <span class="text-slate-500 dark:text-slate-400">
                Next Refresh
            </span>

            <span id="whRefreshTime"
                class="font-semibold text-slate-900 dark:text-white">
                --
            </span>

        </div>

    </div>

    <div class="grid grid-cols-2 gap-3 xl:grid-cols-5">

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">Waiting Approval</div>
                    <div id="whWaitingApprovalCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">0</div>
                </div>
                <div class="rounded-lg bg-emerald-500/10 p-2.5">📝</div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">SPPB On Progress</div>
                    <div id="whSppbOnProgressCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">0</div>
                </div>
                <div class="rounded-lg bg-amber-500/10 p-2.5">📦</div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">PO Solomon (P)</div>
                    <div id="whPoSolomonCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">0</div>
                </div>
                <div class="rounded-lg bg-blue-500/10 p-2.5">🛒</div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">GRN Solomon (P)</div>
                    <div id="whGrnSolomonCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">0</div>
                </div>
                <div class="rounded-lg bg-violet-500/10 p-2.5">📥</div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">Issue Solomon (P)</div>
                    <div id="whIssueSolomonCount" class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">0</div>
                </div>
                <div class="rounded-lg bg-rose-500/10 p-2.5">📤</div>
            </div>
        </div>

    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <div class="flex flex-wrap gap-3">
                <button id="wh-tab-approval">📝 Waiting Approval</button>
                <button id="wh-tab-approval-history">📋 Approval History</button>
                <button id="wh-tab-sppb">📦 SPPB On Progress</button>
                <button id="wh-tab-po-solomon">🛒 PO Solomon</button>
                <button id="wh-tab-grn-solomon">📥 GRN Solomon</button>
                <button id="wh-tab-issue-solomon">📤 Issue Solomon</button>
            </div>
        </div>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">
            <div class="grid gap-3 lg:grid-cols-12">

                <div class="lg:col-span-5" id="whDoctypeFilterWrap">
                    <select id="whDoctypeFilter"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                        <option value="ALL">All Doctype</option>
                    </select>
                </div>

                <div class="lg:col-span-5">
                    <input
                        id="whSearch"
                        type="text"
                        placeholder="Search..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>

                <div class="lg:col-span-2">
                    <div class="flex justify-end gap-2">
                        <button
                            id="whOpenAll"
                            class="rounded-lg w-full flex-1 bg-black px-4 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">
                            🚀 Open All
                        </button>
                        <button
                            id="whRefresh"
                            class="rounded-lg w-full flex-1 border border-slate-300 px-4 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">
                            🔄 Refresh
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="p-4">
            <table id="whTable" class="display w-full text-xs"></table>
        </div>

    </div>

</div>

<script>
window.warehouseRoutes = {
    summary:         "{{ route('warehouse.summary') }}",
    approval:        "{{ route('warehouse.approval') }}",
    approvalHistory: "{{ route('warehouse.approval-history') }}",
    sppbOnProgress:  "{{ route('warehouse.sppb-on-progress') }}",
    poSolomon:       "{{ route('warehouse.po-solomon') }}",
    grnSolomon:      "{{ route('warehouse.grn-solomon') }}",
    issueSolomon:    "{{ route('warehouse.issue-solomon') }}",
    doctypes:        "{{ route('warehouse.approval-doctypes') }}",
};
</script>

<script src="{{ asset('assets/js/multidashboard/dashwarehouse.js') }}"></script>
