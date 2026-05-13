async function openDetailModal(id) {
    openModal("#detailModal");

    renderDetailLoading();

    try {
        const res = await fetchDetail(id);

        const access = res.access ?? {};

        access.can_approve = res.can_approve ?? false;

        $("#detailModalDocId").text(access.docid ?? "-");

        $("#detailModalStatus").html(renderStatusBadge(access.status));
        $('#btnPrintAccess').attr(
            'data-id',
            access.eid
        );

        currentDiscussionHash = id;

        $("#discussionFab").removeClass("hidden");

        $("#discussionPanel").addClass("hidden");


        renderDetailInfo(access);

        renderDetailItems(res.details ?? []);

        renderDetailAttachments(res.attachments ?? [], access);

        renderDetailActions(access);

        renderDetailActivity(
            normalizeActivities(res)
        );

        // renderDiscussionSection(access);

        // await loadDiscussion(id);

        await loadDiscussion(currentDiscussionHash);
    } catch (xhr) {
        console.error(xhr);

        renderDetailError(xhr);
    }
}

async function fetchDetail(id) {
    return $.ajax({
        url: `/access-request/detail/${id}`,
        type: "GET",
    });
}

function renderDetailInfo(access) {

    $("#detailInfoContainer").html(`

        <div class="
            rounded-2xl
            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]

            shadow-sm
            dark:shadow-[0_10px_40px_rgba(0,0,0,.25)]

            overflow-hidden
        ">

            <div class="
                grid grid-cols-1
                gap-x-10 gap-y-5
                px-6 py-5
                sm:grid-cols-2
            ">

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-[11px] font-bold uppercase
                        tracking-[.16em]

                        text-slate-500
                        dark:text-slate-500
                    ">
                        Company
                    </p>

                    <span class="
                        text-slate-400
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">
                        ${access.cpny_id ?? "-"}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-[11px] font-bold uppercase
                        tracking-[.16em]

                        text-slate-500
                        dark:text-slate-500
                    ">
                        Date
                    </p>

                    <span class="
                        text-slate-400
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm

                        text-slate-600
                        dark:text-slate-300
                    ">
                        ${formatDate(access.created_at)}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-[11px] font-bold uppercase
                        tracking-[.16em]

                        text-slate-500
                        dark:text-slate-500
                    ">
                        Requester
                    </p>

                    <span class="
                        text-slate-400
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">
                        ${access.user_peminta ?? "-"}
                    </p>

                </div>

                <div class="flex items-center gap-2">

                    <p class="
                        min-w-[110px]
                        text-[11px] font-bold uppercase
                        tracking-[.16em]

                        text-slate-500
                        dark:text-slate-500
                    ">
                        Department
                    </p>

                    <span class="
                        text-slate-400
                        dark:text-slate-600
                    ">
                        :
                    </span>

                    <p class="
                        text-sm font-semibold

                        text-slate-700
                        dark:text-slate-100
                    ">
                        ${access.department_id ?? "-"}
                    </p>

                </div>

            </div>

            <div class="
                flex items-start justify-between gap-4

                border-t border-slate-200
                dark:border-white/[0.06]

                px-6 py-5
            ">

                <div class="min-w-0 flex-1">

                    <p class="
                        text-[11px] font-bold uppercase
                        tracking-[.16em]

                        text-slate-500
                        dark:text-slate-500
                    ">
                        Purpose / Notes
                    </p>

                    <p class="
                        mt-2
                        whitespace-normal
                        break-words

                        text-sm leading-7

                        text-slate-700
                        dark:text-slate-200
                    ">
                        ${access.keperluan ?? "-"}
                    </p>

                </div>

                <div class="shrink-0">

                    <div class="flex items-center gap-2">

                        <span class="
                            text-[11px] font-bold uppercase
                            tracking-[.16em]

                            text-slate-500
                            dark:text-slate-500
                        ">
                            Request Type :
                        </span>

                        ${renderTypeBadge(access.access_type)}

                    </div>

                </div>

            </div>

        </div>

    `);
}
function renderDetailItems(details = []) {

    const currentUsername =
        window.authUsername ?? '';

    const currentRole =
        window.authRole ?? '';

    let rows = "";

    details.forEach((item) => {

        const canSeePassword =
            currentUsername === item.created_by ||
            ['ITHARDWARE', 'ITSOFTWARE']
                .includes(currentRole);

        rows += `

            <tr class="
                border-b border-slate-100
                dark:border-white/[0.06]
                align-top
            ">

                <td class="px-5 py-4 w-[26%]">

                    <div class="space-y-2">

                        <p class="
                            text-sm font-semibold

                            text-slate-700
                            dark:text-slate-100
                        ">
                            ${item.access_descr ?? "-"}
                        </p>

                        <span class="
                            inline-flex rounded-lg px-2.5 py-1
                            text-[11px] font-semibold

                            ${
                                item.group_category === "HARDWARE"
                                    ? `
                                        bg-blue-100 text-blue-700
                                        dark:bg-blue-500/15 dark:text-blue-300
                                    `
                                    : `
                                        bg-emerald-100 text-emerald-700
                                        dark:bg-emerald-500/15 dark:text-emerald-300
                                    `
                            }
                        ">
                            ${item.group_category ?? "-"}
                        </span>

                    </div>

                </td>

                <td class="px-5 py-4 w-[12%]">

                    ${buildStatusPill(item.status)}

                </td>

                <td class="px-5 py-4 w-[24%]">

                    <div class="
                        min-h-[72px]
                        rounded-xl

                        border border-slate-200
                        dark:border-white/[0.06]

                        bg-slate-50
                        dark:bg-white/[0.03]

                        px-4 py-3
                    ">

                        <p class="
                            whitespace-normal
                            break-words

                            text-sm

                            text-slate-700
                            dark:text-slate-200
                        ">
                            ${item.access_response ?? "-"}
                        </p>

                    </div>

                </td>

                <td class="px-5 py-4 w-[18%]">

                    <div class="
                        rounded-xl

                        border border-slate-200
                        dark:border-white/[0.06]

                        bg-slate-50
                        dark:bg-white/[0.03]

                        px-4 py-3

                        text-sm

                        text-slate-700
                        dark:text-slate-200

                        break-all
                    ">

                        ${item.access_username ?? "-"}

                    </div>

                </td>

                <td class="px-5 py-4 w-[20%]">

                    <div class="
                        rounded-xl

                        border border-slate-200
                        dark:border-white/[0.06]

                        bg-slate-50
                        dark:bg-white/[0.03]

                        px-4 py-3

                        text-sm

                        text-slate-700
                        dark:text-slate-200

                        break-all
                    ">

                        ${
                            item.access_password
                                ? (
                                    canSeePassword
                                        ? item.access_password
                                        : '••••••••'
                                )
                                : '-'
                        }

                    </div>

                </td>

            </tr>

        `;
    });

    if (!rows) {

        rows = `

            <tr>

                <td
                    colspan="5"
                    class="
                        px-4 py-10
                        text-center

                        text-sm

                        text-slate-500
                        dark:text-slate-400
                    "
                >

                    No detail item available

                </td>

            </tr>

        `;
    }

    $("#detailItemsContainer").html(`

        <div class="
            overflow-hidden
            rounded-2xl

            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]

            shadow-sm
            dark:shadow-[0_10px_40px_rgba(0,0,0,.25)]
        ">

            <div class="
                border-b border-slate-200
                dark:border-white/[0.06]

                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider

                    text-slate-700
                    dark:text-slate-200
                ">
                    Request Detail
                </h3>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="
                        bg-slate-50
                        dark:bg-white/[0.02]
                    ">

                        <tr>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-500
                            ">
                                Access Item
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-500
                            ">
                                Status
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-500
                            ">
                                Response
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-500
                            ">
                                Username
                            </th>

                            <th class="
                                px-5 py-3 text-left
                                text-xs font-semibold uppercase
                                tracking-wider

                                text-slate-500
                                dark:text-slate-500
                            ">
                                Password
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        ${rows}

                    </tbody>

                </table>

            </div>

        </div>

    `);
}


