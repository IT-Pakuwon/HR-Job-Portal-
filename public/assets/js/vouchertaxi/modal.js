// ============================================================
// modal.js — Voucher Taxi
// Modal open / close animations and management
// ============================================================

const VoucherTaxiModal = {

    // --------------------------------------------------------
    // SELECTORS
    // --------------------------------------------------------
    ids: {
        create:  'createVoucherModal',
        edit:    'editVoucherTaxiModal',
        view:    'viewVoucherModal',
        process: 'processVoucherModal',
    },

    // --------------------------------------------------------
    // INIT — wire close buttons once
    // --------------------------------------------------------
    init() {
        const M = VoucherTaxiModal;

        // Create
        document.getElementById('openCreateVoucherModal')
            ?.addEventListener('click', () => M.openCreate());
        document.getElementById('closeCreateVoucherModal')
            ?.addEventListener('click', () => M.closeCreate());
        document.getElementById('closeCreateVoucherModalFooter')
            ?.addEventListener('click', () => M.closeCreate());

        // Edit
        document.getElementById('closeEditVoucherModal')
            ?.addEventListener('click', () => M.closeEdit());
        document.getElementById('closeEditVoucherModalFooter')
            ?.addEventListener('click', () => M.closeEdit());
        document.getElementById('openEditFromViewBtn')
            ?.addEventListener('click', () => { M.closeView(); M.openEdit(); });

        // View
        document.getElementById('closeViewVoucherModal')
            ?.addEventListener('click', () => M.closeView());
        document.getElementById('closeViewVoucherModalFooter')
            ?.addEventListener('click', () => M.closeView());

        // Process
        document.getElementById('closeProcessVoucherModal')
            ?.addEventListener('click', () => M.closeProcess());
        document.getElementById('closeProcessVoucherModalFooter')
            ?.addEventListener('click', () => M.closeProcess());

        // --------------------------------------------------------
        // CLOSE ON BACKDROP CLICK — disabled intentionally
        // User must use close button or cancel button only
        // --------------------------------------------------------
    },

    // --------------------------------------------------------
    // OPEN / CLOSE HELPERS
    // --------------------------------------------------------
    _open(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';

        requestAnimationFrame(() => {
            el.querySelector('.modal-backdrop')?.style.setProperty('opacity', '1');
            const panel = el.querySelector('.modal-panel');
            if (panel) {
                panel.style.opacity   = '1';
                panel.style.transform = 'translate(0,0) scale(1)';
            }
        });
    },

    _close(id, onClosed) {
        const el = document.getElementById(id);
        if (!el) return;

        el.querySelector('.modal-backdrop')?.style.setProperty('opacity', '0');
        const panel = el.querySelector('.modal-panel');
        if (panel) {
            panel.style.opacity   = '0';
            panel.style.transform = 'translate(0,16px) scale(0.98)';
        }

        setTimeout(() => {
            el.classList.add('hidden');
            el.classList.remove('flex');
            document.body.style.overflow = '';
            onClosed?.();
        }, 200);
    },

    // --------------------------------------------------------
    // PUBLIC API
    // --------------------------------------------------------
    openCreate()  { this._open(this.ids.create); },
    openEdit()    { this._open(this.ids.edit); },
    openView()    { this._open(this.ids.view); },
    openProcess() { this._open(this.ids.process); },

    closeCreate()  { this._close(this.ids.create,  () => VoucherTaxiRequestForm.reset()); },
    closeEdit()    { this._close(this.ids.edit,     () => VoucherTaxiEditForm.reset()); },
    closeView() {
        this._close(this.ids.view, () => {
            // Revert URL back to /vouchertaxi if it was pushed to /showvouchertaxi/{eid}
            if (window.location.pathname.includes('/showvouchertaxi/') && window.history?.replaceState) {
                window.history.replaceState({}, '', VoucherTaxi.routes.index);
            }
        });
    },
    closeProcess() { this._close(this.ids.process,  () => VoucherTaxiProcess.reset()); },

    closeAll() {
        this.closeCreate();
        this.closeEdit();
        this.closeView();
        this.closeProcess();
    },

    isAnyOpen() {
        return Object.values(this.ids).some(id => {
            return !document.getElementById(id)?.classList.contains('hidden');
        });
    },

    // --------------------------------------------------------
    // SCROLL TO ELEMENT INSIDE MODAL
    // --------------------------------------------------------
    scrollToElement(selector) {
        const el    = document.querySelector(selector);
        const body  = el?.closest('.modal-scroll');
        if (!el || !body) return;
        body.scrollTop += el.getBoundingClientRect().top - body.getBoundingClientRect().top - 100;
    },
};
