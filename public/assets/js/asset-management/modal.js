function amOpenModal() {
    const el = document.getElementById('assignModal');
    el.classList.remove('hidden');
    el.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    requestAnimationFrame(() => {
        $(el).find('.modal-backdrop')
            .removeClass('opacity-0')
            .addClass('opacity-100');

        $(el).find('.modal-panel')
            .removeClass('opacity-0 translate-y-4 scale-[0.98]')
            .addClass('opacity-100 translate-y-0 scale-100');
    });
}

function amCloseModal() {
    const el = document.getElementById('assignModal');

    $(el).find('.modal-backdrop')
        .removeClass('opacity-100')
        .addClass('opacity-0');

    $(el).find('.modal-panel')
        .removeClass('opacity-100 translate-y-0 scale-100')
        .addClass('opacity-0 translate-y-4 scale-[0.98]');

    setTimeout(() => {
        el.classList.remove('flex');
        el.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        amResetForm();
    }, 180);
}

function initModalHandlers() {

    // Close button
    $('#closeModalBtn, #cancelModalBtn').on('click', function () {
        const isDirty = amIsFormDirty();

        if (!isDirty) {
            amCloseModal();
            return;
        }

        amConfirm({
            title: 'Close Form?',
            text: 'Unsaved changes will be lost.',
            confirmText: 'Yes, Close',
        }).then(result => {
            if (result.isConfirmed) {
                amCloseModal();
            }
        });
    });

    // Backdrop click
    $(document).on('click', '#assignModal .modal-backdrop', function () {
        $('#closeModalBtn').trigger('click');
    });

    // Escape key
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && !$('#assignModal').hasClass('hidden')) {
            $('#closeModalBtn').trigger('click');
        }
        if (e.key === 'Escape' && !$('#detailModal').hasClass('hidden')) {
            amCloseDetail();
        }
    });

    // Detail modal close buttons + backdrop
    $('#closeDetailBtn, #closeDetailBtnFooter').on('click', amCloseDetail);
    $(document).on('click', '#detailModal .modal-backdrop', amCloseDetail);
}

function amOpenDetail(id) {
    const url = window.amRoutes.show.replace('__ID__', id);

    $.get(url, function (data) {
        $('#detail_doc_id').text(data.assign_id || '—');
        $('#detail_inventoryid').text(data.inventoryid || '—');
        $('#detail_inventory_descr').text(data.inventory_descr || '—');
        $('#detail_receiptnbr').text(data.receiptnbr || '—');
        $('#detail_ponbr').text(data.ponbr || '—');
        $('#detail_cpny').text(data.assign_cpny_id || '—');
        $('#detail_dept').text(data.assign_department_id || '—');
        $('#detail_user').text(data.assign_username || '—');
        $('#detail_start_date').text(data.start_date ? amFormatDate(data.start_date) : '—');
        $('#detail_end_date').text(data.end_date ? amFormatDate(data.end_date) : '—');
        $('#detail_warranty_status').html(data.has_expired
            ? '<span class="font-medium text-red-600 dark:text-red-400">Expired</span>'
            : '<span class="font-medium text-emerald-600 dark:text-emerald-400">Active</span>');
        $('#detail_serial_number').text(data.serial_number || '—');
        $('#detail_notes').text(data.notes || '—');

        const el = document.getElementById('detailModal');
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            $(el).find('.modal-backdrop').removeClass('opacity-0').addClass('opacity-100');
            $(el).find('.modal-panel').removeClass('opacity-0 translate-y-4 scale-[0.98]').addClass('opacity-100 translate-y-0 scale-100');
        });
    }).fail(function () {
        amSwalError('Failed to load asset details.');
    });
}

function amCloseDetail() {
    const el = document.getElementById('detailModal');

    $(el).find('.modal-backdrop').removeClass('opacity-100').addClass('opacity-0');
    $(el).find('.modal-panel').removeClass('opacity-100 translate-y-0 scale-100').addClass('opacity-0 translate-y-4 scale-[0.98]');

    setTimeout(() => {
        el.classList.remove('flex');
        el.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }, 180);
}

function amIsFormDirty() {
    return (
        $('#f_assign_cpny').val() ||
        $('#f_assign_dept').val() ||
        $('#f_assign_user').val() ||
        $('#f_start_date').val()  ||
        $('#f_serial_number').val()
    );
}
