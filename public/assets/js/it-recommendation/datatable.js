function renderITRStatusBadge(status) {
    const map = {
        S: {
            text: "Submitted",

            cls: "bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-300",
        },

        IT: {
            text: "Processed",

            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        A: {
            text: "Approved",

            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        W: {
            text: "Waiting IT",

            cls: "bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-300",
        },

        I: {
            text: "Waiting IT Revision",

            cls: "bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-300",
        },

        P: {
            text: "Waiting Approval",

            cls: "bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-300",
        },

        C: {
            text: "Completed",

            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        R: {
            text: "Rejected",

            cls: "bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-300",
        },

        D: {
            text: "Revise",

            cls: "bg-gray-100 text-gray-700 border border-gray-200 dark:bg-white/10 dark:border-white/10 dark:text-gray-300",
        },

        X: {
            text: "Cancelled",

            cls: "bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-500/10 dark:border-slate-500/20 dark:text-slate-300",
        },
    };

    const item = map[status] || {
        text: status || "-",

        cls: "bg-gray-100 text-gray-700 border border-gray-200",
    };

    return `
        <span class="
            inline-flex
            items-center
            justify-center

            min-w-[150px]

            rounded-full

            px-3 py-1.5

            text-xs
            font-semibold

            ${item.cls}
        ">
            ${item.text}
        </span>
    `;
}

function renderITRDocButton(row) {
    let url = `/showitrecommendation/${row.eid}`;

    let cls = `
        inline-flex
        items-center
        justify-center

        min-w-[150px]

        rounded-lg

        bg-slate-800
        dark:bg-slate-100

        px-3 py-1.5

        text-sm
        font-semibold

        text-white
        dark:text-slate-900

        transition-all
        duration-200

        hover:bg-slate-700
        dark:hover:bg-white
    `;

    if (row.can_edit) {
        url = `/edititrecommendation/${row.eid}`;

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

            <a
                href="${url}"
                class="${cls}"
            >
                ${row.docid}
            </a>

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

                class="
                    ${
                        isRevision
                            ? 'edit-recommendation-btn bg-orange-500 hover:bg-orange-600'
                            : 'process-btn bg-indigo-600 hover:bg-indigo-700'
                    }

                    inline-flex
                    items-center
                    gap-2

                    rounded-lg

                    px-3 py-1.5

                    text-sm
                    font-semibold

                    text-white

                    shadow-sm

                    transition-all
                    duration-200
                "

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

                class="
                    attachment-btn

                    inline-flex
                    items-center
                    gap-2

                    rounded-lg

                    bg-slate-600
                    hover:bg-slate-700

                    px-3 py-1.5

                    text-sm
                    font-semibold

                    text-white

                    transition-all
                    duration-200
                "

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

    dom: "flrtip",

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

            className: "dtr-control px-5 py-4 text-center align-top",

            width: "20px",
        },

        {
            data: "docid",

            className: "px-5 py-4 whitespace-nowrap align-top",

            render: function (data, type, row) {
                return renderITRDocButton(row);
            },
        },

        {
            data: "itrecommend_date",

            className: "px-5 py-4 whitespace-nowrap align-top",

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

            className: "px-5 py-4 whitespace-nowrap align-top",
        },

        {
            data: "cpny_id",

            className: "px-5 py-4 whitespace-nowrap align-top",
        },

        {
            data: "department_id",

            className: "px-5 py-4 whitespace-nowrap align-top",
        },

        {
            data: "user_peminta",

            className: "px-5 py-4 whitespace-nowrap align-top",
        },

        {
            data: "keperluan",

            className: "min-w-[260px] px-5 py-4 align-top",
        },

        {
            data: "recommend_pic",

            defaultContent: "-",

            className: "px-5 py-4 whitespace-nowrap align-top",
        },

        {
            data: "status",

            className: "px-5 py-4 whitespace-nowrap align-top",

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
