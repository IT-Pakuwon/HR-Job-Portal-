window.Ticket = window.Ticket || {};

function initIssueDescrEditor() {
    if (window.issueDescrQuill) return;

    window.issueDescrQuill = new Quill('#issue_descr_editor', {
        theme: 'snow',
        placeholder: 'Explain your issue detail...',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
                ['clean'],
            ],
        },
    });
}

function initTicketRequestForm() {

    bindOpenCreateTicketModal();

    bindSubmitCreateTicket();

}

function bindOpenCreateTicketModal() {

    $(document).on(
        'click',
        '#btn_create_ticket',
        function () {

            initIssueDescrEditor();

            resetCreateTicketForm();

            openModal(
                Ticket.modal.create
            );

        }
    );

}

function resetCreateTicketForm() {

    const form =
        $(Ticket.selectors.createForm);

    if (form.length) {

        form[0].reset();

    }

    clearValidationErrors(
        Ticket.selectors.createForm
    );

    Ticket.state.createAttachments = [];

    renderTicketAttachment();

    $('#ticket_type').val(null).trigger('change');
    $('#ticket_categoryid').val(null).trigger('change');
    $('#ticket_subcategoryid').val(null).trigger('change');
    $('#location_id').val(null).trigger('change');
    $('#sub_location_id').val(null).trigger('change');

    const firstCompany =
        window.ticketCompanies?.[0];

    if (firstCompany) {

        $('#cpny_id')
            .val(
                String(
                    firstCompany.cpny_id
                ).trim()
            )
            .trigger('change.select2');

    }

    const firstDepartment =
        window.ticketDepartments?.[0];

    if (firstDepartment) {

        $('#department_id')
            .val(
                String(
                    firstDepartment.department_id
                ).trim()
            )
            .trigger('change.select2');

    }

    $('#issue_summary').val('');
    $('#issue_descr').val('');

    if (window.issueDescrQuill) {
        window.issueDescrQuill.setText('');
    }

}

function bindSubmitCreateTicket() {

    $(document).on(
        'submit',
        Ticket.selectors.createForm,
        function (e) {

            const eid =
                $('#ticket_eid').val();

            if (eid) {
                return;
            }

            e.preventDefault();

            submitCreateTicket();

        }
    );

}

function submitCreateTicket() {

    clearValidationErrors(
        Ticket.selectors.createForm
    );

    if (window.issueDescrQuill) {
        $('#issue_descr').val(
            window.issueDescrQuill.root.innerHTML
        );
    }

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

    setButtonLoading(
        Ticket.selectors.submitButton,
        true,
        'Submitting...'
    );

    // console.log(
    //     'department_id:',
    //     $('#department_id').val()
    // );

    $.ajax({

        url:
            Ticket.routes.store,

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
                'Ticket created successfully'
            );

            closeModal(
                Ticket.modal.create
            );

            resetCreateTicketForm();

            if (
                $.fn.DataTable &&
                $('#ticketTable').length
            ) {

                $('#ticketTable')
                    .DataTable()
                    .ajax
                    .reload(
                        null,
                        false
                    );

            }

        },

        error: function(xhr){

            // console.log(xhr);

            // console.log(xhr.responseJSON);

            // console.log(xhr.responseText);

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
