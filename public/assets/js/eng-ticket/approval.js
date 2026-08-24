// assets/js/eng-ticket/approval.js

window.Ticket = window.Ticket || {};

function approveTicket(eid) {

    if (!eid) return;

    Swal.fire({

        icon: 'question',

        title: 'Approve Ticket?',

        text: 'This ticket will move to the next approval step.',

        showCancelButton: true,

        confirmButtonText: 'Yes, Approve',

        cancelButtonText: 'Back',

        reverseButtons: true,

        confirmButtonColor: '#16a34a',

    }).then((result) => {

        if (!result.isConfirmed) return;

        showLoading();

        $.ajax({

            url: window.ticketRoutes.approve.replace(':eid', eid),

            type: 'POST',

            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
            },

            success(res) {

                hideLoading();

                showSuccess(res.message || 'Ticket approved successfully.');

                if (typeof loadTicketDetail === 'function') {
                    const currentDetailEid = $('#comment_ticket_id').val();
                    if (currentDetailEid) loadTicketDetail(currentDetailEid);
                }

                if ($.fn.DataTable && $('#ticketTable').length) {
                    $('#ticketTable').DataTable().ajax.reload(null, false);
                }

                refreshTicketCalendar();

                if (typeof EngTicketApprovalPanel !== 'undefined') {
                    EngTicketApprovalPanel.refresh();
                }

            },

            error(xhr) {

                hideLoading();

                handleAjaxError(xhr);

            },

        });

    });

}

function rejectTicket(eid) {

    if (!eid) return;

    Swal.fire({

        icon: 'warning',

        title: 'Reject Ticket (Final)',

        html: 'This will <strong>permanently reject</strong> the ticket — the PIC will no longer be able to process it.',

        input: 'textarea',

        inputLabel: 'Reason',

        inputPlaceholder: 'Enter the reason for rejection...',

        showCancelButton: true,

        confirmButtonText: 'Reject',

        cancelButtonText: 'Back',

        reverseButtons: true,

        confirmButtonColor: '#dc2626',

        inputValidator: (value) => {
            if (!value) {
                return 'Please enter a reason for rejection.';
            }
        },

    }).then((result) => {

        if (!result.isConfirmed) return;

        showLoading();

        $.ajax({

            url: window.ticketRoutes.reject.replace(':eid', eid),

            type: 'POST',

            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                response_descr: result.value,
            },

            success(res) {

                hideLoading();

                showSuccess(res.message || 'Ticket rejected.');

                if (typeof loadTicketDetail === 'function') {
                    const currentDetailEid = $('#comment_ticket_id').val();
                    if (currentDetailEid) loadTicketDetail(currentDetailEid);
                }

                if ($.fn.DataTable && $('#ticketTable').length) {
                    $('#ticketTable').DataTable().ajax.reload(null, false);
                }

                refreshTicketCalendar();

                if (typeof EngTicketApprovalPanel !== 'undefined') {
                    EngTicketApprovalPanel.refresh();
                }

            },

            error(xhr) {

                hideLoading();

                handleAjaxError(xhr);

            },

        });

    });

}

function reviseTicket(eid) {

    if (!eid) return;

    Swal.fire({

        icon: 'question',

        title: 'Revise Ticket',

        text: 'Send this ticket back to the PIC for rework.',

        input: 'textarea',

        inputLabel: 'Reason',

        inputPlaceholder: 'Enter the reason for revision...',

        showCancelButton: true,

        confirmButtonText: 'Revise',

        cancelButtonText: 'Back',

        reverseButtons: true,

        confirmButtonColor: '#d97706',

        inputValidator: (value) => {
            if (!value) {
                return 'Please enter a reason for revision.';
            }
        },

    }).then((result) => {

        if (!result.isConfirmed) return;

        showLoading();

        $.ajax({

            url: window.ticketRoutes.revise.replace(':eid', eid),

            type: 'POST',

            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                response_descr: result.value,
            },

            success(res) {

                hideLoading();

                showSuccess(res.message || 'Ticket sent back for revision.');

                if (typeof loadTicketDetail === 'function') {
                    const currentDetailEid = $('#comment_ticket_id').val();
                    if (currentDetailEid) loadTicketDetail(currentDetailEid);
                }

                if ($.fn.DataTable && $('#ticketTable').length) {
                    $('#ticketTable').DataTable().ajax.reload(null, false);
                }

                refreshTicketCalendar();

                if (typeof EngTicketApprovalPanel !== 'undefined') {
                    EngTicketApprovalPanel.refresh();
                }

            },

            error(xhr) {

                hideLoading();

                handleAjaxError(xhr);

            },

        });

    });

}

window.approveTicket = approveTicket;
window.rejectTicket = rejectTicket;
window.reviseTicket = reviseTicket;
