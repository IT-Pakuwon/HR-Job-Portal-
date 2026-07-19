// ============================================================
// view-modal.js — Voucher Product Master
// Handles the View Product detail modal
// ============================================================

const VplMasterViewModal = {

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        // Open: click Doc No button in the table
        $(document).on('click', '.view-product-btn', function () {
            VplMasterViewModal.open($(this).data('hash'));
        });

        // Close: header X button
        document.getElementById('btnCloseViewModal')
            ?.addEventListener('click', () => VplMasterViewModal.close());

        // Close: footer Close button
        document.getElementById('btnCloseViewModalFooter')
            ?.addEventListener('click', () => VplMasterViewModal.close());

        // Close: backdrop click
        document.querySelector('#viewProductModal .modal-backdrop')
            ?.addEventListener('click', () => VplMasterViewModal.close());

        // Edit button — close view, open edit modal
        document.getElementById('btnViewModalEdit')
            ?.addEventListener('click', () => {
                const id = document.getElementById('viewModalProductDbId')?.value;
                if (id) {
                    VplMasterViewModal.close();
                    VplMasterForm.loadEdit(id);
                }
            });

        // Browser back button — close modal if it's open
        window.addEventListener('popstate', (e) => {
            const modal = document.getElementById('viewProductModal');
            if (modal && !modal.classList.contains('hidden')) {
                VplMasterViewModal._closeAnimate();
            } else if (e.state && e.state.vplHash) {
                VplMasterViewModal.open(e.state.vplHash);
            }
        });
    },

    // --------------------------------------------------------
    // OPEN
    // --------------------------------------------------------
    open(hash) {
        const modal = document.getElementById('viewProductModal');
        if (!modal) return;

        // Push URL to /msproduct/{hash}
        const targetUrl = '/msproduct/' + hash;
        if (window.location.pathname !== targetUrl) {
            history.pushState({ vplHash: hash }, '', targetUrl);
        }

        // Reset to loading state
        VplMasterViewModal._setText('viewModal_productId', '...');
        document.getElementById('viewModal_status').innerHTML = '';
        document.getElementById('viewStockBody').innerHTML =
            '<tr><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">Loading...</td></tr>';

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Animate in
        requestAnimationFrame(() => {
            modal.querySelector('.modal-backdrop')?.classList.remove('opacity-0');
            const panel = modal.querySelector('.modal-panel');
            if (panel) {
                panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
            }
        });

        // Fetch data
        $.get(VplMaster.routes.viewJson(hash))
            .done(res => VplMasterViewModal._populate(res))
            .fail(() => {
                VplMaster.toast('error', 'Failed to load product details.');
                VplMasterViewModal.close();
            });
    },

    // --------------------------------------------------------
    // CLOSE
    // --------------------------------------------------------
    close() {
        // Reset URL to /msproduct
        if (window.location.pathname !== '/msproduct') {
            history.replaceState({}, '', '/msproduct');
        }
        VplMasterViewModal._closeAnimate();
    },

    _closeAnimate() {
        const modal = document.getElementById('viewProductModal');
        if (!modal) return;

        modal.querySelector('.modal-backdrop')?.classList.add('opacity-0');
        const panel = modal.querySelector('.modal-panel');
        if (panel) {
            panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 200);
    },

    // --------------------------------------------------------
    // POPULATE
    // --------------------------------------------------------
    _populate(res) {
        const p = res.product;

        // Store id for Edit button
        const idEl = document.getElementById('viewModalProductDbId');
        if (idEl) idEl.value = p.id;

        // Header
        VplMasterViewModal._setText('viewModal_productId', p.product_id);
        const statusHtml = p.status === 'A'
            ? '<span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Active</span>'
            : '<span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Inactive</span>';
        document.getElementById('viewModal_status').innerHTML = statusHtml;

        // Tenant Info
        VplMasterViewModal._setText('viewModal_cpnyid',   p.cpnyid);
        VplMasterViewModal._setText('viewModal_sourcePT',  p.product_source_company);
        VplMasterViewModal._setText('viewModal_tenant',    p.product_source_tenant);

        // Item Info
        VplMasterViewModal._setText('viewModal_productName', p.product_name);
        const typeLabel = p.product_type === 'V' ? 'Voucher'
                        : p.product_type === 'P' ? 'Product' : (p.product_type ?? '-');
        VplMasterViewModal._setText('viewModal_type',       typeLabel);
        VplMasterViewModal._setText('viewModal_category',   p.product_category);
        VplMasterViewModal._setText('viewModal_sourceType', p.product_source_type);
        VplMasterViewModal._setText('viewModal_uom',        p.product_uom);
        VplMasterViewModal._setText('viewModal_value',
            typeof VplMasterHelper !== 'undefined'
                ? VplMasterHelper.formatDisplay(p.product_value)
                : (p.product_value ?? '-'));
        VplMasterViewModal._setText('viewModal_checkExp',   p.product_check_exp == 1 ? 'Yes' : 'No');
        VplMasterViewModal._setText('viewModal_remarks',    p.product_remark || '-');

        // Stock detail table
        const stock = res.stock ?? [];
        let stockHtml = '';
        if (!stock.length) {
            stockHtml = '<tr><td colspan="3" class="px-4 py-6 text-center text-sm text-slate-400">No stock data</td></tr>';
        } else {
            stock.forEach(row => {
                const expRaw = row.expired_date ? String(row.expired_date).substring(0, 10) : '-';
                const exp = expRaw === '1900-01-01' ? 'No Expired' : expRaw;
                stockHtml += `
                    <tr>
                        <td class="px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300">${exp}</td>
                        <td class="px-4 py-2.5 text-sm text-slate-700 dark:text-slate-300">${row.whs_id ?? '-'}</td>
                        <td class="px-4 py-2.5 text-sm font-semibold text-slate-900 dark:text-slate-100">${row.qty_available ?? 0}</td>
                    </tr>`;
            });
        }
        document.getElementById('viewStockBody').innerHTML = stockHtml;

    },

    // --------------------------------------------------------
    // HELPER
    // --------------------------------------------------------
    _setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = (value !== null && value !== undefined && value !== '') ? value : '-';
    },
};
