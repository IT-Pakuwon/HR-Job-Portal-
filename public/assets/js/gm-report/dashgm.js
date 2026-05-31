(function () {
    'use strict';

    var routes = window.gmRoutes || {};

    // ── State ─────────────────────────────────────────────────────────────────
    var now   = new Date();
    var state = {
        cpnyId: '',
        year:   now.getFullYear(),
        month:  0,          // 0 = all year, 1-12 = specific month
        depts:  [],         // array of selected department IDs
    };

    var charts   = { donut: null, gauge: null, bar: null };
    var xhrSum   = null;
    var xhrComp  = null;
    var xhrDept  = null;

    var MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

    // ── Helpers ───────────────────────────────────────────────────────────────
    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function idr(val) {
        if (val === null || val === undefined || isNaN(val)) return '—';
        var v   = parseFloat(val);
        var abs = Math.abs(v);
        var s   = v < 0 ? '-' : '';
        if (abs >= 1e12) return s + 'Rp ' + (abs / 1e12).toFixed(1).replace('.', ',') + 'T';
        if (abs >= 1e9)  return s + 'Rp ' + (abs / 1e9).toFixed(1).replace('.', ',')  + 'M';
        if (abs >= 1e6)  return s + 'Rp ' + (abs / 1e6).toFixed(1).replace('.', ',')  + 'Jt';
        return s + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(abs));
    }

    function setText(id, v) {
        var el = document.getElementById(id);
        if (el) el.textContent = v;
    }

    function setTrend(id, pct) {
        var el = document.getElementById(id);
        if (!el) return;
        var up = pct >= 0;
        el.className = 'inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-bold ' +
            (up ? 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400'
                : 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400');
        el.innerHTML = (up ? '↑' : '↓') + ' ' + Math.abs(pct).toFixed(1) + '%';
    }

    function updatePeriodLabel() {
        var label = '';
        if (state.month > 0) {
            label = MONTHS[state.month - 1] + ' ' + state.year;
        } else {
            label = 'Full Year ' + state.year;
        }
        setText('gmPeriodLabel', '· ' + label);
    }

    function buildParams(extra) {
        var p = { year: state.year, month: state.month };
        if (state.cpnyId) p.cpny_id = state.cpnyId;
        if (state.depts && state.depts.length) {
            // append as departments[]=
            var base = Object.keys(p).map(function(k) {
                return encodeURIComponent(k) + '=' + encodeURIComponent(p[k]);
            }).join('&');
            var deptStr = state.depts.map(function(d) {
                return 'departments[]=' + encodeURIComponent(d);
            }).join('&');
            return '?' + base + (deptStr ? '&' + deptStr : '');
        }
        var merged = Object.assign({}, p, extra || {});
        return '?' + Object.keys(merged).map(function(k) {
            return encodeURIComponent(k) + '=' + encodeURIComponent(merged[k]);
        }).join('&');
    }

    // ── Year select ───────────────────────────────────────────────────────────
    function loadYears() {
        fetch(routes.years, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var sel = document.getElementById('gmYear');
                if (!sel) return;
                var years = res.data || [now.getFullYear()];
                sel.innerHTML = '';
                years.forEach(function(y) {
                    var o = document.createElement('option');
                    o.value = y;
                    o.textContent = y;
                    if (String(y) === String(state.year)) o.selected = true;
                    sel.appendChild(o);
                });
            })
            .catch(function() {
                // fallback: populate with current year
                var sel = document.getElementById('gmYear');
                if (!sel) return;
                sel.innerHTML = '<option value="' + now.getFullYear() + '">' + now.getFullYear() + '</option>';
            });
    }

    // ── Company filter ────────────────────────────────────────────────────────
    function loadCompanies() {
        fetch(routes.companies, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var locked = res.locked;
                var list   = res.data || [];

                if (locked) {
                    var lockedEl = document.getElementById('gmCompanyLocked');
                    var dropEl   = document.getElementById('gmCompanyDropdown');
                    if (lockedEl) { lockedEl.classList.remove('hidden'); lockedEl.classList.add('flex'); }
                    if (dropEl)   dropEl.classList.add('hidden');
                    setText('gmCompanyLockedText', res.single || (list[0] || ''));
                    state.cpnyId = res.single || (list[0] || '');
                } else {
                    var sel = document.getElementById('gmCompanyFilter');
                    if (!sel) return;
                    sel.innerHTML = '<option value="">All Companies</option>';
                    list.forEach(function(c) {
                        var o = document.createElement('option');
                        o.value = c;
                        o.textContent = c;
                        sel.appendChild(o);
                    });
                }
            })
            .catch(function() {});
    }

    // ── Department multi-select (select2) ─────────────────────────────────────
    function loadDepartments() {
        var params = '?year=' + state.year + (state.cpnyId ? '&cpny_id=' + encodeURIComponent(state.cpnyId) : '');
        fetch(routes.departments + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' }
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var sel    = document.getElementById('gmDeptFilter');
                if (!sel) return;

                var current = state.depts.slice(); // preserve selection

                sel.innerHTML = '';
                (res.data || []).forEach(function(d) {
                    var o = document.createElement('option');
                    o.value = d;
                    o.textContent = d;
                    if (current.indexOf(d) !== -1) o.selected = true;
                    sel.appendChild(o);
                });

                // (re-)initialise select2
                if (window.$ && $.fn.select2) {
                    $(sel).select2({
                        placeholder:  'All Departments',
                        allowClear:   true,
                        width:        '100%',
                        closeOnSelect: false,
                    }).off('change.gm').on('change.gm', function() {
                        state.depts = $(this).val() || [];
                        loadByDept();
                    });
                }
            })
            .catch(function() {});
    }

    // ── Charts ────────────────────────────────────────────────────────────────
    function renderDonut(used, reserve, remaining) {
        var dark = isDark();
        var opts = {
            series: [
                Math.max(0, Math.round(used)),
                Math.max(0, Math.round(reserve)),
                Math.max(0, Math.round(remaining)),
            ],
            labels: ['Used', 'Reserved', 'Remaining'],
            chart: {
                type: 'donut', height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            colors: ['#EF4444', '#F59E0B', '#10B981'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '70%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true, label: 'Total',
                                fontSize: '12px', fontWeight: 600,
                                color: dark ? '#94A3B8' : '#64748B',
                                formatter: function(w) {
                                    return idr(w.globals.seriesTotals.reduce(function(a, b) { return a + b; }, 0));
                                },
                            },
                            value: {
                                fontSize: '16px', fontWeight: 700,
                                color: dark ? '#F8FAFC' : '#0F172A',
                                formatter: function(v) { return idr(parseFloat(v)); },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            stroke: { width: 0 },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return idr(v); } } },
            legend: {
                show: true, position: 'bottom', fontSize: '12px',
                markers: { radius: 6 }, itemMargin: { horizontal: 8, vertical: 4 },
            },
        };
        var el = document.getElementById('gmBudgetDonut');
        if (!el) return;
        if (charts.donut) { charts.donut.updateOptions(opts); return; }
        charts.donut = new ApexCharts(el, opts);
        charts.donut.render();
    }

    function renderGauge(pct) {
        var dark  = isDark();
        var p     = Math.min(100, Math.max(0, parseFloat(pct) || 0));
        var color = p >= 80 ? '#EF4444' : (p >= 60 ? '#F59E0B' : '#10B981');
        var grad  = p >= 80 ? '#F43F5E' : (p >= 60 ? '#FBC02D' : '#06B6D4');

        var opts = {
            series: [p],
            chart: {
                type: 'radialBar', height: 300,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
            },
            colors: [color],
            plotOptions: {
                radialBar: {
                    startAngle: -135, endAngle: 135,
                    hollow: { size: '65%', background: 'transparent' },
                    track: { background: dark ? '#1E293B' : '#F1F5F9', strokeWidth: '100%' },
                    dataLabels: {
                        show: true,
                        name: {
                            show: true, fontSize: '12px', fontWeight: 600,
                            color: dark ? '#94A3B8' : '#64748B', offsetY: 22,
                        },
                        value: {
                            show: true, fontSize: '28px', fontWeight: 700,
                            color: dark ? '#F8FAFC' : '#0F172A', offsetY: -8,
                            formatter: function(v) { return v + '%'; },
                        },
                    },
                },
            },
            labels: ['Utilization'],
            fill: {
                type: 'gradient',
                gradient: { shade: 'dark', type: 'horizontal', gradientToColors: [grad], stops: [0, 100] },
            },
            stroke: { lineCap: 'round' },
        };

        var el = document.getElementById('gmBudgetGauge');
        if (!el) return;
        if (charts.gauge) { charts.gauge.updateOptions(opts); return; }
        charts.gauge = new ApexCharts(el, opts);
        charts.gauge.render();
    }

    function renderBarByCompany(rows) {
        var dark = isDark();
        var cats = rows.map(function(r) { return r.cpny_id; });
        var used = rows.map(function(r) { return Math.round(parseFloat(r.total_used    || 0)); });
        var res  = rows.map(function(r) { return Math.round(parseFloat(r.total_reserve || 0)); });
        var rem  = rows.map(function(r) { return Math.round(parseFloat(r.total_remaining || 0)); });

        var opts = {
            series: [
                { name: 'Used',      data: used },
                { name: 'Reserved',  data: res  },
                { name: 'Remaining', data: rem  },
            ],
            chart: {
                type: 'bar', height: 300, stacked: true,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            colors: ['#EF4444', '#F59E0B', '#10B981'],
            plotOptions: {
                bar: {
                    horizontal: false, columnWidth: '55%',
                    borderRadius: 4, borderRadiusApplication: 'end',
                },
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: cats,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '11px' } },
            },
            yaxis: {
                labels: { style: { fontSize: '11px' }, formatter: function(v) { return idr(v); } },
            },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4 },
            tooltip: { theme: dark ? 'dark' : 'light', y: { formatter: function(v) { return idr(v); } } },
            legend: {
                show: true, position: 'top', horizontalAlign: 'right',
                fontSize: '12px', markers: { radius: 6 },
            },
        };

        var el = document.getElementById('gmBudgetByCompany');
        if (!el) return;
        if (charts.bar) { charts.bar.updateOptions(opts); return; }
        charts.bar = new ApexCharts(el, opts);
        charts.bar.render();
    }

    // ── Department Table ──────────────────────────────────────────────────────
    function renderDeptTable(rows) {
        var tbody = document.getElementById('gmDeptTableBody');
        if (!tbody) return;

        setText('gmDeptCount', rows.length + ' dept' + (rows.length !== 1 ? 's' : ''));

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
            return;
        }

        var html = '';
        rows.forEach(function(r) {
            var pct      = parseFloat(r.used_pct || 0);
            var pctCls   = pct >= 80 ? 'text-red-600 dark:text-red-400 font-bold'
                         : pct >= 60 ? 'text-amber-600 dark:text-amber-400 font-semibold'
                         : 'text-emerald-600 dark:text-emerald-400';

            // Progress bar width capped at 100%
            var barW     = Math.min(100, pct);
            var barColor = pct >= 80 ? '#EF4444' : (pct >= 60 ? '#F59E0B' : '#10B981');

            html += '<tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/30">';
            html += '<td class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-200">'
                  + escHtml(r.department_fin_id) + '</td>';
            html += '<td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">'
                  + idr(r.total_final) + '</td>';
            html += '<td class="px-4 py-3 text-right tabular-nums text-red-600 dark:text-red-400">'
                  + idr(r.total_used) + '</td>';
            html += '<td class="px-4 py-3 text-right tabular-nums text-amber-600 dark:text-amber-400">'
                  + idr(r.total_reserve) + '</td>';
            html += '<td class="px-4 py-3 text-right tabular-nums text-emerald-600 dark:text-emerald-400">'
                  + idr(r.total_remaining) + '</td>';
            html += '<td class="px-4 py-3">'
                  + '<div class="flex items-center gap-2">'
                  + '<div class="h-1.5 w-20 shrink-0 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-700">'
                  + '<div class="h-full rounded-full" style="width:' + barW + '%;background:' + barColor + '"></div>'
                  + '</div>'
                  + '<span class="tabular-nums ' + pctCls + '">' + pct.toFixed(1) + '%</span>'
                  + '</div>'
                  + '</td>';
            html += '</tr>';
        });

        tbody.innerHTML = html;
    }

    function escHtml(s) {
        return String(s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // ── Loaders ───────────────────────────────────────────────────────────────
    function loadSummary() {
        if (xhrSum) xhrSum.abort();
        xhrSum = new AbortController();

        ['gmTotalBudget','gmTotalUsed','gmTotalReserve','gmTotalRemaining'].forEach(function(id) {
            setText(id, '…');
        });
        updatePeriodLabel();

        fetch(routes.summary + buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrSum.signal,
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                var d = res.data || {};
                setText('gmTotalBudget',    idr(d.total_budget));
                setText('gmTotalUsed',      idr(d.total_used));
                setText('gmTotalReserve',   idr(d.total_reserve));
                setText('gmTotalRemaining', idr(d.total_remaining));
                setTrend('gmUtilTrend',     d.utilization_pct);
                renderDonut(d.total_used, d.total_reserve, d.total_remaining);
                renderGauge(d.utilization_pct);
                setText('gmRefreshTime', new Date().toLocaleTimeString());
            })
            .catch(function(e) { if (e.name !== 'AbortError') console.error('summary:', e); });
    }

    function loadByCompany() {
        if (xhrComp) xhrComp.abort();
        xhrComp = new AbortController();

        // by-company uses cpny+year+month but NOT department filter
        var p = '?year=' + state.year + '&month=' + state.month
              + (state.cpnyId ? '&cpny_id=' + encodeURIComponent(state.cpnyId) : '');

        fetch(routes.byCompany + p, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrComp.signal,
        })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if ((res.data || []).length) renderBarByCompany(res.data);
            })
            .catch(function(e) { if (e.name !== 'AbortError') console.error('by company:', e); });
    }

    function loadByDept() {
        if (xhrDept) xhrDept.abort();
        xhrDept = new AbortController();

        setText('gmDeptCount', '…');

        // Build dept params manually
        var paramParts = [
            'year=' + state.year,
            'month=' + state.month,
        ];
        if (state.cpnyId) paramParts.push('cpny_id=' + encodeURIComponent(state.cpnyId));
        state.depts.forEach(function(d) {
            paramParts.push('departments[]=' + encodeURIComponent(d));
        });

        fetch(routes.byDept + '?' + paramParts.join('&'), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrDept.signal,
        })
            .then(function(r) { return r.json(); })
            .then(function(res) { renderDeptTable(res.data || []); })
            .catch(function(e) { if (e.name !== 'AbortError') console.error('by dept:', e); });
    }

    function loadAll() {
        loadSummary();
        loadByCompany();
        loadByDept();
    }

    // ── Dark-mode watcher ─────────────────────────────────────────────────────
    function watchDarkMode() {
        new MutationObserver(function() {
            var dark = isDark();
            Object.values(charts).forEach(function(c) {
                if (!c) return;
                c.updateOptions({
                    chart:   { foreColor: dark ? '#94A3B8' : '#64748B' },
                    grid:    { borderColor: dark ? '#1E293B' : '#F1F5F9' },
                    tooltip: { theme: dark ? 'dark' : 'light' },
                });
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Event binding ─────────────────────────────────────────────────────────
    function bindEvents() {
        // Company
        var cpny = document.getElementById('gmCompanyFilter');
        if (cpny) {
            cpny.addEventListener('change', function() {
                state.cpnyId = this.value;
                loadDepartments(); // reload dept list when company changes
                loadAll();
            });
        }

        // Year
        var yr = document.getElementById('gmYear');
        if (yr) {
            yr.addEventListener('change', function() {
                state.year = parseInt(this.value, 10);
                loadDepartments();
                loadAll();
            });
        }

        // Month
        var mo = document.getElementById('gmMonth');
        if (mo) {
            mo.addEventListener('change', function() {
                state.month = parseInt(this.value, 10);
                loadAll();
            });
        }

        // Refresh
        var btn = document.getElementById('gmRefreshBtn');
        if (btn) {
            btn.addEventListener('click', function() {
                loadDepartments();
                loadAll();
            });
        }
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        // Set current month in the month select
        var mo = document.getElementById('gmMonth');
        if (mo) mo.value = state.month;

        loadYears();
        loadCompanies();
        loadDepartments();
        watchDarkMode();
        bindEvents();
        loadAll();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
