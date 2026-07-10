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

        title: 'Reject Ticket Approval',

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

                showSuccess(res.message || 'Ticket approval rejected.');

                if (typeof loadTicketDetail === 'function') {
                    const currentDetailEid = $('#comment_ticket_id').val();
                    if (currentDetailEid) loadTicketDetail(currentDetailEid);
                }

                if ($.fn.DataTable && $('#ticketTable').length) {
                    $('#ticketTable').DataTable().ajax.reload(null, false);
                }

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
