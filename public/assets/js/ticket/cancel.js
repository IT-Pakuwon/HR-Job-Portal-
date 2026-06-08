function cancelTicket(eid) {
    if (!eid) return;

    if (!confirm('Are you sure you want to cancel this ticket?')) return;

    showLoading();

    $.ajax({
        url: window.ticketRoutes.cancel.replace(':eid', eid),
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
        },
        success(res) {
            hideLoading();
            showSuccess(res.message || 'Ticket cancelled successfully.');

            if (typeof loadTicketDetail === 'function') {
                const currentDetailEid = $('#detail_ticket_eid').val();
                if (currentDetailEid) loadTicketDetail(currentDetailEid);
            }

            if ($.fn.DataTable && $('#ticketTable').length) {
                $('#ticketTable').DataTable().ajax.reload(null, false);
            }

            resetTicketUrl();
        },
        error(xhr) {
            hideLoading();
            handleAjaxError(xhr);
        },
    });
}

window.cancelTicket = cancelTicket;
