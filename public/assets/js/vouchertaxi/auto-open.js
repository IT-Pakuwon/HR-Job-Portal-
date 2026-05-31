// ============================================================
// auto-open.js — Voucher Taxi
// Auto-open modals based on URL path (/showvouchertaxi/:eid)
// ============================================================

const VoucherTaxiAutoOpen = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        opened: false,
    },

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VoucherTaxiAutoOpen.checkPath();
        VoucherTaxiAutoOpen.checkParams();
    },

    // --------------------------------------------------------
    // CHECK PATH — /showvouchertaxi/:eid
    // --------------------------------------------------------
    checkPath() {
        const match = window.location.pathname.match(/\/showvouchertaxi\/([^/]+)/);
        if (!match) return;

        const eid = match[1];
        if (!eid) return;

        setTimeout(() => {
            VoucherTaxiDetailModal.load(eid);
            VoucherTaxiAutoOpen.state.opened = true;
        }, 400);
    },

    // --------------------------------------------------------
    // CHECK QUERY PARAMS — ?action=create / ?view=:eid
    // --------------------------------------------------------
    checkParams() {
        const params = new URLSearchParams(window.location.search);

        if (params.get('action') === 'create') {
            setTimeout(() => VoucherTaxiModal.openCreate(), 400);
        }

        const view = params.get('view');
        if (view && !VoucherTaxiAutoOpen.state.opened) {
            setTimeout(() => VoucherTaxiDetailModal.load(view), 400);
        }

        // Clean URL after reading
        if (params.toString() && window.history?.replaceState) {
            window.history.replaceState({}, '', window.location.pathname);
        }
    },

    // --------------------------------------------------------
    // CALLED WHEN DETAIL MODAL CLOSES (from init integrations)
    // --------------------------------------------------------
    onModalClose() {
        if (window.location.pathname.includes('/showvouchertaxi/')) {
            window.history.replaceState({}, '', VoucherTaxi.routes.index);
        }
    },
};
