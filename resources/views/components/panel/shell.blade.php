@props(['type', 'url', 'title' => '', 'statuses' => [], 'pageSizes' => [10, 25, 50, 100], 'pageSize' => 10])
{{-- Shared frame of a panel template: filter bar, list, pagination. Behaviour lives in
     public/assets/js/Dashboard-Panel/dash-panel.js (DashPanel.types[type] says how a row is drawn).
     Everything is found through data-dp inside this element, so several panels can sit on one page. --}}
<div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800"
    data-dp-root data-dp-type="{{ $type }}" data-dp-url="{{ $url }}"
    x-init="DashPanel.mount($el)">

    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 px-4 py-3 dark:border-slate-700">
        <h2 class="truncate text-sm font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h2>
        {{-- Auto-refresh pill: the same one the Approval panel shows --}}
        <div class="flex items-center gap-2 rounded-full border border-slate-200 bg-white py-1 pl-3 pr-1 text-xs shadow-sm dark:border-slate-700 dark:bg-slate-800">
            <span class="h-2 w-2 rounded-full bg-green-500"></span>
            <span class="hidden text-slate-500 sm:inline dark:text-slate-400">Next refresh</span>
            <span data-dp="countdown" class="font-semibold tabular-nums text-slate-900 dark:text-white">--</span>
            <button type="button" data-dp="refresh" title="Refresh now"
                class="flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 font-semibold text-slate-600 transition hover:bg-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:hover:bg-slate-600">
                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Refresh
            </button>
        </div>
    </div>

    <div class="grid gap-3 border-b border-slate-200 p-4 lg:grid-cols-12 dark:border-slate-700">
        <div class="lg:col-span-4">
            @if (count($statuses))
                <select data-dp="status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            @endif
        </div>
        <div class="lg:col-span-4">
            <input type="text" data-dp="search" placeholder="Search..."
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
        </div>
        <div class="lg:col-span-2">
            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                <span class="hidden shrink-0 sm:inline">Show</span>
                <select data-dp="pagesize" class="w-full rounded-lg border border-slate-300 bg-white px-2 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    @foreach ($pageSizes as $n)
                        <option value="{{ $n }}" @selected($n == $pageSize)>{{ $n }}</option>
                    @endforeach
                </select>
                <span class="hidden shrink-0 sm:inline">Entries</span>
            </div>
        </div>
        <div class="lg:col-span-2">
            <button type="button" data-dp="openall"
                class="flex w-full items-center justify-center gap-1.5 rounded-lg bg-black px-3 py-2 text-xs font-semibold text-white hover:bg-gray-900 dark:bg-zinc-700 dark:hover:bg-zinc-600">
                🚀 Open All
            </button>
        </div>
    </div>

    <div class="p-4">
        <div data-dp="list" class="divide-y divide-slate-100 dark:divide-slate-700"></div>
        <div data-dp="empty" class="hidden py-10 text-center text-sm text-slate-400 dark:text-slate-500">No data available</div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
        <span data-dp="info">Showing 0 to 0 of 0 entries</span>
        <div class="flex gap-2">
            <button type="button" data-dp="prev" class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Previous</button>
            <button type="button" data-dp="next" class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Next</button>
        </div>
    </div>
</div>
