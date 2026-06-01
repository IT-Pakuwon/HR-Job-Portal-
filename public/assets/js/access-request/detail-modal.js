let currentDiscussionHash = null;

// ─── Open / close ─────────────────────────────────────────────────────────────
async function openDetailModal(id) {
    openModal("#detailModal");
    renderDetailLoading();

    try {
        const [res, tracking] = await Promise.all([
            fetchDetail(id),
            fetchTracking(id),
        ]);

        const access = res.access ?? {};
        access.can_approve   = res.can_approve   ?? false;
        access.can_view_password = res.can_view_password ?? false;

        // Header
        $("#detailModalDocId").text(`Access Request - ${access.docid ?? "-"}`);
        $("#detailModalStatus").html(renderStatusBadge(access.status));
        $("#btnPrintAccess").attr("data-id", access.eid);

        // Sections
        renderDetailInfo(access);
        renderDetailItems(res.details ?? [], access);
        renderDetailAttachments(res.attachments ?? []);
        renderDetailActivity(tracking);
        renderDetailActions(access);

        // Discussion
        currentDiscussionHash = id;
        $("#discussionFab").removeClass("hidden");
        $("#discussionPanel").addClass("hidden");
        await loadDiscussion();

    } catch (xhr) {
        console.error(xhr);
        renderDetailError(xhr);
    }
}

async function fetchDetail(id) {
    return $.ajax({ url: `/access-request/detail/${id}`, type: "GET" });
}

async function fetchTracking(id) {
    try {
        return await $.ajax({ url: `/access-request/tracking/${id}`, type: "GET" });
    } catch (e) {
        return { steps: [] };
    }
}

// ─── Timeline helpers (same as ITR) ──────────────────────────────────────────
function timelineBadgeColor(s) {
    switch (s) {
        case 'A':  return 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400';
        case 'R':  return 'bg-red-100 text-red-600 dark:bg-red-500/20 dark:text-red-400';
        case 'D':  return 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400';
        case 'P':  return 'bg-blue-100 text-blue-600 dark:bg-blue-500/20 dark:text-blue-400';
        case 'C':  return 'bg-purple-100 text-purple-600 dark:bg-purple-500/20 dark:text-purple-400';
        case 'F':  return 'bg-green-100 text-green-600 dark:bg-green-500/20 dark:text-green-400';
        default:   return 'bg-slate-100 text-slate-500 dark:bg-white/10 dark:text-slate-400';
    }
}

function timelineBadgeIcon(s) {
    switch (s) {
        case 'A':  return 'fa-check';
        case 'R':  return 'fa-xmark';
        case 'D':  return 'fa-pen';
        case 'P':  return 'fa-hourglass-half';
        case 'C':  return 'fa-flag-checkered';
        case 'F':  return 'fa-circle-check';
        default:   return 'fa-paper-plane';
    }
}

function timelinePill(s, label) {
    const cls = {
        'A': 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300',
        'R': 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-300',
        'D': 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300',
        'P': 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300',
        'C': 'bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300',
        'F': 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-300',
    }[s] ?? 'bg-slate-100 text-slate-600 dark:bg-white/10 dark:text-slate-400';

    return `<span class="inline-flex shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold ${cls}">${label || s}</span>`;
}

const TIMELINE_INITIAL = 5;

