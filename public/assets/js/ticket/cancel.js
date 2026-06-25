function cancelTicket(eid) {
    if (!eid) return;

    Swal.fire({
        title: 'Cancel Ticket',
        html: `
            <p class="mb-3 text-sm text-gray-600">Are you sure you want to cancel this ticket?</p>
            <textarea id="swal-cancel-reason" class="swal2-textarea" placeholder="Enter reason for cancellation..." rows="3" style="width:100%;resize:vertical;"></textarea>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Cancel Ticket',
        cancelButtonText: 'Back',
        confirmButtonColor: '#d33',
        preConfirm: () => {
            const reason = document.getElementById('swal-cancel-reason').value.trim();
            if (!reason) {
                Swal.showValidationMessage('Please enter a reason for cancellation.');
                return false;
            }
            return reason;
        },
    }).then((result) => {
        if (!result.isConfirmed) return;

        showLoading();

        $.ajax({
            url: window.ticketRoutes.cancel.replace(':eid', eid),
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                response_descr: result.value,
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
    });
}

window.cancelTicket = cancelTicket;
