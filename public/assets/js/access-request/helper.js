function formatDate(date) {

    if (!date) {
        return "-";
    }

    const d = new Date(date);

    return d.toLocaleString("id-ID", {
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

        case "P":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-blue-100
                    dark:bg-blue-500/15
                    px-3 py-1
                    text-xs font-semibold
                    text-blue-700
                    dark:text-blue-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-blue-500
                    "></span>

                    ON PROGRESS
                </span>
            `;

        case "C":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-emerald-100
                    dark:bg-emerald-500/15
                    px-3 py-1
                    text-xs font-semibold
                    text-emerald-700
                    dark:text-emerald-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-emerald-500
                    "></span>

                    APPROVED
                </span>
            `;

        case "F":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-green-100
                    dark:bg-green-500/15
                    px-3 py-1
                    text-xs font-semibold
                    text-green-700
                    dark:text-green-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-green-500
                    "></span>

                    COMPLETED
                </span>
            `;

        case "D":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-amber-100
                    dark:bg-amber-500/15
                    px-3 py-1
                    text-xs font-semibold
                    text-amber-700
                    dark:text-amber-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-amber-500
                    "></span>

                    REVISE
                </span>
            `;

        case "R":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-red-100
                    dark:bg-red-500/15
                    px-3 py-1
                    text-xs font-semibold
                    text-red-700
                    dark:text-red-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-red-500
                    "></span>

                    REJECT
                </span>
            `;

        case "X":

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
                    bg-slate-200
                    dark:bg-white/10
                    px-3 py-1
                    text-xs font-semibold
                    text-slate-700
                    dark:text-slate-300
                ">
                    <span class="
                        h-1.5 w-1.5 rounded-full
                        bg-slate-500
                    "></span>

                    CANCEL
                </span>
            `;

        default:

            return `
                <span class="
                    inline-flex items-center gap-1.5
                    rounded-full
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
        percent = Math.round((completed / total) * 100);
    }

    let progressColor = `
        bg-slate-800
        dark:bg-slate-200
    `;

    if (percent >= 100) {

        progressColor = `
            bg-emerald-600
            dark:bg-emerald-400
        `;
    }
    else if (percent >= 50) {

        progressColor = `
            bg-blue-600
            dark:bg-blue-400
        `;
    }

    return `
        <div class="w-full min-w-[120px]">

            <div class="mb-1.5 flex items-center justify-between">

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
                        h-full rounded-full transition-all duration-500
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
        <div class="flex items-center justify-center py-16">

            <div class="
                h-10 w-10 animate-spin rounded-full border-4
                border-slate-300 border-t-slate-700
                dark:border-slate-700 dark:border-t-slate-200
            "></div>

        </div>
    `);
}

function showError(target, message = "Something went wrong") {

    $(target).html(`
        <div class="
            rounded-xl
            border border-red-200
            dark:border-red-500/20
            bg-red-50
            dark:bg-red-500/10
            p-5
            text-sm
            text-red-700
            dark:text-red-300
        ">
            ${message}
        </div>
    `);
}

function appendEmptyState(target, message = "No data available") {

    $(target).html(`
        <div class="
            flex flex-col items-center justify-center
            rounded-xl
            border border-dashed border-slate-200
            dark:border-white/10
            bg-white
            dark:bg-[#0f172a]
            py-14
        ">

            <div class="
                flex h-16 w-16 items-center justify-center
                rounded-full
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
                    rounded-full
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
                    rounded-full
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
                    rounded-full
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
                    rounded-full
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
                    rounded-full
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
                    rounded-full
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

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",
    });
}

function swalSuccess(message = "Success") {

    return Swal.fire({

        icon: "success",

        title: "Success",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#0f172a",

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",

        customClass: {
            popup: "rounded-2xl",
        },
    });
}

function swalError(message = "Something went wrong") {

    return Swal.fire({

        icon: "error",

        title: "Error",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#dc2626",

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",

        customClass: {
            popup: "rounded-2xl",
        },
    });
}

function swalWarning(message = "Incomplete form") {

    return Swal.fire({

        icon: "warning",

        title: "Warning",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#f59e0b",

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",

        customClass: {
            popup: "rounded-2xl",
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

        cancelButtonColor: "#e2e8f0",

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",

        customClass: {

            popup: "rounded-2xl",

            confirmButton: `
                rounded-lg
                px-4 py-2
                font-medium
            `,

            cancelButton: `
                rounded-lg
                px-4 py-2
                font-medium
                text-slate-700
            `,
        },
    });
}

function showValidationMessage(message = "Incomplete form") {

    Swal.fire({

        icon: "warning",

        title: "Incomplete Form",

        text: message,

        confirmButtonText: "OK",

        confirmButtonColor: "#4f46e5",

        background: $("html").hasClass("dark")
            ? "#0f172a"
            : "#ffffff",

        color: $("html").hasClass("dark")
            ? "#ffffff"
            : "#0f172a",

        customClass: {
            popup: "rounded-2xl",
        },
    });
}
