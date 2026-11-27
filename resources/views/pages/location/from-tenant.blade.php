<x-app-layout>
    <x-spp-list-styles />
    <x-setting-skin />

    <div class="max-w-9xl mx-auto w-full space-y-3 p-2" :class="{ 'pb-24': selectedRows.length }" x-data="fromTenant()" x-init="load()">

        {{-- Title + status cards (the cards are the status filter) --}}
        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-gray-800 set-card">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between set-head">
                <div>
                    <h2 class="set-title"><span class="set-ico"><i class="fa-solid fa-store"></i></span><span>Sub Location From Tenant<span class="set-hint">Pick a status, check the Location and name, tick the rows, then add them.</span></span></h2>
                </div>
                <a href="{{ route('locations') }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/5">
                    <i class="fa-solid fa-arrow-left text-xs"></i> Locations
                </a>
            </div>

            <div class="grid grid-cols-2 gap-3 p-4 lg:grid-cols-4">
                <template x-for="t in tabs" :key="t.key">
                    <button type="button" @click="setShow(t.key)" :aria-pressed="show === t.key"
                        class="flex items-center gap-3 rounded-xl border p-3 text-left transition"
                        :class="show === t.key ? t.on : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5'">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg" :class="t.icon"><i class="fa-solid" :class="t.fa"></i></span>
                        <span class="min-w-0">
                            <span class="flex items-baseline gap-2">
                                <span class="text-xl font-bold leading-6 text-gray-800 dark:text-gray-100" x-text="loading && !rows.length ? '…' : count(t.key)"></span>
                                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200" x-text="t.label"></span>
                            </span>
                            <span class="block text-xs text-gray-500 dark:text-gray-400" x-text="t.hint"></span>
                        </span>
                    </button>
                </template>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-gray-800 set-card dt-pretty">
            {{-- Toolbar: DataTables class names, so "Show entries" and Search get the same shared style as the other lists --}}
            <div class="dataTables_wrapper">
            <div class="dt-toolbar flex flex-wrap items-center">
                <div class="dataTables_length">
                    <label>Show
                        <select x-model.number="perPage" @change="page = 1">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        entries
                    </label>
                </div>
                <div class="inline-flex flex-wrap gap-1 rounded-xl bg-gray-100 p-1 dark:bg-white/5">
                    <template x-for="s in ['', ...Object.keys(sites)]" :key="s">
                        <button type="button" @click="site = s; floor = ''; page = 1"
                            class="rounded-lg px-3.5 py-1.5 text-sm font-semibold transition"
                            :class="site === s ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:bg-white dark:text-gray-300 dark:hover:bg-white/10'"
                            x-text="s || 'All sites'"></button>
                    </template>
                </div>
                <select x-model="floor" @change="page = 1" class="w-44" aria-label="Floor">
                    <option value="">All floors</option>
                    <template x-for="f in floors" :key="f">
                        <option :value="f" x-text="f"></option>
                    </template>
                </select>
                <div class="dataTables_filter">
                    <label><input type="search" x-model="search" @input="page = 1" @keydown.escape="search = ''; page = 1" placeholder="Search..." aria-label="Search tenant, unit or name"></label>
                </div>
            </div>
            </div>

            {{-- Select across pages --}}
            <div x-show="!loading && selectable.length" class="flex flex-wrap items-center gap-x-3 gap-y-1 border-b border-blue-100 bg-blue-50/60 px-5 py-2 text-sm text-gray-600 dark:border-blue-500/20 dark:bg-blue-500/10 dark:text-gray-300">
                <span><b x-text="selectable.length"></b> row(s) in this list are ready to add.</span>
                <button type="button" x-show="!allSelected" @click="select(selectable, true)" class="font-semibold text-blue-600 hover:underline dark:text-blue-300" x-text="`Select all ${selectable.length}`"></button>
                <button type="button" x-show="allSelected" @click="select(selectable, false)" class="font-semibold text-blue-600 hover:underline dark:text-blue-300">Unselect all</button>
            </div>

            <div class="overflow-x-auto" x-show="show !== 'similar' && filtered.length">
                <table class="w-full min-w-[1000px] table-fixed text-sm">
                    <colgroup>
                        <col style="width: 56px">
                        <col style="width: 24%">
                        <col style="width: 23%">
                        <col style="width: 27%">
                        <col>
                    </colgroup>
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                            <th class="px-5 py-3">
                                <input type="checkbox" class="h-4 w-4 cursor-pointer rounded disabled:cursor-not-allowed disabled:opacity-40" :checked="pageAllSelected" :disabled="!pageSelectable.length"
                                    @change="select(pageSelectable, $event.target.checked)" title="Select the ready rows on this page">
                            </th>
                            <th class="px-3 py-3 font-semibold">Tenant</th>
                            <th class="px-3 py-3 font-semibold">Location</th>
                            <th class="px-3 py-3 font-semibold">Sub Location name</th>
                            <th class="px-3 py-3 pr-5 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="r in (show === 'similar' ? [] : paged)" :key="r.key">
                            <tr @click="rowClick(r, $event)" class="border-b border-gray-100 align-top transition-colors last:border-0 dark:border-white/5"
                                :class="r.selected ? 'bg-blue-50 dark:bg-blue-500/10' : (r.status === 'new' && r.location_id ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5' : '')">
                                <td class="px-5 py-4">
                                    <template x-if="r.status === 'new'">
                                        <input type="checkbox" class="h-4 w-4 cursor-pointer rounded disabled:cursor-not-allowed disabled:opacity-40" x-model="r.selected" :disabled="!r.location_id"
                                            :title="r.location_id ? 'Select' : 'Choose a Location first'">
                                    </template>
                                    <i x-show="r.status === 'exists'" class="fa-solid fa-check text-emerald-500"></i>
                                    <i x-show="r.status === 'similar'" class="fa-solid fa-triangle-exclamation text-amber-500"></i>
                                </td>
                                <td class="px-3 py-3">
                                    <p class="break-words font-semibold text-gray-800 dark:text-gray-100" x-text="r.store"></p>
                                    <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="rounded bg-blue-50 px-1.5 py-0.5 font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300" x-text="r.site"></span>
                                        <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium dark:bg-white/10" x-text="r.floor || 'No floor'"></span>
                                        <span class="break-all" x-show="r.unit" x-text="`Unit ${r.unit}`"></span>
                                    </p>
                                </td>

                                {{-- Missing: editable --}}
                                <td x-show="r.status !== 'similar'" class="px-3 py-3">
                                    <template x-if="r.status === 'new'">
                                        <div>
                                            <select x-model="r.location_id" @change="if (!r.location_id) r.selected = false" class="w-full" aria-label="Location">
                                                <option value="">Choose a Location…</option>
                                                <template x-for="l in sites[r.site]" :key="l.id">
                                                    <option :value="l.id" :selected="l.id === r.location_id" x-text="l.name"></option>
                                                </template>
                                            </select>
                                            <p x-show="!r.location_id" class="mt-1 text-xs text-amber-600 dark:text-amber-300">Needed before you can add it.</p>
                                        </div>
                                    </template>
                                    {{-- Existing: read only --}}
                                    <template x-for="e in r.existing" :key="e.id">
                                        <p class="mb-1 min-h-[2.5rem] py-2 text-gray-600 dark:text-gray-300" x-text="e.location"></p>
                                    </template>
                                </td>
                                <td x-show="r.status !== 'similar'" class="px-3 py-3">
                                    <template x-if="r.status === 'new'">
                                        <div class="relative">
                                            <input type="text" x-model="r.name" maxlength="255" class="w-full uppercase" style="padding-right: 2.25rem" aria-label="Sub Location name">
                                            <button type="button" x-show="r.name !== r.suggested" @click="r.name = r.suggested" title="Back to the suggested name"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-gray-400 hover:text-blue-600"><i class="fa-solid fa-rotate-left text-xs"></i></button>
                                        </div>
                                    </template>
                                    <template x-for="e in r.existing" :key="e.id">
                                        <p class="mb-1 min-h-[2.5rem] py-2 text-gray-800 dark:text-gray-100">
                                            <span class="break-words font-medium" x-text="e.label"></span>
                                            <span class="ml-1 whitespace-nowrap text-xs text-gray-400" x-text="e.id"></span>
                                        </p>
                                    </template>
                                </td>

                                <td class="px-3 py-3 pr-5 text-xs">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 font-semibold" :class="badge(r).css">
                                        <i class="fa-solid" :class="badge(r).fa"></i><span x-text="badge(r).text"></span>
                                    </span>
                                    <button type="button" x-show="r.linked" @click="unlink(r)" :disabled="r.busy" title="This tenant was linked to the Sub Location by a user"
                                        class="ml-1.5 inline-flex items-center gap-1 rounded-full border border-gray-200 px-2.5 py-1 font-semibold text-gray-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 disabled:opacity-50 dark:border-white/10 dark:text-gray-400 dark:hover:bg-red-500/10">
                                        <i class="fa-solid fa-link-slash"></i> Unlink
                                    </button>
                                    <button type="button" x-show="r.status === 'similar'" @click="review(r)" class="ml-1.5 font-semibold text-blue-600 hover:underline dark:text-blue-300">Review</button>
                                </td>

                                <td x-show="r.status === 'similar'" colspan="2" class="px-3 py-3 text-gray-600 dark:text-gray-300">
                                    <template x-if="r.status === 'similar'">
                                        <p>Looks like <b class="text-gray-800 dark:text-gray-100" x-text="r.similar[0].label"></b>
                                            <span class="text-xs text-gray-400" x-text="r.similar[0].location + (r.similar.length > 1 ? ` · +${r.similar.length - 1} more` : '')"></span></p>
                                    </template>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Need review: one block per tenant, one line per possible match, one Action button per tenant --}}
            <div class="overflow-x-auto" x-show="show === 'similar' && filtered.length">
                <table class="w-full min-w-[1000px] table-fixed text-sm">
                    <colgroup>
                        <col style="width: 25%">
                        <col style="width: 25%">
                        <col style="width: 25%">
                        <col>
                        <col style="width: 140px">
                    </colgroup>
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50 text-left text-[11px] uppercase tracking-wider text-gray-500 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                            <th class="px-5 py-3 font-semibold">Tenant</th>
                            <th class="px-3 py-3 font-semibold">Location</th>
                            <th class="px-3 py-3 font-semibold">Sub Location</th>
                            <th class="px-3 py-3 font-semibold">Status</th>
                            <th class="px-3 py-3 pr-5 font-semibold">Action</th>
                        </tr>
                    </thead>
                    <template x-for="r in (show === 'similar' ? paged : [])" :key="r.key">
                        <tbody class="border-b border-gray-100 last:border-0 hover:bg-gray-50 dark:border-white/5 dark:hover:bg-white/5">
                            <template x-for="(s, i) in (r.similar || [])" :key="i">
                                <tr>
                                    <template x-if="i === 0">
                                        <td class="px-5 py-3 align-middle" :rowspan="r.similar.length">
                                            <p class="break-words font-semibold text-gray-800 dark:text-gray-100" x-text="r.store"></p>
                                            <p class="mt-1 flex flex-wrap items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
                                                <span class="rounded bg-blue-50 px-1.5 py-0.5 font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300" x-text="r.site"></span>
                                                <span class="rounded bg-gray-100 px-1.5 py-0.5 font-medium dark:bg-white/10" x-text="r.floor || 'No floor'"></span>
                                                <span class="break-all" x-show="r.unit" x-text="`Unit ${r.unit}`"></span>
                                            </p>
                                        </td>
                                    </template>
                                    <td class="break-words px-3 py-3 align-middle text-gray-600 dark:text-gray-300" x-text="s.location"></td>
                                    <td class="px-3 py-3 align-middle">
                                        <p class="break-words font-semibold text-gray-800 dark:text-gray-100" x-text="s.label"></p>
                                        <p class="text-xs text-gray-400" x-text="s.id"></p>
                                    </td>
                                    <td class="px-3 py-3 align-middle">
                                        <span class="inline-block rounded-full px-2.5 py-1 text-xs font-semibold" :class="reasonCss(s)" x-text="s.reason"></span>
                                        <span x-show="!s.active" class="ml-1 inline-block rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-300">Inactive</span>
                                    </td>
                                    <template x-if="i === 0">
                                        <td class="px-3 py-3 pr-5 align-middle" :rowspan="r.similar.length">
                                            {{-- same "Action" button as the other lists --}}
                                            <button type="button" @click.stop="openMenu(r, $el)" :disabled="r.busy" aria-haspopup="menu" :aria-label="`Action for ${r.store}`"
                                                class="inline-flex h-8 items-center gap-1 rounded-md border border-blue-100 bg-blue-50 px-3 text-sm font-medium text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 disabled:opacity-50 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                                Action <span>▾</span>
                                            </button>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </template>
                </table>
            </div>

            <div x-show="loading && !rows.length" class="animate-pulse space-y-3 px-5 py-4">
                <template x-for="i in 6" :key="i">
                    <div class="h-12 rounded-lg bg-gray-100 dark:bg-white/5"></div>
                </template>
            </div>
            <div x-show="!loading && !filtered.length" class="px-5 py-12 text-center">
                <i class="fa-solid fa-inbox mb-2 text-2xl text-gray-300 dark:text-gray-600"></i>
                <p class="text-sm text-gray-500 dark:text-gray-400">No tenant matches these filters.</p>
                <button type="button" @click="resetFilters()" class="mt-2 text-sm font-semibold text-blue-600 hover:underline dark:text-blue-300">Clear filters</button>
            </div>

            {{-- Pagination --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 px-5 py-3 text-sm text-gray-500 dark:border-white/10 dark:text-gray-400">
                <span x-text="filtered.length ? `Showing ${(page - 1) * perPage + 1} to ${Math.min(page * perPage, filtered.length)} of ${filtered.length} entries` : 'Showing 0 entries'"></span>
                <div class="flex items-center gap-1.5">
                    <template x-for="b in pager" :key="b.label">
                        <button type="button" @click="goTo(b.to)" :disabled="b.off" :title="b.label"
                            class="flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 transition hover:bg-gray-50 disabled:opacity-40 dark:border-white/10 dark:hover:bg-white/5">
                            <i class="fa-solid text-xs" :class="b.fa"></i>
                        </button>
                    </template>
                    <span class="ml-2 font-medium" x-text="`Page ${page} of ${pages}`"></span>
                </div>
            </div>
        </div>

        {{-- Action menu: one for the whole list, fixed so the table cannot clip it --}}
        <div x-show="menu" style="display: none" :style="menu?.style" role="menu"
            @click.outside="menu = null" @keydown.escape.window="menu = null" @scroll.window.capture="menu = null" @resize.window="menu = null"
            class="fixed z-[9999] w-80 max-w-[calc(100vw-1rem)] overflow-hidden rounded-md border border-gray-200 bg-white py-1 shadow-lg dark:border-gray-700 dark:bg-gray-800">
            <template x-for="a in (menu ? actions(menu.row) : [])" :key="a.key">
                <button type="button" role="menuitem" @click="act(a)" :disabled="a.disabled"
                    class="flex w-full items-start gap-3 px-4 py-2 text-left text-sm hover:bg-gray-100 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-gray-700"
                    :class="{ 'border-t border-gray-100 dark:border-gray-700': a.type === 'add' }">
                    <i class="fa-solid mt-1 w-4 shrink-0 text-center text-xs" :class="a.icon"></i>
                    <span class="min-w-0">
                        <span class="block font-medium text-gray-800 dark:text-gray-100" x-text="a.title"></span>
                        <span class="block break-words text-xs text-gray-500 dark:text-gray-400" x-text="a.sub"></span>
                    </span>
                </button>
            </template>
        </div>

        {{-- Action bar --}}
        <div x-show="selectedRows.length" x-transition style="display: none"
            class="fixed bottom-5 left-1/2 z-40 flex w-max max-w-[calc(100vw-2rem)] -translate-x-1/2 items-center gap-4 rounded-2xl border border-gray-200 bg-white py-2.5 pl-5 pr-2.5 shadow-2xl dark:border-white/10 dark:bg-gray-800">
            <span class="whitespace-nowrap text-sm text-gray-700 dark:text-gray-200"><b x-text="selectedRows.length"></b> selected</span>
            <button type="button" @click="select(rows, false)" class="text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">Clear</button>
            <button type="button" @click="save()" :disabled="saving"
                class="inline-flex h-10 items-center gap-2 whitespace-nowrap rounded-xl bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500 disabled:opacity-50">
                <i class="fa-solid" :class="saving ? 'fa-spinner fa-spin' : 'fa-plus'"></i>
                <span x-text="saving ? 'Saving…' : 'Add Sub Location'"></span>
            </button>
        </div>
    </div>

    <script>
        function fromTenant() {
            return {
                rows: [],
                sites: {},
                site: '',
                floor: '',
                show: 'new',
                search: '',
                page: 1,
                perPage: 10,
                loading: true,
                saving: false,
                menu: null, // open "Action" menu: { row, style }
                tabs: [
                    { key: 'new', label: 'Ready to add', hint: 'Nothing similar found', fa: 'fa-plus', icon: 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-300', on: 'border-blue-500 bg-blue-50/60 ring-2 ring-blue-100 dark:bg-blue-500/10 dark:ring-blue-500/20' },
                    { key: 'similar', label: 'Need review', hint: 'Use, rename, or add new', fa: 'fa-triangle-exclamation', icon: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300', on: 'border-amber-400 bg-amber-50/60 ring-2 ring-amber-100 dark:bg-amber-500/10 dark:ring-amber-500/20' },
                    { key: 'exists', label: 'Already exists', hint: 'View only', fa: 'fa-check', icon: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300', on: 'border-emerald-500 bg-emerald-50/60 ring-2 ring-emerald-100 dark:bg-emerald-500/10 dark:ring-emerald-500/20' },
                    { key: '', label: 'All tenants', hint: 'Every active tenant', fa: 'fa-list', icon: 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300', on: 'border-gray-400 bg-gray-50 ring-2 ring-gray-100 dark:bg-white/5 dark:ring-white/10' },
                ],

                // rows of the chosen site / floor / search, before the status card
                get scoped() {
                    const q = this.search.trim().toLowerCase();

                    return this.rows.filter(r => (!this.site || r.site === this.site)
                        && (!this.floor || r.floor === this.floor)
                        && (!q || [r.store, r.unit, r.name, ...r.existing.map(e => e.label)].some(v => (v || '').toLowerCase().includes(q))));
                },
                get filtered() { return this.scoped.filter(r => !this.show || r.status === this.show); },
                get floors() {
                    return [...new Set(this.rows.filter(r => !this.site || r.site === this.site).map(r => r.floor).filter(Boolean))]
                        .sort((a, b) => a.localeCompare(b, undefined, { numeric: true }));
                },
                get pages() { return Math.max(1, Math.ceil(this.filtered.length / this.perPage)); },
                get paged() { return this.filtered.slice((this.page - 1) * this.perPage, this.page * this.perPage); },
                get pager() {
                    const first = this.page <= 1, last = this.page >= this.pages;

                    return [
                        { label: 'First page', fa: 'fa-angles-left', to: 1, off: first },
                        { label: 'Previous page', fa: 'fa-angle-left', to: this.page - 1, off: first },
                        { label: 'Next page', fa: 'fa-angle-right', to: this.page + 1, off: last },
                        { label: 'Last page', fa: 'fa-angles-right', to: this.pages, off: last },
                    ];
                },
                get selectedRows() { return this.rows.filter(r => r.selected); },
                // bulk selection never includes the rows that look like duplicates
                get selectable() { return this.filtered.filter(r => r.status === 'new' && r.location_id); },
                get pageSelectable() { return this.paged.filter(r => r.status === 'new' && r.location_id); },
                get allSelected() { return this.selectable.length > 0 && this.selectable.every(r => r.selected); },
                get pageAllSelected() { return this.pageSelectable.length > 0 && this.pageSelectable.every(r => r.selected); },

                count(status) { return this.scoped.filter(r => !status || r.status === status).length; },
                select(list, on) { list.forEach(r => r.selected = on); },
                setShow(key) { this.show = key; this.page = 1; },
                goTo(page) { this.page = Math.min(Math.max(page, 1), this.pages); },
                resetFilters() { this.site = ''; this.floor = ''; this.search = ''; this.show = ''; this.page = 1; },
                rowClick(r, event) {
                    if (event.target.closest('input, select, button, a') || window.getSelection().toString()) return;
                    // rows with a similar name need an explicit choice
                    if (r.status === 'new' && r.location_id) r.selected = !r.selected;
                },

                review(r) { this.search = r.store; this.setShow('similar'); },
                openMenu(r, button) {
                    if (this.menu?.row === r) { this.menu = null; return; }

                    const box = button.getBoundingClientRect();
                    // The button is in the last column: line the right edges up so the menu opens leftwards, inside the page.
                    // (clientWidth excludes the scrollbar, innerWidth does not.)
                    const right = Math.max(8, document.documentElement.clientWidth - box.right);
                    // open upwards when there is no room below
                    const up = window.innerHeight - box.bottom < this.actions(r).length * 56 + 16;

                    // an object, so the binding does not overwrite the "display" that x-show sets
                    this.menu = { row: r, style: { right: `${right}px`, ...(up ? { bottom: `${window.innerHeight - box.top + 4}px` } : { top: `${box.bottom + 4}px` }) } };
                },
                // choices of a "Need review" row, each naming the Sub Location and its floor
                actions(r) {
                    const list = [];
                    r.similar.forEach((s, i) => {
                        const sub = `${s.label} · ${s.location}`;
                        list.push({ key: `use${i}`, type: 'use', s, title: s.active ? 'Use this' : 'Use this (inactive)', sub, icon: 'fa-check text-emerald-600', disabled: !s.active });
                        // nothing to rename when the name is already the same
                        if (!s.reason.startsWith('Same name')) list.push({ key: `rename${i}`, type: 'rename', s, title: 'Rename', sub, icon: 'fa-pen text-gray-500', disabled: false });
                        if (s.move) list.push({
                            key: `move${i}`, type: 'move', s, title: 'Change location', icon: 'fa-right-left text-amber-600', disabled: s.move.taken || !s.active,
                            sub: s.move.taken ? 'Another tenant uses it there. Use "Add new" instead.' : sub,
                        });
                    });
                    list.push({ key: 'add', type: 'add', title: 'Add new', sub: 'Not the same place: create a new Sub Location', icon: 'fa-plus text-blue-600', disabled: false });

                    return list;
                },
                act(a) {
                    const r = this.menu.row;
                    this.menu = null;

                    if (a.type === 'add') this.addNew(r);
                    if (a.type === 'use') this.useExisting(r, a.s);
                    if (a.type === 'rename') this.renameExisting(r, a.s);
                    if (a.type === 'move') this.changeLocation(r, a.s);
                },
                // text safe inside popup HTML, also in attributes
                safe(v) { return $('<div>').text(v).html().replace(/"/g, '&quot;'); },
                fieldCss: 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800',
                labelCss: 'mt-3 mb-1 block text-left text-xs font-semibold uppercase text-gray-500',
                // The user picks the Location: nothing is preselected, the one of the tenant's floor is only listed first.
                locationOptions(r, exceptId = null) {
                    const all = (this.sites[r.site] || []).filter(l => l.id !== exceptId);

                    return '<option value="">Choose a Location…</option>' + [...all.filter(l => l.id === r.location_id), ...all.filter(l => l.id !== r.location_id)]
                        .map(l => `<option value="${this.safe(l.id)}">${this.safe(l.name)}${l.id === r.location_id ? ' (tenant floor)' : ''}</option>`).join('');
                },
                changeLocation(r, s) {
                    Swal.fire({
                        title: 'Change location',
                        html: `<p style="font-size:13px;color:#6b7280"><b style="color:#1f2937">${this.safe(s.label)}</b> · ${this.safe(s.id)}<br>
                                now in ${this.safe(s.location)}<br>Tenant <b>${this.safe(r.store)}</b> is on ${this.safe(r.floor || 'no floor')}</p>
                            <label class="${this.labelCss}" for="move-location">New Location</label>
                            <select id="move-location" class="${this.fieldCss}">${this.locationOptions(r, s.location_id)}</select>
                            <p style="margin-top:12px;font-size:12px;color:#b45309;text-align:left">The record moves out of its current Location and keeps its ID. Existing documents keep the old Location.
                                If the store is on both floors, cancel and use "Add new".</p>`,
                        showCancelButton: true,
                        confirmButtonText: 'Move and use',
                        confirmButtonColor: '#2563eb',
                        preConfirm: () => {
                            const location_id = $('#move-location').val();
                            if (!location_id) {
                                Swal.showValidationMessage('Choose the new Location.');
                                return false;
                            }

                            return { location_id };
                        },
                    }).then((result) => {
                        if (result.isConfirmed) this.resolve(r, 'POST', { sub_location_id: s.id, ...result.value }, 'Location changed');
                    });
                },
                addNew(r) {
                    Swal.fire({
                        title: 'Add new Sub Location',
                        html: `<p style="font-size:13px;color:#6b7280">For tenant <b>${this.safe(r.store)}</b> · ${this.safe(r.floor || 'No floor')}</p>
                            <label class="${this.labelCss}" for="new-sub-location">Location</label>
                            <select id="new-sub-location" class="${this.fieldCss}">${this.locationOptions(r)}</select>
                            <label class="${this.labelCss}" for="new-sub-name">Sub Location name</label>
                            <input id="new-sub-name" maxlength="255" class="${this.fieldCss} uppercase" value="${this.safe(r.name)}">`,
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        confirmButtonColor: '#2563eb',
                        preConfirm: () => {
                            const location_id = $('#new-sub-location').val();
                            const name = $('#new-sub-name').val().trim();
                            if (!location_id || !name) {
                                Swal.showValidationMessage('Choose a Location and fill the name.');
                                return false;
                            }

                            return { location_id, name };
                        },
                    }).then((result) => {
                        if (result.isConfirmed) this.add([{ site: r.site, ...result.value }]);
                    });
                },
                reasonCss(s) {
                    return s.reason.startsWith('Same name')
                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300'
                        : 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300';
                },
                useExisting(r, s) {
                    this.resolve(r, 'POST', { sub_location_id: s.id }, `${r.store} now uses ${s.label}`);
                },
                renameExisting(r, s) {
                    const safe = (v) => $('<div>').text(v).html();

                    Swal.fire({
                        title: 'Rename existing Sub Location',
                        html: `<b>${safe(s.label)}</b><br><span style="color:#6b7280;font-size:13px">${safe(s.location)}<br>Work Orders and other documents that use it will show the new name.</span>`,
                        input: 'text',
                        inputValue: r.name.trim().toUpperCase(),
                        showCancelButton: true,
                        confirmButtonText: 'Rename and use',
                        confirmButtonColor: '#2563eb',
                        inputValidator: (v) => v.trim() ? null : 'Please fill the new name.',
                    }).then((result) => {
                        if (result.isConfirmed) this.resolve(r, 'POST', { sub_location_id: s.id, name: result.value.trim() }, 'Sub Location renamed');
                    });
                },
                unlink(r) {
                    this.resolve(r, 'DELETE', {}, 'Link removed');
                },
                resolve(r, method, data, message) {
                    r.busy = true;
                    $.ajax({
                        url: '{{ route('sub_locations.from_tenant.link') }}',
                        method,
                        contentType: 'application/json',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: JSON.stringify({ site: r.site, tenant_key: r.tenant_key, floor_code: r.floor_code, ...data }),
                    })
                        .done(() => {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: message, timer: 2500, showConfirmButton: false });
                            this.load();
                        })
                        .fail((xhr) => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save.' }))
                        .always(() => r.busy = false);
                },
                badge(r) {
                    if (r.status === 'new') return { text: 'Ready to add', fa: 'fa-plus', css: 'bg-blue-50 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300' };
                    if (r.status === 'similar') return { text: 'Check similar', fa: 'fa-triangle-exclamation', css: 'bg-amber-100 text-amber-800 dark:bg-amber-500/20 dark:text-amber-300' };
                    if (!r.existing.some(e => e.active)) return { text: 'Exists (inactive)', fa: 'fa-ban', css: 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-gray-300' };

                    return { text: 'Already exists', fa: 'fa-check', css: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' };
                },

                load() {
                    this.loading = true;
                    $.get('{{ route('sub_locations.from_tenant.json') }}')
                        .done((res) => {
                            // keep the edits and ticks of rows that are still missing
                            const before = Object.fromEntries(this.rows.map(r => [r.key, r]));
                            this.sites = res.sites;
                            this.rows = res.rows.map(r => {
                                const old = r.status === 'exists' ? null : before[r.key];

                                return {
                                    ...r,
                                    suggested: r.name,
                                    name: old ? old.name : r.name,
                                    location_id: old ? old.location_id : r.location_id,
                                    selected: old ? old.selected : false,
                                    busy: false,
                                };
                            });
                            this.goTo(this.page);
                        })
                        .fail((xhr) => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to load the tenants.' }))
                        .always(() => this.loading = false);
                },

                save() {
                    const picked = this.selectedRows;
                    if (picked.some(r => !r.name.trim())) {
                        Swal.fire({ icon: 'warning', title: 'A selected row has an empty name.' });
                        return;
                    }

                    const safe = (s) => $('<div>').text(s).html();
                    const list = (items) => `<div style="max-height:220px;overflow:auto;text-align:left;font-size:13px">${items.map(i => `<div style="padding:4px 0;border-bottom:1px solid #f1f5f9">${i}</div>`).join('')}</div>`;

                    Swal.fire({
                        icon: 'question',
                        title: `Add ${picked.length} Sub Location(s)?`,
                        html: list(picked.map(r => `<b>${safe(r.name.trim().toUpperCase())}</b><br><span style="color:#6b7280">${safe((this.sites[r.site] || []).find(l => l.id === r.location_id)?.name || '')}</span>`)),
                        showCancelButton: true,
                        confirmButtonText: 'Add',
                        confirmButtonColor: '#2563eb',
                    }).then((result) => {
                        if (result.isConfirmed) this.add(picked.map(r => ({ site: r.site, location_id: r.location_id, name: r.name.trim() })));
                    });
                },
                add(items) {
                    const safe = (s) => $('<div>').text(s).html();

                    this.saving = true;
                    $.ajax({
                        url: '{{ route('sub_locations.from_tenant.store') }}',
                        method: 'POST',
                        contentType: 'application/json',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: JSON.stringify({ items }),
                    })
                        .done((res) => {
                            Swal.fire({
                                icon: res.added ? 'success' : 'info',
                                title: `${res.added} Sub Location(s) added`,
                                // the new IDs first, then anything that was not added
                                html: `<div style="max-height:260px;overflow:auto;text-align:left;font-size:13px">${[
                                    ...res.created.map(safe),
                                    ...(res.skipped.length ? ['<b>Skipped:</b>', ...res.skipped.map(safe)] : []),
                                ].join('<br>')}</div>`,
                            });
                            this.load();
                        })
                        .fail((xhr) => Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Failed to save.' }))
                        .always(() => this.saving = false);
                },
            };
        }
    </script>
</x-app-layout>
