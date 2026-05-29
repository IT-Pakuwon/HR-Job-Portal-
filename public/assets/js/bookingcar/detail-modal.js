(function () {

    "use strict";

    window.BookingCar = window.BookingCar || {};

    BookingCar.DetailModal = {

        currentData: null,

        init() {

            this.bindEvents();

            console.log("BookingCar DetailModal Initialized");

        },

        bindEvents() {

            $(document).on("click", ".booking-item", (e) => {

                const eid = $(e.currentTarget).data("eid");

                if (!eid) {
                    return;
                }

                this.open(eid);

            });

            $("#editBookingBtn").on("click", () => {

                if (!this.currentData) {
                    return;
                }

                this.close();

                if (
                    BookingCar.EditForm &&
                    typeof BookingCar.EditForm.open === "function"
                ) {
                    BookingCar.EditForm.open(this.currentData.eid);
                }

            });

            $("#cancelBookingBtn").on("click", () => {

                if (!this.currentData) {
                    return;
                }

                if (
                    BookingCar.Approval &&
                    typeof BookingCar.Approval.cancel === "function"
                ) {
                    BookingCar.Approval.cancel(this.currentData.eid);
                }

            });

            $("#approveBookingBtn").on("click", () => {

                if (!this.currentData) {
                    return;
                }

                if (
                    BookingCar.Approval &&
                    typeof BookingCar.Approval.approve === "function"
                ) {
                    BookingCar.Approval.approve(this.currentData.docid);
                }

            });

            $("#rejectBookingBtn").on("click", () => {

                if (!this.currentData) {
                    return;
                }

                if (
                    BookingCar.Approval &&
                    typeof BookingCar.Approval.reject === "function"
                ) {
                    BookingCar.Approval.reject(this.currentData.docid);
                }

            });

            $("#reviseBookingBtn").on("click", () => {

                if (!this.currentData) {
                    return;
                }

                if (
                    BookingCar.Approval &&
                    typeof BookingCar.Approval.revise === "function"
                ) {
                    BookingCar.Approval.revise(this.currentData.docid);
                }

            });

        },

        close() {

            $("#viewBookingModal .modal-backdrop")
                .removeClass("opacity-100")
                .addClass("opacity-0");

            $("#viewBookingModal .modal-panel")
                .removeClass("translate-y-0 scale-100 opacity-100")
                .addClass("translate-y-4 scale-[0.98] opacity-0");

            setTimeout(() => {

                $("#viewBookingModal")
                    .removeClass("flex")
                    .addClass("hidden");

            }, 200);

            $("body").removeClass("overflow-hidden");

        },

        open(eid) {

            if (!eid) {
                return;
            }

            window.history.pushState(
                {},
                "",
                `/showbookingcar/${eid}`
            );

            BookingCar.state = BookingCar.state || {};
            BookingCar.state.selectedEid = eid;

            Swal.fire({
                title: "Loading...",
                text: "Loading booking detail",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                },
            });

            $.ajax({

                url: BookingCar.config.routes.detail(eid),

                method: "GET",

                success: (res) => {

                    Swal.close();

                    const data = res.data || res || {};

                    this.currentData = data;

                    this.populate(data);

                    $("#viewBookingModal")
                        .removeClass("hidden")
                        .addClass("flex");

                    $("body").addClass("overflow-hidden");

                    requestAnimationFrame(() => {

                        $("#viewBookingModal .modal-backdrop")
                            .removeClass("opacity-0")
                            .addClass("opacity-100");

                        $("#viewBookingModal .modal-panel")
                            .removeClass("translate-y-4 scale-[0.98] opacity-0")
                            .addClass("translate-y-0 scale-100 opacity-100");

                    });

                    if (
                        BookingCar.Tracking &&
                        typeof BookingCar.Tracking.load === "function"
                    ) {
                        BookingCar.Tracking.load(eid);
                    }

                },

                error: (xhr) => {

                    Swal.close();

                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text:
                            xhr.responseJSON?.message ||
                            "Failed load booking detail",
                    });

                },

            });

        },

        populate(data) {

            BookingCar.state.selectedDocId = data.docid || null;

            $("#view_booking_eid").val(data.eid || "");
            $("#view_booking_docid").val(data.docid || "");

            $("#detailBookingTitle").text(
                data.docid || "Booking Detail"
            );

            $("#view_booking_user").text(
                data.user_name ||
                data.user_request ||
                data.user_peminta ||
                "-"
            );

            $("#view_booking_date").text(
                BookingCar.formatDate(data.booking_date)
            );

            $("#view_booking_cpny").text(
                data.cpny_name ||
                data.cpny_id ||
                "-"
            );

            $("#view_booking_dept").text(
                data.department_name ||
                data.department_id ||
                "-"
            );

            $("#view_booking_passenger").text(
                data.passenger || 0
            );

            $("#view_booking_start").text(
                BookingCar.formatTime(data.start_time)
            );

            $("#view_booking_end").text(
                BookingCar.formatTime(data.end_time)
            );

            $("#view_booking_purpose").html(`
                <div class="space-y-3">

                    <div>

                        <div class="
                            text-[11px]
                            font-bold uppercase
                            tracking-wider
                            text-slate-400
                        ">
                            Purpose
                        </div>

                        <div class="
                            mt-1 text-sm font-semibold
                            text-slate-700
                            dark:text-slate-200
                        ">
                            ${data.purpose_id || "-"}
                        </div>

                    </div>

                    <div>

                        <div class="
                            text-[11px]
                            font-bold uppercase
                            tracking-wider
                            text-slate-400
                        ">
                            Description
                        </div>

                        <div class="
                            mt-1 text-sm leading-relaxed
                            text-slate-600
                            dark:text-slate-300
                        ">
                            ${this.nl2br(data.purpose_descr || "-")}
                        </div>

                    </div>

                </div>
            `);

            $("#view_booking_route_table").html(
                this.renderRoutes(data.details || [])
            );

            this.renderStatus(data.status);

            this.renderPrint(data.hash || data.eid);

            this.renderDriverInfo(data);

            this.renderReviseReason(data);

            // this.renderApprovalFlow(data);

            this.renderActions(data);

        },

        renderStatus(status) {

            const badge = BookingCar.getStatusBadge(status);

            $("#view_booking_status_badge")
                .attr(
                    "class",
                    `inline-flex rounded-lg px-3 py-1 text-xs font-semibold ${badge.class}`
                )
                .text(badge.text);

        },

        renderPrint(hash) {

            if (!hash) {
                return;
            }

            $("#printBookingBtn").attr(
                "href",
                BookingCar.config.routes.print(hash)
            );

            $("#printBookingBtnFooter").attr(
                "href",
                BookingCar.config.routes.print(hash)
            );

        },

        renderRoutes(routes) {

            routes = Array.isArray(routes)
                ? routes
                : [];

            $("#view_total_route_badge").text(
                `${routes.length} Route`
            );

            if (!routes.length) {

                return `
                    <tr>
                        <td colspan="3"
                            class="
                                px-4 py-8 text-center
                                text-sm text-slate-500
                            ">
                            No route available
                        </td>
                    </tr>
                `;

            }

            let html = "";

            routes.forEach((item, index) => {

                html += `
                    <tr class="
                        border-b border-slate-100
                        dark:border-white/5
                    ">

                        <td class="
                            px-4 py-3 text-sm
                            text-slate-500
                        ">
                            ${index + 1}
                        </td>

                        <td class="
                            px-4 py-3 text-sm
                            font-medium text-slate-700
                            dark:text-slate-200
                        ">
                            ${item.origin || "-"}
                        </td>

                        <td class="
                            px-4 py-3 text-sm
                            text-slate-600
                            dark:text-slate-300
                        ">
                            ${item.destination || "-"}
                        </td>

                    </tr>
                `;

            });

            return html;

        },

        renderDriverInfo(data) {

            const hasDriver =
                data.driver ||
                data.driver_name ||
                data.handphone ||
                data.no_polisi;

            if (!hasDriver) {

                $("#driverInfoWrapper")
                    .addClass("hidden");

                return;

            }

            $("#driverInfoWrapper")
                .removeClass("hidden");

            $("#view_booking_driver").text(
                data.driver ||
                data.driver_name ||
                "-"
            );

            $("#view_booking_handphone").text(
                data.handphone || "-"
            );

            $("#view_booking_nopol").text(
                data.no_polisi ||
                data.nopol ||
                "-"
            );

        },

        renderReviseReason(data) {

            const status = String(data.status || "")
                .toUpperCase();

            const reason =
                data.revise_reason ||
                data.reason ||
                "";

            if (status !== "D" || !reason) {

                $("#reviseReasonWrapper")
                    .addClass("hidden");

                $("#view_revise_reason")
                    .html("");

                return;

            }

            $("#reviseReasonWrapper")
                .removeClass("hidden");

            $("#view_revise_reason")
                .html(this.nl2br(reason));

        },

        timelineStatus(status) {

            switch (status) {

                case "A":
                    return `
                        <span class="
                            inline-flex rounded-lg
                            bg-emerald-100
                            px-4 py-2
                            text-xs font-semibold
                            text-emerald-700
                        ">
                            Approved
                        </span>
                    `;

                case "R":
                    return `
                        <span class="
                            inline-flex rounded-lg
                            bg-red-100
                            px-4 py-2
                            text-xs font-semibold
                            text-red-700
                        ">
                            Rejected
                        </span>
                    `;

                case "D":
                    return `
                        <span class="
                            inline-flex rounded-lg
                            bg-amber-100
                            px-4 py-2
                            text-xs font-semibold
                            text-amber-700
                        ">
                            Revised
                        </span>
                    `;

                case "P":
                    return `
                        <span class="
                            inline-flex rounded-lg
                            bg-blue-100
                            px-4 py-2
                            text-xs font-semibold
                            text-blue-700
                        ">
                            Waiting Approval
                        </span>
                    `;

                default:
                    return `
                        <span class="
                            inline-flex rounded-lg
                            bg-slate-100
                            px-4 py-2
                            text-xs font-semibold
                            text-slate-600
                        ">
                            Submitted
                        </span>
                    `;

            }

        },

        badgeColor(status) {

            switch (status) {

                case "A":
                    return `
                        bg-emerald-100
                        text-emerald-600
                    `;

                case "R":
                    return `
                        bg-red-100
                        text-red-600
                    `;

                case "D":
                    return `
                        bg-amber-100
                        text-amber-600
                    `;

                case "P":
                    return `
                        bg-blue-100
                        text-blue-600
                    `;

                default:
                    return `
                        bg-violet-100
                        text-violet-600
                    `;

            }

        },

        timelineIcon(status) {

            switch (status) {

                case "A":
                    return `
                        <i class="
                            fa-solid fa-check
                            text-sm
                        "></i>
                    `;

                case "R":
                    return `
                        <i class="
                            fa-solid fa-xmark
                            text-sm
                        "></i>
                    `;

                case "D":
                    return `
                        <i class="
                            fa-solid fa-rotate-left
                            text-sm
                        "></i>
                    `;

                case "P":
                    return `
                        <i class="
                            fa-solid fa-clock
                            text-sm
                        "></i>
                    `;

                default:
                    return `
                        <i class="
                            fa-solid fa-paper-plane
                            text-sm
                        "></i>
                    `;

            }

        },

        renderActions(data) {

            const canEdit = Boolean(data.can_edit);
            const canCancel = Boolean(data.can_cancel);
            const canApprove = Boolean(data.can_approve);
            const canReject = Boolean(data.can_reject);
            const canRevise = Boolean(data.can_revise);

            $("#editBookingBtn")
                .toggleClass("hidden", !canEdit);

            $("#cancelBookingBtn")
                .toggleClass("hidden", !canCancel);

            $("#approveBookingBtn")
                .toggleClass("hidden", !canApprove);

            $("#rejectBookingBtn")
                .toggleClass("hidden", !canReject);

            $("#reviseBookingBtn")
                .toggleClass("hidden", !canRevise);

            $("#bookingApprovalActionsWrapper")
                .toggleClass(
                    "hidden",
                    !(canApprove || canReject || canRevise)
                );

        },

        nl2br(text) {

            return String(text || "")
                .replace(/\n/g, "<br>");

        },

    };

})();
