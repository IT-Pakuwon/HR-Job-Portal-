(function () {
    'use strict';

    VoucherTaxi.Helper = {

        csrf() {
            return $('meta[name="csrf-token"]').attr('content');
        },

        headers() {
            return {
                'X-CSRF-TOKEN': this.csrf()
            };
        },

        money(value) {
            const number = parseFloat(value || 0);

            return new Intl.NumberFormat('id-ID', {
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(number);
        },

        moneyWithPrefix(value) {
            return 'Rp ' + this.money(value);
        },

        parseMoney(value) {
            return String(value || '')
                .replace(/[^\d]/g, '');
        },

        badge(status) {

            const map = {
                P: {
                    text: 'Pending',
                    class: 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300'
                },
                C: {
                    text: 'Completed',
                    class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300'
                },
                D: {
                    text: 'Revise',
                    class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-300'
                },
                R: {
                    text: 'Rejected',
                    class: 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300'
                },
                X: {
                    text: 'Cancelled',
                    class: 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-300'
                }
            };

            return map[status] || {
                text: status ?? '-',
                class: 'bg-slate-100 text-slate-700'
            };
        },

        statusText(status) {

            return {
                P: 'Pending',
                C: 'Completed',
                D: 'Revise',
                R: 'Rejected',
                X: 'Cancelled'
            }[status] ?? status;
        },

        escapeHtml(value) {

            return $('<div>')
                .text(value ?? '')
                .html();
        },

        nl2br(value) {

            return this.escapeHtml(value)
                .replace(/\n/g, '<br>');
        },

        debounce(fn, delay = 400) {

            let timer;

            return (...args) => {

                clearTimeout(timer);

                timer = setTimeout(() => {
                    fn.apply(this, args);
                }, delay);
            };
        },

        loading(title = 'Loading...') {

            Swal.fire({
                title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
        },

        closeLoading() {
            Swal.close();
        },

        success(message = 'Success') {

            return Swal.fire({
                icon: 'success',
                title: 'Success',
                text: message,
                timer: 1800,
                showConfirmButton: false
            });
        },

        error(message = 'Something went wrong') {

            return Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        },

        warning(message = 'Warning') {

            return Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: message
            });
        },

        confirm(
            title = 'Are you sure?',
            text = '',
            confirmText = 'Yes'
        ) {

            return Swal.fire({
                icon: 'question',
                title,
                text,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
        },

        toast(icon = 'success', title = '') {

            return Swal.fire({
                toast: true,
                position: 'top-end',
                icon,
                title,
                showConfirmButton: false,
                timer: 2500,
                timerProgressBar: true
            });
        },

        ajaxError(xhr) {

            let message = 'Something went wrong';

            if (xhr?.responseJSON?.message) {
                message = xhr.responseJSON.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message
            });
        },

        copy(text) {

            navigator.clipboard.writeText(text || '');

            this.toast(
                'success',
                'Copied to clipboard'
            );
        },

        statusTitle(status) {

            switch (status) {

                case 'A':
                    return 'Approved';

                case 'R':
                    return 'Rejected';

                case 'D':
                    return 'Revised';

                case 'P':
                    return 'Waiting Approval';

                case 'F':
                    return 'Completed';

                case 'C':
                    return 'Completed';

                default:
                    return 'Submitted';
            }

        },

        randomId(prefix = 'vt') {

            return (
                prefix +
                '_' +
                Date.now() +
                '_' +
                Math.floor(Math.random() * 1000)
            );
        }
    };

    VoucherTaxi.syncPanelHeight = function () {

        const calendar =
            document.getElementById('calendarWrapper');

        const voucher =
            document.getElementById('voucherListPanel');

        if (!calendar || !voucher) return;

        voucher.style.height =
            calendar.offsetHeight + 'px';
    };

    VoucherTaxi.log('Helper Loaded');

})();
