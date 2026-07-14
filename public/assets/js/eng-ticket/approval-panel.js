// ============================================================
// approval-panel.js — Eng Ticket
// Right-side panel for MGROPRTEKNIKACCESS: tickets currently
// awaiting this user's approval. Approve/Reject reuse the
// existing global approveTicket()/rejectTicket() (approval.js).
// ============================================================

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
        const prefix = ticket.ticket_type === 'BSFOSUPPORTTICKET' ? '[BSFO]' : '[ENG]';

        return `
            <div class="rounded-lg border border-slate-200 p-3 dark:border-white/10">

                <div class="flex items-start justify-between gap-2">
                    <span class="truncate text-xs font-semibold text-slate-800 dark:text-slate-100">
                        ${prefix} ${ticket.ticketid}
                    </span>
                    <span class="shrink-0 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
                        ${ticket.ticket_priority || '-'}
                    </span>
                </div>

                <div class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                    ${ticket.issue_summary || '-'}
                </div>

                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                    <span><i class="fa-solid fa-calendar-days mr-1"></i>${formatDate(ticket.ticketdate)}</span>
                    <span><i class="fa-solid fa-user mr-1"></i>${ticket.created_by || '-'}</span>
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <button type="button"
                        class="flex-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-500"
                        onclick="approveTicket('${ticket.eid}')">
                        Approve
                    </button>
                    <button type="button"
                        class="flex-1 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-red-500"
                        onclick="rejectTicket('${ticket.eid}')">
                        Reject
                    </button>
                </div>

            </div>`;
    },
};
