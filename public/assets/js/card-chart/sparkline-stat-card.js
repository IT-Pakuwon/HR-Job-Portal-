(function () {
    'use strict';

    var PALETTE = {
        violet: { line: '#8B5CF6', line2: '#C4B5FD', fill: 'rgba(139,92,246,0.15)', fill2: 'rgba(196,181,253,0.08)' },
        blue:   { line: '#3B82F6', line2: '#93C5FD', fill: 'rgba(59,130,246,0.15)', fill2: 'rgba(147,197,253,0.08)' },
        green:  { line: '#10B981', line2: '#6EE7B7', fill: 'rgba(16,185,129,0.15)', fill2: 'rgba(110,231,183,0.08)' },
        orange: { line: '#F59E0B', line2: '#FCD34D', fill: 'rgba(245,158,11,0.15)', fill2: 'rgba(252,211,77,0.08)'  },
        red:    { line: '#EF4444', line2: '#FCA5A5', fill: 'rgba(239,68,68,0.15)',  fill2: 'rgba(252,165,165,0.08)' },
        pink:   { line: '#EC4899', line2: '#F9A8D4', fill: 'rgba(236,72,153,0.15)', fill2: 'rgba(249,168,212,0.08)' },
        cyan:   { line: '#06B6D4', line2: '#67E8F9', fill: 'rgba(6,182,212,0.15)',  fill2: 'rgba(103,232,249,0.08)' },
    };

    var SAMPLE = [12, 18, 14, 22, 20, 31, 28, 35, 30, 42, 38, 50];

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function makeSecondWave(data) {
        return data.map(function (v, i) {
            return Math.max(0, Math.round(v * 0.68 + Math.sin(i * 1.1) * 5));
        });
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var rawSeries = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE;
        var height    = cfg.height || 70;
        var color     = cfg.color  || 'violet';
        var waves     = cfg.waves  || 1;
        var pal       = PALETTE[color] || PALETTE.violet;
        var dark      = isDark();

        var seriesData;
        if (Array.isArray(rawSeries[0]) || (rawSeries[0] && typeof rawSeries[0] === 'object')) {
            seriesData = rawSeries;
        } else if (waves === 2) {
            seriesData = [
                { name: '', data: rawSeries },
                { name: '', data: makeSecondWave(rawSeries) },
            ];
        } else {
            seriesData = [{ name: '', data: rawSeries }];
        }

        var isDual = seriesData.length >= 2;

        var chart = new ApexCharts(el, {
            series: seriesData,
            chart: {
                type: 'area', height: height, sparkline: { enabled: true },
                fontFamily: 'Inter, sans-serif', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: isDual ? [pal.line, pal.line2] : [pal.line],
            stroke: { curve: 'smooth', width: isDual ? [2, 1.5] : 2 },
            fill: {
                type: 'solid',
                opacity: isDual ? [0.15, 0.08] : 0.15,
            },
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
