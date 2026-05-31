// ============================================================
// detail-modal.js — Voucher Taxi
// Load and display voucher detail + action buttons
// ============================================================

const VoucherTaxiDetailModal = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isLoading:      false,
        currentEid:     null,
        currentDocid:   null,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VoucherTaxiDetailModal.bindPrint();
        VoucherTaxiDetailModal.bindCancel();
        VoucherTaxiDetailModal.bindEdit();
    },

    bindPrint() {
        document.getElementById('printVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                const eid = VoucherTaxiDetailModal.state.currentEid;
                if (eid) window.open(VoucherTaxi.routes.print(eid), '_blank');
            });
    },

    bindCancel() {
        document.getElementById('cancelVoucherBtn')
            ?.addEventListener('click', () => {
                VoucherTaxiDetailModal.cancel();
            });
    },

    bindEdit() {
        document.getElementById('openEditFromViewBtn')
            ?.addEventListener('click', () => {
                const eid = VoucherTaxiDetailModal.state.currentEid;
                VoucherTaxiEditForm.loadVoucher(eid);
                VoucherTaxiModal.openEdit();
            });
    },

    // --------------------------------------------------------
    // LOAD DETAIL
    // --------------------------------------------------------
    load(eid) {
        if (VoucherTaxiDetailModal.state.isLoading) return;
        VoucherTaxiDetailModal.state.isLoading  = true;
        VoucherTaxiDetailModal.state.currentEid = eid;

        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.detail(eid), {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                VoucherTaxiDetailModal.render(res.data);
                VoucherTaxiModal.openView();
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Failed to load detail');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load voucher details'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiDetailModal.state.isLoading = false;
        });
    },

    // --------------------------------------------------------
    // REFRESH CURRENT DETAIL
    // --------------------------------------------------------
    refresh() {
        const eid = VoucherTaxiDetailModal.state.currentEid;
        if (eid) VoucherTaxiDetailModal.load(eid);
    },

    // --------------------------------------------------------
    // RENDER
    // --------------------------------------------------------
    render(data) {
        VoucherTaxiDetailModal.state.currentEid   = data.eid;
        VoucherTaxiDetailModal.state.currentDocid = data.docid;

        // Also sync VoucherTaxi global state
        VoucherTaxi.setDoc(data.eid, data.docid, data.status);

        // Hidden fields
        document.getElementById('view_eid').value   = data.eid   ?? '';
        document.getElementById('view_docid').value = data.docid ?? '';

        // Title matches Booking Car format: "Voucher Detail - VCR..."
        document.getElementById('detailDocIdTitle').textContent = `Voucher Detail - ${data.docid}`;
        document.getElementById('view_status_badge').innerHTML = VoucherTaxi.statusBadge(data.status);

        // Push URL to /showvouchertaxi/{eid} (same behaviour as Booking Car)
        if (window.history?.pushState) {
            window.history.pushState({}, '', VoucherTaxi.routes.show(data.eid));
        }

        // Info fields
        document.getElementById('view_user').textContent        = data.user_name ?? data.user_peminta ?? '-';
        document.getElementById('view_date').textContent        = VoucherTaxi.formatDate(data.date_used);
        document.getElementById('view_type_trip').textContent   = data.type_trip ?? '-';
        document.getElementById('view_origin').textContent      = data.origin ?? '-';
        document.getElementById('view_destination').textContent = data.destination ?? '-';
        document.getElementById('view_cpny').textContent         = data.cpny_id ?? '-';
        const expCpny = document.getElementById('view_cpny_expense');
        if (expCpny) expCpny.textContent = data.cpny_id_expense ?? data.cpny_id ?? '-';
        document.getElementById('view_dept').textContent        = data.department_id ?? '-';

        // Route + trip type badge
        document.getElementById('view_route').innerHTML = `
            <i class="fa-solid fa-location-arrow mr-2"></i>
            ${data.origin ?? '-'} <i class="fa-solid fa-arrow-right mx-2"></i> ${data.destination ?? '-'}
        `;
        const tripBadge = document.getElementById('view_trip_type_badge');
        if (tripBadge) tripBadge.textContent = data.type_trip ?? '';

        // Purpose + purpose name badge
        document.getElementById('view_purpose').textContent = data.purpose_descr ?? '-';
        const purposeBadge = document.getElementById('view_purpose_name');
        if (purposeBadge) purposeBadge.textContent = data.purpose_id ?? '';

        // Actual expense
        const expWrapper = document.getElementById('actualExpenseWrapper');
        if (data.actual_budget && data.actual_budget > 0) {
            document.getElementById('view_actual_budget').textContent = VoucherTaxi.formatCurrency(data.actual_budget);
            expWrapper?.classList.remove('hidden');
        } else {
            expWrapper?.classList.add('hidden');
        }

        // Revision reason
        const revWrapper = document.getElementById('reviseReasonWrapper');
        if (data.revise_reason) {
            document.getElementById('view_revise_reason').textContent = data.revise_reason;
            revWrapper?.classList.remove('hidden');
        } else {
            revWrapper?.classList.add('hidden');
        }

        // Print button href
        const printBtn = document.getElementById('printVoucherBtn');
        if (printBtn) printBtn.href = VoucherTaxi.routes.print(data.eid);

        // Action buttons
        VoucherTaxiDetailModal.renderActions(data);

        // Load approval timeline
        VoucherTaxiTracking.load(data.eid);
    },

    // --------------------------------------------------------
    // STATUS INFO MESSAGE  (matches Booking Car getStatusMessage)
    // --------------------------------------------------------
    getStatusMessage(status) {
        const messages = {
            P: 'Waiting for approval. Your request is under review.',
            C: 'Approved! Voucher is ready to be processed by GA.',
            F: 'Processed by General Affairs. Ready for execution.',
            D: 'Revision requested. Please review the feedback and resubmit.',
            R: 'Rejected. Your request has been declined.',
            X: 'Cancelled. This voucher request has been cancelled.',
        };
        return messages[status] ?? '';
    },

    // --------------------------------------------------------
    // ACTION BUTTONS
    // --------------------------------------------------------
    renderActions(data) {
        const viewActionsDiv  = document.getElementById('viewActions');
        const approvalBlock   = document.getElementById('approvalActions');
        const editBtn         = document.getElementById('openEditFromViewBtn');
        const cancelBtn       = document.getElementById('cancelVoucherBtn');

        if (!viewActionsDiv) return;

        // Reset everything
        viewActionsDiv.innerHTML = '';
        approvalBlock?.classList.add('hidden');
        approvalBlock?.classList.remove('flex');
        editBtn?.classList.add('hidden');
        cancelBtn?.classList.add('hidden');

        // Reset approval buttons
        ['approveBtn', 'rejectBtn', 'reviseBtn'].forEach(id => {
            const el = document.getElementById(id);
            if (el) { el.disabled = false; el.style.opacity = '1'; }
        });

        // Edit / cancel
        if (data.can_edit)   editBtn?.classList.remove('hidden');
        if (data.can_cancel) cancelBtn?.classList.remove('hidden');

        // Approval actions block
        if (data.can_approve || data.can_reject || data.can_revise) {
            approvalBlock?.classList.remove('hidden');
            approvalBlock?.classList.add('flex');

            if (!data.can_approve) { const el = document.getElementById('approveBtn'); if (el) { el.disabled = true; el.style.opacity = '0.5'; } }
            if (!data.can_reject)  { const el = document.getElementById('rejectBtn');  if (el) { el.disabled = true; el.style.opacity = '0.5'; } }
            if (!data.can_revise)  { const el = document.getElementById('reviseBtn');  if (el) { el.disabled = true; el.style.opacity = '0.5'; } }
        }

        // GA process button (shown in viewActions)
        if (data.can_process) {
            viewActionsDiv.innerHTML = `
                <button type="button" id="openProcessBtn"
                    class="flex-1 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-700">
                    <i class="fa-solid fa-money-bill-wave mr-1"></i> Process
                </button>`;

            document.getElementById('openProcessBtn')
                ?.addEventListener('click', () => VoucherTaxiProcess.load(data.docid));
        } else {
            // Status info message (same pattern as Booking Car)
            const msg = VoucherTaxiDetailModal.getStatusMessage(data.status);
            if (msg) {
                viewActionsDiv.innerHTML = `
                    <div class="flex-1 rounded-lg bg-blue-50 p-3 text-sm text-blue-700 dark:bg-blue-500/10 dark:text-blue-300">
                        <i class="fa-solid fa-circle-info mr-2"></i>${msg}
                    </div>`;
            }
        }
    },

    // --------------------------------------------------------
    // CANCEL VOUCHER
    // --------------------------------------------------------
    async cancel() {
        const docid = VoucherTaxiDetailModal.state.currentDocid;
        if (!docid) return;

        const result = await VoucherTaxi.confirm({
            title:       'Cancel Voucher?',
            text:        'This action cannot be undone.',
            icon:        'warning',
            confirmText: 'Yes, Cancel',
            confirmColor: '#dc2626',
        });

        if (!result.isConfirmed) return;

        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.cancel(docid), {
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
                VoucherTaxi.toast('success', res.message ?? 'Voucher cancelled');
                VoucherTaxiModal.closeView();
                setTimeout(() => VoucherTaxiDataList.reload(), 800);
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Failed to cancel');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => VoucherTaxi.hideLoading());
    },
};
