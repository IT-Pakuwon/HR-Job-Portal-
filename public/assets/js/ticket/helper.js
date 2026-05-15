// assets/js/ticket/helper.js

window.Ticket = window.Ticket || {};

function showSuccess(message = 'Success') {

    Swal.fire({

        icon: 'success',

        title: 'Success',

        text: message,

        timer: 1800,

        showConfirmButton: false,

    });

}

function showError(message = 'Something went wrong') {

    Swal.fire({

        icon: 'error',

        title: 'Error',

        text: message,

    });

}

function showWarning(message = 'Warning') {

    Swal.fire({

        icon: 'warning',

        title: 'Warning',

        text: message,

    });

}

function showConfirm({

    title = 'Are you sure?',

    text = '',

    confirmText = 'Yes',

    cancelText = 'Cancel',

}) {

    return Swal.fire({

        icon: 'warning',

        title,

        text,

        showCancelButton: true,

        confirmButtonText: confirmText,

        cancelButtonText: cancelText,

        reverseButtons: true,

    });

}

function clearValidationErrors(formSelector) {

    $(formSelector)
        .find('.is-invalid')
        .removeClass('is-invalid');

    $(formSelector)
        .find('.invalid-feedback')
        .remove();

}

function renderValidationErrors(
    formSelector,
    errors = {}
) {

    clearValidationErrors(formSelector);

    Object.keys(errors).forEach(function (key) {

        const input =
            $(formSelector)
                .find(`[name="${key}"]`);

        if (!input.length) {
            return;
        }

        input.addClass('is-invalid');

        const errorHtml = `

            <div class="
                invalid-feedback
                mt-1
                text-xs
                text-red-500
            ">
                ${errors[key][0]}
            </div>

        `;

        if (
            input.hasClass(
                'select2-hidden-accessible'
            )
        ) {

            input
                .next('.select2')
                .after(errorHtml);

        } else {

            input.after(errorHtml);

        }

    });

}

function handleAjaxError(xhr) {

    if (xhr.status === 401) {

        window.location.reload();

        return;
    }

    if (xhr.status === 422) {

        renderValidationErrors(
            Ticket.selectors.createForm,
            xhr.responseJSON.errors || {}
        );

        return;
    }

    let message =
        'Something went wrong';

    if (xhr.responseJSON?.message) {

        message =
            xhr.responseJSON.message;

    }

    showError(message);

}

function setButtonLoading(
    button,
    loading = true,
    loadingText = 'Processing...'
) {

    const $button =
        $(button);

    if (loading) {

        $button.data(
            'original-html',
            $button.html()
        );

        $button.prop('disabled', true);

        $button.html(`

            <span class="inline-flex items-center gap-2">

                <i class="fa-solid fa-spinner fa-spin text-xs"></i>

                <span>
                    ${loadingText}
                </span>

            </span>

        `);

    } else {

        $button.prop('disabled', false);

        $button.html(
            $button.data('original-html')
        );

    }

}

function resetForm(formSelector) {

    const form =
        $(formSelector);

    if (
        form.length &&
        form[0]
    ) {

        form[0].reset();

    }

    form
        .find('select')
        .each(function () {

            const select =
                $(this);

            select
                .val(null);

            if (
                select.hasClass(
                    'select2-hidden-accessible'
                )
            ) {

                select.trigger(
                    'change.select2'
                );

            }

        });

    clearValidationErrors(
        formSelector
    );

}

function debounce(callback, delay = 300) {

    let timeout;

    return function (...args) {

        clearTimeout(timeout);

        timeout = setTimeout(() => {

            callback.apply(this, args);

        }, delay);

    };

}

function formatFileSize(bytes = 0) {

    if (bytes < 1024) {

        return `${bytes} B`;

    }

    if (bytes < 1024 * 1024) {

        return `${(bytes / 1024).toFixed(1)} KB`;

    }

    return `${(bytes / (1024 * 1024)).toFixed(2)} MB`;

}

function getFileExtension(filename = '') {

    return filename
        .split('.')
        .pop()
        .toLowerCase();

}
function formatDate(dateString) {

    if (!dateString) {
        return '-';
    }

    const date =
        new Date(dateString);

    return date.toLocaleDateString('en-GB', {

        day:
            '2-digit',

        month:
            'short',

        year:
            'numeric',

    });

}

function formatTime(dateString) {

    if (!dateString) {
        return '-';
    }

    const date =
        new Date(dateString);

    return date.toLocaleTimeString('en-GB', {

        hour:
            '2-digit',

        minute:
            '2-digit',

        hour12:
            false,

    });

}

function formatDateTime(dateString) {

    if (!dateString) {
        return '-';
    }

    return `
        ${formatDate(dateString)}
    `;

}

