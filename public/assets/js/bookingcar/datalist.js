// ============================================================
// datalist.js — Booking Car
// Booking list panel: search, filter, pagination, item rendering
// ============================================================

const BookingCarDatalist = {

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
        BookingCarDatalist.bindSearch();
        BookingCarDatalist.bindFilters();
        BookingCarDatalist.bindPagination();
        BookingCarDatalist.bindToggle();
        BookingCarDatalist.load();
    },

    // --------------------------------------------------------
    // SEARCH — debounced
    // --------------------------------------------------------
    bindSearch() {
        const input = document.getElementById('bookingSearch');
        if (!input) return;

        input.addEventListener('input', () => {
            clearTimeout(BookingCarDatalist.state.debounceTimer);
            BookingCarDatalist.state.debounceTimer = setTimeout(() => {
                BookingCarDatalist.state.search = input.value.trim();
                BookingCarDatalist.state.page   = 1;
                BookingCarDatalist.load();
            }, 350);
        });
    },

    // --------------------------------------------------------
    // FILTER BUTTONS
    // --------------------------------------------------------
    bindFilters() {
        document.querySelectorAll('.booking-filter').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.booking-filter').forEach(b => {
                    b.classList.remove('active-filter');
                });
                btn.classList.add('active-filter');

                BookingCarDatalist.state.filter = btn.dataset.filter ?? 'ALL';
                BookingCarDatalist.state.page   = 1;
                BookingCarDatalist.load();
            });
        });
    },

    // --------------------------------------------------------
    // PAGINATION
    // --------------------------------------------------------
    bindPagination() {
        document.getElementById('prevBookingPage')
            ?.addEventListener('click', () => {
                if (BookingCarDatalist.state.page <= 1) return;
                BookingCarDatalist.state.page--;
                BookingCarDatalist.load();
            });

        document.getElementById('nextBookingPage')
            ?.addEventListener('click', () => {
                const maxPage = Math.ceil(
                    BookingCarDatalist.state.total / BookingCarDatalist.state.perPage
                );
                if (BookingCarDatalist.state.page >= maxPage) return;
                BookingCarDatalist.state.page++;
                BookingCarDatalist.load();
            });
    },

    // --------------------------------------------------------
    // TOGGLE LIST PANEL VISIBILITY
    // --------------------------------------------------------
    bindToggle() {
        const btn   = document.getElementById('toggleList');
        const panel = document.getElementById('bookingListPanel');
        if (!btn || !panel) return;

        btn.addEventListener('click', () => {
            panel.classList.toggle('hidden');
        });
    },

    // --------------------------------------------------------
    // LOAD — fetch from API and render
    // --------------------------------------------------------
    async load() {
        if (BookingCarDatalist.state.isLoading) return;
        BookingCarDatalist.state.isLoading = true;

        BookingCarDatalist.renderLoading();

        try {
            const params = new URLSearchParams({
                page:   BookingCarDatalist.state.page,
                search: BookingCarDatalist.state.search,
                filter: BookingCarDatalist.state.filter,
            });

            const url = `${BookingCar.routes.json}?${params.toString()}`;
            const res = await BookingCar.request(url);

            const items = res.data  ?? [];
            const total = res.total ?? items.length;
            const page  = res.page  ?? BookingCarDatalist.state.page;

            BookingCarDatalist.state.total = total;

            BookingCarDatalist.render(items);
            BookingCarDatalist.updateCount(total);
            BookingCarDatalist.updatePagination(page, total);

        } catch (err) {
            console.error('[BookingCarDatalist] Load error:', err);
            BookingCarDatalist.renderError();

        } finally {
            BookingCarDatalist.state.isLoading = false;
        }
    },

    // --------------------------------------------------------
    // PUBLIC: reload — reset to page 1 then load
    // --------------------------------------------------------
    reload() {
        BookingCarDatalist.state.page = 1;
        return BookingCarDatalist.load();
    },

    // --------------------------------------------------------
    // PUBLIC: refresh — reload without resetting page
    // --------------------------------------------------------
    refresh() {
        return BookingCarDatalist.load();
    },

    // --------------------------------------------------------
    // OPEN BOOKING DETAIL
    // --------------------------------------------------------
    openBookingDetail(eid) {
        if (!eid) return;
        BookingCarModal.openView(eid);
        BookingCarDetailModal?.loadDetail?.(eid);
    },

    // --------------------------------------------------------
    // RENDER: loading skeleton
    // --------------------------------------------------------
    renderLoading() {
        const body = document.getElementById('bookingListBody');
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
        const body = document.getElementById('bookingListBody');
        if (!body) return;

        body.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-400">
                <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400"></i>
                <span class="text-sm text-red-400">Failed to load bookings.</span>
            </div>`;
    },

    // --------------------------------------------------------
    // RENDER: booking item list
    // --------------------------------------------------------
    render(items) {
        const body = document.getElementById('bookingListBody');
        if (!body) return;

        if (!items || items.length === 0) {
            body.innerHTML = `
                <div class="flex flex-col items-center justify-center gap-3 py-12 text-slate-400">
                    <i class="fa-solid fa-inbox text-2xl"></i>
                    <span class="text-sm">No bookings found.</span>
                </div>`;
            return;
        }

        body.innerHTML = items.map(item => BookingCarDatalist.renderItem(item)).join('');

        body.querySelectorAll('.booking-item').forEach(el => {
            el.addEventListener('click', () => {
                BookingCarDatalist.openBookingDetail(el.dataset.eid);
            });
        });
    },

    // --------------------------------------------------------
    // RENDER: single booking card
    // --------------------------------------------------------
    renderItem(item) {
        const badge     = BookingCar.statusBadge(item.status);
        const date      = BookingCar.formatDate(item.booking_date);
        const startTime = item.start_time ? BookingCar.formatTime(item.start_time) : '-';
        const endTime   = item.end_time   ? BookingCar.formatTime(item.end_time)   : '-';
        const route     = item.route_summary ?? '-';
        const requester = item.user_peminta  ?? '-';

        return `
            <div class="booking-item group cursor-pointer rounded-lg border border-slate-200 bg-white p-3.5 transition hover:border-indigo-300 hover:shadow-sm dark:border-white/10 dark:bg-[#0f172a] dark:hover:border-indigo-500/40"
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
                        <i class="fa-solid fa-clock w-3 text-slate-400"></i>
                        <span>${startTime} – ${endTime}</span>
                    </div>

                    <div class="col-span-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-route w-3 text-slate-400"></i>
                        <span class="truncate">${route}</span>
                    </div>

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // UPDATE COUNT BADGE
    // --------------------------------------------------------
    updateCount(total) {
        const el = document.getElementById('bookingCount');
        if (el) el.textContent = total;
    },

    // --------------------------------------------------------
    // UPDATE PAGINATION INFO + BUTTON STATES
    // --------------------------------------------------------
    updatePagination(page, total) {
        const perPage = BookingCarDatalist.state.perPage;
        const from    = total === 0 ? 0 : (page - 1) * perPage + 1;
        const to      = Math.min(page * perPage, total);
        const maxPage = Math.ceil(total / perPage) || 1;

        const info = document.getElementById('bookingPageInfo');
        if (info) info.textContent = `Showing ${from} - ${to}`;

        const prev = document.getElementById('prevBookingPage');
        if (prev) prev.disabled = page <= 1;

        const next = document.getElementById('nextBookingPage');
        if (next) next.disabled = page >= maxPage;
    },
};
