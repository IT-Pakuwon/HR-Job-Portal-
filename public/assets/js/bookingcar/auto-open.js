window.BookingCar = window.BookingCar || {};

// =====================================================
// STATE
// =====================================================

BookingCar.autoOpened = false;

// =====================================================
// OPEN ROUTE
// =====================================================

window.executeBookingAutoOpen = function (handler, eid) {
    if (typeof handler !== "function") {
        return;
    }

    handler(eid);
};

// =====================================================
// AUTO OPEN
// =====================================================

window.handleBookingAutoOpen = function () {
    if (BookingCar.autoOpened) {
        return;
    }

    const path = window.location.pathname;

    const routes = [
        {
            regex: /\/showbookingcar\/([^\/]+)$/,

            handler: window.showBookingDetail,
        },

        {
            regex: /\/editbookingcar\/([^\/]+)$/,

            handler: window.openEditBookingModal,
        },

        {
            regex: /\/processbookingcar\/([^\/]+)$/,

            handler: window.openGaProcessModal,
        },
    ];

    for (const route of routes) {
        const match = path.match(route.regex);

        if (!match || !match[1]) {
            continue;
        }

        BookingCar.autoOpened = true;

        const eid = match[1];

        setTimeout(() => {
            window.executeBookingAutoOpen(route.handler, eid);
        }, 300);

        return;
    }
};
