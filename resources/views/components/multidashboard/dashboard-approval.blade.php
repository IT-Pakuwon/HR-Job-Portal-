@php $dashCd = 'DASHAPPROVAL'; @endphp
<div class="space-y-4" @unless ($embedded ?? false) x-data="{ panel: null }" @endunless>

    @unless ($embedded ?? false)
    @php
        $apprTitle = ($myPage ?? false) ? 'My Page' : 'Approval Dashboard';
        $apprSubtitle = ($myPage ?? false) ? 'Waiting Approval • Approval History • My Document • My Message • My Files' : 'Waiting Approval • Approval History';
    @endphp
    {{-- The title lives in the app header on large screens (see header_new: header-title); smaller screens keep it on the page. --}}
    @push('header-title')
        <div class="min-w-0 leading-tight">
            <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $apprTitle }}</h1>
            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $apprSubtitle }}</p>
        </div>
    @endpush
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between lg:justify-end">

        <div class="lg:hidden">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $apprTitle }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $apprSubtitle }}</p>
        </div>

        <div class="flex items-center gap-2">

            <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-3 pr-1 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                <span class="text-slate-500 dark:text-slate-400">Next refresh</span>
                <span id="approvalCountdown" class="font-semibold tabular-nums text-slate-900 dark:text-white">--</span>
                <button id="refreshApproval" type="button" title="Refresh now"
                    class="flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>


        </div>

    </div>

    @endunless
    @if (!(($myPage ?? false) || ($embedded ?? false)))
    {{-- the approval panel (Waiting Approval / Approval History) is this dashboard's "tabs" block, see blocks/dashboard-approval/tabs;
         a component does not see the page's variables, so the ones the panel reads go along --}}
    <x-multidashboard.dashboard-top :dash-cd="$dashCd"
        :block-data="['doctypes' => $doctypes ?? collect(), 'akses_cc' => $akses_cc ?? false, 'isGA' => $isGA ?? false, 'myPage' => false, 'embedded' => false]" />
    @else
    {{-- My Page / embedded: no top area, the panel stands alone --}}
    @include('components.panel.approval')
    @endif
</div>

