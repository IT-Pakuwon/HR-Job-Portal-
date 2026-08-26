const VplUsageHelper = {

    // Formats a date-ish string as "17 Sep 2027" — no time. Returns null for
    // empty/placeholder dates (empty string, '1900-01-01') so callers can
    // supply their own fallback text (e.g. "—").
    formatExpDate(raw) {
        const d = String(raw ?? '').substring(0, 10);
        if (!d || d === '1900-01-01') return null;
        const parsed = new Date(`${d}T00:00:00`);
        if (isNaN(parsed)) return d;
        return parsed.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
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
                    <input type="hidden" name="addmore[${idx}][product_id]"    class="${prefix}-product-id-input" value="">
                    <input type="hidden" name="addmore[${idx}][qty_available]" class="${prefix}-qty-avail-input"  value="0">
                    <input type="hidden" name="addmore[${idx}][expired_date]"  class="${prefix}-exp-input"        value="">
                    <span class="${prefix}-product-display block truncate rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-500 dark:bg-white/[0.04] dark:text-slate-400" title="">— Select —</span>
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
                    <input type="number" name="addmore[${idx}][qty]" min="1" placeholder="0" readonly
                        class="w-full cursor-not-allowed rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-400"
                        title="Qty is set when the product is added — remove and re-add to change it.">
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
