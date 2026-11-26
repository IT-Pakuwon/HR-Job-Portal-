function renderITRStatusBadge(status) {
    const map = {
        S: ["sky", "Submitted"],
        IT: ["green", "Processed"],
        A: ["green", "Approved"],
        W: ["purple", "Waiting IT"],
        I: ["orange", "Waiting IT Revision"],
        P: ["yellow", "Waiting Approval"],
        C: ["green", "Completed"],
        R: ["red", "Rejected"],
        D: ["blue", "Revise"],
        X: ["slate", "Cancelled"],
    };

    const item = map[status] || ["slate", status || "-"];

    return listBadge(item[0], item[1], true);
}

function renderITRDocButton(row) {
    const url = row.can_edit
        ? `/edititrecommendation/${row.eid}`
        : `/showitrecommendation/${row.eid}`;

    const eyeIcon = row.can_edit
        ? `<a href="/showitrecommendation/${row.eid}" class="${listCls.view}" title="View Detail"><i class="fa-solid fa-eye"></i></a>`
        : '';

    const cancelIcon = row.can_cancel
        ? `<button type="button" class="cancel-btn ${listCls.cancel}" data-id="${row.eid}" title="Cancel Request"><i class="fa-solid fa-ban"></i></button>`
        : '';

    return `
        <div class="flex items-center justify-start! gap-2 whitespace-nowrap">
            <a href="${url}" class="${row.can_edit ? listCls.docEdit : listCls.doc}">${listDocText(row.docid, row.can_edit)}</a>
            ${eyeIcon}
            ${cancelIcon}
        </div>
    `;
}

function renderITRProcessButton(row)
{
    let html = '';

    if (row.can_process) {

        const isRevision =
            row.status === 'I';

        html += `
            <button
                type="button"

                class="${isRevision ? 'edit-recommendation-btn ' + listCls.action.amber : 'process-btn ' + listCls.action.blue}"

                data-id="${row.eid}"
            >

                <i class="fa-solid ${
                    isRevision
                        ? 'fa-rotate-left'
                        : 'fa-gears'
                }"></i>

                ${
                    isRevision
                        ? 'Edit Recommendation'
                        : 'Process'
                }

            </button>
        `;
    }

    if (row.can_upload_attachment) {

        html += `
            <button
                type="button"

                class="attachment-btn ${listCls.action.slate}"

                data-id="${row.eid}"
            >

                <i class="fa-solid fa-paperclip"></i>

                Attachment

            </button>
        `;
    }

    if (!html) {
        return '-';
    }

    return `
        <div class="
            flex
            items-center
            justify-center
            gap-2
        ">
            ${html}
        </div>
    `;
}

table = $("#itrTable").DataTable({
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

    ordering: true,

    dom: '<"dt-toolbar"lf>rtip',

    language: {
        search: "",
        searchPlaceholder: "Search...",
    },

    ajax: {
        url: window.ITRecommendationRoutes.json,

        type: "GET",

        data: function (d) {
            d.status = statusFilter || "";
        },
    },

    order: [[1, "desc"]],

    columns: [
        {
            data: null,

            defaultContent: "",

            orderable: false,

            searchable: false,

            className: "dtr-control px-5 py-4 text-center align-middle",

            width: "20px",
        },

        {
            data: "docid",

            className: "px-5 py-4 whitespace-nowrap align-middle",

            render: function (data, type, row) {
                return renderITRDocButton(row);
            },
        },

        {
            data: "itrecommend_date",

            className: "px-5 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                if (!data) {
                    return "-";
                }

                return new Date(data).toLocaleDateString("en-GB", {
                    day: "2-digit",
                    month: "short",
                    year: "numeric",
                });
            },
        },

        {
            data: "ticketnbr",

            defaultContent: "-",

            className: "px-5 py-4 whitespace-nowrap align-middle",
        },

        {
            data: "cpny_id",

            className: "px-5 py-4 whitespace-nowrap align-middle",
        },

        {
            data: "department_id",

            className: "px-5 py-4 whitespace-nowrap align-middle",
        },

        {
            data: "user_peminta",

            className: "px-5 py-4 whitespace-nowrap align-middle",
        },

        {
            data: "keperluan",

            className: "min-w-[260px] px-5 py-4 align-middle",
        },

        {
            data: "recommend_pic",

            defaultContent: "-",

            className: "px-5 py-4 whitespace-nowrap align-middle",
        },

        {
            data: "status",

            className: "px-5 py-4 whitespace-nowrap align-middle",

            render: function (data) {
                return renderITRStatusBadge(data);
            },
        },

            {
            data: null,
            orderable: false,
            searchable: false,
            className:
                "px-5 py-4 text-center whitespace-nowrap align-middle",

            render: function (data, type, row) {
                return renderITRProcessButton(row);
            },
        },
    ],

    drawCallback: function () {
        $("#itrTable tbody tr").addClass(
            "transition duration-150 hover:bg-gray-50 dark:hover:bg-gray-800/40",
        );

    },

    searchDelay: 400,

    stateSave: true,
});

$(".status-filter").on("click", function (e) {
    e.preventDefault();

    $(".status-filter").removeClass("active");

    $(this).addClass("active");

    statusFilter = $(this).data("status") || "";

    table.ajax.reload(null, true);
});
