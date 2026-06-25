// assets/js/ticket/detail-modal.js

window.Ticket = window.Ticket || {};

function initTicketDetailModal() {
    bindOpenTicketDetail();

    bindCloseTicketDetail();

    bindTicketDetailTabs();

    bindExpandableContent();

    bindPrintTicket();

    autoOpenTicketDetail();
}

function bindPrintTicket() {
    $(document).on('click', '#btn_print_ticket', function () {
        const eid = $('#comment_ticket_id').val();

        if (!eid) {
            return;
        }

        const url = (window.ticketRoutes.print || '').replace(':eid', eid);

        window.open(url, '_blank');
    });
}

function bindOpenTicketDetail() {
    $(document).on("click", ".btn-show-ticket", function () {
        const eid = $(this).data("id");

        if (!eid) {
            return;
        }

        openTicketDetailModal(eid);
    });
}

function bindCloseTicketDetail() {
    $(document).on("click", ".ticket-detail-close", function () {
        closeTicketDetailModal();
    });
}

function openTicketDetailModal(eid) {
    resetTicketDetailModal();

    openModal("#ticketDetailModal");

    const url = `/showticket/${eid}`;

    if (window.location.pathname !== url) {
        window.history.pushState({}, "", url);
    }

    loadTicketDetail(eid);
}

function closeTicketDetailModal() {
    closeModal("#ticketDetailModal");

    resetTicketUrl();

    setTimeout(function () {
        resetTicketDetailModal();
    }, 240);
}
function autoOpenTicketDetail() {
    const path = window.location.pathname;

    if (path.includes("/showticket/")) {
        const eid = path.split("/showticket/")[1];

        if (!eid) {
            return;
        }

        openTicketDetailModal(eid);
    }
}

function loadTicketDetail(eid) {
    $.ajax({
        url: `/ticket/detail/${eid}`,

        type: "GET",

        success: function (response) {
            const data = response.data || {};

            renderTicketInformation(data.ticket || {});

            renderTicketActions(data.ticket || {});

            renderTicketTimeline(data.tracking || []);

            renderTicketComments(data.comments || []);

            renderTicketAttachments(data.attachments || []);

            renderAttachmentTabPanel(data.attachments || []);
        },

        error: function (xhr) {
            showError(xhr.responseJSON?.message || "Failed load ticket detail");

            closeTicketDetailModal();
        },
    });
}
function renderTicketInformation(ticket) {
    $("#comment_ticket_id").val(ticket.eid);

    $("#detail_ticketid").text(ticket.ticketid || "-");

    $("#detail_issue_summary").text(ticket.issue_summary || "-");

    $("#detail_status_badge").html(renderTicketStatusBadge(ticket.status));

    $("#detail_requester").text(ticket.created_by || "-");

    $("#detail_ticketdate").html(formatDateTime(ticket.ticketdate));

    $("#detail_type").text(
        ticket.ticket_type_name || ticket.ticket_type || "-",
    );

    $("#detail_category").html(`

            <div class="
                text-sm
                font-medium
                text-gray-800
                dark:text-white
            ">

                ${ticket.ticket_category || "-"}

                ${
                    ticket.ticket_subcategory
                        ? `
                            <span class="
                                mx-1
                                text-gray-400
                            ">
                                -
                            </span>

                            ${ticket.ticket_subcategory}
                        `
                        : ""
                }

            </div>

        `);

    $("#detail_pic").html(
        ticket.pic_ticket
            ? `
                    <div class="
                        text-sm
                        font-medium
                        text-gray-800
                        dark:text-white
                    ">
                        ${ticket.pic_ticket}
                    </div>
                `
            : `
                    <span class="
                        text-sm
                        italic
                        text-gray-400
                    ">
                        Not assigned yet
                    </span>
                `,
    );

    $("#detail_department").text(ticket.department_id || "-");

    $("#detail_company").text(ticket.cpny_id || "-");

    $("#detail_priority").html(`

            <span class="
                inline-flex
                items-center
                rounded-lg
                px-2.5 py-1
                text-[11px]
                font-semibold
                ${priorityBadgeClass(ticket.priority_name)}
            ">

                ${ticket.priority_name || "-"}

            </span>

        `);

    $("#detail_sla").html(
        ticket.ticket_duedate
            ? `

                    <div class="
                        flex
                        flex-col
                        leading-tight
                    ">

                        <span class="
                            text-sm
                            font-medium
                            text-gray-800
                            dark:text-white
                        ">

                            ${formatDate(ticket.ticket_duedate)}

                        </span>

                        <span class="
                            mt-1
                            text-xs
                            text-gray-400
                        ">

                            ${formatTime(ticket.ticket_duedate)}

                        </span>

                    </div>

                `
            : `

                    <span class="
                        text-sm
                        italic
                        text-gray-400
                    ">

                        Start counting from response

                    </span>

                `,
    );

    $("#detail_issue_descr").html(
        ticket.issue_descr
            ? `<div class="ql-editor" style="padding:0;height:auto;overflow:visible;">${ticket.issue_descr}</div>`
            : "-"
    );

    $("#detail_solution_descr").html(
        ticket.solution_descr
            ? nl2br(ticket.solution_descr)
            : `

                    <span class="
                        italic
                        text-gray-400
                    ">

                        Waiting for response

                    </span>

                `,
    );

    checkExpandableContent("#detail_issue_descr");

    checkExpandableContent("#detail_solution_descr");
}

