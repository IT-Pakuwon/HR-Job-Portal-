(function () {

    'use strict';

    window.BookingCar =
        window.BookingCar || {};

    /*
    |--------------------------------------------------------------------------
    | ROUTES
    |--------------------------------------------------------------------------
    */
    BookingCar.config =
        BookingCar.config || {};

    BookingCar.config.routes = {

        /*
        |--------------------------------------------------------------------------
        | MAIN
        |--------------------------------------------------------------------------
        */
        json:
            '/bookingcar/json',

        store:
            '/bookingcar/store',

        /*
        |--------------------------------------------------------------------------
        | DETAIL
        |--------------------------------------------------------------------------
        */
        detail: (hash) =>
            `/bookingcar/detail/${hash}`,

        tracking: (hash) =>
            `/bookingcar/tracking/${hash}`,

        find: (hash) =>
            `/bookingcar/find/${hash}`,

        /*
        |--------------------------------------------------------------------------
        | UPDATE
        |--------------------------------------------------------------------------
        */
        update: (hash) =>
            `/bookingcar/update/${hash}`,

        /*
        |--------------------------------------------------------------------------
        | WORKFLOW
        |--------------------------------------------------------------------------
        */
        cancel: (docid) =>
            `/bookingcar/cancel/${docid}`,

        approve: (docid) =>
            `/bookingcar/approve/${docid}`,

        reject: (docid) =>
            `/bookingcar/reject/${docid}`,

        revise: (docid) =>
            `/bookingcar/revise/${docid}`,

        /*
        |--------------------------------------------------------------------------
        | PROCESS
        |--------------------------------------------------------------------------
        */
        process: (hash) =>
            `/bookingcar/process/${hash}`,

        /*
        |--------------------------------------------------------------------------
        | PRINT
        |--------------------------------------------------------------------------
        */
        print: (hash) =>
            `/bookingcar/print/${hash}`,

        /*
        |--------------------------------------------------------------------------
        | AUTO OPEN
        |--------------------------------------------------------------------------
        */
        showModal: (eid) =>
            `/showbookingcar/${eid}`,

        editModal: (eid) =>
            `/editbookingcar/${eid}`,

        processModal: (eid) =>
            `/processbookingcar/${eid}`,
    };

    /*
    |--------------------------------------------------------------------------
    | LEGACY COMPATIBILITY
    |--------------------------------------------------------------------------
    */
    BookingCar.Route = {

        detail: (hash) =>
            BookingCar.config.routes.detail(hash),

        tracking: (hash) =>
            BookingCar.config.routes.tracking(hash),

        update: (hash) =>
            BookingCar.config.routes.update(hash),

        cancel: (docid) =>
            BookingCar.config.routes.cancel(docid),

        approve: (docid) =>
            BookingCar.config.routes.approve(docid),

        reject: (docid) =>
            BookingCar.config.routes.reject(docid),

        revise: (docid) =>
            BookingCar.config.routes.revise(docid),

        process: (hash) =>
            BookingCar.config.routes.process(hash),

        print: (hash) =>
            BookingCar.config.routes.print(hash),
    };

    /*
    |--------------------------------------------------------------------------
    | GLOBAL AJAX WRAPPER
    |--------------------------------------------------------------------------
    */
    BookingCar.ajax = ({
        url,
        method = 'GET',
        data = {},
        showLoading = false,
        loadingText = 'Loading...',
    }) => {

        return new Promise(
            (resolve, reject) => {

                if (showLoading) {

                    Swal.fire({
                        title: loadingText,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                }

                $.ajax({

                    url,
                    method,
                    data,

                    success: (response) => {

                        if (showLoading) {
                            Swal.close();
                        }

                        resolve(response);
                    },

                    error: (xhr) => {

                        if (showLoading) {
                            Swal.close();
                        }

                        let message =
                            'Something went wrong';

                        if (
                            xhr.responseJSON?.message
                        ) {

                            message =
                                xhr.responseJSON.message;
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | VALIDATION ERROR
                        |--------------------------------------------------------------------------
                        */
                        if (
                            xhr.status === 422 &&
                            xhr.responseJSON?.errors
                        ) {

                            const errors =
                                Object.values(
                                    xhr.responseJSON.errors
                                )
                                    .flat()
                                    .join('<br>');

                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: errors,
                            });

                        } else {

                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: message,
                            });
                        }

                        reject(xhr);
                    }
                });
            }
        );
    };

    /*
    |--------------------------------------------------------------------------
    | FETCH LIST
    |--------------------------------------------------------------------------
    */
    BookingCar.fetchBookingData =
        async () => {

            try {

                const response =
                    await BookingCar.ajax({

                        url:
                            BookingCar.config.routes.json,

                        method: 'GET',
                    });

                BookingCar.setBookingData(
                    response.data || []
                );

                return response;

            } catch (error) {

                console.error(
                    'Fetch booking failed:',
                    error
                );

                return [];
            }
        };

    /*
    |--------------------------------------------------------------------------
    | FETCH DETAIL
    |--------------------------------------------------------------------------
    */
    BookingCar.fetchBookingDetail =
        async (hash) => {

            try {

                return await BookingCar.ajax({

                    url:
                        BookingCar.config.routes.detail(
                            hash
                        ),

                    method: 'GET',

                    showLoading: true,

                    loadingText:
                        'Loading booking detail...',
                });

            } catch (error) {

                console.error(error);

                return null;
            }
        };

    /*
    |--------------------------------------------------------------------------
    | FETCH TRACKING
    |--------------------------------------------------------------------------
    */
    BookingCar.fetchBookingTracking =
        async (hash) => {

            try {

                return await BookingCar.ajax({

                    url:
                        BookingCar.config.routes.tracking(
                            hash
                        ),

                    method: 'GET',
                });

            } catch (error) {

                console.error(error);

                return [];
            }
        };

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    BookingCar.submitCreateBooking =
        async (formData) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.store,

                method: 'POST',

                data: formData,

                showLoading: true,

                loadingText:
                    'Submitting booking...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    BookingCar.submitEditBooking =
        async (hash, formData) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.update(
                        hash
                    ),

                method: 'POST',

                data: formData,

                showLoading: true,

                loadingText:
                    'Saving changes...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    BookingCar.submitApproveBooking =
        async (docid) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.approve(
                        docid
                    ),

                method: 'POST',

                data: {
                    _token:
                        $('meta[name="csrf-token"]')
                            .attr('content'),
                },

                showLoading: true,

                loadingText:
                    'Approving booking...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    BookingCar.submitRejectBooking =
        async ({
            docid,
            reason,
        }) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.reject(
                        docid
                    ),

                method: 'POST',

                data: {
                    reason,

                    _token:
                        $('meta[name="csrf-token"]')
                            .attr('content'),
                },

                showLoading: true,

                loadingText:
                    'Rejecting booking...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | REVISE
    |--------------------------------------------------------------------------
    */
    BookingCar.submitReviseBooking =
        async ({
            docid,
            reason,
        }) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.revise(
                        docid
                    ),

                method: 'POST',

                data: {
                    reason,

                    _token:
                        $('meta[name="csrf-token"]')
                            .attr('content'),
                },

                showLoading: true,

                loadingText:
                    'Sending revision...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | CANCEL
    |--------------------------------------------------------------------------
    */
    BookingCar.submitCancelBooking =
        async (hash) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.cancel(
                        hash
                    ),

                method: 'POST',

                data: {
                    _token:
                        $('meta[name="csrf-token"]')
                            .attr('content'),
                },

                showLoading: true,

                loadingText:
                    'Cancelling booking...',
            });
        };

    /*
    |--------------------------------------------------------------------------
    | PROCESS
    |--------------------------------------------------------------------------
    */
    BookingCar.submitGaProcess =
        async ({
            hash,
            formData,
        }) => {

            return await BookingCar.ajax({

                url:
                    BookingCar.config.routes.process(
                        hash
                    ),

                method: 'POST',

                data: formData,

                showLoading: true,

                loadingText:
                    'Processing booking...',
            });
        };


})();
