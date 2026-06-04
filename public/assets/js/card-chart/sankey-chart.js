(function () {
    'use strict';

    /* ── SANKEY / ALLUVIAL FLOW CHART ─────────────────────────────────────────
       Custom SVG chart showing how customers/visitors move between zones (floors,
       areas) across time periods.  Bands between columns represent transitions;
       band width is proportional to the number of people making that move.

       Data format
       ───────────
       times[]          time labels, e.g. ['9:00','10:00','11:00','12:00']
       zones[]          zone labels, e.g. ['Ground Floor','Lower Ground','Upper Ground']
       counts[z][t]     # customers in zone z at time t
       transitions[t][z1][z2]  # customers moving from zone z1 at time t
                                 to zone z2 at time t+1  (optional — auto-approx when omitted)
    ──────────────────────────────────────────────────────────────────────────── */

    var ZONE_COLORS = ['#3B82F6','#10B981','#8B5CF6','#F59E0B','#EF4444','#EC4899','#06B6D4'];

    /* Mall sample: 3 floors, 4 time slots, 200 visitors total */
    var SAMPLE = {
        times:  ['9:00 AM', '10:00 AM', '11:00 AM', '12:00 PM'],
        zones:  ['Ground Floor', 'Lower Ground', 'Upper Ground'],
        counts: [
            [150, 110,  80, 120],  // Ground Floor
            [ 30,  60,  90,  50],  // Lower Ground
            [ 20,  30,  30,  30],  // Upper Ground
        ],
        /* transitions[t][from][to] — verified sums (row = source count, col sums = next counts) */
        transitions: [
            [[80, 50, 20], [15, 10, 5], [15, 0, 5]],   // 9→10
            [[60, 40, 10], [15, 40, 5], [5, 10, 15]],  // 10→11
            [[50, 20, 10], [65, 20, 5], [5, 10, 15]],  // 11→12
        ],
    };

    function isDark() { return document.documentElement.classList.contains('dark'); }

    /* When transitions are not supplied, approximate them proportionally */
    function approxTransitions(counts, nZ, nT) {
        var result = [];
        for (var t = 0; t < nT - 1; t++) {
            var totalNext = 0;
            for (var z = 0; z < nZ; z++) totalNext += counts[z][t + 1] || 0;
            var trans = [];
            for (var z1 = 0; z1 < nZ; z1++) {
                var src = counts[z1][t] || 0;
                var row = [], used = 0;
                for (var z2 = 0; z2 < nZ; z2++) {
                    if (z2 === nZ - 1) {
                        row.push(Math.max(0, src - used));
                    } else {
                        var v = totalNext > 0 ? Math.round(src * (counts[z2][t + 1] || 0) / totalNext) : 0;
                        row.push(v);
                        used += v;
                    }
                }
                trans.push(row);
            }
            result.push(trans);
        }
        return result;
    }

    function render(el, cfg) {
        var times = (cfg.times && cfg.times.length) ? cfg.times : SAMPLE.times;
        var zones = (cfg.zones && cfg.zones.length) ? cfg.zones : SAMPLE.zones;
        var counts = (cfg.counts && cfg.counts.length) ? cfg.counts : SAMPLE.counts;
        var transitions = (cfg.transitions && cfg.transitions.length)
            ? cfg.transitions
            : approxTransitions(counts, zones.length, times.length);

        var W = el.offsetWidth;
        if (!W || W < 100) { W = 560; }
        var H   = cfg.height || 380;
        var nZ  = zones.length;
        var nT  = times.length;
        var dark = isDark();

        var textColor = dark ? '#94A3B8' : '#64748B';
        var tickColor = dark ? '#1E293B' : '#E2E8F0';

        /* Layout */
        var labelW  = 120;   // left zone labels reserved width
        var nodeW   = 16;    // width of each zone bar
        var zoneGap = 10;    // vertical gap between zones
        var padTop  = 44;
        var padBot  = 20;
        var padRight = 20;

        var chartX   = labelW;
        var chartW   = W - labelW - padRight;
        var availH   = H - padTop - padBot;
        var colStep  = nT > 1 ? (chartW - nodeW) / (nT - 1) : 0;

        /* Compute node { x, y, h } for each zone at each time */
        var nodePos = [];
        for (var t = 0; t < nT; t++) {
            var total = 0;
            for (var z = 0; z < nZ; z++) total += counts[z][t] || 0;
            var usedH = availH - zoneGap * (nZ - 1);
            var yy = padTop;
            var col = [];
            for (var z = 0; z < nZ; z++) {
                var h = total > 0 ? Math.max(6, ((counts[z][t] || 0) / total) * usedH) : Math.max(6, usedH / nZ);
                col.push({ x: chartX + t * colStep, y: yy, h: h, count: counts[z][t] || 0 });
                yy += h + zoneGap;
            }
            nodePos.push(col);
        }

        var svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' + W + '" height="' + H + '">';

        /* ── Flow bands ─────────────────────────────────────────────────── */
        for (var t = 0; t < nT - 1; t++) {
            var trans  = transitions[t] || [];
            var srcOff = [];
            var dstOff = [];
            for (var z = 0; z < nZ; z++) { srcOff.push(0); dstOff.push(0); }

            for (var z1 = 0; z1 < nZ; z1++) {
                var srcTotal = counts[z1][t] || 0;
                var sn = nodePos[t][z1];
                var color = ZONE_COLORS[z1 % ZONE_COLORS.length];

                for (var z2 = 0; z2 < nZ; z2++) {
                    var flow = (trans[z1] && trans[z1][z2] != null) ? (trans[z1][z2] || 0) : 0;
                    if (flow <= 0) continue;

                    var dstTotal = counts[z2][t + 1] || 0;
                    var dn = nodePos[t + 1][z2];

                    var srcH = srcTotal > 0 ? (flow / srcTotal) * sn.h : 0;
                    var dstH = dstTotal > 0 ? (flow / dstTotal) * dn.h : 0;

                    var x1  = sn.x + nodeW;
                    var y1t = sn.y + srcOff[z1];
                    var y1b = y1t + srcH;

                    var x2  = dn.x;
                    var y2t = dn.y + dstOff[z2];
                    var y2b = y2t + dstH;

                    var cx  = (x1 + x2) / 2;
                    var opacity = (z1 === z2) ? '0.15' : '0.42';

                    var d = 'M ' + x1 + ' ' + y1t +
                            ' C ' + cx + ' ' + y1t + ' ' + cx + ' ' + y2t + ' ' + x2 + ' ' + y2t +
                            ' L ' + x2 + ' ' + y2b +
                            ' C ' + cx + ' ' + y2b + ' ' + cx + ' ' + y1b + ' ' + x1 + ' ' + y1b +
                            ' Z';

                    svg += '<path d="' + d + '" fill="' + color + '" opacity="' + opacity + '">' +
                           '<title>' + zones[z1] + ' → ' + zones[z2] + ': ' + flow + ' visitors</title>' +
                           '</path>';

                    srcOff[z1] += srcH;
                    dstOff[z2] += dstH;
                }
            }
        }

        /* ── Node bars ──────────────────────────────────────────────────── */
        for (var t = 0; t < nT; t++) {
            for (var z = 0; z < nZ; z++) {
                var n = nodePos[t][z];
                var color = ZONE_COLORS[z % ZONE_COLORS.length];
                svg += '<rect x="' + n.x + '" y="' + n.y + '" width="' + nodeW + '" height="' + n.h + '" fill="' + color + '" rx="3"/>';
                if (n.h >= 18) {
                    svg += '<text x="' + (n.x + nodeW / 2) + '" y="' + (n.y + n.h / 2 + 4) + '"' +
                           ' text-anchor="middle" font-size="10" font-weight="700" fill="white" font-family="Inter,sans-serif">' +
                           n.count + '</text>';
                }
            }
        }

        /* ── Time labels (top) ──────────────────────────────────────────── */
        for (var t = 0; t < nT; t++) {
            var tx = chartX + t * colStep + nodeW / 2;
            svg += '<line x1="' + tx + '" y1="' + (padTop - 10) + '" x2="' + tx + '" y2="' + (padTop - 4) + '"' +
                   ' stroke="' + tickColor + '" stroke-width="1.5"/>';
            svg += '<text x="' + tx + '" y="' + (padTop - 14) + '"' +
                   ' text-anchor="middle" font-size="11" font-weight="700" fill="' + textColor + '" font-family="Inter,sans-serif">' +
                   times[t] + '</text>';
        }

        /* ── Zone labels (left) ─────────────────────────────────────────── */
        for (var z = 0; z < nZ; z++) {
            var n = nodePos[0][z];
            var midY = n.y + n.h / 2;
            var color = ZONE_COLORS[z % ZONE_COLORS.length];
            svg += '<circle cx="' + (chartX - 12) + '" cy="' + midY + '" r="4" fill="' + color + '"/>';
            svg += '<text x="' + (chartX - 20) + '" y="' + (midY + 4) + '"' +
                   ' text-anchor="end" font-size="11" font-weight="600" fill="' + color + '" font-family="Inter,sans-serif">' +
                   zones[z] + '</text>';
        }

        /* ── Legend: movement direction hint ────────────────────────────── */
        var legendY = H - 8;
        var legendItems = [
            { label: 'Same zone (stayed)', opacity: 0.15 },
            { label: 'Changed zone (moved)', opacity: 0.42 },
        ];
        var lx = chartX;
        legendItems.forEach(function(item, i) {
            svg += '<rect x="' + lx + '" y="' + (legendY - 9) + '" width="24" height="9" rx="2" fill="' + ZONE_COLORS[0] + '" opacity="' + item.opacity + '"/>';
            svg += '<text x="' + (lx + 28) + '" y="' + legendY + '"' +
                   ' font-size="10" fill="' + textColor + '" font-family="Inter,sans-serif">' + item.label + '</text>';
            lx += 160;
        });

        svg += '</svg>';
        el.innerHTML = svg;
    }

    function init(el) {
        var cfg = {};
        try { cfg = JSON.parse(el.dataset.config || '{}'); } catch (e) {}

        render(el, cfg);

        /* Re-render on dark mode toggle */
        new MutationObserver(function () { render(el, cfg); })
            .observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });

        /* Re-render on container resize */
        if (window.ResizeObserver) {
            new ResizeObserver(function () { render(el, cfg); }).observe(el);
        }
    }

    function boot() { document.querySelectorAll('[data-chart-type="sankey"]').forEach(init); }

    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', boot); } else { boot(); }
})();