function renderTicketActions(ticket) {

    const container =
        $("#ticketActionList");

    if (!container.length) {
        return;
    }

    if (!window.isITRole) {
        $('#ticketActionBtn').addClass('hidden');
        return;
    }

    $('#ticketActionBtn').removeClass('hidden');

    const actions =
        buildTicketActions(ticket);

    if (!actions.length) {

        container.html(`

            <div class="
                px-4
                py-10

                text-center
            ">

                <div class="
                    text-sm
                    font-medium

                    text-gray-500
                    dark:text-gray-400
                ">

                    No available action

                </div>

            </div>

        `);

        return;
    }

    container.html(

        actions.map(action => `

            <button
                type="button"

                onclick="
                    $('#ticketActionDropdown')
                        .addClass('hidden');

                    ${action.onclick}
                "

                class="
                    group

                    flex
                    w-full
                    items-center

                    gap-3

                    rounded-lg

                    px-3 py-2.5

                    text-left

                    transition-all
                    duration-200

                    hover:bg-gray-100
                    dark:hover:bg-white/[0.05]

                    ${action.class || 'text-gray-700 dark:text-gray-200'}
                "
            >

                <div class="
                    flex
                    h-8
                    w-8
                    shrink-0
                    items-center
                    justify-center

                    rounded-xl

                    bg-gray-100
                    dark:bg-white/[0.05]

                    text-gray-500

                    transition-all
                    duration-200

                    group-hover:scale-105

                    ${action.class || ''}
                ">

                    <i class="
                        ${action.icon}

                        text-[15px]
                    "></i>

                </div>

                <div class="
                    min-w-0
                    flex-1
                ">

                    <div class="
                        truncate

                        text-[13px]
                        font-medium
                    ">

                        ${action.label}

                    </div>

                </div>

                <i class="
                    ti
                    ti-chevron-right

                    text-[14px]

                    text-gray-300
                    dark:text-gray-600
                "></i>

            </button>

        `).join("")
    );
}


$(document).on(
    'click',
    '#ticketActionBtn',
    function (e) {

        e.stopPropagation();

        $('#ticketActionDropdown')
            .toggleClass('hidden');
    }
);

$(document).on(
    'click',
    function () {

        $('#ticketActionDropdown')
            .addClass('hidden');
    }
);

