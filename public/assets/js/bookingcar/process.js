window.BookingCar = window.BookingCar || {};

// =====================================================
// OPEN PROCESS MODAL
// =====================================================

window.openGaProcessModal = async function (eid) {
    try {
        const result = await BookingCar.fetchJson(`/bookingcar/detail/${eid}`);

        const d = result.data;

        if (!d) {
            throw new Error("Booking not found");
        }

        BookingCar.currentEid = eid;

        BookingCar.currentDocid = d.docid;

        document
            .getElementById("ga_process_docid")
            ?.setAttribute("value", d.docid || "");

        document.getElementById("ga_driver").value = d.driver || "";

        document.getElementById("ga_handphone").value = d.handphone || "";

        document.getElementById("ga_no_polisi").value = d.no_polisi || "";

        document.getElementById("gaProcessModal")?.classList.remove("hidden");

        document.body.classList.add("overflow-hidden");
    } catch (err) {
        console.error(err);

        BookingCar.error(err.message || "Failed load booking");
    }
};

// =====================================================
// SUBMIT PROCESS
// =====================================================

window.submitGaProcess = async function (e) {
    e.preventDefault();

    const form = document.getElementById("gaProcessForm");

    if (!form) {
        return;
    }

    const submitBtn = document.getElementById("submitGaProcessBtn");

    try {
        BookingCar.setLoading(submitBtn, "Processing...");

        const docid = BookingCar.currentDocid;

        if (!docid) {
            throw new Error("Booking document not found");
        }

        const formData = new FormData(form);

        const result = await BookingCar.postForm(
            `/bookingcar/process/${docid}`,
            formData,
        );

        await BookingCar.success(result.message);

        closeGaProcessModal?.();

        fetchBookingList?.();

        if (BookingCar.currentEid && typeof showBookingDetail === "function") {
            setTimeout(() => {
                showBookingDetail(BookingCar.currentEid);
            }, 300);
        }
    } catch (err) {
        BookingCar.error(err.message);
    } finally {
        BookingCar.clearLoading(submitBtn);
    }
};

// =====================================================
// EVENTS
// =====================================================

document.addEventListener("DOMContentLoaded", function () {
    document
        .getElementById("gaProcessForm")
        ?.addEventListener("submit", window.submitGaProcess);
});
