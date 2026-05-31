(function () {
    'use strict';

    /* Funnel chart via ApexCharts bar with isFunnel: true.
       series expects: [{ data: [{x: 'Stage', y: value}, ...] }] */

    var SAMPLE = {
        series: [{
            name: 'Pipeline',
            data: [
                { x: 'Prospects',   y: 1200 },
                { x: 'Leads',       y: 840  },
                { x: 'Qualified',   y: 560  },
                { x: 'Proposal',    y: 310  },
                { x: 'Negotiation', y: 180  },
                { x: 'Closed',      y: 95   },
            ],
        }],
    };

    var PALETTES = {
        violet: ['#8B5CF6','#A78BFA','#7C3AED','#C4B5FD','#6D28D9','#DDD6FE'],
        blue:   ['#3B82F6','#60A5FA','#2563EB','#93C5FD','#1D4ED8','#BFDBFE'],
        green:  ['#10B981','#34D399','#059669','#6EE7B7','#047857','#A7F3D0'],
        orange: ['#F59E0B','#FBC02D','#D97706','#FDE68A','#B45309','#FEF3C7'],
        red:    ['#EF4444','#F87171','#DC2626','#FCA5A5','#B91C1C','#FEE2E2'],
        pink:   ['#EC4899','#F472B6','#DB2777','#FBCFE8','#BE185D','#FCE7F3'],
        cyan:   ['#06B6D4','#22D3EE','#0891B2','#A5F3FC','#0E7490','#CFFAFE'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 320;
        var color  = cfg.color  || 'orange';
        var dark   = isDark();
        var colors = PALETTES[color] || PALETTES.orange;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            plotOptions: {
                bar: {
                    horizontal: true, isFunnel: true,
                    borderRadius: 4, borderRadiusApplication: 'around',
                    distributed: true,
                },
            },
            dataLabels: {
                enabled: true,
                formatter: function(val, opt) {
                    return opt.w.globals.labels[opt.dataPointIndex] + ':  ' + val.toLocaleString();
                },
                style: { fontSize: '12px', fontWeight: 600 },
                dropShadow: { enabled: false },
            },
            xaxis: {
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { show: false },
            },
            yaxis: { labels: { show: false } },
            grid: { show: false },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return v.toLocaleString(); } } },
            legend: { show: false },
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

    function boot() { document.querySelectorAll('[data-chart-type="funnel"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