function renderDetailActivity(tracking = {}) {
    const steps = tracking.steps ?? [];

    if (!steps.length) {
        $("#detailActivityContainer").html(`
            <div class="rounded-lg border border-dashed border-slate-200 px-4 py-8 text-center text-sm text-slate-400 dark:border-white/10">
                No approval workflow available.
            </div>
        `);
        return;
    }

    // Adapt ACR tracking steps → ITR timeline row format
    // step.title = step name / approver display name
    // step.by    = person who acted (null when pending)
    const rows = steps.map((step) => {
        let title, description;

        if (step.key === 'submitted') {
            title       = step.title ?? 'Submitted';   // "Access Request"
            description = step.by ?? null;             // requester name
        } else {
            // Approval steps: use status label as heading, approver name as body
            title       = step.status_label ?? '-';    // "Waiting approval", "Approved", etc.
            description = step.by ?? step.title ?? null; // acted-by name, or pending approver name
        }

        return {
            status:      step.status ?? 'P',
            title,
            description,
            date:        step.at ?? null,
            label:       step.status_label ?? step.status,
            note:        step.reason ?? null,
        };
    });

    const total = rows.length;
    const hasMore = total > TIMELINE_INITIAL;
    const hiddenCount = total - TIMELINE_INITIAL;

    const items = rows.map((row, index) => {
        const isLast        = index === total - 1;
        const isLastVisible = hasMore && index === TIMELINE_INITIAL - 1;
        const isHidden      = hasMore && index >= TIMELINE_INITIAL;
        const s             = row.status;
        const showNote      = row.note && ['R', 'D'].includes(s);

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
                                ${row.title}
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
        <button id="detailTimelineToggle" type="button" data-expanded="false" data-count="${hiddenCount}"
            class="mt-3 flex w-full items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white py-2 text-xs font-semibold text-slate-500 transition hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.02] dark:text-slate-400 dark:hover:bg-white/[0.05]">
            <i class="fa-solid fa-chevron-down text-[10px]"></i>
            ${hiddenCount} more
        </button>
    ` : '';

    $("#detailActivityContainer").html(`<div>${items}</div>${showMoreBtn}`);
}

$(document).on("click", "#detailTimelineToggle", function () {
    const expanded = $(this).data("expanded");
    const count    = $(this).data("count");
    const container = $(this).closest("#detailActivityContainer");

    if (expanded) {
        container.find(".tl-extra").addClass("hidden");
        container.find(".tl-last-visible-content").removeClass("pb-6").addClass("pb-0");
        $(this).data("expanded", false).html(
            `<i class="fa-solid fa-chevron-down text-[10px]"></i> ${count} more`
        );
    } else {
        container.find(".tl-extra").removeClass("hidden");
        container.find(".tl-last-visible-content").removeClass("pb-0").addClass("pb-6");
        $(this).data("expanded", true).html(
            `<i class="fa-solid fa-chevron-up text-[10px]"></i> Show less`
        );
    }
});

// ─── Request Information ──────────────────────────────────────────────────────
function renderDetailInfo(access) {
    $("#detailInfoContainer").html(`
        <div class="grid grid-cols-1 gap-x-6 gap-y-5 p-5 text-sm md:grid-cols-2 xl:grid-cols-3">
            ${infoItem("Date", formatDate(access.created_at))}
            ${infoItem("Company", access.cpny_id)}
            ${infoItem("Department", access.department_id)}
            ${infoItem("Requester", access.user_peminta)}
            ${infoItem("Request Type", renderTypeBadge(access.access_type))}
            ${infoItem("Purpose / Notes", `<span class="whitespace-normal leading-6">${access.keperluan ?? "-"}</span>`)}
        </div>
    `);
}

// ─── Access Items ─────────────────────────────────────────────────────────────
function renderDetailItems(details = [], access = {}) {
    const canSeePassword = access.can_view_password === true;

    let rows = "";

    details.forEach((item) => {
        const hasPassword = !!item.access_password;

        let passwordCell;
        if (!hasPassword) {
            passwordCell = `<div class="rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2">-</div>`;
        } else if (canSeePassword) {
            passwordCell = `
                <div class="relative flex items-center rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2 pr-9 break-all">
                    <span class="pw-text text-sm">${item.access_password}</span>
                    <button type="button" class="btn-toggle-detail-pw absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" data-pw="${encodeURIComponent(item.access_password)}" data-visible="1">
                        <i class="fa-solid fa-eye text-xs"></i>
                    </button>
                </div>`;
        } else {
            passwordCell = `
                <div class="relative flex items-center rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2 pr-9 break-all">
                    <span class="pw-text text-sm">••••••••</span>
                    <button type="button" class="btn-toggle-detail-pw absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" data-pw="${encodeURIComponent(item.access_password)}" data-visible="0">
                        <i class="fa-solid fa-eye-slash text-xs"></i>
                    </button>
                </div>`;
        }

        rows += `
            <tr class="border-b border-slate-100 dark:border-white/10 align-top">
                <td class="px-4 py-4">
                    <div class="space-y-2">
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-100">${item.access_descr ?? "-"}</p>
                        <span class="inline-flex rounded-lg px-2.5 py-1 text-[11px] font-semibold ${item.group_category === "HARDWARE" ? "bg-blue-100 text-blue-700 dark:bg-blue-500/15 dark:text-blue-300" : "bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300"}">
                            ${item.group_category ?? "-"}
                        </span>
                    </div>
                </td>
                <td class="px-4 py-4">${buildStatusPill(item.status)}</td>
                <td class="px-4 py-4">
                    <div class="rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2 text-sm">
                        ${item.access_response ?? "-"}
                    </div>
                </td>
                <td class="px-4 py-4">
                    <div class="rounded-lg border border-slate-200 dark:border-white/10 bg-slate-50 dark:bg-white/[0.03] px-3 py-2 text-sm break-all">
                        ${item.access_username ?? "-"}
                    </div>
                </td>
                <td class="px-4 py-4 min-w-[160px]">${passwordCell}</td>
            </tr>
        `;
    });

    if (!rows) {
        rows = `<tr><td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">No detail item available</td></tr>`;
    }

    $("#detailItemsContainer").html(`
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-[11px] uppercase tracking-[0.15em] text-slate-500 dark:border-white/10 dark:bg-white/[0.03] dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold">Access Item</th>
                        <th class="px-4 py-3 text-left font-semibold">Status</th>
                        <th class="px-4 py-3 text-left font-semibold">Response</th>
                        <th class="px-4 py-3 text-left font-semibold">Username</th>
                        <th class="px-4 py-3 text-left font-semibold">Password</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
    `);
}

// Toggle password visibility in detail modal
$(document).on("click", ".btn-toggle-detail-pw", function () {
    const btn     = $(this);
    const visible = btn.data("visible") == 1;
    const pw      = decodeURIComponent(btn.data("pw"));
    const span    = btn.closest("div").find(".pw-text");

    if (visible) {
        span.text("••••••••");
        btn.data("visible", 0).html('<i class="fa-solid fa-eye-slash text-xs"></i>');
    } else {
        span.text(pw);
        btn.data("visible", 1).html('<i class="fa-solid fa-eye text-xs"></i>');
    }
});

// ─── Attachments ──────────────────────────────────────────────────────────────
function renderDetailAttachments(files = []) {
    if (!files.length) {
        $("#detailAttachmentContainer").html(`
            <div class="w-full rounded-lg border border-dashed border-slate-200 dark:border-white/10 px-4 py-6 text-center text-sm text-slate-400">
                No attachment available
            </div>
        `);
        return;
    }

    let html = "";
    files.forEach((file) => {
        const displayName = file.display_name ?? file.filename ?? "Attachment";
        html += attachmentCard({
            name: displayName,
            url: file.url ?? "#",
        });
    });

    $("#detailAttachmentContainer").html(html);
}

// ─── Actions ──────────────────────────────────────────────────────────────────
function renderDetailActions(access) {
    // Footer actions (Edit / Cancel for creator in revise state)
    let footerHtml = "";

    if (access.status === "D" && access.created_by === (window.authUsername ?? "")) {
        footerHtml += `
            <button type="button" class="btn-edit-access inline-flex items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-600"
                data-id="${access.eid}">
                <i class="fa-solid fa-pen text-xs"></i> Edit Request
            </button>
            <button type="button" class="btn-cancel-access inline-flex items-center justify-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-red-700"
                data-id="${access.eid}">
                <i class="fa-solid fa-ban text-xs"></i> Cancel Request
            </button>
        `;
    }

    $("#detailFooterActions").html(footerHtml);

    // Sidebar: Approval actions
    if (access.can_approve) {
        $("#detailActionWrapper").removeClass("hidden").html(`
            <div class="flex w-full gap-2">
                <button type="button" class="btn-approve inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    data-doc="${access.docid}">
                    <i class="fa-solid fa-check text-xs"></i> Approve
                </button>
                <button type="button" class="btn-revise inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-amber-600"
                    data-doc="${access.docid}">
                    <i class="fa-solid fa-rotate-left text-xs"></i> Revise
                </button>
                <button type="button" class="btn-reject inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-red-500 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-600"
                    data-doc="${access.docid}">
                    <i class="fa-solid fa-xmark text-xs"></i> Reject
                </button>
            </div>
        `);
    } else {
        $("#detailActionWrapper").addClass("hidden").html("");
    }
}

// Footer Edit / Cancel handlers
$(document).on("click", ".btn-edit-access", function () {
    const id = $(this).data("id");
    closeModal("#detailModal");
    setTimeout(() => openEditModal(id), 220);
});

$(document).on("click", ".btn-cancel-access", async function () {
    const id = $(this).data("id");

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
            url: `/access-request/cancel/${id}`,
            type: "POST",
        });
        itrToast("success", "Request cancelled");
        table.ajax.reload(null, false);
        closeModal("#detailModal");
        window.history.replaceState({}, document.title, "/access-request");
    } catch (xhr) {
        swalError(xhr.responseJSON?.message ?? "Failed to cancel request");
    }
});

