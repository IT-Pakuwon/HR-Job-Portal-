(function () {
    'use strict';

    var routes = window.trainingReportRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { trend: null, top: null, dept: null, level: null };
    var chartData = {}; // last-rendered payload per chart key — reused on theme toggle

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

    function barPalette() {
        return ['#3B82F6', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#06B6D4', '#EF4444', '#84CC16'];
    }

    // ── Stat cards ───────────────────────────────────────────────────────────
    function loadSummary(params) {
        fetchJson(routes.summary, params).then(function (res) {
            var d = res.data || {};
            utils.setText('trnrepStatAttendance', d.total_attendance || 0);
            utils.setText('trnrepStatHours', d.total_training_hours || 0);
            utils.setText('trnrepStatSatisfaction', d.avg_satisfaction !== null && d.avg_satisfaction !== undefined ? d.avg_satisfaction + ' / 5' : '–');
            utils.setText('trnrepStatStars', d.avg_stars !== null && d.avg_stars !== undefined ? d.avg_stars + ' / 5' : '–');
            utils.setText('trnrepStatRate', (d.completion_rate || 0) + '%');
        }).catch(function () {});
    }

    // ── Horizontal bar chart (department / level / top trainings) ──────────────
    function renderBarChart(key, elId, categories, series, color) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, color: color };

        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();
        var height = Math.max(260, categories.length * 34);

        charts[key] = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bar', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: barPalette(),
            plotOptions: { bar: { horizontal: true, barHeight: '60%', borderRadius: 3, borderRadiusApplication: 'end', distributed: true } },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val > 0 ? parseInt(val, 10) : ''; },
                style: { fontSize: '10px', fontWeight: 600, colors: [dark ? '#E2E8F0' : '#1E293B'] },
                offsetX: 6,
            },
            legend: { show: false },
            xaxis: { categories: categories, labels: { style: { fontSize: '11px' } } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 12 } },
            tooltip: { theme: dark ? 'dark' : 'light' },
        });
        charts[key].render();
    }

    function loadByDepartment(params) {
        fetchJson(routes.byDepartment, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('dept', 'trnrepByDepartmentChart', d.categories || [], d.series || [], '#10B981');
        }).catch(function () {});
    }

    function loadByLevel(params) {
        fetchJson(routes.byLevel, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('level', 'trnrepByLevelChart', d.categories || [], d.series || [], '#F59E0B');
        }).catch(function () {});
    }

    function loadTopTrainings(params) {
        fetchJson(routes.topTrainings, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('top', 'trnrepTopTrainingsChart', d.categories || [], d.series || [], '#8B5CF6');
        }).catch(function () {});
    }

    // ── Trend combo chart (attendance bars + satisfaction line) ─────────────────
    function renderTrend(categories, attendance, satisfaction) {
        var el = document.getElementById('trnrepTrendChart');
        if (!el) return;

        chartData.trend = { categories: categories, attendance: attendance, satisfaction: satisfaction };

        if (charts.trend) { charts.trend.destroy(); charts.trend = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();

        charts.trend = new ApexCharts(el, {
            series: [
                { name: 'Attendance', type: 'column', data: attendance },
                { name: 'Avg. Satisfaction', type: 'line', data: satisfaction },
            ],
            chart: {
                height: 320, toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: ['#3B82F6', '#EC4899'],
            stroke: { width: [0, 3], curve: 'smooth' },
            markers: { size: 4, colors: ['#EC4899'], strokeColors: '#fff', strokeWidth: 2 },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: categories, labels: { style: { fontSize: '11px' } } },
            yaxis: [
                { title: { text: 'Attendance', style: { fontSize: '11px' } }, labels: { style: { fontSize: '11px' } } },
                { opposite: true, min: 0, max: 5, title: { text: 'Satisfaction', style: { fontSize: '11px' } }, labels: { style: { fontSize: '11px' } } },
            ],
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light', shared: true },
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
        });
        charts.trend.render();
    }

    function loadTrend(params) {
        fetchJson(routes.trend, params).then(function (res) {
            var d = res.data || {};
            renderTrend(d.categories || [], d.attendance || [], d.satisfaction || []);
        }).catch(function () {});
    }

    // ── Table ────────────────────────────────────────────────────────────────
    function applyTableSearch() {
        var term = (document.getElementById('trnrepTableSearch').value || '').trim().toLowerCase();
        if (!term) return tableAll;
        return tableAll.filter(function (r) {
            return [r.training_name, r.level_name, r.date]
                .some(function (f) { return (f || '').toString().toLowerCase().indexOf(term) !== -1; });
        });
    }

    function fmtNum(v) {
        return v === null || v === undefined ? '–' : v;
    }

    function renderTable() {
        var filtered = tableSorted !== null ? tableSorted : applyTableSearch();
        var total = filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / tablePageSize));
        tablePage = Math.min(tablePage, totalPages);

        var start = (tablePage - 1) * tablePageSize;
        var pageRows = filtered.slice(start, start + tablePageSize);

        var body = document.getElementById('trnrepTableBody');
        if (!pageRows.length) {
            body.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data available</td></tr>';
        } else {
            body.innerHTML = pageRows.map(function (r) {
                return '<tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.date || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">' + utils.escHtml(r.training_name || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.level_name || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-right text-slate-700 dark:text-slate-200">' + fmtNum(r.attendees) + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">' + fmtNum(r.total_hours) + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-right text-slate-600 dark:text-slate-300">' + fmtNum(r.avg_satisfaction) + '</td>'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-right text-slate-600 dark:text-slate-300">' + fmtNum(r.avg_stars) + '</td>'
                    + '</tr>';
            }).join('');
        }

        utils.renderPagination('trnrepTable', total, tablePage, tablePageSize, function (p) {
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
        var search = document.getElementById('trnrepTableSearch');
        if (search) search.addEventListener('keyup', function () {
            tableSorted = null;
            tablePage = 1;
            renderTable();
        });

        var pageSize = document.getElementById('trnrepTablePageSize');
        if (pageSize) pageSize.addEventListener('change', function () {
            tablePageSize = parseInt(this.value, 10) || 10;
            tablePage = 1;
            renderTable();
        });

        tableSortBind = utils.bindTableSort(
            'trnrepTableBody',
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
        loadTrend(params);
        loadTopTrainings(params);
        loadByDepartment(params);
        loadByLevel(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('trnrepTableBody')) return;
        bindTableEvents();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            if (chartData.trend) renderTrend(chartData.trend.categories, chartData.trend.attendance, chartData.trend.satisfaction);
            ['top', 'dept', 'level'].forEach(function (key) {
                var c = chartData[key];
                if (c) renderBarChart(key, c.elId, c.categories, c.series, c.color);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
