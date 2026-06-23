(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils, window.gmRoutes)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts    = { income: null, heatmap: null };
    var trendMode = 'daily';
    var nopolData = [];
    var nopolPage = 1;
    var PAGE_SIZE = 8;
    var nopolSorter = null;

    var xhrTrend = null;
    var xhrHeat  = null;
    var xhrNopol = null;
    var xhrTopTxn = null;

    // ── IDR formatter ─────────────────────────────────────────────────────────
    function idr(val) { return utils.idr(val); }

    // ── Responsive chart wrapper ──────────────────────────────────────────────
    function createResponsiveChart(el, opts) {
        var chart = new ApexCharts(el, opts);
        chart.render();
        var obs = new ResizeObserver(function () { try { chart.reflow(); } catch (e) {} });
        if (el.parentElement) obs.observe(el.parentElement);
        return chart;
    }

    // ── KPI cards ─────────────────────────────────────────────────────────────
    function updateKpi(totalIncome, totalTxn, avgIncome) {
        utils.setText('valetKpiIncome', idr(totalIncome));
        utils.setText('valetKpiTxn',    Number(totalTxn).toLocaleString('id-ID'));
        utils.setText('valetKpiAvg',    idr(avgIncome));
    }

    // ── Income Trend chart (bar income + line transactions) ───────────────────
    function renderIncomeTrend(data, mode) {
        var el = document.getElementById('valetIncomeTrendChart');
        if (!el) return;

        if (!data || !data.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for selected period</p>';
            return;
        }

        if (charts.income) { charts.income.destroy(); charts.income = null; }

        var dark   = utils.isDark();
        var cats   = data.map(function (r) { return r.label; });
        var income = data.map(function (r) { return r.income; });
        var txns   = data.map(function (r) { return r.transactions; });
        var manyPoints = cats.length > 30;

        var opts = {
            series: [
                { name: 'Income',       type: 'bar',  data: income },
                { name: 'Transactions', type: 'line', data: txns   },
            ],
            chart: {
                type: 'line', height: 270, width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            stroke:   { width: [0, 3], curve: 'smooth' },
            colors:   ['#10B981', '#3B82F6'],
            plotOptions: { bar: { columnWidth: mode === 'daily' ? '65%' : '55%', borderRadius: 3 } },
            fill:     { opacity: [0.85, 1] },
            markers:  { size: [0, manyPoints ? 0 : 4], strokeWidth: 0, hover: { size: 6 } },
            xaxis: {
                categories: cats,
                labels: {
                    style: { fontSize: '10px' },
                    rotate: manyPoints ? -45 : 0,
                    rotateAlways: manyPoints,
                    hideOverlappingLabels: true,
                },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: [
                {
                    seriesName: 'Income',
                    title: { text: 'Income (IDR)', style: { fontSize: '10px', fontWeight: 600 } },
                    labels: {
                        style: { fontSize: '10px' },
                        formatter: function (v) { return idr(v); },
                    },
                },
                {
                    seriesName: 'Transactions',
                    opposite: true,
                    title: { text: 'Transactions', style: { fontSize: '10px', fontWeight: 600 } },
                    labels: {
                        style: { fontSize: '10px' },
                        formatter: function (v) { return Math.round(v).toLocaleString('id-ID'); },
                    },
                    min: 0,
                },
            ],
            grid: {
                borderColor: dark ? '#334155' : '#E2E8F0',
                xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } },
            },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '11px', fontWeight: 600,
                markers: { radius: 4, size: 7 },
                itemMargin: { horizontal: 10 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                shared: true, intersect: false,
                y: [
                    { formatter: function (v) { return idr(v); } },
                    { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' txn'; } },
                ],
            },
        };

        charts.income = createResponsiveChart(el, opts);
    }

    // ── Peak Hour Heatmap ─────────────────────────────────────────────────────
    // BigQuery EXTRACT(DAYOFWEEK): 1=Sun, 2=Mon, ..., 7=Sat
    var DOW_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

    function buildHeatmapSeries(data) {
        var matrix = {};
        for (var d = 1; d <= 7; d++) {
            matrix[d] = {};
            for (var h = 0; h < 24; h++) matrix[d][h] = 0;
        }
        data.forEach(function (r) {
            if (r.dow >= 1 && r.dow <= 7 && r.hour >= 0 && r.hour < 24) {
                matrix[r.dow][r.hour] = r.cnt;
            }
        });

        // Order: Mon→Sun displayed top-to-bottom (ApexCharts renders last series at top)
        // so push in reverse: Sun first (appears at bottom), Sat last (appears at top)
        var dowOrder = [1, 7, 6, 5, 4, 3, 2]; // Sun, Sat, Fri, Thu, Wed, Tue, Mon
        return dowOrder.map(function (dow) {
            var pts = [];
            for (var h = 0; h < 24; h++) {
                pts.push({ x: (h < 10 ? '0' : '') + h + ':00', y: matrix[dow][h] || 0 });
            }
            return { name: DOW_LABELS[dow - 1], data: pts };
        });
    }

    function renderHeatmap(data) {
        var el = document.getElementById('valetPeakHeatmap');
        if (!el) return;

        if (!data || !data.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for selected period</p>';
            return;
        }

        if (charts.heatmap) { charts.heatmap.destroy(); charts.heatmap = null; }

        var dark   = utils.isDark();
        var series = buildHeatmapSeries(data);

        // Find max to scale color ranges
        var maxVal = 0;
        data.forEach(function (r) { if (r.cnt > maxVal) maxVal = r.cnt; });
        var hi = maxVal > 0 ? maxVal : 1;

        var opts = {
            series: series,
            chart: {
                type: 'heatmap', height: 270, width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.6,
                    radius: 2,
                    colorScale: {
                        ranges: [
                            { from: 0, to: 0,                  name: 'None',   color: dark ? '#1e293b' : '#F1F5F9' },
                            { from: 1, to: Math.ceil(hi * .25), name: 'Low',    color: '#6EE7B7' },
                            { from: Math.ceil(hi * .25) + 1, to: Math.ceil(hi * .5),  name: 'Medium', color: '#34D399' },
                            { from: Math.ceil(hi * .5)  + 1, to: Math.ceil(hi * .75), name: 'High',   color: '#10B981' },
                            { from: Math.ceil(hi * .75) + 1, to: hi + 1,        name: 'Peak',   color: '#065F46' },
                        ],
                    },
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                labels: {
                    style: { fontSize: '9px' },
                    rotate: -45, rotateAlways: true,
                },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { style: { fontSize: '11px', fontWeight: 600 } } },
            legend: {
                show: true, position: 'bottom', fontSize: '10px',
                markers: { radius: 4, size: 7 },
                itemMargin: { horizontal: 8 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' check-ins'; } },
            },
            grid: { borderColor: dark ? '#334155' : '#E2E8F0', padding: { right: 8 } },
        };

        charts.heatmap = createResponsiveChart(el, opts);
    }

    // ── Repetitive Nopol table ────────────────────────────────────────────────
    function renderNopolTable() {
        var tbody = document.getElementById('valetNopolBody');
        var badge = document.getElementById('valetNopolCount');
        if (!tbody) return;

        if (badge) badge.textContent = nopolData.length.toLocaleString('id-ID') + ' plates';

        if (!nopolData.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400 dark:text-slate-500">No repeat visitors in this period</td></tr>';
            utils.renderPagination('valetNopol', 0, 1, PAGE_SIZE, function () {});
            return;
        }

        var start = (nopolPage - 1) * PAGE_SIZE;
        var rows  = nopolData.slice(start, start + PAGE_SIZE);

        tbody.innerHTML = rows.map(function (r, i) {
            var rank = start + i + 1;
            return '<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">' +
                '<td class="px-5 py-2.5 text-xs font-bold tabular-nums text-slate-400 dark:text-slate-500">' + rank + '</td>' +
                '<td class="px-4 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-100">' + utils.escHtml(r.nopol) + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300 truncate max-w-[120px]">' + utils.escHtml(r.owner || '—') + '</td>' +
                '<td class="px-4 py-2.5 text-center">' +
                    '<span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[11px] font-bold text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">' +
                    r.visit_count + 'x</span>' +
                '</td>' +
                '<td class="px-4 py-2.5 text-right text-xs tabular-nums text-slate-700 dark:text-slate-200">' + idr(r.total_spent) + '</td>' +
            '</tr>';
        }).join('');

        utils.renderPagination('valetNopol', nopolData.length, nopolPage, PAGE_SIZE, function (p) {
            nopolPage = p;
            renderNopolTable();
        });
    }

    // ── API loaders ───────────────────────────────────────────────────────────
    function loadTrend() {
        if (xhrTrend) { xhrTrend.abort(); }
        xhrTrend = new AbortController();

        utils.setText('valetKpiIncome', '…');
        utils.setText('valetKpiTxn',    '…');
        utils.setText('valetKpiAvg',    '…');

        var params = utils.buildParams();
        var url    = routes.valetIncomeTrend + params + (params ? '&' : '?') + 'mode=' + trendMode;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTrend.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                renderIncomeTrend(res.data || [], res.mode || 'daily');
                updateKpi(res.total_income || 0, res.total_txn || 0, res.avg_income || 0);
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet income-trend:', e); });
    }

    function loadHeatmap() {
        if (xhrHeat) { xhrHeat.abort(); }
        xhrHeat = new AbortController();

        fetch(routes.valetPeakHour + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrHeat.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderHeatmap(res.data || []); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet peak-hour:', e); });
    }

    function loadNopol() {
        if (xhrNopol) { xhrNopol.abort(); }
        xhrNopol = new AbortController();

        var tbody = document.getElementById('valetNopolBody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-xs text-slate-400">Loading…</td></tr>';

        fetch(routes.valetRepetitiveNopol + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrNopol.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                nopolData = res.data || [];
                nopolPage = 1;
                if (nopolSorter) nopolSorter.reset();
                renderNopolTable();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet repetitive-nopol:', e); });
    }

    // ── Top 10 Transactions table ─────────────────────────────────────────────
    var STATUS_PAID_COLOR = {
        'PAID':    'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        'UNPAID':  'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400',
        'PARTIAL': 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
    };

    function renderTopTxnTable(data) {
        var tbody = document.getElementById('valetTopTxnBody');
        var badge = document.getElementById('valetTopTxnCount');
        if (!tbody) return;

        if (badge) badge.textContent = data.length + ' transactions';

        if (!data.length) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-5 py-8 text-center text-xs text-slate-400 dark:text-slate-500">No transactions in this period</td></tr>';
            return;
        }

        tbody.innerHTML = data.map(function (r, i) {
            var dur  = r.duration_hour + 'h ' + (r.duration_minute || 0) + 'm';
            var spCls = STATUS_PAID_COLOR[r.status_paid] || 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400';
            var hasVoucher = r.voucher_code && r.voucher_code.trim() !== '';
            return '<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">' +
                '<td class="px-5 py-2.5 text-xs font-bold tabular-nums text-slate-400 dark:text-slate-500">' + (i + 1) + '</td>' +
                '<td class="px-4 py-2.5 text-xs font-semibold text-slate-800 dark:text-slate-100">' + utils.escHtml(r.nopol || '—') + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-slate-600 dark:text-slate-300 max-w-35 truncate">' + utils.escHtml(r.owner || '—') + '</td>' +
                '<td class="px-4 py-2.5 text-xs text-slate-500 dark:text-slate-400">' + utils.escHtml(r.location || '—') + '</td>' +
                '<td class="px-4 py-2.5 text-xs tabular-nums text-slate-600 dark:text-slate-300 whitespace-nowrap">' +
                    utils.escHtml(r.checkin_date) + (r.checkin_time_str ? ' · ' + r.checkin_time_str : '') +
                '</td>' +
                '<td class="px-4 py-2.5 text-right text-xs tabular-nums text-slate-600 dark:text-slate-300">' + utils.escHtml(dur) + '</td>' +
                '<td class="px-4 py-2.5 text-right text-xs font-semibold tabular-nums text-slate-800 dark:text-slate-100">' + idr(r.total_amount) + '</td>' +
                '<td class="px-4 py-2.5 text-center">' +
                    (hasVoucher
                        ? '<span class="inline-flex items-center rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-medium text-violet-600 dark:bg-violet-500/10 dark:text-violet-400">' + utils.escHtml(r.voucher_code) + '</span>'
                        : '<span class="text-xs text-slate-300 dark:text-slate-600">—</span>') +
                '</td>' +
            '</tr>';
        }).join('');
    }

    function loadTopTxn() {
        if (xhrTopTxn) { xhrTopTxn.abort(); }
        xhrTopTxn = new AbortController();

        var tbody = document.getElementById('valetTopTxnBody');
        if (tbody) tbody.innerHTML = '<tr><td colspan="8" class="px-5 py-8 text-center text-xs text-slate-400">Loading…</td></tr>';

        fetch(routes.valetTopTransactions + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTopTxn.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderTopTxnTable(res.data || []); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet top-txn:', e); });
    }

    function loadAll() {
        loadTrend();
        loadHeatmap();
        loadNopol();
        loadTopTxn();
    }

    // ── Trend mode toggle ─────────────────────────────────────────────────────
    ['daily', 'monthly'].forEach(function (mode) {
        var btn = document.getElementById('valetTrendTab_' + mode);
        if (!btn) return;
        btn.addEventListener('click', function () {
            if (trendMode === mode) return;
            trendMode = mode;
            var btnD = document.getElementById('valetTrendTab_daily');
            var btnM = document.getElementById('valetTrendTab_monthly');
            var cls  = 'rounded-lg px-2.5 py-1 text-[10px] font-semibold transition';
            if (btnD) btnD.className = (trendMode === 'daily'   ? 'pgcard-tab-active ' : 'pgcard-tab-idle ') + cls;
            if (btnM) btnM.className = (trendMode === 'monthly' ? 'pgcard-tab-active ' : 'pgcard-tab-idle ') + cls;
            loadTrend();
        });
    });

    // ── Nopol table sort ──────────────────────────────────────────────────────
    nopolSorter = utils.bindTableSort(
        'valetNopolBody',
        function () { return nopolData; },
        function (d) { nopolData = d; },
        function () { nopolPage = 1; },
        renderNopolTable
    );

    // ── Tab switch — reflow charts after hidden→visible transition ────────────
    document.addEventListener('gm:tab-switch', function (e) {
        var tab = e.detail && e.detail.tab;
        if (tab === 'valet' || tab === 'all') {
            setTimeout(function () {
                Object.keys(charts).forEach(function (k) {
                    if (charts[k]) { try { charts[k].updateOptions({}); } catch (err) {} }
                });
            }, 50);
        }
    });

    // ── Filter change → reload ────────────────────────────────────────────────
    document.addEventListener('gm:filter', loadAll);

    if (window.gmState && window.gmState.dateFrom) { loadAll(); }
})();
