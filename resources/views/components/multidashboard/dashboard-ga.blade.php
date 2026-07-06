<div class="space-y-4">

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">
                General Affairs Dashboard
            </h1>

            <p class="text-xs text-slate-500 dark:text-slate-400">
                Approval • Transportation • Parking
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">

            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                <span class="text-slate-500 dark:text-slate-400">
                    Next Refresh
                </span>
                <span id="dashboardRefreshTime"
                    class="font-semibold text-slate-900 dark:text-white">
                    --
                </span>
            </div>

            <button id="refreshDashboard" type="button"
                class="flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>

            <button id="updateNotificationBtn" type="button" title="Update Notification"
                class="relative flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2.5 text-slate-600 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span id="updateNotificationBadge"
                    class="hidden absolute -right-1.5 -top-1.5 min-w-[18px] rounded-full bg-red-500 px-1 text-center text-[10px] font-bold leading-[18px] text-white">
                    0
                </span>
            </button>

        </div>

    </div>

    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Approval
                    </div>
                    <div id="waitingApprovalCount" class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-emerald-500/10 p-2 text-base">
                    📝
                </div>
            </div>
            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div id="waitingApprovalBar" class="h-full rounded-full bg-emerald-500 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Voucher Taxi
                    </div>
                    <div id="voucherTaxiCount" class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-amber-500/10 p-2 text-base">
                    🚕
                </div>
            </div>
            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div id="voucherTaxiBar" class="h-full rounded-full bg-amber-500 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Waiting Booking Car
                    </div>
                    <div id="bookingCarCount" class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-blue-500/10 p-2 text-base">
                    🚗
                </div>
            </div>
            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div id="bookingCarBar" class="h-full rounded-full bg-blue-500 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500">
                        Free Parking
                    </div>
                    <div id="freeParkingCount" class="mt-0.5 text-xl font-bold text-slate-900 dark:text-white">
                        0
                    </div>
                </div>
                <div class="rounded-lg bg-violet-500/10 p-2 text-base">
                    🅿️
                </div>
            </div>
            <div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">
                <div id="freeParkingBar" class="h-full rounded-full bg-violet-500 transition-all duration-500" style="width:0%"></div>
            </div>
        </div>

    </div>

    <div class="grid grid-cols-12 gap-6">
        <x-multidashboard.dashboard-favourite />
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">

            <div class="flex flex-wrap gap-3">

                <button id="tab-approval">
                    📝 Waiting Approval
                </button>

                <button id="tab-approval-history">
                    📋 Approval History
                </button>

                <button id="tab-voucher-taxi">
                    🚕 Waiting Process Voucher Taxi
                </button>

                <button id="tab-booking-car">
                    🚗 Waiting Process Booking Car
                </button>

                <button id="tab-parking">
                    🅿️ Free Parking List
                </button>

            </div>

        </div>

        <div class="border-b border-slate-200 p-4 dark:border-slate-700">

            <div class="grid gap-3 lg:grid-cols-12">

                <div id="dashboardFilterCol" class="lg:col-span-4">

                    <div id="dashboardFilterWrap">
                        <select id="dashboardFilter"
                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                            <option value="ALL">
                                All
                            </option>

                        </select>
                    </div>

                </div>

                <div class="lg:col-span-4">

                    <input
                        id="dashboardSearch"
                        type="text"
                        placeholder="Search..."
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">

                </div>

                <div class="lg:col-span-2">

                    <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                        <span class="hidden shrink-0 sm:inline">Show</span>
                        <select id="dashboardPageSize"
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
                            id="applyFilter"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-xs font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-700">

                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 01.8 1.6l-6.3 8.4v5.5a1 1 0 01-1.45.9l-3-1.5A1 1 0 019.5 18v-4l-6.3-8.4A1 1 0 013 4z" />
                            </svg>

                            Filter

                        </button>

                        <button
                            id="openAllDocument"
                            class="flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-black px-3 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">

                            🚀 Open All

                        </button>

                    </div>

                </div>

            </div>

        </div>

        <div class="p-4">
            <div id="dashboardCardList" class="divide-y divide-slate-100 dark:divide-slate-700"></div>
            <div id="dashboardEmptyState" class="hidden py-10 text-center text-sm text-slate-400 dark:text-slate-500">
                No data available
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
            <span id="paginationInfo">Showing 0 to 0 of 0 entries</span>
            <div class="flex gap-2">
                <button id="prevPage"
                    class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">
                    Previous
                </button>
                <button id="nextPage"
                    class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">
                    Next
                </button>
            </div>
        </div>

    </div>

</div>

<style>
    #dashboardFilterWrap .select2-selection--single {
        height: 34px;
        display: flex;
        align-items: center;
        border-radius: 0.5rem;
        border: 1px solid #cbd5e1;
        background-color: #fff;
        padding: 0 2rem 0 0.75rem;
        font-size: 0.75rem;
        transition: border-color .15s, box-shadow .15s;
    }
    #dashboardFilterWrap .select2-selection--single:hover {
        border-color: #94a3b8;
    }
    #dashboardFilterWrap .select2-container--open .select2-selection--single {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    #dashboardFilterWrap .select2-selection__rendered {
        padding: 0;
        line-height: 32px;
        color: #334155;
    }
    #dashboardFilterWrap .select2-selection__arrow {
        height: 32px;
        right: 8px;
    }
    #dashboardFilterWrap .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }

    #dashboardFilterWrap .select2-dropdown {
        border-radius: 0.5rem;
        border-color: #c7d2e1;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }
    #dashboardFilterWrap .select2-search__field {
        border-radius: 0.375rem;
        border: 1px solid #cbd5e1;
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
        outline: none;
    }
    #dashboardFilterWrap .select2-search__field:focus {
        border-color: #6366f1;
    }
    #dashboardFilterWrap .select2-results__option {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
    #dashboardFilterWrap .select2-results__option--highlighted[aria-selected] {
        background-color: #eef2ff;
        color: #4338ca;
    }
    #dashboardFilterWrap .select2-results__option[aria-selected="true"] {
        background-color: #e0e7ff;
        color: #4338ca;
        font-weight: 600;
    }

    .dark #dashboardFilterWrap .select2-selection--single {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark #dashboardFilterWrap .select2-selection__rendered {
        color: #e2e8f0;
    }
    .dark #dashboardFilterWrap .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }
    .dark #dashboardFilterWrap .select2-dropdown {
        background-color: #1e293b;
        border-color: #475569;
    }
    .dark #dashboardFilterWrap .select2-search__field {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark #dashboardFilterWrap .select2-results__option {
        color: #e2e8f0;
    }
    .dark #dashboardFilterWrap .select2-results__option--highlighted[aria-selected] {
        background-color: #4338ca;
        color: #fff;
    }
    .dark #dashboardFilterWrap .select2-results__option[aria-selected="true"] {
        background-color: #3730a3;
        color: #fff;
    }
</style>

<script src="{{ asset('assets/js/multidashboard/dashga.js') }}?v={{ filemtime(public_path('assets/js/multidashboard/dashga.js')) }}"></script>
