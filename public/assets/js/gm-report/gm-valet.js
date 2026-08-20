(function () {
    'use strict';

    // Depends on gm-core.js (window.gmState, window.gmUtils, window.gmRoutes)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts = { peakHours: null, redemptionDonut: null };

    var xhrKpi        = null;
    var xhrRedemption = null;
    var xhrPeakHours  = null;

    function createResponsiveChart(el, opts) {
        var chart = new ApexCharts(el, opts);
        chart.render();

        var resizeObs = new ResizeObserver(function () {
            try { chart.reflow(); } catch (e) {}
        });
        if (el.parentElement) resizeObs.observe(el.parentElement);

        return chart;
    }

    // ── KPI summary cards ─────────────────────────────────────────────────────
    function renderKpiSummary(d) {
        utils.setText('valetTotalValet', d.total_valet != null ? Number(d.total_valet).toLocaleString('id-ID') : '—');
        utils.setText('valetIncomeService', utils.idr(d.total_income_service));
        utils.setText('valetIncomeParking', utils.idr(d.total_income_parking));

        var avgMin = parseFloat(d.avg_duration_minutes) || 0;
        if (avgMin > 0) {
            var hrs = Math.floor(avgMin / 60);
            var mins = Math.round(avgMin % 60);
            utils.setText('valetAvgDuration', hrs > 0 ? (hrs + 'h ' + mins + 'm') : (mins + 'm'));
        } else {
            utils.setText('valetAvgDuration', '—');
        }
        utils.setText('valetTurnover', (d.daily_avg_turnover != null ? d.daily_avg_turnover : '—') + '/day turnover');

        utils.setText('valetTotalMember', d.total_member != null ? Number(d.total_member).toLocaleString('id-ID') : '—');

        // Total Member card — only meaningful at Gandaria City (AW), where the
        // free-parking member benefit exists. Show it for that scope, or when
        // no company filter is applied (the aggregate still includes AW).
        var memberCard = document.getElementById('gmValetMemberCard');
        if (memberCard) {
            var cpnyId = window.gmState ? window.gmState.cpnyId : '';
            var show = !cpnyId || cpnyId === 'AW';
            memberCard.classList.toggle('hidden', !show);
            memberCard.classList.toggle('flex', show);

            // Grid is normally 5 columns (4 cards + member card). Without the
            // member card, collapse to an even 4-column layout so the empty
            // 5th cell doesn't show through as a gray box.
            var grid = document.getElementById('gmValetKpiGrid');
            if (grid) grid.classList.toggle('gm-kpi-collapsed', !show);
        }
    }

    function loadKpiSummary() {
        if (xhrKpi) xhrKpi.abort();
        xhrKpi = new AbortController();
        ['valetTotalValet', 'valetIncomeService', 'valetIncomeParking', 'valetAvgDuration', 'valetTotalMember']
            .forEach(function (id) { utils.setText(id, '…'); });
        fetch(routes.valetKpiSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrKpi.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderKpiSummary(res.data || {}); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet kpi-summary:', e); });
    }

    // ── Voucher redemption ────────────────────────────────────────────────────
    var STATUS_COLORS = { MEMBER: '#EC4899', None: '#94A3B8' };
    var STATUS_LABELS = { MEMBER: 'Member Hotel', None: 'Regular Customer' };
    var STATUS_FALLBACK = ['#8B5CF6', '#3B82F6', '#F59E0B', '#10B981', '#06B6D4'];

    function renderRedemption(d) {
        utils.setText('valetRedemptionRate', (d.redemption_rate != null ? d.redemption_rate : 0) + '%');

        var el = document.getElementById('valetRedemptionDonut');
        if (!el) return;
        if (charts.redemptionDonut) { charts.redemptionDonut.destroy(); charts.redemptionDonut = null; }

        var byStatus = d.by_status || [];
        if (!byStatus.length) {
            el.innerHTML = '<p class="py-10 text-center text-xs text-slate-400">No data</p>';
            return;
        }

        var dark   = utils.isDark();
        var labels = byStatus.map(function (s) { return STATUS_LABELS[s.status] || s.status; });
        var series = byStatus.map(function (s) { return s.count; });
        var colors = byStatus.map(function (s, i) { return STATUS_COLORS[s.status] || STATUS_FALLBACK[i % STATUS_FALLBACK.length]; });

        charts.redemptionDonut = createResponsiveChart(el, {
            series: series,
            labels: labels,
            chart: {
                type: 'donut', height: 180,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: colors,
            plotOptions: { pie: { donut: { size: '68%' } } },
            dataLabels: { enabled: false },
            legend: {
                show: true, position: 'bottom', fontSize: '11px', fontWeight: 600,
                markers: { radius: 4, size: 7 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' valet(s)'; } },
            },
        });
    }

    function loadRedemption() {
        if (xhrRedemption) xhrRedemption.abort();
        xhrRedemption = new AbortController();
        fetch(routes.valetVoucherRedemption + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrRedemption.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderRedemption(res.data || {}); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet voucher-redemption:', e); });
    }

    // ── Peak check-in hours ────────────────────────────────────────────────────
    function renderPeakHours(hours) {
        var el = document.getElementById('valetPeakHoursChart');
        if (!el) return;
        if (charts.peakHours) { charts.peakHours.destroy(); charts.peakHours = null; }

        var dark = utils.isDark();
        var cats = hours.map(function (_, h) { return (h < 10 ? '0' : '') + h + ':00'; });

        charts.peakHours = createResponsiveChart(el, {
            series: [{ name: 'Check-ins', data: hours }],
            chart: {
                type: 'bar', height: 260, width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            plotOptions: { bar: { borderRadius: 3, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            colors: ['#8B5CF6'],
            xaxis: {
                categories: cats,
                labels: { style: { fontSize: '10px' }, rotate: -45 },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: {
                labels: { style: { fontSize: '11px' }, formatter: function (v) { return Math.round(v).toLocaleString('id-ID'); } },
            },
            grid: {
                borderColor: dark ? '#334155' : '#E2E8F0',
                xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' check-in(s)'; } },
            },
        });
    }

    function loadPeakHours() {
        if (xhrPeakHours) xhrPeakHours.abort();
        xhrPeakHours = new AbortController();
        fetch(routes.valetPeakHours + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrPeakHours.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderPeakHours(res.data || []); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('valet peak-hours:', e); });
    }

    function loadAll() {
        loadKpiSummary();
        loadRedemption();
        loadPeakHours();
    }

    document.addEventListener('gm:filter', loadAll);

    // Re-flow charts rendered while their tab was hidden.
    document.addEventListener('gm:tab-switch', function (e) {
        if (!(e.detail && e.detail.tab === 'valet')) return;
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
