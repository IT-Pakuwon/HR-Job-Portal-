let ticketStatusFilter = "";

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

    dom: '<"dt-toolbar"lf>rtip',

    searchDelay: 500,

    language: {
        search: "",
        searchPlaceholder: "Ticket / Summary / PIC",
    },

    ordering: true,

    order: [[1, "desc"]],

    ajax: {
        url: "/ticket/json",

        data: function (d) {
            d.status = ticketStatusFilter;

            d.search = d.search.value;

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
        "dtr-control px-3 py-4 text-center align-middle",

    width: "20px",
},
        {
            data: "ticketid",

            name: "ticketid",

            className: "px-3 py-3 whitespace-nowrap align-middle",

            render: function (data, type, row) {
                const url = `/showticket/${row.eid}`;
                const text = row.ticketid ?? "-";
                const canEdit =
                    row.status === "P" &&
                    row.status_pekerjaan === "CREATED" &&
                    row.created_by === window.currentUser;

                if (!canEdit) {
                    return `<div class="flex items-center justify-start! gap-2 whitespace-nowrap">
                        <a href="${url}" class="${listCls.doc}" title="Open">${listDocText(text, false)}</a>
                    </div>`;
                }

                return `<div class="flex items-center justify-start! gap-2 whitespace-nowrap">
                    <button type="button" class="btn-edit-ticket ${listCls.docEdit}" data-id="${row.eid}" title="Edit ticket">${listDocText(text, true)}</button>
                    <a href="${url}" class="${listCls.view}" title="View Ticket"><i class="fa-solid fa-eye"></i></a>
                    <button type="button" class="btn-cancel-ticket ${listCls.cancel}" data-id="${row.eid}" title="Cancel Ticket"><i class="fa-solid fa-ban"></i></button>
                </div>`;
            },
        },
                {
            data: "ticketdate",

            name: "ticketdate",

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                return `<span class="whitespace-nowrap">${formatDate(data)}</span>`;
            },
        },

        {
            data: "ticket_type",

            name: "ticket_type",

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                return `
                        <span class="inline-flex whitespace-nowrap rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-slate-300">
                            ${data ?? "-"}
                        </span>
                    `;
            },
        },

        {
            data: "ticket_category",

            name: "ticket_category",

            className: "px-3 py-4 align-middle",

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

            className: "min-w-[260px] px-3 py-4 align-middle",

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

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                if (!data) {
                    return `<span class="text-gray-400">-</span>`;
                }

                return `
                    <div class="flex items-center gap-2 whitespace-nowrap">
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

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                return data
                    ? `<span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>`
                    : `<span class="text-gray-400">-</span>`;
            },
        },

        {
            data: "cpny_id",

            name: "cpny_id",

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                return data
                    ? `<span class="text-sm text-gray-700 dark:text-gray-200">${data}</span>`
                    : `<span class="text-gray-400">-</span>`;
            },
        },
        {
            data: "pic_ticket",

            name: "pic_ticket",

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                if (!data) {
                    return `
                            <span class="text-gray-400">
                                -
                            </span>
                        `;
                }

                return `
                        <div class="flex items-center gap-2 whitespace-nowrap">

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

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                const tone = { low: "slate", medium: "blue", high: "orange", urgent: "red" }[(data || "").toLowerCase()];
                return listBadge(tone, data ?? "-");
            },
        },

        {
            data: "status",

            name: "status",

            className: "px-3 py-4 whitespace-nowrap align-middle",

            render: function (data, type, row) {
                const state = {
                    P: ["yellow", "Open"],
                    C: ["green", "Completed"],
                    R: ["orange", "Reopen"],
                    X: ["red", "Cancelled"],
                }[(data || "").toUpperCase()] || ["slate", "-"];
                return listBadge(state[0], state[1], true);
            },
        },

        ...(isIT
            ? [
                  {
                      data: "status_pekerjaan",

                      name: "status_pekerjaan",

                      className: "px-3 py-4 whitespace-nowrap align-middle",

                      render: function (data) {
                          const tone = {
                              CREATED: "blue", RESPONSE: "sky", PROCESS: "blue", PENDING: "yellow", ENVISION: "purple",
                              "ENVISION CHECKED / SOLVED": "green", TRANSFER: "pink", COMPLETED: "green", REOPEN: "red", CANCEL: "slate",
                          }[(data || "").toUpperCase()];
                          return listBadge(tone, data ?? "-", true);
                      },
                  },
              ]
            : []),

        {
            data: "ticket_duedate",

            name: "ticket_duedate",

            className: "px-3 py-4 whitespace-nowrap align-middle",

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
                        <div class="flex flex-col whitespace-nowrap leading-tight">

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
                                            <span class="rounded-full bg-red-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-red-600 dark:bg-red-900/30 dark:text-red-400">
                                                Late
                                            </span>
                                        `
                                        : `
                                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-[10px] font-medium uppercase tracking-wide text-green-600 dark:bg-green-900/30 dark:text-green-400">
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

            className:"px-3 py-3 text-center whitespace-nowrap align-middle",

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

                class="ticket-action-btn ${listCls.view}"
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

    $(".ticket-status-filter").removeClass("active");

    $(this).addClass("active");

    ticketStatusFilter = $(this).data("status");

    saveFilterState();
    ticketTable.ajax.reload();
});

