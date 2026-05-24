(function () {
    "use strict";

    VoucherTaxi.DataList = {
        loading: false,

        init() {
            this.bindEvents();
            this.load();

            VoucherTaxi.log("DataList Initialized");
        },

        bindEvents() {
            $(document).on(
                "click",
                "#closeViewVoucherModal, #closeViewVoucherModalFooter",
                function () {
                    VoucherTaxi.Modal.close("#viewVoucherModal");
                },
            );

            $("#voucherSearch").on(
                "keyup",
                VoucherTaxi.Helper.debounce(() => {
                    VoucherTaxi.state.currentSearch = $("#voucherSearch")
                        .val()
                        .trim();

                    VoucherTaxi.state.currentPage = 1;

                    this.load();
                }, 400),
            );

            $(document).on("click", ".voucher-filter", (e) => {
                $(".voucher-filter").removeClass("active-filter");

                $(e.currentTarget).addClass("active-filter");

                VoucherTaxi.state.currentFilter = $(e.currentTarget).data(
                    "filter",
                );

                VoucherTaxi.state.currentPage = 1;

                this.load();
            });

            $("#prevVoucherPage").on("click", () => {
                if (VoucherTaxi.state.currentPage <= 1) {
                    return;
                }

                VoucherTaxi.state.currentPage--;

                this.load();
            });

            $("#nextVoucherPage").on("click", () => {
                const totalPages = Math.ceil(
                    VoucherTaxi.state.totalRows / VoucherTaxi.state.pageSize,
                );

                if (VoucherTaxi.state.currentPage >= totalPages) {
                    return;
                }

                VoucherTaxi.state.currentPage++;

                this.load();
            });
        },

        load() {
            if (this.loading) {
                return;
            }

            this.loading = true;

            const page = VoucherTaxi.state.currentPage;

            const length = VoucherTaxi.state.pageSize;

            const start = (page - 1) * length;

            let status = VoucherTaxi.state.currentFilter;

            if (status === "ALL") {
                status = "";
            }

            $.ajax({
                url: VoucherTaxi.Route.json(),
                type: "GET",

                data: {
                    draw: 1,
                    start,
                    length,

                    status,

                    search: {
                        value: VoucherTaxi.state.currentSearch,
                    },
                },

                success: (res) => {
                    VoucherTaxi.state.totalRows = res.recordsFiltered || 0;

                    this.render(res.data || []);

                    this.updateInfo();
                },

                error: VoucherTaxi.Helper.ajaxError,

                complete: () => {
                    this.loading = false;
                },
            });
        },

        render(rows) {
            const $body = $("#voucherListBody");

            $("#voucherCount").text(rows.length);

            if (!rows.length) {
                $body.html(`
                    <div class="rounded-xl border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500">
                        No voucher found
                    </div>
                `);

                return;
            }

            let html = "";

            rows.forEach((row) => {
                const badge = VoucherTaxi.Helper.badge(row.status);

                html += `
<div
    class="voucher-item cursor-pointer rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-indigo-300 hover:shadow-sm dark:border-white/10 dark:bg-white/[0.02]"
    data-eid="${row.eid}"
>

    <div class="flex items-start justify-between gap-3">

        <div class="min-w-0 flex-1">

            <div class="flex items-center gap-2">

                <div class="font-semibold text-slate-900 dark:text-white text-[11px]">
                    ${row.docid}
                </div>

            </div>

            <div class="mt-1 truncate text-sm text-slate-700 dark:text-slate-200">
                ${row.origin ?? "-"} → ${row.destination ?? "-"}
            </div>

            <div class="mt-1 text-xs text-slate-500">
                ${row.user_peminta ?? "-"} • ${row.date_used ?? "-"}
            </div>

        </div>

        <span class="shrink-0 rounded-full px-2 py-1 text-[11px] font-medium ${badge.class}">
            ${badge.text}
        </span>

    </div>

</div>
`;
            });

            $body.html(html);
        },

        updateInfo() {
            const total = VoucherTaxi.state.totalRows;

            const page = VoucherTaxi.state.currentPage;

            const size = VoucherTaxi.state.pageSize;

            const from = total === 0 ? 0 : (page - 1) * size + 1;

            const to = Math.min(page * size, total);

            $("#voucherPageInfo").text(`Showing ${from}-${to} of ${total}`);
        },

        reload() {
            this.load();
        },
    };
})();