function isOverdueDate(dateString) {

    if (!dateString) {
        return false;
    }

    const dueDate =
        new Date(dateString);

    const now =
        new Date();

    return dueDate < now;

}

function priorityBadgeClass(priority) {

    switch ((priority || '').toLowerCase()) {

        case 'low':
            return `
                bg-slate-100
                text-slate-700

                dark:bg-slate-800
                dark:text-slate-300
            `;

        case 'medium':
            return `
                bg-blue-100
                text-blue-700

                dark:bg-blue-900/30
                dark:text-blue-300
            `;

        case 'high':
            return `
                bg-orange-100
                text-orange-700

                dark:bg-orange-900/30
                dark:text-orange-300
            `;

        case 'urgent':
            return `
                bg-red-100
                text-red-700

                dark:bg-red-900/30
                dark:text-red-300
            `;

        default:
            return `
                bg-gray-100
                text-gray-700

                dark:bg-gray-800
                dark:text-gray-300
            `;

    }

}

function workflowBadgeClass(status) {

    switch ((status || '').toUpperCase()) {

        case 'CREATED':
            return `
                bg-slate-100
                text-slate-700

                dark:bg-slate-800
                dark:text-slate-300
            `;

        case 'RESPONSE':
            return `
                bg-blue-100
                text-blue-700

                dark:bg-blue-900/30
                dark:text-blue-300
            `;

        case 'PROCESS':
            return `
                bg-indigo-100
                text-indigo-700

                dark:bg-indigo-900/30
                dark:text-indigo-300
            `;

        case 'PENDING':
            return `
                bg-yellow-100
                text-yellow-700

                dark:bg-yellow-900/30
                dark:text-yellow-300
            `;

        case 'ENVISION':
            return `
                bg-cyan-100
                text-cyan-700

                dark:bg-cyan-900/30
                dark:text-cyan-300
            `;

        case 'TRANSFER':
            return `
                bg-purple-100
                text-purple-700

                dark:bg-purple-900/30
                dark:text-purple-300
            `;

        case 'COMPLETED':
            return `
                bg-green-100
                text-green-700

                dark:bg-green-900/30
                dark:text-green-300
            `;

        case 'REOPEN':
            return `
                bg-orange-100
                text-orange-700

                dark:bg-orange-900/30
                dark:text-orange-300
            `;

        case 'CANCEL':
            return `
                bg-red-100
                text-red-700

                dark:bg-red-900/30
                dark:text-red-300
            `;

        default:
            return `
                bg-gray-100
                text-gray-700

                dark:bg-gray-800
                dark:text-gray-300
            `;

    }

}
function renderTicketStatusBadge(status) {

    switch ((status || '').toUpperCase()) {

        case 'P':

            return `
                <span class="
                    inline-flex
                    items-center
                    rounded-full
                    px-2.5 py-1
                    text-[11px]
                    font-semibold

                    bg-blue-100
                    text-blue-700

                    dark:bg-blue-900/30
                    dark:text-blue-300
                ">
                    Open
                </span>
            `;

        case 'C':

            return `
                <span class="
                    inline-flex
                    items-center
                    rounded-full
                    px-2.5 py-1
                    text-[11px]
                    font-semibold

                    bg-green-100
                    text-green-700

                    dark:bg-green-900/30
                    dark:text-green-300
                ">
                    Completed
                </span>
            `;

        case 'R':

            return `
                <span class="
                    inline-flex
                    items-center
                    rounded-full
                    px-2.5 py-1
                    text-[11px]
                    font-semibold

                    bg-orange-100
                    text-orange-700

                    dark:bg-orange-900/30
                    dark:text-orange-300
                ">
                    Reopen
                </span>
            `;

        case 'X':

            return `
                <span class="
                    inline-flex
                    items-center
                    rounded-full
                    px-2.5 py-1
                    text-[11px]
                    font-semibold

                    bg-red-100
                    text-red-700

                    dark:bg-red-900/30
                    dark:text-red-300
                ">
                    Cancelled
                </span>
            `;

        default:

            return `
                <span class="
                    inline-flex
                    items-center
                    rounded-full
                    px-2.5 py-1
                    text-[11px]
                    font-semibold

                    bg-gray-100
                    text-gray-700

                    dark:bg-gray-800
                    dark:text-gray-300
                ">
                    -
                </span>
            `;

    }

}

function renderWorkflowBadge(status) {

    return `
        <span class="
            inline-flex
            items-center
            rounded-full
            px-2.5 py-1
            text-[11px]
            font-semibold
            ${workflowBadgeClass(status)}
        ">
            ${status ?? '-'}
        </span>
    `;

}
