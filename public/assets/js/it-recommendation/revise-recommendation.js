function createEditRecommendationRow(data = {}) {
    return `

        <tr class="
            edit-detail-row

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
                            edit-inventory-search

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
                            duration-200

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

                        class="edit-inventory-id"

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
                        edit-inventory-result

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

            <td class="w-24 px-3 py-3 align-top">

                <input
                    type="number"

                    min="1"

                    class="
                        edit-item-qty

                        w-full

                        rounded-lg

                        border border-slate-200
                        dark:border-white/10

                        bg-slate-50
                        dark:bg-[#111827]

                        px-3 py-2.5

                        text-sm
                        text-slate-700
                        dark:text-white

                        outline-none

                        transition-all
                        duration-200

                        focus:border-indigo-500
                        focus:ring-2
                        focus:ring-indigo-500/20
                    "

                    value="${data.qty || 1}"
                >

            </td>

            <td class="w-24 px-3 py-3 align-top">

                <input
                    type="text"

                    readonly

                    class="
                        edit-item-uom

                        w-full

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
                        edit-item-note

                        w-full

                        rounded-lg

                        border border-slate-200
                        dark:border-white/10

                        bg-slate-50
                        dark:bg-[#111827]

                        px-3 py-2.5

                        text-sm
                        text-slate-700
                        dark:text-white

                        outline-none

                        transition-all
                        duration-200

                        focus:border-indigo-500
                        focus:ring-2
                        focus:ring-indigo-500/20
                    "

                    placeholder="Optional note"
                >${data.recommend_note || ""}</textarea>

            </td>

            <td class="
                w-14

                px-3 py-3

                align-top
                text-center
            ">

                <button
                    type="button"

                    class="
                        btn-remove-edit-item

                        inline-flex

                        h-9
                        w-9

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
                        duration-200

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
}
function addEditRecommendationRow(data = {}) {
    $("#edit_recommendation_detail_body").append(
        createEditRecommendationRow(data),
    );
}

function renderEditAttachments(files = []) {

    if (files.length === 0) {

        $("#edit_recommendation_attachments").html(`
            <div class="
                w-full
                rounded-lg
                border border-dashed border-slate-200
                dark:border-white/10
                px-4 py-6
                text-center text-sm text-slate-400
            ">
                No attachments
            </div>
        `);

        return;
    }

    const html = files.map(file => `

        <button
            type="button"

            class="
                preview-attachment

                inline-flex
                items-center
                gap-2

                rounded-lg

                border border-slate-200
                dark:border-white/10

                bg-slate-50
                dark:bg-white/[0.03]

                px-3 py-2

                text-xs
                text-slate-700
                dark:text-slate-300

                transition-all
                duration-200

                hover:bg-slate-100
                dark:hover:bg-white/[0.05]
            "

            data-url="${file.signed_url || ''}"
            data-name="${file.filename || 'Attachment'}"
        >

            <i class="
                fa-solid
                fa-paperclip

                text-slate-400
            "></i>

            <div class="
                max-w-[220px]
                truncate
            ">
                ${file.filename || "Attachment"}
            </div>

        </button>

    `).join("");

    $("#edit_recommendation_attachments").html(html);
}



function renderRevisionNote(tracking = []) {
    const reviseTimeline = tracking.find((row) => row.status === "D");

    if (reviseTimeline?.note) {
        $("#revision_note_container").html(`
            <div class="
                rounded-lg

                border border-orange-200
                dark:border-orange-500/20

                bg-white
                dark:bg-[#111827]

                px-4 py-3

                text-sm

                text-orange-700
                dark:text-orange-300
            ">
                ${reviseTimeline.note}
            </div>
        `);

        return;
    }

    $("#revision_note_container").html(`
        <div class="
            rounded-lg

            border
            border-dashed
            border-orange-200

            px-4 py-3

            text-sm

            text-orange-500

            dark:border-orange-500/20
            dark:text-orange-300
        ">
            No revision note available
        </div>
    `);
}

function renderEditRecommendationInfo(header) {
    $("#edit_recommendation_information").html(`

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

        ${infoItem("IT PIC", header.recommend_pic || "-")}

        ${infoItem("Purpose / Requirement", header.keperluan)}

    `);
}

function renderEditRecommendationDetails(details = []) {
    $("#edit_recommendation_detail_body").html("");

    if (details.length === 0) {
        addEditRecommendationRow();

        return;
    }

    details.forEach((row) => {
        addEditRecommendationRow({
            inventory_descr: row.recommend_descr,

            inventoryid: row.inventoryid,

            qty: row.qty,

            uom: row.uom,

            recommend_note: row.recommend_note,
        });
    });
}

async function loadEditRecommendation(hash) {
    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,

            type: "GET",
        });

        const header = res.header;

        $("#edit_recommendation_hash").val(hash);

        $("#edit_recommendation_docid").text(
            `Revise Recommendation - ${header.docid}`,
        );

        $("#edit_recommendation_status").html(statusBadge(header.status));

        renderEditRecommendationInfo(header);

        $("#edit_recommend_type").val(header.recommend_type || "");

        $("#edit_waranty").val(header.waranty || "");

        $("#edit_recommendation").val(header.recommendation || "");

        renderEditAttachments(res.attachments || []);

        const tracking = await $.ajax({
            url: `/it-recommendation/tracking/${header.docid}`,

            type: "GET",
        });

        renderRevisionNote(tracking);

        renderEditRecommendationDetails(res.details || []);

        modalState.reviseDirty = false;

        openEditRecommendationModal();
    } catch (err) {
        console.error(err);

        Swal.fire({
            icon: "error",

            title: "Error",

            text:
                err.responseJSON?.message || "Failed load recommendation data",
        });
    }
}

function collectEditRecommendationDetails() {
    const details = [];

    $("#edit_recommendation_detail_body tr").each(function () {
        const row = $(this);

        const recommendDescr = row.find(".edit-inventory-search").val().trim();

        if (!recommendDescr) {
            return;
        }

        details.push({
            recommend_descr: recommendDescr,

            qty: row.find(".edit-item-qty").val(),

            uom: row.find(".edit-item-uom").val(),

            recommend_note: row.find(".edit-item-note").val(),
        });
    });

    return details;
}

async function submitEditRecommendation() {
    const hash = $("#edit_recommendation_hash").val();

    const details = collectEditRecommendationDetails();

    if (details.length === 0) {
        Swal.fire({
            icon: "warning",

            title: "Validation",

            text: "Please select inventory item",
        });

        return false;
    }

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

            details,
        },
    });

    return true;
}

$(document).on("click", ".edit-recommendation-btn", function () {
    const hash = $(this).data("id");

    window.history.pushState({}, "", `/edit-processitrecommendation/${hash}`);

    loadEditRecommendation(hash);
});

$("#btnCloseEditRecommendationModal, #btnCloseEditRecommendationFooter").on(
    "click",
    function () {
        closeEditRecommendationModal();
    },
);

$("#btnAddEditItem").on("click", function () {
    addEditRecommendationRow();

    modalState.reviseDirty = true;
});

$(document).on("click", ".btn-remove-edit-item", function () {
    $(this).closest("tr").remove();

    modalState.reviseDirty = true;

    if ($("#edit_recommendation_detail_body tr").length === 0) {
        addEditRecommendationRow();
    }
});

$(document).on(
    "input change",
    `
        #editRecommendationForm input,
        #editRecommendationForm textarea,
        #editRecommendationForm select
    `,
    function () {
        modalState.reviseDirty = true;
    },
);

$("#editRecommendationForm").on("submit", async function (e) {
    e.preventDefault();

    const btn = $(this).find('button[type="submit"]');

    btn.prop("disabled", true).html(`

            <i class="
                fa-solid
                fa-spinner
                fa-spin

                text-xs
            "></i>

            Resubmitting...

        `);

    try {
        const success = await submitEditRecommendation();

        if (!success) {
            return;
        }

        Swal.fire({
            icon: "success",

            title: "Success",

            text: "Recommendation resubmitted successfully",

            timer: 1800,

            showConfirmButton: false,
        });

        closeEditRecommendationModal(true);

        closeShowModal(true);

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

                <i class="
                    fa-solid
                    fa-paper-plane

                    text-xs
                "></i>

                Resubmit Approval

            `);
    }
});

if (window.location.pathname.includes("/edit-processitrecommendation/")) {
    const hash = window.location.pathname.split("/").pop();

    loadEditRecommendation(hash);
}
