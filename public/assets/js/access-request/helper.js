function infoItem(label, value) {
    return `
        <div class="min-w-0">
            <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-slate-400">
                ${label}
            </div>
            <div class="mt-1 break-words text-sm text-slate-700 dark:text-slate-200">
                ${value || "-"}
            </div>
        </div>
    `;
}

function formatFileSize(bytes) {
    if (bytes >= 1024 * 1024) {
        return `${(bytes / 1024 / 1024).toFixed(2)} MB`;
    }
    return `${(bytes / 1024).toFixed(1)} KB`;
}

function itrToast(icon, title, timer = 2500) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon,
        title,
        showConfirmButton: false,
        timer,
        timerProgressBar: true,
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
        customClass: { popup: "rounded-lg border border-white/[0.06]" },
    });
}

function formatDate(date) {

    if (!date) {
        return "-";
    }

    const d = new Date(date);

    return d.toLocaleString("id-ID", {

        day: "2-digit",

        month: "short",

        year: "numeric",

    });
}

function renderStatusBadge(status) {

    switch (status) {

        case "P":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-blue-100
                    dark:bg-blue-500/15

                    px-3 py-1

                    text-xs font-semibold

                    text-blue-700
                    dark:text-blue-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-blue-500
                    "></span>

                    ON PROGRESS

                </span>
            `;

        case "C":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-emerald-100
                    dark:bg-emerald-500/15

                    px-3 py-1

                    text-xs font-semibold

                    text-emerald-700
                    dark:text-emerald-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-emerald-500
                    "></span>

                    APPROVED

                </span>
            `;

        case "F":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-green-100
                    dark:bg-green-500/15

                    px-3 py-1

                    text-xs font-semibold

                    text-green-700
                    dark:text-green-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-green-500
                    "></span>

                    COMPLETED

                </span>
            `;

        case "D":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-amber-100
                    dark:bg-amber-500/15

                    px-3 py-1

                    text-xs font-semibold

                    text-amber-700
                    dark:text-amber-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-amber-500
                    "></span>

                    REVISE

                </span>
            `;

        case "R":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-red-100
                    dark:bg-red-500/15

                    px-3 py-1

                    text-xs font-semibold

                    text-red-700
                    dark:text-red-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-red-500
                    "></span>

                    REJECT

                </span>
            `;

        case "X":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-slate-200
                    dark:bg-white/10

                    px-3 py-1

                    text-xs font-semibold

                    text-slate-700
                    dark:text-slate-300
                ">

                    <span class="
                        h-1.5 w-1.5 rounded-lg
                        bg-slate-500
                    "></span>

                    CANCEL

                </span>
            `;

        default:

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-slate-100
                    dark:bg-white/10

                    px-3 py-1

                    text-xs font-semibold

                    text-slate-500
                    dark:text-slate-300
                ">

                    ${status ?? "-"}

                </span>
            `;
    }
}

function renderProgressBar(completed, total) {

    let percent = 0;

    if (total > 0) {

        percent =
            Math.round(
                (completed / total) * 100
            );
    }

    let progressColor = "bg-blue-500";

    if (percent >= 100) {

        progressColor = "bg-green-500";
    }
    else if (percent >= 50) {

        progressColor = "bg-blue-600";
    }

    return `
        <div class="
            w-full min-w-[120px]
        ">

            <div class="
                mb-1.5 flex items-center justify-between
            ">

                <span class="
                    text-xs

                    text-slate-500
                    dark:text-slate-400
                ">

                    ${completed}/${total}

                </span>

                <span class="
                    text-xs font-semibold

                    text-slate-700
                    dark:text-slate-200
                ">

                    ${percent}%

                </span>

            </div>

            <div class="
                h-2 overflow-hidden rounded-full

                bg-slate-100
                dark:bg-white/10
            ">

                <div
                    class="
                        h-full rounded-full
                        transition-all duration-500

                        ${progressColor}
                    "
                    style="width:${percent}%">
                </div>

            </div>

        </div>
    `;
}

function showLoading(target) {

    $(target).html(`

        <div class="
            flex items-center justify-center
            py-16
        ">

            <div class="
                h-10 w-10 animate-spin rounded-lg border-4

                border-slate-300
                border-t-slate-700

                dark:border-slate-700
                dark:border-t-slate-200
            "></div>

        </div>

    `);
}

function showError(
    target,
    message = "Something went wrong"
) {

    $(target).html(`

        <div class="
            rounded-lg

            border border-red-200
            dark:border-red-500/20

            bg-red-50
            dark:bg-red-500/10

            px-5 py-4

            text-sm

            text-red-700
            dark:text-red-300
        ">

            ${message}

        </div>

    `);
}

function appendEmptyState(
    target,
    message = "No data available"
) {

    $(target).html(`

        <div class="
            flex flex-col items-center justify-center

            rounded-lg

            border border-dashed border-slate-200
            dark:border-white/10

            bg-white
            dark:bg-[#0f172a]

            py-14
        ">

            <div class="
                flex h-16 w-16
                items-center justify-center

                rounded-lg

                bg-slate-100
                dark:bg-white/10

                text-3xl
            ">

                📭

            </div>

            <p class="
                mt-4 text-sm font-medium

                text-slate-600
                dark:text-slate-300
            ">

                ${message}

            </p>

        </div>

    `);
}

function buildStatusPill(status) {

    switch (status) {

        case "P":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-blue-100
                    dark:bg-blue-500/15

                    px-2.5 py-1

                    text-xs font-semibold

                    text-blue-700
                    dark:text-blue-300
                ">

                    Pending

                </span>
            `;

        case "A":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-emerald-100
                    dark:bg-emerald-500/15

                    px-2.5 py-1

                    text-xs font-semibold

                    text-emerald-700
                    dark:text-emerald-300
                ">

                    Approved

                </span>
            `;

        case "C":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-green-100
                    dark:bg-green-500/15

                    px-2.5 py-1

                    text-xs font-semibold

                    text-green-700
                    dark:text-green-300
                ">

                    Done

                </span>
            `;

        case "D":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-amber-100
                    dark:bg-amber-500/15

                    px-2.5 py-1

                    text-xs font-semibold

                    text-amber-700
                    dark:text-amber-300
                ">

                    Revise

                </span>
            `;

        case "R":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-red-100
                    dark:bg-red-500/15

                    px-2.5 py-1

                    text-xs font-semibold

                    text-red-700
                    dark:text-red-300
                ">

                    Reject

                </span>
            `;

        default:

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-lg

                    bg-slate-100
                    dark:bg-white/10

                    px-2.5 py-1

                    text-xs font-semibold

                    text-slate-500
                    dark:text-slate-300
                ">

                    ${status ?? "-"}

                </span>
            `;
    }
}

function renderTypeBadge(type) {

    switch (type) {

        case "NEW":

            return `
                <span class="
                    inline-flex items-center

                    text-xs font-semibold

                    text-blue-700
                    dark:text-blue-300
                ">

                    NEW

                </span>
            `;

        case "CHANGE":

            return `
                <span class="
                    inline-flex items-center

                    text-xs font-semibold

                    text-amber-700
                    dark:text-amber-300
                ">

                    CHANGE

                </span>
            `;

        case "REMOVE":

            return `
                <span class="
                    inline-flex items-center

                    text-xs font-semibold

                    text-red-700
                    dark:text-red-300
                ">

                    REMOVE

                </span>
            `;

        default:

            return `
                <span class="
                    inline-flex items-center

                    text-xs font-semibold

                    text-slate-600
                    dark:text-slate-300
                ">

                    ${type ?? "-"}

                </span>
            `;
    }
}

function swalToast({

    icon = "success",

    title = "Success",

    timer = 2500,

}) {

    return Swal.fire({

        toast: true,

        position: "top-end",

        icon,

        title,

        showConfirmButton: false,

        timer,

        timerProgressBar: true,

        background:
            $("html").hasClass("dark")
                ? "#0f172a"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,
        },
    });
}

function swalSuccess(
    message = "Success"
) {

    return Swal.fire({

        icon: "success",

        title: "Success",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#0f172a",

        background:
            $("html").hasClass("dark")
                ? "#111c2d"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,
        },
    });
}

function swalError(
    message = "Something went wrong"
) {

    return Swal.fire({

        icon: "error",

        title: "Error",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#dc2626",

        background:
            $("html").hasClass("dark")
                ? "#111c2d"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,
        },
    });
}

function swalWarning(
    message = "Incomplete form"
) {

    return Swal.fire({

        icon: "warning",

        title: "Warning",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#f59e0b",

        background:
            $("html").hasClass("dark")
                ? "#111c2d"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,
        },
    });
}

function confirmDialog({

    title = "Are you sure?",

    text = "",

    icon = "warning",

    confirmText = "Yes",

}) {

    return Swal.fire({

        title,

        text,

        icon,

        showCancelButton: true,

        confirmButtonText: confirmText,

        cancelButtonText: "Cancel",

        reverseButtons: true,

        confirmButtonColor: "#0f172a",

        cancelButtonColor:
            $("html").hasClass("dark")
                ? "#334155"
                : "#e2e8f0",

        background:
            $("html").hasClass("dark")
                ? "#111c2d"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,

            confirmButton: `
                rounded-lg
                px-4 py-2
                font-medium
            `,

            cancelButton: `
                rounded-lg
                px-4 py-2
                font-medium
            `,
        },
    });
}

function showValidationMessage(
    message = "Incomplete form"
) {

    Swal.fire({

        icon: "warning",

        title: "Incomplete Form",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#3193da",

        background:
            $("html").hasClass("dark")
                ? "#111c2d"
                : "#ffffff",

        color:
            $("html").hasClass("dark")
                ? "#ffffff"
                : "#0f172a",

        customClass: {

            popup: `
                rounded-lg
                border border-white/[0.06]
            `,
        },
    });
}

/* =====================================================
   PROCESS HARDWARE / SOFTWARE POPUPS (shared layout)
===================================================== */

function accessEscape(value) {
    return String(value ?? "").replace(/[&<>"']/g, (c) => ({
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#39;",
    })[c]);
}

function renderAccessProcessInfo(access) {
    const cell = (label, value, extra = "") => `
        <div class="min-w-0 ${extra}">
            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">${label}</p>
            <p class="mt-1 break-words text-sm font-semibold text-slate-800 dark:text-slate-100">${accessEscape(value || "-")}</p>
        </div>`;

    return `
        <div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-white/[0.07] dark:bg-[#111c2d]">
            <div class="grid grid-cols-2 gap-x-6 gap-y-3 px-5 py-4 md:grid-cols-4">
                ${cell("Document", access.docid)}
                ${cell("Requester", access.user_peminta)}
                ${cell("Company", access.cpny_id)}
                ${cell("Department", access.department_id)}
                ${cell("Purpose / Info", access.keperluan, "col-span-2 border-t border-slate-100 pt-3 dark:border-white/[0.06] md:col-span-4")}
            </div>
        </div>`;
}

// Reloads an open process popup after Save / Done without the loader: the scroll position
// and anything typed (but not yet saved) on the other items are kept.
async function refreshAccessProcessModal(kind, hash, savedId) {
    const title = kind === "hardware" ? "Hardware" : "Software";
    const panel = $(`#process${title}Modal .modal-panel`);
    const container = $(`#process${title}DetailContainer`);
    const fields = `.${kind}-response, .${kind}-username, .${kind}-password`;

    let res;

    try {
        res = await $.ajax({ url: `/access-request/detail/${hash}`, type: "GET" });
    } catch (xhr) {
        console.error(xhr);
        swalWarning("Your changes were saved, but the popup could not be refreshed. Please close and open it again.");
        return;
    }

    const access = res.access ?? {};
    const details = (res.details ?? []).filter((item) => item.group_category === title.toUpperCase());

    // read these after the request, so typing done while it was loading is included
    const scrollTop = panel.scrollTop();
    const drafts = container.find(fields).toArray()
        .filter((el) => el.dataset.id !== String(savedId) && el.value !== el.defaultValue)
        .map((el) => ({
            selector: `.${[...el.classList].find((name) => name.startsWith(`${kind}-`))}[data-id="${el.dataset.id}"]`,
            value: el.value,
        }));

    $(`#process${title}InfoContainer`).html(renderAccessProcessInfo(access));
    container.html(renderAccessProcessItems(kind, access, details));

    drafts.forEach((draft) => container.find(draft.selector).val(draft.value));
    panel.scrollTop(scrollTop);
}

// kind: "hardware" or "software" (also the prefix of the field and button classes the page listens to)
function renderAccessProcessItems(kind, access, details = []) {
    const title = kind === "hardware" ? "Hardware" : "Software";
    const icon = kind === "hardware" ? "fa-laptop" : "fa-window-maximize";
    const pending = details.filter((item) => item.status !== "C").length;
    const done = details.length - pending;

    const label = "mb-1.5 block";
    const field = "block w-full";
    const readonlyBox = "mt-1 min-h-10 break-words rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-800 dark:bg-white/[0.04] dark:text-slate-100";
    const readonlyLabel = "text-[11px] font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500";

    const readonlyBody = (item) => `
        <div class="grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <p class="${readonlyLabel}">Response</p>
                <div class="${readonlyBox} whitespace-pre-line">${accessEscape(item.access_response ?? "-")}</div>
            </div>
            <div>
                <p class="${readonlyLabel}">Username</p>
                <div class="${readonlyBox} break-all">${accessEscape(item.access_username ?? "-")}</div>
            </div>
            <div>
                <p class="${readonlyLabel}">Password</p>
                <div class="${readonlyBox} break-all">${accessEscape(item.access_password ?? "-")}</div>
            </div>
        </div>`;

    const formBody = (item) => `
        <div class="grid grid-cols-1 gap-4 px-5 py-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="${label}">Response</label>
                <textarea rows="2" class="${kind}-response ${field} px-3 py-2" data-id="${item.id}" placeholder="Input response ...">${accessEscape(item.access_response)}</textarea>
            </div>
            <div>
                <label class="${label}">Username</label>
                <input type="text" class="${kind}-username ${field}" data-id="${item.id}" value="${accessEscape(item.access_username)}" autocomplete="off" placeholder="Username">
            </div>
            <div>
                <label class="${label}">Password</label>
                <div class="relative">
                    <input type="password" class="${kind}-password ${field} pr-10!" data-id="${item.id}" value="${accessEscape(item.access_password)}" autocomplete="new-password" placeholder="Password">
                    <button type="button" class="btn-toggle-pw-input absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200" data-visible="0" title="Show password">
                        <i class="fa-solid fa-eye-slash text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-2 rounded-b-xl border-t border-slate-100 bg-slate-50/70 px-5 py-3 dark:border-white/[0.06] dark:bg-white/[0.02]">
            <button type="button" class="btn-save-${kind} inline-flex h-10 min-w-28 items-center justify-center rounded-lg border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-100 disabled:opacity-60 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                data-doc="${access.eid}" data-id="${item.id}" data-type="${access.access_type ?? ""}">
                <i class="fa-solid fa-floppy-disk mr-2 text-xs"></i>Save
            </button>
            <button type="button" class="btn-complete-${kind} inline-flex h-10 min-w-28 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 disabled:opacity-60"
                data-doc="${access.eid}" data-id="${item.id}" data-type="${access.access_type ?? ""}">
                <i class="fa-solid fa-check mr-2 text-xs"></i>Done
            </button>
        </div>`;

    const cards = details.map((item) => {
        const isCompleted = item.status === "C";

        return `
            <div class="rounded-xl border bg-white shadow-sm dark:bg-[#111c2d] ${isCompleted ? "border-slate-200 dark:border-white/[0.07]" : "border-blue-200 dark:border-blue-500/30"}">
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-3 dark:border-white/[0.06]">
                    <div class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ${isCompleted ? "bg-green-50 text-green-600 dark:bg-green-500/10 dark:text-green-300" : "bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"}">
                            <i class="fa-solid ${icon} text-sm"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="break-words text-sm font-semibold text-slate-800 dark:text-slate-100">${accessEscape(item.access_descr || "-")}</p>
                            ${item.access_pic ? `<p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"><i class="fa-solid fa-user mr-1.5 text-[10px]"></i>${accessEscape(item.access_pic)}</p>` : ""}
                        </div>
                    </div>
                    ${isCompleted ? listBadge("green", "Completed") : listBadge("yellow", "Pending")}
                </div>
                ${isCompleted ? readonlyBody(item) : formBody(item)}
            </div>`;
    }).join("");

    const empty = `
        <div class="rounded-xl border border-dashed border-slate-300 bg-white px-5 py-12 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-transparent dark:text-slate-400">
            No ${kind} request available
        </div>`;

    return `
        <div class="mt-1 flex flex-wrap items-center justify-between gap-2 px-1 py-2">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">${title} Request Detail</h3>
            ${details.length ? `<p class="text-xs font-medium text-slate-500 dark:text-slate-400">${pending} pending &middot; ${done} completed</p>` : ""}
        </div>
        <div class="space-y-3">${cards || empty}</div>`;
}
