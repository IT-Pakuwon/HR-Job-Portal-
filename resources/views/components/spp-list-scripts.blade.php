{{-- Shared JS for the SPP list pages: Doc ID cell (button + view + tracking), status badge, header Select2 filters. --}}
<script>
    // base: 'sppbs' | 'sppjs' | 'sppks' | 'sppts'  (used for /show{base}/{eid} and /edit{base}/{eid})
    // noTrack: hide the tracking button; editStatuses: statuses that open the edit page for the owner (default Revise + Draft)
    // opts (optional): { id: value used in /show|/edit URLs (default row.eid), owner: row field holding the owner (default 'created_by'), trackId: value for the tracking button (default = id) }
    window.sppDocCell = function(row, text, base, currentUser, noTrack, editStatuses, opts) {
        opts = opts || {};
        const rid = opts.id !== undefined ? opts.id : row.eid;
        const owner = opts.owner || 'created_by';
        const trackId = opts.trackId !== undefined ? opts.trackId : rid;
        const showUrl = `/show${base}/${rid}`;
        const editUrl = `/edit${base}/${rid}`;

        const viewCls =
            'group/view inline-flex h-8 w-8 items-center justify-center rounded-md border text-xs transition-all duration-200 ' +
            'border-blue-100 bg-blue-50 text-blue-600 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 ' +
            'dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white';

        // Doc ID as a button: icon + number; blue for view, amber (pencil) for editable drafts
        const btnBase =
            'group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border px-3 py-1 ' +
            'text-[13px] font-semibold leading-6 tracking-wide tabular-nums transition-all duration-200 ' +
            'hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-95 ';

        const editCls = btnBase +
            'border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500 hover:text-white ' +
            'dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500 dark:hover:text-white';

        const defaultCls = btnBase +
            'border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-600 hover:bg-blue-600 hover:text-white ' +
            'dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white';

        const tileDefault = 'text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:-rotate-6 group-hover/doc:opacity-100';
        const tileEdit = 'text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:rotate-12 group-hover/doc:opacity-100';

        const trackCls =
            'tracking-btn group/trk inline-flex h-8 w-8 items-center justify-center rounded-md border text-xs transition-all duration-200 ' +
            'border-red-100 bg-red-50 text-red-600 hover:-translate-y-0.5 hover:border-red-600 hover:bg-red-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 ' +
            'dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-600 dark:hover:text-white';

        const trackBtn = noTrack ? '' : `
            <button type="button" class="${trackCls}" data-id="${trackId}" data-doc="${text}" aria-label="Tracking" title="Tracking">
                <i class="fa-solid fa-paper-plane transition-transform duration-300 ease-out group-hover/trk:-translate-y-0.5 group-hover/trk:translate-x-0.5 group-hover/trk:-rotate-12 group-hover/trk:scale-110"></i>
            </button>`;

        // draft / revise owned by the current user -> edit page (+ separate view button)
        if ((editStatuses || ['D', 'H']).includes(row.status) && row[owner] === currentUser) {
            return `
                <div class="flex items-center gap-3 whitespace-nowrap">
                    <a href="${editUrl}" class="${editCls}" title="Edit draft">
                        <span class="${tileEdit}"><i class="fa-solid fa-pen text-[10px]"></i></span>
                        <span>${text}</span>
                    </a>
                    <a href="${showUrl}" target="_blank" class="${viewCls}" title="View">
                        <i class="fa-solid fa-eye transition-transform duration-300 group-hover/view:scale-125"></i>
                    </a>
                    ${trackBtn}
                </div>`;
        }

        return `
            <div class="flex items-center gap-3 whitespace-nowrap">
                <a href="${showUrl}" class="${defaultCls}" title="Open">
                    <span class="${tileDefault}"><i class="fa-regular fa-file-lines"></i></span>
                    <span>${text}</span>
                </a>
                ${trackBtn}
            </div>`;
    };

    // soft pill + dot; colours match the status filter cards
    window.sppStatusBadge = function(code, labels) {
        const map = {
            'D': { t: 'Revise', c: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30', d: 'bg-blue-500' },
            'P': { t: 'On Progress', c: 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30', d: 'bg-yellow-500' },
            'C': { t: 'Completed', c: 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', d: 'bg-green-500' },
            'X': { t: 'Cancel', c: 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-500/30', d: 'bg-rose-500' },
            'R': { t: 'Rejected', c: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30', d: 'bg-red-500' },
            'H': { t: 'Draft', c: 'bg-pink-50 text-pink-700 ring-pink-200 dark:bg-pink-500/10 dark:text-pink-300 dark:ring-pink-500/30', d: 'bg-pink-500' },
        };
        if (labels && labels[code] && map[code]) map[code].t = labels[code];
        const it = map[code] || {
            t: code || '-',
            c: 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10',
            d: 'bg-slate-400'
        };
        return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${it.c}"><span class="h-1.5 w-1.5 rounded-full ${it.d}"></span>${it.t}</span>`;
    };

    // ---- Building blocks for lists with their own row buttons (Ticket, IT Recommendation, Access Request) ----
    // class lists: add the page's own hook class (e.g. "cancel-btn ") in front
    window.listCls = (function() {
        const doc = 'group/doc inline-flex min-w-[140px] items-center gap-2 whitespace-nowrap rounded-md border px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md active:translate-y-0 active:scale-95 ';
        const icon = 'inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md border text-xs transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:hover:text-white [&>i]:transition-transform [&>i]:duration-300 hover:[&>i]:scale-125 ';
        const action = 'inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:hover:text-white ';
        const blue = 'border-blue-100 bg-blue-50 text-blue-700 hover:border-blue-600 hover:bg-blue-600 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600';
        const red = 'border-red-100 bg-red-50 text-red-600 hover:border-red-600 hover:bg-red-600 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300 dark:hover:bg-red-600';
        const amber = 'border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300 dark:hover:bg-amber-500';
        const green = 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-600 hover:bg-emerald-600 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300 dark:hover:bg-emerald-600';
        const slate = 'border-slate-200 bg-slate-50 text-slate-600 hover:border-slate-600 hover:bg-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300 dark:hover:bg-slate-600';
        return {
            doc: doc + 'hover:text-white dark:hover:text-white ' + blue,
            docEdit: doc + 'hover:text-white dark:hover:text-white ' + amber,
            view: icon + blue,
            cancel: icon + red,
            action: { blue: action + blue, amber: action + amber, green: action + green, slate: action + slate }
        };
    })();

    window.listDocText = function(text, edit) {
        return `<span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 ${edit ? 'group-hover/doc:rotate-12' : 'group-hover/doc:-rotate-6'}"><i class="${edit ? 'fa-solid fa-pen text-[10px]' : 'fa-regular fa-file-lines'}"></i></span><span>${text}</span>`;
    };

    // soft pill + dot; tones match the status filter cards
    window.listBadge = function(tone, text, wide) {
        const tones = {
            yellow: ['bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30', 'bg-yellow-500'],
            blue: ['bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30', 'bg-blue-500'],
            sky: ['bg-sky-50 text-sky-700 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-300 dark:ring-sky-500/30', 'bg-sky-500'],
            green: ['bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', 'bg-green-500'],
            red: ['bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30', 'bg-red-500'],
            orange: ['bg-orange-50 text-orange-700 ring-orange-200 dark:bg-orange-500/10 dark:text-orange-300 dark:ring-orange-500/30', 'bg-orange-500'],
            pink: ['bg-pink-50 text-pink-700 ring-pink-200 dark:bg-pink-500/10 dark:text-pink-300 dark:ring-pink-500/30', 'bg-pink-500'],
            purple: ['bg-[#f3e8ff]/60 text-[#7e22ce] ring-[#e9d5ff] dark:bg-[#a855f7]/10 dark:text-[#d8b4fe] dark:ring-[#a855f7]/30', 'bg-[#a855f7]'],
            slate: ['bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', 'bg-slate-400']
        };
        const t = tones[tone] || tones.slate;
        return `<span class="inline-flex ${wide ? 'min-w-[116px]' : ''} items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${t[0]}"><span class="h-1.5 w-1.5 rounded-full ${t[1]}"></span>${text}</span>`;
    };

    // Styled dropdowns (Select2) for the All-mode header filters
    window.sppInitFilters = function() {
        $('#filterStatus, #filterCompany, #filterDepartment, #filterPoType, #filterBudgetStatus').each(function() {
            $(this).select2({
                width: '100%',
                minimumResultsForSearch: (this.id === 'filterStatus' || this.id === 'filterBudgetStatus') ? Infinity : 8,
                dropdownCssClass: 'sppb-s2-dropdown'
            });
        });
    };
</script>
