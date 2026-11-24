(function () {
    'use strict';

    var routes = window.legalLeaseReportRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { unit: null, status: null, top: null, sent: null };
    var chartData = {}; // last-rendered {categories, series, colors} per chart key — reused on theme toggle

    var tableAll = [];
    var tableSorted = null;
    var tablePage = 1;
    var tablePageSize = 10;
    var tableSortBind = null;

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function fetchJson(url, params) {
        return fetch(url + (params || ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        }).then(function (r) { return r.json(); });
    }

    // ── Stat cards ───────────────────────────────────────────────────────────
    function loadSummary(params) {
        fetchJson(routes.summary, params).then(function (res) {
            var d = res.data || {};
            utils.setText('llrepStatTotal', d.total || 0);
            utils.setText('llrepStatCompleted', d.completed || 0);
            utils.setText('llrepStatProgress', d.on_progress || 0);
            utils.setText('llrepStatRate', (d.completion_rate || 0) + '%');
            utils.setText('llrepStatIncomplete', d.incomplete_docs || 0);

            var box = document.getElementById('llrepKeyHighlights');
            if (box) {
                var items = d.highlights || [];
                box.innerHTML = items.length
                    ? items.map(function (h) { return '<p>' + utils.escHtml(h) + '</p>'; }).join('')
                    : '<p class="text-slate-400 dark:text-slate-500">No data for this period.</p>';
            }
        }).catch(function () {});
    }

    // ── Charts ───────────────────────────────────────────────────────────────
    function barPalette() {
        return ['#3B82F6', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#06B6D4', '#EF4444', '#84CC16'];
    }

    var BUCKET_ROWS = [
        ['completed', 'Completed', '#10B981'],
        ['on_progress', 'Active', '#F59E0B'],
        ['hold', 'Hold', '#8B5CF6'],
        ['escalated', 'Escalated', '#EF4444'],
        ['cancelled', 'Cancelled', '#94A3B8'],
    ];

    // Per-cell status breakdown tooltip (company/type → {completed, on_progress, hold, escalated, cancelled}).
    // Built as a plain string (not a DOM node) since ApexCharts' `tooltip.custom` expects HTML.
    function breakdownTooltipHtml(breakdown, unit, category, dark) {
        var cell = (breakdown[unit] && breakdown[unit][category]) || {};
        var border = dark ? '#334155' : '#E2E8F0';
        var bg = dark ? '#0F172A' : '#FFFFFF';
        var text = dark ? '#E2E8F0' : '#1E293B';
        var muted = dark ? '#94A3B8' : '#64748B';

        var rowsHtml = BUCKET_ROWS.map(function (b) {
            var val = cell[b[0]] || 0;
            if (!val) return '';
            return '<div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:2px 0">'
                + '<span style="display:flex;align-items:center;gap:5px;color:' + muted + '">'
                + '<span style="width:7px;height:7px;border-radius:2px;background:' + b[2] + ';display:inline-block"></span>' + b[1] + '</span>'
                + '<span style="font-weight:700;color:' + text + '">' + val + '</span></div>';
        }).join('');

        var total = BUCKET_ROWS.reduce(function (sum, b) { return sum + (cell[b[0]] || 0); }, 0);

        return '<div style="background:' + bg + ';border:1px solid ' + border + ';border-radius:8px;padding:8px 10px;font-size:11px;min-width:160px;box-shadow:0 4px 12px rgba(0,0,0,.12)">'
            + '<div style="font-weight:700;color:' + text + ';margin-bottom:4px;white-space:nowrap">' + utils.escHtml(category) + ' — ' + utils.escHtml(unit) + '</div>'
            + (rowsHtml || '<div style="color:' + muted + '">No data</div>')
            + '<div style="border-top:1px solid ' + border + ';margin-top:4px;padding-top:4px;display:flex;justify-content:space-between;font-weight:700;color:' + text + '">'
            + '<span>Total</span><span>' + total + '</span></div>'
            + '</div>';
    }

    function renderStackedBar(key, elId, categories, series, colors, breakdown, opts) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, colors: colors, breakdown: breakdown, opts: opts };
        var grouped = !!(opts && opts.stacked === false);
        var vertical = !!(opts && opts.vertical);

        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();
        var height = vertical ? 300 : Math.max(280, categories.length * (grouped ? 70 : 32));

        // Counts are whole numbers — never label the value axis 0.2, 0.4 …
        var valueLabels = {
            style: { fontSize: '11px' },
            formatter: function (v) { return Math.abs(v - Math.round(v)) < 0.001 ? String(Math.round(v)) : ''; },
        };

        var tooltip = breakdown
            ? {
                custom: function (opts) {
                    var unit = categories[opts.dataPointIndex];
                    var category = opts.w.globals.seriesNames[opts.seriesIndex];
                    return breakdownTooltipHtml(breakdown, unit, category, isDark());
                },
            }
            : { theme: dark ? 'dark' : 'light' };

        charts[key] = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bar', height: height, stacked: !grouped,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: colors || barPalette(),
            plotOptions: { bar: vertical
                ? { horizontal: false, columnWidth: '45%', borderRadius: 3, borderRadiusApplication: 'end' }
                : { horizontal: true, barHeight: '65%', borderRadius: 3, borderRadiusApplication: 'end' } },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val > 0 ? parseInt(val, 10) : ''; },
                style: { fontSize: '10px', fontWeight: 600, colors: ['#fff'] },
            },
            xaxis: { categories: categories, labels: vertical ? { style: { fontSize: '11px' } } : valueLabels },
            yaxis: { labels: vertical ? valueLabels : { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: tooltip,
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
        });
        charts[key].render();
    }

    function loadCategoryByUnit(params) {
        fetchJson(routes.categoryByUnit, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('unit', 'llrepCategoryByUnitChart', d.categories || [], d.series || [], null, d.breakdown || {});
        }).catch(function () {});
    }

    function loadStatusByCategory(params) {
        fetchJson(routes.statusByCategory, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('status', 'llrepStatusByCategoryChart', d.categories || [], d.series || [], ['#10B981', '#F59E0B']);
        }).catch(function () {});
    }

    function loadTopBreakdown(params) {
        fetchJson(routes.topBreakdown, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('top', 'llrepTopBreakdownChart', d.categories || [], d.series || [], ['#3B82F6']);
        }).catch(function () {});
    }

    // ── Memo & LoI documents by Office / Mall (stacked bar) ─────────────────
    function renderMemoBlock(key, chartId, block) {
        block = block || {};
        var received = block.received || 0;

        // Columns = Office / Mall, stacked by company (received documents).
        var props = received ? (block.by_property || []) : [];
        var companies = block.companies || [];
        renderStackedBar(key, chartId,
            props.map(function (p) { return p.property; }),
            companies.map(function (c) {
                return { name: c, data: props.map(function (p) { return (p.by_company || {})[c] || 0; }) };
            }),
            null, null, { vertical: true });
    }

    function loadMemoLoi(params) {
        fetchJson(routes.memoLoi, params).then(function (res) {
            var d = res.data || {};
            renderMemoBlock('memoNew', 'llrepNewLoiMemoChart', d.new_loi_memo);
            renderMemoBlock('memoAddendum', 'llrepAddendumMemoChart', d.addendum_memo);
        }).catch(function () {});
    }

    // ── Tenants sent + Surat 1 / Surat 2 / Escalation ───────────────────────
    function loadSentLetters(params) {
        fetchJson(routes.sentLetters, params).then(function (res) {
            var d = res.data || {};
            var total = d.total || {};
            var rows = d.rows || [];

            // Headline tiles: each letter stage as a share of the tenants already sent.
            var steps = [
                { key: 'sent', label: 'Tenants Sent', color: '#06B6D4', text: 'text-cyan-600 dark:text-cyan-400' },
                { key: 'surat1', label: 'Surat 1', color: '#F59E0B', text: 'text-amber-600 dark:text-amber-400' },
                { key: 'surat2', label: 'Surat 2', color: '#F97316', text: 'text-orange-600 dark:text-orange-400' },
                { key: 'escalation', label: 'Escalation', color: '#EF4444', text: 'text-rose-600 dark:text-rose-400' },
            ];
            var tiles = document.getElementById('llrepSentLettersSummary');
            if (tiles) {
                tiles.innerHTML = steps.map(function (s) {
                    var v = total[s.key] || 0;
                    var sub = s.key === 'sent'
                        ? 'tenants'
                        : ((total.sent ? Math.round((v / total.sent) * 100) : 0) + '% of sent');
                    return '<div class="rounded-xl border border-slate-200 bg-slate-50/60 px-4 py-3 dark:border-slate-700/60 dark:bg-slate-800/40" style="border-left:3px solid ' + s.color + '">'
                        + '<div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">' + s.label + '</div>'
                        + '<div class="mt-1 text-2xl font-extrabold ' + s.text + '">' + v + '</div>'
                        + '<div class="text-[10px] text-slate-400 dark:text-slate-500">' + sub + '</div></div>';
                }).join('');
            }

            // Grouped (not stacked) — every stage is a subset of the one before it, so
            // stacking would double-count the same tenants.
            renderStackedBar('sent', 'llrepSentLettersChart',
                rows.map(function (r) { return r.label; }),
                steps.map(function (s) {
                    return { name: s.label, data: rows.map(function (r) { return r[s.key] || 0; }) };
                }),
                steps.map(function (s) { return s.color; }),
                null, { stacked: false });
        }).catch(function () {});
    }

    // ── Table ────────────────────────────────────────────────────────────────
    function statusBadge(status) {
        var styles = {
            ACTIVE: 'bg-amber-100 text-amber-700 border-amber-200',
            COMPLETED: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            HOLD: 'bg-violet-100 text-violet-700 border-violet-200',
            ESCALATED: 'bg-rose-100 text-rose-700 border-rose-200',
            CANCELLED: 'bg-slate-200 text-slate-700 border-slate-300',
        };
        var cls = styles[status] || 'bg-slate-100 text-slate-700 border-slate-200';
        return '<span class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap ' + cls + '">' + utils.escHtml(status || '-') + '</span>';
    }

    function docsCell(r) {
        if (!r.docs || r.docs === '-') return '<span class="text-slate-400">-</span>';
        var cls = r.incomplete_docs ? 'text-rose-600 font-semibold dark:text-rose-400' : 'text-slate-600 dark:text-slate-300';
        return '<span class="' + cls + '">' + utils.escHtml(r.docs) + '</span>';
    }

    function applyTableSearch() {
        var term = (document.getElementById('llrepTableSearch').value || '').trim().toLowerCase();
        if (!term) return tableAll;
        return tableAll.filter(function (r) {
            return [r.docid, r.no_psm, r.company, r.category, r.tenant, r.unit, r.status, r.cpny_id, r.pic]
                .some(function (f) { return (f || '').toString().toLowerCase().indexOf(term) !== -1; });
        });
    }

    function renderTable() {
        var filtered = tableSorted !== null ? tableSorted : applyTableSearch();
        var total = filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / tablePageSize));
        tablePage = Math.min(tablePage, totalPages);

        var start = (tablePage - 1) * tablePageSize;
        var pageRows = filtered.slice(start, start + tablePageSize);

        var body = document.getElementById('llrepTableBody');
        if (!pageRows.length) {
            body.innerHTML = '<tr><td colspan="10" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data available</td></tr>';
        } else {
            body.innerHTML = pageRows.map(function (r) {
                return '<tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.date || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-700 dark:text-slate-200"><div class="font-semibold">' + utils.escHtml(r.docid || '-') + '</div>'
                    + (r.no_psm ? '<div class="text-[10px] text-slate-400">' + utils.escHtml(r.no_psm) + '</div>' : '') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.category || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.company || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">' + utils.escHtml(r.tenant || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.unit || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.pic || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5">' + docsCell(r) + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5">' + statusBadge(r.status) + '</td>'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-center">'
                    + '<a href="' + r.view_url + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2.5 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">View</a>'
                    + '</td>'
                    + '</tr>';
            }).join('');
        }

        utils.renderPagination('llrepTable', total, tablePage, tablePageSize, function (p) {
            tablePage = p;
            renderTable();
        });
    }

    function loadTable(params) {
        fetchJson(routes.table, params).then(function (res) {
            tableAll = res.data || [];
            tableSorted = null;
            tablePage = 1;
            if (tableSortBind) tableSortBind.reset();
            renderTable();
        }).catch(function () {});
    }

    function bindTableEvents() {
        var search = document.getElementById('llrepTableSearch');
        if (search) search.addEventListener('keyup', function () {
            tableSorted = null;
            tablePage = 1;
            renderTable();
        });

        var pageSize = document.getElementById('llrepTablePageSize');
        if (pageSize) pageSize.addEventListener('change', function () {
            tablePageSize = parseInt(this.value, 10) || 10;
            tablePage = 1;
            renderTable();
        });

        tableSortBind = utils.bindTableSort(
            'llrepTableBody',
            function () { return tableSorted !== null ? tableSorted : applyTableSearch(); },
            function (rows) { tableSorted = rows; },
            function () { tablePage = 1; },
            renderTable
        );
    }

    // ── Refresh on filter change ─────────────────────────────────────────────
    function refresh() {
        var params = utils.buildParams ? utils.buildParams() : '';
        loadSummary(params);
        loadMemoLoi(params);
        loadSentLetters(params);
        loadCategoryByUnit(params);
        loadStatusByCategory(params);
        loadTopBreakdown(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('llrepTableBody')) return;
        bindTableEvents();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            Object.keys(chartData).forEach(function (key) {
                var c = chartData[key];
                renderStackedBar(key, c.elId, c.categories, c.series, c.colors, c.breakdown, c.opts);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
