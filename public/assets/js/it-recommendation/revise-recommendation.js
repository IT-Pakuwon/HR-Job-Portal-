function addEditRecommendationRow(data = {}) {
    const html = `
                    <tr class="edit-detail-row border-b border-gray-100 dark:border-white/5">

                        <td class="px-3 py-3 align-top">

                        <div class="relative">

                                <input
                                    type="text"
                                    class="edit-inventory-search w-full rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-10 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                    placeholder="Search inventory name or item code..."
                                    autocomplete="off"
                                    value="${data.inventory_descr ?? ""}">

                                <input
                                    type="hidden"
                                    class="edit-inventory-id"
                                    value="${data.inventoryid ?? ""}">

                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400">
                                    <i class="fa-solid fa-chevron-down text-xs"></i>
                                </div>

                                <div class="edit-inventory-result  absolute left-0 bottom-full z-[9999] mt-1 hidden max-h-64 w-full overflow-y-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-white/10 dark:bg-[#111827]">
                            </div>

                            </div>

                        </td>

                        <td class="w-24 px-3 py-3 align-top">

                            <input
                                type="number"
                                min="1"
                                class="edit-item-qty w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                value="${data.qty ?? 1}">

                        </td>

                        <td class="w-24 px-3 py-3 align-top">

                            <input
                                type="text"
                                class="edit-item-uom w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-500 outline-none dark:border-white/10 dark:bg-[#1f2937] dark:text-gray-300"
                                readonly
                                value="${data.uom ?? ""}">

                        </td>

                        <td class="px-3 py-3 align-top">

                            <textarea
                                rows="2"
                                class="edit-item-note w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-700 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-white"
                                placeholder="Optional note">${data.recommend_note ?? ""}</textarea>

                        </td>

                        <td class="w-14 px-3 py-3 align-top text-center">

                            <button
                                type="button"
                                class="btn-remove-edit-item inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-200 bg-red-50 text-red-600 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                                <i class="fa-solid fa-trash text-xs"></i>

                            </button>

                        </td>

                    </tr>
                `;

    $("#edit_recommendation_detail_body").append(html);
}

async function loadEditRecommendation(hash) {
    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,
            type: "GET",
        });

        const h = res.header;

        $("#edit_recommendation_hash").val(hash);

        $("#edit_recommendation_docid").text(
            `Revise Recommendation - ${h.docid}`,
        );

        $("#edit_recommendation_status").html(statusBadge(h.status));

        $("#edit_recommendation_information").html(`

                        ${editInfoItem(
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

                        ${editInfoItem("Company", h.cpny_id)}
                        ${editInfoItem("Department", h.department_id)}
                        ${editInfoItem("Requester", h.user_peminta)}
                        ${editInfoItem("Ticket Number", h.ticketnbr)}
                        ${editInfoItem("Asset Number", h.assetnbr || "-")}
                        ${editInfoItem("IT PIC", h.recommend_pic || "-")}
                        ${editInfoItem("Purpose / Requirement", h.keperluan)}

                    `);

        $("#edit_recommend_type").val(h.recommend_type || "");
        $("#edit_waranty").val(h.waranty || "");
        $("#edit_recommendation").val(h.recommendation || "");

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

        $("#edit_recommendation_attachments").html(attachmentHtml);

        let reviseNoteHtml = "";

        const tracking = await $.ajax({
            url: `/it-recommendation/tracking/${h.docid}`,
            type: "GET",
        });

        const reviseTimeline = tracking.find((x) => x.status === "D");

        if (reviseTimeline?.note) {
            reviseNoteHtml = `
                            <div class="rounded-lg border border-orange-200 bg-white px-4 py-3 text-sm text-orange-700 dark:border-orange-500/20 dark:bg-[#111827] dark:text-orange-300">
                                ${reviseTimeline.note}
                            </div>
                        `;
        } else {
            reviseNoteHtml = `
                            <div class="rounded-lg border border-dashed border-orange-200 px-4 py-3 text-sm text-orange-500 dark:border-orange-500/20 dark:text-orange-300">
                                No revision note available
                            </div>
                        `;
        }

        $("#revision_note_container").html(reviseNoteHtml);

        $("#edit_recommendation_detail_body").html("");

        if (res.details.length > 0) {
            res.details.forEach((row) => {
                addEditRecommendationRow({
                    inventory_descr: row.recommend_descr,
                    inventoryid: row.inventoryid,
                    qty: row.qty,
                    uom: row.uom,
                    recommend_note: row.recommend_note,
                });
            });
        } else {
            addEditRecommendationRow();
        }

        openEditRecommendationModal();
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text:
                err.responseJSON?.message || "Failed load recommendation data",
        });
    }
}

$(document).on("click", ".edit-recommendation-btn", function () {
    const hash = $(this).data("id");

    window.history.pushState({}, "", `/edit-processitrecommendation/${hash}`);

    loadEditRecommendation(hash);
});

$("#btnCloseEditRecommendationModal").on("click", function () {
    closeEditRecommendationModal();
});

$("#btnAddEditItem").on("click", function () {
    addEditRecommendationRow();
});

$(document).on("click", ".btn-remove-edit-item", function () {
    $(this).closest("tr").remove();

    if ($("#edit_recommendation_detail_body tr").length === 0) {
        addEditRecommendationRow();
    }
});

$("#editRecommendationForm").on("submit", async function (e) {
    e.preventDefault();

    const hash = $("#edit_recommendation_hash").val();

    let details = [];
    $("#edit_recommendation_detail_body tr").each(function () {
        const row = $(this);

        const recommend_descr = row.find(".edit-inventory-search").val().trim();

        if (!recommend_descr) {
            return;
        }

        details.push({
            recommend_descr: recommend_descr,
            qty: row.find(".edit-item-qty").val(),
            uom: row.find(".edit-item-uom").val(),
            recommend_note: row.find(".edit-item-note").val(),
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

    const btn = $(this).find('button[type="submit"]');

    btn.prop("disabled", true).html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Resubmitting...
                    `);

    try {
        await $.ajax({
            url: `/it-recommendation/process/${hash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                recommend_type: $("#edit_recommend_type").val(),
                waranty: $("#edit_waranty").val(),
                recommendation: $("#edit_recommendation").val(),
                details: details,
            },
        });

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Recommendation resubmitted successfully",
            timer: 1800,
            showConfirmButton: false,
        });

        closeEditRecommendationModal();

        closeShowModal();

        table.ajax.reload(null, false);
    } catch (err) {
        let msg = err.responseJSON?.message || "Failed update recommendation";

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
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Resubmit Approval
                        `);
    }
});

const revisePath = window.location.pathname;

if (path.includes("/edit-processitrecommendation/")) {
    const hash = path.split("/").pop();

    loadEditRecommendation(hash);
}
