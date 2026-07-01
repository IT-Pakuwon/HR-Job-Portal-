const VplTransferDetailModal = {

    open(id) {
        VplTransfer.state.currentViewId = id;

        $.get(VplTransfer.routes.data(id))
            .done((data) => {
                VplTransfer.state.currentViewData = data;
                VplTransferDetailModal.populate(data);
                VplTransferDetailModal.show();
                VplTransfer.pushUrl(data.transfer.id);
            })
            .fail(() => VplTransfer.toast('error', 'Failed to load transfer data.'));
    },

    populate(data) {
        const t = data.transfer;

        document.getElementById('v_title').textContent    = t.transfer_id ?? 'Transfer Detail';
        document.getElementById('v_doc_no').textContent   = t.transfer_id ?? '';
        document.getElementById('v_user').textContent     = t.created_user ?? '';
        document.getElementById('v_date').textContent     = (t.transfer_date ?? '').substring(0, 10);
        document.getElementById('v_cpnyid').textContent   = t.cpnyid ?? '';
        document.getElementById('v_dept').textContent     = t.department ?? '';
        document.getElementById('v_vp_type').textContent  = t.vp_type === 'V' ? 'Voucher' : (t.vp_type === 'P' ? 'Product' : t.vp_type);
        document.getElementById('v_transfer_type').textContent = data.transfer_type_label ?? '';
        document.getElementById('v_remark').textContent   = t.transfer_remark ?? '';

        document.getElementById('v_status_badge').innerHTML = VplTransferHelper.statusBadgeHTML(t.status, data.status_label);

        // Reference ID
        const refWrap = document.getElementById('v_ref_wrapper');
        if (t.ref_transfer_id) {
            document.getElementById('v_ref_id').textContent = t.ref_transfer_id;
            refWrap.classList.remove('hidden');
        } else {
            refWrap.classList.add('hidden');
        }

        // Status banner
        const banner = document.getElementById('v_statusBanner');
        banner.innerHTML = VplTransferHelper.statusBadgeHTML(t.status, data.status_label);

        // Revision reason
        const revWrap = document.getElementById('v_reviseReasonWrapper');
        if (t.status === 'D' && data.messages?.length) {
            const last = data.messages[data.messages.length - 1];
            document.getElementById('v_revise_reason').textContent = last.message;
            revWrap.classList.remove('hidden');
        } else {
            revWrap.classList.add('hidden');
        }

        // Approval actions
        const actionsDiv = document.getElementById('v_approvalActions');
        if (data.can_approve) {
            actionsDiv.classList.remove('hidden');
        } else {
            actionsDiv.classList.add('hidden');
        }

        // Footer buttons
        document.getElementById('v_editBtn').classList.toggle('hidden', !data.can_edit);
        document.getElementById('v_cancelBtn').classList.toggle('hidden', !data.can_cancel);

        // Detail table
        const tbody = document.getElementById('v_detailBody');
        tbody.innerHTML = '';
        (data.details ?? []).forEach((d) => {
            const exp = (d.expired_date ?? '').substring(0, 10);
            const expDisplay = (exp === '' || exp === '1900-01-01') ? '—' : exp;
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="px-4 py-2">
                        <div class="font-medium text-slate-800 dark:text-white">${d.product_id}</div>
                        <div class="text-xs text-slate-500">${d.product_name ?? ''}</div>
                    </td>
                    <td class="px-4 py-2 text-xs">${d.from_whs_id ?? ''}</td>
                    <td class="px-4 py-2 text-xs">${d.to_whs_id ?? ''}</td>
                    <td class="px-4 py-2 text-xs">${expDisplay}</td>
                    <td class="px-4 py-2 text-right text-xs">${Number(d.qty_available ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-right text-xs font-semibold">${Number(d.qty_transfer ?? 0).toLocaleString()}</td>
                </tr>
            `);
        });

        // Attachments
        const attachBody = document.getElementById('v_attachBody');
        attachBody.innerHTML = '';
        if (data.attachments?.length) {
            data.attachments.forEach((a) => {
                const url = `/attachment/${a.year ?? new Date().getFullYear()}/${a.attachfile}`;
                attachBody.insertAdjacentHTML('beforeend', `
                    <div class="flex items-center justify-between px-4 py-2">
                        <a href="${url}" target="_blank" class="text-sm text-indigo-600 hover:underline dark:text-indigo-400">
                            <i class="fa-regular fa-file mr-2 text-xs"></i>${a.name ?? a.attachfile}
                        </a>
                        <span class="text-xs text-slate-400">${a.created_at ?? ''}</span>
                    </div>
                `);
            });
        } else {
            attachBody.innerHTML = '<p class="p-4 text-sm text-slate-400">No attachments.</p>';
        }

        // Approval timeline
        document.getElementById('v_approvalBody').innerHTML = VplTransferHelper.renderTimeline(data.approvals);

        // Messages
        VplTransferDetailModal.renderMessages(data.messages ?? []);

        VplTransferDetailModal.initApprovalActions(data);
        VplTransferDetailModal.initFooterActions(data);
    },

    renderMessages(messages) {
        const msgBody = document.getElementById('v_msgBody');
        msgBody.innerHTML = '';
        messages.forEach((m) => {
            const alignCls = m.is_mine ? 'items-end' : 'items-start';
            const bubbleCls = m.is_mine
                ? 'bg-indigo-600 text-white rounded-br-none'
                : 'bg-white dark:bg-white/[0.07] text-slate-800 dark:text-slate-100 rounded-bl-none';
            msgBody.insertAdjacentHTML('beforeend', `
                <div class="flex flex-col gap-1 ${alignCls}">
                    <div class="text-[11px] text-slate-400">${m.name} · ${m.created_at}</div>
                    <div class="max-w-[85%] rounded-xl px-4 py-2 text-sm shadow-sm ${bubbleCls}">${m.message}</div>
                </div>
            `);
        });
        msgBody.scrollTop = msgBody.scrollHeight;
    },

    initApprovalActions(data) {
        const id = VplTransfer.state.currentViewId;

        document.getElementById('v_approveBtn').onclick = async () => {
            const res = await VplTransfer.confirm({
                title: 'Approve this document?',
                icon: 'question',
                confirmColor: '#10b981',
                confirmText: 'Approve',
            });
            if (!res.isConfirmed) return;

            $.post(VplTransfer.routes.approve(id), { _token: VplTransfer.csrf() })
                .done((r) => {
                    VplTransfer.toast('success', r.success ?? 'Approved.');
                    VplTransferDetailModal.open(id);
                    VplTransferDatalist.refresh();
                })
                .fail((x) => VplTransfer.toast('error', x.responseJSON?.error ?? 'Approve failed.'));
        };

        document.getElementById('v_reviseBtn').onclick = async () => {
            const res = await VplTransfer.prompt({ title: 'Reason for revision' });
            if (!res.isConfirmed || !res.value) return;

            $.post(VplTransfer.routes.revise(id), { _token: VplTransfer.csrf(), message: res.value })
                .done((r) => {
                    VplTransfer.toast('success', r.success ?? 'Sent for revision.');
                    VplTransferDetailModal.open(id);
                    VplTransferDatalist.refresh();
                })
                .fail((x) => VplTransfer.toast('error', x.responseJSON?.error ?? 'Revise failed.'));
        };

        document.getElementById('v_rejectBtn').onclick = async () => {
            const res = await VplTransfer.prompt({ title: 'Reason for rejection' });
            if (!res.isConfirmed || !res.value) return;

            $.post(VplTransfer.routes.reject(id), { _token: VplTransfer.csrf(), message: res.value })
                .done((r) => {
                    VplTransfer.toast('success', r.success ?? 'Rejected.');
                    VplTransferDetailModal.open(id);
                    VplTransferDatalist.refresh();
                })
                .fail((x) => VplTransfer.toast('error', x.responseJSON?.error ?? 'Reject failed.'));
        };
    },

    initFooterActions(data) {
        const id = VplTransfer.state.currentViewId;

        document.getElementById('v_cancelBtn').onclick = async () => {
            const res = await VplTransfer.confirm({ title: 'Cancel this document?', text: 'This cannot be undone.' });
            if (!res.isConfirmed) return;

            $.post(VplTransfer.routes.cancel(id), { _token: VplTransfer.csrf() })
                .done((r) => {
                    VplTransfer.toast('success', r.success ?? 'Cancelled.');
                    VplTransferDetailModal.hide();
                    VplTransferDatalist.refresh();
                })
                .fail((x) => VplTransfer.toast('error', x.responseJSON?.error ?? 'Cancel failed.'));
        };

        document.getElementById('v_editBtn').onclick = () => {
            VplTransferDetailModal.hide();
            VplTransferForm.openEdit(data);
        };
    },

    initDiscussion() {
        const btn    = document.getElementById('v_msgToggleBtn');
        const panel  = document.getElementById('v_discussionPanel');
        const close  = document.getElementById('v_discussionClose');
        const send   = document.getElementById('v_msgSend');
        const input  = document.getElementById('v_msgInput');

        btn.onclick   = () => panel.classList.toggle('hidden');
        close.onclick = () => panel.classList.add('hidden');

        send.onclick = () => {
            const msg = input.value.trim();
            if (!msg) return;
            const id = VplTransfer.state.currentViewId;

            $.post(VplTransfer.routes.message(id), { _token: VplTransfer.csrf(), message: msg })
                .done(() => {
                    input.value = '';
                    $.get(VplTransfer.routes.data(id)).done((fresh) => {
                        VplTransferDetailModal.renderMessages(fresh.messages ?? []);
                    });
                })
                .fail(() => VplTransfer.toast('error', 'Failed to send message.'));
        };
    },

    show() {
        const modal   = document.getElementById('viewModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        requestAnimationFrame(() => {
            backdrop.classList.add('opacity-100');
            panel.classList.remove('opacity-0', 'translate-y-4', 'scale-[0.98]');
        });
    },

    hide() {
        const modal   = document.getElementById('viewModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            VplTransfer.clearUrl();
        }, 200);
    },

    init() {
        // Close buttons only — no backdrop click (view modal has message input)
        ['closeViewModal', 'closeViewModalFooter'].forEach((btnId) => {
            document.getElementById(btnId)?.addEventListener('click', () => VplTransferDetailModal.hide());
        });

        VplTransferDetailModal.initDiscussion();
    },
};
