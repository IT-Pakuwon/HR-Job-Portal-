window.BookingCar = window.BookingCar || {};
BookingCar.state = BookingCar.state || {};
BookingCar.el = BookingCar.el || {};

BookingCar.openCreateBookingModal = (payload = {}) => {

    const $modal =
        $('#createBookingModal');

    if (
        $('#createBookingModal')
            .hasClass('hidden')
    ) {

        BookingCar.resetCreateBookingForm();
    }

    $modal
        .removeClass('hidden')
        .addClass('flex');

    BookingCar.initSingleSelect();

    setTimeout(() => {

        BookingCar
            .filterUserRequestByDepartment();

    }, 100);

    $('body')
        .addClass('overflow-hidden');

    requestAnimationFrame(() => {

        $modal
            .find('.modal-backdrop')
            .removeClass('opacity-0')
            .addClass('opacity-100');

        $modal
            .find('.modal-panel')
            .removeClass(
                'translate-y-4 scale-[0.98] opacity-0'
            )
            .addClass(
                'translate-y-0 scale-100 opacity-100'
            );
    });

    if (
        !$('#purpose_id')
            .hasClass('select2-hidden-accessible')
    ) {

        BookingCar.initializeCreateSelect2();
    }

    setTimeout(() => {

        if (
            payload.booking_date &&
            document.querySelector('#booking_date')._flatpickr
        ) {

            document
                .querySelector('#booking_date')
                ._flatpickr
                .setDate(payload.booking_date, true);
        }

        if (
            payload.start_time &&
            document.querySelector('#start_time')._flatpickr
        ) {

            document
                .querySelector('#start_time')
                ._flatpickr
                .setDate(payload.start_time, true, 'H:i');
        }

        if (
            payload.end_time &&
            document.querySelector('#end_time')._flatpickr
        ) {

            document
                .querySelector('#end_time')
                ._flatpickr
                .setDate(payload.end_time, true, 'H:i');
        }

    }, 150);
};
BookingCar.closeCreateBookingModal = () => {

    const $modal =
        $('#createBookingModal');

    $modal
        .find('.modal-backdrop')
        .removeClass('opacity-100')
        .addClass('opacity-0');

    $modal
        .find('.modal-panel')
        .removeClass(
            'translate-y-0 scale-100 opacity-100'
        )
        .addClass(
            'translate-y-4 scale-[0.98] opacity-0'
        );

    setTimeout(() => {

        $modal
            .removeClass('flex')
            .addClass('hidden');

        $('body')
            .removeClass('overflow-hidden');

    }, 200);
};
BookingCar.resetCreateBookingForm = () => {

    const form =
        $('#bookingCarForm')[0];

    if (form) {
        form.reset();
    }

    $('#createRouteTableBody')
        .empty();

    $('#keterangan')
        .val('');

    BookingCar.addCreateRouteRow();

    /*
    |--------------------------------------------------------------------------
    | RESET SELECT
    |--------------------------------------------------------------------------
    */
    $('#bookingCarForm')
        .find('select')
        .each(function () {

            const $select = $(this);

            const validOptions =
                $select.find('option')
                    .filter(function () {

                        return $(this).val() !== '';
                    });

            /*
            |--------------------------------------------------------------------------
            | AUTO SELECT SINGLE OPTION
            |--------------------------------------------------------------------------
            */
            if (
                validOptions.length === 1
            ) {

                $select
                    .val(
                        validOptions.first().val()
                    )
                    .trigger('change');

            } else {

                $select
                    .val('')
                    .trigger('change');
            }
        });
};

BookingCar.initSingleSelect = () => {

    [
        '#cpny_id',
        '#department_id',
       '#cpny_id_site'
    ].forEach(selector => {

        const $select = $(selector);

        const totalOption =
            $select.find('option[value!=""]').length;

        if (totalOption === 1) {

            const value =
                $select.find('option[value!=""]').first().val();

            $select
                .val(value)
                .trigger('change');
        }
    });
};

BookingCar.initializeCreateSelect2 = () => {

    if (
        typeof $.fn.select2 === 'undefined'
    ) return;

    const config = {

        dropdownParent:
            $('#createBookingModal'),

        width: '100%',
    };

    $('#cpny_id').select2(config);

    $('#department_id').select2(config);

    $('#department_id').on(
        'change',
        function () {

            BookingCar
                .filterUserRequestByDepartment();
        }
    );

    $('#cpny_id_site').select2({
        ...config,
        placeholder: 'Select Company',
    });

    $('#purpose_id').select2({
        ...config,
        placeholder: 'Select Purpose',
    });

    $('#user_request').select2({
        ...config,
        placeholder: 'Select passenger',
    });

    BookingCar.filterUserRequestByDepartment();
};

