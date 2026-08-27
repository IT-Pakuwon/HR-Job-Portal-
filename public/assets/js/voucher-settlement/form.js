const VplSettlementForm = {

    // ------------------------------------------------------------------
    // USAGE DOC PICKER
    // ------------------------------------------------------------------

    loadUsageOptions(mode, preselectUsageId = null) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const cpnyid = document.getElementById(`${prefix}_cpnyid`)?.value ?? '';
        const dept   = document.getElementById(`${prefix}_department`)?.value ?? '';
        const vpType = document.getElementById(`${prefix}_vp_type`)?.value ?? '';

        const $sel = $(`#${prefix}_usage_id`);
        $sel.empty().append('<option value="">Select...</option>');
        $sel.trigger('change.select2');
        VplSettlementForm.clearLines(mode);

        if (!cpnyid || !dept || !vpType) return;

        $.post(VplSettlement.routes.usageOptions, {
            _token: VplSettlement.csrf(), cpnyid, department: dept, vp_type: vpType,
        }).done((refs) => {
            refs.forEach((r) => $sel.append(new Option(r, r)));
            if (preselectUsageId && refs.includes(preselectUsageId)) {
                $sel.val(preselectUsageId).trigger('change');
            } else {
                $sel.trigger('change.select2');
            }
        }).fail(() => VplSettlement.toast('warning', 'Could not load Usage document options.'));
    },

    loadUsageLines(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const usageId = document.getElementById(`${prefix}_usage_id`)?.value ?? '';

        VplSettlementForm.clearLines(mode);
        if (!usageId) return;

        $.post(VplSettlement.routes.usageLines, { _token: VplSettlement.csrf(), usage_id: usageId })
            .done((lines) => {
                const body = document.getElementById('c_detailBody');
                const warning = document.getElementById('c_lines_warning');

                if (!lines.length) {
                    warning?.classList.remove('hidden');
                    body.innerHTML = '<tr id="c_detailEmptyRow"><td colspan="6" class="px-3 py-6 text-center text-sm text-slate-400">This document has no lines.</td></tr>';
                    return;
                }

                warning?.classList.add('hidden');
                body.innerHTML = lines.map((line, i) => VplSettlementHelper.buildCreateRow(i, line)).join('');
                lines.forEach((_, i) => VplSettlementForm.recomputeSisa('c', i));
            })
            .fail(() => VplSettlement.toast('error', 'Could not load Usage document lines.'));
    },

    clearLines(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        const body = document.getElementById(`${prefix}_detailBody`);
        if (prefix === 'c') {
            body.innerHTML = '<tr id="c_detailEmptyRow"><td colspan="6" class="px-3 py-6 text-center text-sm text-slate-400">Select a Usage document to load its lines.</td></tr>';
            document.getElementById('c_lines_warning')?.classList.add('hidden');
        } else {
            body.innerHTML = '';
        }
    },

    // ------------------------------------------------------------------
    // SISA (remaining) LIVE COMPUTE
    // ------------------------------------------------------------------

    recomputeSisa(prefix, idx) {
        const row = document.getElementById(`${prefix}_row_${idx}`);
        if (!row) return;
        const remaining = Number(row.dataset.remaining ?? 0);
        const input = row.querySelector(`.${prefix}-qty-settlement-input`);
        const display = document.getElementById(`${prefix}_sisa_${idx}`);
        if (!input || !display) return;

        let qty = Number(input.value ?? 0);
        if (qty < 0) qty = 0;
        if (qty > remaining) qty = remaining;
        const sisa = remaining - qty;
        display.textContent = sisa.toLocaleString();
        display.classList.toggle('text-amber-600', sisa > 0);
        display.classList.toggle('dark:text-amber-400', sisa > 0);
    },

    // ------------------------------------------------------------------
    // CREATE MODAL
    // ------------------------------------------------------------------

    /**
     * Opens Create Settlement pre-scoped to one Job List row: sets Company/Department/
     * V/P Type from the job, then issues a single authoritative usage-options fetch
     * with that Usage Doc preselected — bypassing the normal cpnyid/department/vp_type
     * change cascade (which would each fire their own fetch and could race the
     * preselect) since here all three are already known together, not picked one at
     * a time by the user.
     */
    openCreateFromJob(job) {
        document.getElementById('createForm').reset();
        VplSettlementForm.clearLines('create');
        document.getElementById('c_attachBody').innerHTML = `
            <tr id="c_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplSettlement.state.cAttachIdx = 1;

        $('#c_cpnyid').val(job.cpnyid).trigger('change.select2');
        $('#c_department').val(job.department).trigger('change.select2');
        $('#c_vp_type').val(job.vpType).trigger('change.select2');

        VplSettlementForm.showFormView('create');
        VplSettlementForm.showModal('createModal');
        VplSettlementForm.loadUsageOptions('create', job.usageId);
    },

    initCreateModal() {
        ['closeCreateModal', 'closeCreateModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplSettlementForm.hideModal('createModal'));
        });

        document.getElementById('c_addAttach').addEventListener('click', () => {
            const html = VplSettlementHelper.buildAttachRow('c', VplSettlement.state.cAttachIdx++);
            document.getElementById('c_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('c_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.c-remove-attach-btn');
            if (btn) document.getElementById(`c_attach_${btn.dataset.idx}`)?.remove();
        });

        document.getElementById('c_detailBody').addEventListener('input', (e) => {
            const input = e.target.closest('.c-qty-settlement-input');
            if (input) VplSettlementForm.recomputeSisa('c', parseInt(input.dataset.idx, 10));
        });

        $('#c_cpnyid, #c_department, #c_vp_type').on('change', () => VplSettlementForm.loadUsageOptions('create'));
        $('#c_usage_id').on('change', () => VplSettlementForm.loadUsageLines('create'));

        document.getElementById('c_reviewBtn').addEventListener('click', () => VplSettlementForm.showPreview('create'));
        document.getElementById('c_backToEditBtn').addEventListener('click', () => VplSettlementForm.showFormView('create'));
        document.getElementById('c_confirmSubmitBtn').addEventListener('click', () => VplSettlementForm.submitCreate());
    },

    // True if any file input matching selector currently has a selected file
    hasAnyFile(selector) {
        let found = false;
        document.querySelectorAll(selector).forEach((input) => {
            if (input.files?.length > 0) found = true;
        });
        return found;
    },

    submitCreate() {
        const form = document.getElementById('createForm');

        if (!document.getElementById('c_usage_id')?.value) {
            VplSettlement.toast('error', 'Please select a Usage document.');
            return;
        }
        if (!document.getElementById('c_remark')?.value.trim()) {
            VplSettlement.toast('error', 'Remark is required.');
            return;
        }
        if (!VplSettlementForm.collectLines('c').length) {
            VplSettlement.toast('error', 'This document has no settlement lines.');
            return;
        }
        if (!VplSettlementForm.hasAnyFile('#createForm input[name="attachment[]"]')) {
            VplSettlement.toast('error', 'Attachment is required.');
            return;
        }

        const fd = new FormData(form);

        $.ajax({
            url:         VplSettlement.routes.store,
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplSettlement.toast('success', r.success ?? 'Saved!');
            VplSettlementForm.hideModal('createModal');
            VplSettlementDatalist.refresh();
            VplSettlementForm.resetCreateModal();
        })
        .fail((x) => {
            VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    resetCreateModal() {
        document.getElementById('createForm').reset();
        VplSettlementForm.clearLines('create');
        document.getElementById('c_attachBody').innerHTML = `
            <tr id="c_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplSettlement.state.cAttachIdx = 1;
        VplSettlementForm.loadUsageOptions('create');
        VplSettlementForm.showFormView('create');
    },

    // ------------------------------------------------------------------
    // EDIT MODAL
    // ------------------------------------------------------------------

    openEdit(data) {
        const t = data.settlement;
        VplSettlement.state.currentViewId = t.id;

        document.getElementById('e_cpnyid_display').value    = t.cpnyid;
        document.getElementById('e_dept_display').value      = t.department;
        document.getElementById('e_usage_id_display').value  = t.usage_id;
        document.getElementById('e_remark').value             = t.settlement_remark ?? '';
        document.getElementById('e_title').textContent        = `Edit Settlement — ${t.settlement_id}`;

        const body = document.getElementById('e_detailBody');
        body.innerHTML = (data.details ?? []).map((d, i) => VplSettlementHelper.buildEditRow(i, d)).join('');
        (data.details ?? []).forEach((_, i) => VplSettlementForm.recomputeSisa('e', i));

        // Existing attachments
        const eAttachBody = document.getElementById('e_existAttachBody');
        eAttachBody.innerHTML = '';
        (data.attachments ?? []).forEach((a) => {
            const url = `/settlementvp/attachment/${a.id}/view`;
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

        document.getElementById('e_attachBody').innerHTML = `
            <tr id="e_attach_0">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]" class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1"></td>
            </tr>`;
        VplSettlement.state.eAttachIdx = 1;

        VplSettlementForm.showFormView('edit');
        VplSettlementForm.showModal('editModal');
    },

    initEditModal() {
        ['closeEditModal', 'closeEditModalFooter'].forEach((id) => {
            document.getElementById(id)?.addEventListener('click', () => VplSettlementForm.hideModal('editModal'));
        });

        document.getElementById('e_addAttach').addEventListener('click', () => {
            const html = VplSettlementHelper.buildAttachRow('e', VplSettlement.state.eAttachIdx++);
            document.getElementById('e_attachBody').insertAdjacentHTML('beforeend', html);
        });

        document.getElementById('e_attachBody').addEventListener('click', (e) => {
            const btn = e.target.closest('.e-remove-attach-btn');
            if (btn) document.getElementById(`e_attach_${btn.dataset.idx}`)?.remove();
        });

        document.getElementById('e_detailBody').addEventListener('input', (e) => {
            const input = e.target.closest('.e-qty-settlement-input');
            if (input) VplSettlementForm.recomputeSisa('e', parseInt(input.dataset.idx, 10));
        });

        document.getElementById('e_existAttachBody').addEventListener('click', async (e) => {
            const btn = e.target.closest('.e-del-exist-attach');
            if (!btn) return;
            const res = await VplSettlement.confirm({ title: 'Remove this attachment?', confirmText: 'Remove' });
            if (!res.isConfirmed) return;
            $.post(VplSettlement.routes.delAttach, { _token: VplSettlement.csrf(), detail_id: btn.dataset.attachId })
                .done(() => {
                    btn.closest('[data-attach-id]')?.remove();
                    VplSettlement.toast('success', 'Attachment removed.');
                })
                .fail(() => VplSettlement.toast('error', 'Remove failed.'));
        });

        document.getElementById('e_reviewBtn').addEventListener('click', () => VplSettlementForm.showPreview('edit'));
        document.getElementById('e_backToEditBtn').addEventListener('click', () => VplSettlementForm.showFormView('edit'));
        document.getElementById('e_confirmSubmitBtn').addEventListener('click', () => VplSettlementForm.submitEdit());
    },

    submitEdit() {
        const hasExistingAttach = document.getElementById('e_existAttachBody')?.children.length > 0;
        if (!hasExistingAttach && !VplSettlementForm.hasAnyFile('#editForm input[name="attachment[]"]')) {
            VplSettlement.toast('error', 'Attachment is required.');
            return;
        }

        const id   = VplSettlement.state.currentViewId;
        const form = document.getElementById('editForm');
        const fd   = new FormData(form);

        $.ajax({
            url:         VplSettlement.routes.update(id),
            type:        'POST',
            data:        fd,
            processData: false,
            contentType: false,
        })
        .done((r) => {
            VplSettlement.toast('success', r.success ?? 'Resubmitted!');
            VplSettlementForm.hideModal('editModal');
            VplSettlementDatalist.refresh();
        })
        .fail((x) => {
            VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Submit failed.');
        });
    },

    // ------------------------------------------------------------------
    // PREVIEW STEP — client-side only, no server round trip
    // ------------------------------------------------------------------

    // Blocks submit with a toast if any settlement line is missing its Qty Settlement value.
    validateLines(prefix) {
        let error = null;
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row, i) => {
            if (error) return;
            const input = row.querySelector(`.${prefix}-qty-settlement-input`);
            if (!input) return;
            if (input.value === '' || input.value === null) {
                error = `Row ${i + 1}: Qty Settlement is required.`;
            }
        });

        if (error) {
            VplSettlement.toast('error', error);
            return false;
        }
        return true;
    },

    collectLines(prefix) {
        const rows = [];
        document.querySelectorAll(`#${prefix}_detailBody tr[id^="${prefix}_row_"]`).forEach((row) => {
            const idx = row.dataset.idx;
            const get = (name) => row.querySelector(`[name="lines[${idx}][${name}]"]`)?.value ?? '';
            const productDisplay = row.querySelector('td:first-child')?.textContent?.trim();
            const qtyUsage = row.children[2]?.textContent?.trim() ?? '0';
            const qtyReturn = row.children[3]?.textContent?.trim() ?? '0';
            const qtySettlement = get('qty_settlement');
            const remaining = Number(row.dataset.remaining ?? 0);
            const sisa = remaining - Number(qtySettlement || 0);

            rows.push({
                product: productDisplay,
                qtyUsage,
                qtyReturn,
                qtySettlement,
                sisa,
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

        const rows = VplSettlementForm.collectLines(prefix);
        if (!rows.length) {
            VplSettlement.toast('error', 'No settlement lines to submit.');
            return;
        }

        if (!VplSettlementForm.validateLines(prefix)) return;

        const cpnyid = document.getElementById(`${prefix}_cpnyid`)?.value ?? document.getElementById('e_cpnyid_display')?.value ?? '';
        const dept   = document.getElementById(`${prefix}_department`)?.value ?? document.getElementById('e_dept_display')?.value ?? '';
        const usageId = document.getElementById(`${prefix}_usage_id`)?.value ?? document.getElementById('e_usage_id_display')?.value ?? '';
        const remark = document.getElementById(`${prefix}_remark`)?.value ?? '';

        const headerEl = document.getElementById(`${prefix}_previewHeader`);
        headerEl.innerHTML = `
            <div><div class="text-xs text-slate-500">Company</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${cpnyid}</div></div>
            <div><div class="text-xs text-slate-500">Department</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${dept}</div></div>
            <div><div class="text-xs text-slate-500">Usage Doc</div><div class="mt-1 font-medium text-slate-800 dark:text-slate-100">${usageId}</div></div>
            <div class="md:col-span-4"><div class="text-xs text-slate-500">Remark</div><div class="mt-1 text-slate-700 dark:text-slate-200">${remark || '—'}</div></div>
        `;

        const detailBody = document.getElementById(`${prefix}_previewDetailBody`);
        detailBody.innerHTML = rows.map((r) => `
            <tr>
                <td class="px-4 py-2">${r.product}</td>
                <td class="px-4 py-2 text-right font-semibold">${Number(r.qtyUsage).toLocaleString()}</td>
                <td class="px-4 py-2 text-right">${Number(r.qtyReturn).toLocaleString()}</td>
                <td class="px-4 py-2 text-right font-semibold text-indigo-600 dark:text-indigo-400">${Number(r.qtySettlement || 0).toLocaleString()}</td>
                <td class="px-4 py-2 text-right font-semibold">${Number(r.sisa).toLocaleString()}</td>
            </tr>
        `).join('');

        const attachBody = document.getElementById(`${prefix}_previewAttachBody`);
        const names = VplSettlementForm.collectAttachNames(prefix);
        attachBody.innerHTML = names.length
            ? names.map((n) => `<div class="py-1"><i class="fa-regular fa-file mr-2 text-xs"></i>${n}</div>`).join('')
            : 'No new attachments.';

        document.getElementById(`${prefix}_formView`).classList.add('hidden');
        document.getElementById(`${prefix}_previewView`).classList.remove('hidden');
        VplSettlementForm.toggleBtn(`${prefix}_reviewBtn`, false);
        VplSettlementForm.toggleBtn(`${prefix}_backToEditBtn`, true);
        VplSettlementForm.toggleBtn(`${prefix}_confirmSubmitBtn`, true);
    },

    showFormView(mode) {
        const prefix = mode === 'create' ? 'c' : 'e';
        document.getElementById(`${prefix}_formView`).classList.remove('hidden');
        document.getElementById(`${prefix}_previewView`).classList.add('hidden');
        VplSettlementForm.toggleBtn(`${prefix}_reviewBtn`, true);
        VplSettlementForm.toggleBtn(`${prefix}_backToEditBtn`, false);
        VplSettlementForm.toggleBtn(`${prefix}_confirmSubmitBtn`, false);
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

    hideModal(modalId) {
        const modal    = document.getElementById(modalId);
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            if (modalId === 'createModal') VplSettlement.clearUrl();
        }, 200);
    },
};
