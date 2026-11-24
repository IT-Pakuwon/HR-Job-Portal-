@php $dashCd = 'DASHVPLOYALTY'; @endphp
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

<script>
    window.vplDashboardConfig = {
        typeLabel: 'Voucher & Product',
        expiredTabLabel: 'Expired Voucher & Product List',
        routes: {
            summary: "{{ route('vployalty-dashboard.summary-json') }}",
            approval: "{{ route('vployalty-dashboard.waiting-approval-json') }}",
            approvalHistory: "{{ route('vployalty-dashboard.approval-history-json') }}",
            expired: "{{ route('vployalty-dashboard.expired-json') }}",
            doctypes: "{{ route('vployalty-dashboard.approval-doctypes-json') }}",
        },
    };
</script>
<script src="{{ asset('assets/js/multidashboard/dashvplmodule.js') }}?v={{ file_exists(public_path('assets/js/multidashboard/dashvplmodule.js')) ? filemtime(public_path('assets/js/multidashboard/dashvplmodule.js')) : time() }}"></script>
