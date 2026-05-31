// ============================================================
// approval.js — Voucher Taxi
// Approval workflow: approve, reject, revise with notifications
// ============================================================

const VoucherTaxiApproval = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isProcessing: false,
        currentAction: null,
        currentDocid: null,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        // Listeners already attached in detail-modal.js
        // This module provides the action handlers
    },

    // --------------------------------------------------------
    // APPROVE VOUCHER TAXI
    // --------------------------------------------------------
    approve(docid = null) {
        docid = docid ?? VoucherTaxi.state.currentDocid;

        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        VoucherTaxi.confirm({
            title: 'Approve Voucher Taxi?',
            text: 'Are you sure you want to approve this taxi voucher request?',
            icon: 'question',
            confirmText: 'Yes, Approve',
            confirmColor: '#10b981',
            cancelText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                VoucherTaxiApproval._submitApprove(docid);
            }
        });
    },

    async _submitApprove(docid) {
        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxiApproval.state.currentAction = 'approve';
        VoucherTaxiApproval.state.currentDocid = docid;

        try {
            VoucherTaxi.toast('info', 'Processing approval...');

            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.approve(docid),
                {
                    method: 'POST',
                }
            );

            if (response.success) {
                VoucherTaxi.toast('success', response.message ?? 'Voucher Taxi approved successfully');

                // Update detail if visible
                setTimeout(() => {
                    VoucherTaxiDetailModal.refresh();
                    VoucherTaxiDatalist.refresh();
                }, 500);

            } else {
                VoucherTaxi.toast('error', response.message ?? 'Failed to approve voucher');
            }

        } catch (err) {
            console.error('Approve error:', err);

            let message = 'Failed to approve voucher';

            if (err.status === 403) {
                message = 'You do not have permission to approve this voucher';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            VoucherTaxi.toast('error', message);

        } finally {
            VoucherTaxiApproval.state.isProcessing = false;
            VoucherTaxiApproval.state.currentAction = null;
            VoucherTaxiApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // REJECT VOUCHER TAXI
    // --------------------------------------------------------
    reject(docid = null) {
        docid = docid ?? VoucherTaxi.state.currentDocid;

        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        VoucherTaxi.prompt({
            title: 'Reject Voucher Taxi',
            label: 'Rejection Reason',
            placeholder: 'Please explain why you are rejecting this voucher request...',
            input: 'textarea',
            confirmText: 'Reject',
            confirmColor: '#ef4444',
            cancelText: 'Cancel',
            validationMsg: 'Please provide a reason for rejection.',
        }).then((result) => {
            if (result.isConfirmed) {
                VoucherTaxiApproval._submitReject(docid, result.value);
            }
        });
    },

    async _submitReject(docid, reason) {
        if (VoucherTaxiHelper.isEmpty(reason)) {
            VoucherTaxi.toast('warning', 'Please provide a rejection reason');
            return;
        }

        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxiApproval.state.currentAction = 'reject';
        VoucherTaxiApproval.state.currentDocid = docid;

        try {
            VoucherTaxi.toast('info', 'Processing rejection...');

            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.reject(docid),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reason: reason,
                    }),
                }
            );

            if (response.success) {
                VoucherTaxi.toast('success', response.message ?? 'Voucher Taxi rejected successfully');

                // Close detail modal after short delay
                setTimeout(() => {
                    VoucherTaxiModal.closeView();
                    VoucherTaxiDatalist.refresh();
                }, 500);

            } else {
                VoucherTaxi.toast('error', response.message ?? 'Failed to reject voucher');
            }

        } catch (err) {
            console.error('Reject error:', err);

            let message = 'Failed to reject voucher';

            if (err.status === 403) {
                message = 'You do not have permission to reject this voucher';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            VoucherTaxi.toast('error', message);

        } finally {
            VoucherTaxiApproval.state.isProcessing = false;
            VoucherTaxiApproval.state.currentAction = null;
            VoucherTaxiApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // REQUEST REVISION
    // --------------------------------------------------------
    revise(docid = null) {
        docid = docid ?? VoucherTaxi.state.currentDocid;

        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        VoucherTaxi.prompt({
            title: 'Request Revision',
            label: 'Revision Notes',
            placeholder: 'Please provide feedback for the requester to revise...',
            input: 'textarea',
            confirmText: 'Request Revision',
            confirmColor: '#f59e0b',
            cancelText: 'Cancel',
            validationMsg: 'Please provide revision notes.',
        }).then((result) => {
            if (result.isConfirmed) {
                VoucherTaxiApproval._submitRevise(docid, result.value);
            }
        });
    },

    async _submitRevise(docid, reason) {
        if (VoucherTaxiHelper.isEmpty(reason)) {
            VoucherTaxi.toast('warning', 'Please provide revision notes');
            return;
        }

        VoucherTaxiApproval.state.isProcessing = true;
        VoucherTaxiApproval.state.currentAction = 'revise';
        VoucherTaxiApproval.state.currentDocid = docid;

        try {
            VoucherTaxi.toast('info', 'Sending revision request...');

            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.revise(docid),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reason: reason,
                    }),
                }
            );

            if (response.success) {
                VoucherTaxi.toast('success', response.message ?? 'Revision request sent successfully');

                // Update detail if visible
                setTimeout(() => {
                    VoucherTaxiDetailModal.refresh();
                    VoucherTaxiDatalist.refresh();
                }, 500);

            } else {
                VoucherTaxi.toast('error', response.message ?? 'Failed to request revision');
            }

        } catch (err) {
            console.error('Revise error:', err);

            let message = 'Failed to request revision';

            if (err.status === 403) {
                message = 'You do not have permission to request revision';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            VoucherTaxi.toast('error', message);

        } finally {
            VoucherTaxiApproval.state.isProcessing = false;
            VoucherTaxiApproval.state.currentAction = null;
            VoucherTaxiApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // IS PROCESSING CHECK
    // --------------------------------------------------------
    isProcessing() {
        return VoucherTaxiApproval.state.isProcessing;
    },

    // --------------------------------------------------------
    // GET APPROVAL STATUS FOR UI
    // --------------------------------------------------------
    getApprovalStatusText(approval) {
        if (!approval) return '-';

        const statusMap = {
            'P': '⏳ Pending',
            'C': '✓ Completed',
            'A': '✓ Approved',
            'R': '✕ Rejected',
            'D': '↻ Revise',
            'F': '✓ Processed',
            'X': '✗ Cancelled',
        };

        return statusMap[approval.status] ?? approval.status;
    },

    // --------------------------------------------------------
    // GET APPROVAL BADGE COLOR
    // --------------------------------------------------------
    getApprovalBadgeClass(status) {
        const classes = {
            'P': 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            'C': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'A': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'R': 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            'D': 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'F': 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            'X': 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400',
        };

        return classes[status] ?? 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-400';
    },

    // --------------------------------------------------------
    // GET STATUS LABEL TEXT
    // --------------------------------------------------------
    getStatusLabel(status) {
        const labels = {
            'P': 'Pending Approval',
            'C': 'Completed',
            'A': 'Approved',
            'R': 'Rejected',
            'D': 'Needs Revision',
            'F': 'Processed',
            'X': 'Cancelled',
        };

        return labels[status] ?? status;
    },

    // --------------------------------------------------------
    // HANDLE BULK APPROVAL (if needed in future)
    // --------------------------------------------------------
    approveBulk(docids = []) {
        if (!Array.isArray(docids) || docids.length === 0) {
            VoucherTaxi.toast('warning', 'No vouchers selected');
            return;
        }

        VoucherTaxi.confirm({
            title: `Approve ${docids.length} Voucher${docids.length > 1 ? 's' : ''}?`,
            text: 'Are you sure you want to approve these voucher requests?',
            icon: 'question',
            confirmText: 'Yes, Approve All',
            confirmColor: '#10b981',
        }).then((result) => {
            if (result.isConfirmed) {
                VoucherTaxiApproval._submitBulkApprove(docids);
            }
        });
    },

    async _submitBulkApprove(docids) {
        VoucherTaxiApproval.state.isProcessing = true;

        let successCount = 0;
        let failCount = 0;

        for (const docid of docids) {
            try {
                const response = await VoucherTaxi.request(
                    VoucherTaxi.routes.approve(docid),
                    {
                        method: 'POST',
                    }
                );

                if (response.success) {
                    successCount++;
                } else {
                    failCount++;
                }
            } catch (err) {
                failCount++;
            }
        }

        VoucherTaxiApproval.state.isProcessing = false;

        if (successCount > 0) {
            VoucherTaxi.toast(
                'success',
                `${successCount} voucher${successCount > 1 ? 's' : ''} approved successfully${failCount > 0 ? `, ${failCount} failed` : ''}`
            );
        } else {
            VoucherTaxi.toast('error', 'Failed to approve vouchers');
        }

        // Refresh list
        VoucherTaxiDatalist.refresh();
    },

    // --------------------------------------------------------
    // VALIDATE APPROVAL PERMISSIONS
    // --------------------------------------------------------
    canApprove(voucher) {
        return voucher?.can_approve ?? false;
    },

    canReject(voucher) {
        return voucher?.can_reject ?? false;
    },

    canRevise(voucher) {
        return voucher?.can_revise ?? false;
    },

    hasApprovalPermissions(voucher) {
        return VoucherTaxiApproval.canApprove(voucher) ||
               VoucherTaxiApproval.canReject(voucher) ||
               VoucherTaxiApproval.canRevise(voucher);
    },

    // --------------------------------------------------------
    // FORMAT APPROVAL DATE
    // --------------------------------------------------------
    formatApprovalDate(dateString) {
        if (!dateString) return '-';

        try {
            const date = new Date(dateString);
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');

            return `${day}/${month}/${year} ${hours}:${minutes}`;
        } catch (err) {
            return dateString;
        }
    },
};
