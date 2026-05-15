// assets/js/ticket/detail-modal.js

window.Ticket = window.Ticket || {};

function initTicketDetailModal() {

    bindOpenTicketDetail();

    bindCloseTicketDetail();

    bindTicketDetailTabs();

    bindExpandableContent();

    autoOpenTicketDetail();

}

function bindOpenTicketDetail() {

    $(document).on(
        'click',
        '.btn-show-ticket',
        function () {

            const eid =
                $(this).data('id');

            if (!eid) {
                return;
            }

            openTicketDetailModal(
                eid
            );

        }
    );

}

function bindCloseTicketDetail() {

    $(document).on(
        'click',
        '.ticket-detail-close',
        function () {

            closeTicketDetailModal();

        }
    );

}

function openTicketDetailModal(eid) {

    resetTicketDetailModal();

    openModal(
        '#ticketDetailModal'
    );

    const url =
        `/showticket/${eid}`;

    if (
        window.location.pathname !== url
    ) {

        window.history.pushState(
            {},
            '',
            url
        );

    }

    loadTicketDetail(eid);

}

function closeTicketDetailModal() {

    closeModal(
        '#ticketDetailModal'
    );

    resetTicketUrl();

    setTimeout(function () {

        resetTicketDetailModal();

    }, 240);

}
function autoOpenTicketDetail() {

    const path =
        window.location.pathname;

    if (
        path.includes('/showticket/')
    ) {

        const eid =
            path.split('/showticket/')[1];

        if (!eid) {
            return;
        }

        openTicketDetailModal(
            eid
        );

    }

}

function loadTicketDetail(eid) {

    $.ajax({

        url:
            `/ticket/detail/${eid}`,

        type:
            'GET',

        success: function (response) {

            const ticket =
                response.data.ticket;

            renderTicketInformation(
                ticket
            );

            renderTicketAttachments(
                response.data.attachments || []
            );

            renderTicketTimeline(
                response.data.tracking || []
            );

        },

        error: function (xhr) {

            showError(
                xhr.responseJSON?.message ||
                'Failed load ticket detail'
            );

            closeTicketDetailModal();

        },

    });

}