$(document).on(
    'click',
    '#ticketActionDropdown',
    function (e) {

        e.stopPropagation();
    }
);
function renderTicketAttachments(attachments = []) {
    const container = $("#detail_attachment_list");

    const counter = $("#detail_attachment_count");

    container.empty();

    counter.text(attachments.length);

    if (!attachments.length) {
        container.html(`

            <div class="
                rounded-lg
                border border-dashed border-gray-300

                px-4 py-5

                text-center

                text-sm
                text-gray-400

                dark:border-gray-700
            ">

                No attachment available

            </div>

        `);

        return;
    }

    attachments.forEach(function (file) {
        container.append(`

            <a
                href="${file.url}"
                target="_blank"
                class="
                    flex
                    items-center
                    justify-between
                    gap-3

                    rounded-lg

                    border border-gray-200

                    bg-white

                    px-4 py-3

                    transition-all
                    duration-200

                    hover:bg-gray-50

                    dark:border-gray-700
                    dark:bg-gray-800
                    dark:hover:bg-gray-700/50
                "
            >

                <div class="
                    flex
                    min-w-0
                    items-center
                    gap-3
                ">

                    <div class="
                        flex
                        h-10 w-10
                        shrink-0
                        items-center
                        justify-center

                        rounded-lg

                        bg-gray-100

                        text-gray-500

                        dark:bg-gray-700
                        dark:text-gray-300
                    ">

                        <i class="fa-solid fa-file"></i>

                    </div>

                    <div class="min-w-0">

                        <div class="
                            truncate

                            text-sm
                            font-medium

                            text-gray-700
                            dark:text-gray-200
                        ">

                            ${file.display_name || file.name}

                        </div>

                        <div class="
                            mt-1

                            text-xs

                            text-gray-400
                        ">

                            ${(file.extention || "-").toUpperCase()}
                            •
                            ${formatFileSize(file.size || 0)}

                        </div>

                    </div>

                </div>

                <div class="
                    text-gray-400
                ">

                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                </div>

            </a>

        `);
    });
}

function renderAttachmentTabPanel(attachments = []) {
    const container = $("#ticket_attachment_tab_list");

    container.empty();

    if (!attachments.length) {
        container.html(`
            <div class="rounded-lg border border-dashed border-gray-300 px-4 py-5 text-center text-sm text-gray-400 dark:border-gray-700">
                No attachment available
            </div>
        `);
        return;
    }

    const imageExts = ["jpg", "jpeg", "png"];

    attachments.forEach(function (file) {
        const ext = (file.extention || "").toLowerCase();
        const isImage = imageExts.includes(ext);
        const isPdf = ext === "pdf";

        if (isImage) {
            container.append(`
                <a href="${file.url}" target="_blank" class="block overflow-hidden rounded-xl border border-gray-200 bg-white transition-all duration-200 hover:shadow-md dark:border-gray-700 dark:bg-gray-800">
                    <img src="${file.url}" alt="${file.display_name || file.name}"
                        class="w-full object-cover"
                        style="max-height: 240px;"
                        onerror="this.closest('a').classList.add('broken-img')">
                    <div class="flex items-center justify-between gap-2 px-4 py-2.5">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                ${file.display_name || file.name}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-400">
                                ${ext.toUpperCase()} • ${formatFileSize(file.size || 0)}
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-sm text-gray-400"></i>
                    </div>
                </a>
            `);
        } else if (isPdf) {
            container.append(`
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
                    <div style="height: 240px; position: relative;">
                        <object data="${file.url}" type="application/pdf" class="w-full h-full" style="height: 240px;">
                            <div class="flex h-full items-center justify-center bg-red-50 dark:bg-red-900/20">
                                <i class="fa-solid fa-file-pdf text-5xl text-red-400"></i>
                            </div>
                        </object>
                    </div>
                    <a href="${file.url}" target="_blank" class="flex items-center justify-between gap-2 px-4 py-2.5 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                ${file.display_name || file.name}
                            </div>
                            <div class="mt-0.5 text-xs text-gray-400">
                                PDF • ${formatFileSize(file.size || 0)}
                            </div>
                        </div>
                        <i class="fa-solid fa-arrow-up-right-from-square text-sm text-gray-400"></i>
                    </a>
                </div>
            `);
        } else {
            container.append(`
                <a href="${file.url}" target="_blank"
                    class="flex items-center justify-between gap-3 rounded-lg border border-gray-200 bg-white px-4 py-3 transition-all duration-200 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700/50">
                    <div class="flex min-w-0 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                            <i class="fa-solid fa-file"></i>
                        </div>
                        <div class="min-w-0">
                            <div class="truncate text-sm font-medium text-gray-700 dark:text-gray-200">
                                ${file.display_name || file.name}
                            </div>
                            <div class="mt-1 text-xs text-gray-400">
                                ${ext.toUpperCase()} • ${formatFileSize(file.size || 0)}
                            </div>
                        </div>
                    </div>
                    <i class="fa-solid fa-arrow-up-right-from-square text-gray-400"></i>
                </a>
            `);
        }
    });
}

