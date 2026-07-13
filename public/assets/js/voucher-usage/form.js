const VplUsageForm = {

    // ------------------------------------------------------------------
    // PRODUCT SEARCH MODAL (Usage-type manual add only)
    // ------------------------------------------------------------------

    productSearchTable: null,

    openProductSearch(mode, rowIdx) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const whsId  = document.getElementById(`${prefix}_whs_id`)?.value ?? '';

        if (!whsId) {
            VplUsage.toast('warning', 'Please select a Warehouse first.');
            return;
        }

        VplUsage.state.pendingProductMode   = mode;
        VplUsage.state.pendingProductRowIdx = rowIdx;

        const cpnyid = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const vpType = document.getElementById(`${prefix}_vp_type`)?.value
                    ?? document.getElementById('c_vp_type')?.value ?? '';

        if (VplUsageForm.productSearchTable) {
            VplUsageForm.productSearchTable.destroy();
            VplUsageForm.productSearchTable = null;
            $('#productSearchTable tbody').empty();
        }

        VplUsageForm.productSearchTable = $('#productSearchTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url:     VplUsage.routes.products,
                type:    'POST',
                dataSrc: '',
                data: { _token: VplUsage.csrf(), cpnyid, vp_type: vpType, whs_id: whsId },
            },
            columns: [
                { data: 'product_id',   title: 'Product ID' },
                { data: 'product_name', title: 'Product Name' },
                { data: 'expired_date', title: 'Expired Date', render: (v) => v ? v.substring(0, 10) : '—' },
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
                            data-whs-id="${row.whs_id ?? ''}">Select</button>`,
                },
            ],
            pageLength: 10,
        });

        VplUsageForm.showModal('productSearchModal');
    },

    // ------------------------------------------------------------------
    // WAREHOUSE LOADER
    // A department can have more than one usage warehouse assigned for the
    // same company/vp_type: 0 matches -> block submit with a warning,
    // 1 match -> auto-fill, 2+ matches -> let the user choose.
    // ------------------------------------------------------------------

    loadWarehouseOptions(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const cpnyid = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept   = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType = document.getElementById(`${prefix}_vp_type`)?.value ?? '';

        const $sel = $(`#${prefix}_whs_id`);
        $sel.empty().append('<option value="">Select...</option>');
        VplUsageForm.applyWarehouseToRows(mode, '');

        if (!cpnyid || !dept || !vpType) return;

        $.post(VplUsage.routes.warehouse, {
            _token: VplUsage.csrf(), cpnyid, department: dept, vp_type: vpType,
        }).done((list) => {
            list.forEach((w) => $sel.append(new Option(w.whs_id, w.whs_id)));
            $sel.trigger('change.select2');

            if (prefix === 'c') {
                const warning = document.getElementById('c_whs_warning');
                warning?.classList.toggle('hidden', list.length > 0);
                VplUsageForm.setReviewEnabled(list.length > 0);
            }

            // Single match: auto-select. Multiple: leave it to the user.
            if (list.length === 1) {
                $sel.val(list[0].whs_id).trigger('change');
            }
        }).fail(() => VplUsage.toast('warning', 'Could not load usage warehouse options.'));
    },

    applyWarehouseToRows(mode, whsId) {
        const prefix = mode === 'create' ? 'c' : 'e';
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row) => {
            const input   = row.querySelector(`.${prefix}-whs-input`);
            const display = row.querySelector(`.${prefix}-whs-display`);
            if (input && display) {
                input.value = whsId;
                display.textContent = whsId || '—';
            }
        });
    },

    // ------------------------------------------------------------------
    // USAGE DATE (CUSTOMERSERVICE backdate, H-14)
    // ------------------------------------------------------------------

    USAGE_DATE_BACKDATE_DAYS: 14,

    toggleUsageDateSection() {
        const dept      = document.getElementById('c_department')?.value ?? '';
        const usageType = document.getElementById('c_usagetype')?.value ?? '';
        const show      = dept === 'CUSTOMERSERVICE' && usageType === 'Usage';

        const wrapper = document.getElementById('c_usage_date_wrapper');
        const input   = document.getElementById('c_usage_date');
        wrapper?.classList.toggle('hidden', !show);

        if (show && input) {
            const today = new Date();
            const earliest = new Date();
            earliest.setDate(today.getDate() - VplUsageForm.USAGE_DATE_BACKDATE_DAYS);
            const toISO = (d) => d.toISOString().substring(0, 10);
            input.max = toISO(today);
            input.min = toISO(earliest);
            if (!input.value) input.value = toISO(today);
        } else if (input) {
            input.value = '';
        }
    },

    /** Returns true if valid, otherwise toasts an error and returns false. */
    validateUsageDate() {
        const wrapper = document.getElementById('c_usage_date_wrapper');
        if (wrapper?.classList.contains('hidden')) return true;

        const input = document.getElementById('c_usage_date');
        const value = input?.value ?? '';
        if (!value) {
            VplUsage.toast('error', 'Usage Date is required.');
            return false;
        }

        const selected = new Date(value + 'T00:00:00');
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const earliest = new Date(today);
        earliest.setDate(today.getDate() - VplUsageForm.USAGE_DATE_BACKDATE_DAYS);

        if (selected < earliest || selected > today) {
            VplUsage.toast('error', `Usage Date must be within H-${VplUsageForm.USAGE_DATE_BACKDATE_DAYS} to today.`);
            return false;
        }
        return true;
    },

    setReviewEnabled(enabled) {
        const reviewBtn = document.getElementById('c_reviewBtn');
        if (!reviewBtn) return;
        reviewBtn.disabled = !enabled;
        reviewBtn.classList.toggle('opacity-50', !enabled);
        reviewBtn.classList.toggle('cursor-not-allowed', !enabled);
    },

    // ------------------------------------------------------------------
    // REFERENCE USAGE DOC (Return mode)
    // ------------------------------------------------------------------

    loadRefOptions(mode) {
        const prefix   = mode === 'create' ? 'c' : 'e';
        const cpnyid   = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept     = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType   = document.getElementById(`${prefix}_vp_type`)?.value ?? '';
        const usageType = document.getElementById(`${prefix}_usagetype`)?.value ?? '';

        if (usageType !== 'Return') return;

        const $sel = $(`#${prefix}_ref_usage_id`);
        $sel.empty().append('<option value="">Select Reference...</option>');

        $.post(VplUsage.routes.refOpts, {
            _token: VplUsage.csrf(), cpnyid, department: dept, vp_type: vpType,
        }).done((refs) => {
            refs.forEach((r) => $sel.append(new Option(r, r)));
            $sel.trigger('change.select2');
        }).fail(() => VplUsage.toast('warning', 'Could not load reference options.'));
    },

    /**
     * Return mode: replace all detail rows with the remaining-returnable lines
     * of the referenced Usage document.
     */
    loadReturnLines(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const refId  = document.getElementById(`${prefix}_ref_usage_id`)?.value ?? '';
        const body   = document.getElementById(`${prefix}_detailBody`);

        body.innerHTML = '';
        if (!refId) return;

        $.post(VplUsage.routes.refDetails, { _token: VplUsage.csrf(), ref_usage_id: refId })
            .done((lines) => {
                if (!lines.length) {
                    VplUsage.toast('warning', 'This document has no remaining returnable quantity.');
                    return;
                }
                lines.forEach((line, i) => {
                    body.insertAdjacentHTML('beforeend', VplUsageHelper.buildDetailRow(prefix, i, '', line));
                });
                VplUsage.state[mode === 'create' ? 'cRowIdx' : 'eRowIdx'] = lines.length - 1;
            })
            .fail(() => VplUsage.toast('error', 'Could not load reference detail lines.'));
    },

    // ------------------------------------------------------------------
    // ADD ROW (Usage-type manual add only)
    // ------------------------------------------------------------------

    addRow(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const key    = mode === 'create' ? 'cRowIdx' : 'eRowIdx';
        const whsId  = document.getElementById(`${prefix}_whs_id`)?.value ?? '';
        VplUsage.state[key]++;
        const idx = VplUsage.state[key];
        const html = VplUsageHelper.buildDetailRow(prefix, idx, whsId);

        document.getElementById(`${prefix}_detailBody`).insertAdjacentHTML('beforeend', html);
    },

    // ------------------------------------------------------------------
    // HEADER CHANGE HANDLING
    // ------------------------------------------------------------------

    onUsageTypeChange(mode) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const usageType = document.getElementById(`${prefix}_usagetype`)?.value ?? 'Usage';
        const isReturn  = usageType === 'Return';

        document.getElementById(`${prefix}_ref_wrapper`)?.classList.toggle('hidden', !isReturn);
        document.getElementById(`${prefix}_whs_wrapper`)?.classList.toggle('hidden', isReturn);

        if (prefix === 'c') VplUsageForm.toggleUsageDateSection();

        const addRowBtn = document.getElementById(`${prefix}_addRow`);
        if (addRowBtn) addRowBtn.classList.toggle('hidden', isReturn);

        if (isReturn) {
            VplUsageForm.loadRefOptions(mode);
            document.getElementById(`${prefix}_detailBody`).innerHTML = '';
            if (prefix === 'c') {
                document.getElementById('c_whs_warning')?.classList.add('hidden');
                VplUsageForm.setReviewEnabled(true);
            }
        } else {
            const body = document.getElementById(`${prefix}_detailBody`);
            body.innerHTML = '';
            VplUsage.state[mode === 'create' ? 'cRowIdx' : 'eRowIdx'] = 0;
            body.insertAdjacentHTML('beforeend', VplUsageHelper.buildDetailRow(prefix, 0));
            VplUsageForm.loadWarehouseOptions(mode);
        }
    },

    // ------------------------------------------------------------------
    // CREATE MODAL
    // ------------------------------------------------------------------

    initCreateModal() {
        document.getElementById('c_detailBody').insertAdjacentHTML('beforeend',
            VplUsageHelper.buildDetailRow('c', 0));

        document.getElementById('openCreateBtn').addEventListener('click', () => {
            VplUsageForm.showModal('createModal');
            setTimeout(() => VplUsageForm.loadWarehouseOptions('create'), 50);
            VplUsageForm.toggleUsageDateSection();
        });

        ['closeCreateModal', 'closeCreateModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('createModal'));
        });

        document.getElementById('c_addRow').addEventListener('click', () => VplUsageForm.addRow('create'));

        document.getElementById('c_addAttach').addEventListener('click', () => {
            const html = VplUsageHelper.buildAttachRow('c', VplUsage.state.cAttachIdx++);
            document.getElementById('c_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('c_detailBody').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.c-remove-row-btn');
            if (removeBtn) { document.getElementById(`c_row_${removeBtn.dataset.idx}`)?.remove(); return; }
            const pickBtn = e.target.closest('.c-pick-product-btn');
            if (pickBtn) VplUsageForm.openProductSearch('create', parseInt(pickBtn.dataset.idx, 10));
        });

        document.getElementById('c_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-remove-attach-btn');
            if (btn) document.getElementById(`c_attach_${btn.dataset.idx}`)?.remove();
        });

        $('#c_cpnyid, #c_department, #c_vp_type').on('change', () => VplUsageForm.loadWarehouseOptions('create'));
        $('#c_department').on('change', () => VplUsageForm.toggleUsageDateSection());
        $('#c_whs_id').on('change', () => VplUsageForm.applyWarehouseToRows('create', $('#c_whs_id').val() ?? ''));
        $('#c_usagetype').on('change', () => VplUsageForm.onUsageTypeChange('create'));
        $('#c_ref_usage_id').on('change', () => VplUsageForm.loadReturnLines('create'));

        document.getElementById('c_reviewBtn').addEventListener('click', () => VplUsageForm.showPreview('create'));
        document.getElementById('c_backToEditBtn').addEventListener('click', () => VplUsageForm.showFormView('create'));
        document.getElementById('c_confirmSubmitBtn').addEventListener('click', () => VplUsageForm.submitCreate());
    },

    submitCreate() {
        const form      = document.getElementById('createForm');
        const usageType = document.getElementById('c_usagetype')?.value ?? '';
        const refUsage  = document.getElementById('c_ref_usage_id')?.value ?? '';

        if (usageType === 'Return' && !refUsage) {
            VplUsage.toast('error', 'Please select a Reference Usage Doc for Return.');
            return;
        }

        if (!VplUsageForm.validateUsageDate()) return;

        const fd = new FormData(form);

        $.ajax({
            url:         VplUsage.routes.store,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplUsage.toast('success', r.success ?? 'Saved!');
            VplUsageForm.hideModal('createModal');
            VplUsageDatalist.refresh();
            VplUsageForm.resetCreateModal();
        })
        .fail((x) => {
            VplUsage.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    resetCreateModal() {
        document.getElementById('createForm').reset();
        const body = document.getElementById('c_detailBody');
        body.innerHTML = '';
        VplUsage.state.cRowIdx = 0;
        body.insertAdjacentHTML('beforeend', VplUsageHelper.buildDetailRow('c', 0));
        VplUsageForm.loadWarehouseOptions('create');
        document.getElementById('c_attachBody').innerHTML = `
            <tr id="c_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplUsage.state.cAttachIdx = 1;
        document.getElementById('c_ref_wrapper').classList.add('hidden');
        document.getElementById('c_whs_wrapper').classList.remove('hidden');
        document.getElementById('c_addRow').classList.remove('hidden');
        VplUsageForm.toggleUsageDateSection();
        VplUsageForm.showFormView('create');
    },

    // ------------------------------------------------------------------
    // EDIT MODAL
    // ------------------------------------------------------------------

    openEdit(data) {
        const t = data.usage;
        VplUsage.state.currentViewId = t.id;

        document.getElementById('e_cpnyid_display').value      = t.cpnyid;
        document.getElementById('e_cpnyid').value               = t.cpnyid;
        document.getElementById('e_dept_display').value         = t.department;
        document.getElementById('e_department').value            = t.department;
        document.getElementById('e_vp_type_display').value      = data.vp_label;
        document.getElementById('e_vp_type').value               = t.vp_type;
        document.getElementById('e_usagetype_display').value    = data.usagetype_label;
        document.getElementById('e_remark').value                = t.usage_remark ?? '';
        document.getElementById('e_title').textContent           = `Edit Usage — ${t.usage_id}`;

        const refWrap = document.getElementById('e_ref_display_wrapper');
        if (t.ref_usage_id) {
            document.getElementById('e_ref_display').value = t.ref_usage_id;
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
            const qty = t.usagetype === 'Return' ? d.qty_return_usage : d.qty_usage;
            existBody.insertAdjacentHTML('beforeend', `
                <tr data-detail-id="${d.id}">
                    <td class="px-4 py-2">
                        <div class="text-sm font-medium text-slate-800 dark:text-white">${d.product_id}</div>
                        <div class="text-xs text-slate-500">${d.product_name ?? ''}</div>
                    </td>
                    <td class="px-4 py-2 text-xs">${d.whs_id ?? ''}</td>
                    <td class="px-4 py-2 text-xs">${expDisplay}</td>
                    <td class="px-4 py-2 text-right text-xs font-semibold">${Number(qty ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-xs">${d.purpose_id ?? ''}</td>
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
        VplUsage.state.eRowIdx = 0;

        const addRowBtn = document.getElementById('e_addRow');
        const isReturn  = t.usagetype === 'Return';
        addRowBtn?.classList.toggle('hidden', isReturn);
        document.getElementById('e_whs_wrapper')?.classList.toggle('hidden', isReturn);

        if (!isReturn) {
            newDetailBody.insertAdjacentHTML('beforeend', VplUsageHelper.buildDetailRow('e', 0));
            VplUsageForm.loadWarehouseOptions('edit');
        }

        VplUsageForm.showFormView('edit');
        VplUsageForm.showModal('editModal');
    },

    initEditModal() {
        ['closeEditModal', 'closeEditModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('editModal'));
        });

        document.getElementById('e_addRow').addEventListener('click', () => VplUsageForm.addRow('edit'));
        $('#e_whs_id').on('change', () => VplUsageForm.applyWarehouseToRows('edit', $('#e_whs_id').val() ?? ''));

        document.getElementById('e_addAttach').addEventListener('click', () => {
            const html = VplUsageHelper.buildAttachRow('e', VplUsage.state.eAttachIdx++);
            document.getElementById('e_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('e_detailBody').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.e-remove-row-btn');
            if (removeBtn) { document.getElementById(`e_row_${removeBtn.dataset.idx}`)?.remove(); return; }
            const pickBtn = e.target.closest('.e-pick-product-btn');
            if (pickBtn) VplUsageForm.openProductSearch('edit', parseInt(pickBtn.dataset.idx, 10));
        });

        document.getElementById('e_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.e-remove-attach-btn');
            if (btn) document.getElementById(`e_attach_${btn.dataset.idx}`)?.remove();
        });

        document.getElementById('e_existDetailBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.e-del-exist-detail');
            if (!btn) return;
            const res = await VplUsage.confirm({ title: 'Remove this detail line?', confirmText: 'Remove' });
            if (!res.isConfirmed) return;
            $.post(VplUsage.routes.delDetail, { _token: VplUsage.csrf(), detail_id: btn.dataset.detailId })
                .done(() => {
                    btn.closest('tr')?.remove();
                    VplUsage.toast('success', 'Detail removed.');
                })
                .fail(() => VplUsage.toast('error', 'Remove failed.'));
        });

        document.getElementById('e_existAttachBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.e-del-exist-attach');
            if (!btn) return;
            const res = await VplUsage.confirm({ title: 'Remove this attachment?', confirmText: 'Remove' });
            if (!res.isConfirmed) return;
            $.post(VplUsage.routes.delAttach, { _token: VplUsage.csrf(), detail_id: btn.dataset.attachId })
                .done(() => {
                    btn.closest('[data-attach-id]')?.remove();
                    VplUsage.toast('success', 'Attachment removed.');
                })
                .fail(() => VplUsage.toast('error', 'Remove failed.'));
        });

        document.getElementById('e_reviewBtn').addEventListener('click', () => VplUsageForm.showPreview('edit'));
        document.getElementById('e_backToEditBtn').addEventListener('click', () => VplUsageForm.showFormView('edit'));
        document.getElementById('e_confirmSubmitBtn').addEventListener('click', () => VplUsageForm.submitEdit());
    },

    submitEdit() {
        const id   = VplUsage.state.currentViewId;
        const form = document.getElementById('editForm');
        const fd   = new FormData(form);

        $.ajax({
            url:         VplUsage.routes.update(id),
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplUsage.toast('success', r.success ?? 'Resubmitted!');
            VplUsageForm.hideModal('editModal');
            VplUsageDatalist.refresh();
        })
        .fail((x) => {
            VplUsage.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    // ------------------------------------------------------------------
    // PREVIEW STEP — client-side only, no server round trip
    // ------------------------------------------------------------------

    collectRows(prefix) {
        const rows = [];
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row) => {
            const get = (name) => row.querySelector(`[name="addmore[${row.dataset.idx}][${name}]"]`)?.value ?? '';
            const productId  = get('product_id');
            const qty        = get('qty');
            if (!productId || !qty) return;

            const productDisplay = row.querySelector('.c-product-display, .e-product-display')?.textContent
                ?? row.querySelector('td:first-child')?.textContent?.trim();

            rows.push({
                product: productDisplay || productId,
                whs: get('whs_id'),
                expired: get('expired_date'),
                qty,
                purpose: get('purpose_id'),
                purposeRemark: get('purpose_remark'),
            });
        });
        return rows;
    },

    collectAttachNames(prefix) {
        const names = [];
        document.querySelectorAll(`#${prefix}_attachBody input[type="file"]`).forEach((input) => {
            if (input.files?.[0]) names.push(input.files[0].name);
        });
        return names;
    },

    showPreview(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';

        const rows = VplUsageForm.collectRows(prefix);
        if (!rows.length) {
            VplUsage.toast('error', 'Please add at least one detail line before submitting.');
            return;
        }

        const cpnyid   = document.getElementById(`${prefix}_cpnyid`)?.value ?? document.getElementById('e_cpnyid_display')?.value ?? '';
        const dept     = document.getElementById(`${prefix}_department`)?.value ?? document.getElementById('e_dept_display')?.value ?? '';
        const vpLabel  = mode === 'create'
            ? (document.getElementById('c_vp_type')?.selectedOptions[0]?.textContent ?? '')
            : (document.getElementById('e_vp_type_display')?.value ?? '');
        const typeLabel = mode === 'create'
            ? (document.getElementById('c_usagetype')?.selectedOptions[0]?.textContent ?? '')
            : (document.getElementById('e_usagetype_display')?.value ?? '');
        const refId    = document.getElementById(`${prefix}_ref_usage_id`)?.value ?? document.getElementById('e_ref_display')?.value ?? '';
        const remark   = document.getElementById(`${prefix}_remark`)?.value ?? '';
        const usageDateWrapper = document.getElementById('c_usage_date_wrapper');
        const usageDate = (prefix === 'c' && !usageDateWrapper?.classList.contains('hidden'))
            ? document.getElementById('c_usage_date')?.value ?? ''
            : '';

        const headerEl = document.getElementById(`${prefix}_previewHeader`);
        headerEl.innerHTML = `
            <div><div class="text-xs text-slate-500">Company</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${cpnyid}</div></div>
            <div><div class="text-xs text-slate-500">Department</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${dept}</div></div>
            <div><div class="text-xs text-slate-500">V/P Type</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${vpLabel}</div></div>
            <div><div class="text-xs text-slate-500">Usage Type</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${typeLabel}</div></div>
            ${refId ? `<div><div class="text-xs text-slate-500">Reference Doc</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${refId}</div></div>` : ''}
            ${usageDate ? `<div><div class="text-xs text-slate-500">Usage Date</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${usageDate}</div></div>` : ''}
            <div class="md:col-span-4"><div class="text-xs text-slate-500">Remark</div><div class="mt-1 text-slate-700 dark:text-slate-200">${remark || '—'}</div></div>
        `;

        const detailBody = document.getElementById(`${prefix}_previewDetailBody`);
        detailBody.innerHTML = rows.map((r) => `
            <tr>
                <td class="px-4 py-2">${r.product}</td>
                <td class="px-4 py-2 text-xs">${r.whs || '—'}</td>
                <td class="px-4 py-2 text-xs">${r.expired ? r.expired.substring(0, 10) : '—'}</td>
                <td class="px-4 py-2 text-right font-semibold">${Number(r.qty).toLocaleString()}</td>
                <td class="px-4 py-2 text-xs">${r.purpose || ''}${r.purposeRemark ? ' — ' + r.purposeRemark : ''}</td>
            </tr>
        `).join('');

        const attachBody = document.getElementById(`${prefix}_previewAttachBody`);
        const names = VplUsageForm.collectAttachNames(prefix);
        attachBody.innerHTML = names.length
            ? names.map((n) => `<div class="py-1"><i class="fa-regular fa-file mr-2 text-xs"></i>${n}</div>`).join('')
            : 'No new attachments.';

        document.getElementById(`${prefix}_formView`).classList.add('hidden');
        document.getElementById(`${prefix}_previewView`).classList.remove('hidden');
        VplUsageForm.toggleBtn(`${prefix}_reviewBtn`, false);
        VplUsageForm.toggleBtn(`${prefix}_backToEditBtn`, true);
        VplUsageForm.toggleBtn(`${prefix}_confirmSubmitBtn`, true);
    },

    showFormView(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        document.getElementById(`${prefix}_formView`).classList.remove('hidden');
        document.getElementById(`${prefix}_previewView`).classList.add('hidden');
        VplUsageForm.toggleBtn(`${prefix}_reviewBtn`, true);
        VplUsageForm.toggleBtn(`${prefix}_backToEditBtn`, false);
        VplUsageForm.toggleBtn(`${prefix}_confirmSubmitBtn`, false);
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
    // PRODUCT SEARCH MODAL events
    // ------------------------------------------------------------------

    initProductSearchModal() {
        document.getElementById('closeProductSearchModal')?.addEventListener('click', () => {
            VplUsageForm.hideModal('productSearchModal');
        });
        document.getElementById('productSearchModal')?.querySelector('.modal-backdrop')
            ?.addEventListener('click', () => VplUsageForm.hideModal('productSearchModal'));

        $('#productSearchTable').on('click', '.btn-pick-product', function () {
            const productId   = this.dataset.productId;
            const productName = this.dataset.productName;
            const qtyAvail    = this.dataset.qtyAvailable;
            const expDate     = this.dataset.expiredDate;

            const mode   = VplUsage.state.pendingProductMode;
            const rowIdx = VplUsage.state.pendingProductRowIdx;
            const prefix = mode === 'create' ? 'c' : 'e';
            const row    = document.getElementById(`${prefix}_row_${rowIdx}`);
            if (!row) return;

            row.querySelector(`.${prefix}-product-id-input`).value        = productId;
            row.querySelector(`.${prefix}-product-display`).textContent   = productName;
            row.querySelector(`.${prefix}-product-display`).title         = productName;
            row.querySelector(`.${prefix}-qty-avail-input`).value         = qtyAvail;
            row.querySelector(`.${prefix}-qty-avail-display`).textContent = Number(qtyAvail).toLocaleString();
            row.querySelector(`.${prefix}-exp-input`).value               = expDate;
            row.querySelector(`.${prefix}-exp-display`).textContent       = expDate ? expDate.substring(0, 10) : '—';

            VplUsageForm.hideModal('productSearchModal');
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
            if (modalId === 'createModal') VplUsage.clearUrl();
        }, 200);
    },
};
