// ============================================================
// modal.js — Voucher Taxi
// Modal open/close animations and URL state management
// ============================================================

const VoucherTaxiModal = {

    // --------------------------------------------------------
    // OPEN MODAL
    // --------------------------------------------------------
    open(modalId) {
        const modal    = document.getElementById(modalId);
        if (!modal) return;

        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        // Make modal visible first (flex)
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Trigger animation on next frame
        requestAnimationFrame(() => {
            if (backdrop) backdrop.classList.add('opacity-100');

            if (panel) {
                panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
                panel.classList.add('opacity-100', 'translate-y-0', 'scale-100');
            }
        });

        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    },

    // --------------------------------------------------------
    // CLOSE MODAL
    // --------------------------------------------------------
    close(modalId, onClosed = null) {
        const modal    = document.getElementById(modalId);
        if (!modal) return;

        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        // Reverse animation
        if (backdrop) backdrop.classList.remove('opacity-100');

        if (panel) {
            panel.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
            panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');
        }

        // Wait for transition to finish
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // Restore body scroll only if no other modal is open
            const anyOpen = document.querySelector(
                '.fixed.flex:not(.hidden) .modal-panel'
            );
            if (!anyOpen) {
                document.body.style.overflow = '';
            }

            if (typeof onClosed === 'function') {
                onClosed();
            }
        }, 200);
    },

    // --------------------------------------------------------
    // CLOSE ON BACKDROP CLICK  — disabled intentionally
    // User must use close button or cancel button only
    // --------------------------------------------------------

    // --------------------------------------------------------
    // INIT ALL MODAL CLOSE BUTTONS
    // --------------------------------------------------------
    init() {
        // ── OPEN CREATE MODAL ─────────────────────────────────
        document.getElementById('openCreateVoucherModal')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.openCreate();
            });

        // ── CREATE MODAL ─────────────────────────────────────
        document.getElementById('closeCreateVoucherModal')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeCreate();
            });

        document.getElementById('closeCreateVoucherModalFooter')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeCreate();
            });

        // ── VIEW MODAL ───────────────────────────────────────
        document.getElementById('closeViewVoucherModal')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeView();
            });

        document.getElementById('closeViewVoucherModalFooter')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeView();
            });

        // ── EDIT MODAL ───────────────────────────────────────
        document.getElementById('closeEditVoucherModal')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeEdit();
            });

        document.getElementById('closeEditVoucherModalFooter')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeEdit();
            });

        // ── PROCESS MODAL ────────────────────────────────────
        document.getElementById('closeProcessVoucherModal')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeProcess();
            });

        document.getElementById('closeProcessVoucherModalFooter')
            ?.addEventListener('click', () => {
                VoucherTaxiModal.closeProcess();
            });

        // ── ESC KEY — disabled intentionally ─────────────────
        // User must use close/cancel button only
    },

    // --------------------------------------------------------
    // NAMED CLOSE HELPERS
    // --------------------------------------------------------
    closeCreate() {
        VoucherTaxiModal.close('createVoucherModal', () => {
            VoucherTaxiHelper.resetForm('voucherTaxiForm');
        });
    },

    closeView() {
        VoucherTaxiModal.close('viewVoucherModal', () => {
            VoucherTaxi.clearDoc();
            VoucherTaxi.clearUrl();
        });
    },

    closeEdit() {
        const fromDetail = VoucherTaxiEditForm?.state?.fromDetail ?? false;
        const eid        = VoucherTaxi?.state?.currentEid ?? null;

        VoucherTaxiModal.close('editVoucherTaxiModal', () => {
            VoucherTaxiHelper.resetForm('editVoucherTaxiForm');
            VoucherTaxiEditForm.state.fromDetail = false;

            // If cancelled (not saved), reopen the detail modal
            if (fromDetail && eid) {
                VoucherTaxiModal.openView(eid);
            }
        });
    },

    closeProcess() {
        VoucherTaxiModal.close('processVoucherModal', () => {
            VoucherTaxiHelper.resetForm('processVoucherForm');
            VoucherTaxiHelper.hide('expenseOwnerSection');
        });
    },

    // --------------------------------------------------------
    // OPEN HELPERS
    // --------------------------------------------------------
    openCreate() {
        VoucherTaxiModal.open('createVoucherModal');
    },

    openView(eid) {
        VoucherTaxi.pushUrl(eid);
        VoucherTaxiModal.open('viewVoucherModal');
    },

    openEdit() {
        VoucherTaxiModal.open('editVoucherTaxiModal');
    },

    openProcess() {
        VoucherTaxiModal.open('processVoucherModal');
    },
};
