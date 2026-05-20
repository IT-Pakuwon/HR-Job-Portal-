window.BookingCar = window.BookingCar || {};

// =====================================================
// TOGGLE LIST PANEL
// =====================================================

window.initializeBookingLayout = function () {
    const toggleListBtn = document.getElementById("toggleList");

    const bookingListPanel = document.getElementById("bookingListPanel");

    const calendarWrapper = document.getElementById("calendarWrapper");

    if (!toggleListBtn || !bookingListPanel || !calendarWrapper) {
        return;
    }

    let listHidden = false;

    toggleListBtn.addEventListener("click", function () {
        listHidden = !listHidden;

        if (listHidden) {
            bookingListPanel.classList.add("hidden");

            calendarWrapper.classList.remove("lg:col-span-8");

            calendarWrapper.classList.add("lg:col-span-12");

            this.innerHTML = `
                        <span>📋</span>
                        <span>Show Listing</span>
                    `;
        } else {
            bookingListPanel.classList.remove("hidden");

            calendarWrapper.classList.remove("lg:col-span-12");

            calendarWrapper.classList.add("lg:col-span-8");

            this.innerHTML = `
                        <span>📋</span>
                        <span>Listing</span>
                    `;
        }

        setTimeout(() => {
            window.refreshCalendarSize?.();
        }, 200);
    });
};

// =====================================================
// INITIAL FILTERS
// =====================================================

window.initializeBookingFilters = function () {
    [
        window.filterUserRequest,
        window.filterEditUserRequest,
        window.togglePurposeDescription,
        window.toggleEditPurposeDescription,
    ].forEach((fn) => {
        if (typeof fn === "function") {
            fn();
        }
    });
};

// =====================================================
// INITIAL DATA
// =====================================================

window.initializeBookingData = async function () {
    if (typeof window.fetchBookingList !== "function") {
        console.warn("fetchBookingList not found");

        return;
    }

    try {
        await window.fetchBookingList();

        window.handleBookingAutoOpen?.();
    } catch (error) {
        console.error(error);
    }
};

// =====================================================
// INIT
// =====================================================

document.addEventListener("DOMContentLoaded", async function () {
    window.initializeBookingLayout();

    window.initializeBookingFilters();

    await window.initializeBookingData();
});
