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
