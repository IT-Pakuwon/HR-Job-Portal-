(function () {
    'use strict';

    VoucherTaxi.Tracking = {

        load(eid) {

            $('#approvalFlow').html(`
                <div class="flex items-center justify-center py-8">
                    <div class="text-sm text-slate-500">
                        Loading timeline...
                    </div>
                </div>
            `);

            $.ajax({

                url: VoucherTaxi.Route.tracking(eid),

                method: 'GET',

                success: (res) => {

                    const rows =
                        res.data || res || [];

                    this.render(rows);
                },

                error: (xhr) => {

                    VoucherTaxi.Helper.ajaxError(xhr);

                    $('#approvalFlow').html(`
                        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                            Failed to load tracking information
                        </div>
                    `);
                }
            });
        },

        render(rows) {

            const container =
                $('#approvalFlow');

            if (!rows.length) {

                container.html(`
                    <div class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">
                        No tracking history available
                    </div>
                `);

                return;
            }

            let html = '';

            rows.forEach((item, index) => {

                html += this.timelineItem(
                    item,
                    index === rows.length - 1
                );
            });

            container.html(html);
        },

        timelineItem(item, isLast) {

            const icon =
                this.icon(item.type);

            const color =
                this.color(item.type);

            const user =
                item.user ||
                item.username ||
                '-';

            const date =
                item.date ||
                item.created_at ||
                '-';

            const title =
                item.title ||
                item.status ||
                '-';

            const description =
                item.description ||
                item.comment ||
                item.message ||
                '';

            return `
                <div class="relative flex gap-4">

                    ${
                        !isLast
                        ? `
                            <div
                                class="absolute left-[15px] top-8 bottom-0 w-px bg-slate-200 dark:bg-white/10">
                            </div>
                        `
                        : ''
                    }

                    <div
                        class="relative z-10 flex h-8 w-8 items-center justify-center rounded-full ${color} text-white text-xs">

                        ${icon}

                    </div>

                    <div class="flex-1 pb-6">

                        <div class="flex flex-wrap items-center justify-between gap-2">

                            <div
                                class="font-semibold text-slate-900 dark:text-white">

                                ${VoucherTaxi.Helper.escapeHtml(title)}

                            </div>

                            <div
                                class="text-xs text-slate-400">

                                ${VoucherTaxi.Helper.escapeHtml(date)}

                            </div>

                        </div>

                        <div
                            class="mt-1 text-xs text-slate-500">

                            ${VoucherTaxi.Helper.escapeHtml(user)}

                        </div>

                        ${
                            description
                            ? `
                                <div
                                    class="mt-2 rounded-lg bg-slate-50 p-3 text-sm text-slate-600 dark:bg-white/[0.03] dark:text-slate-300">

                                    ${VoucherTaxi.Helper.nl2br(description)}

                                </div>
                            `
                            : ''
                        }

                    </div>

                </div>
            `;
        },

        icon(type) {

            switch ((type || '').toUpperCase()) {

                case 'SUBMIT':
                    return '<i class="fa-solid fa-paper-plane"></i>';

                case 'APPROVE':
                    return '<i class="fa-solid fa-check"></i>';

                case 'REJECT':
                    return '<i class="fa-solid fa-xmark"></i>';

                case 'REVISE':
                    return '<i class="fa-solid fa-rotate-left"></i>';

                case 'PROCESS':
                    return '<i class="fa-solid fa-money-bill-wave"></i>';

                case 'COMPLETE':
                    return '<i class="fa-solid fa-flag-checkered"></i>';

                default:
                    return '<i class="fa-solid fa-circle"></i>';
            }
        },

        color(type) {

            switch ((type || '').toUpperCase()) {

                case 'SUBMIT':
                    return 'bg-blue-500';

                case 'APPROVE':
                    return 'bg-emerald-500';

                case 'REJECT':
                    return 'bg-red-500';

                case 'REVISE':
                    return 'bg-yellow-500';

                case 'PROCESS':
                    return 'bg-indigo-500';

                case 'COMPLETE':
                    return 'bg-emerald-600';

                default:
                    return 'bg-slate-500';
            }
        }
    };

})();
