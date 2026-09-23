// ============================================================
// request-form.js — Voucher Taxi
// Create voucher form handling — matches Booking Car pattern
// ============================================================

const VoucherTaxiForm = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isSubmitting: false,
    },

    // --------------------------------------------------------
    // INIT — wire events ONCE on page load
    // --------------------------------------------------------
    init() {
        // Initialize Select2 ONCE here (not on every modal open)
        VoucherTaxiForm.initSelect2();

        // Auto-load topup employees if department already set (single-dept user)
        const deptId = document.getElementById('department_id')?.value;
        const cpnyExpense = document.getElementById('cpny_id_expense')?.value;
        if (deptId) VoucherTaxiForm.loadTopupEmployees(deptId, cpnyExpense);

        // Department or Company Expense change → reload topup employees
        $(document).on('change.voucherCreate', '#department_id, #cpny_id_expense', function () {
            const dept = $('#department_id').val();
            const cpny = $('#cpny_id_expense').val();
            VoucherTaxiForm.loadTopupEmployees(dept, cpny);
        });

        // Form submit
        $(document).on('submit', '#voucherTaxiForm', function (e) {
            e.preventDefault();
            VoucherTaxiForm.submit();
        });
    },

    // --------------------------------------------------------
    // INIT SELECT2 — called once on page load
    // --------------------------------------------------------
    initSelect2() {
        if (typeof $.fn.select2 === 'undefined') return;

        const parent = $('#createVoucherModal');

        const make = ($el, extra = {}) => {
            if (!$el.length) return;
            if ($el.hasClass('select2-hidden-accessible')) $el.select2('destroy');
            $el.select2({ dropdownParent: parent, width: '100%', ...extra });
        };

        // Static selects (options rendered by blade)
        // Only init Select2 on <select> — blade renders a hidden input when user has 1 company
        if ($('#cpny_id').is('select')) make($('#cpny_id'), { placeholder: 'Select Company' });
        make($('#cpny_id_expense'), { placeholder: 'Select Company' });

        // Purpose — AJAX search (options NOT in blade, loaded on demand)
        make($('#purpose'), {
            placeholder: 'Search and select purpose...',
            allowClear:  true,
            ajax: {
                url:      '/vouchertaxi/purpose-search',
                dataType: 'json',
                delay:    250,
                data:     (params) => ({ q: params.term ?? '' }),
                processResults: (res) => ({ results: res.data ?? [] }),
            },
            minimumInputLength: 0,
        });

        // Topup — starts empty, populated by loadTopupEmployees()
        make($('#user_topup'), { placeholder: 'Select Employee' });
    },

    // --------------------------------------------------------
    // LOAD TOPUP EMPLOYEES BY DEPARTMENT
    // --------------------------------------------------------
    loadTopupEmployees(deptId, cpnyId = null) {
        const $select = $('#user_topup');

        if (!deptId) {
            $select.html('<option value="">Select Employee</option>').trigger('change');
            return;
        }

        let url = `/vouchertaxi/employee-by-department?department_id=${encodeURIComponent(deptId)}`;
        if (cpnyId) url += `&cpny_id=${encodeURIComponent(cpnyId)}`;

        fetch(url, {
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
            },
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                $select.html('<option value="">Select Employee</option>');
                (res.data ?? []).forEach(emp => {
                    $select.append(`<option value="${emp.username}">${emp.name}</option>`);
                });
                $select.trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    // --------------------------------------------------------
    // ON OPEN — called when the create modal opens
    // (modal opens via modal.js; this is hooked from calendar.js for date pre-fill)
    // --------------------------------------------------------
    onOpen(payload = {}) {
        document.getElementById('voucherTaxiForm')?.reset();

        // Clear Select2 selections
        $('#purpose').val(null).trigger('change');
        $('#user_topup').val(null).trigger('change');

        // Pre-fill date from calendar click
        if (payload.date_used) {
            VoucherTaxiHelper.setValue('date_used', payload.date_used);
        }

        // Re-load employees for existing department and company expense selection
        const deptId = document.getElementById('department_id')?.value;
        const cpnyExpense = document.getElementById('cpny_id_expense')?.value;
        if (deptId) VoucherTaxiForm.loadTopupEmployees(deptId, cpnyExpense);
    },

    // --------------------------------------------------------
    // VALIDATE
    // --------------------------------------------------------
    validate() {
        const f = (id) => VoucherTaxiHelper.getValue(id);
        const empty = (v) => VoucherTaxiHelper.isEmpty(v);

        if (empty(f('cpny_id')))          { VoucherTaxi.toast('warning', 'Company is required.');              return false; }
        if (empty(f('department_id')))    { VoucherTaxi.toast('warning', 'Department is required.');           return false; }
        if (empty(f('user_peminta')))     { VoucherTaxi.toast('warning', 'Requester is required.');            return false; }
        if (empty(f('date_used')))        { VoucherTaxi.toast('warning', 'Date used is required.');            return false; }

        const tripType = document.querySelector('input[name="type_trip"]:checked')?.value ?? '';
        if (empty(tripType))              { VoucherTaxi.toast('warning', 'Trip type is required.');            return false; }

        const origin      = f('origin');
        const destination = f('destination');
        if (empty(origin))                { VoucherTaxi.toast('warning', 'Origin is required.');               return false; }
        if (empty(destination))           { VoucherTaxi.toast('warning', 'Destination is required.');         return false; }
        if (origin.toLowerCase() === destination.toLowerCase()) {
            VoucherTaxi.toast('warning', 'Origin and destination cannot be the same.');
            return false;
        }

        if (empty(f('purpose')))          { VoucherTaxi.toast('warning', 'Purpose is required.');             return false; }
        if (empty(f('purpose_desc')))     { VoucherTaxi.toast('warning', 'Purpose description is required.'); return false; }
        if (empty(f('cpny_id_expense')))  { VoucherTaxi.toast('warning', 'Company expense is required.');     return false; }
        if (empty(f('user_topup')))       { VoucherTaxi.toast('warning', 'Topup employee is required.');      return false; }

        return true;
    },

    // --------------------------------------------------------
    // SUBMIT
    // --------------------------------------------------------
    async submit() {
        if (VoucherTaxiForm.state.isSubmitting) return;
        if (!VoucherTaxiForm.validate()) return;

        VoucherTaxiForm.state.isSubmitting = true;
        VoucherTaxiHelper.setButtonLoading('submitCreateVoucherBtn', true);

        try {
            const res = await VoucherTaxi.request(VoucherTaxi.routes.store, {
                method: 'POST',
                body:   VoucherTaxiHelper.getFormData('voucherTaxiForm'),
            });

            if (!res.success) {
                VoucherTaxi.toast('error', res.message ?? 'Failed to create voucher.');
                return;
            }

            VoucherTaxi.toast('success', res.message ?? 'Voucher created successfully.');
            VoucherTaxiModal.closeCreate();

            setTimeout(() => {
                if (typeof VoucherTaxiDatalist !== 'undefined') VoucherTaxiDatalist.refresh();
                if (typeof VoucherTaxiCalendar !== 'undefined') VoucherTaxiCalendar.refresh?.();
            }, 400);

        } catch (err) {
            let msg = 'Failed to create voucher.';
            if (err.status === 422 && err.data?.errors) {
                msg = Object.values(err.data.errors).flat()[0] ?? msg;
            } else if (err.data?.message) {
                msg = err.data.message;
            }
            VoucherTaxi.toast('error', msg);

        } finally {
            VoucherTaxiForm.state.isSubmitting = false;
            VoucherTaxiHelper.setButtonLoading('submitCreateVoucherBtn', false);
        }
    },
};
