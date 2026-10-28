/* My Activity: the data side (documents / messages / files lists, filters, paging) of components/multidashboard/my-activity.blade.php.
   A plain file so the panel (components/panel/my-activity.blade.php), which Alpine mounts later from a <template>, can use it:
   inline <script> tags inside a <template> never run. Loaded up front by component/menu.blade.php, or by my-activity.blade.php itself. */
(function () {
    if (window.myActivity) return;

        const MA_ICONS = {
            msg: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a8 8 0 0 1-11.6 7.1L4 20l1-4.6A8 8 0 1 1 21 12z"/></svg>',
            doc: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/></svg>',
            file: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5l-8.6 8.6a5 5 0 0 1-7-7l8.6-8.6a3.4 3.4 0 0 1 5 5l-8.6 8.6a1.7 1.7 0 0 1-2.4-2.4l7.9-7.9"/></svg>',
            user: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
        };

        function myActivity(cfg) {
            return {
                // '' = hidden (a dashboard tab is showing), 'doc' | 'msg' = tab selected,
                // 'both' = no tab bar on this dashboard, so show both panels below it
                mode: 'both', embedded: false, loaded: { doc: false, msg: false, file: false }, perPage: 10,
                docs: [], docTypes: [], docStatuses: [], docQ: '', docType: '', docStatus: '', docsLoading: false,
                msgs: [], msgQ: '', msgsLoading: false, msgScope: 'mine',
                files: [], fileExts: [], fileQ: '', fileExt: '', filesLoading: false,
                docPg: { page: 1, last_page: 1, total: 0, from: 0, to: 0 },
                msgPg: { page: 1, last_page: 1, total: 0, from: 0, to: 0 },
                filePg: { page: 1, last_page: 1, total: 0, from: 0, to: 0 },

                // Count tiles (combined "My Activity" tab only)
                icons: MA_ICONS,
                tiles: [
                    { mode: 'msg', label: 'Messages · Comments you wrote', icon: MA_ICONS.msg },
                    { mode: 'doc', label: 'Documents · Created by you', icon: MA_ICONS.doc },
                    { mode: 'file', label: 'Files · Attached by you', icon: MA_ICONS.file },
                ],

                // Select2 on a filter <select>: Alpine owns the value (x-model), Select2 only draws it.
                // Select2 fires a jQuery-only change, which x-model never hears, so push the value back by hand.
                s2(el, key, listKey, onPick, minSearch) {
                    const $el = $(el);
                    if (!$.fn.select2) return;
                    $el.select2({ width: '100%', minimumResultsForSearch: minSearch, dropdownParent: $el.parent() });
                    $el.on('change', () => {
                        if ($el.val() !== String(this[key] ?? '')) { this[key] = $el.val(); onPick(); }
                    });
                    const redraw = () => this.$nextTick(() => $el.val(String(this[key] ?? '')).trigger('change.select2'));
                    this.$watch(key, redraw);
                    this.$watch(listKey, redraw);
                },

                count(mode) {
                    const pg = { msg: this.msgPg, doc: this.docPg, file: this.filePg }[mode];
                    return this.loaded[mode] ? pg.total : '–';
                },

                init() {
                    // The tab injector below runs first and sets these before Alpine starts
                    const root = document.getElementById('myActivityRoot');
                    if (root && root.dataset.docked === '1') {
                        // right-hand panel: always visible, last-used sub-view (default: documents), all three loaded for the counts
                        this.embedded = true;
                        let last = null;
                        try { last = localStorage.getItem('myActivityTab'); } catch (e) {}
                        this.mode = ['msg', 'doc', 'file'].includes(last) ? last : 'doc';
                        this.loadDocs(); this.loadMsgs(); this.loadFiles();
                    }
                    else if (root && root.dataset.embedded === '1') { this.embedded = true; this.mode = ''; }
                    else { this.loadDocs(); this.loadMsgs(); this.loadFiles(); }
                },

                open(mode) {
                    // The single "My Activity" tab opens the last sub-view (default: documents) and loads all three for the tile counts
                    if (mode === 'activity') {
                        let last = null;
                        try { last = localStorage.getItem('myActivityTab'); } catch (e) {}
                        mode = ['msg', 'doc', 'file'].includes(last) ? last : 'doc';
                        ['doc', 'msg', 'file'].forEach(m => { if (!this.loaded[m]) this[{ doc: 'loadDocs', msg: 'loadMsgs', file: 'loadFiles' }[m]](); });
                    }
                    if (cfg.combined && mode) { try { localStorage.setItem('myActivityTab', mode); } catch (e) {} }
                    this.mode = mode;
                    if (mode === 'doc' && !this.loaded.doc) this.loadDocs();
                    if (mode === 'msg' && !this.loaded.msg) this.loadMsgs();
                    if (mode === 'file' && !this.loaded.file) this.loadFiles();
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

                setMsgScope(scope) {
                    if (this.msgScope === scope) return;
                    this.msgScope = scope;
                    this.msgs = [];
                    this.loadMsgs(1);
                },

                async loadMsgs(page = 1) {
                    this.msgsLoading = true;
                    const scope = this.msgScope;
                    try {
                        const r = await this.get(cfg.messagesUrl, { q: this.msgQ, page, per_page: this.perPage, scope });
                        if (scope !== this.msgScope) return; // user switched tabs while this was loading
                        this.msgs = r.data; this.msgPg = r; this.loaded.msg = true;
                    }
                    catch (e) { this.msgs = []; }
                    this.msgsLoading = false;
                },

                async loadFiles(page = 1) {
                    this.filesLoading = true;
                    try {
                        const r = await this.get(cfg.filesUrl, { q: this.fileQ, ext: this.fileExt, page, per_page: this.perPage });
                        this.files = r.data; this.filePg = r; this.loaded.file = true;
                        // keep the full type list stable while a type filter is active
                        if (!this.fileExt) this.fileExts = r.exts;
                    }
                    catch (e) { this.files = []; }
                    this.filesLoading = false;
                },

                fileKind(ext) {
                    if (ext === 'pdf') return 'pdf';
                    if (['png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp', 'svg'].includes(ext)) return 'img';
                    if (['xls', 'xlsx', 'csv'].includes(ext)) return 'xls';
                    if (['doc', 'docx'].includes(ext)) return 'doc';
                    return 'oth';
                },

                size(b) {
                    b = Number(b);
                    if (!b) return '-';
                    if (b < 1024) return b + ' B';
                    if (b < 1048576) return (b / 1024).toFixed(1) + ' KB';
                    return (b / 1048576).toFixed(1) + ' MB';
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

    window.MA_ICONS = MA_ICONS;
    window.myActivity = myActivity;
})();
