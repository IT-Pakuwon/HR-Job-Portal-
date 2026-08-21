(function () {
    'use strict';

    // Depends on gm-core.js (window.gmState, window.gmUtils, window.gmRoutes)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts = { statusChart: null };

    var xhrSummary        = null;
    var xhrByType          = null;
    var xhrTimeline        = null;
    var xhrStatusByCompany = null;

    function createResponsiveChart(el, opts) {
        var chart = new ApexCharts(el, opts);
        chart.render();

        var resizeObs = new ResizeObserver(function () {
            try { chart.reflow(); } catch (e) {}
        });
        if (el.parentElement) resizeObs.observe(el.parentElement);

        return chart;
    }

    function barHeight(n, extra) {
        return Math.max(180, n * 52 + (extra || 40));
    }

    // ── Summary KPIs ───────────────────────────────────────────────────────────
    function renderSummary(d) {
        utils.setText('eventTotalContract', utils.idr(d.total_contract));
        utils.setText('eventPaidRevenue', utils.idr(d.total_paid));
        utils.setText('eventTotalCount', d.total_count != null ? Number(d.total_count).toLocaleString('id-ID') : '—');
        utils.setText('eventAvgContract', utils.idr(d.avg_contract));
    }

    function loadSummary() {
        if (xhrSummary) xhrSummary.abort();
        xhrSummary = new AbortController();
        ['eventTotalContract', 'eventPaidRevenue', 'eventTotalCount', 'eventAvgContract']
            .forEach(function (id) { utils.setText(id, '…'); });
        fetch(routes.eventSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrSummary.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                renderSummary(res.data || {});
                lastEventSummary = res.data || {};
                computeEventInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event summary:', e); });
    }

    // ── GM Insight — draws on summary, by-type, and the Paid events list
    //    (ganttEvents, populated by loadTimeline below) together. ─────────────
    var lastEventSummary = null, lastEventByType = null;
    function computeEventInsights() {
        var d = lastEventSummary;
        if (!d) return;

        var gm = [];
        var totalContract = parseFloat(d.total_contract) || 0;
        var totalPaid = parseFloat(d.total_paid) || 0;

        if (totalContract > 0) {
            var realizedPct = totalPaid / totalContract * 100;
            var pipeline = totalContract - totalPaid;
            gm.push({ type: realizedPct >= 70 ? 'positive' : 'info', text: '<b>' + realizedPct.toFixed(0) + '%</b> of total contract value has been realized as Paid revenue (' + utils.idr(totalPaid) + '); ' + utils.idr(pipeline) + ' remains in the Booked/Confirmed pipeline.' });
        }

        if (lastEventByType && lastEventByType.length) {
            var top = lastEventByType.slice().sort(function (a, b) { return b.total_contract - a.total_contract; })[0];
            if (top && top.total_contract > 0) {
                gm.push({ type: 'info', text: '<b>' + utils.escHtml(top.event_type) + '</b> drives the most contract value at ' + utils.idr(top.total_contract) + ' across ' + Number(top.count).toLocaleString('id-ID') + ' event(s).' });
            }
        }

        if (ganttEvents && ganttEvents.length) {
            var ongoing = ganttEvents.filter(function (e) { return e.bucket === 'ongoing'; });
            if (ongoing.length) {
                gm.push({ type: 'positive', text: '<b>' + ongoing.length + '</b> Paid event(s) ongoing right now.' });
            }

            var upcoming30 = ganttEvents.filter(function (e) { return e.bucket === 'upcoming' && e.start_days <= 30; });
            if (upcoming30.length) {
                var upcomingValue = upcoming30.reduce(function (s, e) { return s + (parseFloat(e.contract) || 0); }, 0);
                gm.push({ type: 'info', text: '<b>' + upcoming30.length + '</b> Paid event(s) start within the next 30 days, worth ' + utils.idr(upcomingValue) + '.' });
            }

            var byCompany = {};
            ganttEvents.forEach(function (e) {
                byCompany[e.code] = (byCompany[e.code] || 0) + (parseFloat(e.contract) || 0);
            });
            var companies = Object.keys(byCompany);
            if (companies.length > 1) {
                var leader = companies.sort(function (a, b) { return byCompany[b] - byCompany[a]; })[0];
                gm.push({ type: 'positive', text: '<b>' + utils.escHtml(leader) + '</b> leads all companies in Paid event revenue at ' + utils.idr(byCompany[leader]) + '.' });
            }
        }

        utils.renderInsights('gmEventInsights', gm);
    }

    // ── Events by Type table ──────────────────────────────────────────────────
    function renderByType(rows) {
        var body = document.getElementById('eventByTypeBody');
        if (!body) return;

        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="4" class="py-6 text-center text-slate-400">No data</td></tr>';
            return;
        }

        body.innerHTML = rows.map(function (r) {
            return '<tr>'
                + '<td class="py-2 pr-2 font-medium text-slate-700 dark:text-slate-200">' + utils.escHtml(r.event_type) + '</td>'
                + '<td class="py-2 pr-2 text-right tabular-nums">' + Number(r.count).toLocaleString('id-ID') + '</td>'
                + '<td class="py-2 pr-2 text-right tabular-nums font-semibold text-slate-900 dark:text-white">' + utils.idr(r.total_contract) + '</td>'
                + '<td class="py-2 text-right tabular-nums">' + utils.idr(r.avg_contract) + '</td>'
                + '</tr>';
        }).join('');
    }

    function loadByType() {
        if (xhrByType) xhrByType.abort();
        xhrByType = new AbortController();
        fetch(routes.eventByType + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrByType.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                renderByType(res.data || []);
                lastEventByType = res.data || [];
                computeEventInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event by-type:', e); });
    }

    // ── Shared bar-chart renderer ─────────────────────────────────────────────
    // Used by both "By Status" (Booked/Confirmed/Paid) and "Paid Events
    // Timeline" (Ongoing/Upcoming/Past) — single bar per category, colored by
    // colorMap, when a company is selected; stacked (one segment per company,
    // with a per-company + per-event-name tooltip) when "All Companies" is
    // selected.
    var COMPANY_COLORS = { AW: '#8B5CF6', EP: '#3B82F6', PSA: '#10B981', GPS: '#F59E0B' };
    var COMPANY_FALLBACK = ['#06B6D4', '#EC4899', '#EF4444', '#84CC16', '#F97316', '#14B8A6'];
    var COMPANY_LABEL = { AW: 'GC', EP: 'KK', PSA: 'PBM', GPS: 'PMB' };

    function companyColor(code, idx) {
        return COMPANY_COLORS[code] || COMPANY_FALLBACK[idx % COMPANY_FALLBACK.length];
    }
    function companyLabel(code) {
        return COMPANY_LABEL[code] || code;
    }

    // Renders the "top N event names" list shared by both tooltip variants.
    function eventsListHtml(row, theme) {
        var events = row.events || [];
        if (!events.length) return '';

        var html = '<div style="margin-top:12px;padding-top:10px;border-top:1px solid ' + theme.divider + ';">'
                 + '<div style="font-size:9.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:' + theme.sub + ';margin-bottom:6px;">Top Events</div>'
                 + '<div style="max-height:170px;overflow-y:auto;margin:0 -6px;padding:0 6px;">';

        events.forEach(function (ev, i) {
            html += '<div style="display:flex;justify-content:space-between;align-items:center;gap:12px;padding:5px 6px;border-radius:7px;' + (i % 2 === 0 ? 'background:' + theme.chipBg + ';' : '') + '">'
                  + '<span style="color:' + theme.text + ';font-size:11.5px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;min-width:0;">' + utils.escHtml(ev.name || '—') + '</span>'
                  + '<span style="color:#10B981;font-size:11px;font-weight:700;flex-shrink:0;">' + utils.idr(ev.contract) + '</span>'
                  + '</div>';
        });

        if (row.events_more > 0) {
            html += '<div style="font-size:10.5px;color:' + theme.sub + ';padding:4px 6px;">+' + row.events_more + ' more…</div>';
        }

        return html + '</div></div>';
    }

    function themeColors() {
        var dark = utils.isDark();
        return {
            bg: dark ? '#111827' : '#ffffff',
            text: dark ? '#f1f5f9' : '#0f172a',
            sub: dark ? '#94a3b8' : '#64748b',
            divider: dark ? '#2a3548' : '#e7ebf1',
            chipBg: dark ? 'rgba(255,255,255,.06)' : 'rgba(15,23,42,.035)',
            chipBgHover: dark ? 'rgba(255,255,255,.10)' : 'rgba(15,23,42,.06)',
        };
    }

    function tooltipShell(theme, width) {
        return 'background:' + theme.bg + ';border-radius:14px;padding:16px;width:' + width + 'px;max-height:380px;overflow-y:auto;'
             + 'box-shadow:0 16px 40px -12px rgba(0,0,0,.35),0 0 0 1px ' + theme.divider + ';font-family:Inter,sans-serif;';
    }

    function tooltipHeader(theme, statusName, dotColor, totalCount, totalContract) {
        return '<div style="display:flex;align-items:center;gap:8px;">'
             +   '<span style="width:9px;height:9px;border-radius:50%;background:' + dotColor + ';box-shadow:0 0 0 3px ' + dotColor + '26;flex-shrink:0;"></span>'
             +   '<span style="font-weight:800;font-size:13.5px;color:' + theme.text + ';flex:1;">' + utils.escHtml(statusName || '') + '</span>'
             +   '<span style="font-size:10px;font-weight:700;color:' + theme.sub + ';background:' + theme.chipBg + ';padding:2px 7px;border-radius:999px;white-space:nowrap;">' + Number(totalCount || 0).toLocaleString('id-ID') + ' events</span>'
             + '</div>'
             + '<div style="font-size:19px;font-weight:800;color:' + dotColor + ';letter-spacing:-.015em;margin:7px 0 2px;">' + utils.idr(totalContract) + '</div>';
    }

    function makeStackedTooltip(data, allSites) {
        return function (opts) {
            var idx       = opts.dataPointIndex;
            var seriesIdx = opts.seriesIndex;
            var row       = data[idx] || {};
            var theme     = themeColors();
            var hovClr    = opts.w.globals.colors[seriesIdx] || '#8B5CF6';
            var total     = row.total || 0;

            var html = '<div style="' + tooltipShell(theme, 260) + '">'
                     + tooltipHeader(theme, row.status, hovClr, total, row.total_contract)
                     + '<div style="display:flex;height:6px;border-radius:999px;overflow:hidden;margin:10px 0 8px;background:' + theme.chipBg + ';">';

            allSites.forEach(function (site, si) {
                var cnt = (row.by_site && row.by_site[site]) || 0;
                var pct = total > 0 ? (cnt / total * 100) : 0;
                if (pct <= 0) return;
                html += '<span style="width:' + pct + '%;background:' + companyColor(site, si) + ';"></span>';
            });
            html += '</div>';

            allSites.forEach(function (site, si) {
                var cnt   = (row.by_site && row.by_site[site]) || 0;
                var clr   = companyColor(site, si);
                var isHov = si === seriesIdx;
                html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 6px;margin:0 -6px;border-radius:7px;'
                      +   'background:' + (isHov ? theme.chipBgHover : 'transparent') + ';">'
                      + '<span style="width:7px;height:7px;border-radius:50%;background:' + clr + ';display:inline-block;flex-shrink:0;"></span>'
                      + '<span style="font-size:11.5px;font-weight:' + (isHov ? '700' : '500') + ';color:' + (isHov ? theme.text : theme.sub) + ';flex:1;">' + utils.escHtml(companyLabel(site)) + '</span>'
                      + '<span style="font-size:11.5px;font-weight:800;color:' + clr + ';">' + Number(cnt).toLocaleString('id-ID') + '</span>'
                      + '</div>';
            });

            html += eventsListHtml(row, theme);

            return html + '</div>';
        };
    }

    function makeSimpleTooltip(data, colorMap) {
        return function (opts) {
            var row   = data[opts.dataPointIndex] || {};
            var theme = themeColors();
            var hovClr = colorMap[row.status] || '#8B5CF6';

            var html = '<div style="' + tooltipShell(theme, 240) + '">'
                     + tooltipHeader(theme, row.status, hovClr, row.total, row.total_contract)
                     + eventsListHtml(row, theme);

            return html + '</div>';
        };
    }

    function renderBarChart(elId, chartsKey, res, colorMap) {
        var data     = (res && res.data)      || [];
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById(elId);
        if (!el) return;
        if (charts[chartsKey]) { charts[chartsKey].destroy(); charts[chartsKey] = null; }

        if (!data.length || !data.some(function (s) { return s.total > 0; })) {
            el.innerHTML = '<p class="py-10 text-center text-xs text-slate-400">No data</p>';
            return;
        }

        var dark = utils.isDark();
        var cats = data.map(function (r) { return r.status; });
        var opts;

        if (stacked && allSites.length > 1) {
            var series = allSites.map(function (site) {
                return { name: companyLabel(site), data: data.map(function (r) { return (r.by_site && r.by_site[site]) || 0; }) };
            });
            var colors = allSites.map(function (site, si) { return companyColor(site, si); });

            opts = {
                series: series,
                chart: {
                    type: 'bar', height: barHeight(cats.length, 80), width: '100%', stacked: true,
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 500 },
                    redrawOnWindowResize: true, redrawOnParentResize: true,
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 0, barHeight: '55%' } },
                colors: colors,
                dataLabels: { enabled: false },
                xaxis: {
                    categories: cats,
                    labels: { style: { fontSize: '11px' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '11px' } } },
                grid: {
                    borderColor: dark ? '#334155' : '#E2E8F0',
                    xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
                },
                legend: {
                    show: true, position: 'top', horizontalAlign: 'right',
                    fontSize: '11px', fontWeight: 600,
                    markers: { radius: 4, size: 7 },
                    itemMargin: { horizontal: 8, vertical: 0 },
                },
                tooltip: {
                    custom: makeStackedTooltip(data, allSites),
                    fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 38 },
                },
            };
        } else {
            var vals    = data.map(function (r) { return r.total; });
            var colors2 = data.map(function (r) { return colorMap[r.status] || '#94A3B8'; });

            opts = {
                series: [{ name: 'Events', data: vals }],
                chart: {
                    type: 'bar', height: barHeight(cats.length), width: '100%',
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 500 },
                    redrawOnWindowResize: true, redrawOnParentResize: true,
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%', distributed: true } },
                dataLabels: {
                    enabled: true,
                    style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] },
                    formatter: function (v) { return v; },
                },
                colors: colors2,
                xaxis: {
                    categories: cats,
                    labels: { style: { fontSize: '11px' } },
                    axisBorder: { show: false }, axisTicks: { show: false },
                },
                yaxis: { labels: { style: { fontSize: '11px' } } },
                grid: {
                    borderColor: dark ? '#334155' : '#E2E8F0',
                    xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
                },
                legend: { show: false },
                tooltip: {
                    custom: makeSimpleTooltip(data, colorMap),
                    fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 },
                },
            };
        }

        charts[chartsKey] = createResponsiveChart(el, opts);
    }

    // ── By Status (Booked / Confirmed / Paid) ─────────────────────────────────
    var STATUS_COLORS = { Booked: '#3B82F6', Confirmed: '#F59E0B', Paid: '#10B981' };

    function loadStatusByCompany() {
        if (xhrStatusByCompany) xhrStatusByCompany.abort();
        xhrStatusByCompany = new AbortController();
        fetch(routes.eventStatusByCompany + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrStatusByCompany.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderBarChart('eventStatusChart', 'statusChart', res, STATUS_COLORS); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event status-by-company:', e); });
    }

    // ── Paid Events Timeline — Gantt strip on a shared, auto-scaling axis ─────
    var BUCKET_DOT = { ongoing: '#10B981', upcoming: '#3B82F6', past: '#94A3B8' };
    var GANTT_BADGE = {
        GC:  { bg: '#EDE9FE', fg: '#7C3AED' }, KK:  { bg: '#DBEAFE', fg: '#2563EB' },
        PBM: { bg: '#D1FAE5', fg: '#059669' }, PMB: { bg: '#FEF3C7', fg: '#D97706' },
    };
    var GANTT_BADGE_FALLBACK = { bg: '#F1F5F9', fg: '#475569' };

    function ganttBadge(code) { return GANTT_BADGE[code] || GANTT_BADGE_FALLBACK; }

    function relativeDayText(bucket, days) {
        if (bucket === 'ongoing') return days > 0 ? ('Ends ' + days + 'd') : 'Ends today';
        if (bucket === 'upcoming') return days > 0 ? ('Starts ' + days + 'd') : 'Starts today';
        return days > 0 ? (days + 'd ago') : 'Ended today';
    }

    function fmtDayTick(v) {
        if (v === 0) return 'Today';
        return (v > 0 ? '+' : '') + v + 'd';
    }

    // Domain always includes "today" (0) plus a small padding margin so bars
    // and the today-line never sit flush against the card edge.
    function ganttDomain(events) {
        var min = 0, max = 0;
        events.forEach(function (ev) {
            if (ev.start_days < min) min = ev.start_days;
            if (ev.end_days > max) max = ev.end_days;
        });
        if (min === 0 && max === 0) { min = -2; max = 5; }
        var pad = Math.max(1, Math.round((max - min) * 0.08));
        return { min: min - pad, max: max + pad };
    }

    function renderGantt(events) {
        var axisEl = document.getElementById('eventGanttAxis');
        var rowsEl = document.getElementById('eventGanttRows');
        if (!axisEl || !rowsEl) return;

        if (!events.length) {
            axisEl.innerHTML = '';
            rowsEl.innerHTML = '<p class="py-6 text-center text-xs text-slate-400">No paid events in this period</p>';
            return;
        }

        var domain = ganttDomain(events);
        var span   = domain.max - domain.min;
        var pct    = function (v) { return ((v - domain.min) / span) * 100; };

        var ticks = [];
        for (var i = 0; i <= 4; i++) { ticks.push(Math.round(domain.min + (span * i / 4))); }
        axisEl.innerHTML = ticks.map(function (v, i) {
            var style = i === 0 ? 'left:0' : (i === 4 ? 'right:0' : ('left:' + pct(v) + '%;transform:translateX(-50%)'));
            var cls = v === 0 ? 'text-red-500 dark:text-red-400 font-bold' : '';
            return '<span class="absolute whitespace-nowrap ' + cls + '" style="' + style + '">' + fmtDayTick(v) + '</span>';
        }).join('');

        rowsEl.innerHTML = events.map(function (ev) {
            var dot   = BUCKET_DOT[ev.bucket] || '#94A3B8';
            var badge = ganttBadge(ev.code);
            var left  = pct(ev.start_days);
            var width = Math.max(pct(ev.end_days) - left, 1.5);
            var todayLeft = pct(0);

            return '<div class="grid grid-cols-[150px_1fr] items-center gap-3">'
                + '<div class="flex min-w-0 items-center gap-1.5">'
                +   '<span class="shrink-0 rounded px-1 py-0.5 text-[9px] font-extrabold" style="background:' + badge.bg + ';color:' + badge.fg + '">' + utils.escHtml(ev.code) + '</span>'
                +   '<span class="truncate text-xs font-semibold text-slate-700 dark:text-slate-200">' + utils.escHtml(ev.name || '—') + '</span>'
                + '</div>'
                + '<div class="relative h-6">'
                +   '<div class="absolute inset-x-0 top-1/2 h-px bg-slate-100 dark:bg-slate-700"></div>'
                +   '<div class="absolute -top-1 -bottom-1 border-l border-dashed border-red-400/70 dark:border-slate-300/70" style="left:' + todayLeft + '%"></div>'
                +   '<div class="absolute top-1/2 h-2.5 -translate-y-1/2 rounded-full" style="left:' + left + '%;width:' + width + '%;min-width:10px;background:' + dot + '"></div>'
                +   '<span class="absolute top-1/2 -translate-y-1/2 whitespace-nowrap text-[9px] font-bold uppercase" style="left:calc(' + (left + width) + '% + 8px);color:' + dot + '">' + relativeDayText(ev.bucket, ev.days) + '</span>'
                + '</div>'
                + '</div>';
        }).join('');
    }

    // ── Per-company filter buttons above the Gantt ────────────────────────────
    // When the dashboard's own company filter is "All Companies", these let
    // you narrow the Gantt to one mall without touching the global filter.
    // Once the global filter already picks a company, the events array only
    // ever contains that one company anyway — so there's nothing to switch
    // between; the row just shows that company as a fixed, non-clickable pill.
    var ganttEvents = [];
    var ganttActiveCompany = null;

    function ganttCompanies() {
        var seen = {};
        var list = [];
        ganttEvents.forEach(function (ev) {
            if (!seen[ev.cpny]) { seen[ev.cpny] = true; list.push({ cpny: ev.cpny, code: ev.code }); }
        });
        list.sort(function (a, b) { return a.code < b.code ? -1 : a.code > b.code ? 1 : 0; });
        return list;
    }

    function renderGanttCompanyFilter() {
        var el = document.getElementById('eventGanttCompanyFilter');
        if (!el) return;

        var companies = ganttCompanies();
        var globalCpny = window.gmState ? window.gmState.cpnyId : '';

        // Global filter already scopes to one company — show it as a fixed
        // badge, nothing else to pick from.
        if (globalCpny || companies.length <= 1) {
            el.innerHTML = companies.map(function (c) {
                var badge = ganttBadge(c.code);
                return '<span class="rounded-full px-2.5 py-1 text-[10.5px] font-bold" style="background:' + badge.bg + ';color:' + badge.fg + '">' + utils.escHtml(c.code) + '</span>';
            }).join('');
            return;
        }

        var buttons = ['<button type="button" data-cpny="" class="gantt-cpny-btn rounded-full px-2.5 py-1 text-[10.5px] font-bold transition"'
            + (ganttActiveCompany ? ' style="background:#F1F5F9;color:#64748B"' : ' style="background:#0F172A;color:#fff"')
            + '>All</button>'];

        buttons = buttons.concat(companies.map(function (c) {
            var badge = ganttBadge(c.code);
            var active = ganttActiveCompany === c.cpny;
            var style = active
                ? ('background:' + badge.fg + ';color:#fff')
                : ('background:' + badge.bg + ';color:' + badge.fg);
            return '<button type="button" data-cpny="' + utils.escHtml(c.cpny) + '" class="gantt-cpny-btn rounded-full px-2.5 py-1 text-[10.5px] font-bold transition" style="' + style + '">' + utils.escHtml(c.code) + '</button>';
        }));

        el.innerHTML = buttons.join('');

        el.querySelectorAll('.gantt-cpny-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                ganttActiveCompany = btn.getAttribute('data-cpny') || null;
                renderGanttCompanyFilter();
                renderGantt(ganttActiveCompany
                    ? ganttEvents.filter(function (ev) { return ev.cpny === ganttActiveCompany; })
                    : ganttEvents);
            });
        });
    }

    function loadTimeline() {
        if (xhrTimeline) xhrTimeline.abort();
        xhrTimeline = new AbortController();
        var rowsEl = document.getElementById('eventGanttRows');
        if (rowsEl) rowsEl.innerHTML = '<p class="py-6 text-center text-xs text-slate-400">Loading…</p>';
        ganttActiveCompany = null;
        fetch(routes.eventStatusStrip + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTimeline.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                ganttEvents = res.data || [];
                renderGanttCompanyFilter();
                renderGantt(ganttEvents);
                computeEventInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('event timeline:', e); });
    }

    function loadAll() {
        loadSummary();
        loadByType();
        loadTimeline();
        loadStatusByCompany();
    }

    document.addEventListener('gm:filter', loadAll);

    // Re-flow charts rendered while their tab was hidden.
    document.addEventListener('gm:tab-switch', function (e) {
        if (!(e.detail && e.detail.tab === 'event')) return;
        setTimeout(function () {
            Object.keys(charts).forEach(function (key) {
                if (charts[key]) {
                    try { charts[key].updateOptions({}); } catch (err) {}
                }
            });
        }, 50);
    });

    if (window.gmState && window.gmState.dateFrom) { loadAll(); }
})();
