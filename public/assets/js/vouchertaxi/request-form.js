// ============================================================
// request-form.js — Voucher Taxi
// Create voucher form handling
// ============================================================

const VoucherTaxiRequestForm = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isSubmitting: false,
    },

    formId: '#voucherTaxiForm',

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VoucherTaxiRequestForm.initPurposeSelect();
        VoucherTaxiRequestForm.initTopupSelect();
        VoucherTaxiRequestForm.bindSubmit();
        VoucherTaxiRequestForm.bindDepartmentChange();
    },

    // --------------------------------------------------------
    // PURPOSE SELECT2 (AJAX search)
    // --------------------------------------------------------
    initPurposeSelect() {
        $('#purpose').select2({
            placeholder: 'Search and select purpose...',
            allowClear:  true,
            ajax: {
                url:      VoucherTaxi.routes.purposeSearch,
                dataType: 'json',
                delay:    250,
                data:     (params) => ({ q: params.term }),
                processResults: (data) => ({ results: data.data ?? [] }),
            },
            minimumInputLength: 0,
            templateResult:    (d) => d.id ? $(`<div>${d.text}</div>`) : d.text,
            templateSelection: (d) => d.text || 'Select purpose...',
        });
    },

    // --------------------------------------------------------
    // TOPUP EMPLOYEE SELECT2
    // --------------------------------------------------------
    initTopupSelect() {
        $('#user_topup').select2({ placeholder: 'Select employee...', allowClear: true });
    },

    // --------------------------------------------------------
    // DEPARTMENT CHANGE → reload employees
    // --------------------------------------------------------
    bindDepartmentChange() {
        $(document).on('change', '#department_id', function() {
            VoucherTaxiRequestForm.loadEmployees($(this).val(), '#user_topup');
        });
    },

    loadEmployees(deptId, selectId) {
        const $select = $(selectId);

        if (!deptId) {
            $select.html('<option value="">Select employee...</option>').val(null).trigger('change');
            return;
        }

        fetch(`${VoucherTaxi.routes.employeeByDept}?department_id=${encodeURIComponent(deptId)}`, {
            headers: { 'X-CSRF-TOKEN': VoucherTaxi.csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                $select.html('<option value="">Select employee...</option>');
                (data.data ?? []).forEach(e => $select.append(`<option value="${e.username}">${e.name}</option>`));
                $select.trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    // --------------------------------------------------------
    // FORM SUBMISSION
    // --------------------------------------------------------
    bindSubmit() {
        $(document).on('submit', VoucherTaxiRequestForm.formId, function(e) {
            e.preventDefault();
            VoucherTaxiRequestForm.submit();
        });
    },

    // --------------------------------------------------------
    // VALIDATE
    // --------------------------------------------------------
    validate(data) {
        const errors = {};
        if (!data.cpny_id)       errors.cpny_id       = ['Company is required'];
        if (!data.department_id) errors.department_id = ['Department is required'];
        if (!data.user_peminta)  errors.user_peminta  = ['Requester is required'];
        if (!data.date_used)     errors.date_used     = ['Date used is required'];
        if (!data.type_trip)     errors.type_trip     = ['Trip type is required'];
        if (!data.purpose_id)    errors.purpose_id    = ['Purpose is required'];
        if (!data.purpose_descr) errors.purpose_descr = ['Purpose description is required'];
        if (!data.user_topup)    errors.user_topup    = ['Top-up employee is required'];
        if (!data.origin)        errors.origin        = ['Origin is required'];
        if (!data.destination)   errors.destination   = ['Destination is required'];

        if (data.date_used && !VoucherTaxiHelper.isValidDate(data.date_used)) {
            errors.date_used = ['Date used must be a valid date'];
        }
        return errors;
    },

    // --------------------------------------------------------
    // GET FORM DATA
    // --------------------------------------------------------
    getData() {
        const f = $(VoucherTaxiRequestForm.formId);
        return {
            cpny_id:       f.find('[name="cpny_id"]').val(),
            department_id: f.find('[name="department_id"]').val(),
            user_peminta:  f.find('[name="user_peminta"]').val(),
            date_used:     f.find('[name="date_used"]').val(),
            type_trip:     f.find('[name="type_trip"]:checked').val(),
            purpose_id:    f.find('[name="purpose_id"]').val(),
            purpose_descr: f.find('[name="purpose_descr"]').val(),
            user_topup:    f.find('[name="user_topup"]').val(),
            origin:        f.find('[name="origin"]').val(),
            destination:   f.find('[name="destination"]').val(),
        };
    },

    // --------------------------------------------------------
    // SUBMIT
    // --------------------------------------------------------
    submit() {
        if (VoucherTaxiRequestForm.state.isSubmitting) return;

        const data   = VoucherTaxiRequestForm.getData();
        const errors = VoucherTaxiRequestForm.validate(data);

        if (Object.keys(errors).length) {
            VoucherTaxi.toast('error', Object.values(errors).flat()[0]);
            return;
        }

        VoucherTaxiRequestForm.state.isSubmitting = true;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.store, {
            method:  'POST',
            headers: {
                'X-CSRF-TOKEN':     VoucherTaxi.csrf(),
                'X-Requested-With': 'XMLHttpRequest',
                'Accept':           'application/json',
                'Content-Type':     'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                VoucherTaxi.toast('success', res.message ?? 'Voucher created');
                VoucherTaxiRequestForm.reset();
                VoucherTaxiModal.closeCreate();
                setTimeout(() => VoucherTaxiDataList.reload(), 800);
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Failed to create voucher');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiRequestForm.state.isSubmitting = false;
        });
    },

    // --------------------------------------------------------
    // RESET
    // --------------------------------------------------------
    reset() {
        const form = document.querySelector(VoucherTaxiRequestForm.formId);
        form?.reset();
        $('#purpose').val(null).trigger('change');
        $('#user_topup').val(null).trigger('change');
        // Reset radio to default
        const returnRadio = form?.querySelector('[name="type_trip"][value="Return"]');
        if (returnRadio) returnRadio.checked = true;
    },
};