function renderTicketTimeline(timelines = []) {
    const container = $("#ticketTimeline");

    container.empty();

    if (!timelines.length) {
        container.html(`

            <div class="
                rounded-lg
                border border-dashed border-gray-300

                px-5 py-10

                text-center

                text-sm
                text-gray-400

                dark:border-gray-700
            ">

                No tracking history

            </div>

        `);

        return;
    }

    timelines.forEach(function (item, index) {
        const submittedBy = item.submitted_by || item.created_by || "System";
        const assignedTo  = item.pic && item.pic !== submittedBy ? item.pic : null;

        const date = item.datetime
            ? formatDateTime(item.datetime)
            : item.created_at
              ? formatDateTime(item.created_at)
              : "No timestamp";

        const workflow =
            item.status ||
            item.status_pekerjaan ||
            'CREATED';

        const description =
            workflow === 'ENVISION CHECKED / SOLVED' || workflow === 'CANCEL'
                ? (
                    item.description ||
                    item.response_descr ||
                    ''
                )
                : '';

        // container.append(`

        //     <div class="
        //         relative

        //         pl-8

        //         pb-3
        //     ">

        //         ${
        //             index !== timelines.length - 1
        //                 ? `
        //                     <div class="
        //                         absolute

        //                         left-[11px]
        //                         top-7
        //                         bottom-0

        //                         w-px

        //                         bg-gradient-to-b
        //                         from-blue-500/60
        //                         via-blue-400/20
        //                         to-transparent
        //                     "></div>
        //                 `
        //                 : ''
        //         }

        //         <div class="
        //             absolute
        //             left-0
        //             top-0

        //             flex
        //             h-[22px] w-[22px]
        //             items-center
        //             justify-center

        //             rounded-lg

        //             border border-white/10

        //             bg-gradient-to-br
        //             from-blue-500
        //             via-blue-600
        //             to-indigo-600

        //             text-white

        //             shadow-md
        //             shadow-blue-500/20
        //         ">

        //             <i class="
        //                 fa-solid
        //                 fa-check

        //                 text-[9px]
        //             "></i>

        //         </div>

        //         <div class="
        //             group

        //             relative

        //             overflow-hidden

        //             rounded-lg

        //             border border-gray-200/70
        //             dark:border-white/[0.05]

        //             bg-white/85
        //             dark:bg-[#0f172a]/85

        //             px-3 py-2.5

        //

        //             transition-all
        //             duration-300

        //             hover:-translate-y-[1px]
        //             hover:border-blue-400/30
        //             hover:shadow-lg
        //             hover:shadow-blue-500/10
        //         ">

        //             <div class="
        //                 absolute
        //                 inset-x-0
        //                 top-0

        //                 h-[2px]

        //                 bg-gradient-to-r
        //                 from-blue-500/0
        //                 via-blue-500/40
        //                 to-indigo-500/0

        //                 opacity-0

        //                 transition-all
        //                 duration-300

        //                 group-hover:opacity-100
        //             "></div>

        //             <div class="
        //                 flex
        //                 items-start
        //                 gap-2
        //             ">

        //                 <div class="
        //                     min-w-0
        //                     flex-1
        //                 ">

        //                     <div class="
        //                         flex
        //                         items-center
        //                         gap-2
        //                     ">

        //                         <div class="
        //                             truncate

        //                             text-[12px]
        //                             font-semibold

        //                             text-gray-800
        //                             dark:text-white
        //                         ">

        //                             ${item.title || 'Activity'}

        //                         </div>

        //                         <div class="
        //                             shrink-0
        //                         ">

        //                             ${renderWorkflowBadge(workflow)}

        //                         </div>

        //                     </div>

        //                     <div class="
        //                         mt-0.5

        //                         flex
        //                         flex-wrap
        //                         items-center

        //                         gap-1.5

        //                         text-[10px]

        //                         text-gray-400
        //                         dark:text-gray-500
        //                     ">

        //                         <span class="
        //                             truncate
        //                             max-w-[120px]
        //                         ">
        //                             ${user}
        //                         </span>

        //                         <span class="opacity-40">
        //                             •
        //                         </span>

        //                         <span>
        //                             ${date}
        //                         </span>

        //                     </div>

        //                     ${
        //                         description
        //                             ? `

        //                                 <div class="
        //                                     mt-2

        //                                     rounded-lg

        //                                     border border-gray-100
        //                                     dark:border-white/[0.04]

        //                                     bg-gray-50/70
        //                                     dark:bg-white/[0.03]

        //                                     px-2.5 py-2

        //                                     text-[11px]
        //                                     leading-5

        //                                     text-gray-600
        //                                     dark:text-gray-300
        //                                 ">

        //                                     ${nl2br(description)}

        //                                 </div>

        //                             `
        //                             : ''
        //                     }

        //                 </div>

        //             </div>

        //         </div>

        //     </div>

        // `);

const iconStyle = getTimelineIconStyle(workflow);

container.append(`

    <div class="
        relative

        pl-10

        pb-3
    ">

        ${
            index !== timelines.length - 1
                ? `
                    <div class="
                        absolute

                        left-[15px]
                        top-10
                        bottom-0

                        w-px

                        bg-gray-200

                        dark:bg-white/[0.06]
                    "></div>
                `
                : ''
        }

        <div class="
            absolute
            left-0
            top-1

            flex
            h-8 w-8
            items-center
            justify-center

            rounded-2xl

            ring-[1px]

            shadow-md

            transition-all
            duration-300

            hover:scale-105

            ${iconStyle.wrap}
        ">

            <i class="
                ${iconStyle.icon}

                text-[11px]
            "></i>

        </div>

        <div class="
            group

            relative

            overflow-hidden

            rounded-lg

            border border-gray-200/80
            dark:border-white/[0.05]

            bg-gray-50/20
            dark:bg-[#111827]/90

            px-4 py-2.5
        ">

            <div class="
                absolute
                inset-0

                opacity-0

                transition-all
                duration-500

                group-hover:opacity-100
            ">

                <div class="
                    absolute
                    -right-10
                    -top-10

                    h-24
                    w-24

                    rounded-lg

                    bg-white/40

                    blur-2xl
                "></div>

            </div>

            <div class="
                relative

                flex
                items-start
                justify-between

                gap-3
            ">

                <div class="
                    min-w-0
                    flex-1
                ">

                    <div class="
                        flex
                        items-center

                        gap-2
                    ">

                        <div class="
                            min-w-0
                            flex-1
                        ">

                            <div class="
                                truncate

                                text-[13px]
                                font-semibold

                                text-gray-800
                                dark:text-white
                            ">

                                ${item.title || 'Activity'}

                            </div>

                            <div class="
                                mt-0.5

                                flex
                                flex-wrap
                                items-center

                                gap-1.5

                                text-[10px]

                                text-gray-400
                                dark:text-gray-500
                            ">

                                <span class="truncate max-w-[130px]">
                                    ${submittedBy}
                                </span>

                                ${assignedTo ? `
                                    <i class="fa-solid fa-arrow-right text-[8px] opacity-50"></i>
                                    <span class="truncate max-w-[130px] text-blue-500 dark:text-blue-400 font-medium">
                                        ${assignedTo}
                                    </span>
                                ` : ''}

                                <span class="opacity-40">•</span>

                                <span>
                                    ${date}
                                </span>

                            </div>



                        </div>


                    </div>

                </div>

                <div class="
                    shrink-0
                ">

                    ${renderWorkflowBadge(workflow)}

                </div>

            </div>

                        ${
                                description
                                    ? `
                                        <div class="
                                            mt-3

                                            rounded-lg

                                            border ${workflow === 'CANCEL' ? 'border-red-200 dark:border-red-500/20' : 'border-emerald-200 dark:border-emerald-500/20'}

                                            ${workflow === 'CANCEL' ? 'bg-red-50 dark:bg-red-500/10' : 'bg-emerald-50 dark:bg-emerald-500/10'}

                                            px-3 py-2

                                            text-xs
                                            leading-6

                                            ${workflow === 'CANCEL' ? 'text-red-700 dark:text-red-300' : 'text-emerald-700 dark:text-emerald-300'}
                                        ">

                                            ${nl2br(description)}

                                        </div>
                                    `
                                    : ''
                            }

        </div>

    </div>

`);
    });
}

