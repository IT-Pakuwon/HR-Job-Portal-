(function () {
    'use strict';

    var PALETTE = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8'],
        green:  ['#10B981','#34D399','#059669'],
        orange: ['#F59E0B','#FBC02D','#D97706'],
        red:    ['#EF4444','#F87171','#DC2626'],
        pink:   ['#EC4899','#F472B6','#DB2777'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2'],
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899'],
    };

    var SAMPLE = {
        series: [
            { name: 'Actual',   data: [80, 92, 67, 74, 88, 95, 61] },
            { name: 'Target',   data: [85, 90, 75, 80, 90, 100, 70] },
        ],
        categories: ['Marketing','Operations','Finance','HR','IT','Sales','Legal'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Custom shared tooltip for multi-series bar charts (stacked or grouped) —
    // shows every series' value for the hovered category in one card instead
    // of just the single segment under the cursor, styled to match the app's
    // rounded-card look rather than ApexCharts' plain default tooltip.
    function buildSharedTooltip(opts, dark, stacked) {
        var w = opts.w;
        var idx = opts.dataPointIndex;
        var category = (w.globals.labels && w.globals.labels[idx] != null) ? w.globals.labels[idx] : '';

        var bg = dark ? '#0F172A' : '#FFFFFF';
        var border = dark ? '#1E293B' : '#E2E8F0';
        var headText = dark ? '#64748B' : '#94A3B8';
        var labelText = dark ? '#CBD5E1' : '#475569';
        var valueText = dark ? '#F1F5F9' : '#1E293B';

        var total = 0;
        var rows = w.globals.seriesNames.map(function (name, i) {
            var val = (w.globals.series[i] && w.globals.series[i][idx]) || 0;
            total += val;
            var color = w.globals.colors[i];
            return '<div style="display:flex;align-items:center;justify-content:space-between;gap:18px;padding:3px 0;">'
                + '<span style="display:flex;align-items:center;gap:6px;font-size:12px;color:' + labelText + ';white-space:nowrap;">'
                + '<span style="width:8px;height:8px;min-width:8px;border-radius:9999px;background:' + color + ';display:inline-block;"></span>'
                + esc(name) + '</span>'
                + '<span style="font-size:12px;font-weight:700;color:' + valueText + ';">' + (+val).toLocaleString() + '</span>'
                + '</div>';
        }).join('');

        var totalRow = (stacked && w.globals.seriesNames.length > 1)
            ? '<div style="display:flex;align-items:center;justify-content:space-between;gap:18px;margin-top:4px;padding-top:6px;border-top:1px solid ' + border + ';">'
                + '<span style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:' + headText + ';">Total</span>'
                + '<span style="font-size:12px;font-weight:700;color:' + valueText + ';">' + total.toLocaleString() + '</span>'
                + '</div>'
            : '';

        return '<div style="min-width:150px;padding:10px 12px;border-radius:12px;background:' + bg + ';border:1px solid ' + border + ';box-shadow:0 10px 25px -5px rgba(0,0,0,.18);font-family:Inter, sans-serif;">'
            + '<div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:' + headText + ';margin-bottom:6px;">' + esc(category) + '</div>'
            + rows + totalRow
            + '</div>';
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height     = cfg.height  || 300;
        var color      = cfg.color   || 'blue';
        var stacked    = cfg.stacked || false;
        var showLegend = cfg.showLegend !== false;
        var legendPosition = cfg.legendPosition || 'top';
        var horizontal = cfg.horizontal !== false;
        var dark       = isDark();
        var colors     = series.length > 1 ? PALETTE.multi : (PALETTE[color] || PALETTE.blue);

        // The numeric value scale sits on whichever axis isn't showing the
        // category labels — the x-axis for vertical (column) bars, the
        // y-axis for horizontal ones.
        var valueAxisLabels = { style: { fontSize: '11px' }, formatter: function(v) { return (+v).toLocaleString(); } };
        var categoryAxisLabels = { style: { fontSize: '11px' } };

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bar', height: height, stacked: stacked,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            plotOptions: {
                bar: Object.assign(
                    { horizontal: horizontal, borderRadius: 5, borderRadiusApplication: 'end' },
                    horizontal ? { barHeight: '60%' } : { columnWidth: '55%' }
                ),
            },
            dataLabels: {
                enabled: true,
                formatter: function(val) { return parseInt(val).toLocaleString(); },
                style: { fontSize: '11px', fontWeight: 600, colors: [dark ? '#F1F5F9' : '#1E293B'] },
                background: { enabled: true, foreColor: dark ? '#0F172A' : '#fff', opacity: 0.85, borderWidth: 0, padding: 4 },
                dropShadow: { enabled: false },
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: horizontal ? valueAxisLabels : categoryAxisLabels,
            },
            yaxis: { labels: horizontal ? categoryAxisLabels : valueAxisLabels },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: series.length > 1
                ? {
                    shared: true, intersect: false, followCursor: true,
                    custom: function (opts) { return buildSharedTooltip(opts, dark, stacked); },
                }
                : { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v.toLocaleString(); } } },
            legend: {
                show: showLegend && series.length > 1, position: legendPosition,
                horizontalAlign: legendPosition === 'bottom' ? 'center' : 'right',
                fontSize: '12px', markers: { shape: 'circle', size: 6 },
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart: { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:  { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip: series.length > 1
                    ? { custom: function (opts) { return buildSharedTooltip(opts, d, stacked); } }
                    : { theme: d ? 'dark' : 'light' },
                dataLabels: {
                    style: { colors: [d ? '#F1F5F9' : '#1E293B'] },
                    background: { foreColor: d ? '#0F172A' : '#fff' },
                },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="bar"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
