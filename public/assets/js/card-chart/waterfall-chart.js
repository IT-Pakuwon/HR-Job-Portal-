(function () {
    'use strict';

    /* Waterfall simulated via ApexCharts rangeBar (floating bars).
       series expects: [{ name: 'Flow', data: [number, ...] }]
       categories: label per period.
       Positive values = gain (green), negative = loss (red), last = total (blue). */

    var SAMPLE_DATA = [
        { x: 'Start',     y: [0, 200],  fill: '#10B981' },
        { x: 'Q1 Sales',  y: [200, 380], fill: '#10B981' },
        { x: 'Q1 Cost',   y: [280, 380], fill: '#EF4444' },
        { x: 'Q2 Sales',  y: [280, 520], fill: '#10B981' },
        { x: 'Q2 Cost',   y: [410, 520], fill: '#EF4444' },
        { x: 'Q3 Sales',  y: [410, 610], fill: '#10B981' },
        { x: 'Net Total', y: [0, 610],   fill: '#3B82F6' },
    ];

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function buildWaterfallData(rawSeries, rawCategories) {
        if (!rawSeries || !rawSeries.length) return SAMPLE_DATA;
        var vals = rawSeries[0].data || [];
        var cats = rawCategories || [];
        var data = []; var running = 0;
        vals.forEach(function(v, i) {
            var label  = cats[i] || ('P' + (i+1));
            var isLast = i === vals.length - 1;
            var fill   = isLast ? '#3B82F6' : (v >= 0 ? '#10B981' : '#EF4444');
            var y0     = isLast ? 0 : running;
            var y1     = isLast ? running + v : running + v;
            data.push({ x: label, y: [Math.min(y0, y1), Math.max(y0, y1)], fillColor: fill });
            if (!isLast) running += v;
        });
        return data;
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var height = cfg.height || 300;
        var dark   = isDark();
        var wData  = buildWaterfallData(cfg.series, cfg.categories);

        var chart = new ApexCharts(el, {
            series: [{ name: 'Flow', data: wData }],
            chart: {
                type: 'rangeBar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            plotOptions: {
                bar: { horizontal: false, columnWidth: '60%', borderRadius: 4 },
            },
            dataLabels: { enabled: false },
            xaxis: {
                type: 'category',
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: { labels: { style: { fontSize: '11px' }, formatter: function(v) { return v.toLocaleString(); } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v ? v.toLocaleString() : ''; } } },
            legend: { show: false },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart: { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:  { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="waterfall"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
