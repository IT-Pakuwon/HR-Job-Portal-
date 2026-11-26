// ============================================================
// helper.js — Voucher Product Receive
// DOM-builder utilities and badge renderers
// ============================================================

const VplReceiveHelper = {

    // Formats a date-ish string as "17 Sep 2027" — no time. Returns null for
    // empty/placeholder dates (empty string, '1900-01-01') so callers can
    // supply their own fallback text (e.g. "No Expired" or "—").
    formatExpDate(raw) {
        const d = String(raw ?? '').substring(0, 10);
        if (!d || d === '1900-01-01') return null;
        const parsed = new Date(`${d}T00:00:00`);
        if (isNaN(parsed)) return d;
        return parsed.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
    },

    // Formats a date-ish string with the full month name, e.g. "7 August 2026".
    // Returns null for empty/placeholder dates so callers can supply their own fallback.
    formatDateLong(raw) {
        const d = String(raw ?? '').substring(0, 10);
        if (!d || d === '1900-01-01') return null;
        const parsed = new Date(`${d}T00:00:00`);
        if (isNaN(parsed)) return d;
        return parsed.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });
    },

    statusBadgeHTML(status, label) {
        const map = {
            P: 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30',
            C: 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30',
            R: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
            X: 'bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30',
            D: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30',
        };
        const cls = map[status] ?? 'bg-gray-300/30 text-gray-600';
        return `<span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${cls}">${label}</span>`;
    },

    approvalStepIcon(status) {
        if (status === 'A') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-500"><i class="fa-solid fa-check text-[10px] text-white"></i></div>';
        if (status === 'R') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-red-500"><i class="fa-solid fa-xmark text-[10px] text-white"></i></div>';
        if (status === 'D') return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-yellow-400"><i class="fa-solid fa-rotate-left text-[10px] text-white"></i></div>';
        return '<div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gray-200 dark:bg-white/10"><i class="fa-solid fa-clock text-[10px] text-gray-500 dark:text-gray-400"></i></div>';
    },

    approvalBadge(status) {
        switch (status) {
            case 'A': return '<span class="apv-badge" style="background:#d1fae5;color:#065f46">Approved</span>';
            case 'R': return '<span class="apv-badge" style="background:#fee2e2;color:#991b1b">Rejected</span>';
            case 'D': return '<span class="apv-badge" style="background:#fef3c7;color:#92400e">Revise</span>';
            default:  return '<span class="apv-badge" style="background:#e2e8f0;color:#475569">Waiting</span>';
        }
    },

    // Build one dynamic detail row — columns: Product | Tenant | Price | Qty | UOM | Expired Date | Dest WHS | Action
    buildDetailRow(prefix, idx) {
        const delBtn = idx > 0
            ? `<button type="button" class="${prefix}-del-row inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-500/10" data-idx="${idx}">
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
                <span class="${prefix}-tenant-display block rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-white/5 dark:bg-white/[0.04] text-sm text-gray-600 dark:text-gray-300">—</span>
            </td>
            <td class="px-4 py-2">
                <span class="${prefix}-price-display block rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-white/5 dark:bg-white/[0.04] text-sm text-gray-600 dark:text-gray-300">—</span>
            </td>
            <td class="px-4 py-2">
                <input type="number" name="addmore[${idx}][qty]" min="1" placeholder="Qty"
                    class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
            </td>
            <td class="px-4 py-2">
                <span class="${prefix}-uom-display block rounded-lg border border-gray-100 bg-gray-50 px-3 py-2 dark:border-white/5 dark:bg-white/[0.04] text-sm text-gray-600 dark:text-gray-300">—</span>
            </td>
            <td class="px-4 py-2">
                <input type="date" name="addmore[${idx}][expired_date]" min="${new Date().toISOString().split('T')[0]}"
                    class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
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
            ? `<button type="button" class="${prefix}-del-attach inline-flex h-8 w-8 items-center justify-center rounded-lg text-red-500 transition hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-500/10" data-idx="${idx}">
                   <i class="fa-solid fa-trash text-[10px]"></i>
               </button>`
            : '';
        return `
        <tr id="${prefix}_attach_${idx}">
            <td class="py-1 pr-2">
                <input type="file" name="attachment[]"
                    class="w-full rounded-lg border border-dashed border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 transition file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:border-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
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
            return `<div class="rounded-xl border border-dashed border-gray-300 bg-white px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800">No approval records.</div>`;
        }

        const badgeColor = (s) => {
            switch (s) {
                case 'A': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
                case 'R': return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
                case 'D': return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
                case 'P': return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
                default:  return 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400';
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
                default:  return `<span class="inline-flex shrink-0 rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-600 dark:bg-white/10 dark:text-gray-400">Waiting</span>`;
            }
        };

        const items = approvals.map((ap, index) => {
            const isLast = index === approvals.length - 1;
            const s      = (ap.status ?? '').toUpperCase();
            const title  = `Approval Level ${parseInt(ap.aprvid, 10)}`;
            const by     = ap.name || ap.aprvusername || null;
            const at     = ap.aprvdateafter || ap.aprvdatebefore || null;

            return `
                <div class="relative flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${badgeColor(s)}">
                            ${icon(s)}
                        </div>
                        ${!isLast ? '<div class="mt-1 min-h-10 w-px flex-1 bg-gray-200 dark:bg-white/10"></div>' : ''}
                    </div>
                    <div class="min-w-0 flex-1 pb-6">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">${title}</p>
                                ${by ? `<p class="mt-1 text-sm font-semibold text-gray-700 dark:text-gray-200">${by}</p>` : ''}
                                ${at ? `<p class="mt-1 text-xs text-gray-400 dark:text-gray-500">${at}</p>` : ''}
                            </div>
                            ${pill(s)}
                        </div>
                    </div>
                </div>`;
        }).join('');

        return `
            <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Approval Workflow</h3>
                </div>
                <div class="space-y-4 p-4">
                    ${items}
                </div>
            </div>`;
    },
};
