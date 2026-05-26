let currentDetailHash = null;
let currentDiscussionHash = null;

async function loadDetail(hash) {
    currentDetailHash = hash;

    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,

            type: "GET",
        });

        console.log("DETAIL RESPONSE", res);

        const header = res.header;

        renderHeaderInfo(header);

        renderRecommendationInfo(header);

        renderDetailItems(res.details || []);

        renderAttachments(res.attachments || []);

        const tracking = await $.ajax({
            url: `/it-recommendation/tracking/${header.docid}`,

            type: "GET",
        });

        renderTimeline(tracking || []);

        currentDiscussionHash = hash;

        $("#discussionFab").removeClass("hidden");

        $("#discussionPanel").addClass("hidden");

        await loadDiscussion();

        renderActions(header, res.permissions || {}, hash);

        openShowModal();
    } catch (err) {
        Swal.fire({
            icon: "error",

            title: "Error",

            text: err.responseJSON?.message || "Failed to load detail",
        });
    }
}

// async function renderCommentSection(header) {
//     if (["X"].includes(header.status)) {
//         $("#commentSection").addClass("hidden");

//         $("#show_comments").html("");

//         return;
//     }

//     $("#commentSection").removeClass("hidden");

//     const comments = await $.ajax({
//         url: `/it-recommendation/comments/${header.docid}`,

//         type: "GET",
//     });

//     renderComments(comments || []);
// }

function renderHeaderInfo(header) {

     console.log(header);
    $("#show_docid").text(header.docid || "-");

    $("#show_status_badge").html(statusBadge(header.status));

    $("#show_information").html(`

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

        ${infoItem(
            "Ticket Number",
            header.ticket_hash
                ? `
                    <a
                        href="/showticket/${header.ticket_hash}"
                        target="_blank"
                        class="
                            font-medium
                            text-blue-600
                            hover:text-blue-700
                            hover:underline
                        "
                    >
                        ${header.ticketnbr}
                    </a>
                `
                : (header.ticketnbr || "-")
        )}

        ${infoItem("Asset Number", header.assetnbr || "-")}

        ${infoItem("IT PIC", header.recommend_pic || "-")}

        ${infoItem("Purpose / Requirement", header.keperluan)}

    `);
}

function renderRecommendationInfo(header) {
    $("#show_recommendation_info").html(`

        ${infoItem("Recommendation Type", header.recommend_type || "-")}

        ${infoItem("Warranty", header.waranty || "-")}

        ${infoItem("Recommendation", header.recommendation || "-")}

    `);
}

function timelineNoteClass(status) {
    if (status === "R") {
        return `
            border-red-200
            bg-red-50
            text-red-700

            dark:border-red-500/20
            dark:bg-red-500/10
            dark:text-red-300
        `;
    }

    if (status === "D" || status === "I") {
        return `
            border-amber-200
            bg-amber-50
            text-amber-700

            dark:border-amber-500/20
            dark:bg-amber-500/10
            dark:text-amber-300
        `;
    }

    if (status === "X") {
        return `
            border-slate-200
            bg-slate-50
            text-slate-700

            dark:border-slate-500/20
            dark:bg-slate-500/10
            dark:text-slate-300
        `;
    }

    return `
        border-gray-200
        bg-gray-50
        text-gray-700

        dark:border-white/10
        dark:bg-white/[0.03]
        dark:text-gray-300
    `;
}

function renderTimeline(tracking = []) {
    let html = "";

    tracking.forEach((row) => {
        html += `

            <div class="flex gap-3">

                <div class="
                    flex
                    h-8 w-8
                    shrink-0

                    items-center
                    justify-center

                    rounded-lg

                    border border-slate-200
                    dark:border-white/10

                    bg-white
                    dark:bg-[#111827]
                ">

                    <i class="
                        fa-solid
                        text-xs

                        ${timelineIcon(row.status)}
                    "></i>

                </div>

                <div class="min-w-0 flex-1 pb-2">

                    <div class="
                        flex
                        flex-wrap
                        items-center
                        gap-2
                    ">

                        <h4 class="
                            text-sm
                            font-medium

                            text-slate-700
                            dark:text-slate-200
                        ">
                            ${row.title || "-"}
                        </h4>

                        ${statusBadge(row.status)}

                    </div>

                    <p class="
                        mt-1

                        text-xs

                        text-slate-500
                        dark:text-slate-400
                    ">
                        ${row.description || "-"}
                    </p>

                    <p class="
                        mt-1

                        text-[11px]

                        text-slate-400
                    ">
                        ${row.date || "-"}
                    </p>

                    ${
                        row.note
                            ? `
                                <div class="
                                    mt-2

                                    rounded-lg
                                    border

                                    px-3 py-2

                                    text-xs
                                    leading-relaxed

                                    ${timelineNoteClass(row.status)}
                                ">
                                    ${row.note}
                                </div>
                            `
                            : ""
                    }

                </div>

            </div>

        `;
    });

    $("#show_tracking").html(html);
}

