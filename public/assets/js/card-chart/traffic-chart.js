(function () {
    'use strict';

    /* Customer movement / foot traffic chart.
       series expects a flat numeric array: [12, 45, 130, ...]
       Bars above peakThreshold (or 120% of avg when 0) are colored as "peak". */

    var PALETTES = {
        violet: { normal: '#DDD6FE', peak: '#6D28D9' },
        blue:   { normal: '#BFDBFE', peak: '#1D4ED8' },
        green:  { normal: '#A7F3D0', peak: '#047857' },
        orange: { normal: '#FDE68A', peak: '#D97706' },
        red:    { normal: '#FECACA', peak: '#B91C1C' },
        pink:   { normal: '#FBCFE8', peak: '#BE185D' },
        cyan:   { normal: '#A5F3FC', peak: '#0E7490' },
    };

    /* Showroom foot traffic sample — hourly, 8am-8pm */
    var SAMPLE = {
        categories: ['08:00','09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00','18:00','19:00','20:00'],
        data:       [12, 28, 45, 82, 130, 95, 88, 110, 148, 98, 72, 55, 30],
        peakThreshold: 90,
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function buildColors(data, threshold, pal) {
        return data.map(function(v) { return v >= threshold ? pal.peak : pal.normal; });
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var rawSeries     = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.data;
        var categories    = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height        = cfg.height || 280;
        var color         = cfg.color  || 'orange';
        var peakThreshold = cfg.peakThreshold || 0;
        var dark          = isDark();
        var pal           = PALETTES[color] || PALETTES.orange;

        /* Accept flat numeric array or already-formatted series */
        var data = (typeof rawSeries[0] === 'object' && rawSeries[0] !== null)
            ? rawSeries[0].data || rawSeries
            : rawSeries;

        var avg       = data.reduce(function(a, v) { return a + v; }, 0) / (data.length || 1);
        var threshold = peakThreshold > 0 ? peakThreshold : Math.round(avg * 1.2);
        var barColors = buildColors(data, threshold, pal);

        var chart = new ApexCharts(el, {
            series: [{ name: 'Visitors', data: data }],
            chart: {
                type: 'bar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: barColors,
            plotOptions: {
                bar: {
                    borderRadius: 5, columnWidth: '62%',
                    distributed: true,
                    dataLabels: { position: 'top' },
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function(v) { return v > 0 ? v : ''; },
                offsetY: -18,
                style: { fontSize: '10px', fontWeight: 600, colors: [dark ? '#94A3B8' : '#64748B'] },
                dropShadow: { enabled: false },
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '10px' } },
            },
            yaxis: { labels: { show: false } },
            grid: {
                borderColor: dark ? '#1E293B' : '#F1F5F9',
                strokeDashArray: 4,
                yaxis: { lines: { show: true } },
                xaxis: { lines: { show: false } },
                padding: { left: 4, right: 4, top: 12 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function(v) { return v + ' visitors'; } },
            },
            legend: { show: false },
            annotations: {
                yaxis: [{
                    y: threshold,
                    borderColor: pal.peak,
                    strokeDashArray: 5,
                    borderWidth: 2,
                    label: {
                        borderColor: pal.peak,
                        style: { color: '#fff', background: pal.peak, fontSize: '10px', fontWeight: 700 },
                        text: 'Peak Threshold: ' + threshold,
                        position: 'right',
                        offsetX: -4,
                    },
                }],
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:       { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:        { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip:     { theme: d ? 'dark' : 'light' },
                dataLabels:  { style: { colors: [d ? '#94A3B8' : '#64748B'] } },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="traffic"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
