// assets/js/ticket/reopen.js

window.Ticket = window.Ticket || {};
Ticket.state = Ticket.state || {};

Ticket.state.reopenAttachments = [];

function initReopenTicket() {

    $(document).on(
        'submit',
        '#reopenTicketForm',
        function (e) {

            e.preventDefault();

            submitReopenTicket();
        }
    );

    $(document)

        .off(
            'change.reopen',
            '#reopen_attachments'
        )

        .on(
            'change.reopen',
            '#reopen_attachments',

            function (e) {

                const files =
                    Array.from(
                        e.target.files
                    );

                files.forEach(file => {

                    const key =
                        file.name + '_' + file.size;

                    const exists =
                        Ticket.state.reopenAttachments
                            .find(f =>
                                (f.name + '_' + f.size) === key
                            );

                    if (!exists) {

                        Ticket.state.reopenAttachments
                            .push(file);
                    }
                });

                renderReopenAttachment();

                $(this).val('');
            }
        );
}

function openReopenTicketModal(eid) {

    if (!eid) {
        return;
    }

    resetReopenTicketForm();

    $('#reopen_ticket_eid')
        .val(eid);

    openModal(
        '#reopenTicketModal'
    );

    loadReopenTicketDetail(eid);
}

function closeReopenTicketModal() {

    closeModal(
        '#reopenTicketModal'
    );

    setTimeout(function () {

        resetReopenTicketForm();

    }, 200);
}

function resetReopenTicketForm() {

    const form =
        $('#reopenTicketForm');

    if (form.length) {

        form[0].reset();
    }

    $('#reopen_ticketid')
        .text('-');

    $('#reopen_pic_ticket')
        .text('-');

    $('#reopen_descr')
        .val('');

    Ticket.state.reopenAttachments = [];

    $('#reopen_existing_attachment_list')
        .empty();

    $('#reopen_new_attachment_list')
        .empty();

    $('#btnSubmitReopenTicket')
        .prop('disabled', false);
}

function loadReopenTicketDetail(eid) {

    $.ajax({

        url:
            window.ticketRoutes.detail
                .replace(':eid', eid),

        type: 'GET',

        success(res) {

            populateReopenTicket(
                res.data.ticket
            );

            if (
                res.data.attachments
                &&
                res.data.attachments.length
            ) {

                renderExistingReopenAttachments(
                    res.data.attachments
                );
            }
        },

        error(xhr) {

            handleAjaxError(xhr);

            closeReopenTicketModal();
        }
    });
}

function populateReopenTicket(ticket) {

    if (!ticket) {
        return;
    }

    $('#reopen_ticketid')
        .text(
            ticket.ticketid || '-'
        );

    $('#reopen_pic_ticket')
        .text(
            ticket.pic_ticket || '-'
        );

    $('#reopen_summary')
        .val(
            'Ticket Reopened'
        );

    $('#reopen_descr')
        .val('');
}

function submitReopenTicket() {

    const eid =
        $('#reopen_ticket_eid')
            .val();

    const form =
        $('#reopenTicketForm')[0];

    const formData =
        new FormData(form);

    Ticket.state.reopenAttachments
        .forEach(file => {

            formData.append(
                'attachments[]',
                file
            );
        });

    $.ajax({

        url:
            window.ticketRoutes.reopen
                .replace(':eid', eid),

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {

            $('#btnSubmitReopenTicket')
                .prop('disabled', true);

            showLoading();
        },

        success(res) {

            hideLoading();

            $('#btnSubmitReopenTicket')
                .prop('disabled', false);

            closeReopenTicketModal();

            showSuccess(

                res.message ||
                'Ticket reopened successfully.'
            );

            if (
                typeof loadTicketDetail === 'function'
            ) {

                const currentDetailEid =
                    $('#detail_ticket_eid')
                        .val();

                if (currentDetailEid) {

                    loadTicketDetail(
                        currentDetailEid
                    );
                }
            }

            if (
                $.fn.DataTable &&
                $('#ticketTable').length
            ) {

                $('#ticketTable')
                    .DataTable()
                    .ajax
                    .reload(
                        null,
                        false
                    );
            }

            resetTicketUrl();
        },

        error(xhr) {

            hideLoading();

            $('#btnSubmitReopenTicket')
                .prop('disabled', false);

            handleAjaxError(xhr);
        }
    });
}

function renderExistingReopenAttachments(
    files = []
) {

    const container =
        $('#reopen_existing_attachment_list');

    container.empty();

    files.forEach((file) => {

        container.append(`

            <div class="
                flex items-center justify-between gap-3

                rounded-xl

                border border-slate-200
                dark:border-white/[0.06]

                bg-white
                dark:bg-[#111827]

                px-4 py-3
            ">

                <div class="min-w-0 flex-1">

                    <div class="
                        truncate

                        text-sm
                        font-medium

                        text-slate-700
                        dark:text-slate-200
                    ">

                        ${file.display_name || file.name}

                    </div>

                </div>

                <a href="${file.url}"
                    target="_blank"
                    class="
                        inline-flex
                        h-9 w-9

                        items-center
                        justify-center

                        rounded-lg

                        text-slate-400

                        transition-all
                        duration-200

                        hover:bg-slate-100
                        hover:text-slate-700

                        dark:hover:bg-white/[0.06]
                        dark:hover:text-white
                    ">

                    <i class="fa-solid fa-arrow-up-right-from-square"></i>

                </a>

            </div>

        `);
    });
}

function renderReopenAttachment() {

    const container =
        $('#reopen_new_attachment_list');

    container.empty();

    Ticket.state.reopenAttachments
        .forEach((file, index) => {

            container.append(`

                <div class="
                    flex items-center justify-between gap-3

                    rounded-xl

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-white
                    dark:bg-[#111827]

                    px-4 py-3
                ">

                    <div class="
                        flex min-w-0 items-center gap-3
                    ">

                        <div class="
                            flex h-10 w-10 shrink-0
                            items-center justify-center

                            rounded-xl

                            bg-slate-100
                            dark:bg-white/[0.06]

                            text-slate-500
                            dark:text-slate-300
                        ">

                            <i class="fa-solid fa-file"></i>

                        </div>

                        <div class="min-w-0">

                            <div class="
                                truncate

                                text-sm
                                font-medium

                                text-slate-700
                                dark:text-slate-200
                            ">

                                ${file.name}

                            </div>

                            <div class="
                                mt-1

                                text-xs

                                text-slate-400
                            ">

                                ${formatFileSize(file.size)}

                            </div>

                        </div>

                    </div>

                    <button
                        type="button"

                        onclick="
                            removeReopenAttachment(${index})
                        "

                        class="
                            inline-flex
                            h-9 w-9

                            items-center
                            justify-center

                            rounded-lg

                            border border-red-200
                            dark:border-red-500/20

                            bg-red-50
                            dark:bg-red-500/10

                            text-red-600
                            dark:text-red-300

                            transition-all
                            duration-200

                            hover:bg-red-100
                            dark:hover:bg-red-500/20
                        "
                    >

                        <i class="fa-solid fa-trash"></i>

                    </button>

                </div>

            `);
        });
}

function removeReopenAttachment(index) {

    Ticket.state.reopenAttachments
        .splice(index, 1);

    renderReopenAttachment();
}

window.removeReopenAttachment =
    removeReopenAttachment;

if (
    window.location.pathname.includes(
        '/reopenticket/'
    )
) {

    const eid =
        window.location.pathname
            .split('/')
            .pop();

    openReopenTicketModal(eid);
}

window.openReopenTicketModal =
    openReopenTicketModal;
