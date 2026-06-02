(function () {
    'use strict';

    var PALETTE = {
        violet: { line: '#8B5CF6', fill: 'rgba(139,92,246,0.15)' },
        blue:   { line: '#3B82F6', fill: 'rgba(59,130,246,0.15)'  },
        green:  { line: '#10B981', fill: 'rgba(16,185,129,0.15)'  },
        orange: { line: '#F59E0B', fill: 'rgba(245,158,11,0.15)'  },
        red:    { line: '#EF4444', fill: 'rgba(239,68,68,0.15)'   },
        pink:   { line: '#EC4899', fill: 'rgba(236,72,153,0.15)'  },
        cyan:   { line: '#06B6D4', fill: 'rgba(6,182,212,0.15)'   },
    };

    var SAMPLE = { series: [12, 18, 14, 22, 20, 31, 28, 35, 30, 42, 38, 50] };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var rawSeries = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height    = cfg.height || 70;
        var color     = cfg.color  || 'violet';
        var pal       = PALETTE[color] || PALETTE.violet;
        var dark      = isDark();

        var seriesData = Array.isArray(rawSeries[0]) ? rawSeries : [{ name: '', data: rawSeries }];

        var chart = new ApexCharts(el, {
            series: seriesData,
            chart: {
                type: 'area', height: height, sparkline: { enabled: true },
                fontFamily: 'Inter, sans-serif', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: [pal.line],
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'solid', opacity: 0.15 },
            markers: { size: 0 },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                fixed: { enabled: false },
                x: { show: false },
                y: { formatter: function(v){ return v.toLocaleString(); } },
                marker: { show: false },
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({ tooltip: { theme: d ? 'dark' : 'light' } });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="sparkline"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
