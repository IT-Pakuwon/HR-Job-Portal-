window.BookingCar = window.BookingCar || {};

BookingCar.formatDate = (date) => {

    if (!date) return '-';

    const d = new Date(date);

    return d.toLocaleDateString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
};

BookingCar.formatDateTime = (date) => {

    if (!date) return '-';

    const d = new Date(date);

    return d.toLocaleString('en-GB', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

BookingCar.formatTime = (time) => {

    if (!time) return '-';

    return time.toString().substring(0, 5);
};

BookingCar.escapeHtml = (unsafe = '') => {
    return $('<div>').text(unsafe).html();
};

BookingCar.showLoading = (title = 'Loading...') => {
    Swal.fire({
        title,
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

BookingCar.hideLoading = () => {
    Swal.close();
};

BookingCar.showSuccess = (message = 'Success') => {
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: message,
        timer: 1800,
        showConfirmButton: false,
    });
};

BookingCar.nl2br = (value) => {

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {

        return '-';
    }

    return String(value)
        .replace(/\n/g, '<br>');
};

BookingCar.showError = (message = 'Something went wrong') => {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
    });
};

BookingCar.showWarning = (message = 'Warning') => {
    Swal.fire({
        icon: 'warning',
        title: 'Warning',
        text: message,
    });
};

BookingCar.confirmAction = async ({
    title = 'Are you sure?',
    text = '',
    confirmButtonText = 'Yes',
    cancelButtonText = 'Cancel',
    icon = 'warning',
}) => {
    return await Swal.fire({
        title,
        text,
        icon,
        showCancelButton: true,
        confirmButtonText,
        cancelButtonText,
        reverseButtons: true,
    });
};

BookingCar.openModal = (modal) => {
    if (!modal) return;

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
};

BookingCar.closeModal = (modal) => {
    if (!modal) return;

    modal.classList.add('hidden');

    const visibleModal = document.querySelector(
        '#createBookingModal:not(.hidden), #viewBookingModal:not(.hidden), #editBookingModal:not(.hidden), #gaProcessModal:not(.hidden)'
    );

    if (!visibleModal) {
        document.body.classList.remove('overflow-hidden');
    }
};

BookingCar.resetCreateForm = () => {
    const form = BookingCar.el.bookingCarForm;

    if (!form) return;

    form.reset();

    BookingCar.state.routeIndex = 0;

    if (BookingCar.el.createRouteTableBody) {
        BookingCar.el.createRouteTableBody.innerHTML = '';
    }
};

BookingCar.resetEditForm = () => {
    const form = BookingCar.el.editBookingForm;

    if (!form) return;

    form.reset();

    BookingCar.state.editRouteIndex = 0;

    if (BookingCar.el.editRouteTableBody) {
        BookingCar.el.editRouteTableBody.innerHTML = '';
    }
};

BookingCar.badgeStatus = (status) => {
    const map = {
        P: {
            label: 'Pending',
            class: 'bg-blue-100 text-blue-700 border-blue-200',
        },
        C: {
            label: 'Approved',
            class: 'bg-emerald-100 text-emerald-700 border-emerald-200',
        },
        D: {
            label: 'Revise',
            class: 'bg-amber-100 text-amber-700 border-amber-200',
        },
        R: {
            label: 'Rejected',
            class: 'bg-red-100 text-red-700 border-red-200',
        },
        X: {
            label: 'Closed',
            class: 'bg-gray-100 text-gray-700 border-gray-200',
        },
        WAITING_PROCESS: {
            label: 'Waiting Process',
            class: 'bg-indigo-100 text-indigo-700 border-indigo-200',
        },
    };

    const item = map[status] || {
        label: status || '-',
        class: 'bg-gray-100 text-gray-700 border-gray-200',
    };

    return `
        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold ${item.class}">
            ${item.label}
        </span>
    `;
};

BookingCar.getStatusBadge = (status) => {

    const map = {
        P: {
            text: 'Pending',
            class: 'bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300',
        },

        C: {
            text: 'Approved',
            class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300',
        },

        D: {
            text: 'Revise',
            class: 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300',
        },

        R: {
            text: 'Rejected',
            class: 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-300',
        },

        X: {
            text: 'Closed',
            class: 'bg-slate-200 text-slate-700 dark:bg-slate-500/15 dark:text-slate-300',
        },

        WAITING_PROCESS: {
            text: 'Waiting Process',
            class: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-300',
        },
    };

    return map[status] || {
        text: status || '-',
        class: 'bg-slate-100 text-slate-600',
    };
};
BookingCar.renderEmptyState = (
    message = 'No booking request available'
) => {
    return `
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 px-6 py-14 text-center">
            <div class="text-4xl">
                🚘
            </div>

            <div class="mt-3 text-sm font-semibold text-gray-700">
                ${message}
            </div>

            <div class="mt-1 text-xs text-gray-400">
                Booking data will appear here
            </div>
        </div>
    `;
};

BookingCar.debounce = (func, delay = 500) => {
    let timeout;

    return (...args) => {
        clearTimeout(timeout);

        timeout = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
};

BookingCar.scrollTopModal = (modalSelector) => {
    const modal = document.querySelector(modalSelector);

    if (!modal) return;

    modal.scrollTop = 0;
};

BookingCar.generateRouteRow = ({
    index,
    pickup = '',
    destination = '',
    type = 'create',
}) => {
    const pickupName =
        type === 'create'
            ? `pickup_location[${index}]`
            : `edit_pickup_location[${index}]`;

    const destinationName =
        type === 'create'
            ? `destination_location[${index}]`
            : `edit_destination_location[${index}]`;

    return `
        <tr class="border-b border-gray-100">
            <td class="px-4 py-3 text-sm font-medium text-gray-600">
                ${index + 1}
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="${pickupName}"
                    value="${BookingCar.escapeHtml(pickup)}"
                    placeholder="Pickup location"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                >
            </td>

            <td class="px-4 py-3">
                <input
                    type="text"
                    name="${destinationName}"
                    value="${BookingCar.escapeHtml(destination)}"
                    placeholder="Destination location"
                    class="w-full rounded-xl border border-gray-200 px-3 py-2 text-sm focus:border-black focus:outline-none"
                >
            </td>

            <td class="px-4 py-3 text-center">
                <button
                    type="button"
                    class="remove-route-btn inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100"
                >
                    ✕
                </button>
            </td>
        </tr>
    `;
};
