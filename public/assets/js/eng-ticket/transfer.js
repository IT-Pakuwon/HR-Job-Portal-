window.Ticket = window.Ticket || {};

function initTransferTicket() {

    $(document).on(
        'submit',
        '#transferTicketForm',
        function (e) {

            e.preventDefault();

            submitTransferTicket();
        }
    );
}

function openTransferTicketModal(eid) {

    if (!eid) {
        return;
    }

    resetTransferTicketForm();

    $('#transfer_ticket_eid')
        .val(eid);

    openModal(
        '#transferTicketModal'
    );

    loadTransferTicketDetail(eid);
}

function resetTransferTicketForm() {

    const form =
        $('#transferTicketForm');

    if (!form.length) {

        console.error(
            'transferTicketForm not found'
        );

        return;
    }

    form[0].reset();

    [
        '#transfer_ticket_categoryid',
        '#transfer_ticket_subcategoryid',
        '#transfer_pic_ticket'
    ].forEach(selector => {

        if (
            $(selector)
                .hasClass('select2-hidden-accessible')
        ) {

            $(selector)
                .select2('destroy');
        }
    });

    $('#transfer_ticket_categoryid')
        .empty();

    $('#transfer_ticket_subcategoryid')
        .empty();

    $('#transfer_pic_ticket')
        .empty()
        .append(`
            <option value="">
                Select PIC
            </option>
        `);

    $('#transfer_current_category')
        .text('-');

    $('#transfer_current_subcategory')
        .text('-');

    $('#transfer_ticket_type')
        .val('');

    $('#transfer_ticket_type_text')
        .val('');

    $('#transfer_note')
        .val('');

    $('#btnSubmitTransferTicket')
        .prop('disabled', false);
}

function loadTransferTicketDetail(eid) {

    $.ajax({

        url:
            window.ticketRoutes.detail
                .replace(':eid', eid),

        type: 'GET',

        beforeSend() {

            showLoading();
        },

        success(res) {

            hideLoading();

            populateTransferTicket(
                res.data.ticket
            );
        },

        error(xhr) {

            hideLoading();

            handleAjaxError(xhr);
        }
    });
}

function populateTransferTicket(ticket) {

    if (!ticket) {
        return;
    }

    $('#transfer_current_category')
        .text(
            ticket.ticket_category || '-'
        );

    $('#transfer_current_subcategory')
        .text(
            ticket.ticket_subcategory || '-'
        );

    $('#transfer_ticket_type')
        .val(
            ticket.ticket_type || ''
        );

    $('#transfer_ticket_type_text')
        .val(
            ticket.ticket_type || '-'
        );

    loadTransferCategory(ticket);
}

