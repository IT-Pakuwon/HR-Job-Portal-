let ticketStatusFilter = "";
let ticketSearchTimeout;

const isIT = window.isIT || false;

const ticketTable = $("#ticketTable").DataTable({
    processing: true,

    serverSide: true,

   responsive: {
        details: {
            type: "column",
            target: 0,
        },
    },

    columnDefs: [
        {
            targets: 0,
            orderable: false,
        },
    ],

    autoWidth: false,

    pageLength: 10,

    lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"],
    ],

    lengthChange: true,

    searching: true,

    dom: "lrtip",

    ordering: true,

    order: [[1, "desc"]],

    ajax: {
        url: "/ticket/json",

        data: function (d) {
            d.status = ticketStatusFilter;

            d.search = $("#filter_search").val();

            d.status_filter = $("#filter_status").val();

            d.status_pekerjaan = $("#filter_status_pekerjaan").val();

            d.category_id = $("#filter_category_id").val();

            d.cpny_id = $("#filter_company_id").val();

            d.date_from = $("#filter_date_from").val();

            d.date_to = $("#filter_date_to").val();
        },
    },

    columns: [
{
    data: null,

    defaultContent: "",

    orderable: false,

    searchable: false,

    className:
        "dtr-control px-5 py-4 text-center align-top",

    width: "20px",
},
        {
            data: "ticketid",

            name: "ticketid",

            className:
                "px-5 py-4 whitespace-nowrap align-top",

           render: function (data, type, row) {

    let url =
        `/showticket/${row.eid}`;

    let cls = `
         inline-flex items-center justify-center
                        w-[150px]

                        rounded-lg

                        bg-slate-800
                        dark:bg-slate-100

                        px-3 py-1.5

                        text-sm font-semibold

                        text-white
                        dark:text-slate-900

                        transition-all duration-200

                        hover:bg-slate-700
                        dark:hover:bg-white
    `;

    const canEdit =
        row.status === "P" &&
        row.status_pekerjaan === "CREATED" &&
        row.created_by === window.currentUser;

    if (canEdit) {

        cls = `
            inline-flex
            items-center
            justify-center

            min-w-[150px]

            rounded-lg

            bg-amber-500
            dark:bg-amber-400

           px-3 py-1.5

            text-sm
            font-semibold

            text-white
            dark:text-slate-900

            transition-all
            duration-200

            hover:bg-amber-600
            dark:hover:bg-amber-300
        `;

    }

    return `
        <div class="flex items-center gap-2 whitespace-nowrap">

            ${
                canEdit
                    ? `
                        <button
                            type="button"
                            class="${cls} btn-edit-ticket"
                            data-id="${row.eid}"
                        >
                            ${row.ticketid ?? "-"}
                        </button>
                    `
                    : `
                        <a
                            href="${url}"
                            class="${cls}"
                        >
                            ${row.ticketid ?? "-"}
                        </a>
                    `
            }

            ${
                canEdit
                    ? `
                        <a
                            href="/showticket/${row.eid}"
                            class="
                                inline-flex
                                h-9 w-9

                                items-center
                                justify-center

                                rounded-lg

                                border
                                border-slate-200

                                dark:border-white/[0.06]

                                bg-white
                                dark:bg-white/[0.04]

                                text-slate-600
                                dark:text-slate-300

                                transition-all
                                duration-200

                                hover:bg-slate-100
                                dark:hover:bg-white/[0.08]
                            "
                            title="View Ticket"
                        >

                            <i class="fa-regular fa-eye"></i>

                        </a>

                        <button
                            type="button"
                            class="
                                btn-cancel-ticket

                                inline-flex
                                h-9 w-9

                                items-center
                                justify-center

                                rounded-lg

                                border
                                border-red-200

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
                            data-id="${row.eid}"
                            title="Cancel Ticket"
                        >

                            <i class="fa-solid fa-ban"></i>

                        </button>
                    `
                    : ""
            }

        </div>
    `;


            },

        },
                {
            data: "ticketdate",

            name: "ticketdate",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                return formatDate(data);
            },
        },

        {
            data: "ticket_type",

            name: "ticket_type",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                return `
                        <span class="inline-flex rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            ${data ?? "-"}
                        </span>
                    `;
            },
        },

        {
            data: "ticket_category",

            name: "ticket_category",

            className: "px-5 py-4 align-top",

            render: function (data, type, row) {
                return `
                        <div class="flex flex-col">

                            <span class="font-medium text-gray-900 dark:text-white">
                                ${data ?? "-"}
                            </span>

                            <span class="mt-1 text-[11px] text-gray-400">
                                ${row.ticket_subcategory ?? "-"}
                            </span>

                        </div>
                    `;
            },
        },

        {
            data: "issue_summary",

            name: "issue_summary",

            className: "min-w-[260px] px-5 py-4 align-top",

            render: function (data) {
                return `
                        <div class="max-w-[260px] text-sm leading-relaxed text-gray-700 dark:text-gray-200">
                            ${data ?? "-"}
                        </div>
                    `;
            },
        },



        {
            data: "created_by",

            name: "created_by",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                if (!data) {
                    return `<span class="text-gray-400">-</span>`;
                }

                return `
                    <div class="flex items-center gap-2">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-xs font-semibold uppercase text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                            ${data.charAt(0)}
                        </div>
                        <span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>
                    </div>
                `;
            },
        },

        {
            data: "department_id",

            name: "department_id",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                return data
                    ? `<span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>`
                    : `<span class="text-gray-400">-</span>`;
            },
        },

        {
            data: "cpny_id",

            name: "cpny_id",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                return data
                    ? `<span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>`
                    : `<span class="text-gray-400">-</span>`;
            },
        },
        {
            data: "pic_ticket",

            name: "pic_ticket",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                if (!data) {
                    return `
                            <span class="text-gray-400">
                                -
                            </span>
                        `;
                }

                return `
                        <div class="flex items-center gap-2">

                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-xs font-semibold uppercase text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                ${data.charAt(0)}
                            </div>

                            <span class="text-sm text-gray-700 dark:text-gray-200">
                                ${data}
                            </span>

                        </div>
                    `;
            },
        },
        {
            data: "priority_name",

            name: "priority_name",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data) {
                return `
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${priorityBadgeClass(data)}">
                            ${data ?? "-"}
                        </span>
                    `;
            },
        },

        {
            data: "status",

            name: "status",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data, type, row) {
                return renderTicketStatusBadge(data);
            },
        },

        ...(isIT
            ? [
                  {
                      data: "status_pekerjaan",

                      name: "status_pekerjaan",

                      className: "px-5 py-4 whitespace-nowrap align-top",

                      render: function (data) {
                          return renderWorkflowBadge(data);
                      },
                  },
              ]
            : []),

        {
            data: "ticket_duedate",

            name: "ticket_duedate",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data, type, row) {
                if (!data) {
                    return `
                            <span class="text-gray-400">
                                -
                            </span>
                        `;
                }

                const responseDate = row.response_working_start ?? null;
                const isOverdue = row.status_pekerjaan === "CREATED"
                    ? isOverdueDate(data)
                    : responseDate
                        ? new Date(responseDate) > new Date(data)
                        : false;

                return `
                        <div class="flex flex-col leading-tight">

                            <span class="${
                                isOverdue
                                    ? "font-medium text-red-500 dark:text-red-400"
                                    : "text-gray-700 dark:text-gray-200"
                            }">

                                ${formatDate(data)}

                            </span>

                            <div class="mt-1 flex items-center gap-2">

                                <span class="text-[11px] text-gray-400">
                                    ${formatTime(data)}
                                </span>

                                ${
                                    isOverdue
                                        ? `
                                            <span class="rounded bg-red-100 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                                Late
                                            </span>
                                        `
                                        : `
                                            <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-green-600 dark:bg-green-900/30 dark:text-green-400">
                                                On Time
                                            </span>
                                        `
                                }

                            </div>

                        </div>
                    `;
            },
        },

        {
            data: "actions",

            orderable: false,

            searchable: false,

            className:"px-5 py-3 text-center whitespace-nowrap align-middle",

           render: function (data, type, row) {

                return renderTicketActionDropdown(row);
            }
        },
    ],

    drawCallback: function () {
        refreshStatusCards();

        $("#ticketTable tbody tr").addClass(
            "transition duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/40",
        );

        $(".dataTables_paginate > .pagination").addClass(
            "flex items-center gap-2",
        );

        $(".paginate_button").addClass(
            "rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-600 transition hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700",
        );

        $(".paginate_button.current").addClass(
            "border-black bg-black text-white dark:border-white dark:bg-white dark:text-black",
        );
    },
});

