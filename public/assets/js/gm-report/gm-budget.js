(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils)
    // Routes: window.gmRoutes.summary, .byDept, .byActivity
    var routes    = window.gmRoutes || {};
    var utils     = window.gmUtils;
    var PAGE_SIZE = 15;

    var charts        = { donut: null, trend: null };
    var xhrSum        = null, xhrDept = null, xhrAct = null, xhrMonth = null;
    var deptRows      = [], deptPage = 1, deptSort = null;
    var actRows       = [], actPage  = 1, actSort  = null;
    var lastDonutData = null; // { used, reserve, remaining } — kept for resize re-render
    var donutLegendPos = null; // track current rendered legend position
    var donutSelected  = null; // { label, val } when a segment is clicked, null = show total

    // ── Donut chart (Used / Reserved / Remaining) ──────────────────────────────
    function buildDonutOpts(used, reserve, remaining, containerWidth) {
        var dark       = utils.isDark();
        var hasReserve = reserve > 0;
        var series = hasReserve
            ? [Math.max(0, Math.round(used)), Math.max(0, Math.round(reserve)), Math.max(0, Math.round(remaining))]
            : [Math.max(0, Math.round(used)), Math.max(0, Math.round(remaining))];
        var labels = hasReserve ? ['Used', 'Reserved', 'Remaining'] : ['Used', 'Remaining'];
        var colors = hasReserve ? ['#EF4444', '#F59E0B', '#10B981'] : ['#EF4444', '#10B981'];

        // Legend goes to bottom when there isn't enough horizontal room;
        // right is the default for wide cards (> 460 px).
        var wide       = containerWidth > 460;
        var legendPos  = wide ? 'right' : 'bottom';
        var chartH     = wide ? 210 : (containerWidth < 340 ? 300 : 260);

        return {
            series : series,
            labels : labels,
            chart: {
                type: 'donut', height: chartH,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var pts    = cfg.selectedDataPoints;
                        var hasSel = pts && pts[0] && pts[0].length > 0;
                        donutSelected = hasSel ? {
                            label: cfg.w.globals.labels[pts[0][0]],
                            val:   cfg.w.globals.series[pts[0][0]],
                        } : null;
                        ctx.updateOptions({
                            plotOptions: { pie: { donut: { labels: { total: {
                                label: donutSelected ? donutSelected.label : 'Total',
                                formatter: function (w) {
                                    return donutSelected
                                        ? utils.idr(donutSelected.val)
                                        : utils.idr(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0));
                                },
                            }}}}}
                        }, false, false);
                    },
                },
            },
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: wide ? '70%' : '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true, label: 'Total',
                                fontSize: '12px', fontWeight: 600,
                                color: dark ? '#CBD5E1' : '#64748B',
                                formatter: function (w) {
                                    return utils.idr(
                                        w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)
                                    );
                                },
                            },
                            name: {
                                show: true,
                                fontSize: '12px', fontWeight: 600,
                                color: dark ? '#CBD5E1' : '#64748B',
                            },
                            value: {
                                fontSize: wide ? '16px' : '14px', fontWeight: 700,
                                color: dark ? '#F1F5F9' : '#0F172A',
                                formatter: function (v) { return utils.idr(parseFloat(v)); },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                fixed: wide
                    ? { enabled: true, position: 'topLeft', offsetX: 10, offsetY: 10 }
                    : { enabled: false },
                y: {
                    formatter: function (v, o) {
                        if (!o || !o.w || !o.w.globals) return utils.idr(v);
                        var s     = o.w.globals.series || [];
                        var total = s.reduce(function (a, b) { return a + (parseFloat(b) || 0); }, 0);
                        var pct   = total > 0 ? (v / total * 100).toFixed(1) : '0.0';
                        return utils.idr(v) + ' (' + pct + '%)';
                    },
                },
            },
            legend: {
                show: true,
                position: legendPos,
                horizontalAlign: wide ? 'right' : 'center',
                fontSize: '12px',
                markers: { radius: 6 },
                itemMargin: { horizontal: wide ? 8 : 10, vertical: 4 },
                formatter: function (seriesName, o) {
                    if (!o || !o.w || !o.w.globals) return seriesName;
                    var s     = o.w.globals.series || [];
                    var total = s.reduce(function (a, b) { return a + (parseFloat(b) || 0); }, 0);
                    var val   = parseFloat(s[o.seriesIndex]) || 0;
                    var pct   = total > 0 ? (val / total * 100).toFixed(1) : '0.0';
                    return seriesName + ' <b>' + pct + '%</b>';
                },
            },
        };
    }

    function renderDonut(used, reserve, remaining) {
        var el = document.getElementById('gmBudgetDonut');
        if (!el) return;

        lastDonutData = { used: used, reserve: reserve, remaining: remaining };

        var containerW = el.offsetWidth || 400;
        var wide       = containerW > 460;
        var legendPos  = wide ? 'right' : 'bottom';
        var opts       = buildDonutOpts(used, reserve, remaining, containerW);

        // Destroy & recreate when legend position changes (updateOptions can't switch sides)
        if (charts.donut && donutLegendPos !== legendPos) {
            charts.donut.destroy();
            charts.donut = null;
        }
        donutLegendPos = legendPos;

        if (charts.donut) { charts.donut.updateOptions(opts); return; }
        charts.donut = new ApexCharts(el, opts);
        charts.donut.render();
    }

    // ── Inline % bar helper ────────────────────────────────────────────────────
    function pctBar(pct) {
        var w     = Math.min(100, pct);
        var color = pct >= 80 ? '#EF4444' : (pct >= 60 ? '#F59E0B' : '#10B981');
        var cls   = pct >= 80 ? 'text-red-600 dark:text-red-400 font-bold'
                  : pct >= 60 ? 'text-amber-600 dark:text-amber-400 font-semibold'
                  :             'text-emerald-600 dark:text-emerald-400';
        return '<div class="flex items-center gap-2">'
            + '<div class="h-1.5 w-20 shrink-0 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">'
            + '<div class="h-full rounded-full" style="width:' + w + '%;background:' + color + '"></div>'
            + '</div>'
            + '<span class="tabular-nums ' + cls + '">' + pct.toFixed(1) + '%</span>'
            + '</div>';
    }

    function setTrend(id, pct) {
        var el = document.getElementById(id);
        if (!el) return;
        el.className = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold '
            + (pct >= 0
                ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400');
        el.innerHTML = (pct >= 0 ? '&#8593;' : '&#8595;') + ' ' + Math.abs(pct).toFixed(1) + '%';
    }

    // ── Department table ───────────────────────────────────────────────────────
    function renderDeptTable() {
        var tbody = document.getElementById('gmDeptTableBody');
        if (!tbody) return;

        utils.setText('gmDeptCount', deptRows.length + ' dept' + (deptRows.length !== 1 ? 's' : ''));

        if (!deptRows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
            var p = document.getElementById('gmDeptPagination');
            if (p) p.classList.add('hidden');
            return;
        }

        var totalPages = Math.ceil(deptRows.length / PAGE_SIZE);
        deptPage = Math.min(deptPage, totalPages);
        var slice = deptRows.slice((deptPage - 1) * PAGE_SIZE, deptPage * PAGE_SIZE);

        tbody.innerHTML = slice.map(function (r) {
            var pct = parseFloat(r.used_pct || 0);
            return '<tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/30">'
                + '<td class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-200">' + utils.escHtml(r.department_fin_id) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">' + utils.idr(r.total_final) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-amber-600 dark:text-amber-400">' + utils.idr(r.total_reserve) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-emerald-600 dark:text-emerald-400">' + utils.idr(r.total_remaining) + '</td>'
                + '<td class="px-4 py-3">' + pctBar(pct) + '</td>'
                + '</tr>';
        }).join('');

        utils.renderPagination('gmDept', deptRows.length, deptPage, PAGE_SIZE, function (p) {
            deptPage = p; renderDeptTable();
        });
    }

    // ── Activity table ─────────────────────────────────────────────────────────
    function renderActTable() {
        var tbody = document.getElementById('gmActTableBody');
        if (!tbody) return;

        utils.setText('gmActCount', actRows.length + ' activit' + (actRows.length !== 1 ? 'ies' : 'y'));

        if (!actRows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
            var p = document.getElementById('gmActPagination');
            if (p) p.classList.add('hidden');
            return;
        }

        var totalPages = Math.ceil(actRows.length / PAGE_SIZE);
        actPage = Math.min(actPage, totalPages);
        var slice = actRows.slice((actPage - 1) * PAGE_SIZE, actPage * PAGE_SIZE);

        tbody.innerHTML = slice.map(function (r) {
            var pct = parseFloat(r.used_pct || 0);
            return '<tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/30">'
                + '<td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-200">' + utils.escHtml(r.activity_descr || r.activity_id || '—') + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">' + utils.idr(r.total_final) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-amber-600 dark:text-amber-400">' + utils.idr(r.total_reserve) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-emerald-600 dark:text-emerald-400">' + utils.idr(r.total_remaining) + '</td>'
                + '<td class="px-4 py-3">' + pctBar(pct) + '</td>'
                + '</tr>';
        }).join('');

        utils.renderPagination('gmAct', actRows.length, actPage, PAGE_SIZE, function (p) {
            actPage = p; renderActTable();
        });
    }

    // ── API loaders ────────────────────────────────────────────────────────────
    function loadSummary() {
        if (xhrSum) xhrSum.abort();
        xhrSum = new AbortController();
        ['gmTotalBudget', 'gmTotalRemaining'].forEach(function (id) { utils.setText(id, '…'); });

        fetch(routes.summary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrSum.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var d   = res.data || {};
                var pct = parseFloat(d.utilization_pct) || 0;

                utils.setText('gmTotalBudget',    utils.idr(d.total_budget));
                utils.setText('gmTotalRemaining', utils.idr(d.total_remaining));
                utils.setText('gmUtilPct',        pct.toFixed(1) + '%');

                var bar = document.getElementById('gmUtilBar');
                if (bar) {
                    bar.style.width      = Math.min(100, Math.max(0, pct)) + '%';
                    bar.style.background = pct >= 80
                        ? 'linear-gradient(to right,#F59E0B,#EF4444)'
                        : pct >= 60
                        ? 'linear-gradient(to right,#10B981,#F59E0B)'
                        : 'linear-gradient(to right,#10B981,#06B6D4)';
                }

                setTrend('gmUtilTrend', pct);
                renderDonut(d.total_used, d.total_reserve, d.total_remaining);
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('budget summary:', e); });
    }

    function loadByDept() {
        if (xhrDept) xhrDept.abort();
        xhrDept = new AbortController();
        utils.setText('gmDeptCount', '…');

        fetch(routes.byDept + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrDept.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                deptRows = res.data || []; deptPage = 1;
                if (deptSort) deptSort.reset();
                renderDeptTable();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('budget by dept:', e); });
    }

    function loadByActivity() {
        if (xhrAct) xhrAct.abort();
        xhrAct = new AbortController();
        utils.setText('gmActCount', '…');

        fetch(routes.byActivity + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrAct.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                actRows = res.data || []; actPage = 1;
                if (actSort) actSort.reset();
                renderActTable();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('budget by activity:', e); });
    }

    // ── Monthly trend chart (cumulative used + monthly bars) ──────────────────
    function renderTrendChart(data, totalBudget) {
        var dark       = utils.isDark();
        var categories = data.map(function (d) { return d.month; });
        var cumulative = data.map(function (d) { return d.cumulative; });
        var monthly    = data.map(function (d) { return d.used; });

        var opts = {
            series: [
                { name: 'Cumulative Used', type: 'area',   data: cumulative },
                { name: 'Monthly Used',    type: 'column', data: monthly },
            ],
            chart: {
                type: 'line', height: 210,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 800 },
                zoom: { enabled: false },
            },
            colors: ['#8B5CF6', '#C4B5FD'],
            fill: {
                type: ['gradient', 'solid'],
                gradient: {
                    type: 'vertical',
                    gradientToColors: ['#06B6D4'],
                    shadeIntensity: 1,
                    opacityFrom: 0.55,
                    opacityTo: 0.02,
                    stops: [0, 95],
                },
                opacity: [1, 0.75],
            },
            stroke: { curve: 'smooth', width: [2.5, 0] },
            markers: {
                size: [0, 0],
                hover: { size: 5 },
                colors: ['#8B5CF6'],
                strokeWidth: 0,
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: false },
                axisTicks:  { show: false },
                tickAmount: 5,
                labels: { style: { fontSize: '10px', fontWeight: 600 } },
            },
            yaxis: [
                {
                    seriesName: 'Cumulative Used',
                    tickAmount: 4,
                    labels: {
                        formatter: function (v) { return utils.idr(v); },
                        style: { fontSize: '9px' },
                    },
                },
                {
                    seriesName: 'Monthly Used',
                    opposite: true,
                    show: false,
                },
            ],
            annotations: totalBudget > 0 ? {
                yaxis: [{
                    y           : totalBudget,
                    yAxisIndex  : 0,
                    borderColor : '#EF4444',
                    borderWidth : 1.5,
                    strokeDashArray: 5,
                    label: {
                        text    : 'Budget · ' + utils.idr(totalBudget),
                        position: 'right',
                        offsetX : -6,
                        style   : {
                            color: '#EF4444', fontSize: '9px', fontWeight: 700,
                            background: dark ? '#1e293b' : '#fff',
                            border: '0', padding: { top: 2, bottom: 2, left: 4, right: 4 },
                        },
                    },
                }],
            } : {},
            grid: {
                borderColor: dark ? '#334155' : '#F1F5F9',
                strokeDashArray: 4,
                padding: { left: 2, right: 12, top: 0, bottom: 0 },
                xaxis: { lines: { show: false } },
                yaxis: { lines: { show: true } },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                shared: true,
                intersect: false,
                y: { formatter: function (v) { return utils.idr(v); } },
            },
            dataLabels: { enabled: false },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '11px', markers: { radius: 4 },
                itemMargin: { horizontal: 10 },
            },
            plotOptions: {
                bar: { columnWidth: '50%', borderRadius: 3 },
            },
            responsive: [
                {
                    // narrow sidebar column (xl left-col) or phone portrait
                    breakpoint: 480,
                    options: {
                        chart: { height: 190 },
                        xaxis: { tickAmount: 4, labels: { style: { fontSize: '8px' } } },
                        legend: {
                            show: true, position: 'bottom', horizontalAlign: 'center',
                            fontSize: '10px', itemMargin: { horizontal: 6 },
                        },
                        grid: { padding: { left: 0, right: 4 } },
                        yaxis: [
                            { tickAmount: 3, labels: { style: { fontSize: '8px' }, formatter: function (v) { return utils.idr(v); } } },
                            { show: false },
                        ],
                    },
                },
                {
                    // small tablet / large phone landscape
                    breakpoint: 768,
                    options: {
                        chart: { height: 210 },
                        xaxis: { tickAmount: 5 },
                        legend: { position: 'top', horizontalAlign: 'center', fontSize: '10px' },
                    },
                },
            ],
        };

        var el = document.getElementById('gmMonthlyTrend');
        if (!el) return;
        if (charts.trend) { charts.trend.updateOptions(opts); return; }
        charts.trend = new ApexCharts(el, opts);
        charts.trend.render();
    }

    function loadByMonth() {
        if (xhrMonth) xhrMonth.abort();
        xhrMonth = new AbortController();

        fetch(routes.byMonth + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrMonth.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                utils.setText('gmTrendYear', res.year || '');
                renderTrendChart(res.data || [], res.total_budget || 0);
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('budget by month:', e); });
    }

    // ── Dark-mode watcher (budget charts only) ────────────────────────────────
    function watchDarkMode() {
        new MutationObserver(function () {
            var dark = utils.isDark();
            var themeOpts = {
                chart:   { foreColor: dark ? '#94A3B8' : '#64748B' },
                tooltip: { theme: dark ? 'dark' : 'light' },
                grid:    { borderColor: dark ? '#334155' : '#F1F5F9' },
            };
            if (charts.donut) charts.donut.updateOptions({
                chart:   themeOpts.chart,
                tooltip: themeOpts.tooltip,
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                total: { color: dark ? '#CBD5E1' : '#64748B' },
                                name:  { color: dark ? '#CBD5E1' : '#64748B' },
                                value: { color: dark ? '#F1F5F9' : '#0F172A' },
                            },
                        },
                    },
                },
            });
            if (charts.trend) charts.trend.updateOptions(themeOpts);
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Donut resize watcher — re-renders when the grid column changes width ──
    // Uses ResizeObserver (supported on all modern browsers). Falls back to a
    // debounced window resize listener for older environments.
    function watchDonutResize() {
        var el = document.getElementById('gmBudgetDonut');
        if (!el) return;

        var debounceTimer = null;
        function onResize() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                if (lastDonutData) {
                    renderDonut(lastDonutData.used, lastDonutData.reserve, lastDonutData.remaining);
                }
            }, 150);
        }

        if (window.ResizeObserver) {
            new ResizeObserver(onResize).observe(el);
        } else {
            window.addEventListener('resize', onResize);
        }
    }

    // ── Listen for filter change — fired by gm-filter.js ──────────────────────
    // Registered immediately (before DOMContentLoaded) so it's always ready
    // before the first gmDispatchFilter() call from gm-filter's init.
    document.addEventListener('gm:filter', function () {
        deptPage = 1; actPage = 1;
        loadSummary();
        loadByDept();
        loadByActivity();
        loadByMonth();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        watchDarkMode();
        watchDonutResize();

        deptSort = utils.bindTableSort(
            'gmDeptTableBody',
            function () { return deptRows; },
            function (r) { deptRows = r; },
            function () { deptPage = 1; },
            renderDeptTable
        );
        actSort = utils.bindTableSort(
            'gmActTableBody',
            function () { return actRows; },
            function (r) { actRows = r; },
            function () { actPage = 1; },
            renderActTable
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
