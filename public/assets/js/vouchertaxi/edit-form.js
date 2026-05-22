(function () {
    'use strict';

    VoucherTaxi.EditForm = {

        currentEid: null,
        currentDocId: null,

        init() {

            this.bindSubmit();

            VoucherTaxi.log(
                'EditForm Initialized'
            );
        },

        open(eid) {

            this.currentEid = eid;

            VoucherTaxi.Helper.loading(
                'Loading voucher...'
            );

            $.ajax({

                url: VoucherTaxi.Route.find(eid),

                method: 'GET',

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    const data =
                        res.data || res;

                    this.populate(data);

                    VoucherTaxi.Modal.open(
                        '#editVoucherTaxiModal'
                    );
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(
                        xhr
                    );
                }
            });
        },

        populate(data) {

            this.currentDocId =
                data.docid;

            $('#edit_docid')
                .val(data.docid);

            $('#edit_cpny_id')
                .val(data.cpny_id);

            $('#edit_department_id')
                .val(data.department_id);

            $('#edit_user_peminta')
                .val(
                    data.user_name ??
                    data.user_peminta
                );

            $('#edit_date_used')
                .val(data.date_used);

            $('#edit_origin')
                .val(data.origin);

            $('#edit_destination')
                .val(data.destination);

            $('#edit_purpose')
                .val(data.purpose);

            $('#edit_cpny_id_expense')
                .val(data.cpny_id_expense);

            $('#edit_user_topup')
                .val(data.user_topup);

            $('input[name="type_trip"]')
                .prop('checked', false);

            $(
                `#editVoucherTaxiModal input[name="type_trip"][value="${data.type_trip}"]`
            ).prop('checked', true);

            $('#editMetaUser')
                .text(
                    data.created_by ?? ''
                );

            $('#editMetaDate')
                .text(
                    data.created_at ?? ''
                );

            $('#edit_cpny_id_expense')
                .val(data.cpny_id_expense)
                .trigger('change');

            $('#edit_user_topup')
                .val(data.user_topup)
                .trigger('change');

            this.renderStatus(
                data.status
            );

            this.renderReviseReason(
                data
            );
        },

        renderStatus(status) {

            const badge =
                VoucherTaxi.Helper.badge(
                    status
                );

            $('#editStatusBadge')
                .attr(
                    'class',
                    `rounded-full px-3 py-1 text-xs font-medium ${badge.class}`
                )
                .text(
                    badge.text
                );
        },

        renderReviseReason(data) {

            const reason =
                data.revise_reason ?? '';

            if (!reason) {

                $('#editReviseReasonWrapper')
                    .addClass('hidden');

                return;
            }

            $('#editReviseReasonWrapper')
                .removeClass('hidden');

            $('#edit_revise_reason')
                .html(
                    VoucherTaxi.Helper.nl2br(
                        reason
                    )
                );
        },

        bindSubmit() {

            $('#editVoucherTaxiForm').on(
                'submit',
                (e) => {

                    e.preventDefault();

                    this.submit();
                }
            );
        },

        async submit() {

            if (!this.currentDocId) {
                return;
            }

            const confirm =
                await VoucherTaxi.Helper.confirm(
                    'Save Changes?',
                    'Voucher will be resubmitted for approval.',
                    'Save'
                );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Saving changes...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.update(
                        this.currentDocId
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                data:
                    $('#editVoucherTaxiForm')
                        .serialize(),

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#editVoucherTaxiModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Voucher updated successfully.'
                    );
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Helper.ajaxError(
                        xhr
                    );
                }
            });
        }
    };

})();
