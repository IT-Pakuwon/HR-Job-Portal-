{{--
    My Message / My Document.
    Injected as two extra tabs into each dashboard's tab bar (see the script at the bottom);
    on a dashboard without a tab bar the panels simply stay visible under the dashboard.
--}}
@php
    $inputCls = 'w-40 rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-indigo-500 focus:outline-none dark:border-slate-600 dark:bg-slate-700 dark:text-slate-200';
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
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">My Document</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Documents you created and their status</p>
                </div>
                <div class="flex items-center gap-2">
                    <select x-model="docType" @change="loadDocs(1)" class="{{ $inputCls }}">
                        <option value="">All types</option>
                        <template x-for="t in docTypes" :key="t"><option :value="t" x-text="t"></option></template>
                    </select>
                    <select x-model="docStatus" @change="loadDocs(1)" class="{{ $inputCls }}">
                        <option value="">All status</option>
                        <template x-for="st in docStatuses" :key="st.code"><option :value="st.code" x-text="st.label"></option></template>
                    </select>
                    <input type="search" x-model="docQ" @input.debounce.400ms="loadDocs(1)" placeholder="Search…" class="{{ $inputCls }}">
                </div>
            </div>

            <div class="border-t border-slate-200 dark:border-slate-700">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2">Document No</th>
                            <th class="px-2 py-2">Type</th>
                            <th class="px-2 py-2">Status</th>
                            <th class="px-4 py-2">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        <template x-for="d in docs" :key="d.docid">
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/50">
                                <td class="px-4 py-2">
                                    <a :href="d.href" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400" x-text="d.docid"></a>
                                    <div class="max-w-[16rem] truncate text-[11px] text-slate-500 dark:text-slate-400" x-text="d.info" x-show="d.info"></div>
                                </td>
                                <td class="px-2 py-2">
                                    <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold uppercase text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400" x-text="d.type"></span>
                                </td>
                                <td class="px-2 py-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold" :class="statusClass(d.code)" x-text="d.status"></span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-slate-500 dark:text-slate-400" x-text="fmt(d.date)"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div x-show="docsLoading" class="p-4 text-center text-xs text-slate-400">Loading…</div>
                <div x-show="!docsLoading && !docs.length" class="py-6 text-center text-sm text-slate-400">No documents found.</div>
            </div>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                <span x-text="docPg.total ? 'Showing ' + docPg.from + ' to ' + docPg.to + ' of ' + docPg.total + ' entries' : 'Showing 0 to 0 of 0 entries'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="loadDocs(docPg.page - 1)" :disabled="docPg.page <= 1"
                        class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Previous</button>
                    <span x-text="docPg.page + ' / ' + docPg.last_page"></span>
                    <button type="button" @click="loadDocs(docPg.page + 1)" :disabled="docPg.page >= docPg.last_page"
                        class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Next</button>
                </div>
            </div>
        </div>

        {{-- My Message --}}
        <div x-show="mode === 'msg' || mode === 'both'"
            :class="embedded ? '' : 'rounded-lg border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-800'">
            <div class="flex flex-wrap items-center justify-between gap-2 px-4 py-2">
                <div>
                    <h2 class="text-sm font-bold text-slate-900 dark:text-white">My Message</h2>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Comments you posted on any document</p>
                </div>
                <input type="search" x-model="msgQ" @input.debounce.400ms="loadMsgs(1)" placeholder="Search…" class="{{ $inputCls }}">
            </div>

            <ul class="divide-y divide-slate-100 border-t border-slate-200 dark:divide-slate-700 dark:border-slate-700">
                <template x-for="m in msgs" :key="m.id">
                    <li class="px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700/50">
                        <div class="flex items-center gap-2 text-[11px]">
                            <template x-if="m.href">
                                <a :href="m.href" class="font-semibold text-indigo-600 hover:underline dark:text-indigo-400" x-text="m.docid"></a>
                            </template>
                            <template x-if="!m.href">
                                <span class="font-semibold text-slate-700 dark:text-slate-200" x-text="m.docid"></span>
                            </template>
                            <span class="rounded-full bg-indigo-100 px-2 py-0.5 text-[10px] font-bold uppercase text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400" x-text="m.type"></span>
                            <span x-show="m.private" class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Private</span>
                            <span class="ml-auto whitespace-nowrap text-slate-400" x-text="fmt(m.date)"></span>
                        </div>
                        <p class="mt-1 whitespace-pre-line break-words text-xs text-slate-700 dark:text-slate-300" x-text="m.text"></p>
                    </li>
                </template>
                <li x-show="msgsLoading" class="p-4 text-center text-xs text-slate-400">Loading…</li>
                <li x-show="!msgsLoading && !msgs.length" class="py-6 text-center text-sm text-slate-400">No messages found.</li>
            </ul>
            <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-200 px-4 py-2 text-xs text-slate-500 dark:border-slate-700 dark:text-slate-400">
                <span x-text="msgPg.total ? 'Showing ' + msgPg.from + ' to ' + msgPg.to + ' of ' + msgPg.total + ' entries' : 'Showing 0 to 0 of 0 entries'"></span>
                <div class="flex items-center gap-2">
                    <button type="button" @click="loadMsgs(msgPg.page - 1)" :disabled="msgPg.page <= 1"
                        class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Previous</button>
                    <span x-text="msgPg.page + ' / ' + msgPg.last_page"></span>
                    <button type="button" @click="loadMsgs(msgPg.page + 1)" :disabled="msgPg.page >= msgPg.last_page"
                        class="rounded border border-slate-300 px-3 py-1.5 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600">Next</button>
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
            mode: 'both', embedded: false, loaded: { doc: false, msg: false },
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
                    const r = await this.get(cfg.documentsUrl, { q: this.docQ, type: this.docType, status: this.docStatus, page });
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
                    const r = await this.get(cfg.messagesUrl, { q: this.msgQ, page });
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
