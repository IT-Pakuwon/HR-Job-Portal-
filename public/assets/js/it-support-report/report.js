(function () {
    'use strict';

    var routes = window.itSupportReportRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { unit: null, status: null, top: null };
    var chartData = {}; // last-rendered {categories, series, colors} per chart key — reused on theme toggle

    var tableAll = [];
    var tableSorted = null;
    var tablePage = 1;
    var tablePageSize = 10;
    var tableSortBind = null;

    var MODULE_LABELS = {
        TICKET: 'Ticket Support',
        RECOMMENDATION: 'IT Recommendation',
        ACCESS: 'Access Request',
    };

    var CATEGORY_TITLES = {
        TICKET: 'Category by Department',
        RECOMMENDATION: 'Recommendation Type by Department',
        ACCESS: 'Access Type by Department',
    };

    var BREAKDOWN_TITLES = {
        TICKET: 'Top Equipment System (Completed)',
        RECOMMENDATION: 'Top Recommendation Type (Completed)',
        ACCESS: 'Top Access Category (Completed)',
    };

    function currentModule() {
        var sel = document.getElementById('gmTicketTypeFilter');
        return sel ? sel.value : 'TICKET';
    }

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
            utils.setText('itrepStatTotal', d.total_ticket || 0);
            utils.setText('itrepStatCompleted', d.completed || 0);
            utils.setText('itrepStatProgress', d.on_progress || 0);
            utils.setText('itrepStatRate', (d.completion_rate || 0) + '%');
            utils.setText('itrepStatResolution', d.avg_resolution_label || '–');

            var box = document.getElementById('itrepKeyHighlights');
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
        ['on_progress', 'On Progress', '#F59E0B'],
        ['revised', 'Revised', '#8B5CF6'],
        ['rejected', 'Rejected', '#EF4444'],
        ['cancelled', 'Cancelled', '#94A3B8'],
    ];

    // Per-cell status breakdown tooltip (unit/category → {completed, on_progress, revised, rejected, cancelled}).
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

    function renderStackedBar(key, elId, categories, series, colors, breakdown) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, colors: colors, breakdown: breakdown };

        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();
        var height = Math.max(280, categories.length * 32);

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
                type: 'bar', height: height, stacked: true,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: colors || barPalette(),
            plotOptions: { bar: { horizontal: true, barHeight: '65%', borderRadius: 3, borderRadiusApplication: 'end' } },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val > 0 ? parseInt(val, 10) : ''; },
                style: { fontSize: '10px', fontWeight: 600, colors: ['#fff'] },
            },
            xaxis: { categories: categories, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: tooltip,
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
        });
        charts[key].render();
    }

    function loadCategoryByUnit(params) {
        fetchJson(routes.categoryByUnit, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('unit', 'itrepCategoryByUnitChart', d.categories || [], d.series || [], null, d.breakdown || {});
        }).catch(function () {});
    }

    function loadStatusByCategory(params) {
        fetchJson(routes.statusByCategory, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('status', 'itrepStatusByCategoryChart', d.categories || [], d.series || [], ['#10B981', '#F59E0B']);
        }).catch(function () {});
    }

    function loadTopBreakdown(params) {
        fetchJson(routes.topBreakdown, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('top', 'itrepTopBreakdownChart', d.categories || [], d.series || [], ['#3B82F6']);
        }).catch(function () {});
    }

    // ── Table ────────────────────────────────────────────────────────────────
    function statusBadge(status) {
        var styles = {
            CREATED: 'bg-slate-100 text-slate-700 border-slate-200',
            RESPONSE: 'bg-blue-100 text-blue-700 border-blue-200',
            APPROVED: 'bg-teal-100 text-teal-700 border-teal-200',
            PROCESS: 'bg-amber-100 text-amber-700 border-amber-200',
            PENDING: 'bg-orange-100 text-orange-700 border-orange-200',
            COMPLETED: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            FINISHED: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            TRANSFER: 'bg-cyan-100 text-cyan-700 border-cyan-200',
            REOPEN: 'bg-red-100 text-red-700 border-red-200',
            REJECTED: 'bg-rose-100 text-rose-700 border-rose-200',
            CANCEL: 'bg-slate-200 text-slate-700 border-slate-300',
            CANCELLED: 'bg-slate-200 text-slate-700 border-slate-300',
            'WAITING IT': 'bg-blue-100 text-blue-700 border-blue-200',
            'WAITING IT REVISION': 'bg-amber-100 text-amber-700 border-amber-200',
            'WAITING APPROVAL': 'bg-orange-100 text-orange-700 border-orange-200',
            REVISE: 'bg-amber-100 text-amber-700 border-amber-200',
            APPROVED: 'bg-teal-100 text-teal-700 border-teal-200',
        };
        var cls = styles[status] || 'bg-slate-100 text-slate-700 border-slate-200';
        return '<span class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap ' + cls + '">' + utils.escHtml(status || '-') + '</span>';
    }

    function applyTableSearch() {
        var term = (document.getElementById('itrepTableSearch').value || '').trim().toLowerCase();
        if (!term) return tableAll;
        return tableAll.filter(function (r) {
            return [r.docid, r.unit, r.category, r.issue, r.status, r.cpny_id, r.pic]
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

        var body = document.getElementById('itrepTableBody');
        if (!pageRows.length) {
            body.innerHTML = '<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data available</td></tr>';
        } else {
            body.innerHTML = pageRows.map(function (r) {
                return '<tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.date || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">' + utils.escHtml(r.unit || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.category || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.issue || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5">' + statusBadge(r.status) + '</td>'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-center">'
                    + '<a href="' + r.view_url + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2.5 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">View</a>'
                    + '</td>'
                    + '</tr>';
            }).join('');
        }

        utils.renderPagination('itrepTable', total, tablePage, tablePageSize, function (p) {
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
        var search = document.getElementById('itrepTableSearch');
        if (search) search.addEventListener('keyup', function () {
            tableSorted = null;
            tablePage = 1;
            renderTable();
        });

        var pageSize = document.getElementById('itrepTablePageSize');
        if (pageSize) pageSize.addEventListener('change', function () {
            tablePageSize = parseInt(this.value, 10) || 10;
            tablePage = 1;
            renderTable();
        });

        tableSortBind = utils.bindTableSort(
            'itrepTableBody',
            function () { return tableSorted !== null ? tableSorted : applyTableSearch(); },
            function (rows) { tableSorted = rows; },
            function () { tablePage = 1; },
            renderTable
        );
    }

    function updateTitlesForModule() {
        var mod = currentModule();
        utils.setText('itrepTableSubtitle', MODULE_LABELS[mod] || 'Ticket Support');
        utils.setText('itrepCategoryByUnitTitle', CATEGORY_TITLES[mod] || CATEGORY_TITLES.TICKET);
        utils.setText('itrepTopBreakdownTitle', BREAKDOWN_TITLES[mod] || BREAKDOWN_TITLES.TICKET);
    }

    // ── Refresh on filter change ─────────────────────────────────────────────
    function refresh() {
        var params = utils.buildParams ? utils.buildParams() : '';
        updateTitlesForModule();
        loadSummary(params);
        loadCategoryByUnit(params);
        loadStatusByCategory(params);
        loadTopBreakdown(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('itrepTableBody')) return;
        bindTableEvents();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            Object.keys(chartData).forEach(function (key) {
                var c = chartData[key];
                renderStackedBar(key, c.elId, c.categories, c.series, c.colors, c.breakdown);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
