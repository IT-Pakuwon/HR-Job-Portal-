// ============================================================
// modal.js — Booking Car
// Modal open/close animations and URL state management
// ============================================================

const BookingCarModal = {

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
        // ── CREATE MODAL ─────────────────────────────────────
        document.getElementById('closeCreateBookingModal')
            ?.addEventListener('click', () => {
                BookingCarModal.closeCreate();
            });

        document.getElementById('closeCreateBookingModalFooter')
            ?.addEventListener('click', () => {
                BookingCarModal.closeCreate();
            });

        // ── VIEW MODAL ───────────────────────────────────────
        document.getElementById('closeViewBookingModal')
            ?.addEventListener('click', () => {
                BookingCarModal.closeView();
            });

        document.getElementById('closeViewBookingModalFooter')
            ?.addEventListener('click', () => {
                BookingCarModal.closeView();
            });

        // ── EDIT MODAL ───────────────────────────────────────
        document.getElementById('closeEditBookingModal')
            ?.addEventListener('click', () => {
                BookingCarModal.closeEdit();
            });

        document.getElementById('cancelEditBookingBtn')
            ?.addEventListener('click', () => {
                BookingCarModal.closeEdit();
            });

        // ── PROCESS MODAL ────────────────────────────────────
        document.getElementById('closeGaProcessModal')
            ?.addEventListener('click', () => {
                BookingCarModal.closeProcess();
            });

        document.getElementById('cancelGaProcessBtn')
            ?.addEventListener('click', () => {
                BookingCarModal.closeProcess();
            });

        // ── ESC KEY — disabled intentionally ─────────────────
        // User must use close/cancel button only
    },

    // --------------------------------------------------------
    // NAMED CLOSE HELPERS
    // --------------------------------------------------------
    closeCreate() {
        BookingCarModal.close('createBookingModal', () => {
            BookingCarHelper.resetForm('bookingCarForm');
            BookingCarRoute.clearCreate();
        });
    },

    closeView() {
        BookingCarModal.close('viewBookingModal', () => {
            BookingCar.clearDoc();
            BookingCar.clearUrl();
        });
    },

    closeEdit() {
        const fromDetail = BookingCarEditForm?.state?.fromDetail ?? false;
        const eid        = BookingCar?.state?.currentEid ?? null;

        BookingCarModal.close('editBookingModal', () => {
            BookingCarHelper.resetForm('editBookingForm');
            BookingCarRoute.clearEdit();
            BookingCarEditForm.state.fromDetail = false;

            // If cancelled (not saved), reopen the detail modal
            if (fromDetail && eid) {
                BookingCarModal.open('viewBookingModal');
            }
        });
    },

    closeProcess() {
        BookingCarModal.close('gaProcessModal', () => {
            BookingCarHelper.resetForm('gaProcessForm');
            BookingCarHelper.hide('driverAssignmentWrapper');
            BookingCarHelper.hide('vehicleAssignmentWrapper');
        });
    },

    // --------------------------------------------------------
    // OPEN HELPERS
    // --------------------------------------------------------
    openCreate() {
        BookingCarModal.open('createBookingModal');
    },

    openView(eid) {
        BookingCar.pushUrl(eid);
        BookingCarModal.open('viewBookingModal');
    },

    openEdit() {
        BookingCarModal.open('editBookingModal');
    },

    openProcess() {
        BookingCarModal.open('gaProcessModal');
    },
};
