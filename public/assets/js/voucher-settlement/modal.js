const VplSettlementDetailModal = {

    open(id) {
        VplSettlement.state.currentViewId = id;

        $.get(VplSettlement.routes.data(id))
            .done((data) => {
                VplSettlement.state.currentViewData = data;
                VplSettlementDetailModal.populate(data);
                VplSettlementDetailModal.show();
                VplSettlement.pushUrl(data.hash);
            })
            .fail(() => VplSettlement.toast('error', 'Failed to load settlement data.'));
    },

    populate(data) {
        const t = data.settlement;

        document.getElementById('v_title').textContent   = t.settlement_id ?? 'Settlement Detail';
        document.getElementById('v_doc_no').textContent   = t.settlement_id ?? '';
        document.getElementById('v_user').textContent     = t.created_user ?? '';
        document.getElementById('v_date').textContent     = (t.settlement_date ?? '').substring(0, 10);
        document.getElementById('v_cpnyid').textContent   = t.cpnyid ?? '';
        document.getElementById('v_dept').textContent     = t.department ?? '';
        document.getElementById('v_vp_type').textContent  = data.vp_label ?? '';
        document.getElementById('v_usage_id').textContent = t.usage_id ?? '';
        document.getElementById('v_remark').textContent   = t.settlement_remark ?? '';

        document.getElementById('v_status_badge').innerHTML = VplSettlementHelper.statusBadgeHTML(t.status, data.status_label);

        // Status banner
        const banner = document.getElementById('v_statusBanner');
        banner.innerHTML = VplSettlementHelper.statusBadgeHTML(t.status, data.status_label);

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
        actionsDiv.classList.toggle('hidden', !data.can_approve);

        // Footer buttons
        document.getElementById('v_editBtn').classList.toggle('hidden', !data.can_edit);
        document.getElementById('v_cancelBtn').classList.toggle('hidden', !data.can_cancel);

        // Detail table
        const tbody = document.getElementById('v_detailBody');
        tbody.innerHTML = '';
        (data.details ?? []).forEach((d) => {
            const qtyReturn = VplSettlementHelper.deriveQtyReturn(d);
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td class="px-4 py-2">
                        <div class="font-medium text-slate-800 dark:text-white">${d.product_id}</div>
                        <div class="text-xs text-slate-500">${d.product_name ?? ''}</div>
                        ${d.settlement_remark ? `<div class="text-xs text-slate-400">${d.settlement_remark}</div>` : ''}
                    </td>
                    <td class="px-4 py-2 text-right text-xs font-semibold">${Number(d.qty_usage ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-right text-xs">${qtyReturn.toLocaleString()}</td>
                    <td class="px-4 py-2 text-right text-xs font-semibold text-indigo-600 dark:text-indigo-400">${Number(d.qty_settlement ?? 0).toLocaleString()}</td>
                    <td class="px-4 py-2 text-right text-xs font-semibold">${Number(d.qty_remain ?? 0).toLocaleString()}</td>
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
        document.getElementById('v_approvalBody').innerHTML = VplSettlementHelper.renderTimeline(data.approvals);

        // Messages
        VplSettlementDetailModal.renderMessages(data.messages ?? []);

        VplSettlementDetailModal.initApprovalActions(data);
        VplSettlementDetailModal.initFooterActions(data);
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
        const id = VplSettlement.state.currentViewId;

        document.getElementById('v_approveBtn').onclick = async () => {
            const res = await VplSettlement.confirm({
                title: 'Approve this document?',
                icon: 'question',
                confirmColor: '#10b981',
                confirmText: 'Approve',
            });
            if (!res.isConfirmed) return;

            $.post(VplSettlement.routes.approve(id), { _token: VplSettlement.csrf() })
                .done((r) => {
                    VplSettlement.toast('success', r.success ?? 'Approved.');
                    VplSettlementDetailModal.open(id);
                    VplSettlementDatalist.refresh();
                })
                .fail((x) => VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Approve failed.'));
        };

        document.getElementById('v_reviseBtn').onclick = async () => {
            const res = await VplSettlement.prompt({ title: 'Reason for revision' });
            if (!res.isConfirmed || !res.value) return;

            $.post(VplSettlement.routes.revise(id), { _token: VplSettlement.csrf(), message: res.value })
                .done((r) => {
                    VplSettlement.toast('success', r.success ?? 'Sent for revision.');
                    VplSettlementDetailModal.open(id);
                    VplSettlementDatalist.refresh();
                })
                .fail((x) => VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Revise failed.'));
        };

        document.getElementById('v_rejectBtn').onclick = async () => {
            const res = await VplSettlement.prompt({ title: 'Reason for rejection' });
            if (!res.isConfirmed || !res.value) return;

            $.post(VplSettlement.routes.reject(id), { _token: VplSettlement.csrf(), message: res.value })
                .done((r) => {
                    VplSettlement.toast('success', r.success ?? 'Rejected.');
                    VplSettlementDetailModal.open(id);
                    VplSettlementDatalist.refresh();
                })
                .fail((x) => VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Reject failed.'));
        };
    },

    initFooterActions(data) {
        const id = VplSettlement.state.currentViewId;

        document.getElementById('v_cancelBtn').onclick = async () => {
            const res = await VplSettlement.confirm({ title: 'Cancel this document?', text: 'This cannot be undone.' });
            if (!res.isConfirmed) return;

            $.post(VplSettlement.routes.cancel(id), { _token: VplSettlement.csrf() })
                .done((r) => {
                    VplSettlement.toast('success', r.success ?? 'Cancelled.');
                    VplSettlementDetailModal.hide();
                    VplSettlementDatalist.refresh();
                })
                .fail((x) => VplSettlement.toast('error', x.responseJSON?.error ?? x.responseJSON?.message ?? 'Cancel failed.'));
        };

        document.getElementById('v_editBtn').onclick = () => {
            VplSettlementDetailModal.hide();
            VplSettlementForm.openEdit(data);
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
            const id = VplSettlement.state.currentViewId;

            $.post(VplSettlement.routes.message(id), { _token: VplSettlement.csrf(), message: msg })
                .done(() => {
                    input.value = '';
                    $.get(VplSettlement.routes.data(id)).done((fresh) => {
                        VplSettlementDetailModal.renderMessages(fresh.messages ?? []);
                    });
                })
                .fail(() => VplSettlement.toast('error', 'Failed to send message.'));
        };
    },

    show() {
        const modal    = document.getElementById('viewModal');
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
        const modal    = document.getElementById('viewModal');
        const backdrop = modal.querySelector('.modal-backdrop');
        const panel    = modal.querySelector('.modal-panel');

        backdrop.classList.remove('opacity-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'scale-[0.98]');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            VplSettlement.clearUrl();
        }, 200);
    },

    init() {
        ['closeViewModal', 'closeViewModalFooter'].forEach((btnId) => {
            document.getElementById(btnId)?.addEventListener('click', () => VplSettlementDetailModal.hide());
        });

        VplSettlementDetailModal.initDiscussion();
    },
};
