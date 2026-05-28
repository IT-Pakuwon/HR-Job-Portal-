(function () {
    "use strict";

    BookingCar.DataList = {

        loading: false,

        init() {

            this.bindEvents();
            this.load();
        },

        bindEvents() {

            $("#bookingSearch").on(
                "keyup",
                BookingCar.debounce(() => {

                    BookingCar.state.currentSearch =
                        $("#bookingSearch")
                            .val()
                            .trim();

                    BookingCar.state.currentPage = 1;

                    this.load();

                }, 400),
            );

            $(document).on(
                "click",
                ".booking-filter",
                (e) => {

                    $(".booking-filter")
                        .removeClass(
                            "active-filter"
                        );

                    $(e.currentTarget)
                        .addClass(
                            "active-filter"
                        );

                    BookingCar.state.currentFilter =
                        $(e.currentTarget)
                            .data("filter");

                    BookingCar.state.currentPage = 1;

                    this.load();
                },
            );

            $("#prevBookingPage").on(
                "click",
                () => {

                    if (
                        BookingCar.state.currentPage <= 1
                    ) {
                        return;
                    }

                    BookingCar.state.currentPage--;

                    this.load();
                },
            );

            $("#nextBookingPage").on(
                "click",
                () => {

                    const totalPages = Math.ceil(
                        BookingCar.state.totalRows /
                        BookingCar.state.pageSize
                    );

                    if (
                        BookingCar.state.currentPage >= totalPages
                    ) {
                        return;
                    }

                    BookingCar.state.currentPage++;

                    this.load();
                },
            );

            $(document).on(
                "click",
                ".booking-item",
                async (e) => {

                    $(".booking-item")
                        .removeClass(
                            "ring-2 ring-indigo-500 border-indigo-400"
                        );

                    $(e.currentTarget)
                        .addClass(
                            "ring-2 ring-indigo-500 border-indigo-400"
                        );

                    const eid =
                        $(e.currentTarget)
                            .data("eid");

                    if (!eid) return;

                    if (
                        typeof BookingCar.openBookingDetail === 'function'
                    ) {

                        await BookingCar.openBookingDetail(
                            eid
                        );
                    }
                },
            );
        },

        showLoading() {

            const $body =
                $("#bookingListBody");

            let skeleton = '';

            for (let i = 0; i < 5; i++) {

                skeleton += `
                    <div class="animate-pulse rounded-2xl border border-slate-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.02]">

                        <div class="flex items-start justify-between gap-3">

                            <div class="flex-1">

                                <div class="h-3 w-24 rounded bg-slate-200 dark:bg-white/10"></div>

                                <div class="mt-3 h-4 w-40 rounded bg-slate-200 dark:bg-white/10"></div>

                                <div class="mt-3 h-3 w-32 rounded bg-slate-200 dark:bg-white/10"></div>

                            </div>

                            <div class="h-6 w-20 rounded-full bg-slate-200 dark:bg-white/10"></div>

                        </div>

                    </div>
                `;
            }

            $body.html(skeleton);
        },

        load() {

            if (this.loading) {
                return;
            }

            this.loading = true;

            this.showLoading();

            const page =
                BookingCar.state.currentPage;

            const length =
                BookingCar.state.pageSize;

            const start =
                (page - 1) * length;

            let status =
                BookingCar.state.currentFilter;

            if (status === "ALL") {
                status = "";
            }

            $.ajax({

                url:
                    BookingCar.config.routes.json,

                type: "GET",

                data: {
                    draw: 1,
                    start,
                    length,

                    status,

                    search: {
                        value:
                            BookingCar.state.currentSearch,
                    },
                },

                success: (res) => {

                    BookingCar.state.totalRows =
                        res.recordsFiltered || 0;

                    BookingCar.state.bookingData =
                        res.data || [];

                    this.render(
                        res.data || []
                    );

                    this.updateInfo();

                    $("#bookingCount")
                        .text(
                            res.recordsFiltered || 0
                        );

                    if (
                        typeof BookingCar.refreshCalendarEvents === 'function'
                    ) {

                        BookingCar.refreshCalendarEvents();
                    }
                },

                error: (xhr) => {

                    console.error(
                        'Booking list load failed:',
                        xhr
                    );

                    BookingCar.showError?.(
                        'Failed to load booking list'
                    );
                },

                complete: () => {

                    this.loading = false;
                },
            });
        },

        render(rows) {

            const $body =
                $("#bookingListBody");

            const isSearching =
                BookingCar.state.currentSearch;

            if (!rows.length) {

                const emptyText =
                    isSearching
                        ? 'No matching booking found'
                        : 'No booking request available';

                $body.html(`
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center dark:border-white/10 dark:bg-white/[0.02]">

                        <div class="text-4xl">
                            🚘
                        </div>

                        <div class="mt-3 text-sm font-semibold text-slate-700 dark:text-slate-200">
                            ${emptyText}
                        </div>

                        <div class="mt-1 text-xs text-slate-400">
                            Booking data will appear here
                        </div>

                    </div>
                `);

                return;
            }

            let html = "";

            rows.forEach((row) => {

                const badge =
                    BookingCar.getStatusBadge(
                        row.status
                    );

                const routeLabel = (() => {

                    if (
                        Array.isArray(
                            row.details
                        ) &&
                        row.details.length > 1
                    ) {

                        return 'Multiple Route';
                    }

                    if (
                        Array.isArray(
                            row.details
                        ) &&
                        row.details.length === 1
                    ) {

                        return (
                            row.details[0].tujuan ||
                            row.details[0].route ||
                            row.details[0].destination ||
                            '-'
                        );
                    }

                    return '-';

                })();

                html += `
<div
    class="booking-item cursor-pointer rounded-2xl border border-slate-200 bg-white px-4 py-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.02]"
    data-eid="${row.eid}"
>

    <div class="flex items-start justify-between gap-3">

        <div class="min-w-0 flex-1">

            <div class="flex items-center gap-2">

                <div class="truncate text-[11px] font-semibold text-slate-900 dark:text-white">
                    ${row.docid}
                </div>

            </div>

            <div class="mt-1 truncate text-sm font-medium text-slate-700 dark:text-slate-200">
                🚘 ${routeLabel}
            </div>

            <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                ${row.user_request ?? '-'}
            </div>

            <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-400">

                <span>
                    📅
                    ${BookingCar.formatDate(
                        row.booking_date
                    )}
                </span>

            </div>

        </div>

        <div class="shrink-0">

            <span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ${badge.class}">
                ${badge.text}
            </span>

        </div>

    </div>

</div>
`;
            });

            $body.html(html);
        },

        updateInfo() {

            const total =
                BookingCar.state.totalRows;

            const page =
                BookingCar.state.currentPage;

            const size =
                BookingCar.state.pageSize;

            const from =
                total === 0
                    ? 0
                    : (page - 1) * size + 1;

            const to =
                Math.min(
                    page * size,
                    total
                );

            $("#bookingPageInfo")
                .text(
                    `Showing ${from}-${to} of ${total}`
                );
        },

        reload() {
            this.load();
        },
    };

    BookingCar.bindDatalistEvents = () => {

        BookingCar.DataList.init();
    };

    BookingCar.renderBookingList = () => {

        BookingCar.DataList.render(
            BookingCar.state.bookingData || []
        );
    };

})();
