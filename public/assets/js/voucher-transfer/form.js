const VplTransferForm = {

    // ------------------------------------------------------------------
    // PRODUCT SEARCH MODAL
    // ------------------------------------------------------------------

    productSearchTable: null,

    openProductSearch(mode, rowIdx) {
        const prefix  = mode === 'create' ? 'c' : 'e';
        const fromWhs = document.querySelector(`#${prefix}_row_${rowIdx} .${prefix}-from-whs-input`)?.value ?? '';

        if (!fromWhs) return; // inline #c_whs_warning banner already informs the user

        VplTransfer.state.pendingProductMode   = mode;
        VplTransfer.state.pendingProductRowIdx = rowIdx;

        const cpnyid  = document.getElementById(`${prefix}_cpnyid`)?.value
                     ?? document.getElementById('c_cpnyid')?.value ?? '';
        const vpType  = document.getElementById(`${prefix}_vp_type`)?.value
                     ?? document.getElementById('c_vp_type')?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value
                       ?? document.getElementById('c_transfertype')?.value ?? '';
        const refId   = document.getElementById(`${prefix}_ref_transfer_id`)?.value ?? '';

        // Destroy existing table
        if (VplTransferForm.productSearchTable) {
            VplTransferForm.productSearchTable.destroy();
            VplTransferForm.productSearchTable = null;
            $('#productSearchTable tbody').empty();
        }

        VplTransferForm.productSearchTable = $('#productSearchTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url:     VplTransfer.routes.products,
                type:    'POST',
                dataSrc: '',
                data: {
                    _token:       VplTransfer.csrf(),
                    cpnyid,
                    vp_type:      vpType,
                    transfertype: transType,
                    from_whs_id:  fromWhs,
                    ref_transfer_id: refId,
                },
            },
            columns: [
                { data: 'product_id',   title: 'Product ID' },
                { data: 'product_name', title: 'Product Name' },
                { data: 'expired_date', title: 'Expired Date', render: (v) => v ? v.substring(0, 10) : '—' },
                {
                    data: 'qty_available', title: 'Stock',
                    className: 'text-right',
                    render: (v) => Number(v ?? 0).toLocaleString(),
                },
                {
                    data: 'qty_reserved', title: 'Reserved',
                    className: 'text-right text-amber-600',
                    render: (v) => Number(v ?? 0).toLocaleString(),
                },
                {
                    data: 'qty_pickable', title: 'Available',
                    className: 'text-right font-semibold text-green-600',
                    render: (v) => Number(v ?? 0).toLocaleString(),
                },
                {
                    data: null, title: '', orderable: false, searchable: false,
                    render: (_, __, row) =>
                        `<button type="button" class="btn-pick-product rounded bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500"
                            data-product-id="${row.product_id}"
                            data-product-name="${(row.product_name ?? '').replace(/"/g, '&quot;')}"
                            data-qty-available="${row.qty_pickable ?? 0}"
                            data-expired-date="${row.expired_date ?? ''}"
                            data-from-whs="${row.whs_id ?? ''}">Select</button>`,
                },
            ],
            pageLength: 10,
        });

        VplTransferForm.showModal('productSearchModal');
    },

    // ------------------------------------------------------------------
    // FROM WHS LOADER
    // ------------------------------------------------------------------

    loadFromWhs(mode, rowIdx) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const cpnyid    = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept      = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value ?? '';

        if (!cpnyid || !vpType || !transType) return;

        $.post(VplTransfer.routes.fromWhs, {
            _token: VplTransfer.csrf(),
            cpnyid, department: dept, vp_type: vpType, transfertype: transType,
        }).done((list) => {
            const row = document.getElementById(`${prefix}_row_${rowIdx}`);
            if (!row) return;

            const input   = row.querySelector(`.${prefix}-from-whs-input`);
            const $sel    = $(row).find(`.${prefix}-from-whs-sel`);
            const isMulti = list.length > 1;

            if (!$sel.data('select2')) {
                $sel.select2({ placeholder: 'Select WHS', allowClear: true, width: '100%' });
            }

            // Always the same select2 dropdown — enabled to pick from when there are
            // multiple candidates, pre-selected & disabled (read-only) when there's just one.
            $sel.empty().append('<option value="">Select WHS</option>');
            list.forEach((w) => $sel.append(new Option(w.whs_id, w.whs_id)));

            if (isMulti) {
                input.value = '';
                $sel.prop('disabled', false).val('').trigger('change.select2');
                VplTransferForm.updateWhsWarning(prefix, '', false);
            } else {
                // Exactly one (or zero) candidate — auto-fill, lock the picker
                const whsId = list[0]?.whs_id ?? '';
                input.value = whsId;
                $sel.prop('disabled', true).val(whsId || '').trigger('change.select2');
                VplTransferForm.updateWhsWarning(prefix, whsId, list.length === 0);
            }
        }).fail(() => VplTransfer.toast('warning', 'Could not load FROM warehouse.'));
    },

    // Show warning and disable submit when the create form has no FROM warehouse resolved yet.
    // noWarehouseRegistered distinguishes "dept truly has 0 candidates" (show banner) from
    // "dept has several candidates but user hasn't picked one yet" (submit stays disabled, no banner).
    updateWhsWarning(prefix, whsId, noWarehouseRegistered = !whsId) {
        if (prefix !== 'c') return;
        const warning   = document.getElementById('c_whs_warning');
        const submitBtn = document.getElementById('submitCreateBtn');
        if (!whsId) {
            if (submitBtn) { submitBtn.disabled = true; submitBtn.classList.add('opacity-50', 'cursor-not-allowed'); }
            warning?.classList.toggle('hidden', !noWarehouseRegistered);
        } else {
            warning?.classList.add('hidden');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.classList.remove('opacity-50', 'cursor-not-allowed'); }
        }
    },

    // Fired when the user picks a warehouse from the FROM WHS dropdown (multi-candidate case)
    onFromWhsChange(mode, selectEl) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const row    = selectEl.closest('tr');
        if (!row) return;
        const idx   = parseInt(row.dataset.idx, 10);
        const whsId = selectEl.value;

        row.querySelector(`.${prefix}-from-whs-input`).value = whsId;
        // This handler only fires from the multi-candidate dropdown, so warehouses ARE
        // registered — never show the "not registered" banner here, even if cleared back to empty.
        VplTransferForm.updateWhsWarning(prefix, whsId, false);

        // Reset the product pick since the FROM warehouse changed
        row.querySelector(`.${prefix}-product-id-input`).value        = '';
        row.querySelector(`.${prefix}-product-display`).textContent   = '— Select —';
        row.querySelector(`.${prefix}-product-display`).title         = '';
        row.querySelector(`.${prefix}-qty-avail-input`).value         = '0';
        row.querySelector(`.${prefix}-qty-avail-display`).textContent = '0';
        row.querySelector(`.${prefix}-exp-input`).value               = '';
        row.querySelector(`.${prefix}-exp-display`).textContent       = '—';

        if (whsId) VplTransferForm.loadToWhs(mode, idx);
    },

    // ------------------------------------------------------------------
    // TO WHS LOADER
    // ------------------------------------------------------------------

    loadToWhs(mode, rowIdx) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const cpnyid    = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept      = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value ?? '';
        const fromWhsEl = document.querySelector(`#${prefix}_row_${rowIdx} .${prefix}-from-whs-input`);
        const fromWhs   = fromWhsEl?.value ?? '';

        if (!cpnyid || !vpType || !transType) return;

        const $sel = $(`#${prefix}_row_${rowIdx} .${prefix}-to-whs-sel`);

        $.post(VplTransfer.routes.toWhs, {
            _token: VplTransfer.csrf(),
            cpnyid, department: dept, vp_type: vpType, transfertype: transType, from_whs_id: fromWhs,
        }).done((list) => {
            $sel.empty().append('<option value="">Select WHS</option>');
            list.forEach((w) => {
                $sel.append(new Option(`${w.whs_id}${w.department_id ? ' (' + w.department_id + ')' : ''}`, w.whs_id));
            });
            if (!$sel.data('select2')) {
                $sel.select2({ placeholder: 'Select WHS', allowClear: true, width: '100%' });
            } else {
                $sel.trigger('change');
            }
        }).fail(() => VplTransfer.toast('warning', 'Could not load TO warehouse.'));
    },

    // ------------------------------------------------------------------
    // REF OPTIONS (for ReturnTf)
    // ------------------------------------------------------------------

    loadRefOptions(mode) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const cpnyid    = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept      = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value ?? '';

        if (transType !== 'ReturnTf') return;

        const $sel = $(`#${prefix}_ref_transfer_id`);
        $sel.empty().append('<option value="">Select Reference...</option>');

        $.post(VplTransfer.routes.refOpts, {
            _token: VplTransfer.csrf(),
            cpnyid, department: dept, vp_type: vpType, transfertype: transType,
        }).done((refs) => {
            refs.forEach((r) => $sel.append(new Option(r, r)));
            $sel.trigger('change.select2');
        }).fail(() => VplTransfer.toast('warning', 'Could not load reference options.'));
    },

    // ------------------------------------------------------------------
    // RELOAD all existing rows after a header field changes
    // ------------------------------------------------------------------

    reloadAllRows(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const bodyId = `${prefix}_detailBody`;
        const rows   = document.querySelectorAll(`#${bodyId} tr[id^="${prefix}_row_"]`);
        rows.forEach((row) => {
            const idx = parseInt(row.dataset.idx, 10);
            VplTransferForm.loadFromWhs(mode, idx);
            VplTransferForm.loadToWhs(mode, idx);
            // Reset product selection in that row
            row.querySelector(`.${prefix}-product-id-input`).value = '';
            row.querySelector(`.${prefix}-product-display`).textContent = '— Select —';
            row.querySelector(`.${prefix}-qty-avail-input`).value = '0';
            row.querySelector(`.${prefix}-qty-avail-display`).textContent = '0';
            row.querySelector(`.${prefix}-exp-input`).value = '';
            row.querySelector(`.${prefix}-exp-display`).textContent = '—';
        });
    },

    // ------------------------------------------------------------------
    // ADD ROW
    // ------------------------------------------------------------------

    addRow(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const key    = mode === 'create' ? 'cRowIdx' : 'eRowIdx';
        VplTransfer.state[key]++;
        const idx = VplTransfer.state[key];
        const html = VplTransferHelper.buildDetailRow(prefix, idx);

        document.getElementById(`${prefix}_detailBody`).insertAdjacentHTML('beforeend', html);

        // Load from whs & to whs for this new row
        VplTransferForm.loadFromWhs(mode, idx);
        VplTransferForm.loadToWhs(mode, idx);
    },

    // ------------------------------------------------------------------
    // CREATE MODAL
    // ------------------------------------------------------------------

    initCreateModal() {
        const modal = document.getElementById('createModal');

        // First row
        document.getElementById('c_detailBody').insertAdjacentHTML('beforeend',
            VplTransferHelper.buildDetailRow('c', 0));

        document.getElementById('openCreateBtn').addEventListener('click', () => {
            VplTransferForm.showModal('createModal');
            // Trigger initial FROM/TO WHS load so the warning banner shows immediately
            setTimeout(() => VplTransferForm.reloadAllRows('create'), 50);
        });

        ['closeCreateModal', 'closeCreateModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplTransferForm.hideModal('createModal'));
        });

        // Add row
        document.getElementById('c_addRow').addEventListener('click', () => VplTransferForm.addRow('create'));

        // Add attachment
        document.getElementById('c_addAttach').addEventListener('click', () => {
            const html = VplTransferHelper.buildAttachRow('c', VplTransfer.state.cAttachIdx++);
            document.getElementById('c_attachBody').insertAdjacentHTML('beforeend', html);
        });

        // Remove row (delegated)
        document.getElementById('c_detailBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-remove-row-btn');
            if (btn) {
                document.getElementById(`c_row_${btn.dataset.idx}`)?.remove();
            }
        });

        // Remove attachment (delegated)
        document.getElementById('c_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-remove-attach-btn');
            if (btn) {
                document.getElementById(`c_attach_${btn.dataset.idx}`)?.remove();
            }
        });

        // Header selects reload — use jQuery .on() so Select2 .trigger('change') is captured
        $('#c_cpnyid, #c_department, #c_vp_type').on('change', () => {
            VplTransferForm.reloadAllRows('create');
        });

        $('#c_transfertype').on('change', function () {
            const isReturn = $(this).val() === 'ReturnTf';
            document.getElementById('c_ref_wrapper').classList.toggle('hidden', !isReturn);
            VplTransferForm.reloadAllRows('create');
            if (isReturn) VplTransferForm.loadRefOptions('create');
        });

        // Pick product (delegated) — opens product search modal
        document.getElementById('c_detailBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-pick-product-btn');
            if (btn) VplTransferForm.openProductSearch('create', parseInt(btn.dataset.idx, 10));
        });

        // FROM WHS dropdown change (only visible when a dept/vp_type has >1 assigned warehouse)
        $('#c_detailBody').on('change', '.c-from-whs-sel', function () {
            VplTransferForm.onFromWhsChange('create', this);
        });

        // Submit
        document.getElementById('submitCreateBtn').addEventListener('click', () => VplTransferForm.submitCreate());
    },

    submitCreate() {
        const form        = document.getElementById('createForm');
        const transType   = document.getElementById('c_transfertype')?.value ?? '';
        const refTransfer = document.getElementById('c_ref_transfer_id')?.value ?? '';

        if (transType === 'ReturnTf' && !refTransfer) {
            VplTransfer.toast('error', 'Please select a Reference Transfer for Return Transfer.');
            return;
        }

        const fd = new FormData(form);

        $.ajax({
            url:         VplTransfer.routes.store,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplTransfer.toast('success', r.success ?? 'Saved!');
            VplTransferForm.hideModal('createModal');
            VplTransferDatalist.refresh();
            VplTransferForm.resetCreateModal();
        })
        .fail((x) => {
            VplTransfer.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    resetCreateModal() {
        document.getElementById('createForm').reset();
        const body = document.getElementById('c_detailBody');
        body.innerHTML = '';
        VplTransfer.state.cRowIdx = 0;
        body.insertAdjacentHTML('beforeend', VplTransferHelper.buildDetailRow('c', 0));
        VplTransferForm.loadFromWhs('create', 0);
        VplTransferForm.loadToWhs('create', 0);
        document.getElementById('c_attachBody').innerHTML = `
            <tr id="c_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplTransfer.state.cAttachIdx = 1;
        document.getElementById('c_ref_wrapper').classList.add('hidden');
    },

    // ------------------------------------------------------------------
    // EDIT MODAL
    // ------------------------------------------------------------------

    openEdit(data) {
        const t = data.transfer;
        VplTransfer.state.currentViewId = t.id;

        // Read-only fields
        document.getElementById('e_cpnyid_display').value         = t.cpnyid;
        document.getElementById('e_cpnyid').value                  = t.cpnyid;
        document.getElementById('e_dept_display').value            = t.department;
        document.getElementById('e_dept').value                    = t.department;
        document.getElementById('e_vp_type_display').value         = t.vp_type === 'V' ? 'Voucher' : 'Product';
        document.getElementById('e_vp_type').value                 = t.vp_type;
        document.getElementById('e_transfertype_display').value    = data.transfer_type_label;
        document.getElementById('e_transfertype').value            = t.transfertype;
        document.getElementById('e_remark').value                  = t.transfer_remark ?? '';
        document.getElementById('e_title').textContent             = `Edit Transfer — ${t.transfer_id}`;

        // Reference field
        const refWrap = document.getElementById('e_ref_display_wrapper');
        if (t.ref_transfer_id) {
            document.getElementById('e_ref_display').value      = t.ref_transfer_id;
            document.getElementById('e_ref_transfer_id').value  = t.ref_transfer_id;
            refWrap.classList.remove('hidden');
        } else {
            refWrap.classList.add('hidden');
        }

        // Existing details
        const existBody = document.getElementById('e_existDetailBody');
        existBody.innerHTML = '';
        (data.details ?? []).forEach((d) => {
            const exp = (d.expired_date ?? '').substring(0, 10);
            const expDisplay = (exp === '' || exp === '1900-01-01') ? '—' : exp;
            existBody.insertAdjacentHTML('beforeend', `
                <tr data-detail-id="${d.id}">
                    <td class="px-4 py-2">
                        <div class="text-sm font-medium text-slate-800 dark:text-white">${d.product_id}</div>
                        <div class="text-xs text-slate-500">${d.product_name ?? ''}</div>
                    </td>
                    <td class="px-4 py-2 text-xs">${d.from_whs_id ?? ''}</td>
                    <td class="px-4 py-2 text-xs">${d.to_whs_id ?? ''}</td>
                    <td class="px-4 py-2 text-xs">${expDisplay}</td>
                    <td class="px-4 py-2 text-right text-xs">${Number(d.qty_available ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-right text-xs font-semibold">${Number(d.qty_transfer ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-center">
                        <button type="button" class="e-del-exist-detail text-red-400 hover:text-red-600"
                            data-detail-id="${d.id}">
                            <i class="fa-solid fa-trash-can text-sm"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        // Existing attachments
        const eAttachBody = document.getElementById('e_existAttachBody');
        eAttachBody.innerHTML = '';
        (data.attachments ?? []).forEach((a) => {
            const url = `/attachment/${a.year ?? new Date().getFullYear()}/${a.attachfile}`;
            eAttachBody.insertAdjacentHTML('beforeend', `
                <div class="flex items-center justify-between px-4 py-2" data-attach-id="${a.id}">
                    <a href="${url}" target="_blank" class="text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                        <i class="fa-regular fa-file mr-1 text-xs"></i>${a.name ?? a.attachfile}
                    </a>
                    <button type="button" class="e-del-exist-attach text-red-400 hover:text-red-600 ml-4"
                        data-attach-id="${a.id}">
                        <i class="fa-solid fa-trash-can text-xs"></i>
                    </button>
                </div>
            `);
        });

        // Reset new rows
        const newDetailBody = document.getElementById('e_detailBody');
        newDetailBody.innerHTML = '';
        VplTransfer.state.eRowIdx = 0;
        newDetailBody.insertAdjacentHTML('beforeend', VplTransferHelper.buildDetailRow('e', 0));
        VplTransferForm.loadFromWhs('edit', 0);
        VplTransferForm.loadToWhs('edit', 0);

        VplTransferForm.showModal('editModal');
    },

    initEditModal() {
        const modal = document.getElementById('editModal');

        ['closeEditModal', 'closeEditModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplTransferForm.hideModal('editModal'));
        });

        // Add row
        document.getElementById('e_addRow').addEventListener('click', () => VplTransferForm.addRow('edit'));

        // Add attachment
        document.getElementById('e_addAttach').addEventListener('click', () => {
            const html = VplTransferHelper.buildAttachRow('e', VplTransfer.state.eAttachIdx++);
            document.getElementById('e_attachBody').insertAdjacentHTML('beforeend', html);
        });

        // Remove new row (delegated)
        document.getElementById('e_detailBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.e-remove-row-btn');
            if (btn) document.getElementById(`e_row_${btn.dataset.idx}`)?.remove();
        });

        // Remove new attachment (delegated)
        document.getElementById('e_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.e-remove-attach-btn');
            if (btn) document.getElementById(`e_attach_${btn.dataset.idx}`)?.remove();
        });

        // Delete existing detail (delegated)
        document.getElementById('e_existDetailBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.e-del-exist-detail');
            if (!btn) return;
            const res = await VplTransfer.confirm({ title: 'Remove this detail line?', confirmText: 'Remove' });
            if (!res.isConfirmed) return;
            const detailId = btn.dataset.detailId;
            $.post(VplTransfer.routes.delDetail, { _token: VplTransfer.csrf(), detail_id: detailId })
                .done(() => {
                    btn.closest('tr')?.remove();
                    VplTransfer.toast('success', 'Detail removed.');
                })
                .fail(() => VplTransfer.toast('error', 'Remove failed.'));
        });

        // Delete existing attachment (delegated)
        document.getElementById('e_existAttachBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.e-del-exist-attach');
            if (!btn) return;
            const res = await VplTransfer.confirm({ title: 'Remove this attachment?', confirmText: 'Remove' });
            if (!res.isConfirmed) return;
            const attachId = btn.dataset.attachId;
            $.post(VplTransfer.routes.delAttach, { _token: VplTransfer.csrf(), detail_id: attachId })
                .done(() => {
                    btn.closest('[data-attach-id]')?.remove();
                    VplTransfer.toast('success', 'Attachment removed.');
                })
                .fail(() => VplTransfer.toast('error', 'Remove failed.'));
        });

        // Pick product (delegated)
        document.getElementById('e_detailBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.e-pick-product-btn');
            if (btn) VplTransferForm.openProductSearch('edit', parseInt(btn.dataset.idx, 10));
        });

        // FROM WHS dropdown change (only visible when a dept/vp_type has >1 assigned warehouse)
        $('#e_detailBody').on('change', '.e-from-whs-sel', function () {
            VplTransferForm.onFromWhsChange('edit', this);
        });

        // Submit
        document.getElementById('submitEditBtn').addEventListener('click', () => VplTransferForm.submitEdit());
    },

    submitEdit() {
        const id   = VplTransfer.state.currentViewId;
        const form = document.getElementById('editForm');
        const fd   = new FormData(form);
        fd.append('_method', 'POST');

        $.ajax({
            url:         VplTransfer.routes.update(id),
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplTransfer.toast('success', r.success ?? 'Resubmitted!');
            VplTransferForm.hideModal('editModal');
            VplTransferDatalist.refresh();
        })
        .fail((x) => {
            VplTransfer.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    // ------------------------------------------------------------------
    // PRODUCT SEARCH MODAL events
    // ------------------------------------------------------------------

    initProductSearchModal() {
        document.getElementById('closeProductSearchModal')?.addEventListener('click', () => {
            VplTransferForm.hideModal('productSearchModal');
        });
        document.getElementById('productSearchModal')?.querySelector('.modal-backdrop')
            ?.addEventListener('click', () => VplTransferForm.hideModal('productSearchModal'));

        // Row select (delegated on table)
        $('#productSearchTable').on('click', '.btn-pick-product', function () {
            const productId   = this.dataset.productId;
            const productName = this.dataset.productName;
            const qtyAvail    = this.dataset.qtyAvailable;
            const expDate     = this.dataset.expiredDate;
            const fromWhs     = this.dataset.fromWhs;

            const mode   = VplTransfer.state.pendingProductMode;
            const rowIdx = VplTransfer.state.pendingProductRowIdx;
            const prefix = mode === 'create' ? 'c' : 'e';
            const row    = document.getElementById(`${prefix}_row_${rowIdx}`);
            if (!row) return;

            row.querySelector(`.${prefix}-product-id-input`).value              = productId;
            row.querySelector(`.${prefix}-product-display`).textContent         = productName;
            row.querySelector(`.${prefix}-product-display`).title               = productName;
            row.querySelector(`.${prefix}-qty-avail-input`).value               = qtyAvail;
            row.querySelector(`.${prefix}-qty-avail-display`).textContent       = Number(qtyAvail).toLocaleString();
            row.querySelector(`.${prefix}-exp-input`).value                     = expDate;
            row.querySelector(`.${prefix}-exp-display`).textContent             = expDate ? expDate.substring(0, 10) : '—';

            // If from_whs came back from product search, fill it
            if (fromWhs) {
                row.querySelector(`.${prefix}-from-whs-input`).value = fromWhs;
                $(row).find(`.${prefix}-from-whs-sel`).val(fromWhs).trigger('change.select2');
                VplTransferForm.loadToWhs(mode, rowIdx);
            }

            VplTransferForm.hideModal('productSearchModal');
        });
    },

    // ------------------------------------------------------------------
    // MODAL OPEN / CLOSE HELPERS
    // ------------------------------------------------------------------

    showModal(modalId) {
        const modal    = document.getElementById(modalId);
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
        });
    },

    hideModal(modalId) {
        const modal    = document.getElementById(modalId);
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modalId === 'createModal') VplTransfer.clearUrl();
        }, 200);
    },
};
