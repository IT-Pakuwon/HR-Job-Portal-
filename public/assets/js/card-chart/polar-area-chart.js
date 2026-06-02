(function () {
    'use strict';

    var MULTI = ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899','#06B6D4'];

    var PALETTE = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9','#C4B5FD','#7C3AED'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8','#93C5FD','#2563EB'],
        green:  ['#10B981','#34D399','#059669','#6EE7B7','#047857'],
        orange: ['#F59E0B','#FBC02D','#D97706','#FDE68A','#B45309'],
        red:    ['#EF4444','#F87171','#DC2626','#FCA5A5','#B91C1C'],
        pink:   ['#EC4899','#F472B6','#DB2777','#FBCFE8','#BE185D'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2','#A5F3FC','#0E7490'],
    };

    var SAMPLE = {
        series: [42, 58, 35, 70, 28, 55],
        labels: ['Planning','Design','Dev','QA','Deploy','Support'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var labels = (cfg.labels && cfg.labels.length) ? cfg.labels : SAMPLE.labels;
        var height = cfg.height || 320;
        var color  = cfg.color  || 'blue';
        var dark   = isDark();
        var colors = series.length > 4 ? MULTI : (PALETTE[color] || PALETTE.blue);

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'polarArea', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            labels: labels,
            fill: { opacity: 0.85 },
            stroke: { width: 1, colors: [dark ? '#0F172A' : '#FFFFFF'] },
            plotOptions: {
                polarArea: { rings: { strokeWidth: 1, strokeColor: dark ? '#1E293B' : '#F1F5F9' }, spokes: { strokeWidth: 1, connectorColors: dark ? '#1E293B' : '#F1F5F9' } },
            },
            yaxis: { show: false },
            legend: {
                position: 'bottom', horizontalAlign: 'center',
                fontSize: '12px', markers: { radius: 6 },
                itemMargin: { horizontal: 6, vertical: 4 },
            },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v){ return v.toLocaleString(); } } },
            responsive: [
                { breakpoint: 640, options: { chart: { height: Math.max(220, height - 60) }, legend: { fontSize: '10px' } } },
                { breakpoint: 480, options: { chart: { height: Math.max(200, height - 80) }, legend: { show: false } } },
            ],
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                stroke:  { colors: [d ? '#0F172A' : '#FFFFFF'] },
                tooltip: { theme: d ? 'dark' : 'light' },
                plotOptions: { polarArea: { rings: { strokeColor: d ? '#1E293B' : '#F1F5F9' }, spokes: { connectorColors: d ? '#1E293B' : '#F1F5F9' } } },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="polararea"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
