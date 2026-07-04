const VplUsageInit = {

    init() {
        const cfg = window.VplUsageConfig ?? {};
        VplUsage.boot(cfg);

        toastr.options = {
            closeButton:       true,
            progressBar:       true,
            positionClass:     'toast-top-right',
            timeOut:           4000,
            extendedTimeOut:   1000,
            preventDuplicates: true,
        };

        ['#c_cpnyid', '#c_department', '#c_vp_type', '#c_usagetype', '#c_ref_usage_id', '#c_whs_id'].forEach((sel) => {
            $(sel).select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#createModal') });
        });

        $('#e_whs_id').select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#editModal') });

        VplUsageDatalist.init();
        VplUsageDatalist.initFilterButtons();
        VplUsageDatalist.initRowClick();

        VplUsageDetailModal.init();

        VplUsageForm.initCreateModal();
        VplUsageForm.initEditModal();
        VplUsageForm.initProductSearchModal();

        // Deep-link: open view modal if URL has /usagevp/{id}
        const pathParts = window.location.pathname.replace(/^\//, '').split('/');
        if (pathParts[0] === 'usagevp' && pathParts[1] && !isNaN(pathParts[1])) {
            VplUsageDetailModal.open(parseInt(pathParts[1], 10));
        }

        window.addEventListener('popstate', (e) => {
            if (e.state?.usageId) {
                VplUsageDetailModal.open(e.state.usageId);
            }
        });
    },
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplUsageInit.init());
} else {
    VplUsageInit.init();
}
