window.processAttachments = [];

function processInfoContent(header) {
    return `

        ${infoItem(
            "Date",
            header.itrecommend_date
                ? new Date(header.itrecommend_date).toLocaleDateString(
                      "en-GB",
                      {
                          day: "2-digit",
                          month: "short",
                          year: "numeric",
                      },
                  )
                : "-",
        )}

        ${infoItem("Company", header.cpny_id)}

        ${infoItem("Department", header.department_id)}

        ${infoItem("Requester", header.user_peminta)}

        ${infoItem("Ticket Number", header.ticketnbr)}

        ${infoItem("Asset Number", header.assetnbr || "-")}

        ${infoItem("Purpose / Requirement", header.keperluan)}

    `;
}

function renderProcessAttachments(attachments = []) {
    if (attachments.length === 0) {
        return attachmentEmptyState();
    }

    return attachments.map(file => {
        const displayName = file.attachment_name
            ? file.attachment_name + (file.extention ? '.' + file.extention : '')
            : (file.filename || "Attachment");

        return attachmentCard({
            name: displayName,
            url: file.signed_url || "#",
        });
    }).join('');
}

function removeProcessAttachment(index)
{
    window.processAttachments.splice(index, 1);

    renderProcessAttachmentPreview();
}

function addDetailRow(data = {}) {
    const html = `

        <tr class="
            detail-row

            border-b border-slate-200
            dark:border-white/5
        ">

            <td class="
                relative

                px-3 py-3
                align-top
            ">

                <div class="relative">

                    <input
                        type="text"

                        class="
                            inventory-search

                            w-full

                            rounded-lg

                            border border-slate-200
                            dark:border-white/10

                            bg-white
                            dark:bg-[#111827]

                            py-2.5
                            pl-3
                            pr-10

                            text-sm

                            text-slate-700
                            dark:text-white

                            outline-none

                            transition-all
                            duration-150

                            focus:border-indigo-500
                            focus:ring-2
                            focus:ring-indigo-500/20
                        "

                        placeholder="Search inventory name or item code..."

                        autocomplete="off"

                        value="${data.inventory_descr || ""}"
                    >

                    <input
                        type="hidden"

                        class="inventory-id"

                        value="${data.inventoryid || ""}"
                    >

                    <div class="
                        pointer-events-none

                        absolute
                        inset-y-0
                        right-0

                        flex
                        items-center

                        pr-3

                        text-slate-400
                    ">

                        <i class="
                            fa-solid
                            fa-chevron-down

                            text-xs
                        "></i>

                    </div>

                    <div class="
                        inventory-result

                        absolute
                        left-0
                        top-full

                        z-[999999]

                        mt-2

                        hidden

                        max-h-72
                        w-full

                        overflow-y-auto

                        rounded-xl

                        border border-slate-200
                        dark:border-white/10

                        bg-white
                        dark:bg-[#111827]

                        shadow-[0_20px_60px_rgba(0,0,0,0.25)]

                        ring-1
                        ring-black/5

                        isolate
                    ">
                    </div>

                </div>

            </td>

            <td class="px-3 py-3 align-top">

                <input
                    type="number"

                    min="1"

                    class="
                        item-qty

                        w-24

                        rounded-lg

                        border border-slate-200
                        dark:border-white/10

                        bg-slate-50
                        dark:bg-white/[0.03]

                        px-3 py-2.5

                        text-sm

                        text-slate-700
                        dark:text-white

                        outline-none

                        transition-all
                        duration-150

                        focus:border-indigo-500
                        focus:ring-2
                        focus:ring-indigo-500/20
                    "

                    value="${data.qty || 1}"
                >

            </td>

            <td class="px-3 py-3 align-top">

                <input
                    type="text"

                    readonly

                    class="
                        item-uom

                        w-24

                        rounded-lg

                        border border-slate-200
                        dark:border-white/10

                        bg-slate-100
                        dark:bg-[#1f2937]

                        px-3 py-2.5

                        text-sm

                        text-slate-500
                        dark:text-slate-300

                        outline-none
                    "

                    value="${data.uom || ""}"
                >

            </td>

            <td class="px-3 py-3 align-top">

                <textarea
                    rows="2"

                    class="
                        item-note

                        w-full

                        rounded-lg

                        border border-slate-200
                        dark:border-white/10

                        bg-slate-50
                        dark:bg-white/[0.03]

                        px-3 py-2.5

                        text-sm

                        text-slate-700
                        dark:text-white

                        outline-none

                        transition-all
                        duration-150

                        focus:border-indigo-500
                        focus:ring-2
                        focus:ring-indigo-500/20
                    "

                    placeholder="Optional note"
                >${data.recommend_note || ""}</textarea>

            </td>

            <td class="
                px-3 py-3

                align-top
                text-center
            ">

                <button
                    type="button"

                    class="
                        btn-remove-item

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
                        duration-150

                        hover:bg-red-100
                        dark:hover:bg-red-500/20
                    "
                >

                    <i class="
                        fa-solid
                        fa-trash

                        text-xs
                    "></i>

                </button>

            </td>

        </tr>

    `;

    $("#process_detail_body").append(html);
}

