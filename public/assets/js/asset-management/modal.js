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
    });
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
