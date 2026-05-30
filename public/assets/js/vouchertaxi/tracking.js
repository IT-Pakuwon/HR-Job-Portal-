(function () {
    "use strict";

    VoucherTaxi.Tracking = {
        load(eid) {
            $("#approvalFlow").html(`
                <div class="
                    flex items-center justify-center
                    rounded-lg border border-slate-200
                    bg-slate-50 py-10
                ">
                    <div class="
                        h-8 w-8 animate-spin rounded-full
                        border-4 border-slate-300 border-t-slate-700
                    "></div>
                </div>
            `);

            $.ajax({
                url: VoucherTaxi.Route.tracking(eid),
                method: "GET",

                success: (res) => {
                    const rows = Array.isArray(res)
                        ? res
                        : Array.isArray(res.data)
                          ? res.data
                          : Array.isArray(res.steps)
                            ? res.steps
                            : Array.isArray(res.data?.steps)
                              ? res.data.steps
                              : [];

                    this.render(rows);
                },

                error: (xhr) => {
                    VoucherTaxi.Helper.ajaxError(xhr);

                    $("#approvalFlow").html(`
                        <div class="
                            rounded-lg
                            border border-red-200
                            bg-red-50
                            px-5 py-4
                            text-sm text-red-600
                        ">
                            Failed to load tracking information
                        </div>
                    `);
                },
            });
        },

        render(rows) {
            const container = $("#approvalFlow");

            if (!rows.length) {
                container.html(`
            <div class="
                rounded-lg border border-slate-200
                bg-slate-50 px-4 py-8
                text-center text-sm text-slate-500
            ">
                No approval workflow available
            </div>
        `);
                return;
            }

            let html = `
        <div class="
            rounded-lg border border-slate-200
            bg-white overflow-hidden
        ">

            <div class="
                border-b border-slate-200
                px-5 py-4
            ">
                <h3 class="
                    text-sm font-bold uppercase
                    tracking-wider text-slate-700
                ">
                    Approval Timeline
                </h3>
            </div>

            <div class="space-y-2 p-4">
    `;

            rows.forEach((item, index) => {
                html += this.timelineItem(item, index === rows.length - 1);
            });

            html += `
            </div>
        </div>
    `;

            container.html(html);
        },
        approvalCard(item, stepNo) {
            const status = (item.status || "").toUpperCase();

            const approver =
                item.by || item.user || item.username || item.title || "-";

            const date = item.at || item.date || item.created_at || "-";

            const remark = item.reason || item.comment || item.message || "";

            return `
        <div class="
            rounded-lg border border-slate-200
            bg-white p-4
        ">

            <div class="
                flex items-start justify-between
                gap-3
            ">

                <div class="flex gap-3">

                    <div class="
                        flex h-10 w-10 shrink-0
                        items-center justify-center
                        rounded-full
                        ${this.badgeColor(status)}
                    ">
                        ${stepNo}
                    </div>

                    <div>

                        <div class="
                            text-xs font-semibold
                            uppercase tracking-wide
                            text-slate-400
                        ">
                            Step ${stepNo}
                        </div>

                        <div class="
                            mt-1 text-sm font-semibold
                            text-slate-800
                        ">
                            ${VoucherTaxi.Helper.escapeHtml(approver)}
                        </div>

                        <div class="
                            mt-1 text-xs
                            text-slate-500
                        ">
                            ${VoucherTaxi.Helper.escapeHtml(date)}
                        </div>

                    </div>

                </div>

                ${this.statusPill(status)}

            </div>

            ${
                remark
                    ? `
                        <div class="
                            mt-4 rounded-lg
                            bg-slate-50
                            px-3 py-2
                            text-sm text-slate-600
                        ">
                            ${VoucherTaxi.Helper.nl2br(
                                VoucherTaxi.Helper.escapeHtml(remark),
                            )}
                        </div>
                    `
                    : ""
            }

        </div>
    `;
        },
timelineItem(item, isLast) {

    const status = (item.status || "").toUpperCase();

    const level = item.aprv_leveling || "-";

    const user =
        item.aprv_name ||
        item.by ||
        item.user ||
        item.username ||
        "-";

    const date =
        item.at ||
        item.date ||
        item.created_at ||
        "-";

    const remark =
        item.reason ||
        item.comment ||
        item.message ||
        "";

    return `
        <div class="relative flex gap-4">

            <div class="flex flex-col items-center">

                <div class="
                    flex h-10 w-10
                    items-center justify-center
                    rounded-full
                    ${this.badgeColor(status)}
                ">
                    ${this.icon(status)}
                </div>

                ${
                    !isLast
                        ? `
                            <div class="
                                mt-1 w-px flex-1
                                min-h-[24px]
                                bg-slate-200
                            "></div>
                        `
                        : ""
                }

            </div>

            <div class="min-w-0 flex-1">

                <div class="
                    flex items-start justify-between
                    gap-3
                ">

                    <div>

                        <p class="
                            text-[11px]
                            font-bold uppercase
                            tracking-wider
                            text-slate-400
                        ">
                            ${
                                !level || level === "-"
                                    ? "Submitted"
                                    : `Approval ${parseInt(level)}`
                            }
                        </p>

                        <p class="
                            mt-1 text-sm
                            font-semibold
                            text-slate-700
                        ">
                            ${VoucherTaxi.Helper.escapeHtml(user)}
                        </p>

                        <p class="
                            mt-1 text-xs
                            text-slate-400
                        ">
                            ${VoucherTaxi.Helper.escapeHtml(date)}
                        </p>

                    </div>

                    ${this.statusPill(status)}

                </div>

              ${
    (status === "D" || status === "R") && remark
        ? `
            <div class="
                mt-3 rounded-lg
                border border-slate-200
                bg-slate-50
                px-3 py-2
                text-sm text-slate-600
            ">
                ${VoucherTaxi.Helper.nl2br(
                    VoucherTaxi.Helper.escapeHtml(remark)
                )}
            </div>
        `
        : ""
}

            </div>

        </div>
    `;
},
        statusPill(status) {
            switch (status) {
                case "A":
                    return `
                        <span class="
                            inline-flex rounded-full
                            bg-emerald-100
                            px-2.5 py-1
                            text-xs font-semibold
                            text-emerald-700
                        ">
                            Approved
                        </span>
                    `;

                case "R":
                    return `
                        <span class="
                            inline-flex rounded-full
                            bg-red-100
                            px-2.5 py-1
                            text-xs font-semibold
                            text-red-700
                        ">
                            Rejected
                        </span>
                    `;

                case "D":
                    return `
                        <span class="
                            inline-flex rounded-full
                            bg-amber-100
                            px-2.5 py-1
                            text-xs font-semibold
                            text-amber-700
                        ">
                            Revised
                        </span>
                    `;

                case "P":
                    return `
                        <span class="
                            inline-flex rounded-full
                            bg-blue-100
                            px-2.5 py-1
                            text-xs font-semibold
                            text-blue-700
                        ">
                            Waiting Approval
                        </span>
                    `;

                default:
                    return `
                        <span class="
                            inline-flex rounded-full
                            bg-slate-100
                            px-2.5 py-1
                            text-xs font-semibold
                            text-slate-600
                        ">
                            Submitted
                        </span>
                    `;
            }
        },
        badgeColor(status) {
            switch (status) {
                case "A":
                    return `
                bg-emerald-100
                text-emerald-600
            `;

                case "R":
                    return `
                bg-red-100
                text-red-600
            `;

                case "D":
                    return `
                bg-amber-100
                text-amber-600
            `;

                case "P":
                    return `
                bg-blue-100
                text-blue-600
            `;

                case "F":
                case "C":
                    return `
                bg-purple-100
                text-purple-600
            `;

                default:
                    return `
                bg-slate-100
                text-slate-500
            `;
            }
        },

        icon(status) {
            switch (status) {
                case "A":
                    return `
                <i class="
                    fa-solid fa-check
                    text-xs
                "></i>
            `;

                case "R":
                    return `
                <i class="
                    fa-solid fa-xmark
                    text-xs
                "></i>
            `;

                case "D":
                    return `
                <i class="
                    fa-solid fa-rotate-left
                    text-xs
                "></i>
            `;

                case "P":
                    return `
                <i class="
                    fa-solid fa-clock
                    text-xs
                "></i>
            `;

                case "F":
                case "C":
                    return `
                <i class="
                    fa-solid fa-flag-checkered
                    text-xs
                "></i>
            `;

                default:
                    return `
                <i class="
                    fa-solid fa-paper-plane
                    text-xs
                "></i>
            `;
            }
        },
    };
})();
