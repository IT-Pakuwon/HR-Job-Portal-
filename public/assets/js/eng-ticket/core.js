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
