const VplSettlementHelper = {

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

    /** Derives Qty Return from stored settlement fields (not persisted on tr_vpl_settlement_detail). */
    deriveQtyReturn(d) {
        return Number(d.qty_usage ?? 0) - Number(d.qty_settlement ?? 0) - Number(d.qty_remain ?? 0);
    },

    formatExpiry(expired_date) {
        const exp = (expired_date ?? '').substring(0, 10);
        return (exp === '' || exp === '1900-01-01') ? '—' : exp;
    },

    /**
     * Create-mode row: one line loaded from the picked Usage doc.
     * line = { id, product_id, product_name, whs_id, expired_date, qty_usage, qty_return_usage, remaining }
     */
    buildCreateRow(idx, line) {
        const remaining = Number(line.remaining ?? 0);
        return `
            <tr id="c_row_${idx}" data-idx="${idx}" data-remaining="${remaining}">
                <td class="px-3 py-2">
                    <input type="hidden" name="lines[${idx}][usage_detail_id]" value="${line.id}">
                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">${line.product_id}</div>
                    <div class="text-xs text-slate-500">${line.product_name ?? ''}</div>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="lines[${idx}][settlement_remark]" placeholder="Remark"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2 text-right text-xs font-semibold text-slate-700 dark:text-slate-200">${Number(line.qty_usage ?? 0).toLocaleString()}</td>
                <td class="px-3 py-2 text-right text-xs text-slate-500 dark:text-slate-400">${Number(line.qty_return_usage ?? 0).toLocaleString()}</td>
                <td class="px-3 py-2">
                    <input type="number" name="lines[${idx}][qty_settlement]" min="0" max="${remaining}" value="${remaining}"
                        class="c-qty-settlement-input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white" data-idx="${idx}">
                </td>
                <td class="px-3 py-2 text-right text-xs font-semibold">
                    <span class="c-sisa-display" id="c_sisa_${idx}">0</span>
                </td>
            </tr>
        `;
    },

    /**
     * Edit-mode row: one existing TrxVplSettlementDetail line (Hold/Revise resubmit).
     */
    buildEditRow(idx, d) {
        const qtyReturn = VplSettlementHelper.deriveQtyReturn(d);
        const maxSettle = Number(d.qty_usage ?? 0) - qtyReturn;
        return `
            <tr id="e_row_${idx}" data-idx="${idx}" data-remaining="${maxSettle}">
                <td class="px-3 py-2">
                    <input type="hidden" name="lines[${idx}][settlement_detail_id]" value="${d.id}">
                    <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">${d.product_id}</div>
                    <div class="text-xs text-slate-500">${d.product_name ?? ''}</div>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="lines[${idx}][settlement_remark]" value="${(d.settlement_remark ?? '').replace(/"/g, '&quot;')}" placeholder="Remark"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2 text-right text-xs font-semibold text-slate-700 dark:text-slate-200">${Number(d.qty_usage ?? 0).toLocaleString()}</td>
                <td class="px-3 py-2 text-right text-xs text-slate-500 dark:text-slate-400">${qtyReturn.toLocaleString()}</td>
                <td class="px-3 py-2">
                    <input type="number" name="lines[${idx}][qty_settlement]" min="0" max="${maxSettle}" value="${d.qty_settlement ?? 0}"
                        class="e-qty-settlement-input w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white" data-idx="${idx}">
                </td>
                <td class="px-3 py-2 text-right text-xs font-semibold">
                    <span class="e-sisa-display" id="e_sisa_${idx}">${Number(d.qty_remain ?? 0).toLocaleString()}</span>
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
