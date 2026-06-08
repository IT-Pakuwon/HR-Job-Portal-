function amFormatDate(date) {
    if (!date) return '-';
    const d = new Date(date);
    return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
}

function amIsDark() {
    return $('html').hasClass('dark');
}

function amSwalBase() {
    return {
        background: amIsDark() ? '#111c2d' : '#ffffff',
        color:      amIsDark() ? '#ffffff' : '#0f172a',
        customClass: {
            popup: 'rounded-lg border border-white/[0.06]',
        },
    };
}

function amToast(icon, title, timer = 2500) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
        ...amSwalBase(),
    });
}

function amSwalSuccess(message = 'Success') {
    return Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#0f172a',
        ...amSwalBase(),
    });
}

function amSwalError(message = 'Something went wrong') {
    return Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#dc2626',
        ...amSwalBase(),
    });
}

function amSwalWarning(message = 'Warning') {
    return Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: message,
        confirmButtonText: 'OK',
        confirmButtonColor: '#f59e0b',
        ...amSwalBase(),
    });
}

function amConfirm({ title = 'Are you sure?', text = '', confirmText = 'Yes' } = {}) {
    return Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: confirmText,
        cancelButtonText: 'Cancel',
        reverseButtons: true,
        confirmButtonColor: '#0f172a',
        cancelButtonColor: amIsDark() ? '#334155' : '#e2e8f0',
        ...amSwalBase(),
        customClass: {
            popup: 'rounded-lg border border-white/[0.06]',
            confirmButton: 'rounded-lg px-4 py-2 font-medium',
            cancelButton:  'rounded-lg px-4 py-2 font-medium',
        },
    });
}

function amRenderWarrantyBadge(hasExpired, startDate, endDate) {
    const start = startDate ? amFormatDate(startDate) : '—';
    const end   = endDate   ? amFormatDate(endDate)   : '—';

    if (hasExpired) {
        return `
            <span class="inline-flex items-center gap-1.5 rounded-lg bg-red-100 dark:bg-red-500/15 px-3 py-1 text-xs font-semibold text-red-700 dark:text-red-300">
                <span class="h-1.5 w-1.5 rounded-lg bg-red-500"></span>
                EXPIRED
            </span>
            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">${start} → ${end}</div>
        `;
    }

    return `
        <span class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-100 dark:bg-emerald-500/15 px-3 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
            <span class="h-1.5 w-1.5 rounded-lg bg-emerald-500"></span>
            ACTIVE
        </span>
        <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">Since ${start}</div>
    `;
}
