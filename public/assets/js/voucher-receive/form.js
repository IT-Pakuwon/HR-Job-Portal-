// ============================================================
// form.js — Voucher Product Receive
// Create modal, Edit modal, and all AJAX dropdown loaders
// ============================================================

const VplReceiveForm = {

    // ============================================================
    // AJAX LOADERS
    // ============================================================

    // Products — filtered by company + vp_type; tenant comes from master product, not filtered
    loadProductsForRow(mode, rowIdx) {
        const cpnyid = mode === 'create' ? $('#c_cpnyid').val() : $('#e_cpnyid').val();
        const vpType = mode === 'create' ? $('#c_vp_type').val() : $('#e_vp_type').val();
        if (!cpnyid || !vpType) return;

        const sel = `select[name="addmore[${rowIdx}][product_name]"]`;
        $.post(VplReceive.routes.products,
            { cpnyid, vp_type: vpType, _token: VplReceive.csrf() },
            function (data) {
                $(sel).empty().append('<option value="">Select Product</option>');
                data.forEach(p => {
                    $(sel).append(
                        $('<option>', { value: p.product_id, text: p.product_label })
                            .data('uom', p.product_uom ?? '')
                            .data('tenant', p.product_source_tenant ?? '—')
                    );
                });
                $(sel).trigger('change.select2');
            }
        );
    },

    loadProductsForAllRows(mode) {
        const cpnyid = mode === 'create' ? $('#c_cpnyid').val() : $('#e_cpnyid').val();
        const vpType = mode === 'create' ? $('#c_vp_type').val() : $('#e_vp_type').val();
        if (!cpnyid || !vpType) return;

        const prefix = mode === 'create' ? 'c' : 'e';
        $.post(VplReceive.routes.products,
            { cpnyid, vp_type: vpType, _token: VplReceive.csrf() },
            function (data) {
                $(`.${prefix}-product-sel`).each(function () {
                    const cur = $(this).val();
                    $(this).empty().append('<option value="">Select Product</option>');
                    data.forEach(p => {
                        $(this).append(
                            $('<option>', { value: p.product_id, text: p.product_label, selected: p.product_id === cur })
                                .data('uom', p.product_uom ?? '')
                                .data('tenant', p.product_source_tenant ?? '—')
                        );
                    });
                    $(this).trigger('change.select2');
                });
            }
        );
    },

    loadWhsForRow(mode, rowIdx) {
        const cpnyid = mode === 'create' ? $('#c_cpnyid').val()     : $('#e_cpnyid').val();
        const dept   = mode === 'create' ? $('#c_department').val() : $('#e_dept').val();
        const vpType = mode === 'create' ? $('#c_vp_type').val()    : $('#e_vp_type').val();
        if (!cpnyid || !dept || !vpType) return;

        const sel    = `select[name="addmore[${rowIdx}][whs_id]"]`;
        const prefix = mode === 'create' ? 'c' : 'e';
        $.post(VplReceive.routes.warehouse,
            { cpnyid, department: dept, vp_type: vpType, _token: VplReceive.csrf() },
            function (data) {
                $(sel).empty().append('<option value="">Select WHS</option>');
                data.forEach(w => $(sel).append(`<option value="${w.whs_id}">${w.whs_id}</option>`));
                $(sel).trigger('change.select2');
                VplReceiveForm._applyWhsState(prefix, data.length > 0);
            }
        );
    },

    loadWhsForAllRows(mode) {
        const cpnyid = mode === 'create' ? $('#c_cpnyid').val()     : $('#e_cpnyid').val();
        const dept   = mode === 'create' ? $('#c_department').val() : $('#e_dept').val();
        const vpType = mode === 'create' ? $('#c_vp_type').val()    : $('#e_vp_type').val();
        if (!cpnyid || !dept || !vpType) return;

        const prefix = mode === 'create' ? 'c' : 'e';
        $.post(VplReceive.routes.warehouse,
            { cpnyid, department: dept, vp_type: vpType, _token: VplReceive.csrf() },
            function (data) {
                $(`.${prefix}-whs-sel`).each(function () {
                    const cur = $(this).val();
                    $(this).empty().append('<option value="">Select WHS</option>');
                    data.forEach(w => $(this).append(`<option value="${w.whs_id}" ${w.whs_id === cur ? 'selected' : ''}>${w.whs_id}</option>`));
                    $(this).trigger('change.select2');
                });
                VplReceiveForm._applyWhsState(prefix, data.length > 0);
            }
        );
    },

    _applyWhsState(prefix, hasWhs) {
        $(`.${prefix}-whs-th, .${prefix}-whs-td`).toggle(hasWhs);
        if (prefix === 'c') {
            $('#c_whs_warning').toggle(!hasWhs);
            $('#submitCreateBtn').prop('disabled', !hasWhs).toggleClass('opacity-50 cursor-not-allowed', !hasWhs);
        }
    },

    checkProductExpiry(productId, $dateInput) {
        if (!productId) return;
        $.post(VplReceive.routes.prodDetail, { product_id: productId, _token: VplReceive.csrf() }, function (data) {
            if (data && data.product_check_exp === 0) {
                $dateInput.val('1900-01-01').prop('disabled', true);
            } else {
                $dateInput.val('').prop('disabled', false);
            }
        });
    },

    resetProductSelects(prefix) {
        $(`.${prefix}-product-sel`)
            .empty()
            .append('<option value="">Select Product</option>')
            .trigger('change.select2');
    },

    // ============================================================
    // CREATE MODAL
    // ============================================================

    initCreateModal() {
        // Open
        $('#openCreateBtn').on('click', () => {
            VplReceiveForm.resetCreateForm();
            VplReceiveModal.open('createModal');
            // Auto-trigger loads from pre-selected company / dept / type
            VplReceiveForm._triggerCreateInitialLoads();
        });

        // Close (X only — no backdrop click)
        $('#closeCreateModal, #closeCreateModalFooter').on('click', () => VplReceiveModal.close('createModal'));

        // Cascading changes
        $('#c_cpnyid').on('change', () => {
            VplReceiveForm.resetProductSelects('c');
            VplReceiveForm.loadWhsForAllRows('create');
            VplReceiveForm.loadProductsForAllRows('create');
        });
        $('#c_department').on('change', () => VplReceiveForm.loadWhsForAllRows('create'));

        $('#c_vp_type').on('change', () => {
            VplReceiveForm.resetProductSelects('c');
            VplReceiveForm.loadWhsForAllRows('create');
            VplReceiveForm.loadProductsForAllRows('create');
        });

        // Dynamic detail rows
        $('#c_addRow').on('click', () => {
            VplReceive.state.cRowIdx++;
            const idx = VplReceive.state.cRowIdx;
            $('#c_detailBody').append(VplReceiveHelper.buildDetailRow('c', idx));
            VplReceiveHelper.initRowSelect2(idx);
            VplReceiveForm.loadProductsForRow('create', idx);
            VplReceiveForm.loadWhsForRow('create', idx);
        });

        // Dynamic attachment rows
        $('#c_addAttach').on('click', () => {
            VplReceive.state.cAttachIdx++;
            $('#c_attachBody').append(VplReceiveHelper.buildAttachRow('c', VplReceive.state.cAttachIdx));
        });

        // Remove handlers
        $(document).on('click', '.c-del-row',    function () { $(`#c_row_${$(this).data('idx')}`).remove(); });
        $(document).on('click', '.c-del-attach', function () { $(`#c_attach_${$(this).data('idx')}`).remove(); });

        // Product selection → check expiry + populate UOM + populate Tenant
        $(document).on('change', '.c-product-sel', function () {
            const $row   = $(this).closest('tr');
            const $opt   = $(this).find('option:selected');
            VplReceiveForm.checkProductExpiry($(this).val(), $row.find('input[type="date"]'));
            $row.find('.c-uom-display').text($opt.data('uom') || '—');
            $row.find('.c-tenant-display').text($opt.data('tenant') || '—');
        });

        // Submit
        $('#submitCreateBtn').on('click', () => VplReceiveForm.submitCreate());
    },

    _triggerCreateInitialLoads() {
        const cpnyid = $('#c_cpnyid').val();
        const dept   = $('#c_department').val();
        const vpType = $('#c_vp_type').val();
        if (cpnyid && vpType) VplReceiveForm.loadProductsForAllRows('create');
        if (cpnyid && dept && vpType) VplReceiveForm.loadWhsForAllRows('create');
    },

    submitCreate() {
        // Collect detail rows for confirmation
        const rows = [];
        $('#c_detailBody tr').each(function () {
            const $row    = $(this);
            const product = $row.find('.c-product-sel option:selected').text().trim();
            const qty     = $row.find('input[name*="[qty]"]').val();
            const uom     = $row.find('.c-uom-display').text().trim();
            const exp     = $row.find('input[type="date"]').val() || '—';
            const whs     = $row.find('.c-whs-sel').val() || '—';
            if (product && product !== 'Select Product' && qty) {
                rows.push({ product, qty, uom, exp, whs });
            }
        });

        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one product before submitting.' });
            return;
        }

        const tableRows = rows.map(r => `
            <tr style="border-bottom:1px solid #e2e8f0">
                <td style="padding:6px 10px;text-align:left;font-size:12px">${r.product}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${r.qty}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${r.uom}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${r.exp}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px;font-weight:600">${r.whs}</td>
            </tr>`).join('');

        const html = `
            <p style="margin-bottom:12px;font-size:13px;color:#475569">Please review before submitting for approval:</p>
            <div style="overflow-x:auto;border-radius:8px;border:1px solid #e2e8f0">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Product</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Qty</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">UOM</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Expired Date</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Dest. WHS</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                </table>
            </div>`;

        Swal.fire({
            title:             'Confirm Submission',
            html,
            icon:              'question',
            showCancelButton:  true,
            confirmButtonColor: '#0f172a',
            cancelButtonColor:  '#94a3b8',
            confirmButtonText: '<i class="fa-solid fa-paper-plane mr-1"></i> Yes, Submit',
            cancelButtonText:  'Review Again',
            width:             700,
        }).then(result => {
            if (!result.isConfirmed) return;

            const $btn = $('#submitCreateBtn')
                .prop('disabled', true)
                .html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...');

            $.ajax({
                type:        'POST',
                url:         VplReceive.routes.store,
                data:        new FormData($('#createForm')[0]),
                contentType: false,
                processData: false,
                headers:     { 'X-CSRF-TOKEN': VplReceive.csrf() },
                success() {
                    VplReceiveModal.close('createModal');
                    VplReceive.toast('success', 'Receive saved and submitted for approval!');
                    setTimeout(() => location.reload(), 1200);
                },
                error(xhr) {
                    VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error saving receive.');
                },
                complete() {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane text-xs"></i> Submit Approval');
                },
            });
        });
    },

    resetCreateForm() {
        $('#createForm')[0].reset();
        VplReceive.state.cRowIdx    = 0;
        VplReceive.state.cAttachIdx = 0;

        // Reset detail table to one empty row
        $('#c_detailBody').html(VplReceiveHelper.buildDetailRow('c', 0));
        VplReceiveHelper.initRowSelect2(0);

        // Reset attachment table
        $('#c_attachBody').html(VplReceiveHelper.buildAttachRow('c', 0));

        // Clear product selects
        VplReceiveForm.resetProductSelects('c');
    },

    // ============================================================
    // EDIT MODAL
    // ============================================================

    initEditModal() {
        // Close (X only)
        $('#closeEditModal, #closeEditModalFooter').on('click', () => VplReceiveModal.close('editModal'));

        // Dynamic new detail rows
        $('#e_addRow').on('click', () => {
            VplReceive.state.eRowIdx++;
            const idx = VplReceive.state.eRowIdx;
            $('#e_detailBody').append(VplReceiveHelper.buildDetailRow('e', idx));
            VplReceiveHelper.initRowSelect2(idx);
            VplReceiveForm.loadProductsForRow('edit', idx);
            VplReceiveForm.loadWhsForRow('edit', idx);
        });

        // Dynamic new attachment rows
        $('#e_addAttach').on('click', () => {
            VplReceive.state.eAttachIdx++;
            $('#e_attachBody').append(VplReceiveHelper.buildAttachRow('e', VplReceive.state.eAttachIdx));
        });

        // Remove
        $(document).on('click', '.e-del-row',    function () { $(`#e_row_${$(this).data('idx')}`).remove(); });
        $(document).on('click', '.e-del-attach', function () { $(`#e_attach_${$(this).data('idx')}`).remove(); });

        // Product selection → check expiry + populate UOM + populate Tenant
        $(document).on('change', '.e-product-sel', function () {
            const $row = $(this).closest('tr');
            const $opt = $(this).find('option:selected');
            VplReceiveForm.checkProductExpiry($(this).val(), $row.find('input[type="date"]'));
            $row.find('.e-uom-display').text($opt.data('uom') || '—');
            $row.find('.e-tenant-display').text($opt.data('tenant') || '—');
        });

        // Delete existing detail line via AJAX
        $(document).on('click', '.e-del-exist-detail', function () {
            const $row = $(this).closest('tr');
            $.post(VplReceive.routes.delDetail, { detail_id: $(this).data('id'), _token: VplReceive.csrf() }, function () {
                $row.remove();
                VplReceive.toast('success', 'Detail deleted.');
            }).fail(() => VplReceive.toast('error', 'Failed to delete detail.'));
        });

        // Delete existing attachment via AJAX
        $(document).on('click', '.e-del-exist-attach', function () {
            const $div = $(this).closest('div');
            $.post(VplReceive.routes.delAttach, { detail_id: $(this).data('id'), _token: VplReceive.csrf() }, function () {
                $div.remove();
                VplReceive.toast('success', 'Attachment deleted.');
            }).fail(() => VplReceive.toast('error', 'Failed to delete attachment.'));
        });

        // Submit
        $('#submitEditBtn').on('click', () => VplReceiveForm.submitEdit());
    },

    populateEditModal(d) {
        const r = d.receive;
        VplReceive.state.eRowIdx    = 0;
        VplReceive.state.eAttachIdx = 0;

        $('#e_title').text(`Edit — ${r.receive_id}`);
        $('#e_cpnyid_display').val(r.cpnyid);
        $('#e_cpnyid').val(r.cpnyid);
        $('#e_dept_display').val(r.department);
        $('#e_dept').val(r.department);
        $('#e_vp_type_display').val(d.vp_label);
        $('#e_vp_type').val(r.vp_type);

        $('#e_remark').val(r.receive_remark || '');
        $('#e_receive_type').val(r.receive_type).trigger('change.select2');
        $('#e_source_dept').val(r.source_receive_dept).trigger('change.select2');

        // Existing details — with UOM column
        let dHtml = d.details.length === 0
            ? '<tr><td colspan="7" class="px-4 py-3 text-center text-xs text-slate-400">No details.</td></tr>'
            : '';
        d.details.forEach(row => {
            const exp = row.expired_date === '1900-01-01' ? 'No Expired' : row.expired_date;
            dHtml += `<tr>
                <td class="px-4 py-2 text-xs">${row.product_name || row.product_id}</td>
                <td class="px-4 py-2 text-xs">${row.product_source_tenant || '—'}</td>
                <td class="px-4 py-2 text-xs">${exp}</td>
                <td class="px-4 py-2 text-xs font-semibold">${row.qty_receive}</td>
                <td class="px-4 py-2 text-xs">${row.product_uom || '—'}</td>
                <td class="px-4 py-2 text-xs">${row.whs_id}</td>
                <td class="px-4 py-2">
                    <button type="button" class="e-del-exist-detail rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50" data-id="${row.id}">Del</button>
                </td>
            </tr>`;
        });
        $('#e_existDetailBody').html(dHtml);

        // Existing attachments
        let aHtml = d.attachments.length === 0
            ? '<div class="p-4 text-xs text-slate-400">No attachments.</div>'
            : '';
        d.attachments.forEach(a => {
            aHtml += `<div class="flex items-center justify-between px-5 py-2 hover:bg-slate-50">
                <span class="text-sm text-slate-700">${a.name}</span>
                <button type="button" class="e-del-exist-attach rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50" data-id="${a.id}">Del</button>
            </div>`;
        });
        $('#e_existAttachBody').html(aHtml);

        // Fresh new-line row + load products/whs for edit context
        $('#e_detailBody').html(VplReceiveHelper.buildDetailRow('e', 0));
        VplReceiveHelper.initRowSelect2(0);
        VplReceiveForm.loadProductsForRow('edit', 0);
        VplReceiveForm.loadWhsForRow('edit', 0);
        $('#e_attachBody').html(VplReceiveHelper.buildAttachRow('e', 0));
    },

    submitEdit() {
        const id   = VplReceive.state.currentViewId;
        const $btn = $('#submitEditBtn')
            .prop('disabled', true)
            .html('<i class="fa-solid fa-spinner fa-spin mr-1"></i> Saving...');

        $.ajax({
            type:        'POST',
            url:         VplReceive.routes.update(id),
            data:        new FormData($('#editForm')[0]),
            contentType: false,
            processData: false,
            headers:     { 'X-CSRF-TOKEN': VplReceive.csrf() },
            success() {
                VplReceiveModal.close('editModal');
                VplReceive.toast('success', 'Receive updated and resubmitted!');
                setTimeout(() => location.reload(), 1200);
            },
            error(xhr) {
                VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error updating receive.');
            },
            complete() {
                $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane text-xs"></i> Resubmit Approval');
            },
        });
    },
};
