function initDataTable() {

    table = $("#accessRequestTable").DataTable({

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
                className: "dtr-control",
                orderable: false,
                width: "28px",
            },
        ],

        autoWidth: false,

        pageLength: 10,

        lengthMenu: [
            [10, 25, 50, 100],
            [10, 25, 50, 100],
        ],

        order: [[1, "desc"]],

        ajax: {
            url: "/access-request/json",

            data: function (d) {

                d.status = currentStatus;
                d.search.value = $("#globalSearch").val();

            },
        },

        language: {

            processing: `
                <div class="flex items-center justify-center py-10">
                    <div class="
                        h-10 w-10 animate-spin rounded-full border-4
                        border-slate-300 border-t-slate-700
                        dark:border-slate-700 dark:border-t-slate-200
                    "></div>
                </div>
            `,

            emptyTable: `
                <div class="py-10 text-center">

                    <div class="
                        mx-auto flex h-14 w-14 items-center justify-center
                        rounded-full
                        bg-slate-100 text-2xl
                        dark:bg-white/10
                    ">
                        📭
                    </div>

                    <p class="
                        mt-4 text-sm font-medium
                        text-slate-700
                        dark:text-slate-300
                    ">
                        No access request found
                    </p>

                </div>
            `,
        },

        columns: [

            {
                data: null,
                defaultContent: "",
            },

            {
                data: "docid",
                name: "docid",

                render: function (data, type, row) {

                    let url = `/showaccessrequest/${row.eid}`;

                    let cls = `
                        inline-flex items-center justify-center
                        w-[150px]
                        rounded-lg
                        bg-slate-700
                        dark:bg-slate-200
                        px-4 py-2
                        text-sm font-semibold
                        text-white
                        dark:text-slate-900
                        transition
                        hover:bg-slate-800
                        dark:hover:bg-white
                    `;

                    const canEdit =
                        row.status === "D" &&
                        row.created_by === currentUser;

                    if (canEdit) {

                        url = `/editaccessrequest/${row.eid}`;

                        cls = `
                            inline-flex items-center justify-center
                            w-[150px]
                            rounded-lg
                            bg-amber-500
                            dark:bg-amber-400
                            px-4 py-2
                            text-sm font-semibold
                            text-white
                            dark:text-slate-900
                            transition
                            hover:bg-amber-600
                            dark:hover:bg-amber-300
                        `;
                    }

                    return `
                        <div class="flex items-center gap-2 whitespace-nowrap">

                            <a href="${url}" class="${cls}">
                                ${row.docid ?? "-"}
                            </a>

                            ${
                                canEdit
                                    ? `
                                        <button
                                            type="button"
                                            class="
                                                btn-detail
                                                inline-flex h-9 w-9 items-center justify-center
                                                rounded-lg
                                                border border-slate-200
                                                dark:border-white/10
                                                bg-white
                                                dark:bg-white/5
                                                text-slate-600
                                                dark:text-slate-300
                                                transition
                                                hover:bg-slate-100
                                                dark:hover:bg-white/10
                                            "
                                            data-id="${row.eid}"
                                            title="View Detail">

                                            <i class="fa-regular fa-eye"></i>

                                        </button>

                                        <button
                                            type="button"
                                            class="
                                                btn-cancel-document
                                                inline-flex h-9 w-9 items-center justify-center
                                                rounded-lg
                                                border border-red-200
                                                dark:border-red-500/20
                                                bg-red-50
                                                dark:bg-red-500/10
                                                text-red-600
                                                dark:text-red-300
                                                transition
                                                hover:bg-red-100
                                                dark:hover:bg-red-500/20
                                            "
                                            data-id="${row.eid}"
                                            title="Cancel Document">

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
                data: "access_date",
                name: "access_date",
                className: "text-left",

                render: function (data) {

                    return `
                        <span class="
                            text-sm
                            text-slate-700
                            dark:text-slate-300
                        ">
                            ${formatDate(data)}
                        </span>
                    `;
                },
            },

            {
                data: "user_peminta",
                name: "user_peminta",
                className: "text-left",

                render: function (data, type, row) {

                    return `
                        <div class="min-w-[160px]">

                            <p class="
                                font-medium
                                text-slate-800
                                dark:text-slate-100
                            ">
                                ${row.user_peminta ?? "-"}
                            </p>

                        </div>
                    `;
                },
            },

            {
                data: "cpny_id",
                name: "cpny_id",
                className: "text-center",

                // render: function (data) {

                //     return `
                //         <span class="
                //             inline-flex rounded-lg
                //             bg-slate-100
                //             dark:bg-white/10
                //             px-3 py-1
                //             text-xs font-medium
                //             text-slate-700
                //             dark:text-slate-200
                //         ">
                //             ${data ?? "-"}
                //         </span>
                //     `;
                // },
            },

            {
                data: "department_id",
                name: "department_id",
                className: "text-left whitespace-normal break-words",

                render: function (data) {

                    return `
                        <span class="
                            text-sm
                            text-slate-700
                            dark:text-slate-300
                        ">
                            ${data ?? "-"}
                        </span>
                    `;
                },
            },

            {
                data: "groups",
                name: "groups",
                orderable: false,
                searchable: false,
                className: "text-left",

                render: function (data) {

                    if (!data || data.length === 0) {
                        return "-";
                    }

                    let html = `
                        <div class="flex flex-wrap gap-2">
                    `;

                    data.forEach((item) => {

                        let badgeClass =
                            item === "HARDWARE"
                                ? `
                                    bg-blue-100
                                    dark:bg-blue-500/15
                                    text-blue-700
                                    dark:text-blue-300
                                `
                                : `
                                    bg-emerald-100
                                    dark:bg-emerald-500/15
                                    text-emerald-700
                                    dark:text-emerald-300
                                `;

                        html += `
                            <span class="
                                inline-flex rounded-lg
                                px-3 py-1
                                text-xs font-medium
                                ${badgeClass}
                            ">
                                ${item}
                            </span>
                        `;
                    });

                    html += `</div>`;

                    return html;
                },
            },

            {
                data: null,
                name: "progress",
                orderable: false,
                searchable: false,
                className: "text-left",

                render: function (data, type, row) {

                    let completed = row.total_completed ?? 0;
                    let total = row.total_detail ?? 0;

                    return renderProgressBar(completed, total);
                },
            },

            {
                data: "status",
                name: "status",
                className: "text-left",

                render: function (data) {
                    return renderStatusBadge(data);
                },
            },

            {
                data: null,
                orderable: false,
                searchable: false,
                className: "px-4 py-3",

                render: function (data, type, row) {

                    const hasAction =
                        row.can_process_hardware ||
                        row.can_process_software;

                    if (!hasAction) {

                        return `
                            <div class="
                                text-xs
                                text-slate-400
                                dark:text-slate-500
                            ">
                                No Process Needed
                            </div>
                        `;
                    }

                    let actions = `
                        <div class="flex items-center gap-2">
                    `;

                    if (row.can_process_hardware){

                        actions += `
                            <button
                                type="button"
                                class="
                                    btn-process-hardware
                                    inline-flex h-9 items-center justify-center
                                    rounded-lg
                                    bg-blue-600
                                    dark:bg-blue-500
                                    px-3
                                    text-xs font-semibold
                                    text-white
                                    transition
                                    hover:bg-blue-700
                                    dark:hover:bg-blue-400
                                "
                                data-id="${row.eid}"
                            >
                                Hardware
                            </button>
                        `;
                    }

                    if (row.can_process_software) {

                        actions += `
                            <button
                                type="button"
                                class="
                                    btn-process-software
                                    inline-flex h-9 items-center justify-center
                                    rounded-lg
                                    bg-emerald-600
                                    dark:bg-emerald-500
                                    px-3
                                    text-xs font-semibold
                                    text-white
                                    transition
                                    hover:bg-emerald-700
                                    dark:hover:bg-emerald-400
                                "
                                data-id="${row.eid}"
                            >
                                Software
                            </button>
                        `;
                    }

                    actions += `</div>`;

                    return actions;
                }
            },
        ],

        drawCallback: function () {

            bindTableActions();
            bindExtendedActions();

        },
    });
}
function initFilters() {
    $(document).on("click", ".status-filter", function (e) {
        e.preventDefault();

        $(".status-filter").removeClass("active");

        $(this).addClass("active");

        currentStatus = $(this).data("status");

        table.ajax.reload();
    });
}

