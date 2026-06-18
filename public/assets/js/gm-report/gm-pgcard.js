(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts         = { customer: null, tenant: null, coupon: null };
    var couponSelected = null; // { label, val } when a segment is clicked, null = show total
    var customerData   = {};   // { mall_code: { mall_name, data: [{label,value,total_amount,txn_rank,amt_rank}] } }
    var tenantData     = {};
    var activeCustomer = null;
    var activeTenant   = null;
    var metricCustomer = 'transaction';   // 'transaction' | 'spending'
    var metricTenant   = 'transaction';
    var xhrCustomer    = null;
    var xhrTenant      = null;
    var xhrCoupon      = null;

    // ── IDR formatter ─────────────────────────────────────────────────────────
    function idr(val) {
        var v   = parseFloat(val) || 0;
        var abs = Math.abs(v);
        var s   = v < 0 ? '-' : '';
        if (abs >= 1e12) return s + 'Rp ' + (abs / 1e12).toFixed(1).replace('.', ',') + 'T';
        if (abs >= 1e9)  return s + 'Rp ' + (abs / 1e9) .toFixed(1).replace('.', ',') + 'M';
        if (abs >= 1e6)  return s + 'Rp ' + (abs / 1e6) .toFixed(1).replace('.', ',') + 'Jt';
        return s + 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.round(abs));
    }

    // ── Filter + sort by metric ───────────────────────────────────────────────
    function applyMetric(allRows, metric) {
        var useAmt = metric === 'spending';
        return allRows
            .filter(function (r) { return useAmt ? r.amt_rank <= 10 : r.txn_rank <= 10; })
            .sort(function (a, b) {
                return useAmt ? (b.total_amount - a.total_amount) : (b.value - a.value);
            })
            .slice(0, 10);
    }

    // ── Build mall tabs dynamically from data keys ────────────────────────────
    function buildTabs(containerId, data, activeCode, onTabClick) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';

        Object.keys(data).forEach(function (code) {
            var btn = document.createElement('button');
            btn.type          = 'button';
            btn.dataset.code  = code;
            btn.title         = data[code].mall_name || code;
            btn.textContent   = code;
            btn.className     = 'rounded-lg px-2 py-1 text-[10px] font-semibold transition '
                              + (code === activeCode ? 'pgcard-tab-active' : 'pgcard-tab-idle');
            btn.addEventListener('click', function () { onTabClick(code); });
            container.appendChild(btn);
        });
    }

    function setActiveTab(containerId, activeCode) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.querySelectorAll('button[data-code]').forEach(function (btn) {
            var isActive = btn.dataset.code === activeCode;
            btn.classList.toggle('pgcard-tab-active', isActive);
            btn.classList.toggle('pgcard-tab-idle',   !isActive);
        });
    }

    function setActiveMetric(prefix, active) {
        ['transaction', 'spending'].forEach(function (key) {
            var btn = document.getElementById(prefix + '_' + key);
            if (!btn) return;
            btn.classList.toggle('pgcard-metric-active', key === active);
            btn.classList.toggle('pgcard-metric-idle',   key !== active);
        });
    }

    // ── Bar chart options ─────────────────────────────────────────────────────
    function buildBarOpts(rows, color, metric) {
        var dark   = utils.isDark();
        var useAmt = metric === 'spending';
        var categories = rows.map(function (r) { return r.label; });
        var values     = rows.map(function (r) { return useAmt ? r.total_amount : r.value; });

        return {
            series: [{ name: useAmt ? 'Total Spending' : 'Transactions', data: values }],
            chart: {
                type: 'bar', height: 310,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
            colors: [color],
            fill: {
                type: 'gradient',
                gradient: {
                    type: 'horizontal',
                    gradientToColors: [color + 'aa'],
                    shadeIntensity: 0.4,
                    opacityFrom: 1, opacityTo: 0.7,
                    stops: [0, 100],
                },
            },
            xaxis: {
                categories: categories,
                axisBorder: { show: false },
                axisTicks:  { show: false },
                labels: {
                    style: { fontSize: '10px', fontWeight: 600 },
                    formatter: useAmt ? function (v) { return idr(v); } : undefined,
                },
            },
            yaxis: {
                labels: { style: { fontSize: '10px', fontWeight: 600 }, maxWidth: 110 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                fixed: { enabled: true, position: 'topRight', offsetX: -8, offsetY: 8 },
                custom: function (opts) {
                    var idx    = opts.dataPointIndex;
                    var row    = rows[idx] || {};
                    var txn    = Number(row.value        || 0).toLocaleString('id-ID');
                    var amt    = idr(row.total_amount    || 0);
                    var name   = utils.escHtml(row.label || '');
                    // Pick the right extra field based on active metric
                    var extraLabel = '';
                    var extraValue = '';
                    if (useAmt) {
                        if (row.top_merchant_amt) { extraLabel = 'Highest Spending Store'; extraValue = utils.escHtml(row.top_merchant_amt); }
                        else if (row.top_customer_amt) { extraLabel = 'Top Spender';       extraValue = utils.escHtml(row.top_customer_amt); }
                    } else {
                        if (row.top_merchant_txn) { extraLabel = 'Most Visited Store';     extraValue = utils.escHtml(row.top_merchant_txn); }
                        else if (row.top_customer_txn) { extraLabel = 'Top Visitor';       extraValue = utils.escHtml(row.top_customer_txn); }
                    }
                    var bg     = dark ? '#1e293b' : '#ffffff';
                    var text   = dark ? '#f1f5f9' : '#0f172a';
                    var sub    = dark ? '#94a3b8' : '#64748b';
                    var divider = '<div style="margin-top:8px;padding-top:8px;border-top:1px solid ' + (dark ? '#334155' : '#e2e8f0') + ';">';
                    var extraHtml = extraValue
                        ? divider
                          + '<div style="font-size:10px;color:' + sub + ';margin-bottom:2px;">' + extraLabel + '</div>'
                          + '<div style="font-size:11px;font-weight:700;color:' + color + ';white-space:normal;word-break:break-word;">' + extraValue + '</div>'
                          + '</div>'
                        : '';
                    return '<div style="padding:10px 14px;background:' + bg + ';border-radius:10px;min-width:180px;">'
                        + '<div style="font-size:11px;font-weight:700;color:' + text + ';margin-bottom:6px;white-space:normal;word-break:break-word;">' + name + '</div>'
                        + '<div style="font-size:10px;color:' + sub + ';margin-bottom:2px;">Transactions</div>'
                        + '<div style="font-size:13px;font-weight:700;color:' + color + ';margin-bottom:6px;">' + txn + ' txn</div>'
                        + '<div style="font-size:10px;color:' + sub + ';margin-bottom:2px;">Total Spending</div>'
                        + '<div style="font-size:13px;font-weight:700;color:' + text + ';">' + amt + '</div>'
                        + extraHtml
                        + '</div>';
                },
            },
            grid: {
                borderColor: dark ? '#334155' : '#F1F5F9',
                strokeDashArray: 4,
                xaxis: { lines: { show: true } },
                yaxis: { lines: { show: false } },
                padding: { left: 4, right: 12 },
            },
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                offsetX: 4,
                style: { fontSize: '9px', fontWeight: 700, colors: [dark ? '#e2e8f0' : '#334155'] },
                formatter: function (v, o) {
                    var row = rows[o.dataPointIndex] || {};
                    return useAmt
                        ? idr(v) + '  |  ' + Number(row.value || 0).toLocaleString('id-ID') + ' txn'
                        : Number(v).toLocaleString('id-ID') + ' txn  |  ' + idr(row.total_amount || 0);
                },
            },
            legend: { show: false },
        };
    }

    // ── Chart render ──────────────────────────────────────────────────────────
    function renderCustomerChart(code) {
        var el      = document.getElementById('pgcardCustomerChart');
        if (!el) return;
        var mall    = customerData[code];
        var rows    = applyMetric((mall && mall.data) ? mall.data : [], metricCustomer);

        if (charts.customer) { charts.customer.destroy(); charts.customer = null; }

        if (!rows.length) {
            el.innerHTML = '<div class="flex h-full items-center justify-center py-16 text-xs text-slate-400 dark:text-slate-500">No data available</div>';
            return;
        }
        charts.customer = new ApexCharts(el, buildBarOpts(rows, '#8B5CF6', metricCustomer));
        charts.customer.render();
    }

    function renderTenantChart(code) {
        var el   = document.getElementById('pgcardTenantChart');
        if (!el) return;
        var mall = tenantData[code];
        var rows = applyMetric((mall && mall.data) ? mall.data : [], metricTenant);

        if (charts.tenant) { charts.tenant.destroy(); charts.tenant = null; }

        if (!rows.length) {
            el.innerHTML = '<div class="flex h-full items-center justify-center py-16 text-xs text-slate-400 dark:text-slate-500">No data available</div>';
            return;
        }
        charts.tenant = new ApexCharts(el, buildBarOpts(rows, '#06B6D4', metricTenant));
        charts.tenant.render();
    }

    // ── Metric toggle binding ─────────────────────────────────────────────────
    function bindMetrics() {
        ['transaction', 'spending'].forEach(function (key) {
            var cb = document.getElementById('pgcardCustMetric_' + key);
            if (cb) {
                cb.addEventListener('click', function () {
                    metricCustomer = key;
                    setActiveMetric('pgcardCustMetric', key);
                    renderCustomerChart(activeCustomer);
                });
            }
            var tb = document.getElementById('pgcardTenMetric_' + key);
            if (tb) {
                tb.addEventListener('click', function () {
                    metricTenant = key;
                    setActiveMetric('pgcardTenMetric', key);
                    renderTenantChart(activeTenant);
                });
            }
        });
    }

    // ── Loaders ────────────────────────────────────────────────────────────────
    function loadCustomers() {
        if (xhrCustomer) xhrCustomer.abort();
        xhrCustomer = new AbortController();

        fetch(routes.pgcardTopCustomers + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrCustomer.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                customerData   = res.data || {};
                var keys       = Object.keys(customerData);
                activeCustomer = keys[0] || null;

                buildTabs('pgcardCustTab_container', customerData, activeCustomer, function (code) {
                    activeCustomer = code;
                    setActiveTab('pgcardCustTab_container', code);
                    renderCustomerChart(code);
                });

                if (activeCustomer) renderCustomerChart(activeCustomer);
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('pgcard customers:', e); });
    }

    function loadTenants() {
        if (xhrTenant) xhrTenant.abort();
        xhrTenant = new AbortController();

        fetch(routes.pgcardTopTenants + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTenant.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                tenantData   = res.data || {};
                var keys     = Object.keys(tenantData);
                activeTenant = keys[0] || null;

                buildTabs('pgcardTenTab_container', tenantData, activeTenant, function (code) {
                    activeTenant = code;
                    setActiveTab('pgcardTenTab_container', code);
                    renderTenantChart(code);
                });

                if (activeTenant) renderTenantChart(activeTenant);
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('pgcard tenants:', e); });
    }

    // ── Coupon donut + status ─────────────────────────────────────────────────

    var MALL_COLORS = {
        GC:  '#8B5CF6',
        KK:  '#06B6D4',
        PBM: '#EC4899',
        PMB: '#F59E0B',
    };
    var STATUS_COLORS = {
        active:   '#10B981',
        used:     '#3B82F6',
        expired:  '#EF4444',
        redeemed: '#8B5CF6',
        pending:  '#F59E0B',
    };

    function statusColor(s) {
        return STATUS_COLORS[(s || '').toLowerCase()] || '#64748B';
    }

    function renderCouponDonut(byMall) {
        var el = document.getElementById('pgcardCouponDonut');
        if (!el) return;

        if (charts.coupon) { charts.coupon.destroy(); charts.coupon = null; }

        if (!byMall || !byMall.length) {
            el.innerHTML = '<div class="flex h-full items-center justify-center py-10 text-xs text-slate-400">No data</div>';
            return;
        }

        var dark      = utils.isDark();
        var labels    = byMall.map(function (m) { return m.mall_name || m.mall_code; });
        var series    = byMall.map(function (m) { return m.count; });
        var colors    = byMall.map(function (m) { return MALL_COLORS[m.mall_code] || '#94A3B8'; });

        couponSelected = null; // reset on every re-render

        charts.coupon = new ApexCharts(el, {
            series: series,
            labels: labels,
            chart: {
                type: 'donut', height: 200,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
                events: {
                    dataPointSelection: function (e, ctx, cfg) {
                        var pts    = cfg.selectedDataPoints;
                        var hasSel = pts && pts[0] && pts[0].length > 0;
                        couponSelected = hasSel ? {
                            label: cfg.w.globals.labels[pts[0][0]],
                            val:   cfg.w.globals.series[pts[0][0]],
                        } : null;
                        ctx.updateOptions({
                            plotOptions: { pie: { donut: { labels: { total: {
                                label: couponSelected ? couponSelected.label : 'Total',
                                formatter: function (w) {
                                    return couponSelected
                                        ? Number(couponSelected.val).toLocaleString('id-ID')
                                        : Number(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)).toLocaleString('id-ID');
                                },
                            }}}}}
                        }, false, false);
                    },
                },
            },
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true, label: 'Total',
                                fontSize: '11px', fontWeight: 600,
                                color: dark ? '#CBD5E1' : '#64748B',
                                formatter: function (w) {
                                    var t = w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0);
                                    return Number(t).toLocaleString('id-ID');
                                },
                            },
                            name: {
                                show: true,
                                fontSize: '11px', fontWeight: 600,
                                color: dark ? '#CBD5E1' : '#64748B',
                            },
                            value: {
                                fontSize: '14px', fontWeight: 700,
                                color: dark ? '#F1F5F9' : '#0F172A',
                                formatter: function (v) { return Number(v).toLocaleString('id-ID'); },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            legend: {
                position: 'bottom',
                fontSize: '10px',
                fontWeight: 600,
                markers: { size: 6 },
                itemMargin: { horizontal: 6, vertical: 2 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: {
                    formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' coupons'; },
                },
            },
            stroke: { width: 2, colors: [dark ? '#0f172a' : '#ffffff'] },
        });
        charts.coupon.render();
    }

    var STATUS_LABELS = {
        'None': 'Waiting for Processed',
        '-':    'Waiting for Processed',
    };

    function renderCouponStatus(byStatus) {
        var el = document.getElementById('pgcardCouponStatus');
        if (!el) return;
        el.innerHTML = '';
        (byStatus || []).forEach(function (item) {
            var col   = statusColor(item.status);
            var label = STATUS_LABELS[item.status] || item.status;
            var pill  = document.createElement('span');
            pill.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:2px 8px;'
                + 'border-radius:9999px;font-size:10px;font-weight:600;'
                + 'background:' + col + '1a;color:' + col + ';';
            pill.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:' + col + ';display:inline-block;"></span>'
                + utils.escHtml(label) + ' · ' + Number(item.count).toLocaleString('id-ID');
            el.appendChild(pill);
        });
    }


    var byMallStatus = [];  // [{mall_code, mall_name, status, count}] — full breakdown for client-side filtering

    function mallStatusFilter() {
        var sel = document.getElementById('pgcardMallStatusFilter');
        return sel ? sel.value : 'VALID';
    }

    function applyMallStatusFilter(statusVal) {
        var grouped = {};
        byMallStatus.forEach(function (row) {
            if (statusVal === '' || row.status === statusVal) {
                if (!grouped[row.mall_code]) {
                    grouped[row.mall_code] = { mall_code: row.mall_code, mall_name: row.mall_name, count: 0 };
                }
                grouped[row.mall_code].count += row.count;
            }
        });
        return Object.values(grouped);
    }

    function loadCouponStyw() {
        if (xhrCoupon) xhrCoupon.abort();
        xhrCoupon = new AbortController();

        fetch(routes.pgcardCouponStyw + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrCoupon.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var d = res.data || {};

                // Total = VALID count only
                var validRow = (d.by_status_filtered || []).find(function (s) { return s.status === 'VALID'; });
                utils.setText('pgcardCouponTotal', Number((validRow && validRow.count) || 0).toLocaleString('id-ID'));

                renderCouponStatus(d.by_status_filtered || []);

                byMallStatus = d.by_mall_status || [];
                renderCouponDonut(applyMallStatusFilter(mallStatusFilter()));
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('pgcard coupon:', e); });
    }

    function bindMallStatusFilter() {
        var sel = document.getElementById('pgcardMallStatusFilter');
        if (!sel) return;
        sel.addEventListener('change', function () {
            renderCouponDonut(applyMallStatusFilter(this.value));
        });
    }

    // ── Query comparison (Option A vs Option B) ───────────────────────────────

    function renderCompareStatus(containerId, byStatus) {
        var el = document.getElementById(containerId);
        if (!el) return;
        el.innerHTML = '';
        (byStatus || []).forEach(function (item) {
            var col  = statusColor(item.status);
            var label = STATUS_LABELS[item.status] || item.status;
            var pill = document.createElement('span');
            pill.style.cssText = 'display:inline-flex;align-items:center;gap:4px;padding:2px 8px;'
                + 'border-radius:9999px;font-size:10px;font-weight:600;'
                + 'background:' + col + '1a;color:' + col + ';';
            pill.innerHTML = '<span style="width:6px;height:6px;border-radius:50%;background:' + col + ';display:inline-block;"></span>'
                + utils.escHtml(label) + ' · ' + Number(item.count).toLocaleString('id-ID');
            el.appendChild(pill);
        });
    }

    var compareCharts = { a: null, b: null };

    function renderCompareDonut(containerId, byMall, chartKey) {
        var el = document.getElementById(containerId);
        if (!el) return;
        if (compareCharts[chartKey]) { compareCharts[chartKey].destroy(); compareCharts[chartKey] = null; }

        if (!byMall || !byMall.length) {
            el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">No data</div>';
            return;
        }

        var dark   = utils.isDark();
        var labels = byMall.map(function (m) { return m.mall_name || m.mall_code; });
        var series = byMall.map(function (m) { return m.count; });
        var colors = byMall.map(function (m) { return MALL_COLORS[m.mall_code] || '#94A3B8'; });

        compareCharts[chartKey] = new ApexCharts(el, {
            series: series,
            labels: labels,
            chart: {
                type: 'donut', height: 160,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: colors,
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true, showAlways: true, label: 'Total',
                                fontSize: '10px', fontWeight: 600,
                                color: dark ? '#CBD5E1' : '#64748B',
                                formatter: function (w) {
                                    return Number(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)).toLocaleString('id-ID');
                                },
                            },
                            name: { show: true, fontSize: '10px', fontWeight: 600, color: dark ? '#CBD5E1' : '#64748B' },
                            value: {
                                fontSize: '12px', fontWeight: 700,
                                color: dark ? '#F1F5F9' : '#0F172A',
                                formatter: function (v) { return Number(v).toLocaleString('id-ID'); },
                            },
                        },
                    },
                },
            },
            dataLabels: { enabled: false },
            legend: { show: false },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: function (v) { return Number(v).toLocaleString('id-ID') + ' coupons'; } },
            },
            stroke: { width: 2, colors: [dark ? '#0f172a' : '#ffffff'] },
        });
        compareCharts[chartKey].render();
    }

    function loadCompare() {
        var btn    = document.getElementById('pgcardRunCompare');
        var status = document.getElementById('pgcardCompareStatus');
        var result = document.getElementById('pgcardCompareResult');

        if (btn) btn.disabled = true;
        if (status) status.textContent = 'Running both queries…';
        if (result) result.classList.add('hidden');

        fetch(routes.pgcardCouponStywCompare + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.error) {
                    if (status) status.textContent = 'Error: ' + res.error;
                    if (btn) btn.disabled = false;
                    return;
                }

                var a = res.optionA || {};
                var b = res.optionB || {};

                utils.setText('pgcardCompareTotalA', Number(a.total_valid || 0).toLocaleString('id-ID'));
                utils.setText('pgcardCompareTotalB', Number(b.total_valid || 0).toLocaleString('id-ID'));
                utils.setText('pgcardCompareTimeA',  (a.time_ms || 0).toLocaleString('id-ID') + ' ms');
                utils.setText('pgcardCompareTimeB',  (b.time_ms || 0).toLocaleString('id-ID') + ' ms');

                renderCompareStatus('pgcardCompareStatusA', a.by_status || []);
                renderCompareStatus('pgcardCompareStatusB', b.by_status || []);
                renderCompareDonut('pgcardCompareDonutA', a.by_mall || [], 'a');
                renderCompareDonut('pgcardCompareDonutB', b.by_mall || [], 'b');

                var faster = a.time_ms <= b.time_ms ? 'A' : 'B';
                var diff   = Math.abs((a.time_ms || 0) - (b.time_ms || 0));
                if (status) status.textContent = 'Done — Option ' + faster + ' was faster by ' + diff.toLocaleString('id-ID') + ' ms';
                if (result) result.classList.remove('hidden');
                if (btn) btn.disabled = false;
            })
            .catch(function (e) {
                if (status) status.textContent = 'Request failed: ' + e.message;
                if (btn) btn.disabled = false;
            });
    }

    function bindCompare() {
        var btn = document.getElementById('pgcardRunCompare');
        if (btn) btn.addEventListener('click', loadCompare);
    }

    // ── Dark-mode watcher ──────────────────────────────────────────────────────
    function watchDarkMode() {
        new MutationObserver(function () {
            if (activeCustomer) renderCustomerChart(activeCustomer);
            if (activeTenant)   renderTenantChart(activeTenant);
            loadCouponStyw();
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Reload on dashboard filter change ─────────────────────────────────────
    function reloadAll() {
        customerData       = {};
        tenantData         = {};
        activeCustomer     = null;
        activeTenant       = null;
        if (charts.customer) { charts.customer.destroy(); charts.customer = null; }
        if (charts.tenant)   { charts.tenant.destroy();   charts.tenant   = null; }
        if (charts.coupon)   { charts.coupon.destroy();   charts.coupon   = null; }
        var custEl = document.getElementById('pgcardCustomerChart');
        var tenEl  = document.getElementById('pgcardTenantChart');
        if (custEl) custEl.innerHTML = '';
        if (tenEl)  tenEl.innerHTML  = '';
        var custTab = document.getElementById('pgcardCustTab_container');
        var tenTab  = document.getElementById('pgcardTenTab_container');
        if (custTab) custTab.innerHTML = '';
        if (tenTab)  tenTab.innerHTML  = '';
        loadCustomers();
        loadTenants();
        loadCouponStyw();
    }

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        bindMetrics();
        bindMallStatusFilter();
        bindCompare();
        setActiveMetric('pgcardCustMetric', metricCustomer);
        setActiveMetric('pgcardTenMetric',  metricTenant);
        watchDarkMode();
        document.addEventListener('gm:filter', reloadAll);
        loadCustomers();
        loadTenants();
        loadCouponStyw();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
