(function () {
    'use strict';

    VoucherTaxi.RequestForm = {

        form: null,

        init() {

            this.form = $('#voucherTaxiForm');

            if (!this.form.length) {
                return;
            }

            this.bindSubmit();
            this.bindTopupFilter();

            VoucherTaxi.log('RequestForm Initialized');
        },

        bindSubmit() {

            this.form.on('submit', (e) => {

                e.preventDefault();

                this.submit();
            });
        },

        async submit() {

            const confirm = await VoucherTaxi.Helper.confirm(
                'Submit Voucher Taxi?',
                'Please make sure all information is correct.',
                'Submit'
            );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Submitting voucher...'
            );

            $.ajax({

                url: VoucherTaxi.Route.store(),

                method: 'POST',

                headers: VoucherTaxi.Helper.headers(),

                data: this.form.serialize(),

                success: (response) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#createVoucherModal'
                    );

                    this.reset();

                    VoucherTaxi.DataList.reload();

                    if (VoucherTaxi.Calendar) {
                        VoucherTaxi.Calendar.reload();
                    }

                    VoucherTaxi.Helper.success(
                        response.message ||
                        'Voucher submitted successfully.'
                    );
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(xhr);
                }
            });
        },

        reset() {

            this.form.trigger('reset');

            $('#user_topup')
                .prop('selectedIndex', 0)
                .trigger('change');
        },

        bindTopupFilter() {

            $('#department_id').on(
                'change',
                function () {

                    const dept =
                        $(this).val();

                    $('#user_topup option').each(
                        function () {

                            const optionDept =
                                $(this).data('dept');

                            if (
                                !dept ||
                                !optionDept ||
                                optionDept === dept
                            ) {

                                $(this).show();
                                return;
                            }

                            $(this).hide();
                        }
                    );

                    $('#user_topup')
                        .val('');
                }
            );
        }
    };

})();
