window.Ticket = window.Ticket || {};
Ticket.state = Ticket.state || {};
Ticket.state.processAttachments = [];

function initProcessTicket() {
    bindSubmitProcessTicket();
    bindProcessSchedule();
    bindProcessCompany();
    bindProcessAttachment();
}

function initProcessDescrEditor() {
    if (window.processDescr) return;
    window.processDescr = new Quill('#process_descr_editor', {
        theme: 'snow',
        placeholder: 'Write process description...',
        modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] }
    });
}

function openProcessTicketModal(eid) {
    if (!eid) return;
    initProcessDescrEditor();
    resetProcessTicketForm();
    $("#process_ticket_eid").val(eid);
    openModal("#processTicketModal");
    loadProcessTicketDetail(eid);
}

function resetProcessTicketForm() {
    const form = $("#processTicketForm");
    if (!form.length) return;

    form[0].reset();
    Ticket.state.processAttachments = [];

    $("#process_existing_attachment_list").empty();
    $("#process_new_attachment_list").empty();
    $("#process_ticketid").text("-");
    $("#process_pic_ticket").text("-");
    $("#process_cpny_info").text("-");
    $("#process_department_info").text("-");
    $("#process_ticket_category").text("-");
    $("#process_ticket_sla").text("-");
    if (window.processDescr) { window.processDescr.setText(''); }
    $("#process_use_schedule").prop("checked", false);
    $("#process_schedule_container").addClass("hidden");
    $("#process_working_start_date").val("");
    $("#process_working_end_date").val("");
    $("#process_use_company").prop("checked", false);
    $("#process_company_container").addClass("hidden");
    $("#process_cpny_id").val("").trigger("change");
    $("#btnSubmitProcessTicket").prop("disabled", false);
}

function loadProcessTicketDetail(eid) {
    $.ajax({
        url: window.ticketRoutes.detail.replace(":eid", eid),
        type: "GET",
        success(res) {
            populateProcessTicket(res.data.ticket || {});
            if (res.data.attachments?.length) {
                renderExistingProcessAttachments(res.data.attachments);
            }
        },
        error(xhr) {
            handleAjaxError(xhr);
        },
    });
}

function populateProcessTicket(ticket) {
    if (!ticket) return;

    $("#process_ticketid").text(ticket.ticketid || "-");
    $("#process_pic_ticket").text(ticket.pic_ticket || "-");
    $("#process_cpny_info").text(ticket.cpny_id || "-");
    $("#process_department_info").text(ticket.department_id || "-");
    $("#process_ticket_category").text(
        `${ticket.ticket_category || "-"} / ${ticket.ticket_subcategory || "-"}`,
    );
    $("#process_ticket_sla").text(
        ticket.ticket_duedate ? formatDateTime(ticket.ticket_duedate) : "-",
    );

    if (ticket.working_start_date || ticket.working_end_date) {
        $("#process_use_schedule").prop("checked", true);
        $("#process_schedule_container").removeClass("hidden");
        if (ticket.working_start_date)
            $("#process_working_start_date").val(
                formatDateTimeLocal(ticket.working_start_date),
            );
        if (ticket.working_end_date)
            $("#process_working_end_date").val(
                formatDateTimeLocal(ticket.working_end_date),
            );
    }

    if (window.processDescr) { window.processDescr.clipboard.dangerouslyPasteHTML(ticket.response_descr || ''); }
}

function bindProcessSchedule() {
    $(document).on("change", "#process_use_schedule", function () {
        if ($(this).is(":checked")) {
            $("#process_schedule_container").removeClass("hidden");
        } else {
            $("#process_schedule_container").addClass("hidden");
            $("#process_working_start_date").val("");
            $("#process_working_end_date").val("");
        }
    });
}

function bindProcessCompany() {
    $(document).on("change", "#process_use_company", function () {
        if ($(this).is(":checked")) {
            $("#process_company_container").removeClass("hidden");
            loadProcessCompanies();
        } else {
            $("#process_company_container").addClass("hidden");
            $("#process_cpny_id").val("").trigger("change");
        }
    });
}

