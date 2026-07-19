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

        $('#c_picker_product').select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#c_addProductModal') });
        $('#e_picker_product').select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $('#e_addProductModal') });

        VplUsageDatalist.init();
        VplUsageDatalist.initFilterButtons();
        VplUsageDatalist.initRowClick();

        VplUsageDetailModal.init();

        VplUsageForm.initCreateModal();
        VplUsageForm.initEditModal();

        // Deep-link: open view modal if page was loaded via /showusagevp/{eid}
        if (window.VplUsageConfig?.initialId) {
            VplUsageDetailModal.open(window.VplUsageConfig.initialId);
        }

        window.addEventListener('popstate', (e) => {
            if (e.state?.usageEid) {
                VplUsageDetailModal.open(VplUsage.state.currentViewId);
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
    document.addEventListener('DOMContentLoaded', () => VplUsageInit.init());
} else {
    VplUsageInit.init();
}
