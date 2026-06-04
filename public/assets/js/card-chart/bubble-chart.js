(function () {
    'use strict';

    var PALETTES = {
        violet: ['#8B5CF6','#A78BFA','#7C3AED','#C4B5FD'],
        blue:   ['#3B82F6','#60A5FA','#06B6D4','#93C5FD'],
        green:  ['#10B981','#34D399','#059669','#6EE7B7'],
        orange: ['#F59E0B','#FBC02D','#D97706','#FDE68A'],
        red:    ['#EF4444','#F87171','#DC2626','#FCA5A5'],
        pink:   ['#EC4899','#F472B6','#DB2777','#FBCFE8'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2','#A5F3FC'],
    };

    /* Real estate sample: x = price (M IDR), y = gross yield (%), z = floor area (m²) */
    var SAMPLE = {
        series: [
            { name: 'BSD City', data: [
                { x: 850,  y: 7.2, z: 120 },
                { x: 1200, y: 6.5, z: 180 },
                { x: 650,  y: 8.1, z: 90  },
                { x: 1800, y: 5.8, z: 240 },
            ]},
            { name: 'Alam Sutera', data: [
                { x: 950,  y: 6.8, z: 140 },
                { x: 1400, y: 6.0, z: 200 },
                { x: 700,  y: 7.5, z: 100 },
                { x: 2100, y: 5.2, z: 300 },
            ]},
            { name: 'Kelapa Gading', data: [
                { x: 1100, y: 7.8, z: 160 },
                { x: 1600, y: 6.3, z: 220 },
                { x: 800,  y: 8.4, z: 110 },
            ]},
        ],
        xLabel: 'Price (M IDR)',
        yLabel: 'Gross Yield (%)',
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'blue';
        var xLabel = cfg.xLabel || SAMPLE.xLabel;
        var yLabel = cfg.yLabel || SAMPLE.yLabel;
        var dark   = isDark();
        var colors = PALETTES[color] || PALETTES.blue;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bubble', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            fill: { opacity: 0.78 },
            xaxis: {
                title: { text: xLabel, style: { fontSize: '11px', fontWeight: 600 } },
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: {
                title: { text: yLabel, style: { fontSize: '11px', fontWeight: 600 } },
                labels: { style: { fontSize: '11px' } },
            },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                custom: function(opts) {
                    var si = opts.seriesIndex, di = opts.dataPointIndex;
                    var pt   = opts.w.config.series[si].data[di];
                    var name = opts.w.config.series[si].name;
                    return '<div style="padding:8px 12px;font-size:12px;line-height:1.6">' +
                        '<b>' + name + '</b><br>' +
                        xLabel + ': <b>' + (pt.x || 0).toLocaleString() + '</b><br>' +
                        yLabel + ': <b>' + (pt.y || 0) + '</b><br>' +
                        'Size: <b>' + (pt.z || 0) + ' m²</b></div>';
                },
            },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { radius: 6 },
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:    { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="bubble"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
