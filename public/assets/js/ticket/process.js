window.Ticket = window.Ticket || {};

function initTicketProcess() {

    bindCancelTicket();

}

function bindCancelTicket() {

    $(document).on(
        'click',
        '.btn-cancel-ticket',
        function () {

            const eid =
                $(this).data('id');

            if (!eid) {
                return;
            }

            Swal.fire({

                icon: 'warning',

                title: 'Cancel Ticket?',

                text:
                    'This ticket will be cancelled.',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Cancel',

                cancelButtonText:
                    'Back',

                reverseButtons: true,

                confirmButtonColor:
                    '#dc2626',

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({

                    url:
                        `/ticket/cancel/${eid}`,

                    type:
                        'POST',

                    data: {
                        _token:
                            Ticket.csrf
                    },

                    success: function (response) {

                        showSuccess(
                            response.message ||
                            'Ticket cancelled successfully'
                        );

                        if (
                            typeof ticketTable !== 'undefined'
                        ) {

                            ticketTable.ajax.reload(
                                null,
                                false
                            );

                        }

                        closeTicketDetailModal();

                    },

                    error: function (xhr) {

                        showError(
                            xhr.responseJSON?.message ||
                            'Failed cancel ticket'
                        );

                    }

                });

            });

        }
    );

}
