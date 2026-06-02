(function () {
    'use strict';

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function makeSample() {
        var base = 100, cats = [];
        var data = [];
        for (var i = 0; i < 20; i++) {
            var open  = base + (Math.random() - 0.5) * 10;
            var close = open + (Math.random() - 0.5) * 12;
            var high  = Math.max(open, close) + Math.random() * 5;
            var low   = Math.min(open, close) - Math.random() * 5;
            base = close;
            var d = new Date(2026, 0, i + 1);
            cats.push(d.getTime());
            data.push({ x: d.getTime(), y: [+open.toFixed(2), +high.toFixed(2), +low.toFixed(2), +close.toFixed(2)] });
        }
        return data;
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        var rawSeries  = (cfg.series     && cfg.series.length)     ? cfg.series     : null;
        var height     = cfg.height || 300;
        var dark       = isDark();

        var seriesData = rawSeries && Array.isArray(rawSeries[0]) === false && rawSeries[0] && rawSeries[0].y
            ? rawSeries
            : makeSample();

        var chart = new ApexCharts(el, {
            series: [{ name: 'Price', data: seriesData }],
            chart: {
                type: 'candlestick', height: height,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 700 },
            },
            plotOptions: {
                candlestick: {
                    colors: { upward: '#10B981', downward: '#EF4444' },
                    wick: { useFillColor: true },
                },
            },
            xaxis: {
                type: 'datetime',
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' }, datetimeFormatter: { day: 'dd MMM' } },
            },
            yaxis: {
                tooltip: { enabled: true },
                labels: { style: { fontSize: '11px' }, formatter: function(v){ return v.toFixed(1); } },
            },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                custom: function(opts) {
                    var o = opts.seriesData[0][opts.dataPointIndex];
                    if (!o) return '';
                    var up = o.y[3] >= o.y[0];
                    var col = up ? '#10B981' : '#EF4444';
                    return '<div style="padding:8px 12px;font-size:12px;font-family:Inter,sans-serif">'
                        + '<div style="font-weight:700;color:' + col + '">' + (up ? '▲' : '▼') + ' ' + o.y[3] + '</div>'
                        + '<div>O: ' + o.y[0] + ' H: ' + o.y[1] + '</div>'
                        + '<div>L: ' + o.y[2] + ' C: ' + o.y[3] + '</div>'
                        + '</div>';
                },
            },
            responsive: [
                { breakpoint: 640, options: { chart: { height: Math.max(200, height - 60) } } },
                { breakpoint: 480, options: { chart: { height: Math.max(180, height - 80) } } },
            ],
        });
        chart.render();

        new MutationObserver(function() {
            var d = isDark();
            chart.updateOptions({
                chart:   { foreColor: d ? '#94A3B8' : '#64748B' },
                grid:    { borderColor: d ? '#1E293B' : '#F1F5F9' },
                tooltip: { theme: d ? 'dark' : 'light' },
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    function boot() { document.querySelectorAll('[data-chart-type="candlestick"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
