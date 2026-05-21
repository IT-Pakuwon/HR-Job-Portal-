<x-app-layout>
    @php
        $user = auth()->user();

        // Samakan seperti view RFP
        $isFinanceAccess = $isFinanceAccess ?? ($user ? $user->hasRole('FINACCESS') : false);
        $hasApFinAccess = $hasApFinAccess ?? ($user ? $user->hasRole('APFINACCESS') : false);
        $hasApTreAccess = $hasApTreAccess ?? ($user ? $user->hasRole('APTREACCESS') : false);
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @if ($isFinanceAccess)
            <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-7">
        @else
            <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
        @endif
            {{-- CALR Jobs --}}
            <button type="button" class="scope-filter group block h-full" data-scope="calrjobs">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📦</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">CALR Jobs</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $calrjobs }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="scope-filter group block h-full" data-scope="onprogress">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Rejected --}}
            <button type="button" class="scope-filter group block h-full" data-scope="rejected">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">❌</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Rejected</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $rejected }}</p>
                </div>
            </button>

            {{-- Revise --}}
            <button type="button" class="scope-filter group block h-full" data-scope="revise">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Revise</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="scope-filter group block h-full" data-scope="completed">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All --}}
            <button type="button" class="scope-filter group block h-full" data-scope="all">
                <div
                    class="scope-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🧾</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </button>
            @if ($isFinanceAccess)
                {{-- CALR Finance --}}
                <button type="button" class="scope-filter group block h-full" data-scope="calrfinance">
                    <div
                        class="scope-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                        <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">💰</div>

                        <div class="flex min-w-0 flex-grow flex-col">
                            <p class="break-words text-sm font-medium leading-tight">CALR Finance</p>
                        </div>

                        <p class="shrink-0 text-base font-bold">{{ $calrFinance }}</p>
                    </div>
                </button>
            @endif
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">CALR Non Purchase</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="calrTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="calrActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-lg rounded-xl bg-white p-6 dark:bg-gray-800">
            <h2 id="calrActionTitle" class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">
                Action
            </h2>

            <div class="space-y-3 text-sm">
                <div><strong>CALR ID:</strong> <span id="modalCalrId">-</span></div>
                <div><strong>RFCA ID:</strong> <span id="modalRfcaId">-</span></div>
                <div><strong>Keperluan:</strong> <span id="modalKeperluan">-</span></div>
                <div><strong>Total Amount:</strong> <span id="modalAmount">-</span></div>
                <div><strong id="modalUserLabel">User Receive:</strong> <span id="modalUserValue">-</span></div>
                <div><strong id="modalDateLabel">Date Receive:</strong> <span id="modalDateValue">-</span></div>

                <div id="modalMessageWrapper" class="hidden">
                    <label id="modalMessageLabel" class="mb-1 block font-semibold text-gray-700 dark:text-gray-200">
                        Message
                    </label>

                    <textarea id="modalMessage" rows="4"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        placeholder="Input message..."></textarea>

                    <p id="modalMessageHint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Message wajib diisi.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="closeCalrActionModal"
                    class="rounded border border-gray-300 px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                    Cancel
                </button>

                <button type="button" id="submitCalrActionBtn"
                    class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                    Update
                </button>
            </div>
        </div>
    </div>

    <script>
        const currentUser = @json(auth()->user()->username ?? '');
        const hasApFinAccess = @json($hasApFinAccess ?? false);
        const hasApTreAccess = @json($hasApTreAccess ?? false);
        let scopeFilter = 'calrjobs';

        const dtControlColumn = {
            data: null,
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            defaultContent: ''
        };

        function formatNow() {
            const now = new Date();

            const day = String(now.getDate()).padStart(2, '0');
            const month = now.toLocaleString('en-US', { month: 'short' });
            const year = now.getFullYear();
            const time = now.toTimeString().slice(0, 8);

            return `${day} ${month} ${year} ${time}`;
        }

        $(function() {

            $(document).on('click', '.btn-calr-action-menu', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                const $dropdown = $btn.closest('.calr-action-wrap').find('.calr-action-dropdown');
                const isOpen = !$dropdown.hasClass('hidden');

                $('.calr-action-dropdown').addClass('hidden');

                if (isOpen) {
                    return;
                }

                const rect = this.getBoundingClientRect();

                $dropdown
                    .css({
                        top: rect.bottom + 6 + 'px',
                        left: Math.max(8, rect.right - 208) + 'px',
                    })
                    .removeClass('hidden');
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.calr-action-wrap').length) {
                    $('.calr-action-dropdown').addClass('hidden');
                }
            });

            $(window).on('scroll resize', function() {
                $('.calr-action-dropdown').addClass('hidden');
            });

            $('.overflow-x-auto').on('scroll', function() {
                $('.calr-action-dropdown').addClass('hidden');
            });

            let scope = 'calrjobs';
            const $title = $('h1.text-base.font-extrabold');
            let table;

            const titleMap = {
                calrjobs: 'CALR Non Purchase - Jobs',
                onprogress: 'CALR Non Purchase - On Progress',
                completed: 'CALR Non Purchase - Completed',
                rejected: 'CALR Non Purchase - Rejected',
                revise: 'CALR Non Purchase - Revise',
                all: 'CALR Non Purchase - All',
                calrfinance: 'CALR Non Purchase - Finance',
            };

            function headerFor(sc) {
                if (sc === 'calrjobs') {
                    return `
                        <th></th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Document ID</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Date</th>                  
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Department</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Please Pay To</th>
                        <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Amount RFCA</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Created By</th>
                    `;
                }

                return `
                    <th></th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">CALR ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">CALR Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">RFCA ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Company</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Department</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Amount RFCA</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Settlement</th>
                    <th class="px-6 py-3 text-right text-sm font-semibold uppercase tracking-wider">Diff</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Created By</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider">Status</th>
                `;
            }

            function columnsFor(sc) {
                if (sc === 'calrjobs') {
                    return [
                        dtControlColumn,
                        {
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: (_v, _t, row) => renderPlusCreate(row)
                        },
                        {
                            data: 'rfpnonpurchaseid',
                            render: (_v, _t, row) => renderRfpNonPurchLink(row),
                            className: 'text-left'
                        },
                        {
                            data: 'rfpnonpurchasedate',
                            render: (_v, _t, row) => row.rfpnonpurchasedate_fmt ?? '',
                            className: 'text-left'
                        },                        
                        {
                            data: 'cpny_id',
                            className: 'text-left'
                        },
                        {
                            data: 'department_id',
                            className: 'text-left'
                        },
                        {
                            data: 'pleasepayto',
                            className: 'text-left'
                        },
                        {
                            data: 'amountrequestpayment',
                            render: (_v, _t, row) => row.amountrequestpayment_fmt ?? formatMoney(row.amountrequestpayment),
                            className: 'text-right'
                        },
                        {
                            data: 'created_by',
                            className: 'text-left'
                        },
                    ];
                }

                return [
                    dtControlColumn,
                    {
                        data: 'calrnonpurchaseid',
                        render: (_v, _t, row) => renderCalrNonPurchLink(row),
                        className: 'text-left'
                    },
                    {
                        data: 'calrnonpurchasedate',
                        render: (_v, _t, row) => row.calrnonpurchasedate_fmt ?? '',
                        className: 'text-left'
                    },
                    {
                        data: 'rfpnonpurchaseid',
                        // render: (_v, _t, row) => renderRfpNonPurchPlainLink(row),
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'department_id',
                        className: 'text-left'
                    },
                    {
                        data: 'amountrfp',
                        render: (_v, _t, row) => row.amountrfp_fmt ?? formatMoney(row.amountrfp),
                        className: 'text-right'
                    },
                    {
                        data: 'amountsettlement',
                        render: (_v, _t, row) => row.amountsettlement_fmt ?? formatMoney(row.amountsettlement),
                        className: 'text-right'
                    },
                    {
                        data: 'amountdiff',
                        render: (_v, _t, row) => row.amountdiff_fmt ?? formatMoney(row.amountdiff),
                        className: 'text-right'
                    },
                    {
                        data: 'created_by',
                        className: 'text-left'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-left',
                        render: function(data, type, row) {
                            if (scopeFilter !== 'calrfinance') {
                                return '';
                            }

                            let statusText = row.finance_flow_status_text || '';

                            if (!statusText) {
                                if (
                                    row.statuspayment === 'C' ||
                                    (row.userpayment && row.paymentdate)
                                ) {
                                    statusText = 'Treasury Received';
                                } else if (
                                    row.statusreceive === 'C' ||
                                    (row.userreceive && row.receivedate)
                                ) {
                                    statusText = 'Finance Received';
                                } else {
                                    statusText = 'Waiting User';
                                }
                            }

                            const isReceiveCompleted =
                                row.statusreceive === 'C' ||
                                statusText === 'Finance Received' ||
                                statusText === 'Treasury Received';

                            const isPaymentCompleted =
                                row.statuspayment === 'C' ||
                                (row.userpayment && row.paymentdate);

                            let receiveMode = '';
                            let receiveAction = '';
                            let receiveText = '';
                            let receiveUser = '';
                            let receiveDate = '';
                            let receiveAllowed = false;

                            if (hasApTreAccess && isReceiveCompleted) {
                                receiveMode = 'treasury';
                                receiveAction = isPaymentCompleted ? 'rollback' : 'update';
                                receiveText = isPaymentCompleted ? 'Rollback Treasury' : 'Update Treasury';
                                receiveUser = row.userpayment || '';
                                receiveDate = row.paymentdate || '';
                                receiveAllowed = true;
                            } else if (
                                hasApFinAccess &&
                                (statusText === 'Waiting User' || statusText === 'Finance Received')
                            ) {
                                const isReceived =
                                    row.statusreceive === 'C' ||
                                    (row.userreceive && row.receivedate);

                                receiveMode = 'received';
                                receiveAction = isReceived ? 'rollback' : 'update';
                                receiveText = isReceived ? 'Rollback Received' : 'Update Received';
                                receiveUser = row.userreceive || '';
                                receiveDate = row.receivedate || '';
                                receiveAllowed = true;
                            }

                            const commonData = `
                                data-hash="${row.calrnonpurchase_eid}"
                                data-calrnonpurchaseid="${escapeHtml(row.calrnonpurchaseid || '')}"
                                data-rfpnonpurchaseid="${escapeHtml(row.rfpnonpurchaseid || '')}"
                                data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                data-amount="${row.amountsettlement || 0}"
                            `;

                            let receiveItem = '';
                            let reviseItem = '';
                            let reminderItem = '';

                            if (receiveAllowed) {
                                receiveItem = `
                                    <button type="button"
                                        class="calr-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                        data-mode="${receiveMode}"
                                        data-action="${receiveAction}"
                                        ${commonData}
                                        data-user="${escapeHtml(receiveUser)}"
                                        data-date="${escapeHtml(receiveDate)}"
                                        data-button-text="${escapeHtml(receiveText)}">
                                        ${escapeHtml(receiveText)}
                                    </button>
                                `;

                                reviseItem = `
                                    <button type="button"
                                        class="calr-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                        data-mode="revise"
                                        data-action="update"
                                        ${commonData}
                                        data-user=""
                                        data-date=""
                                        data-button-text="Submit Revise">
                                        Revise
                                    </button>
                                `;

                                reminderItem = `
                                    <button type="button"
                                        class="calr-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                        data-mode="reminder"
                                        data-action="update"
                                        ${commonData}
                                        data-user=""
                                        data-date=""
                                        data-button-text="Send Reminder">
                                        Reminder
                                    </button>
                                `;
                            } else {
                                receiveItem = `
                                    <button type="button"
                                        class="block w-full cursor-not-allowed px-4 py-2 text-left text-sm text-gray-400"
                                        disabled>
                                        Receive / Rollback
                                    </button>
                                `;

                                reviseItem = `
                                    <button type="button"
                                        class="block w-full cursor-not-allowed px-4 py-2 text-left text-sm text-gray-400"
                                        disabled>
                                        Revise
                                    </button>
                                `;

                                reminderItem = `
                                    <button type="button"
                                        class="block w-full cursor-not-allowed px-4 py-2 text-left text-sm text-gray-400"
                                        disabled>
                                        Reminder
                                    </button>
                                `;
                            }

                            return `
                                <div class="calr-action-wrap inline-block text-left">
                                    <button type="button"
                                        class="btn-calr-action-menu inline-flex items-center gap-2 rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
                                        Action
                                        <span>▾</span>
                                    </button>

                                    <div class="calr-action-dropdown fixed z-[9999] hidden w-52 overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        ${receiveItem}
                                        ${reviseItem}
                                        ${reminderItem}
                                    </div>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data, type, row) {
                            if (scopeFilter === 'calrfinance') {
                                let statusText = row.finance_flow_status_text || '';

                                if (!statusText) {
                                    if (
                                        row.statuspayment === 'C' ||
                                        (row.userpayment && row.paymentdate)
                                    ) {
                                        statusText = 'Treasury Received';
                                    } else if (
                                        row.statusreceive === 'C' ||
                                        (row.userreceive && row.receivedate)
                                    ) {
                                        statusText = 'Finance Received';
                                    } else {
                                        statusText = 'Waiting User';
                                    }
                                }

                                const map = {
                                    'Waiting User': {
                                        c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                    },
                                    'Finance Received': {
                                        c: 'bg-blue-200/60 text-blue-800 border border-blue-600/40'
                                    },
                                    'Treasury Received': {
                                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                    }
                                };

                                const it = map[statusText] || {
                                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                };

                                return `<span class="w-40 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${statusText}</span>`;
                            }

                            return renderStatusBadge(data);
                        }
                    }
                ];
            }

            function orderFor(sc) {
                if (sc === 'calrjobs') {
                    return [
                        [2, 'desc']
                    ];
                }

                return [
                    [1, 'desc']
                ];
            }

            function updateTitle(sc) {
                $title.text(titleMap[sc] ?? 'CALR Non Purchase');
            }

            function resetThead(sc) {
                const $table = $('#calrTable');

                $table.find('thead').remove();

                const theadHtml = `
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr id="thead-row">${headerFor(sc)}</tr>
                    </thead>
                `;

                $table.prepend(theadHtml);

                if ($table.find('tbody').length === 0) {
                    $table.append(`
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    `);
                }
            }

            function rebuild(sc) {
                if ($.fn.DataTable.isDataTable('#calrTable')) {
                    $('#calrTable').DataTable().clear().destroy();
                }

                resetThead(sc);

                table = $('#calrTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    columnDefs: [{
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_CALR_Non_Purchase',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'List_CALR_Non_Purchase',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    order: orderFor(sc),
                    ajax: {
                        url: "{{ route('calrnonpurch.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.scope = sc;
                        }
                    },
                    columns: columnsFor(sc),
                    searchDelay: 400,
                    stateSave: false,
                });
            }

            function renderPlusCreate(row) {
                const rfpHash = row.rfpnonpurchase_eid ?? '';

                const url = `{{ route('calrnonpurch.create') }}` +
                    `?rfpnonpurchase=${encodeURIComponent(rfpHash)}`;

                return `
                    <a href="${url}"
                        class="inline-flex items-center justify-center rounded bg-blue-500 px-4 py-2 text-center text-sm font-medium leading-tight text-white transition-colors duration-200 hover:bg-blue-700">
                        <i class="fas fa-plus"></i>
                    </a>
                `;
            }

            function renderRfpNonPurchLink(row) {
                const label = row.rfpnonpurchaseid ?? '';
                const hash = row.rfpnonpurchase_eid || row.eid || row.hash || row.id;

                if (!label) return '';

                if (!hash) {
                    return `
                        <span class="inline-flex items-center rounded bg-gray-400 px-3 py-1.5 text-sm font-semibold text-white">
                            ${escapeHtml(label)}
                        </span>
                    `;
                }

                const url = `/showrfpnonpurch/${encodeURIComponent(hash)}`;

                return `
                    <a href="${url}" target="_blank"
                        class="inline-flex items-center justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">
                        ${escapeHtml(label)}
                    </a>
                `;
            }

            function renderRfpNonPurchPlainLink(row) {
                const label = row.rfpnonpurchaseid ?? '';

                if (!label) return '';

                return `
                    <span class="inline-flex items-center justify-center rounded bg-gray-500 px-3 py-1.5 text-sm font-semibold text-white">
                        ${escapeHtml(label)}
                    </span>
                `;
            }

            function renderCalrNonPurchLink(row) {
                const label = row.calrnonpurchaseid ?? '';
                const hash = row.calrnonpurchase_eid || row.eid || row.hash || row.id;

                if (!label) return '';

                if (!hash) {
                    return `
                        <span class="inline-flex items-center rounded bg-gray-400 px-3 py-1.5 text-sm font-semibold text-white">
                            ${escapeHtml(label)}
                        </span>
                    `;
                }

                const statusRaw = (row.status ?? '').toString().trim().toUpperCase();
                const creator = (row.created_by ?? '').toString();
                const isRevise = statusRaw === 'D';
                const isOwner = creator === (currentUser ?? '');

                if (isRevise && isOwner) {
                    const editUrl = `/editcalrnonpurch/${encodeURIComponent(hash)}`;
                    const showUrl = `/showcalrnonpurch/${encodeURIComponent(hash)}`;

                    return `
                        <div class="inline-flex items-center gap-2">
                            <a href="${editUrl}"
                                class="inline-flex items-center justify-center rounded bg-amber-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-amber-700"
                                title="Edit Revise">
                                ${escapeHtml(label)}
                            </a>

                            <a href="${showUrl}" target="_blank"
                                class="inline-flex items-center justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700"
                                title="View Detail">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    `;
                }

                const url = `/showcalrnonpurch/${encodeURIComponent(hash)}`;

                return `
                    <a href="${url}"
                        class="inline-flex items-center justify-center rounded bg-gray-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">
                        ${escapeHtml(label)}
                    </a>
                `;
            }

            function renderStatusBadge(data) {
                const map = {
                    'D': {
                        t: 'Revise',
                        c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                    },
                    'P': {
                        t: 'On Progress',
                        c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                    },
                    'C': {
                        t: 'Completed',
                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                    },
                    'X': {
                        t: 'Cancel',
                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                    },
                    'R': {
                        t: 'Rejected',
                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                    },
                };

                const it = map[data] || {
                    t: data || '-',
                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                };

                return `
                    <span class="inline-block w-32 rounded px-3 py-1.5 text-center text-sm font-semibold ${it.c}">
                        ${it.t}
                    </span>
                `;
            }

            function formatMoney(value) {
                const num = Number(value || 0);

                return num.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replaceAll('&', '&amp;')
                    .replaceAll('<', '&lt;')
                    .replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;')
                    .replaceAll("'", '&#039;');
            }

            updateTitle(scope);
            rebuild(scope);

            $('.scope-filter').on('click', function(e) {
                e.preventDefault();

                scope = $(this).data('scope') || 'calrjobs';
                scopeFilter = scope;

                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(this).addClass('active');

                localStorage.setItem('activeCalrNonPurchScope', scope);
            });

            const savedCalrScope = localStorage.getItem('activeCalrNonPurchScope');

            // if (savedCalrScope) {
            //     scope = savedCalrScope;
            const allowedScopes = [
                'calrjobs',
                'calrfinance',
                'onprogress',
                'completed',
                'rejected',
                'revise',
                'all'
            ];

            if (savedCalrScope && allowedScopes.includes(savedCalrScope)) {
                scope = savedCalrScope;
                scopeFilter = scope;

                updateTitle(scope);
                rebuild(scope);

                $('.scope-filter').removeClass('active');
                $(`.scope-filter[data-scope="${scope}"]`).addClass('active');
            } else {
                scope = 'calrjobs';
                scopeFilter = scope;

                $(`.scope-filter[data-scope="calrjobs"]`).addClass('active');
            }

            function formatRupiah(num) {
                return parseFloat(num || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            let selectedActionHash = null;
            let selectedActionMode = null;
            let selectedActionType = null;

            $(document).on('click', '.calr-dropdown-item', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $('.calr-action-dropdown').addClass('hidden');

                selectedActionHash = $(this).data('hash');
                selectedActionMode = $(this).data('mode');
                selectedActionType = $(this).data('action');

                if (!selectedActionHash || !selectedActionMode) {
                    return;
                }

                if (selectedActionMode === 'received' && !hasApFinAccess) {
                    toastr.error('You are not authorized to update or rollback receive.');
                    return;
                }

                if (selectedActionMode === 'treasury' && !hasApTreAccess) {
                    toastr.error('You are not authorized to update or rollback payment.');
                    return;
                }

                if (
                    (selectedActionMode === 'revise' || selectedActionMode === 'reminder') &&
                    !hasApFinAccess &&
                    !hasApTreAccess
                ) {
                    toastr.error('You are not authorized to process this action.');
                    return;
                }

                const calrId = $(this).data('calrnonpurchaseid');
                const rfpId = $(this).data('rfpnonpurchaseid');
                const keperluan = $(this).data('keperluan');
                const amount = $(this).data('amount');
                const currentValueUser = $(this).data('user') || '';
                const currentValueDate = $(this).data('date') || '';
                const buttonText = $(this).data('button-text') || 'Update';

                $('#modalCalrId').text(calrId || '-');
                $('#modalRfcaId').text(rfpId || '-');
                $('#modalKeperluan').text(keperluan || '-');
                $('#modalAmount').text(formatRupiah(amount));

                $('#modalMessage').val('');
                $('#modalMessageWrapper').addClass('hidden');
                $('#modalMessageLabel').text('Message');
                $('#modalMessageHint').text('Message wajib diisi.');

                if (selectedActionMode === 'received') {
                    $('#calrActionTitle').text(
                        selectedActionType === 'rollback'
                            ? 'Rollback Received Finance'
                            : 'Received Finance'
                    );
                    $('#modalUserLabel').text('User Receive:');
                    $('#modalDateLabel').text('Date Receive:');
                    $('#modalUserValue').text(currentValueUser || '');
                    $('#modalDateValue').text(currentValueDate || '');
                } else if (selectedActionMode === 'treasury') {
                    $('#calrActionTitle').text(
                        selectedActionType === 'rollback'
                            ? 'Rollback Treasury'
                            : 'Received Treasury'
                    );
                    $('#modalUserLabel').text('User Payment:');
                    $('#modalDateLabel').text('Date Payment:');
                    $('#modalUserValue').text(currentValueUser || '');
                    $('#modalDateValue').text(currentValueDate || '');
                } else if (selectedActionMode === 'revise') {
                    $('#calrActionTitle').text('Revise CALR');
                    $('#modalUserLabel').text('Action:');
                    $('#modalDateLabel').text('Date:');
                    $('#modalUserValue').text('Revise');
                    $('#modalDateValue').text(formatNow());

                    $('#modalMessageWrapper').removeClass('hidden');
                    $('#modalMessageLabel').text('Revise Message');
                    $('#modalMessage').attr('placeholder', 'Input revise reason/message...');
                    $('#modalMessageHint').text('Message wajib diisi untuk Revise.');
                } else if (selectedActionMode === 'reminder') {
                    $('#calrActionTitle').text('Send Reminder');
                    $('#modalUserLabel').text('Action:');
                    $('#modalDateLabel').text('Date:');
                    $('#modalUserValue').text('Reminder');
                    $('#modalDateValue').text(formatNow());

                    $('#modalMessageWrapper').removeClass('hidden');
                    $('#modalMessageLabel').text('Reminder Message');
                    $('#modalMessage').attr('placeholder', 'Input reminder message...');
                    $('#modalMessageHint').text('Message wajib diisi untuk Reminder.');
                }

                $('#submitCalrActionBtn')
                    .text(buttonText)
                    .removeClass('bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700 bg-green-600 hover:bg-green-700 bg-yellow-600 hover:bg-yellow-700')
                    .addClass(
                        selectedActionType === 'rollback'
                            ? 'bg-red-600 hover:bg-red-700'
                            : selectedActionMode === 'revise'
                                ? 'bg-yellow-600 hover:bg-yellow-700'
                                : 'bg-indigo-600 hover:bg-indigo-700'
                    );

                $('#calrActionModal').removeClass('hidden').addClass('flex');
            });

            $('#closeCalrActionModal').on('click', function() {
                $('#calrActionModal').addClass('hidden').removeClass('flex');
            });

            $('#submitCalrActionBtn').on('click', function() {
                if (!selectedActionHash || !selectedActionMode) {
                    return;
                }

                if (selectedActionMode === 'received' && !hasApFinAccess) {
                    toastr.error('You are not authorized to update or rollback receive.');
                    return;
                }

                if (selectedActionMode === 'treasury' && !hasApTreAccess) {
                    toastr.error('You are not authorized to update or rollback payment.');
                    return;
                }

                if (
                    (selectedActionMode === 'revise' || selectedActionMode === 'reminder') &&
                    !hasApFinAccess &&
                    !hasApTreAccess
                ) {
                    toastr.error('You are not authorized to process this action.');
                    return;
                }

                let message = '';

                if (selectedActionMode === 'revise' || selectedActionMode === 'reminder') {
                    message = ($('#modalMessage').val() || '').trim();

                    if (!message) {
                        toastr.error('Message wajib diisi.');
                        $('#modalMessage').focus();
                        return;
                    }
                }

                let url = '';

                if (selectedActionMode === 'received') {
                    url = `/calrnonpurch/${selectedActionHash}/received`;
                } else if (selectedActionMode === 'treasury') {
                    url = `/calrnonpurch/${selectedActionHash}/treasury`;
                } else if (selectedActionMode === 'revise') {
                    url = `/calrnonpurch/${selectedActionHash}/finance-revise`;
                } else if (selectedActionMode === 'reminder') {
                    url = `/calrnonpurch/${selectedActionHash}/reminder`;
                }

                if (!url) {
                    toastr.error('Invalid action.');
                    return;
                }

                $('#submitCalrActionBtn').prop('disabled', true).text('Processing...');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action_type: selectedActionType,
                        message: message,
                        comment: message,
                        reason: message
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message || 'Action processed successfully.');

                            $('#calrActionModal').addClass('hidden').removeClass('flex');

                            selectedActionHash = null;
                            selectedActionMode = null;
                            selectedActionType = null;

                            $('#modalMessage').val('');

                            $('#calrTable').DataTable().ajax.reload(null, false);
                        } else {
                            toastr.error(res.message || 'Action failed.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(
                            xhr.responseJSON?.error ||
                            xhr.responseJSON?.message ||
                            'Action failed.'
                        );
                    },
                    complete: function() {
                        $('#submitCalrActionBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</x-app-layout>