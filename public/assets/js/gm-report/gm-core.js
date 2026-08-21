(function () {
    'use strict';

    // ── Shared filter state — mutated by gm-filter, read by all sections ───────
    window.gmState = {
        cpnyId    : '',
        dateFrom  : '',   // ISO date: YYYY-MM-DD
        dateTo    : '',   // ISO date: YYYY-MM-DD
        depts     : [],
        preset    : 'this-year',
        ticketType: '',   // only used by pages that render the Ticket Type filter (e.g. corp-teknik-report)
    };

    // ── Shared utilities — available to all section scripts ────────────────────
    window.gmUtils = {

        isDark: function () {
            return document.documentElement.classList.contains('dark');
        },

        idr: function (val) {
            if (val === null || val === undefined || isNaN(val)) return '—';
            var v = parseFloat(val), abs = Math.abs(v), s = v < 0 ? '-' : '';
            if (abs >= 1e12) return s + 'Rp ' + (abs / 1e12).toFixed(1).replace('.', ',') + 'T';
            if (abs >= 1e9)  return s + 'Rp ' + (abs / 1e9).toFixed(1).replace('.', ',')  + 'M';
            if (abs >= 1e6)  return s + 'Rp ' + (abs / 1e6).toFixed(1).replace('.', ',')  + 'Jt';
            return s + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(abs));
        },

        setText: function (id, v) {
            var el = document.getElementById(id);
            if (el) el.textContent = v;
        },

        escHtml: function (s) {
            return String(s)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        },

        // Renders a section's "GM Insight" bullet list. insights: array of
        // { type: 'positive'|'warning'|'critical'|'info', text: <safe HTML string> }.
        // Callers are responsible for escHtml-ing any dynamic values (names,
        // labels) before interpolating them into `text` — numbers/percentages
        // formatted via idr()/toLocaleString() are safe as-is.
        renderInsights: function (listId, insights) {
            var el = document.getElementById(listId);
            if (!el) return;

            if (!insights || !insights.length) {
                el.innerHTML = '<li class="text-xs text-slate-400 dark:text-slate-500">Not enough data yet for insights in this period.</li>';
                return;
            }

            var ICONS = {
                positive: { color: '#10B981', path: 'M5 13l4 4L19 7' },
                warning:  { color: '#F59E0B', path: 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z' },
                critical: { color: '#EF4444', path: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
                info:     { color: '#6366F1', path: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
            };

            el.innerHTML = insights.map(function (ins) {
                var ic = ICONS[ins.type] || ICONS.info;
                return '<li class="flex items-start gap-2 text-xs leading-relaxed text-slate-700 dark:text-slate-200">'
                    + '<svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="' + ic.color + '" stroke-width="2">'
                    +   '<path stroke-linecap="round" stroke-linejoin="round" d="' + ic.path + '"/>'
                    + '</svg>'
                    + '<span>' + ins.text + '</span>'
                    + '</li>';
            }).join('');
        },

        // Builds ?date_from=&date_to=&cpny_id=&departments[]= from current gmState
        buildParams: function () {
            var s = window.gmState;
            var parts = [];
            if (s.dateFrom)   parts.push('date_from='   + encodeURIComponent(s.dateFrom));
            if (s.dateTo)     parts.push('date_to='     + encodeURIComponent(s.dateTo));
            if (s.cpnyId)     parts.push('cpny_id='     + encodeURIComponent(s.cpnyId));
            if (s.ticketType) parts.push('ticket_type=' + encodeURIComponent(s.ticketType));
            s.depts.forEach(function (d) { parts.push('departments[]=' + encodeURIComponent(d)); });
            return parts.length ? '?' + parts.join('&') : '';
        },

        // Generic column sort — binds clickable sort headers to a client-side data array.
        // bodyId: the tbody element id (table id is derived as bodyId + '-tbl')
        // getRows / setRows: getter and setter for the full data array
        // resetPage: callback to reset current page to 1
        // renderFn: callback to re-render the table after sorting
        // Returns { reset } to restore unsorted state (call on fresh data load)
        bindTableSort: function (bodyId, getRows, setRows, resetPage, renderFn) {
            var tbl = document.getElementById(bodyId + '-tbl');
            if (!tbl) return { reset: function () {} };

            var ths   = tbl.querySelectorAll('thead th[data-sort-key]');
            var state = { key: null, dir: 1 };

            function resetIcons() {
                ths.forEach(function (h) {
                    var icon = h.querySelector('.sort-icon');
                    if (icon) { icon.textContent = '↕'; icon.className = 'sort-icon ml-0.5 opacity-30'; }
                });
            }

            ths.forEach(function (th) {
                th.addEventListener('click', function () {
                    var key     = th.dataset.sortKey;
                    var numeric = th.dataset.sortNumeric === 'true';
                    state.dir   = (state.key === key) ? state.dir * -1 : 1;
                    state.key   = key;

                    resetIcons();
                    var icon = th.querySelector('.sort-icon');
                    if (icon) {
                        icon.textContent = state.dir === 1 ? '↑' : '↓';
                        icon.className   = 'sort-icon ml-0.5 text-violet-500';
                    }

                    var rows = getRows().slice();
                    rows.sort(function (a, b) {
                        var av = a[key], bv = b[key];
                        if (numeric) { av = parseFloat(av) || 0; bv = parseFloat(bv) || 0; }
                        else { av = String(av || '').toLowerCase(); bv = String(bv || '').toLowerCase(); }
                        return av < bv ? -state.dir : av > bv ? state.dir : 0;
                    });
                    setRows(rows);
                    resetPage();
                    renderFn();
                });
            });

            return {
                reset: function () { state.key = null; state.dir = 1; resetIcons(); },
            };
        },

        // Generic paginator — used by any section with a paginated table.
        renderPagination: function (prefix, total, page, pageSize, onPage) {
            var wrap    = document.getElementById(prefix + 'Pagination');
            var infoEl  = document.getElementById(prefix + 'PageInfo');
            var numsEl  = document.getElementById(prefix + 'PageNums');
            var prevBtn = document.getElementById(prefix + 'Prev');
            var nextBtn = document.getElementById(prefix + 'Next');
            if (!wrap) return;

            var totalPages = Math.ceil(total / pageSize);
            if (totalPages <= 1) { wrap.classList.add('hidden'); return; }
            wrap.classList.remove('hidden');

            var start = (page - 1) * pageSize + 1;
            var end   = Math.min(page * pageSize, total);
            if (infoEl) infoEl.textContent = 'Showing ' + start + '–' + end + ' of ' + total;

            if (prevBtn) {
                prevBtn.disabled = page <= 1;
                prevBtn.onclick  = function () { if (page > 1) onPage(page - 1); };
            }
            if (nextBtn) {
                nextBtn.disabled = page >= totalPages;
                nextBtn.onclick  = function () { if (page < totalPages) onPage(page + 1); };
            }

            if (numsEl) {
                var pages = [], half = 2;
                var lo = Math.max(1, page - half), hi = Math.min(totalPages, page + half);
                if (lo > 1) pages.push(1);
                if (lo > 2) pages.push('…');
                for (var i = lo; i <= hi; i++) pages.push(i);
                if (hi < totalPages - 1) pages.push('…');
                if (hi < totalPages) pages.push(totalPages);

                numsEl.innerHTML = '';
                pages.forEach(function (p) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    if (p === '…') {
                        btn.className   = 'gm-page-btn';
                        btn.textContent = '…';
                        btn.disabled    = true;
                    } else {
                        btn.className   = 'gm-page-btn' + (p === page ? ' active' : '');
                        btn.textContent = p;
                        (function (pg) { btn.onclick = function () { onPage(pg); }; })(p);
                    }
                    numsEl.appendChild(btn);
                });
            }
        },
    };

    // ── Filter event dispatcher ────────────────────────────────────────────────
    // gm-filter calls this whenever state changes.
    // Every section script registers: document.addEventListener('gm:filter', handler)
    window.gmDispatchFilter = function () {
        document.dispatchEvent(new CustomEvent('gm:filter', {
            detail: Object.assign({}, window.gmState),
        }));
    };

})();
