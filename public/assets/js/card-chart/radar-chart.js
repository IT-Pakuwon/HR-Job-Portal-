(function () {
    'use strict';

    var PALETTES = {
        violet: ['#8B5CF6','#C4B5FD'],
        blue:   ['#3B82F6','#93C5FD'],
        green:  ['#10B981','#6EE7B7'],
        orange: ['#F59E0B','#FDE68A'],
        red:    ['#EF4444','#FCA5A5'],
        pink:   ['#EC4899','#FBCFE8'],
        cyan:   ['#06B6D4','#A5F3FC'],
    };

    /* Marketing channel performance comparison sample */
    var SAMPLE = {
        series: [
            { name: 'Q1 2026', data: [80, 72, 65, 90, 58, 77] },
            { name: 'Q2 2026', data: [88, 60, 75, 82, 71, 83] },
        ],
        categories: ['Social Media', 'Email', 'SEO', 'Paid Ads', 'Events', 'Referral'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series     = (cfg.series     && cfg.series.length)     ? cfg.series     : SAMPLE.series;
        var categories = (cfg.categories && cfg.categories.length) ? cfg.categories : SAMPLE.categories;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'violet';
        var dark   = isDark();
        var pal    = PALETTES[color] || PALETTES.violet;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'radar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                dropShadow: { enabled: true, blur: 1, left: 1, top: 1, opacity: 0.08 },
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: pal,
            fill:    { opacity: 0.18 },
            stroke:  { width: 2 },
            markers: { size: 4, hover: { size: 6 } },
            xaxis: { categories: categories },
            yaxis: { show: false },
            grid:  { show: false },
            tooltip: { theme: dark ? 'dark' : 'light' },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { radius: 6 },
            },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="radar"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
