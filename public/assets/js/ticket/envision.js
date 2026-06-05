// assets/js/ticket/envision-ticket.js

window.Ticket = window.Ticket || {};
Ticket.state = Ticket.state || {};

Ticket.state.envisionAttachments = [];

function initEnvisionTicket() {

    $(document).on(
        'submit',
        '#envisionTicketForm',
        function (e) {

            e.preventDefault();

            submitEnvisionTicket();
        }
    );

    $(document)

    .off(
        'change.envision',
        '#envision_attachments'
    )

    .on(
        'change.envision',
        '#envision_attachments',

        function (e) {

            const files =
                Array.from(
                    e.target.files
                );

            files.forEach(file => {

                const key =
                    file.name + '_' + file.size;

                const exists =
                    Ticket.state.envisionAttachments
                        .find(f =>
                            (f.name + '_' + f.size) === key
                        );

                if (!exists) {

                    Ticket.state.envisionAttachments
                        .push(file);
                }
            });

            renderEnvisionAttachment();

            $(this).val('');
        }
    );

    $(document).on(
        'change',
        '#envision_use_schedule',
        function () {

            if ($(this).is(':checked')) {

                $('#envision_schedule_container')
                    .removeClass('hidden');

            } else {

                $('#envision_schedule_container')
                    .addClass('hidden');

                $('#envision_working_start_date')
                    .val('');

                $('#envision_working_end_date')
                    .val('');
            }
        }
    );
}

function initEnvisionDescrEditor() {
    if (window.envisionDescr) return;
    window.envisionDescr = new Quill('#envision_descr_editor', {
        theme: 'snow',
        placeholder: 'Write envision description...',
        modules: { toolbar: [['bold', 'italic', 'underline'], [{ list: 'ordered' }, { list: 'bullet' }], ['link'], ['clean']] }
    });
}

function openEnvisionTicketModal(eid) {

    if (!eid) {
        return;
    }

    initEnvisionDescrEditor();

    resetEnvisionTicketForm();

    $('#envision_ticket_eid')
        .val(eid);

    openModal(
        '#envisionTicketModal'
    );

    loadEnvisionTicketDetail(eid);
}

function closeEnvisionTicketModal() {

    closeModal(
        '#envisionTicketModal'
    );

    setTimeout(function () {

        resetEnvisionTicketForm();

    }, 200);
}

function resetEnvisionTicketForm() {

    const form =
        $('#envisionTicketForm');

    if (form.length) {

        form[0].reset();
    }

    $('#envision_ticketid')
        .text('-');

    $('#envision_pic_ticket')
        .text('-');

    $('#envision_response_summary')
        .val('');

    if (window.envisionDescr) { window.envisionDescr.setText(''); }

    $('#envision_working_start_date')
        .val('');

    $('#envision_working_end_date')
        .val('');

    $('#envision_schedule_container')
        .addClass('hidden');

    $('#envision_use_schedule')
        .prop('checked', false);

    Ticket.state.envisionAttachments = [];

    $('#envision_existing_attachment_list')
        .empty();

    $('#envision_new_attachment_list')
        .empty();

    $('#btnSubmitEnvisionTicket')
        .prop('disabled', false);
}

function loadEnvisionTicketDetail(eid) {

    $.ajax({

        url:
            window.ticketRoutes.detail
                .replace(':eid', eid),

        type: 'GET',

        success(res) {

            populateEnvisionTicket(
                res.data.ticket
            );

            if (
                res.data.attachments
                &&
                res.data.attachments.length
            ) {

                renderExistingEnvisionAttachments(
                    res.data.attachments
                );
            }
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function populateEnvisionTicket(ticket) {

    if (!ticket) {
        return;
    }

    $('#envision_ticketid')
        .text(
            ticket.ticketid || '-'
        );

    $('#envision_pic_ticket')
        .text(
            ticket.pic_ticket || '-'
        );

    $('#envision_response_summary')
        .val(
            ticket.response_summary ||
            'Ticket Envision'
        );

    if (
        ticket.working_start_date
    ) {

        $('#envision_use_schedule')
            .prop('checked', true);

        $('#envision_schedule_container')
            .removeClass('hidden');

        $('#envision_working_start_date')
            .val(
                formatInputDateTime(
                    ticket.working_start_date
                )
            );
    }

    if (
        ticket.working_end_date
    ) {

        $('#envision_working_end_date')
            .val(
                formatInputDateTime(
                    ticket.working_end_date
                )
            );
    }
}
function submitEnvisionTicket() {

    const summary =
        $('#envision_response_summary')
            .val()
            .trim();

    if (!summary) {

        showError(
            'Response summary is required.'
        );

        return;
    }

    const eid =
        $('#envision_ticket_eid')
            .val();

    const form =
        $('#envisionTicketForm')[0];

    if (window.envisionDescr) { $('#envision_descr').val(window.envisionDescr.root.innerHTML); }

    const formData =
        new FormData(form);

    Ticket.state.envisionAttachments
        .forEach(file => {

            formData.append(
                'attachments[]',
                file
            );
        });

    $.ajax({

        url:
            window.ticketRoutes.envision
                .replace(':eid', eid),

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {

            $('#btnSubmitEnvisionTicket')
                .prop('disabled', true);

            showLoading();
        },

        success(res) {

            hideLoading();

            $('#btnSubmitEnvisionTicket')
                .prop('disabled', false);

            closeEnvisionTicketModal();

            showSuccess(

                res.message ||
                'Ticket envision submitted successfully.'
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

            $('#btnSubmitEnvisionTicket')
                .prop('disabled', false);

            handleAjaxError(xhr);
        }
    });
}
function renderExistingEnvisionAttachments(
    files = []
) {

    const container =
        $('#envision_existing_attachment_list');

    container.empty();

    files.forEach((file) => {

        container.append(`

            <div class="
                flex items-center justify-between gap-3

                rounded-xl

                border border-slate-200
                dark:border-white/[0.06]

                bg-white
                dark:bg-[#111c2d]

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
function renderEnvisionAttachment() {

    const container =
        $('#envision_new_attachment_list');

    container.empty();

    Ticket.state.envisionAttachments
        .forEach((file, index) => {

            container.append(`

                <div class="
                    flex items-center justify-between gap-3

                    rounded-xl

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-white
                    dark:bg-[#111c2d]

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
                            removeEnvisionAttachment(${index})
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
                        "
                    >

                        <i class="fa-solid fa-trash"></i>

                    </button>

                </div>

            `);
        });
}
function removeEnvisionAttachment(index) {

    Ticket.state.envisionAttachments
        .splice(index, 1);

    renderEnvisionAttachment();
}

window.removeEnvisionAttachment =
    removeEnvisionAttachment;


if (
    window.location.pathname.includes(
        '/envisionticket/'
    )
) {

    const eid =
        window.location.pathname
            .split('/')
            .pop();

    openEnvisionTicketModal(eid);
}

window.openEnvisionTicketModal =
    openEnvisionTicketModal;