function renderTicketActionDropdown(row) {

    const actions =
        buildTicketActions(row);

    let html = '';

    actions.forEach(action => {

        html += `

            <button
                type="button"

                onclick="${action.onclick}"

                class="
                    flex w-full items-center gap-3

                   px-3 py-1.5

                    text-left text-sm

                    transition

                    hover:bg-slate-100
                    dark:hover:bg-white/[0.06]

                    ${action.class || 'text-slate-700 dark:text-slate-200'}
                "
            >

                <i class="${action.icon} text-base"></i>

                <span>
                    ${action.label}
                </span>

            </button>

        `;
    });

    return `

        <div class="relative inline-block text-left ticket-dropdown">

            <button
                type="button"

                class="
                    ticket-action-btn

                    inline-flex
                    items-center
                    justify-center

                    h-8
                    min-w-[36px]

                    rounded-md

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-slate-50
                    dark:bg-white/[0.03]

                    px-2

                    text-slate-500
                    dark:text-slate-300

                    transition-all
                    duration-150

                    hover:border-slate-300
                    hover:bg-slate-100
                    hover:text-slate-700

                    dark:hover:border-white/[0.12]
                    dark:hover:bg-white/[0.06]
                    dark:hover:text-white
                "
            >

                <i class="ti ti-dots text-[18px]"></i>

            </button>

            <div
                class="
                    ticket-action-menu

                    fixed z-[99999]

                    hidden

                    min-w-[220px]

                    overflow-hidden

                    rounded-xl

                    border border-slate-200
                    dark:border-white/[0.08]

                    bg-white
                    dark:bg-[#0f172a]

                    shadow-xl
                "
            >

                ${html}

            </div>

        </div>

    `;
}

