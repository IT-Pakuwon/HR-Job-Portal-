(function () {
    'use strict';

    // Depends on gm-core.js (window.gmState, window.gmUtils, window.gmRoutes)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts = { statusChart: null };

    var xhrSummary         = null;
    var xhrByType           = null;
    var xhrStatusStrip      = null;
    var xhrStatusByCompany  = null;

    function createResponsiveChart(el, opts) {
        var chart = new ApexCharts(el, opts);
        chart.render();

        var resizeObs = new ResizeObserver(function () {
            try { chart.reflow(); } catch (e) {}
        });
        if (el.parentElement) resizeObs.observe(el.parentElement);

        return chart;
    }

    function barHeight(n, extra) {
        return Math.max(220, n * 52 + (extra || 40));
    }

    // ── Summary KPIs ───────────────────────────────────────────────────────────
    function renderSummary(d) {
        utils.setText('eventTotalContract', utils.idr(d.total_contract));
        utils.setText('eventPaidRevenue', utils.idr(d.total_paid));
        utils.setText('eventTotalCount', d.total_count != null ? Number(d.total_count).toLocaleString('id-ID') : '—');
        utils.setText('eventAvgContract', utils.idr(d.avg_contract));
    }

    function loadSummary() {
        if (xhrSummary) xhrSummary.abort();
        xhrSummary = new AbortController();
        ['eventTotalContract', 'eventPaidRevenue', 'eventTotalCount', 'eventAvgContract']
            .forEach(function (id) { utils.setText(id, '…'); });
        fetch(routes.eventSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrSummary.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderSummary(res.data || {}); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event summary:', e); });
    }

    // ── Events by Type table ──────────────────────────────────────────────────
    function renderByType(rows) {
        var body = document.getElementById('eventByTypeBody');
        if (!body) return;

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-slate-400">No data</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (r) {
            return '<tr>'
                + '<td class="py-2 pr-2 font-medium text-slate-700 dark:text-slate-200">' + utils.escHtml(r.event_type) + '</td>'
                + '<td class="py-2 pr-2 text-right tabular-nums">' + Number(r.count).toLocaleString('id-ID') + '</td>'
                + '<td class="py-2 pr-2 text-right tabular-nums font-semibold text-slate-900 dark:text-white">' + utils.idr(r.total_contract) + '</td>'
                + '<td class="py-2 text-right tabular-nums">' + utils.idr(r.avg_contract) + '</td>'
                + '</tr>';
        }).join('');
    }

    function loadByType() {
        if (xhrByType) xhrByType.abort();
        xhrByType = new AbortController();
        fetch(routes.eventByType + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrByType.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderByType(res.data || []); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event by-type:', e); });
    }

    // ── Timeline pulse strip (Paid events only) ───────────────────────────────
    function renderStatusStrip(d) {
        utils.setText('eventOngoingCount', d.ongoing != null ? Number(d.ongoing).toLocaleString('id-ID') : '—');
        utils.setText('eventUpcomingCount', d.upcoming != null ? Number(d.upcoming).toLocaleString('id-ID') : '—');
        utils.setText('eventPastCount', d.past != null ? Number(d.past).toLocaleString('id-ID') : '—');
    }

    function loadStatusStrip() {
        if (xhrStatusStrip) xhrStatusStrip.abort();
        xhrStatusStrip = new AbortController();
        fetch(routes.eventStatusStrip + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrStatusStrip.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderStatusStrip(res.data || {}); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event status-strip:', e); });
    }

    // ── By Status chart — single bar when a company is selected, stacked
    //    (one segment per company) when "All Companies" is selected ───────────
    var STATUS_COLORS  = { Booked: '#3B82F6', Confirmed: '#F59E0B', Paid: '#10B981' };
    var COMPANY_COLORS = { AW: '#8B5CF6', EP: '#3B82F6', PSA: '#10B981', GPS: '#F59E0B' };
    var COMPANY_FALLBACK = ['#06B6D4', '#EC4899', '#EF4444', '#84CC16', '#F97316', '#14B8A6'];

    function companyColor(code, idx) {
        return COMPANY_COLORS[code] || COMPANY_FALLBACK[idx % COMPANY_FALLBACK.length];
    }

    function makeStackedTooltip(data, allSites) {
        return function (opts) {
            var idx       = opts.dataPointIndex;
            var seriesIdx = opts.seriesIndex;
            var row       = data[idx] || {};
            var dark      = utils.isDark();
            var bg        = dark ? '#1e293b' : '#ffffff';
            var text      = dark ? '#e2e8f0' : '#1e293b';
            var sub       = dark ? '#94a3b8' : '#64748b';
            var divider   = dark ? '#334155' : '#e2e8f0';
            var hovClr    = opts.w.globals.colors[seriesIdx] || '#8B5CF6';

            var html = '<div style="background:' + bg + ';border-radius:10px;padding:11px 14px;min-width:220px;'
                     + 'box-shadow:0 4px 20px rgba(0,0,0,.2);font-family:Inter,sans-serif;border-left:3px solid ' + hovClr + ';">'
                     + '<div style="font-weight:700;font-size:12px;color:' + text + ';margin-bottom:2px;">' + utils.escHtml(row.status || '') + '</div>'
                     + '<div style="font-size:11px;color:' + sub + ';margin-bottom:8px;">'
                     +   Number(row.total || 0).toLocaleString('id-ID') + ' event(s) · ' + utils.idr(row.total_contract) + '</div>'
                     + '<div style="height:1px;background:' + divider + ';margin-bottom:8px;"></div>';

            allSites.forEach(function (site, si) {
                var cnt   = (row.by_site && row.by_site[site]) || 0;
                var clr   = companyColor(site, si);
                var isHov = si === seriesIdx;
                html += '<div style="display:flex;align-items:center;gap:8px;padding:2px 4px;border-radius:4px;'
                      +   'background:' + (isHov ? (dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.04)') : 'transparent') + ';">'
                      + '<span style="width:8px;height:8px;border-radius:50%;background:' + clr + ';display:inline-block;flex-shrink:0;"></span>'
                      + '<span style="font-size:11px;font-weight:' + (isHov ? '700' : '400') + ';color:' + (isHov ? text : sub) + ';flex:1;">' + utils.escHtml(site) + '</span>'
                      + '<span style="font-size:11px;font-weight:700;color:' + clr + ';">' + Number(cnt).toLocaleString('id-ID') + '</span>'
                      + '</div>';
            });

            return html + '</div>';
        };
    }

    function renderStatusChart(res) {
        var data     = (res && res.data)      || [];
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById('eventStatusChart');
        if (!el) return;
        if (charts.statusChart) { charts.statusChart.destroy(); charts.statusChart = null; }

        if (!data.length || !data.some(function (s) { return s.total > 0; })) {
            el.innerHTML = '<p class="py-10 text-center text-xs text-slate-400">No data</p>';
            return;
        }

        var dark = utils.isDark();
        var cats = data.map(function (r) { return r.status; });
        var opts;

        if (stacked && allSites.length > 1) {
            var series = allSites.map(function (site) {
                return { name: site, data: data.map(function (r) { return (r.by_site && r.by_site[site]) || 0; }) };
            });
            var colors = allSites.map(function (site, si) { return companyColor(site, si); });

            opts = {
                series: series,
                chart: {
                    type: 'bar', height: barHeight(cats.length, 80), width: '100%', stacked: true,
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 500 },
                    redrawOnWindowResize: true, redrawOnParentResize: true,
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 0, barHeight: '55%' } },
                colors: colors,
                dataLabels: { enabled: false },
                xaxis: {
                    categories: cats,
                    labels: { style: { fontSize: '11px' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '11px' } } },
                grid: {
                    borderColor: dark ? '#334155' : '#E2E8F0',
                    xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
                },
                legend: {
                    show: true, position: 'top', horizontalAlign: 'right',
                    fontSize: '11px', fontWeight: 600,
                    markers: { radius: 4, size: 7 },
                    itemMargin: { horizontal: 8, vertical: 0 },
                },
                tooltip: {
                    custom: makeStackedTooltip(data, allSites),
                    fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 },
                },
            };
        } else {
            var vals   = data.map(function (r) { return r.total; });
            var revs   = data.map(function (r) { return r.total_contract; });
            var colors2 = data.map(function (r) { return STATUS_COLORS[r.status] || '#94A3B8'; });

            opts = {
                series: [{ name: 'Events', data: vals }],
                chart: {
                    type: 'bar', height: barHeight(cats.length), width: '100%',
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 500 },
                    redrawOnWindowResize: true, redrawOnParentResize: true,
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%', distributed: true } },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] },
                    formatter: function (v) { return v; },
                },
                colors: colors2,
                xaxis: {
                    categories: cats,
                    labels: { style: { fontSize: '11px' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '11px' } } },
                grid: {
                    borderColor: dark ? '#334155' : '#E2E8F0',
                    xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
                },
                legend: { show: false },
                tooltip: {
                    theme: dark ? 'dark' : 'light',
                    y: {
                        formatter: function (v, opts) {
                            var rev = revs[opts.dataPointIndex] || 0;
                            return Number(v).toLocaleString('id-ID') + ' event(s) · ' + utils.idr(rev);
                        },
                    },
                },
            };
        }

        charts.statusChart = createResponsiveChart(el, opts);
    }

    function loadStatusByCompany() {
        if (xhrStatusByCompany) xhrStatusByCompany.abort();
        xhrStatusByCompany = new AbortController();
        fetch(routes.eventStatusByCompany + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrStatusByCompany.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderStatusChart(res); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event status-by-company:', e); });
    }

    function loadAll() {
        loadSummary();
        loadByType();
        loadStatusStrip();
        loadStatusByCompany();
    }

    document.addEventListener('gm:filter', loadAll);

    // Re-flow charts rendered while their tab was hidden.
    document.addEventListener('gm:tab-switch', function (e) {
        if (!(e.detail && e.detail.tab === 'event')) return;
        setTimeout(function () {
            Object.keys(charts).forEach(function (key) {
                if (charts[key]) {
                    try { charts[key].updateOptions({}); } catch (err) {}
                }
            });
        }, 50);
    });

    if (window.gmState && window.gmState.dateFrom) { loadAll(); }
})();
