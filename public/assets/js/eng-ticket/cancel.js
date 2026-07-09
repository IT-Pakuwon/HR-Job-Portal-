function cancelTicket(eid) {
    if (!eid) return;

    Swal.fire({
        title: '',
        html: `
            <div style="display:flex;flex-direction:column;align-items:center;gap:12px;padding:8px 4px 4px;">
                <div style="width:64px;height:64px;border-radius:50%;background:#fee2e2;display:flex;align-items:center;justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:20px;font-weight:700;color:#111827;margin-bottom:4px;">Cancel Ticket</div>
                    <div style="font-size:13px;color:#6b7280;">This action cannot be undone. Please provide a reason below.</div>
                </div>
                <div style="width:100%;text-align:left;">
                    <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;letter-spacing:0.4px;text-transform:uppercase;">Reason <span style="color:#ef4444;">*</span></label>
                    <textarea id="swal-cancel-reason" placeholder="e.g. Issue already resolved, wrong submission..." rows="4"
                        style="width:100%;box-sizing:border-box;resize:vertical;padding:10px 12px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:14px;color:#111827;outline:none;font-family:inherit;transition:border-color .2s;"
                        onfocus="this.style.borderColor='#ef4444'" onblur="this.style.borderColor='#e5e7eb'"></textarea>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Cancel Ticket',
        cancelButtonText: 'Back',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        buttonsStyling: true,
        showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
        customClass: { popup: 'swal-cancel-ticket-popup' },
        didOpen: () => {
            const container = document.querySelector('.swal2-html-container');
            if (container) { container.style.overflowX = 'hidden'; container.style.margin = '0'; }
            const popup = document.querySelector('.swal-cancel-ticket-popup');
            if (popup) { popup.style.borderRadius = '16px'; popup.style.padding = '28px 28px 20px'; }
            const actions = document.querySelector('.swal2-actions');
            if (actions) { actions.style.gap = '10px'; actions.style.marginTop = '8px'; }
            document.getElementById('swal-cancel-reason').focus();
        },
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
