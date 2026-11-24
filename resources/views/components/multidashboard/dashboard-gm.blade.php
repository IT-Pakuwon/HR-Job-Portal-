@php
    $hdr = \App\Services\Dashboard\DashboardLayout::header('DASHGM');
    // A panel tile marked "open by default" is already open when the page loads.
    $gmDefaultPanel = \App\Models\DashboardMenu::forDashboard('DASHGM', auth()->user())
        ->first(fn ($i) => !empty($i['panel']) && !empty($i['default']))['panel'] ?? null;
@endphp
<div class="space-y-4" x-data="{ panel: @js($gmDefaultPanel) }">

    {{-- The title lives in the app header on large screens (see header_new: header-title). The auto-refresh pill is at the top of each panel. --}}
    @push('header-title')
        <div class="min-w-0 leading-tight">
            <h1 class="truncate text-base font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
            <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
        </div>
    @endpush

    {{-- Smaller screens have no room in the header: the title stays on the page --}}
    <div class="lg:hidden">
        <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $hdr['title'] }}</h1>
        <p class="text-xs text-slate-500 dark:text-slate-400">{{ $hdr['subtitle'] }}</p>
    </div>

    {{-- Main Menu | My Favourites | Your Progress in one row, then the To Do List (same rules as every other dashboard). --}}
    <x-multidashboard.dashboard-top dash-cd="DASHGM">
        {{-- Approval Dashboard listing, opened from the Menu tile above (other panels load via the Menu component). --}}
        <div x-ref="gmPanel" x-show="panel === 'approval'" x-cloak class="min-w-0 overflow-x-auto" style="display: none;">
            @include('components.panel.approval', ['embedded' => true, 'panelPill' => true])
        </div>
    </x-multidashboard.dashboard-top>


</div>
