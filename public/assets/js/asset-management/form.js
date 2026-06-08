// ── Open/fill helpers ─────────────────────────────────────────────────────────

function amFillReceiptInfo(d) {
    $('#info_sttb').val(d.receiptnbr       || '');
    $('#info_po').val(d.ponbr              || '');
    $('#info_vendorid').val(d.vendorid     || '');
    $('#info_vendorname').val(d.vendorname || '');
    $('#info_inventoryid').val(d.inventoryid      || '');
    $('#info_invdescr').val(d.inventory_descr     || '');
    $('#f_compound_id').val(d.compound_id         || '');
    $('#f_receipt_detail_id').val(d.receipt_detail_id || '');
    $('#f_unit_num').val(d.unit_num               || '');
    $('#f_receiptnbr_h').val(d.receiptnbr         || '');
    $('#f_budget_cpny_id_h').val(d.budget_cpny_id || '');
    $('#f_ponbr_h').val(d.ponbr                   || '');
    $('#f_vendorid_h').val(d.vendorid             || '');
    $('#f_vendorname_h').val(d.vendorname         || '');
    $('#f_inventoryid_h').val(d.inventoryid       || '');
    $('#f_inventory_descr_h').val(d.inventory_descr || '');
}

function amResetForm() {
    document.getElementById('assignForm').reset();
    $('#f_asset_id').val('');
    $('#f_compound_id').val('');
    $('#f_unit_num').val('');
    // Reset dept/user selects with fresh placeholder, notify Select2
    $('#f_assign_dept').html('<option value="">— Choose —</option>').trigger('change');
    $('#f_assign_user').html('<option value="">— Choose —</option>').trigger('change');
    // Notify Select2 that company was reset by form.reset()
    $('#f_assign_cpny').trigger('change');
    $('#endDateWrapper').addClass('hidden');
}

function amOpenAssign(d) {
    amResetForm();
    $('#modalTitle').text('Assign Asset');
    $('#submitBtn').html('<i class="fa-solid fa-paper-plane text-xs"></i> Save Assignment');
    amFillReceiptInfo(d);
    amLoadCompanies(null);
    amOpenModal();
}

function amOpenEdit(id) {
    amResetForm();
    $('#modalTitle').text('Edit Assignment');
    $('#submitBtn').html('<i class="fa-solid fa-floppy-disk text-xs"></i> Update Assignment');

    $.get(window.amRoutes.show.replace('__ID__', id), function (d) {
        amFillReceiptInfo(d);
        $('#f_asset_id').val(d.id);
        $('#f_start_date').val(d.start_date);
        $('#f_has_expired').prop('checked', !!d.has_expired);
        $('#endDateWrapper').toggleClass('hidden', !d.has_expired);
        $('#f_end_date').val(d.end_date || '');
        $('#f_serial_number').val(d.serial_number || '');
        $('#f_notes').val(d.notes || '');

        amLoadCompanies(d.assign_cpny_id, function () {
            // Use 'change' (not namespaced) so Select2 display also updates
            $('#f_assign_cpny').val(d.assign_cpny_id).trigger('change', [d.assign_department_id, d.assign_username]);
        });

        amOpenModal();
    }).fail(function () {
        amSwalError('Failed to load assignment data.');
    });
}

// ── Dropdown cascade ──────────────────────────────────────────────────────────

function amLoadCompanies(selected, callback) {
    $.get(window.amRoutes.companies, function (data) {
        let opts = '<option value="">— Choose —</option>';
        data.forEach(c => {
            opts += `<option value="${c.cpny_id}">${c.cpny_id} — ${c.cpny_name}</option>`;
        });
        $('#f_assign_cpny').html(opts);
        // Notify Select2 of new options
        $('#f_assign_cpny').trigger('change');
        if (selected) $('#f_assign_cpny').val(selected);
        if (callback) callback();
    }).fail(function () {
        amSwalError('Failed to load companies.');
    });
}

