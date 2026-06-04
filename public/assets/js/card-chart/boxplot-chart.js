(function () {
    'use strict';

    /* y array per point: [min, Q1, median, Q3, max] */

    var PALETTES = {
        violet: ['#8B5CF6','#7C3AED'],
        blue:   ['#3B82F6','#2563EB'],
        green:  ['#10B981','#059669'],
        orange: ['#F59E0B','#D97706'],
        red:    ['#EF4444','#DC2626'],
        pink:   ['#EC4899','#DB2777'],
        cyan:   ['#06B6D4','#0891B2'],
    };

    /* Property price distribution by district (M IDR) */
    var SAMPLE = {
        series: [{
            data: [
                { x: 'BSD City',      y: [400,  650,  850,  1200, 2200] },
                { x: 'Alam Sutera',   y: [500,  750,  950,  1400, 2500] },
                { x: 'Kelapa Gading', y: [800,  1100, 1500, 2100, 4000] },
                { x: 'Fatmawati',     y: [300,  500,  700,  1000, 1800] },
                { x: 'Bekasi',        y: [200,  380,  550,  800,  1500] },
            ],
        }],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'cyan';
        var dark   = isDark();
        var pal    = PALETTES[color] || PALETTES.cyan;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'boxPlot', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: [pal[0], pal[1]],
            plotOptions: {
                boxPlot: { colors: { upper: pal[0], lower: pal[1] } },
            },
            xaxis: {
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px' },
                    formatter: function(v) { return v >= 1000 ? (v / 1000).toFixed(1) + 'B' : v.toLocaleString(); },
                },
            },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light' },
            legend: { show: false },
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

    function boot() { document.querySelectorAll('[data-chart-type="boxplot"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