function getTimelineIconStyle(workflow) {

    switch ((workflow || '').toUpperCase()) {

        case 'CREATED':
            return {
                icon: 'fa-solid fa-plus',
                wrap:
                    `
                        bg-blue-500

                        text-white

                        ring-blue-100
                        dark:ring-blue-500/10

                        shadow-blue-500/20
                    `
            };

        case 'COMMENT':
            return {
                icon: 'fa-solid fa-message',
                wrap:
                    `
                        bg-fuchsia-500

                        text-white

                        ring-fuchsia-100
                        dark:ring-fuchsia-500/10

                        shadow-fuchsia-500/20
                    `
            };

        case 'PROCESS':
            return {
                icon: 'fa-solid fa-gear',
                wrap:
                    `
                        bg-orange-500

                        text-white

                        ring-orange-100
                        dark:ring-orange-500/10

                        shadow-orange-500/20
                    `
            };

        case 'PENDING':
            return {
                icon: 'fa-solid fa-clock',
                wrap:
                    `
                        bg-sky-500

                        text-white

                        ring-sky-100
                        dark:ring-sky-500/10

                        shadow-sky-500/20
                    `
            };

        case 'TRANSFER':
            return {
                icon: 'fa-solid fa-arrow-right-arrow-left',
                wrap:
                    `
                        bg-teal-500

                        text-white

                        ring-teal-100
                        dark:ring-teal-500/10

                        shadow-teal-500/20
                    `
            };

        case 'COMPLETED':
            return {
                icon: 'fa-solid fa-circle-check',
                wrap:
                    `
                        bg-emerald-500

                        text-white

                        ring-emerald-100
                        dark:ring-emerald-500/10

                        shadow-emerald-500/20
                    `
            };

        case 'CANCEL':
            return {
                icon: 'fa-solid fa-ban',
                wrap:
                    `
                        bg-rose-500

                        text-white

                        ring-rose-100
                        dark:ring-rose-500/10

                        shadow-rose-500/20
                    `
            };

        case 'REOPEN':
            return {
                icon: 'fa-solid fa-rotate',
                wrap:
                    `
                        bg-indigo-500

                        text-white

                        ring-indigo-100
                        dark:ring-indigo-500/10

                        shadow-indigo-500/20
                    `
            };

        default:
            return {
                icon: 'fa-solid fa-bolt',
                wrap:
                    `
                        bg-slate-500

                        text-white

                        ring-slate-100
                        dark:ring-slate-500/10

                        shadow-slate-500/20
                    `
            };
    }

}