const TICKET_FILTER_KEY = 'ticketFilterState';

function saveFilterState() {
    const state = {
        search:            ticketTable.search(),
        status:            $('#filter_status').val(),
        status_pekerjaan:  $('#filter_status_pekerjaan').val(),
        category_id:       $('#filter_category_id').val(),
        company_id:        $('#filter_company_id').val(),
        date_from:         $('#filter_date_from').val(),
        date_to:           $('#filter_date_to').val(),
        statusFilter:      ticketStatusFilter,
    };
    sessionStorage.setItem(TICKET_FILTER_KEY, JSON.stringify(state));
}

function restoreFilterState() {
    const raw = sessionStorage.getItem(TICKET_FILTER_KEY);
    if (!raw) return;

    try {
        const state = JSON.parse(raw);

        ticketTable.search(state.search || '');
        $('#ticketTable_wrapper .dataTables_filter input').val(state.search || '');

        if ($.fn.select2) {
            $('#filter_status').val(state.status || '').trigger('change');
            $('#filter_status_pekerjaan').val(state.status_pekerjaan || '').trigger('change');
        } else {
            $('#filter_status').val(state.status || '');
            $('#filter_status_pekerjaan').val(state.status_pekerjaan || '');
        }

        $('#filter_category_id').val(state.category_id || '').trigger('change.select2');
        $('#filter_company_id').val(state.company_id || '').trigger('change.select2');
        $('#filter_date_from').val(state.date_from || '');
        $('#filter_date_to').val(state.date_to || '');

        ticketStatusFilter = state.statusFilter || '';

        if (ticketStatusFilter) {
            $(`.ticket-status-filter[data-status="${ticketStatusFilter}"]`)
                .addClass('active');
        }
    } catch (e) {
        sessionStorage.removeItem(TICKET_FILTER_KEY);
    }
}

$(document).on('click', '#btn_apply_filter', function () {
    saveFilterState();
    ticketTable.ajax.reload();
});

$(document).on('search', '#ticketTable', function () {
    saveFilterState();
});

$(document).on('click', '#btn_reset_filter', function () {
    sessionStorage.removeItem(TICKET_FILTER_KEY);
    $('#ticketTable_wrapper .dataTables_filter input').val('');
    $('#filter_status').val('').trigger('change');
    $('#filter_status_pekerjaan').val('').trigger('change');
    $('#filter_category_id').val('').trigger('change');
    $('#filter_company_id').val('').trigger('change');
    $('#filter_date_from').val('');
    $('#filter_date_to').val('');
    ticketStatusFilter = '';
    $('.ticket-status-filter').removeClass('active');
    ticketTable.search('').ajax.reload();
});

$(document).ready(function () {
    if ($.fn.select2) {
        $('.filter-select2').select2({
            theme: 'default',
            width: '100%',
            allowClear: true,
            dropdownCssClass: 'sf-s2-dropdown',
            placeholder: function () {
                return $(this).find('option[value=""]').text() || 'Select...';
            },
        });
    }

    restoreFilterState();

    if (sessionStorage.getItem(TICKET_FILTER_KEY)) {
        ticketTable.ajax.reload();
    }
});

function doExportTicket() {
    const params = new URLSearchParams({
        search:      ticketTable.search() || '',
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
