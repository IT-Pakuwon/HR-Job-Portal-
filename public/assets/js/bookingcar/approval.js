// ============================================================
// approval.js — Booking Car
// Approval workflow: approve, reject, revise with notifications
// ============================================================

const BookingCarApproval = {

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
    // APPROVE BOOKING
    // --------------------------------------------------------
    approve(docid = null) {
        docid = docid ?? BookingCar.state.currentDocid;

        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        BookingCar.confirm({
            title: 'Approve Booking?',
            text: 'Are you sure you want to approve this booking request?',
            icon: 'question',
            confirmText: 'Yes, Approve',
            confirmColor: '#10b981',
            cancelText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarApproval._submitApprove(docid);
            }
        });
    },

    async _submitApprove(docid) {
        BookingCarApproval.state.isProcessing = true;
        BookingCarApproval.state.currentAction = 'approve';
        BookingCarApproval.state.currentDocid = docid;

        try {
            BookingCar.toast('info', 'Processing approval...');

            const response = await BookingCar.request(
                BookingCar.routes.approve(docid),
                {
                    method: 'POST',
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Booking approved successfully');

                // Update detail if visible
                setTimeout(() => {
                    BookingCarDetailModal.refresh();
                    BookingCarDatalist.refresh();
                }, 500);

            } else {
                BookingCar.toast('error', response.message ?? 'Failed to approve booking');
            }

        } catch (err) {
            console.error('Approve error:', err);

            let message = 'Failed to approve booking';

            if (err.status === 403) {
                message = 'You do not have permission to approve this booking';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            BookingCar.toast('error', message);

        } finally {
            BookingCarApproval.state.isProcessing = false;
            BookingCarApproval.state.currentAction = null;
            BookingCarApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // REJECT BOOKING
    // --------------------------------------------------------
    reject(docid = null) {
        docid = docid ?? BookingCar.state.currentDocid;

        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        BookingCar.prompt({
            title: 'Reject Booking',
            label: 'Rejection Reason',
            placeholder: 'Please explain why you are rejecting this booking...',
            input: 'textarea',
            confirmText: 'Reject',
            confirmColor: '#ef4444',
            cancelText: 'Cancel',
            validationMsg: 'Please provide a reason for rejection.',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarApproval._submitReject(docid, result.value);
            }
        });
    },

    async _submitReject(docid, reason) {
        if (BookingCarHelper.isEmpty(reason)) {
            BookingCar.toast('warning', 'Please provide a rejection reason');
            return;
        }

        BookingCarApproval.state.isProcessing = true;
        BookingCarApproval.state.currentAction = 'reject';
        BookingCarApproval.state.currentDocid = docid;

        try {
            BookingCar.toast('info', 'Processing rejection...');

            const response = await BookingCar.request(
                BookingCar.routes.reject(docid),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment: reason,
                    }),
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Booking rejected successfully');

                // Close detail modal after short delay
                setTimeout(() => {
                    BookingCarModal.closeView();
                    BookingCarDatalist.refresh();
                }, 500);

            } else {
                BookingCar.toast('error', response.message ?? 'Failed to reject booking');
            }

        } catch (err) {
            console.error('Reject error:', err);

            let message = 'Failed to reject booking';

            if (err.status === 403) {
                message = 'You do not have permission to reject this booking';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            BookingCar.toast('error', message);

        } finally {
            BookingCarApproval.state.isProcessing = false;
            BookingCarApproval.state.currentAction = null;
            BookingCarApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // REQUEST REVISION
    // --------------------------------------------------------
    revise(docid = null) {
        docid = docid ?? BookingCar.state.currentDocid;

        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        BookingCar.prompt({
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
                BookingCarApproval._submitRevise(docid, result.value);
            }
        });
    },

    async _submitRevise(docid, reason) {
        if (BookingCarHelper.isEmpty(reason)) {
            BookingCar.toast('warning', 'Please provide revision notes');
            return;
        }

        BookingCarApproval.state.isProcessing = true;
        BookingCarApproval.state.currentAction = 'revise';
        BookingCarApproval.state.currentDocid = docid;

        try {
            BookingCar.toast('info', 'Sending revision request...');

            const response = await BookingCar.request(
                BookingCar.routes.revise(docid),
                {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        comment: reason,
                    }),
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Revision request sent successfully');

                // Update detail if visible
                setTimeout(() => {
                    BookingCarDetailModal.refresh();
                    BookingCarDatalist.refresh();
                }, 500);

            } else {
                BookingCar.toast('error', response.message ?? 'Failed to request revision');
            }

        } catch (err) {
            console.error('Revise error:', err);

            let message = 'Failed to request revision';

            if (err.status === 403) {
                message = 'You do not have permission to request revision';
            } else if (err.data?.message) {
                message = err.data.message;
            }

            BookingCar.toast('error', message);

        } finally {
            BookingCarApproval.state.isProcessing = false;
            BookingCarApproval.state.currentAction = null;
            BookingCarApproval.state.currentDocid = null;
        }
    },

    // --------------------------------------------------------
    // IS PROCESSING CHECK
    // --------------------------------------------------------
    isProcessing() {
        return BookingCarApproval.state.isProcessing;
    },

    // --------------------------------------------------------
    // GET APPROVAL STATUS FOR UI
    // --------------------------------------------------------
    getApprovalStatusText(approval) {
        if (!approval) return '-';

        const statusMap = {
            'P': '⏳ Pending',
            'A': '✓ Approved',
            'R': '✕ Rejected',
            'D': '↻ Revise',
            'C': '✓ Complete',
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
            'A': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            'R': 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            'D': 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            'C': 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-400',
            'X': 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400',
        };

        return classes[status] ?? 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-400';
    },

    // --------------------------------------------------------
    // HANDLE BULK APPROVAL (if needed in future)
    // --------------------------------------------------------
    approveBulk(docids = []) {
        if (!Array.isArray(docids) || docids.length === 0) {
            BookingCar.toast('warning', 'No bookings selected');
            return;
        }

        BookingCar.confirm({
            title: `Approve ${docids.length} Booking${docids.length > 1 ? 's' : ''}?`,
            text: 'Are you sure you want to approve these booking requests?',
            icon: 'question',
            confirmText: 'Yes, Approve All',
            confirmColor: '#10b981',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarApproval._submitBulkApprove(docids);
            }
        });
    },

    async _submitBulkApprove(docids) {
        BookingCarApproval.state.isProcessing = true;

        let successCount = 0;
        let failCount = 0;

        for (const docid of docids) {
            try {
                const response = await BookingCar.request(
                    BookingCar.routes.approve(docid),
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

        BookingCarApproval.state.isProcessing = false;

        if (successCount > 0) {
            BookingCar.toast(
                'success',
                `${successCount} booking${successCount > 1 ? 's' : ''} approved successfully${failCount > 0 ? `, ${failCount} failed` : ''}`
            );
        } else {
            BookingCar.toast('error', 'Failed to approve bookings');
        }

        // Refresh list
        BookingCarDatalist.refresh();
    },

    // --------------------------------------------------------
    // VALIDATE APPROVAL PERMISSIONS
    // --------------------------------------------------------
    canApprove(booking) {
        return booking?.can_approve ?? false;
    },

    canReject(booking) {
        return booking?.can_reject ?? false;
    },

    canRevise(booking) {
        return booking?.can_revise ?? false;
    },

    hasApprovalPermissions(booking) {
        return BookingCarApproval.canApprove(booking) ||
               BookingCarApproval.canReject(booking) ||
               BookingCarApproval.canRevise(booking);
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
