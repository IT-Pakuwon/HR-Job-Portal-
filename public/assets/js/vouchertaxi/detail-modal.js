// ============================================================
// detail-modal.js — Voucher Taxi
// Render voucher details in view modal with actions and timeline
// ============================================================

const VoucherTaxiDetailModal = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentVoucher: null,
    },

    // --------------------------------------------------------
    // INIT — attach event listeners
    // --------------------------------------------------------
    init() {
        VoucherTaxiDetailModal.attachEventListeners();
    },

    // --------------------------------------------------------
    // ATTACH EVENT LISTENERS
    // --------------------------------------------------------
    attachEventListeners() {
        document.getElementById('openEditFromViewBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiEditForm.openEditForm();
            });

        document.getElementById('cancelVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiDetailModal.confirmCancel();
            });

        document.getElementById('printVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                const eid = document.getElementById('view_eid')?.value;
                if (eid) window.open(VoucherTaxi.routes.print(eid), '_blank');
            });

        document.getElementById('processVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiProcess.openProcess();
            });

        document.getElementById('approveBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiApproval.approve();
            });

        document.getElementById('reviseBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiApproval.revise();
            });

        document.getElementById('rejectBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiApproval.reject();
            });
    },

    // --------------------------------------------------------
    // RENDER DETAIL
    // --------------------------------------------------------
    renderDetail(voucher) {
        VoucherTaxiDetailModal.state.currentVoucher = voucher;

        // Store IDs in hidden inputs
        document.getElementById('view_eid').value   = voucher.eid   ?? '';
        document.getElementById('view_docid').value = voucher.docid ?? '';

        // ── HEADER  (matches Booking Car: "Voucher Detail - {docid}") ──
        VoucherTaxiHelper.setText('detailDocIdTitle', `Voucher Detail - ${voucher.docid}`);

        // ── REQUESTER & STATUS ───────────────────────────────
        VoucherTaxiHelper.setText('view_user', voucher.user_name ?? voucher.user_peminta ?? '-');
        VoucherTaxiHelper.setHtml('view_status_badge', VoucherTaxi.statusBadge(voucher.status));

        // ── BASIC INFO ───────────────────────────────────────
        VoucherTaxiHelper.setText('view_date',        VoucherTaxi.formatDate(voucher.date_used));
        VoucherTaxiHelper.setText('view_type_trip',   voucher.type_trip  ?? '-');
        VoucherTaxiHelper.setText('view_origin',      voucher.origin     ?? '-');
        VoucherTaxiHelper.setText('view_destination', voucher.destination ?? '-');
        VoucherTaxiHelper.setText('view_cpny',        voucher.cpny_id ?? '-');
        VoucherTaxiHelper.setText('view_cpny_expense', voucher.cpny_id_expense ?? voucher.cpny_id ?? '-');
        VoucherTaxiHelper.setText('view_dept',        voucher.department_id ?? '-');
        VoucherTaxiHelper.setText('view_topup_user',  voucher.user_topup_name ?? voucher.user_topup ?? '-');

        // ── ROUTE ────────────────────────────────────────────
        VoucherTaxiHelper.setText('view_route', `${voucher.origin ?? '-'} → ${voucher.destination ?? '-'}`);

        // ── PURPOSE NAME BADGE (show purpose_name, not ID) ───
        VoucherTaxiHelper.setText('view_purpose_name', voucher.purpose_name ?? voucher.purpose_id ?? '-');

        // ── PURPOSE DESCRIPTION ──────────────────────────────
        VoucherTaxiHelper.setText('view_purpose', voucher.purpose_descr ?? '-');

        // ── ACTUAL EXPENSE (C or F status only) ─────────────
        if ((voucher.status === 'C' || voucher.status === 'F') && voucher.actual_budget) {
            VoucherTaxiHelper.show('actualExpenseWrapper');
            VoucherTaxiHelper.setText('view_actual_budget', VoucherTaxi.formatCurrency(voucher.actual_budget));
        } else {
            VoucherTaxiHelper.hide('actualExpenseWrapper');
        }

        // ── REVISION REASON (D status only) ──────────────────
        if (voucher.status === 'D' && voucher.revise_reason) {
            VoucherTaxiHelper.show('reviseReasonWrapper');
            VoucherTaxiHelper.setText('view_revise_reason', voucher.revise_reason);
        } else {
            VoucherTaxiHelper.hide('reviseReasonWrapper');
        }

        // ── ACTION BUTTONS ───────────────────────────────────
        VoucherTaxiDetailModal.renderActionButtons(voucher);

        // ── APPROVAL TIMELINE  (async — loads from tracking endpoint) ──
        VoucherTaxiDetailModal.renderApprovalFlow(voucher);
    },

    // --------------------------------------------------------
    // RENDER ACTION BUTTONS
    // --------------------------------------------------------
    renderActionButtons(voucher) {
        const viewActionsDiv   = document.getElementById('viewActions');
        const approvalActionsDiv = document.getElementById('approvalActions');
        const editBtn          = document.getElementById('openEditFromViewBtn');
        const cancelBtn        = document.getElementById('cancelVoucherBtn');
        const processBtn       = document.getElementById('processVoucherBtn');

        if (!viewActionsDiv || !approvalActionsDiv) return;

        // Reset
        viewActionsDiv.innerHTML = '';
        approvalActionsDiv.classList.add('hidden');
        editBtn?.classList.add('hidden');
        cancelBtn?.classList.add('hidden');
        processBtn?.classList.add('hidden');

        if (voucher.can_edit)    editBtn?.classList.remove('hidden');
        if (voucher.can_cancel)  cancelBtn?.classList.remove('hidden');
        if (voucher.can_process) processBtn?.classList.remove('hidden');

        if (voucher.can_approve || voucher.can_reject || voucher.can_revise) {
            approvalActionsDiv.classList.remove('hidden');
        }

        // Status message banner — always blue (matches Booking Car)
        const statusText = VoucherTaxiDetailModal.getStatusMessage(voucher.status);
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
            P: 'Waiting for approval. Your request is under review.',
            C: 'Approved! Your voucher request has been accepted.',
            F: 'Processed by General Affairs. Ready for execution.',
            D: 'Revise requested. Please review the feedback and resubmit.',
            R: 'Rejected. Your request has been declined.',
            X: 'Cancelled. This voucher request has been cancelled.',
        };
        return messages[status] ?? '';
    },

    // --------------------------------------------------------
    // RENDER APPROVAL FLOW TIMELINE
    // Fetches tracking data and renders via VoucherTaxiHelper.renderTimeline()
    // --------------------------------------------------------
    async renderApprovalFlow(voucher) {
        const flowDiv = document.getElementById('approvalFlow');
        if (!flowDiv) return;

        // Loading placeholder
        flowDiv.innerHTML = `
            <div class="flex items-center gap-2 rounded-lg border border-slate-200 bg-white p-4 text-sm text-slate-500 dark:border-white/10 dark:bg-[#0f172a]">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <span>Loading timeline...</span>
            </div>`;

        try {
            const res   = await VoucherTaxi.request(VoucherTaxi.routes.tracking(voucher.eid));
            const steps = res.steps ?? res.data ?? [];
            flowDiv.innerHTML = VoucherTaxiHelper.renderTimeline(steps);

        } catch {
            flowDiv.innerHTML = VoucherTaxiHelper.renderTimeline([]);
        }
    },

    // --------------------------------------------------------
    // CONFIRM CANCEL
    // --------------------------------------------------------
    confirmCancel() {
        const voucher = VoucherTaxiDetailModal.state.currentVoucher;
        if (!voucher) return;

        VoucherTaxi.confirm({
            title:        'Cancel Voucher?',
            text:         `Are you sure you want to cancel voucher "${voucher.docid}"? This action cannot be undone.`,
            icon:         'warning',
            confirmText:  'Yes, Cancel it',
            confirmColor: '#dc2626',
            cancelText:   'No, Keep it',
        }).then((result) => {
            if (result.isConfirmed) VoucherTaxiDetailModal.submitCancel();
        });
    },

    // --------------------------------------------------------
    // SUBMIT CANCEL
    // --------------------------------------------------------
    async submitCancel() {
        const docid = VoucherTaxi.state.currentDocid;
        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        try {
            const res = await VoucherTaxi.request(VoucherTaxi.routes.cancel(docid), { method: 'POST' });

            if (res.success) {
                VoucherTaxi.toast('success', res.message ?? 'Voucher cancelled successfully');
                VoucherTaxiModal.closeView();
                VoucherTaxiDatalist.refresh();
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Failed to cancel voucher');
            }

        } catch (err) {
            VoucherTaxi.toast('error', err.data?.message ?? 'Failed to cancel voucher');
        }
    },

    // --------------------------------------------------------
    // LOAD DETAIL BY EID
    // --------------------------------------------------------
    async loadDetail(eid) {
        if (!eid) return;

        VoucherTaxi.state.currentEid = eid;

        try {
            const res = await VoucherTaxi.request(VoucherTaxi.routes.detail(eid));

            if (!res.success) {
                VoucherTaxi.toast('error', res.message ?? 'Failed to load voucher detail');
                return;
            }

            VoucherTaxi.setDoc(eid, res.data.docid, res.data.status);
            VoucherTaxiDetailModal.renderDetail(res.data);

        } catch (err) {
            console.error('[VoucherTaxiDetailModal] loadDetail error:', err);
            VoucherTaxi.toast('error', 'Failed to load voucher detail');
        }
    },

    // --------------------------------------------------------
    // REFRESH DETAIL
    // --------------------------------------------------------
    async refresh() {
        const eid = VoucherTaxi.state.currentEid;
        if (!eid) return;

        try {
            const res = await VoucherTaxi.request(VoucherTaxi.routes.detail(eid));
            if (res.success) VoucherTaxiDetailModal.renderDetail(res.data);
        } catch (err) {
            console.error('Refresh error:', err);
        }
    },
};
