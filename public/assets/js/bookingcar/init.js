window.BookingCar = window.BookingCar || {};

document.addEventListener('DOMContentLoaded', async () => {

    try {

        if (typeof BookingCar.bindModalEvents === 'function') {
            BookingCar.bindModalEvents();
        }

        if (typeof BookingCar.bindDatalistEvents === 'function') {
            BookingCar.bindDatalistEvents();
        }

        if (typeof BookingCar.bindCalendarResize === 'function') {
            BookingCar.bindCalendarResize();
        }

        if (typeof BookingCar.initializeCalendar === 'function') {
            BookingCar.initializeCalendar();
        }

        if (typeof BookingCar.fetchBookingData === 'function') {
            await BookingCar.fetchBookingData();
        }

        if (typeof BookingCar.renderBookingList === 'function') {
            BookingCar.renderBookingList();
        }

        if (typeof BookingCar.refreshCalendarEvents === 'function') {
            BookingCar.refreshCalendarEvents();
        }

        const createRouteTable =
            BookingCar.el?.createRouteTableBody;

        if (
            createRouteTable &&
            !createRouteTable.querySelector('tr')
        ) {
        }

        if (
            typeof flatpickr !== 'undefined'
        ) {

            document.querySelectorAll(
                'input[type="date"]'
            ).forEach(el => {

                flatpickr(el, {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                });
            });

            document.querySelectorAll(
                'input[type="time"]'
            ).forEach(el => {

                flatpickr(el, {
                    enableTime: true,
                    noCalendar: true,
                    dateFormat: 'H:i',
                    time_24hr: true,
                });
            });
        }

        if (
            typeof $ !== 'undefined' &&
            typeof $.fn.select2 !== 'undefined'
        ) {

            $('select').select2({
                width: '100%',
            });
        }

    } catch (error) {

        console.error(
            'BookingCar initialization failed:',
            error
        );
    }
});

window.addEventListener('resize', () => {

    if (
        BookingCar.state?.calendar
    ) {

        BookingCar.state.calendar.updateSize();
    }
});

window.addEventListener('focus', async () => {

    try {

        if (
            typeof BookingCar.fetchBookingData === 'function'
        ) {

            await BookingCar.fetchBookingData();
        }

        if (
            typeof BookingCar.renderBookingList === 'function'
        ) {

            BookingCar.renderBookingList();
        }

        if (
            typeof BookingCar.refreshCalendarEvents === 'function'
        ) {

            BookingCar.refreshCalendarEvents();
        }

    } catch (error) {

        console.error(
            'BookingCar refresh failed:',
            error
        );
    }
});