function loadProcessCompanies() {
    const select = $("#process_cpny_id");
    if (select.find("option").length > 1) return;

    $.ajax({
        url: window.ticketRoutes.companiesSearch,
        type: "GET",
        success(res) {
            select.find("option:not(:first)").remove();
            (res.results || []).forEach(function (item) {
                select.append(new Option(item.text, item.id));
            });
        },
    });
}

function bindProcessAttachment() {
    $(document)
        .off("change.process", "#process_attachments")
        .on("change.process", "#process_attachments", function (e) {
            const files = Array.from(e.target.files);
            files.forEach((file) => {
                // Deduplicate by name+size
                const key = file.name + "_" + file.size;
                if (
                    !Ticket.state.processAttachments.find(
                        (f) => f.name + "_" + f.size === key,
                    )
                ) {
                    Ticket.state.processAttachments.push(file);
                }
            });
            renderProcessAttachment();
            $(this).val("");
        });
}


function renderProcessAttachment() {
    const container = $("#process_new_attachment_list");
    container.empty();
    Ticket.state.processAttachments.forEach((file, index) => {
        container.append(`
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.03] px-4 py-3">
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">${file.name}</div>
                    <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">${formatFileSize ? formatFileSize(file.size) : ''}</div>
                </div>
                <button type="button" onclick="removeProcessAttachment(${index})"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 dark:border-red-500/20 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-500/20">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
            </div>
        `);
    });
}

function removeProcessAttachment(index) {
    Ticket.state.processAttachments.splice(index, 1);
    renderProcessAttachment();
}

function renderExistingProcessAttachments(files = []) {
    const container = $("#process_existing_attachment_list");
    container.empty();
    files.forEach((file) => {
        const html = `
            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-white/[0.06] bg-white dark:bg-white/[0.03] px-4 py-3">
                <div class="min-w-0 flex-1">
                    <div class="truncate text-sm font-medium text-slate-700 dark:text-slate-200">${file.display_name || file.name}</div>
                </div>
                <a href="${file.url}" target="_blank" class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/[0.06] dark:hover:text-white">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </a>
            </div>
        `;
        container.append(html);
    });
}

function bindSubmitProcessTicket() {
    $(document)
        .off("submit.process", "#processTicketForm")
        .on("submit.process", "#processTicketForm", function (e) {
            e.preventDefault();
            submitProcessTicket();
        });
}

function submitProcessTicket() {
    const eid = $("#process_ticket_eid").val();

    if (window.processDescr) { $('#response_descr').val(window.processDescr.root.innerHTML); }

    const formData = new FormData($("#processTicketForm")[0]);
    Ticket.state.processAttachments.forEach((file) => {
        formData.append("attachments[]", file);
    });

    $("#btnSubmitProcessTicket").prop("disabled", true);

    $.ajax({
        url: window.ticketRoutes.process.replace(":eid", eid),
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend() {
            showLoading();
        },
        success(res) {
            hideLoading();
            $("#btnSubmitProcessTicket").prop("disabled", false);
            closeModal("#processTicketModal");
            showSuccess(res.message || "Ticket processed successfully.");

            // Reload detail modal if open
            if (typeof loadTicketDetail === "function") {
                const currentDetailEid = $("#comment_ticket_id").val();
                if (currentDetailEid) loadTicketDetail(currentDetailEid);
            }

            if ($.fn.DataTable && $("#ticketTable").length) {
                $("#ticketTable").DataTable().ajax.reload(null, false);
            }

            resetTicketUrl();
        },
        error(xhr) {
            hideLoading();
            $("#btnSubmitProcessTicket").prop("disabled", false);
            handleAjaxError(xhr);
        },
    });
}

if (window.location.pathname.includes("/processticket/")) {
    const eid = window.location.pathname.split("/").pop();
    openProcessTicketModal(eid);
}

window.openProcessTicketModal = openProcessTicketModal;
window.removeProcessAttachment = removeProcessAttachment;
