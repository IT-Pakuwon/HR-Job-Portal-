(function () {
    'use strict';

    var MULTI = ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4'];

    var PALETTE = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8'],
        green:  ['#10B981','#34D399','#059669'],
        orange: ['#F59E0B','#FBC02D','#D97706'],
        red:    ['#EF4444','#F87171','#DC2626'],
        pink:   ['#EC4899','#F472B6','#DB2777'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2'],
    };

    var t0 = new Date(2026, 0, 1).getTime();
    var d  = 86400000;

    var SAMPLE = {
        series: [
            { name: 'Design',      data: [{ x: 'Phase 1', y: [t0,          t0 + 5*d] }] },
            { name: 'Development', data: [{ x: 'Phase 1', y: [t0 + 3*d,   t0 + 12*d] }] },
            { name: 'Testing',     data: [{ x: 'Phase 1', y: [t0 + 10*d,  t0 + 16*d] }] },
            { name: 'Deploy',      data: [{ x: 'Phase 1', y: [t0 + 15*d,  t0 + 18*d] }] },
        ],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'cyan';
        var dark   = isDark();
        var colors = series.length > 3 ? MULTI : (PALETTE[color] || PALETTE.cyan);

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'rangeBar', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: true, barHeight: '50%',
                    borderRadius: 4, borderRadiusApplication: 'end',
                    rangeBarGroupRows: false,
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                type: 'datetime',
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '10px' }, datetimeFormatter: { day: 'dd MMM' } },
            },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 8 } },
            legend: {
                show: series.length > 1, position: 'top', horizontalAlign: 'right',
                fontSize: '11px', markers: { radius: 4 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                x: { format: 'dd MMM yyyy' },
            },
            responsive: [
                { breakpoint: 640, options: { chart: { height: Math.max(200, height - 60) }, xaxis: { labels: { style: { fontSize: '9px' } } } } },
                { breakpoint: 480, options: { chart: { height: Math.max(180, height - 80) }, legend: { show: false } } },
            ],
        });
        chart.render();

        new MutationObserver(function() {
            var d2 = isDark();
            chart.updateOptions({
                chart:   { foreColor: d2 ? '#94A3B8' : '#64748B' },
                grid:    { borderColor: d2 ? '#1E293B' : '#F1F5F9' },
                tooltip: { theme: d2 ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="rangebar"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