function loadTransferCategory(ticket) {

    const categorySelect =
        $('#transfer_ticket_categoryid');

    if (
        categorySelect.hasClass(
            'select2-hidden-accessible'
        )
    ) {

        categorySelect
            .select2('destroy');
    }

    categorySelect.empty();

    $.ajax({

        url:
            window.ticketRoutes.categorySearch,

        type: 'GET',

        data: {

            ticket_type:
                ticket.ticket_type
        },

        success(res) {

            const results =
                res.results || [];

            categorySelect.append(`
                <option value="">
                    Select Category
                </option>
            `);

            results.forEach(category => {

                const selected =
                    ticket.ticket_categoryid ===
                    category.id;

                const option =
                    new Option(

                        category.text,

                        category.id,

                        selected,
                        selected
                    );

                categorySelect
                    .append(option);
            });

            categorySelect.select2({

                dropdownParent:
                    $('#transferTicketModal'),

                width:
                    '100%'
            });

            loadTransferSubcategory(ticket);
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function loadTransferSubcategory(ticket) {

    const subcategorySelect =
        $('#transfer_ticket_subcategoryid');

    if (
        subcategorySelect.hasClass(
            'select2-hidden-accessible'
        )
    ) {

        subcategorySelect
            .select2('destroy');
    }

    subcategorySelect.empty();

    $.ajax({

        url:
            window.ticketRoutes.subcategorySearch,

        type: 'GET',

        data: {

            ticket_categoryid:
                ticket.ticket_categoryid
        },

        success(res) {

            const results =
                res.results || [];

            subcategorySelect.append(`
                <option value="">
                    Select Sub Category
                </option>
            `);

            results.forEach(subcategory => {

                const selected =
                    ticket.ticket_subcategoryid ===
                    subcategory.id;

                const option =
                    new Option(

                        subcategory.text,

                        subcategory.id,

                        selected,
                        selected
                    );

                subcategorySelect
                    .append(option);
            });

            subcategorySelect.select2({

                dropdownParent:
                    $('#transferTicketModal'),

                width:
                    '100%'
            });

            loadTransferPIC(ticket);
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function loadTransferPIC(ticket) {

    const picSelect =
        $('#transfer_pic_ticket');

    if (
        picSelect.hasClass(
            'select2-hidden-accessible'
        )
    ) {

        picSelect
            .select2('destroy');
    }

    picSelect
        .empty()
        .append(`
            <option value="">
                Select PIC
            </option>
        `);

    $.ajax({

        url:
            window.ticketRoutes.picSearch,

        type: 'GET',

        data: {

            ticket_type:
                ticket.ticket_type,

            ticket_categoryid:
                $('#transfer_ticket_categoryid').val(),
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

                picSelect.append(option);
            });

            picSelect.select2({

                dropdownParent:
                    $('#transferTicketModal'),

                width:
                    '100%',

                allowClear: true,

                placeholder:
                    'Select PIC'
            });
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

$(document).on(
    'change',
    '#transfer_ticket_categoryid',
    function () {

        const categoryid =
            $(this).val();

        const subcategorySelect =
            $('#transfer_ticket_subcategoryid');

        if (
            subcategorySelect.hasClass(
                'select2-hidden-accessible'
            )
        ) {

            subcategorySelect
                .select2('destroy');
        }

        subcategorySelect.empty();

        $.ajax({

            url:
                window.ticketRoutes.subcategorySearch,

            type: 'GET',

            data: {

                ticket_categoryid:
                    categoryid
            },

            success(res) {

                const results =
                    res.results || [];

                subcategorySelect.append(`
                    <option value="">
                        Select Sub Category
                    </option>
                `);

                results.forEach(subcategory => {

                    const option =
                        new Option(

                            subcategory.text,

                            subcategory.id,

                            false,
                            false
                        );

                    subcategorySelect
                        .append(option);
                });

                subcategorySelect.select2({

                    dropdownParent:
                        $('#transferTicketModal'),

                    width:
                        '100%'
                });

                reloadTransferPIC();
            },

            error(xhr) {

                handleAjaxError(xhr);
            }
        });
    }
);

$(document).on(
    'change',
    '#transfer_ticket_subcategoryid',
    function () {

        reloadTransferPIC();
    }
);

function reloadTransferPIC() {

    const currentPIC =
        $('#transfer_pic_ticket').val();

    const ticketType =
        $('#transfer_ticket_type').val();

    const categoryid =
        $('#transfer_ticket_categoryid').val();

    const picSelect =
        $('#transfer_pic_ticket');

    if (
        picSelect.hasClass(
            'select2-hidden-accessible'
        )
    ) {

        picSelect
            .select2('destroy');
    }

    picSelect
        .empty()
        .append(`
            <option value="">
                Select PIC
            </option>
        `);

    $.ajax({

        url:
            window.ticketRoutes.picSearch,

        type: 'GET',

        data: {

            ticket_type:
                ticketType,

            ticket_categoryid:
                categoryid,
        },

        success(res) {

            const results =
                res.results || [];

            results.forEach(pic => {

                const selected =
                    currentPIC === pic.id;

                const option =
                    new Option(

                        pic.text,

                        pic.id,

                        selected,
                        selected
                    );

                picSelect.append(option);
            });

            picSelect.select2({

                dropdownParent:
                    $('#transferTicketModal'),

                width:
                    '100%',

                allowClear: true,

                placeholder:
                    'Select PIC'
            });
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function submitTransferTicket() {

    const eid =
        $('#transfer_ticket_eid').val();

    const formData =
        new FormData(
            $('#transferTicketForm')[0]
        );

    $.ajax({

        url:
            window.ticketRoutes.transfer
                .replace(':eid', eid),

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        beforeSend() {

            $('#btnSubmitTransferTicket')
                .prop('disabled', true);

            showLoading();
        },

        success(res) {

            hideLoading();

            $('#btnSubmitTransferTicket')
                .prop('disabled', false);

            closeModal(
                '#transferTicketModal'
            );

            showSuccess(

                res.message ||
                'Ticket transferred successfully.'
            );

            if (
                typeof loadTicketDetail === 'function'
            ) {

                const currentDetailEid =
                    $('#comment_ticket_id')
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

            $('#btnSubmitTransferTicket')
                .prop('disabled', false);

            handleAjaxError(xhr);
        }
    });
}

if (
    window.location.pathname.includes(
        '/transferticket/'
    )
) {

    const eid =
        window.location.pathname
            .split('/')
            .pop();

    openTransferTicketModal(eid);
}

window.openTransferTicketModal =
    openTransferTicketModal;