function renderTicketComments(comments = []) {
    const container = $("#ticket_comment_list");

    container.empty();

    if (!comments.length) {
        container.html(`

            <div class="
                flex
                h-full
                items-center
                justify-center
            ">

                <div class="
                    rounded-lg
                    border border-dashed border-gray-300

                    px-6 py-10

                    text-center

                    dark:border-gray-700
                ">

                    <div class="
                        mx-auto

                        flex
                        h-14 w-14
                        items-center
                        justify-center

                        rounded-lg

                        bg-gray-100

                        dark:bg-gray-800
                    ">

                        <i class="
                            fa-regular
                            fa-comments

                            text-lg
                            text-gray-400
                        "></i>

                    </div>

                    <div class="
                        mt-4

                        text-sm
                        font-medium

                        text-gray-700
                        dark:text-gray-200
                    ">

                        No discussion yet

                    </div>

                    <div class="
                        mt-1

                        text-xs
                        text-gray-400
                    ">

                        Start conversation here

                    </div>

                </div>

            </div>

        `);

        return;
    }

    comments.forEach(function (item) {
        const user = item.username || item.created_by || "System";

        const message = item.message || "-";

        const date = item.created_at
            ? formatDateTime(item.created_at)
            : "No timestamp";

        const initials = user.substring(0, 1).toUpperCase();

        container.append(`

            <div class="
                flex
                items-start
                gap-3
            ">

                <div class="
                    flex
                    h-10 w-10
                    shrink-0
                    items-center
                    justify-center

                    rounded-lg

                    bg-slate-900

                    text-sm
                    font-semibold
                    text-white

                    dark:bg-white
                    dark:text-slate-900
                ">

                    ${initials}

                </div>

                <div class="min-w-0 flex-1">

                    <div class="
                        rounded-lg

                        border border-gray-200

                        bg-white

                        px-4 py-3

                        shadow-sm

                        dark:border-gray-700
                        dark:bg-gray-800
                    ">

                        <div class="
                            flex
                            flex-wrap
                            items-center
                            justify-between
                            gap-2
                        ">

                            <div class="
                                text-sm
                                font-semibold

                                text-gray-800
                                dark:text-white
                            ">

                                ${user}

                            </div>

                            <div class="
                                text-[11px]
                                text-gray-400
                            ">

                                ${date}

                            </div>

                        </div>

                        <div class="

                            whitespace-normal

                            text-sm
                            leading-7

                            text-gray-700
                            dark:text-gray-300
                        ">

                            ${nl2br(message)}

                        </div>

                    </div>

                </div>

            </div>

        `);
    });
}

