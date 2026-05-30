// ============================================================
// core.js — Booking Car
// Global state, constants, and shared utilities
// ============================================================

const BookingCar = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:    null,
        currentDocid:  null,
        currentStatus: null,
    },

    // --------------------------------------------------------
    // ROUTES  (injected from Blade via meta tags or inline)
    // --------------------------------------------------------
    routes: {
        index:    '/bookingcar',
        json:         '/bookingcar/json',
        calendarJson: '/bookingcar/calendar-json',
        store:    '/bookingcar/store',
        detail:   (eid)    => `/bookingcar/detail/${eid}`,
        tracking: (eid)    => `/bookingcar/tracking/${eid}`,
        find:     (eid)    => `/bookingcar/find/${eid}`,
        print:    (hash)   => `/bookingcar/print/${hash}`,
        update:   (docid)  => `/bookingcar/update/${docid}`,
        cancel:   (docid)  => `/bookingcar/cancel/${docid}`,
        approve:  (docid)  => `/bookingcar/approve/${docid}`,
        reject:   (docid)  => `/bookingcar/reject/${docid}`,
        revise:   (docid)  => `/bookingcar/revise/${docid}`,
        process:        (eid)   => `/bookingcar/process/${eid}`,
        changeExpense:  (eid)   => `/bookingcar/change-expense/${eid}`,
        show:           (eid)   => `/showbookingcar/${eid}`,
    },

    // --------------------------------------------------------
    // STATUS MAP
    // --------------------------------------------------------
    statusMap: {
        P: { label: 'Pending',    color: 'blue'   },
        C: { label: 'Approved',   color: 'emerald' },
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
    // DATE FORMATTER  (no memory/locale tricks — explicit)
    // --------------------------------------------------------
    formatDate(raw) {
        if (!raw) return '-';

        // Accept "YYYY-MM-DD" or full datetime string
        const str  = String(raw).trim();
        const date = new Date(str.length === 10 ? str + 'T00:00:00' : str);

        if (isNaN(date.getTime())) return raw;

        const dd   = String(date.getDate()).padStart(2, '0');
        const mm   = String(date.getMonth() + 1).padStart(2, '0');
        const yyyy = date.getFullYear();

        return `${dd}/${mm}/${yyyy}`;
    },

    // --------------------------------------------------------
    // TIME FORMATTER  — extracts HH:MM from datetime string
    // --------------------------------------------------------
    formatTime(raw) {
        if (!raw) return '-';

        const str = String(raw).trim();

        // If already HH:MM or HH:MM:SS
        if (/^\d{2}:\d{2}/.test(str)) return str.substring(0, 5);

        // If full datetime: "YYYY-MM-DD HH:MM:SS"
        const parts = str.split(' ');
        if (parts.length >= 2) return parts[1].substring(0, 5);

        return str;
    },

    // --------------------------------------------------------
    // STATUS BADGE HTML
    // --------------------------------------------------------
    statusBadge(status) {
        const map = {
            P: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
            C: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
            F: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300',
            D: 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
            R: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
            X: 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400',
        };

        const info  = BookingCar.statusMap[status] ?? { label: status };
        const cls   = map[status] ?? map.X;

        return `<span class="inline-flex items-center rounded-lg px-3 py-1 text-xs font-semibold ${cls}">
                    ${info.label}
                </span>`;
    },

    // --------------------------------------------------------
    // FETCH WRAPPER
    // --------------------------------------------------------
    async request(url, options = {}) {
        const defaults = {
            headers: {
                'X-CSRF-TOKEN':     BookingCar.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        };

        // Merge headers
        if (options.headers) {
            options.headers = { ...defaults.headers, ...options.headers };
        }

        const config = { ...defaults, ...options };

        try {
            const res  = await fetch(url, config);
            const data = await res.json();

            if (!res.ok) {
                throw { status: res.status, data };
            }

            return data;

        } catch (err) {
            // Re-throw so callers handle it
            throw err;
        }
    },

    // --------------------------------------------------------
    // SWAL SHORTCUTS
    // --------------------------------------------------------
    toast(icon, title, timer = 2500) {
        Swal.fire({
            toast:            true,
            position:         'top-end',
            icon,
            title,
            showConfirmButton: false,
            timer,
            timerProgressBar:  true,
        });
    },

    confirm(opts = {}) {
        return Swal.fire({
            title:              opts.title             ?? 'Are you sure?',
            text:               opts.text              ?? '',
            icon:               opts.icon              ?? 'question',
            showCancelButton:   true,
            confirmButtonText:  opts.confirmText       ?? 'Yes',
            cancelButtonText:   opts.cancelText        ?? 'Cancel',
            confirmButtonColor: opts.confirmColor      ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor       ?? '#94a3b8',
            reverseButtons:     true,
        });
    },

    prompt(opts = {}) {
        return Swal.fire({
            title:              opts.title             ?? 'Input required',
            input:              opts.input             ?? 'textarea',
            inputPlaceholder:   opts.placeholder       ?? '',
            inputLabel:         opts.label             ?? '',
            showCancelButton:   true,
            confirmButtonText:  opts.confirmText       ?? 'Submit',
            cancelButtonText:   opts.cancelText        ?? 'Cancel',
            confirmButtonColor: opts.confirmColor      ?? '#0f172a',
            cancelButtonColor:  opts.cancelColor       ?? '#94a3b8',
            reverseButtons:     true,
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return opts.validationMsg ?? 'This field is required.';
                }
            },
        });
    },

    // --------------------------------------------------------
    // URL STATE  — push/clear eid in address bar
    // --------------------------------------------------------
    pushUrl(eid) {
        const url = BookingCar.routes.show(eid);
        history.pushState({ eid }, '', url);
    },

    clearUrl() {
        history.pushState({}, '', BookingCar.routes.index);
    },

    // --------------------------------------------------------
    // SET / CLEAR CURRENT DOCUMENT
    // --------------------------------------------------------
    setDoc(eid, docid, status) {
        BookingCar.state.currentEid    = eid;
        BookingCar.state.currentDocid  = docid;
        BookingCar.state.currentStatus = status;
    },

    clearDoc() {
        BookingCar.state.currentEid    = null;
        BookingCar.state.currentDocid  = null;
        BookingCar.state.currentStatus = null;
    },
};
