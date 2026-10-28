{{-- Work tabs of dashboard-warehouse. Placed by Dashboard Setup (DashboardLayout block "tabs"); dashboard-top includes it. --}}
    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-4 dark:border-slate-700">
            <div class="flex min-w-0 flex-wrap items-center gap-3">
                <div class="flex flex-wrap gap-3">
                <button id="wh-tab-approval">📝 Waiting Approval</button>
                <button id="wh-tab-approval-history">📋 Approval History</button>
                <button id="wh-tab-sppb">📦 SPPB On Progress</button>
                <button id="wh-tab-po-solomon">🛒 PO Solomon</button>
                <button id="wh-tab-grn-solomon">📥 GRN Solomon</button>
                <button id="wh-tab-issue-solomon">📤 Issue Solomon</button>
            </div>
            </div>

            <x-multidashboard.component.refresh-pill time-id="whRefreshTime" button-id="whRefresh" />
        </div>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">
            <div class="grid gap-3 lg:grid-cols-12">

                <div class="lg:col-span-4" id="whDoctypeFilterWrap">
                    <div id="whDoctypeFilterInnerWrap">
                        <select id="whDoctypeFilter"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option value="ALL">All Doctype</option>
                        </select>
                    </div>
                </div>

                <div class="lg:col-span-4">
                    <input
                        id="whSearch"
                        type="text"
                        placeholder="Search..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>

                <div class="lg:col-span-2">
                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="hidden shrink-0 sm:inline">Show</span>
                        <select id="whPageSize"
                            class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <span class="hidden shrink-0 sm:inline">Entries</span>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="flex gap-2">
                        <button
                            id="whApplyFilter"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 01.8 1.6l-6.3 8.4v5.5a1 1 0 01-1.45.9l-3-1.5A1 1 0 019.5 18v-4l-6.3-8.4A1 1 0 013 4z" />
                            </svg>
                            Filter
                        </button>
                        <button
                            id="whOpenAll"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-black px-3 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">
                            🚀 Open All
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="p-4">
            <div id="whCardList" class="divide-y divide-slate-100 dark:divide-slate-700"></div>
            <div id="whEmptyState" class="hidden py-10 text-center text-sm text-slate-400 dark:text-slate-500">
                No data available
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            <span id="whPaginationInfo">Showing 0 to 0 of 0 entries</span>
            <div class="flex gap-2">
                <button id="whPrevPage"
                    class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">
                    Previous
                </button>
                <button id="whNextPage"
                    class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">
                    Next
                </button>
            </div>
        </div>

    </div>
