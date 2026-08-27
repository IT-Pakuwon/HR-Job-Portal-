// ============================================================
// form.js — Voucher Product Receive
// Create modal, Edit modal, and all AJAX dropdown loaders
// ============================================================

const VplReceiveForm = {

    formatPrice(val) {
        const n = parseFloat(val);
        return isNaN(n) ? '—' : n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    },

    parsePrice(val) {
        const n = parseFloat(String(val ?? '').replace(/\./g, '').replace(',', '.'));
        return isNaN(n) ? 0 : n;
    },

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
                            .data('price', p.product_value ?? 0)
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
                                .data('price', p.product_value ?? 0)
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
            $row.find('.c-price-display').text(VplReceiveForm.formatPrice($opt.data('price')));
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

    // ============================================================
    // SUBMIT CONFIRMATION — shared between create & edit
    // ============================================================

    _escape(str) {
        return String(str ?? '').replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    },

    // Pending (not-yet-saved) rows from the detail table for the given prefix ('c' or 'e')
    collectRows(prefix) {
        const rows = [];
        $(`#${prefix}_detailBody tr`).each(function () {
            const $row    = $(this);
            const product = $row.find(`.${prefix}-product-sel option:selected`).text().trim();
            const qty     = $row.find('input[name*="[qty]"]').val();
            const uom     = $row.find(`.${prefix}-uom-display`).text().trim();
            const rawExp  = $row.find('input[type="date"]').val();
            const exp     = !rawExp ? '—' : (rawExp === '1900-01-01' ? 'No Expired' : (VplReceiveHelper.formatDateLong(rawExp) ?? rawExp));
            const whs     = $row.find(`.${prefix}-whs-sel`).val() || '—';
            const price   = $row.find(`.${prefix}-price-display`).text().trim();
            if (product && product !== 'Select Product' && qty) {
                rows.push({ product, qty, uom, exp, whs, price });
            }
        });
        return rows;
    },

    // Blocks submit with a toast if any row with a product selected is missing Qty or Dest. WHS.
    validateRows(prefix) {
        let error = null;
        $(`#${prefix}_detailBody tr`).each(function (i) {
            if (error) return;
            const $row    = $(this);
            const product = $row.find(`.${prefix}-product-sel option:selected`).text().trim();
            if (!product || product === 'Select Product') return;

            const qty = $row.find('input[name*="[qty]"]').val();
            const whs = $row.find(`.${prefix}-whs-sel`).val();

            if (!qty) error = `Row ${i + 1}: Qty is required.`;
            else if (!whs) error = `Row ${i + 1}: Dest. WHS is required.`;
        });

        if (error) {
            VplReceive.toast('error', error);
            return false;
        }
        return true;
    },

    // Already-saved detail lines shown in the Edit modal's "Existing Details" table
    collectExistingRows() {
        const rows = [];
        $('#e_existDetailBody tr').each(function () {
            const cells = $(this).find('td');
            if (cells.length < 6) return; // skip "No details." placeholder row
            rows.push({
                product: $(cells[0]).text().trim(),
                price:   $(cells[2]).text().trim(),
                qty:     $(cells[4]).text().trim(),
                uom:     $(cells[5]).text().trim(),
                exp:     $(cells[3]).text().trim(),
                whs:     $(cells[6]).text().trim(),
            });
        });
        return rows;
    },

    // Shows a review dialog listing every product/voucher line before it's saved.
    // Returns the Swal promise so callers can act on `result.isConfirmed`.
    confirmRows(rows, title) {
        const esc = VplReceiveForm._escape;
        const fmt = VplReceiveForm.formatPrice;
        const parse = VplReceiveForm.parsePrice;

        let grandTotal = 0;
        const tableRows = rows.map(r => {
            const total = parse(r.price) * parse(r.qty);
            grandTotal += total;
            return `
            <tr style="border-bottom:1px solid #e2e8f0">
                <td style="padding:6px 10px;text-align:left;font-size:12px">${esc(r.product)}</td>
                <td style="padding:6px 10px;text-align:right;font-size:12px">${esc(r.price)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.qty)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.uom)}</td>
                <td style="padding:6px 10px;text-align:right;font-size:12px;font-weight:600">${esc(fmt(total))}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px">${esc(r.exp)}</td>
                <td style="padding:6px 10px;text-align:center;font-size:12px;font-weight:600">${esc(r.whs)}</td>
            </tr>`;
        }).join('');

        const html = `
            <p style="margin-bottom:12px;font-size:13px;color:#475569">Please review before submitting for approval:</p>
            <div style="overflow-x:auto;border-radius:8px;border:1px solid #e2e8f0">
                <table style="width:100%;border-collapse:collapse">
                    <thead>
                        <tr style="background:#f8fafc">
                            <th style="padding:8px 10px;text-align:left;font-size:11px;text-transform:uppercase;color:#64748b">Product</th>
                            <th style="padding:8px 10px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b">Price</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Qty</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">UOM</th>
                            <th style="padding:8px 10px;text-align:right;font-size:11px;text-transform:uppercase;color:#64748b">Total Price</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Expired Date</th>
                            <th style="padding:8px 10px;text-align:center;font-size:11px;text-transform:uppercase;color:#64748b">Dest. WHS</th>
                        </tr>
                    </thead>
                    <tbody>${tableRows}</tbody>
                    <tfoot>
                        <tr style="background:#f8fafc">
                            <td colspan="4" style="padding:8px 10px;text-align:right;font-size:12px;font-weight:700;color:#334155">Grand Total</td>
                            <td style="padding:8px 10px;text-align:right;font-size:12px;font-weight:700;color:#334155">${esc(fmt(grandTotal))}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>`;

        return Swal.fire({
            title,
            html,
            icon:              'question',
            showCancelButton:  true,
            confirmButtonColor: '#0f172a',
            cancelButtonColor:  '#94a3b8',
            confirmButtonText: '<i class="fa-solid fa-paper-plane mr-1"></i> Yes, Submit',
            cancelButtonText:  'Review Again',
            width:             950,
        });
    },

    // True if any file input matching selector currently has a selected file
    hasAnyFile(selector) {
        let found = false;
        $(selector).each(function () {
            if (this.files && this.files.length > 0) found = true;
        });
        return found;
    },

    submitCreate() {
        if (!$('#c_remark').val()?.trim()) {
            Swal.fire({ icon: 'warning', title: 'Remark Required', text: 'Please enter a remark before submitting.' });
            return;
        }

        if (!VplReceiveForm.hasAnyFile('#createForm input[name="attachment[]"]')) {
            Swal.fire({ icon: 'warning', title: 'Attachment Required', text: 'Please attach at least one file before submitting.' });
            return;
        }

        if (!VplReceiveForm.validateRows('c')) return;

        const rows = VplReceiveForm.collectRows('c');

        if (rows.length === 0) {
            Swal.fire({ icon: 'warning', title: 'No Items', text: 'Please add at least one product before submitting.' });
            return;
        }

        VplReceiveForm.confirmRows(rows, 'Confirm Submission').then(result => {
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
                    VplReceive.toast('error', xhr.responseJSON?.error ?? xhr.responseJSON?.message ?? 'Error saving receive.');
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
            $row.find('.e-price-display').text(VplReceiveForm.formatPrice($opt.data('price')));
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

        // Existing details — with UOM + Price columns
        let dHtml = d.details.length === 0
            ? '<tr><td colspan="8" class="px-4 py-3 text-center text-xs text-slate-400">No details.</td></tr>'
            : '';
        d.details.forEach(row => {
            const rawExp = (row.expired_date || '').split('T')[0];
            const exp = (!rawExp || rawExp === '1900-01-01') ? 'No Expired' : (VplReceiveHelper.formatDateLong(rawExp) ?? rawExp);
            dHtml += `<tr>
                <td class="px-4 py-2 text-xs">${row.product_name || row.product_id}</td>
                <td class="px-4 py-2 text-xs">${row.product_source_tenant || '—'}</td>
                <td class="px-4 py-2 text-xs">${VplReceiveForm.formatPrice(row.product_price)}</td>
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
        if (!$('#e_remark').val()?.trim()) {
            Swal.fire({ icon: 'warning', title: 'Remark Required', text: 'Please enter a remark before submitting.' });
            return;
        }

        const hasExistingAttach = $('#e_existAttachBody .e-del-exist-attach').length > 0;
        if (!hasExistingAttach && !VplReceiveForm.hasAnyFile('#editForm input[name="attachment[]"]')) {
            Swal.fire({ icon: 'warning', title: 'Attachment Required', text: 'Please attach at least one file before submitting.' });
            return;
        }

        if (!VplReceiveForm.validateRows('e')) return;

        const doSubmit = () => {
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
                    VplReceive.toast('error', xhr.responseJSON?.error ?? xhr.responseJSON?.message ?? 'Error updating receive.');
                },
                complete() {
                    $btn.prop('disabled', false).html('<i class="fa-solid fa-paper-plane text-xs"></i> Resubmit Approval');
                },
            });
        };

        const rows = VplReceiveForm.collectExistingRows().concat(VplReceiveForm.collectRows('e'));
        if (rows.length === 0) {
            doSubmit();
            return;
        }

        VplReceiveForm.confirmRows(rows, 'Confirm Resubmission').then(result => {
            if (result.isConfirmed) doSubmit();
        });
    },
};