// function renderComments(comments = []) {
//     let html = "";

//     if (comments.length === 0) {
//         html = `
//             <div class="
//                 rounded-lg

//                 border border-dashed border-slate-200
//                 dark:border-white/10

//                 px-4 py-6

//                 text-center
//                 text-sm

//                 text-slate-400
//             ">
//                 No comments yet
//             </div>
//         `;
//     } else {
//         comments.forEach((row) => {
//             html += `

//                 <div class="
//                     rounded-lg

//                     bg-slate-50
//                     dark:bg-white/[0.02]

//                     px-3 py-2
//                 ">

//                     <div class="
//                         flex
//                         items-center
//                         justify-between
//                         gap-3
//                     ">

//                         <div class="min-w-0">

//                             <div class="
//                                 truncate

//                                 text-[11px]
//                                 font-semibold
//                                 uppercase
//                                 tracking-wide

//                                 text-slate-500
//                                 dark:text-slate-400
//                             ">
//                                 ${row.name || row.username || "-"}
//                             </div>

//                         </div>

//                         <div class="
//                             shrink-0

//                             text-[10px]

//                             text-slate-400
//                         ">
//                             ${
//                                 row.message_date
//                                     ? new Date(row.message_date).toLocaleString(
//                                           "en-GB",
//                                           {
//                                               day: "2-digit",
//                                               month: "short",
//                                               hour: "2-digit",
//                                               minute: "2-digit",
//                                           },
//                                       )
//                                     : "-"
//                             }
//                         </div>

//                     </div>

//                     <div class="
//                         mt-1

//                         whitespace-pre-wrap
//                         break-words

//                         text-sm
//                         leading-relaxed

//                         text-slate-700
//                         dark:text-slate-300
//                     ">
//                         ${row.message || "-"}
//                     </div>

//                 </div>

//             `;
//         });
//     }

//     $("#show_comments").html(html);
// }

function renderProcessActionButton({ hash, className, icon, label }) {
    return `
        <button
            type="button"

            data-id="${hash}"

            class="
                ${className}

                inline-flex
                w-full

                items-center
                justify-center
                gap-2

                rounded-lg

                px-4 py-2.5

                text-sm
                font-semibold

                text-white

                transition-all
                duration-200
            "
        >

            <i class="
                fa-solid
                ${icon}

                text-xs
            "></i>

            ${label}

        </button>
    `;
}

