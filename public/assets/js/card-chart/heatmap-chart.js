(function () {
    'use strict';

    var PALETTE = {
        violet: { from: '#C4B5FD', to: '#7C3AED' },
        blue:   { from: '#BAE6FD', to: '#1D4ED8' },
        green:  { from: '#A7F3D0', to: '#047857' },
        orange: { from: '#FDE68A', to: '#D97706' },
        red:    { from: '#FECACA', to: '#B91C1C' },
        pink:   { from: '#FBCFE8', to: '#BE185D' },
        cyan:   { from: '#A5F3FC', to: '#0E7490' },
    };

    var SAMPLE = {
        series: [
            { name: 'Mon', data: [10, 41, 35, 51, 49, 62, 69, 91, 148, 35, 51, 49] },
            { name: 'Tue', data: [56, 70, 48, 32, 58, 74, 30, 60, 50, 70, 48, 32] },
            { name: 'Wed', data: [20, 33, 55, 40, 72, 85, 60, 45, 20, 33, 55, 40] },
            { name: 'Thu', data: [30, 50, 80, 65, 90, 45, 55, 70, 30, 50, 80, 65] },
            { name: 'Fri', data: [44, 28, 60, 48, 38, 52, 78, 32, 44, 28, 60, 48] },
        ],
        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height     = cfg.height  || 280;
        var color      = cfg.color   || 'blue';
        var dark       = isDark();
        var pal        = PALETTE[color] || PALETTE.blue;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'heatmap', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            dataLabels: { enabled: false },
            colors: [pal.from],
            plotOptions: {
                heatmap: {
                    shadeIntensity: 0.85,
                    colorScale: {
                        ranges: [
                            { from: 0,   to: 25,  color: pal.from,  name: 'Low'    },
                            { from: 26,  to: 60,  color: mixColor(pal.from, pal.to, 0.5), name: 'Medium' },
                            { from: 61,  to: 100, color: mixColor(pal.from, pal.to, 0.2), name: 'High'   },
                            { from: 101, to: 999, color: pal.to,    name: 'Peak'   },
                        ],
                    },
                    radius: 3,
                },
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light' },
            legend: { show: true, position: 'top', horizontalAlign: 'right', fontSize: '11px', markers: { radius: 4 } },
            responsive: [
                { breakpoint: 640, options: { chart: { height: Math.max(180, height - 60) }, xaxis: { labels: { rotate: -45, style: { fontSize: '9px' } } } } },
                { breakpoint: 480, options: { chart: { height: Math.max(160, height - 80) }, legend: { show: false } } },
            ],
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

    function mixColor(hex1, hex2, t) {
        var r1 = parseInt(hex1.slice(1,3),16), g1 = parseInt(hex1.slice(3,5),16), b1 = parseInt(hex1.slice(5,7),16);
        var r2 = parseInt(hex2.slice(1,3),16), g2 = parseInt(hex2.slice(3,5),16), b2 = parseInt(hex2.slice(5,7),16);
        var r = Math.round(r1 + (r2-r1)*t), g = Math.round(g1 + (g2-g1)*t), b = Math.round(b1 + (b2-b1)*t);
        return '#' + [r,g,b].map(function(v){ return ('0'+v.toString(16)).slice(-2); }).join('');
    }

    function boot() { document.querySelectorAll('[data-chart-type="heatmap"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
