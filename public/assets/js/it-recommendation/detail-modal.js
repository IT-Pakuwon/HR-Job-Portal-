async function loadDetail(hash) {
    currentDetailHash = hash;

    try {
        const res = await $.ajax({
            url: `/it-recommendation/detail/${hash}`,
            type: "GET",
        });

        const h = res.header;

        $("#process_status_badge").html(statusBadge(h.status));

        $("#process_hash").val(hash);

        $("#process_docid").text(`${h.docid}`);

        renderHeaderInfo(h);

        renderRecommendationInfo(h);

        renderDetailItems(res.details);

        renderAttachments(res.attachments);

        const tracking = await $.ajax({
            url: `/it-recommendation/tracking/${h.docid}`,
            type: "GET",
        });

        renderTimeline(tracking);

        if (["X"].includes(h.status)) {
            $("#commentSection").addClass("hidden");

            $("#show_comments").html("");
        } else {
            $("#commentSection").removeClass("hidden");

            const comments = await $.ajax({
                url: `/it-recommendation/comments/${h.docid}`,
                type: "GET",
            });

            renderComments(comments);
        }
        renderActions(h, res.permissions, hash);

        openShowModal();
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed to load detail",
        });
    }
}

function renderHeaderInfo(h) {
    $("#show_docid").text(h.docid);

    $("#show_status_badge").html(statusBadge(h.status));

    $("#show_information").html(`

                ${infoItem(
                    "Date",
                    h.itrecommend_date
                        ? new Date(h.itrecommend_date).toLocaleDateString(
                              "en-GB",
                              {
                                  day: "2-digit",
                                  month: "short",
                                  year: "numeric",
                              },
                          )
                        : "-",
                )}

                ${infoItem("Company", h.cpny_id)}
                ${infoItem("Department", h.department_id)}
                ${infoItem("Requester", h.user_peminta)}
                ${infoItem("Ticket Number", h.ticketnbr)}
                ${infoItem("Asset Number", h.assetnbr || "-")}
                ${infoItem("IT PIC", h.recommend_pic || "-")}
                ${infoItem("Purpose / Requirement", h.keperluan)}

            `);
}

function renderRecommendationInfo(h) {
    $("#show_recommendation_info").html(`

        ${infoItem("Recommendation Type", h.recommend_type || "-")}
        ${infoItem("Warranty", h.waranty || "-")}
        ${infoItem("Recommendation", h.recommendation || "-")}

    `);
}

