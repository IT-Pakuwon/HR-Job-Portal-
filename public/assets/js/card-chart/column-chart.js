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
            { name: 'Q1', data: [44, 55, 57, 56, 61, 58, 63, 60, 66] },
            { name: 'Q2', data: [76, 85, 101, 98, 87, 105, 91, 114, 94] },
        ],
        categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep'],
    };

    // Axis ticks shortened the way the cards write rupiah: 8M (miliar) / 6,5Jt (juta); below a million stays whole
    function compact(v) {
        var n = Math.abs(v), f = function (x, s) { return (v < 0 ? '-' : '') + (Math.round(x * 10) / 10).toLocaleString('id-ID') + s; };
        if (n >= 1e12) return f(n / 1e12, 'T');
        if (n >= 1e9) return f(n / 1e9, 'M');
        if (n >= 1e6) return f(n / 1e6, 'Jt');
        return v.toLocaleString();
    }

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
                    horizontal: false, columnWidth: '55%',
                    borderRadius: 5, borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: categories,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: { labels: { style: { fontSize: '11px' }, formatter: function(v) { return compact(v); } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return compact(v); } } },
            legend: {
                show: series.length > 1, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { shape: 'circle', size: 6 },
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

    function boot() { document.querySelectorAll('[data-chart-type="column"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }

    window.CardChart = window.CardChart || {};
    window.CardChart.initColumn = init;
})();
