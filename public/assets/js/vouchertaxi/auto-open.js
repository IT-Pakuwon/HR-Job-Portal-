// ============================================================
// auto-open.js — Voucher Taxi
// Auto-open voucher detail from URL: /showvouchertaxi/{eid}
// ============================================================

const VoucherTaxiAutoOpen = {

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
        if (VoucherTaxiAutoOpen.state.isInitialized) return;

        VoucherTaxiAutoOpen.state.isInitialized = true;

        // Handle initial load
        VoucherTaxiAutoOpen.checkAndOpenFromUrl();

        // Handle browser back/forward
        window.addEventListener('popstate', (e) => {
            VoucherTaxiAutoOpen.handlePopstate(e);
        });
    },

    // --------------------------------------------------------
    // CHECK AND OPEN FROM URL
    // --------------------------------------------------------
    checkAndOpenFromUrl() {
        const eid = VoucherTaxiAutoOpen.extractEidFromUrl();

        if (!eid) {
            // No EID in URL, show normal listing
            return;
        }

        // Store in history
        VoucherTaxiAutoOpen.state.urlEid = eid;

        // Open detail modal
        VoucherTaxiAutoOpen.openFromUrl(eid);
    },

    // --------------------------------------------------------
    // EXTRACT EID FROM URL
    // --------------------------------------------------------
    extractEidFromUrl() {
        // Pattern: /showvouchertaxi/{eid}
        const pathname = window.location.pathname;
        const regex = /\/showvouchertaxi\/([a-zA-Z0-9_-]+)/;
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
            VoucherTaxiAutoOpen.showLoadingIndicator();

            // Fetch voucher detail
            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.detail(eid)
            );

            if (!response.success) {
                throw new Error(response.message ?? 'Failed to load voucher');
            }

            // Store in core state
            const voucher = response.data;
            VoucherTaxi.setDoc(eid, voucher.docid, voucher.status);

            // Render and open detail modal
            VoucherTaxiDetailModal.renderDetail(voucher);
            VoucherTaxiModal.openView(eid);

            // Hide loading indicator
            VoucherTaxiAutoOpen.hideLoadingIndicator();

        } catch (err) {
            console.error('Auto-open error:', err);

            VoucherTaxiAutoOpen.hideLoadingIndicator();

            // Show error message
            VoucherTaxi.toast('error', 'Failed to load voucher: ' + (err.message || 'Unknown error'));

            // Clear URL state since voucher couldn't be loaded
            VoucherTaxiAutoOpen.clearUrlState();
        }
    },

    // --------------------------------------------------------
    // HANDLE BROWSER BACK/FORWARD (popstate)
    // --------------------------------------------------------
    handlePopstate(event) {
        const state = event.state;

        if (state && state.eid) {
            // User navigated back to detail view
            VoucherTaxiAutoOpen.openFromUrl(state.eid);
        } else {
            // User navigated back to list view
            VoucherTaxiAutoOpen.clearUrlState();

            // Close detail modal if open
            if (!document.getElementById('viewVoucherModal')?.classList.contains('hidden')) {
                VoucherTaxiModal.closeView();
            }
        }
    },

    // --------------------------------------------------------
    // PUSH STATE TO HISTORY
    // --------------------------------------------------------
    pushState(eid) {
        const url = VoucherTaxi.routes.show(eid);
        const state = { eid };

        history.pushState(state, '', url);
    },

    // --------------------------------------------------------
    // CLEAR URL STATE
    // --------------------------------------------------------
    clearUrlState() {
        const url = VoucherTaxi.routes.index;
        history.pushState({}, '', url);

        VoucherTaxi.clearDoc();
        VoucherTaxiAutoOpen.state.urlEid = null;
    },

    // --------------------------------------------------------
    // SHOW LOADING INDICATOR
    // --------------------------------------------------------
    showLoadingIndicator() {
        const modalPanel = document.querySelector('#viewVoucherModal .modal-panel');
        if (!modalPanel) return;

        // Create loading overlay
        const overlay = document.createElement('div');
        overlay.id = 'autoOpenLoading';
        overlay.className = 'absolute inset-0 flex items-center justify-center bg-white/50 dark:bg-black/30 z-50 rounded-lg';
        overlay.innerHTML = `
            <div class="flex flex-col items-center gap-2">
                <div class="h-8 w-8 animate-spin rounded-full border-4 border-slate-300 border-t-slate-800 dark:border-slate-700 dark:border-t-slate-200"></div>
                <div class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    Loading voucher...
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
        return VoucherTaxiAutoOpen.extractEidFromUrl();
    },

    // --------------------------------------------------------
    // CHECK IF SHOULD AUTO-OPEN
    // --------------------------------------------------------
    shouldAutoOpen() {
        const eid = VoucherTaxiAutoOpen.extractEidFromUrl();
        return !!eid;
    },

    // --------------------------------------------------------
    // NAVIGATE TO VOUCHER
    // --------------------------------------------------------
    navigateTo(eid) {
        if (!eid) {
            console.error('EID is required');
            return;
        }

        const url = VoucherTaxi.routes.show(eid);
        window.location.href = url;
    },

    // --------------------------------------------------------
    // NAVIGATE TO LIST
    // --------------------------------------------------------
    navigateToList() {
        const url = VoucherTaxi.routes.index;
        window.location.href = url;
    },

    // --------------------------------------------------------
    // HANDLE MODAL CLOSE (update URL)
    // --------------------------------------------------------
    onModalClose() {
        // When detail modal closes, update URL to list view
        VoucherTaxiAutoOpen.clearUrlState();
    },

    // --------------------------------------------------------
    // HANDLE MODAL OPEN (update URL)
    // --------------------------------------------------------
    onModalOpen(eid) {
        // When detail modal opens, update URL with EID
        if (eid) {
            VoucherTaxiAutoOpen.pushState(eid);
        }
    },

    // --------------------------------------------------------
    // GET SHAREABLE LINK
    // --------------------------------------------------------
    getShareableLink(eid) {
        if (!eid) return null;

        const baseUrl = window.location.origin;
        const route = VoucherTaxi.routes.show(eid);

        return baseUrl + route;
    },

    // --------------------------------------------------------
    // COPY LINK TO CLIPBOARD
    // --------------------------------------------------------
    copyLinkToClipboard(eid) {
        const link = VoucherTaxiAutoOpen.getShareableLink(eid);

        if (!link) {
            VoucherTaxi.toast('error', 'Invalid EID');
            return;
        }

        // Copy to clipboard
        navigator.clipboard.writeText(link).then(() => {
            VoucherTaxi.toast('success', 'Link copied to clipboard');
        }).catch(() => {
            VoucherTaxi.toast('error', 'Failed to copy link');
        });
    },

    // --------------------------------------------------------
    // HANDLE DIRECT LINK SHARING
    // --------------------------------------------------------
    shareVoucher(eid) {
        const link = VoucherTaxiAutoOpen.getShareableLink(eid);

        if (!link) {
            VoucherTaxi.toast('error', 'Invalid EID');
            return;
        }

        // Check if Web Share API is available
        if (navigator.share) {
            navigator.share({
                title: 'Voucher Taxi',
                text: `View this voucher: ${VoucherTaxi.state.currentDocid}`,
                url: link,
            }).catch(() => {
                // User cancelled or API not available
                // Fallback to copy
                VoucherTaxiAutoOpen.copyLinkToClipboard(eid);
            });
        } else {
            // Fallback to copy
            VoucherTaxiAutoOpen.copyLinkToClipboard(eid);
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
        VoucherTaxi.toast('error', 'The voucher link is invalid or expired');

        // Redirect to list after 2 seconds
        setTimeout(() => {
            VoucherTaxiAutoOpen.navigateToList();
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
            eid: VoucherTaxiAutoOpen.state.urlEid,
            ...details,
        };

        console.log('[VoucherTaxiAutoOpen]', logEntry);

        // Could be sent to analytics/monitoring service
    },

    // --------------------------------------------------------
    // INITIALIZE WITH DELAY (for page load)
    // --------------------------------------------------------
    initWithDelay(delayMs = 500) {
        // Wait for other modules to be ready
        setTimeout(() => {
            VoucherTaxiAutoOpen.init();
        }, delayMs);
    },
};
