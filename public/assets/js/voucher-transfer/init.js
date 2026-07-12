const VplTransferInit = {

    init() {
        // Boot route config from blade
        const cfg = window.VplTransferConfig ?? {};
        VplTransfer.boot(cfg);

        // Toastr defaults
        toastr.options = {
            closeButton:       true,
            progressBar:       true,
            positionClass:     'toast-top-right',
            timeOut:           4000,
            extendedTimeOut:   1000,
            preventDuplicates: true,
        };

        // Select2 on create modal header selects
        ['#c_cpnyid', '#c_department', '#c_vp_type', '#c_transfertype', '#c_ref_transfer_id'].forEach((sel) => {
            $(sel).select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#createModal') });
        });

        // DataTable + events
        VplTransferDatalist.init();
        VplTransferDatalist.initFilterButtons();
        VplTransferDatalist.initRowClick();

        // View detail modal
        VplTransferDetailModal.init();

        // Forms
        VplTransferForm.initCreateModal();
        VplTransferForm.initEditModal();
        VplTransferForm.initProductSearchModal();

        // Deep-link: open view modal if page was loaded via /showtransfervp/{eid}
        if (window.VplTransferConfig?.initialId) {
            VplTransferDetailModal.open(window.VplTransferConfig.initialId);
        }

        // Browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state?.transferEid) {
                VplTransferDetailModal.open(VplTransfer.state.currentViewId);
            } else {
                // Close without pushing a new history entry (we're already reacting to one)
                const modal = document.getElementById('viewModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplTransferInit.init());
} else {
    VplTransferInit.init();
}
