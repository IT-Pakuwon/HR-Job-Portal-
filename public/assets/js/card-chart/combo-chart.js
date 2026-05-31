(function () {
    'use strict';

    var SAMPLE = {
        series: [
            { name: 'Revenue',  type: 'column', data: [44, 55, 41, 67, 72, 89, 76, 92, 80, 110, 130, 118] },
            { name: 'Growth %', type: 'line',   data: [8, 12, 5, 18, 9, 22, 14, 25, 17, 28, 32, 24]      },
        ],
        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height     = cfg.height || 300;
        var dark       = isDark();

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'line', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: ['#8B5CF6','#F59E0B'],
            stroke: { width: [0, 3], curve: 'smooth' },
            plotOptions: {
                bar: { columnWidth: '55%', borderRadius: 4, borderRadiusApplication: 'end' },
            },
            fill: { opacity: [0.85, 1] },
            markers: { size: [0, 4], hover: { size: 6 } },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: [
                {
                    seriesName: series[0] ? series[0].name : 'Column',
                    labels: { style: { fontSize: '11px' }, formatter: function(v) { return v.toLocaleString(); } },
                },
                {
                    opposite: true,
                    seriesName: series[1] ? series[1].name : 'Line',
                    labels: { style: { fontSize: '11px', colors: '#F59E0B' }, formatter: function(v) { return v + '%'; } },
                },
            ],
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: {
                theme: dark ? 'dark' : 'light', shared: true,
                y: [
                    { formatter: function(v) { return v ? v.toLocaleString() : ''; } },
                    { formatter: function(v) { return v ? v + '%' : ''; } },
                ],
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
                chart: { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:  { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="combo"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
