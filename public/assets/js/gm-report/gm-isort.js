(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils, window.gmRoutes)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts   = { kaizenType: null, incident: null, dept: null, monthlyTrend: null, topAreas: null };
    var deptData = []; // full dept objects — read by custom tooltip

    var xhrSummary      = null;
    var xhrKaizenType   = null;
    var xhrIncident     = null;
    var xhrDept         = null;
    var xhrMonthlyTrend = null;
    var xhrTopAreas     = null;

    function fullLabel(val) {
        return (val || '').toString().toUpperCase();
    }

    function barHeight(n, extra) {
        return Math.max(260, n * 42 + (extra || 40));
    }

    // Site → display color (consistent across all 3 charts)
    var SITE_COLORS = {
        'GC': '#8B5CF6', 'KK': '#3B82F6', 'PBM': '#10B981', 'PMB': '#F59E0B',
    };
    var SITE_FALLBACK = ['#06B6D4','#EC4899','#EF4444','#84CC16','#F97316','#14B8A6'];

    function siteColor(site, idx) {
        return SITE_COLORS[site] || SITE_FALLBACK[idx % SITE_FALLBACK.length];
    }

    // ── Single-bar chart ──────────────────────────────────────────────────────
    function buildBarOpts(categories, values, colorPalette, tooltipSuffix) {
        var dark = utils.isDark();
        return {
            series: [{ name: 'Total', data: values }],
            chart: {
                type: 'bar', height: barHeight(categories.length), width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '50%', distributed: true } },
            dataLabels: {
                enabled: true,
                style: { fontSize: '11px', fontWeight: 600, colors: ['#fff'] },
                formatter: function (v) { return v; },
            },
            colors: colorPalette || ['#8B5CF6','#3B82F6','#06B6D4','#10B981','#F59E0B','#EF4444','#EC4899','#84CC16'],
            xaxis: {
                categories: categories,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { align: 'left', style: { fontSize: '11px' }, maxWidth: 240, formatter: fullLabel } },
            grid: {
                borderColor: dark ? '#334155' : '#E2E8F0',
                xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
            },
            legend: { show: false },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' ' + (tooltipSuffix || 'case(s)'); } },
            },
        };
    }

    // ── Custom tooltip factory for stacked type/incident charts ──────────────
    function makeStackedTooltip(data, allSites, labelKey, unitLabel) {
        return function (opts) {
            var idx       = opts.dataPointIndex;
            var seriesIdx = opts.seriesIndex;
            var row       = data[idx] || {};
            var label     = row[labelKey] || '';
            var total     = row.total    || 0;
            var hovClr    = opts.w.globals.colors[seriesIdx] || '#8B5CF6';

            var dark    = utils.isDark();
            var bg      = dark ? '#1e293b' : '#ffffff';
            var text    = dark ? '#e2e8f0' : '#1e293b';
            var sub     = dark ? '#94a3b8' : '#64748b';
            var divider = dark ? '#334155' : '#e2e8f0';

            var html = '<div style="background:' + bg + ';border-radius:10px;padding:11px 14px;min-width:230px;'
                     + 'box-shadow:0 4px 20px rgba(0,0,0,.2);font-family:Inter,sans-serif;border-left:3px solid ' + hovClr + ';">'
                     + '<div style="font-weight:700;font-size:12px;color:' + text + ';margin-bottom:2px;">' + utils.escHtml(label) + '</div>'
                     + '<div style="font-size:11px;color:' + sub + ';margin-bottom:8px;">Total <b style="color:' + text + ';font-size:13px;">'
                     +   Number(total).toLocaleString('id-ID') + '</b> ' + (unitLabel || 'case(s)') + '</div>'
                     + '<div style="height:1px;background:' + divider + ';margin-bottom:8px;"></div>';

            allSites.forEach(function (site, si) {
                var cnt   = (row.by_site && row.by_site[site]) || 0;
                var clr   = siteColor(site, si);
                var isHov = si === seriesIdx;
                html += '<div style="display:flex;align-items:center;gap:8px;padding:2px 4px;border-radius:4px;'
                      +   'background:' + (isHov ? (dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.04)') : 'transparent') + ';">'
                      + '<span style="width:8px;height:8px;border-radius:50%;background:' + clr + ';display:inline-block;flex-shrink:0;"></span>'
                      + '<span style="font-size:11px;font-weight:' + (isHov ? '700' : '400') + ';color:' + (isHov ? text : sub) + ';flex:1;">' + utils.escHtml(site) + '</span>'
                      + '<span style="font-size:11px;font-weight:700;color:' + clr + ';">' + Number(cnt).toLocaleString('id-ID') + '</span>'
                      + '</div>';
            });

            return html + '</div>';
        };
    }

    // ── Stacked bar chart (per-site breakdown) ────────────────────────────────
    function buildStackedOpts(categories, allSites, data, labelKey, tooltipFn) {
        var dark    = utils.isDark();
        var series  = allSites.map(function (site, si) {
            return {
                name: site,
                data: data.map(function (r) { return (r.by_site && r.by_site[site]) || 0; }),
            };
        });
        var colors = allSites.map(function (site, si) { return siteColor(site, si); });

        return {
            series: series,
            chart: {
                type: 'bar', height: barHeight(categories.length, 80), width: '100%', stacked: true,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 0, barHeight: '50%' } },
            colors: colors,
            dataLabels: { enabled: false },
            xaxis: {
                categories: categories,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { align: 'left', style: { fontSize: '11px' }, maxWidth: 240, formatter: fullLabel } },
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
            tooltip: tooltipFn
                ? { custom: tooltipFn, fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 } }
                : {
                    theme: dark ? 'dark' : 'light',
                    shared: true, intersect: false,
                    y: { formatter: function (v) { return Number(v).toLocaleString('id-ID'); } },
                },
        };
    }

    // ── Helper: width-responsive chart (reflows on container resize) ─────────
    function createResponsiveChart(el, opts) {
        var chart = new ApexCharts(el, opts);
        chart.render();

        var resizeObs = new ResizeObserver(function () {
            try { chart.reflow(); } catch (e) {}
        });
        if (el.parentElement) resizeObs.observe(el.parentElement);

        return chart;
    }

    // ── Kaizen by Type chart ──────────────────────────────────────────────────
    function renderKaizenTypeChart(res) {
        var data     = (res && res.data)      || (Array.isArray(res) ? res : []);
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById('isortKaizenTypeChart');
        if (!el) return;
        if (!data.length) { el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400">No data</p>'; return; }
        if (charts.kaizenType) { charts.kaizenType.destroy(); charts.kaizenType = null; }

        var opts;
        if (stacked && allSites.length > 1) {
            var cats = data.map(function (r) { return r.kaizen_type; });
            opts = buildStackedOpts(cats, allSites, data, 'kaizen_type', makeStackedTooltip(data, allSites, 'kaizen_type', 'kaizen'));
        } else {
            var cats = data.map(function (r) { return r.kaizen_type; });
            var vals = data.map(function (r) { return r.total; });
            opts = buildBarOpts(cats, vals, null, 'kaizen');
        }
        charts.kaizenType = createResponsiveChart(el, opts);
    }

    // ── Incidents by Name chart ───────────────────────────────────────────────
    function renderIncidentChart(res) {
        var data     = (res && res.data)      || (Array.isArray(res) ? res : []);
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById('isortIncidentChart');
        if (!el) return;
        if (!data.length) { el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400">No data</p>'; return; }
        if (charts.incident) { charts.incident.destroy(); charts.incident = null; }

        var opts;
        if (stacked && allSites.length > 1) {
            var cats = data.map(function (r) { return r.incident_name; });
            opts = buildStackedOpts(cats, allSites, data, 'incident_name', makeStackedTooltip(data, allSites, 'incident_name', 'case(s)'));
        } else {
            var cats   = data.map(function (r) { return r.incident_name; });
            var vals   = data.map(function (r) { return r.total; });
            var colors = ['#EF4444','#F59E0B','#10B981','#3B82F6','#8B5CF6','#EC4899','#06B6D4','#84CC16'];
            opts = buildBarOpts(cats, vals, colors, 'case(s)');
        }
        charts.incident = createResponsiveChart(el, opts);
    }

    // Stores current allSites list so the tooltip can show per-site breakdown
    var currentAllSites = [];

    // ── Dept chart — custom tooltip ───────────────────────────────────────────
    function deptTooltip(opts) {
        var idx       = opts.dataPointIndex;
        var seriesIdx = opts.seriesIndex; // which site segment is hovered (-1 if not stacked)
        var dept      = deptData[idx];
        if (!dept) return '';

        var esc     = utils.escHtml;
        var dark    = utils.isDark();
        var bg      = dark ? '#1e293b' : '#ffffff';
        var text    = dark ? '#e2e8f0' : '#1e293b';
        var sub     = dark ? '#94a3b8' : '#64748b';
        var divider = dark ? '#334155' : '#e2e8f0';
        // In stacked mode accent comes from hovered series color; in single bar use bar color
        var accent  = (seriesIdx >= 0 && opts.w.globals.colors[seriesIdx])
                    ? opts.w.globals.colors[seriesIdx]
                    : (opts.w.globals.colors[idx] || '#8B5CF6');

        var col = function (title, color, items, nameKey) {
            if (!items || !items.length) return '<div></div>';
            var h = '<div>'
                  + '<div style="font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:'
                  + color + ';margin-bottom:6px;border-bottom:1px solid ' + color + '33;padding-bottom:3px;">' + title + '</div>';
            items.slice(0, 3).forEach(function (item) {
                h += '<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;padding:2px 0;">'
                   + '<span style="color:' + sub + ';font-size:11px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">'
                   + esc(item[nameKey] || '—') + '</span>'
                   + '<span style="font-weight:800;font-size:12px;color:' + text + ';flex-shrink:0;">' + Number(item.count).toLocaleString('id-ID') + '</span>'
                   + '</div>';
            });
            return h + '</div>';
        };

        // Build BY SITE section when stacked data is available
        var siteSection = '';
        var bySite = dept.by_site;
        if (bySite && currentAllSites.length > 0) {
            siteSection = '<div style="margin-bottom:10px;">'
                + '<div style="font-size:9px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:#06B6D4;'
                +   'margin-bottom:6px;border-bottom:1px solid #06B6D433;padding-bottom:3px;">By Company</div>';
            currentAllSites.forEach(function (site, si) {
                var cnt      = bySite[site] || 0;
                var clr      = siteColor(site, si);
                var isHov    = seriesIdx >= 0 && si === seriesIdx;
                var rowBg    = isHov ? (dark ? 'rgba(255,255,255,.06)' : 'rgba(0,0,0,.04)') : 'transparent';
                var weight   = isHov ? '800' : '500';
                siteSection += '<div style="display:flex;align-items:center;gap:8px;padding:2px 4px;border-radius:4px;background:' + rowBg + ';">'
                    + '<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:' + clr + ';flex-shrink:0;"></span>'
                    + '<span style="font-size:11px;font-weight:' + weight + ';color:' + (isHov ? text : sub) + ';flex:1;">' + esc(site) + '</span>'
                    + '<span style="font-size:11px;font-weight:800;color:' + clr + ';">' + Number(cnt).toLocaleString('id-ID') + '</span>'
                    + '</div>';
            });
            siteSection += '</div>'
                + '<div style="height:1px;background:' + divider + ';margin-bottom:10px;"></div>';
        } else {
            siteSection = '<div style="height:1px;background:' + divider + ';margin-bottom:10px;"></div>';
        }

        return '<div style="background:' + bg + ';border-radius:12px;padding:12px 15px;min-width:300px;'
             + 'box-shadow:0 8px 24px rgba(0,0,0,.2);font-family:Inter,sans-serif;border-left:3px solid ' + accent + ';">'
             + '<div style="font-weight:800;font-size:14px;color:' + text + ';margin-bottom:2px;">' + esc(dept.department) + '</div>'
             + '<div style="font-size:11px;color:' + sub + ';margin-bottom:10px;">'
             +   'Total <b style="color:' + accent + ';font-size:13px;">' + Number(dept.total).toLocaleString('id-ID') + '</b> Issue</div>'
             + siteSection
             + '<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">'
             + col('By Kaizen Type', '#8B5CF6', dept.by_type, 'kaizen_type')
             + col('By Incident', '#EF4444', dept.by_incident, 'incident_name')
             + '</div>'
             + '</div>';
    }

    function renderDeptChart(res) {
        var data     = (res && res.data)      || (Array.isArray(res) ? res : []);
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        deptData = data;

        var el = document.getElementById('isortDeptChart');
        if (!el) return;
        if (!data.length) { el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400">No data</p>'; return; }

        var dark = utils.isDark();
        var cats = data.map(function (d) { return d.department; });
        var opts;

        currentAllSites = allSites; // expose to deptTooltip

        if (stacked && allSites.length > 1) {
            opts = buildStackedOpts(cats, allSites, data, 'department', deptTooltip);
            // Override tooltip to fixed position to avoid clipping
            opts.tooltip = {
                custom: deptTooltip,
                fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 },
            };
            // For stacked dept chart, remove borderRadius so bars stack cleanly
            opts.plotOptions.bar.barHeight = '60%';
        } else {
            var vals = data.map(function (d) { return d.total; });
            opts = {
            series: [{ name: 'Total Kaizen', data: vals }],
            chart: {
                type: 'bar', height: barHeight(cats.length), width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            plotOptions: {
                bar: { horizontal: true, borderRadius: 4, barHeight: '50%', distributed: true },
            },
            dataLabels: {
                enabled: true,
                style: { fontSize: '11px', fontWeight: 700, colors: ['#fff'] },
                formatter: function (v) { return v; },
            },
            colors: ['#8B5CF6','#3B82F6','#06B6D4','#10B981','#F59E0B','#EF4444','#EC4899','#84CC16','#F97316','#14B8A6'],
            xaxis: {
                categories: cats,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: { labels: { align: 'left', style: { fontSize: '12px', fontWeight: 500 }, maxWidth: 240, formatter: fullLabel } },
            grid: {
                borderColor: dark ? '#334155' : '#E2E8F0',
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
            },
            legend: { show: false },
            tooltip: {
                custom: deptTooltip,
                fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 },
            },
            };  // end single-bar opts
        }  // end else

        if (charts.dept) { charts.dept.destroy(); charts.dept = null; }
        charts.dept = createResponsiveChart(el, opts);
    }

    // ── KPI summary cards ─────────────────────────────────────────────────────
    function renderSummary(d) {
        utils.setText('isortTotalCase',    d.total_case    != null ? Number(d.total_case).toLocaleString('id-ID')    : '—');
        utils.setText('isortTotalOpen',    d.total_open    != null ? Number(d.total_open).toLocaleString('id-ID')    : '—');
        utils.setText('isortTotalClosed',  d.total_closed  != null ? Number(d.total_closed).toLocaleString('id-ID')  : '—');
        utils.setText('isortTotalOverdue', d.total_overdue != null ? Number(d.total_overdue).toLocaleString('id-ID') : '—');

        // Avg Resolution Time
        var solvedHours = parseFloat(d.solved_hours)      || 0;
        var solvedCount = parseInt(d.solved_case_count, 10) || 0;
        if (solvedCount > 0) {
            var avgHrs = solvedHours / solvedCount;
            var val, unit;
            if (avgHrs < 1) {
                val  = Math.round(avgHrs * 60).toString();
                unit = 'min to close';
            } else if (avgHrs < 48) {
                val  = avgHrs.toFixed(1);
                unit = 'hrs to close';
            } else {
                val  = (avgHrs / 24).toFixed(1);
                unit = 'days to close';
            }
            utils.setText('isortAvgResolution',     val);
            utils.setText('isortAvgResolutionUnit', unit);
        } else {
            utils.setText('isortAvgResolution',     '—');
            utils.setText('isortAvgResolutionUnit', 'hrs to close');
        }

        // Closure Rate
        var total  = parseInt(d.total_case,   10) || 0;
        var closed = parseInt(d.total_closed, 10) || 0;
        utils.setText('isortClosureRate', total > 0 ? Math.round((closed / total) * 100) + '%' : '—');
    }

    // ── Monthly Trend chart ───────────────────────────────────────────────────
    function renderMonthlyTrendChart(data) {
        var el = document.getElementById('isortMonthlyTrendChart');
        if (!el) return;
        if (!data.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400">No data</p>';
            return;
        }
        if (charts.monthlyTrend) { charts.monthlyTrend.destroy(); charts.monthlyTrend = null; }

        var dark = utils.isDark();
        var cats = data.map(function (r) { return r.month; });

        var opts = {
            series: [
                { name: 'Total',   data: data.map(function (r) { return r.total_case;    }) },
                { name: 'Closed',  data: data.map(function (r) { return r.total_closed;  }) },
                { name: 'Open',    data: data.map(function (r) { return r.total_open;    }) },
                { name: 'Overdue', data: data.map(function (r) { return r.total_overdue; }) },
            ],
            chart: {
                type: 'line', height: 260, width: '100%',
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                redrawOnWindowResize: true, redrawOnParentResize: true,
            },
            stroke: { curve: 'smooth', width: [3, 2.5, 2, 2] },
            colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444'],
            markers: { size: 4, strokeWidth: 0, hover: { size: 6 } },
            xaxis: {
                categories: cats,
                labels: { style: { fontSize: '11px' } },
                axisBorder: { show: false }, axisTicks: { show: false },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '11px' },
                    formatter: function (v) { return Math.round(v).toLocaleString('id-ID'); },
                },
                min: 0,
            },
            grid: {
                borderColor: dark ? '#334155' : '#E2E8F0',
                xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } },
            },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '11px', fontWeight: 600,
                markers: { radius: 4, size: 7 },
                itemMargin: { horizontal: 10, vertical: 0 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                shared: true, intersect: false,
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' case(s)'; } },
            },
        };

        charts.monthlyTrend = createResponsiveChart(el, opts);
    }

    // ── API loaders ───────────────────────────────────────────────────────────
    function loadSummary() {
        if (xhrSummary) xhrSummary.abort();
        xhrSummary = new AbortController();
        ['isortTotalCase','isortTotalOpen','isortTotalClosed','isortTotalOverdue']
            .forEach(function (id) { utils.setText(id, '…'); });
        fetch(routes.isortSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrSummary.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderSummary(res.data || {}); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort summary:', e); });
    }

    function loadKaizenByType() {
        if (xhrKaizenType) xhrKaizenType.abort();
        xhrKaizenType = new AbortController();
        fetch(routes.isortKaizenByType + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrKaizenType.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderKaizenTypeChart(res); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort kaizen-by-type:', e); });
    }

    function loadIncidents() {
        if (xhrIncident) xhrIncident.abort();
        xhrIncident = new AbortController();
        fetch(routes.isortIncidents + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrIncident.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderIncidentChart(res); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort incidents:', e); });
    }

    function loadDeptSummary() {
        if (xhrDept) xhrDept.abort();
        xhrDept = new AbortController();
        fetch(routes.isortDeptSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrDept.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderDeptChart(res); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort dept-summary:', e); });
    }

    // ── Top 10 Problem Areas chart ────────────────────────────────────────────
    function renderTopAreasChart(res) {
        var data     = (res && res.data)      || (Array.isArray(res) ? res : []);
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById('isortTopAreasChart');
        if (!el) return;
        if (!data.length) { el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400">No data</p>'; return; }
        if (charts.topAreas) { charts.topAreas.destroy(); charts.topAreas = null; }

        // Compact height: ~26px per bar so 10 items ≈ 300px — matches Monthly Trend card
        var h = Math.max(280, data.length * 26 + 40);

        var opts;
        if (stacked && allSites.length > 1) {
            var cats = data.map(function (r) { return r.area_name; });
            opts = buildStackedOpts(cats, allSites, data, 'area_name',
                makeStackedTooltip(data, allSites, 'area_name', 'issue(s)'));
        } else {
            var cats   = data.map(function (r) { return r.area_name; });
            var vals   = data.map(function (r) { return r.total; });
            var colors = ['#F59E0B','#EF4444','#8B5CF6','#06B6D4','#10B981','#3B82F6','#EC4899','#F97316','#84CC16','#14B8A6'];
            opts = buildBarOpts(cats, vals, colors, 'issue(s)');
        }
        // Override chart height to compact value
        opts.chart.height = h;
        opts.plotOptions = opts.plotOptions || {};
        opts.plotOptions.bar = opts.plotOptions.bar || {};
        opts.plotOptions.bar.barHeight = '55%';

        charts.topAreas = createResponsiveChart(el, opts);
    }

    function loadTopAreas() {
        if (!routes.isortTopAreas) return;
        if (xhrTopAreas) xhrTopAreas.abort();
        xhrTopAreas = new AbortController();
        fetch(routes.isortTopAreas + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTopAreas.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderTopAreasChart(res); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort top-areas:', e); });
    }

    function loadMonthlyTrend() {
        if (!routes.isortMonthlyTrend) return;
        if (xhrMonthlyTrend) xhrMonthlyTrend.abort();
        xhrMonthlyTrend = new AbortController();
        fetch(routes.isortMonthlyTrend + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrMonthlyTrend.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { renderMonthlyTrendChart(res.data || []); })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('isort monthly-trend:', e); });
    }

    function loadAll() {
        loadSummary();
        loadKaizenByType();
        loadIncidents();
        loadDeptSummary();
        loadMonthlyTrend();
        loadTopAreas();
    }

    // ── Department filter swap + chart resize on tab change ───────────────────
    document.addEventListener('gm:tab-switch', function (e) {
        var tab = e.detail && e.detail.tab;
        if (tab === 'isort' || tab === 'all') {
            // Swap dept panel to isort departments
            if (window.gmFilter && routes.isortAvailableDepts) {
                window.gmFilter.reloadDepts(routes.isortAvailableDepts);
            }
            // Re-trigger chart layout — charts rendered while hidden need a size update
            setTimeout(function () {
                Object.keys(charts).forEach(function (key) {
                    if (charts[key]) {
                        try { charts[key].updateOptions({}); } catch (err) {}
                    }
                });
            }, 50);
        } else {
            // Restore HR departments
            if (window.gmFilter) {
                window.gmFilter.reloadDepts();
            }
        }
    });

    document.addEventListener('gm:filter', loadAll);

    if (window.gmState && window.gmState.dateFrom) { loadAll(); }
})();
