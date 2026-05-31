// ============================================================
// init.js — Booking Car
// Master initialization: loads and initializes all modules
// ============================================================

const BookingCarInit = {

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
        if (BookingCarInit.state.initialized) {
            // console.warn('[BookingCar] Already initialized');
            return;
        }

        BookingCarInit.state.initStartTime = performance.now();

        try {
            // 1. Initialize modal system first (foundation)
            BookingCarInit.initStep('Modal System', () => {
                BookingCarModal.init();
            });

            // 2. Initialize route table management
            BookingCarInit.initStep('Route Management', () => {
                BookingCarRoute.init();
            });

            // 3. Initialize create form
            BookingCarInit.initStep('Create Form', () => {
                BookingCarForm.init();
            });

            // 4. Initialize edit form
            BookingCarInit.initStep('Edit Form', () => {
                BookingCarEditForm.init();
            });

            // 5. Initialize data list and pagination
            BookingCarInit.initStep('Data List', () => {
                BookingCarDatalist.init();
            });

            // 6. Initialize detail modal
            BookingCarInit.initStep('Detail Modal', () => {
                BookingCarDetailModal.init();
            });

            // 7. Initialize tracking/timeline
            BookingCarInit.initStep('Tracking Module', () => {
                BookingCarTracking.init();
            });

            // 8. Initialize approval actions
            BookingCarInit.initStep('Approval Module', () => {
                BookingCarApproval.init();
            });

            // 9. Initialize GA process
            BookingCarInit.initStep('Process Module', () => {
                BookingCarProcess.init();
            });

            // 10. Initialize calendar
            BookingCarInit.initStep('Calendar Module', () => {
                BookingCarCalendar.init();
            });

            // 11. Initialize auto-open (after all modules ready)
            BookingCarInit.initStep('Auto-Open Module', () => {
                BookingCarAutoOpen.init();
            });

            // 12. Setup cross-module integrations
            BookingCarInit.setupIntegrations();

            // 13. Setup global event handlers
            BookingCarInit.setupGlobalHandlers();

            BookingCarInit.state.initialized = true;
            BookingCarInit.state.initEndTime = performance.now();

            // 14. Log initialization complete
            BookingCarInit.logSuccess();

        } catch (err) {
            BookingCarInit.handleInitError(err);
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

            // console.log(`[BookingCar] ✓ ${stepName} initialized`);

        } catch (err) {
            const errorMsg = `${stepName} initialization failed: ${err.message}`;
            // console.error(`[BookingCar] ✗ ${errorMsg}`);

            BookingCarInit.state.errors.push({
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
        // console.log('[BookingCar] Setting up cross-module integrations...');

        try {
            // 1. Modal close events should update auto-open state
            const viewModalFooterClose = document.getElementById('closeViewBookingModalFooter');
            if (viewModalFooterClose) {
                viewModalFooterClose.addEventListener('click', () => {
                    BookingCarAutoOpen.onModalClose();
                });
            }

            // 2. Detail modal close should update auto-open
            const viewModalClose = document.getElementById('closeViewBookingModal');
            if (viewModalClose) {
                viewModalClose.addEventListener('click', () => {
                    BookingCarAutoOpen.onModalClose();
                });
            }

            // 3. When detail modal opens, update URL
            // This is handled in detail-modal.js openBookingDetail

            // 4. Calendar event refresh should update list
            const originalCalendarRefresh = BookingCarCalendar.refresh;
            BookingCarCalendar.refresh = function() {
                originalCalendarRefresh.call(this);
                // Optionally refresh list too
                setTimeout(() => {
                    BookingCarDatalist.refresh();
                }, 1000);
            };

            // 5. Form submission should refresh both list and calendar
            // Already handled in request-form.js

            // 6. Edit form submission should refresh both
            // Already handled in edit-form.js

            // console.log('[BookingCar] ✓ Cross-module integrations completed');

        } catch (err) {
            // console.warn('[BookingCar] ⚠ Integration setup warning:', err.message);
            BookingCarInit.state.warnings.push({
                type: 'integration',
                message: err.message,
            });
        }
    },

    // --------------------------------------------------------
    // SETUP GLOBAL EVENT HANDLERS
    // --------------------------------------------------------
    setupGlobalHandlers() {
        // console.log('[BookingCar] Setting up global event handlers...');

        try {
            // 1. Handle ESC key (close modals)
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    // Check which modal is open and close it
                    const viewModal = document.getElementById('viewBookingModal');
                    const createModal = document.getElementById('createBookingModal');
                    const editModal = document.getElementById('editBookingModal');
                    const processModal = document.getElementById('gaProcessModal');

                    // Close first open modal found (in reverse order of z-index)
                    if (!processModal?.classList.contains('hidden')) {
                        BookingCarModal.closeProcess();
                    } else if (!editModal?.classList.contains('hidden')) {
                        BookingCarModal.closeEdit();
                    } else if (!viewModal?.classList.contains('hidden')) {
                        BookingCarModal.closeView();
                    } else if (!createModal?.classList.contains('hidden')) {
                        BookingCarModal.closeCreate();
                    }
                }
            });

            // 2. Handle window resize (responsive adjustments)
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    // Adjust modals if needed
                    // console.log('[BookingCar] Window resized');
                }, 250);
            });

            // 3. Handle visibility change (pause/resume refreshes)
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    // Page hidden - stop auto-refreshes
                    BookingCarTracking.stopAutoRefresh();
                    // console.log('[BookingCar] Page hidden - paused auto-refresh');
                } else {
                    // Page visible - restart if needed
                    // console.log('[BookingCar] Page visible - resuming');
                }
            });

            // 4. Handle before unload (cleanup)
            window.addEventListener('beforeunload', () => {
                try {
                    BookingCarTracking.stopAutoRefresh();
                    BookingCarCalendar.destroy();
                } catch (err) {
                //  console.warn('[BookingCar] Cleanup warning:', err);
                }
            });

            // 5. Handle online/offline status
            window.addEventListener('online', () => {
                // BookingCar.toast('success', 'Connection restored');
                // Refresh data
                BookingCarDatalist.refresh();
            });

            window.addEventListener('offline', () => {
                BookingCar.toast('warning', 'You are offline. Some features may not work.');
            });

            // console.log('[BookingCar] ✓ Global event handlers registered');

        } catch (err) {
            // console.warn('[BookingCar] ⚠ Global handlers warning:', err.message);
            BookingCarInit.state.warnings.push({
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

        // console.error(`[BookingCar] ✗ ${errorMsg}`);

        BookingCarInit.state.initialized = false;

        // Show user-friendly message
        BookingCar.toast('error', 'Failed to initialize application. Please refresh the page.');

        // Log detailed error
        // console.error('[BookingCar] Full error:', err);

        // Could send to error tracking service
        BookingCarInit.reportError(err);
    },

    // --------------------------------------------------------
    // LOG SUCCESSFUL INITIALIZATION
    // --------------------------------------------------------
    logSuccess() {
        const duration = BookingCarInit.state.initEndTime - BookingCarInit.state.initStartTime;
        const durationMs = Math.round(duration * 100) / 100;

//         console.log(`
// ╔════════════════════════════════════════════════════════╗
// ║                                                        ║
// ║     ✓ Booking Car Application Initialized             ║
// ║                                                        ║
// ║     Duration: ${durationMs}ms                           ║
// ║     Status: Ready                                      ║
// ║                                                        ║
// ╚════════════════════════════════════════════════════════╝
//         `);

//         console.log('[BookingCar] Modules loaded:', {
//             core: 'BookingCar',
//             modal: 'BookingCarModal',
//             form: 'BookingCarForm',
//             editForm: 'BookingCarEditForm',
//             route: 'BookingCarRoute',
//             list: 'BookingCarDatalist',
//             detail: 'BookingCarDetailModal',
//             approval: 'BookingCarApproval',
//             tracking: 'BookingCarTracking',
//             process: 'BookingCarProcess',
//             calendar: 'BookingCarCalendar',
//             autoOpen: 'BookingCarAutoOpen',
//         });

        if (BookingCarInit.state.warnings.length > 0) {
            // console.warn('[BookingCar] Initialization warnings:', BookingCarInit.state.warnings);
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

            // console.log('[BookingCar] Error report:', errorData);

            // Example: Send to backend error logging endpoint
            // fetch('/api/errors', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(errorData),
            // }).catch(() => {
            //     // Silently fail if error endpoint is down
            // });

        } catch (err) {
            // console.error('[BookingCar] Error reporting failed:', err);
        }
    },

    // --------------------------------------------------------
    // GET INITIALIZATION STATUS
    // --------------------------------------------------------
    getStatus() {
        return {
            initialized: BookingCarInit.state.initialized,
            initTime: BookingCarInit.state.initStartTime,
            duration: BookingCarInit.state.initEndTime
                ? BookingCarInit.state.initEndTime - BookingCarInit.state.initStartTime
                : null,
            errors: BookingCarInit.state.errors,
            warnings: BookingCarInit.state.warnings,
        };
    },

    // --------------------------------------------------------
    // HEALTH CHECK (verify all modules)
    // --------------------------------------------------------
    healthCheck() {
        const modules = {
            'BookingCar': typeof BookingCar !== 'undefined',
            'BookingCarModal': typeof BookingCarModal !== 'undefined',
            'BookingCarForm': typeof BookingCarForm !== 'undefined',
            'BookingCarEditForm': typeof BookingCarEditForm !== 'undefined',
            'BookingCarRoute': typeof BookingCarRoute !== 'undefined',
            'BookingCarDatalist': typeof BookingCarDatalist !== 'undefined',
            'BookingCarDetailModal': typeof BookingCarDetailModal !== 'undefined',
            'BookingCarApproval': typeof BookingCarApproval !== 'undefined',
            'BookingCarTracking': typeof BookingCarTracking !== 'undefined',
            'BookingCarProcess': typeof BookingCarProcess !== 'undefined',
            'BookingCarCalendar': typeof BookingCarCalendar !== 'undefined',
            'BookingCarAutoOpen': typeof BookingCarAutoOpen !== 'undefined',
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
        // console.warn('[BookingCar] Resetting application state...');

        try {
            // Clear core state
            BookingCar.clearDoc();

            // Clear detail modal state
            BookingCarDetailModal.state.currentBooking = null;

            // Clear edit form state
            BookingCarEditForm.state.currentEid = null;
            BookingCarEditForm.state.originalData = null;

            // Clear process state
            BookingCarProcess.state.currentEid = null;
            BookingCarProcess.state.currentBooking = null;

            // Clear auto-open state
            BookingCarAutoOpen.clearUrlState();

            // Close all modals
            BookingCarModal.closeCreate();
            BookingCarModal.closeView();
            BookingCarModal.closeEdit();
            BookingCarModal.closeProcess();

            // Refresh data
            BookingCarDatalist.refresh();
            BookingCarCalendar.refresh();



        } catch (err) {
            // console.error('[BookingCar] Reset failed:', err);
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
        indicator.id = 'bookingCarReady';
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
        indicator.textContent = '✓ Booking Car Ready';

        // Add animation CSS
        if (!document.getElementById('bookingCarReadyStyles')) {
            const style = document.createElement('style');
            style.id = 'bookingCarReadyStyles';
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
        BookingCarInit.init();
    });
} else {
    // DOM already loaded
    BookingCarInit.init();
}
