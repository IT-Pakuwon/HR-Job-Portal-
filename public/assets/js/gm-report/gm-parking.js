(function () {
    'use strict';

    var routes = window.gmRoutes || {};
    var utils  = window.gmUtils;

    var activeSite  = null; // null = All sites
    var lastFilter  = {};

    // ── Site tab helpers ──────────────────────────────────────────────────────

    function buildSiteTabs(sites) {
        var container = document.getElementById('parkingSiteTabsContainer');
        if (!container) return;
        container.innerHTML = '';

        // "All" button
        var allBtn = document.createElement('button');
        allBtn.type         = 'button';
        allBtn.textContent  = 'All';
        allBtn.dataset.site = '';
        allBtn.className    = 'rounded-lg px-2 py-1 text-[10px] font-semibold transition '
                            + (activeSite === null ? 'pgcard-tab-active' : 'pgcard-tab-idle');
        allBtn.addEventListener('click', function () { setActiveSite(null); });
        container.appendChild(allBtn);

        sites.forEach(function (siteId) {
            var btn = document.createElement('button');
            btn.type         = 'button';
            btn.textContent  = siteId;
            btn.dataset.site = siteId;
            btn.className    = 'rounded-lg px-2 py-1 text-[10px] font-semibold transition '
                             + (activeSite === siteId ? 'pgcard-tab-active' : 'pgcard-tab-idle');
            btn.addEventListener('click', function () { setActiveSite(siteId); });
            container.appendChild(btn);
        });
    }

    function setActiveSite(siteId) {
        activeSite = siteId || null;

        var container = document.getElementById('parkingSiteTabsContainer');
        if (container) {
            container.querySelectorAll('button[data-site]').forEach(function (btn) {
                var isActive = btn.dataset.site === (activeSite || '');
                btn.classList.toggle('pgcard-tab-active', isActive);
                btn.classList.toggle('pgcard-tab-idle',   !isActive);
            });
        }

        loadAllCharts();
    }

    // ── Param builder (core params + optional site override) ─────────────────

    function buildParams(extra) {
        var base = utils.buildParams();
        var parts = base ? [base.slice(1)] : [];
        if (activeSite) parts.push('site_id=' + encodeURIComponent(activeSite));
        if (extra)      parts.push(extra);
        return parts.length ? '?' + parts.join('&') : '';
    }

    // ── Load site list from BigQuery ──────────────────────────────────────────

    function loadSites() {
        var container = document.getElementById('parkingSiteTabsContainer');
        if (container) container.innerHTML = '<span class="text-[10px] text-slate-400">Loading…</span>';

        fetch(routes.parkingSites + utils.buildParams(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            var sites = res.data || [];
            buildSiteTabs(sites);
            loadAllCharts();
        })
        .catch(function () {
            var c = document.getElementById('parkingSiteTabsContainer');
            if (c) c.innerHTML = '<span class="text-[10px] text-red-400">Failed to load sites</span>';
        });
    }

    // ── KPI helpers ───────────────────────────────────────────────────────────

    function setText(id, v) { utils.setText(id, v); }

    function resetKpis() {
        ['parkingKpiIncome', 'parkingKpiTxn', 'parkingKpiDuration', 'parkingKpiAvg'].forEach(function (id) {
            utils.setText(id, '—');
        });
    }

    // ── Income period tab state ───────────────────────────────────────────────

    var incomePeriod = 'daily'; // 'daily' | 'monthly'

    function setIncomePeriod(period) {
        incomePeriod = period;
        ['daily', 'monthly'].forEach(function (k) {
            var btn = document.getElementById('parkingIncomePeriod_' + k);
            if (!btn) return;
            btn.classList.toggle('pgcard-tab-active', k === period);
            btn.classList.toggle('pgcard-tab-idle',   k !== period);
        });
        loadIncomeChart();
    }

    // ── Chart instances ───────────────────────────────────────────────────────

    var charts = {
        income:     null,
        peakHour:   null,
        vehicle:    null,
        payment:    null,
        member:     null,
        repetitive: null,
    };

    function destroyChart(key) {
        if (charts[key]) {
            try { charts[key].destroy(); } catch (e) {}
            charts[key] = null;
        }
    }

    // ── Shared ApexCharts theme ───────────────────────────────────────────────

    function isDark() { return utils.isDark(); }

    function baseChartOpts() {
        var dark = isDark();
        return {
            chart: { toolbar: { show: false }, background: 'transparent', fontFamily: 'inherit' },
            theme: { mode: dark ? 'dark' : 'light' },
            tooltip: { theme: dark ? 'dark' : 'light' },
            grid: { borderColor: dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)', strokeDashArray: 4 },
        };
    }

    // ── 1. Income Trend ───────────────────────────────────────────────────────

    function loadIncomeChart() {
        // placeholder — charts will be implemented after siteId format is confirmed
        var el = document.getElementById('parkingIncomeTrendChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── 2. Peak Hour Heatmap ──────────────────────────────────────────────────

    function loadPeakHourChart() {
        var el = document.getElementById('parkingPeakHourChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── 3. Vehicle Type + Revenue ─────────────────────────────────────────────

    function loadVehicleChart() {
        var el = document.getElementById('parkingVehicleTypeChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── 4. Payment Method ─────────────────────────────────────────────────────

    function loadPaymentChart() {
        var el = document.getElementById('parkingPaymentChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── 5. Member vs Non-member ───────────────────────────────────────────────

    function loadMemberChart() {
        var el = document.getElementById('parkingMemberChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── 6. Repetitive Nopol ──────────────────────────────────────────────────

    function loadRepetitiveChart() {
        var el = document.getElementById('parkingRepetitiveChart');
        if (!el) return;
        el.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-400">Loading…</div>';
    }

    // ── Load all charts ───────────────────────────────────────────────────────

    function loadAllCharts() {
        resetKpis();
        loadIncomeChart();
        loadPeakHourChart();
        loadVehicleChart();
        loadPaymentChart();
        loadMemberChart();
        loadRepetitiveChart();
    }

    // ── Bind income period buttons ────────────────────────────────────────────

    function bindIncomePeriod() {
        ['daily', 'monthly'].forEach(function (k) {
            var btn = document.getElementById('parkingIncomePeriod_' + k);
            if (btn) btn.addEventListener('click', function () { setIncomePeriod(k); });
        });
    }

    // ── Filter listener ───────────────────────────────────────────────────────

    document.addEventListener('gm:filter', function () {
        loadSites();
    });

    // ── Tab visibility: only load when parking tab is active ─────────────────

    document.addEventListener('gm:tab-switch', function (e) {
        if (e.detail && (e.detail.tab === 'parking' || e.detail.tab === 'all')) {
            loadSites();
        }
    });

    // ── Init ──────────────────────────────────────────────────────────────────

    bindIncomePeriod();

})();
