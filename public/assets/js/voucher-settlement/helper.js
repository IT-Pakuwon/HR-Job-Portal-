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
        if (!approvals?.length) {
            return '<p class="text-sm text-slate-400 p-4">No approval workflow.</p>';
        }

        return approvals.map((ap, i) => {
            const statusCls = {
                A: 'bg-green-500',
                R: 'bg-red-500',
                D: 'bg-yellow-500',
                X: 'bg-slate-400',
                P: 'bg-slate-300 dark:bg-slate-600',
            }[ap.status] ?? 'bg-slate-300';

            const statusIcon = {
                A: '<i class="fa-solid fa-check text-[10px] text-white"></i>',
                R: '<i class="fa-solid fa-xmark text-[10px] text-white"></i>',
                D: '<i class="fa-solid fa-rotate-left text-[10px] text-white"></i>',
                X: '<i class="fa-solid fa-minus text-[10px] text-white"></i>',
                P: '',
            }[ap.status] ?? '';

            const dateAfter = ap.aprvdateafter
                ? `<div class="text-[11px] text-slate-400 mt-0.5">${ap.aprvdateafter}</div>`
                : '';
            const dateBefore = ap.aprvdatebefore
                ? `<div class="text-[11px] text-slate-400">Assigned: ${ap.aprvdatebefore}</div>`
                : '';

            const connector = i < approvals.length - 1
                ? '<div class="ml-[11px] h-6 w-0.5 bg-slate-200 dark:bg-white/10"></div>'
                : '';

            return `
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 flex h-[22px] w-[22px] shrink-0 items-center justify-center rounded-full ${statusCls}">
                        ${statusIcon}
                    </div>
                    <div class="flex-1 pb-1">
                        <div class="text-sm font-semibold text-slate-800 dark:text-white">${ap.name ?? ap.aprvusername}</div>
                        <div class="text-[11px] text-slate-500">${ap.aprvusername}</div>
                        ${dateBefore}
                        ${dateAfter}
                    </div>
                </div>
                ${connector}
            `;
        }).join('');
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
