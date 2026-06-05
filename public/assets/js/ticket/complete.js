// assets/js/ticket/complete-ticket.js

window.Ticket = window.Ticket || {};
Ticket.state = Ticket.state || {};

Ticket.state.completeAttachments = [];

function initCompleteTicket() {

    $(document).on(
        'submit',
        '#completeTicketForm',
        function (e) {

            e.preventDefault();

            submitCompleteTicket();
        }
    );

    $(document)
        .off('change.complete_schedule', '#complete_use_schedule')
        .on('change.complete_schedule', '#complete_use_schedule', function () {
            if ($(this).is(':checked')) {
                $('#complete_schedule_container').removeClass('hidden');
            } else {
                $('#complete_schedule_container').addClass('hidden');
                $('#complete_working_start_date').val('');
                $('#complete_working_end_date').val('');
            }
        });

    $(document)

        .off(
            'change.complete',
            '#complete_attachments'
        )

        .on(
            'change.complete',
            '#complete_attachments',

            function (e) {

                const files =
                    Array.from(
                        e.target.files
                    );

                files.forEach(file => {

                    const key =
                        file.name + '_' + file.size;

                    const exists =
                        Ticket.state.completeAttachments
                            .find(f =>
                                (f.name + '_' + f.size) === key
                            );

                    if (!exists) {

                        Ticket.state.completeAttachments
                            .push(file);
                    }
                });

                renderCompleteAttachment();

                $(this).val('');
            }
        );
}

function initCompleteDescrEditor() {
    if (window.completeDescr) return;
    window.completeDescr = new Quill('#complete_solution_editor', {
        theme: 'snow',
        placeholder: 'Write solution description...',
        modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] }
    });
}

function openCompleteTicketModal(eid) {

    if (!eid) {
        return;
    }

    initCompleteDescrEditor();

    resetCompleteTicketForm();

    $('#complete_ticket_eid')
        .val(eid);

    openModal(
        '#completeTicketModal'
    );

    loadCompleteTicketDetail(eid);
}

function closeCompleteTicketModal() {

    closeModal(
        '#completeTicketModal'
    );

    setTimeout(function () {

        resetCompleteTicketForm();

    }, 200);
}

function resetCompleteTicketForm() {

    const form =
        $('#completeTicketForm');

    if (form.length) {

        form[0].reset();
    }

    $('#complete_ticketid')
        .text('-');

    $('#complete_pic_ticket')
        .text('-');

    if (window.completeDescr) { window.completeDescr.setText(''); }

    $('#complete_use_schedule')
        .prop('checked', false);

    $('#complete_schedule_container')
        .addClass('hidden');

    $('#complete_working_start_date').val('');
    $('#complete_working_end_date').val('');

    Ticket.state.completeAttachments = [];

    $('#complete_existing_attachment_list')
        .empty();

    $('#complete_new_attachment_list')
        .empty();

    $('#btnSubmitCompleteTicket')
        .prop('disabled', false);
}

function loadCompleteTicketDetail(eid) {

    $.ajax({

        url:
            window.ticketRoutes.detail
                .replace(':eid', eid),

        type: 'GET',

        success(res) {

            populateCompleteTicket(
                res.data.ticket
            );

            if (
                res.data.attachments
                &&
                res.data.attachments.length
            ) {

                renderExistingCompleteAttachments(
                    res.data.attachments
                );
            }
        },

        error(xhr) {

            handleAjaxError(xhr);

            closeCompleteTicketModal();
        }
    });
}

function populateCompleteTicket(ticket) {

    if (!ticket) {
        return;
    }

    $('#complete_ticketid')
        .text(
            ticket.ticketid || '-'
        );

    $('#complete_pic_ticket')
        .text(
            ticket.pic_ticket || '-'
        );

    $('#complete_summary')
        .val(
            ticket.response_summary ||
            'Ticket Completed'
        );

    if (window.completeDescr) {
        window.completeDescr.clipboard.dangerouslyPasteHTML(
            ticket.serviceorder_action || ticket.solution_descr || ''
        );
    }
}

function submitCompleteTicket() {

    const eid =
        $('#complete_ticket_eid')
            .val();

    const form =
        $('#completeTicketForm')[0];

    if (window.completeDescr) { $('#complete_solution_descr').val(window.completeDescr.root.innerHTML); }

    const formData =
        new FormData(form);

    Ticket.state.completeAttachments
        .forEach(file => {

            formData.append(
                'attachments[]',
                file
            );
        });

    $.ajax({

        url:
            window.ticketRoutes.complete
                .replace(':eid', eid),

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {

            $('#btnSubmitCompleteTicket')
                .prop('disabled', true);

            showLoading();
        },

        success(res) {

            hideLoading();

            $('#btnSubmitCompleteTicket')
                .prop('disabled', false);

            closeCompleteTicketModal();

            showSuccess(

                res.message ||
                'Ticket completed successfully.'
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

            $('#btnSubmitCompleteTicket')
                .prop('disabled', false);

            handleAjaxError(xhr);
        }
    });
}

function renderExistingCompleteAttachments(
    files = []
) {

    const container =
        $('#complete_existing_attachment_list');

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

function renderCompleteAttachment() {

    const container =
        $('#complete_new_attachment_list');

    container.empty();

    Ticket.state.completeAttachments
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
                            removeCompleteAttachment(${index})
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

function removeCompleteAttachment(index) {

    Ticket.state.completeAttachments
        .splice(index, 1);

    renderCompleteAttachment();
}

window.removeCompleteAttachment =
    removeCompleteAttachment;

if (
    window.location.pathname.includes(
        '/completeticket/'
    )
) {

    const eid =
        window.location.pathname
            .split('/')
            .pop();

    openCompleteTicketModal(eid);
}

window.openCompleteTicketModal =
    openCompleteTicketModal;
