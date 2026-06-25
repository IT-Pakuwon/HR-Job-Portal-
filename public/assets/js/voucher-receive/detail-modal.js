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
            // Push URL so it becomes shareable (e.g. /showreceivevp/VPR266007)
            VplReceive.pushUrl(data.receive?.receive_id);
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
        $('#v_date').text(r.receive_date ? String(r.receive_date).split('T')[0] : '—');
        $('#v_cpnyid').text(r.cpnyid || '—');
        $('#v_dept').text(r.department || '—');
        $('#v_user').text(r.created_user || '—');
        $('#v_vp_type').text(d.vp_label || '—');
        $('#v_receive_type').text(r.receive_type || '—');
        $('#v_tenant').text(r.receive_tenant || '—');
        $('#v_source_dept').text(r.source_receive_dept || '—');
        $('#v_remark').text(r.receive_remark || '—');
        $('#v_status_badge').html(VplReceiveHelper.statusBadgeHTML(r.status, d.status_label));

        // Status banner (populates inner content of #v_statusBanner, matching Voucher Taxi pattern)
        const bannerMap = {
            P: { cls: 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:border-blue-500/30',         icon: 'fa-circle-info',          text: 'Waiting for approval. Your request is under review.' },
            C: { cls: 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-500/30', icon: 'fa-circle-check', text: 'Approved! Your receive document has been accepted.' },
            R: { cls: 'bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-300 dark:border-red-500/30',               icon: 'fa-circle-xmark',         text: 'Rejected. This document has been rejected.' },
            D: { cls: 'bg-yellow-50 text-yellow-800 border border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:border-yellow-500/30', icon: 'fa-triangle-exclamation', text: 'On Hold. This document requires revision.' },
            X: { cls: 'bg-slate-100 text-slate-600 border border-slate-200 dark:bg-white/5 dark:text-slate-400 dark:border-white/10',           icon: 'fa-ban',                  text: 'Cancelled. This document has been cancelled.' },
        };
        const bc = bannerMap[r.status];
        $('#v_statusBanner').html(bc
            ? `<div class="flex-1 rounded-lg border px-4 py-3 text-sm font-medium ${bc.cls}"><i class="fa-solid ${bc.icon} mr-2 shrink-0"></i>${bc.text}</div>`
            : '');

        // Revision reason box (shown when status = D)
        if (r.status === 'D') {
            const reviseText = d.revise_reason || (d.approvals ?? []).find(a => a.status === 'D')?.remark || '';
            if (reviseText) {
                $('#v_reviseReasonWrapper').removeClass('hidden');
                $('#v_revise_reason').text(reviseText);
            } else {
                $('#v_reviseReasonWrapper').addClass('hidden');
            }
        } else {
            $('#v_reviseReasonWrapper').addClass('hidden');
        }

        // Details
        let dHtml = '';
        d.details.forEach(row => {
            const rawExp = (row.expired_date || '').split('T')[0];
            const exp = (!rawExp || rawExp === '1900-01-01') ? 'No Expired' : rawExp;
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

        // Approval timeline (rendered same style as Voucher Taxi)
        $('#v_approvalBody').html(VplReceiveHelper.renderTimeline(d.approvals));

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
        const $body = $('#v_msgBody');

        if (!messages || messages.length === 0) {
            $body.html(`
                <div class="flex h-full items-center justify-center px-6 text-center">
                    <div>
                        <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white/[0.04] text-slate-400">
                            <i class="fa-regular fa-comments text-lg"></i>
                        </div>
                        <p class="text-sm font-medium text-slate-400">No messages yet</p>
                    </div>
                </div>`);
            return;
        }

        let html = '';
        messages.forEach(m => {
            const isMine = !!m.is_mine;
            html += `
                <div class="flex ${isMine ? 'justify-end' : 'justify-start'}">
                    <div class="max-w-[82%] rounded-2xl px-4 py-3 shadow-[0_8px_25px_rgba(0,0,0,.18)]
                        ${isMine
                            ? 'rounded-br-md bg-blue-600 text-white'
                            : 'rounded-bl-md border border-slate-200 bg-white text-slate-700 dark:border-white/6 dark:bg-white/6 dark:text-slate-200'}">
                        <div class="mb-0 flex items-center gap-2 text-[11px] font-medium ${isMine ? 'text-blue-100' : 'text-slate-500 dark:text-slate-400'}">
                            <span>${m.name ?? '-'}</span>
                            <span>•</span>
                            <span>${m.created_at ?? ''}</span>
                        </div>
                        <div class="whitespace-normal wrap-break-word text-sm leading-6">${m.message ?? '-'}</div>
                    </div>
                </div>`;
        });
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
        $('#v_approveBtn').on('click', () => {
            VplReceive.confirm({
                title:        'Approve Document?',
                text:         'Are you sure you want to approve this receive document?',
                icon:         'question',
                confirmText:  'Yes, Approve',
                confirmColor: '#10b981',
                cancelText:   'Cancel',
            }).then(r => { if (r.isConfirmed) VplReceiveDetailModal._submitApprove(VplReceive.state.currentViewId); });
        });

        $('#v_rejectBtn').on('click', () => {
            VplReceive.prompt({
                title:         'Reject Document',
                label:         'Rejection Reason',
                placeholder:   'Please explain why you are rejecting this document...',
                confirmText:   'Reject',
                confirmColor:  '#ef4444',
                cancelText:    'Cancel',
                validationMsg: 'Please provide a reason for rejection.',
            }).then(r => { if (r.isConfirmed) VplReceiveDetailModal._submitReject(VplReceive.state.currentViewId, r.value); });
        });

        $('#v_reviseBtn').on('click', () => {
            VplReceive.prompt({
                title:         'Request Revision',
                label:         'Revision Notes',
                placeholder:   'Please provide feedback for the requester to revise...',
                confirmText:   'Request Revision',
                confirmColor:  '#f59e0b',
                cancelText:    'Cancel',
                validationMsg: 'Please provide revision notes.',
            }).then(r => { if (r.isConfirmed) VplReceiveDetailModal._submitRevise(VplReceive.state.currentViewId, r.value); });
        });

        $('#v_cancelBtn').on('click', () => {
            VplReceive.confirm({
                title:        'Cancel Document?',
                text:         'Are you sure you want to cancel this document? This action cannot be undone.',
                icon:         'warning',
                confirmText:  'Yes, Cancel it',
                confirmColor: '#dc2626',
                cancelText:   'No, Keep it',
            }).then(r => { if (r.isConfirmed) VplReceiveDetailModal._submitCancel(VplReceive.state.currentViewId); });
        });
    },

    _closeViewModal() {
        VplReceiveModal.close('viewModal');
        VplReceive.clearUrl();
        $('#v_discussionPanel').addClass('hidden');
    },

    _submitApprove(id) {
        $.post(VplReceive.routes.approve(id), { _token: VplReceive.csrf() })
            .done(() => {
                VplReceiveDetailModal._closeViewModal();
                VplReceive.toast('success', 'Document approved!');
                setTimeout(() => location.reload(), 1200);
            })
            .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error approving.'));
    },

    _submitReject(id, reason) {
        $.post(VplReceive.routes.reject(id), { message: reason, _token: VplReceive.csrf() })
            .done(() => {
                VplReceiveDetailModal._closeViewModal();
                VplReceive.toast('success', 'Document rejected.');
                setTimeout(() => location.reload(), 1200);
            })
            .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error rejecting.'));
    },

    _submitRevise(id, reason) {
        $.post(VplReceive.routes.revise(id), { message: reason, _token: VplReceive.csrf() })
            .done(() => {
                VplReceiveDetailModal._closeViewModal();
                VplReceive.toast('success', 'Document sent for revision.');
                setTimeout(() => location.reload(), 1200);
            })
            .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error.'));
    },

    _submitCancel(id) {
        $.post(VplReceive.routes.cancel(id), { _token: VplReceive.csrf() })
            .done(() => {
                VplReceiveDetailModal._closeViewModal();
                VplReceive.toast('success', 'Document cancelled.');
                setTimeout(() => location.reload(), 1200);
            })
            .fail(xhr => VplReceive.toast('error', xhr.responseJSON?.error ?? 'Error cancelling.'));
    },

    // --------------------------------------------------------
    // INIT — bind all view-modal events
    // --------------------------------------------------------
    init() {
        // Close (clear URL + hide discussion panel)
        $('#closeViewModal, #closeViewModalFooter').on('click', () => {
            VplReceiveDetailModal._closeViewModal();
        });

        // Edit button → open edit modal
        $('#v_editBtn').on('click', () => {
            if (!VplReceive.state.currentViewData) return;
            VplReceiveDetailModal._closeViewModal();
            VplReceiveForm.populateEditModal(VplReceive.state.currentViewData);
            VplReceiveModal.open('editModal');
        });

        // Discussion panel toggle
        $('#v_msgToggleBtn').on('click', () => {
            $('#v_discussionPanel').toggleClass('hidden');
        });
        $('#v_discussionClose').on('click', () => {
            $('#v_discussionPanel').addClass('hidden');
        });

        // Message send
        $('#v_msgSend').on('click', () => VplReceiveDetailModal.sendMessage());
        $('#v_msgInput').on('keypress', e => {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                VplReceiveDetailModal.sendMessage();
            }
        });

        // Approval actions
        VplReceiveDetailModal.initApprovalActions();
    },
};
