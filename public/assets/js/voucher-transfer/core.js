const VplTransfer = {

    state: {
        currentStatus: 'ALL',
        currentViewId:   null,
        currentViewData: null,
        cRowIdx: 0,
        eRowIdx: 0,
        cAttachIdx: 1,
        eAttachIdx: 1,
        pendingProductRowIdx: null,
        pendingProductMode:   null,
    },

    routes: {
        base:      '',
        store:     '',
        fromWhs:   '',
        toWhs:     '',
        products:  '',
        refOpts:   '',
        delDetail: '',
        delAttach: '',
        data:    (id) => `${VplTransfer.routes.base}/${id}/data`,
        update:  (id) => `${VplTransfer.routes.base}/${id}/update`,
        cancel:  (id) => `${VplTransfer.routes.base}/${id}/cancel`,
        approve: (id) => `${VplTransfer.routes.base}/${id}/approve`,
        reject:  (id) => `${VplTransfer.routes.base}/${id}/reject`,
        revise:  (id) => `${VplTransfer.routes.base}/${id}/revise`,
        message: (id) => `${VplTransfer.routes.base}/${id}/message`,
        show:    (id) => `${VplTransfer.routes.base}/${id}`,
    },

    boot(cfg) {
        Object.assign(VplTransfer.routes, cfg);
    },

    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    toast(type, msg) {
        toastr[type]?.(msg);
    },

    confirm(opts) {
        return Swal.fire({
            title:              opts.title ?? 'Are you sure?',
            text:               opts.text ?? '',
            icon:               opts.icon ?? 'warning',
            showCancelButton:   true,
            confirmButtonColor: opts.confirmColor ?? '#dc2626',
            cancelButtonColor:  '#6b7280',
            confirmButtonText:  opts.confirmText ?? 'Yes',
        });
    },

    prompt(opts) {
        return Swal.fire({
            title:            opts.title ?? 'Enter reason',
            input:            'textarea',
            inputPlaceholder: opts.placeholder ?? 'Write your reason here...',
            showCancelButton: true,
            inputValidator:   (v) => !v ? 'Reason cannot be empty.' : null,
        });
    },

    pushUrl(transferId) {
        history.pushState({ transferId }, '', VplTransfer.routes.show(transferId));
    },

    clearUrl() {
        history.pushState({}, '', VplTransfer.routes.base);
    },
};
