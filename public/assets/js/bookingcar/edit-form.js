// ============================================================
// edit-form.js — Booking Car
// Edit form population, validation, and submission
// ============================================================

const BookingCarEditForm = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:      null,
        currentDocid:    null,
        originalData:    null,
        isSubmitting:    false,
        fromDetail:      false,
    },

    // --------------------------------------------------------
    // INIT — called once on page load
    // --------------------------------------------------------
    init() {
        BookingCarEditForm.attachFormListeners();
    },

    // --------------------------------------------------------
    // ATTACH FORM EVENT LISTENERS
    // --------------------------------------------------------
    attachFormListeners() {
        const form = document.getElementById('editBookingForm');
        if (!form) return;

        // Form submit
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            BookingCarEditForm.submit();
        });

        // Reset button
        document.getElementById('resetEditBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarEditForm.resetToOriginal();
            });

        // Edit button (in detail modal)
        document.getElementById('editBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarEditForm.openEditForm();
            });

        // Department or Company Expense change → re-filter passenger list
        $('#edit_department_id, #edit_cpny_id_site').on('change.bookingFilter', function () {
            BookingCarEditForm.filterUserByDept();
        });

        // Driver dropdown change — auto-fill handphone
        document.getElementById('ga_driver')
            ?.addEventListener('change', (e) => {
                const option = e.target.selectedOptions[0];
                const hp = option?.dataset?.hp ?? '';
                const handphoneInput = document.getElementById('ga_handphone');
                if (handphoneInput) {
                    handphoneInput.value = hp;
                }
            });

        // Vehicle dropdown change — auto-fill no_polisi
        document.getElementById('ga_vehicle')
            ?.addEventListener('change', (e) => {
                const option = e.target.selectedOptions[0];
                const nopol = option?.value ?? '';
                const nopolInput = document.getElementById('ga_no_polisi');
                if (nopolInput) {
                    nopolInput.value = nopol;
                }
            });
    },

    // --------------------------------------------------------
    // OPEN EDIT FORM
    // --------------------------------------------------------
    async openEditForm() {
        const eid = BookingCar.state.currentEid;
        const docid = BookingCar.state.currentDocid;

        if (!eid || !docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        try {
            // Fetch full booking data
            const response = await BookingCar.request(
                BookingCar.routes.detail(eid)
            );

            if (!response.success) {
                BookingCar.toast('error', response.message ?? 'Failed to load booking');
                return;
            }

            const booking = response.data;

            // Check if editable (status must be D)
            if (booking.status !== 'D') {
                BookingCar.toast('warning', 'Only documents with "Revise" status can be edited.');
                return;
            }

            // Populate form
            BookingCarEditForm.populateForm(booking);

            // Store original data for reset
            BookingCarEditForm.state.originalData = BookingCarEditForm.getFormData();
            BookingCarEditForm.state.currentEid   = eid;
            BookingCarEditForm.state.currentDocid = docid;
            BookingCarEditForm.state.fromDetail   = true;

            // Close view modal silently (no clearDoc — state must stay intact)
            BookingCarModal.close('viewBookingModal');

            // Open edit modal
            BookingCarModal.openEdit();

        } catch (err) {
            console.error('Open edit form error:', err);
            BookingCar.toast('error', 'Failed to open edit form');
        }
    },

    // --------------------------------------------------------
    // POPULATE FORM WITH BOOKING DATA
    // --------------------------------------------------------
    populateForm(booking) {
        // Basic Information
        BookingCarHelper.setSelect('edit_cpny_id', booking.cpny_id);
        BookingCarHelper.setSelect('edit_department_id', booking.department_id);
        BookingCarHelper.setValue('edit_user_peminta', booking.user_peminta);
        BookingCarHelper.setValue('edit_user_peminta_val', booking.user_peminta);
        BookingCarHelper.setValue('edit_passenger', booking.passenger ?? 0);

        // Schedule
        BookingCarHelper.setSelect('edit_booking_date', booking.booking_date);

        // Extract time from datetime strings
        const startTime = BookingCar.formatTime(booking.start_time);
        const endTime = BookingCar.formatTime(booking.end_time);

        BookingCarHelper.setSelect('edit_start_time', startTime);
        BookingCarHelper.setSelect('edit_end_time', endTime);

        // Routes
        BookingCarRoute.loadEditRoutes(booking.details ?? []);

        // Purpose Information
        BookingCarHelper.setSelect('edit_cpny_id_site', booking.cpny_id_site);
        BookingCarHelper.setSelect('edit_purpose_id', booking.purpose_id);
        BookingCarHelper.setValue('edit_purpose_descr', booking.purpose_descr);

        // Filter user_request by department, then restore saved value
        BookingCarEditForm.filterUserByDept(booking.user_request);

        // Initialize Select2 for all other dropdowns
        BookingCarEditForm.initSelect2();

        // Show revision reason if status is D
        if (booking.status === 'D' && booking.revise_reason) {
            const wrapper = document.getElementById('editBookingReviseWrapper');
            const reason = document.getElementById('edit_booking_revise_reason');

            if (wrapper) wrapper.classList.remove('hidden');
            if (reason) reason.textContent = booking.revise_reason;
        }

        // Update modal title
        const docInfo = document.getElementById('editBookingDocInfo');
        if (docInfo) {
            docInfo.textContent = `Editing ${booking.docid}. Make corrections and resubmit for approval.`;
        }
    },

    // --------------------------------------------------------
    // FILTER PASSENGER DROPDOWN BY SELECTED DEPARTMENT
    // --------------------------------------------------------
    filterUserByDept(preselectValue = null) {
        const selectedDept = BookingCarHelper.getValue('edit_department_id').trim();
        const selectedCpny = BookingCarHelper.getValue('edit_cpny_id_site').trim();
        const $sel         = $('#edit_user_request');
        if (!$sel.length) return;

        const targetVal = preselectValue ?? $sel.val();

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
            if (!$opt.val()) return;

            const optDept = ($opt.attr('data-dept') ?? '').toString().trim();
            const optCpny = ($opt.attr('data-cpny') ?? '').toString().trim();

            const deptOk = !selectedDept || optDept.split(',').map(s => s.trim()).includes(selectedDept);
            const cpnyOk = !selectedCpny || optCpny.split(',').map(s => s.trim()).includes(selectedCpny);

            if (!deptOk || !cpnyOk) {
                $opt.remove();
            }
        });

        const stillValid = $sel.find(`option[value="${targetVal}"]`).length > 0;
        $sel.val(stillValid ? targetVal : '');

        $sel.select2({
            dropdownParent: $('#editBookingModal'),
            width:          '100%',
            placeholder:    'Select passenger',
            allowClear:     false,
        });
    },

    // --------------------------------------------------------
    // INIT SELECT2 FOR EDIT FORM DROPDOWNS
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const config = {
            dropdownParent: $('#editBookingModal'),
            width:          '100%',
            allowClear:     false,
        };

        const init = ($el, extra = {}) => {
            if (!$el.length) return;
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({ ...config, ...extra });
        };

        init($('#edit_cpny_id'));
        init($('#edit_department_id'));
        init($('#edit_cpny_id_site'), { placeholder: 'Select Company' });
        init($('#edit_purpose_id'),   { placeholder: 'Select Purpose' });
        // #edit_user_request is managed by filterUserByDept()
    },

    // --------------------------------------------------------
    // GET FORM DATA AS OBJECT
    // --------------------------------------------------------
    getFormData() {
        const form = document.getElementById('editBookingForm');
        if (!form) return {};

        const cpny_id = BookingCarHelper.getValue('edit_cpny_id');
        const department_id = BookingCarHelper.getValue('edit_department_id');
        const user_peminta = BookingCarHelper.getValue('edit_user_peminta_val');
        const passenger = BookingCarHelper.getValue('edit_passenger');
        const booking_date = BookingCarHelper.getValue('edit_booking_date');
        const start_time = BookingCarHelper.getValue('edit_start_time');
        const end_time = BookingCarHelper.getValue('edit_end_time');
        const cpny_id_site = BookingCarHelper.getValue('edit_cpny_id_site');
        const user_request = BookingCarHelper.getValue('edit_user_request');
        const purpose_id = BookingCarHelper.getValue('edit_purpose_id');
        const purpose_descr = BookingCarHelper.getValue('edit_purpose_descr');

        const routes = BookingCarRoute.getEditRoutes();

        return {
            cpny_id,
            department_id,
            user_peminta,
            passenger,
            booking_date,
            start_time,
            end_time,
            cpny_id_site,
            user_request,
            purpose_id,
            purpose_descr,
            routes,
            location_from: routes.map(r => r.origin),
            destination: routes.map(r => r.destination),
        };
    },

    // --------------------------------------------------------
    // VALIDATE FORM
    // --------------------------------------------------------
    validate() {
        const data = BookingCarEditForm.getFormData();

        // Required fields
        if (BookingCarHelper.isEmpty(data.cpny_id)) {
            BookingCar.toast('warning', 'Company is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.department_id)) {
            BookingCar.toast('warning', 'Department is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.user_peminta)) {
            BookingCar.toast('warning', 'Requester is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.passenger)) {
            BookingCar.toast('warning', 'Total passenger is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.booking_date)) {
            BookingCar.toast('warning', 'Booking date is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.start_time)) {
            BookingCar.toast('warning', 'Start time is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.end_time)) {
            BookingCar.toast('warning', 'End time is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.cpny_id_site)) {
            BookingCar.toast('warning', 'Company expense is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.user_request)) {
            BookingCar.toast('warning', 'User request is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.purpose_id)) {
            BookingCar.toast('warning', 'Purpose is required');
            return false;
        }

        if (BookingCarHelper.isEmpty(data.purpose_descr)) {
            BookingCar.toast('warning', 'Purpose description is required');
            return false;
        }

        // Routes validation
        if (!BookingCarRoute.validateRoutes(data.routes)) {
            return false;
        }

        // Time validation
        if (data.start_time >= data.end_time) {
            BookingCar.toast('warning', 'Start time must be before end time');
            return false;
        }

        return true;
    },

    // --------------------------------------------------------
    // SUBMIT FORM
    // --------------------------------------------------------
    async submit() {
        if (!BookingCarEditForm.validate()) {
            return;
        }

        const docid = BookingCar.state.currentDocid;
        if (!docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        BookingCarEditForm.state.isSubmitting = true;
        BookingCarHelper.setButtonLoading('submitEditBookingBtn', true);

        try {
            const data = BookingCarEditForm.getFormData();

            const payload = {
                cpny_id: data.cpny_id,
                department_id: data.department_id,
                user_peminta: data.user_peminta,
                passenger: data.passenger,
                booking_date: data.booking_date,
                start_time: data.start_time,
                end_time: data.end_time,
                cpny_id_site: data.cpny_id_site,
                user_request: data.user_request,
                purpose_id: data.purpose_id,
                purpose_descr: data.purpose_descr,
                location_from: data.location_from,
                destination: data.destination,
            };

            const response = await BookingCar.request(
                BookingCar.routes.update(docid),
                {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Booking updated successfully');

                // Prevent closeEdit from auto-reopening detail (save handles it below)
                BookingCarEditForm.state.fromDetail = false;
                BookingCarModal.closeEdit();

                // Reopen detail with refreshed data
                const eid = BookingCar.state.currentEid;
                if (eid) {
                    setTimeout(() => {
                        BookingCarModal.open('viewBookingModal');
                        BookingCarDetailModal.loadDetail(eid);
                    }, 300);
                }

                // Refresh list
                BookingCarDatalist.refresh();

            } else {
                BookingCar.toast('error', response.message ?? 'Failed to update booking');
            }

        } catch (err) {
            console.error('Submit error:', err);

            if (err.status === 422 && err.data?.errors) {
                const errors = Object.values(err.data.errors).flat();
                BookingCar.toast('error', errors[0] ?? 'Validation error');
            } else if (err.data?.message) {
                BookingCar.toast('error', err.data.message);
            } else {
                BookingCar.toast('error', 'Failed to update booking');
            }

        } finally {
            BookingCarEditForm.state.isSubmitting = false;
            BookingCarHelper.setButtonLoading('submitEditBookingBtn', false);
        }
    },

    // --------------------------------------------------------
    // RESET TO ORIGINAL DATA
    // --------------------------------------------------------
    resetToOriginal() {
        if (!BookingCarEditForm.state.originalData) {
            BookingCar.toast('warning', 'No original data available');
            return;
        }

        const original = BookingCarEditForm.state.originalData;

        // Restore basic fields
        BookingCarHelper.setSelect('edit_cpny_id', original.cpny_id);
        BookingCarHelper.setSelect('edit_department_id', original.department_id);
        BookingCarHelper.setValue('edit_passenger', original.passenger);
        BookingCarHelper.setSelect('edit_booking_date', original.booking_date);
        BookingCarHelper.setSelect('edit_start_time', original.start_time);
        BookingCarHelper.setSelect('edit_end_time', original.end_time);
        BookingCarHelper.setSelect('edit_cpny_id_site', original.cpny_id_site);
        BookingCarHelper.setSelect('edit_user_request', original.user_request);
        BookingCarHelper.setSelect('edit_purpose_id', original.purpose_id);
        BookingCarHelper.setValue('edit_purpose_descr', original.purpose_descr);

        // Restore routes
        BookingCarRoute.loadEditRoutes(original.routes ?? []);

        BookingCar.toast('info', 'Form reset to original values');
    },

    // --------------------------------------------------------
    // CLOSE & CLEANUP
    // --------------------------------------------------------
    cleanup() {
        BookingCarEditForm.state.currentEid = null;
        BookingCarEditForm.state.currentDocid = null;
        BookingCarEditForm.state.originalData = null;
        BookingCarEditForm.state.isSubmitting = false;

        // Reset form
        BookingCarHelper.resetForm('editBookingForm');

        // Hide revision reason
        const wrapper = document.getElementById('editBookingReviseWrapper');
        if (wrapper) wrapper.classList.add('hidden');

        // Clear routes
        BookingCarRoute.clearEdit();
    },
};
