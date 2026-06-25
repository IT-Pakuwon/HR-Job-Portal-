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
        editBase:   '',
        store:      '',
        products:   '',
        warehouse:  '',
        tenants:    '',
        prodDetail: '',
        delDetail:  '',
        delAttach:  '',
        index:      '/requestvp',

        data:    (id)  => `${VplReceive.routes.base}/${id}/data`,
        update:  (id)  => `${VplReceive.routes.editBase}/${id}`,
        approve: (id)  => `${VplReceive.routes.base}/${id}/approve`,
        reject:  (id)  => `${VplReceive.routes.base}/${id}/reject`,
        revise:  (id)  => `${VplReceive.routes.base}/${id}/revise`,
        cancel:  (id)  => `${VplReceive.routes.base}/${id}/cancel`,
        message: (id)  => `${VplReceive.routes.base}/${id}/message`,
        show:    (rid) => `/showreceivevp/${rid}`,
    },

    // --------------------------------------------------------
    // URL STATE  — push / clear receive ID in the address bar
    // --------------------------------------------------------
    pushUrl(receiveId) {
        if (!receiveId) return;
        history.pushState({ receiveId }, '', VplReceive.routes.show(receiveId));
    },

    clearUrl() {
        history.pushState({}, '', VplReceive.routes.index);
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
    // SWAL SHORTCUTS  (mirrors VoucherTaxi.confirm / .prompt)
    // --------------------------------------------------------
    confirm(opts = {}) {
        return Swal.fire({
            title:              opts.title        ?? 'Are you sure?',
            text:               opts.text         ?? '',
            icon:               opts.icon         ?? 'question',
            showCancelButton:   true,
            confirmButtonText:  opts.confirmText  ?? 'Yes',
            cancelButtonText:   opts.cancelText   ?? 'Cancel',
            confirmButtonColor: opts.confirmColor ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor  ?? '#94a3b8',
            reverseButtons:     true,
        });
    },

    prompt(opts = {}) {
        return Swal.fire({
            title:              opts.title        ?? 'Input required',
            input:              opts.input        ?? 'textarea',
            inputPlaceholder:   opts.placeholder  ?? '',
            inputLabel:         opts.label        ?? '',
            showCancelButton:   true,
            confirmButtonText:  opts.confirmText  ?? 'Submit',
            cancelButtonText:   opts.cancelText   ?? 'Cancel',
            confirmButtonColor: opts.confirmColor ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor  ?? '#94a3b8',
            reverseButtons:     true,
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return opts.validationMsg ?? 'This field is required.';
                }
            },
        });
    },

    // --------------------------------------------------------
    // BOOT — called from init.js after window.VplReceiveConfig is set
    // --------------------------------------------------------
    boot(config) {
        Object.assign(VplReceive.routes, config);
    },
};
