(function () {
    'use strict';

    // Depends on gm-core.js (window.gmState, window.gmUtils, window.gmDispatchFilter)
    var routes = window.gmRoutes || {};
    var state  = window.gmState;
    var utils  = window.gmUtils;

    var deptData = [];

    // ── Date helpers ───────────────────────────────────────────────────────────
    function fmtDate(d) {
        var y   = d.getFullYear();
        var m   = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + day;
    }

    // "2026-05-15" → "15 May 2026"
    function fmtShort(iso) {
        if (!iso) return '';
        var parts = iso.split('-');
        var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return parseInt(parts[2], 10) + ' ' + months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
    }

    // "2026-05-15" → "May 2026"
    function fmtMonthYear(iso) {
        if (!iso) return '';
        var parts  = iso.split('-');
        var months = ['January','February','March','April','May','June',
                      'July','August','September','October','November','December'];
        return months[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
    }

    // ── Preset logic ───────────────────────────────────────────────────────────
    function applyPreset(preset) {
        var now = new Date();
        var y   = now.getFullYear();
        var m   = now.getMonth();      // 0-based
        var dom = now.getDate();

        switch (preset) {
            case 'today':
                state.dateFrom = state.dateTo = fmtDate(now);
                break;

            case 'this-week': {
                var dow    = (now.getDay() + 6) % 7;  // Mon=0 … Sun=6
                var monday = new Date(now); monday.setDate(dom - dow);
                var sunday = new Date(monday); sunday.setDate(monday.getDate() + 6);
                state.dateFrom = fmtDate(monday);
                state.dateTo   = fmtDate(sunday);
                break;
            }

            case 'this-month':
                state.dateFrom = fmtDate(new Date(y, m, 1));
                state.dateTo   = fmtDate(new Date(y, m + 1, 0));
                break;

            case 'last-month': {
                var lm = m === 0 ? 11 : m - 1;
                var ly = m === 0 ? y - 1 : y;
                state.dateFrom = fmtDate(new Date(ly, lm, 1));
                state.dateTo   = fmtDate(new Date(ly, lm + 1, 0));
                break;
            }

            case 'this-year':
                state.dateFrom = y + '-01-01';
                state.dateTo   = y + '-12-31';
                break;

            case 'last-year':
                state.dateFrom = (y - 1) + '-01-01';
                state.dateTo   = (y - 1) + '-12-31';
                break;
        }
        state.preset = preset;
        syncDateInputs();
        updateDateLabel();
        updatePresetActive();
        updatePeriodLabel();
    }

    function syncDateInputs() {
        var from = document.getElementById('gmDateFrom');
        var to   = document.getElementById('gmDateTo');
        if (from) from.value = state.dateFrom;
        if (to)   to.value   = state.dateTo;
    }

    // ── Label updates ──────────────────────────────────────────────────────────
    function updateDateLabel() {
        var el = document.getElementById('gmDateLabel');
        if (!el) return;
        var lbl;
        switch (state.preset) {
            case 'today':      lbl = 'Today · ' + fmtShort(state.dateFrom); break;
            case 'this-week':  lbl = 'This Week'; break;
            case 'this-month': lbl = 'This Month · ' + fmtMonthYear(state.dateFrom); break;
            case 'last-month': lbl = 'Last Month · ' + fmtMonthYear(state.dateFrom); break;
            case 'this-year':  lbl = 'This Year · ' + state.dateFrom.slice(0, 4); break;
            case 'last-year':  lbl = 'Last Year · ' + state.dateFrom.slice(0, 4); break;
            default:
                lbl = state.dateFrom === state.dateTo
                    ? fmtShort(state.dateFrom)
                    : fmtShort(state.dateFrom) + ' – ' + fmtShort(state.dateTo);
        }
        el.textContent = lbl;
    }

    function updatePresetActive() {
        document.querySelectorAll('.gmPreset').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.dataset.preset === state.preset);
        });
    }

    function updatePeriodLabel() {
        var lbl;
        switch (state.preset) {
            case 'today':      lbl = fmtShort(state.dateFrom); break;
            case 'this-week':  lbl = fmtShort(state.dateFrom) + ' – ' + fmtShort(state.dateTo); break;
            case 'this-month': lbl = fmtMonthYear(state.dateFrom); break;
            case 'last-month': lbl = fmtMonthYear(state.dateFrom); break;
            case 'this-year':  lbl = 'Full Year ' + state.dateFrom.slice(0, 4); break;
            case 'last-year':  lbl = 'Full Year ' + state.dateFrom.slice(0, 4); break;
            default:
                lbl = state.dateFrom === state.dateTo
                    ? fmtShort(state.dateFrom)
                    : fmtShort(state.dateFrom) + ' – ' + fmtShort(state.dateTo);
        }
        utils.setText('gmPeriodLabel', '· ' + lbl);
    }

    function updateDeptLabel() {
        var el = document.getElementById('gmDeptLabel');
        if (!el) return;
        el.textContent = state.depts.length
            ? state.depts.length + ' Dept Selected'
            : 'All Departments';
    }

    // ── Panel helpers ──────────────────────────────────────────────────────────
    function closeAllPanels() {
        ['gmDatePanel', 'gmDeptPanel'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        });
    }

    // ── API: Companies ─────────────────────────────────────────────────────────
    function loadCompanies() {
        fetch(routes.companies, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res.locked) {
                    var locked = document.getElementById('gmCompanyLocked');
                    var drop   = document.getElementById('gmCompanyDropdown');
                    if (locked) { locked.classList.remove('hidden'); locked.classList.add('flex'); }
                    if (drop)   drop.classList.add('hidden');
                    utils.setText('gmCompanyLockedText', res.single || (res.data || [])[0] || '');
                    state.cpnyId = res.single || (res.data || [])[0] || '';
                } else {
                    var sel = document.getElementById('gmCompanyFilter');
                    if (sel) {
                        sel.innerHTML = '<option value="">All Companies</option>';
                        (res.data || []).forEach(function (c) {
                            var o = document.createElement('option');
                            o.value = c; o.textContent = c;
                            sel.appendChild(o);
                        });
                    }
                }
                loadDepartments();
                window.gmDispatchFilter();
            })
            .catch(function () { window.gmDispatchFilter(); });
    }

    // ── API: Department list for the filter panel ──────────────────────────────
    function loadDepartments() {
        var params = '?date_from=' + encodeURIComponent(state.dateFrom)
            + '&date_to='   + encodeURIComponent(state.dateTo)
            + (state.cpnyId ? '&cpny_id=' + encodeURIComponent(state.cpnyId) : '');
        fetch(routes.departments + params, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        })
            .then(function (r) { return r.json(); })
            .then(function (res) { deptData = res.data || []; renderDeptPanel(); })
            .catch(function () {});
    }

    function renderDeptPanel(query) {
        var list = document.getElementById('gmDeptList');
        if (!list) return;
        var q        = (query || '').toLowerCase();
        var filtered = q
            ? deptData.filter(function (d) {
                return (d.name || '').toLowerCase().indexOf(q) !== -1
                    || (d.id   || '').toLowerCase().indexOf(q) !== -1;
              })
            : deptData;

        if (!filtered.length) {
            list.innerHTML = '<p class="py-4 text-center text-xs text-slate-400">No departments found.</p>';
            return;
        }
        list.innerHTML = filtered.map(function (d) {
            var checked = state.depts.indexOf(d.id) !== -1 ? 'checked' : '';
            return '<label class="gm-dept-item">'
                + '<input type="checkbox" value="' + utils.escHtml(d.id) + '" ' + checked + '>'
                + '<span class="dn">' + utils.escHtml(d.name || d.id) + '</span>'
                + '<span class="di">' + utils.escHtml(d.id) + '</span>'
                + '</label>';
        }).join('');
    }

    // ── Event bindings ─────────────────────────────────────────────────────────
    function bindEvents() {

        // Company dropdown
        var cpny = document.getElementById('gmCompanyFilter');
        if (cpny) cpny.addEventListener('change', function () {
            state.cpnyId = this.value;
            loadDepartments();
            window.gmDispatchFilter();
        });

        // Date panel toggle
        var dateBtn = document.getElementById('gmDateBtn');
        if (dateBtn) dateBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var dp     = document.getElementById('gmDatePanel');
            var closed = dp.classList.contains('hidden');
            closeAllPanels();
            if (closed) dp.classList.remove('hidden');
        });
        var datePanel = document.getElementById('gmDatePanel');
        if (datePanel) datePanel.addEventListener('click', function (e) { e.stopPropagation(); });

        // Preset buttons
        document.querySelectorAll('.gmPreset').forEach(function (btn) {
            btn.addEventListener('click', function () {
                applyPreset(this.dataset.preset);
                closeAllPanels();
                window.gmDispatchFilter();
            });
        });

        // Clear custom date range → back to This Year
        var clearCustom = document.getElementById('gmClearCustom');
        if (clearCustom) clearCustom.addEventListener('click', function () {
            applyPreset('this-year');
            closeAllPanels();
            loadDepartments();
            window.gmDispatchFilter();
        });

        // Custom date range apply
        var applyCustom = document.getElementById('gmApplyCustom');
        if (applyCustom) applyCustom.addEventListener('click', function () {
            var fromEl = document.getElementById('gmDateFrom');
            var toEl   = document.getElementById('gmDateTo');
            var df     = fromEl ? fromEl.value : '';
            var dt     = toEl   ? toEl.value   : '';
            if (!df || !dt) return;
            // Auto-swap if user entered range backwards
            if (df > dt) { var tmp = df; df = dt; dt = tmp; fromEl.value = df; toEl.value = dt; }
            state.dateFrom = df;
            state.dateTo   = dt;
            state.preset   = 'custom';
            updateDateLabel();
            updatePresetActive();
            updatePeriodLabel();
            closeAllPanels();
            loadDepartments();
            window.gmDispatchFilter();
        });

        // Dept panel toggle
        var deptBtn = document.getElementById('gmDeptBtn');
        if (deptBtn) deptBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var dp     = document.getElementById('gmDeptPanel');
            var closed = dp.classList.contains('hidden');
            closeAllPanels();
            if (closed) dp.classList.remove('hidden');
        });
        var deptPanel = document.getElementById('gmDeptPanel');
        if (deptPanel) deptPanel.addEventListener('click', function (e) { e.stopPropagation(); });

        // Dept search
        var deptSearch = document.getElementById('gmDeptSearch');
        if (deptSearch) deptSearch.addEventListener('input', function () { renderDeptPanel(this.value); });

        // Select all departments
        var selAll = document.getElementById('gmDeptSelectAll');
        if (selAll) selAll.addEventListener('click', function () {
            state.depts = deptData.map(function (d) { return d.id; });
            var q = document.getElementById('gmDeptSearch');
            renderDeptPanel(q ? q.value : '');
        });

        // Clear department selection
        var clrBtn = document.getElementById('gmDeptClear');
        if (clrBtn) clrBtn.addEventListener('click', function () {
            state.depts = [];
            var q = document.getElementById('gmDeptSearch');
            renderDeptPanel(q ? q.value : '');
        });

        // Apply department filter
        var deptApply = document.getElementById('gmDeptApply');
        if (deptApply) deptApply.addEventListener('click', function () {
            var checks = document.querySelectorAll('#gmDeptList input[type="checkbox"]:checked');
            state.depts = Array.prototype.map.call(checks, function (c) { return c.value; });
            updateDeptLabel();
            closeAllPanels();
            window.gmDispatchFilter();
        });

        // Refresh
        var refreshBtn = document.getElementById('gmRefreshBtn');
        if (refreshBtn) refreshBtn.addEventListener('click', function () {
            loadDepartments();
            window.gmDispatchFilter();
        });

        // Close panels on outside click
        document.addEventListener('click', closeAllPanels);
    }

    // ── Public API — lets other scripts reload departments with a custom URL ─────
    window.gmFilter = {
        reloadDepts: function (url) {
            if (url) {
                fetch(url + '?' + 'date_from=' + encodeURIComponent(state.dateFrom)
                        + '&date_to=' + encodeURIComponent(state.dateTo)
                        + (state.cpnyId ? '&cpny_id=' + encodeURIComponent(state.cpnyId) : ''), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        // res.data is an array of strings (isort) or objects {id,name} (HR)
                        deptData = (res.data || []).map(function (item) {
                            return typeof item === 'string'
                                ? { id: item, name: item }
                                : item;
                        });
                        state.depts = [];
                        utils.setText('gmDeptLabel', 'All Departments');
                        renderDeptPanel();
                    })
                    .catch(function () {});
            } else {
                state.depts = [];
                utils.setText('gmDeptLabel', 'All Departments');
                loadDepartments();
            }
        },
    };

    // ── Init ──────────────────────────────────────────────────────────────────
    function init() {
        applyPreset('this-year');   // sets dateFrom/dateTo before first fetch
        bindEvents();
        loadCompanies();            // → loadDepartments() → gmDispatchFilter()
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
