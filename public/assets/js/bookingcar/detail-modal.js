window.BookingCar = window.BookingCar || {};

BookingCar.currentEid = null;
BookingCar.currentDocid = null;

// =====================================================
// SHOW DETAIL
// =====================================================

window.showBookingDetail = async function (eid) {

    try {

        BookingCar.currentEid = eid;

        const result =
            await BookingCar.fetchJson(
                `/bookingcar/detail/${eid}`
            );

        const d =
            result.data;

        if (!d) {
            throw new Error(
                "Booking not found"
            );
        }

        BookingCar.currentDocid =
            d.docid;

        renderBookingHeader(d);
        renderBookingRoute(d);
        renderBookingPurpose(d);
        renderBookingDriver(d);
        renderBookingRevise(d);
        renderBookingAction(d);

        document
            .getElementById(
                "printBookingBtn"
            ).href =
            `/bookingcar/print/${eid}`;

        document
            .getElementById(
                "viewBookingModal"
            )
            ?.classList.remove(
                "hidden"
            );

        document.body.classList.add(
            "overflow-hidden"
        );

        if (
            typeof loadBookingTracking ===
            "function"
        ) {

            await loadBookingTracking(
                eid
            );
        }

        if (
            window.location.pathname !==
            `/showbookingcar/${eid}`
        ) {

            window.history.pushState(
                {},
                "",
                `/showbookingcar/${eid}`
            );
        }

    } catch (err) {

        console.error(err);

        BookingCar.error(
            err.message ||
            "Failed load booking detail"
        );
    }
};

// =====================================================
// HEADER
// =====================================================

window.renderBookingHeader =
    function (d) {

        document.getElementById(
            "view_booking_user"
        ).innerText =
            d.user_request ||
            d.user_peminta ||
            "-";

        document.getElementById(
            "view_booking_docid"
        ).innerText =
            d.docid || "-";

        document.getElementById(
            "view_booking_status_badge"
        ).innerHTML =
            BookingCar.statusHtml(
                d.status
            );

        document.getElementById(
            "view_booking_date"
        ).innerText =
            d.booking_date || "-";

        document.getElementById(
            "view_booking_passenger"
        ).innerText =
            d.passenger || "-";

        document.getElementById(
            "view_booking_start"
        ).innerText =
            BookingCar.timeOnly(
                d.start_time
            );

        document.getElementById(
            "view_booking_end"
        ).innerText =
            BookingCar.timeOnly(
                d.end_time
            );

        document.getElementById(
            "view_booking_cpny"
        ).innerText =
            d.cpny_id || "-";

        document.getElementById(
            "view_booking_dept"
        ).innerText =
            d.department_id || "-";
    };

// =====================================================
// ROUTE
// =====================================================

window.renderBookingRoute =
    function (d) {

        const table =
            document.getElementById(
                "view_booking_route_table"
            );

        const total =
            document.getElementById(
                "view_total_route"
            );

        if (!table) {
            return;
        }

        table.innerHTML = "";

        const routes =
            d.routes || [];

        total.innerText =
            `${routes.length} Route`;

        if (!routes.length) {

            table.innerHTML = `
                <tr>
                    <td colspan="3"
                        class="px-4 py-6 text-center text-sm text-gray-400">
                        No route data
                    </td>
                </tr>
            `;

            return;
        }

        routes.forEach(
            (route, index) => {

                table.innerHTML += `
                    <tr>

                        <td class="px-4 py-3 text-sm text-gray-500">
                            ${index + 1}
                        </td>

                        <td class="px-4 py-3 text-sm font-medium text-gray-700">
                            ${BookingCar.escapeHtml(
                                route.origin || "-"
                            )}
                        </td>

                        <td class="px-4 py-3 text-sm font-medium text-gray-700">
                            ${BookingCar.escapeHtml(
                                route.destination || "-"
                            )}
                        </td>

                    </tr>
                `;
            }
        );
    };

// =====================================================
// PURPOSE
// =====================================================

window.renderBookingPurpose =
    function (d) {

        document.getElementById(
            "view_booking_purpose"
        ).innerText =
            d.purpose_descr ||
            d.purpose_id ||
            "-";
    };

// =====================================================
// DRIVER
// =====================================================

window.renderBookingDriver =
    function (d) {

        const wrapper =
            document.getElementById(
                "driverInfoWrapper"
            );

        if (
            d.driver ||
            d.handphone ||
            d.no_polisi
        ) {

            wrapper?.classList.remove(
                "hidden"
            );

            document.getElementById(
                "view_booking_driver"
            ).innerText =
                d.driver || "-";

            document.getElementById(
                "view_booking_handphone"
            ).innerText =
                d.handphone || "-";

            document.getElementById(
                "view_booking_nopol"
            ).innerText =
                d.no_polisi || "-";

        } else {

            wrapper?.classList.add(
                "hidden"
            );
        }
    };

// =====================================================
// REVISE
// =====================================================

window.renderBookingRevise =
    function (d) {

        const wrapper =
            document.getElementById(
                "bookingReviseWrapper"
            );

        const reason =
            document.getElementById(
                "view_booking_revise_reason"
            );

        wrapper?.classList.add(
            "hidden"
        );

        if (reason) {
            reason.innerHTML = "";
        }

        if (
            d.status === "D" &&
            d.revise_reason
        ) {

            wrapper?.classList.remove(
                "hidden"
            );

            reason.innerText =
                d.revise_reason;
        }
    };

// =====================================================
// ACTION BUTTON
// =====================================================

window.renderBookingAction =
    function (d) {

        const editBtn =
            document.getElementById(
                "editBookingBtn"
            );

        const cancelBtn =
            document.getElementById(
                "cancelBookingBtn"
            );

        const approval =
            document.getElementById(
                "bookingApprovalActions"
            );

        editBtn?.classList.add(
            "hidden"
        );

        cancelBtn?.classList.add(
            "hidden"
        );

        approval?.classList.add(
            "hidden"
        );

        editBtn.onclick = null;
        cancelBtn.onclick = null;

        const approveBtn =
            document.getElementById(
                "approveBookingBtn"
            );

        const reviseBtn =
            document.getElementById(
                "reviseBookingBtn"
            );

        const rejectBtn =
            document.getElementById(
                "rejectBookingBtn"
            );

        approveBtn.onclick = null;
        reviseBtn.onclick = null;
        rejectBtn.onclick = null;

        // edit button
        if (
            d.status === "D"
        ) {

            editBtn?.classList.remove(
                "hidden"
            );

            editBtn.onclick =
                function () {

                    if (
                        typeof openEditBookingModal ===
                        "function"
                    ) {

                        openEditBookingModal(
                            d.eid
                        );
                    }
                };

            cancelBtn?.classList.remove(
                "hidden"
            );
        }

        // approver button
        if (
            d.can_approve
        ) {

            approval?.classList.remove(
                "hidden"
            );

            approveBtn.onclick =
                () => approveBooking(
                    d.docid
                );

            reviseBtn.onclick =
                () => openReviseBooking(
                    d.docid
                );

            rejectBtn.onclick =
                () => openRejectBooking(
                    d.docid
                );
        }
    };
