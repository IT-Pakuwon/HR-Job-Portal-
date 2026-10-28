{{--
    My Message / My Document / My Files.
    Injected as three extra tabs into each dashboard's tab bar (see the script at the bottom);
    on a dashboard without a tab bar the panels simply stay visible under the dashboard.
    Table, filter bar and pager mirror the dashboards' "Waiting Approval" tab.
    $asPanel = true (components/panel/my-activity) renders it in place as a Panel-area panel instead.
--}}
@php
    $combined = $combined ?? false;
    $selectCls = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white';
    $thCls = 'px-3 py-2 font-semibold';
    $tdCls = 'ma-chip whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300';
    $pillCls = $combined ? 'dsh-id' : 'inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600';
    $rowCls = 'border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30';
    $theadCls = 'border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400';
    $pagerBtn = 'rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600';
@endphp

<style>
    /* Plain CSS on purpose: no Vite build needed for these classes. Dark mode follows the app's .dark class. */
    #myActivityRoot { --ma-g1: #0e7490; --ma-g2: #2563eb; --ma-line: #e2e8f0; --ma-soft: #f1f5f9; --ma-muted: #64748b; --ma-card: #fff; --ma-fg: #0f172a; }
    .dark #myActivityRoot { --ma-g1: #22d3ee; --ma-g2: #818cf8; --ma-line: #334155; --ma-soft: #273449; --ma-muted: #94a3b8; --ma-card: #1e293b; --ma-fg: #f1f5f9; }

    .ma-seg { display: flex; gap: .25rem; margin: 1rem 1rem 0; padding: .25rem; border-radius: .875rem; background: var(--ma-soft); }
    .ma-seg button { flex: 1 1 0; padding: .5rem .75rem; border-radius: .625rem; font-size: .75rem; font-weight: 600; color: var(--ma-muted); transition: background .15s, color .15s; touch-action: manipulation; }
    .ma-seg button.on { background: var(--ma-card); color: var(--ma-fg); box-shadow: 0 1px 3px #0f172a22; }
    @media (max-width: 768px), (display-mode: standalone) { .ma-seg { margin: .25rem 0 .5rem; } }
    .ma-by { display: block; margin-bottom: .125rem; font-size: .6875rem; color: var(--ma-g2); }
    .ma-tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; padding: 1rem 1.25rem .25rem; }
    @media (max-width: 640px) { .ma-tiles { grid-template-columns: 1fr; } }
    .ma-tile { display: flex; align-items: center; gap: .875rem; text-align: left; cursor: pointer; padding: .875rem 1rem; border-radius: .875rem;
        border: 1.5px solid var(--ma-line); background: var(--ma-card); color: var(--ma-fg); transition: transform .15s, box-shadow .15s; }
    .ma-tile:hover { transform: translateY(-1px); box-shadow: 0 8px 20px -10px #0f172a40; }
    .ma-tile[aria-selected="true"] { border-color: transparent;
        background: linear-gradient(var(--ma-card), var(--ma-card)) padding-box, linear-gradient(120deg, var(--ma-g1), var(--ma-g2)) border-box; }
    .ma-ic { width: 2.375rem; height: 2.375rem; flex: none; border-radius: .6875rem; display: grid; place-items: center; background: var(--ma-soft); color: var(--ma-muted); }
    .ma-ic svg { width: 1.1875rem; height: 1.1875rem; }
    .ma-tile[aria-selected="true"] .ma-ic { color: #fff; background: linear-gradient(135deg, var(--ma-g1), var(--ma-g2)); }
    #myActivityDock .ma-tiles { gap: .5rem; padding: .875rem 1.25rem .25rem; }
    #myActivityDock .ma-tile { gap: .5rem; padding: .625rem .75rem; }
    #myActivityDock .ma-tile .ma-ic { width: 1.875rem; height: 1.875rem; }
    #myActivityDock .ma-l { font-size: .6875rem; line-height: 1.25; }
    #myActivityRoot[data-combined="1"] .ma-xcol { display: none; }
    .ma-n { display: block; font-size: 1.375rem; font-weight: 700; line-height: 1; font-variant-numeric: tabular-nums; }
    .ma-l { display: block; margin-top: .25rem; font-size: .75rem; color: var(--ma-muted); }

    /* Select2 filter dropdowns (My Document / My Files): look like the other inputs, open as a rounded card under the field */
    .ma-s2 { position: relative; }
    #myActivityRoot .ma-s2 .select2-container { width: 100% !important; }
    #myActivityRoot .ma-s2 .select2-selection--single { height: 34px; display: flex; align-items: center; border-radius: .5rem; border: 1px solid #cbd5e1;
        background: #fff; padding: 0 2rem 0 .75rem; font-size: .75rem; transition: border-color .15s, box-shadow .15s; }
    #myActivityRoot .ma-s2 .select2-selection--single:hover { border-color: #94a3b8; }
    #myActivityRoot .ma-s2 .select2-container--open .select2-selection--single { border-color: #6366f1; box-shadow: 0 0 0 3px #6366f126; }
    #myActivityRoot .ma-s2 .select2-selection__rendered { padding: 0 !important; line-height: 32px !important; color: #334155 !important; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    #myActivityRoot .ma-s2 .select2-selection__arrow { height: 32px !important; right: 8px !important; }
    #myActivityRoot .ma-s2 .select2-selection__arrow b { border-color: #94a3b8 transparent transparent transparent !important; }
    #myActivityRoot .ma-s2 .select2-dropdown { z-index: 60; margin-top: 4px; border-radius: .75rem; border: 1px solid #c7d2e1; overflow: hidden;
        box-shadow: 0 12px 24px -8px #0f172a33; }
    #myActivityRoot .ma-s2 .select2-results__options { max-height: 15rem; }
    #myActivityRoot .ma-s2 .select2-results__option { font-size: .8125rem; padding: .5rem .75rem; }
    #myActivityRoot .ma-s2 .select2-results__option--highlighted[aria-selected] { background: #eef2ff; color: #4338ca; }
    #myActivityRoot .ma-s2 .select2-results__option[aria-selected="true"] { background: #e0e7ff; color: #4338ca; font-weight: 600; }
    #myActivityRoot .ma-s2 .select2-search__field { border-radius: .375rem; border: 1px solid #cbd5e1; padding: .375rem .5rem; font-size: .75rem; outline: none; }
    .dark #myActivityRoot .ma-s2 .select2-selection--single { background: #334155; border-color: #475569; }
    .dark #myActivityRoot .ma-s2 .select2-selection__rendered { color: #e2e8f0 !important; }
    .dark #myActivityRoot .ma-s2 .select2-dropdown { background: #1e293b; border-color: #475569; }
    .dark #myActivityRoot .ma-s2 .select2-results__option { color: #e2e8f0; }
    .dark #myActivityRoot .ma-s2 .select2-results__option--highlighted[aria-selected] { background: #4338ca; color: #fff; }
    .dark #myActivityRoot .ma-s2 .select2-results__option[aria-selected="true"] { background: #3730a3; color: #fff; }
    .dark #myActivityRoot .ma-s2 .select2-search__field { background: #334155; border-color: #475569; color: #e2e8f0; }

    .ma-sep { flex: none; width: 1px; align-self: stretch; margin: .25rem .375rem; background: var(--ma-line); }
    .ma-mine { display: inline-flex; align-items: center; gap: .4rem; border-style: dashed; }
    .ma-mine.ma-grad { border-style: solid; border-color: transparent; color: #fff; background: linear-gradient(120deg, var(--ma-g1), var(--ma-g2)); }

    .ma-fcell { display: flex; align-items: center; gap: .75rem; min-width: 14rem; }
    .ma-fsize { display: block; margin-top: .125rem; font-size: .6875rem; color: var(--ma-muted); }
    .ma-ftile { width: 2.125rem; height: 2.125rem; flex: none; border-radius: .5625rem; display: grid; place-items: center; font-size: .5625rem; font-weight: 800; letter-spacing: .04em; }
    .ma-ft-pdf { color: #dc2626; background: #dc262622; } .ma-ft-img { color: #2563eb; background: #2563eb22; }
    .ma-ft-xls { color: #16a34a; background: #16a34a22; } .ma-ft-doc { color: #0369a1; background: #0369a122; } .ma-ft-oth { color: #7c3aed; background: #7c3aed22; }
    .dark .ma-ft-pdf { color: #f87171; } .dark .ma-ft-img { color: #60a5fa; } .dark .ma-ft-xls { color: #4ade80; } .dark .ma-ft-doc { color: #38bdf8; } .dark .ma-ft-oth { color: #c4b5fd; }
    .ma-dead { display: inline-block; padding: .25rem .5rem; border-radius: .375rem; border: 1px dashed var(--ma-muted); font-size: .6875rem; font-weight: 600; color: var(--ma-muted); }
</style>

<div id="myActivityRoot" :class="embedded ? '' : 'mt-4'"
    data-combined="{{ $combined ? '1' : '0' }}"
    @if ($asPanel ?? false) data-panel="1" data-docked="1" data-embedded="1" @endif
    x-data="myActivity({ documentsUrl: '{{ route('my-activity.documents') }}', messagesUrl: '{{ route('my-activity.messages') }}', filesUrl: '{{ route('my-activity.files') }}', combined: {{ $combined ? 'true' : 'false' }} })"
    x-init="init()"
    x-show="mode !== ''"
    @my-activity-open.window="open($event.detail)">

    @if ($combined)
        {{-- Count tiles replace the three separate tabs --}}
        <div class="ma-tiles" role="tablist" aria-label="My Activity" x-show="mode !== ''">
            <template x-for="t in tiles" :key="t.mode">
                <button type="button" role="tab" class="ma-tile" :aria-selected="mode === t.mode" @click="open(t.mode)">
                    <span class="ma-ic" x-html="t.icon"></span>
                    <span class="min-w-0">
                        <span class="ma-n" x-text="count(t.mode)"></span>
                        <span class="ma-l" x-text="t.label"></span>
                    </span>
                </button>
            </template>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-3" :class="mode === 'both' ? 'xl:grid-cols-2' : ''">

        {{-- My Document --}}
        <div x-show="mode === 'doc' || mode === 'both'"
            :class="embedded ? '' : 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800'">

            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="grid grid-cols-2 gap-3 lg:col-span-4">
                        <div class="ma-s2 min-w-0">
                            <select x-model="docType" x-init="$nextTick(() => s2($el, 'docType', 'docTypes', () => loadDocs(1), 8))" class="{{ $selectCls }}">
                                <option value="">All Doctype</option>
                                <template x-for="t in docTypes" :key="t"><option :value="t" x-text="t"></option></template>
                            </select>
                        </div>
                        <div class="ma-s2 min-w-0">
                            <select x-model="docStatus" x-init="$nextTick(() => s2($el, 'docStatus', 'docStatuses', () => loadDocs(1), -1))" class="{{ $selectCls }}">
                                <option value="">All Status</option>
                                <template x-for="st in docStatuses" :key="st.code + st.label"><option :value="st.code" x-text="st.label"></option></template>
                            </select>
                        </div>
                    </div>
                    <div class="lg:col-span-4">
                        <input type="text" x-model="docQ" @input.debounce.400ms="loadDocs(1)" placeholder="Search..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="hidden shrink-0 sm:inline">Show</span>
                            <select x-model.number="perPage" @change="loadDocs(1); loadMsgs(1); loadFiles(1)" class="w-24 {{ $selectCls }}">
                                <option value="10">10</option><option value="25">25</option>
                                <option value="50">50</option><option value="100">100</option>
                            </select>
                            <span class="hidden shrink-0 sm:inline">Entries</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="overflow-x-auto" x-show="docs.length">
                    @if ($combined)
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $theadCls }}">
                            <tr>
                                <th class="{{ $thCls }}">Document</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Date</th>
                                <th class="{{ $thCls }}">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="d in docs" :key="d.docid">
                                <tr class="{{ $rowCls }} ma-r ma-r-doc" :data-s="d.code">
                                    <td class="px-3 py-2.5 align-top">
                                        <div class="font-semibold text-slate-800 dark:text-slate-100" x-text="d.info || d.type"></div>
                                        <div class="dsh-sub">
                                            <a :href="d.href" target="_blank" rel="noopener noreferrer" class="dsh-id" x-text="d.docid"></a>
                                            <span>·</span><span x-text="d.type"></span>
                                        </div>
                                    </td>
                                    <td class="{{ $tdCls }}" x-text="d.company || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="d.dept || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="fmt(d.date)"></td>
                                    <td class="whitespace-nowrap px-3 py-2.5 align-top">
                                        <span class="dsh-st" :class="statusClass(d.code)" x-text="d.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    @else
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $theadCls }}">
                            <tr>
                                <th class="{{ $thCls }}">Doc ID</th>
                                <th class="{{ $thCls }} ma-xcol">Type</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Date</th>
                                <th class="{{ $thCls }}">Desc</th>
                                <th class="{{ $thCls }}">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="d in docs" :key="d.docid">
                                <tr class="{{ $rowCls }} ma-r ma-r-doc" :data-s="d.code">
                                    <td class="whitespace-nowrap px-3 py-2 align-top">
                                        <a :href="d.href" target="_blank" rel="noopener noreferrer" class="{{ $pillCls }}" x-text="d.docid"></a>
                                    </td>
                                    <td class="{{ $tdCls }}" x-text="d.type"></td>
                                    <td class="{{ $tdCls }}" x-text="d.company || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="d.dept || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="fmt(d.date)"></td>
                                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300" x-text="d.info || '-'"></td>
                                    <td class="whitespace-nowrap px-3 py-2 align-top">
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusClass(d.code)" x-text="d.status"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                    @endif
                </div>
                <div x-show="docsLoading" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">Loading…</div>
                <div x-show="!docsLoading && !docs.length" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">No data available</div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                <span x-text="'Showing ' + docPg.from + ' to ' + docPg.to + ' of ' + docPg.total + ' entries'"></span>
                <div class="flex gap-2">
                    <button type="button" @click="loadDocs(docPg.page - 1)" :disabled="docPg.page <= 1" class="{{ $pagerBtn }}">Previous</button>
                    <button type="button" @click="loadDocs(docPg.page + 1)" :disabled="docPg.page >= docPg.last_page" class="{{ $pagerBtn }}">Next</button>
                </div>
            </div>
        </div>

        {{-- My Message --}}
        <div x-show="mode === 'msg' || mode === 'both'"
            :class="embedded ? '' : 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800'">

            {{-- Comments I wrote  /  comments others wrote on my documents --}}
            <div class="ma-seg" role="tablist">
                <button type="button" role="tab" :aria-selected="msgScope === 'mine'" :class="msgScope === 'mine' && 'on'" @click="setMsgScope('mine')">My Comments</button>
                <button type="button" role="tab" :aria-selected="msgScope === 'others'" :class="msgScope === 'others' && 'on'" @click="setMsgScope('others')">From Others</button>
            </div>

            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <input type="text" x-model="msgQ" @input.debounce.400ms="loadMsgs(1)" placeholder="Search..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="hidden shrink-0 sm:inline">Show</span>
                            <select x-model.number="perPage" @change="loadDocs(1); loadMsgs(1); loadFiles(1)" class="w-24 {{ $selectCls }}">
                                <option value="10">10</option><option value="25">25</option>
                                <option value="50">50</option><option value="100">100</option>
                            </select>
                            <span class="hidden shrink-0 sm:inline">Entries</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="overflow-x-auto" x-show="msgs.length">
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $theadCls }}">
                            <tr>
                                <th class="{{ $thCls }}">Doc ID</th>
                                <th class="{{ $thCls }} ma-xcol">Type</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Date</th>
                                <th class="{{ $thCls }}">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="m in msgs" :key="m.id">
                                <tr class="{{ $rowCls }} ma-r ma-r-msg">
                                    <td class="whitespace-nowrap px-3 py-2 align-top">
                                        <a x-show="m.href" :href="m.href" target="_blank" rel="noopener noreferrer" class="{{ $pillCls }}" x-text="m.docid"></a>
                                        <span x-show="!m.href" class="{{ $combined ? 'dsh-id' : 'inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white dark:bg-cyan-700' }}" x-text="m.docid"></span>
                                        <small class="ma-fsize" x-text="m.type"></small>
                                    </td>
                                    <td class="{{ $tdCls }} ma-xcol" x-text="m.type"></td>
                                    <td class="{{ $tdCls }}" x-text="m.company || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="m.dept || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="fmt(m.date)"></td>
                                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">
                                        <b x-show="msgScope === 'others' && m.by" class="ma-by" x-text="m.by"></b>
                                        <span x-show="m.private" class="mr-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Private</span>
                                        <span class="whitespace-pre-line break-words" x-text="m.text"></span>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="msgsLoading" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">Loading…</div>
                <div x-show="!msgsLoading && !msgs.length" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">No data available</div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                <span x-text="'Showing ' + msgPg.from + ' to ' + msgPg.to + ' of ' + msgPg.total + ' entries'"></span>
                <div class="flex gap-2">
                    <button type="button" @click="loadMsgs(msgPg.page - 1)" :disabled="msgPg.page <= 1" class="{{ $pagerBtn }}">Previous</button>
                    <button type="button" @click="loadMsgs(msgPg.page + 1)" :disabled="msgPg.page >= msgPg.last_page" class="{{ $pagerBtn }}">Next</button>
                </div>
            </div>
        </div>

        {{-- My Files --}}
        <div x-show="mode === 'file' || mode === 'both'"
            :class="embedded ? '' : 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800'">

            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="ma-s2 min-w-0 lg:col-span-4">
                        <select x-model="fileExt" x-init="$nextTick(() => s2($el, 'fileExt', 'fileExts', () => loadFiles(1), -1))" class="{{ $selectCls }}">
                            <option value="">All File Types</option>
                            <template x-for="e in fileExts" :key="e"><option :value="e" x-text="e.toUpperCase()"></option></template>
                        </select>
                    </div>
                    <div class="lg:col-span-4">
                        <input type="text" x-model="fileQ" @input.debounce.400ms="loadFiles(1)" placeholder="Search..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="hidden shrink-0 sm:inline">Show</span>
                            <select x-model.number="perPage" @change="loadDocs(1); loadMsgs(1); loadFiles(1)" class="w-24 {{ $selectCls }}">
                                <option value="10">10</option><option value="25">25</option>
                                <option value="50">50</option><option value="100">100</option>
                            </select>
                            <span class="hidden shrink-0 sm:inline">Entries</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="p-4">
                <div class="overflow-x-auto" x-show="files.length">
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $theadCls }}">
                            <tr>
                                <th class="{{ $thCls }}">File</th>
                                <th class="{{ $thCls }}">Doc ID</th>
                                <th class="{{ $thCls }} ma-xcol">Type</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Uploaded</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="f in files" :key="f.id">
                                <tr class="{{ $rowCls }} ma-r ma-r-file">
                                    <td class="px-3 py-2 align-top">
                                        <div class="ma-fcell">
                                            <span class="ma-ftile" :class="'ma-ft-' + fileKind(f.ext)" x-text="(f.ext || 'file').toUpperCase().slice(0, 4)"></span>
                                            <span class="min-w-0">
                                                <a :href="f.file" target="_blank" rel="noopener noreferrer"
                                                    class="break-all font-medium text-blue-600 hover:underline dark:text-cyan-400" x-text="f.name"></a>
                                                <small class="ma-fsize" x-text="size(f.size)"></small>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-2 align-top">
                                        <a x-show="f.href" :href="f.href" target="_blank" rel="noopener noreferrer" class="{{ $pillCls }}" x-text="f.docid"></a>
                                        <span x-show="!f.href" class="ma-dead" title="Document not found" x-text="f.docid || '-'"></span>
                                        <small class="ma-fsize" x-text="f.type"></small>
                                    </td>
                                    <td class="{{ $tdCls }} ma-xcol" x-text="f.type || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="f.company || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="f.dept || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="fmt(f.date)"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                <div x-show="filesLoading" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">Loading…</div>
                <div x-show="!filesLoading && !files.length" class="py-10 text-center text-sm text-slate-400 dark:text-slate-500">No data available</div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-3 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                <span x-text="'Showing ' + filePg.from + ' to ' + filePg.to + ' of ' + filePg.total + ' entries'"></span>
                <div class="flex gap-2">
                    <button type="button" @click="loadFiles(filePg.page - 1)" :disabled="filePg.page <= 1" class="{{ $pagerBtn }}">Previous</button>
                    <button type="button" @click="loadFiles(filePg.page + 1)" :disabled="filePg.page >= filePg.last_page" class="{{ $pagerBtn }}">Next</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/multidashboard/my-activity.js') }}?v={{ filemtime(public_path('assets/js/multidashboard/my-activity.js')) }}"></script>
