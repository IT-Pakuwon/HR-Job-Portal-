(function () {
    'use strict';

    var PALETTES = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8'],
        green:  ['#10B981','#34D399','#059669'],
        orange: ['#F59E0B','#FBC02D','#D97706'],
        red:    ['#EF4444','#F87171','#DC2626'],
        pink:   ['#EC4899','#F472B6','#DB2777'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2'],
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444'],
    };

    var SAMPLE = {
        series: [
            { name: 'Group A', data: [[1,5],[2,8],[3,6],[4,11],[5,9],[6,14],[7,12],[8,16],[9,13],[10,18]] },
            { name: 'Group B', data: [[1,3],[2,6],[3,4],[4,8],[5,7],[6,11],[7,9],[8,13],[9,11],[10,15]] },
        ],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'pink';
        var xLabel = cfg.xLabel || 'X';
        var yLabel = cfg.yLabel || 'Y';
        var dark   = isDark();
        var colors = series.length > 1 ? PALETTES.multi : (PALETTES[color] || PALETTES.pink);

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'scatter', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            markers: { size: 7, strokeWidth: 0, hover: { size: 9 } },
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
            tooltip: { theme: dark ? 'dark' : 'light', x: { show: true } },
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

    function boot() { document.querySelectorAll('[data-chart-type="scatter"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
