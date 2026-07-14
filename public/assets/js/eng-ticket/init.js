$(document).ready(function () {

    initTicketViewToggle();

    EngTicketApprovalPanel.init();

    initModal();

    initTicketSelect();

    initEditTicketForm();

    initTicketAttachment();

    initTicketRequestForm();

    initTicketDetailModal();

    initTicketComment();

    initResponseTicket();

    initTransferTicket();

    initProcessTicket();

    initPendingTicket();

    initReopenTicket();

    initCompleteTicket();
});

function initTicketViewToggle() {
    if (!$(".js-toggle-ticket-view").length) {
        return;
    }

    EngTicketCalendar.init();

    $(document).on("click", ".js-toggle-ticket-view", function () {
        $("#ticketCalendarWrapper").toggleClass("hidden");
        $("#ticketTableWrapper").toggleClass("hidden");
        $("#ticketFilterToolbar").toggleClass("hidden");
        $("#ticketStatusFilterRow").toggleClass("hidden");

        if (typeof EngTicketApprovalPanel !== 'undefined') {
            EngTicketApprovalPanel.updateVisibility();
        }

        if (!$("#ticketCalendarWrapper").hasClass("hidden")) {
            EngTicketCalendar.state.calendar?.updateSize();
        }
    });
}
