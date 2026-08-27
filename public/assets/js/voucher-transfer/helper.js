const VplTransferHelper = {

    // Formats a date-ish string as "7 Agustus 2026" — no time. Returns null for
    // empty/placeholder dates (empty string, '1900-01-01') so callers can
    // supply their own fallback text (e.g. "—").
    formatExpDate(raw) {
        const d = String(raw ?? '').substring(0, 10);
        if (!d || d === '1900-01-01') return null;
        const parsed = new Date(`${d}T00:00:00`);
        if (isNaN(parsed)) return d;
        return parsed.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    },

    statusBadgeHTML(status, label) {
        const map = {
            P: 'bg-yellow-300/30 text-yellow-600',
            C: 'bg-green-300/30 text-green-600',
            R: 'bg-red-300/30 text-red-600',
            X: 'bg-red-300/30 text-red-600',
            D: 'bg-blue-300/30 text-blue-600',
        };
        const cls = map[status] ?? 'bg-slate-300/30 text-slate-600';
        return `<span class="inline-block rounded px-3 py-1.5 text-sm font-semibold ${cls}">${label}</span>`;
    },

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
            const title  = `Approval Level ${parseInt(ap.aprvid, 10)}`;
            const by     = ap.name || ap.aprvusername || null;
            const at     = ap.aprvdateafter || ap.aprvdatebefore || null;

            return `
                <div class="relative flex gap-4">
                    <div class="flex flex-col items-center">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${badgeColor(s)}">
                            ${icon(s)}
                        </div>
                        ${!isLast ? '<div class="mt-1 min-h-10 w-px flex-1 bg-slate-200 dark:bg-white/10"></div>' : ''}
                    </div>
                    <div class="min-w-0 flex-1 pb-6">
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
                <div class="space-y-4 p-4">
                    ${items}
                </div>
            </div>`;
    },

    /**
     * Builds a detail row for the create/edit form.
     * prefix = 'c' or 'e'
     * idx    = row index
     * fromWhs = pre-filled FROM warehouse (readonly)
     */
    buildDetailRow(prefix, idx, fromWhs = '') {
        const mode = prefix === 'c' ? 'create' : 'edit';
        return `
            <tr id="${prefix}_row_${idx}" data-idx="${idx}">
                <td class="px-3 py-2">
                    <input type="hidden" name="addmore[${idx}][from_whs_id]" class="${prefix}-from-whs-input" value="${fromWhs}">
                    <select class="${prefix}-from-whs-sel w-full" style="min-width:140px">
                        <option value="">Select WHS</option>
                    </select>
                </td>
                <td class="px-3 py-2">
                    <div class="flex items-center gap-1">
                        <input type="hidden" name="addmore[${idx}][product_id]"    class="${prefix}-product-id-input" value="">
                        <input type="hidden" name="addmore[${idx}][qty_available]" class="${prefix}-qty-avail-input"  value="0">
                        <input type="hidden" name="addmore[${idx}][expired_date]"  class="${prefix}-exp-input"        value="">
                        <span class="${prefix}-product-display block flex-1 truncate rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400" title="">— Select —</span>
                        <button type="button"
                            class="${prefix}-pick-product-btn inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white hover:bg-indigo-500"
                            data-idx="${idx}" data-mode="${mode}" title="Pick product">
                            <i class="fa-solid fa-magnifying-glass text-[10px]"></i>
                        </button>
                    </div>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-qty-avail-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-right text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">0</span>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-exp-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="addmore[${idx}][qty_transfer]" min="1" placeholder="0"
                        class="${prefix}-qty-transfer-input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2">
                    <input type="hidden" name="addmore[${idx}][to_whs_id]" class="${prefix}-to-whs-input" value="">
                    <select class="${prefix}-to-whs-sel w-full" style="min-width:140px">
                        <option value="">Select WHS</option>
                    </select>
                </td>
                <td class="px-3 py-2 text-center">
                    ${idx === 0 ? '' : `<button type="button" class="${prefix}-remove-row-btn text-red-400 hover:text-red-600" data-idx="${idx}"><i class="fa-solid fa-trash-can text-sm"></i></button>`}
                </td>
            </tr>
        `;
    },

    /**
     * Builds a Return Transfer detail row pre-filled from a reference transfer line
     * (see VplTransferController::getRefDetails). Warehouses and product are fixed
     * (read-only) — a return can only go back to where the product came from — the
     * user only edits Qty Transfer. Always removable, including the first row.
     */
    buildRefDetailRow(prefix, idx, line) {
        const expDisplay = VplTransferHelper.formatExpDate(line.expired_date) ?? '—';
        const avail      = Number(line.qty_returnable ?? 0);
        const nameEsc    = String(line.product_name ?? '').replace(/"/g, '&quot;');
        return `
            <tr id="${prefix}_row_${idx}" data-idx="${idx}">
                <td class="px-3 py-2">
                    <input type="hidden" name="addmore[${idx}][from_whs_id]" class="${prefix}-from-whs-input" value="${line.from_whs_id ?? ''}">
                    <span class="block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-white/[0.04] dark:text-slate-300">${line.from_whs_id ?? '—'}</span>
                </td>
                <td class="px-3 py-2">
                    <input type="hidden" name="addmore[${idx}][product_id]"    class="${prefix}-product-id-input" value="${line.product_id ?? ''}">
                    <input type="hidden" name="addmore[${idx}][qty_available]" class="${prefix}-qty-avail-input"  value="${avail}">
                    <input type="hidden" name="addmore[${idx}][expired_date]"  class="${prefix}-exp-input"        value="${line.expired_date ?? ''}">
                    <span class="${prefix}-product-display block truncate rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-700 dark:bg-white/[0.04] dark:text-slate-200" title="${nameEsc}">${line.product_name ?? ''}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-qty-avail-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-right text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">${avail.toLocaleString()}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-exp-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">${expDisplay}</span>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="addmore[${idx}][qty_transfer]" min="1" max="${avail}" placeholder="0"
                        class="${prefix}-qty-transfer-input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2">
                    <input type="hidden" name="addmore[${idx}][to_whs_id]" class="${prefix}-to-whs-input" value="${line.to_whs_id ?? ''}">
                    <span class="block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600 dark:bg-white/[0.04] dark:text-slate-300">${line.to_whs_id ?? '—'}</span>
                </td>
                <td class="px-3 py-2 text-center">
                    <button type="button" class="${prefix}-remove-row-btn text-red-400 hover:text-red-600" data-idx="${idx}"><i class="fa-solid fa-trash-can text-sm"></i></button>
                </td>
            </tr>
        `;
    },

    buildAttachRow(prefix, idx) {
        return `
            <tr id="${prefix}_attach_${idx}">
                <td class="py-1 pr-2">
                    <input type="file" name="attachment[]"
                        class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                </td>
                <td class="py-1 pl-1">
                    <button type="button" class="${prefix}-remove-attach-btn text-red-400 hover:text-red-600" data-idx="${idx}">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </td>
            </tr>
        `;
    },
};
