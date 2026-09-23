// ============================================================
// edit-form.js — Voucher Taxi
// Edit form population, validation, and submission
// ============================================================

const VoucherTaxiEditForm = {

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
        VoucherTaxiEditForm.attachFormListeners();
    },

    // --------------------------------------------------------
    // ATTACH FORM EVENT LISTENERS
    // --------------------------------------------------------
    attachFormListeners() {
        const form = document.getElementById('editVoucherTaxiForm');
        if (!form) return;

        // Form submit
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            VoucherTaxiEditForm.submit();
        });

        // Reset button
        document.getElementById('resetEditVoucherBtn')
            ?.addEventListener('click', (e) => {
                e.preventDefault();
                VoucherTaxiEditForm.resetToOriginal();
            });

        // Note: close buttons are handled by modal.js init()
    },

    // --------------------------------------------------------
    // CLOSE FORM  (called by modal.js closeEdit)
    // --------------------------------------------------------
    closeForm() {
        VoucherTaxiModal.closeEdit();
    },

    // --------------------------------------------------------
    // RESET  (called by modal.js closeEdit onClosed callback)
    // --------------------------------------------------------
    reset() {
        document.getElementById('editVoucherTaxiForm')?.reset();
        $('#edit_purpose').val(null).trigger('change');
        $('#edit_user_topup').val(null).trigger('change');
        VoucherTaxiEditForm.state.currentEid    = null;
        VoucherTaxiEditForm.state.currentDocid  = null;
        VoucherTaxiEditForm.state.originalData  = null;
        VoucherTaxiEditForm.state.fromDetail    = false;
        VoucherTaxiHelper.hide('editReviseReasonWrapper');
    },

    // --------------------------------------------------------
    // OPEN EDIT FORM
    // --------------------------------------------------------
    async openEditForm() {
        const eid = VoucherTaxi.state.currentEid;
        const docid = VoucherTaxi.state.currentDocid;

        if (!eid || !docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        try {
            // Fetch full voucher data
            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.detail(eid)
            );

            if (!response.success) {
                VoucherTaxi.toast('error', response.message ?? 'Failed to load voucher');
                return;
            }

            const voucher = response.data;

            // Check if editable (status must be D)
            if (voucher.status !== 'D') {
                VoucherTaxi.toast('warning', 'Only vouchers with "Revise" status can be edited.');
                return;
            }

            // Populate form
            VoucherTaxiEditForm.populateForm(voucher);

            // Store original data for reset
            VoucherTaxiEditForm.state.originalData = VoucherTaxiEditForm.getFormData();
            VoucherTaxiEditForm.state.currentEid   = eid;
            VoucherTaxiEditForm.state.currentDocid = docid;
            VoucherTaxiEditForm.state.fromDetail   = true;

            // Close view modal silently (no clearDoc — state must stay intact)
            VoucherTaxiModal.closeView();

            // Open edit modal
            VoucherTaxiModal.openEdit();

        } catch (err) {
            console.error('Open edit form error:', err);
            VoucherTaxi.toast('error', 'Failed to open edit form');
        }
    },

    // --------------------------------------------------------
    // POPULATE FORM WITH VOUCHER DATA
    // --------------------------------------------------------
    populateForm(voucher) {
        // Basic Information
        VoucherTaxiHelper.setSelect('edit_cpny_id', voucher.cpny_id);
        VoucherTaxiHelper.setSelect('edit_department_id', voucher.department_id);
        VoucherTaxiHelper.setValue('edit_user_peminta', voucher.user_peminta);
        VoucherTaxiHelper.setValue('edit_requester_name', voucher.user_name ?? voucher.user_peminta);

        // Date Used
        VoucherTaxiHelper.setValue('edit_date_used', voucher.date_used);

        // Trip Type — scope to EDIT form so CREATE form radios aren't found first
        const editForm = document.getElementById('editVoucherTaxiForm');
        const tripTypeRadio = editForm?.querySelector(`input[name="type_trip"][value="${voucher.type_trip}"]`);
        if (tripTypeRadio) tripTypeRadio.checked = true;

        // Route Information
        VoucherTaxiHelper.setValue('edit_origin', voucher.origin);
        VoucherTaxiHelper.setValue('edit_destination', voucher.destination);

        // Purpose — Select2 AJAX: must inject option before selecting
        if (voucher.purpose_id) {
            const $purposeSel = $('#edit_purpose');
            const label = voucher.purpose_name ?? voucher.purpose_id;
            if ($purposeSel.find(`option[value="${voucher.purpose_id}"]`).length === 0) {
                $purposeSel.append(new Option(label, voucher.purpose_id, true, true));
            } else {
                $purposeSel.val(voucher.purpose_id);
            }
            $purposeSel.trigger('change');
        }
        VoucherTaxiHelper.setValue('edit_purpose_desc', voucher.purpose_descr);

        // Expense Information
        VoucherTaxiHelper.setSelect('edit_cpny_id_expense', voucher.cpny_id_expense);

        // Load topup employees filtered by department + company expense, then set the value
        if (voucher.department_id) {
            VoucherTaxiEditForm.loadTopupEmployeesAndSet(voucher.department_id, voucher.user_topup, voucher.cpny_id_expense);
        } else {
            VoucherTaxiHelper.setSelect('edit_user_topup', voucher.user_topup);
        }

        // Show revision reason if status is D
        if (voucher.status === 'D' && voucher.revise_reason) {
            const wrapper = document.getElementById('editReviseReasonWrapper');
            const reason = document.getElementById('edit_revise_reason');

            if (wrapper) wrapper.classList.remove('hidden');
            if (reason) reason.textContent = voucher.revise_reason;
        }

        // Initialize Select2 for dropdowns
        VoucherTaxiEditForm.initSelect2();
    },

    // --------------------------------------------------------
    // INIT SELECT2 FOR EDIT FORM DROPDOWNS
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const config = {
            dropdownParent: $('#editVoucherTaxiModal'),
            width: '100%',
            allowClear: false,
        };

        const init = ($el, extra = {}) => {
            if (!$el.length) return;
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({ ...config, ...extra });
        };

        // Basic selects (static options from blade)
        init($('#edit_cpny_id'), { placeholder: 'Select Company' });
        init($('#edit_department_id'), { placeholder: 'Select Department' });
        init($('#edit_cpny_id_expense'), { placeholder: 'Select Company' });

        // Purpose — AJAX search
        init($('#edit_purpose'), {
            placeholder: 'Search and select purpose...',
            allowClear: true,
            ajax: {
                url:      '/vouchertaxi/purpose-search',
                dataType: 'json',
                delay:    250,
                data:     (params) => ({ q: params.term }),
                processResults: (data) => ({ results: data.data ?? [] }),
            },
            minimumInputLength: 0,
        });

        // Topup — dynamically populated by department change
        init($('#edit_user_topup'), { placeholder: 'Select Employee' });

        // Department or Company Expense change → load employees for topup
        $('#edit_department_id, #edit_cpny_id_expense').off('change.editVoucher').on('change.editVoucher', function () {
            const deptId = $('#edit_department_id').val();
            const cpnyId = $('#edit_cpny_id_expense').val();
            VoucherTaxiEditForm.loadTopupEmployees(deptId, cpnyId);
        });
    },

    // --------------------------------------------------------
    // LOAD TOPUP EMPLOYEES BY DEPARTMENT
    // --------------------------------------------------------
    loadTopupEmployees(deptId, cpnyId = null) {
        const $select = $('#edit_user_topup');

        if (!deptId) {
            $select.html('<option value="">Select Employee</option>').val('').trigger('change');
            return;
        }

        let url = `/vouchertaxi/employee-by-department?department_id=${encodeURIComponent(deptId)}`;
        if (cpnyId) url += `&cpny_id=${encodeURIComponent(cpnyId)}`;

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $select.html('<option value="">Select Employee</option>');
                (data.data ?? []).forEach(emp => {
                    $select.append(`<option value="${emp.username}">${emp.name}</option>`);
                });
                $select.val('').trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    // --------------------------------------------------------
    // LOAD TOPUP EMPLOYEES AND SET A SPECIFIC VALUE
    // --------------------------------------------------------
    loadTopupEmployeesAndSet(deptId, valueToSet, cpnyId = null) {
        const $select = $('#edit_user_topup');

        if (!deptId) {
            $select.html('<option value="">Select Employee</option>').val(valueToSet || '').trigger('change');
            return;
        }

        let url = `/vouchertaxi/employee-by-department?department_id=${encodeURIComponent(deptId)}`;
        if (cpnyId) url += `&cpny_id=${encodeURIComponent(cpnyId)}`;

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN': VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $select.html('<option value="">Select Employee</option>');
                (data.data ?? []).forEach(emp => {
                    $select.append(`<option value="${emp.username}">${emp.name}</option>`);
                });
                if (valueToSet) $select.val(valueToSet).trigger('change');
                else $select.trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    // --------------------------------------------------------
    // GET FORM DATA AS OBJECT
    // --------------------------------------------------------
    getFormData() {
        const form = document.getElementById('editVoucherTaxiForm');
        if (!form) return {};

        const cpny_id = VoucherTaxiHelper.getValue('edit_cpny_id');
        const department_id = VoucherTaxiHelper.getValue('edit_department_id');
        const user_peminta = VoucherTaxiHelper.getValue('edit_user_peminta');
        const date_used = VoucherTaxiHelper.getValue('edit_date_used');
        const type_trip = document.querySelector('input[name="type_trip"]:checked')?.value ?? '';
        const origin = VoucherTaxiHelper.getValue('edit_origin');
        const destination = VoucherTaxiHelper.getValue('edit_destination');
        const purpose_id = VoucherTaxiHelper.getValue('edit_purpose');
        const purpose_descr = VoucherTaxiHelper.getValue('edit_purpose_desc');
        const cpny_id_expense = VoucherTaxiHelper.getValue('edit_cpny_id_expense');
        const user_topup = VoucherTaxiHelper.getValue('edit_user_topup');

        return {
            cpny_id,
            department_id,
            user_peminta,
            date_used,
            type_trip,
            origin,
            destination,
            purpose_id,
            purpose_descr,
            cpny_id_expense,
            user_topup,
        };
    },

    // --------------------------------------------------------
    // VALIDATE FORM
    // --------------------------------------------------------
    validate() {
        const data = VoucherTaxiEditForm.getFormData();

        // Required fields
        if (VoucherTaxiHelper.isEmpty(data.cpny_id)) {
            VoucherTaxi.toast('warning', 'Company is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.department_id)) {
            VoucherTaxi.toast('warning', 'Department is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.user_peminta)) {
            VoucherTaxi.toast('warning', 'Requester is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.date_used)) {
            VoucherTaxi.toast('warning', 'Date used is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.type_trip)) {
            VoucherTaxi.toast('warning', 'Trip type is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.origin)) {
            VoucherTaxi.toast('warning', 'Origin is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.destination)) {
            VoucherTaxi.toast('warning', 'Destination is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.purpose_id)) {
            VoucherTaxi.toast('warning', 'Purpose is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.purpose_descr)) {
            VoucherTaxi.toast('warning', 'Purpose description is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.cpny_id_expense)) {
            VoucherTaxi.toast('warning', 'Company expense is required');
            return false;
        }

        if (VoucherTaxiHelper.isEmpty(data.user_topup)) {
            VoucherTaxi.toast('warning', 'User topup is required');
            return false;
        }

        // Validate origin !== destination
        if (data.origin === data.destination) {
            VoucherTaxi.toast('warning', 'Origin and destination cannot be the same');
            return false;
        }

        return true;
    },

    // --------------------------------------------------------
    // SUBMIT FORM
    // --------------------------------------------------------
    async submit() {
        if (!VoucherTaxiEditForm.validate()) {
            return;
        }

        // Use local state — VoucherTaxi.state may be cleared by closeView() before submit
        const docid = VoucherTaxiEditForm.state.currentDocid ?? VoucherTaxi.state.currentDocid;
        if (!docid) {
            VoucherTaxi.toast('error', 'Invalid voucher reference');
            return;
        }

        VoucherTaxiEditForm.state.isSubmitting = true;
        VoucherTaxiHelper.setButtonLoading('submitEditVoucherBtn', true);

        try {
            const data = VoucherTaxiEditForm.getFormData();

            const payload = {
                cpny_id: data.cpny_id,
                department_id: data.department_id,
                user_peminta: data.user_peminta,
                date_used: data.date_used,
                type_trip: data.type_trip,
                purpose_id: data.purpose_id,
                purpose_descr: data.purpose_descr,
                origin: data.origin,
                destination: data.destination,
                cpny_id_expense: data.cpny_id_expense,
                user_topup: data.user_topup,
            };

            const response = await VoucherTaxi.request(
                VoucherTaxi.routes.update(docid),
                {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload),
                }
            );

            if (response.success) {
                VoucherTaxi.toast('success', response.message ?? 'Voucher updated successfully');

                // Prevent closeEdit from auto-closing
                VoucherTaxiEditForm.state.fromDetail = false;
                VoucherTaxiModal.closeEdit();

                // Reopen detail with refreshed data
                const eid = VoucherTaxi.state.currentEid;
                if (eid) {
                    setTimeout(() => {
                        VoucherTaxiModal.openView(eid);
                        VoucherTaxiDetailModal.loadDetail(eid);
                    }, 300);
                }

                // Refresh list
                VoucherTaxiDatalist.refresh();

            } else {
                VoucherTaxi.toast('error', response.message ?? 'Failed to update voucher');
            }

        } catch (err) {
            console.error('Submit error:', err);

            if (err.status === 422 && err.data?.errors) {
                const errors = Object.values(err.data.errors).flat();
                VoucherTaxi.toast('error', errors[0] ?? 'Validation error');
            } else if (err.data?.message) {
                VoucherTaxi.toast('error', err.data.message);
            } else {
                VoucherTaxi.toast('error', 'Failed to update voucher');
            }

        } finally {
            VoucherTaxiEditForm.state.isSubmitting = false;
            VoucherTaxiHelper.setButtonLoading('submitEditVoucherBtn', false);
        }
    },

    // --------------------------------------------------------
    // RESET TO ORIGINAL DATA
    // --------------------------------------------------------
    resetToOriginal() {
        if (!VoucherTaxiEditForm.state.originalData) {
            VoucherTaxi.toast('warning', 'No original data available');
            return;
        }

        const original = VoucherTaxiEditForm.state.originalData;

        // Restore all fields
        VoucherTaxiHelper.setSelect('edit_cpny_id', original.cpny_id);
        VoucherTaxiHelper.setSelect('edit_department_id', original.department_id);
        VoucherTaxiHelper.setValue('edit_user_peminta', original.user_peminta);
        VoucherTaxiHelper.setValue('edit_date_used', original.date_used);

        // Trip type radio
        const tripTypeRadio = document.querySelector(`input[name="type_trip"][value="${original.type_trip}"]`);
        if (tripTypeRadio) tripTypeRadio.checked = true;

        VoucherTaxiHelper.setValue('edit_origin', original.origin);
        VoucherTaxiHelper.setValue('edit_destination', original.destination);
        VoucherTaxiHelper.setSelect('edit_purpose', original.purpose_id);
        VoucherTaxiHelper.setValue('edit_purpose_desc', original.purpose_descr);
        VoucherTaxiHelper.setSelect('edit_cpny_id_expense', original.cpny_id_expense);
        VoucherTaxiHelper.setSelect('edit_user_topup', original.user_topup);

        VoucherTaxi.toast('info', 'Form reset to original values');
    },

    // --------------------------------------------------------
    // CLOSE FORM & CLEANUP
    // --------------------------------------------------------
    closeForm() {
        VoucherTaxiEditForm.cleanup();
        VoucherTaxiModal.closeEdit();

        // If opened from detail modal, reopen it
        if (VoucherTaxiEditForm.state.fromDetail) {
            const eid = VoucherTaxi.state.currentEid;
            if (eid) {
                setTimeout(() => {
                    VoucherTaxiModal.openView(eid);
                }, 300);
            }
        }
    },

    // --------------------------------------------------------
    // CLEANUP
    // --------------------------------------------------------
    cleanup() {
        VoucherTaxiEditForm.state.currentEid = null;
        VoucherTaxiEditForm.state.currentDocid = null;
        VoucherTaxiEditForm.state.originalData = null;
        VoucherTaxiEditForm.state.isSubmitting = false;
        VoucherTaxiEditForm.state.fromDetail = false;

        // Reset form
        VoucherTaxiHelper.resetForm('editVoucherTaxiForm');

        // Hide revision reason
        const wrapper = document.getElementById('editReviseReasonWrapper');
        if (wrapper) wrapper.classList.add('hidden');
    },
};
