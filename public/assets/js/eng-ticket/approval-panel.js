// ============================================================
// approval-panel.js — Eng Ticket
// Right-side panel for any user currently listed as approver on
// a ticket's active approval level. Approve/Reject reuse the
// existing global approveTicket()/rejectTicket() (approval.js),
// which already confirm via SweetAlert before submitting.
// ============================================================

const ENG_TICKET_TYPE_BADGE = {
    ENGSUPPORTTICKET: { label: 'ENG', cls: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' },
    BSSUPPORTTICKET: { label: 'BS', cls: 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300' },
    FOSUPPORTTICKET: { label: 'FO', cls: 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' },
    BA_ENG: { label: 'BA ENG', cls: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' },
    BA_BS: { label: 'BA BS', cls: 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' },
    BA_FO: { label: 'BA FO', cls: 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' },
};

function getEngTicketTypeBadge(ticketType) {
    return ENG_TICKET_TYPE_BADGE[ticketType] || {
        label: ticketType || '-',
        cls: 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    };
}

const EngTicketApprovalPanel = {

    state: {
        tickets: [],
    },

    init() {
        if (!document.getElementById('ticketApprovalBody')) {
            return;
        }

        EngTicketApprovalPanel.load();
    },

    async load() {
        try {
            const response = await $.getJSON(window.ticketRoutes.pendingApprovalJson);
            EngTicketApprovalPanel.state.tickets = Array.isArray(response.data) ? response.data : [];
            EngTicketApprovalPanel.render();
        } catch (err) {
            console.error('[EngTicketApprovalPanel] load error:', err);
            EngTicketApprovalPanel.renderError();
        }
    },

    refresh() {
        EngTicketApprovalPanel.load();
    },

    render() {
        const $body = $('#ticketApprovalBody');
        if (!$body.length) return;

        const tickets = EngTicketApprovalPanel.state.tickets;

        $('#ticketApprovalCount').text(tickets.length);

        EngTicketApprovalPanel.updateVisibility();

        if (!tickets.length) {
            $body.html(`
                <div class="flex flex-col items-center justify-center gap-2 py-10 text-gray-400">
                    <i class="fa-solid fa-circle-check text-2xl"></i>
                    <span class="text-xs">Nothing pending your approval.</span>
                </div>`);
            return;
        }

        $body.html(tickets.map(EngTicketApprovalPanel.renderItem).join(''));
    },

    // Panel only shows in Calendar view, and only when there's something to approve.
    // No pending approvals -> panel hides and the calendar takes the full width.
    updateVisibility() {
        const $panel = $('#ticketApprovalPanel');
        if (!$panel.length) return;

        const calendarViewActive = !$('#ticketCalendarWrapper').hasClass('hidden');
        const hasPendingApprovals = EngTicketApprovalPanel.state.tickets.length > 0;

        $panel.toggleClass('hidden', !(calendarViewActive && hasPendingApprovals));
    },

    renderError() {
        const $body = $('#ticketApprovalBody');
        if (!$body.length) return;

        $body.html(`
            <div class="flex flex-col items-center justify-center gap-2 py-10 text-gray-400">
                <i class="fa-solid fa-triangle-exclamation text-2xl text-red-400"></i>
                <span class="text-xs text-red-400">Failed to load approvals.</span>
            </div>`);
    },

    renderItem(ticket) {
        const typeBadge = getEngTicketTypeBadge(ticket.ticket_type);

        const priorityCls = typeof priorityBadgeClass === 'function'
            ? priorityBadgeClass(ticket.ticket_priority)
            : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300';

        return `
            <div class="group rounded-xl border border-slate-200 bg-white p-3.5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-300 hover:shadow-md dark:border-white/10 dark:bg-white/[0.02] dark:hover:border-amber-500/30">

                <div class="flex items-start justify-between gap-2">
                    <div class="flex min-w-0 items-center gap-1.5">
                        <span class="inline-flex shrink-0 rounded-md px-1.5 py-0.5 text-[10px] font-bold ${typeBadge.cls}">
                            ${typeBadge.label}
                        </span>
                        <span class="truncate text-xs font-semibold text-slate-800 dark:text-slate-100">
                            ${ticket.ticketid}
                        </span>
                    </div>
                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold ${priorityCls}">
                        ${ticket.ticket_priority || '-'}
                    </span>
                </div>

                <div class="mt-1.5 truncate text-xs text-slate-500 dark:text-slate-400">
                    ${ticket.issue_summary || '-'}
                </div>

                <div class="mt-2.5 flex items-center gap-3 text-[11px] text-slate-400 dark:text-slate-500">
                    <span class="inline-flex items-center gap-1 truncate">
                        <i class="fa-regular fa-calendar"></i>${formatDate(ticket.ticketdate)}
                    </span>
                    <span class="inline-flex min-w-0 items-center gap-1 truncate">
                        <i class="fa-regular fa-user"></i>${ticket.created_by || '-'}
                    </span>
                </div>

                <div class="mt-3 flex items-center gap-2 border-t border-slate-100 pt-3 dark:border-white/[0.06]">
                    <button type="button"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-green-500 active:scale-95"
                        onclick="approveTicket('${ticket.eid}')">
                        <i class="fa-solid fa-check text-[11px]"></i>
                        Approve
                    </button>
                    <button type="button"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-500 active:scale-95"
                        onclick="rejectTicket('${ticket.eid}')">
                        <i class="fa-solid fa-xmark text-[11px]"></i>
                        Reject
                    </button>
                </div>

            </div>`;
    },
};
