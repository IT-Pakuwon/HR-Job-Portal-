// ============================================================
// tracking.js — Voucher Taxi
// Approval timeline — style matches BookingCarHelper.renderTimeline
// ============================================================

const VoucherTaxiTracking = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        autoRefreshTimer: null,
    },

    // --------------------------------------------------------
    // LOAD AND RENDER IN DETAIL MODAL
    // --------------------------------------------------------
    async load(eid) {
        if (!eid) return;

        try {
            const res = await VoucherTaxi.request(VoucherTaxi.routes.tracking(eid));
            if (res.success) {
                VoucherTaxiTracking.render(res.steps ?? []);
            } else {
                VoucherTaxiTracking.renderEmpty();
            }
        } catch {
            VoucherTaxiTracking.renderEmpty();
        }
    },

    // --------------------------------------------------------
    // RENDER — wrapped card with "Approval Timeline" header
    // --------------------------------------------------------
    render(steps) {
        const container = document.getElementById('approvalFlow');
        if (!container) return;

        if (!steps.length) {
            container.innerHTML = `
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-white/5">
                    No approval workflow available.
                </div>`;
            return;
        }

        const items = steps.map((step, i) =>
            VoucherTaxiTracking.stepHtml(step, i, steps.length)
        ).join('');

        container.innerHTML = `
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        Approval Timeline
                    </h3>
                </div>

                <div class="space-y-2 p-4">
                    ${items}
                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // SINGLE STEP
    // --------------------------------------------------------
    stepHtml(step, index, total) {
        const isLast  = index === total - 1;
        const s       = (step.status ?? '').toUpperCase();
        const title   = step.title ?? '-';
        const by      = step.by ?? null;
        const at      = step.at ?? null;
        const remark  = step.comment ?? step.reason ?? null;
        const showRemark = remark && (s === 'D' || s === 'R');

        return `
            <div class="relative flex gap-4">

                <div class="flex flex-col items-center">

                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${VoucherTaxiTracking.badgeColor(s)}">
                        ${VoucherTaxiTracking.icon(s)}
                    </div>

                    ${!isLast ? `<div class="mt-1 min-h-6 w-px flex-1 bg-slate-200 dark:bg-white/10"></div>` : ''}

                </div>

                <div class="min-w-0 flex-1 pb-4">

                    <div class="flex items-start justify-between gap-3">

                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                ${VoucherTaxiTracking.escape(title)}
                            </p>

                            ${by ? `<p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">${VoucherTaxiTracking.escape(by)}</p>` : ''}

                            ${at ? `<p class="mt-1 text-xs text-slate-400 dark:text-slate-500">${VoucherTaxiTracking.escape(at)}</p>` : ''}
                        </div>

                        ${VoucherTaxiTracking.pill(s)}

                    </div>

                    ${showRemark ? `
                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-white/10 dark:bg-white/5 dark:text-slate-300">
                            ${VoucherTaxiTracking.escape(remark)}
                        </div>` : ''}

                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // ICON  (rounded-lg, same as BookingCar)
    // --------------------------------------------------------
    icon(s) {
        switch (s) {
            case 'A': return '<i class="fa-solid fa-check text-xs"></i>';
            case 'R': return '<i class="fa-solid fa-xmark text-xs"></i>';
            case 'D': return '<i class="fa-solid fa-rotate-left text-xs"></i>';
            case 'P': return '<i class="fa-solid fa-clock text-xs"></i>';
            case 'C': return '<i class="fa-solid fa-paper-plane text-xs"></i>';
            default:  return '<i class="fa-solid fa-paper-plane text-xs"></i>';
        }
    },

    // --------------------------------------------------------
    // ICON BACKGROUND COLOR
    // --------------------------------------------------------
    badgeColor(s) {
        switch (s) {
            case 'A': return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
            case 'R': return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
            case 'D': return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
            case 'P': return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
            case 'C': return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
            default:  return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
        }
    },

    // --------------------------------------------------------
    // STATUS PILL BADGE  (rounded-lg, same as BookingCar)
    // --------------------------------------------------------
    pill(s) {
        switch (s) {
            case 'A': return `<span class="inline-flex shrink-0 rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Approved</span>`;
            case 'R': return `<span class="inline-flex shrink-0 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-300">Rejected</span>`;
            case 'D': return `<span class="inline-flex shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Revised</span>`;
            case 'P': return `<span class="inline-flex shrink-0 rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Waiting</span>`;
            case 'C': return `<span class="inline-flex shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-400">Submitted</span>`;
            default:  return `<span class="inline-flex shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-400">-</span>`;
        }
    },

    // --------------------------------------------------------
    // EMPTY STATE
    // --------------------------------------------------------
    renderEmpty() {
        const container = document.getElementById('approvalFlow');
        if (!container) return;
        container.innerHTML = `
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-white/5">
                No approval workflow available.
            </div>`;
    },

    // --------------------------------------------------------
    // ESCAPE HTML
    // --------------------------------------------------------
    escape(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    // --------------------------------------------------------
    // AUTO-REFRESH
    // --------------------------------------------------------
    stopAutoRefresh() {
        clearTimeout(VoucherTaxiTracking.state.autoRefreshTimer);
        VoucherTaxiTracking.state.autoRefreshTimer = null;
    },
};
