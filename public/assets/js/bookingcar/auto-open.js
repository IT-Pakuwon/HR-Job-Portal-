(function () {

    'use strict';

    window.BookingCar =
        window.BookingCar || {};

    BookingCar.AutoOpen = {

        init() {

            const path =
                window.location.pathname;

            this.handleDetail(path);

            this.handleEdit(path);

            this.handleProcess(path);
        },

        handleDetail(path) {

            const match =
                path.match(
                    /\/showbookingcar\/([^\/]+)/i
                );

            if (
                !match ||
                !match[1]
            ) {
                return;
            }

            const eid =
                decodeURIComponent(
                    match[1]
                );

            setTimeout(() => {

                if (
                    BookingCar.DetailModal &&
                    typeof BookingCar.DetailModal.open ===
                    'function'
                ) {

                    BookingCar.DetailModal.open(
                        eid
                    );

                } else {

                    console.error(
                        '[BookingCar] DetailModal.open not found'
                    );
                }

            }, 300);
        },

        handleEdit(path) {

            const match =
                path.match(
                    /\/editbookingcar\/([^\/]+)/i
                );

            if (
                !match ||
                !match[1]
            ) {
                return;
            }

            const eid =
                decodeURIComponent(
                    match[1]
                );

            setTimeout(() => {

                if (
                    BookingCar.EditForm &&
                    typeof BookingCar.EditForm.open ===
                    'function'
                ) {

                    BookingCar.EditForm.open(
                        eid
                    );

                } else {

                    console.error(
                        '[BookingCar] EditForm.open not found'
                    );
                }

            }, 300);
        },

        handleProcess(path) {

            const match =
                path.match(
                    /\/processbookingcar\/([^\/]+)/i
                );

            if (
                !match ||
                !match[1]
            ) {
                return;
            }

            const eid =
                decodeURIComponent(
                    match[1]
                );

            setTimeout(() => {

                if (
                    BookingCar.Process &&
                    typeof BookingCar.Process.open ===
                    'function'
                ) {

                    BookingCar.Process.open(
                        eid
                    );

                } else {

                    console.error(
                        '[BookingCar] Process.open not found'
                    );
                }

            }, 300);
        },

        resetUrl() {

            window.history.pushState(
                {},
                '',
                '/bookingcar'
            );
        }
    };

    /*
    |--------------------------------------------------------------------------
    | INIT
    |--------------------------------------------------------------------------
    */
    $(document).ready(function () {

        BookingCar.AutoOpen.init();

    });

    /*
    |--------------------------------------------------------------------------
    | HANDLE BROWSER BACK/FORWARD
    |--------------------------------------------------------------------------
    */
    window.addEventListener(
        'popstate',
        function () {

            const path =
                window.location.pathname;

            if (
                path === '/bookingcar' ||
                path === '/booking-car'
            ) {

                if (
                    BookingCar.Modal &&
                    typeof BookingCar.Modal.close ===
                    'function'
                ) {

                    BookingCar.Modal.close(
                        '#viewBookingModal'
                    );

                    BookingCar.Modal.close(
                        '#editBookingModal'
                    );

                    BookingCar.Modal.close(
                        '#processBookingModal'
                    );
                }
            }
        }
    );

})();