// ─── Loading / Error states ───────────────────────────────────────────────────
function renderDetailLoading() {
    const loader = `
        <div class="flex items-center justify-center rounded-lg border border-white/[0.06] dark:bg-[#111c2d] bg-white py-20">
            <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-300 border-t-slate-700 dark:border-slate-700 dark:border-t-slate-200"></div>
        </div>`;

    $("#detailInfoContainer").html(loader);
    $("#detailItemsContainer").html(loader);
    $("#detailAttachmentContainer").html(loader);
    $("#detailActivityContainer").html(loader);
    $("#detailActionWrapper").addClass("hidden").html("");
    $("#detailFooterActions").html("");
}

function renderDetailError(xhr) {
    const message = xhr.responseJSON?.message ?? "Failed load detail";
    const html = `
        <div class="rounded-2xl border border-red-500/20 bg-red-500/10 px-6 py-10 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-500/15 text-red-300">
                <i class="fa-solid fa-circle-exclamation text-lg"></i>
            </div>
            <p class="mt-4 text-sm font-semibold text-red-200">${message}</p>
        </div>`;

    $("#detailInfoContainer").html(html);
    $("#detailItemsContainer").html("");
    $("#detailAttachmentContainer").html("");
    $("#detailActivityContainer").html("");
}

// ─── Print ───────────────────────────────────────────────────────────────────
$(document).on("click", "#btnPrintAccess", function () {
    const id = $(this).data("id");
    if (!id) return;
    window.open(`/access-request/print/${id}`, "_blank");
});

