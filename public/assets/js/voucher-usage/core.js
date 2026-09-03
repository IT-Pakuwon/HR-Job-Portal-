const VplUsage = {

    state: {
        currentStatus: 'P',
        currentViewId:   null,
        currentViewData: null,
        cRowIdx: 0,
        eRowIdx: 0,
        cAttachIdx: 1,
        eAttachIdx: 1,
    },

    routes: {
        base:       '',
        store:      '',
        warehouse:  '',
        products:   '',
        fefoPick:   '',
        refOpts:    '',
        refDetails: '',
        delDetail:  '',
        delAttach:  '',
        data:    (id) => `${VplUsage.routes.base}/${id}/data`,
        pdf:     (id) => `${VplUsage.routes.base}/${id}/pdf`,
        update:  (id) => `${VplUsage.routes.base}/${id}/update`,
        cancel:  (id) => `${VplUsage.routes.base}/${id}/cancel`,
        approve: (id) => `${VplUsage.routes.base}/${id}/approve`,
        reject:  (id) => `${VplUsage.routes.base}/${id}/reject`,
        revise:  (id) => `${VplUsage.routes.base}/${id}/revise`,
        message: (id) => `${VplUsage.routes.base}/${id}/message`,
        show:    (eid) => `/showusagevp/${eid}`,
    },

    boot(cfg) {
        Object.assign(VplUsage.routes, cfg);
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

    pushUrl(usageEid) {
        if (!usageEid) return;
        const targetUrl = VplUsage.routes.show(usageEid);
        if (window.location.pathname === targetUrl) return;
        history.pushState({ usageEid }, '', targetUrl);
    },

    clearUrl() {
        history.pushState({}, '', VplUsage.routes.base);
    },
};