function renderActions(header, permissions, hash) {
    let html = "";

    if (permissions.can_edit) {
        html += `
            <a
                href="/edititrecommendation/${hash}"

                class="
                    inline-flex
                    w-full

                    items-center
                    justify-center
                    gap-2

                    rounded-lg

                    bg-amber-500

                    px-4 py-2.5

                    text-sm
                    font-semibold

                    text-white

                    transition-all
                    duration-200

                    hover:bg-amber-600
                "
            >

                <i class="
                    fa-solid
                    fa-pen

                    text-xs
                "></i>

                Edit Request

            </a>
        `;
    }

    if (header.status === "W" && permissions.can_process && isITHardware) {
        html += renderProcessActionButton({
            hash,

            className: "process-btn bg-indigo-600 hover:bg-indigo-700",

            icon: "fa-gears",

            label: "Process Request",
        });
    }

    if (header.status === "I" && permissions.can_process && isITHardware) {
        html += renderProcessActionButton({
            hash,

            className:
                "edit-recommendation-btn bg-orange-500 hover:bg-orange-600",

            icon: "fa-rotate-left",

            label: "Revise Recommendation",
        });
    }

    if (permissions.can_cancel) {
        html += renderProcessActionButton({
            hash,

            className: "cancel-btn bg-red-600 hover:bg-red-700",

            icon: "fa-ban",

            label: "Cancel Request",
        });
    }

    if (permissions.can_approve) {
        html += `

            <div class="
              w-full flex justify-between gap-2
            ">

                <button
                    type="button"

                    data-docid="${header.docid}"

                    class="
                        approve-btn

                         inline-flex flex-1 items-center justify-center gap-2
                            rounded-lg bg-emerald-600
                            px-4 py-3
                            text-sm font-semibold text-white
                            transition hover:bg-emerald-700
                    "
                >

                    <i class="
                        fa-solid
                        fa-check

                        text-xs
                    "></i>

                    Approve

                </button>

                <button
                    type="button"

                    data-docid="${header.docid}"

                    class="
                        revise-approval-btn

                         inline-flex flex-1 items-center justify-center gap-2
                    rounded-lg bg-amber-500
                    px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-amber-600
                    "
                >

                    <i class="
                        fa-solid
                        fa-rotate-left

                        text-xs
                    "></i>

                    Revise

                </button>

                <button
                    type="button"

                    data-docid="${header.docid}"

                    class="
                        reject-approval-btn

                       inline-flex flex-1 items-center justify-center gap-2
                    rounded-lg bg-red-500
                    px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-red-600
                    "
                >

                    <i class="
                        fa-solid
                        fa-xmark

                        text-xs
                    "></i>

                    Reject

                </button>

            </div>

        `;
    }

    if (!html) {
        $("#show_header_actions").html("");
        $("#show_actions_card").addClass("hidden");
        return;
    }

    $("#show_header_actions").html(html);
    $("#show_actions_card").removeClass("hidden");
}

function renderDetailItems(details = []) {
    let html = "";

    if (details.length === 0) {
        html = `
            <tr>

                <td
                    colspan="5"

                    class="
                        px-3 py-8

                        text-center
                        text-sm

                        text-slate-400
                    "
                >
                    No recommendation items
                </td>

            </tr>
        `;
    } else {
        details.forEach((row) => {
            html += `

                <tr class="
                    border-b border-slate-100
                    dark:border-white/5
                ">

                    <td class="
                        px-3 py-2.5

                        text-slate-700
                        dark:text-slate-300
                    ">
                        ${row.recommend_descr || "-"}
                    </td>

                    <td class="
                        px-3 py-2.5

                        text-slate-700
                        dark:text-slate-300
                    ">
                        ${row.qty || "-"}
                    </td>

                    <td class="
                        px-3 py-2.5

                        text-slate-700
                        dark:text-slate-300
                    ">
                        ${row.uom || "-"}
                    </td>

                    <td class="
                        px-3 py-2.5

                        text-slate-700
                        dark:text-slate-300
                    ">
                        ${row.category || "-"}
                    </td>

                    <td class="
                        px-3 py-2.5

                        text-slate-700
                        dark:text-slate-300
                    ">
                        ${row.recommend_note || "-"}
                    </td>

                </tr>

            `;
        });
    }

    $("#show_detail_items").html(html);
}

$(document).on("click", 'a[href*="/showitrecommendation/"]', function (e) {
    e.preventDefault();

    const href = $(this).attr("href");

    const hash = href.split("/").pop();

    window.history.pushState({}, "", href);

    loadDetail(hash);
});

// $(document).on("click", "#btnSubmitComment", async function () {
//     const hash = currentDetailHash;

//     const message = $("#comment_message").val().trim();

//     if (!message) {
//         Swal.fire({
//             icon: "warning",

//             title: "Validation",

//             text: "Comment cannot be empty",
//         });

//         return;
//     }

//     const btn = $(this);

//     btn.prop("disabled", true).html(`
//             <i class="
//                 fa-solid
//                 fa-spinner
//                 fa-spin
//                 text-xs
//             "></i>

//             Sending...
//         `);

//     try {
//         await $.ajax({
//             url: `/it-recommendation/comment/${hash}`,

//             type: "POST",

//             headers: {
//                 "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
//             },

//             data: {
//                 message,
//             },
//         });

//         $("#comment_message").val("");

//         Swal.fire({
//             icon: "success",

//             title: "Success",

//             text: "Comment submitted",

