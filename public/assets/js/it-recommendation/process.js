function addDetailRow(data = {}) {
    const rowId = Date.now() + Math.floor(Math.random() * 1000);

    const html = `
                    <tr class="detail-row border-b border-gray-100 dark:border-white/5">

                        <td class="px-3 py-3 align-top">

                        <div class="relative">

                            <input
                                type="text"
                                class="inventory-search w-full rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Search inventory name or item code..."
                                autocomplete="off"
                                value="${data.inventory_descr ?? ""}">

                            <input
                                type="hidden"
                                class="inventory-id"
                                value="${data.inventoryid ?? ""}">

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>

                            <div class="inventory-result absolute left-0 bottom-full z-[9999] mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#111827]">
                            </div>

                        </div>
                        </td>

                        <td class="px-3 py-3 align-top">

                            <input
                                type="number"
                                min="1"
                                class="item-qty w-24 rounded-lg bg-gray-50 px-3 py-2
                                dark:bg-white/[0.03] text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                value="${data.qty ?? 1}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <input
                                type="text"
                                class="item-uom w-24 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 outline-none dark:border-white/10 dark:bg-[#1f2937] dark:text-gray-300"
                                readonly
                                value="${data.uom ?? ""}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <textarea
                                rows="2"
                                class="item-note w-full rounded-lg bg-gray-50 px-3 py-2
                                dark:bg-white/[0.03] text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Optional note">${data.recommend_note ?? ""}</textarea>

                        </td>

                        <td class="px-3 py-3 align-top text-center">

                            <button
                                type="button"
                                class="btn-remove-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                                <i class="fa-solid fa-trash text-xs"></i>

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

        const h = res.header;

        $("#process_hash").val(hash);

        $("#process_docid").text(`${h.docid}`);

        $("#process_information").html(`

                        ${processInfoItem(
                            "Date",
                            h.itrecommend_date
                                ? new Date(
                                      h.itrecommend_date,
                                  ).toLocaleDateString("en-GB", {
                                      day: "2-digit",
                                      month: "short",
                                      year: "numeric",
                                  })
                                : "-",
                        )}

                        ${processInfoItem("Company", h.cpny_id)}
                        ${processInfoItem("Department", h.department_id)}
                        ${processInfoItem("Requester", h.user_peminta)}
                        ${processInfoItem("Ticket Number", h.ticketnbr)}
                        ${processInfoItem("Asset Number", h.assetnbr || "-")}
                        ${processInfoItem("Purpose / Requirement", h.keperluan)}

                    `);

        $("#recommend_type").val(h.recommend_type || "");
        $("#waranty").val(h.waranty || "");
        $("#recommendation").val(h.recommendation || "");

        let attachmentHtml = "";

        if (res.attachments.length === 0) {
            attachmentHtml = `
                            <div class="w-full rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                                No attachments
                            </div>
                        `;
        } else {
            res.attachments.forEach((file) => {
                attachmentHtml += `
                                <a
                                    href="${file.signed_url ?? "#"}"
                                    target="_blank"
                                    class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700 transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300 dark:hover:bg-white/[0.05]">

                                    <i class="fa-solid fa-paperclip text-gray-400"></i>

                                    <div class="max-w-[220px] truncate">
                                        ${file.filename ?? "Attachment"}
                                    </div>

                                </a>
                            `;
            });
        }

        $("#process_attachments").html(attachmentHtml);

        $("#process_detail_body").html("");

        if (res.details.length > 0) {
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
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed load process data",
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
});

$(document).on("click", ".btn-remove-item", function () {
    $(this).closest("tr").remove();

    if ($("#process_detail_body tr").length === 0) {
        addDetailRow();
    }
});

$("#processForm").on("submit", async function (e) {
    e.preventDefault();

    const hash = $("#process_hash").val();

    const btn = $("#btnSubmitProcess");

    let details = [];

    $("#process_detail_body tr").each(function () {
        const row = $(this);

        const recommend_descr = row.find(".inventory-search").val().trim();

        if (!recommend_descr) {
            return;
        }

        details.push({
            recommend_descr: recommend_descr,
            qty: row.find(".item-qty").val(),
            uom: row.find(".item-uom").val(),
            recommend_note: row.find(".item-note").val(),
        });
    });

    if (details.length === 0) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Please select inventory item",
        });

        return;
    }

    btn.prop("disabled", true).html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Processing...
                    `);

    try {
        await $.ajax({
            url: `/it-recommendation/process/${hash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                recommend_type: $("#recommend_type").val(),
                waranty: $("#waranty").val(),
                recommendation: $("#recommendation").val(),
                details: details,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Processed successfully",
            timer: 1800,
            showConfirmButton: false,
        });

        closeProcessModal();

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        let msg = err.responseJSON?.message || "Failed process";

        if (err.status === 422 && err.responseJSON?.errors) {
            msg = Object.values(err.responseJSON.errors).flat().join("<br>");
        }

        Swal.fire({
            icon: "error",
            title: "Error",
            html: msg,
        });
    } finally {
        btn.prop("disabled", false).html(`
                            <i class="fa-solid fa-gears text-xs"></i>
                            Submit Process
                        `);
    }
});

$("#btnReviseProcess").on("click", async function () {
    const hash = $("#process_hash").val();

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

    try {
        await $.ajax({
            url: `/it-recommendation/it-revise/${hash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                note: result.value,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Document revised",
        });

        closeProcessModal();

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed revise",
        });
    }
});

$("#btnRejectProcess").on("click", async function () {
    const hash = $("#process_hash").val();

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

    try {
        await $.ajax({
            url: `/it-recommendation/it-reject/${hash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                note: result.value,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Document rejected",
        });

        closeProcessModal();

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed reject",
        });
    }
});

const processPath = window.location.pathname;

if (path.includes("/processitrecommendation/")) {
    const hash = path.split("/").pop();

    loadProcessDetail(hash);
}
