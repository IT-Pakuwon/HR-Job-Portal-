<x-app-layout>
    {{-- Dashboard Setup: arrange the blocks of each dashboard in rows and columns. Saved in ms_dashboard_layout; the same for everyone
         assigned to the dashboard (the data inside the blocks is still filtered by each user's company). A block that is not on the page is OFF. --}}
    <div class="max-w-9xl mx-auto w-full p-2"
        x-data="dashboardLayoutEditor(@js($dashboards), @js($catalog), @js($spans), @js($maxRows), @js(url('dashboard-setup')), @js(request('dash')), @js($refreshChoices), @js($limits))">

        <div class="mb-5 flex flex-wrap items-center gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-br from-indigo-600 to-violet-700 text-xl text-white shadow-lg shadow-indigo-500/30">
                <i class="fa-solid fa-table-cells-large"></i>
            </div>
            <div class="min-w-0 flex-1">
                <h1 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">Dashboard Setup</h1>
                <p class="mt-0.5 max-w-3xl text-xs leading-relaxed text-gray-500 dark:text-gray-400">Pick a dashboard, then drag its blocks into rows and columns. Everyone assigned to it sees the same arrangement; the data inside is still filtered by each user's company.</p>
            </div>
            <div class="flex shrink-0 flex-wrap items-center gap-2">
                <span x-show="changed" x-cloak class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1.5 text-[11px] font-semibold text-amber-700 ring-1 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30">
                    <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-amber-500"></span> Unsaved changes
                </span>
                <button type="button" @click="reset()" :disabled="busy || !current.custom"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-600 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <i class="fa-solid fa-rotate-left text-[11px]"></i> Reset to standard
                </button>
                <button type="button" @click="save()" :disabled="busy || !changed"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-indigo-500/25 transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none">
                    <i class="fa-solid text-[11px]" :class="busy ? 'fa-spinner fa-spin' : 'fa-floppy-disk'"></i> Save arrangement
                </button>
            </div>
        </div>

        <div x-show="msg" x-cloak x-transition
            :class="ok ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700'"
            class="mb-3 rounded-lg border px-3 py-2 text-sm" x-text="msg"></div>

        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-end gap-x-6 gap-y-3">

                {{-- Dashboard switcher --}}
                <div class="relative w-full sm:w-96" @click.outside="dashOpen = false" @keydown.escape="dashOpen = false">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Dashboard</span>
                    <button type="button" @click="dashOpen = !dashOpen; if (dashOpen) $nextTick(() => $refs.dashSearch.focus())" aria-haspopup="listbox" :aria-expanded="dashOpen"
                        :class="dashOpen ? 'border-indigo-500 ring-2 ring-indigo-100 dark:ring-indigo-500/20' : 'border-gray-200 hover:border-indigo-300 dark:border-gray-600'"
                        class="flex w-full items-center gap-3 rounded-xl border bg-white px-3 py-2.5 text-left shadow-sm transition focus:outline-none dark:bg-gray-700/40">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300"><i class="fa-solid fa-table-cells-large text-sm"></i></span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="current.name"></span>
                            <span class="block truncate font-mono text-[11px] text-gray-400" x-text="current.code"></span>
                        </span>
                        <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide"
                            :class="current.custom ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-600 dark:text-gray-300'">
                            <span class="h-1.5 w-1.5 rounded-full" :class="current.custom ? 'bg-emerald-500' : 'bg-gray-400'"></span>
                            <span x-text="current.custom ? 'Custom' : 'Standard'"></span>
                        </span>
                        <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="dashOpen ? 'rotate-180' : ''"></i>
                    </button>

                    <div x-show="dashOpen" x-cloak x-transition.origin.top role="listbox"
                        class="absolute left-0 right-0 z-30 mt-2 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-xl dark:border-gray-600 dark:bg-gray-800">
                        <div class="border-b border-gray-100 p-2 dark:border-white/10">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                                <input type="text" x-ref="dashSearch" x-model="dashQ" placeholder="Search dashboard…"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 py-1.5 pl-8 pr-3 text-sm focus:border-indigo-400 focus:bg-white focus:outline-none dark:border-white/10 dark:bg-white/5 dark:text-gray-100">
                            </div>
                        </div>
                        <div class="max-h-72 overflow-y-auto p-1.5">
                            <template x-for="d in dashList" :key="d.code">
                                <button type="button" role="option" :aria-selected="d.code === dash" @click="choose(d.code)"
                                    :class="d.code === dash ? 'bg-indigo-50 dark:bg-indigo-500/10' : 'hover:bg-gray-50 dark:hover:bg-white/5'"
                                    class="flex w-full items-center gap-3 rounded-lg px-2.5 py-2 text-left transition">
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm text-gray-800 dark:text-gray-100" x-text="d.name"></span>
                                        <span class="block truncate font-mono text-[11px] text-gray-400" x-text="d.code"></span>
                                    </span>
                                    <span x-show="d.custom" class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Custom</span>
                                    <i class="fa-solid fa-check w-3 text-xs text-indigo-600" :class="d.code === dash ? '' : 'invisible'"></i>
                                </button>
                            </template>
                            <p x-show="!dashList.length" class="px-3 py-6 text-center text-xs text-gray-400">No matching dashboard.</p>
                        </div>
                    </div>
                </div>

                {{-- What this dashboard has --}}
                <div class="min-w-0 flex-1">
                    <span class="mb-1.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400">This dashboard has</span>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium"
                            :class="current.tiles ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'">
                            <i class="fa-solid fa-bars text-[10px]"></i>
                            <span x-text="current.tiles ? current.tiles + (current.tiles === 1 ? ' menu tile' : ' menu tiles') : 'No menu tiles'"></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium"
                            :class="current.has.kpis ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'">
                            <i class="fa-solid fa-gauge-high text-[10px]"></i> <span x-text="current.has.kpis ? 'KPI cards' : 'No KPI cards'"></span>
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium"
                            :class="current.has.tabs ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300' : 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500'">
                            <i class="fa-solid fa-folder-open text-[10px]"></i> <span x-text="current.has.tabs ? 'Work tabs' : 'No work tabs'"></span>
                        </span>
                    </div>
                </div>

                <p class="max-w-xs text-[11px] leading-relaxed text-gray-400" x-text="current.custom ? 'A saved arrangement is in use. Reset to go back to the standard one.' : 'Nothing saved yet: this dashboard shows the standard arrangement.'"></p>
            </div>
        </div>

        {{-- Heading and auto-refresh of this dashboard (stored with the arrangement, in the same row) --}}
        <div class="mb-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-3 flex flex-wrap items-center gap-2">
                <i class="fa-solid fa-sliders text-sm text-indigo-500"></i>
                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Page settings</h2>
                <span class="text-[11px] text-gray-400">Leave a field empty to keep what the dashboard has by default.</span>
            </div>
            <div class="grid gap-4 lg:grid-cols-12">
                <label class="lg:col-span-4">
                    <span class="mb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-gray-400">Title <span class="font-medium normal-case tracking-normal" x-text="form.title.length + '/' + limits.title"></span></span>
                    <input type="text" x-model="form.title" :maxlength="limits.title" :placeholder="current.headerDefault.title"
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-100">
                </label>
                <label class="lg:col-span-5">
                    <span class="mb-1 flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-gray-400">Subtitle <span class="font-medium normal-case tracking-normal" x-text="form.subtitle.length + '/' + limits.subtitle"></span></span>
                    <input type="text" x-model="form.subtitle" :maxlength="limits.subtitle" :placeholder="current.headerDefault.subtitle"
                        class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-gray-600 dark:bg-gray-700/40 dark:text-gray-100">
                </label>
                <div class="lg:col-span-3">
                    <span class="mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400">Refresh every</span>
                    <div class="flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1 dark:border-gray-700 dark:bg-gray-900/40" role="group" aria-label="Auto-refresh interval">
                        <button type="button" @click="form.refresh = null" :aria-pressed="form.refresh === null"
                            :class="form.refresh === null ? 'bg-white text-indigo-700 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                            class="rounded-lg px-2.5 py-1.5 text-xs font-semibold transition" title="Every dashboard, tab and panel refreshes every {{ \App\Services\Dashboard\DashboardLayout::DEFAULT_REFRESH }} seconds unless you pick another value">Default ({{ \App\Services\Dashboard\DashboardLayout::DEFAULT_REFRESH }}s)</button>
                        <template x-for="n in refreshChoices" :key="n">
                            <button type="button" @click="form.refresh = n" :aria-pressed="form.refresh === n"
                                :class="form.refresh === n ? 'bg-white text-indigo-700 shadow-sm dark:bg-gray-700 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-800 dark:text-gray-400'"
                                class="rounded-lg px-2.5 py-1.5 text-xs font-semibold tabular-nums transition" x-text="n < 60 ? n + 's' : (n / 60) + 'm'"></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-3 xl:grid-cols-[minmax(0,1fr)_17rem]">

            {{-- The page: rows from top to bottom, columns side by side inside a row, blocks stacked inside a column --}}
            <section class="space-y-3 rounded-2xl border border-gray-200 bg-gray-100 p-3 dark:border-gray-700 dark:bg-gray-900/40" aria-label="Dashboard arrangement">
                <template x-for="(row, ri) in rows" :key="ri">
                    <div class="rounded-xl border border-gray-200 bg-gray-50 p-2 dark:border-gray-700 dark:bg-gray-800/40">
                        <div class="mb-2 flex flex-wrap items-center gap-2 px-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400" x-text="'Row ' + (ri + 1)"></span>
                            <div class="flex items-center gap-1 rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-900/40" role="group" aria-label="Number of columns in this row">
                                <template x-for="n in [1, 2, 3]" :key="n">
                                    <button type="button" @click="setColumnCount(ri, n)" :aria-pressed="row.columns.length === n" :title="n + (n === 1 ? ' column' : ' columns')"
                                        :class="row.columns.length === n ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'text-gray-500 hover:text-gray-800'"
                                        class="h-6 min-w-7 rounded-md px-2 text-xs font-bold transition" x-text="n"></button>
                                </template>
                            </div>
                            <div x-show="row.columns.length > 1" x-cloak class="flex flex-wrap items-center gap-1 rounded-lg border border-gray-200 bg-white p-0.5 dark:border-gray-700 dark:bg-gray-900/40" role="group" aria-label="Column widths in this row">
                                <template x-for="t in layoutsFor(row.columns.length)" :key="t.join('-')">
                                    <button type="button" @click="applyTemplate(ri, t)" :aria-pressed="sameSpans(ri, t)" :title="t.join(' / ')"
                                        :class="sameSpans(ri, t) ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300' : 'text-gray-400 hover:text-gray-700'"
                                        class="flex h-6 w-14 items-center gap-0.5 rounded-md px-1.5 transition">
                                        <template x-for="(s, i) in t" :key="i"><span class="h-3 rounded-sm bg-current opacity-70" :style="'flex:' + s + ' 1 0'"></span></template>
                                    </button>
                                </template>
                            </div>
                            <span class="flex-1"></span>
                            <button type="button" @click="moveRow(ri, -1)" :disabled="ri === 0" class="rounded p-1 text-gray-400 hover:bg-white hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :aria-label="'Move row ' + (ri + 1) + ' up'"><i class="fa-solid fa-arrow-up text-xs"></i></button>
                            <button type="button" @click="moveRow(ri, 1)" :disabled="ri === rows.length - 1" class="rounded p-1 text-gray-400 hover:bg-white hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :aria-label="'Move row ' + (ri + 1) + ' down'"><i class="fa-solid fa-arrow-down text-xs"></i></button>
                            <button type="button" @click="removeRow(ri)" :disabled="rows.length === 1" class="rounded p-1 text-gray-400 hover:bg-red-50 hover:text-red-600 disabled:opacity-30 dark:hover:bg-red-500/10" :aria-label="'Remove row ' + (ri + 1)" title="Remove row (its blocks move to the row above)"><i class="fa-solid fa-trash-can text-xs"></i></button>
                        </div>

                        <div class="flex items-stretch gap-2">
                            <template x-for="(c, ci) in row.columns" :key="ci">
                                <div class="flex min-h-24 min-w-0 flex-col gap-2 rounded-lg border-2 border-dashed p-2 transition"
                                    :class="over === ri + '-' + ci ? 'border-indigo-400 bg-indigo-50/60 dark:bg-indigo-500/10' : 'border-gray-300 dark:border-gray-600'"
                                    :style="'flex:' + c.span + ' 1 0'"
                                    @dragover.prevent="over = ri + '-' + ci" @dragleave="over = null" @drop.prevent="drop(ri, ci, c.blocks.length)">
                                    <div class="px-1 text-[10px] font-semibold uppercase tracking-wider text-gray-400" x-text="'Column ' + (ci + 1) + ' · ' + c.span + '/12'"></div>

                                    <template x-for="(id, bi) in c.blocks" :key="id">
                                        <div draggable="true" @dragstart="dragged = id; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', id)"
                                            @dragend="dragged = null; over = null" @dragover.prevent.stop="over = ri + '-' + ci" @drop.prevent.stop="drop(ri, ci, bi)"
                                            :class="dragged === id ? 'opacity-40' : ''"
                                            class="cursor-grab rounded-xl border border-gray-200 bg-white p-2.5 shadow-sm active:cursor-grabbing dark:border-gray-600 dark:bg-gray-800">
                                            <div class="flex items-start gap-2.5">
                                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300"><i class="fa-solid text-sm" :class="info(id).icon"></i></span>
                                                <span class="min-w-0 flex-1">
                                                    <span class="block text-sm font-semibold text-gray-800 dark:text-gray-100" x-text="info(id).label"></span>
                                                    <span class="block text-[11px] text-gray-500 dark:text-gray-400" x-text="info(id).desc"></span>
                                                    <span x-show="unavailable(id)" class="mt-1 block text-[10px] font-medium text-amber-600 dark:text-amber-400" x-text="unavailable(id)"></span>
                                                </span>
                                            </div>
                                            <div class="mt-1.5 flex items-center justify-end gap-0.5 text-gray-400">
                                                <button type="button" @click="shift(id, -1)" :disabled="ci === 0" class="rounded p-1 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :aria-label="'Move ' + info(id).label + ' to the previous column'"><i class="fa-solid fa-arrow-left text-xs"></i></button>
                                                <button type="button" @click="step(id, -1)" class="rounded p-1 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :disabled="ri === 0 && bi === 0" :aria-label="'Move ' + info(id).label + ' up'"><i class="fa-solid fa-arrow-up text-xs"></i></button>
                                                <button type="button" @click="step(id, 1)" class="rounded p-1 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :disabled="ri === rows.length - 1 && bi === c.blocks.length - 1" :aria-label="'Move ' + info(id).label + ' down'"><i class="fa-solid fa-arrow-down text-xs"></i></button>
                                                <button type="button" @click="shift(id, 1)" :disabled="ci === row.columns.length - 1" class="rounded p-1 hover:bg-gray-100 hover:text-gray-700 disabled:opacity-30 dark:hover:bg-gray-700" :aria-label="'Move ' + info(id).label + ' to the next column'"><i class="fa-solid fa-arrow-right text-xs"></i></button>
                                                <button type="button" x-show="!info(id).locked" @click="remove(id)" class="ml-1 rounded p-1 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" :aria-label="'Take ' + info(id).label + ' off this dashboard'" title="Take off this dashboard"><i class="fa-solid fa-xmark text-xs"></i></button>
                                                <span x-show="info(id).locked" class="ml-1 p-1" title="Needed to open a tile's panel"><i class="fa-solid fa-lock text-xs"></i></span>
                                            </div>
                                        </div>
                                    </template>

                                    <div x-show="!c.blocks.length" class="flex flex-1 items-center justify-center rounded-lg py-4 text-center text-xs text-gray-400">Drop a block here</div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <button type="button" @click="addRow()" :disabled="rows.length >= maxRows"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-dashed border-indigo-300 bg-indigo-50/50 py-2 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 disabled:opacity-40 dark:border-indigo-500/40 dark:bg-indigo-500/5 dark:text-indigo-300">
                    <i class="fa-solid fa-plus text-[10px]"></i> Add a row
                </button>
                <p class="text-[11px] text-gray-500 dark:text-gray-400">Blocks that do not apply to a dashboard are skipped when it is shown (for example Main Menu without tiles), and an emptied column or row folds away. A block taken off the KPI cards or Work tabs stays on the page out of sight, because the dashboard's script reads it. On small screens everything stacks in this order.</p>
            </section>

            {{-- Blocks not on this dashboard --}}
            <aside class="self-start rounded-2xl border border-gray-200 bg-white p-3 shadow-sm dark:border-gray-700 dark:bg-gray-800" aria-label="Available blocks"
                @dragover.prevent="over = 'tray'" @dragleave="over = null" @drop.prevent="dropTray()"
                :class="over === 'tray' ? 'ring-2 ring-indigo-300' : ''">
                <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100">Not on this dashboard</h2>
                <p class="mb-3 mt-0.5 text-[11px] text-gray-500 dark:text-gray-400">Drag a block into a column to add it, or drag one back here to take it off.</p>
                <div class="space-y-2">
                    <template x-for="id in tray" :key="id">
                        <div draggable="true" @dragstart="dragged = id; $event.dataTransfer.effectAllowed = 'move'; $event.dataTransfer.setData('text/plain', id)" @dragend="dragged = null; over = null"
                            class="flex cursor-grab items-center gap-2.5 rounded-xl border border-dashed border-gray-300 bg-gray-50 p-2.5 dark:border-gray-600 dark:bg-gray-700/30">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-gray-200 text-gray-500 dark:bg-gray-600 dark:text-gray-300"><i class="fa-solid text-sm" :class="info(id).icon"></i></span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="info(id).label"></span>
                                <span class="block truncate text-[11px] text-gray-500 dark:text-gray-400" x-text="info(id).desc"></span>
                            </span>
                            <button type="button" @click="add(id)" class="shrink-0 rounded-lg bg-indigo-600 px-2.5 py-1 text-[11px] font-semibold text-white hover:bg-indigo-700" :aria-label="'Add ' + info(id).label">Add</button>
                        </div>
                    </template>
                    <p x-show="!tray.length" x-cloak class="py-3 text-center text-xs text-gray-400">Every block is on this dashboard.</p>
                </div>

                <template x-if="unusable.length">
                    <div class="mt-4 border-t border-gray-100 pt-3 dark:border-gray-700">
                        <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400">This dashboard has no</h3>
                        <ul class="mt-2 space-y-1.5">
                            <template x-for="id in unusable" :key="id">
                                <li class="flex items-center gap-2 text-xs text-gray-400"><i class="fa-solid w-4 text-center" :class="info(id).icon"></i><span x-text="info(id).label"></span></li>
                            </template>
                        </ul>
                    </div>
                </template>
            </aside>
        </div>
    </div>

    <script>
        function dashboardLayoutEditor(dashboards, catalog, spans, maxRows, baseUrl, wanted, refreshChoices, limits) {
            const ICON = { menu: 'fa-bars', favourites: 'fa-star', panels: 'fa-window-maximize', progress: 'fa-chart-line', todo: 'fa-circle-check', kpis: 'fa-gauge-high', tabs: 'fa-folder-open' };
            const REASON = { tiles: 'Shown only when the dashboard has menu tiles.', counts: 'Shown only when a menu tile carries a count.', kpis: 'This dashboard has no KPI cards.', tabs: 'This dashboard has no work tabs.' };
            const byId = Object.fromEntries(catalog.map(b => [b.id, { ...b, icon: ICON[b.id] || 'fa-square' }]));
            // a layout from the server -> only the blocks that are ON; the Panel area is always there when the dashboard has tiles
            const toRows = (layout, has) => {
                const rows = layout.rows.map(r => ({ columns: r.columns.map(c => ({ span: c.span, blocks: c.blocks.filter(b => b.v !== false).map(b => b.id) })) }));
                if (has.tiles && !rows.some(r => r.columns.some(c => c.blocks.includes('panels')))) {
                    const last = rows[rows.length - 1].columns; last[last.length - 1].blocks.push('panels');
                }
                return rows;
            };

            return {
                dashboards, maxRows,
                dash: (dashboards.find(d => d.code === wanted) || dashboards[0] || {}).code,
                rows: [], dragged: null, over: null, dirty: false, busy: false, msg: '', ok: true,
                dashOpen: false, dashQ: '',
                refreshChoices, limits, form: { title: '', subtitle: '', refresh: null }, baseSettings: '',

                init() { this.load(); },
                get current() { return this.dashboards.find(d => d.code === this.dash) || { custom: false, layout: { rows: [] }, default: { rows: [] }, has: {} }; },
                // the page settings differ from what is saved; "changed" = anything unsaved (arrangement or settings)
                get settingsChanged() { return JSON.stringify(this.form) !== this.baseSettings; },
                get changed() { return this.dirty || this.settingsChanged; },
                get dashList() { const q = this.dashQ.trim().toLowerCase(); return this.dashboards.filter(d => !q || (d.name + ' ' + d.code).toLowerCase().includes(q)); },
                get placed() { return this.rows.flatMap(r => r.columns.flatMap(c => c.blocks)); },
                // a block the dashboard cannot have at all (no KPI cards / work tabs of its own) is not offered
                canHave(id) { const n = (byId[id] || {}).needs; return n !== 'kpis' && n !== 'tabs' ? true : !!this.current.has[n]; },
                get tray() { return catalog.map(b => b.id).filter(id => !this.placed.includes(id) && this.canHave(id)); },
                get unusable() { return catalog.map(b => b.id).filter(id => !this.canHave(id)); },
                info(id) { return byId[id] || { label: id, desc: '', icon: 'fa-square', locked: false }; },
                unavailable(id) { const n = (byId[id] || {}).needs; return n && !this.current.has[n] ? REASON[n] : ''; },
                sameSpans(ri, t) { const cs = this.rows[ri].columns; return t.length === cs.length && t.every((s, i) => s === cs[i].span); },

                load() {
                    this.rows = toRows(this.current.layout, this.current.has); this.dirty = false; this.over = null;
                    const st = this.current.settings || {};
                    this.form = { title: st.title || '', subtitle: st.subtitle || '', refresh: st.refresh || null };
                    this.baseSettings = JSON.stringify(this.form);
                },
                choose(code) {
                    this.dashOpen = false; this.dashQ = '';
                    if (code === this.dash) return;
                    if (this.changed && !confirm('This dashboard has changes you have not saved. Switch anyway and lose it?')) return;
                    this.dash = code;
                    this.load();
                    try { history.replaceState(null, '', '?dash=' + encodeURIComponent(this.dash)); } catch (x) {}
                },

                // rows
                addRow() { if (this.rows.length < maxRows) { this.rows.push({ columns: [{ span: 12, blocks: [] }] }); this.dirty = true; } },
                removeRow(ri) {
                    if (this.rows.length === 1) return;
                    const moved = this.rows[ri].columns.flatMap(c => c.blocks), to = ri > 0 ? ri - 1 : 1;
                    const cols = this.rows[to].columns; cols[cols.length - 1].blocks.push(...moved);
                    this.rows.splice(ri, 1); this.dirty = true;
                },
                moveRow(ri, d) { const j = ri + d; if (j < 0 || j >= this.rows.length) return; [this.rows[ri], this.rows[j]] = [this.rows[j], this.rows[ri]]; this.dirty = true; },
                // every way to split 12 into n columns using the allowed widths (3..9): 2 columns = 3/9 ... 9/3, 3 columns = 3/3/6 ... 6/3/3
                layoutsFor(n) {
                    if (n === 1) return [[12]];
                    const out = [], rec = acc => {
                        const sum = acc.reduce((a, b) => a + b, 0);
                        if (acc.length === n) { if (sum === 12) out.push(acc.slice()); return; }
                        for (const s of spans) { if (s === 12 || sum + s > 12) continue; acc.push(s); rec(acc); acc.pop(); }
                    };
                    rec([]);
                    return out;
                },
                // a different number of columns starts from an even split; the width buttons then fine-tune it
                setColumnCount(ri, n) {
                    if (this.rows[ri].columns.length === n) return;
                    this.applyTemplate(ri, { 1: [12], 2: [6, 6], 3: [4, 4, 4] }[n]);
                },
                // columns of one row: keep every block, folding extra columns into the last one
                applyTemplate(ri, t) {
                    const blocks = this.rows[ri].columns.map(c => c.blocks);
                    this.rows[ri].columns = t.map((s, i) => ({ span: s, blocks: i < t.length - 1 ? (blocks[i] || []) : blocks.slice(i).flat() }));
                    this.dirty = true;
                },

                // blocks
                detach(id) { this.rows.forEach(r => r.columns.forEach(c => { c.blocks = c.blocks.filter(x => x !== id); })); },
                drop(ri, ci, idx) {
                    const id = this.dragged; this.over = null; if (!id) return;
                    const col = this.rows[ri].columns[ci], from = col.blocks.indexOf(id);
                    if (from > -1 && from < idx) idx--; // dropping lower in the same column: the removal shifts everything up by one
                    this.detach(id);
                    col.blocks.splice(Math.max(0, Math.min(idx, col.blocks.length)), 0, id);
                    this.dragged = null; this.dirty = true;
                },
                dropTray() { const id = this.dragged; this.over = null; if (id && !this.info(id).locked) { this.detach(id); this.dirty = true; } this.dragged = null; },
                add(id) { const cs = this.rows[this.rows.length - 1].columns; cs[cs.length - 1].blocks.push(id); this.dirty = true; },
                remove(id) { this.detach(id); this.dirty = true; },
                locate(id) {
                    for (let ri = 0; ri < this.rows.length; ri++) for (let ci = 0; ci < this.rows[ri].columns.length; ci++) {
                        const bi = this.rows[ri].columns[ci].blocks.indexOf(id); if (bi > -1) return { ri, ci, bi };
                    }
                    return null;
                },
                // up / down: inside the column, then on into the neighbouring row
                step(id, d) {
                    const p = this.locate(id); if (!p) return;
                    const col = this.rows[p.ri].columns[p.ci], j = p.bi + d;
                    if (j >= 0 && j < col.blocks.length) { [col.blocks[p.bi], col.blocks[j]] = [col.blocks[j], col.blocks[p.bi]]; this.dirty = true; return; }
                    const nr = p.ri + d; if (nr < 0 || nr >= this.rows.length) return;
                    this.detach(id);
                    const cols = this.rows[nr].columns, target = d < 0 ? cols[cols.length - 1] : cols[0];
                    if (d < 0) target.blocks.push(id); else target.blocks.unshift(id);
                    this.dirty = true;
                },
                shift(id, d) {
                    const p = this.locate(id); if (!p) return;
                    const to = p.ci + d, cols = this.rows[p.ri].columns; if (to < 0 || to >= cols.length) return;
                    this.detach(id); cols[to].blocks.push(id); this.dirty = true;
                },

                flash(ok, msg) { this.ok = ok; this.msg = msg; setTimeout(() => { this.msg = ''; }, 4000); },
                async send(method, body) {
                    this.busy = true;
                    try {
                        const res = await fetch(baseUrl + '/' + encodeURIComponent(this.dash), {
                            method,
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '' },
                            body: body ? JSON.stringify(body) : undefined,
                        });
                        const data = await res.json().catch(() => ({}));
                        if (!res.ok) { this.flash(false, data.message || 'Could not save (HTTP ' + res.status + ').'); return null; }
                        return data;
                    } catch (e) { this.flash(false, 'Could not reach the server.'); return null; }
                    finally { this.busy = false; }
                },
                async save() {
                    // the arrangement is sent when it was changed (or one is already saved); a title / refresh change alone leaves the standard arrangement standard
                    const rows = (this.dirty || this.current.hasRows)
                        ? this.rows.map(r => ({ columns: r.columns.map(c => ({ span: c.span, blocks: c.blocks.map(id => ({ id, v: true })) })) }))
                        : null;
                    const data = await this.send('PUT', { rows, settings: { title: this.form.title, subtitle: this.form.subtitle, refresh: this.form.refresh } });
                    if (!data) return;
                    this.current.custom = true; this.current.hasRows = data.hasRows; this.current.settings = data.settings;
                    if (data.layout) this.current.layout = data.layout;
                    this.dirty = false;
                    this.form = { title: data.settings.title || '', subtitle: data.settings.subtitle || '', refresh: data.settings.refresh || null };
                    this.baseSettings = JSON.stringify(this.form);
                    this.flash(true, this.current.name + ' saved.');
                },
                async reset() {
                    if (!confirm('Go back to the standard arrangement for this dashboard?')) return;
                    if (!await this.send('DELETE')) return;
                    this.current.custom = false; this.current.hasRows = false; this.current.settings = { title: null, subtitle: null, refresh: null }; this.current.layout = this.current.default; this.load();
                    this.flash(true, 'Standard arrangement restored.');
                },
            };
        }
    </script>
</x-app-layout>
