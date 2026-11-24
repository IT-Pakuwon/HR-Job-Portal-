@php $dashCd = 'DASHLEGALLEASE'; @endphp
@php $hdr = \App\Services\Dashboard\DashboardLayout::header($dashCd); @endphp
{{-- Dashboard Legal Leasing: the approval panel (Waiting Approval / Approval History) plus the My Activity tab that index.blade.php adds.
     Same shape as the Approval Dashboard; the KPI cards and the tabs are blocks (blocks/dashboard-legal-leasing/) arranged in Dashboard Setup. --}}
<div class="space-y-4" x-data="{ panel: null }">

    {{-- The title lives in the app header on large screens (see header_new: header-title); smaller screens keep it on the page. --}}
    @push('header-title')
        <div class="min-w-0 leading-tight">
            <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
        </div>
    @endpush
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between lg:hidden">
        <div class="lg:hidden">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
        </div>
    </div>

    {{-- a component does not see the page's variables, so the ones the approval panel reads go along --}}
    <x-multidashboard.dashboard-top :dash-cd="$dashCd"
        :block-data="['doctypes' => $doctypes ?? collect(), 'akses_cc' => $akses_cc ?? false, 'isGA' => $isGA ?? false, 'myPage' => false, 'embedded' => false, 'panelPill' => true]" />

</div>
