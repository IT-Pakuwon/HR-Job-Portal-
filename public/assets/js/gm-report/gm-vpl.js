(function () {
    'use strict';

    // Depends on gm-core.js (window.gmUtils)
    // Routes: window.gmRoutes.vplCompanyOverview / .vplVoucherList / .vplTopOut / .vplByCategory / .vplUsageByReason
    var routes    = window.gmRoutes || {};
    var utils     = window.gmUtils;
    var PAGE_SIZE = 10;

    var charts = { category: null, topOut: null, reason: null };
    var xhrOverview = null, xhrVoucher = null, xhrTopOut = null, xhrCategory = null, xhrReason = null;
    var voucherRows = [], voucherPage = 1, voucherSort = null;
    var topOutCardType = ''; // '' = All, 'V' = Voucher, 'P' = Product

    // Cached per-loader results, kept together so computeVplInsights() can
    // reason across all of them the same way gm-budget.js's insight builder does,
    // and so watchDarkMode() can re-render charts without an extra round-trip.
    var lastOverview = null, lastCategoryData = [], lastTopOutData = [];
    var lastTopOutRes = null, lastCategoryRes = null, lastReasonRes = null;

    // ── Company color/label helpers — same palette as gm-event.js so a
    // company keeps the same identity color across every GM section. ──────────
    var COMPANY_COLORS   = { AW: '#8B5CF6', EP: '#3B82F6', PSA: '#10B981', GPS: '#F59E0B' };
    var COMPANY_FALLBACK = ['#06B6D4', '#EC4899', '#EF4444', '#84CC16', '#F97316', '#14B8A6'];
    var COMPANY_LABEL    = { AW: 'GC', EP: 'KK', PSA: 'PBM', GPS: 'PMB' };

    function companyColor(code, idx) {
        return COMPANY_COLORS[code] || COMPANY_FALLBACK[idx % COMPANY_FALLBACK.length];
    }
    function companyLabel(code) {
        return COMPANY_LABEL[code] || code;
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
        return 'background:' + theme.bg + ';border-radius:14px;padding:14px;width:' + width + 'px;max-height:340px;overflow-y:auto;'
             + 'box-shadow:0 16px 40px -12px rgba(0,0,0,.35),0 0 0 1px ' + theme.divider + ';font-family:Inter,sans-serif;';
    }

    function fmt(n) {
        return Number(n || 0).toLocaleString('id-ID');
    }

    // ── GM Insight ─────────────────────────────────────────────────────────────
    function computeVplInsights() {
        if (!lastOverview) return;
        var insights = [];
        var t = lastOverview.totals || {};
        var voucherStock = parseFloat(t.voucher_stock) || 0;
        var productStock = parseFloat(t.product_stock) || 0;
        var voucherValue = parseFloat(t.voucher_value) || 0;
        var productValue = parseFloat(t.product_value) || 0;
        var totalStock = voucherStock + productStock;
        var totalValue = voucherValue + productValue;

        if (totalStock > 0) {
            insights.push({ type: 'info', text: 'Current stock on hand: <b>' + fmt(voucherStock) + '</b> vouchers and <b>' + fmt(productStock) + '</b> products (' + fmt(totalStock) + ' units combined).' });
        }

        if (totalValue > 0) {
            var vPct = (voucherValue / totalValue) * 100;
            insights.push({ type: 'info', text: 'Inventory value splits <b>' + vPct.toFixed(0) + '%</b> voucher (' + utils.idr(voucherValue) + ') vs <b>' + (100 - vPct).toFixed(0) + '%</b> product (' + utils.idr(productValue) + ') — ' + utils.idr(totalValue) + ' total.' });
        }

        if (lastCategoryData.length) {
            var topCat = lastCategoryData.slice().sort(function (a, b) {
                return (b.voucher + b.product) - (a.voucher + a.product);
            })[0];
            if (topCat && (topCat.voucher + topCat.product) > 0) {
                insights.push({ type: 'info', text: '<b>' + utils.escHtml(topCat.category) + '</b> is the largest category in stock, at ' + fmt(topCat.voucher + topCat.product) + ' units.' });
            }
        }

        if (lastTopOutData.length && lastTopOutData[0].total > 0) {
            var top = lastTopOutData[0];
            insights.push({ type: 'warning', text: '<b>' + utils.escHtml(top.label) + '</b> moved out the most this period — <b>' + fmt(top.total) + '</b> pcs.' });
        }

        utils.renderInsights('gmVplInsights', insights);
    }

    // ── Company overview + KPI strip ────────────────────────────────────────────
    function renderCompanyOverview(res) {
        var d = res && res.data ? res.data : {};
        var t = d.totals || {};

        utils.setText('vplTotalVoucherStock', fmt(t.voucher_stock));
        utils.setText('vplTotalProductStock', fmt(t.product_stock));
        var totalValue = (parseFloat(t.voucher_value) || 0) + (parseFloat(t.product_value) || 0);
        utils.setText('vplTotalValue', utils.idr(totalValue));
        utils.setText('vplVoucherValue', utils.idr(t.voucher_value));
        utils.setText('vplProductValue', utils.idr(t.product_value));
        utils.setText('vplAsOfBadge', d.as_of ? 'As of ' + d.as_of : '');

        var rows = d.by_company || [];
        var tbody = document.getElementById('vplCompanyBody');
        if (!tbody) return;

        if (!rows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
            return;
        }

        tbody.innerHTML = rows.map(function (r) {
            return '<tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/30">'
                + '<td class="py-2.5 pl-5 pr-2 font-semibold text-slate-700 dark:text-slate-200">' + utils.escHtml(companyLabel(r.cpnyid)) + ' <span class="font-normal text-slate-400">(' + utils.escHtml(r.cpnyid) + ')</span></td>'
                + '<td class="py-2.5 pr-2 text-right tabular-nums text-violet-600 dark:text-violet-400">' + fmt(r.voucher_stock) + '</td>'
                + '<td class="py-2.5 pr-2 text-right tabular-nums text-cyan-600 dark:text-cyan-400">' + fmt(r.product_stock) + '</td>'
                + '<td class="py-2.5 pr-2 text-right tabular-nums text-slate-600 dark:text-slate-300">' + utils.idr(r.voucher_value) + '</td>'
                + '<td class="py-2.5 pr-5 text-right tabular-nums text-slate-600 dark:text-slate-300">' + utils.idr(r.product_value) + '</td>'
                + '</tr>';
        }).join('');
    }

    function loadCompanyOverview() {
        if (xhrOverview) xhrOverview.abort();
        xhrOverview = new AbortController();

        fetch(routes.vplCompanyOverview + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrOverview.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lastOverview = res.data || {};
                renderCompanyOverview(res);
                computeVplInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('vpl company overview:', e); });
    }

    // ── Voucher list table (sorted by stock) ────────────────────────────────────
    function companyBreakdownTitle(byCompany) {
        byCompany = byCompany || {};
        var keys = Object.keys(byCompany);
        if (keys.length <= 1) return '';
        return keys.sort().map(function (k) { return companyLabel(k) + ': ' + fmt(byCompany[k]); }).join(' · ');
    }

    function renderVoucherTable() {
        var tbody = document.getElementById('vplVoucherTableBody');
        if (!tbody) return;

        utils.setText('vplVoucherCount', voucherRows.length + ' voucher' + (voucherRows.length !== 1 ? 's' : ''));

        if (!voucherRows.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data for the selected filters.</td></tr>';
            var p = document.getElementById('vplVoucherPagination');
            if (p) p.classList.add('hidden');
            return;
        }

        var totalPages = Math.ceil(voucherRows.length / PAGE_SIZE);
        voucherPage = Math.min(voucherPage, totalPages);
        var slice = voucherRows.slice((voucherPage - 1) * PAGE_SIZE, voucherPage * PAGE_SIZE);

        tbody.innerHTML = slice.map(function (r) {
            var title = utils.escHtml(companyBreakdownTitle(r.by_company));
            return '<tr class="transition hover:bg-slate-50/60 dark:hover:bg-slate-800/30"' + (title ? ' title="' + title + '"' : '') + '>'
                + '<td class="px-5 py-3 font-semibold text-slate-700 dark:text-slate-200">' + utils.escHtml(r.product_name) + '</td>'
                + '<td class="px-4 py-3 text-slate-500 dark:text-slate-400">' + utils.escHtml(r.category) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums font-bold text-violet-600 dark:text-violet-400">' + fmt(r.stock) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums text-slate-600 dark:text-slate-300">' + utils.idr(r.value) + '</td>'
                + '<td class="px-4 py-3 text-right tabular-nums font-bold text-slate-700 dark:text-slate-200">' + utils.idr(r.total_value) + '</td>'
                + '</tr>';
        }).join('');

        utils.renderPagination('vplVoucher', voucherRows.length, voucherPage, PAGE_SIZE, function (p) {
            voucherPage = p; renderVoucherTable();
        });
    }

    function loadVoucherList() {
        if (xhrVoucher) xhrVoucher.abort();
        xhrVoucher = new AbortController();
        utils.setText('vplVoucherCount', '…');

        fetch(routes.vplVoucherList + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrVoucher.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                voucherRows = res.data || []; voucherPage = 1;
                if (voucherSort) voucherSort.reset();
                renderVoucherTable();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('vpl voucher list:', e); });
    }

    // ── Top 10 Most Out (stacked by company when "All Companies") ──────────────
    function makeSiteBreakdownTooltip(data, allSites, unitLabel) {
        return function (opts) {
            var idx = opts.dataPointIndex, seriesIdx = opts.seriesIndex;
            var row = data[idx] || {};
            var theme = themeColors();
            var hovClr = opts.w.globals.colors[seriesIdx] || '#8B5CF6';
            var total = row.total || 0;

            var html = '<div style="' + tooltipShell(theme, 230) + '">'
                     + '<div style="font-weight:800;font-size:12.5px;color:' + theme.text + ';margin-bottom:2px;">' + utils.escHtml(row.label || '') + '</div>'
                     + (row.type ? '<div style="font-size:10px;font-weight:700;color:' + theme.sub + ';margin-bottom:6px;">' + (row.type === 'V' ? 'Voucher' : 'Product') + '</div>' : '')
                     + '<div style="font-size:18px;font-weight:800;color:' + hovClr + ';margin-bottom:8px;">' + fmt(total) + ' ' + unitLabel + '</div>';

            allSites.forEach(function (site, si) {
                var v = (row.by_company && row.by_company[site]) || 0;
                var clr = companyColor(site, si);
                var isHov = si === seriesIdx;
                html += '<div style="display:flex;align-items:center;gap:8px;padding:4px 6px;margin:0 -6px;border-radius:7px;'
                      +   'background:' + (isHov ? theme.chipBgHover : 'transparent') + ';">'
                      + '<span style="width:7px;height:7px;border-radius:50%;background:' + clr + ';display:inline-block;flex-shrink:0;"></span>'
                      + '<span style="font-size:11.5px;font-weight:' + (isHov ? '700' : '500') + ';color:' + (isHov ? theme.text : theme.sub) + ';flex:1;">' + utils.escHtml(companyLabel(site)) + '</span>'
                      + '<span style="font-size:11.5px;font-weight:800;color:' + clr + ';">' + fmt(v) + '</span>'
                      + '</div>';
            });

            return html + '</div>';
        };
    }

    function makeSimpleOutTooltip(rows) {
        return function (opts) {
            var row = rows[opts.dataPointIndex] || {};
            var theme = themeColors();
            var clr = row.type === 'V' ? '#8B5CF6' : '#06B6D4';

            return '<div style="' + tooltipShell(theme, 210) + '">'
                 + '<div style="font-weight:800;font-size:12.5px;color:' + theme.text + ';">' + utils.escHtml(row.label || '') + '</div>'
                 + '<div style="font-size:10px;font-weight:700;color:' + theme.sub + ';margin-bottom:6px;">' + (row.type === 'V' ? 'Voucher' : 'Product') + '</div>'
                 + '<div style="font-size:18px;font-weight:800;color:' + clr + ';">' + fmt(row.total) + ' pcs out</div>'
                 + '</div>';
        };
    }

    function barHeight(n) {
        return Math.max(240, n * 36);
    }

    function renderTopOutChart(res) {
        var data     = (res && res.data)      || [];
        var stacked  = res && res.stacked;
        var allSites = (res && res.all_sites) || [];
        var el = document.getElementById('vplTopOutChart');
        if (!el) return;
        if (charts.topOut) { charts.topOut.destroy(); charts.topOut = null; }

        if (!data.length) {
            el.innerHTML = '<p class="py-10 text-center text-xs text-slate-400">No usage in this period.</p>';
            return;
        }

        var dark = utils.isDark();
        var rows = data.slice().reverse(); // #1 renders at the top of a horizontal bar
        var cats = rows.map(function (r) { return r.label; });
        var opts;

        if (stacked && allSites.length > 1) {
            var series = allSites.map(function (site) {
                return { name: companyLabel(site), data: rows.map(function (r) { return (r.by_company && r.by_company[site]) || 0; }) };
            });
            var colors = allSites.map(function (site, si) { return companyColor(site, si); });

            opts = {
                series: series,
                chart: {
                    type: 'bar', height: barHeight(cats.length), width: '100%', stacked: true,
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                    animations: { enabled: true, easing: 'easeinout', speed: 400 },
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '55%' } },
                colors: colors,
                dataLabels: { enabled: false },
                xaxis: { categories: cats, labels: { style: { fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { fontSize: '10.5px' } } },
                grid: { borderColor: dark ? '#334155' : '#E2E8F0', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                legend: { show: true, position: 'top', fontSize: '11px', labels: { colors: dark ? '#CBD5E1' : '#475569' } },
                tooltip: { custom: makeSiteBreakdownTooltip(rows, allSites, 'pcs out'), fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 } },
            };
        } else {
            var vals   = rows.map(function (r) { return r.total; });
            var colors2 = rows.map(function (r) { return r.type === 'V' ? '#8B5CF6' : '#06B6D4'; });

            opts = {
                series: [{ name: 'Qty Out', data: vals }],
                chart: {
                    type: 'bar', height: barHeight(cats.length), width: '100%',
                    toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                    foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                },
                plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '55%', distributed: true } },
                colors: colors2,
                dataLabels: { enabled: false },
                xaxis: { categories: cats, labels: { style: { fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { style: { fontSize: '10.5px' } } },
                grid: { borderColor: dark ? '#334155' : '#E2E8F0', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
                legend: { show: false },
                tooltip: { custom: makeSimpleOutTooltip(rows) },
            };
        }

        charts.topOut = new ApexCharts(el, opts);
        charts.topOut.render();
    }

    function loadTopOut() {
        if (xhrTopOut) xhrTopOut.abort();
        xhrTopOut = new AbortController();

        var qs = utils.buildParams();
        qs += (qs ? '&' : '?') + 'card_type=' + encodeURIComponent(topOutCardType);

        fetch(routes.vplTopOut + qs, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrTopOut.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lastTopOutData = res.data || [];
                lastTopOutRes = res;
                renderTopOutChart(res);
                computeVplInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('vpl top out:', e); });
    }

    // ── Shared "stacked by type" chart — By Category & Usage by Reason ─────────
    // Always two series (Voucher/Product); when more than one company is
    // present in a bar's by_company map, the tooltip lists the per-company
    // split for whichever series (Voucher or Product) is being hovered.
    function makeTypeTooltip(data, labelKey) {
        return function (opts) {
            var idx = opts.dataPointIndex, seriesIdx = opts.seriesIndex; // 0 = Voucher, 1 = Product
            var row = data[idx] || {};
            var theme = themeColors();
            var key = seriesIdx === 0 ? 'voucher' : 'product';
            var label = seriesIdx === 0 ? 'Voucher' : 'Product';
            var clr = seriesIdx === 0 ? '#8B5CF6' : '#06B6D4';
            var total = row[key] || 0;

            var html = '<div style="' + tooltipShell(theme, 230) + '">'
                     + '<div style="font-weight:800;font-size:12.5px;color:' + theme.text + ';margin-bottom:2px;">' + utils.escHtml(row[labelKey] || '') + '</div>'
                     + '<div style="font-size:10px;font-weight:700;color:' + theme.sub + ';margin-bottom:6px;">' + label + '</div>'
                     + '<div style="font-size:18px;font-weight:800;color:' + clr + ';margin-bottom:8px;">' + fmt(total) + '</div>';

            var byCompany = row.by_company || {};
            var sites = Object.keys(byCompany);
            if (sites.length > 1) {
                sites.sort();
                html += '<div style="font-size:9.5px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:' + theme.sub + ';margin:6px 0 4px;">By Company</div>';
                sites.forEach(function (site, si) {
                    var v = (byCompany[site] && byCompany[site][key]) || 0;
                    var sClr = companyColor(site, si);
                    html += '<div style="display:flex;align-items:center;gap:8px;padding:3px 6px;margin:0 -6px;">'
                          + '<span style="width:7px;height:7px;border-radius:50%;background:' + sClr + ';display:inline-block;flex-shrink:0;"></span>'
                          + '<span style="font-size:11px;color:' + theme.sub + ';flex:1;">' + utils.escHtml(companyLabel(site)) + '</span>'
                          + '<span style="font-size:11px;font-weight:700;color:' + theme.text + ';">' + fmt(v) + '</span>'
                          + '</div>';
                });
            }

            return html + '</div>';
        };
    }

    function renderTypeStackChart(elId, chartsKey, data, labelKey, emptyMsg) {
        var el = document.getElementById(elId);
        if (!el) return;
        if (charts[chartsKey]) { charts[chartsKey].destroy(); charts[chartsKey] = null; }

        if (!data.length || !data.some(function (d) { return (d.voucher + d.product) > 0; })) {
            el.innerHTML = '<p class="py-10 text-center text-xs text-slate-400">' + emptyMsg + '</p>';
            return;
        }

        var dark = utils.isDark();
        var cats = data.map(function (d) { return d[labelKey]; });

        var opts = {
            series: [
                { name: 'Voucher', data: data.map(function (d) { return d.voucher; }) },
                { name: 'Product', data: data.map(function (d) { return d.product; }) },
            ],
            chart: {
                type: 'bar', height: barHeight(cats.length), width: '100%', stacked: true,
                toolbar: { show: false }, fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B', background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 400 },
            },
            plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '55%' } },
            colors: ['#8B5CF6', '#06B6D4'],
            dataLabels: { enabled: false },
            xaxis: { categories: cats, labels: { style: { fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#334155' : '#E2E8F0', xaxis: { lines: { show: true } }, yaxis: { lines: { show: false } } },
            legend: { show: true, position: 'top', fontSize: '11px', labels: { colors: dark ? '#CBD5E1' : '#475569' } },
            tooltip: { custom: makeTypeTooltip(data, labelKey), fixed: { enabled: true, position: 'topRight', offsetX: -10, offsetY: 10 } },
        };

        charts[chartsKey] = new ApexCharts(el, opts);
        charts[chartsKey].render();
    }

    function loadCategory() {
        if (xhrCategory) xhrCategory.abort();
        xhrCategory = new AbortController();

        fetch(routes.vplByCategory + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrCategory.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lastCategoryData = res.data || [];
                lastCategoryRes = res;
                renderTypeStackChart('vplCategoryChart', 'category', lastCategoryData, 'category', 'No stock data for the selected filters.');
                computeVplInsights();
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('vpl by category:', e); });
    }

    function loadReason() {
        if (xhrReason) xhrReason.abort();
        xhrReason = new AbortController();

        fetch(routes.vplUsageByReason + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
            signal: xhrReason.signal,
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lastReasonRes = res;
                renderTypeStackChart('vplReasonChart', 'reason', res.data || [], 'reason', 'No usage in this period.');
            })
            .catch(function (e) { if (e.name !== 'AbortError') console.error('vpl usage by reason:', e); });
    }

    // ── Top 10 Out — card type filter buttons ───────────────────────────────────
    function bindTopOutTypeButtons() {
        var map = { all: '', v: 'V', p: 'P' };
        Object.keys(map).forEach(function (key) {
            var btn = document.getElementById('vplTopOutType_' + key);
            if (!btn) return;
            btn.addEventListener('click', function () {
                topOutCardType = map[key];
                Object.keys(map).forEach(function (k) {
                    var b = document.getElementById('vplTopOutType_' + k);
                    if (!b) return;
                    b.classList.toggle('vpl-tab-active', k === key);
                    b.classList.toggle('vpl-tab-idle', k !== key);
                });
                loadTopOut();
            });
        });
    }

    // ── Redraw charts on dark-mode toggle — re-render from cached responses
    // instead of re-fetching, same effect as gm-budget.js's watchDarkMode()
    // without the redundant round-trip. ─────────────────────────────────────────
    function watchDarkMode() {
        var observer = new MutationObserver(function () {
            if (lastTopOutRes) renderTopOutChart(lastTopOutRes);
            if (lastCategoryRes) renderTypeStackChart('vplCategoryChart', 'category', lastCategoryRes.data || [], 'category', 'No stock data for the selected filters.');
            if (lastReasonRes) renderTypeStackChart('vplReasonChart', 'reason', lastReasonRes.data || [], 'reason', 'No usage in this period.');
        });
        observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    // ── Listen for filter change — fired by gm-filter.js ──────────────────────
    document.addEventListener('gm:filter', function () {
        voucherPage = 1;
        loadCompanyOverview();
        loadVoucherList();
        loadTopOut();
        loadCategory();
        loadReason();
    });

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        bindTopOutTypeButtons();
        watchDarkMode();

        voucherSort = utils.bindTableSort(
            'vplVoucherTableBody',
            function () { return voucherRows; },
            function (r) { voucherRows = r; },
            function () { voucherPage = 1; },
            renderVoucherTable
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
