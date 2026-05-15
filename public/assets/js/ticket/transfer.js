window.Ticket = window.Ticket || {};

function initTransferTicket() {

    bindSubmitTransferTicket();
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

    $('#transferTicketForm')[0]
        .reset();

    if (
        $('#transfer_ticket_categoryid')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#transfer_ticket_categoryid')
            .select2('destroy');
    }

    if (
        $('#transfer_ticket_subcategoryid')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#transfer_ticket_subcategoryid')
            .select2('destroy');
    }

    if (
        $('#transfer_pic_ticket')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#transfer_pic_ticket')
            .select2('destroy');
    }

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
                res.data
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

    loadTransferSubcategory(ticket);

    loadTransferPIC(ticket);
}

function loadTransferCategory(ticket) {

    $('#transfer_ticket_categoryid')
        .empty();

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

            $('#transfer_ticket_categoryid')
                .append(`
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

                $('#transfer_ticket_categoryid')
                    .append(option);
            });

            $('#transfer_ticket_categoryid')
                .select2({

                    dropdownParent:
                        $('#transferTicketModal'),

                    width:
                        '100%'
                });
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function loadTransferSubcategory(ticket) {

    $('#transfer_ticket_subcategoryid')
        .empty();

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

            $('#transfer_ticket_subcategoryid')
                .append(`
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

                $('#transfer_ticket_subcategoryid')
                    .append(option);
            });

            $('#transfer_ticket_subcategoryid')
                .select2({

                    dropdownParent:
                        $('#transferTicketModal'),

                    width:
                        '100%'
                });
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function loadTransferPIC(ticket) {

    if (
        $('#transfer_pic_ticket')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#transfer_pic_ticket')
            .select2('destroy');
    }

    $('#transfer_pic_ticket')
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

            department_id:
                ticket.department_id,
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

                $('#transfer_pic_ticket')
                    .append(option);
            });

            $('#transfer_pic_ticket')
                .select2({

                    dropdownParent:
                        $('#transferTicketModal'),

                    width:
                        '100%'
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

        $('#transfer_ticket_subcategoryid')
            .empty();

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

                $('#transfer_ticket_subcategoryid')
                    .append(`
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

                    $('#transfer_ticket_subcategoryid')
                        .append(option);
                });

                $('#transfer_ticket_subcategoryid')
                    .trigger('change');

                reloadTransferPIC();
            },

            error(xhr) {

                handleAjaxError(xhr);
            }
        });
    }
);

function reloadTransferPIC() {

    const ticketType =
        $('#transfer_ticket_type').val();

    const categoryid =
        $('#transfer_ticket_categoryid').val();

    if (
        $('#transfer_pic_ticket')
            .hasClass('select2-hidden-accessible')
    ) {

        $('#transfer_pic_ticket')
            .select2('destroy');
    }

    $('#transfer_pic_ticket')
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

                const option =
                    new Option(

                        pic.text,

                        pic.id,

                        false,
                        false
                    );

                $('#transfer_pic_ticket')
                    .append(option);
            });

            $('#transfer_pic_ticket')
                .select2({

                    dropdownParent:
                        $('#transferTicketModal'),

                    width:
                        '100%'
                });
        },

        error(xhr) {

            handleAjaxError(xhr);
        }
    });
}

function bindSubmitTransferTicket() {

    $(document).on(
        'submit',
        '#transferTicketForm',
        function (e) {

            e.preventDefault();

            submitTransferTicket();
        }
    );
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
