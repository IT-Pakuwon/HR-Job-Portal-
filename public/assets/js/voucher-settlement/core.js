const VplSettlement = {

    state: {
        currentStatus: 'JOBLIST',
        currentViewId:   null,
        currentViewData: null,
        cAttachIdx: 1,
        eAttachIdx: 1,
    },

    routes: {
        base:         '',
        store:        '',
        jobList:      '',
        usageOptions: '',
        usageLines:   '',
        delAttach:    '',
        data:    (id) => `${VplSettlement.routes.base}/${id}/data`,
        pdf:     (id) => `${VplSettlement.routes.base}/${id}/pdf`,
        update:  (id) => `${VplSettlement.routes.base}/${id}/update`,
        cancel:  (id) => `${VplSettlement.routes.base}/${id}/cancel`,
        approve: (id) => `${VplSettlement.routes.base}/${id}/approve`,
        reject:  (id) => `${VplSettlement.routes.base}/${id}/reject`,
        revise:  (id) => `${VplSettlement.routes.base}/${id}/revise`,
        message: (id) => `${VplSettlement.routes.base}/${id}/message`,
        show:    (eid) => `/showsettlementvp/${eid}`,
    },

    boot(cfg) {
        Object.assign(VplSettlement.routes, cfg);
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

    pushUrl(settlementEid) {
        if (!settlementEid) return;
        const targetUrl = VplSettlement.routes.show(settlementEid);
        if (window.location.pathname === targetUrl) return;
        history.pushState({ settlementEid }, '', targetUrl);
    },

    clearUrl() {
        history.pushState({}, '', VplSettlement.routes.base);
    },
};
