{{--
    My Message / My Document.
    Injected as two extra tabs into each dashboard's tab bar (see the script at the bottom);
    on a dashboard without a tab bar the panels simply stay visible under the dashboard.
    Table, filter bar and pager mirror the dashboards' "Waiting Approval" tab.
--}}
@php
    $selectCls = 'w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white';
    $thCls = 'px-3 py-2 font-semibold';
    $tdCls = 'whitespace-nowrap px-3 py-2 align-top text-slate-600 dark:text-slate-300';
    $pillCls = 'inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600';
    $rowCls = 'border-b border-slate-100 last:border-0 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-700/30';
    $theadCls = 'border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500 dark:border-slate-700 dark:text-slate-400';
    $pagerBtn = 'rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600';
@endphp

<div id="myActivityRoot" :class="embedded ? '' : 'mt-4'"
    x-data="myActivity({ documentsUrl: '{{ route('my-activity.documents') }}', messagesUrl: '{{ route('my-activity.messages') }}' })"
    x-init="init()"
    x-show="mode !== ''"
    @my-activity-open.window="open($event.detail)">

    <div class="grid grid-cols-1 gap-3" :class="mode === 'both' ? 'xl:grid-cols-2' : ''">

        {{-- My Document --}}
        <div x-show="mode === 'doc' || mode === 'both'"
            :class="embedded ? '' : 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800'">

            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="grid grid-cols-2 gap-3 lg:col-span-4">
                        <select x-model="docType" @change="loadDocs(1)" class="{{ $selectCls }}">
                            <option value="">All Doctype</option>
                            <template x-for="t in docTypes" :key="t"><option :value="t" x-text="t"></option></template>
                        </select>
                        <select x-model="docStatus" @change="loadDocs(1)" class="{{ $selectCls }}">
                            <option value="">All Status</option>
                            <template x-for="st in docStatuses" :key="st.code + st.label"><option :value="st.code" x-text="st.label"></option></template>
                        </select>
                    </div>
                    <div class="lg:col-span-4">
                        <input type="text" x-model="docQ" @input.debounce.400ms="loadDocs(1)" placeholder="Search..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="hidden shrink-0 sm:inline">Show</span>
                            <select x-model.number="perPage" @change="loadDocs(1); loadMsgs(1)" class="w-24 {{ $selectCls }}">
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
                    <table class="w-full text-left text-sm">
                        <thead class="{{ $theadCls }}">
                            <tr>
                                <th class="{{ $thCls }}">Doc ID</th>
                                <th class="{{ $thCls }}">Type</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Date</th>
                                <th class="{{ $thCls }}">Desc</th>
                                <th class="{{ $thCls }}">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="d in docs" :key="d.docid">
                                <tr class="{{ $rowCls }}">
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

            <div class="border-b border-slate-200 p-4 dark:border-slate-700">
                <div class="grid gap-3 lg:grid-cols-12">
                    <div class="lg:col-span-8">
                        <input type="text" x-model="msgQ" @input.debounce.400ms="loadMsgs(1)" placeholder="Search..."
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    </div>
                    <div class="lg:col-span-4">
                        <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                            <span class="hidden shrink-0 sm:inline">Show</span>
                            <select x-model.number="perPage" @change="loadDocs(1); loadMsgs(1)" class="w-24 {{ $selectCls }}">
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
                                <th class="{{ $thCls }}">Type</th>
                                <th class="{{ $thCls }}">Company</th>
                                <th class="{{ $thCls }}">Dept</th>
                                <th class="{{ $thCls }}">Date</th>
                                <th class="{{ $thCls }}">Message</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                            <template x-for="m in msgs" :key="m.id">
                                <tr class="{{ $rowCls }}">
                                    <td class="whitespace-nowrap px-3 py-2 align-top">
                                        <a x-show="m.href" :href="m.href" target="_blank" rel="noopener noreferrer" class="{{ $pillCls }}" x-text="m.docid"></a>
                                        <span x-show="!m.href" class="inline-flex items-center rounded-md bg-gray-700 px-2 py-1 text-[11px] font-bold text-white dark:bg-cyan-700" x-text="m.docid"></span>
                                    </td>
                                    <td class="{{ $tdCls }}" x-text="m.type"></td>
                                    <td class="{{ $tdCls }}" x-text="m.company || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="m.dept || '-'"></td>
                                    <td class="{{ $tdCls }}" x-text="fmt(m.date)"></td>
                                    <td class="px-3 py-2 align-top text-slate-600 dark:text-slate-300">
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
    </div>