function renderDetailAttachments(files = []) {

    let html = `

        <div class="
            rounded-2xl

            border border-slate-200
            dark:border-white/[0.06]

            bg-white
            dark:bg-[#111c2d]

            overflow-hidden

            shadow-sm
            dark:shadow-[0_10px_40px_rgba(0,0,0,.25)]
        ">

            <div class="
                border-b border-slate-200
                dark:border-white/[0.06]

                px-5 py-4
            ">

                <h3 class="
                    text-sm font-bold uppercase
                    tracking-[.16em]

                    text-slate-700
                    dark:text-slate-300
                ">
                    Attachment
                </h3>

            </div>

            <div class="space-y-3 p-5">

    `;

    if (!files.length) {

        html += `

            <div class="
                rounded-xl

                border border-slate-200
                dark:border-white/[0.06]

                bg-slate-50
                dark:bg-white/[0.02]

                px-4 py-4

                text-sm

                text-slate-500
                dark:text-slate-400
            ">

                No attachment available

            </div>

        `;

    } else {

        files.forEach((file) => {

            html += `

                <div class="
                    flex items-center justify-between
                    gap-3

                    rounded-xl

                    border border-slate-200
                    dark:border-white/[0.06]

                    bg-slate-50
                    dark:bg-white/[0.02]

                    px-4 py-3

                    transition
                    hover:bg-slate-100
                    dark:hover:bg-white/[0.04]
                ">

                    <div class="
                        flex items-center gap-3
                        min-w-0 flex-1
                    ">

                        <div class="
                            flex h-10 w-10 shrink-0
                            items-center justify-center

                            rounded-xl

                            border border-slate-200
                            dark:border-white/[0.06]

                            bg-white
                            dark:bg-[#0b1525]
                        ">

                            <i class="
                                fa-regular fa-file

                                text-slate-500
                                dark:text-slate-300
                            "></i>

                        </div>

                        <div class="min-w-0 flex-1">

                            <p class="
                                truncate

                                text-sm font-medium

                                text-slate-700
                                dark:text-slate-200
                            ">

                                ${file.display_name ?? file.filename ?? "-"}

                            </p>

                        </div>

                    </div>

                    <a
                        href="${file.url}"
                        target="_blank"
                        class="
                            inline-flex h-9 w-9 shrink-0
                            items-center justify-center

                            rounded-xl

                            border border-slate-200
                            dark:border-white/[0.06]

                            bg-white
                            dark:bg-[#0b1525]

                            text-slate-600
                            dark:text-slate-300

                            transition

                            hover:bg-slate-100
                            dark:hover:bg-white/[0.06]
                        "
                    >

                        <i class="
                            fa-solid fa-eye text-xs
                        "></i>

                    </a>

                </div>

            `;
        });
    }

    html += `

            </div>

        </div>

    `;

    $("#detailAttachmentContainer").html(html);
}

