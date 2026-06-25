// ============================================================
// helper.js — Voucher Product Receive
// DOM-builder utilities and badge renderers
// ============================================================

const VplReceiveHelper = {

    statusBadgeHTML(status, label) {
        const map = {
            P: 'background:#FFCD05;color:#000',
            C: 'background:#05A801;color:#fff',
            R: 'background:#EA002F;color:#fff',
            X: 'background:#6b7280;color:#fff',
            D: 'background:#3c87e2;color:#fff',
        };
        const style = map[status] ?? 'background:#e5e7eb;color:#374151';
        return `<span class="rounded-full px-3 py-1 text-xs font-semibold" style="${style}">${label || status}</span>`;
    },

    approvalBadge(status) {
        switch (status) {
            case 'A': return '<span class="apv-badge" style="background:#d1fae5;color:#065f46">Approved</span>';
            case 'R': return '<span class="apv-badge" style="background:#fee2e2;color:#991b1b">Rejected</span>';
            case 'D': return '<span class="apv-badge" style="background:#fef3c7;color:#92400e">Revise</span>';
            default:  return '<span class="apv-badge" style="background:#e2e8f0;color:#475569">Waiting</span>';
        }
    },

    // Build one dynamic detail row — columns: Product | Tenant | Qty | UOM | Expired Date | Dest WHS | Action
    buildDetailRow(prefix, idx) {
        const delBtn = idx > 0
            ? `<button type="button" class="${prefix}-del-row rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50" data-idx="${idx}">
                   <i class="fa-solid fa-trash text-[10px]"></i>
               </button>`
            : '';
        return `
        <tr id="${prefix}_row_${idx}">
            <td class="px-4 py-2">
                <select name="addmore[${idx}][product_name]"
                    class="w-full ${prefix}-product-sel"
                    style="min-width:200px">
                    <option value="">Select Product</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <span class="${prefix}-tenant-display block rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="addmore[${idx}][qty]" min="1" placeholder="Qty"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
            </td>
            <td class="px-4 py-2">
                <span class="${prefix}-uom-display block rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
            </td>
            <td class="px-4 py-2">
                <input type="date" name="addmore[${idx}][expired_date]"
                    class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
            </td>
            <td class="${prefix}-whs-td px-4 py-2">
                <select name="addmore[${idx}][whs_id]"
                    class="w-full ${prefix}-whs-sel"
                    style="min-width:160px">
                    <option value="">Select WHS</option>
                </select>
            </td>
            <td class="px-4 py-2">${delBtn}</td>
        </tr>`;
    },

    buildAttachRow(prefix, idx) {
        const delBtn = idx > 0
            ? `<button type="button" class="${prefix}-del-attach rounded px-2 py-1 text-xs text-red-500 hover:bg-red-50" data-idx="${idx}">
                   <i class="fa-solid fa-trash text-[10px]"></i>
               </button>`
            : '';
        return `
        <tr id="${prefix}_attach_${idx}">
            <td class="py-1 pr-2">
                <input type="file" name="attachment[]"
                    class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
            </td>
            <td class="py-1 pl-1">${delBtn}</td>
        </tr>`;
    },

    initRowSelect2(idx) {
        $(`select[name="addmore[${idx}][product_name]"],
           select[name="addmore[${idx}][whs_id]"]`).select2({
            placeholder: 'Select...',
            allowClear:  true,
            width:       '100%',
        });
    },

    attachIcon(ext) {
        if (ext === 'pdf')                        return 'fa-file-pdf';
        if (ext === 'xls' || ext === 'xlsx')      return 'fa-file-excel';
        if (ext === 'doc' || ext === 'docx')      return 'fa-file-word';
        return 'fa-file-image';
    },
};
