// ============================================================
// init.js — Voucher Product Master
// Bootstraps all modules in the correct order
// ============================================================

const VplMasterInit = {

    init() {
        // 1. Modal system (select2, open/close)
        VplMasterModal.init();

        // 2. DataTable list + action menu + status filter + filter bar
        VplMasterDatalist.init();
        VplMasterDatalist.initActionMenu();
        VplMasterDatalist.initStatusFilter();
        VplMasterDatalist.initFilterBar();

        // 3. Form (save + edit delegate)
        VplMasterForm.init();

        // 4. View detail modal
        VplMasterViewModal.init();

        // 5. New Stock button
        document.getElementById('btnNewStock')?.addEventListener('click', () => {
            VplMasterModal.reset();
            document.getElementById('modalTitle').textContent = 'New Stock';
            document.getElementById('btnSave').textContent    = 'Save';
            VplMasterModal.show();
        });

        // 6. Auto-open view modal if URL already contains a hash (deep link / page refresh)
        const pathParts = window.location.pathname.replace(/^\//, '').split('/');
        if (pathParts[0] === 'msproduct' && pathParts[1]) {
            VplMasterViewModal.open(pathParts[1]);
        }
    },
};

// ============================================================
// BOOT
// ============================================================
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => VplMasterInit.init());
} else {
    VplMasterInit.init();
}