</div>

<script>
    function myActivity(cfg) {
        return {
            // '' = hidden (a dashboard tab is showing), 'doc' | 'msg' = tab selected,
            // 'both' = no tab bar on this dashboard, so show both panels below it
            mode: 'both', embedded: false, loaded: { doc: false, msg: false }, perPage: 10,
            docs: [], docTypes: [], docStatuses: [], docQ: '', docType: '', docStatus: '', docsLoading: false,
            msgs: [], msgQ: '', msgsLoading: false,
            docPg: { page: 1, last_page: 1, total: 0, from: 0, to: 0 },
            msgPg: { page: 1, last_page: 1, total: 0, from: 0, to: 0 },

            init() {
                // The tab injector below runs first and sets these before Alpine starts
                const root = document.getElementById('myActivityRoot');
                if (root && root.dataset.embedded === '1') { this.embedded = true; this.mode = ''; }
                else { this.loadDocs(); this.loadMsgs(); }
            },

            open(mode) {
                this.mode = mode;
                if (mode === 'doc' && !this.loaded.doc) this.loadDocs();
                if (mode === 'msg' && !this.loaded.msg) this.loadMsgs();
            },

            async get(url, params) {
                const res = await fetch(url + '?' + new URLSearchParams(params), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) throw new Error(res.status);
                return res.json();
            },

            async loadDocs(page = 1) {
                this.docsLoading = true;
                try {
                    const r = await this.get(cfg.documentsUrl, { q: this.docQ, type: this.docType, status: this.docStatus, page, per_page: this.perPage });
                    this.docs = r.data;
                    this.docPg = r;
                    // keep the full type list stable while a type filter is active
                    if (!this.docType) this.docTypes = r.types;
                    if (!this.docStatus) this.docStatuses = r.statuses;
                    this.loaded.doc = true;
                } catch (e) { this.docs = []; }
                this.docsLoading = false;
            },

            async loadMsgs(page = 1) {
                this.msgsLoading = true;
                try {
                    const r = await this.get(cfg.messagesUrl, { q: this.msgQ, page, per_page: this.perPage });
                    this.msgs = r.data; this.msgPg = r; this.loaded.msg = true;
                }
                catch (e) { this.msgs = []; }
                this.msgsLoading = false;
            },

            fmt(v) {
                if (!v) return '-';
                const d = new Date(String(v).replace(' ', 'T'));
                return isNaN(d) ? v : d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            },

            statusClass(code) {
                return ({
                    C: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    F: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    P: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    W: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    I: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                    D: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                    H: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                    R: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                    X: 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                })[code] || 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300';
            },
        };
    }

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
            const first = document.querySelector(TAB_SEL);
            if (!root || !first) return;

            const bar = first.parentElement;
            const header = bar.parentElement;
            const card = header && header.parentElement;
            if (!card || card === document.body) return;

            const mine = [
                { id: 'my-tab-message', mode: 'msg', label: '💬 My Message' },
                { id: 'my-tab-document', mode: 'doc', label: '📄 My Document' },
            ].map(t => {
                const b = document.createElement('button');
                b.type = 'button'; b.id = t.id; b.textContent = t.label; b.className = IDLE;
                b.dataset.mode = t.mode;
                bar.appendChild(b);
                return b;
            });

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
                bar.querySelectorAll(TAB_SEL).forEach(o => { o.className = IDLE; });
                mine.forEach(o => { o.className = o === b ? ACTIVE : IDLE; });
                hideBody();
                emit(b.dataset.mode);
            }));

            // Delegated + capture so it runs before the dashboard's own click handler,
            // which then re-styles its tabs and reloads its data as usual.
            bar.addEventListener('click', e => {
                const t = e.target.closest(TAB_SEL);
                if (!t || mine.includes(t)) return;
                mine.forEach(o => { o.className = IDLE; });
                showBody();
                emit('');
            }, true);
        }

        // Both the dashboard's tab bar and #myActivityRoot are above this script, so run now —
        // that is always before Alpine initialises myActivity() and reads data-embedded.
        setup();
    })();
</script>
