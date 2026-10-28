@php $dashCd = 'DASHWH'; @endphp
@php $hdr = \App\Services\Dashboard\DashboardLayout::header($dashCd); @endphp
<div class="space-y-4" x-data="{ panel: null }">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between lg:hidden">

        {{-- The title lives in the app header on large screens (see header_new: header-title); smaller screens keep it on the page. --}}
        @push('header-title')
            <div class="min-w-0 leading-tight">
                <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
                <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
            </div>
        @endpush
        <div class="lg:hidden">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
        </div>


    </div>

    <x-multidashboard.dashboard-top :dash-cd="$dashCd" />

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Waiting Approval</span>
            <span id="whWaitingApprovalCount" class="dsh-kpi-n">0</span>
            <span id="whWaitingApprovalBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">SPPB On Progress</span>
            <span id="whSppbOnProgressCount" class="dsh-kpi-n">0</span>
            <span id="whSppbOnProgressBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">PO Solomon (P)</span>
            <span id="whPoSolomonCount" class="dsh-kpi-n">0</span>
            <span id="whPoSolomonBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">GRN Ifca (P)</span>
            <span id="whGrnSolomonCount" class="dsh-kpi-n">0</span>
            <span id="whGrnSolomonBar" class="hidden" style="width:0%"></span>
        </div>

        <div class="dsh-kpi dsh-kpi-static">
            <span class="dsh-kpi-l">Issue Solomon (P)</span>
            <span id="whIssueSolomonCount" class="dsh-kpi-n">0</span>
            <span id="whIssueSolomonBar" class="hidden" style="width:0%"></span>
        </div>

    </div>

    <div class="grid grid-cols-12 gap-6">
        <x-multidashboard.dashboard-favourite />
    </div>

    <div class="rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800">

        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-700">
            <div class="flex flex-wrap gap-3">
                <button id="wh-tab-approval">📝 Waiting Approval</button>
                <button id="wh-tab-approval-history">📋 Approval History</button>
                <button id="wh-tab-sppb">📦 SPPB On Progress</button>
                <button id="wh-tab-po-solomon">🛒 PO Solomon</button>
                <button id="wh-tab-grn-solomon">📥 GRN Ifca</button>
                <button id="wh-tab-issue-solomon">📤 Issue Solomon</button>
            </div>
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

</div>

<style>
    #whDoctypeFilterInnerWrap .select2-selection--single {
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
    #whDoctypeFilterInnerWrap .select2-selection--single:hover {
        border-color: #94a3b8;
    }
    #whDoctypeFilterInnerWrap .select2-container--open .select2-selection--single {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
    #whDoctypeFilterInnerWrap .select2-selection__rendered {
        padding: 0;
        line-height: 32px;
        color: #334155;
    }
    #whDoctypeFilterInnerWrap .select2-selection__arrow {
        height: 32px;
        right: 8px;
    }
    #whDoctypeFilterInnerWrap .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }

    #whDoctypeFilterInnerWrap .select2-dropdown {
        border-radius: 0.5rem;
        border-color: #c7d2e1;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
    }
    #whDoctypeFilterInnerWrap .select2-search__field {
        border-radius: 0.375rem;
        border: 1px solid #cbd5e1;
        padding: 0.375rem 0.5rem;
        font-size: 0.75rem;
        outline: none;
    }
    #whDoctypeFilterInnerWrap .select2-search__field:focus {
        border-color: #6366f1;
    }
    #whDoctypeFilterInnerWrap .select2-results__option {
        font-size: 0.75rem;
        padding: 0.5rem 0.75rem;
    }
    #whDoctypeFilterInnerWrap .select2-results__option--highlighted[aria-selected] {
        background-color: #eef2ff;
        color: #4338ca;
    }
    #whDoctypeFilterInnerWrap .select2-results__option[aria-selected="true"] {
        background-color: #e0e7ff;
        color: #4338ca;
        font-weight: 600;
    }

    .dark #whDoctypeFilterInnerWrap .select2-selection--single {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark #whDoctypeFilterInnerWrap .select2-selection__rendered {
        color: #e2e8f0;
    }
    .dark #whDoctypeFilterInnerWrap .select2-selection__arrow b {
        border-color: #94a3b8 transparent transparent transparent;
    }
    .dark #whDoctypeFilterInnerWrap .select2-dropdown {
        background-color: #1e293b;
        border-color: #475569;
    }
    .dark #whDoctypeFilterInnerWrap .select2-search__field {
        background-color: #334155;
        border-color: #475569;
        color: #e2e8f0;
    }
    .dark #whDoctypeFilterInnerWrap .select2-results__option {
        color: #e2e8f0;
    }
    .dark #whDoctypeFilterInnerWrap .select2-results__option--highlighted[aria-selected] {
        background-color: #4338ca;
        color: #fff;
    }
    .dark #whDoctypeFilterInnerWrap .select2-results__option[aria-selected="true"] {
        background-color: #3730a3;
        color: #fff;
    }
</style>

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

<script src="{{ asset('assets/js/multidashboard/dashwarehouse.js') }}?v={{ file_exists(public_path('assets/js/multidashboard/dashwarehouse.js')) ? filemtime(public_path('assets/js/multidashboard/dashwarehouse.js')) : time() }}"></script>
