(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils)
    // Routes: window.gmRoutes.summary, .byDept, .byActivity
    var routes    = window.gmRoutes || {};
    var utils     = window.gmUtils;
    var PAGE_SIZE = 7;

    var charts   = { donut: null };
    var xhrSum   = null, xhrDept = null, xhrAct = null;
    var deptRows = [], deptPage = 1, deptSort = null;
    var actRows  = [], actPage  = 1, actSort  = null;

    // ── Donut chart (Used / Reserved / Remaining) ──────────────────────────────
    // function renderDonut(used, reserve, remaining) {
    function renderDonut(used, remaining) {
        var dark = utils.isDark();
        var opts = {
            series: [
                Math.max(0, Math.round(used)),
                // Math.max(0, Math.round(reserve)),
                Math.max(0, Math.round(remaining)),
            ],
            labels: ['Used', 'Remaining'],
            chart: {
                type: 'donut', height: 210,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            colors: ['#EF4444', '#10B981'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true, label: 'Total',
                                fontSize: '12px', fontWeight: 600,
                                color: dark ? '#94A3B8' : '#64748B',
                                formatter: function (w) {
                                    return utils.idr(
                                        w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)
                                    );
                                },
                            },
                            value: {
                                fontSize: '16px', fontWeight: 700,
                                color: dark ? '#F8FAFC' : '#0F172A',
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
                y: { formatter: function (v) { return utils.idr(v); } },
            },
            legend: {
                show: true, position: 'right', fontSize: '12px',
                markers: { radius: 6 }, itemMargin: { horizontal: 8, vertical: 4 },
            },
        };
        var el = document.getElementById('gmBudgetDonut');
        if (!el) return;
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
            tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
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
            tbody.innerHTML = '<tr><td colspan="4" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
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
                // renderDonut(d.total_used, d.total_reserve, d.total_remaining);
                renderDonut(d.total_used, d.total_remaining);
                utils.setText('gmRefreshTime', new Date().toLocaleTimeString());
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

    // ── Dark-mode watcher (budget charts only) ────────────────────────────────
    function watchDarkMode() {
        new MutationObserver(function () {
            var dark = utils.isDark();
            if (charts.donut) {
                charts.donut.updateOptions({
                    chart:   { foreColor: dark ? '#94A3B8' : '#64748B' },
                    tooltip: { theme: dark ? 'dark' : 'light' },
                });
            }
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Listen for filter change — fired by gm-filter.js ──────────────────────
    // Registered immediately (before DOMContentLoaded) so it's always ready
    // before the first gmDispatchFilter() call from gm-filter's init.
    document.addEventListener('gm:filter', function () {
        deptPage = 1; actPage = 1;
        loadSummary();
        loadByDept();
        loadByActivity();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        watchDarkMode();

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
