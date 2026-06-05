window.Ticket = window.Ticket || {};

Ticket.state = Ticket.state || {};

Ticket.state.pendingAttachments = [];

function initPendingTicket() {

    bindSubmitPendingTicket();

    bindPendingSchedule();

    bindPendingAttachment();
}

function initPendingDescrEditor() {
    if (window.pendingDescr) return;
    window.pendingDescr = new Quill('#pending_descr_editor', {
        theme: 'snow',
        placeholder: 'Write pending reason...',
        modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] }
    });
}

function openPendingTicketModal(eid) {

    if (!eid) {
        return;
    }

    initPendingDescrEditor();

    resetPendingTicketForm();

    $('#pending_ticket_eid')
        .val(eid);

    openModal(
        '#pendingTicketModal'
    );

    loadPendingTicketDetail(eid);
}

function resetPendingTicketForm() {

    const form =
        $('#pendingTicketForm');

    if (!form.length) {
        return;
    }

    form[0].reset();

    Ticket.state.pendingAttachments = [];

    $('#pending_existing_attachment_list')
        .empty();

    $('#pending_new_attachment_list')
        .empty();

    renderPendingAttachment();

    $('#pending_ticketid')
        .text('-');

    $('#pending_pic_ticket')
        .text('-');

    $('#pending_ticket_category')
        .text('-');

    $('#pending_ticket_sla')
        .text('-');

    if (window.pendingDescr) { window.pendingDescr.setText(''); }

    $('#pending_use_schedule')
        .prop('checked', false);

    $('#pending_schedule_container')
        .addClass('hidden');

    $('#pending_working_start_date')
        .val('');

    $('#pending_working_end_date')
        .val('');

    $('#btnSubmitPendingTicket')
        .prop('disabled', false);
}

function loadPendingTicketDetail(eid) {

    $.ajax({

        url:
            window.ticketRoutes.detail
                .replace(':eid', eid),

        type: 'GET',

        success(res) {

            populatePendingTicket(
                res.data.ticket
            );

            if (
                res.data.attachments
                &&
                res.data.attachments.length
            ) {

                renderExistingPendingAttachments(
                    res.data.attachments
                );
            }
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function populatePendingTicket(ticket) {

    if (!ticket) {
        return;
    }

    $('#pending_ticketid')
        .text(
            ticket.ticketid || '-'
        );

    $('#pending_pic_ticket')
        .text(
            ticket.pic_ticket || '-'
        );

    $('#pending_ticket_category')
        .text(
            `${ticket.ticket_category || '-'} / ${ticket.ticket_subcategory || '-'}`
        );

    $('#pending_ticket_sla')
        .text(
            ticket.ticket_duedate
                ? formatDateTime(ticket.ticket_duedate)
                : '-'
        );

    if (
        ticket.working_start_date ||
        ticket.working_end_date
    ) {

        $('#pending_use_schedule')
            .prop('checked', true);

        $('#pending_schedule_container')
            .removeClass('hidden');

        if (ticket.working_start_date) {

            $('#pending_working_start_date')
                .val(
                    formatDateTimeLocal(
                        ticket.working_start_date
                    )
                );
        }

        if (ticket.working_end_date) {

            $('#pending_working_end_date')
                .val(
                    formatDateTimeLocal(
                        ticket.working_end_date
                    )
                );
        }
    }
}

function bindPendingSchedule() {

    $(document).on(
        'change',
        '#pending_use_schedule',
        function () {

            if ($(this).is(':checked')) {

                $('#pending_schedule_container')
                    .removeClass('hidden');

            } else {

                $('#pending_schedule_container')
                    .addClass('hidden');

                $('#pending_working_start_date')
                    .val('');

                $('#pending_working_end_date')
                    .val('');
            }
        }
    );
}

function bindPendingAttachment() {

    $(document).on(
        'change',
        '#pending_attachments',
        function (e) {

            const files =
                Array.from(
                    e.target.files
                );

            files.forEach(file => {

                Ticket.state.pendingAttachments
                    .push(file);
            });

            renderPendingAttachment();

            $(this).val('');
        }
    );
}
function renderExistingPendingAttachments(
    files = []
) {

    const container =
        $('#pending_existing_attachment_list');

    container.empty();

    files.forEach((file) => {

        container.append(`

            <div class="
                flex items-center justify-between gap-3

                rounded-xl

                border border-slate-200
                dark:border-white/[0.06]

                bg-white
                dark:bg-white/[0.03]

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
function renderPendingAttachment() {

    let html = '';

    Ticket.state.pendingAttachments
        .forEach((file, index) => {

            html += `

                <div
                    class="
                        flex items-center justify-between gap-3

                        rounded-xl

                        border border-slate-200
                        dark:border-white/[0.06]

                        bg-white
                        dark:bg-white/[0.03]

                        px-4 py-3
                    "
                >

                    <div class="min-w-0 flex-1">

                        <div
                            class="
                                truncate

                                text-sm
                                font-medium

                                text-slate-700
                                dark:text-slate-200
                            "
                        >

                            ${file.name}

                        </div>

                        <div
                            class="
                                mt-1

                                text-xs

                                text-slate-400
                                dark:text-slate-500
                            "
                        >

                            ${formatFileSize(file.size)}

                        </div>

                    </div>

                    <button
                        type="button"

                        onclick="
                            removePendingAttachment(${index})
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

            `;
        });

        $('#pending_new_attachment_list')
            .html(html);
}

function removePendingAttachment(index) {

    Ticket.state.pendingAttachments
        .splice(index, 1);

    renderPendingAttachment();
}

function bindSubmitPendingTicket() {

    $(document).on(
        'submit',
        '#pendingTicketForm',
        function (e) {

            e.preventDefault();

            submitPendingTicket();
        }
    );
}

function submitPendingTicket() {

    const eid =
        $('#pending_ticket_eid').val();

    if (window.pendingDescr) { $('#pending_response_descr').val(window.pendingDescr.root.innerHTML); }

    const formData =
        new FormData(
            $('#pendingTicketForm')[0]
        );

    Ticket.state.pendingAttachments
        .forEach(file => {

            formData.append(
                'attachments[]',
                file
            );
        });

    $.ajax({

        url:
            window.ticketRoutes.pending
                .replace(':eid', eid),

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {

            $('#btnSubmitPendingTicket')
                .prop('disabled', true);
        },

        success(res) {

            $('#btnSubmitPendingTicket')
                .prop('disabled', false);

            closeModal(
                '#pendingTicketModal'
            );

            showSuccess(

                res.message ||
                'Ticket pending updated successfully.'
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

            $('#btnSubmitPendingTicket')
                .prop('disabled', false);

            handleAjaxError(xhr);
        }
    });
}

if (
    window.location.pathname.includes(
        '/pendingticket/'
    )
) {

    const eid =
        window.location.pathname
            .split('/')
            .pop();

    openPendingTicketModal(eid);
}

window.openPendingTicketModal =
    openPendingTicketModal;

window.removePendingAttachment =
    removePendingAttachment;
