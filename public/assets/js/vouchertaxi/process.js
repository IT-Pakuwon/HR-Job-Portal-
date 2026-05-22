(function () {
    'use strict';

    VoucherTaxi.Process = {

        currentDocId: null,

        init() {

            this.bindOpen();
            this.bindExpenseOwner();
            this.bindBudgetFormatting();
            this.bindSubmit();

            VoucherTaxi.log(
                'Process Initialized'
            );
        },

        bindOpen() {

            $(document).on(
                'click',
                '#openProcessVoucherBtn',
                () => {

                    const data =
                        VoucherTaxi.DetailModal.currentData;

                    if (!data) {
                        return;
                    }

                    this.open(data);
                }
            );
        },

        open(data) {

            this.currentDocId =
                data.docid;

            $('#process_docid')
                .val(data.docid);

            $('#process_docno')
                .text(data.docid || '-');

            $('#process_requester')
                .text(
                    data.user_name ||
                    data.user_peminta ||
                    '-'
                );

            $('#process_date')
                .text(
                    data.date_used || '-'
                );

            $('#process_company')
                .text(
                    data.cpny_id || '-'
                );

            $('#process_department')
                .text(
                    data.department_id || '-'
                );

            $('#process_trip')
                .text(
                    data.type_trip || '-'
                );

            $('#process_route')
                .text(
                    `${data.origin || '-'} → ${data.destination || '-'}`
                );

            $('#process_purpose')
                .text(
                    data.purpose || '-'
                );

            $('#process_budget')
                .text(
                    VoucherTaxi.Helper.moneyWithPrefix(
                        data.actual_budget || 0
                    )
                );

            const badge =
                VoucherTaxi.Helper.badge(
                    data.status
                );

            $('#process_status')
                .attr(
                    'class',
                    `rounded-full px-3 py-1 text-xs font-semibold ${badge.class}`
                )
                .text(
                    badge.text
                );

            $('#actual_budget_display')
                .val('');

            $('#actual_budget')
                .val('');

            $('#changeExpenseOwner')
                .prop('checked', false);

            $('#expenseOwnerSection')
                .addClass('hidden');

            VoucherTaxi.Modal.open(
                '#processVoucherModal'
            );
        },

        bindExpenseOwner() {

            $('#changeExpenseOwner').on(
                'change',
                function () {

                    $('#expenseOwnerSection')
                        .toggleClass(
                            'hidden',
                            !this.checked
                        );
                }
            );
        },

        bindBudgetFormatting() {

            $('#actual_budget_display').on(
                'input',
                function () {

                    const raw =
                        VoucherTaxi.Helper.parseMoney(
                            $(this).val()
                        );

                    $('#actual_budget')
                        .val(raw);

                    $(this).val(
                        VoucherTaxi.Helper.money(
                            raw
                        )
                    );
                }
            );
        },

        bindSubmit() {

            $('#processVoucherForm').on(
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

            const actualBudget =
                $('#actual_budget').val();

            if (!actualBudget) {

                VoucherTaxi.Helper.warning(
                    'Actual budget is required.'
                );

                return;
            }

            const confirm =
                await VoucherTaxi.Helper.confirm(
                    'Process Voucher?',
                    'Actual expense will be saved.',
                    'Save Process'
                );

            if (!confirm.isConfirmed) {
                return;
            }

            VoucherTaxi.Helper.loading(
                'Saving process...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.process(
                        this.currentDocId
                    ),

                method: 'POST',

                headers:
                    VoucherTaxi.Helper.headers(),

                data:
                    $('#processVoucherForm')
                        .serialize(),

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    VoucherTaxi.Modal.close(
                        '#processVoucherModal'
                    );

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.DataList.reload();

                    VoucherTaxi.Calendar.reload();

                    VoucherTaxi.Helper.success(
                        res.message ||
                        'Voucher processed successfully.'
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
