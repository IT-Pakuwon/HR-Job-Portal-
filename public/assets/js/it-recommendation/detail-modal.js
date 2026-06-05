let currentDetailHash = null;
let currentDiscussionHash = null;

$(document).on('click', '#btnPrintRecommendation', function () {
    if (!currentDetailHash) return;
    const url = (window.ITRecommendationRoutes.print || '').replace('__HASH__', currentDetailHash);
    window.open(url, '_blank');
});

async function loadDetail(hash) {
    currentDetailHash = hash;

    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,

            type: "GET",
        });

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
    $("#show_docid").text(`IT Recommendation - ${header.docid || "-"}`);

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

function timelineBadgeColor(s) {
    switch (s) {
        case 'A':  return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
        case 'R':  return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
        case 'D':  return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
        case 'I':  return 'bg-orange-100 text-orange-600 dark:bg-orange-500/20 dark:text-orange-400';
        case 'P':  return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
        case 'C':  return 'bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400';
        case 'IT': return 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400';
        case 'X':  return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
        default:   return 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400';
    }
}

function timelineBadgeIcon(s) {
    switch (s) {
        case 'A':  return 'fa-check';
        case 'R':  return 'fa-xmark';
        case 'D':  return 'fa-pen';
        case 'I':  return 'fa-rotate-left';
        case 'P':  return 'fa-hourglass-half';
        case 'C':  return 'fa-flag-checkered';
        case 'IT': return 'fa-gears';
        case 'X':  return 'fa-ban';
        default:   return 'fa-paper-plane';
    }
}

function timelinePill(s, label) {
    const cls = {
        'A':  'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
        'R':  'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
        'D':  'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        'I':  'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-300',
        'P':  'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
        'C':  'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
        'IT': 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
        'X':  'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400',
    }[s] ?? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300';

    return `<span class="inline-flex shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold ${cls}">${label || s}</span>`;
}

const TIMELINE_INITIAL = 5;

function renderTimeline(tracking = []) {
    if (!tracking.length) {
        $("#show_tracking").html(`
            <div class="rounded-lg border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400 dark:border-white/10">
                No approval workflow available.
            </div>
        `);
        return;
    }

    const total    = tracking.length;
    const hasMore  = total > TIMELINE_INITIAL;
    const hiddenCount = total - TIMELINE_INITIAL;

    const items = tracking.map((row, index) => {
        const isLast        = index === total - 1;
        const isLastVisible = hasMore && index === TIMELINE_INITIAL - 1;
        const isHidden      = hasMore && index >= TIMELINE_INITIAL;
        const s             = row.status ?? '';
        const showNote      = row.note && ['R', 'D', 'I'].includes(s);

        return `
            <div class="relative flex gap-4${isHidden ? ' tl-extra hidden' : ''}">
                <div class="flex flex-col items-center">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg ${timelineBadgeColor(s)}">
                        <i class="fa-solid ${timelineBadgeIcon(s)} text-xs"></i>
                    </div>
                    ${!isLast ? `<div class="mt-1 w-px flex-1 bg-slate-200 dark:bg-white/10"></div>` : ''}
                </div>
                <div class="min-w-0 flex-1 ${(isLast || isLastVisible) ? 'pb-0' : 'pb-6'}${isLastVisible ? ' tl-last-visible-content' : ''}">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                ${row.title || '-'}
                            </p>
                            ${row.description ? `<p class="mt-1 text-sm font-semibold text-slate-700 dark:text-slate-200">${row.description}</p>` : ''}
                            ${row.date ? `<p class="mt-1 text-xs text-slate-400 dark:text-slate-500">${row.date}</p>` : ''}
                        </div>
                        ${timelinePill(s, row.label)}
                    </div>
                    ${showNote ? `
                        <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-300">
                            ${row.note}
                        </div>
                    ` : ''}
                </div>
            </div>
        `;
    }).join('');

    const showMoreBtn = hasMore ? `
        <button id="timelineToggle" type="button" data-expanded="false" data-count="${hiddenCount}"
            class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.02] dark:text-slate-400 dark:hover:bg-white/[0.05]">
            <i class="fa-solid fa-chevron-down text-[10px]"></i>
            ${hiddenCount} more
        </button>
    ` : '';

    $("#show_tracking").html(`<div>${items}</div>${showMoreBtn}`);
}

