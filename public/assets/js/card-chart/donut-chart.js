(function () {
    'use strict';

    var PALETTES = {
        violet: ['#8B5CF6','#A78BFA','#6D28D9','#C4B5FD','#7C3AED'],
        blue:   ['#3B82F6','#60A5FA','#1D4ED8','#93C5FD','#2563EB'],
        green:  ['#10B981','#34D399','#059669','#6EE7B7','#047857'],
        orange: ['#F59E0B','#FBC02D','#D97706','#FDE68A','#B45309'],
        red:    ['#EF4444','#F87171','#DC2626','#FCA5A5','#B91C1C'],
        pink:   ['#EC4899','#F472B6','#DB2777','#FBCFE8','#BE185D'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2','#A5F3FC','#0E7490'],
        multi:  ['#8B5CF6','#3B82F6','#10B981','#F59E0B','#EF4444'],
    };

    var SAMPLE = {
        series: [44, 28, 15, 8, 5],
        labels: ['Direct','Organic Search','Referral','Social','Email'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var labels = (cfg.labels && cfg.labels.length) ? cfg.labels : SAMPLE.labels;
        var height = cfg.height || 320;
        var color  = cfg.color  || 'violet';
        var dark   = isDark();
        var colors = PALETTES[color] || PALETTES.violet;

        var total  = series.reduce(function(a,b){ return a+b; }, 0);

        var chart = new ApexCharts(el, {
            series: series,
            labels: labels,
            chart: {
                type: 'donut', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true,
                                label: 'Total',
                                fontSize: '13px', fontWeight: 600,
                                color: dark ? '#94A3B8' : '#64748B',
                                formatter: function() { return total.toLocaleString(); },
                            },
                            value: {
                                fontSize: '22px', fontWeight: 700,
                                color: dark ? '#F8FAFC' : '#0F172A',
                                formatter: function(v) { return parseInt(v).toLocaleString(); },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v.toLocaleString(); } } },
            legend: {
                show: true, position: 'bottom', fontSize: '12px',
                markers: { radius: 6 }, itemMargin: { horizontal: 8, vertical: 4 },
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart: { foreColor: d ? '#94A3B8' : '#64748B' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="donut"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