BookingCar.filterUserRequestByDepartment = () => {

    const selectedDept =
        ($('#department_id').val() || '')
            .toString()
            .trim();

    const $userRequest =
        $('#user_request');

    /*
    |--------------------------------------------------------------------------
    | STORE CURRENT VALUE
    |--------------------------------------------------------------------------
    */
    const currentValue =
        $userRequest.val();

    /*
    |--------------------------------------------------------------------------
    | GET ALL OPTIONS
    |--------------------------------------------------------------------------
    */
    const allOptions =
        $userRequest
            .data('all-options');

    /*
    |--------------------------------------------------------------------------
    | FIRST LOAD
    |--------------------------------------------------------------------------
    */
    if (!allOptions) {

        $userRequest.data(
            'all-options',
            $userRequest.html()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET ORIGINAL OPTIONS
    |--------------------------------------------------------------------------
    */
    $userRequest.html(
        $userRequest.data('all-options')
    );

    /*
    |--------------------------------------------------------------------------
    | FILTER OPTIONS
    |--------------------------------------------------------------------------
    */
    $userRequest.find('option').each(function () {

        const $option =
            $(this);

        const optionValue =
            ($option.val() || '')
                .toString()
                .trim();

        const optionDept =
            ($option.data('dept') || '')
                .toString()
                .trim();

        /*
        |--------------------------------------------------------------------------
        | KEEP PLACEHOLDER
        |--------------------------------------------------------------------------
        */
        if (!optionValue) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | REMOVE DIFFERENT DEPARTMENT
        |--------------------------------------------------------------------------
        */
        if (
            selectedDept &&
            optionDept !== selectedDept
        ) {

            $option.remove();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | RESET INVALID VALUE
    |--------------------------------------------------------------------------
    */
    if (
        !$userRequest.find(
            `option[value="${currentValue}"]`
        ).length
    ) {

        $userRequest.val('');
    } else {

        $userRequest.val(currentValue);
    }

    /*
    |--------------------------------------------------------------------------
    | RELOAD SELECT2
    |--------------------------------------------------------------------------
    */
    if (
        $userRequest.hasClass(
            'select2-hidden-accessible'
        )
    ) {

        $userRequest.select2('destroy');
    }

    $userRequest.select2({

        dropdownParent:
            $('#createBookingModal'),

        width: '100%',

        placeholder:
            'Select passenger',
    });
};

BookingCar.addCreateRouteRow = () => {

    const index =
        $('#createRouteTableBody tr').length;

    const row = `
        <tr data-index="${index}"
            class="border-b border-slate-200 dark:border-white/10">

            <td class="px-4 py-3 text-sm font-medium text-slate-600 dark:text-slate-300">
                ${index + 1}
            </td>

            <td class="px-4 py-3">

                <input type="text"
                    name="location_from[]"
                    placeholder="Pickup location"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

            </td>

            <td class="px-4 py-3">

                <input type="text"
                    name="destination[]"
                    placeholder="Destination location"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100 dark:border-white/10 dark:bg-[#0b1220] dark:text-white">

            </td>

            <td class="px-4 py-3 text-right">

                <button type="button"
                    class="remove-route-btn inline-flex h-10 w-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                    <i class="fa-solid fa-xmark text-sm"></i>

                </button>

            </td>

        </tr>
    `;

    $('#createRouteTableBody')
        .append(row);
};

BookingCar.removeCreateRouteRow = (
    button
) => {

    const totalRows =
        $('#createRouteTableBody tr')
            .length;

    if (totalRows <= 1) {

        Swal.fire({
            icon: 'warning',
            title:
                'Minimum Route',
            text:
                'At least one route is required',
        });

        return;
    }

    $(button)
        .closest('tr')
        .remove();

    BookingCar.reorderCreateRouteRows();
};

BookingCar.reorderCreateRouteRows = () => {

    $('#createRouteTableBody tr')
        .each(
            (index, element) => {

                $(element)
                    .find(
                        'td:first'
                    )
                    .html(
                        index + 1
                    );
            }
        );
};

BookingCar.collectCreateRoutes = () => {

    const routes = [];

    $('#createRouteTableBody tr').each((_, element) => {

        const pickup =
            $(element)
                .find('input[name="location_from[]"]')
                .val();

        const destination =
            $(element)
                .find('input[name="destination[]"]')
                .val();

        routes.push({
            pickup: pickup,
            destination: destination,
        });
    });

    return routes;
};

BookingCar.validateCreateForm = () => {

    const routes =
        BookingCar.collectCreateRoutes();

    let valid = true;

    routes.forEach(route => {

        if (
            !route.pickup ||
            !route.destination
        ) {

            valid = false;
        }
    });

    if (
        !$('#purpose_id').val()
    ) {

        Swal.fire({
            icon: 'warning',
            title: 'Purpose Required',
            text: 'Please select purpose',
        });

        return false;
    }

    if (!valid) {

        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Route',
            text:
                'Please complete all pickup and destination fields',
        });

        return false;
    }

    return true;
};

BookingCar.submitCreateBooking =
    async () => {

        if (
            !BookingCar.validateCreateForm()
        ) {

            return;
        }

        const form =
            $('#bookingCarForm');

        const submitButton =
            $(
                'button[form="bookingCarForm"]'
            );

        const formData = new FormData();

        $('#bookingCarForm')
            .serializeArray()
            .forEach(field => {

                formData.append(
                    field.name,
                    field.value
                );
            });

        try {

            submitButton.prop(
                'disabled',
                true
            );

            submitButton.html(`
                <span class="inline-flex items-center gap-2">

                    <svg class="h-4 w-4 animate-spin"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24">

                        <circle class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4">
                        </circle>

                        <path class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8v8H4z">
                        </path>

                    </svg>

                    Processing...

                </span>
            `);

            const response =
                await $.ajax({

                    url:
                        BookingCar
                            .config
                            .routes
                            .store,

                    type: 'POST',

                    data: formData,

                    processData: false,

                    contentType: false,

                    headers: {
                        'X-CSRF-TOKEN':
                            BookingCar
                                .config
                                .csrf,
                    },
                });

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text:
                    response.message ||
                    'Booking created successfully',
            });

            BookingCar.closeCreateBookingModal();

            await BookingCar.reloadAllData();

        } catch (error) {

            console.error(
                'Create booking failed:',
                error
            );

            let message =
                'Failed create booking';

            if (
                error.responseJSON
                    ?.message
            ) {

                message =
                    error
                        .responseJSON
                        .message;
            }

            if (
                error.responseJSON
                    ?.errors
            ) {

                const firstError =
                    Object.values(
                        error
                            .responseJSON
                            .errors
                    )[0];

                if (
                    Array.isArray(
                        firstError
                    )
                ) {

                    message =
                        firstError[0];
                }
            }

            Swal.fire({
                icon: 'error',
                title: 'Failed',
                text: message,
            });

        } finally {

            submitButton.prop(
                'disabled',
                false
            );

            submitButton.html(`
                <i class="fa-solid fa-paper-plane text-xs"></i>
                Submit Request
            `);
        }
    };

$(document).on(
    'click',
    '#openCreateBookingModal',
    () => {

        BookingCar.openCreateBookingModal();
    }
);

$(document).on(
    'click',
    '#closeCreateBookingModal',
    () => {

        BookingCar.closeCreateBookingModal();
    }
);

$(document).on(
    'click',
    '#closeCreateBookingModalFooter',
    () => {

        BookingCar.closeCreateBookingModal();
    }
);

$(document).on(
    'click',
    '#createBookingModal',
    function (e) {

        if (
            e.target.id ===
            'createBookingModal'
        ) {

            BookingCar.closeCreateBookingModal();
        }
    }
);

$(document).on(
    'click',
    '#createAddRouteBtn',
    () => {

        BookingCar.addCreateRouteRow();
    }
);

$(document).on(
    'click',
    '.remove-route-btn',
    function () {

        BookingCar.removeCreateRouteRow(
            this
        );
    }
);

$(document).on(
    'change',
    '#department_id',
    function () {

        BookingCar
            .filterUserRequestByDepartment();
    }
);

$(document).on(
    'submit',
    '#bookingCarForm',
    async function (e) {

        e.preventDefault();

        await BookingCar.submitCreateBooking();
    }
);

$(document).on(
    'keydown',
    function (e) {

        if (
            e.key === 'Escape'
        ) {

            BookingCar.closeCreateBookingModal();
        }
    }
);
