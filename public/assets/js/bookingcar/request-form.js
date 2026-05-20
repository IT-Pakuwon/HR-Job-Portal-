window.BookingCar = window.BookingCar || {};

// =====================================================
// PASSENGER FILTER
// =====================================================

window.filterUserRequest = function () {
    const deptSelect = document.getElementById("booking_department_id");

    const userSelect = document.getElementById("booking_user_request");

    if (!deptSelect || !userSelect) {
        return;
    }

    const selectedDept = deptSelect.value;

    const originalOptions = BookingCar.userRequestOptions || [];

    userSelect.innerHTML = '<option value="">Select passenger</option>';

    originalOptions.forEach((option) => {
        if (!option.value) {
            return;
        }

        const dept = option.getAttribute("data-dept");

        if (!selectedDept || dept === selectedDept) {
            userSelect.appendChild(option.cloneNode(true));
        }
    });
};

// =====================================================
// EDIT PASSENGER FILTER
// =====================================================

window.filterEditUserRequest = function () {
    const deptSelect = document.getElementById("edit_department_id");

    const userSelect = document.getElementById("edit_user_request");

    if (!deptSelect || !userSelect) {
        return;
    }

    const selectedDept = deptSelect.value;

    const currentValue = userSelect.value;

    const originalOptions = BookingCar.editUserRequestOptions || [];

    userSelect.innerHTML = '<option value="">Select passenger</option>';

    originalOptions.forEach((option) => {
        if (!option.value) {
            return;
        }

        const dept = option.getAttribute("data-dept");

        if (!selectedDept || dept === selectedDept) {
            const clone = option.cloneNode(true);

            if (clone.value === currentValue) {
                clone.selected = true;
            }

            userSelect.appendChild(clone);
        }
    });
};

// =====================================================
// PURPOSE CREATE
// =====================================================

window.togglePurposeDescription = function () {
    const purpose = document.getElementById("purpose_id");

    const wrapper = document.getElementById("purposeDescrWrapper");

    const descr = document.getElementById("purpose_descr");

    if (!purpose || !wrapper || !descr) {
        return;
    }

    if (purpose.value === "OTHER") {
        wrapper.classList.remove("hidden");

        descr.required = true;

        descr.value = "";
    } else {
        wrapper.classList.add("hidden");

        descr.required = false;

        descr.value = purpose.value;
    }
};

// =====================================================
// PURPOSE EDIT
// =====================================================

window.toggleEditPurposeDescription = function () {
    const purpose = document.getElementById("edit_purpose_id");

    const wrapper = document.getElementById("editPurposeDescrWrapper");

    const descr = document.getElementById("edit_purpose_descr");

    if (!purpose || !wrapper || !descr) {
        return;
    }

    if (purpose.value === "OTHER") {
        wrapper.classList.remove("hidden");

        descr.required = true;
    } else {
        wrapper.classList.add("hidden");

        descr.required = false;

        descr.value = purpose.value;
    }
};

// =====================================================
// CREATE SUBMIT
// =====================================================

window.submitCreateBooking = async function (e) {
    e.preventDefault();

    const form = document.getElementById("bookingCarForm");

    if (!form) {
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');

    try {
        BookingCar.setLoading(submitBtn, "Submitting...");

        const purpose = document.getElementById("purpose_id");

        const descr = document.getElementById("purpose_descr");

        if (purpose && descr && purpose.value !== "OTHER") {
            descr.value = purpose.value;
        }

        const formData = new FormData(form);

        const result = await BookingCar.postForm("/bookingcar/store", formData);

        await BookingCar.success(result.message);

        window.closeBookingModal?.();

        fetchBookingList?.();
    } catch (err) {
        BookingCar.error(err.message);
    } finally {
        BookingCar.clearLoading(submitBtn);
    }
};

// =====================================================
// EDIT SUBMIT
// =====================================================

window.submitEditBooking = async function (e) {
    e.preventDefault();

    const form = document.getElementById("editBookingForm");

    if (!form) {
        return;
    }

    const submitBtn = document.getElementById("saveEditBookingBtn");

    try {
        BookingCar.setLoading(submitBtn, "Saving...");

        const purpose = document.getElementById("edit_purpose_id");

        const descr = document.getElementById("edit_purpose_descr");

        if (purpose && descr && purpose.value !== "OTHER") {
            descr.value = purpose.value;
        }

        const docidElement = document.getElementById("edit_booking_docid");

        if (!docidElement) {
            throw new Error("Booking document not found");
        }

        const formData = new FormData(form);

        const requester = document.getElementById("edit_user_peminta");

        if (requester) {
            formData.append("user_peminta", requester.value);
        }

        const result = await BookingCar.postForm(
            `/bookingcar/update/${docidElement.value}`,
            formData,
        );

        await BookingCar.success(result.message);

        window.closeEditBookingModal?.();

        fetchBookingList?.();

        const eid = document.getElementById("edit_booking_eid")?.value;

        if (eid && typeof showBookingDetail === "function") {
            setTimeout(() => {
                showBookingDetail(eid);
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
    const bookingUserRequest = document.getElementById("booking_user_request");

    if (bookingUserRequest) {
        BookingCar.userRequestOptions = Array.from(bookingUserRequest.options);
    }

    const editUserRequest = document.getElementById("edit_user_request");

    if (editUserRequest) {
        BookingCar.editUserRequestOptions = Array.from(editUserRequest.options);
    }

    document
        .getElementById("booking_department_id")
        ?.addEventListener("change", window.filterUserRequest);

    document
        .getElementById("edit_department_id")
        ?.addEventListener("change", window.filterEditUserRequest);

    document
        .getElementById("purpose_id")
        ?.addEventListener("change", window.togglePurposeDescription);

    document
        .getElementById("edit_purpose_id")
        ?.addEventListener("change", window.toggleEditPurposeDescription);

    document
        .getElementById("bookingCarForm")
        ?.addEventListener("submit", window.submitCreateBooking);

    document
        .getElementById("editBookingForm")
        ?.addEventListener("submit", window.submitEditBooking);

    window.filterUserRequest?.();
    window.filterEditUserRequest?.();

    window.togglePurposeDescription?.();
    window.toggleEditPurposeDescription?.();
});
