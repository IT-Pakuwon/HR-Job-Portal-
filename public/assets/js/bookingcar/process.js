// ============================================================
// process.js — Booking Car
// GA (General Affairs) process: driver & vehicle assignment
// ============================================================

const BookingCarProcess = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        currentEid:      null,
        currentDocid:    null,
        currentBooking:  null,
        isSubmitting:    false,
    },

    // --------------------------------------------------------
    // INIT — attach event listeners
    // --------------------------------------------------------
    init() {
        BookingCarProcess.attachEventListeners();
    },

    // --------------------------------------------------------
    // ATTACH EVENT LISTENERS
    // --------------------------------------------------------
    attachEventListeners() {
        // Status toggle checkbox → show/hide the select
        document.getElementById('ga_status_toggle')
            ?.addEventListener('change', (e) => {
                const wrapper = document.getElementById('ga_status_wrapper');
                if (wrapper) wrapper.classList.toggle('hidden', !e.target.checked);
                if (!e.target.checked) {
                    const sel = document.getElementById('ga_status_perjalanan');
                    if (sel) sel.value = '';
                }
            });

        // Status perjalanan change → (kept for any future logic)
        document.getElementById('ga_status_perjalanan')
            ?.addEventListener('change', (e) => {
                BookingCarProcess.handleStatusChange(e.target.value);
            });

        // Driver selection → auto-fill handphone (jQuery for Select2 compat)
        $(document).on('change', '#ga_driver', function () {
            BookingCarProcess.handleDriverChange(this);
        });

        // Vehicle selection → auto-fill no_polisi (jQuery for Select2 compat)
        $(document).on('change', '#ga_vehicle', function () {
            BookingCarProcess.handleVehicleChange(this);
        });

        // Save button (no lock)
        document.getElementById('saveGaProcessBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarProcess.submit(false);
            });

        // Submit & Lock button
        document.getElementById('submitGaProcessBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarProcess.confirmLock();
            });

        // Process button click (from detail modal)
        document.getElementById('processBookingBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                BookingCarProcess.openProcess();
            });
    },

    // --------------------------------------------------------
    // OPEN PROCESS MODAL
    // --------------------------------------------------------
    async openProcess() {
        const eid = BookingCar.state.currentEid;
        const docid = BookingCar.state.currentDocid;

        if (!eid || !docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        try {
            // Fetch booking data
            const response = await BookingCar.request(
                BookingCar.routes.detail(eid)
            );

            if (!response.success) {
                BookingCar.toast('error', response.message ?? 'Failed to load booking');
                return;
            }

            const booking = response.data;

            // Validate status (must be C or F)
            if (!['C', 'F'].includes(booking.status)) {
                BookingCar.toast('warning', 'Only approved or processed bookings can be processed.');
                return;
            }

            // Store and populate
            BookingCarProcess.state.currentEid = eid;
            BookingCarProcess.state.currentDocid = docid;
            BookingCarProcess.state.currentBooking = booking;

            BookingCarProcess.populateProcess(booking);

            // Open modal
            BookingCarModal.openProcess();

        } catch (err) {
            console.error('Open process error:', err);
            BookingCar.toast('error', 'Failed to open process form');
        }
    },

    // --------------------------------------------------------
    // POPULATE PROCESS MODAL
    // --------------------------------------------------------
    populateProcess(booking) {
        // Store IDs
        document.getElementById('ga_process_eid').value = booking.eid ?? '';
        document.getElementById('ga_process_docid').value = booking.docid ?? '';

        // Display information
        BookingCarHelper.setText('ga_booking_docid', booking.docid ?? '-');
        BookingCarHelper.setText('ga_booking_requester', booking.user_peminta ?? '-');
        BookingCarHelper.setText('ga_booking_date', BookingCar.formatDate(booking.booking_date) ?? '-');
        BookingCarHelper.setText('ga_booking_start', BookingCar.formatTime(booking.start_time) ?? '-');
        BookingCarHelper.setText('ga_booking_end', BookingCar.formatTime(booking.end_time) ?? '-');

        // Route summary
        const routes = booking.details ?? [];
        let routeText = '-';
        if (routes.length > 0) {
            routeText = routes
                .map(r => `${r.origin} → ${r.destination}`)
                .join(', ');
        }
        BookingCarHelper.setText('ga_booking_route', routeText);

        // Purpose
        BookingCarHelper.setText('ga_booking_purpose', booking.purpose_descr ?? '-');

        // Reset status toggle + wrapper
        const statusToggle = document.getElementById('ga_status_toggle');
        const statusWrapper = document.getElementById('ga_status_wrapper');
        if (statusToggle) statusToggle.checked = false;
        if (statusWrapper) statusWrapper.classList.add('hidden');

        // Reset form fields
        BookingCarHelper.setValue('ga_status_perjalanan', '');
        BookingCarHelper.setValue('ga_handphone', '');
        BookingCarHelper.setValue('ga_no_polisi', '');

        // Reset Select2 dropdowns
        if (typeof $.fn.select2 !== 'undefined') {
            $('#ga_driver').val('').trigger('change');
            $('#ga_vehicle').val('').trigger('change');
        } else {
            BookingCarHelper.setValue('ga_driver', '');
            BookingCarHelper.setValue('ga_vehicle', '');
        }

        // Show driver and vehicle sections by default
        BookingCarHelper.show('driverAssignmentWrapper');
        BookingCarHelper.show('vehicleAssignmentWrapper');

        // Init Select2 after DOM is ready
        BookingCarProcess.initSelect2();
    },

    // --------------------------------------------------------
    // INIT SELECT2 FOR DRIVER & VEHICLE
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const config = {
            dropdownParent: $('#gaProcessModal'),
            width:          '100%',
            allowClear:     true,
        };

        const $driver = $('#ga_driver');
        if ($driver.hasClass('select2-hidden-accessible')) $driver.select2('destroy');
        $driver.select2({ ...config, placeholder: 'Search driver...' });

        const $vehicle = $('#ga_vehicle');
        if ($vehicle.hasClass('select2-hidden-accessible')) $vehicle.select2('destroy');
        $vehicle.select2({ ...config, placeholder: 'Search vehicle...' });
    },

    // --------------------------------------------------------
    // HANDLE STATUS PERJALANAN CHANGE
    // --------------------------------------------------------
    handleStatusChange(statusPerjalanan) {
        // Driver and vehicle are always shown; status_perjalanan is optional context
        BookingCarHelper.show('driverAssignmentWrapper');
        BookingCarHelper.show('vehicleAssignmentWrapper');
    },

    // --------------------------------------------------------
    // HANDLE DRIVER SELECTION
    // --------------------------------------------------------
    handleDriverChange(selectElement) {
        const option = selectElement.selectedOptions[0];
        const hp = option?.dataset?.hp ?? '';

        const handphoneInput = document.getElementById('ga_handphone');
        if (handphoneInput) {
            handphoneInput.value = hp;
        }
    },

    // --------------------------------------------------------
    // HANDLE VEHICLE SELECTION
    // --------------------------------------------------------
    handleVehicleChange(selectElement) {
        const nopol = selectElement.value ?? '';

        const nopolInput = document.getElementById('ga_no_polisi');
        if (nopolInput) {
            nopolInput.value = nopol;
        }
    },

    // --------------------------------------------------------
    // VALIDATE FORM
    // --------------------------------------------------------
    validate() {
        return true;
    },

    // --------------------------------------------------------
    // CONFIRM LOCK (shows warning before Submit & Lock)
    // --------------------------------------------------------
    confirmLock() {
        BookingCar.confirm({
            title: 'Submit & Lock?',
            text: 'Once submitted, this booking cannot be edited again. Are you sure?',
            icon: 'warning',
            confirmText: 'Yes, Submit & Lock',
            confirmColor: '#059669',
            cancelText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) BookingCarProcess.submit(true);
        });
    },

    // --------------------------------------------------------
    // SUBMIT FORM
    // --------------------------------------------------------
    async submit(lock = false) {
        if (BookingCarProcess.state.isSubmitting) return;

        if (!BookingCarProcess.validate()) return;

        const eid   = document.getElementById('ga_process_eid')?.value  || BookingCarProcess.state.currentEid;
        const docid = document.getElementById('ga_process_docid')?.value || BookingCarProcess.state.currentDocid;

        if (!eid || !docid) {
            BookingCar.toast('error', 'Invalid booking reference');
            return;
        }

        BookingCarProcess.state.isSubmitting = true;
        const btnId = lock ? 'submitGaProcessBtn' : 'saveGaProcessBtn';
        BookingCarHelper.setButtonLoading(btnId, true);

        try {
            const statusPerjalanan = BookingCarHelper.getValue('ga_status_perjalanan');
            const driver    = BookingCarHelper.getValue('ga_driver')   || null;
            const handphone = BookingCarHelper.getValue('ga_handphone') || null;
            const nopol     = BookingCarHelper.getValue('ga_no_polisi') || null;

            const payload = {
                lock:              lock,
                status_perjalanan: statusPerjalanan || null,
                driver:            driver,
                handphone:         handphone,
                no_polisi:         nopol,
            };

            const response = await BookingCar.request(
                BookingCar.routes.process(eid),
                {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body:    JSON.stringify(payload),
                }
            );

            if (response.success) {
                BookingCar.toast('success', response.message ?? 'Saved successfully');

                if (response.locked) {
                    // Locked — close modal, refresh list
                    BookingCarModal.closeProcess();
                    setTimeout(() => {
                        BookingCarDetailModal.refresh();
                        BookingCarDatalist.refresh();
                    }, 400);
                } else {
                    // Saved but not locked — reload detail in process modal
                    BookingCarDatalist.refresh();
                    setTimeout(() => BookingCarDetailModal.refresh(), 400);
                }
            } else {
                BookingCar.toast('error', response.message ?? 'Failed to save');
            }

        } catch (err) {
            console.error('Process submit error:', err);
            let message = 'Failed to save';
            if (err.status === 403)                         message = 'You do not have permission to process this booking';
            else if (err.status === 422 && err.data?.errors) message = Object.values(err.data.errors).flat()[0] ?? message;
            else if (err.data?.message)                     message = err.data.message;
            BookingCar.toast('error', message);

        } finally {
            BookingCarProcess.state.isSubmitting = false;
            BookingCarHelper.setButtonLoading(btnId, false);
        }
    },

    // --------------------------------------------------------
    // CLEANUP ON MODAL CLOSE
    // --------------------------------------------------------
    cleanup() {
        BookingCarProcess.state.currentEid = null;
        BookingCarProcess.state.currentDocid = null;
        BookingCarProcess.state.currentBooking = null;
        BookingCarProcess.state.isSubmitting = false;

        // Reset form
        BookingCarHelper.resetForm('gaProcessForm');

        // Hide sections
        BookingCarHelper.hide('driverAssignmentWrapper');
        BookingCarHelper.hide('vehicleAssignmentWrapper');
    },

    // --------------------------------------------------------
    // GET TRAVEL STATUS OPTIONS
    // --------------------------------------------------------
    getTravelStatusOptions() {
        // These should come from the Blade template (from controller)
        // Common options include:
        // - "Company Car" → no driver/vehicle needed (company provides)
        // - "Handle by Taxi" → requires driver & vehicle assignment
        // - "Cancel Trip" → trip is cancelled
        // - etc.

        return [
            { value: 'Company Car', label: 'Company Car' },
            { value: 'Handle by Taxi', label: 'Handle by Taxi' },
        ];
    },

    // --------------------------------------------------------
    // SHOW PROCESS BUTTON (for GA users in detail modal)
    // --------------------------------------------------------
    canShowProcessButton(booking) {
        // Show process button if:
        // 1. User is GA (checked server-side with auth)
        // 2. Status is C (Approved) or F (Processed)
        // 3. Within H+3 from approval (checked server-side)

        if (!booking) return false;
        return ['C', 'F'].includes(booking.status);
    },

    // --------------------------------------------------------
    // FORMAT TRAVEL STATUS
    // --------------------------------------------------------
    formatTravelStatus(status) {
        const statuses = {
            'Company Car': '🚗 Company Car',
            'Handle by Taxi': '🚕 Handle by Taxi',
            'Cancel Trip': '❌ Cancel Trip',
        };

        return statuses[status] ?? status;
    },

    // --------------------------------------------------------
    // GET PROCESS SUMMARY
    // --------------------------------------------------------
    getProcessSummary(booking) {
        if (!booking) return {};

        return {
            docid: booking.docid,
            requester: booking.user_peminta,
            date: BookingCar.formatDate(booking.booking_date),
            time: `${BookingCar.formatTime(booking.start_time)} - ${BookingCar.formatTime(booking.end_time)}`,
            routes: booking.details?.length ?? 0,
            purpose: booking.purpose_descr,
            passenger: booking.passenger ?? 0,
        };
    },
};
