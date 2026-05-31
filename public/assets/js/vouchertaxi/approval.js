// ============================================================
// approval.js — Voucher Taxi
// Approve, reject, revise actions
// ============================================================

const VoucherTaxiApproval = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isProcessing: false,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VoucherTaxiApproval.bindApprove();
        VoucherTaxiApproval.bindReject();
        VoucherTaxiApproval.bindRevise();
    },

    // --------------------------------------------------------
    // APPROVE
    // --------------------------------------------------------
    bindApprove() {
        document.getElementById('approveBtn')
            ?.addEventListener('click', () => VoucherTaxiApproval.approve());
    },

    async approve() {
        if (VoucherTaxiApproval.state.isProcessing) return;

        const docid = document.getElementById('view_docid')?.value;
        if (!docid) return;

        const result = await VoucherTaxi.confirm({
            title:       'Approve Voucher?',
            icon:        'question',
            confirmText: 'Yes, Approve',
            confirmColor: '#10b981',
        });
        if (!result.isConfirmed) return;

        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.approve(docid), {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                VoucherTaxi.toast('success', res.message ?? 'Voucher approved');
                VoucherTaxiDetailModal.refresh();
                VoucherTaxiDataList.reload();
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Approve failed');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiApproval.state.isProcessing = false;
        });
    },

    // --------------------------------------------------------
    // REJECT
    // --------------------------------------------------------
    bindReject() {
        document.getElementById('rejectBtn')
            ?.addEventListener('click', () => VoucherTaxiApproval.promptReject());
    },

    async promptReject() {
        const result = await VoucherTaxi.prompt({
            title:         'Reject Voucher',
            label:         'Reason for rejection',
            placeholder:   'Explain why you are rejecting this request...',
            confirmText:   'Reject',
            confirmColor:  '#dc2626',
            validationMsg: 'Please provide a rejection reason.',
        });
        if (!result.isConfirmed) return;
        VoucherTaxiApproval.submitReject(result.value.trim());
    },

    submitReject(reason) {
        if (VoucherTaxiApproval.state.isProcessing) return;

        const docid = document.getElementById('view_docid')?.value;
        if (!docid) return;

        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.reject(docid), {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify({ reason }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                VoucherTaxi.toast('success', res.message ?? 'Voucher rejected');
                VoucherTaxiDetailModal.refresh();
                VoucherTaxiDataList.reload();
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Reject failed');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiApproval.state.isProcessing = false;
        });
    },

    // --------------------------------------------------------
    // REVISE
    // --------------------------------------------------------
    bindRevise() {
        document.getElementById('reviseBtn')
            ?.addEventListener('click', () => VoucherTaxiApproval.promptRevise());
    },

    async promptRevise() {
        const result = await VoucherTaxi.prompt({
            title:         'Request Revision',
            label:         'Revision notes',
            placeholder:   'What changes are needed?',
            confirmText:   'Send Revision',
            confirmColor:  '#d97706',
            validationMsg: 'Please provide revision notes.',
        });
        if (!result.isConfirmed) return;
        VoucherTaxiApproval.submitRevise(result.value.trim());
    },

    submitRevise(reason) {
        if (VoucherTaxiApproval.state.isProcessing) return;

        const docid = document.getElementById('view_docid')?.value;
        if (!docid) return;

        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.revise(docid), {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify({ reason }),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                VoucherTaxi.toast('success', res.message ?? 'Revision requested');
                VoucherTaxiDetailModal.refresh();
                VoucherTaxiDataList.reload();
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Revise failed');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiApproval.state.isProcessing = false;
        });
    },
};