function initSearch() {
    let delayTimer;

    $("#globalSearch").on("keyup", function () {
        clearTimeout(delayTimer);

        delayTimer = setTimeout(() => {
            table.ajax.reload();
        }, 500);
    });
}

function bindTableActions() {
    $(".btn-detail")
        .off("click")
        .on("click", function () {
            const id = $(this).data("id");

            openDetailModal(id);
        });

    $(".btn-print")
        .off("click")
        .on("click", function () {
            const id = $(this).data("id");

            window.open(`/access-request/print/${id}`, "_blank");
        });
}

function bindExtendedActions() {

    $(".btn-edit")
        .off("click")
        .on("click", function () {

            const id = $(this).data("id");

            openEditModal(id);

        });

    $(".btn-cancel-document")
        .off("click")
        .on("click", function () {

            const id = $(this).data("id");

            cancelDocument(id);

        });

    $(".btn-process-hardware")
        .off("click")
        .on("click", function () {

            const id = $(this).data("id");

            openProcessHardwareModal(id);

        });

    $(".btn-process-software")
        .off("click")
        .on("click", function () {

            const id = $(this).data("id");

            openProcessSoftwareModal(id);

        });

}


function renderActionDropdown(row) {

    return `
        <div class="relative inline-block text-left">

            <button
                type="button"
                class="
                    btn-dropdown
                    inline-flex h-9 w-9 items-center justify-center
                    rounded-lg
                    border border-slate-200
                    dark:border-white/10
                    bg-white
                    dark:bg-white/5
                    text-slate-600
                    dark:text-slate-300
                    transition
                    hover:bg-slate-100
                    dark:hover:bg-white/10
                "
                data-id="${row.eid}">

                <i class="fa-solid fa-ellipsis"></i>

            </button>

            <div class="
                dropdown-menu
                absolute right-0 z-50 mt-2 hidden
                w-52 overflow-hidden rounded-lg
                border border-slate-200
                dark:border-white/10
                bg-white
                dark:bg-[#0f172a]
                shadow-xl
            ">

                <div class="p-2">

                    <button
                        class="
                            btn-detail
                            flex w-full items-center gap-3
                            rounded-lg px-3 py-2
                            text-left text-sm
                            text-slate-700
                            dark:text-slate-200
                            transition
                            hover:bg-slate-100
                            dark:hover:bg-white/10
                        "
                        data-id="${row.eid}">

                        <i class="
                            fa-solid fa-eye w-4
                            text-slate-500
                            dark:text-slate-400
                        "></i>

                        View Detail

                    </button>

                    <button
                        class="
                            btn-print
                            flex w-full items-center gap-3
                            rounded-lg px-3 py-2
                            text-left text-sm
                            text-slate-700
                            dark:text-slate-200
                            transition
                            hover:bg-slate-100
                            dark:hover:bg-white/10
                        "
                        data-id="${row.eid}">

                        <i class="
                            fa-solid fa-print w-4
                            text-slate-500
                            dark:text-slate-400
                        "></i>

                        Print

                    </button>

                    ${
                        row.status === "D" &&
                        row.created_by === currentUser
                            ? `
                                <button
                                    class="
                                        btn-edit
                                        flex w-full items-center gap-3
                                        rounded-lg px-3 py-2
                                        text-left text-sm
                                        text-amber-700
                                        dark:text-amber-300
                                        transition
                                        hover:bg-amber-50
                                        dark:hover:bg-amber-500/10
                                    "
                                    data-id="${row.eid}">

                                    <i class="fa-solid fa-pen-to-square w-4"></i>

                                    Edit

                                </button>
                            `
                            : ""
                    }

                    ${
                        row.status === "D" &&
                        row.created_by === currentUser
                            ? `
                                <button
                                    class="
                                        btn-cancel-document
                                        flex w-full items-center gap-3
                                        rounded-lg px-3 py-2
                                        text-left text-sm
                                        text-red-700
                                        dark:text-red-300
                                        transition
                                        hover:bg-red-50
                                        dark:hover:bg-red-500/10
                                    "
                                    data-id="${row.eid}">

                                    <i class="fa-solid fa-ban w-4"></i>

                                    Cancel

                                </button>
                            `
                            : ""
                    }

                </div>

            </div>

        </div>
    `;
}


async function cancelDocument(id) {
    const confirm = await confirmDialog({
        title: "Cancel Document?",
        text: "This action cannot be undone.",
        confirmText: "Yes, Cancel",
    });

    if (!confirm.isConfirmed) {
        return;
    }

    $.ajax({
        url: `/access-request/cancel/${id}`,
        type: "POST",

        success: function (res) {
            swalSuccess(res.message ?? "Document cancelled successfully");

            table.ajax.reload(null, false);
        },

        error: function (xhr) {
            swalWarning(xhr.responseJSON?.message ?? "Failed cancel document");
        },
    });
}
$(document).on("click", ".btn-dropdown", function (e) {
    e.stopPropagation();

    const menu = $(this).closest(".relative").find(".dropdown-menu");

    $(".dropdown-menu").not(menu).addClass("hidden");

    menu.toggleClass("hidden");
});

$(document).on("click", ".btn-refresh-table", function () {
    table.ajax.reload(null, false);
});

$(document).on("click", ".btn-create-request", function () {
    resetRequestForm();

    openModal("#requestModal");
});

$(document).on("click", function () {
    $(".dropdown-menu").addClass("hidden");
});




