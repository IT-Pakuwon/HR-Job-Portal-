const VplUsageForm = {

    // ------------------------------------------------------------------
    // ADD PRODUCT MODAL (Usage-type manual add)
    // Product select2 + Qty sit in one row. As soon as both are filled, a
    // live batch-breakdown table shows every expiry batch FEFO would draw
    // from (Name/Qty/Expired/Availability/Received), Received is read-only
    // and always reflects the FEFO split. Add commits that split as-is —
    // no extra network call at commit time.
    // ------------------------------------------------------------------

    pickerProducts: { create: [], edit: [] },
    previewDebounce: { create: null, edit: null },

    _escape(str) {
        return String(str ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    },

    loadPickerProducts(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const cpnyid = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const vpType = document.getElementById(`${prefix}_vp_type`)?.value
                    ?? document.getElementById('c_vp_type')?.value ?? '';
        const whsId  = document.getElementById(`${prefix}_whs_id`)?.value ?? '';

        const $sel = $(`#${prefix}_picker_product`);
        VplUsageForm.pickerProducts[mode] = [];
        VplUsageForm.resetPickerQty(mode);

        if (!cpnyid || !vpType || !whsId) {
            $sel.empty().append('<option value="">Select warehouse first...</option>')
                .prop('disabled', true).trigger('change.select2');
            return;
        }

        $.post(VplUsage.routes.products, { _token: VplUsage.csrf(), cpnyid, vp_type: vpType, whs_id: whsId })
            .done((list) => {
                VplUsageForm.pickerProducts[mode] = list;
                $sel.empty().append('<option value="">Select product...</option>');
                list.forEach((p) => {
                    $sel.append(new Option(`${p.product_name} — Avail: ${Number(p.qty_pickable ?? 0).toLocaleString()}`, p.product_id));
                });
                $sel.prop('disabled', list.length === 0).trigger('change.select2');
            })
            .fail(() => VplUsage.toast('warning', 'Could not load products for this warehouse.'));
    },

    resetPickerQty(mode) {
        const prefix   = mode === 'create' ? 'c' : 'e';
        const qtyInput = document.getElementById(`${prefix}_picker_qty`);
        if (qtyInput) qtyInput.value = '';
        VplUsageForm.clearPreview(mode);
    },

    clearPreview(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const body = document.getElementById(`${prefix}_picker_preview_body`);
        if (body) body.innerHTML = '';
        document.getElementById(`${prefix}_picker_preview_wrap`)?.classList.add('hidden');
        VplUsageForm.showPreviewError(mode, '');
    },

    showPreviewError(mode, message) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const el = document.getElementById(`${prefix}_picker_preview_error`);
        if (!el) return;
        el.textContent = message;
        el.classList.toggle('hidden', !message);
    },

    /** Debounced trigger for the Qty input so it doesn't fire a request per keystroke. */
    schedulePreview(mode) {
        clearTimeout(VplUsageForm.previewDebounce[mode]);
        VplUsageForm.previewDebounce[mode] = setTimeout(() => VplUsageForm.updatePreview(mode), 350);
    },

    /**
     * Sums qty already staged in the current draft for this product+warehouse,
     * keyed by expiry date (Y-m-d) — so a second Add Product pass for the same
     * product can tell the server's FEFO calc which batches this draft already
     * claims, instead of it re-offering them off unchanged DB stock.
     *
     * Edit mode also folds in the "Existing Details" rows still standing: revise()
     * released their stock hold up front, so the DB looks like that qty is free
     * again even though it's still committed to this document — without this,
     * the preview would offer the same batch twice (see update()'s post-restore
     * validation, which is the server-side backstop for the same gap).
     */
    collectStagedBatchQty(prefix, productId, whsId) {
        const staged = {};
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row) => {
            const idx = row.dataset.idx;
            const get = (name) => row.querySelector(`[name="addmore[${idx}][${name}]"]`)?.value ?? '';
            if (get('product_id') !== productId || get('whs_id') !== whsId) return;

            const exp = get('expired_date');
            const key = exp ? exp.substring(0, 10) : '';
            staged[key] = (staged[key] ?? 0) + parseFloat(get('qty') || '0');
        });

        if (prefix === 'e') {
            document.querySelectorAll('#e_existDetailBody tr[data-detail-id]').forEach((row) => {
                if (row.dataset.productId !== productId || row.dataset.whsId !== whsId) return;
                const key = row.dataset.expiredDate ?? '';
                staged[key] = (staged[key] ?? 0) + parseFloat(row.dataset.qty || '0');
            });
        }

        return staged;
    },

    /** Fetches the FEFO batch breakdown for the current Product+Qty and renders it as a table. */
    updatePreview(mode) {
        const prefix    = mode === 'create' ? 'c' : 'e';
        const cpnyid    = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const vpType    = document.getElementById(`${prefix}_vp_type`)?.value
                        ?? document.getElementById('c_vp_type')?.value ?? '';
        const whsId     = document.getElementById(`${prefix}_whs_id`)?.value ?? '';
        const productId = document.getElementById(`${prefix}_picker_product`)?.value ?? '';
        const qty       = parseFloat(document.getElementById(`${prefix}_picker_qty`)?.value ?? '0');

        if (!productId || !qty || qty <= 0) {
            VplUsageForm.clearPreview(mode);
            return;
        }

        const staged = VplUsageForm.collectStagedBatchQty(prefix, productId, whsId);

        $.post(VplUsage.routes.fefoPick, {
            _token: VplUsage.csrf(), cpnyid, vp_type: vpType, whs_id: whsId, product_id: productId, qty,
            staged: JSON.stringify(staged),
        })
        .done((breakdown) => {
            VplUsageForm.showPreviewError(mode, '');
            VplUsageForm.renderPreviewTable(mode, breakdown);
        })
        .fail((x) => {
            const body = document.getElementById(`${prefix}_picker_preview_body`);
            if (body) body.innerHTML = '';
            document.getElementById(`${prefix}_picker_preview_wrap`)?.classList.remove('hidden');
            VplUsageForm.showPreviewError(mode, x.responseJSON?.error ?? 'Could not compute batch breakdown.');
        });
    },

    /** Renders one row per FEFO batch, each with a read-only "Received" qty locked to the FEFO suggestion. */
    renderPreviewTable(mode, breakdown) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const body   = document.getElementById(`${prefix}_picker_preview_body`);
        body.innerHTML = '';

        breakdown.forEach((item) => {
            const exp  = item.expired_date ? item.expired_date.substring(0, 10) : '—';
            const name = VplUsageForm._escape(item.product_name);
            const tr = document.createElement('tr');
            tr.className = 'border-t border-slate-100 dark:border-white/10';
            tr.dataset.productId    = item.product_id;
            tr.dataset.productName  = item.product_name;
            tr.dataset.whsId        = item.whs_id;
            tr.dataset.expiredDate  = item.expired_date ?? '';
            tr.dataset.qtyAvailable = item.qty_available;
            tr.innerHTML = `
                <td class="px-3 py-2">${name}</td>
                <td class="px-3 py-2 text-right">${Number(item.qty_stock ?? 0).toLocaleString()}</td>
                <td class="px-3 py-2">${exp}</td>
                <td class="px-3 py-2 text-right">${Number(item.qty_available ?? 0).toLocaleString()}</td>
                <td class="px-3 py-2 text-right">
                    <input type="number" class="${prefix}-picker-received-input w-20 cursor-not-allowed rounded border border-slate-200 bg-slate-100 px-2 py-1 text-right text-xs text-slate-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-400"
                        readonly tabindex="-1" max="${item.qty_available ?? 0}" value="${item.qty}">
                </td>
            `;
            body.appendChild(tr);
        });

        document.getElementById(`${prefix}_picker_preview_wrap`)?.classList.remove('hidden');
    },

    /** Commits whatever is currently in the preview table's Received inputs — respects manual overrides, no extra network call. */
    pickerAdd(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const rows   = document.querySelectorAll(`#${prefix}_picker_preview_body tr`);

        if (rows.length === 0) {
            VplUsage.toast('warning', 'Select a product and enter a quantity first.');
            return;
        }

        const items = [];
        for (const row of rows) {
            const input = row.querySelector(`.${prefix}-picker-received-input`);
            const qty   = parseFloat(input?.value ?? '0');
            const max   = parseFloat(row.dataset.qtyAvailable ?? '0');

            if (qty > max) {
                VplUsage.toast('error', `Received qty for ${row.dataset.productName} exceeds availability.`);
                return;
            }
            if (qty > 0) {
                items.push({
                    product_id:    row.dataset.productId,
                    product_name:  row.dataset.productName,
                    whs_id:        row.dataset.whsId,
                    expired_date:  row.dataset.expiredDate,
                    qty,
                    qty_available: max,
                });
            }
        }

        if (items.length === 0) {
            VplUsage.toast('warning', 'Received qty must be greater than 0.');
            return;
        }

        VplUsageForm.appendFefoRows(mode, items);
        VplUsageForm.renderAddedSummary(mode, items);

        $(`#${prefix}_picker_product`).val('').trigger('change.select2');
        VplUsageForm.resetPickerQty(mode);
    },

    /** Confirms what was just added inside the modal — one line per batch, so a split across expiry dates is visible without closing. */
    renderAddedSummary(mode, items) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const list   = document.getElementById(`${prefix}_picker_added_list`);
        if (!list) return;

        items.forEach((item) => {
            const exp = item.expired_date ? item.expired_date.substring(0, 10) : '—';
            const line = document.createElement('div');
            line.textContent = `${item.product_name} — ${Number(item.qty).toLocaleString()} pcs (Exp ${exp})`;
            list.appendChild(line);
        });

        document.getElementById(`${prefix}_picker_added`)?.classList.remove('hidden');
    },

    /** Clears the "Added to this document" summary — called each time the Add Product modal is freshly opened. */
    clearAddedSummary(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const list = document.getElementById(`${prefix}_picker_added_list`);
        if (list) list.innerHTML = '';
        document.getElementById(`${prefix}_picker_added`)?.classList.add('hidden');
    },

    /** Appends one detail row per committed batch item. */
    appendFefoRows(mode, items) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const key    = mode === 'create' ? 'cRowIdx' : 'eRowIdx';
        items.forEach((item) => {
            VplUsageForm.addRow(mode);
            VplUsageForm.fillProductRow(prefix, VplUsage.state[key], item);
        });
    },

    openAddProductModal(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const whsId  = document.getElementById(`${prefix}_whs_id`)?.value ?? '';

        if (!whsId) {
            VplUsage.toast('warning', 'Please select a Warehouse first.');
            return;
        }

        VplUsageForm.loadPickerProducts(mode);
        VplUsageForm.clearAddedSummary(mode);
        VplUsageForm.showModal(`${prefix}_addProductModal`);
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
        VplUsageForm.loadPickerProducts(mode);

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

    // ------------------------------------------------------------------
    // USAGE DATE (CUSTOMERSERVICE backdate, H-3)
    // ------------------------------------------------------------------

    USAGE_DATE_BACKDATE_DAYS: 3,

    toggleUsageDateSection() {
        const dept      = document.getElementById('c_department')?.value ?? '';
        const usageType = document.getElementById('c_usagetype')?.value ?? '';
        const show      = dept === 'CUSTOMERSERVICE' && (usageType === 'Usage' || usageType === 'Return');

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

        VplUsageForm.updateWhsSpan();
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

    // ------------------------------------------------------------------
    // EVENT DATE (required for every department other than CUSTOMERSERVICE)
    // ------------------------------------------------------------------

    /** Today's date as YYYY-MM-DD, used as the `min` on Event Date inputs — it can never be backdated. */
    todayISO() {
        return new Date().toISOString().substring(0, 10);
    },

    /** Create-mode only — Edit's visibility is set directly in openEdit() from the loaded document's department/usagetype. */
    toggleEventDateSection() {
        const dept      = document.getElementById('c_department')?.value ?? '';
        const usageType = document.getElementById('c_usagetype')?.value ?? '';
        const show      = dept !== '' && dept !== 'CUSTOMERSERVICE' && usageType !== 'Return';

        const wrapper = document.getElementById('c_event_date_wrapper');
        const input   = document.getElementById('c_event_date');
        wrapper?.classList.toggle('hidden', !show);
        if (show && input) {
            input.min = VplUsageForm.todayISO();
        } else if (input) {
            input.value = '';
        }

        VplUsageForm.updateWhsSpan();
    },

    /** Warehouse field shares its row with whichever date field (Usage Date / Event Date, each col-span-2) is showing, instead of spanning full width. */
    updateWhsSpan() {
        const usageDateVisible = !document.getElementById('c_usage_date_wrapper')?.classList.contains('hidden');
        const eventDateVisible = !document.getElementById('c_event_date_wrapper')?.classList.contains('hidden');
        const show = usageDateVisible || eventDateVisible;

        const whsWrapper = document.getElementById('c_whs_wrapper');
        whsWrapper?.classList.toggle('md:col-span-4', !show);
        whsWrapper?.classList.toggle('md:col-span-2', show);
    },

    /** Returns true if valid, otherwise toasts an error and returns false. */
    validateEventDate(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const wrapper = document.getElementById(`${prefix}_event_date_wrapper`);
        if (wrapper?.classList.contains('hidden')) return true;

        const input = document.getElementById(`${prefix}_event_date`);
        if (!input?.value) {
            VplUsage.toast('error', 'Event Date is required.');
            return false;
        }
        if (input.value < VplUsageForm.todayISO()) {
            VplUsage.toast('error', 'Event Date cannot be backdated.');
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
    // PURPOSE DROPDOWN (select2; CUSTOMERSERVICE Usage defaults to "Redeem PG Card")
    // ------------------------------------------------------------------

    DEFAULT_PURPOSE_DEPT:  'CUSTOMERSERVICE',
    DEFAULT_PURPOSE_VALUE: 'Redeem PG Card',

    /** Turns any not-yet-enhanced Purpose <select> in the given detail body into select2. */
    initPurposeSelects(prefix) {
        const modalId = prefix === 'c' ? 'createModal' : 'editModal';
        $(`#${prefix}_detailBody select[name*="[purpose_id]"]`).each(function () {
            const $el = $(this);
            if (!$el.hasClass('select2-hidden-accessible')) {
                $el.select2({ placeholder: 'Select...', allowClear: true, width: '100%', dropdownParent: $(`#${modalId}`) });
            }
        });
    },

    /** CUSTOMERSERVICE Usage docs (create only): default every empty Purpose to "Redeem PG Card". */
    applyDefaultPurpose() {
        const dept      = document.getElementById('c_department')?.value ?? '';
        const usageType = document.getElementById('c_usagetype')?.value ?? '';
        if (dept !== VplUsageForm.DEFAULT_PURPOSE_DEPT || usageType !== 'Usage') return;

        document.querySelectorAll('#c_detailBody select[name*="[purpose_id]"]').forEach((sel) => {
            if (!sel.value) {
                sel.value = VplUsageForm.DEFAULT_PURPOSE_VALUE;
                $(sel).trigger('change');
            }
        });
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
                VplUsageForm.initPurposeSelects(prefix);
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
        VplUsageForm.initPurposeSelects(prefix);
        if (mode === 'create') VplUsageForm.applyDefaultPurpose();
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
        VplUsageForm.toggleBtn(`${prefix}_openAddProductBtn`, !isReturn);

        if (prefix === 'c') {
            VplUsageForm.toggleUsageDateSection();
            VplUsageForm.toggleEventDateSection();
        }

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
            VplUsageForm.loadWarehouseOptions(mode);
        }
    },

    // ------------------------------------------------------------------
    // CREATE MODAL
    // ------------------------------------------------------------------

    initCreateModal() {
        document.getElementById('openCreateBtn').addEventListener('click', () => {
            VplUsageForm.showModal('createModal');
            setTimeout(() => VplUsageForm.loadWarehouseOptions('create'), 50);
            VplUsageForm.toggleUsageDateSection();
            VplUsageForm.toggleEventDateSection();
        });

        ['closeCreateModal', 'closeCreateModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('createModal'));
        });

        document.getElementById('c_openAddProductBtn').addEventListener('click', () => VplUsageForm.openAddProductModal('create'));
        ['c_closeAddProductModal', 'c_closeAddProductModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('c_addProductModal'));
        });
        document.getElementById('c_addProductModal')?.querySelector('.modal-backdrop')
            ?.addEventListener('click', () => VplUsageForm.hideModal('c_addProductModal'));

        document.getElementById('c_addAttach').addEventListener('click', () => {
            const html = VplUsageHelper.buildAttachRow('c', VplUsage.state.cAttachIdx++);
            document.getElementById('c_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('c_detailBody').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.c-remove-row-btn');
            if (removeBtn) document.getElementById(`c_row_${removeBtn.dataset.idx}`)?.remove();
        });

        document.getElementById('c_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-remove-attach-btn');
            if (btn) document.getElementById(`c_attach_${btn.dataset.idx}`)?.remove();
        });

        $('#c_cpnyid, #c_department, #c_vp_type').on('change', () => {
            // Already-added detail rows were picked against the previous company/dept/type —
            // they're no longer valid, so clear them along with the warehouse/product pickers.
            document.getElementById('c_detailBody').innerHTML = '';
            VplUsage.state.cRowIdx = 0;
            VplUsageForm.loadWarehouseOptions('create');
        });
        $('#c_department').on('change', () => {
            VplUsageForm.toggleUsageDateSection();
            VplUsageForm.toggleEventDateSection();
            VplUsageForm.applyDefaultPurpose();
        });
        $('#c_whs_id').on('change', () => VplUsageForm.loadPickerProducts('create'));
        $('#c_picker_product').on('change', () => VplUsageForm.updatePreview('create'));
        $('#c_picker_qty').on('input', () => VplUsageForm.schedulePreview('create'));
        document.getElementById('c_pickerAddBtn').addEventListener('click', () => VplUsageForm.pickerAdd('create'));
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

        if (!document.getElementById('c_remark')?.value.trim()) {
            VplUsage.toast('error', 'Remark is required.');
            return;
        }

        if (!VplUsageForm.validateUsageDate()) return;
        if (!VplUsageForm.validateEventDate('create')) return;

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
        VplUsageForm.toggleBtn('c_openAddProductBtn', true);
        VplUsageForm.toggleUsageDateSection();
        VplUsageForm.toggleEventDateSection();
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

        const eventDateWrapper = document.getElementById('e_event_date_wrapper');
        const eventDateInput   = document.getElementById('e_event_date');
        const needsEventDate   = t.department !== 'CUSTOMERSERVICE' && t.usagetype !== 'Return';
        eventDateWrapper?.classList.toggle('hidden', !needsEventDate);
        if (eventDateInput) {
            eventDateInput.min = VplUsageForm.todayISO();
            eventDateInput.value = t.event_date ? String(t.event_date).substring(0, 10) : '';
        }

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
            const exp = (d.expired_date ?? '').substring(0, 10) || '1900-01-01';
            const expDisplay = exp === '1900-01-01' ? '—' : exp;
            const qty = t.usagetype === 'Return' ? d.qty_return_usage : d.qty_usage;
            existBody.insertAdjacentHTML('beforeend', `
                <tr data-detail-id="${d.id}" data-product-id="${d.product_id}" data-whs-id="${d.whs_id ?? ''}"
                    data-expired-date="${exp}" data-qty="${Number(qty ?? 0)}" data-purpose-id="${d.purpose_id ?? ''}"
                    data-product-name="${d.product_name ?? ''}">
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
            const url = `/usagevp/attachment/${a.id}/view`;
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

        const isReturn = t.usagetype === 'Return';
        document.getElementById('e_whs_wrapper')?.classList.toggle('hidden', isReturn);
        VplUsageForm.toggleBtn('e_openAddProductBtn', !isReturn);

        if (!isReturn) {
            VplUsageForm.loadWarehouseOptions('edit');
        }

        VplUsageForm.showFormView('edit');
        VplUsageForm.showModal('editModal');
    },

    initEditModal() {
        ['closeEditModal', 'closeEditModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('editModal'));
        });

        $('#e_whs_id').on('change', () => VplUsageForm.loadPickerProducts('edit'));
        $('#e_picker_product').on('change', () => VplUsageForm.updatePreview('edit'));
        $('#e_picker_qty').on('input', () => VplUsageForm.schedulePreview('edit'));
        document.getElementById('e_pickerAddBtn').addEventListener('click', () => VplUsageForm.pickerAdd('edit'));

        document.getElementById('e_openAddProductBtn').addEventListener('click', () => VplUsageForm.openAddProductModal('edit'));
        ['e_closeAddProductModal', 'e_closeAddProductModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplUsageForm.hideModal('e_addProductModal'));
        });
        document.getElementById('e_addProductModal')?.querySelector('.modal-backdrop')
            ?.addEventListener('click', () => VplUsageForm.hideModal('e_addProductModal'));

        document.getElementById('e_addAttach').addEventListener('click', () => {
            const html = VplUsageHelper.buildAttachRow('e', VplUsage.state.eAttachIdx++);
            document.getElementById('e_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('e_detailBody').addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.e-remove-row-btn');
            if (removeBtn) document.getElementById(`e_row_${removeBtn.dataset.idx}`)?.remove();
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
        if (!VplUsageForm.validateEventDate('edit')) return;

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

        // Edit mode: existing lines the user hasn't deleted still count toward the
        // document — a resubmit that only changes the remark/attachments shouldn't
        // be blocked for having no *new* rows (the server's update() already keeps
        // these as-is when `addmore` is absent/empty).
        if (prefix === 'e') {
            document.querySelectorAll('#e_existDetailBody tr[data-detail-id]').forEach((row) => {
                rows.push({
                    product: row.dataset.productName || row.dataset.productId,
                    whs: row.dataset.whsId,
                    expired: row.dataset.expiredDate,
                    qty: row.dataset.qty,
                    purpose: row.dataset.purposeId,
                    purposeRemark: '',
                });
            });
        }

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

        if (rows.some((r) => !r.purpose)) {
            VplUsage.toast('error', 'Purpose is required for every detail line.');
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
            <div class="md:col-span-5"><div class="text-xs text-slate-500">Remark</div><div class="mt-1 text-slate-700 dark:text-slate-200">${remark || '—'}</div></div>
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

    /** Fills one detail row's product/expiry/qty fields (used by the picker's FEFO add). */
    fillProductRow(prefix, rowIdx, item) {
        const row = document.getElementById(`${prefix}_row_${rowIdx}`);
        if (!row) return;

        row.querySelector(`.${prefix}-product-id-input`).value        = item.product_id;
        row.querySelector(`.${prefix}-product-display`).textContent   = item.product_name;
        row.querySelector(`.${prefix}-product-display`).title         = item.product_name;
        row.querySelector(`.${prefix}-qty-avail-input`).value         = item.qty_available;
        row.querySelector(`.${prefix}-qty-avail-display`).textContent = Number(item.qty_available).toLocaleString();
        row.querySelector(`.${prefix}-exp-input`).value               = item.expired_date ?? '';
        row.querySelector(`.${prefix}-exp-display`).textContent       = item.expired_date ? item.expired_date.substring(0, 10) : '—';

        const qtyInput = row.querySelector(`[name="addmore[${rowIdx}][qty]"]`);
        if (qtyInput) {
            qtyInput.value = item.qty;
            qtyInput.max   = item.qty_available;
        }
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