function buildTicketActions(row) {

    if (!window.isITRole) {
        return [];
    }

    const actions = [];

    /*
    |--------------------------------------------------------------------------
    | Response
    |--------------------------------------------------------------------------
    */

   if (
        row.status === 'P' &&
        [
            'CREATED',
            'TRANSFER'
        ].includes(row.status_pekerjaan) &&
        window.isIT
    ) {

        actions.push({

            label: 'Response Ticket',

            icon: 'ti ti-user-check',

            onclick:
                `openResponseTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Process
    |--------------------------------------------------------------------------
    */

    if (
        row.status === 'P' &&
        [
            'RESPONSE',
            'PENDING',
            'REOPEN',
            'ENVISION',
        ].includes(row.status_pekerjaan) &&
        row.pic_ticket === window.currentUser
    ) {

        actions.push({

            label: 'Process Ticket',

            icon: 'ti ti-player-play',

            onclick:
                `openProcessTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Pending
    |--------------------------------------------------------------------------
    */

    if (
        row.status === 'P' &&
        row.status_pekerjaan === 'PROCESS' &&
        row.pic_ticket === window.currentUser
    ) {

        actions.push({

            label: 'Pending Ticket',

            icon: 'ti ti-clock-pause',

            onclick:
                `openPendingTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Transfer
    |--------------------------------------------------------------------------
    */

    if (
        row.status === 'P' &&
        [
            'CREATED',
            'TRANSFER',
            'REOPEN',
            'RESPONSE',
        ].includes(row.status_pekerjaan) &&
        (
            row.pic_ticket === window.currentUser ||
            window.isIT
        )
    ) {

        actions.push({

            label: 'Transfer Ticket',

            icon: 'ti ti-switch-horizontal',

            onclick:
                `openTransferTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Envision
    |--------------------------------------------------------------------------
    */

    if (
        row.status === 'P' &&
        [
            'PROCESS',
            'PENDING',
        ].includes(row.status_pekerjaan)  &&
        row.pic_ticket === window.currentUser
    ) {

        actions.push({

            label: 'Envision Ticket',

            icon: 'ti ti-bulb',

            onclick:
                `openEnvisionTicketModal('${row.eid}')`
        });
    }
    /*
    |--------------------------------------------------------------------------
    | Complete
    |--------------------------------------------------------------------------
    */

    if (
        row.status === 'P' &&
        [
            'PROCESS',
            'PENDING',
            'ENVISION CHECKED / SOLVED',
        ].includes(row.status_pekerjaan) &&
        row.pic_ticket === window.currentUser
    )
    {
        actions.push({
            label: 'Complete Ticket',
            icon: 'ti ti-check',
            onclick: `openCompleteTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reopen
    |--------------------------------------------------------------------------
    */

   if (
            row.status === 'C' &&
            window.isIT
    ){

        actions.push({

            label: 'Reopen Ticket',

            icon: 'ti ti-rotate-clockwise',

            onclick:
                `openReopenTicketModal('${row.eid}')`
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

   if (
        (
            row.created_by === window.currentUser &&
            row.status === 'P' &&
            row.status_pekerjaan === 'CREATED'
        )
        ||
        (
            window.isIT &&
            row.status_pekerjaan !== 'COMPLETED'
        )
    ) {

        actions.push({

            label: 'Cancel Ticket',

            icon: 'ti ti-x',

            class:
                'text-rose-600 dark:text-rose-400',

            onclick:
                `cancelTicket('${row.eid}')`
        });
    }

    return actions;
}

$(document).on(
    'click',
    '.ticket-action-btn',
    function (e) {

        e.stopPropagation();

        const $btn  = $(this);
        const $menu = $btn.siblings('.ticket-action-menu');
        const isHidden = $menu.hasClass('hidden');

        $('.ticket-action-menu').addClass('hidden');

        if (isHidden) {
            const rect      = this.getBoundingClientRect();
            const menuWidth = 220;
            const left      = Math.max(0, rect.right - menuWidth);
            const top       = rect.bottom + 8;

            $menu.css({ top: top + 'px', left: left + 'px' }).removeClass('hidden');
        }
    }
);

$(document).on(
    'click',
    function () {

        $('.ticket-action-menu')
            .addClass('hidden');
    }
);

$(document).on("click", ".ticket-status-filter", function (e) {
    e.preventDefault();

    $(".ticket-status-card").removeClass("active");

    $(this).find(".ticket-status-card").addClass("active");

    ticketStatusFilter = $(this).data("status");

    ticketTable.ajax.reload();
});

$(document).on("keyup", "#filter_search", function () {
    clearTimeout(ticketSearchTimeout);

    ticketSearchTimeout = setTimeout(function () {
        ticketTable.ajax.reload();
    }, 500);
});

$(document).on("change", "#filter_category_id", function () {
    ticketTable.ajax.reload();
});

$(document).on('click', '#btn_apply_filter', function () {
    ticketTable.ajax.reload();
});

$(document).on('click', '#btn_reset_filter', function () {
    $('#filter_search').val('');
    $('#filter_status').val('');
    $('#filter_status_pekerjaan').val('');
    $('#filter_category_id').val('');
    $('#filter_company_id').val('');
    $('#filter_date_from').val('');
    $('#filter_date_to').val('');
    ticketStatusFilter = '';
    $('.ticket-status-card').removeClass('active');
    ticketTable.ajax.reload();
});

function doExportTicket() {
    const params = new URLSearchParams({
        search:      $('#filter_search').val() || '',
        status:      $('#filter_status_pekerjaan').val() || '',
        category_id: $('#filter_category_id').val() || '',
        date_from:   $('#filter_date_from').val() || '',
        date_to:     $('#filter_date_to').val() || '',
    });

    window.open(`/ticket/export?${params.toString()}`, '_blank');
}

$(document).on('click', '#btn_export_ticket', doExportTicket);
$(document).on('click', '#btn_export_filter', doExportTicket);

function refreshStatusCards() {
    if (!window.ticketRoutes?.counts) return;

    $.getJSON(window.ticketRoutes.counts, function (data) {
        $('[data-count]').each(function () {
            const key = $(this).data('count');
            if (data[key] !== undefined) {
                $(this).text(data[key]);
            }
        });
    });
}

window.refreshStatusCards = refreshStatusCards;
