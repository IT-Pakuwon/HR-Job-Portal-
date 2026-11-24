/* Stacked bar: one bar split into parts, with the amount and share of each part listed under it.
   Used where a donut hides the small parts (e.g. Budget: Used 1.3% | Reserved 1.1% | Remaining 97.6%).
   Plain DOM + inline styles (no ApexCharts, no Tailwind classes), so any page can use it.

     CardChart.stackedBar(el, { segments: [{ label, value, color, text? }], total?: 'Rp 503,6M', totalLabel?: 'Total' })

   text = the amount as the caller wants it written (e.g. "Rp 6,7Jt"); without it the raw number is shown.
   Also bootable from markup: <div data-chart-type="stacked-bar" data-config='{"segments":[...]}'>. */
(function () {
    'use strict';

    var live = []; // [{el, cfg}] drawn so far — redrawn when the page switches between light and dark

    function isDark() { return document.documentElement.classList.contains('dark'); }

    function esc(v) {
        return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; });
    }

    function pctText(p) {
        if (p > 0 && p < 0.1) return '<0.1%';
        return (Math.round(p * 10) / 10).toLocaleString('id-ID') + '%';
    }

    function draw(el, cfg) {
        var dark = isDark();
        var ink = dark ? '#F1F5F9' : '#0F172A', soft = dark ? '#94A3B8' : '#64748B', line = dark ? 'rgba(148,163,184,.18)' : '#F1F5F9';
        var segs = (cfg.segments || []).map(function (s) { return { label: s.label, color: s.color, value: Math.max(0, +s.value || 0), text: s.text }; });
        var sum = segs.reduce(function (a, s) { return a + s.value; }, 0);

        if (!(sum > 0)) { el.innerHTML = '<p style="padding:24px 0;text-align:center;font-size:12px;color:' + soft + '">No data available</p>'; return; }

        segs.forEach(function (s) { s.pct = s.value / sum * 100; });

        // a part under ~1% would be a hairline: give every non-empty part a visible minimum width
        var bar = segs.filter(function (s) { return s.value > 0; }).map(function (s) {
            return '<span title="' + esc(s.label + ': ' + s.value.toLocaleString('id-ID') + ' (' + pctText(s.pct) + ')') + '" style="flex:' + s.pct + ' 1 0;min-width:8px;background:' + esc(s.color) + ';transition:flex .6s ease"></span>';
        }).join('');

        var rows = segs.map(function (s) {
            return '<div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-top:1px solid ' + line + '">' +
                '<span style="width:9px;height:9px;flex:none;border-radius:50%;background:' + esc(s.color) + '"></span>' +
                '<span style="flex:1;min-width:0;font-size:12px;font-weight:600;color:' + soft + '">' + esc(s.label) + '</span>' +
                '<span title="' + esc(s.value.toLocaleString('id-ID')) + '" style="font-size:13px;font-weight:700;color:' + ink + ';font-variant-numeric:tabular-nums">' + esc(s.text != null ? s.text : s.value.toLocaleString('id-ID')) + '</span>' +
                '<span style="width:52px;text-align:right;font-size:12px;font-weight:600;color:' + soft + ';font-variant-numeric:tabular-nums">' + pctText(s.pct) + '</span></div>';
        }).join('');

        var head = cfg.total
            ? '<div style="display:flex;align-items:baseline;justify-content:space-between;margin-bottom:10px">' +
              '<span style="font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:' + soft + '">' + esc(cfg.totalLabel || 'Total') + '</span>' +
              '<span style="font-size:20px;font-weight:800;color:' + ink + ';font-variant-numeric:tabular-nums">' + esc(cfg.total) + '</span></div>'
            : '';

        el.innerHTML = '<div style="padding:4px 4px 2px">' + head +
            '<div style="display:flex;gap:2px;height:16px;overflow:hidden;border-radius:999px;background:' + line + '">' + bar + '</div>' +
            '<div style="margin-top:12px">' + rows + '</div></div>';
    }

    function stackedBar(el, cfg) {
        cfg = cfg || {};
        draw(el, cfg);
        live = live.filter(function (x) { return document.body.contains(x.el) && x.el !== el; });
        live.push({ el: el, cfg: cfg });
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}
        stackedBar(el, cfg);
    }

    new MutationObserver(function () {
        live = live.filter(function (x) { return document.body.contains(x.el); });
        live.forEach(function (x) { draw(x.el, x.cfg); });
    }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

    function boot() { document.querySelectorAll('[data-chart-type="stacked-bar"]').forEach(init); }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }

    window.CardChart = window.CardChart || {};
    window.CardChart.stackedBar = stackedBar;
    window.CardChart.initStackedBar = init;
})();