//             timer: 1500,

//             showConfirmButton: false,
//         });

//         loadDetail(hash);
//     } catch (err) {
//         Swal.fire({
//             icon: "error",

//             title: "Error",

//             text: err.responseJSON?.message || "Failed submit comment",
//         });
//     } finally {
//         btn.prop("disabled", false).html(`
//                 <i class="
//                     fa-solid
//                     fa-paper-plane
//                     text-xs
//                 "></i>

//                 Submit Comment
//             `);
//     }
// });

const detailPath = window.location.pathname;

if (detailPath.includes("/showitrecommendation/")) {
    const hash = detailPath.split("/").pop();

    loadDetail(hash);
}

async function loadDiscussion() {
    const res = await $.ajax({
        url: `/it-recommendation/comments/${currentDiscussionHash}`,
        type: "GET",
    });

    let messages = [];

    if (Array.isArray(res)) {
        messages = res;
    } else {
        messages = res.data ?? res.comments ?? [];
    }

    renderDiscussionMessages(messages);
}
async function sendDiscussion() {
    const message = $("#discussionInput").val().trim();

    if (!message) {
        return;
    }

    await $.ajax({
        url: `/it-recommendation/comment/${currentDiscussionHash}`,

        type: "POST",

        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },

        data: {
            message,
        },
    });

    $("#discussionInput").val("");

    await loadDiscussion();
}
function renderDiscussionMessages(messages = []) {
    let html = "";

    if (!Array.isArray(messages) || messages.length === 0) {
        $("#discussionMessages").html(`
            <div class="
                flex
                h-full
                items-center
                justify-center
            ">
                <div class="
                    text-center
                    text-sm
                    text-slate-400
                ">
                    No discussion yet
                </div>
            </div>
        `);

        return;
    }

    messages.forEach((row) => {
        const mine =
            String(row.username || "").toUpperCase() ===
            String(window.currentUser || "").toUpperCase();

        html += `
            <div class="
                flex
                ${mine ? "justify-end" : "justify-start"}
            ">

                <div class="max-w-[85%]">

                    <div class="
                        mb-1
                        px-1

                        text-[10px]
                        font-semibold
                        uppercase
                        tracking-wide

                        ${mine ? "text-right text-blue-500" : "text-slate-500"}
                    ">
                        ${row.name || row.username || "-"}
                    </div>

                    <div class="
                        rounded-2xl

                        px-4 py-2.5

                        text-sm
                        leading-relaxed

                        ${
                            mine
                                ? `
                                    bg-blue-600
                                    text-white
                                `
                                : `
                                    border border-slate-200
                                    bg-white
                                    text-slate-700

                                    dark:border-white/10
                                    dark:bg-[#111827]
                                    dark:text-slate-200
                                `
                        }
                    ">
                        ${row.message || "-"}
                    </div>

                    <div class="
                        mt-1
                        px-1

                        text-[10px]
                        text-slate-400

                        ${mine ? "text-right" : ""}
                    ">
                        ${
                            row.message_date
                                ? new Date(row.message_date).toLocaleString(
                                      "en-GB",
                                      {
                                          day: "2-digit",
                                          month: "short",
                                          year: "numeric",
                                          hour: "2-digit",
                                          minute: "2-digit",
                                      },
                                  )
                                : "-"
                        }
                    </div>

                </div>

            </div>
        `;
    });

    $("#discussionMessages").html(html);

    const container = document.getElementById("discussionMessages");

    if (container) {
        container.scrollTop = container.scrollHeight;
    }
}
$(document).on("click", "#discussionFab button", function () {
    $("#discussionPanel").removeClass("hidden");

    setTimeout(() => {
        $("#discussionInput").trigger("focus");
    }, 100);
});

$(document).on("click", "#btnCloseDiscussion", function () {
    $("#discussionPanel").addClass("hidden");
});

$(document).on("click", "#btnSendDiscussion", async function () {
    const btn = $(this);

    btn.prop("disabled", true);

    try {
        await sendDiscussion();
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed submit discussion",
        });
    } finally {
        btn.prop("disabled", false);
    }
});

$(document).on("keydown", "#discussionInput", async function (e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();

        try {
            await sendDiscussion();
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: err.responseJSON?.message || "Failed submit discussion",
            });
        }
    }
});
