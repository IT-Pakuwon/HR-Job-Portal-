@php
    // A panel tile marked "open by default" is already open when the page loads.
    $omDefaultPanel = \App\Models\DashboardMenu::forDashboard('DASHOM', auth()->user())
        ->first(fn ($i) => !empty($i['panel']) && !empty($i['default']))['panel'] ?? null;
@endphp
<div class="space-y-4" x-data="{ panel: @js($omDefaultPanel) }">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">OM Dashboard</h1>
            <p class="text-xs text-slate-500 dark:text-slate-400">Favourites • Main Menu</p>
        </div>

        {{-- Auto-refresh pill for the embedded Approval Dashboard (ids are driven by Dashboard-Panel/approval.js). --}}
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

    {{-- Menu on the left, My Favourites on the right (stacked on small screens). --}}
    <div class="grid grid-cols-1 items-start gap-4 lg:grid-cols-2">

        <x-multidashboard.component.menu dash-cd="DASHOM" />

        <div>
            <x-multidashboard.component.favourite />
        </div>

    </div>

    <x-multidashboard.component.progress />

    <x-multidashboard.component.todo />

    {{-- Approval Dashboard listing, opened from the Menu tile above (other panels load via the Menu component). --}}
    <div x-ref="omPanel" x-show="panel === 'approval'" x-cloak style="display: none;">
        @include('components.panel.approval', ['embedded' => true])
    </div>

</div>