// function renderDiscussionSection(access) {

//     $("#detailDiscussionContainer").html(`

//         <div class="
//             rounded-lg border border-slate-200
//             bg-white overflow-hidden
//         ">

//             <div class="
//                 border-b border-slate-200
//                 px-5 py-4
//             ">

//                 <h3 class="
//                     text-sm font-bold uppercase
//                     tracking-wider text-slate-700
//                 ">
//                     Discussion
//                 </h3>

//             </div>

//             <div
//                 id="discussionMessages"
//                 class="
//                     max-h-[320px]
//                     space-y-4 overflow-y-auto
//                     bg-slate-50 p-4
//                 "
//             ></div>

//             <div class="
//                 border-t border-slate-200
//                 p-4
//             ">

//                 <div class="flex gap-2">

//                     <textarea
//                         id="discussionMessage"
//                         rows="2"
//                         placeholder="Write message..."
//                         class="
//                             flex-1 rounded-xl
//                             border border-slate-200
//                             px-4 py-3 text-sm
//                             focus:border-slate-400
//                             focus:ring-0
//                         "
//                     ></textarea>

//                     <button
//                         type="button"
//                         id="btnSendDiscussion"
//                         data-hash="${access.eid}"
//                         class="
//                             inline-flex h-[52px]
//                             w-[52px]
//                             items-center justify-center
//                             rounded-xl bg-slate-900
//                             text-white transition
//                             hover:bg-slate-800
//                         "
//                     >

//                         <i class="fa-solid fa-paper-plane"></i>

//                     </button>

//                 </div>

//             </div>

//         </div>

//     `);

