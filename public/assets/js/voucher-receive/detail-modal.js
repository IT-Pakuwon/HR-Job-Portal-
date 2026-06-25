// ============================================================
// detail-modal.js — Voucher Product Receive
// View / detail modal: populate, approval actions, messages
// ============================================================

const VplReceiveDetailModal = {

    // --------------------------------------------------------
    // OPEN — fetch data from server then show modal
    // --------------------------------------------------------
    open(id) {
        VplReceive.state.currentViewId = id;
        $.get(VplReceive.routes.data(id), function (data) {
            VplReceive.state.currentViewData = data;
            VplReceiveDetailModal.populate(data);
            VplReceiveModal.open('viewModal');
        }).fail(() => VplReceive.toast('error', 'Failed to load receive data.'));
    },

    // --------------------------------------------------------
    // POPULATE — fill every section of the view modal
    // --------------------------------------------------------
    populate(d) {
        const r = d.receive;

        // Header
        $('#v_title').text(`Receive — ${r.receive_id}`);
        $('#v_receive_id').text(r.receive_id);
        $('#v_date').text(r.receive_date || '—');
        $('#v_cpnyid').text(r.cpnyid || '—');
        $('#v_dept').text(r.department || '—');
        $('#v_user').text(r.created_user || '—');
        $('#v_vp_type').text(d.vp_label || '—');
        $('#v_receive_type').text(r.receive_type || '—');
        $('#v_tenant').text(r.receive_tenant || '—');
        $('#v_source_dept').text(r.source_receive_dept || '—');
        $('#v_remark').text(r.receive_remark || '—');
        $('#v_status_badge').html(VplReceiveHelper.statusBadgeHTML(r.status, d.status_label));

        // Details
        let dHtml = '';
        d.details.forEach(row => {
            const exp = row.expired_date === '1900-01-01' ? 'No Expired' : row.expired_date;
            dHtml += `<tr class="hover:bg-blue-50 dark:hover:bg-blue-500/5">
                <td class="px-4 py-2 text-xs text-slate-700 dark:text-slate-200">${row.product_id}</td>
                <td class="px-4 py-2 text-xs text-slate-700 dark:text-slate-200">${row.product_name || '—'}</td>
                <td class="px-4 py-2 text-xs text-slate-700 dark:text-slate-200">${row.product_source_tenant || '—'}</td>
                <td class="px-4 py-2 text-xs text-slate-700 dark:text-slate-200">${exp}</td>
                <td class="px-4 py-2 text-xs font-semibold text-slate-800 dark:text-slate-100">${row.qty_receive}</td>
                <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300">${row.product_uom || ''}</td>
                <td class="px-4 py-2 text-xs text-slate-600 dark:text-slate-300">${row.whs_id}</td>
            </tr>`;
        });
        $('#v_detailBody').html(dHtml || '<tr><td colspan="7" class="px-4 py-3 text-center text-xs text-slate-400">No details.</td></tr>');

        // Approval timeline
        let aHtml = '';
        d.approvals.forEach(ap => {
            aHtml += `<div class="flex items-center justify-between px-4 py-2.5 text-xs">
                <div class="flex items-center gap-2">
                    <span class="flex h-5 w-5 items-center justify-center rounded-full bg-indigo-100 text-[10px] font-bold text-indigo-700">${ap.aprvid}</span>
                    <span class="font-medium text-slate-700 dark:text-slate-200">${ap.name || ap.aprvusername}</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-slate-400">${ap.aprvdateafter || ap.aprvdatebefore || '—'}</span>
                    ${VplReceiveHelper.approvalBadge(ap.status)}
                </div>
            </div>`;
        });
        $('#v_approvalBody').html(aHtml || '<div class="p-4 text-xs text-slate-400">No approval records.</div>');

        // Show / hide approval action buttons
        if (d.can_approve || d.can_reject || d.can_revise) {
            $('#v_approvalActions').removeClass('hidden');
            $('#v_approveBtn').toggle(!!d.can_approve);
            $('#v_rejectBtn').toggle(!!d.can_reject);
            $('#v_reviseBtn').toggle(!!d.can_revise);
        } else {
            $('#v_approvalActions').addClass('hidden');
        }
        $('#v_rejectForm, #v_reviseForm').addClass('hidden');
        $('#v_rejectReason, #v_reviseReason').val('');

        // Attachments
        let attHtml = '';
        if (d.attachments.length === 0) {
            attHtml = '<div class="p-4 text-xs text-slate-400">No attachments.</div>';
        } else {
            d.attachments.forEach(a => {
                const icon = VplReceiveHelper.attachIcon(a.extention);
                attHtml += `<div class="flex items-center justify-between px-5 py-2.5 hover:bg-slate-50 dark:hover:bg-white/[0.02]">
                    <a href="/attachment/${a.year}/${a.attachfile}" target="_blank"
                        class="flex items-center gap-2 text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                        <i class="fa-solid ${icon} text-base"></i>${a.name}
                    </a>
                    <span class="text-xs text-slate-400">${a.created_at || ''}</span>
                </div>`;
            });
        }
        $('#v_attachBody').html(attHtml);

        // Messages
        VplReceiveDetailModal.renderMessages(d.messages);

        // Footer action buttons
        $('#v_cancelBtn').toggle(!!d.can_cancel);
        $('#v_editBtn').toggle(!!d.can_edit);
    },

    // --------------------------------------------------------
    // MESSAGES
    // --------------------------------------------------------
    renderMessages(messages) {
        let html = '';
        if (messages.length === 0) {
            html = '<div class="text-xs text-slate-400">No messages yet.</div>';
        } else {
            messages.forEach(m => {
                if (m.is_mine) {
                    html += `<div class="flex justify-end">
                        <div class="max-w-[80%] rounded-lg rounded-tr-none bg-indigo-100 px-3 py-2 dark:bg-indigo-500/20">
                            <div class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">${m.name}</div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">${m.message}</div>
                            <div class="mt-1 text-[10px] text-slate-400">${m.created_at || ''}</div>
                        </div>
                    </div>`;
                } else {
                    html += `<div class="flex justify-start">
                        <div class="max-w-[80%] rounded-lg rounded-tl-none bg-slate-100 px-3 py-2 dark:bg-white/[0.06]">
                            <div class="text-xs font-semibold text-slate-700 dark:text-slate-200">${m.name}</div>
                            <div class="text-sm text-slate-700 dark:text-slate-200">${m.message}</div>
                            <div class="mt-1 text-[10px] text-slate-400">${m.created_at || ''}</div>
                        </div>
                    </div>`;
                }
            });
        }
        const $body = $('#v_msgBody');
        $body.html(html).scrollTop($body[0]?.scrollHeight ?? 0);
    },

    sendMessage() {
        const msg = $('#v_msgInput').val().trim();
        if (!msg) return;
        const id = VplReceive.state.currentViewId;
        $.post(VplReceive.routes.message(id), { message: msg, _token: VplReceive.csrf() }, function () {
            $('#v_msgInput').val('');
            $.get(VplReceive.routes.data(id), function (data) {
                VplReceive.state.currentViewData = data;
                VplReceiveDetailModal.renderMessages(data.messages);
            });
        }).fail(() => VplReceive.toast('error', 'Failed to send message.'));
    },

    // --------------------------------------------------------
    // APPROVAL ACTIONS
    // --------------------------------------------------------
    initApprovalActions() {
        // ── Approve ──
        $('#v_approveBtn').on('click', () => {
            VplReceiveModal.close('viewModal');
            VplReceiveModal.open('confirmApproveModal');
        });
        $('#closeConfirmApprove').on('click', () => {
            VplReceiveModal.close('confirmApproveModal');
            VplReceiveModal.open('viewModal');
        });
        $('#doApproveBtn').on('click', function () {
            const $btn = $(this).prop('disabled', true).text('Approving...');
            $.post(VplReceive.routes.approve(VplReceive.state.currentViewId), { _token: VplReceive.csrf() })
                .done(() => {
                    VplReceiveModal.close('confirmApproveModal');
                    VplReceive.toast('success', 'Document approved!');
                    setTimeout(() => location.reload(), 1200);
                })
                .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error approving.'))
                .always(() => $btn.prop('disabled', false).text('Approve'));
        });

        // ── Reject ──
        $('#v_rejectBtn').on('click', () => {
            $('#v_rejectForm').removeClass('hidden');
            $('#v_reviseForm').addClass('hidden');
        });
        $('#v_rejectCancel').on('click', () => $('#v_rejectForm').addClass('hidden'));
        $('#v_rejectConfirm').on('click', function () {
            const msg = $('#v_rejectReason').val().trim();
            if (!msg) { VplReceive.toast('warning', 'Please enter a rejection reason.'); return; }
            const $btn = $(this).prop('disabled', true).text('Rejecting...');
            $.post(VplReceive.routes.reject(VplReceive.state.currentViewId), { message: msg, _token: VplReceive.csrf() })
                .done(() => {
                    VplReceiveModal.close('viewModal');
                    VplReceive.toast('success', 'Document rejected.');
                    setTimeout(() => location.reload(), 1200);
                })
                .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error rejecting.'))
                .always(() => $btn.prop('disabled', false).text('Confirm Reject'));
        });

        // ── Revise ──
        $('#v_reviseBtn').on('click', () => {
            $('#v_reviseForm').removeClass('hidden');
            $('#v_rejectForm').addClass('hidden');
        });
        $('#v_reviseCancel').on('click', () => $('#v_reviseForm').addClass('hidden'));
        $('#v_reviseConfirm').on('click', function () {
            const msg = $('#v_reviseReason').val().trim();
            if (!msg) { VplReceive.toast('warning', 'Please enter a revision reason.'); return; }
            const $btn = $(this).prop('disabled', true).text('Sending...');
            $.post(VplReceive.routes.revise(VplReceive.state.currentViewId), { message: msg, _token: VplReceive.csrf() })
                .done(() => {
                    VplReceiveModal.close('viewModal');
                    VplReceive.toast('success', 'Document sent for revision.');
                    setTimeout(() => location.reload(), 1200);
                })
                .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error.'))
                .always(() => $btn.prop('disabled', false).text('Confirm Revise'));
        });

        // ── Cancel Document ──
        $('#v_cancelBtn').on('click', () => {
            VplReceiveModal.close('viewModal');
            VplReceiveModal.open('confirmCancelModal');
        });
        $('#closeConfirmCancel').on('click', () => {
            VplReceiveModal.close('confirmCancelModal');
            VplReceiveModal.open('viewModal');
        });
        $('#doCancelBtn').on('click', function () {
            const $btn = $(this).prop('disabled', true).text('Cancelling...');
            $.post(VplReceive.routes.cancel(VplReceive.state.currentViewId), { _token: VplReceive.csrf() })
                .done(() => {
                    VplReceiveModal.close('confirmCancelModal');
                    VplReceive.toast('success', 'Document cancelled.');
                    setTimeout(() => location.reload(), 1200);
                })
                .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error cancelling.'))
                .always(() => $btn.prop('disabled', false).text('Yes, Cancel'));
        });
    },

    // --------------------------------------------------------
    // INIT — bind all view-modal events
    // --------------------------------------------------------
    init() {
        // Close
        $('#closeViewModal, #closeViewModalFooter').on('click', () => VplReceiveModal.close('viewModal'));

        // Edit button → open edit modal
        $('#v_editBtn').on('click', () => {
            if (!VplReceive.state.currentViewData) return;
            VplReceiveModal.close('viewModal');
            VplReceiveForm.populateEditModal(VplReceive.state.currentViewData);
            VplReceiveModal.open('editModal');
        });

        // Message send
        $('#v_msgSend').on('click',    ()  => VplReceiveDetailModal.sendMessage());
        $('#v_msgInput').on('keypress', e  => { if (e.which === 13) VplReceiveDetailModal.sendMessage(); });

        // Approval actions
        VplReceiveDetailModal.initApprovalActions();
    },
};
