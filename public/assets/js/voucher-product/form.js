// ============================================================
// form.js — Voucher Product Master
// Save (create/edit) and load-for-edit logic
// ============================================================

const VplMasterForm = {

    // --------------------------------------------------------
    // REQUIRED FIELDS FOR VALIDATION
    // --------------------------------------------------------
    requiredFields: [
        { sel: '#cpnyidx',                label: 'Company' },
        { sel: '#product_source_company', label: 'Nama PT' },
        { sel: '#product_source_tenant',  label: 'Nama Tenant / Event' },
        { sel: '#product_name',           label: 'Product Name' },
        { sel: '#product_uom',            label: 'UOM' },
        { sel: '#product_typex',          label: 'Product Type' },
        { sel: '#categoryx',              label: 'Category' },
    ],

    // --------------------------------------------------------
    // VALIDATE
    // --------------------------------------------------------
    validate() {
        for (const { sel, label } of VplMasterForm.requiredFields) {
            const val = $(sel).val();
            if (!val || val.toString().trim() === '') {
                VplMasterForm.showError(`${label} is required.`);
                $(sel).focus();
                return false;
            }
        }
        return true;
    },

    showError(msg) {
        const el = document.getElementById('formError');
        if (!el) return;
        el.textContent = msg;
        el.classList.remove('hidden');
    },

    clearError() {
        const el = document.getElementById('formError');
        if (el) el.classList.add('hidden');
    },

    // --------------------------------------------------------
    // SAVE (create or update)
    // --------------------------------------------------------
    save() {
        VplMasterForm.clearError();
        if (!VplMasterForm.validate()) return;

        const formData = new FormData(document.getElementById('stockForm'));
        // strip commas from value
        formData.set('product_value', VplMasterHelper.unformat($('#product_value').val()));

        const $btn = $('#btnSave').prop('disabled', true).text('Saving...');

        $.ajax({
            url:         VplMaster.routes.save,
            type:        'POST',
            data:        formData,
            contentType: false,
            processData: false,
            headers:     { 'X-CSRF-TOKEN': VplMaster.csrf() },
            success() {
                VplMasterModal.hide();
                VplMasterDatalist.refresh();
                VplMaster.toast('success', 'Product saved successfully.');
            },
            error(xhr) {
                const msg = xhr.responseJSON?.message ?? 'Failed to save. Please try again.';
                VplMasterForm.showError(msg);
            },
            complete() {
                const isEdit = !!document.getElementById('key_id')?.value;
                $btn.prop('disabled', false).text(isEdit ? 'Update' : 'Save');
            },
        });
    },

    // --------------------------------------------------------
    // LOAD FOR EDIT
    // --------------------------------------------------------
    loadEdit(id) {
        $.get(VplMaster.routes.edit(id), function (res) {
            const p        = res.msproduct;
            const hasStock = res.has_stock === true;

            VplMasterModal.reset();
            document.getElementById('modalTitle').textContent = 'Edit Stock';
            document.getElementById('btnSave').textContent    = 'Update';
            document.getElementById('key_id').value           = p.id;

            $('#cpnyidx').val(p.cpnyid).trigger('change');
            $('#product_source_company').val(p.product_source_company);
            $('#product_source_tenant').val(p.product_source_tenant);
            $('#product_name').val(p.product_name);
            $('#product_uom').val(p.product_uom).trigger('change');
            document.getElementById('product_check_exp').checked = (p.product_check_exp == 1);
            $('#product_typex').val(p.product_type).trigger('change');

            // load categories from API then pre-select saved value
            VplMasterModal.loadCategories(p.product_category);
            $('#product_value').val(VplMasterHelper.formatDisplay(p.product_value));
            $('#product_remark').val(p.product_remark);

            // Lock product name if stock exists
            const $name = $('#product_name');
            if (hasStock) {
                $name.prop('readonly', true)
                     .addClass('bg-slate-100 cursor-not-allowed dark:bg-white/[0.04]')
                     .attr('title', 'Cannot change product name while stock exists');
            } else {
                $name.prop('readonly', false)
                     .removeClass('bg-slate-100 cursor-not-allowed dark:bg-white/[0.04]')
                     .removeAttr('title');
            }

            VplMasterModal.show();
        }).fail(() => {
            VplMaster.toast('error', 'Failed to load product data.');
        });
    },

    // --------------------------------------------------------
    // DEACTIVATE — blocked if product still has stock
    // --------------------------------------------------------
    deactivate(id) {
        if (!confirm('Deactivate this product? This action cannot be undone while stock exists.')) return;

        $.ajax({
            url:     VplMaster.routes.deactivate(id),
            type:    'PUT',
            headers: { 'X-CSRF-TOKEN': VplMaster.csrf() },
            success() {
                VplMaster.toast('success', 'Product deactivated.');
                VplMasterDatalist.refresh();
            },
            error(xhr) {
                const msg = xhr.responseJSON?.message ?? 'Failed to deactivate.';
                VplMaster.toast('error', msg);
            },
        });
    },

    // --------------------------------------------------------
    // ACTIVATE
    // --------------------------------------------------------
    activate(id) {
        if (!confirm('Activate this product?')) return;

        $.ajax({
            url:     VplMaster.routes.activate(id),
            type:    'PUT',
            headers: { 'X-CSRF-TOKEN': VplMaster.csrf() },
            success() {
                VplMaster.toast('success', 'Product activated.');
                VplMasterDatalist.refresh();
            },
            error(xhr) {
                const msg = xhr.responseJSON?.message ?? 'Failed to activate.';
                VplMaster.toast('error', msg);
            },
        });
    },

    // --------------------------------------------------------
    // INIT: wire save button + edit/deactivate/activate delegates
    // --------------------------------------------------------
    init() {
        document.getElementById('btnSave')?.addEventListener('click', VplMasterForm.save);

        // edit / deactivate / activate are now handled by VplMasterDatalist.initActionMenu()
    },
};
