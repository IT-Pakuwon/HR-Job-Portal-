(function () {
    'use strict';

    VoucherTaxi.DetailModal = {

        currentData: null,

        init() {

            this.bindEvents();

            VoucherTaxi.log(
                'DetailModal Initialized'
            );
        },

        bindEvents() {

            $(document).on(
                'click',
                '.voucher-item',
                (e) => {

                    const eid =
                        $(e.currentTarget)
                            .data('eid');

                    if (!eid) return;

                    this.open(eid);
                }
            );

            $('#openEditFromViewBtn').on(
                'click',
                () => {

                    if (
                        !this.currentData
                    ) {
                        return;
                    }

                    VoucherTaxi.Modal.close(
                        '#viewVoucherModal'
                    );

                    VoucherTaxi.EditForm.open(
                        this.currentData.eid
                    );
                }
            );

            $('#cancelVoucherBtn').on(
                'click',
                () => {

                    if (
                        !this.currentData
                    ) {
                        return;
                    }

                    VoucherTaxi.Approval.cancel(
                        this.currentData.docid
                    );
                }
            );
        },

        open(eid) {

            window.history.pushState(
                {},
                '',
                `/showvouchertaxi/${eid}`
            );


            VoucherTaxi.state.selectedEid =
                eid;

            VoucherTaxi.Helper.loading(
                'Loading voucher...'
            );

            $.ajax({

                url:
                    VoucherTaxi.Route.detail(
                        eid
                    ),

                method: 'GET',

                success: (res) => {

                    VoucherTaxi.Helper.closeLoading();

                    const data =
                        res.data || res;

                    this.currentData =
                        data;

                    this.populate(
                        data
                    );

                    VoucherTaxi.Modal.open(
                        '#viewVoucherModal'
                    );

                    if (
                        VoucherTaxi.Tracking
                    ) {
                        VoucherTaxi.Tracking.load(
                            eid
                        );
                    }
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

            VoucherTaxi.state.selectedDocId =
                data.docid;

            $("#detailDocIdTitle").text(
                data.docid || "Voucher Taxi Detail"
            );

            $('#view_user')
                .text(
                    data.user_name ??
                    data.user_peminta ??
                    '-'
                );

            $('#view_date')
                .text(
                    data.date_used ?? '-'
                );

            $('#view_type_trip')
                .text(
                    data.type_trip ?? '-'
                );

            $('#view_origin')
                .text(
                    data.origin ?? '-'
                );

            $('#view_destination')
                .text(
                    data.destination ?? '-'
                );

            $('#view_cpny')
                .text(
                    data.cpny_id ?? '-'
                );

            $('#view_dept')
                .text(
                    data.department_id ?? '-'
                );

            $('#view_route')
                .html(
                    `${data.origin ?? '-'} → ${data.destination ?? '-'}`
                );

            $('#view_purpose').html(`
                <div class="space-y-2">
                    <div>
                        <span class="font-semibold">
                            Purpose :
                        </span>
                        ${data.purpose_name ?? data.purpose_id ?? '-'}
                    </div>

                    <div>
                        <span class="font-semibold">
                            Description :
                        </span>
                        <div class="mt-1">
                            ${VoucherTaxi.Helper.nl2br(
                                data.purpose_descr ?? '-'
                            )}
                        </div>
                    </div>
                </div>
            `);

            this.renderStatus(
                data.status
            );

            this.renderPrint(
                data.hash ??
                data.eid
            );

            this.renderActualBudget(
                data
            );

            this.renderReviseReason(
                data
            );

            this.renderActions(
                data
            );
        },

        renderStatus(status) {

            const badge =
                VoucherTaxi.Helper.badge(
                    status
                );

            $('#view_status_badge')
                .attr(
                    'class',
                    `rounded-full px-3 py-1 text-xs font-medium ${badge.class}`
                )
                .text(
                    badge.text
                );
        },

        renderPrint(hash) {

            if (!hash) return;

            $('#printVoucherBtn')
                .attr(
                    'href',
                    VoucherTaxi.Route.print(
                        hash
                    )
                );
        },

        renderActualBudget(data) {

            if (
                !data.actual_budget
            ) {

                $('#actualExpenseWrapper')
                    .addClass('hidden');

                return;
            }

            $('#actualExpenseWrapper')
                .removeClass('hidden');

            $('#view_actual_budget')
                .text(
                    VoucherTaxi.Helper
                        .moneyWithPrefix(
                            data.actual_budget
                        )
                );
        },

        renderReviseReason(data) {

            const reason =
                data.revise_reason ??
                '';

            if (!reason) {

                $('#reviseReasonWrapper')
                    .addClass('hidden');

                return;
            }

            $('#reviseReasonWrapper')
                .removeClass('hidden');

            $('#view_revise_reason')
                .html(
                    VoucherTaxi.Helper.nl2br(
                        reason
                    )
                );
        },

        renderActions(data) {

            const status =
                data.status;

            const canEdit =
                data.can_edit ?? false;

            const canCancel =
                data.can_cancel ?? false;

            const canApprove =
                data.can_approve ?? false;

            const canReject =
                data.can_reject ?? false;

            const canRevise =
                data.can_revise ?? false;

            const canProcess =
                data.can_process ?? false;

            $('#openEditFromViewBtn')
                .toggleClass(
                    'hidden',
                    !canEdit
                );

            $('#cancelVoucherBtn')
                .toggleClass(
                    'hidden',
                    !canCancel
                );

            $('#approveBtn')
                .toggleClass(
                    'hidden',
                    !canApprove
                );

            $('#rejectBtn')
                .toggleClass(
                    'hidden',
                    !canReject
                );

            $('#reviseBtn')
                .toggleClass(
                    'hidden',
                    !canRevise
                );

            $('#approvalActions')
                .toggleClass(
                    'hidden',
                    !(
                        canApprove ||
                        canReject ||
                        canRevise
                    )
                );

            const actions = [];

            if (
                status === 'C' &&
                canProcess
            ) {

                actions.push(`
                    <button
                        type="button"
                        id="openProcessVoucherBtn"
                        class="rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-500 flex-1"
                    >
                        Process
                    </button>
                `);
            }

            $('#viewActions')
                .html(
                    actions.join('')
                );
        }
    };

})();
