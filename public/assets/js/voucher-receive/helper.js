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

    approvalStepIcon(status) {
        if (status === 'A') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500"><i class="fa-solid fa-check text-[10px] text-white"></i></div>';
        if (status === 'R') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-500"><i class="fa-solid fa-xmark text-[10px] text-white"></i></div>';
        if (status === 'D') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-yellow-400"><i class="fa-solid fa-rotate-left text-[10px] text-white"></i></div>';
        return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-200 dark:bg-white/10"><i class="fa-solid fa-clock text-[10px] text-slate-500 dark:text-slate-400"></i></div>';
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

    // --------------------------------------------------------
    // RENDER APPROVAL TIMELINE  (mirrors VoucherTaxiHelper.renderTimeline)
    // --------------------------------------------------------
    renderTimeline(approvals) {
        if (!approvals || approvals.length === 0) {
            return `<div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-white/[0.02]">No approval records.</div>`;
        }

        const badgeColor = (s) => {
            switch (s) {
                case 'A': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
                case 'R': return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
                case 'D': return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
                case 'P': return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
                default:  return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
            }
        };

        const icon = (s) => {
            switch (s) {
                case 'A': return '<i class="fa-solid fa-check text-xs"></i>';
                case 'R': return '<i class="fa-solid fa-xmark text-xs"></i>';
                case 'D': return '<i class="fa-solid fa-rotate-left text-xs"></i>';
                default:  return '<i class="fa-solid fa-clock text-xs"></i>';
            }
        };

        const pill = (s) => {
            switch (s) {
                case 'A': return `<span class="inline-flex shrink-0 rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Approved</span>`;
                case 'R': return `<span class="inline-flex shrink-0 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-300">Rejected</span>`;
                case 'D': return `<span class="inline-flex shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Revise</span>`;
                default:  return `<span class="inline-flex shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-400">Waiting</span>`;
            }
        };

        const items = approvals.map((ap, index) => {
            const isLast = index === approvals.length - 1;
            const s      = (ap.status ?? '').toUpperCase();
            const title  = `Approval Level ${ap.aprvid}`;
            const by     = ap.name || ap.aprvusername || null;
            const at     = ap.aprvdateafter || ap.aprvdatebefore || null;

            return `
                <div class="relative flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${badgeColor(s)}">
                            ${icon(s)}
                        </div>
                        ${!isLast ? '<div class="mt-1 min-h-6 w-px flex-1 bg-slate-200 dark:bg-white/10"></div>' : ''}
                    </div>
                    <div class="min-w-0 flex-1 pb-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">${title}</p>
                                ${by ? `<p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">${by}</p>` : ''}
                                ${at ? `<p class="mt-1 text-xs text-slate-400 dark:text-slate-500">${at}</p>` : ''}
                            </div>
                            ${pill(s)}
                        </div>
                    </div>
                </div>`;
        }).join('');

        return `
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Approval Workflow</h3>
                </div>
                <div class="space-y-2 p-4">
                    ${items}
                </div>
            </div>`;
    },
};
