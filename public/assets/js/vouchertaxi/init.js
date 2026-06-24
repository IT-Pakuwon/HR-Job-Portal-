// ============================================================
// init.js — Voucher Taxi
// Master initialization: loads and initializes all modules
// ============================================================

const VoucherTaxiInit = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        initialized:      false,
        initStartTime:    null,
        initEndTime:      null,
        errors:           [],
        warnings:         [],
    },

    // --------------------------------------------------------
    // MASTER INIT — called on page ready
    // --------------------------------------------------------
    init() {
        // Prevent multiple initializations
        if (VoucherTaxiInit.state.initialized) {
            // console.warn('[VoucherTaxi] Already initialized');
            return;
        }

        VoucherTaxiInit.state.initStartTime = performance.now();

        try {
            // 1. Initialize modal system first (foundation)
            VoucherTaxiInit.initStep('Modal System', () => {
                VoucherTaxiModal.init();
            });

            // 2. Initialize create form
            VoucherTaxiInit.initStep('Create Form', () => {
                VoucherTaxiForm.init();
            });

            // 3. Initialize edit form
            VoucherTaxiInit.initStep('Edit Form', () => {
                VoucherTaxiEditForm.init();
            });

            // 4. Initialize data list and pagination
            VoucherTaxiInit.initStep('Data List', () => {
                VoucherTaxiDatalist.init();
            });

            // 5. Initialize detail modal
            VoucherTaxiInit.initStep('Detail Modal', () => {
                VoucherTaxiDetailModal.init();
            });

            // 6. Initialize tracking/timeline
            VoucherTaxiInit.initStep('Tracking Module', () => {
                VoucherTaxiTracking.init();
            });

            // 7. Initialize approval actions
            VoucherTaxiInit.initStep('Approval Module', () => {
                VoucherTaxiApproval.init();
            });

            // 8. Initialize GA process
            VoucherTaxiInit.initStep('Process Module', () => {
                VoucherTaxiProcess.init();
            });

            // 9. Initialize calendar
            VoucherTaxiInit.initStep('Calendar Module', () => {
                VoucherTaxiCalendar.init();
            });

            // 10. Initialize auto-open (after all modules ready)
            VoucherTaxiInit.initStep('Auto-Open Module', () => {
                VoucherTaxiAutoOpen.init();
            });

            // 11. Setup cross-module integrations
            VoucherTaxiInit.setupIntegrations();

            // 12. Setup global event handlers
            VoucherTaxiInit.setupGlobalHandlers();

            VoucherTaxiInit.state.initialized = true;
            VoucherTaxiInit.state.initEndTime = performance.now();

            // 13. Log initialization complete
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
            if (typeof callback !== 'function') {
                throw new Error('Callback must be a function');
            }

            callback();

            // console.log(`[VoucherTaxi] ✓ ${stepName} initialized`);

        } catch (err) {
            const errorMsg = `${stepName} initialization failed: ${err.message}`;
            // console.error(`[VoucherTaxi] ✗ ${errorMsg}`);

            VoucherTaxiInit.state.errors.push({
                module: stepName,
                error: err.message,
                timestamp: new Date().toISOString(),
            });

            // Re-throw to stop initialization chain
            throw err;
        }
    },

    // --------------------------------------------------------
    // SETUP CROSS-MODULE INTEGRATIONS
    // --------------------------------------------------------
    setupIntegrations() {
        // console.log('[VoucherTaxi] Setting up cross-module integrations...');

        try {
            // 1. Modal close events should update auto-open state
            const viewModalFooterClose = document.getElementById('closeViewVoucherModalFooter');
            if (viewModalFooterClose) {
                viewModalFooterClose.addEventListener('click', () => {
                    VoucherTaxiAutoOpen.onModalClose();
                });
            }

            // 2. Detail modal close should update auto-open
            const viewModalClose = document.getElementById('closeViewVoucherModal');
            if (viewModalClose) {
                viewModalClose.addEventListener('click', () => {
                    VoucherTaxiAutoOpen.onModalClose();
                });
            }

            // 3. Calendar event refresh should update list
            const originalCalendarRefresh = VoucherTaxiCalendar.refresh;
            VoucherTaxiCalendar.refresh = function() {
                originalCalendarRefresh.call(this);
                // Optionally refresh list too
                setTimeout(() => {
                    VoucherTaxiDatalist.refresh();
                }, 1000);
            };

            // 4. Form submission should refresh both list and calendar
            // Already handled in request-form.js

            // 5. Edit form submission should refresh both
            // Already handled in edit-form.js

            // console.log('[VoucherTaxi] ✓ Cross-module integrations completed');

        } catch (err) {
            // console.warn('[VoucherTaxi] ⚠ Integration setup warning:', err.message);
            VoucherTaxiInit.state.warnings.push({
                type: 'integration',
                message: err.message,
            });
        }
    },

    // --------------------------------------------------------
    // SETUP GLOBAL EVENT HANDLERS
    // --------------------------------------------------------
    setupGlobalHandlers() {
        // console.log('[VoucherTaxi] Setting up global event handlers...');

        try {
            // 1. Handle ESC key (close modals)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    // Check which modal is open and close it
                    const viewModal = document.getElementById('viewVoucherModal');
                    const createModal = document.getElementById('createVoucherModal');
                    const editModal = document.getElementById('editVoucherTaxiModal');
                    const processModal = document.getElementById('processVoucherModal');

                    // Close first open modal found (in reverse order of z-index)
                    if (!processModal?.classList.contains('hidden')) {
                        VoucherTaxiModal.closeProcess();
                    } else if (!editModal?.classList.contains('hidden')) {
                        VoucherTaxiModal.closeEdit();
                    } else if (!viewModal?.classList.contains('hidden')) {
                        VoucherTaxiModal.closeView();
                    } else if (!createModal?.classList.contains('hidden')) {
                        VoucherTaxiModal.closeCreate();
                    }
                }
            });

            // 2. Handle window resize (responsive adjustments)
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    // Adjust modals if needed
                    // console.log('[VoucherTaxi] Window resized');
                }, 250);
            });

            // 3. Handle visibility change (pause/resume refreshes)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    // Page hidden - stop auto-refreshes
                    VoucherTaxiTracking.stopAutoRefresh?.();
                    // console.log('[VoucherTaxi] Page hidden - paused auto-refresh');
                } else {
                    // Page visible - restart if needed
                    // console.log('[VoucherTaxi] Page visible - resuming');
                }
            });

            // 4. Handle before unload (cleanup)
            window.addEventListener('beforeunload', () => {
                try {
                    VoucherTaxiTracking.stopAutoRefresh?.();
                    VoucherTaxiCalendar.destroy?.();
                } catch (err) {
                    // console.warn('[VoucherTaxi] Cleanup warning:', err);
                }
            });

            // 5. Handle online/offline status
            window.addEventListener('online', () => {
                // VoucherTaxi.toast('success', 'Connection restored');
                // Refresh data
                VoucherTaxiDatalist.refresh();
            });

            window.addEventListener('offline', () => {
                VoucherTaxi.toast('warning', 'You are offline. Some features may not work.');
            });

            // console.log('[VoucherTaxi] ✓ Global event handlers registered');

        } catch (err) {
            // console.warn('[VoucherTaxi] ⚠ Global handlers warning:', err.message);
            VoucherTaxiInit.state.warnings.push({
                type: 'globalHandlers',
                message: err.message,
            });
        }
    },

    // --------------------------------------------------------
    // HANDLE INITIALIZATION ERROR
    // --------------------------------------------------------
    handleInitError(err) {
        const errorMsg = `Initialization failed: ${err.message}`;

        console.error(`[VoucherTaxi] ✗ ${errorMsg}`);

        VoucherTaxiInit.state.initialized = false;

        // Show user-friendly message
        VoucherTaxi.toast('error', 'Failed to initialize application. Please refresh the page.');

        // Log detailed error
        console.error('[VoucherTaxi] Full error:', err);

        // Could send to error tracking service
        VoucherTaxiInit.reportError(err);
    },

    // --------------------------------------------------------
    // LOG SUCCESSFUL INITIALIZATION
    // --------------------------------------------------------
    logSuccess() {
        const duration = VoucherTaxiInit.state.initEndTime - VoucherTaxiInit.state.initStartTime;
        const durationMs = Math.round(duration * 100) / 100;

        // console.log(`
// ╔════════════════════════════════════════════════════════╗
// ║                                                        ║
// ║     ✓ Voucher Taxi Application Initialized            ║
// ║                                                        ║
// ║     Duration: ${durationMs}ms                           ║
// ║     Status: Ready                                      ║
// ║                                                        ║
// ╚════════════════════════════════════════════════════════╝
//         `);

        // console.log('[VoucherTaxi] Modules loaded:', {
        //     core: 'VoucherTaxi',
        //     modal: 'VoucherTaxiModal',
        //     form: 'VoucherTaxiForm',
        //     editForm: 'VoucherTaxiEditForm',
        //     list: 'VoucherTaxiDatalist',
        //     detail: 'VoucherTaxiDetailModal',
        //     approval: 'VoucherTaxiApproval',
        //     tracking: 'VoucherTaxiTracking',
        //     process: 'VoucherTaxiProcess',
        //     calendar: 'VoucherTaxiCalendar',
        //     autoOpen: 'VoucherTaxiAutoOpen',
        // });

        if (VoucherTaxiInit.state.warnings.length > 0) {
            // console.warn('[VoucherTaxi] Initialization warnings:', VoucherTaxiInit.state.warnings);
        }
    },

    // --------------------------------------------------------
    // REPORT ERROR (could be sent to backend)
    // --------------------------------------------------------
    reportError(err) {
        try {
            // Could send to error tracking service like Sentry
            const errorData = {
                message: err.message,
                stack: err.stack,
                timestamp: new Date().toISOString(),
                url: window.location.href,
                userAgent: navigator.userAgent,
            };

            // console.log('[VoucherTaxi] Error report:', errorData);

            // Example: Send to backend error logging endpoint
            // fetch('/api/errors', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(errorData),
            // }).catch(() => {
            //     // Silently fail if error endpoint is down
            // });

        } catch (err) {
            // console.error('[VoucherTaxi] Error reporting failed:', err);
        }
    },

    // --------------------------------------------------------
    // GET INITIALIZATION STATUS
    // --------------------------------------------------------
    getStatus() {
        return {
            initialized: VoucherTaxiInit.state.initialized,
            initTime: VoucherTaxiInit.state.initStartTime,
            duration: VoucherTaxiInit.state.initEndTime
                ? VoucherTaxiInit.state.initEndTime - VoucherTaxiInit.state.initStartTime
                : null,
            errors: VoucherTaxiInit.state.errors,
            warnings: VoucherTaxiInit.state.warnings,
        };
    },

    // --------------------------------------------------------
    // HEALTH CHECK (verify all modules)
    // --------------------------------------------------------
    healthCheck() {
        const modules = {
            'VoucherTaxi': typeof VoucherTaxi !== 'undefined',
            'VoucherTaxiModal': typeof VoucherTaxiModal !== 'undefined',
            'VoucherTaxiForm': typeof VoucherTaxiForm !== 'undefined',
            'VoucherTaxiEditForm': typeof VoucherTaxiEditForm !== 'undefined',
            'VoucherTaxiDatalist': typeof VoucherTaxiDatalist !== 'undefined',
            'VoucherTaxiDetailModal': typeof VoucherTaxiDetailModal !== 'undefined',
            'VoucherTaxiApproval': typeof VoucherTaxiApproval !== 'undefined',
            'VoucherTaxiTracking': typeof VoucherTaxiTracking !== 'undefined',
            'VoucherTaxiProcess': typeof VoucherTaxiProcess !== 'undefined',
            'VoucherTaxiCalendar': typeof VoucherTaxiCalendar !== 'undefined',
            'VoucherTaxiAutoOpen': typeof VoucherTaxiAutoOpen !== 'undefined',
        };

        const allLoaded = Object.values(modules).every(loaded => loaded);

        if (allLoaded) {
            // console.log('✓ All modules loaded', modules);
        } else {
            // console.warn('⚠ Some modules missing:', modules);
        }

        return allLoaded;
    },

    // --------------------------------------------------------
    // RESET APPLICATION STATE (for debugging)
    // --------------------------------------------------------
    reset() {
        // console.warn('[VoucherTaxi] Resetting application state...');

        try {
            // Clear core state
            VoucherTaxi.clearDoc();

            // Clear detail modal state
            VoucherTaxiDetailModal.state.currentVoucher = null;

            // Clear edit form state
            VoucherTaxiEditForm.state.currentEid = null;
            VoucherTaxiEditForm.state.originalData = null;

            // Clear process state
            VoucherTaxiProcess.state.currentEid = null;
            VoucherTaxiProcess.state.currentVoucher = null;

            // Clear auto-open state
            VoucherTaxiAutoOpen.clearUrlState();

            // Close all modals
            VoucherTaxiModal.closeCreate();
            VoucherTaxiModal.closeView();
            VoucherTaxiModal.closeEdit();
            VoucherTaxiModal.closeProcess();

            // Refresh data
            VoucherTaxiDatalist.refresh();
            VoucherTaxiCalendar.refresh();

        } catch (err) {
            // console.error('[VoucherTaxi] Reset failed:', err);
        }
    },

    // --------------------------------------------------------
    // SETUP COMPLETE NOTIFICATION
    // --------------------------------------------------------
    showReadyIndicator() {
        if (!document.body) return;

        // Optional: Show a subtle "Ready" indicator
        // Can be styled with CSS
        const indicator = document.createElement('div');
        indicator.id = 'voucherTaxiReady';
        indicator.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            padding: 10px 15px;
            background: #10b981;
            color: white;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            opacity: 0.8;
            pointer-events: none;
            z-index: 9999;
            animation: slideIn 0.3s ease-out;
        `;
        indicator.textContent = '✓ Voucher Taxi Ready';

        // Add animation CSS
        if (!document.getElementById('voucherTaxiReadyStyles')) {
            const style = document.createElement('style');
            style.id = 'voucherTaxiReadyStyles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 0.8;
                    }
                }
            `;
            document.head.appendChild(style);
        }

        document.body.appendChild(indicator);

        // Auto-remove after 3 seconds
        setTimeout(() => {
            indicator.remove();
        }, 3000);
    },
};

// ============================================================
// DOCUMENT READY TRIGGER
// ============================================================

if (document.readyState === 'loading') {
    // DOM still loading
    document.addEventListener('DOMContentLoaded', () => {
        VoucherTaxiInit.init();
    });
} else {
    // DOM already loaded
    VoucherTaxiInit.init();
}
