@props(['timeId' => 'dashboardRefreshTime', 'buttonId' => 'refreshDashboard'])
{{-- Auto-refresh pill ("Next refresh 00:17 [Refresh]") at the right of a dashboard's tab bar. The ids are what the dashboard's own
     script looks up (it runs the countdown and the refresh click), so they are passed in per dashboard. --}}
<div class="flex shrink-0 items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-3 pr-1 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <span class="h-2 w-2 rounded-full bg-green-500"></span>
    <span class="hidden text-slate-500 sm:inline dark:text-slate-400">Next refresh</span>
    <span id="{{ $timeId }}" class="font-semibold tabular-nums text-slate-900 dark:text-white">--</span>
    <button id="{{ $buttonId }}" type="button" title="Refresh now"
        class="flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
        Refresh
    </button>
</div>
