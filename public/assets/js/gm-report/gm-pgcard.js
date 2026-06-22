(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils)
    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var charts         = { customer: null, tenant: null, coupon: null, campaign: null, trend: null };
    var couponSelected = null;
    var customerData   = {};
    var tenantData     = {};
    var activeCustomer = null;
    var activeTenant   = null;
    var metricCustomer = 'transaction';
    var metricTenant   = 'transaction';
    var trendMetric     = 'txn_count';     // 'txn_count' | 'unique_members' | 'total_spending'
    var rawTrendData    = [];
    var lastTrendHeight = 0;
    var lastTrendMetric = '';
    var trendResizeObs  = null;
    var xhrCustomer    = null;
    var xhrTenant      = null;
    var xhrCoupon      = null;
    var xhrKpi         = null;
    var xhrTrend       = null;

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

    // ── Word-wrap + uppercase helper for Y-axis labels ───────────────────────
    function wrapLabel(val, maxChars) {
        if (!val) return '';
        var s = (typeof val === 'string' ? val : String(val)).toUpperCase();
        if (s.length <= maxChars) return s;
        var words = s.split(' ');
        var lines = [''];
        words.forEach(function (word) {
            var cur = lines[lines.length - 1];
            if (!cur) {
                lines[lines.length - 1] = word;
            } else if (cur.length + 1 + word.length <= maxChars) {
                lines[lines.length - 1] = cur + ' ' + word;
            } else {
                lines.push(word);
            }
        });
        return lines.length > 1 ? lines : lines[0];
    }

    // ── Bar chart options ─────────────────────────────────────────────────────
    function buildBarOpts(rows, color, metric) {
        var dark   = utils.isDark();
        var useAmt = metric === 'spending';
        var categories = rows.map(function (r) { return r.label; });
        var values     = rows.map(function (r) { return useAmt ? r.total_amount : r.value; });

        var chartHeight = Math.max(260, rows.length * 38 + 40);

        return {
            series: [{ name: useAmt ? 'Total Spending' : 'Transactions', data: values }],
            chart: {
                type: 'bar', height: chartHeight,
                toolbar: { show: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
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
                labels: {
                    align: 'left',
                    style: { fontSize: '10px', fontWeight: 600 },
                    maxWidth: 240,
                    formatter: function (val) { return wrapLabel(val, 22); },
                },
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
                        + '<div style="font-size:13px;font-weight:700;color:' + color + ';margin-bottom:6px;">' + txn + ' Transactions</div>'
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
                        ? idr(v) + '  |  ' + Number(row.value || 0).toLocaleString('id-ID') + ' Transactions'
                        : Number(v).toLocaleString('id-ID') + ' Transactions  |  ' + idr(row.total_amount || 0);
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
        charts.customer = createResponsiveChart(el, buildBarOpts(rows, '#8B5CF6', metricCustomer));
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
        charts.tenant = createResponsiveChart(el, buildBarOpts(rows, '#06B6D4', metricTenant));
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

        charts.coupon = createResponsiveChart(el, {
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


    // ── Campaign bar chart ────────────────────────────────────────────────────
    var CAMPAIGN_COLORS = [
        '#8B5CF6', '#06B6D4', '#EC4899', '#F59E0B', '#10B981',
        '#3B82F6', '#EF4444', '#14B8A6', '#F97316', '#6366F1',
    ];

    // HR company → mall code embedded in campaign names
    var CAMPAIGN_MALL_MAP = { 'AW': '(GC)', 'EP': '(KK)', 'PSA': '(BM)', 'GPS': '(MB)' };
    var ALL_MALL_CODES    = ['(GC)', '(KK)', '(BM)', '(MB)'];

    var campaignMode  = 'campaign'; // 'campaign' | 'customer'
    var rawByCampaign = [];

    function hasMallCode(name) {
        var up = (name || '').toUpperCase();
        return ALL_MALL_CODES.some(function (c) { return up.indexOf(c.toUpperCase()) !== -1; });
    }

    function getVisibleCampaigns() {
        var cpny     = (window.gmState && window.gmState.cpnyId) ? window.gmState.cpnyId.toUpperCase() : '';
        var mallCode = cpny ? CAMPAIGN_MALL_MAP[cpny] : null;

        // No company filter → show all campaigns
        if (!cpny || !mallCode) return rawByCampaign;

        // Company filter applied → Grand Prize (no mall code) + that company's campaigns
        return rawByCampaign.filter(function (r) {
            var nameUp = (r.campaign_name || '').toUpperCase();
            return !hasMallCode(nameUp) || nameUp.indexOf(mallCode.toUpperCase()) !== -1;
        });
    }

    function drawCampaignChart() {
        var data       = getVisibleCampaigns();
        var isCustomer = campaignMode === 'customer';
        var metric     = isCustomer ? 'customer_count' : 'txn_count';
        var metricLbl  = isCustomer ? 'Customers' : 'Transactions';
        var el         = document.getElementById('pgcardCampaignChart');
        if (!el) return;
        if (charts.campaign) { charts.campaign.destroy(); charts.campaign = null; }

        if (!data.length) {
            el.innerHTML = '<div class="flex h-full items-center justify-center py-10 text-xs text-slate-400 dark:text-slate-500">No data</div>';
            return;
        }

        var dark   = utils.isDark();
        var labels = data.map(function (r) { return r.campaign_name || ('Campaign ' + r.campaign_id); });
        var values = data.map(function (r) { return r[metric] || 0; });
        var colors = data.map(function (_, i) { return CAMPAIGN_COLORS[i % CAMPAIGN_COLORS.length]; });
        var total  = values.reduce(function (a, b) { return a + b; }, 0);

        charts.campaign = createResponsiveChart(el, {
            series: [{ name: metricLbl, data: values }],
            chart: {
                type: 'bar', height: Math.max(280, data.length * 44 + 60),
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '40%', distributed: true } },
            colors: colors,
            xaxis: {
                categories: labels,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '10px', fontWeight: 600 } },
            },
            yaxis: {
                labels: {
                    style: { fontSize: '10px', fontWeight: 600 },
                    maxWidth: 260,
                    formatter: function (val) { return wrapLabel(val, 22); },
                },
            },
            dataLabels: {
                enabled: true, textAnchor: 'start', offsetX: 4,
                style: { fontSize: '10px', fontWeight: 700, colors: [dark ? '#e2e8f0' : '#334155'] },
                formatter: function (v) {
                    var pct = total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';
                    return Number(v).toLocaleString('id-ID') + ' ' + metricLbl + ' (' + pct + '%)';
                },
            },
            tooltip: {
                fixed: { enabled: true, position: 'topRight', offsetX: -8, offsetY: 8 },
                custom: function (opts) {
                    var idx     = opts.dataPointIndex;
                    var row     = data[idx] || {};
                    var v       = row[metric] || 0;
                    var pct     = total > 0 ? ((v / total) * 100).toFixed(1) : '0.0';
                    var name    = utils.escHtml(row.campaign_name || ('Campaign ' + row.campaign_id));
                    var topMerchant  = row.top_merchant && row.top_merchant !== '-'   ? utils.escHtml(row.top_merchant)  : null;
                    var topCustomer  = row.top_customer && row.top_customer !== '-'   ? utils.escHtml(row.top_customer)  : null;
                    var topCustomerTxnCount   = row.top_customer_txn || 0;
                    var bg      = dark ? '#1e293b' : '#ffffff';
                    var text    = dark ? '#f1f5f9' : '#0f172a';
                    var sub     = dark ? '#94a3b8' : '#64748b';
                    var clr     = CAMPAIGN_COLORS[idx % CAMPAIGN_COLORS.length];
                    var divider = '<div style="height:1px;background:' + (dark ? '#334155' : '#e2e8f0') + ';margin:8px 0;"></div>';
                    var html = '<div style="padding:10px 14px;background:' + bg + ';border-radius:10px;'
                             + 'min-width:230px;box-shadow:0 4px 16px rgba(0,0,0,.14);">'
                             + '<div style="font-size:10px;font-weight:600;color:' + sub + ';margin-bottom:2px;">' + name + '</div>'
                             + '<div style="font-size:15px;font-weight:800;color:' + clr + ';line-height:1.1;">'
                             +   Number(v).toLocaleString('id-ID') + ' ' + metricLbl + '</div>'
                             + '<div style="font-size:10px;color:' + sub + ';">(' + pct + '%)</div>';
                    if (!isCustomer && topMerchant) {
                        html += divider
                             + '<div style="font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:' + sub + ';margin-bottom:3px;">Top Merchant</div>'
                             + '<div style="font-size:12px;font-weight:700;color:' + text + ';white-space:normal;word-break:break-word;">' + topMerchant + '</div>';
                    }
                    if (isCustomer && topCustomer) {
                        html += divider
                             + '<div style="font-size:9px;text-transform:uppercase;letter-spacing:.06em;color:' + sub + ';margin-bottom:3px;">Top Customer</div>'
                             + '<div style="font-size:12px;font-weight:700;color:' + text + ';margin-bottom:4px;white-space:normal;word-break:break-word;">' + topCustomer + '</div>'
                             + '<div style="font-size:10px;color:' + sub + ';">Total Transactions: <span style="font-weight:700;color:' + clr + ';">' + Number(topCustomerTxnCount).toLocaleString('id-ID') + '</span></div>';
                    }
                    return html + '</div>';
                },
            },
            grid: {
                borderColor: dark ? '#334155' : '#F1F5F9', strokeDashArray: 4,
                xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } },
                padding: { left: 4, right: 16 },
            },
            legend: { show: false },
        });

        // After campaign finishes rendering use rAF to redraw trend chart if needed
        requestAnimationFrame(function () {
            if (rawTrendData.length) {
                lastTrendHeight = 0;
                drawTrendChart();
            }
        });
    }

    function renderCampaignChart(byCampaign) {
        rawByCampaign = byCampaign || [];
        drawCampaignChart();
    }

    function setCampaignTab(mode) {
        campaignMode = mode;
        ['campaign', 'customer'].forEach(function (m) {
            var btn = document.getElementById('pgcardCmpTab_' + m);
            if (!btn) return;
            btn.classList.toggle('pgcard-tab-active', m === mode);
            btn.classList.toggle('pgcard-tab-idle',   m !== mode);
        });
        drawCampaignChart();
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

    // ── KPI summary ───────────────────────────────────────────────────────────
    function loadKpiSummary() {
        if (xhrKpi) xhrKpi.abort();
        xhrKpi = new AbortController();
        ['pgcardKpiTxn', 'pgcardKpiSpending', 'pgcardKpiMembers', 'pgcardKpiAvg']
            .forEach(function (id) { utils.setText(id, '…'); });

        fetch(routes.pgcardKpiSummary + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrKpi.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var d = res.data || {};
                utils.setText('pgcardKpiTxn',      Number(d.total_txn      || 0).toLocaleString('id-ID'));
                utils.setText('pgcardKpiSpending',  idr(d.total_spending  || 0));
                utils.setText('pgcardKpiMembers',   Number(d.active_members || 0).toLocaleString('id-ID'));
                utils.setText('pgcardKpiAvg',       idr(d.avg_txn_value   || 0));

                // Render per-mall breakdowns for all 4 KPI cells
                var byMall   = d.by_mall || [];
                var dark     = utils.isDark();
                var MALL_CLR = { GC: '#8B5CF6', KK: '#06B6D4', PBM: '#EC4899', PMB: '#F59E0B' };
                var textClr  = dark ? '#e2e8f0' : '#1e293b';
                var subClr   = '#94a3b8';

                function renderKpiMallList(containerId, sorted, valueFn, formatFn, total) {
                    var el = document.getElementById(containerId);
                    if (!el || !sorted.length) return;
                    el.innerHTML = sorted.map(function (m) {
                        var val = valueFn(m);
                        var pct = total > 0 ? ((val / total) * 100).toFixed(0) : '–';
                        var clr = MALL_CLR[m.mall_code] || '#64748B';
                        return '<div style="display:flex;align-items:center;gap:5px;margin-top:2px;">'
                            + '<span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:' + clr + ';flex-shrink:0;"></span>'
                            + '<span style="font-size:10px;font-weight:600;color:' + clr + ';flex:1;">' + utils.escHtml(m.mall_code) + '</span>'
                            + '<span style="font-size:10px;font-weight:700;color:' + textClr + ';">' + formatFn(val) + '</span>'
                            + '<span style="font-size:9px;color:' + subClr + ';min-width:26px;text-align:right;">' + (total > 0 ? pct + '%' : '') + '</span>'
                            + '</div>';
                    }).join('');
                }

                var numFmt = function (v) { return Number(v).toLocaleString('id-ID'); };

                // 1. Total Transactions — sorted by txn_count
                renderKpiMallList('pgcardKpiMallList',
                    byMall.slice().sort(function (a, b) { return b.txn_count - a.txn_count; }),
                    function (m) { return m.txn_count; }, numFmt, d.total_txn);

                // 2. Total Spending — sorted by total_spending
                renderKpiMallList('pgcardKpiSpendingMallList',
                    byMall.slice().sort(function (a, b) { return b.total_spending - a.total_spending; }),
                    function (m) { return m.total_spending; }, idr, d.total_spending);

                // 3. Active Members — sorted by active_members
                renderKpiMallList('pgcardKpiMembersMallList',
                    byMall.slice().sort(function (a, b) { return b.active_members - a.active_members; }),
                    function (m) { return m.active_members; }, numFmt, d.active_members);

                // 4. Avg Transaction — computed per mall, no total percentage (avg of avgs is meaningless)
                var withAvg = byMall.map(function (m) {
                    return { mall_code: m.mall_code, avg: m.txn_count > 0 ? m.total_spending / m.txn_count : 0 };
                }).sort(function (a, b) { return b.avg - a.avg; });
                renderKpiMallList('pgcardKpiAvgMallList',
                    withAvg, function (m) { return m.avg; }, idr, 0);

                // ── Insights (only when a single company is filtered) ─────────────
                var insights     = d.insights || null;
                var showInsights = insights !== null && byMall.length === 1;
                var insightIds   = ['pgcardInsightTxn','pgcardInsightSpending','pgcardInsightMembers','pgcardInsightAvg'];

                if (!showInsights) {
                    insightIds.forEach(function (id) {
                        var el = document.getElementById(id);
                        if (el) el.innerHTML = '';
                    });
                } else {
                    var borderClr = dark ? '#334155' : '#e2e8f0';
                    var labelClr  = dark ? '#94a3b8' : '#94a3b8';
                    var nameClr   = dark ? '#e2e8f0' : '#1e293b';
                    var valClr    = dark ? '#f1f5f9' : '#0f172a';

                    function renderInsight(containerId, rows) {
                        var el = document.getElementById(containerId);
                        if (!el) return;
                        el.innerHTML = '<div style="margin-top:8px;padding-top:8px;border-top:1px dashed ' + borderClr + ';">'
                            + rows.map(function (r) {
                                return '<div style="display:flex;align-items:baseline;gap:4px;margin-bottom:5px;">'
                                    + '<span style="font-size:9px;text-transform:uppercase;letter-spacing:.05em;color:' + labelClr + ';flex-shrink:0;min-width:68px;">' + utils.escHtml(r.label) + '</span>'
                                    + (r.name
                                        ? '<span style="font-size:10px;font-weight:600;color:' + nameClr + ';flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="' + utils.escHtml(r.name) + '">' + utils.escHtml(r.name) + '</span>'
                                        : '<span style="flex:1;"></span>')
                                    + '<span style="font-size:10px;font-weight:700;color:' + valClr + ';white-space:nowrap;margin-left:4px;">' + utils.escHtml(r.value) + '</span>'
                                    + '</div>';
                            }).join('')
                            + '</div>';
                    }

                    // 1. Total Transactions
                    var it = insights.top_customer_txn;
                    var imt = insights.top_tenant_txn;
                    renderInsight('pgcardInsightTxn', [
                        { label: 'Top Customer', name: it  ? it.name  : '-', value: it  ? Number(it.value1).toLocaleString('id-ID')  + ' txns' : '-' },
                        { label: 'Top Tenant',   name: imt ? imt.name : '-', value: imt ? Number(imt.value1).toLocaleString('id-ID') + ' txns' : '-' },
                    ]);

                    // 2. Total Spending
                    var is = insights.top_customer_spending;
                    var ims = insights.top_tenant_spending;
                    renderInsight('pgcardInsightSpending', [
                        { label: 'Top Spender', name: is  ? is.name  : '-', value: is  ? idr(is.value1)  : '-' },
                        { label: 'Top Revenue', name: ims ? ims.name : '-', value: ims ? idr(ims.value1) : '-' },
                    ]);

                    // 3. Active Members
                    var il = insights.top_customer_txn;
                    renderInsight('pgcardInsightMembers', [
                        { label: 'Most Loyal', name: il ? il.name : '-', value: il ? Number(il.value1).toLocaleString('id-ID') + ' visits' : '-' },
                        { label: 'New Members', name: null, value: Number(insights.new_members_count || 0).toLocaleString('id-ID') + ' new' },
                    ]);

                    // 4. Avg Transaction
                    var ia = insights.top_tenant_avg;
                    renderInsight('pgcardInsightAvg', [
                        { label: 'Best Avg Store', name: ia ? ia.name : '-', value: ia ? idr(ia.value1) + ' avg' : '-' },
                    ]);
                }
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('pgcard kpi:', e); });
    }

    // ── Monthly trend chart ───────────────────────────────────────────────────
    var TREND_MONTH_LABELS = {
        '01':'Jan','02':'Feb','03':'Mar','04':'Apr','05':'May','06':'Jun',
        '07':'Jul','08':'Aug','09':'Sep','10':'Oct','11':'Nov','12':'Dec',
    };

    // No ResizeObserver — we trigger a single delayed re-draw from drawCampaignChart instead

    function drawTrendChart() {
        var el = document.getElementById('pgcardTrendChart');
        if (!el) return;

        var data = rawTrendData;
        if (!data.length) {
            if (charts.trend) { charts.trend.destroy(); charts.trend = null; }
            el.innerHTML = '<div class="flex h-full items-center justify-center py-10 text-xs text-slate-400 dark:text-slate-500">No data</div>';
            lastTrendHeight = 0;
            return;
        }

        // Read actual rendered height (set by flex layout after campaign chart has rendered)
        var h = el.offsetHeight || 0;
        if (h < 80) h = 300;   // fallback before layout has settled

        // Skip re-render only for resize-triggered calls (same height, same metric)
        if (charts.trend && Math.abs(h - lastTrendHeight) < 10 && lastTrendMetric === trendMetric) return;
        lastTrendHeight = h;
        lastTrendMetric = trendMetric;

        if (charts.trend) { charts.trend.destroy(); charts.trend = null; }

        var trendH = Math.min(h, 480);

        var dark       = utils.isDark();
        var isSpending = trendMetric === 'total_spending';
        var isMembers  = trendMetric === 'unique_members';
        var seriesName = isSpending ? 'Spending (Rp)' : isMembers ? 'Unique Members' : 'Transactions';
        var color      = isSpending ? '#10B981' : isMembers ? '#06B6D4' : '#8B5CF6';

        var labels = data.map(function (r) {
            var parts = (r.month || '').split('-');
            return (TREND_MONTH_LABELS[parts[1]] || parts[1]) + (parts[0] ? ' ' + parts[0] : '');
        });
        var values = data.map(function (r) { return r[trendMetric] || 0; });

        // Dynamic y-axis floor: start just below the lowest data point so the line fills the chart
        var nonZero  = values.filter(function (v) { return v > 0; });
        var minVal   = nonZero.length ? Math.min.apply(null, nonZero) : 0;
        var yAxisMin = minVal > 0 ? Math.floor(minVal * 0.85) : 0;

        charts.trend = createResponsiveChart(el, {
            series: [{ name: seriesName, data: values }],
            chart: {
                type: 'area', height: trendH,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 600 },
                sparkline: { enabled: false },
            },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                type: 'gradient',
                gradient: { shadeIntensity: 1, opacityFrom: 0.35, opacityTo: 0.02, stops: [0, 100] },
            },
            colors: [color],
            xaxis: {
                categories: labels,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { fontSize: '10px', fontWeight: 600 } },
            },
            yaxis: {
                min: yAxisMin,
                labels: {
                    style: { fontSize: '10px' },
                    formatter: isSpending ? function (v) { return idr(v); } : function (v) { return Number(v).toLocaleString('id-ID'); },
                },
            },
            dataLabels: { enabled: false },
            grid: {
                borderColor: dark ? '#334155' : '#F1F5F9', strokeDashArray: 4,
                xaxis: { lines: { show: false } }, yaxis: { lines: { show: true } },
                padding: { left: 4, right: 12, top: 0, bottom: 0 },
            },
            tooltip: {
                theme: dark ? 'dark' : 'light',
                y: { formatter: isSpending ? function (v) { return idr(v); } : function (v) { return Number(v).toLocaleString('id-ID') + ' ' + (isMembers ? 'members' : 'transactions'); } },
            },
            markers: { size: 3, strokeWidth: 0, hover: { size: 5 } },
        });
    }

    function setTrendTab(metric) {
        trendMetric = metric;
        var map = { txn_count: 'txn', unique_members: 'members', total_spending: 'spending' };
        ['txn', 'members', 'spending'].forEach(function (k) {
            var btn = document.getElementById('pgcardTrendTab_' + k);
            if (!btn) return;
            var active = map[metric] === k;
            btn.classList.toggle('pgcard-tab-active', active);
            btn.classList.toggle('pgcard-tab-idle',   !active);
        });
        drawTrendChart();
    }

    function loadMonthlyTrend() {
        if (xhrTrend) xhrTrend.abort();
        xhrTrend = new AbortController();

        fetch(routes.pgcardMonthlyTrend + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTrend.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                rawTrendData = res.data || [];
                lastTrendHeight = 0;
                drawTrendChart();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('pgcard trend:', e); });
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
                renderCampaignChart(d.by_campaign || []);
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
            drawTrendChart();
            loadCouponStyw();
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Reload on dashboard filter change ─────────────────────────────────────
    function reloadAll() {
        customerData   = {};
        tenantData     = {};
        rawTrendData    = [];
        lastTrendHeight = 0;
        lastTrendMetric = '';
        if (trendResizeObs) { trendResizeObs.disconnect(); trendResizeObs = null; }
        activeCustomer = null;
        activeTenant   = null;
        if (charts.customer)  { charts.customer.destroy();  charts.customer  = null; }
        if (charts.tenant)    { charts.tenant.destroy();    charts.tenant    = null; }
        if (charts.coupon)    { charts.coupon.destroy();    charts.coupon    = null; }
        if (charts.campaign)  { charts.campaign.destroy();  charts.campaign  = null; }
        if (charts.trend)     { charts.trend.destroy();     charts.trend     = null; }
        var custEl = document.getElementById('pgcardCustomerChart');
        var tenEl  = document.getElementById('pgcardTenantChart');
        if (custEl) custEl.innerHTML = '';
        if (tenEl)  tenEl.innerHTML  = '';
        var custTab = document.getElementById('pgcardCustTab_container');
        var tenTab  = document.getElementById('pgcardTenTab_container');
        if (custTab) custTab.innerHTML = '';
        if (tenTab)  tenTab.innerHTML  = '';
        loadKpiSummary();
        loadMonthlyTrend();
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

        // Campaign tab buttons
        ['campaign', 'customer'].forEach(function (m) {
            var btn = document.getElementById('pgcardCmpTab_' + m);
            if (btn) btn.addEventListener('click', function () { setCampaignTab(m); });
        });

        // Trend tab buttons
        var trendMap = { txn: 'txn_count', members: 'unique_members', spending: 'total_spending' };
        ['txn', 'members', 'spending'].forEach(function (k) {
            var btn = document.getElementById('pgcardTrendTab_' + k);
            if (btn) btn.addEventListener('click', function () { setTrendTab(trendMap[k]); });
        });

        document.addEventListener('gm:filter', reloadAll);
        loadKpiSummary();
        loadMonthlyTrend();
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