<script>
    // Adds "My Message" / "My Document" to the dashboard's own tab bar. Each dashboard has its
    // own tab JS, so rather than touch all of them we hook the DOM they share: a row of
    // button#tab-* (#wh-tab-* on Warehouse) inside a card whose other children are the filters,
    // list and pagination. Selecting one of ours hides those and shows the panel in their place;
    // clicking any original tab undoes that and lets the dashboard's own handler run as before.
    (function () {
        const ACTIVE = 'rounded-xl px-4 py-2 text-sm font-semibold transition-all duration-200 bg-black text-white shadow-sm dark:bg-zinc-700';
        const IDLE = 'rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-all duration-200 hover:bg-slate-50 hover:border-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700';
        const TAB_SEL = 'button[id^="tab-"], button[id^="wh-tab-"]';

        function setup() {
            const root = document.getElementById('myActivityRoot');
            if (!root) return;
            // the standalone panel (components/panel/my-activity) sits where it is: no dock, no tab
            if (root.dataset.panel === '1') return;
            const dock = document.getElementById('myActivityDock');
            if (root.dataset.combined === '1' && dock) { root.dataset.embedded = '1'; root.dataset.docked = '1'; dock.appendChild(root); return; }
            const first = document.querySelector(TAB_SEL);
            if (!first) return;

            const bar = first.parentElement;
            const header = bar.parentElement;
            const card = header && header.parentElement;
            if (!card || card === document.body) return;

            const combined = root.dataset.combined === '1';
            const defs = combined
                ? [{ id: 'my-tab-activity', mode: 'activity', label: MA_ICONS.user + '<span>My Activity</span>' }]
                : [
                    { id: 'my-tab-document', mode: 'doc', label: '📄 My Document' },
                    { id: 'my-tab-message', mode: 'msg', label: '💬 My Message' },
                    { id: 'my-tab-files', mode: 'file', label: '📎 My Files' },
                ];
            const mine = defs.map(t => {
                const b = document.createElement('button');
                b.type = 'button'; b.id = t.id; b.innerHTML = t.label; b.className = combined ? 'dsh-tab' : IDLE;
                b.dataset.mode = t.mode;
                bar.appendChild(b);
                return b;
            });
            const styleOf = (o, on) => { o.className = combined ? (on ? 'dsh-tab dsh-tab-on' : 'dsh-tab') : (on ? ACTIVE : IDLE); };

            root.dataset.embedded = '1';
            card.appendChild(root);

            let hidden = [];
            const hideBody = () => {
                if (hidden.length) return;
                hidden = [...card.children].filter(el => el !== header && el !== root );
                hidden.forEach(el => { el.dataset.prevDisplay = el.style.display; el.style.display = 'none'; });
            };
            const showBody = () => {
                hidden.forEach(el => { el.style.display = el.dataset.prevDisplay || ''; });
                hidden = [];
            };
            const emit = mode => window.dispatchEvent(new CustomEvent('my-activity-open', { detail: mode }));

            mine.forEach(b => b.addEventListener('click', () => {
                // deactivate the dashboard's own tabs; ours take the active style
                bar.querySelectorAll(TAB_SEL).forEach(o => { o.className = combined ? 'dsh-tab' : IDLE; });
                mine.forEach(o => styleOf(o, o === b));
                hideBody();
                emit(b.dataset.mode);
            }));

            // Delegated + capture so it runs before the dashboard's own click handler,
            // which then re-styles its tabs and reloads its data as usual.
            bar.addEventListener('click', e => {
                const t = e.target.closest(TAB_SEL);
                if (!t || mine.includes(t)) return;
                mine.forEach(o => styleOf(o, false));
                showBody();
                emit('');
            }, true);
        }

        // Both the dashboard's tab bar and #myActivityRoot are above this script, so run now —
        // that is always before Alpine initialises myActivity() and reads data-embedded.
        setup();
    })();
</script>
