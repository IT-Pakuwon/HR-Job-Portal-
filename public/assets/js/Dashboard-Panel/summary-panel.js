/* Engine of the Summary panels (components/panel/summary-grid.blade.php).
   Fetches {widgets: {id: data}} once per period/refresh and draws every [data-sp-widget] by its data-sp-type.
   To add a widget type: add a function to DRAW. Data shapes are documented in App\Services\Dashboard\SummaryPanels\SummaryData. */
(function () {
    'use strict';

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    var BADGE = {
        good: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-300',
        warn: 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300',
        bad:  'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300',
    };
    var DOT = { red: 'bg-rose-500', amber: 'bg-amber-500', indigo: 'bg-indigo-500' };
    var INSIGHT = { positive: '#10B981', warning: '#F59E0B', critical: '#EF4444', info: '#6366F1' };
    var LABEL = 'text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500';
    var BIG = 'font-extrabold tabular-nums text-slate-900 dark:text-white';

    var DRAW = {
        stat: function (el, d) {
            var arrow = d.trend && d.trend !== 'flat'
                ? '<span class="flex items-center gap-0.5 text-xs font-bold ' + (d.trend === 'up' ? 'text-emerald-500' : 'text-rose-500') + '">' +
                  '<svg class="h-3 w-3 ' + (d.trend === 'down' ? 'rotate-180' : '') + '" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>' + esc(d.delta) + '</span>'
                : '';
            el.innerHTML = '<div class="flex items-end justify-between gap-2"><span class="text-3xl ' + BIG + '">' + esc(d.value) + '</span>' + arrow + '</div>' +
                (d.hint ? '<p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">' + esc(d.hint) + '</p>' : '');
        },

        split: function (el, d) {
            var l = d.left || {}, r = d.right || {}, pct = Math.max(0, Math.min(100, +d.pct || 0));
            var bar = d.pct == null ? '' :
                '<div class="mt-3 border-t border-slate-100 pt-2.5 dark:border-slate-700/60">' +
                  '<div class="mb-1.5 flex items-center justify-between"><span class="' + LABEL + '">' + esc(d.barLabel || 'Progress') + '</span>' +
                  '<span class="text-xs font-extrabold text-slate-700 dark:text-slate-200">' + pct + '%</span></div>' +
                  '<div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60"><div class="h-full rounded-full transition-all duration-700" style="width:' + pct + '%;background:linear-gradient(to right,' + el.dataset.spFrom + ',' + el.dataset.spTo + ')"></div></div>' +
                '</div>';
            var top = (d.top || []).length
                ? '<div class="mt-3 border-t border-slate-100 pt-2.5 dark:border-slate-700/60"><p class="mb-1.5 ' + LABEL + '">' + esc(d.topTitle || 'Biggest spend') + '</p>' +
                  '<ul class="divide-y divide-slate-100 dark:divide-slate-700/60">' + d.top.map(function (t, i) {
                      return '<li class="flex items-center gap-2 py-1.5">' +
                          '<span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-[10px] font-bold text-slate-500 dark:bg-slate-700 dark:text-slate-300">' + (i + 1) + '</span>' +
                          (t.tag ? '<span class="shrink-0 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-bold text-slate-600 dark:bg-slate-700 dark:text-slate-200">' + esc(t.tag) + '</span>' : '') +
                          '<span class="min-w-0 flex-1 truncate text-xs font-semibold text-slate-700 dark:text-slate-200" title="' + esc(t.name) + '">' + esc(t.name) + '</span>' +
                          '<span class="shrink-0 text-xs font-extrabold tabular-nums text-slate-900 dark:text-white">' + esc(t.value) + '</span></li>';
                  }).join('') + '</ul></div>'
                : '';
            el.className += ' flex flex-col'; // so the content can spread over the cell's full height
            el.innerHTML = '<div class="flex flex-1 flex-col justify-between gap-3"><div>' +
                '<div class="flex flex-col items-stretch gap-3 @sm:flex-row @sm:items-start @sm:justify-between @sm:gap-4">' +
                  '<div class="min-w-0"><p class="' + LABEL + '">' + esc(l.label) + '</p><p class="mt-1 bg-clip-text text-2xl font-extrabold tabular-nums text-transparent" style="background-image:linear-gradient(to right,' + el.dataset.spFrom + ',' + el.dataset.spTo + ')">' + esc(l.value) + '</p>' +
                  (l.hint ? '<p class="mt-0.5 text-[10px] text-slate-400">' + esc(l.hint) + '</p>' : '') + '</div>' +
                  '<div class="hidden h-10 w-px self-center @sm:block bg-slate-100 dark:bg-slate-700/60"></div>' +
                  '<div class="min-w-0 @sm:text-right"><p class="' + LABEL + '">' + esc(r.label) + '</p><p class="mt-1 text-xl ' + BIG + '">' + esc(r.value) + '</p></div>' +
                '</div>' + bar + '</div>' + top + '</div>';
        },

        table: function (el, d) {
            // narrow widget: drop every other middle column (e.g. Budget, Remaining) so the rest stays readable
            function hide(i, n) { return n > 3 && i > 0 && i < n - 1 && i % 2 === 1 ? 'hidden @xl:table-cell' : ''; }
            var cols = d.columns || [], rows = d.rows || [];
            if (!rows.length) return empty(el);
            function al(c, i) { return (c && c.align) || (i === 0 ? 'left' : 'right'); }
            var head = cols.map(function (c, i) {
                return '<th class="' + hide(i, cols.length) + ' whitespace-nowrap bg-slate-50 px-4 py-2.5 text-' + al(c, i) + ' text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:bg-slate-800/50 dark:text-slate-500">' + esc(c.label) + '</th>';
            }).join('');
            var body = rows.length ? rows.map(function (row) {
                return '<tr class="text-slate-700 transition-colors hover:bg-slate-50/80 dark:text-slate-200 dark:hover:bg-slate-800/60">' + row.map(function (cell, i) {
                    var inner;
                    if (cell && typeof cell === 'object') {
                        // usage badge → value + a mini meter, so heavy rows stand out at a glance
                        var pct = Math.max(0, Math.min(100, parseFloat(String(cell.text).replace(',', '.')) || 0));
                        var tone = { good: '#10B981', warn: '#F59E0B', bad: '#EF4444' }[cell.badge] || '#F59E0B';
                        if (pct <= 0) inner = '<span class="text-[11px] font-semibold text-slate-300 dark:text-slate-600">—</span>';
                        else inner = '<span class="inline-flex min-w-20 flex-col items-end gap-1"><span class="text-[11px] font-bold" style="color:' + tone + '">' + esc(cell.text) + '</span>' +
                            '<span class="block h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60"><span class="block h-full rounded-full" style="width:' + Math.max(pct, 4) + '%;background:' + tone + '"></span></span></span>';
                    } else inner = esc(cell);
                    return '<td class="' + hide(i, cols.length) + ' px-4 py-2.5 text-' + al(cols[i], i) + (i === 0 ? ' font-semibold' : ' tabular-nums') + '">' + inner + '</td>';
                }).join('') + '</tr>';
            }).join('') : '<tr><td colspan="' + Math.max(cols.length, 1) + '" class="px-4 py-8 text-center text-slate-400">No data available</td></tr>';
            // the card's own padding is px-5; let the table run edge to edge
            el.className = el.className.replace('px-5 pb-4 pt-3', 'pb-1 pt-2');
            el.innerHTML = '<div class="overflow-x-auto"><table class="w-full text-xs"><thead><tr class="border-t border-slate-100 dark:border-slate-700/60">' + head +
                '</tr></thead><tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">' + body + '</tbody></table></div>' +
                (d.note ? '<p class="px-4 pb-2 pt-1 text-right text-[10px] font-medium text-slate-400 dark:text-slate-500">' + esc(d.note) + '</p>' : '');
        },

        list: function (el, d) {
            var items = d.items || [];
            el.innerHTML = items.length
                ? '<ul class="divide-y divide-slate-100 dark:divide-slate-700/60">' + items.map(function (i) {
                    return '<li class="flex items-center gap-3 py-2.5"><span class="h-2 w-2 shrink-0 rounded-full ' + (DOT[i.tone] || DOT.indigo) + '"></span>' +
                        '<span class="min-w-0 flex-1"><span class="block truncate text-sm font-semibold text-slate-700 dark:text-slate-100">' + esc(i.title) + '</span>' +
                        '<span class="block truncate text-[11px] text-slate-500 dark:text-slate-400">' + esc(i.meta) + '</span></span></li>';
                }).join('') + '</ul>'
                : '<p class="py-6 text-center text-slate-400">Nothing to show</p>';
        },

        insight: function (el, d) {
            var items = d.items || [];
            el.className = 'grid grid-cols-1 gap-x-6 gap-y-1 @md:grid-cols-2 @3xl:grid-cols-3';
            el.innerHTML = items.length ? items.map(function (i) {
                // text may carry <b>; the server writes it, so only <b> is let through
                var text = esc(i.text).replace(/&lt;(\/?)b&gt;/g, '<$1b>');
                return '<div class="flex items-start gap-1.5 py-0.5 text-xs leading-relaxed text-slate-700 dark:text-slate-200">' +
                    '<svg class="mt-0.5 h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="' + (INSIGHT[i.type] || INSIGHT.info) + '" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg><span>' + text + '</span></div>';
            }).join('') : '<span class="text-xs text-slate-400">No insights</span>';
        },

        // a row of headline tiles: label, big number, share of the total as a thin meter
        kpi: function (el, d) {
            var items = d.items || [];
            if (!items.length) return empty(el);
            el.className = '@container flex-1 px-5 pb-4 pt-3';
            // laid out by the width of the widget itself (not the screen): a narrow column stacks the tiles, a wide one puts them side by side
            el.innerHTML = '<div class="grid grid-cols-1 gap-3 @md:grid-cols-2 @3xl:grid-cols-4">' + items.map(function (i) {
                var c = i.color || '#6366F1';
                return '<div class="relative overflow-hidden rounded-xl border border-slate-100 bg-white/70 p-3.5 dark:border-slate-700/60 dark:bg-slate-800/40">' +
                    '<span class="absolute inset-y-0 left-0 w-1" style="background:' + c + '"></span>' +
                    '<p class="' + LABEL + '">' + esc(i.label) + '</p>' +
                    '<p class="mt-1 whitespace-nowrap text-2xl ' + BIG + '">' + esc(i.value) + '</p>' +
                    '<p class="mt-0.5 text-[11px] text-slate-500 dark:text-slate-400">' + esc(i.hint || '') + '</p>' +
                    (i.pct == null ? '' : '<div class="mt-2 h-1 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700/60"><div class="h-full rounded-full transition-all duration-700" style="width:' + Math.max(0, Math.min(100, i.pct)) + '%;background:' + c + '"></div></div>') +
                    '</div>';
            }).join('') + '</div>';
        },

        donut: function (el, d) { chart(el, d, 'donut', 'initDonut', { series: d.series, labels: d.labels, legendPosition: 'bottom' }); },
        // Used · Reserved · Remaining as one bar with each part's amount and share (a donut hides the small parts)
        mix: function (el, d) {
            if (!window.CardChart || !window.CardChart.stackedBar) { el.textContent = 'Chart library not loaded'; return; }
            el.className = 'flex-1 px-5 pb-4 pt-2';
            el.innerHTML = '';
            window.CardChart.stackedBar(el, { segments: d.segments || [], total: d.total, totalLabel: d.totalLabel });
        },
        column: function (el, d) { chart(el, d, 'column', 'initColumn', { series: d.series, categories: d.categories }); },
    };

    // nothing to show: a quiet note centred in the cell, and the header chip fades (.sp-empty)
    function empty(el, text) {
        el.className = 'flex flex-1 flex-col items-center justify-center gap-1.5 px-5 pb-6 pt-4 text-center text-xs text-slate-400';
        el.innerHTML = '<svg class="h-6 w-6 text-slate-300 dark:text-slate-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>' +
            '<span>' + esc(text || 'No data for this period') + '</span>';
        var sec = el.closest('section');
        if (sec) sec.classList.add('sp-empty');
    }

    function chart(el, d, type, init, cfg) {
        if (!window.ApexCharts || !window.CardChart || !window.CardChart[init]) { el.textContent = 'Chart library not loaded'; return; }
        // the chart scripts draw their own DEMO numbers when given nothing — never let that look like real data
        var s = cfg.series || [];
        var has = type === 'donut' ? s.some(function (v) { return v > 0; }) : s.some(function (x) { return (x.data || []).some(function (v) { return v > 0; }); });
        if (!has) {
            return empty(el);
        }
        cfg.height = +el.dataset.spHeight || 260;
        cfg.color = el.dataset.spColor || 'violet';
        el.className = 'flex-1 px-2 pb-3 pt-1';
        el.innerHTML = '';
        var host = document.createElement('div');
        host.dataset.chartType = type;
        host.dataset.config = JSON.stringify(cfg);
        el.appendChild(host);
        window.CardChart[init](host);
    }

    function mount(root) {
        if (root.__sp) return;
        var url = root.dataset.spUrl;
        // companies = codes applied to the data; options = what the user may pick [{code, label}]; draft = ticks while the list is open
        // the period is the admin's default (Dashboard Setup); a click only lasts until the panel is opened again
        var state = { period: root.dataset.spPeriod || 'today', companies: [], options: [], draft: [], seq: 0 };
        try {
            state.companies = (localStorage.getItem('summaryPanelCompanies') || '').split(',').filter(Boolean);
        } catch (e) {}
        root.__sp = state;

        var selectBox = root.querySelector('[data-sp="company-box"]');
        var selectBtn = root.querySelector('[data-sp="company-btn"]');
        var selectLabel = root.querySelector('[data-sp="company-label"]');
        var selectMenu = root.querySelector('[data-sp="company-menu"]');
        var spinner = root.querySelector('[data-sp="spinner"]');
        var widgets = function () { return root.querySelectorAll('[data-sp-widget]'); };

        // ── Company filter (multi-select). The server sends the companies this user may pick from, only when more than one. ──
        function companyText(codes) {
            if (!codes.length) return 'All companies';
            if (codes.length === 1) {
                var o = state.options.filter(function (x) { return x.code === codes[0]; })[0];
                return o ? o.label : codes[0];          // a company name: the server sends it uppercase
            }
            return codes.length + ' companies';
        }

        function renderMenu() {
            var row = function (code, text, on) {
                return '<button type="button" data-code="' + esc(code) + '" class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-xs font-semibold transition hover:bg-slate-50 dark:hover:bg-slate-700 ' + (on ? 'text-indigo-700 dark:text-indigo-300' : 'text-slate-600 dark:text-slate-300') + '">' +
                    '<span class="flex h-4 w-4 shrink-0 items-center justify-center rounded border ' + (on ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-300 dark:border-slate-500') + '">' +
                    (on ? '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>' : '') + '</span>' +
                    '<span class="min-w-0 flex-1 truncate">' + esc(text) + '</span></button>';
            };
            selectMenu.innerHTML = row('', 'All companies', !state.draft.length) +
                '<div class="my-1 h-px bg-slate-100 dark:bg-slate-700"></div>' +
                state.options.map(function (o) { return row(o.code, o.label, state.draft.indexOf(o.code) > -1); }).join('');
        }

        function paintCompanies(list, selected) {
            if (!selectBox) return;
            state.options = list || [];
            if (!state.options.length) { selectBox.hidden = true; return; }
            selectBox.hidden = false;
            state.companies = selected || [];            // the server keeps only codes this user is allowed
            state.draft = state.companies.slice();
            selectLabel.textContent = companyText(state.companies);
            renderMenu();
        }

        function closeMenu() {
            if (!selectMenu || selectMenu.hidden) return;
            selectMenu.hidden = true;
            if (state.draft.slice().sort().join() !== state.companies.slice().sort().join()) {   // changed: apply
                state.companies = state.draft.slice();
                try { localStorage.setItem('summaryPanelCompanies', state.companies.join(',')); } catch (e) {}
                load();
            }
        }

        function paintPeriod() {
            root.querySelectorAll('[data-sp-period]').forEach(function (b) {
                var on = b.dataset.spPeriod === state.period;
                ['bg-indigo-600', 'text-white', 'shadow-sm'].forEach(function (c) { b.classList.toggle(c, on); });
                ['text-slate-500', 'dark:text-slate-300'].forEach(function (c) { b.classList.toggle(c, !on); });
            });
        }

        // One request per card, all started together: a card is drawn the moment its own answer arrives, so quick
        // cards show at once and a slow BigQuery card never holds the others back.
        function load(fresh) {
            paintPeriod();
            var seq = ++state.seq;                        // answers of an older load must not overwrite a newer one
            var els = Array.prototype.slice.call(widgets());
            var pending = els.length, metaDone = false;
            els.forEach(function (el) { el.style.opacity = '.5'; el.classList.add('animate-pulse'); });
            if (spinner) spinner.classList.add('animate-spin');

            var qs = '?period=' + encodeURIComponent(state.period) + '&companies=' + encodeURIComponent(state.companies.join(',')) + (fresh ? '&fresh=1' : '');
            var finish = function (el) {
                el.style.opacity = '';
                el.classList.remove('animate-pulse');
                if (--pending <= 0 && seq === state.seq && spinner) spinner.classList.remove('animate-spin');
            };

            els.forEach(function (el) {
                var ctl = window.AbortController ? new AbortController() : null;
                var timer = setTimeout(function () { if (ctl) ctl.abort(); }, 45000);

                fetch(url + qs + '&widget=' + encodeURIComponent(el.dataset.spWidget), { headers: { Accept: 'application/json' }, signal: ctl ? ctl.signal : undefined })
                    .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
                    .then(function (res) {
                        if (seq !== state.seq) return;
                        if (!metaDone) {                      // filter list + timestamp ride along on the first answer
                            metaDone = true;
                            var up = root.querySelector('[data-sp="updated"]');
                            if (up) up.textContent = 'Updated ' + res.updated;
                            paintCompanies(res.companies, res.selected);
                        }
                        var d = (res.widgets || {})[el.dataset.spWidget];
                        if (!d || d.error) { el.textContent = (d && d.error) || 'No data'; return; }
                        var sec = el.closest('section');
                        if (sec) sec.classList.remove('sp-empty');
                        try { (DRAW[el.dataset.spType] || function (e) { e.textContent = 'Unknown widget type'; })(el, d); }
                        catch (e) { el.textContent = 'Could not draw'; }
                    })
                    .catch(function (err) {
                        if (seq !== state.seq) return;
                        el.textContent = err && err.name === 'AbortError' ? 'Took too long — press Refresh to try again' : 'Could not load (' + ((err && err.message) || 'network error') + ')';
                    })
                    .then(function () {
                        clearTimeout(timer);
                        if (seq === state.seq) finish(el);
                    });
            });
        }

        root.addEventListener('click', function (e) {
            var p = e.target.closest('[data-sp-period]');
            if (p) {
                state.period = p.dataset.spPeriod;
                return load();
            }
            if (e.target.closest('[data-sp="refresh"]')) load(true);
        });
        if (selectBtn) {
            selectBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (selectMenu.hidden) { state.draft = state.companies.slice(); renderMenu(); selectMenu.hidden = false; } else { closeMenu(); }
            });
            selectMenu.addEventListener('click', function (e) {
                e.stopPropagation();
                var b = e.target.closest('[data-code]');
                if (!b) return;
                var code = b.dataset.code, i = state.draft.indexOf(code);
                if (!code) state.draft = [];                                  // "All companies" clears the ticks
                else if (i > -1) state.draft.splice(i, 1); else state.draft.push(code);
                selectLabel.textContent = companyText(state.draft);
                renderMenu();
            });
        }
        state.closeMenu = closeMenu;
        bindDocument();
        load();
    }

    // "Click outside / Esc closes the company list" is bound to the document ONCE, however many times a panel is
    // mounted (the page rebuilds a panel every time its tile is opened) — it closes every list that is open.
    var documentBound = false;
    function bindDocument() {
        if (documentBound) return;
        documentBound = true;
        var closeAll = function () {
            document.querySelectorAll('[data-sp-root]').forEach(function (r) { if (r.__sp && r.__sp.closeMenu) r.__sp.closeMenu(); });
        };
        document.addEventListener('click', closeAll);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeAll(); });
    }

    window.SummaryPanel = { mount: mount, types: DRAW };
})();
