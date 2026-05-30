// ============================================================
// auto-open.js — Booking Car
// Auto-open booking detail from URL: /showbookingcar/{eid}
// ============================================================

const BookingCarAutoOpen = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isInitialized: false,
        urlEid:        null,
        urlHistory:    [],
    },

    // --------------------------------------------------------
    // INIT — called once on page load
    // --------------------------------------------------------
    init() {
        if (BookingCarAutoOpen.state.isInitialized) return;

        BookingCarAutoOpen.state.isInitialized = true;

        // Handle initial load
        BookingCarAutoOpen.checkAndOpenFromUrl();

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            BookingCarAutoOpen.handlePopstate(e);
        });
    },

    // --------------------------------------------------------
    // CHECK AND OPEN FROM URL
    // --------------------------------------------------------
    checkAndOpenFromUrl() {
        const eid = BookingCarAutoOpen.extractEidFromUrl();

        if (!eid) {
            // No EID in URL, show normal listing
            return;
        }

        // Store in history
        BookingCarAutoOpen.state.urlEid = eid;

        // Open detail modal
        BookingCarAutoOpen.openFromUrl(eid);
    },

    // --------------------------------------------------------
    // EXTRACT EID FROM URL
    // --------------------------------------------------------
    extractEidFromUrl() {
        // Pattern: /showbookingcar/{eid}
        const pathname = window.location.pathname;
        const regex = /\/showbookingcar\/([a-zA-Z0-9_-]+)/;
        const match = pathname.match(regex);

        if (match && match[1]) {
            return match[1];
        }

        // Also check query parameters as fallback
        const params = new URLSearchParams(window.location.search);
        return params.get('eid') || null;
    },

    // --------------------------------------------------------
    // OPEN FROM URL
    // --------------------------------------------------------
    async openFromUrl(eid) {
        if (!eid) {
            console.warn('No valid EID to open');
            return;
        }

        try {
            // Show loading indicator
            BookingCarAutoOpen.showLoadingIndicator();

            // Fetch booking detail
            const response = await BookingCar.request(
                BookingCar.routes.detail(eid)
            );

            if (!response.success) {
                throw new Error(response.message ?? 'Failed to load booking');
            }

            // Store in core state
            const booking = response.data;
            BookingCar.setDoc(eid, booking.docid, booking.status);

            // Render and open detail modal
            BookingCarDetailModal.renderDetail(booking);
            BookingCarModal.openView(eid);

            // Hide loading indicator
            BookingCarAutoOpen.hideLoadingIndicator();

        } catch (err) {
            console.error('Auto-open error:', err);

            BookingCarAutoOpen.hideLoadingIndicator();

            // Show error message
            BookingCar.toast('error', 'Failed to load booking: ' + (err.message || 'Unknown error'));

            // Clear URL state since booking couldn't be loaded
            BookingCarAutoOpen.clearUrlState();
        }
    },

    // --------------------------------------------------------
    // HANDLE BROWSER BACK/FORWARD (popstate)
    // --------------------------------------------------------
    handlePopstate(event) {
        const state = event.state;

        if (state && state.eid) {
            // User navigated back to detail view
            BookingCarAutoOpen.openFromUrl(state.eid);
        } else {
            // User navigated back to list view
            BookingCarAutoOpen.clearUrlState();

            // Close detail modal if open
            if (!document.getElementById('viewBookingModal')?.classList.contains('hidden')) {
                BookingCarModal.closeView();
            }
        }
    },

    // --------------------------------------------------------
    // PUSH STATE TO HISTORY
    // --------------------------------------------------------
    pushState(eid) {
        const url = BookingCar.routes.show(eid);
        const state = { eid };

        history.pushState(state, '', url);
    },

    // --------------------------------------------------------
    // CLEAR URL STATE
    // --------------------------------------------------------
    clearUrlState() {
        const url = BookingCar.routes.index;
        history.pushState({}, '', url);

        BookingCar.clearDoc();
        BookingCarAutoOpen.state.urlEid = null;
    },

    // --------------------------------------------------------
    // SHOW LOADING INDICATOR
    // --------------------------------------------------------
    showLoadingIndicator() {
        const modalPanel = document.querySelector('#viewBookingModal .modal-panel');
        if (!modalPanel) return;

        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'autoOpenLoading';
        overlay.className = 'absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-black/30 z-50 rounded-lg';
        overlay.innerHTML = `
            <div class="flex flex-col items-center gap-2">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-300 border-t-slate-800 dark:border-slate-700 dark:border-t-slate-200"></div>
                <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Loading booking...
                </div>
            </div>`;

        // Add to modal (will be removed when modal opens)
    },

    // --------------------------------------------------------
    // HIDE LOADING INDICATOR
    // --------------------------------------------------------
    hideLoadingIndicator() {
        const loading = document.getElementById('autoOpenLoading');
        if (loading) {
            loading.remove();
        }
    },

    // --------------------------------------------------------
    // GET CURRENT EID FROM URL
    // --------------------------------------------------------
    getCurrentEid() {
        return BookingCarAutoOpen.extractEidFromUrl();
    },

    // --------------------------------------------------------
    // CHECK IF SHOULD AUTO-OPEN
    // --------------------------------------------------------
    shouldAutoOpen() {
        const eid = BookingCarAutoOpen.extractEidFromUrl();
        return !!eid;
    },

    // --------------------------------------------------------
    // NAVIGATE TO BOOKING
    // --------------------------------------------------------
    navigateTo(eid) {
        if (!eid) {
            console.error('EID is required');
            return;
        }

        const url = BookingCar.routes.show(eid);
        window.location.href = url;
    },

    // --------------------------------------------------------
    // NAVIGATE TO LIST
    // --------------------------------------------------------
    navigateToList() {
        const url = BookingCar.routes.index;
        window.location.href = url;
    },

    // --------------------------------------------------------
    // HANDLE MODAL CLOSE (update URL)
    // --------------------------------------------------------
    onModalClose() {
        // When detail modal closes, update URL to list view
        BookingCarAutoOpen.clearUrlState();
    },

    // --------------------------------------------------------
    // HANDLE MODAL OPEN (update URL)
    // --------------------------------------------------------
    onModalOpen(eid) {
        // When detail modal opens, update URL with EID
        if (eid) {
            BookingCarAutoOpen.pushState(eid);
        }
    },

    // --------------------------------------------------------
    // SHARE BOOKING LINK
    // --------------------------------------------------------
    getShareableLink(eid) {
        if (!eid) return null;

        const baseUrl = window.location.origin;
        const route = BookingCar.routes.show(eid);

        return baseUrl + route;
    },

    // --------------------------------------------------------
    // COPY LINK TO CLIPBOARD
    // --------------------------------------------------------
    copyLinkToClipboard(eid) {
        const link = BookingCarAutoOpen.getShareableLink(eid);

        if (!link) {
            BookingCar.toast('error', 'Invalid EID');
            return;
        }

        // Copy to clipboard
        navigator.clipboard.writeText(link).then(() => {
            BookingCar.toast('success', 'Link copied to clipboard');
        }).catch(() => {
            BookingCar.toast('error', 'Failed to copy link');
        });
    },

    // --------------------------------------------------------
    // HANDLE DIRECT LINK SHARING
    // --------------------------------------------------------
    shareBooking(eid) {
        const link = BookingCarAutoOpen.getShareableLink(eid);

        if (!link) {
            BookingCar.toast('error', 'Invalid EID');
            return;
        }

        // Check if Web Share API is available
        if (navigator.share) {
            navigator.share({
                title: 'Booking Car',
                text: `View this booking: ${BookingCar.state.currentDocid}`,
                url: link,
            }).catch(() => {
                // User cancelled or API not available
                // Fallback to copy
                BookingCarAutoOpen.copyLinkToClipboard(eid);
            });
        } else {
            // Fallback to copy
            BookingCarAutoOpen.copyLinkToClipboard(eid);
        }
    },

    // --------------------------------------------------------
    // DEEP LINK VALIDATION
    // --------------------------------------------------------
    validateDeepLink(eid) {
        if (!eid || typeof eid !== 'string') {
            return false;
        }

        // Basic validation: EID should be alphanumeric + underscore/dash
        const validPattern = /^[a-zA-Z0-9_-]+$/;
        return validPattern.test(eid);
    },

    // --------------------------------------------------------
    // HANDLE ERRORS GRACEFULLY
    // --------------------------------------------------------
    handleInvalidLink() {
        // Show error message
        BookingCar.toast('error', 'The booking link is invalid or expired');

        // Redirect to list after 2 seconds
        setTimeout(() => {
            BookingCarAutoOpen.navigateToList();
        }, 2000);
    },

    // --------------------------------------------------------
    // LOG AUTO-OPEN ACTIVITY
    // --------------------------------------------------------
    logActivity(action, details = {}) {
        const logEntry = {
            timestamp: new Date().toISOString(),
            action: action,
            url: window.location.href,
            eid: BookingCarAutoOpen.state.urlEid,
            ...details,
        };

        console.log('[BookingCarAutoOpen]', logEntry);

        // Could be sent to analytics/monitoring service
    },

    // --------------------------------------------------------
    // INITIALIZE WITH DELAY (for page load)
    // --------------------------------------------------------
    initWithDelay(delayMs = 500) {
        // Wait for other modules to be ready
        setTimeout(() => {
            BookingCarAutoOpen.init();
        }, delayMs);
    },
};
