(function () {
    'use strict';

    var DEFAULT_COLORS = ['#EF4444', '#F59E0B', '#10B981'];
    var DEFAULT_SERIES = [1.3, 1.1, 97.7];
    var DEFAULT_LABELS = ['Used', 'Reserved', 'Remaining'];

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var series      = (cfg.series && cfg.series.length) ? cfg.series : DEFAULT_SERIES;
        var labels      = (cfg.labels && cfg.labels.length) ? cfg.labels : DEFAULT_LABELS;
        var colors      = (cfg.colors && cfg.colors.length) ? cfg.colors : DEFAULT_COLORS.slice(0, series.length);
        var height      = cfg.height      || 220;
        var totalLabel  = cfg.totalLabel  || 'Total';
        var totalValue  = cfg.totalValue  || '0';
        var dark        = isDark();

        var total = series.reduce(function(a, b) { return a + b; }, 0);

        function pct(v) { return total > 0 ? ((v / total) * 100).toFixed(1) + '%' : '0%'; }

        var chart = new ApexCharts(el, {
            series: series,
            labels: labels,
            chart: {
                type: 'donut',
                height: height,
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
                        size: '76%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '12px',
                                fontWeight: 600,
                                color: dark ? '#94A3B8' : '#64748B',
                                offsetY: -6,
                            },
                            value: {
                                show: true,
                                fontSize: '17px',
                                fontWeight: 700,
                                color: dark ? '#F8FAFC' : '#0F172A',
                                offsetY: 4,
                                formatter: function(v) { return v; },
                            },
                            total: {
                                show: true,
                                showAlways: true,
                                label: totalLabel,
                                fontSize: '12px',
                                fontWeight: 600,
                                color: dark ? '#94A3B8' : '#64748B',
                                formatter: function() { return totalValue; },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            stroke: { width: 2, colors: [dark ? '#0F172A' : '#FFFFFF'] },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function(v) { return pct(v) + ' (' + v.toLocaleString() + ')'; } },
            },
            legend: {
                show: true,
                position: 'right',
                verticalAlign: 'middle',
                fontSize: '12px',
                fontWeight: 500,
                markers: { radius: 10, width: 10, height: 10 },
                itemMargin: { horizontal: 4, vertical: 6 },
                formatter: function(seriesName, opts) {
                    var v   = opts.w.globals.series[opts.seriesIndex];
                    var p   = pct(v);
                    return '<span style="color:' + (dark ? '#CBD5E1' : '#475569') + '">'
                        + seriesName + '</span> '
                        + '<strong style="color:' + (dark ? '#F1F5F9' : '#0F172A') + '">' + p + '</strong>';
                },
            },
            responsive: [
                {
                    breakpoint: 640,
                    options: {
                        chart: { height: Math.max(180, height - 40) },
                        legend: { position: 'bottom', fontSize: '11px', itemMargin: { horizontal: 6, vertical: 3 } },
                        plotOptions: { pie: { donut: { labels: { value: { fontSize: '14px' } } } } },
                    },
                },
                {
                    breakpoint: 420,
                    options: {
                        chart: { height: Math.max(160, height - 60) },
                        legend: { show: false },
                    },
                },
            ],
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                stroke:  { colors: [d ? '#0F172A' : '#FFFFFF'] },
                tooltip: { theme: d ? 'dark' : 'light' },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name:  { color: d ? '#94A3B8' : '#64748B' },
                                value: { color: d ? '#F8FAFC' : '#0F172A' },
                                total: { color: d ? '#94A3B8' : '#64748B' },
                            },
                        },
                    },
                },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="breakdown-donut"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
