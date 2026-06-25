// ============================================================
// core.js — Voucher Product Receive
// Global state, routes (from window.VplReceiveConfig), CSRF, toast
// ============================================================

const VplReceive = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentStatus:   'ALL',
        currentViewId:   null,
        currentViewData: null,
        cRowIdx:         0,
        cAttachIdx:      0,
        eRowIdx:         0,
        eAttachIdx:      0,
    },

    // --------------------------------------------------------
    // ROUTES  — static entries from window.VplReceiveConfig;
    //           dynamic entries built as arrow functions
    // --------------------------------------------------------
    routes: {
        base:       '',
        store:      '',
        products:   '',
        warehouse:  '',
        tenants:    '',
        prodDetail: '',
        delDetail:  '',
        delAttach:  '',

        data:    (id) => `${VplReceive.routes.base}/${id}/data`,
        update:  (id) => `${VplReceive.routes.base}/${id}/update`,
        approve: (id) => `${VplReceive.routes.base}/${id}/approve`,
        reject:  (id) => `${VplReceive.routes.base}/${id}/reject`,
        revise:  (id) => `${VplReceive.routes.base}/${id}/revise`,
        cancel:  (id) => `${VplReceive.routes.base}/${id}/cancel`,
        message: (id) => `${VplReceive.routes.base}/${id}/message`,
    },

    // --------------------------------------------------------
    // CSRF
    // --------------------------------------------------------
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    // --------------------------------------------------------
    // TOAST
    // --------------------------------------------------------
    toast(type, msg) {
        if (typeof toastr !== 'undefined') {
            toastr[type]?.(msg);
        } else {
            console[type === 'error' ? 'error' : 'log'](`[VplReceive] ${msg}`);
        }
    },

    // --------------------------------------------------------
    // BOOT — called from init.js after window.VplReceiveConfig is set
    // --------------------------------------------------------
    boot(config) {
        Object.assign(VplReceive.routes, config);
    },
};
