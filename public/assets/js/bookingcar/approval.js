(function () {

    "use strict";

    window.BookingCar = window.BookingCar || {};

    BookingCar.Approval = {

        approve(docid) {

            if (!docid) {
                return;
            }

            Swal.fire({

                title: "Approve Booking?",
                text: "This booking will be approved",
                icon: "question",

                showCancelButton: true,

                confirmButtonText: "Yes, Approve",
                cancelButtonText: "Cancel",

                reverseButtons: true,

                customClass: {
                    confirmButton: `
                        inline-flex items-center rounded-lg
                        bg-emerald-600 px-4 py-2
                        text-sm font-semibold text-white
                    `,
                    cancelButton: `
                        inline-flex items-center rounded-lg
                        bg-slate-200 px-4 py-2
                        text-sm font-semibold text-slate-700
                    `,
                },

                buttonsStyling: false,

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: "Processing...",
                    text: "Approving booking",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({

                    url: BookingCar.config.routes.approve(docid),

                    method: "POST",

                    data: {
                        _token: BookingCar.csrf,
                    },

                    success: (res) => {

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text:
                                res.message ||
                                "Booking approved successfully",
                        });

                        BookingCar.Approval.refreshAfterAction();

                    },

                    error: (xhr) => {

                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text:
                                xhr.responseJSON?.message ||
                                "Failed approve booking",
                        });

                    },

                });

            });

        },

        reject(docid) {

            if (!docid) {
                return;
            }

            Swal.fire({

                title: "Reject Booking",

                input: "textarea",

                inputLabel: "Reject Reason",

                inputPlaceholder: "Input reject reason...",

                inputAttributes: {
                    autocapitalize: "off",
                },

                showCancelButton: true,

                confirmButtonText: "Reject",
                cancelButtonText: "Cancel",

                reverseButtons: true,

                customClass: {
                    confirmButton: `
                        inline-flex items-center rounded-lg
                        bg-red-600 px-4 py-2
                        text-sm font-semibold text-white
                    `,
                    cancelButton: `
                        inline-flex items-center rounded-lg
                        bg-slate-200 px-4 py-2
                        text-sm font-semibold text-slate-700
                    `,
                },

                buttonsStyling: false,

                inputValidator: (value) => {

                    if (!value) {
                        return "Reject reason is required";
                    }

                },

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: "Processing...",
                    text: "Rejecting booking",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({

                    url: BookingCar.config.routes.reject(docid),

                    method: "POST",

                    data: {
                        _token: BookingCar.csrf,
                        reason: result.value,
                    },

                    success: (res) => {

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text:
                                res.message ||
                                "Booking rejected successfully",
                        });

                        BookingCar.Approval.refreshAfterAction();

                    },

                    error: (xhr) => {

                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text:
                                xhr.responseJSON?.message ||
                                "Failed reject booking",
                        });

                    },

                });

            });

        },

        revise(docid) {

            if (!docid) {
                return;
            }

            Swal.fire({

                title: "Revise Booking",

                input: "textarea",

                inputLabel: "Revise Reason",

                inputPlaceholder: "Input revise reason...",

                inputAttributes: {
                    autocapitalize: "off",
                },

                showCancelButton: true,

                confirmButtonText: "Send Revise",
                cancelButtonText: "Cancel",

                reverseButtons: true,

                customClass: {
                    confirmButton: `
                        inline-flex items-center rounded-lg
                        bg-amber-500 px-4 py-2
                        text-sm font-semibold text-white
                    `,
                    cancelButton: `
                        inline-flex items-center rounded-lg
                        bg-slate-200 px-4 py-2
                        text-sm font-semibold text-slate-700
                    `,
                },

                buttonsStyling: false,

                inputValidator: (value) => {

                    if (!value) {
                        return "Revise reason is required";
                    }

                },

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: "Processing...",
                    text: "Sending revise",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({

                    url: BookingCar.config.routes.revise(docid),

                    method: "POST",

                    data: {
                        _token: BookingCar.csrf,
                        reason: result.value,
                    },

                    success: (res) => {

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text:
                                res.message ||
                                "Booking revised successfully",
                        });

                        BookingCar.Approval.refreshAfterAction();

                    },

                    error: (xhr) => {

                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text:
                                xhr.responseJSON?.message ||
                                "Failed revise booking",
                        });

                    },

                });

            });

        },

        cancel(eid) {

            if (!eid) {
                return;
            }

            Swal.fire({

                title: "Cancel Booking?",
                text: "This booking will be cancelled",
                icon: "warning",

                showCancelButton: true,

                confirmButtonText: "Yes, Cancel",
                cancelButtonText: "Back",

                reverseButtons: true,

                customClass: {
                    confirmButton: `
                        inline-flex items-center rounded-lg
                        bg-red-600 px-4 py-2
                        text-sm font-semibold text-white
                    `,
                    cancelButton: `
                        inline-flex items-center rounded-lg
                        bg-slate-200 px-4 py-2
                        text-sm font-semibold text-slate-700
                    `,
                },

                buttonsStyling: false,

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                Swal.fire({
                    title: "Processing...",
                    text: "Cancelling booking",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading(),
                });

                $.ajax({

                    url: BookingCar.config.routes.cancel(eid),

                    method: "POST",

                    data: {
                        _token: BookingCar.csrf,
                    },

                    success: (res) => {

                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text:
                                res.message ||
                                "Booking cancelled successfully",
                        });

                        BookingCar.DetailModal.close();

                        BookingCar.Approval.reloadList();

                    },

                    error: (xhr) => {

                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text:
                                xhr.responseJSON?.message ||
                                "Failed cancel booking",
                        });

                    },

                });

            });

        },

        refreshAfterAction() {

            const eid =
                BookingCar.state?.selectedEid;

            BookingCar.DetailModal.close();

            if (eid) {

                setTimeout(() => {

                    BookingCar.DetailModal.open(eid);

                }, 300);

            }

            BookingCar.Approval.reloadList();

        },

        reloadList() {

            if (
                BookingCar.DataList &&
                typeof BookingCar.DataList.reload === "function"
            ) {
                BookingCar.DataList.reload();
            }

            if (
                BookingCar.Calendar &&
                typeof BookingCar.Calendar.refetch === "function"
            ) {
                BookingCar.Calendar.refetch();
            }

        },

    };

})();
