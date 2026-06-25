// ============================================================
// init.js — Voucher Product Receive
// Bootstraps all modules in the correct dependency order
// ============================================================

const VplReceiveInit = {

    init() {
        // 1. Seed route config from blade-injected window variable
        VplReceive.boot(window.VplReceiveConfig ?? {});

        // 2. Toastr defaults
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                closeButton:   true,
                progressBar:   true,
                positionClass: 'toast-top-right',
            };
        }

        // 3. Static Select2 for header-level selects only
        //    (detail-row selects are initialised dynamically in helper.js)
        const s2 = { placeholder: 'Select...', allowClear: true, width: '100%' };
        // Exclude the text input #c_tenant — it is now an <input type="text">
        $('#c_cpnyid, #c_department, #c_vp_type, #c_receive_type, #c_source_dept').select2(s2);
        $('#e_receive_type, #e_source_dept').select2(s2);

        // 4. DataTable + filter pills + row click
        VplReceiveDatalist.init();
        VplReceiveDatalist.initFilterButtons();
        VplReceiveDatalist.initRowClick();

        // 5. View (detail) modal + approval actions
        VplReceiveDetailModal.init();

        // 6. Create and Edit forms
        VplReceiveForm.initCreateModal();
        VplReceiveForm.initEditModal();
    },
};

// ── BOOT ──
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplReceiveInit.init());
} else {
    VplReceiveInit.init();
}
