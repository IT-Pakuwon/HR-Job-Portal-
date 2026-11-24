(function () {
    'use strict';

    var routes = window.legalLeaseReportRoutes || {};
    var utils = window.gmUtils || {};

    var charts = { unit: null, sent: null };
    var chartData = {}; // last-rendered {categories, series, colors} per chart key — reused on theme toggle

    var tableAll = [];
    var tableSorted = null;
    var tablePage = 1;
    var tablePageSize = 10;   // fixed: 10 rows per page
    var tableSortBind = null;

    function isDark() {
        return document.documentElement.classList.contains('dark');
    }

    function fetchJson(url, params) {
        return fetch(url + (params || ''), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        }).then(function (r) { return r.json(); });
    }

    // ── Stat cards ───────────────────────────────────────────────────────────
    function loadSummary(params) {
        fetchJson(routes.summary, params).then(function (res) {
            var d = res.data || {};
            utils.setText('llrepStatTotal', d.total || 0);
            utils.setText('llrepStatCompleted', d.completed || 0);
            utils.setText('llrepStatProgress', d.on_progress || 0);
            utils.setText('llrepStatRate', (d.completion_rate || 0) + '%');
            utils.setText('llrepStatIncomplete', d.incomplete_docs || 0);

            hlItems = d.highlight_items || [];
            renderHighlights();
        }).catch(function () {});
    }

    // ── Key Highlights: one point per item, English by default, EN / ID switch ──
    var hlItems = [];
    var hlLang = 'en';
    var HL_COLORS = { blue: '#3B82F6', violet: '#56b1f3', cyan: '#06B6D4', amber: '#F59E0B', rose: '#EF4444', emerald: '#10B981', slate: '#94A3B8' };

    function renderHighlights() {
        var box = document.getElementById('llrepKeyHighlights');
        if (!box) return;

        box.innerHTML = hlItems.length
            ? hlItems.map(function (h) {
                var text = (h.text || {})[hlLang] || (h.text || {}).en || '';
                return '<div class="flex gap-3">'
                    + '<span class="w-1 shrink-0 self-stretch rounded-full" style="background:' + (HL_COLORS[h.tone] || '#94A3B8') + '"></span>'
                    + '<div class="min-w-0">' + utils.escHtml(text) + '</div></div>';
            }).join('')
            : '<p class="text-slate-400 dark:text-slate-500">' + (hlLang === 'id' ? 'Tidak ada data untuk periode ini.' : 'No data for this period.') + '</p>';

        var sw = document.getElementById('llrepHlLang');
        if (sw) {
            Array.prototype.forEach.call(sw.querySelectorAll('[data-hl-lang]'), function (btn) {
                var on = btn.getAttribute('data-hl-lang') === hlLang;
                btn.className = 'rounded-full px-2.5 py-0.5 transition ' + (on
                    ? 'bg-violet-600 text-white'
                    : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200');
            });
        }
    }

    function bindHighlightLang() {
        var sw = document.getElementById('llrepHlLang');
        if (!sw) return;
        // English unless the user switched before (remembered per browser).
        try { if (window.localStorage.getItem('llrepHlLang') === 'id') hlLang = 'id'; } catch (e) {}
        sw.addEventListener('click', function (e) {
            var btn = e.target.closest('[data-hl-lang]');
            if (!btn) return;
            hlLang = btn.getAttribute('data-hl-lang') === 'id' ? 'id' : 'en';
            try { window.localStorage.setItem('llrepHlLang', hlLang); } catch (err) {}
            renderHighlights();
        });
        renderHighlights();
    }

    // ── Charts ───────────────────────────────────────────────────────────────
    function barPalette() {
        return ['#3B82F6', '#56b1f3', '#EC4899', '#F59E0B', '#10B981', '#06B6D4', '#EF4444', '#84CC16'];
    }

    var BUCKET_ROWS = [
        ['completed', 'Completed', '#10B981'],
        ['on_progress', 'Active', '#F59E0B'],
        ['hold', 'Hold', '#56b1f3'],
        ['escalated', 'Escalated', '#EF4444'],
        ['cancelled', 'Cancelled', '#94A3B8'],
    ];

    // Per-cell status breakdown tooltip (company/type → {completed, on_progress, hold, escalated, cancelled}).
    // Built as a plain string (not a DOM node) since ApexCharts' `tooltip.custom` expects HTML.
    function breakdownTooltipHtml(breakdown, unit, category, dark) {
        var cell = (breakdown[unit] && breakdown[unit][category]) || {};
        var border = dark ? '#334155' : '#E2E8F0';
        var bg = dark ? '#0F172A' : '#FFFFFF';
        var text = dark ? '#E2E8F0' : '#1E293B';
        var muted = dark ? '#94A3B8' : '#64748B';

        var rowsHtml = BUCKET_ROWS.map(function (b) {
            var val = cell[b[0]] || 0;
            if (!val) return '';
            return '<div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:2px 0">'
                + '<span style="display:flex;align-items:center;gap:5px;color:' + muted + '">'
                + '<span style="width:7px;height:7px;border-radius:2px;background:' + b[2] + ';display:inline-block"></span>' + b[1] + '</span>'
                + '<span style="font-weight:700;color:' + text + '">' + val + '</span></div>';
        }).join('');

        var total = BUCKET_ROWS.reduce(function (sum, b) { return sum + (cell[b[0]] || 0); }, 0);

        return '<div style="background:' + bg + ';border:1px solid ' + border + ';border-radius:8px;padding:8px 10px;font-size:11px;min-width:160px;box-shadow:0 4px 12px rgba(0,0,0,.12)">'
            + '<div style="font-weight:700;color:' + text + ';margin-bottom:4px;white-space:nowrap">' + utils.escHtml(category) + ' — ' + utils.escHtml(unit) + '</div>'
            + (rowsHtml || '<div style="color:' + muted + '">No data</div>')
            + '<div style="border-top:1px solid ' + border + ';margin-top:4px;padding-top:4px;display:flex;justify-content:space-between;font-weight:700;color:' + text + '">'
            + '<span>Total</span><span>' + total + '</span></div>'
            + '</div>';
    }

    function renderStackedBar(key, elId, categories, series, colors, breakdown, opts) {
        var el = document.getElementById(elId);
        if (!el) return;

        chartData[key] = { elId: elId, categories: categories, series: series, colors: colors, breakdown: breakdown, opts: opts };
        var grouped = !!(opts && opts.stacked === false);
        var vertical = !!(opts && opts.vertical);
        var groupedStack = !!(opts && opts.groupedStack);   // stacked columns, several stacks per category (series[].group)

        if (charts[key]) { charts[key].destroy(); charts[key] = null; }
        if (!categories.length) {
            el.innerHTML = '<p class="py-16 text-center text-xs text-slate-400 dark:text-slate-500">No data for this period.</p>';
            return;
        }
        el.innerHTML = '';

        var dark = isDark();
        var height = vertical ? 300 : Math.max(grouped ? 340 : 280, categories.length * (grouped ? 80 : 32));

        // Counts are whole numbers — never label the value axis 0.2, 0.4 …
        var valueLabels = {
            style: { fontSize: '11px' },
            formatter: function (v) { return Math.abs(v - Math.round(v)) < 0.001 ? String(Math.round(v)) : ''; },
        };

        var tooltip = groupedStack
            ? {
                custom: function (o) {
                    var cur = o.w.config.series[o.seriesIndex];
                    var type = categories[o.dataPointIndex];
                    var dark2 = isDark();
                    var text = dark2 ? '#E2E8F0' : '#1E293B', muted = dark2 ? '#94A3B8' : '#64748B';
                    var rowsHtml = '', total = 0;
                    o.w.config.series.forEach(function (sr, i) {
                        if (sr.group !== cur.group) return;
                        var v = sr.data[o.dataPointIndex] || 0;
                        total += v;
                        if (!v) return;
                        rowsHtml += '<div style="display:flex;align-items:center;justify-content:space-between;gap:14px;padding:2px 0">'
                            + '<span style="display:flex;align-items:center;gap:5px;color:' + muted + '"><span style="width:7px;height:7px;border-radius:2px;background:' + o.w.globals.colors[i] + ';display:inline-block"></span>' + utils.escHtml(sr.name) + '</span>'
                            + '<span style="font-weight:700;color:' + text + '">' + v + '</span></div>';
                    });
                    return '<div style="background:' + (dark2 ? '#0F172A' : '#FFFFFF') + ';border:1px solid ' + (dark2 ? '#334155' : '#E2E8F0') + ';border-radius:8px;padding:8px 10px;font-size:11px;min-width:170px;box-shadow:0 4px 12px rgba(0,0,0,.12)">'
                        + '<div style="font-weight:700;color:' + text + ';margin-bottom:4px">' + utils.escHtml(type) + ' — ' + utils.escHtml(cur.group) + '</div>'
                        + (rowsHtml || '<div style="color:' + muted + '">None</div>')
                        + '<div style="border-top:1px solid ' + (dark2 ? '#334155' : '#E2E8F0') + ';margin-top:4px;padding-top:4px;display:flex;justify-content:space-between;font-weight:700;color:' + text + '"><span>Total</span><span>' + total + '</span></div></div>';
                },
            }
            : breakdown
            ? {
                custom: function (opts) {
                    var unit = categories[opts.dataPointIndex];
                    var category = opts.w.globals.seriesNames[opts.seriesIndex];
                    return breakdownTooltipHtml(breakdown, unit, category, isDark());
                },
            }
            : { theme: dark ? 'dark' : 'light' };

        charts[key] = new ApexCharts(el, {
            series: series,
            chart: {
                type: 'bar', height: height, stacked: !grouped,
                toolbar: { show: false }, zoom: { enabled: false },
                fontFamily: 'Inter, sans-serif',
                foreColor: dark ? '#94A3B8' : '#64748B',
                background: 'transparent',
                animations: { enabled: true, easing: 'easeinout', speed: 500 },
            },
            colors: colors || barPalette(),
            plotOptions: { bar: vertical
                ? { horizontal: false, columnWidth: '45%', borderRadius: 3, borderRadiusApplication: 'end' }
                : { horizontal: true, barHeight: '65%', borderRadius: 3, borderRadiusApplication: 'end' } },
            dataLabels: {
                enabled: true,
                formatter: function (val) { return val > 0 ? parseInt(val, 10) : ''; },
                style: { fontSize: '10px', fontWeight: 600, colors: ['#fff'] },
            },
            xaxis: { categories: categories, labels: vertical ? { style: { fontSize: '11px' } } : valueLabels },
            yaxis: { labels: vertical ? valueLabels : { style: { fontSize: '11px' } } },
            grid: { borderColor: dark ? '#1E293B' : '#F1F5F9', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            tooltip: tooltip,
            legend: Object.assign(
                { show: true, position: 'top', horizontalAlign: 'center', fontSize: '11px', markers: { radius: 6 } },
                // One legend entry per company (not per company × stage), and no toggling a single stage's slice.
                groupedStack && opts.legendItems ? { customLegendItems: opts.legendItems, onItemClick: { toggleDataSeries: false } } : {}
            ),
        });
        charts[key].render();
    }

    function loadCategoryByUnit(params) {
        fetchJson(routes.categoryByUnit, params).then(function (res) {
            var d = res.data || {};
            renderStackedBar('unit', 'llrepCategoryByUnitChart', d.categories || [], d.series || [], null, d.breakdown || {}, { vertical: true });
        }).catch(function () {});
    }

    // ── Memo & LoI documents by Office / Mall (stacked bar) ─────────────────
    function renderMemoBlock(key, chartId, block) {
        block = block || {};
        var received = block.received || 0;

        // Columns = Office / Mall, stacked by company (received documents).
        var props = received ? (block.by_property || []) : [];
        var companies = block.companies || [];
        renderStackedBar(key, chartId,
            props.map(function (p) { return p.property; }),
            companies.map(function (c) {
                return { name: c, data: props.map(function (p) { return (p.by_company || {})[c] || 0; }) };
            }),
            null, null, { vertical: true });
    }

    function loadMemoLoi(params) {
        fetchJson(routes.memoLoi, params).then(function (res) {
            var d = res.data || {};
            renderMemoBlock('memoNew', 'llrepNewLoiMemoChart', d.new_loi_memo);
            renderMemoBlock('memoAddendum', 'llrepAddendumMemoChart', d.addendum_memo);
        }).catch(function () {});
    }

    // ── Tenants sent + Surat 1 / Surat 2 / Escalation ───────────────────────
    function loadSentLetters(params) {
        fetchJson(routes.sentLetters, params).then(function (res) {
            var d = res.data || {};
            var rows = d.rows || [];
            var companies = d.companies || [];

            // Four stacked columns per document type (Sent → Surat 1 → Surat 2 → Escalation),
            // each stacked by company. series[].group puts the series of one stage in one stack.
            var stages = [['sent', 'Tenants Sent'], ['surat1', 'Surat 1'], ['surat2', 'Surat 2'], ['escalation', 'Escalation']];
            var palette = barPalette();
            var series = [];
            var colors = [];
            stages.forEach(function (st) {
                companies.forEach(function (c, ci) {
                    series.push({
                        name: c,
                        group: st[1],
                        data: rows.map(function (r) { return (((r.by_company || {})[c]) || {})[st[0]] || 0; }),
                    });
                    colors.push(palette[ci % palette.length]);
                });
            });

            renderStackedBar('sent', 'llrepSentLettersChart',
                rows.map(function (r) { return r.label; }),
                series, colors, null,
                { groupedStack: true, vertical: true, legendItems: companies });
        }).catch(function () {});
    }

    // ── PIC workload (stacked bar: PIC × document type) ─────────────────────
    var WORKLOAD_COLORS = { 'PSM': '#3B82F6', 'OLA': '#56b1f3', 'Addendum': '#F59E0B', 'Others': '#EC4899', 'Follow Up': '#10B981' };

    function loadPicWorkload(params) {
        fetchJson(routes.picWorkload, params).then(function (res) {
            var d = res.data || {};
            var series = (d.series || []).filter(function (s) {
                return (s.data || []).some(function (v) { return v > 0; });
            });
            renderStackedBar('picWorkload', 'llrepPicWorkloadChart', d.categories || [], series,
                series.map(function (s) { return WORKLOAD_COLORS[s.name] || '#64748B'; }));
        }).catch(function () {});
    }

    // ── Delay analysis (open agreements + delivered follow-ups) ─────────────
    var DELAY_CAUSE_STYLES = {
        hold: 'bg-violet-100 text-violet-700 border-violet-200',
        tenant: 'bg-amber-100 text-amber-700 border-amber-200',
        step: 'bg-sky-100 text-sky-700 border-sky-200',
        return: 'bg-orange-100 text-orange-700 border-orange-200',
        escalated: 'bg-rose-100 text-rose-700 border-rose-200',
        open: 'bg-slate-100 text-slate-600 border-slate-200',
    };

    function delayDetail(r) {
        var parts = [];

        if (r.hold) {
            parts.push('<div><span class="font-semibold">Hold' + (r.hold.days !== null ? ' ' + r.hold.days + 'd' : '') + ':</span> ' + utils.escHtml(r.hold.reason) + '</div>');
        }

        if (r.return) {
            var f = r.return;
            var line = '<div><span class="font-semibold">Delivered ' + utils.escHtml(f.delivered) + '</span> — signed copy not back after ' + f.days + 'd</div>';
            var letters = 'Surat 1: ' + (f.surat1 ? 'sent ' + utils.escHtml(f.surat1) : 'not yet')
                + ' · Surat 2: ' + (f.surat2 ? 'sent ' + utils.escHtml(f.surat2) : 'not yet')
                + ' · Escalation: ' + (f.escalated ? '<span class="font-semibold text-rose-600 dark:text-rose-400">escalated</span>' : 'not yet');
            var next = '';
            if (f.next) {
                var d = f.next_in_days;
                next = '<div class="' + (d < 0 ? 'font-semibold text-rose-600 dark:text-rose-400' : 'text-slate-500 dark:text-slate-400') + '">Next: ' + utils.escHtml(f.next)
                    + (d < 0 ? ' overdue ' + Math.abs(d) + 'd' : (d === 0 ? ' due today' : ' in ' + d + 'd')) + '</div>';
            }
            parts.push(line + '<div class="text-slate-500 dark:text-slate-400">' + letters + '</div>' + next);
        }

        if (r.tenant) {
            var docs = (r.tenant.docs || []).slice(0, 3).join(', ') + ((r.tenant.docs || []).length > 3 ? ' +' + (r.tenant.docs.length - 3) : '');
            parts.push('<div><span class="font-semibold">' + r.tenant.count + ' document' + (r.tenant.count > 1 ? 's' : '') + ' missing'
                + (r.tenant.days !== null ? ' (' + r.tenant.days + 'd)' : '') + ':</span> ' + utils.escHtml(docs) + '</div>');
        }

        if (r.step) {
            parts.push('<div><span class="font-semibold">In step ' + utils.escHtml(r.step.name) + '</span> for ' + r.step.days + 'd</div>');
        }

        if (r.revisions) {
            parts.push('<div class="text-slate-400">Revised ' + r.revisions + '×</div>');
        }

        return parts.length ? parts.join('') : '<span class="text-slate-400">Nothing blocking recorded</span>';
    }

    var delayRows = [];
    var delayPage = 1;
    var DELAY_PAGE_SIZE = 10;
    var delayGroup = 'ALL';   // PSM / OLA / ADDENDUM / OTHERS / FOLLOWUP
    var delayFu = 'ALL';      // follow-up sub-type: PSM / OLA / ADDENDUM / OTHERS / UNSPECIFIED

    var DELAY_GROUPS = [['ALL', 'All'], ['PSM', 'PSM'], ['OLA', 'OLA'], ['ADDENDUM', 'Addendum'], ['OTHERS', 'Others'], ['FOLLOWUP', 'Follow Up Document']];
    var DELAY_FU_TYPES = [['ALL', 'All Follow Up'], ['PSM', 'FU PSM'], ['OLA', 'FU OLA'], ['ADDENDUM', 'FU Addendum'], ['OTHERS', 'FU Others'], ['UNSPECIFIED', 'No type']];

    function delayInGroup(r, g) { return g === 'ALL' || r.group === g; }

    function delayFiltered() {
        return delayRows.filter(function (r) {
            if (!delayInGroup(r, delayGroup)) return false;
            return !(delayGroup === 'FOLLOWUP' && delayFu !== 'ALL' && r.fu_type !== delayFu);
        });
    }

    var delaySelectsReady = false;

    function delayOptionsHtml(items, current, counter) {
        return items.map(function (it) {
            return '<option value="' + utils.escHtml(it[0]) + '"' + (it[0] === current ? ' selected' : '') + '>'
                + utils.escHtml(it[1]) + ' (' + counter(it[0]) + ')</option>';
        }).join('');
    }

    // Two dropdowns, top right of the card: document type, and — only when
    // "Follow Up Document" is picked — which document the follow-up is for.
    function ensureDelaySelects() {
        var box = document.getElementById('llrepDelayFilters');
        if (!box || delaySelectsReady) return;
        delaySelectsReady = true;

        box.innerHTML = '<select id="llrepDelayGroup" aria-label="Document type" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></select>'
            + '<span id="llrepDelayFuWrap" class="hidden shrink-0"><select id="llrepDelayFu" aria-label="Follow up document type" class="rounded-lg border border-slate-200 bg-white px-2 py-1 text-xs dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"></select></span>';

        var $ = window.jQuery;
        var opts = { minimumResultsForSearch: Infinity, width: '12rem', dropdownCssClass: 'llrep-s2' };
        if ($ && $.fn && $.fn.select2) {
            $('#llrepDelayGroup').select2(opts);
            $('#llrepDelayFu').select2(opts);
            $('#llrepDelayGroup').on('change', function () { onDelayGroup(this.value); });
            $('#llrepDelayFu').on('change', function () { onDelayFu(this.value); });
        } else {
            // select2 missing — the plain selects still work.
            document.getElementById('llrepDelayGroup').addEventListener('change', function () { onDelayGroup(this.value); });
            document.getElementById('llrepDelayFu').addEventListener('change', function () { onDelayFu(this.value); });
        }
    }

    function onDelayGroup(v) { delayGroup = v; delayFu = 'ALL'; delayPage = 1; renderDelay(); }
    function onDelayFu(v) { delayFu = v; delayPage = 1; renderDelay(); }

    function renderDelayTabs() {
        ensureDelaySelects();
        var g = document.getElementById('llrepDelayGroup');
        var f = document.getElementById('llrepDelayFu');
        if (!g || !f) return;

        var fu = delayRows.filter(function (r) { return r.group === 'FOLLOWUP'; });
        var fuTypes = DELAY_FU_TYPES.filter(function (t) {
            // "No type" only when some follow-up really has none
            return t[0] !== 'UNSPECIFIED' || fu.some(function (r) { return r.fu_type === 'UNSPECIFIED'; });
        });

        g.innerHTML = delayOptionsHtml(DELAY_GROUPS, delayGroup, function (k) {
            return delayRows.filter(function (r) { return delayInGroup(r, k); }).length;
        });
        f.innerHTML = delayOptionsHtml(fuTypes, delayFu, function (k) {
            return fu.filter(function (r) { return k === 'ALL' || r.fu_type === k; }).length;
        });

        // Programmatic refresh of the select2 widgets (does not re-fire the change handlers).
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            window.jQuery(g).val(delayGroup).trigger('change.select2');
            window.jQuery(f).val(delayFu).trigger('change.select2');
        }

        var wrap = document.getElementById('llrepDelayFuWrap');
        if (wrap) wrap.classList.toggle('hidden', delayGroup !== 'FOLLOWUP');
    }

    function renderDelay() {
        var body = document.getElementById('llrepDelayBody');
        if (!body) return;
        renderDelayTabs();

        var list = delayFiltered();
        utils.setText('llrepDelayCount', list.length);

        if (!list.length) {
            body.innerHTML = '<tr><td colspan="6" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No open agreements for this filter.</td></tr>';
            utils.renderPagination('llrepDelay', 0, 1, DELAY_PAGE_SIZE, function () {});
            return;
        }

        var totalPages = Math.max(1, Math.ceil(list.length / DELAY_PAGE_SIZE));
        delayPage = Math.min(delayPage, totalPages);
        var pageRows = list.slice((delayPage - 1) * DELAY_PAGE_SIZE, delayPage * DELAY_PAGE_SIZE);

        body.innerHTML = pageRows.map(function (r) {
            var ageCls = r.age_days >= 30 ? 'text-rose-600 dark:text-rose-400' : (r.age_days >= 14 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-slate-300');
            return '<tr class="align-top transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">'
                + '<td class="whitespace-nowrap px-5 py-2.5"><a href="' + r.view_url + '" target="_blank" rel="noopener noreferrer" class="font-semibold text-slate-700 hover:underline dark:text-slate-200">' + utils.escHtml(r.docid) + '</a>'
                + '<div class="text-[10px] text-slate-400">' + utils.escHtml(r.type || '') + (r.company ? ' · ' + utils.escHtml(r.company) : '') + '</div></td>'
                + '<td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">' + utils.escHtml(r.tenant_name) + '</td>'
                + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.pic || '-') + '</td>'
                + '<td class="whitespace-nowrap px-4 py-2.5 text-center font-bold ' + ageCls + '">' + r.age_days + 'd</td>'
                + '<td class="whitespace-nowrap px-4 py-2.5"><span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold ' + (DELAY_CAUSE_STYLES[r.cause] || DELAY_CAUSE_STYLES.open) + '">' + utils.escHtml(r.cause_label) + '</span></td>'
                + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + delayDetail(r) + '</td>'
                + '</tr>';
        }).join('');

        utils.renderPagination('llrepDelay', list.length, delayPage, DELAY_PAGE_SIZE, function (p) {
            delayPage = p;
            renderDelay();
        });
    }

    function loadDelay(params) {
        fetchJson(routes.delay, params).then(function (res) {
            delayRows = res.data || [];
            delayPage = 1;
            renderDelay();
        }).catch(function () {});
    }

    // ── Table ────────────────────────────────────────────────────────────────
    function statusBadge(status) {
        var styles = {
            ACTIVE: 'bg-amber-100 text-amber-700 border-amber-200',
            COMPLETED: 'bg-emerald-100 text-emerald-700 border-emerald-200',
            HOLD: 'bg-violet-100 text-violet-700 border-violet-200',
            ESCALATED: 'bg-rose-100 text-rose-700 border-rose-200',
            CANCELLED: 'bg-slate-200 text-slate-700 border-slate-300',
        };
        var cls = styles[status] || 'bg-slate-100 text-slate-700 border-slate-200';
        return '<span class="inline-flex shrink-0 items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold whitespace-nowrap ' + cls + '">' + utils.escHtml(status || '-') + '</span>';
    }

    function docsCell(r) {
        if (!r.docs || r.docs === '-') return '<span class="text-slate-400">-</span>';
        var cls = r.incomplete_docs ? 'text-rose-600 font-semibold dark:text-rose-400' : 'text-slate-600 dark:text-slate-300';
        return '<span class="' + cls + '">' + utils.escHtml(r.docs) + '</span>';
    }

    function applyTableSearch() {
        var term = (document.getElementById('llrepTableSearch').value || '').trim().toLowerCase();
        if (!term) return tableAll;
        return tableAll.filter(function (r) {
            return [r.docid, r.no_psm, r.company, r.category, r.tenant, r.unit, r.status, r.cpny_id, r.pic]
                .some(function (f) { return (f || '').toString().toLowerCase().indexOf(term) !== -1; });
        });
    }

    function renderTable() {
        var filtered = tableSorted !== null ? tableSorted : applyTableSearch();
        var total = filtered.length;
        var totalPages = Math.max(1, Math.ceil(total / tablePageSize));
        tablePage = Math.min(tablePage, totalPages);

        var start = (tablePage - 1) * tablePageSize;
        var pageRows = filtered.slice(start, start + tablePageSize);

        var body = document.getElementById('llrepTableBody');
        if (!pageRows.length) {
            body.innerHTML = '<tr><td colspan="10" class="px-5 py-8 text-center text-slate-400 dark:text-slate-500">No data available</td></tr>';
        } else {
            body.innerHTML = pageRows.map(function (r) {
                return '<tr class="transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30">'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.date || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-700 dark:text-slate-200"><div class="font-semibold">' + utils.escHtml(r.docid || '-') + '</div>'
                    + (r.no_psm ? '<div class="text-[10px] text-slate-400">' + utils.escHtml(r.no_psm) + '</div>' : '') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.category || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.company || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-700 dark:text-slate-200">' + utils.escHtml(r.tenant || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.unit || '-') + '</td>'
                    + '<td class="px-4 py-2.5 text-slate-600 dark:text-slate-300">' + utils.escHtml(r.pic || '-') + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5">' + docsCell(r) + '</td>'
                    + '<td class="whitespace-nowrap px-4 py-2.5">' + statusBadge(r.status) + '</td>'
                    + '<td class="whitespace-nowrap px-5 py-2.5 text-center">'
                    + '<a href="' + r.view_url + '" target="_blank" rel="noopener noreferrer" class="inline-flex items-center rounded-md bg-gray-700 px-2.5 py-1 text-[11px] font-bold text-white transition-colors hover:bg-gray-800 dark:bg-cyan-700 dark:hover:bg-cyan-600">View</a>'
                    + '</td>'
                    + '</tr>';
            }).join('');
        }

        utils.renderPagination('llrepTable', total, tablePage, tablePageSize, function (p) {
            tablePage = p;
            renderTable();
        });
    }

    function loadTable(params) {
        fetchJson(routes.table, params).then(function (res) {
            tableAll = res.data || [];
            tableSorted = null;
            tablePage = 1;
            if (tableSortBind) tableSortBind.reset();
            renderTable();
        }).catch(function () {});
    }

    function bindTableEvents() {
        var search = document.getElementById('llrepTableSearch');
        if (search) search.addEventListener('keyup', function () {
            tableSorted = null;
            tablePage = 1;
            renderTable();
        });


        tableSortBind = utils.bindTableSort(
            'llrepTableBody',
            function () { return tableSorted !== null ? tableSorted : applyTableSearch(); },
            function (rows) { tableSorted = rows; },
            function () { tablePage = 1; },
            renderTable
        );
    }

    // ── Refresh on filter change ─────────────────────────────────────────────
    function refresh() {
        var params = utils.buildParams ? utils.buildParams() : '';
        loadSummary(params);
        loadMemoLoi(params);
        loadSentLetters(params);
        loadDelay(params);
        loadPicWorkload(params);
        loadCategoryByUnit(params);
        loadTable(params);
    }

    function init() {
        if (!document.getElementById('llrepTableBody')) return;
        bindTableEvents();
        bindHighlightLang();
        document.addEventListener('gm:filter', refresh);

        // Re-paint charts (not re-fetch) when dark mode toggles, using last-loaded data.
        new MutationObserver(function () {
            Object.keys(chartData).forEach(function (key) {
                var c = chartData[key];
                renderStackedBar(key, c.elId, c.categories, c.series, c.colors, c.breakdown, c.opts);
            });
        }).observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
