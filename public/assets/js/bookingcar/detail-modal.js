// ============================================================
// detail-modal.js — Booking Car
// Render booking details in view modal with actions and timelines
// ============================================================

const BookingCarDetailModal = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentBooking: null,
    },

    // --------------------------------------------------------
    // INIT — attach event listeners
    // --------------------------------------------------------
    init() {
        BookingCarDetailModal.attachEventListeners();
    },

    // --------------------------------------------------------
    // ATTACH EVENT LISTENERS
    // --------------------------------------------------------
    attachEventListeners() {
        // Edit button
        document.getElementById('editBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarEditForm.openEditForm();
            });

        // Cancel button
        document.getElementById('cancelBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarDetailModal.confirmCancel();
            });

        // Print buttons
        document.getElementById('printBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                const eid = document.getElementById('view_booking_eid')?.value;
                if (eid) {
                    window.open(BookingCar.routes.print(eid), '_blank');
                }
            });

        document.getElementById('printBookingBtnFooter')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                const eid = document.getElementById('view_booking_eid')?.value;
                if (eid) {
                    window.open(BookingCar.routes.print(eid), '_blank');
                }
            });

        // Change expense button (GA only)
        document.getElementById('changeExpenseBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarDetailModal.promptChangeExpense();
            });

        // Approval action buttons
        document.getElementById('approveBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarDetailModal.approveBooking();
            });

        document.getElementById('reviseBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarDetailModal.promptRevise();
            });

        document.getElementById('rejectBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarDetailModal.promptReject();
            });
    },

    // --------------------------------------------------------
    // RENDER DETAIL
    // --------------------------------------------------------
    renderDetail(booking) {
        BookingCarDetailModal.state.currentBooking = booking;

        // Store IDs in hidden inputs
        document.getElementById('view_booking_eid').value = booking.eid ?? '';
        document.getElementById('view_booking_docid').value = booking.docid ?? '';

        // ── HEADER ──────────────────────────────────────────
        BookingCarHelper.setText('detailBookingTitle', `Booking Detail - ${booking.docid}`);

        // ── REQUESTER & STATUS ───────────────────────────────
        BookingCarHelper.setText('view_booking_user', booking.user_peminta ?? '-');
        BookingCarHelper.setHtml('view_booking_status_badge', BookingCar.statusBadge(booking.status));

        // ── BASIC INFO ───────────────────────────────────────
        BookingCarHelper.setText('view_booking_date', BookingCar.formatDate(booking.booking_date));
        BookingCarHelper.setText('view_booking_passenger', booking.passenger ?? '-');
        BookingCarHelper.setText('view_booking_start', BookingCar.formatTime(booking.start_time));
        BookingCarHelper.setText('view_booking_end', BookingCar.formatTime(booking.end_time));
        BookingCarHelper.setText('view_booking_cpny', booking.cpny_id ?? '-');
        BookingCarHelper.setText('view_booking_cpny_site', booking.cpny_id_site ?? '-');
        BookingCarHelper.setText('view_booking_dept', booking.department_id ?? '-');

        // ── PURPOSE NAME BADGE ───────────────────────────────
        BookingCarHelper.setText('view_booking_purpose_name', booking.purpose_name ?? booking.purpose_id ?? '-');

        // ── ROUTES ───────────────────────────────────────────
        const routeCount = booking.details?.length ?? 0;
        BookingCarHelper.setText('view_total_route_badge', `${routeCount} Route${routeCount !== 1 ? 's' : ''}`);
        BookingCarHelper.setHtml('view_booking_route_table', BookingCarHelper.renderRouteRows(booking.details ?? []));

        // ── PURPOSE ──────────────────────────────────────────
        BookingCarHelper.setText('view_booking_purpose', booking.purpose_descr ?? '-');

        // ── DRIVER INFO (shown only if status = F) ───────────
        if (booking.status === 'F') {
            BookingCarHelper.show('driverInfoWrapper');
            BookingCarHelper.setText('view_booking_driver', booking.driver_name ?? '-');
            BookingCarHelper.setText('view_booking_handphone', booking.handphone ?? '-');
            BookingCarHelper.setText('view_booking_nopol', booking.nopol ?? '-');
        } else {
            BookingCarHelper.hide('driverInfoWrapper');
        }

        // ── REVISION REASON (shown only if status = D) ───────
        if (booking.status === 'D' && booking.revise_reason) {
            BookingCarHelper.show('reviseReasonWrapper');
            BookingCarHelper.setText('view_revise_reason', booking.revise_reason);
        } else {
            BookingCarHelper.hide('reviseReasonWrapper');
        }

        // ── ACTION BUTTONS ───────────────────────────────────
        BookingCarDetailModal.renderActionButtons(booking);

        // ── APPROVAL TIMELINE ────────────────────────────────
        const timelineHtml = BookingCarHelper.renderTimeline(booking.approvals ?? []);
        BookingCarHelper.setHtml('bookingTrackingTimeline', timelineHtml);
    },

    // --------------------------------------------------------
    // RENDER ACTION BUTTONS
    // --------------------------------------------------------
    renderActionButtons(booking) {
        const viewActionsDiv = document.getElementById('bookingViewActions');
        const approvalActionsDiv = document.getElementById('bookingApprovalActionsWrapper');
        const editBtn = document.getElementById('editBookingBtn');
        const cancelBtn = document.getElementById('cancelBookingBtn');
        const processBtn = document.getElementById('processBookingBtn');
        const changeExpenseBtn = document.getElementById('changeExpenseBtn');

        if (!viewActionsDiv || !approvalActionsDiv) return;

        // Reset visibility
        viewActionsDiv.innerHTML = '';
        approvalActionsDiv.classList.add('hidden');
        if (editBtn) editBtn.classList.add('hidden');
        if (cancelBtn) cancelBtn.classList.add('hidden');
        if (processBtn) processBtn.classList.add('hidden');
        if (changeExpenseBtn) changeExpenseBtn.classList.add('hidden');

        // ── EDIT BUTTON (show if can_edit) ──────────────────
        if (booking.can_edit) {
            if (editBtn) editBtn.classList.remove('hidden');
        }

        // ── CANCEL BUTTON (show if can_cancel) ──────────────
        if (booking.can_cancel) {
            if (cancelBtn) cancelBtn.classList.remove('hidden');
        }

        // ── PROCESS BUTTON (GA + status C only) ─────────────
        if (booking.can_process) {
            if (processBtn) processBtn.classList.remove('hidden');
        }

        // ── CHANGE EXPENSE BUTTON (GA + Pending only) ────────
        if (booking.can_change_expense) {
            if (changeExpenseBtn) changeExpenseBtn.classList.remove('hidden');
        }

        // ── APPROVAL BUTTONS (show if can_approve or can_reject or can_revise) ──
        if (booking.can_approve || booking.can_reject || booking.can_revise) {
            approvalActionsDiv.classList.remove('hidden');
        } else {
            approvalActionsDiv.classList.add('hidden');
        }

        // ── INFO BADGES / TEXT ──────────────────────────────
        // Show different text based on status
        const statusText = BookingCarDetailModal.getStatusMessage(booking.status);
        if (statusText) {
            viewActionsDiv.innerHTML = `
                <div class="flex-1 rounded-lg bg-blue-50 p-3 text-sm text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                    <i class="fa-solid fa-info-circle mr-2"></i>
                    ${statusText}
                </div>`;
        }
    },

    // --------------------------------------------------------
    // GET STATUS MESSAGE
    // --------------------------------------------------------
    getStatusMessage(status) {
        const messages = {
            'P': 'Waiting for approval. Your request is under review.',
            'C': 'Approved! Your booking request has been accepted.',
            'F': 'Processed by General Affairs. Ready for execution.',
            'D': 'Revise requested. Please review the feedback and resubmit.',
            'R': 'Rejected. Your request has been declined.',
            'X': 'Cancelled. This booking request has been cancelled.',
        };

        return messages[status] ?? '';
    },

    // --------------------------------------------------------
    // CONFIRM CANCEL
    // --------------------------------------------------------
    confirmCancel() {
        const booking = BookingCarDetailModal.state.currentBooking;
        if (!booking) return;

        BookingCar.confirm({
            title: 'Cancel Booking?',
            text: `Are you sure you want to cancel booking "${booking.docid}"? This action cannot be undone.`,
            icon: 'warning',
            confirmText: 'Yes, Cancel it',
            confirmColor: '#dc2626',
            cancelText: 'No, Keep it',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarDetailModal.submitCancel();
            }
        });
    },

    // --------------------------------------------------------
    // SUBMIT CANCEL REQUEST
    // --------------------------------------------------------
    async submitCancel() {
        const docid = BookingCar.state.currentDocid;
        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        try {
            const response = await BookingCar.request(
                BookingCar.routes.cancel(docid),
                {
                    method: 'POST',
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Booking cancelled successfully');
                BookingCarModal.closeView();
                BookingCarDatalist.refresh();
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to cancel booking');
            }

        } catch (err) {
            console.error('Cancel error:', err);
            const message = err.data?.message ?? 'Failed to cancel booking';
            BookingCar.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // APPROVE BOOKING
    // --------------------------------------------------------
    async approveBooking() {
        const docid = BookingCar.state.currentDocid;
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
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarDetailModal.submitApprove(docid);
            }
        });
    },

    async submitApprove(docid) {
        try {
            const response = await BookingCar.request(
                BookingCar.routes.approve(docid),
                {
                    method: 'POST',
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Booking approved successfully');
                BookingCarModal.closeView();
                BookingCarDatalist.refresh();
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to approve booking');
            }

        } catch (err) {
            console.error('Approve error:', err);
            const message = err.data?.message ?? 'Failed to approve booking';
            BookingCar.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // PROMPT FOR REJECT WITH REASON
    // --------------------------------------------------------
    promptReject() {
        BookingCar.prompt({
            title: 'Reject Booking',
            label: 'Rejection Reason',
            placeholder: 'Please explain why you are rejecting this booking...',
            input: 'textarea',
            confirmText: 'Reject',
            confirmColor: '#ef4444',
            validationMsg: 'Please provide a reason for rejection.',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarDetailModal.submitReject(result.value);
            }
        });
    },

    // --------------------------------------------------------
    // SUBMIT REJECT
    // --------------------------------------------------------
    async submitReject(reason) {
        const docid = BookingCar.state.currentDocid;
        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        try {
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
                BookingCarModal.closeView();
                BookingCarDatalist.refresh();
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to reject booking');
            }

        } catch (err) {
            console.error('Reject error:', err);
            const message = err.data?.message ?? 'Failed to reject booking';
            BookingCar.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // PROMPT FOR REVISE WITH REASON
    // --------------------------------------------------------
    promptRevise() {
        BookingCar.prompt({
            title: 'Request Revision',
            label: 'Revision Notes',
            placeholder: 'Please provide feedback for the requester to revise...',
            input: 'textarea',
            confirmText: 'Request Revision',
            confirmColor: '#f59e0b',
            validationMsg: 'Please provide revision notes.',
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarDetailModal.submitRevise(result.value);
            }
        });
    },

    // --------------------------------------------------------
    // SUBMIT REVISE
    // --------------------------------------------------------
    async submitRevise(reason) {
        const docid = BookingCar.state.currentDocid;
        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        try {
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
                BookingCarModal.closeView();
                BookingCarDatalist.refresh();
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to request revision');
            }

        } catch (err) {
            console.error('Revise error:', err);
            const message = err.data?.message ?? 'Failed to request revision';
            BookingCar.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // CHANGE COMPANY EXPENSE  (GA + Pending only)
    // --------------------------------------------------------
    promptChangeExpense() {
        const eid     = BookingCar.state.currentEid;
        const booking = BookingCarDetailModal.state.currentBooking;
        if (!eid || !booking) return;

        const companies = window.BookingCarCompanyList ?? [];
        const options   = companies
            .map(c => `<option value="${c.id}" ${c.id === booking.cpny_id_site ? 'selected' : ''}>${c.id} - ${c.name}</option>`)
            .join('');

        Swal.fire({
            title:             'Change Company Expense',
            html: `
                <p class="mb-3 text-sm text-slate-500">Select the new company expense for <strong>${booking.docid}</strong>.</p>
                <select id="swal-cpny-select" class="swal2-input" style="width:100%;margin:0;height:42px;font-size:14px;">
                    ${options}
                </select>`,
            showCancelButton:   true,
            confirmButtonText:  'Change',
            cancelButtonText:   'Cancel',
            confirmButtonColor: '#0f172a',
            cancelButtonColor:  '#94a3b8',
            reverseButtons:     true,
            preConfirm() {
                const val = document.getElementById('swal-cpny-select')?.value;
                if (!val) {
                    Swal.showValidationMessage('Please select a company.');
                }
                return val;
            },
        }).then((result) => {
            if (result.isConfirmed) {
                BookingCarDetailModal.submitChangeExpense(eid, result.value);
            }
        });
    },

    async submitChangeExpense(eid, cpnyIdSite) {
        try {
            const response = await BookingCar.request(
                BookingCar.routes.changeExpense(eid),
                {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ cpny_id_site: cpnyIdSite }),
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Company expense updated successfully');
                BookingCarDetailModal.loadDetail(eid);
                BookingCarDatalist.refresh();
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to update company expense');
            }

        } catch (err) {
            console.error('Change expense error:', err);
            const message = err.data?.message ?? 'Failed to update company expense';
            BookingCar.toast('error', message);
        }
    },

    // --------------------------------------------------------
    // LOAD DETAIL BY EID  — called from datalist / auto-open
    // --------------------------------------------------------
    async loadDetail(eid) {
        if (!eid) return;

        // Register EID immediately so refresh() works during load
        BookingCar.state.currentEid = eid;

        try {
            const response = await BookingCar.request(BookingCar.routes.detail(eid));

            if (!response.success) {
                BookingCar.toast('error', response.message ?? 'Failed to load booking detail');
                return;
            }

            const booking = response.data;

            // Update full global doc state now that we have docid + status
            BookingCar.setDoc(eid, booking.docid, booking.status);

            BookingCarDetailModal.renderDetail(booking);

        } catch (err) {
            console.error('[BookingCarDetailModal] loadDetail error:', err);
            BookingCar.toast('error', 'Failed to load booking detail');
        }
    },

    // --------------------------------------------------------
    // REFRESH DETAIL (reload current booking)
    // --------------------------------------------------------
    async refresh() {
        const eid = BookingCar.state.currentEid;
        if (!eid) return;

        try {
            const response = await BookingCar.request(BookingCar.routes.detail(eid));

            if (response.success) {
                BookingCarDetailModal.renderDetail(response.data);
            }

        } catch (err) {
            console.error('Refresh error:', err);
        }
    },
};
