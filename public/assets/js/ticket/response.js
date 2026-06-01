window.Ticket = window.Ticket || {};

function openResponseTicketModal(eid) {
    if (!eid) {
        return;
    }

    resetResponseTicketForm();

    $("#response_ticket_eid").val(eid);

    openModal("#responseTicketModal");

    loadResponseTicketDetail(eid);
}

function resetResponseTicketForm() {
    $("#responseTicketForm")[0].reset();

    $("#response_pic")
        .empty()
        .append(
            `
            <option value="">
                Select PIC
            </option>
        `,
        )
        .val(null)
        .trigger("change");

    $("#response_priority")
        .empty()
        .append(
            `
            <option value="">
                Select Priority
            </option>
        `,
        )
        .val(null)
        .trigger("change");

    $("#response_ticket_category").text("-");

    $("#response_ticket_subcategory").text("-");

    $("#response_ticket_location").text("-");

    $("#response_ticket_sublocation").text("-");

    $("#response_use_schedule").prop("checked", false);

    $("#response_schedule_container").addClass("hidden");

    $("#response_working_start_date").val("");

    $("#response_working_end_date").val("");

    $("#btnSubmitResponseTicket").prop("disabled", false);
}

function loadResponseTicketDetail(eid) {
    $.ajax({
        url: window.ticketRoutes.detail.replace(":eid", eid),

        type: "GET",

        success(res) {
            populateResponseTicket(res.data.ticket);
        },

        error(xhr) {
            handleAjaxError(xhr);
        },
    });
}

function populateResponseTicket(ticket) {
    if (!ticket) {
        return;
    }

    $("#response_ticket_category").text(ticket.ticket_category || "-");

    $("#response_ticket_subcategory").text(ticket.ticket_subcategory || "-");

    $("#response_ticket_location").text(ticket.location_name || "-");

    $("#response_ticket_sublocation").text(ticket.sub_location_name || "-");

    if (ticket.ticket_priority && ticket.priority_name) {
        const priorityOption = new Option(
            ticket.priority_name,

            ticket.ticket_priority,

            true,
            true,
        );

        $("#response_priority").append(priorityOption).trigger("change");
    }

    loadResponsePIC(ticket);

    loadResponsePriority(ticket);

    if (ticket.working_start_date || ticket.working_end_date) {
        $("#response_use_schedule").prop("checked", true);

        $("#response_schedule_container").removeClass("hidden");

        if (ticket.working_start_date) {
            $("#response_working_start_date").val(
                formatDateTimeLocal(ticket.working_start_date),
            );
        }

        if (ticket.working_end_date) {
            $("#response_working_end_date").val(
                formatDateTimeLocal(ticket.working_end_date),
            );
        }
    }
}

function loadResponsePIC(ticket) {

    if (
        $('#response_pic')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#response_pic')
            .select2('destroy');

    }

    $('#response_pic')
        .empty()
        .append(`
            <option value="">
                Select PIC
            </option>
        `);

    $.ajax({

        url:
            window.ticketRoutes.picSearch,

        type:
            'GET',

        data: {

            ticket_type:
                ticket.ticket_type,

            ticket_categoryid:
                ticket.ticket_categoryid,

        },

        success(res) {

            const results =
                res.results || [];

            results.forEach(pic => {

                const selected =
                    ticket.pic_ticket === pic.id;

                const option =
                    new Option(
                        pic.text,
                        pic.id,
                        selected,
                        selected
                    );

                $('#response_pic')
                    .append(option);

            });

            $('#response_pic')
                .select2({

                    dropdownParent:
                        $('#responseTicketModal'),

                    width:
                        '100%'

                });

        },

        error(xhr) {

            handleAjaxError(xhr);

        }

    });

}

function loadResponsePriority(ticket) {
    if ($("#response_priority").hasClass("select2-hidden-accessible")) {
        $("#response_priority").select2("destroy");
    }

    $("#response_priority").empty().append(`
            <option value="">
                Select Priority
            </option>
        `);

    $.ajax({
        url: window.ticketRoutes.prioritySearch,

        type: "GET",

        data: {
            ticket_type: ticket.ticket_type,

            ticket_categoryid: ticket.ticket_categoryid,
        },

        success(res) {
            const results = res.results || [];

            results.forEach((priority) => {
                const selected = ticket.ticket_priority === priority.id;

                const option = new Option(
                    priority.text,
                    priority.id,
                    selected,
                    selected,
                );

                $("#response_priority").append(option);
            });

            $("#response_priority").select2({
                dropdownParent: $("#responseTicketModal"),

                width: "100%",
            });
        },

        error(xhr) {
            handleAjaxError(xhr);
        },
    });
}

$(document).on("change", "#response_use_schedule", function () {
    if ($(this).is(":checked")) {
        $("#response_schedule_container").removeClass("hidden");
    } else {
        $("#response_schedule_container").addClass("hidden");

        $("#response_working_start_date").val("");

        $("#response_working_end_date").val("");
    }
});

function submitResponseTicket() {
    const eid = $("#response_ticket_eid").val();

    const formData = new FormData($("#responseTicketForm")[0]);

    $.ajax({
        url: window.ticketRoutes.response.replace(":eid", eid),

        type: "POST",

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {
            $("#btnSubmitResponseTicket").prop("disabled", true);
        },

        success(res) {
            $("#btnSubmitResponseTicket").prop("disabled", false);

            closeModal("#responseTicketModal");

            showSuccess(res.message || "Ticket responded successfully.");

            if (typeof loadTicketDetail === "function") {
                const currentDetailEid = $("#detail_ticket_eid").val();

                if (currentDetailEid) {
                    loadTicketDetail(currentDetailEid);
                }
            }

            if ($.fn.DataTable && $("#ticketTable").length) {
                $("#ticketTable").DataTable().ajax.reload(null, false);
            }

            resetTicketUrl();
        },

        error(xhr) {
            $("#btnSubmitResponseTicket").prop("disabled", false);

            handleAjaxError(xhr);
        },
    });
}

function initResponseTicket() {
    initResponseTicketSelect();

    $(document).on("submit", "#responseTicketForm", function (e) {
        e.preventDefault();

        submitResponseTicket();
    });
}

if (window.location.pathname.includes("/responseticket/")) {
    const eid = window.location.pathname.split("/").pop();

    openResponseTicketModal(eid);
}

window.openResponseTicketModal = openResponseTicketModal;
