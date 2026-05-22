(function () {
    'use strict';

    VoucherTaxi.Approval = {

        init() {

            this.bindApprove();
            this.bindReject();
            this.bindRevise();

            VoucherTaxi.log(
                'Approval Initialized'
            );
        },

        bindApprove() {

            $('#approveBtn').on(
                'click',
                () => {

                    const docid =
                        VoucherTaxi.state.selectedDocId;

                    if (!docid) {
                        return;
                    }

                    this.approve(docid);
                }
            );
        },

        bindReject() {

            $('#rejectBtn').on(
                'click',
                () => {

                    const docid =
                        VoucherTaxi.state.selectedDocId;

                    if (!docid) {
                        return;
                    }

                    this.reject(docid);
                }
            );
        },

        bindRevise() {

            $('#reviseBtn').on(
                'click',
                () => {

                    const docid =
                        VoucherTaxi.state.selectedDocId;

                    if (!docid) {
                        return;
                    }

                    this.revise(docid);
                }
            );
        },

        async approve(docid) {

            const confirm =
                await VoucherTaxi.Helper.confirm(
                    'Approve Voucher?',
                    'This voucher will continue to the next approval step.',
                    'Approve'
                );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Approving voucher...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.approve(
                        docid
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Voucher approved.'
                    );
                },

                error:
                    VoucherTaxi.Helper.ajaxError
            });
        },

        async reject(docid) {

            const result =
                await Swal.fire({

                    title:
                        'Reject Voucher',

                    input:
                        'textarea',

                    inputLabel:
                        'Reject Reason',

                    inputPlaceholder:
                        'Enter reject reason...',

                    inputAttributes: {
                        rows: 4
                    },

                    showCancelButton: true,

                    confirmButtonText:
                        'Reject',

                    confirmButtonColor:
                        '#dc2626',

                    inputValidator:
                        value => {

                            if (!value) {
                                return 'Reject reason is required';
                            }
                        }
                });

            if (!result.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Rejecting voucher...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.reject(
                        docid
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                data: {
                    reason:
                        result.value
                },

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Voucher rejected.'
                    );
                },

                error:
                    VoucherTaxi.Helper.ajaxError
            });
        },

        async revise(docid) {

            const result =
                await Swal.fire({

                    title:
                        'Request Revision',

                    input:
                        'textarea',

                    inputLabel:
                        'Revision Reason',

                    inputPlaceholder:
                        'Enter revision reason...',

                    inputAttributes: {
                        rows: 4
                    },

                    showCancelButton: true,

                    confirmButtonText:
                        'Request Revision',

                    confirmButtonColor:
                        '#eab308',

                    inputValidator:
                        value => {

                            if (!value) {
                                return 'Revision reason is required';
                            }
                        }
                });

            if (!result.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Sending revision...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.revise(
                        docid
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                data: {
                    reason:
                        result.value
                },

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Revision requested.'
                    );
                },

                error:
                    VoucherTaxi.Helper.ajaxError
            });
        },

        async cancel(docid) {

            const confirm =
                await VoucherTaxi.Helper.confirm(
                    'Cancel Voucher?',
                    'This request will be cancelled permanently.',
                    'Cancel Voucher'
                );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Cancelling voucher...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.cancel(
                        docid
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Voucher cancelled.'
                    );
                },

                error:
                    VoucherTaxi.Helper.ajaxError
            });
        }
    };

})();
