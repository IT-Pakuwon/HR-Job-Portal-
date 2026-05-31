// ============================================================
// helper.js — Voucher Taxi
// DOM helpers, UI utilities, and shared rendering functions
// ============================================================

const VoucherTaxiHelper = {

    // --------------------------------------------------------
    // SHOW / HIDE ELEMENT
    // --------------------------------------------------------
    show(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    },

    hide(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    },

    toggle(id, condition) {
        condition ? VoucherTaxiHelper.show(id) : VoucherTaxiHelper.hide(id);
    },

    // --------------------------------------------------------
    // SET TEXT CONTENT
    // --------------------------------------------------------
    setText(id, value) {
        const el = document.getElementById(id);
        if (el) el.textContent = value ?? '-';
    },

    // --------------------------------------------------------
    // SET HTML CONTENT
    // --------------------------------------------------------
    setHtml(id, html) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = html ?? '';
    },

    // --------------------------------------------------------
    // SET INPUT VALUE
    // --------------------------------------------------------
    setValue(id, value) {
        const el = document.getElementById(id);
        if (el) el.value = value ?? '';
    },

    // --------------------------------------------------------
    // GET INPUT VALUE (trimmed)
    // --------------------------------------------------------
    getValue(id) {
        return document.getElementById(id)?.value?.trim() ?? '';
    },

    // --------------------------------------------------------
    // SET SELECT VALUE
    // --------------------------------------------------------
    setSelect(id, value) {
        const el = document.getElementById(id);
        if (!el) return;
        el.value = value ?? '';

        // Trigger change event for Select2
        if (typeof $ !== 'undefined' && $(el).hasClass('select2-hidden-accessible')) {
            $(el).trigger('change');
        }
    },

    // --------------------------------------------------------
    // DISABLE / ENABLE INPUT
    // --------------------------------------------------------
    disable(id) {
        const el = document.getElementById(id);
        if (el) el.disabled = true;
    },

    enable(id) {
        const el = document.getElementById(id);
        if (el) el.disabled = false;
    },

    // --------------------------------------------------------
    // LOADING STATE ON BUTTON
    // --------------------------------------------------------
    setButtonLoading(id, loading, originalText = null) {
        const btn = document.getElementById(id);
        if (!btn) return;

        if (loading) {
            btn.disabled             = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML            = `<i class="fa-solid fa-spinner fa-spin text-xs"></i>
                                        <span>Processing...</span>`;
        } else {
            btn.disabled  = false;
            btn.innerHTML = originalText ?? btn.dataset.originalText ?? btn.innerHTML;
        }
    },

    // --------------------------------------------------------
    // RESET FORM
    // --------------------------------------------------------
    resetForm(formId) {
        const form = document.getElementById(formId);
        if (form) form.reset();
    },

    // --------------------------------------------------------
    // SERIALIZE FORM TO FORMDATA
    // --------------------------------------------------------
    getFormData(formId) {
        const form = document.getElementById(formId);
        if (!form) return new FormData();
        return new FormData(form);
    },

    // --------------------------------------------------------
    // SERIALIZE FORM TO PLAIN OBJECT
    // --------------------------------------------------------
    getFormObject(formId) {
        const fd  = VoucherTaxiHelper.getFormData(formId);
        const obj = {};
        fd.forEach((value, key) => {
            if (obj[key] !== undefined) {
                if (!Array.isArray(obj[key])) obj[key] = [obj[key]];
                obj[key].push(value);
            } else {
                obj[key] = value;
            }
        });
        return obj;
    },

    // --------------------------------------------------------
    // RENDER STATUS BADGE  (delegates to core)
    // --------------------------------------------------------
    statusBadge(status) {
        return VoucherTaxi.statusBadge(status);
    },

    // --------------------------------------------------------
    // FORMAT CURRENCY (IDR)
    // --------------------------------------------------------
    formatCurrency(amount) {
        if (!amount || isNaN(amount)) return 'Rp 0';

        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        }).format(amount);
    },

    // --------------------------------------------------------
    // FORMAT DATE
    // --------------------------------------------------------
    formatDate(dateStr) {
        return VoucherTaxi.formatDate(dateStr);
    },

    // --------------------------------------------------------
    // RENDER SIMPLE STATUS TIMELINE
    // --------------------------------------------------------
    renderTimeline(steps) {
        if (!steps || steps.length === 0) {
            return `
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-8 text-center text-sm text-slate-500 dark:border-white/10 dark:bg-white/2">
                    No approval workflow available.
                </div>`;
        }

        const escape = (str) => {
            if (!str) return '';
            const d = document.createElement('div');
            d.textContent = str;
            return d.innerHTML;
        };

        const nl2br = (str) => str.replace(/\n/g, '<br>');

        const badgeColor = (s) => {
            switch (s) {
                case 'C':
                case 'A':  return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
                case 'R':  return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
                case 'D':  return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
                case 'P':  return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
                case 'F':  return 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400';
                case 'X':  return 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400';
                default:   return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
            }
        };

        const icon = (s) => {
            switch (s) {
                case 'C':
                case 'A':  return '<i class="fa-solid fa-check text-xs"></i>';
                case 'R':  return '<i class="fa-solid fa-xmark text-xs"></i>';
                case 'D':  return '<i class="fa-solid fa-rotate-left text-xs"></i>';
                case 'P':  return '<i class="fa-solid fa-clock text-xs"></i>';
                case 'F':  return '<i class="fa-solid fa-flag-checkered text-xs"></i>';
                case 'X':  return '<i class="fa-solid fa-ban text-xs"></i>';
                default:   return '<i class="fa-solid fa-paper-plane text-xs"></i>';
            }
        };

        const pill = (s) => {
            switch (s) {
                case 'C':
                case 'A':  return `<span class="inline-flex shrink-0 rounded-lg bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">Completed</span>`;
                case 'R':  return `<span class="inline-flex shrink-0 rounded-lg bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700 dark:bg-red-500/20 dark:text-red-300">Rejected</span>`;
                case 'D':  return `<span class="inline-flex shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">Revise</span>`;
                case 'P':  return `<span class="inline-flex shrink-0 rounded-lg bg-blue-100 px-2.5 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-500/20 dark:text-blue-300">Pending</span>`;
                case 'F':  return `<span class="inline-flex shrink-0 rounded-lg bg-indigo-100 px-2.5 py-1 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">Processed</span>`;
                case 'X':  return `<span class="inline-flex shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-400">Cancelled</span>`;
                default:   return `<span class="inline-flex shrink-0 rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 dark:bg-white/10 dark:text-slate-400">-</span>`;
            }
        };

        const items = steps.map((step, index) => {
            const isLast = index === steps.length - 1;
            const s      = (step.status ?? '').toUpperCase();
            const title  = step.title  ?? step.status_label ?? '-';
            const by     = step.by     ?? null;
            const at     = step.at     ?? null;
            const remark = step.comment ?? step.reason ?? null;
            const showRemark = remark && (s === 'D' || s === 'R');

            return `
                <div class="relative flex gap-4">

                    <div class="flex flex-col items-center">

                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${badgeColor(s)}">
                            ${icon(s)}
                        </div>

                        ${!isLast ? `<div class="mt-1 w-px flex-1 min-h-6 bg-slate-200 dark:bg-white/10"></div>` : ''}

                    </div>

                    <div class="min-w-0 flex-1 pb-4">

                        <div class="flex items-start justify-between gap-3">

                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    ${escape(title)}
                                </p>

                                ${by ? `<p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">${escape(by)}</p>` : ''}

                                ${at ? `<p class="mt-1 text-xs text-slate-400 dark:text-slate-500">${escape(at)}</p>` : ''}
                            </div>

                            ${pill(s)}

                        </div>

                        ${showRemark ? `
                            <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-white/10 dark:bg-white/3 dark:text-slate-300">
                                ${nl2br(escape(remark))}
                            </div>
                        ` : ''}

                    </div>

                </div>`;
        }).join('');

        return `
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">

                <div class="border-b border-slate-200 px-5 py-4 dark:border-white/10">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                        Approval Workflow
                    </h3>
                </div>

                <div class="space-y-2 p-4">
                    ${items}
                </div>

            </div>`;
    },

    // --------------------------------------------------------
    // EMPTY CHECK
    // --------------------------------------------------------
    isEmpty(value) {
        return value === null || value === undefined || String(value).trim() === '';
    },

    // --------------------------------------------------------
    // CLONE ELEMENT
    // --------------------------------------------------------
    clone(id) {
        const el = document.getElementById(id);
        if (!el) return null;
        return el.cloneNode(true);
    },

    // --------------------------------------------------------
    // ADD CLASS
    // --------------------------------------------------------
    addClass(id, className) {
        const el = document.getElementById(id);
        if (el) el.classList.add(className);
    },

    // --------------------------------------------------------
    // REMOVE CLASS
    // --------------------------------------------------------
    removeClass(id, className) {
        const el = document.getElementById(id);
        if (el) el.classList.remove(className);
    },

    // --------------------------------------------------------
    // HAS CLASS
    // --------------------------------------------------------
    hasClass(id, className) {
        const el = document.getElementById(id);
        return el?.classList.contains(className) ?? false;
    },

    // --------------------------------------------------------
    // SCROLL TO ELEMENT
    // --------------------------------------------------------
    scrollIntoView(id, behavior = 'smooth') {
        const el = document.getElementById(id);
        if (el) el.scrollIntoView({ behavior });
    },

};
