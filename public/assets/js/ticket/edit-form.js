window.Ticket = window.Ticket || {};

function initEditTicketForm() {

    bindOpenEditTicket();

    bindSubmitEditTicket();

}

function bindOpenEditTicket() {

    $(document).on(
        'click',
        '.btn-edit-ticket',
        function () {

            const eid =
                $(this).data('id');

            openEditTicketModal(
                eid
            );

        }
    );

}

function openEditTicketModal(eid) {

    resetCreateTicketForm();

    $('#ticket_eid')
        .val(eid);

    $('.modal-title')
        .text('Edit Ticket');

    $('.modal-description')
        .text(
            'Update existing ticket request.'
        );

    $('#btn_submit_ticket')
        .html(`
            <i class="fa-solid fa-floppy-disk text-xs"></i>
            Update Ticket
        `);

    loadEditTicket(eid);

    openModal(
        Ticket.modal.create
    );

}

function loadEditTicket(eid) {

    $.ajax({

        url:
            `/ticket/detail/${eid}`,

        type:
            'GET',

        success: function (response) {

            Ticket.state.isEditLoading = true;

            const ticket =
                response.data.ticket;

            $('#cpny_id')
                .val(ticket.cpny_id)
                .trigger('change');

            $('#department_id')
                .val(ticket.department_id)
                .trigger('change');

            $('#ticket_type')
                .val(ticket.ticket_type)
                .trigger('change');

            setTimeout(function () {

                if (ticket.ticket_categoryid) {

                    const categoryOption =
                        new Option(
                            ticket.ticket_category_name,
                            ticket.ticket_categoryid,
                            true,
                            true
                        );

                    $('#ticket_categoryid')
                        .append(categoryOption)
                        .trigger('change');

                }

                setTimeout(function () {

                    if (ticket.ticket_subcategoryid) {

                        const subCategoryOption =
                            new Option(
                                ticket.ticket_subcategory_name,
                                ticket.ticket_subcategoryid,
                                true,
                                true
                            );

                        $('#ticket_subcategoryid')
                            .append(subCategoryOption)
                            .trigger('change');

                    }

                }, 300);

            }, 300);

            $('#location_id')
                .val(ticket.location_id)
                .trigger('change');

            setTimeout(function () {

                if (ticket.sub_location_id) {

                    const subLocationOption =
                        new Option(
                            ticket.sub_location_name,
                            ticket.sub_location_id,
                            true,
                            true
                        );

                    $('#sub_location_id')
                        .append(subLocationOption)
                        .trigger('change');

                }

            }, 300);

            $('#issue_summary')
                .val(ticket.issue_summary);

            $('#issue_descr')
                .val(ticket.issue_descr);

            Ticket.state.existingAttachments =
                response.data.attachments || [];

            Ticket.state.deletedAttachments = [];

            setTimeout(() => {

                Ticket.state.isEditLoading = false;

            }, 800);

            renderTicketAttachment();

        },

        error: function (xhr) {

            showError(
                xhr.responseJSON?.message ||
                'Failed load ticket detail'
            );

        },

    });

}

function bindSubmitEditTicket() {

    $(document).on(
        'submit',
        Ticket.selectors.createForm,
        function (e) {

            const eid =
                $('#ticket_eid').val();

            if (!eid) {
                return;
            }

            e.preventDefault();

            submitUpdateTicket();

        }
    );

}

function submitUpdateTicket() {

    clearValidationErrors(
        Ticket.selectors.createForm
    );

    const eid =
        $('#ticket_eid').val();

    const form =
        $(Ticket.selectors.createForm)[0];

    const formData =
        new FormData(form);

    Ticket.state.createAttachments.forEach(
        function (file) {

            formData.append(
                'attachments[]',
                file
            );

        }
    );

    Ticket.state.deletedAttachments.forEach(
        function (id) {

            formData.append(
                'deleted_attachments[]',
                id
            );

        }
    );

    setButtonLoading(
        Ticket.selectors.submitButton,
        true,
        'Updating...'
    );

    $.ajax({

        url:
            `/ticket/update/${eid}`,

        type:
            'POST',

        data:
            formData,

        processData:
            false,

        contentType:
            false,

        success: function (response) {

            showSuccess(
                response.message ||
                'Ticket updated successfully'
            );

            closeModal(
                Ticket.modal.create
            );

            resetCreateTicketForm();

            $('#ticket_eid')
                .val('');

            $('.modal-title')
                .text('Create Ticket');

            $('.modal-description')
                .text(
                    'Create new IT support ticket request.'
                );

            $('#btn_submit_ticket')
                .html(`
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Submit Ticket
                `);

            $('#ticketTable')
                .DataTable()
                .ajax
                .reload(
                    null,
                    false
                );

        },

        error: function (xhr) {

            if (xhr.status === 422) {

                renderValidationErrors(
                    Ticket.selectors.createForm,
                    xhr.responseJSON.errors || {}
                );

                return;

            }

            showError(
                xhr.responseJSON?.message ||
                'Server Error'
            );

        },

        complete: function () {

            setButtonLoading(
                Ticket.selectors.submitButton,
                false
            );

        },

    });

}