// ─── Discussion ──────────────────────────────────────────────────────────────
async function loadDiscussion() {
    if (!currentDiscussionHash) return;

    try {
        const res = await $.ajax({ url: `/access-request/comments/${currentDiscussionHash}`, type: "GET" });
        renderDiscussionMessages(res.data ?? []);
    } catch (xhr) {
        console.error(xhr);
        $("#discussionMessages").html(`
            <div class="flex h-full items-center justify-center text-sm text-red-400">Failed load discussion</div>`);
    }
}

async function sendDiscussion() {
    const input   = $("#discussionInput");
    const message = input.val().trim();
    if (!message) return;

    $("#btnSendDiscussion").prop("disabled", true);

    try {
        await $.ajax({
            url: `/access-request/comment/${currentDiscussionHash}`,
            type: "POST",
            data: { message },
        });
        input.val("");
        loadDiscussion();
    } catch (xhr) {
        swalError(xhr.responseJSON?.message ?? "Failed send discussion");
    } finally {
        $("#btnSendDiscussion").prop("disabled", false);
    }
}

function renderDiscussionMessages(messages = []) {
    const container = $("#discussionMessages");
    container.html("");

    if (!messages.length) {
        container.html(`
            <div class="flex h-full items-center justify-center px-6 text-center">
                <div>
                    <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-full bg-white/[0.04] text-slate-400">
                        <i class="fa-regular fa-comments text-lg"></i>
                    </div>
                    <p class="text-sm font-medium text-slate-400">No discussion yet</p>
                </div>
            </div>`);
        return;
    }

    messages.forEach((item) => {
        const isMine = (item.username?.toLowerCase() ?? "") === (window.authUsername ?? "").toLowerCase();

        container.append(`
            <div class="flex ${isMine ? "justify-end" : "justify-start"}">
                <div class="max-w-[82%] rounded-2xl px-4 py-3 shadow-[0_8px_25px_rgba(0,0,0,.18)]
                    ${isMine ? "bg-blue-600 text-white rounded-br-md" : "bg-white/[0.04] border border-white/[0.06] text-slate-200 rounded-bl-md"}">
                    <div class="mb-0 flex items-center gap-2 text-[11px] font-medium ${isMine ? "text-blue-100" : "text-slate-500"}">
                        <span>${item.username ?? "-"}</span>
                        <span>•</span>
                        <span>${formatDate(item.message_date ?? item.created_at)}</span>
                    </div>
                    <div class="whitespace-normal break-words text-sm leading-6">${item.message ?? "-"}</div>
                </div>
            </div>`);
    });

    container.scrollTop(container[0].scrollHeight);
}

function initDiscussionUI() {
    $(document).on("click", "#discussionFab button", function () {
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