$(document).on("click", "#timelineToggle", function () {
    const expanded = $(this).data("expanded");
    const count    = $(this).data("count");

    if (expanded) {
        $(".tl-extra").addClass("hidden");
        $(".tl-last-visible-content").removeClass("pb-6").addClass("pb-0");
        $(this).data("expanded", false).html(
            `<i class="fa-solid fa-chevron-down text-[10px]"></i> ${count} more`
        );
    } else {
        $(".tl-extra").removeClass("hidden");
        $(".tl-last-visible-content").removeClass("pb-0").addClass("pb-6");
        $(this).data("expanded", true).html(
            `<i class="fa-solid fa-chevron-up text-[10px]"></i> Show less`
        );
    }
});

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
    // ── FOOTER ACTIONS (Edit / Process / Cancel / Revise Recommendation) ──
    let footerHtml = "";

    if (permissions.can_edit) {
        footerHtml += `
            <a href="/edititrecommendation/${hash}"
                class="inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600">
                <i class="fa-solid fa-pen text-xs"></i>
                Edit Request
            </a>
        `;
    }

    if (header.status === "W" && permissions.can_process && isITHardware) {
        footerHtml += `
            <button type="button" data-id="${hash}"
                class="process-btn inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700">
                <i class="fa-solid fa-gears text-xs"></i>
                Process Request
            </button>
        `;
    }

    if (header.status === "I" && permissions.can_process && isITHardware) {
        footerHtml += `
            <button type="button" data-id="${hash}"
                class="edit-recommendation-btn inline-flex items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-orange-600">
                <i class="fa-solid fa-rotate-left text-xs"></i>
                Revise Recommendation
            </button>
        `;
    }

    if (permissions.can_cancel) {
        footerHtml += `
            <button type="button" data-id="${hash}"
                class="cancel-btn inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700">
                <i class="fa-solid fa-ban text-xs"></i>
                Cancel Request
            </button>
        `;
    }

    $("#show_footer_actions").html(footerHtml);

    // ── APPROVAL ACTIONS (Approve / Revise / Reject) ──
    if (permissions.can_approve) {
        $("#show_approval_actions_wrapper").removeClass("hidden").html(`
            <div class="flex w-full gap-2">
                <button type="button" data-docid="${header.docid}"
                    class="approve-btn inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    <i class="fa-solid fa-check text-xs"></i> Approve
                </button>
                <button type="button" data-docid="${header.docid}"
                    class="revise-approval-btn inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Revise
                </button>
                <button type="button" data-docid="${header.docid}"
                    class="reject-approval-btn inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600">
                    <i class="fa-solid fa-xmark text-xs"></i> Reject
                </button>
            </div>
        `);
    } else {
        $("#show_approval_actions_wrapper").addClass("hidden").html("");
    }

    // ── REVISION BANNER (latest note when document is in revision state) ──
    const notes = permissions.notes || [];
    const reviseNote = notes.find(n => n.note);
    if (reviseNote?.note) {
        $("#show_revision_note").text(reviseNote.note);
        $("#show_revision_banner").removeClass("hidden");
    } else {
        $("#show_revision_banner").addClass("hidden");
        $("#show_revision_note").html("");
    }
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

$(document).on("click", ".cancel-btn", async function () {
    const hash = $(this).data("id");

    const result = await Swal.fire({
        icon: "warning",
        title: "Cancel Request?",
        text: "This action cannot be undone.",
        showCancelButton: true,
        confirmButtonText: "Yes, Cancel",
        confirmButtonColor: "#dc2626",
    });

    if (!result.isConfirmed) return;

    try {
        await $.ajax({
            url: `/it-recommendation/cancel/${hash}`,
            type: "POST",
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        });

        itrToast('success', 'Request cancelled');

        table.ajax.reload(null, false);

        if (currentDetailHash) {
            await loadDetail(currentDetailHash);
        }
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed to cancel request",
        });
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