async function loadProcessDetail(hash) {
    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,

            type: "GET",
        });

        const header = res.header;

        window.processAttachments = [];

        $("#processAttachmentPreview").html("");

        $("#process_hash").val(hash);

        $("#process_docid").text(header.docid || "-");

        $("#process_information").html(processInfoContent(header));

        if (!$("#recommend_type").hasClass("select2-hidden-accessible")) {
            $(".process-select2").select2({
                width: "100%",
                dropdownParent: $("#processModal"),
                placeholder: "Select Type",
            });
        }
        $("#recommend_type").val(header.recommend_type || "").trigger("change");

        $("#waranty").val(header.waranty || "");

        $("#recommendation").val(header.recommendation || "");

        $("#process_attachments").html(
            renderProcessAttachments(res.attachments || []),
        );

        $("#process_detail_body").html("");

        if (res.details && res.details.length > 0) {
            res.details.forEach((row) => {
                addDetailRow({
                    inventory_descr: row.recommend_descr,

                    inventoryid: row.inventoryid,

                    qty: row.qty,

                    uom: row.uom,

                    recommend_note: row.recommend_note,
                });
            });
        } else {
            addDetailRow();
        }

        openProcessModal();
    } catch (err) {
        console.error(err);

        Swal.fire({
            icon: "error",

            title: "Error",

            text:
                err.responseJSON?.message ||
                err.message ||
                "Failed load process data",
        });
    }
}

function collectProcessDetails() {
    const details = [];

    $("#process_detail_body tr").each(function () {
        const row = $(this);

        const recommendDescr = row.find(".inventory-search").val().trim();

        if (!recommendDescr) {
            return;
        }

        details.push({
            recommend_descr: recommendDescr,

            qty: row.find(".item-qty").val(),

            uom: row.find(".item-uom").val(),

            recommend_note: row.find(".item-note").val(),
        });
    });

    return details;
}

async function submitProcessAction({ url, successText, note = null })
{
    try {

        let data;
        let processData = true;
        let contentType =
            "application/x-www-form-urlencoded; charset=UTF-8";

        if (note) {

            data = {
                note,
            };

        } else {

            data = new FormData();

            data.append(
                "recommend_type",
                $("#recommend_type").val()
            );

            data.append(
                "waranty",
                $("#waranty").val()
            );

            data.append(
                "recommendation",
                $("#recommendation").val()
            );

            collectProcessDetails().forEach(
                (row, index) => {

                    data.append(
                        `details[${index}][recommend_descr]`,
                        row.recommend_descr
                    );

                    data.append(
                        `details[${index}][qty]`,
                        row.qty
                    );

                    data.append(
                        `details[${index}][uom]`,
                        row.uom
                    );

                    data.append(
                        `details[${index}][recommend_note]`,
                        row.recommend_note || ""
                    );
                }
            );

            window.processAttachments.forEach(file => {

                data.append(
                    "attachments[]",
                    file
                );
            });

            processData = false;
            contentType = false;
        }

        await $.ajax({

            url,

            type: "POST",

            headers: {
                "X-CSRF-TOKEN":
                    $('meta[name="csrf-token"]').attr("content"),
            },

            data,

            processData,

            contentType
        });

        itrToast('success', successText);

        closeProcessModal(true);

        table.ajax.reload(null, false);

        if (currentDetailHash) {
            await loadDetail(currentDetailHash);
        }

    } catch (err) {

        let msg =
            err.responseJSON?.message ||
            "Process failed";

        if (
            err.status === 422 &&
            err.responseJSON?.errors
        ) {
            msg = Object
                .values(err.responseJSON.errors)
                .flat()
                .join("<br>");
        }

        Swal.fire({
            icon: "error",
            title: "Error",
            html: msg,
        });
    }
}

