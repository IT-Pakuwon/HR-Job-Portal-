(function () {
    'use strict';

    var PALETTE = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9','#C4B5FD','#7C3AED'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8','#93C5FD','#2563EB'],
        green:  ['#10B981','#34D399','#059669','#6EE7B7','#047857'],
        orange: ['#F59E0B','#FBC02D','#D97706','#FDE68A','#B45309'],
        red:    ['#EF4444','#F87171','#DC2626','#FCA5A5','#B91C1C'],
        pink:   ['#EC4899','#F472B6','#DB2777','#FBCFE8','#BE185D'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2','#A5F3FC','#0E7490'],
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4'],
    };

    var SAMPLE = {
        series: [
            { name: 'This Year',  data: [31, 52, 41, 67, 55, 82, 63, 78, 60, 88, 105, 92] },
            { name: 'Last Year',  data: [20, 35, 28, 48, 40, 65, 50, 60, 45, 70, 88, 74] },
        ],
        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height     = cfg.height  || 300;
        var color      = cfg.color   || 'violet';
        var stacked    = cfg.stacked || false;
        var dark       = isDark();
        var colors     = series.length > 1 ? PALETTE.multi : (PALETTE[color] || PALETTE.violet);

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'line', height: height, stacked: stacked,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            stroke: { curve: 'smooth', width: 2.5 },
            markers: { size: 0, hover: { size: 5, sizeOffset: 3 } },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: { labels: { style: { fontSize: '11px' }, formatter: function(v) { return v.toLocaleString(); } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v.toLocaleString(); } } },
            legend: {
                show: series.length > 1, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { radius: 6 }, itemMargin: { horizontal: 8 },
            },
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

    function boot() { document.querySelectorAll('[data-chart-type="line"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
