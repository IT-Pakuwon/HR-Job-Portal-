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
                { data: 'expired_date', title: 'Expired Date', render: (v) => VplTransferHelper.formatExpDate(v) ?? '—' },
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
        // Edit modal's department field is a read-only mirror with id `e_dept`
        // (not `e_department` like the create modal's live select) — resolve per mode.
        const dept      = document.getElementById(prefix === 'c' ? 'c_department' : 'e_dept')?.value ?? '';
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
        // Edit modal's department field is a read-only mirror with id `e_dept`
        // (not `e_department` like the create modal's live select) — resolve per mode.
        const dept      = document.getElementById(prefix === 'c' ? 'c_department' : 'e_dept')?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value ?? '';
        const fromWhsEl = document.querySelector(`#${prefix}_row_${rowIdx} .${prefix}-from-whs-input`);
        const fromWhs   = fromWhsEl?.value ?? '';

        if (!cpnyid || !vpType || !transType) return;

        const row   = document.getElementById(`${prefix}_row_${rowIdx}`);
        const $sel  = $(row).find(`.${prefix}-to-whs-sel`);
        const input = row?.querySelector(`.${prefix}-to-whs-input`);

        $.post(VplTransfer.routes.toWhs, {
            _token: VplTransfer.csrf(),
            cpnyid, department: dept, vp_type: vpType, transfertype: transType, from_whs_id: fromWhs,
        }).done((list) => {
            if (!$sel.data('select2')) {
                $sel.select2({ placeholder: 'Select WHS', allowClear: true, width: '100%' });
            }

            $sel.empty().append('<option value="">Select WHS</option>');
            list.forEach((w) => {
                $sel.append(new Option(`${w.whs_id}${w.department_id ? ' (' + w.department_id + ')' : ''}`, w.whs_id));
            });

            // Exactly one candidate — auto-fill, lock the picker; otherwise let the user pick via select2.
            // The select carries no `name` itself (a disabled <select> is dropped from FormData), so the
            // hidden input is what actually gets submitted — keep it mirrored to the select's value.
            if (list.length === 1) {
                if (input) input.value = list[0].whs_id;
                $sel.prop('disabled', true).val(list[0].whs_id).trigger('change.select2');
            } else {
                if (input) input.value = '';
                $sel.prop('disabled', false).val('').trigger('change.select2');
            }
        }).fail(() => VplTransfer.toast('warning', 'Could not load TO warehouse.'));
    },

    // ------------------------------------------------------------------
    // REF OPTIONS (for ReturnTf)
    // ------------------------------------------------------------------

    loadRefOptions(mode, selectedRef = null) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const cpnyid    = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        // Edit modal's department field is a read-only mirror with id `e_dept`
        // (not `e_department` like the create modal's live select) — resolve per mode.
        const dept      = document.getElementById(prefix === 'c' ? 'c_department' : 'e_dept')?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const transType = document.getElementById(`${prefix}_transfertype`)?.value ?? '';

        if (transType !== 'ReturnTf') return;

        const $sel = $(`#${prefix}_ref_transfer_id`);
        $sel.empty().append('<option value="">Select Reference...</option>');
        if (selectedRef) {
            $sel.append(new Option(selectedRef, selectedRef)).val(selectedRef);
        }
        $sel.data('current-ref', selectedRef || '');
        $sel.trigger('change.select2');

        return $.post(VplTransfer.routes.refOpts, {
            _token: VplTransfer.csrf(),
            cpnyid, department: dept, vp_type: vpType, transfertype: transType,
        }).done((refs) => {
            $sel.empty().append('<option value="">Select Reference...</option>');
            refs.forEach((r) => $sel.append(new Option(r, r)));
            if (selectedRef && !refs.includes(selectedRef)) {
                $sel.append(new Option(selectedRef, selectedRef));
            }
            $sel.val(selectedRef || '');
            $sel.data('current-ref', selectedRef || '');
            $sel.trigger('change.select2');
        }).fail(() => VplTransfer.toast('warning', 'Could not load reference options.'));
    },

    // ------------------------------------------------------------------
    // RETURN TRANSFER — auto-populate detail rows from the reference transfer
    // ------------------------------------------------------------------

    // Replaces the detail table with one row per still-returnable line from the chosen
    // reference transfer. excludeTransferId (edit mode) is the DB id of the document
    // being resubmitted, so its own already-claimed qty doesn't shrink its own options.
    loadRefDetails(mode, excludeTransferId = null) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const key    = mode === 'create' ? 'cRowIdx' : 'eRowIdx';
        const refId  = document.getElementById(`${prefix}_ref_transfer_id`)?.value ?? '';
        const body   = document.getElementById(`${prefix}_detailBody`);

        body.innerHTML = '';
        VplTransfer.state[key] = -1;

        if (!refId) return;

        $.post(VplTransfer.routes.refDetails, {
            _token: VplTransfer.csrf(),
            ref_transfer_id: refId,
            exclude_transfer_id: excludeTransferId,
        }).done((lines) => {
            lines.forEach((line) => {
                VplTransfer.state[key]++;
                body.insertAdjacentHTML('beforeend', VplTransferHelper.buildRefDetailRow(prefix, VplTransfer.state[key], line));
            });
            if (lines.length === 0) {
                VplTransfer.toast('warning', 'Nothing left to return on this reference transfer.');
            }
        }).fail(() => VplTransfer.toast('warning', 'Could not load reference transfer details.'));
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
            const qtyInput = row.querySelector(`.${prefix}-qty-transfer-input`);
            if (qtyInput) qtyInput.value = '';
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
            document.getElementById(id)?.addEventListener('click', () => {
                VplTransferForm.hideModal('createModal', () => VplTransferForm.resetCreateModal());
            });
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
            if (document.getElementById('c_transfertype')?.value === 'ReturnTf') {
                // Ref options depend on cpny/dept/vp_type — the previous pick may no longer be valid
                $('#c_ref_transfer_id').val('').trigger('change.select2');
                VplTransferForm.loadRefOptions('create');
                VplTransferForm.loadRefDetails('create');
            } else {
                VplTransferForm.reloadAllRows('create');
            }
        });

        $('#c_transfertype').on('change', function () {
            const isReturn = $(this).val() === 'ReturnTf';
            document.getElementById('c_ref_wrapper').classList.toggle('hidden', !isReturn);
            VplTransferForm.toggleBtn('c_addRow', !isReturn);

            const body = document.getElementById('c_detailBody');
            body.innerHTML = '';
            VplTransfer.state.cRowIdx = 0;

            if (isReturn) {
                VplTransferForm.loadRefOptions('create');
                // Rows populate once a reference transfer is picked — see c_ref_transfer_id below
            } else {
                body.insertAdjacentHTML('beforeend', VplTransferHelper.buildDetailRow('c', 0));
                VplTransferForm.reloadAllRows('create');
            }
        });

        // Reference Transfer picked — auto-populate all its still-returnable lines
        $('#c_ref_transfer_id').on('change', () => VplTransferForm.loadRefDetails('create'));

        // Pick product (delegated) — opens product search modal
        document.getElementById('c_detailBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-pick-product-btn');
            if (btn) VplTransferForm.openProductSearch('create', parseInt(btn.dataset.idx, 10));
        });

        // FROM WHS dropdown change (only visible when a dept/vp_type has >1 assigned warehouse)
        $('#c_detailBody').on('change', '.c-from-whs-sel', function () {
            VplTransferForm.onFromWhsChange('create', this);
        });

        // TO WHS dropdown change (multi-candidate case) — mirror the pick into the hidden submit field
        $('#c_detailBody').on('change', '.c-to-whs-sel', function () {
            const row = this.closest('tr');
            row.querySelector('.c-to-whs-input').value = this.value;
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

        if (!document.getElementById('c_remark')?.value.trim()) {
            VplTransfer.toast('error', 'Please enter a remark before submitting.');
            return;
        }

        if (!VplTransferForm.validateRows('c')) return;

        const rows = VplTransferForm.collectRows('c');
        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one product before submitting.' });
            return;
        }

        const typeLabel = transType === 'ReturnTf' ? 'Return Transfer' : 'Transfer';

        VplTransferForm.confirmRows(rows, typeLabel).then((result) => {
            if (!result.isConfirmed) return;

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
        });
    },

    // ------------------------------------------------------------------
    // SUBMIT CONFIRMATION — shared between create & edit
    // ------------------------------------------------------------------

    _escape(str) {
        return String(str ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    },

    // Pending (not-yet-saved) rows from the detail table for the given prefix ('c' or 'e')
    collectRows(prefix) {
        const rows = [];
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row) => {
            const productEl = row.querySelector(`.${prefix}-product-display`);
            const product   = productEl?.title?.trim() || productEl?.textContent.trim() || '';
            const qty       = row.querySelector(`.${prefix}-qty-transfer-input`)?.value;

            if (!product || product === '— Select —' || !qty) return;

            rows.push({
                product,
                fromWhs: row.querySelector(`.${prefix}-from-whs-input`)?.value || '—',
                toWhs:   row.querySelector(`.${prefix}-to-whs-input`)?.value || '—',
                exp:     row.querySelector(`.${prefix}-exp-display`)?.textContent.trim() || '—',
                avail:   row.querySelector(`.${prefix}-qty-avail-input`)?.value || '0',
                qty,
            });
        });
        return rows;
    },

    // Blocks submit with a toast if any row with a product picked is missing From WHS, To WHS, or Qty.
    validateRows(prefix) {
        let error = null;
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row, i) => {
            if (error) return;
            const productEl = row.querySelector(`.${prefix}-product-display`);
            const product    = productEl?.title?.trim() || productEl?.textContent.trim() || '';
            if (!product || product === '— Select —') return;

            const fromWhs = row.querySelector(`.${prefix}-from-whs-input`)?.value;
            const toWhs   = row.querySelector(`.${prefix}-to-whs-input`)?.value;
            const qty     = row.querySelector(`.${prefix}-qty-transfer-input`)?.value;
            const avail   = row.querySelector(`.${prefix}-qty-avail-input`)?.value;

            if (!fromWhs) error = `Row ${i + 1}: From WHS is required.`;
            else if (!toWhs) error = `Row ${i + 1}: To WHS is required.`;
            else if (!qty) error = `Row ${i + 1}: Transfer Qty is required.`;
            else if (Number(qty) > Number(avail)) error = `Row ${i + 1}: Transfer Qty (${qty}) cannot exceed Available Qty (${avail}).`;
        });

        if (error) {
            VplTransfer.toast('error', error);
            return false;
        }
        return true;
    },

    // Already-saved detail lines shown in the Edit modal's "Existing Details" table
    collectExistingRows() {
        const rows = [];
        document.querySelectorAll('#e_existDetailBody tr[data-detail-id]').forEach((row) => {
            const cells = row.querySelectorAll(':scope > td');
            const nameDivs = cells[0]?.querySelectorAll('div') ?? [];
            const product   = nameDivs[1]?.textContent.trim() || nameDivs[0]?.textContent.trim() || '';

            rows.push({
                product,
                fromWhs: cells[1]?.textContent.trim() || '—',
                toWhs:   cells[2]?.textContent.trim() || '—',
                exp:     cells[3]?.textContent.trim() || '—',
                avail:   cells[4]?.textContent.trim() || '0',
                qty:     cells[5]?.textContent.trim() || '0',
            });
        });
        return rows;
    },

    // Shows a review dialog listing every product/voucher line before it's transferred.
    // Returns the Swal promise so callers can act on `result.isConfirmed`.
    confirmRows(rows, typeLabel) {
        const esc = VplTransferForm._escape;
        const tableRows = rows.map(r => `
            <tr style="border-bottom:1px solid #e2e8f0">
                <td style="padding:6px 10px;text-align:left;font-size:12px">${esc(r.product)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.fromWhs)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.toWhs)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.exp)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.avail)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px;font-weight:600">${esc(r.qty)}</td>
            </tr>`).join('');

        const html = `
            <p style="margin-bottom:12px;font-size:13px;color:#475569">Please review the ${typeLabel.toLowerCase()} items below before submitting for approval:</p>
            <div style="overflow-x:auto;border-radius:8px;border:1px solid #e2e8f0">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Product</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">From WHS</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">To WHS</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Expired Date</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Avail. Qty</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Transfer Qty</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>`;

        return Swal.fire({
            title:              `Confirm ${typeLabel}`,
            html,
            icon:               'question',
            showCancelButton:   true,
            confirmButtonColor: '#0f172a',
            cancelButtonColor:  '#94a3b8',
            confirmButtonText:  '<i class="fa-solid fa-paper-plane mr-1"></i> Yes, Submit',
            cancelButtonText:   'Review Again',
            width:              750,
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
        VplTransferForm.toggleBtn('c_addRow', true);
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
            refWrap.classList.remove('hidden');
            const $refSelect = $('#e_ref_transfer_id');
            if (!$refSelect.data('select2')) {
                $refSelect.select2({
                    placeholder: 'Select Reference...',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#editModal'),
                });
            }
            VplTransferForm.loadRefOptions('edit', t.ref_transfer_id)
                .done(() => VplTransferForm.loadRefDetails('edit', t.id));
        } else {
            refWrap.classList.add('hidden');
        }

        // Existing details
        const existBody = document.getElementById('e_existDetailBody');
        existBody.innerHTML = '';
        (data.details ?? []).forEach((d) => {
            const expDisplay = VplTransferHelper.formatExpDate(d.expired_date) ?? '—';
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
            const url = `/transfervp/attachment/${a.id}/view`;
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
        const isReturn = t.transfertype === 'ReturnTf';
        VplTransferForm.toggleBtn('e_addRow', !isReturn);

        if (isReturn && t.ref_transfer_id) {
            // Auto-populate remaining returnable lines from the reference transfer, excluding
            // this document's own already-saved lines (shown above in "Existing Details") from
            // the "already returned" calc so its own prior claim doesn't shrink its own options.
            // Reference options loader above populates these rows after it restores
            // the currently selected reference.
        } else {
            const newDetailBody = document.getElementById('e_detailBody');
            newDetailBody.innerHTML = '';
            VplTransfer.state.eRowIdx = 0;
            newDetailBody.insertAdjacentHTML('beforeend', VplTransferHelper.buildDetailRow('e', 0));
            VplTransferForm.loadFromWhs('edit', 0);
            VplTransferForm.loadToWhs('edit', 0);
        }

        VplTransferForm.showModal('editModal');
    },

    initEditModal() {
        const modal = document.getElementById('editModal');

        ['closeEditModal', 'closeEditModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => {
                VplTransferForm.hideModal('editModal', () => VplTransferForm.resetEditModal());
            });
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

        // A Return Transfer may select a different reference after all details from
        // the previous reference have been removed.
        $('#e_ref_transfer_id').on('change', function () {
            const previousRef = $(this).data('current-ref') || '';
            const selectedRef = this.value || '';
            if (selectedRef === previousRef) return;

            if (document.querySelector('#e_existDetailBody tr[data-detail-id]')) {
                $(this).val(previousRef).trigger('change.select2');
                VplTransfer.toast('error', 'Remove all existing details before changing the Reference Transfer.');
                return;
            }

            $(this).data('current-ref', selectedRef);
            VplTransferForm.loadRefDetails('edit', VplTransfer.state.currentViewId);
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

        // TO WHS dropdown change (multi-candidate case) — mirror the pick into the hidden submit field
        $('#e_detailBody').on('change', '.e-to-whs-sel', function () {
            const row = this.closest('tr');
            row.querySelector('.e-to-whs-input').value = this.value;
        });

        // Submit
        document.getElementById('submitEditBtn').addEventListener('click', () => VplTransferForm.submitEdit());
    },

    submitEdit() {
        if (!document.getElementById('e_remark')?.value.trim()) {
            VplTransfer.toast('error', 'Please enter a remark before submitting.');
            return;
        }

        if (!VplTransferForm.validateRows('e')) return;

        const transType = document.getElementById('e_transfertype')?.value ?? '';
        const refTransfer = document.getElementById('e_ref_transfer_id')?.value ?? '';
        if (transType === 'ReturnTf' && !refTransfer) {
            VplTransfer.toast('error', 'Please select a Reference Transfer for Return Transfer.');
            return;
        }

        const typeLabel = transType === 'ReturnTf' ? 'Return Transfer' : 'Transfer';
        const rows      = VplTransferForm.collectExistingRows().concat(VplTransferForm.collectRows('e'));

        if (rows.length === 0) {
            VplTransfer.toast('error', 'At least one transfer detail is required before resubmitting for approval.');
            return;
        }

        const doSubmit = () => {
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
                VplTransferForm.hideModal('editModal', () => VplTransferForm.resetEditModal());
                VplTransferDatalist.refresh();
            })
            .fail((x) => {
                VplTransfer.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
            });
        };

        VplTransferForm.confirmRows(rows, typeLabel).then((result) => {
            if (result.isConfirmed) doSubmit();
        });
    },

    resetEditModal() {
        document.getElementById('editForm').reset();
        document.getElementById('e_existDetailBody').innerHTML = '';
        document.getElementById('e_existAttachBody').innerHTML = '';

        const body = document.getElementById('e_detailBody');
        body.innerHTML = '';
        VplTransfer.state.eRowIdx = 0;
        body.insertAdjacentHTML('beforeend', VplTransferHelper.buildDetailRow('e', 0));

        document.getElementById('e_attachBody').innerHTML = `
            <tr id="e_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplTransfer.state.eAttachIdx = 1;

        document.getElementById('e_ref_display_wrapper').classList.add('hidden');
        VplTransferForm.toggleBtn('e_addRow', true);
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
            row.querySelector(`.${prefix}-exp-display`).textContent             = VplTransferHelper.formatExpDate(expDate) ?? '—';

            const qtyInput = row.querySelector(`.${prefix}-qty-transfer-input`);
            qtyInput.max   = qtyAvail;
            if (Number(qtyInput.value) > Number(qtyAvail)) qtyInput.value = qtyAvail;

            // If from_whs came back from product search, fill it
            if (fromWhs) {
                row.querySelector(`.${prefix}-from-whs-input`).value = fromWhs;
                $(row).find(`.${prefix}-from-whs-sel`).val(fromWhs).trigger('change.select2');
                VplTransferForm.loadToWhs(mode, rowIdx);
            }

            VplTransferForm.hideModal('productSearchModal');
        });
    },

    /**
     * Toggles visibility using both `hidden` and `inline-flex` together —
     * Tailwind's `hidden` utility can lose to a statically-declared `inline-flex`
     * depending on generated CSS order, so both must be flipped in lockstep.
     */
    toggleBtn(id, visible) {
        const el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('hidden', !visible);
        el.classList.toggle('inline-flex', visible);
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

    // onHidden fires after the fade-out completes, so a form reset doesn't flash
    // visibly mid-transition — used to clear Create/Edit modals on Close/X/Cancel.
    hideModal(modalId, onHidden) {
        const modal    = document.getElementById(modalId);
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modalId === 'createModal') VplTransfer.clearUrl();
            onHidden?.();
        }, 200);
    },
};
