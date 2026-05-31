// ============================================================
// core.js — Voucher Taxi
// Global state, constants, and shared utilities
// ============================================================

const VoucherTaxi = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:    null,
        currentDocid:  null,
        currentStatus: null,
    },

    // --------------------------------------------------------
    // ROUTES
    // --------------------------------------------------------
    routes: {
        index:           '/vouchertaxi',
        json:            '/vouchertaxi/json',
        calendarJson:    '/vouchertaxi/calendar-json',
        store:           '/vouchertaxi/store',
        purposeSearch:   '/vouchertaxi/purpose-search',
        employeeByDept:  '/vouchertaxi/employee-by-department',
        detail:   (eid)   => `/vouchertaxi/detail/${eid}`,
        tracking: (eid)   => `/vouchertaxi/tracking/${eid}`,
        find:     (eid)   => `/vouchertaxi/find/${eid}`,
        print:    (hash)  => `/vouchertaxi/print/${hash}`,
        update:   (docid) => `/vouchertaxi/update/${docid}`,
        cancel:   (docid) => `/vouchertaxi/cancel/${docid}`,
        approve:  (docid) => `/vouchertaxi/approve/${docid}`,
        reject:   (docid) => `/vouchertaxi/reject/${docid}`,
        revise:   (docid) => `/vouchertaxi/revise/${docid}`,
        process:  (docid) => `/vouchertaxi/process/${docid}`,
        show:     (eid)   => `/showvouchertaxi/${eid}`,
    },

    // --------------------------------------------------------
    // STATUS MAP
    // --------------------------------------------------------
    statusMap: {
        P: { label: 'Pending',    color: 'blue'    },
        C: { label: 'Completed',  color: 'emerald' },
        F: { label: 'Processed',  color: 'indigo'  },
        D: { label: 'Revise',     color: 'amber'   },
        R: { label: 'Rejected',   color: 'red'     },
        X: { label: 'Cancelled',  color: 'slate'   },
    },

    // --------------------------------------------------------
    // CSRF TOKEN
    // --------------------------------------------------------
    csrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    },

    // --------------------------------------------------------
    // SET / CLEAR CURRENT DOCUMENT
    // --------------------------------------------------------
    setDoc(eid, docid, status) {
        this.state.currentEid    = eid    ?? null;
        this.state.currentDocid  = docid  ?? null;
        this.state.currentStatus = status ?? null;
    },

    clearDoc() {
        this.state.currentEid    = null;
        this.state.currentDocid  = null;
        this.state.currentStatus = null;
    },

    // --------------------------------------------------------
    // DATE FORMATTER  (no moment — explicit)
    // --------------------------------------------------------
    formatDate(raw) {
        if (!raw) return '-';
        const str  = String(raw).trim();
        const date = new Date(str.length === 10 ? str + 'T00:00:00' : str);
        if (isNaN(date.getTime())) return raw;
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const dd   = String(date.getDate()).padStart(2, '0');
        const mmm  = months[date.getMonth()];
        const yyyy = date.getFullYear();
        return `${dd} ${mmm} ${yyyy}`;
    },

    formatDateTime(raw) {
        if (!raw) return '-';
        const str  = String(raw).trim();
        const date = new Date(str.length === 10 ? str + 'T00:00:00' : str);
        if (isNaN(date.getTime())) return raw;
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const dd  = String(date.getDate()).padStart(2, '0');
        const mmm = months[date.getMonth()];
        const yy  = date.getFullYear();
        const hh  = String(date.getHours()).padStart(2, '0');
        const min = String(date.getMinutes()).padStart(2, '0');
        return `${dd} ${mmm} ${yy} ${hh}:${min}`;
    },

    parseDate(raw) {
        if (!raw) return '';
        const d = new Date(String(raw).trim());
        if (isNaN(d.getTime())) return '';
        const yyyy = d.getFullYear();
        const mm   = String(d.getMonth() + 1).padStart(2, '0');
        const dd   = String(d.getDate()).padStart(2, '0');
        return `${yyyy}-${mm}-${dd}`;
    },

    // --------------------------------------------------------
    // STATUS BADGE HTML
    // --------------------------------------------------------
    statusBadge(status) {
        const colorMap = {
            P: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            C: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            F: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            D: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            R: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            X: 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400',
        };
        const info = VoucherTaxi.statusMap[status] ?? { label: status };
        const cls  = colorMap[status] ?? colorMap.X;
        return `<span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold ${cls}">${info.label}</span>`;
    },

    statusColor(status) {
        const colors = {
            P: '#3B82F6',
            C: '#10B981',
            F: '#6366F1',
            D: '#F59E0B',
            R: '#EF4444',
            X: '#6B7280',
        };
        return colors[status] ?? '#999999';
    },

    statusIcon(status) {
        const icons = {
            P: 'fa-hourglass-half',
            C: 'fa-check-circle',
            F: 'fa-flag-checkered',
            D: 'fa-sync-alt',
            R: 'fa-times-circle',
            X: 'fa-ban',
        };
        return icons[status] ?? 'fa-circle';
    },

    // --------------------------------------------------------
    // CURRENCY FORMATTER
    // --------------------------------------------------------
    formatCurrency(value) {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0,
        }).format(value ?? 0);
    },

    parseCurrency(value) {
        return parseInt(String(value ?? '').replace(/\D/g, '')) || 0;
    },

    // --------------------------------------------------------
    // FETCH WRAPPER  (async / await)
    // --------------------------------------------------------
    async request(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        };

        if (options.headers) {
            options.headers = { ...defaults.headers, ...options.headers };
        }

        const config = { ...defaults, ...options };

        const res  = await fetch(url, config);
        const data = await res.json();

        if (!res.ok) throw { status: res.status, data };

        return data;
    },

    // --------------------------------------------------------
    // JQUERY AJAX SETUP  (still used by legacy modules)
    // --------------------------------------------------------
    initAjax() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            timeout: 30000,
        });
    },

    // --------------------------------------------------------
    // SWEETALERT2 SHORTCUTS
    // --------------------------------------------------------
    toast(icon, title, timer = 2500) {
        Swal.fire({
            toast:             true,
            position:          'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer,
            timerProgressBar:  true,
        });
    },

    confirm(opts = {}) {
        return Swal.fire({
            title:              opts.title       ?? 'Are you sure?',
            text:               opts.text        ?? '',
            icon:               opts.icon        ?? 'question',
            showCancelButton:   true,
            confirmButtonText:  opts.confirmText ?? 'Yes',
            cancelButtonText:   opts.cancelText  ?? 'Cancel',
            confirmButtonColor: opts.confirmColor ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor  ?? '#94a3b8',
            reverseButtons:     true,
        });
    },

    prompt(opts = {}) {
        return Swal.fire({
            title:             opts.title       ?? 'Input required',
            input:             opts.input       ?? 'textarea',
            inputPlaceholder:  opts.placeholder ?? '',
            inputLabel:        opts.label       ?? '',
            showCancelButton:  true,
            confirmButtonText: opts.confirmText ?? 'Submit',
            cancelButtonText:  opts.cancelText  ?? 'Cancel',
            confirmButtonColor: opts.confirmColor ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor  ?? '#94a3b8',
            reverseButtons:    true,
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return opts.validationMsg ?? 'This field is required.';
                }
            },
        });
    },

    // --------------------------------------------------------
    // LEGACY COMPATIBILITY SHIMS
    // --------------------------------------------------------
    showSuccess(message) { this.toast('success', message); },
    showError(message)   { this.toast('error',   message); },
    showWarning(message) { this.toast('warning', message); },
    showInfo(message)    { this.toast('info',    message); },

    showLoading() {
        Swal.fire({
            title: 'Please wait...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => { Swal.showLoading(); },
        });
    },

    hideLoading() {
        Swal.close();
    },

    // --------------------------------------------------------
    // UTILITIES
    // --------------------------------------------------------
    debounce(func, wait) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    },

    getUrlParam(param) {
        return new URLSearchParams(window.location.search).get(param);
    },

    highlightText(text, term) {
        if (!term || !text) return text ?? '';
        const regex = new RegExp(`(${term})`, 'gi');
        return String(text).replace(regex, '<mark>$1</mark>');
    },

    truncate(text, len = 50) {
        if (!text) return '';
        return text.length <= len ? text : text.substring(0, len) + '…';
    },
};
