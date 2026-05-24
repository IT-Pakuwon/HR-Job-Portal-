window.BookingCar = window.BookingCar || {};

// =====================================================
// TRACKING
// =====================================================

window.loadBookingTracking = async function (eid) {

    const wrapper =
        document.getElementById(
            "bookingApprovalFlow"
        );

    if (!wrapper) {
        return;
    }

    wrapper.innerHTML = `
        <div class="py-8 text-center text-sm text-gray-400">
            Loading approval workflow...
        </div>
    `;

    try {

        const result =
            await BookingCar.fetchJson(
                `/bookingcar/tracking/${eid}`
            );

        const items =
            result.steps || [];

        if (!items.length) {

            wrapper.innerHTML = `
                <div class="py-8 text-center text-sm text-gray-400">
                    No approval history
                </div>
            `;

            return;
        }

        wrapper.innerHTML =
            items.map((item, index) => {

                let badgeClass =
                    "bg-blue-100 text-blue-700";

                if (
                    item.status === "A" ||
                    item.status === "C"
                ) {

                    badgeClass =
                        "bg-green-100 text-green-700";
                }

                if (
                    item.status === "R"
                ) {

                    badgeClass =
                        "bg-red-100 text-red-700";
                }

                if (
                    item.status === "D"
                ) {

                    badgeClass =
                        "bg-yellow-100 text-yellow-700";
                }

                return `
                    <div class="relative pl-8 pb-6">

                        ${
                            index < items.length - 1
                                ? `
                                    <div
                                        class="
                                            absolute
                                            left-3
                                            top-6
                                            bottom-0
                                            w-px
                                            bg-gray-200
                                        "
                                    ></div>
                                `
                                : ""
                        }

                        <div
                            class="
                                absolute
                                left-0
                                top-1
                                h-6
                                w-6
                                rounded-lg
                                border
                                border-gray-300
                                bg-white
                            "
                        ></div>

                        <div
                            class="
                                rounded-lg
                                border
                                border-gray-100
                                bg-white
                                p-4
                            "
                        >

                            <div
                                class="
                                    flex
                                    items-start
                                    justify-between
                                    gap-3
                                "
                            >

                                <div>

                                    <div
                                        class="
                                            text-sm
                                            font-semibold
                                            text-gray-900
                                        "
                                    >
                                        ${BookingCar.escapeHtml(
                                            item.title || "-"
                                        )}
                                    </div>

                                    ${
                                        item.by
                                            ? `
                                                <div
                                                    class="
                                                        mt-1
                                                        text-xs
                                                        text-gray-500
                                                    "
                                                >
                                                    By :
                                                    ${BookingCar.escapeHtml(
                                                        item.by
                                                    )}
                                                </div>
                                            `
                                            : ""
                                    }

                                    ${
                                        item.at
                                            ? `
                                                <div
                                                    class="
                                                        text-xs
                                                        text-gray-400
                                                    "
                                                >
                                                    ${BookingCar.escapeHtml(
                                                        item.at
                                                    )}
                                                </div>
                                            `
                                            : ""
                                    }

                                </div>

                                <span
                                    class="
                                        rounded-lg
                                        px-2
                                        py-1
                                        text-[10px]
                                        font-semibold
                                        ${badgeClass}
                                    "
                                >
                                    ${BookingCar.escapeHtml(
                                        item.status_label ||
                                        item.status ||
                                        "-"
                                    )}
                                </span>

                            </div>

                            ${
                                item.comment
                                    ? `
                                        <div
                                            class="
                                                mt-3
                                                rounded-lg
                                                bg-gray-50
                                                p-3
                                                text-xs
                                                text-gray-600
                                            "
                                        >
                                            ${BookingCar.escapeHtml(
                                                item.comment
                                            )}
                                        </div>
                                    `
                                    : ""
                            }

                        </div>

                    </div>
                `;
            }).join("");

    } catch (err) {

        console.error(err);

        wrapper.innerHTML = `
            <div class="py-8 text-center text-sm text-red-500">
                Failed to load approval workflow
            </div>
        `;
    }
};
// =====================================================
// APPROVE
// =====================================================

window.approveBooking = async function (docid) {
    const confirm = await BookingCar.confirm(
        "Approve Booking",
        "Continue approval?",
        "Approve",
    );

    if (!confirm.isConfirmed) {
        return;
    }

    try {
        const result = await BookingCar.postJson(
            `/bookingcar/approve/${docid}`,
        );

        await BookingCar.success(result.message);

        if (BookingCar.currentEid) {
            showBookingDetail(BookingCar.currentEid);
        }

        fetchBookingList?.();
    } catch (err) {
        BookingCar.error(err.message);
    }
};

// =====================================================
// OPEN REVISE
// =====================================================

window.openReviseBooking = function (docid) {
    window.openReasonModal?.("revise", docid);
};

// =====================================================
// OPEN REJECT
// =====================================================

window.openRejectBooking = function (docid) {
    window.openReasonModal?.("reject", docid);
};

// =====================================================
// SUBMIT REASON
// =====================================================

window.submitReasonAction = async function () {
    const reasonInput = document.getElementById("reasonInput");

    const errorText = document.getElementById("reasonError");

    if (!reasonInput || !window.currentBookingDocid) {
        return;
    }

    const reason = reasonInput.value.trim();

    if (!reason) {
        errorText?.classList.remove("hidden");

        return;
    }

    try {
        let url = "";

        if (window.currentReasonAction === "revise") {
            url = `/bookingcar/revise/${window.currentBookingDocid}`;
        } else {
            url = `/bookingcar/reject/${window.currentBookingDocid}`;
        }

        const result = await BookingCar.postJson(url, {
            reason,
        });

        await BookingCar.success(result.message);

        closeReasonModal?.();

        if (BookingCar.currentEid) {
            showBookingDetail(BookingCar.currentEid);
        }

        fetchBookingList?.();
    } catch (err) {
        BookingCar.error(err.message);
    }
};

// =====================================================
// EVENTS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("submitReasonBtn")
        ?.addEventListener("click", window.submitReasonAction);
});
