// ============================================================
// init.js — Voucher Taxi
// Master initialization: loads and wires all modules
// ============================================================

const VoucherTaxiInit = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        initialized:   false,
        initStartTime: null,
        initEndTime:   null,
        errors:        [],
        warnings:      [],
    },

    // --------------------------------------------------------
    // MASTER INIT
    // --------------------------------------------------------
    init() {
        if (VoucherTaxiInit.state.initialized) return;

        VoucherTaxiInit.state.initStartTime = performance.now();

        try {
            VoucherTaxiInit.initStep('Ajax Setup',     () => VoucherTaxi.initAjax());
            VoucherTaxiInit.initStep('Modal System',   () => VoucherTaxiModal.init());
            VoucherTaxiInit.initStep('Create Form',    () => VoucherTaxiRequestForm.init());
            VoucherTaxiInit.initStep('Edit Form',      () => VoucherTaxiEditForm.init());
            VoucherTaxiInit.initStep('Data List',      () => VoucherTaxiDataList.init());
            VoucherTaxiInit.initStep('Detail Modal',   () => VoucherTaxiDetailModal.init());
            VoucherTaxiInit.initStep('Tracking',       () => {}); // loaded on-demand
            VoucherTaxiInit.initStep('Approval',       () => VoucherTaxiApproval.init());
            VoucherTaxiInit.initStep('Process Module', () => VoucherTaxiProcess.init());
            VoucherTaxiInit.initStep('Calendar',       () => VoucherTaxiCalendar.init());
            VoucherTaxiInit.initStep('Auto-Open',      () => VoucherTaxiAutoOpen.init());

            VoucherTaxiInit.setupIntegrations();
            VoucherTaxiInit.setupGlobalHandlers();

            VoucherTaxiInit.state.initialized = true;
            VoucherTaxiInit.state.initEndTime = performance.now();

            VoucherTaxiInit.logSuccess();

        } catch (err) {
            VoucherTaxiInit.handleInitError(err);
        }
    },

    // --------------------------------------------------------
    // INIT STEP WITH ERROR HANDLING
    // --------------------------------------------------------
    initStep(stepName, callback) {
        try {
            callback();
        } catch (err) {
            VoucherTaxiInit.state.errors.push({
                module:    stepName,
                error:     err.message,
                timestamp: new Date().toISOString(),
            });
            throw err;
        }
    },

    // --------------------------------------------------------
    // CROSS-MODULE INTEGRATIONS
    // --------------------------------------------------------
    setupIntegrations() {
        try {
            // View modal close → clean URL state
            document.getElementById('closeViewVoucherModal')
                ?.addEventListener('click', () => VoucherTaxiAutoOpen.onModalClose?.());

            document.getElementById('closeViewVoucherModalFooter')
                ?.addEventListener('click', () => VoucherTaxiAutoOpen.onModalClose?.());

            // After create/edit submit → refresh calendar too
            const origReload = VoucherTaxiDataList.reload.bind(VoucherTaxiDataList);
            VoucherTaxiDataList.reload = function() {
                origReload();
                setTimeout(() => VoucherTaxiCalendar.refresh(), 800);
            };

        } catch (err) {
            VoucherTaxiInit.state.warnings.push({ type: 'integration', message: err.message });
        }
    },

    // --------------------------------------------------------
    // GLOBAL EVENT HANDLERS
    // --------------------------------------------------------
    setupGlobalHandlers() {
        try {
            // ESC key → close topmost open modal
            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Escape') return;

                const process = document.getElementById('processVoucherModal');
                const edit    = document.getElementById('editVoucherTaxiModal');
                const view    = document.getElementById('viewVoucherModal');
                const create  = document.getElementById('createVoucherModal');

                if (!process?.classList.contains('hidden'))      VoucherTaxiModal.closeProcess();
                else if (!edit?.classList.contains('hidden'))    VoucherTaxiModal.closeEdit();
                else if (!view?.classList.contains('hidden'))    VoucherTaxiModal.closeView();
                else if (!create?.classList.contains('hidden'))  VoucherTaxiModal.closeCreate();
            });

            // Toggle list panel
            document.getElementById('toggleList')
                ?.addEventListener('click', () => {
                    document.getElementById('voucherListPanel')?.classList.toggle('hidden');
                });

            // Online / offline
            window.addEventListener('offline', () => VoucherTaxi.toast('warning', 'You are offline.'));
            window.addEventListener('online',  () => {
                VoucherTaxi.toast('success', 'Connection restored.');
                VoucherTaxiDataList.reload();
            });

            // Page visibility → stop auto-refresh when hidden
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) VoucherTaxiTracking.stopAutoRefresh?.();
            });

            // Cleanup on unload
            window.addEventListener('beforeunload', () => {
                try {
                    VoucherTaxiTracking.stopAutoRefresh?.();
                    VoucherTaxiCalendar.destroy?.();
                } catch (_) {}
            });

        } catch (err) {
            VoucherTaxiInit.state.warnings.push({ type: 'globalHandlers', message: err.message });
        }
    },

    // --------------------------------------------------------
    // ERROR HANDLER
    // --------------------------------------------------------
    handleInitError(err) {
        VoucherTaxiInit.state.initialized = false;
        VoucherTaxi.toast('error', 'Failed to initialize. Please refresh the page.');
        console.error('[VoucherTaxi] Init error:', err);
    },

    // --------------------------------------------------------
    // LOG SUCCESS
    // --------------------------------------------------------
    logSuccess() {
        const ms = Math.round(
            (VoucherTaxiInit.state.initEndTime - VoucherTaxiInit.state.initStartTime) * 100
        ) / 100;
        console.log(`[VoucherTaxi] ✓ Initialized in ${ms}ms`);

        if (VoucherTaxiInit.state.warnings.length) {
            console.warn('[VoucherTaxi] Warnings:', VoucherTaxiInit.state.warnings);
        }
    },

    // --------------------------------------------------------
    // HEALTH CHECK
    // --------------------------------------------------------
    healthCheck() {
        const modules = {
            VoucherTaxi,
            VoucherTaxiModal,
            VoucherTaxiRequestForm,
            VoucherTaxiEditForm,
            VoucherTaxiDataList,
            VoucherTaxiDetailModal,
            VoucherTaxiApproval,
            VoucherTaxiTracking,
            VoucherTaxiProcess,
            VoucherTaxiCalendar,
            VoucherTaxiAutoOpen,
        };

        const missing = Object.entries(modules)
            .filter(([, v]) => typeof v === 'undefined')
            .map(([k]) => k);

        if (missing.length) console.warn('[VoucherTaxi] Missing modules:', missing);

        return missing.length === 0;
    },
};

// ============================================================
// DOCUMENT READY TRIGGER
// ============================================================

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VoucherTaxiInit.init());
} else {
    VoucherTaxiInit.init();
}
