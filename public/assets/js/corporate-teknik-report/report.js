(function () {
    'use strict';

    var routes = window.corpTekRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { unit: null, status: null, equip: null };
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
            utils.setText('corpStatTotal', d.total_ticket || 0);
            utils.setText('corpStatCompleted', d.completed || 0);
            utils.setText('corpStatProgress', d.on_progress || 0);
            utils.setText('corpStatRate', (d.completion_rate || 0) + '%');

            var box = document.getElementById('corpKeyHighlights');
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

    function renderStackedBar(key, elId, categories, series, colors) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, colors: colors };

        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();
        var height = Math.max(280, categories.length * 32);

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
            tooltip: { theme: dark ? 'dark' : 'light' },
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
        });
        charts[key].render();
    }

    function loadCategoryByUnit(params) {
        fetchJson(routes.categoryByUnit, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('unit', 'corpCategoryByUnitChart', d.categories || [], d.series || []);
        }).catch(function () {});
    }

    function loadStatusByCategory(params) {
        fetchJson(routes.statusByCategory, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('status', 'corpStatusByCategoryChart', d.categories || [], d.series || [], ['#10B981', '#F59E0B']);
        }).catch(function () {});
    }

    function loadTopEquipment(params) {
        fetchJson(routes.topEquipment, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('equip', 'corpTopEquipmentChart', d.categories || [], d.series || [], ['#3B82F6']);
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
            TRANSFER: 'bg-cyan-100 text-cyan-700 border-cyan-200',
            REOPEN: 'bg-red-100 text-red-700 border-red-200',
            CANCEL: 'bg-slate-200 text-slate-700 border-slate-300',
        };
        var cls = styles[status] || 'bg-slate-100 text-slate-700 border-slate-200';
        return '<span class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap ' + cls + '">' + utils.escHtml(status || '-') + '</span>';
    }

    function applyTableSearch() {
        var term = (document.getElementById('corpTableSearch').value || '').trim().toLowerCase();
        if (!term) return tableAll;
        return tableAll.filter(function (r) {
            return [r.ticketid, r.unit, r.category, r.equipment_system, r.issue, r.status, r.cpny_id, r.pic_ticket]
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

        var body = document.getElementById('corpTableBody');
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
                    + '<a href="/showoprtekticket/' + r.eid + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2.5 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">View</a>'
                    + '</td>'
                    + '</tr>';
            }).join('');
        }

        utils.renderPagination('corpTable', total, tablePage, tablePageSize, function (p) {
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
        var search = document.getElementById('corpTableSearch');
        if (search) search.addEventListener('keyup', function () {
            tableSorted = null;
            tablePage = 1;
            renderTable();
        });

        var pageSize = document.getElementById('corpTablePageSize');
        if (pageSize) pageSize.addEventListener('change', function () {
            tablePageSize = parseInt(this.value, 10) || 10;
            tablePage = 1;
            renderTable();
        });

        tableSortBind = utils.bindTableSort(
            'corpTableBody',
            function () { return tableSorted !== null ? tableSorted : applyTableSearch(); },
            function (rows) { tableSorted = rows; },
            function () { tablePage = 1; },
            renderTable
        );
    }

    function updateTableSubtitle() {
        var sel = document.getElementById('gmTicketTypeFilter');
        var label = sel ? sel.options[sel.selectedIndex].text : 'Support Ticket';
        utils.setText('corpTableSubtitle', label);
    }

    // ── Refresh on filter change ─────────────────────────────────────────────
    function refresh() {
        var params = utils.buildParams ? utils.buildParams() : '';
        updateTableSubtitle();
        loadSummary(params);
        loadCategoryByUnit(params);
        loadStatusByCategory(params);
        loadTopEquipment(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('corpTableBody')) return;
        bindTableEvents();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            Object.keys(chartData).forEach(function (key) {
                var c = chartData[key];
                renderStackedBar(key, c.elId, c.categories, c.series, c.colors);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
