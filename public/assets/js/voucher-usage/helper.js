const VplUsageHelper = {

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

    /**
     * Builds a detail row for the create/edit form.
     * prefix  = 'c' or 'e'
     * idx     = row index
     * whsId   = pre-filled warehouse (readonly, resolved from cpny+dept+vp_type)
     * origin  = when set (Return mode), the row is seeded from a referenced Usage
     *           line and the product/whs/expiry cells become read-only.
     */
    /** Purpose dropdown options, sourced from ms_category (doctype=VPU, categoryid=type, groups=PURPOSE). */
    purposeOptionsHTML(selected = '') {
        const purposes = window.VplUsageConfig?.purposes ?? [];
        const opts = purposes.map((p) => {
            const safe = String(p).replace(/"/g, '&quot;');
            return `<option value="${safe}" ${p === selected ? 'selected' : ''}>${safe}</option>`;
        }).join('');
        return `<option value="">Select...</option>${opts}`;
    },

    buildDetailRow(prefix, idx, whsId = '', origin = null) {
        const mode = prefix === 'c' ? 'create' : 'edit';

        if (origin) {
            const exp = (origin.expired_date ?? '').substring(0, 10);
            const expDisplay = (exp === '' || exp === '1900-01-01') ? '—' : exp;
            return `
                <tr id="${prefix}_row_${idx}" data-idx="${idx}">
                    <td class="px-3 py-2">
                        <input type="hidden" name="addmore[${idx}][product_id]" value="${origin.product_id}">
                        <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">${origin.product_id}</div>
                        <div class="text-xs text-slate-500">${origin.product_name ?? ''}</div>
                    </td>
                    <td class="px-3 py-2">
                        <input type="hidden" name="addmore[${idx}][whs_id]" value="${origin.whs_id}">
                        <span class="block rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 dark:bg-white/[0.04] dark:text-slate-200">${origin.whs_id}</span>
                    </td>
                    <td class="px-3 py-2">
                        <span class="block rounded-lg bg-slate-50 px-3 py-2 text-xs text-right text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">${Number(origin.qty_remaining ?? 0).toLocaleString()}</span>
                    </td>
                    <td class="px-3 py-2">
                        <input type="hidden" name="addmore[${idx}][expired_date]" value="${origin.expired_date ?? ''}">
                        <span class="block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">${expDisplay}</span>
                    </td>
                    <td class="px-3 py-2">
                        <input type="number" name="addmore[${idx}][qty]" min="1" max="${origin.qty_remaining ?? 0}" value="${origin.qty_remaining ?? 0}"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                    </td>
                    <td class="px-3 py-2">
                        <select name="addmore[${idx}][purpose_id]"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                            ${VplUsageHelper.purposeOptionsHTML(origin.purpose_id ?? '')}
                        </select>
                    </td>
                    <td class="px-3 py-2">
                        <input type="text" name="addmore[${idx}][purpose_remark]" value="${(origin.purpose_remark ?? '').replace(/"/g, '&quot;')}" placeholder="Remark"
                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                    </td>
                    <td class="px-3 py-2 text-center">
                        <button type="button" class="${prefix}-remove-row-btn text-red-400 hover:text-red-600" data-idx="${idx}"><i class="fa-solid fa-trash-can text-sm"></i></button>
                    </td>
                </tr>
            `;
        }

        return `
            <tr id="${prefix}_row_${idx}" data-idx="${idx}">
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
                    <input type="hidden" name="addmore[${idx}][whs_id]" class="${prefix}-whs-input" value="${whsId}">
                    <span class="${prefix}-whs-display block rounded-lg bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-700 dark:bg-white/[0.04] dark:text-slate-200">${whsId || '—'}</span>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-qty-avail-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-right text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">0</span>
                </td>
                <td class="px-3 py-2">
                    <span class="${prefix}-exp-display block rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
                </td>
                <td class="px-3 py-2">
                    <input type="number" name="addmore[${idx}][qty]" min="1" placeholder="0"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2">
                    <select name="addmore[${idx}][purpose_id]"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                        ${VplUsageHelper.purposeOptionsHTML()}
                    </select>
                </td>
                <td class="px-3 py-2">
                    <input type="text" name="addmore[${idx}][purpose_remark]" placeholder="Remark"
                        class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220] dark:text-white">
                </td>
                <td class="px-3 py-2 text-center">
                    ${idx === 0 ? '' : `<button type="button" class="${prefix}-remove-row-btn text-red-400 hover:text-red-600" data-idx="${idx}"><i class="fa-solid fa-trash-can text-sm"></i></button>`}
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