$(document).on("click", ".process-btn", function () {
    const hash = $(this).data("id");

    window.history.pushState({}, "", `/processitrecommendation/${hash}`);

    loadProcessDetail(hash);
});

$("#btnCloseProcessModal").on("click", function () {
    closeProcessModal();
});

$("#btnAddItem").on("click", function () {
    addDetailRow();

    modalState.processDirty = true;
});

$(document).on("click", ".btn-remove-item", function () {
    $(this).closest("tr").remove();

    modalState.processDirty = true;

    if ($("#process_detail_body tr").length === 0) {
        addDetailRow();
    }
});

$(document).on(
    "change",
    "#process_attachments_input",
    function () {

        const files = Array.from(this.files);

        files.forEach(file => {

            if (file.size > 5 * 1024 * 1024) {

                Swal.fire({
                    icon: "warning",
                    title: "File too large",
                    text: `${file.name} exceeds 5 MB`
                });

                return;
            }

            window.processAttachments.push(file);
        });

        renderProcessAttachmentPreview();

        $(this).val("");
    }
);

function renderProcessAttachmentPreview() {
    let html = "";

    window.processAttachments.forEach((file, index) => {
        html += attachmentCard({
            name: file.name,
            size: formatFileSize(file.size),
            removable: true,
            index,
            removeClass: "btn-remove-process-attachment",
        });
    });

    $("#processAttachmentPreview").html(html || attachmentEmptyState());
}

$(document).on("click", ".btn-remove-process-attachment", function () {
    const index = $(this).data("index");
    removeProcessAttachment(index);
});

$("#processForm").on("submit", async function (e) {
    e.preventDefault();

    const details = collectProcessDetails();

    if (details.length === 0) {
        Swal.fire({
            icon: "warning",

            title: "Validation",

            text: "Please select inventory item",
        });

        return;
    }

    const btn = $("#btnSubmitProcess");

    btn.prop("disabled", true).html(`

            <i class="
                fa-solid
                fa-spinner
                fa-spin

                text-xs
            "></i>

            Processing...

        `);

    await submitProcessAction({
        url: `/it-recommendation/process/${$("#process_hash").val()}`,

        successText: "Processed successfully",
    });

    btn.prop("disabled", false).html(`

            <i class="
                fa-solid
                fa-gears

                text-xs
            "></i>

            Submit Process

        `);
});

$("#btnReviseProcess").on("click", async function () {
    const result = await Swal.fire({
        title: "Revise Request",

        input: "textarea",

        inputPlaceholder: "Write revise reason...",

        showCancelButton: true,

        confirmButtonText: "Submit Revise",
    });

    if (!result.isConfirmed || !result.value) {
        return;
    }

    submitProcessAction({
        url: `/it-recommendation/it-revise/${$("#process_hash").val()}`,

        successText: "Document revised",

        note: result.value,
    });
});

$("#btnRejectProcess").on("click", async function () {
    const result = await Swal.fire({
        title: "Reject Request",

        input: "textarea",

        inputPlaceholder: "Write reject reason...",

        showCancelButton: true,

        confirmButtonText: "Reject",
    });

    if (!result.isConfirmed || !result.value) {
        return;
    }

    submitProcessAction({
        url: `/it-recommendation/it-reject/${$("#process_hash").val()}`,

        successText: "Document rejected",

        note: result.value,
    });
});

const processPath = window.location.pathname;

if (processPath.includes("/processitrecommendation/")) {
    const hash = processPath.split("/").pop();

    loadProcessDetail(hash);
}
