(function () {
    'use strict';

    var SAMPLE = {
        series: [{
            data: [
                { x: 'Operations',   y: 218 },
                { x: 'Sales',        y: 149 },
                { x: 'Marketing',    y: 184 },
                { x: 'Finance',      y: 55  },
                { x: 'HR',           y: 84  },
                { x: 'IT',           y: 122 },
                { x: 'Legal',        y: 43  },
                { x: 'Procurement',  y: 97  },
            ],
        }],
    };

    var PALETTES = {
        violet: ['#8B5CF6','#7C3AED','#6D28D9','#A78BFA','#C4B5FD','#DDD6FE','#EDE9FE','#4C1D95'],
        blue:   ['#3B82F6','#2563EB','#1D4ED8','#60A5FA','#93C5FD','#BFDBFE','#DBEAFE','#1E3A8A'],
        green:  ['#10B981','#059669','#047857','#34D399','#6EE7B7','#A7F3D0','#D1FAE5','#064E3B'],
        orange: ['#F59E0B','#D97706','#B45309','#FBC02D','#FDE68A','#FEF3C7','#FFFBEB','#78350F'],
        red:    ['#EF4444','#DC2626','#B91C1C','#F87171','#FCA5A5','#FEE2E2','#FEF2F2','#7F1D1D'],
        pink:   ['#EC4899','#DB2777','#BE185D','#F472B6','#FBCFE8','#FCE7F3','#FDF2F8','#831843'],
        cyan:   ['#06B6D4','#0891B2','#0E7490','#22D3EE','#A5F3FC','#CFFAFE','#ECFEFF','#164E63'],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series = (cfg.series && cfg.series.length) ? cfg.series : SAMPLE.series;
        var height = cfg.height || 300;
        var color  = cfg.color  || 'violet';
        var dark   = isDark();
        var colors = PALETTES[color] || PALETTES.violet;

        var chart = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'treemap', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            colors: colors,
            plotOptions: {
                treemap: {
                    distributed: true, enableShades: false,
                    colorScale: { ranges: [] },
                },
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '12px', fontWeight: 700, colors: ['#ffffff'] },
                formatter: function(text, op) { return [text, op.value.toLocaleString()]; },
            },
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

    function boot() { document.querySelectorAll('[data-chart-type="treemap"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
