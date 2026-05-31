// ============================================================
// edit-form.js — Voucher Taxi
// Edit voucher form (status D / Revise only)
// ============================================================

const VoucherTaxiEditForm = {

    // --------------------------------------------------------
    // STATE
    // --------------------------------------------------------
    state: {
        isSubmitting: false,
        originalData: null,
        currentEid:   null,
    },

    formId: '#editVoucherTaxiForm',

    // --------------------------------------------------------
    // INIT
    // --------------------------------------------------------
    init() {
        VoucherTaxiEditForm.initPurposeSelect();
        VoucherTaxiEditForm.initTopupSelect();
        VoucherTaxiEditForm.bindSubmit();
        VoucherTaxiEditForm.bindDepartmentChange();
    },

    // --------------------------------------------------------
    // PURPOSE SELECT2
    // --------------------------------------------------------
    initPurposeSelect() {
        $('#edit_purpose').select2({
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
    // TOPUP SELECT2
    // --------------------------------------------------------
    initTopupSelect() {
        $('#edit_user_topup').select2({ placeholder: 'Select employee...', allowClear: true });
    },

    // --------------------------------------------------------
    // DEPARTMENT CHANGE → reload employees
    // --------------------------------------------------------
    bindDepartmentChange() {
        $(document).on('change', '#edit_department_id', function() {
            VoucherTaxiEditForm.loadEmployees($(this).val());
        });
    },

    // Load employees for dept and then set a specific value (used during populate)
    loadEmployeesAndSet(deptId, valueToSet) {
        fetch(`${VoucherTaxi.routes.employeeByDept}?department_id=${encodeURIComponent(deptId)}`, {
            headers: { 'X-CSRF-TOKEN': VoucherTaxi.csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const $select = $('#edit_user_topup');
                $select.html('<option value="">Select User</option>');
                (data.data ?? []).forEach(e => $select.append(`<option value="${e.username}">${e.name}</option>`));
                if (valueToSet) $select.val(valueToSet).trigger('change');
                else $select.trigger('change');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load employees'));
    },

    loadEmployees(deptId) {
        const $select = $('#edit_user_topup');

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
    // LOAD VOUCHER DATA INTO FORM
    // --------------------------------------------------------
    loadVoucher(eid) {
        VoucherTaxiEditForm.state.currentEid = eid;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.find(eid), {
            headers: { 'X-CSRF-TOKEN': VoucherTaxi.csrf(), 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            VoucherTaxiEditForm.state.originalData = VoucherTaxiHelper.deepClone(data);
            VoucherTaxiEditForm.populate(data);
            VoucherTaxiEditForm.showRevisionReason(data);
        })
        .catch(() => VoucherTaxi.toast('error', 'Failed to load voucher details'))
        .finally(() => VoucherTaxi.hideLoading());
    },

    // --------------------------------------------------------
    // POPULATE FORM
    // --------------------------------------------------------
    populate(data) {
        const f = $(VoucherTaxiEditForm.formId);

        f.find('#edit_docid').val(data.docid ?? '');
        f.find('#edit_eid').val(data.eid ?? VoucherTaxiEditForm.state.currentEid ?? '');
        f.find('[name="cpny_id"]').val(data.cpny_id ?? '').trigger('change');
        f.find('[name="department_id"]').val(data.department_id ?? '').trigger('change');
        f.find('#edit_requester_name').val(data.user_name ?? data.user_peminta ?? '');
        f.find('#edit_user_peminta').val(data.user_peminta ?? '');
        f.find('[name="date_used"]').val(data.date_used ?? '');
        f.find(`[name="type_trip"][value="${data.type_trip ?? 'Return'}"]`).prop('checked', true);

        // Purpose select2 — set pre-existing value
        const purposeOpt = new Option(data.purpose_id ?? '', data.purpose_id ?? '', true, true);
        $('#edit_purpose').append(purposeOpt).trigger('change');

        f.find('[name="purpose_descr"]').val(data.purpose_descr ?? '');
        f.find('[name="cpny_id_expense"]').val(data.cpny_id_expense ?? '').trigger('change');

        // Load dept employees first, then set topup value
        if (data.department_id) {
            VoucherTaxiEditForm.loadEmployeesAndSet(data.department_id, data.user_topup);
        } else {
            f.find('[name="user_topup"]').val(data.user_topup ?? '').trigger('change');
        }
        f.find('[name="origin"]').val(data.origin ?? '');
        f.find('[name="destination"]').val(data.destination ?? '');
    },

    // --------------------------------------------------------
    // REVISION REASON BANNER
    // --------------------------------------------------------
    showRevisionReason(data) {
        const reason   = data.revise_reason ?? VoucherTaxiEditForm.state.originalData?.revise_reason;
        const $wrapper = $('#editReviseReasonWrapper');

        if (reason) {
            $wrapper.find('#edit_revise_reason').text(reason);
            $wrapper.removeClass('hidden');
        } else {
            $wrapper.addClass('hidden');
        }
    },

    // --------------------------------------------------------
    // FORM SUBMIT
    // --------------------------------------------------------
    bindSubmit() {
        $(document).on('submit', VoucherTaxiEditForm.formId, function(e) {
            e.preventDefault();
            VoucherTaxiEditForm.submit();
        });
    },

    // --------------------------------------------------------
    // VALIDATE
    // --------------------------------------------------------
    validate(data) {
        const errors = {};
        if (!data.cpny_id)       errors.cpny_id       = ['Company is required'];
        if (!data.department_id) errors.department_id = ['Department is required'];
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
        const f = $(VoucherTaxiEditForm.formId);
        return {
            cpny_id:         f.find('[name="cpny_id"]').val(),
            department_id:   f.find('[name="department_id"]').val(),
            user_peminta:    f.find('#edit_user_peminta').val(),
            date_used:       f.find('[name="date_used"]').val(),
            type_trip:       f.find('[name="type_trip"]:checked').val(),
            purpose_id:      f.find('[name="purpose_id"]').val(),
            purpose_descr:   f.find('[name="purpose_descr"]').val(),
            user_topup:      f.find('[name="user_topup"]').val(),
            origin:          f.find('[name="origin"]').val(),
            destination:     f.find('[name="destination"]').val(),
            cpny_id_expense: f.find('[name="cpny_id_expense"]').val(),
        };
    },

    // --------------------------------------------------------
    // SUBMIT
    // --------------------------------------------------------
    submit() {
        if (VoucherTaxiEditForm.state.isSubmitting) return;

        const data   = VoucherTaxiEditForm.getData();
        const errors = VoucherTaxiEditForm.validate(data);

        if (Object.keys(errors).length) {
            VoucherTaxi.toast('error', Object.values(errors).flat()[0]);
            return;
        }

        const docid = $(VoucherTaxiEditForm.formId).find('#edit_docid').val();
        if (!docid) { VoucherTaxi.toast('error', 'Document ID missing'); return; }

        VoucherTaxiEditForm.state.isSubmitting = true;
        VoucherTaxi.showLoading();

        fetch(VoucherTaxi.routes.update(docid), {
            method:  'PUT',
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
                VoucherTaxi.toast('success', res.message ?? 'Voucher updated');
                VoucherTaxiEditForm.reset();
                VoucherTaxiModal.closeEdit();
                setTimeout(() => {
                    VoucherTaxiDetailModal.refresh();
                    VoucherTaxiDataList.reload();
                }, 800);
            } else {
                VoucherTaxi.toast('error', res.message ?? 'Failed to update voucher');
            }
        })
        .catch(() => VoucherTaxi.toast('error', 'An unexpected error occurred'))
        .finally(() => {
            VoucherTaxi.hideLoading();
            VoucherTaxiEditForm.state.isSubmitting = false;
        });
    },

    // --------------------------------------------------------
    // RESET
    // --------------------------------------------------------
    reset() {
        VoucherTaxiEditForm.state.originalData = null;
        VoucherTaxiEditForm.state.currentEid   = null;
        $('#edit_purpose').val(null).trigger('change');
        $('#edit_user_topup').val(null).trigger('change');
        $('#editReviseReasonWrapper').addClass('hidden');
    },
};
