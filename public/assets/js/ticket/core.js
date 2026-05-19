// assets/js/ticket/core.js

window.Ticket = window.Ticket || {};

Ticket.csrf =
    $('meta[name="csrf-token"]').attr('content');

Ticket.baseUrl =
    window.location.origin;

Ticket.routes =
    window.ticketRoutes || {};

Ticket.modal = {

    create:
        '#createTicketModal',

};

Ticket.selectors = {

    createForm:
        '#createTicketForm',

    attachmentInput:
        '#ticket_attachments',

    attachmentList:
        '#create_attachment_list',

    submitButton:
        '#btn_submit_ticket',

};

Ticket.upload = {

    maxFileSizeKB:
        5120,

    maxFileSize:
        5120 * 1024,

    allowedExtensions: [
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'xls',
        'xlsx',
        'doc',
        'docx',
    ],

};

Ticket.state = {

    createAttachments: [],

    existingAttachments: [],

    deletedAttachments: [],

    isEditLoading: false,

};

$.ajaxSetup({

    headers: {

        'X-CSRF-TOKEN':
            Ticket.csrf,

        'Accept':
            'application/json',

    },

});

$(document).on('click', '#btn_export_ticket', function() {

    const params = new URLSearchParams({

        search: $('#search_ticket').val() || '',

        cpny_id: $('#filter_company').val() || '',

        department_id: $('#filter_department').val() || '',

        ticket_type: $('#filter_ticket_type').val() || '',

        date_from: $('#filter_date_from').val() || '',

        date_to: $('#filter_date_to').val() || ''

    });

    window.open(
        `/ticket/export?${params.toString()}`,
        '_blank'
    );

});
