(function () {
    'use strict';

    var routes = window.trainingReportRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { trend: null, top: null, dept: null, level: null, funnel: null };
    var chartData = {}; // last-rendered payload per chart key — reused on theme toggle

    var tableAll = [];
    var tableSorted = null;
    var tablePage = 1;
    var tablePageSize = 10;
    var tableSortBind = null;

    // Training/Schedule/Level filters — kept outside gmState since gm-core.js/
    // gm-filter.js are shared by other reports and don't know about these.
    var extraFilters = { training_id: '', schedule_id: '', level: '' };

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function fetchJson(url, params) {
        return fetch(url + (params || ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        }).then(function (r) { return r.json(); });
    }

    // Appends training_id/schedule_id/level onto gmUtils.buildParams()'s
    // query string. Exposed on window so the dashboard view's own export-link
    // script (outside this IIFE) can build the same params.
    function combinedParams() {
        var base = utils.buildParams ? utils.buildParams() : '';
        var extra = [];
        if (extraFilters.training_id) extra.push('training_id=' + encodeURIComponent(extraFilters.training_id));
        if (extraFilters.schedule_id) extra.push('schedule_id=' + encodeURIComponent(extraFilters.schedule_id));
        if (extraFilters.level) extra.push('level=' + encodeURIComponent(extraFilters.level));
        if (!extra.length) return base;
        return base ? base + '&' + extra.join('&') : '?' + extra.join('&');
    }
    window.trainingReportCombinedParams = combinedParams;

    // ── Training / Schedule / Level filter dropdowns (Select2-powered) ──────────
    var $ = window.jQuery;
    var hasSelect2 = !!($ && $.fn && $.fn.select2);

    function optionsHtml(placeholder, items) {
        return '<option value="">' + placeholder + '</option>' + items.map(function (it) {
            return '<option value="' + utils.escHtml(it.id) + '">' + utils.escHtml(it.name) + '</option>';
        }).join('');
    }

    // Replaces a <select>'s options and tells Select2 (if active) to re-read
    // them — plain DOM .innerHTML assignment alone leaves Select2's cached
    // rendering stale.
    function setSelectOptions(id, html, value) {
        var el = document.getElementById(id);
        if (!el) return;
        el.innerHTML = html;
        el.value = value || '';
        if (hasSelect2) $(el).trigger('change.select2');
    }

    function initSelect2() {
        if (!hasSelect2) return;
        $('#trnrepTrainingFilter').select2({ width: '160px', dropdownAutoWidth: true });
        $('#trnrepScheduleFilter').select2({ width: '220px', dropdownAutoWidth: true });
        $('#trnrepLevelFilter').select2({ width: '150px', dropdownAutoWidth: true });
    }

    // trainingId narrows the schedule list to just that training's sessions;
    // omit it to (re)load the full training/level lists too.
    function loadFilterOptions(trainingId) {
        var url = routes.filters + (trainingId ? ('?training_id=' + encodeURIComponent(trainingId)) : '');
        fetchJson(url).then(function (res) {
            if (!trainingId) {
                setSelectOptions('trnrepTrainingFilter', optionsHtml('All Trainings', res.trainings || []), extraFilters.training_id);
                setSelectOptions('trnrepLevelFilter', optionsHtml('All Levels', (res.levels || []).map(function (l) { return { id: l, name: l }; })), extraFilters.level);
            }
            setSelectOptions('trnrepScheduleFilter', optionsHtml('All Schedules', res.schedules || []), extraFilters.schedule_id);
        }).catch(function () {});
    }

    // Select2 updates the underlying <select> and fires a plain (un-namespaced)
    // jQuery 'change' — jQuery's trigger() for 'change' only invokes handlers
    // registered through jQuery itself, never ones added via native
    // addEventListener, so these must be bound with $.on() whenever Select2 is
    // active. Falls back to addEventListener when Select2 didn't load.
    function onChange(el, handler) {
        if (!el) return;
        if (hasSelect2) {
            $(el).on('change', handler);
        } else {
            el.addEventListener('change', handler);
        }
    }

    function bindExtraFilterEvents() {
        onChange(document.getElementById('trnrepTrainingFilter'), function () {
            extraFilters.training_id = this.value;
            extraFilters.schedule_id = ''; // a schedule from a different training no longer applies
            loadFilterOptions(this.value);
            if (window.gmDispatchFilter) window.gmDispatchFilter();
        });

        onChange(document.getElementById('trnrepScheduleFilter'), function () {
            extraFilters.schedule_id = this.value;
            if (window.gmDispatchFilter) window.gmDispatchFilter();
        });

        onChange(document.getElementById('trnrepLevelFilter'), function () {
            extraFilters.level = this.value;
            if (window.gmDispatchFilter) window.gmDispatchFilter();
        });
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
            utils.setText('trnrepStatRate', (d.completion_rate || 0) + '%');
        }).catch(function () {});
    }

    // ── Quota vs Registered vs Attended (column + conversion-% line combo) ──────
    function renderQuotaFunnel(quota, registered, attended, pctOfQuota, breakdown) {
        var el = document.getElementById('trnrepQuotaFunnelChart');
        if (!el) return;

        chartData.funnel = { quota: quota, registered: registered, attended: attended, pctOfQuota: pctOfQuota, breakdown: breakdown };

        if (charts.funnel) { charts.funnel.destroy(); charts.funnel = null; }
        el.innerHTML = '';

        var dark = isDark();
        var categories = ['Quota', 'Registered', 'Attended'];
        var counts = [quota, registered, attended];

        charts.funnel = new ApexCharts(el, {
            series: [
                { name: 'Seats', type: 'column', data: counts },
                { name: '% of Quota', type: 'line', data: pctOfQuota },
            ],
            chart: {
                height: 260, toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: ['#3B82F6', '#EC4899'],
            stroke: { width: [0, 3], curve: 'smooth' },
            markers: { size: 4, colors: ['#EC4899'], strokeColors: '#fff', strokeWidth: 2 },
            plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
            dataLabels: {
                enabled: true,
                enabledOnSeries: [0],
                formatter: function (val) { return val; },
                style: { fontSize: '11px', fontWeight: 700, colors: [dark ? '#E2E8F0' : '#1E293B'] },
            },
            legend: { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
            xaxis: { categories: categories, labels: { style: { fontSize: '12px', fontWeight: 600 } } },
            yaxis: [
                { title: { text: 'Seats', style: { fontSize: '11px' } }, labels: { style: { fontSize: '11px' } } },
                { opposite: true, min: 0, max: 100, title: { text: '% of Quota', style: { fontSize: '11px' } }, labels: { style: { fontSize: '11px' }, formatter: function (v) { return v + '%'; } } },
            ],
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: {
                shared: true,
                custom: function (opts) {
                    var category = categories[opts.dataPointIndex];
                    return companyTooltipHtml(breakdown, category, isDark());
                },
            },
        });
        charts.funnel.render();
    }

    function loadQuotaFunnel(params) {
        fetchJson(routes.quotaFunnel, params).then(function (res) {
            var d = res.data || {};
            renderQuotaFunnel(d.quota || 0, d.registered || 0, d.attended || 0, d.pct_of_quota || [0, 0, 0], d.breakdown || {});
            utils.setText('trnrepFillRate', (d.fill_rate || 0) + '%');
            utils.setText('trnrepNoShowRate', (d.no_show_rate || 0) + '%');
        }).catch(function () {});
    }

    // Per-category company breakdown tooltip, shared by the department/level/
    // top-trainings bar charts. `breakdown` is {category: {companyName: count}}.
    function companyTooltipHtml(breakdown, category, dark) {
        var cell = (breakdown && breakdown[category]) || {};
        var border = dark ? '#334155' : '#E2E8F0';
        var bg = dark ? '#0F172A' : '#FFFFFF';
        var text = dark ? '#E2E8F0' : '#1E293B';
        var muted = dark ? '#94A3B8' : '#64748B';

        var entries = Object.keys(cell).map(function (k) { return [k, cell[k]]; });
        entries.sort(function (a, b) { return b[1] - a[1]; });

        var rowsHtml = entries.map(function (e) {
            return '<div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:2px 0">'
                + '<span style="color:' + muted + '">' + utils.escHtml(e[0]) + '</span>'
                + '<span style="font-weight:700;color:' + text + '">' + e[1] + '</span></div>';
        }).join('');

        var total = entries.reduce(function (sum, e) { return sum + e[1]; }, 0);

        return '<div style="background:' + bg + ';border:1px solid ' + border + ';border-radius:8px;padding:8px 10px;font-size:11px;min-width:170px;box-shadow:0 4px 12px rgba(0,0,0,.12)">'
            + '<div style="font-weight:700;color:' + text + ';margin-bottom:4px;white-space:nowrap">' + utils.escHtml(category) + '</div>'
            + (rowsHtml || '<div style="color:' + muted + '">No data</div>')
            + '<div style="border-top:1px solid ' + border + ';margin-top:4px;padding-top:4px;display:flex;justify-content:space-between;gap:14px;font-weight:700;color:' + text + '">'
            + '<span>Total</span><span>' + total + '</span></div>'
            + '</div>';
    }

    // ── Horizontal bar chart (department / level / top trainings) ──────────────
    function renderBarChart(key, elId, categories, series, color, breakdown) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, color: color, breakdown: breakdown };

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
            tooltip: breakdown
                ? {
                    custom: function (opts) {
                        var category = categories[opts.dataPointIndex];
                        return companyTooltipHtml(breakdown, category, isDark());
                    },
                }
                : { theme: dark ? 'dark' : 'light' },
        });
        charts[key].render();
    }

    function loadByDepartment(params) {
        fetchJson(routes.byDepartment, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('dept', 'trnrepByDepartmentChart', d.categories || [], d.series || [], '#10B981', d.breakdown || {});
        }).catch(function () {});
    }

    function loadByLevel(params) {
        fetchJson(routes.byLevel, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('level', 'trnrepByLevelChart', d.categories || [], d.series || [], '#F59E0B', d.breakdown || {});
        }).catch(function () {});
    }

    function loadTopTrainings(params) {
        fetchJson(routes.topTrainings, params).then(function (res) {
            var d = res.data || {};
            renderBarChart('top', 'trnrepTopTrainingsChart', d.categories || [], d.series || [], '#8B5CF6', d.breakdown || {});
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
        var params = combinedParams();
        loadSummary(params);
        loadQuotaFunnel(params);
        loadTrend(params);
        loadTopTrainings(params);
        loadByDepartment(params);
        loadByLevel(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('trnrepTableBody')) return;
        bindTableEvents();
        initSelect2();
        bindExtraFilterEvents();
        loadFilterOptions();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            if (chartData.trend) renderTrend(chartData.trend.categories, chartData.trend.attendance, chartData.trend.satisfaction);
            if (chartData.funnel) renderQuotaFunnel(chartData.funnel.quota, chartData.funnel.registered, chartData.funnel.attended, chartData.funnel.pctOfQuota, chartData.funnel.breakdown);
            ['top', 'dept', 'level'].forEach(function (key) {
                var c = chartData[key];
                if (c) renderBarChart(key, c.elId, c.categories, c.series, c.color, c.breakdown);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
