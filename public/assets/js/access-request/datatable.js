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

        dom: '<"dt-toolbar"lf>rtip',

        language: {
            search: "",
            searchPlaceholder: "Search...",
        },

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
                d.group_category = currentGroup;

            },
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

                    const canEdit =
                        row.status === "D" &&
                        row.created_by === currentUser;

                    const url = canEdit
                        ? `/editaccessrequest/${row.eid}`
                        : `/showaccessrequest/${row.eid}`;

                    return `
                        <div class="flex items-center justify-start! gap-2 whitespace-nowrap">
                            <a href="${url}" class="${canEdit ? listCls.docEdit : listCls.doc}">${listDocText(row.docid ?? "-", canEdit)}</a>
                            ${
                                canEdit
                                    ? `
                                        <button type="button" class="btn-detail ${listCls.view}" data-id="${row.eid}" title="View Detail"><i class="fa-solid fa-eye"></i></button>
                                        <button type="button" class="btn-cancel-document ${listCls.cancel}" data-id="${row.eid}" title="Cancel Document"><i class="fa-solid fa-ban"></i></button>
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

                render: function (data) {

                    return `
                        <span class="
                            inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 dark:bg-white/10 dark:text-slate-200
                        ">
                            ${data ?? "-"}
                        </span>
                    `;
                },
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
                                inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${badgeClass}
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
                    const state = {
                        P: ["yellow", "On Progress"],
                        C: ["sky", "Approved"],
                        F: ["green", "Completed"],
                        D: ["blue", "Revise"],
                        R: ["red", "Reject"],
                        X: ["slate", "Cancel"],
                    }[data] || ["slate", data ?? "-"];
                    return listBadge(state[0], state[1], true);
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
                                class="btn-process-hardware ${listCls.action.blue}"
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
                                class="btn-process-software ${listCls.action.green}"
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

    $("#approvedGroupFilter").select2({
        width: "100%",
        minimumResultsForSearch: Infinity,
        dropdownCssClass: "sppb-s2-dropdown",
    });

    $(document).on(
        "click",
        ".status-filter",
        function (e) {

            e.preventDefault();

            $(".status-filter")
                .removeClass("active");

            $(this)
                .addClass("active");

            currentStatus =
                $(this).data("status");

            currentGroup = "";
            $("#approvedGroupFilter").val("").trigger("change.select2");

            table.ajax.reload();

        }
    );

    $(document).on("change", "#approvedGroupFilter", function () {
        currentStatus = "C";
        currentGroup = $(this).val();
        $(".status-filter").removeClass("active");
        $('.status-filter[data-status="C"]').addClass("active");
        table.ajax.reload();
    });
}

function bindTableActions() {

    $(".btn-detail")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

            openDetailModal(id);

        });

    $(".btn-print")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

            window.open(
                `/access-request/print/${id}`,
                "_blank"
            );

        });
}

function bindExtendedActions() {

    $(".btn-edit")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

            openEditModal(id);

        });

    $(".btn-cancel-document")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

            cancelDocument(id);

        });

    $(".btn-process-hardware")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

            openProcessHardwareModal(id);

        });

    $(".btn-process-software")
        .off("click")
        .on("click", function () {

            const id =
                $(this).data("id");

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

                    inline-flex h-9 w-9
                    items-center justify-center

                    rounded-lg

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-white
                    dark:bg-white/[0.04]

                    text-slate-600
                    dark:text-slate-300

                    transition-all duration-200

                    hover:bg-slate-100
                    dark:hover:bg-white/[0.08]
                "
                data-id="${row.eid}">

                <i class="fa-solid fa-ellipsis"></i>

            </button>

            <div class="
                dropdown-menu

                absolute right-0 z-50 mt-2 hidden

                w-52 overflow-hidden

                rounded-2xl

                border border-slate-200
                dark:border-white/[0.06]

                bg-white
                dark:bg-[#111c2d]

                shadow-2xl
            ">

                <div class="p-2">

                    <button
                        class="
                            btn-detail

                            flex w-full items-center gap-3

                            rounded-lg

                            px-3 py-2.5

                            text-left text-sm

                            text-slate-700
                            dark:text-slate-200

                            transition-all duration-200

                            hover:bg-slate-100
                            dark:hover:bg-white/[0.06]
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

                            rounded-lg

                            px-3 py-2.5

                            text-left text-sm

                            text-slate-700
                            dark:text-slate-200

                            transition-all duration-200

                            hover:bg-slate-100
                            dark:hover:bg-white/[0.06]
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

                                        rounded-lg

                                        px-3 py-2.5

                                        text-left text-sm

                                        text-amber-700
                                        dark:text-amber-300

                                        transition-all duration-200

                                        hover:bg-amber-50
                                        dark:hover:bg-amber-500/10
                                    "
                                    data-id="${row.eid}">

                                    <i class="
                                        fa-solid fa-pen-to-square w-4
                                    "></i>

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

                                        rounded-lg

                                        px-3 py-2.5

                                        text-left text-sm

                                        text-red-700
                                        dark:text-red-300

                                        transition-all duration-200

                                        hover:bg-red-50
                                        dark:hover:bg-red-500/10
                                    "
                                    data-id="${row.eid}">

                                    <i class="
                                        fa-solid fa-ban w-4
                                    "></i>

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

    try {

        const res = await $.ajax({
            url: `/access-request/cancel/${id}`,
            type: "POST",
        });

        itrToast("success", res.message ?? "Document cancelled successfully");

        table.ajax.reload(null, false);

    } catch (xhr) {

        swalError(xhr.responseJSON?.message ?? "Failed cancel document");

    }
}

$(document).on(
    "click",
    ".btn-dropdown",
    function (e) {

        e.stopPropagation();

        const menu = $(this)
            .closest(".relative")
            .find(".dropdown-menu");

        $(".dropdown-menu")
            .not(menu)
            .addClass("hidden");

        menu.toggleClass("hidden");

    }
);

$(document).on(
    "click",
    ".btn-refresh-table",
    function () {

        table.ajax.reload(
            null,
            false
        );

    }
);

$(document).on(
    "click",
    ".btn-create-request",
    function () {

        resetRequestForm();

        openModal("#requestModal");

    }
);

$(document).on(
    "click",
    function () {

        $(".dropdown-menu")
            .addClass("hidden");

    }
);