function bindTicketDetailTabs() {
    $(document).on("click", ".ticket-detail-tab", function () {
        const tab = $(this).data("tab");

        $(".ticket-detail-tab").removeClass("active");

        $(this).addClass("active");

        $(".ticket-tab-content").addClass("hidden");

        if (tab === "tracking") {
            $("#ticket_tracking_panel").removeClass("hidden");
        }

        if (tab === "discussion") {
            $("#ticket_discussion_panel").removeClass("hidden");
        }

        if (tab === "attachments") {
            $("#ticket_attachments_panel").removeClass("hidden");
        }
    });
}

function bindExpandableContent() {
    $(document).on("click", ".ticket-expand-btn", function () {
        const target = $(this).data("target");

        const content = $(target);

        content.toggleClass("expanded");

        if (content.hasClass("expanded")) {
            content.css({
                maxHeight: "unset",
            });

            $(this).text("Show less");
        } else {
            content.css({
                maxHeight: "180px",
            });

            $(this).text("Show more");
        }
    });
}

function checkExpandableContent(selector) {
    const content = $(selector);

    const button = $(`.ticket-expand-btn[data-target="${selector}"]`);

    content.css({
        maxHeight: "180px",
        overflow: "hidden",
    });

    function updateBtn() {
        if (content[0].scrollHeight > 180) {
            button.removeClass("hidden");
        } else {
            button.addClass("hidden");
        }
    }

    updateBtn();

    const imgs = content.find("img");

    if (imgs.length) {
        imgs.on("load error", function () {
            updateBtn();
        });
    }
}

function resetTicketDetailModal() {
    $("#detail_ticketid").text("-");

    $("#detail_issue_summary").text("-");

    $("#detail_status_badge").html("");

    $("#detail_requester").text("-");

    $("#detail_ticketdate").text("-");

    $("#detail_type").text("-");

    $("#detail_category").html("-");

    $("#detail_pic").html("-");

    $("#detail_department").text("-");

    $("#detail_company").text("-");

    $("#detail_priority").html("-");

    $("#detail_sla").html("-");

    $("#detail_issue_descr").html("-");

    $("#detail_solution_descr").html("-");

    $("#detail_attachment_count").text("0");

    $("#detail_attachment_list").empty();

    $("#ticketTimeline").empty();
}

function nl2br(text) {
    if (!text) {
        return "-";
    }

    return text.replace(/\n/g, "<br>");
}