function renderTicketInformation(ticket) {

    $('#comment_ticket_id').val(
        ticket.eid
    );

    $('#detail_ticketid')
        .text(
            ticket.ticketid || '-'
        );

    $('#detail_issue_summary')
        .text(
            ticket.issue_summary || '-'
        );

    $('#detail_status_badge')
        .html(
            renderTicketStatusBadge(
                ticket.status
            )
        );

    $('#detail_requester')
        .text(
            ticket.created_by || '-'
        );

    $('#detail_ticketdate')
        .html(
            formatDateTime(
                ticket.ticketdate
            )
        );

    $('#detail_type')
        .text(
            ticket.ticket_type_name ||
            ticket.ticket_type ||
            '-'
        );

    $('#detail_category')
        .html(`

            <div class="
                text-sm
                font-medium
                text-gray-800
                dark:text-white
            ">

                ${ticket.ticket_category || '-'}

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
                        : ''
                }

            </div>

        `);

    $('#detail_pic')
        .html(

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
                `
        );

    $('#detail_priority')
        .html(`

            <span class="
                inline-flex
                items-center
                rounded-full
                px-2.5 py-1
                text-[11px]
                font-semibold
                ${priorityBadgeClass(ticket.priority_name)}
            ">

                ${ticket.priority_name || '-'}

            </span>

        `);

    $('#detail_sla')
        .html(

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

                `
        );

    $('#detail_issue_descr')
        .html(
            nl2br(
                ticket.issue_descr || '-'
            )
        );

    $('#detail_solution_descr')
        .html(

            ticket.solution_descr
                ? nl2br(
                    ticket.solution_descr
                )
                : `

                    <span class="
                        italic
                        text-gray-400
                    ">

                        Waiting for response

                    </span>

                `
        );

    checkExpandableContent(
        '#detail_issue_descr'
    );

    checkExpandableContent(
        '#detail_solution_descr'
    );

}
function renderTicketAttachments(
    attachments = []
) {

    const container =
        $('#detail_attachment_list');

    const counter =
        $('#detail_attachment_count');

    container.empty();

    counter.text(
        attachments.length
    );

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

                            ${(file.extention || '-').toUpperCase()}
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

function renderTicketTimeline(
    timelines = []
) {

    const container =
        $('#ticketTimeline');

    container.empty();

    if (!timelines.length) {

        container.html(`

            <div class="
                rounded-xl
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

        const user =
            item.pic ||
            item.created_by ||
            'System';

        const date =
            item.datetime
                ? formatDateTime(item.datetime)
                : (
                    item.created_at
                        ? formatDateTime(item.created_at)
                        : 'No timestamp'
                );

        const description =
            item.description ||
            item.response_descr ||
            '-';

        const workflow =
            item.status ||
            item.status_pekerjaan ||
            'CREATED';

        container.append(`

            <div class="
                relative

                pl-8
            ">

                ${
                    index !== timelines.length - 1
                        ? `
                            <div class="
                                absolute
                                left-[14px]
                                top-10
                                bottom-0

                                w-px

                                bg-gray-200

                                dark:bg-gray-700
                            "></div>
                        `
                        : ''
                }

                <div class="
                    absolute
                    left-0
                    top-1

                    flex
                    h-7 w-7
                    items-center
                    justify-center

                    rounded-full

                    bg-blue-500

                    text-white

                    shadow-md
                ">

                    <i class="
                        fa-solid
                        fa-check
                        text-[10px]
                    "></i>

                </div>

                <div class="
                    rounded-xl

                    border border-gray-200

                    bg-white

                    px-5 py-4

                    shadow-sm

                    transition-all
                    duration-200

                    hover:shadow-md

                    dark:border-gray-700
                    dark:bg-gray-800
                ">

                    <div class="
                        flex
                        items-start
                        justify-between
                        gap-4
                    ">

                        <div class="min-w-0">

                            <div class="
                                text-sm
                                font-semibold

                                text-gray-800
                                dark:text-white
                            ">

                                ${item.title || 'Activity'}

                            </div>

                            <div class="
                                mt-1

                                flex
                                flex-wrap
                                items-center
                                gap-2

                                text-xs
                                text-gray-400
                            ">

                                <span>
                                    ${user}
                                </span>

                                <span>
                                    •
                                </span>

                                <span>
                                    ${date}
                                </span>

                            </div>

                        </div>

                        <div class="shrink-0">

                            ${renderWorkflowBadge(workflow)}

                        </div>

                    </div>

                </div>

            </div>

        `);

    });

}

function renderTicketComments(
    comments = []
) {

    const container =
        $('#ticket_comment_list');

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
                    rounded-xl
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

                        rounded-full

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

        const user =
            item.username ||
            item.created_by ||
            'System';

        const message =
            item.message || '-';

        const date =
            item.created_at
                ? formatDateTime(item.created_at)
                : 'No timestamp';

        const initials =
            user
                .substring(0, 1)
                .toUpperCase();

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

                    rounded-full

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
                        rounded-2xl

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

    $(document).on(
        'click',
        '.ticket-detail-tab',
        function () {

            const tab =
                $(this).data('tab');

            $('.ticket-detail-tab')
                .removeClass('active');

            $(this)
                .addClass('active');

            $('.ticket-tab-content')
                .addClass('hidden');

            if (tab === 'tracking') {

                $('#ticket_tracking_panel')
                    .removeClass('hidden');

            }

            if (tab === 'discussion') {

                $('#ticket_discussion_panel')
                    .removeClass('hidden');

            }

        }
    );

}

function bindExpandableContent() {

    $(document).on(
        'click',
        '.ticket-expand-btn',
        function () {

            const target =
                $(this).data('target');

            const content =
                $(target);

            content.toggleClass(
                'expanded'
            );

            if (
                content.hasClass(
                    'expanded'
                )
            ) {

                content.css({
                    maxHeight: 'unset',
                });

                $(this)
                    .text(
                        'Show less'
                    );

            } else {

                content.css({
                    maxHeight: '180px',
                });

                $(this)
                    .text(
                        'Show more'
                    );

            }

        }
    );

}

function checkExpandableContent(
    selector
) {

    const content =
        $(selector);

    const button =
        $(
            `.ticket-expand-btn[data-target="${selector}"]`
        );

    content.css({
        maxHeight: '180px',
        overflow: 'hidden',
    });

    if (
        content[0].scrollHeight > 180
    ) {

        button.removeClass(
            'hidden'
        );

    } else {

        button.addClass(
            'hidden'
        );

    }

}

function resetTicketDetailModal() {

    $('#detail_ticketid')
        .text('-');

    $('#detail_issue_summary')
        .text('-');

    $('#detail_status_badge')
        .html('');

    $('#detail_requester')
        .text('-');

    $('#detail_ticketdate')
        .text('-');

    $('#detail_type')
        .text('-');

    $('#detail_category')
        .html('-');

    $('#detail_pic')
        .html('-');

    $('#detail_priority')
        .html('-');

    $('#detail_sla')
        .html('-');

    $('#detail_issue_descr')
        .html('-');

    $('#detail_solution_descr')
        .html('-');

    $('#detail_attachment_count')
        .text('0');

    $('#detail_attachment_list')
        .empty();

    $('#ticketTimeline')
        .empty();

}

function nl2br(text) {

    if (!text) {
        return '-';
    }

    return text.replace(
        /\n/g,
        '<br>'
    );

}
