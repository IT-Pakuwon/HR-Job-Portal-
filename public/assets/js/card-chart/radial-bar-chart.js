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
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444','#EC4899'],
    };

    var SAMPLE = {
        series: [78, 62, 91, 55],
        labels: ['Efficiency', 'Quality', 'Delivery', 'Support'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series      = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var labels      = (cfg.labels && cfg.labels.length) ? cfg.labels : SAMPLE.labels;
        var height      = cfg.height  || 300;
        var color       = cfg.color   || 'violet';
        var totalLabel  = cfg.totalLabel || 'Avg';
        var legPos      = cfg.legendPosition || 'bottom';
        var dark        = isDark();
        var colors      = series.length > 1 ? PALETTE.multi : (PALETTE[color] || PALETTE.violet);

        var avg = series.length ? Math.round(series.reduce(function(a,b){return a+b;},0)/series.length) : 0;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'radialBar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 800 },
            },
            colors: colors,
            plotOptions: {
                radialBar: {
                    offsetY: legPos === 'bottom' ? -10 : 0,
                    startAngle: -135, endAngle: 135,
                    hollow: {
                        margin: 5, size: '40%', background: 'transparent',
                    },
                    track: {
                        background: dark ? '#1E293B' : '#F1F5F9',
                        strokeWidth: '97%', margin: 5,
                    },
                    dataLabels: {
                        name: { fontSize: '11px', color: dark ? '#94A3B8' : '#64748B', offsetY: -6 },
                        value: { fontSize: '18px', fontWeight: 700, color: dark ? '#F1F5F9' : '#0F172A', offsetY: 4, formatter: function(v){ return v + '%'; } },
                        total: {
                            show: series.length > 1,
                            label: totalLabel,
                            fontSize: '11px',
                            color: dark ? '#94A3B8' : '#64748B',
                            formatter: function(){ return avg + '%'; },
                        },
                    },
                },
            },
            labels: labels,
            legend: {
                show: series.length > 1,
                position: legPos === 'bottom' ? 'bottom' : 'right',
                horizontalAlign: 'center',
                fontSize: '12px',
                markers: { radius: 6 },
                itemMargin: { horizontal: 6, vertical: 4 },
            },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v){ return v + '%'; } } },
            responsive: [
                { breakpoint: 640, options: { chart: { height: Math.max(200, height - 60) }, legend: { position: 'bottom', fontSize: '10px' } } },
                { breakpoint: 480, options: { chart: { height: Math.max(180, height - 80) }, legend: { show: false } } },
            ],
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                tooltip: { theme: d ? 'dark' : 'light' },
                plotOptions: { radialBar: { track: { background: d ? '#1E293B' : '#F1F5F9' }, dataLabels: { name: { color: d ? '#94A3B8' : '#64748B' }, value: { color: d ? '#F1F5F9' : '#0F172A' }, total: { color: d ? '#94A3B8' : '#64748B' } } } },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="radialbar"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
