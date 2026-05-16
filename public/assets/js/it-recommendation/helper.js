function statusBadge(status) {
    const raw = status;

    if (raw === "Processed") {
        return `
                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300">
                                Processed
                            </span>
                        `;
    }
    const map = {
        S: {
            text: "Submitted",
            cls: "bg-indigo-50 text-indigo-700 border border-indigo-200 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-300",
        },

        IT: {
            text: "Processed",
            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        A: {
            text: "Approved",
            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        W: {
            text: "Waiting IT",
            cls: "bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-300",
        },

        I: {
            text: "Waiting IT Revision",
            cls: "bg-orange-50 text-orange-700 border border-orange-200 dark:bg-orange-500/10 dark:border-orange-500/20 dark:text-orange-300",
        },

        P: {
            text: "Waiting Approval",
            cls: "bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-500/10 dark:border-blue-500/20 dark:text-blue-300",
        },

        C: {
            text: "Completed",
            cls: "bg-green-50 text-green-700 border border-green-200 dark:bg-green-500/10 dark:border-green-500/20 dark:text-green-300",
        },

        R: {
            text: "Rejected",
            cls: "bg-red-50 text-red-700 border border-red-200 dark:bg-red-500/10 dark:border-red-500/20 dark:text-red-300",
        },

        D: {
            text: "Revise",
            cls: "bg-gray-100 text-gray-700 border border-gray-200 dark:bg-white/10 dark:border-white/10 dark:text-gray-300",
        },

        X: {
            text: "Cancelled",
            cls: "bg-slate-100 text-slate-700 border border-slate-200 dark:bg-slate-500/10 dark:border-slate-500/20 dark:text-slate-300",
        },
    };

    const item = map[status] || {
        text: status ?? "-",
        cls: "bg-gray-100 text-gray-700 border border-gray-200",
    };

    return `
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ${item.cls}">
                        ${item.text}
                    </span>
                `;
}

function timelineIcon(status) {
    const map = {
        S: "fa-paper-plane text-indigo-500",
        IT: "fa-gears text-green-500",
        A: "fa-check text-green-500",
        W: "fa-clock text-amber-500",
        I: "fa-rotate-left text-orange-500",
        P: "fa-hourglass-half text-blue-500",
        C: "fa-circle-check text-green-500",
        R: "fa-xmark text-red-500",
        D: "fa-pen text-gray-500",
        X: "fa-ban text-slate-500",
    };

    return map[status] || "fa-circle text-gray-400";
}

function infoItem(label, value) {
    return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? "-"}
                        </div>

                    </div>
                `;
}

function processInfoItem(label, value) {
    return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? "-"}
                        </div>

                    </div>
                `;
}

function editInfoItem(label, value) {
    return `
                    <div class="min-w-0">

                        <div class="text-[11px] font-semibold uppercase tracking-[0.15em] text-gray-400">
                            ${label}
                        </div>

                        <div class="mt-1 break-words text-sm text-gray-700 dark:text-gray-200">
                            ${value ?? "-"}
                        </div>

                    </div>
                `;
}
