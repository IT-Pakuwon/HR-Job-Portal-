@props(['listId'])

<div class="relative overflow-hidden rounded-2xl border border-indigo-200/70 bg-gradient-to-br from-indigo-50/80 via-white to-white p-4 shadow-sm dark:border-indigo-500/20 dark:from-indigo-500/[0.06] dark:via-slate-900 dark:to-slate-900">
    <div class="mb-2 flex items-center gap-1.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0c-.943.945-1.657 1.657-1.657 3.657h-3.758c0-2-.714-2.712-1.657-3.657z" />
        </svg>
        <span class="text-[10px] font-bold uppercase tracking-widest text-indigo-600 dark:text-indigo-400">GM Insight</span>
    </div>
    <ul id="{{ $listId }}" class="space-y-1.5">
        <li class="text-xs text-slate-400 dark:text-slate-500">Loading insights…</li>
    </ul>
</div>
