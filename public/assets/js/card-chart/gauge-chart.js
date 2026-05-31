(function () {
    'use strict';

    var COLORS = {
        violet: '#8B5CF6', blue: '#3B82F6', green: '#10B981',
        orange: '#F59E0B', red: '#EF4444',  pink: '#EC4899', cyan: '#06B6D4',
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var pct    = cfg.pct    != null ? cfg.pct    : 72;
        var label  = cfg.label  || 'Progress';
        var height = cfg.height || 280;
        var color  = cfg.color  || 'green';
        var dark   = isDark();
        var hex    = COLORS[color] || COLORS.green;

        var chart = new ApexCharts(el, {
            series: [pct],
            chart: {
                type: 'radialBar', height: height,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 900 },
            },
            colors: [hex],
            plotOptions: {
                radialBar: {
                    startAngle: -135, endAngle: 135,
                    hollow: { size: '65%', background: 'transparent' },
                    track: {
                        background: dark ? '#1E293B' : '#F1F5F9',
                        strokeWidth: '100%',
                    },
                    dataLabels: {
                        show: true,
                        name: {
                            show: true, fontSize: '12px', fontWeight: 600,
                            color: dark ? '#94A3B8' : '#64748B',
                            offsetY: 22,
                        },
                        value: {
                            show: true, fontSize: '28px', fontWeight: 700,
                            color: dark ? '#F8FAFC' : '#0F172A',
                            offsetY: -8,
                            formatter: function(v) { return v + '%'; },
                        },
                    },
                },
            },
            labels: [label],
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark', type: 'horizontal',
                    gradientToColors: [COLORS.cyan],
                    stops: [0, 100],
                },
            },
            stroke: { lineCap: 'round' },
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart: { foreColor: d ? '#94A3B8' : '#64748B' },
                plotOptions: {
                    radialBar: {
                        track: { background: d ? '#1E293B' : '#F1F5F9' },
                        dataLabels: {
                            name:  { color: d ? '#94A3B8' : '#64748B' },
                            value: { color: d ? '#F8FAFC' : '#0F172A' },
                        },
                    },
                },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="gauge"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
