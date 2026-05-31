// ============================================================
// datalist.js — Voucher Taxi
// List panel: search, filter, pagination, item rendering
// ============================================================

const VoucherTaxiDataList = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        page:          1,
        perPage:       10,
        total:         0,
        filter:        'P',
        search:        '',
        isLoading:     false,
        debounceTimer: null,
    },

    // --------------------------------------------------------
    // INIT — wire events once on page load
    // --------------------------------------------------------
    init() {
        VoucherTaxiDataList.bindSearch();
        VoucherTaxiDataList.bindFilters();
        VoucherTaxiDataList.bindPagination();
        VoucherTaxiDataList.bindRows();
        VoucherTaxiDataList.load();
    },

    // --------------------------------------------------------
    // SEARCH — debounced
    // --------------------------------------------------------
    bindSearch() {
        const input = document.getElementById('voucherSearch');
        if (!input) return;

        input.addEventListener('input', () => {
            clearTimeout(VoucherTaxiDataList.state.debounceTimer);
            VoucherTaxiDataList.state.debounceTimer = setTimeout(() => {
                VoucherTaxiDataList.state.search = input.value.trim();
                VoucherTaxiDataList.state.page   = 1;
                VoucherTaxiDataList.load();
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

                const filter = btn.dataset.filter ?? 'ALL';
                VoucherTaxiDataList.state.filter = filter === 'ALL' ? '' : filter;
                VoucherTaxiDataList.state.page   = 1;
                VoucherTaxiDataList.load();
            });
        });
    },

    // --------------------------------------------------------
    // PAGINATION
    // --------------------------------------------------------
    bindPagination() {
        document.getElementById('prevVoucherPage')
            ?.addEventListener('click', () => {
                if (VoucherTaxiDataList.state.page <= 1) return;
                VoucherTaxiDataList.state.page--;
                VoucherTaxiDataList.load();
            });

        document.getElementById('nextVoucherPage')
            ?.addEventListener('click', () => {
                const maxPage = Math.ceil(
                    VoucherTaxiDataList.state.total / VoucherTaxiDataList.state.perPage
                );
                if (VoucherTaxiDataList.state.page >= maxPage) return;
                VoucherTaxiDataList.state.page++;
                VoucherTaxiDataList.load();
            });
    },

    // --------------------------------------------------------
    // ROW CLICK → open detail
    // --------------------------------------------------------
    bindRows() {
        document.getElementById('voucherListBody')
            ?.addEventListener('click', (e) => {
                const row = e.target.closest('.voucher-list-item');
                if (!row) return;
                const eid = row.dataset.eid;
                if (eid) VoucherTaxiDetailModal.load(eid);
            });
    },

    // --------------------------------------------------------
    // LOAD DATA FROM API
    // --------------------------------------------------------
    load() {
        if (VoucherTaxiDataList.state.isLoading) return;
        VoucherTaxiDataList.state.isLoading = true;

        const s = VoucherTaxiDataList.state;
        const params = new URLSearchParams({
            draw:           s.page,
            start:          (s.page - 1) * s.perPage,
            length:         s.perPage,
            'search[value]': s.search,
            status:         s.filter,
        });

        fetch(`${VoucherTaxi.routes.json}?${params}`, {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            VoucherTaxiDataList.state.total = data.recordsFiltered ?? data.recordsTotal ?? 0;
            VoucherTaxiDataList.render(data.data ?? []);
            VoucherTaxiDataList.updatePagination();
        })
        .catch(() => {
            VoucherTaxiDataList.renderEmpty();
        })
        .finally(() => {
            VoucherTaxiDataList.state.isLoading = false;
        });
    },

    // --------------------------------------------------------
    // RENDER ITEMS
    // --------------------------------------------------------
    render(items) {
        const container = document.getElementById('voucherListBody');
        if (!container) return;

        if (!items.length) {
            VoucherTaxiDataList.renderEmpty();
            return;
        }

        container.innerHTML = items.map(item => VoucherTaxiDataList.itemHtml(item)).join('');
    },

    // --------------------------------------------------------
    // ITEM HTML  — same card style as Booking Car
    // --------------------------------------------------------
    itemHtml(item) {
        const badge     = VoucherTaxi.statusBadge(item.status);
        const date      = VoucherTaxi.formatDate(item.date_used);
        const route     = item.route_summary ?? `${item.origin ?? '-'} → ${item.destination ?? '-'}`;
        const requester = item.user_peminta ?? '-';
        const cpny      = item.cpny_id ?? '-';

        return `
            <div class="voucher-list-item group cursor-pointer rounded-lg border border-slate-200 bg-white p-3.5 transition hover:border-indigo-300 hover:shadow-sm dark:border-white/10 dark:bg-[#0f172a] dark:hover:border-indigo-500/40"
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
                        <i class="fa-solid fa-building w-3 text-slate-400"></i>
                        <span class="truncate">${cpny}</span>
                    </div>

                    <div class="col-span-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-route w-3 text-slate-400"></i>
                        <span class="truncate">${route}</span>
                    </div>

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // EMPTY STATE
    // --------------------------------------------------------
    renderEmpty() {
        const container = document.getElementById('voucherListBody');
        if (!container) return;
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <i class="fa-solid fa-inbox mb-3 text-4xl text-slate-300 dark:text-slate-600"></i>
                <p class="text-slate-500 dark:text-slate-400">No vouchers found</p>
                <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">Try adjusting your filters or search</p>
            </div>
        `;
    },

    // --------------------------------------------------------
    // PAGINATION INFO
    // --------------------------------------------------------
    updatePagination() {
        const s       = VoucherTaxiDataList.state;
        const start   = s.total === 0 ? 0 : (s.page - 1) * s.perPage + 1;
        const end     = Math.min(s.page * s.perPage, s.total);
        const maxPage = Math.ceil(s.total / s.perPage);

        const info = document.getElementById('voucherPageInfo');
        if (info) info.textContent = `Showing ${start} - ${end} of ${s.total}`;

        const prev = document.getElementById('prevVoucherPage');
        const next = document.getElementById('nextVoucherPage');
        if (prev) prev.disabled = s.page <= 1;
        if (next) next.disabled = s.page >= maxPage;

        const count = document.getElementById('voucherCount');
        if (count) count.textContent = s.total;
    },

    // --------------------------------------------------------
    // PUBLIC HELPERS
    // --------------------------------------------------------
    reload() {
        VoucherTaxiDataList.state.page = 1;
        VoucherTaxiDataList.load();
    },

    refresh() {
        VoucherTaxiDataList.load();
    },
};
