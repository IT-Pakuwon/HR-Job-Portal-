window.BookingCar = window.BookingCar || {};

// =====================================================
// GLOBAL MODAL STATE
// =====================================================

window.currentReasonAction = null;
window.currentBookingDocid = null;

// =====================================================
// CREATE BOOKING
// =====================================================

window.openBookingModal = function () {
    const modal = document.getElementById("createBookingModal");

    const routeBody = document.getElementById("createRouteTableBody");

    modal?.classList.remove("hidden");

    document.body.classList.add("overflow-hidden");

    if (routeBody && !routeBody.querySelector("tr")) {
        if (typeof window.createRouteRow === "function") {
            routeBody.innerHTML = window.createRouteRow(1);
        }
    }
};

window.closeBookingModal = function () {
    const modal = document.getElementById("createBookingModal");

    const form = document.getElementById("bookingCarForm");

    const purposeWrapper = document.getElementById("purposeDescrWrapper");

    const purposeDescr = document.getElementById("purpose_descr");

    modal?.classList.add("hidden");

    document.body.classList.remove("overflow-hidden");

    form?.reset();

    purposeWrapper?.classList.add("hidden");

    if (purposeDescr) {
        purposeDescr.required = false;

        purposeDescr.value = "";
    }

    const routeBody = document.getElementById("createRouteTableBody");

    if (routeBody) {
        routeBody.innerHTML = "";
    }
};

// =====================================================
// DETAIL
// =====================================================

window.closeBookingDetailModal = function () {
    const modal = document.getElementById("viewBookingModal");

    modal?.classList.add("hidden");

    document.body.classList.remove("overflow-hidden");

    BookingCar.currentEid = null;

    BookingCar.currentDocid = null;

    window.history.pushState({}, "", "/bookingcar");
};

// =====================================================
// EDIT
// =====================================================

window.closeEditBookingModal = function () {
    const editModal = document.getElementById("editBookingModal");

    const viewModal = document.getElementById("viewBookingModal");

    const form = document.getElementById("editBookingForm");

    editModal?.classList.add("hidden");

    viewModal?.classList.remove("hidden");

    document.body.classList.add("overflow-hidden");

    form?.reset();
};

// =====================================================
// REASON MODAL
// =====================================================

window.openReasonModal = function (type, docid) {
    window.currentReasonAction = type;

    window.currentBookingDocid = docid;

    const modal = document.getElementById("reasonModal");

    const title = document.getElementById("reasonModalTitle");

    const input = document.getElementById("reasonInput");

    const error = document.getElementById("reasonError");

    if (input) {
        input.value = "";
    }

    error?.classList.add("hidden");

    if (title) {
        title.innerText =
            type === "revise" ? "Revision Reason" : "Reject Reason";
    }

    modal?.classList.remove("hidden");

    modal?.classList.add("flex");

    document.body.classList.add("overflow-hidden");

    setTimeout(() => {
        input?.focus();
    }, 100);
};

window.closeReasonModal = function () {
    const modal = document.getElementById("reasonModal");

    modal?.classList.add("hidden");

    modal?.classList.remove("flex");

    const viewModal = document.getElementById("viewBookingModal");

    const editModal = document.getElementById("editBookingModal");

    if (
        !viewModal?.classList.contains("hidden") ||
        !editModal?.classList.contains("hidden")
    ) {
        document.body.classList.add("overflow-hidden");
    } else {
        document.body.classList.remove("overflow-hidden");
    }

    window.currentReasonAction = null;

    window.currentBookingDocid = null;
};

// =====================================================
// GA PROCESS
// =====================================================

window.closeGaProcessModal = function () {
    const modal = document.getElementById("gaProcessModal");

    const form = document.getElementById("gaProcessForm");

    modal?.classList.add("hidden");

    form?.reset();

    const viewModal = document.getElementById("viewBookingModal");

    if (viewModal && !viewModal.classList.contains("hidden")) {
        document.body.classList.add("overflow-hidden");
    } else {
        document.body.classList.remove("overflow-hidden");
    }
};

// =====================================================
// ESCAPE KEY
// =====================================================

window.handleBookingModalEscape = function (event) {
    if (event.key !== "Escape") {
        return;
    }

    if (!document.getElementById("reasonModal")?.classList.contains("hidden")) {
        closeReasonModal();

        return;
    }

    if (
        !document.getElementById("gaProcessModal")?.classList.contains("hidden")
    ) {
        closeGaProcessModal();

        return;
    }

    if (
        !document
            .getElementById("editBookingModal")
            ?.classList.contains("hidden")
    ) {
        closeEditBookingModal();

        return;
    }

    if (
        !document
            .getElementById("viewBookingModal")
            ?.classList.contains("hidden")
    ) {
        closeBookingDetailModal();

        return;
    }

    if (
        !document
            .getElementById("createBookingModal")
            ?.classList.contains("hidden")
    ) {
        closeBookingModal();
    }
};

// =====================================================
// EVENTS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("closeBookingModal")
        ?.addEventListener("click", window.closeBookingModal);

    document
        .getElementById("cancelBookingBtn")
        ?.addEventListener("click", window.closeBookingModal);

    document
        .getElementById("closeBookingDetailModal")
        ?.addEventListener("click", window.closeBookingDetailModal);

    document
        .getElementById("closeEditBookingModal")
        ?.addEventListener("click", window.closeEditBookingModal);

    document
        .getElementById("cancelEditBookingBtn")
        ?.addEventListener("click", window.closeEditBookingModal);

    document
        .getElementById("cancelReasonBtn")
        ?.addEventListener("click", window.closeReasonModal);

    document
        .getElementById("closeGaProcessModal")
        ?.addEventListener("click", window.closeGaProcessModal);

    document
        .getElementById("cancelGaProcessBtn")
        ?.addEventListener("click", window.closeGaProcessModal);

    document.addEventListener("keydown", window.handleBookingModalEscape);
});
