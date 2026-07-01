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

        // Deep-link: open view modal if URL has /transfervp/{id}
        const pathParts = window.location.pathname.replace(/^\//, '').split('/');
        if (pathParts[0] === 'transfervp' && pathParts[1] && !isNaN(pathParts[1])) {
            VplTransferDetailModal.open(parseInt(pathParts[1], 10));
        }

        // Browser back/forward
        window.addEventListener('popstate', (e) => {
            if (e.state?.transferId) {
                VplTransferDetailModal.open(e.state.transferId);
            }
        });
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplTransferInit.init());
} else {
    VplTransferInit.init();
}
