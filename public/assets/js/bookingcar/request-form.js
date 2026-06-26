// ============================================================
// request-form.js — Booking Car
// Create booking form: Select2 init, department filter, validate, submit
// ============================================================

const BookingCarForm = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isSubmitting: false,
    },

    // --------------------------------------------------------
    // INIT — wire events once on page load
    // --------------------------------------------------------
    init() {
        // Open modal button
        document.getElementById('openCreateBookingModal')
            ?.addEventListener('click', () => {
                BookingCarModal.openCreate();
                BookingCarForm.onOpen();
            });

        // Form submit
        document.getElementById('bookingCarForm')
            ?.addEventListener('submit', async (e) => {
                e.preventDefault();
                await BookingCarForm.submit();
            });

        // Department or Company Expense change → re-filter passenger list
        $('#department_id, #cpny_id_site').on('change.bookingFilter', function () {
            BookingCarForm.filterUserByDept();
        });
    },

    // --------------------------------------------------------
    // ON OPEN — called every time the create modal opens
    // --------------------------------------------------------
    onOpen(payload = {}) {
        // Reset form state
        BookingCarHelper.resetForm('bookingCarForm');
        BookingCarRoute.clearCreate();

        // Initialize dropdowns
        BookingCarForm.initSelect2();
        BookingCarForm.initSingleSelect();
        BookingCarForm.filterUserByDept();

        // Pre-fill from payload (e.g. calendar date click)
        if (payload.booking_date) {
            BookingCarHelper.setValue('booking_date', payload.booking_date);
        }
        if (payload.start_time) {
            BookingCarHelper.setValue('start_time', payload.start_time);
        }
        if (payload.end_time) {
            BookingCarHelper.setValue('end_time', payload.end_time);
        }
    },

    // --------------------------------------------------------
    // INIT SELECT2  — only initializes each dropdown once
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const config = {
            dropdownParent: $('#createBookingModal'),
            width:          '100%',
            allowClear:     false,
        };

        const init = ($el, extra = {}) => {
            if (!$el.length) return;

            // Destroy if already initialized
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }

            $el.select2({ ...config, ...extra });
        };

        init($('#cpny_id'));
        init($('#department_id'));
        init($('#cpny_id_site'), { placeholder: 'Select Company' });
        init($('#purpose_id'),   { placeholder: 'Select Purpose' });
        // #user_request is managed by filterUserByDept()
    },

    // --------------------------------------------------------
    // AUTO-SELECT WHEN ONLY ONE VALID OPTION EXISTS
    // --------------------------------------------------------
    initSingleSelect() {
        ['#cpny_id', '#department_id', '#cpny_id_site'].forEach(selector => {
            const $sel    = $(selector);
            if (!$sel.length) return;

            const options = $sel.find('option[value!=""]');

            // Auto-select if only one option
            if (options.length === 1) {
                $sel.val(options.first().val()).trigger('change');
            }
        });
    },

    // --------------------------------------------------------
    // FILTER PASSENGER DROPDOWN BY SELECTED DEPARTMENT
    // --------------------------------------------------------
    filterUserByDept() {
        const selectedDept = BookingCarHelper.getValue('department_id').trim();
        const selectedCpny = BookingCarHelper.getValue('cpny_id_site').trim();
        const $sel         = $('#user_request');
        if (!$sel.length) return;

        const currentVal = $sel.val();

        // Cache original HTML on first call
        if (!$sel.data('all-options')) {
            $sel.data('all-options', $sel.html());
        }

        // Destroy Select2 FIRST so we can freely modify the underlying options
        if ($sel.hasClass('select2-hidden-accessible')) {
            $sel.select2('destroy');
        }

        // Restore full option list
        $sel.html($sel.data('all-options'));

        // Remove options that don't match selected department AND company expense
        $sel.find('option').each(function () {
            const $opt = $(this);
            if (!$opt.val()) return; // keep placeholder

            const optDept = ($opt.attr('data-dept') ?? '').toString().trim();
            const optCpny = ($opt.attr('data-cpny') ?? '').toString().trim();

            const deptOk = !selectedDept || optDept === selectedDept;
            const cpnyOk = !selectedCpny || optCpny === selectedCpny;

            if (!deptOk || !cpnyOk) {
                $opt.remove();
            }
        });

        // Restore previous selection only if still valid
        const stillValid = $sel.find(`option[value="${currentVal}"]`).length > 0;
        $sel.val(stillValid ? currentVal : '');

        // Rebuild Select2 with the filtered option set
        $sel.select2({
            dropdownParent: $('#createBookingModal'),
            width:          '100%',
            placeholder:    'Select passenger',
            allowClear:     false,
        });
    },

    // --------------------------------------------------------
    // VALIDATE — comprehensive validation
    // --------------------------------------------------------
    validate() {
        // Get all form values
        const cpny_id = BookingCarHelper.getValue('cpny_id');
        const department_id = BookingCarHelper.getValue('department_id');
        const user_peminta = BookingCarHelper.getValue('user_peminta');
        const passenger = BookingCarHelper.getValue('passenger');
        const booking_date = BookingCarHelper.getValue('booking_date');
        const start_time = BookingCarHelper.getValue('start_time');
        const end_time = BookingCarHelper.getValue('end_time');
        const cpny_id_site = BookingCarHelper.getValue('cpny_id_site');
        const user_request = BookingCarHelper.getValue('user_request');
        const purpose_id = BookingCarHelper.getValue('purpose_id');
        const purpose_descr = BookingCarHelper.getValue('purpose_descr');

        // Required fields validation
        if (BookingCarHelper.isEmpty(cpny_id)) {
            BookingCar.toast('warning', 'Company is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(department_id)) {
            BookingCar.toast('warning', 'Department is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(user_peminta)) {
            BookingCar.toast('warning', 'Requester information is missing.');
            return false;
        }

        if (BookingCarHelper.isEmpty(passenger) || parseInt(passenger) < 1) {
            BookingCar.toast('warning', 'Total passenger must be at least 1.');
            return false;
        }

        if (BookingCarHelper.isEmpty(booking_date)) {
            BookingCar.toast('warning', 'Booking date is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(start_time)) {
            BookingCar.toast('warning', 'Start time is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(end_time)) {
            BookingCar.toast('warning', 'End time is required.');
            return false;
        }

        // Time validation
        if (start_time >= end_time) {
            BookingCar.toast('warning', 'Start time must be before end time.');
            return false;
        }

        if (BookingCarHelper.isEmpty(cpny_id_site)) {
            BookingCar.toast('warning', 'Company expense is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(user_request)) {
            BookingCar.toast('warning', 'User request (passenger) is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(purpose_id)) {
            BookingCar.toast('warning', 'Purpose is required.');
            return false;
        }

        if (BookingCarHelper.isEmpty(purpose_descr)) {
            BookingCar.toast('warning', 'Purpose description is required.');
            return false;
        }

        // Routes validation
        const routes = BookingCarRoute.getCreateRoutes();
        if (!BookingCarRoute.validateRoutes(routes)) {
            return false;
        }

        return true;
    },

    // --------------------------------------------------------
    // SUBMIT
    // --------------------------------------------------------
    async submit() {
        // Prevent double submit
        if (BookingCarForm.state.isSubmitting) return;

        if (!BookingCarForm.validate()) {
            return;
        }

        BookingCarForm.state.isSubmitting = true;

        const btn = document.querySelector('button[form="bookingCarForm"][type="submit"]');
        const originalText = btn?.innerHTML ?? '';

        // Loading state
        BookingCarHelper.setButtonLoading('submitRequestBtn', true, originalText);

        try {
            const formData = BookingCarHelper.getFormData('bookingCarForm');

            const res = await BookingCar.request(BookingCar.routes.store, {
                method: 'POST',
                body:   formData,
            });

            if (!res.success) {
                BookingCar.toast('error', res.message ?? 'Failed to create booking.');
                return;
            }

            // Success
            BookingCar.toast('success', res.message ?? 'Booking created successfully.');

            // Close modal
            BookingCarModal.closeCreate();

            // Refresh data
            if (typeof BookingCarDatalist !== 'undefined') {
                BookingCarDatalist.refresh();
            }

            if (typeof BookingCarCalendar !== 'undefined') {
                BookingCarCalendar.refresh?.();
            }

        } catch (err) {
            console.error('Submit error:', err);

            let message = 'Failed to create booking.';

            if (err.status === 422 && err.data?.errors) {
                // Validation errors from backend
                const errors = Object.values(err.data.errors).flat();
                message = errors[0] ?? message;
            } else if (err.data?.message) {
                message = err.data.message;
            }

            BookingCar.toast('error', message);

        } finally {
            BookingCarForm.state.isSubmitting = false;
            BookingCarHelper.setButtonLoading('submitRequestBtn', false, originalText);
        }
    },
};
