function formatDate(date) {
    if (!date) return "-";

    const d = new Date(date);

    return d.toLocaleString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
    });
}

function renderStatusBadge(status) {

    switch (status) {

        case 'P':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-blue-100 px-3 py-1
                    text-xs font-semibold text-blue-700
                ">
                    ON PROGRESS
                </span>
            `;

        case 'C':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-emerald-100 px-3 py-1
                    text-xs font-semibold text-emerald-700
                ">
                    APPROVED
                </span>
            `;

        case 'F':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-green-100 px-3 py-1
                    text-xs font-semibold text-green-700
                ">
                    COMPLETED
                </span>
            `;

        case 'D':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-amber-100 px-3 py-1
                    text-xs font-semibold text-amber-700
                ">
                    REVISE
                </span>
            `;

        case 'R':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-red-100 px-3 py-1
                    text-xs font-semibold text-red-700
                ">
                    REJECT
                </span>
            `;

        case 'X':
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-slate-200 px-3 py-1
                    text-xs font-semibold text-slate-700
                ">
                    CANCEL
                </span>
            `;

        default:
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-slate-100 px-3 py-1
                    text-xs font-semibold text-slate-500
                ">
                    ${status}
                </span>
            `;
    }

}

function renderProgressBar(completed, total) {
    let percent = 0;

    if (total > 0) {
        percent = Math.round((completed / total) * 100);
    }

    return `
            <div class="w-full">

                <div class="mb-1 flex items-center justify-between">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        ${completed}/${total}
                    </span>

                    <span class="text-xs font-semibold text-slate-700">
                        ${percent}%
                    </span>
                </div>

                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div
                        class="h-full rounded-full bg-slate-800"
                        style="width:${percent}%">
                    </div>
                </div>

            </div>
        `;
}

function showLoading(target) {
    $(target).html(`
            <div class="flex items-center justify-center py-16">
                <div class="h-10 w-10 animate-spin rounded-full border-4 border-slate-300 border-t-slate-700"></div>
            </div>
        `);
}
function showError(target, message = "Something went wrong") {
    $(target).html(`
            <div class="rounded-lg border border-red-200 bg-red-50 p-5 text-sm text-red-700">
                ${message}
            </div>
        `);
}

function swalSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 2500,
    });
}

function swalSuccess(message = "Success") {
    Swal.fire({
        icon: "success",
        title: "Success",
        text: message,
        confirmButtonText: "OK",
        confirmButtonColor: "#0f172a",
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
        customClass: {
            popup: "rounded-lg",
        },
    });
}

function swalError(message = "Something went wrong") {
    Swal.fire({
        icon: "error",
        title: "Error",
        text: message,
        confirmButtonText: "OK",
        confirmButtonColor: "#dc2626",
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
        customClass: {
            popup: "rounded-lg",
        },
    });
}

function swalWarning(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "error",
        title: message,
        showConfirmButton: false,
        timer: 3500,
    });
}

function swalWarning(message = "Incomplete form") {
    Swal.fire({
        icon: "warning",
        title: "Incomplete Form",
        text: message,
        confirmButtonText: "OK",
        confirmButtonColor: "#f59e0b",
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
        customClass: {
            popup: "rounded-lg",
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
        customClass: {
            confirmButton: "swal2-confirm btn-confirm",
            cancelButton: "swal2-cancel btn-cancel",
        },
    });
}

function appendEmptyState(target, message = "No data available") {
    $(target).html(`
            <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-slate-200 dark:border-white/10 bg-white dark:bg-[#0f172a] py-14">
                <div class="text-4xl">📭</div>

                <p class="mt-3 text-sm font-medium text-slate-600">
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
                    inline-flex items-center rounded-full
                    bg-blue-100 px-2.5 py-1
                    text-xs font-semibold text-blue-700
                ">
                    Pending
                </span>
            `;

        case "A":
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-emerald-100 px-2.5 py-1
                    text-xs font-semibold text-emerald-700
                ">
                    Approved
                </span>
            `;

        case "C":
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-green-100 px-2.5 py-1
                    text-xs font-semibold text-green-700
                ">
                    Done
                </span>
            `;

        case "D":
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-amber-100 px-2.5 py-1
                    text-xs font-semibold text-amber-700
                ">
                    Revise
                </span>
            `;

        case "R":
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-red-100 px-2.5 py-1
                    text-xs font-semibold text-red-700
                ">
                    Reject
                </span>
            `;

        default:
            return `
                <span class="
                    inline-flex items-center rounded-full
                    bg-slate-100 px-2.5 py-1
                    text-xs font-semibold text-slate-500
                ">
                    ${status}
                </span>
            `;
    }
}
function renderTypeBadge(type) {
    switch (type) {
        case "NEW":
            return `
                    <span class="inline-flex text-xs font-semibold text-blue-700">
                        NEW
                    </span>
                `;

        case "CHANGE":
            return `
                    <span class="inline-flex text-xs font-semibold text-amber-700">
                        CHANGE
                    </span>
                `;

        case "REMOVE":
            return `
                    <span class="inline-flex text-xs font-semibold text-red-700">
                        REMOVE
                    </span>
                `;

        default:
            return `
                    <span class="inline-flex text-xs font-semibold text-slate-600">
                        ${type ?? "-"}
                    </span>
                `;
    }
}

function showValidationMessage(message) {
    Swal.fire({
        icon: "warning",
        title: "Incomplete Form",
        text: message,
        confirmButtonText: "OK",
        confirmButtonColor: "#4f46e5",
        background: $("html").hasClass("dark") ? "#0f172a" : "#ffffff",
        color: $("html").hasClass("dark") ? "#ffffff" : "#0f172a",
    });
}