// }
// async function loadDiscussion(hash) {

//     try {

//         const res = await $.ajax({

//             url: `/access-request/comments/${hash}`,

//             type: 'GET'

//         });

//         renderDiscussionMessages(
//             res.comments ?? []
//         );

//     } catch (xhr) {

//         console.error(xhr);

//         $("#discussionMessages").html(`

//             <div class="
//                 rounded-xl border border-red-200
//                 bg-red-50 px-4 py-3
//                 text-sm text-red-600
//             ">

//                 Failed load discussion

//             </div>

//         `);

//     }

// }


async function loadDiscussion() {

    if (!currentDiscussionHash) {
        return;
    }

    try {

        const res = await $.ajax({

            url: `/access-request/comments/${currentDiscussionHash}`,

            type: 'GET'

        });

        renderDiscussionMessages(
            res.data ?? []
        );

    } catch (xhr) {

        console.error(xhr);

        $("#discussionMessages").html(`

            <div class="
                flex h-full items-center
                justify-center text-sm
                text-red-400
            ">

                Failed load discussion

            </div>

        `);

    }

}
async function sendDiscussion() {

    const input = $("#discussionInput");

    const message = input.val().trim();

    if (!message) {
        return;
    }

    $("#btnSendDiscussion")
        .prop("disabled", true);

    try {

        await $.ajax({

            url: `/access-request/comment/${currentDiscussionHash}`,

            type: "POST",

            data: {
                message : message
            },

        });

        input.val("");

        loadDiscussion();

    } catch (xhr) {

        swalError(
            xhr.responseJSON?.message ??
            "Failed send discussion"
        );

    } finally {

        $("#btnSendDiscussion")
            .prop("disabled", false);

    }

}
function renderDiscussionMessages(messages = []) {

    const container =
        $("#discussionMessages");

    container.html("");

    if (!messages.length) {

        container.html(`

            <div class="
                flex h-full items-center
                justify-center
                px-6 text-center
            ">

                <div>

                    <div class="
                        mx-auto mb-3 flex h-14 w-14
                        items-center justify-center
                        rounded-full
                        bg-white/[0.04]
                        text-slate-400
                    ">

                        <i class="
                            fa-regular fa-comments
                            text-lg
                        "></i>

                    </div>

                    <p class="
                        text-sm font-medium
                        text-slate-400
                    ">
                        No discussion yet
                    </p>

                </div>

            </div>

        `);

        return;
    }

    messages.forEach((item) => {

        const isMine =
            (
                item.username?.toLowerCase() ===
                (window.authUsername ?? "")
                    .toLowerCase()
            );

        container.append(`

            <div class="
                flex
                ${isMine ? "justify-end" : "justify-start"}
            ">

                <div class="
                    max-w-[82%]
                    rounded-2xl
                    px-4 py-3
                    shadow-[0_8px_25px_rgba(0,0,0,.18)]

                    ${
                        isMine
                            ? `
                                bg-blue-600
                                text-white
                                rounded-br-md
                            `
                            : `
                                bg-white/[0.04]
                                border border-white/[0.06]
                                text-slate-200
                                rounded-bl-md
                            `
                    }
                ">

                    <div class="
                        mb-0 flex items-center gap-2
                        text-[11px]
                        font-medium
                        ${
                            isMine
                                ? "text-blue-100"
                                : "text-slate-500"
                        }
                    ">

                        <span>
                            ${item.username ?? "-"}
                        </span>

                        <span>•</span>

                        <span>
                            ${
                                formatDate(
                                    item.message_date ??
                                    item.created_at
                                )
                            }
                        </span>

                    </div>

                    <div class="
                        whitespace-normal
                        break-words
                        text-sm leading-6
                    ">

                        ${item.message ?? "-"}

                    </div>

                </div>

            </div>

        `);

    });

    container.scrollTop(
        container[0].scrollHeight
    );
}

// async function sendDiscussion(button) {

//     const hash =
//         button.data('hash');

//     const textarea =
//         $('#discussionMessage');

//     const message =
//         textarea.val()?.trim();

//     if (!message) {

//         return;

//     }

//     const originalHtml =
//         button.html();

//     button.prop('disabled', true);

//     button.html(`