$('#f_assign_cpny').on('change', function (e, deptId, userId) {
    const cpny = $(this).val();
    $('#f_assign_dept').html('<option value="">— Choose —</option>').trigger('change');
    $('#f_assign_user').html('<option value="">— Choose —</option>').trigger('change');
    if (!cpny) return;

    $.get(window.amRoutes.departments, { cpny_id: cpny }, function (data) {
        let opts = '<option value="">— Choose —</option>';
        data.forEach(d => {
            opts += `<option value="${d.department_id}">${d.department_id} — ${d.department_name}</option>`;
        });
        $('#f_assign_dept').html(opts).trigger('change');
        if (deptId) {
            $('#f_assign_dept').val(deptId).trigger('change', [userId]);
        }
    });
});

$('#f_assign_dept').on('change', function (e, userId) {
    const cpny = $('#f_assign_cpny').val();
    const dept = $(this).val();
    $('#f_assign_user').html('<option value="">— Choose —</option>').trigger('change');
    if (!dept) return;

    $.get(window.amRoutes.users, { cpny_id: cpny, department_id: dept }, function (data) {
        let opts = '<option value="">— Choose —</option>';
        data.forEach(u => {
            opts += `<option value="${u.username}">${u.name}${u.npk ? ' (' + u.npk + ')' : ''}</option>`;
        });
        $('#f_assign_user').html(opts).trigger('change');
        if (userId) {
            $('#f_assign_user').val(userId).trigger('change');
        }
    });
});

// ── Has Expired toggle ────────────────────────────────────────────────────────

$('#f_has_expired').on('change', function () {
    $('#endDateWrapper').toggleClass('hidden', !this.checked);
    if (!this.checked) $('#f_end_date').val('');
});

// ── Form submit ───────────────────────────────────────────────────────────────

function initFormHandlers() {

    // Initialize Select2 on modal dropdowns
    if ($.fn.select2) {
        const s2opts = {
            placeholder:    '— Choose —',
            width:          '100%',
            dropdownParent: $('#assignModal'),
        };
        $('#f_assign_cpny').select2(s2opts);
        $('#f_assign_dept').select2(s2opts);
        $('#f_assign_user').select2(s2opts);
    }

    $('#assignForm').on('submit', async function (e) {
        e.preventDefault();

        const assetId = $('#f_asset_id').val();
        const url     = assetId
            ? window.amRoutes.update.replace('__ID__', assetId)
            : window.amRoutes.store;
        const method  = assetId ? 'PUT' : 'POST';

        if (!$('#f_assign_cpny').val())  { return amSwalWarning('Please select a Company.');    }
        if (!$('#f_assign_dept').val())  { return amSwalWarning('Please select a Department.'); }
        if (!$('#f_assign_user').val())  { return amSwalWarning('Please select a Username.');   }
        if (!$('#f_start_date').val())   { return amSwalWarning('Please fill in Start Date.');  }
        if ($('#f_has_expired').is(':checked') && !$('#f_end_date').val()) {
            return amSwalWarning('Please fill in End Date when warranty has expired.');
        }

        $('#submitBtn').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin text-xs"></i> Saving…');

        try {
            const res = await $.ajax({
                url,
                type: method,
                data: $(this).serialize(),
            });

            amToast('success', res.message ?? 'Saved successfully');
            amCloseModal();
            table.ajax.reload(null, false);

        } catch (xhr) {
            const errors = xhr.responseJSON?.errors;
            const msg    = errors
                ? Object.values(errors).flat().join('\n')
                : (xhr.responseJSON?.message ?? 'Something went wrong.');
            amSwalError(msg);

        } finally {
            const isEdit = !!$('#f_asset_id').val();
            $('#submitBtn').prop('disabled', false).html(
                isEdit
                    ? '<i class="fa-solid fa-floppy-disk text-xs"></i> Update Assignment'
                    : '<i class="fa-solid fa-paper-plane text-xs"></i> Save Assignment'
            );
        }
    });
}
