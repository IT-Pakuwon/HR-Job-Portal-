function statusBadge(status) {
    const map = {
        Processed: {
            text: "Processed",

            cls: `
                border border-green-200
                bg-green-50
                text-green-700

                dark:border-green-500/20
                dark:bg-green-500/10
                dark:text-green-300
            `,
        },

        S: {
            text: "Submitted",

            cls: `
                border border-indigo-200
                bg-indigo-50
                text-indigo-700

                dark:border-indigo-500/20
                dark:bg-indigo-500/10
                dark:text-indigo-300
            `,
        },

        IT: {
            text: "Processed",

            cls: `
                border border-green-200
                bg-green-50
                text-green-700

                dark:border-green-500/20
                dark:bg-green-500/10
                dark:text-green-300
            `,
        },

        A: {
            text: "Approved",

            cls: `
                border border-green-200
                bg-green-50
                text-green-700

                dark:border-green-500/20
                dark:bg-green-500/10
                dark:text-green-300
            `,
        },

        W: {
            text: "Waiting IT",

            cls: `
                border border-amber-200
                bg-amber-50
                text-amber-700

                dark:border-amber-500/20
                dark:bg-amber-500/10
                dark:text-amber-300
            `,
        },

        I: {
            text: "Waiting IT Revision",

            cls: `
                border border-orange-200
                bg-orange-50
                text-orange-700

                dark:border-orange-500/20
                dark:bg-orange-500/10
                dark:text-orange-300
            `,
        },

        P: {
            text: "Waiting Approval",

            cls: `
                border border-blue-200
                bg-blue-50
                text-blue-700

                dark:border-blue-500/20
                dark:bg-blue-500/10
                dark:text-blue-300
            `,
        },

        C: {
            text: "Completed",

            cls: `
                border border-green-200
                bg-green-50
                text-green-700

                dark:border-green-500/20
                dark:bg-green-500/10
                dark:text-green-300
            `,
        },

        R: {
            text: "Rejected",

            cls: `
                border border-red-200
                bg-red-50
                text-red-700

                dark:border-red-500/20
                dark:bg-red-500/10
                dark:text-red-300
            `,
        },

        D: {
            text: "Revise",

            cls: `
                border border-slate-200
                bg-slate-100
                text-slate-700

                dark:border-white/10
                dark:bg-white/10
                dark:text-slate-300
            `,
        },

        X: {
            text: "Cancelled",

            cls: `
                border border-slate-200
                bg-slate-100
                text-slate-700

                dark:border-slate-500/20
                dark:bg-slate-500/10
                dark:text-slate-300
            `,
        },
    };

    const item = map[status] || {
        text: status || "-",

        cls: `
            border border-slate-200
            bg-slate-100
            text-slate-700

            dark:border-white/10
            dark:bg-white/10
            dark:text-slate-300
        `,
    };

    return `

        <span class="
            inline-flex
            items-center

            rounded-full

            px-2.5 py-1

            text-[11px]
            font-semibold

            ${item.cls}
        ">

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

        D: "fa-pen text-slate-500",

        X: "fa-ban text-slate-500",
    };

    return map[status] || "fa-circle text-slate-400";
}

function infoItem(label, value) {
    return `

        <div class="min-w-0">

            <div class="
                text-[11px]
                font-semibold
                uppercase

                tracking-[0.15em]

                text-slate-400
            ">

                ${label}

            </div>

            <div class="
                mt-1

                break-words

                text-sm

                text-slate-700
                dark:text-slate-200
            ">

                ${value || "-"}

            </div>

        </div>

    `;
}

function emptyState(text = "No data available") {
    return `

        <div class="
            rounded-xl

            border
            border-dashed
            border-slate-200

            px-4 py-8

            text-center
            text-sm

            text-slate-400

            dark:border-white/10
        ">

            ${text}

        </div>

    `;
}

function sectionCard({
    title = "",
    content = "",
    actions = "",
    className = "",
}) {
    return `

        <div class="
            rounded-2xl

            border border-slate-200
            dark:border-white/10

            bg-white
            dark:bg-[#0f172a]

            shadow-sm

            ${className}
        ">

            <div class="
                flex
                items-center
                justify-between

                border-b border-slate-200
                dark:border-white/10

                px-5 py-4
            ">

                <h3 class="
                    text-xs
                    font-semibold
                    uppercase

                    tracking-[0.2em]

                    text-slate-500
                    dark:text-slate-400
                ">

                    ${title}

                </h3>

                ${actions}

            </div>

            <div class="p-5">

                ${content}

            </div>

        </div>

    `;
}