function renderTimeline(tracking) {
    let html = "";

    tracking.forEach((row) => {
        let noteClass =
            "border-gray-200 bg-gray-50 text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300";

        if (row.status === "R") {
            noteClass =
                "border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300";
        } else if (row.status === "D" || row.status === "I") {
            noteClass =
                "border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300";
        } else if (row.status === "X") {
            noteClass =
                "border-slate-200 bg-slate-50 text-slate-700 dark:border-slate-500/20 dark:bg-slate-500/10 dark:text-slate-300";
        }

        html += `
                        <div class="flex gap-3">

                            <div
                                class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-[#111827]">

                                <i class="fa-solid text-xs ${timelineIcon(row.status)}"></i>

                            </div>

                            <div class="min-w-0 flex-1 pb-2">

                                <div class="flex flex-wrap items-center gap-2">

                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        ${row.title}
                                    </h4>

                                    ${statusBadge(row.status)}

                                </div>

                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    ${row.description ?? "-"}
                                </p>

                                <p class="mt-1 text-[11px] text-gray-400">
                                    ${row.date ?? "-"}
                                </p>

                                ${
                                    row.note
                                        ? `
                                    <div class="
                                        mt-2 rounded-lg px-3 py-2 text-xs leading-relaxed
                                        ${
                                            row.status === "R"
                                                ? "border border-red-200 bg-red-50 text-red-700 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300"
                                                : row.status === "D" ||
                                                    row.status === "I"
                                                  ? "border border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300"
                                                  : "border border-gray-200 bg-gray-50 text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-300"
                                        }
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

function renderComments(comments) {
    let html = "";

    if (comments.length === 0) {
        html = `
            <div class="rounded-lg border border-dashed border-gray-200 px-4 py-6 text-center text-sm text-gray-400 dark:border-white/10">
                No comments yet
            </div>
        `;
    } else {
        comments.forEach((row) => {
            html += `
                <div class="rounded-lg bg-gray-50 px-3 py-2 dark:border-white/10 dark:bg-white/[0.02]">

                    <div class="flex items-center justify-between gap-3">

                        <div class="min-w-0">

                            <div class="truncate text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                                ${row.name ?? row.username ?? "-"}
                            </div>

                        </div>

                        <div class="shrink-0 text-[10px] text-gray-400">
                            ${
                                row.message_date
                                    ? new Date(row.message_date).toLocaleString(
                                          "en-GB",
                                          {
                                              day: "2-digit",
                                              month: "short",
                                              hour: "2-digit",
                                              minute: "2-digit",
                                          },
                                      )
                                    : "-"
                            }
                        </div>

                    </div>

                    <div class="mt-1 whitespace-normal break-words text-sm leading-relaxed text-gray-700 dark:text-gray-300">
                        ${row.message ?? "-"}
                    </div>

                </div>
            `;
        });
    }

    $("#show_comments").html(html);
}

function renderActions(h, permissions, hash) {
    let html = "";

    if (permissions.can_edit) {
        html += `
            <a
                href="/edititrecommendation/${hash}"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600">

                <i class="fa-solid fa-pen text-xs"></i>
                Edit Request

            </a>
        `;
    }

    if (h.status === "W" && permissions.can_process && isITHardware) {
        html += `
            <button
                type="button"
                data-id="${hash}"
                class="process-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">

                <i class="fa-solid fa-gears text-xs"></i>
                Process Request

            </button>
        `;
    }

    if (h.status === "I" && permissions.can_process && isITHardware) {
        html += `
            <button
                type="button"
                data-id="${hash}"
                class="edit-recommendation-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-orange-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-orange-600">

                <i class="fa-solid fa-rotate-left text-xs"></i>
                Revise Recommendation

            </button>
        `;
    }

    if (permissions.can_cancel) {
        html += `
            <button
                type="button"
                data-id="${hash}"
                class="cancel-btn mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">

                <i class="fa-solid fa-ban text-xs"></i>
                Cancel Request

            </button>
        `;
    }

    if (permissions.can_approve) {
        html += `
            <div class="flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="approve-btn inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">

                    <i class="fa-solid fa-check text-xs"></i>
                    Approve

                </button>

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="revise-approval-btn inline-flex items-center justify-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-100 dark:border-amber-500/20 dark:bg-amber-500/10 dark:text-amber-300">

                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    Revise Recommendation

                </button>

                <button
                    type="button"
                    data-docid="${h.docid}"
                    class="reject-approval-btn inline-flex items-center justify-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-100 dark:border-red-500/20 dark:bg-red-500/10 dark:text-red-300">

                    <i class="fa-solid fa-xmark text-xs"></i>
                    Reject

                </button>

            </div>
        `;
    }

    if (!html) {
        $("#show_header_actions").addClass("hidden").html("");
    } else {
        $("#show_header_actions").removeClass("hidden").html(html);
    }
}

function renderDetailItems(details) {
    let html = "";

    if (details.length === 0) {
        html = `
            <tr>
                <td colspan="5"
                    class="px-3 py-8 text-center text-sm text-gray-400">
                    No recommendation items
                </td>
            </tr>
        `;
    } else {
        details.forEach((row) => {
            html += `
                <tr class="border-b border-gray-100 dark:border-white/5">

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.recommend_descr ?? "-"}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.qty ?? "-"}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.uom ?? "-"}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.category ?? "-"}
                    </td>

                    <td class="px-3 py-2.5 text-gray-700 dark:text-gray-300">
                        ${row.recommend_note ?? "-"}
                    </td>

                </tr>
            `;
        });
    }

    $("#show_detail_items").html(html);
}
$(document).on("click", 'a[href*="/showitrecommendation/"]', function (e) {
    const href = $(this).attr("href");

    if (href.includes("/showitrecommendation/")) {
        e.preventDefault();

        const hash = href.split("/").pop();

        window.history.pushState({}, "", href);

        loadDetail(hash);
    }
});

$("#btnCloseShowModal").on("click", function () {
    closeShowModal();
});
$(document).on("click", "#btnSubmitComment", async function () {
    const hash = currentDetailHash;

    const message = $("#comment_message").val().trim();

    if (!message) {
        Swal.fire({
            icon: "warning",
            title: "Validation",
            text: "Comment cannot be empty",
        });

        return;
    }

    const btn = $(this);

    btn.prop("disabled", true).html(`
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                        Sending...
                    `);

    try {
        await $.ajax({
            url: `/it-recommendation/comment/${hash}`,
            type: "POST",

            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },

            data: {
                message: message,
            },
        });

        $("#comment_message").val("");

        Swal.fire({
            icon: "success",
            title: "Success",
            text: "Comment submitted",
            timer: 1500,
            showConfirmButton: false,
        });

        loadDetail(hash);
    } catch (err) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: err.responseJSON?.message || "Failed submit comment",
        });
    } finally {
        btn.prop("disabled", false).html(`
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Submit Comment
                        `);
    }
});

const detailPath = window.location.pathname;

if (path.includes("/showitrecommendation/")) {
    const hash = path.split("/").pop();

    loadDetail(hash);
}
