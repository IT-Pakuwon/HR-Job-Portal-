const VplSettlementInit = {

    init() {
        const cfg = window.VplSettlementConfig ?? {};
        VplSettlement.boot(cfg);

        toastr.options = {
            closeButton:       true,
            progressBar:       true,
            positionClass:     'toast-top-right',
            timeOut:           4000,
            extendedTimeOut:   1000,
            preventDuplicates: true,
        };

        ['#c_cpnyid', '#c_department', '#c_vp_type', '#c_usage_id'].forEach((sel) => {
            $(sel).select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#createModal') });
        });

        VplSettlementDatalist.init();
        VplSettlementDatalist.initFilterButtons();
        VplSettlementDatalist.initRowClick();

        VplSettlementDetailModal.init();

        VplSettlementForm.initCreateModal();
        VplSettlementForm.initEditModal();

        // Deep-link: open view modal if page was loaded via /showsettlementvp/{eid}
        if (window.VplSettlementConfig?.initialId) {
            VplSettlementDetailModal.open(window.VplSettlementConfig.initialId);
        }

        window.addEventListener('popstate', (e) => {
            if (e.state?.settlementEid) {
                VplSettlementDetailModal.open(VplSettlement.state.currentViewId);
            } else {
                const modal = document.getElementById('viewModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplSettlementInit.init());
} else {
    VplSettlementInit.init();
}
