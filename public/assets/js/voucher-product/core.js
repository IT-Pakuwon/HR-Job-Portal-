// ============================================================
// core.js — Voucher Product Master
// Global state, routes, CSRF, and shared utilities
// ============================================================

const VplMaster = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        mode:        'create',   // 'create' | 'edit'
        currentId:   null,
    },

    // --------------------------------------------------------
    // ROUTES  (matches web.php vpl.* definitions)
    // --------------------------------------------------------
    routes: {
        list:       '/msproduct',
        save:       '/msproduct/save',
        category:   '/msproduct/get-category',
        source:     '/msproduct/get-source',
        edit:       (id)   => `/msproduct/${id}/edit`,
        viewJson:   (hash) => `/msproduct/${hash}/view-json`,
        docIds:     ()     => `/msproduct/get-doc-ids`,
        export:     ()     => `/msproduct/export`,
        deactivate: (id)   => `/msproduct/${id}/deactivate`,
        activate:   (id)   => `/msproduct/${id}/activate`,
    },

    // --------------------------------------------------------
    // CSRF TOKEN
    // --------------------------------------------------------
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    // --------------------------------------------------------
    // TOAST (reuse Toastr if available, otherwise console)
    // --------------------------------------------------------
    toast(type, msg) {
        if (typeof toastr !== 'undefined') {
            toastr[type]?.(msg);
        } else {
            console[type === 'error' ? 'error' : 'log'](`[VplMaster] ${msg}`);
        }
    },
};
