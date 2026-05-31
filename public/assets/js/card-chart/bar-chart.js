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

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height     = cfg.height  || 300;
        var color      = cfg.color   || 'blue';
        var stacked    = cfg.stacked || false;
        var dark       = isDark();
        var colors     = series.length > 1 ? PALETTE.multi : (PALETTE[color] || PALETTE.blue);

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
                bar: {
                    horizontal: true, barHeight: '60%',
                    borderRadius: 5, borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' }, formatter: function(v) { return v.toLocaleString(); } },
            },
            yaxis: { categories: categories, labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v.toLocaleString(); } } },
            legend: {
                show: series.length > 1, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { radius: 6 },
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

    function boot() { document.querySelectorAll('[data-chart-type="bar"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