//         <i class="
//             fa-solid fa-spinner
//             animate-spin
//         "></i>

//     `);

//     try {

//         const res = await $.ajax({

//             url: `/access-request/comment/${hash}`,

//             type: 'POST',

//             data: {
//                 message: message
//             }

//         });

//         textarea.val('');

//         await loadDiscussion(hash);

//     } catch (xhr) {

//         console.error(xhr);

//         swalError(
//             xhr.responseJSON?.message ??
//             'Failed send message'
//         );

//     } finally {

//         button.prop('disabled', false);

//         button.html(originalHtml);

//     }

// }
function renderDetailError(xhr) {

    const message =
        xhr.responseJSON?.message ??
        "Failed load detail";

    const html = `
        <div class="
            rounded-2xl
            border border-red-500/20
            bg-red-500/10
            px-6 py-10
            text-center
        ">

            <div class="
                mx-auto flex h-14 w-14
                items-center justify-center
                rounded-full
                bg-red-500/15
                text-red-300
            ">
                <i class="
                    fa-solid fa-circle-exclamation
                    text-lg
                "></i>
            </div>

            <p class="
                mt-4 text-sm font-semibold
                text-red-200
            ">
                ${message}
            </p>

        </div>
    `;

    $("#detailInfoContainer").html(html);

    $("#detailItemsContainer").html("");

    $("#detailAttachmentContainer").html("");

    $("#detailActivityContainer").html("");

    $("#detailActionContainer").html("");
}
function normalizeActivities(res) {

    let activities = [];

    (res.approvals ?? []).forEach((item) => {

        activities.push({

            type: mapApprovalType(item.status),

            user:
                item.updated_by ??
                item.aprv_username ??
                "-",

            date:
                item.updated_at ??
                item.created_at,

            remark:
                item.remark ?? null,

            status:
                item.status,

        });

    });

(res.comments ?? []).forEach((item) => {

    // ONLY INCLUDE SYSTEM WORKFLOW MESSAGE
    if (
        item.message_type !== 'REJECT' &&
        item.message_type !== 'REVISE'
    ) {
        return;
    }

    activities.push({

        type:
            item.message_type === 'REJECT'
                ? 'rejected'
                : 'revised',

        user:
            item.created_by ?? '-',

        date:
            item.created_at,

        remark:
            item.message ?? null,

        status:
            item.message_type === 'REJECT'
                ? 'R'
                : 'D',

    });

});

    activities.sort((a, b) => {
        return new Date(a.date) - new Date(b.date);
    });

    return activities;

}
function mapApprovalType(status) {
    switch (status) {
        case "A":
            return "approved";

        case "R":
            return "rejected";

        case "D":
            return "revised";

        case "P":
            return "pending";

        default:
            return "activity";
    }
}
function formatActivityType(type) {
    switch (type) {
        case "approved":
            return "Approved";

        case "rejected":
            return "Rejected";

        case "revised":
            return "Revised";

        case "pending":
            return "Pending";

        case "submitted":
            return "Submitted";

        case "comment":
            return "Comment";

        case "hardware-process":
            return "Hardware Process";

        case "software-process":
            return "Software Process";

        default:
            return "Activity";
    }
}
function renderDetailActivity(activities = []) {
    let html = `
        <div class="rounded-lg border border-slate-200 bg-white">

            <div class="border-b border-slate-200 px-5 py-4">

                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700">
                    Activity Timeline
                </h3>

            </div>

            <div class="space-y-4 p-5">
    `;

    if (!activities.length) {
        html += `
            <div class="
                rounded-lg border border-slate-200
                bg-slate-50 px-4 py-5
                text-center text-sm text-slate-500
            ">
                No activity available
            </div>
        `;
    } else {
        activities.forEach((activity, index) => {
            html += `
                <div class="relative flex gap-4">

                    <div class="flex flex-col items-center">

                        <div class="
                            flex h-9 w-9 items-center justify-center
                            rounded-full
                            ${
                                activity.status === "A"
                                    ? "bg-emerald-100 text-emerald-600"
                                    : activity.status === "R"
                                    ? "bg-red-100 text-red-600"
                                    : activity.status === "D"
                                        ? "bg-amber-100 text-amber-600"
                                        : "bg-slate-100 text-slate-500"
                            }
                        ">
                            <i class="fa-solid fa-user-check text-xs"></i>
                        </div>

                        ${
                            index !== activities.length - 1
                                ? `
                                    <div class="
                                        mt-1 min-h-[42px]
                                        w-px bg-slate-200
                                    "></div>
                                `
                                : ""
                        }

                    </div>

                    <div class="min-w-0 flex-1 pb-2">

                        <p class="
                            mb-1 text-[11px]
                            font-bold uppercase tracking-wider
                            text-slate-400
                        ">
                            ${formatActivityType(activity.type)}
                        </p>

                        <div class="
                            flex items-start justify-between gap-3
                        ">

                            <div>

                                <p class="
                                    text-sm font-semibold text-slate-700
                                ">
                                    ${activity.user ?? "-"}
                                </p>

                                <p class="
                                    mt-1 text-xs text-slate-400
                                ">
                                    ${formatDate(activity.date)}
                                </p>

                            </div>

                            ${buildStatusPill(activity.status)}

                        </div>

                        ${
                            activity.remark
                                ? `
                                    <div class="
                                        mt-3 rounded-lg
                                        border border-slate-200
                                        bg-slate-50 px-3 py-2
                                        text-sm text-slate-600
                                    ">
                                        ${activity.remark}
                                    </div>
                                `
                                : ""
                        }

                    </div>

                </div>
            `;
        });
    }

    html += `
            </div>
        </div>
    `;

    $("#detailActivityContainer").html(html);
}
function renderDetailLoading() {

    const loader = `
        <div class="
            flex items-center justify-center
            rounded-2xl
            border border-white/[0.06]
            dark:bg-[#111c2d] bg-white
            py-20
        ">

            <div class="
                h-10 w-10 animate-spin rounded-full
                border-4
                border-slate-700
                border-t-slate-200
            "></div>

        </div>
    `;

    $("#detailInfoContainer").html(loader);
    $("#detailItemsContainer").html(loader);
    $("#detailAttachmentContainer").html(loader);
    $("#detailActivityContainer").html(loader);
    $("#detailActionContainer").html(loader);
}
function renderDetailActions(access) {

    if (!access.can_approve) {

        $("#detailActionContainer").html("");

        return;

    }

    let html = `
        <div class="grid grid-cols-3 gap-2">

            <button
                type="button"
                class="
                    btn-approve
                    inline-flex items-center justify-center gap-2
                    rounded-lg bg-emerald-600
                    px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-emerald-700
                "
                data-doc="${access.docid}"
            >
                <i class="fa-solid fa-check"></i>
                <span>Approve</span>
            </button>

            <button
                type="button"
                class="
                    btn-revise
                    inline-flex items-center justify-center gap-2
                    rounded-lg bg-amber-500
                    px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-amber-600
                "
                data-doc="${access.docid}"
            >
                <i class="fa-solid fa-rotate-left"></i>
                <span>Revise</span>
            </button>

            <button
                type="button"
                class="
                    btn-reject
                    inline-flex items-center justify-center gap-2
                    rounded-lg bg-red-500
                    px-4 py-3
                    text-sm font-semibold text-white
                    transition hover:bg-red-600
                "
                data-doc="${access.docid}"
            >
                <i class="fa-solid fa-xmark"></i>
                <span>Reject</span>
            </button>

        </div>
    `;

    $("#detailActionContainer").html(html);

}

$(document).on('click', '#btnPrintAccess', function () {

    const id = $(this).data('id');

    if (!id) {
        return;
    }

    window.open(
        `/access-request/print/${id}`,
        '_blank'
    );

});
let currentDiscussionHash = null;

function initDiscussionUI() {

    $(document).on("click", "#discussionFab", function () {

        $("#discussionPanel").toggleClass("hidden");

    });

    $(document).on("click", "#btnCloseDiscussion", function () {

        $("#discussionPanel").addClass("hidden");

    });

    $(document).on("click", "#btnSendDiscussion", function () {

        sendDiscussion();

    });

    $(document).on("keypress", "#discussionInput", function (e) {

        if (e.which === 13 && !e.shiftKey) {

            e.preventDefault();

            sendDiscussion();

        }

    });

}
