// ============================================================
// datalist.js — Voucher Taxi
// Voucher list panel: search, filter, pagination, item rendering
// ============================================================

const VoucherTaxiDatalist = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        page:          1,
        perPage:       10,
        total:         0,
        filter:        'ALL',
        search:        '',
        isLoading:     false,
        debounceTimer: null,
    },

    // --------------------------------------------------------
    // INIT — wire events once on page load
    // --------------------------------------------------------
    init() {
        VoucherTaxiDatalist.bindSearch();
        VoucherTaxiDatalist.bindFilters();
        VoucherTaxiDatalist.bindPagination();
        VoucherTaxiDatalist.bindToggle();
        VoucherTaxiDatalist.load();
    },

    // --------------------------------------------------------
    // SEARCH — debounced
    // --------------------------------------------------------
    bindSearch() {
        const input = document.getElementById('voucherSearch');
        if (!input) return;

        input.addEventListener('input', () => {
            clearTimeout(VoucherTaxiDatalist.state.debounceTimer);
            VoucherTaxiDatalist.state.debounceTimer = setTimeout(() => {
                VoucherTaxiDatalist.state.search = input.value.trim();
                VoucherTaxiDatalist.state.page   = 1;
                VoucherTaxiDatalist.load();
            }, 350);
        });
    },

    // --------------------------------------------------------
    // FILTER BUTTONS
    // --------------------------------------------------------
    bindFilters() {
        document.querySelectorAll('.voucher-filter').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.voucher-filter').forEach(b => {
                    b.classList.remove('active-filter');
                });
                btn.classList.add('active-filter');

                VoucherTaxiDatalist.state.filter = btn.dataset.filter ?? 'ALL';
                VoucherTaxiDatalist.state.page   = 1;
                VoucherTaxiDatalist.load();
            });
        });
    },

    // --------------------------------------------------------
    // PAGINATION
    // --------------------------------------------------------
    bindPagination() {
        document.getElementById('prevVoucherPage')
            ?.addEventListener('click', () => {
                if (VoucherTaxiDatalist.state.page <= 1) return;
                VoucherTaxiDatalist.state.page--;
                VoucherTaxiDatalist.load();
            });

        document.getElementById('nextVoucherPage')
            ?.addEventListener('click', () => {
                const maxPage = Math.ceil(
                    VoucherTaxiDatalist.state.total / VoucherTaxiDatalist.state.perPage
                );
                if (VoucherTaxiDatalist.state.page >= maxPage) return;
                VoucherTaxiDatalist.state.page++;
                VoucherTaxiDatalist.load();
            });
    },

    // --------------------------------------------------------
    // TOGGLE LIST PANEL VISIBILITY
    // --------------------------------------------------------
    bindToggle() {
        const btn   = document.getElementById('toggleList');
        const panel = document.getElementById('voucherListPanel');
        if (!btn || !panel) return;

        btn.addEventListener('click', () => {
            panel.classList.toggle('hidden');
        });
    },

    // --------------------------------------------------------
    // LOAD — fetch from API and render
    // --------------------------------------------------------
    async load() {
        if (VoucherTaxiDatalist.state.isLoading) return;
        VoucherTaxiDatalist.state.isLoading = true;

        VoucherTaxiDatalist.renderLoading();

        try {
            const params = new URLSearchParams({
                draw:   VoucherTaxiDatalist.state.page,
                start:  (VoucherTaxiDatalist.state.page - 1) * VoucherTaxiDatalist.state.perPage,
                length: VoucherTaxiDatalist.state.perPage,
                search: VoucherTaxiDatalist.state.search,
                status: VoucherTaxiDatalist.state.filter === 'ALL' ? '' : VoucherTaxiDatalist.state.filter,
            });

            const url = `${VoucherTaxi.routes.json}?${params.toString()}`;
            const res = await VoucherTaxi.request(url);

            const items = res.data ?? [];
            const total = res.recordsFiltered ?? res.recordsTotal ?? 0;

            VoucherTaxiDatalist.state.total = total;

            VoucherTaxiDatalist.render(items);
            VoucherTaxiDatalist.updateCount(total);
            VoucherTaxiDatalist.updatePagination(VoucherTaxiDatalist.state.page, total);

        } catch (err) {
            console.error('[VoucherTaxiDatalist] Load error:', err);
            VoucherTaxiDatalist.renderError();

        } finally {
            VoucherTaxiDatalist.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // PUBLIC: reload — reset to page 1 then load
    // --------------------------------------------------------
    reload() {
        VoucherTaxiDatalist.state.page = 1;
        return VoucherTaxiDatalist.load();
    },

    // --------------------------------------------------------
    // PUBLIC: refresh — reload without resetting page
    // --------------------------------------------------------
    refresh() {
        return VoucherTaxiDatalist.load();
    },

    // --------------------------------------------------------
    // OPEN VOUCHER DETAIL
    // --------------------------------------------------------
    openVoucherDetail(eid) {
        if (!eid) return;
        VoucherTaxiModal.openView(eid);
        VoucherTaxiDetailModal?.loadDetail?.(eid);
    },

    // --------------------------------------------------------
    // RENDER: loading skeleton
    // --------------------------------------------------------
    renderLoading() {
        const body = document.getElementById('voucherListBody');
        if (!body) return;

        body.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-400">
                <i class="fa-solid fa-spinner fa-spin text-2xl"></i>
                <span class="text-sm">Loading...</span>
            </div>`;
    },

    // --------------------------------------------------------
    // RENDER: error state
    // --------------------------------------------------------
    renderError() {
        const body = document.getElementById('voucherListBody');
        if (!body) return;

        body.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-400">
                <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400"></i>
                <span class="text-sm text-red-400">Failed to load vouchers.</span>
            </div>`;
    },

    // --------------------------------------------------------
    // RENDER: voucher item list
    // --------------------------------------------------------
    render(items) {
        const body = document.getElementById('voucherListBody');
        if (!body) return;

        if (!items || items.length === 0) {
            body.innerHTML = `
                <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-400">
                    <i class="fa-solid fa-inbox text-2xl"></i>
                    <span class="text-sm">No vouchers found.</span>
                </div>`;
            return;
        }

        body.innerHTML = items.map(item => VoucherTaxiDatalist.renderItem(item)).join('');

        body.querySelectorAll('.voucher-item').forEach(el => {
            el.addEventListener('click', () => {
                VoucherTaxiDatalist.openVoucherDetail(el.dataset.eid);
            });
        });
    },

    // --------------------------------------------------------
    // RENDER: single voucher card
    // --------------------------------------------------------
    renderItem(item) {
        const badge     = VoucherTaxi.statusBadge(item.status);
        const date      = VoucherTaxi.formatDate(item.date_used || item.voucher_date);
        const origin    = item.origin ?? '-';
        const dest      = item.destination ?? '-';
        const route     = `${origin} → ${dest}`;
        const requester = item.user_peminta ?? '-';
        const purpose   = item.purpose ?? item.purpose_descr ?? '-';

        return `
            <div class="voucher-item group cursor-pointer rounded-lg border border-slate-200 bg-white p-3.5 transition hover:border-indigo-300 hover:shadow-sm dark:border-white/10 dark:bg-[#0f172a] dark:hover:border-indigo-500/40"
                 data-eid="${item.eid}">

                <div class="flex items-start justify-between gap-2">

                    <div class="min-w-0 flex-1">
                        <span class="truncate text-xs font-bold text-slate-800 dark:text-slate-100">
                            ${item.docid ?? '-'}
                        </span>
                        <div class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">
                            <i class="fa-solid fa-user mr-1"></i>${requester}
                        </div>
                    </div>

                    <div class="shrink-0">${badge}</div>

                </div>

                <div class="mt-2.5 grid grid-cols-2 gap-1.5 text-xs text-slate-500 dark:text-slate-400">

                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-calendar-days w-3 text-slate-400"></i>
                        <span>${date}</span>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <i class="fa-solid fa-car w-3 text-slate-400"></i>
                        <span class="truncate">${route}</span>
                    </div>

                    <div class="col-span-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-tag w-3 text-slate-400"></i>
                        <span class="truncate" title="${purpose}">${purpose}</span>
                    </div>

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // UPDATE COUNT BADGE
    // --------------------------------------------------------
    updateCount(total) {
        const el = document.getElementById('voucherCount');
        if (el) el.textContent = total;
    },

    // --------------------------------------------------------
    // UPDATE PAGINATION INFO + BUTTON STATES
    // --------------------------------------------------------
    updatePagination(page, total) {
        const perPage = VoucherTaxiDatalist.state.perPage;
        const from    = total === 0 ? 0 : (page - 1) * perPage + 1;
        const to      = Math.min(page * perPage, total);
        const maxPage = Math.ceil(total / perPage) || 1;

        const info = document.getElementById('voucherPageInfo');
        if (info) info.textContent = `Showing ${from} - ${to}`;

        const prev = document.getElementById('prevVoucherPage');
        if (prev) prev.disabled = page <= 1;

        const next = document.getElementById('nextVoucherPage');
        if (next) next.disabled = page >= maxPage;
    },
};
