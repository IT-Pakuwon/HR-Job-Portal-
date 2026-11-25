<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $user = auth()->user();

        // Samakan seperti view RFP
        $isFinanceAccess = $isFinanceAccess ?? ($user ? $user->hasRole('FINACCESS') : false);
        $hasApFinAccess = $hasApFinAccess ?? ($user ? $user->hasRole('APFINACCESS') : false);
        $hasApTreAccess = $hasApTreAccess ?? ($user ? $user->hasRole('APTREACCESS') : false);
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-scope' => 'calrjobs'], 'CALR Jobs', '📦', 'blue', $calrjobs, false, null, 'scope-filter', true],
                [['data-scope' => 'onprogress'], 'On Progress', '⏳', 'yellow', $onProgress, false, null, 'scope-filter', true],
                [['data-scope' => 'rejected'], 'Rejected', '⛔', 'red', $rejected, false, null, 'scope-filter', true],
                [['data-scope' => 'revise'], 'Revise', '✏️', 'pink', $revise, false, null, 'scope-filter', true],
                [['data-scope' => 'completed'], 'Completed', '✅', 'green', $completed, false, null, 'scope-filter', true],
                [['data-scope' => 'all'], 'All', '🧾', 'all', $all, false, null, 'scope-filter', true],
            ];
            if ($isFinanceAccess) {
                $statusCards[] = [['data-scope' => 'calrall'], 'CALR All', '📋', 'purple', $calrAll, false, null, 'scope-filter', true];
                $statusCards[] = [['data-scope' => 'calrfinance'], 'CALR Finance', '💰', 'sky', $calrFinance, false, null, 'scope-filter', true];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="CALR Non Purchase" title-id="pageTitle" subtitle="Settlement of cash advances against RFCA">
                @if ($isFinanceAccess)
                    <div id="calrFinanceFilters" style="display: none;" class="flex-wrap items-center gap-3">
                        <div class="sppb-f relative w-44">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                            <select id="calrFinanceCompany" aria-label="Company">
                                <option value="">All Company</option>
                                @foreach ($cpnyList as $cpnyId)
                                    <option value="{{ $cpnyId }}">{{ $cpnyId }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sppb-f relative w-56">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                            <select id="calrFinanceStatus" aria-label="Finance Status">
                                <option value="">All Finance Status</option>
                                <option value="waiting_user">Waiting User</option>
                                <option value="finance_received">Finance Received</option>
                                <option value="treasury_received">Treasury Received</option>
                            </select>
                        </div>
                    </div>
                    <div id="calrAllStatusFilter" style="display: none;" class="items-center gap-2">
                        <div class="sppb-f relative w-44">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z" /></svg></span>
                            <select id="calrAllStatus" aria-label="Status">
                                <option value="">All Status</option>
                                <option value="P">On Progress</option>
                                <option value="R">Rejected</option>
                                <option value="D">Revise</option>
                                <option value="C">Completed</option>
                                <option value="X">Cancel</option>
                            </select>
                        </div>
                    </div>
                @endif
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="calrTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr id="thead-row"></tr>
                    </thead>
                    <tbody>
                        {{-- DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

                <div id="calrActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                            <h2 id="calrActionTitle" class="text-base font-bold text-gray-800 dark:text-white">Action</h2>
                        </div>

                        <div class="space-y-2 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 [&_strong]:mr-1 [&_strong]:text-[11px] [&_strong]:font-semibold [&_strong]:uppercase [&_strong]:tracking-wider [&_strong]:text-gray-500">
                            <div><strong>CALR ID:</strong> <span id="modalCalrId">-</span></div>
                            <div><strong>RFCA ID:</strong> <span id="modalRfcaId">-</span></div>
                            <div><strong>Keperluan:</strong> <span id="modalKeperluan">-</span></div>
                            <div><strong>Total Amount:</strong> <span id="modalAmount">-</span></div>
                            <div><strong id="modalUserLabel">User Receive:</strong> <span id="modalUserValue">-</span></div>
                            <div><strong id="modalDateLabel">Date Receive:</strong> <span id="modalDateValue">-</span></div>

                            <div id="modalMessageWrapper" class="hidden">
                                <label id="modalMessageLabel" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Message
                                </label>

                                <textarea id="modalMessage" rows="4"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                    placeholder="Input message..."></textarea>

                                <p id="modalMessageHint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    Message wajib diisi.
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-700">
                            <button type="button" id="closeCalrActionModal"
                                class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                Cancel
                            </button>

                            <button type="button" id="submitCalrActionBtn"
                                class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500">
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
                        left: Math.max(8, Math.min(rect.right - 208, window.innerWidth - 208 - 8)) + 'px',
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
            const $title = $('#pageTitle');
            let table;

            const titleMap = {
                calrjobs: 'CALR Non Purchase - Jobs',
                onprogress: 'CALR Non Purchase - On Progress',
                completed: 'CALR Non Purchase - Completed',
                rejected: 'CALR Non Purchase - Rejected',
                revise: 'CALR Non Purchase - Revise',
                all: 'CALR Non Purchase - All',
                calrall: 'CALR Non Purchase - CALR All',
                calrfinance: 'CALR Non Purchase - Finance',
            };

            function headerFor(sc) {
                if (sc === 'calrjobs') {
                    return `
                        <th></th>
                        <th class="px-4 py-3 text-left font-medium">Action</th>
                        <th class="px-4 py-3 text-left font-medium">Document ID</th>
                        <th class="px-4 py-3 text-left font-medium">Date</th>
                        <th class="px-4 py-3 text-left font-medium">Company</th>
                        <th class="px-4 py-3 text-left font-medium">Department</th>
                        <th class="px-4 py-3 text-left font-medium">Please Pay To</th>
                        <th class="px-4 py-3 text-left font-medium">Keperluan</th>
                        <th class="px-4 py-3 text-right font-medium">Amount RFCA</th>
                        <th class="px-4 py-3 text-left font-medium">Created By</th>
                    `;
                }

                return `
                    <th></th>
                    <th class="px-4 py-3 text-left font-medium">CALR ID</th>
                    <th class="px-4 py-3 text-left font-medium">CALR Date</th>
                    <th class="px-4 py-3 text-left font-medium">RFCA ID</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Department</th>
                    <th class="px-4 py-3 text-left font-medium">Keperluan</th>
                    <th class="px-4 py-3 text-right font-medium">Amount RFCA</th>
                    <th class="px-4 py-3 text-right font-medium">Settlement</th>
                    <th class="px-4 py-3 text-right font-medium">Diff</th>
                    <th class="px-4 py-3 text-left font-medium">Created By</th>
                    <th class="px-4 py-3 text-left font-medium">Action</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
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
                            data: 'keperluan',
                            defaultContent: '',
                            render: (value, type) => type === 'display'
                                ? `<div style="min-width: 200px; white-space: normal; overflow-wrap: anywhere;">${escapeHtml(value || '-')}</div>`
                                : (value || ''),
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
                        data: 'rfp_keperluan',
                        defaultContent: '',
                        render: (value, type) => type === 'display'
                            ? `<div style="min-width: 200px; white-space: normal; overflow-wrap: anywhere;">${escapeHtml(value || '-')}</div>`
                            : (value || ''),
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
                                        class="btn-calr-action-menu inline-flex h-8 items-center gap-1 rounded-md border border-blue-100 bg-blue-50 px-3 text-sm font-medium text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
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

                                { const fm = { 'Waiting User': ['bg-slate-50 text-slate-600 ring-slate-200', 'bg-slate-400'], 'Finance Received': ['bg-blue-50 text-blue-700 ring-blue-200', 'bg-blue-500'], 'Treasury Received': ['bg-green-50 text-green-700 ring-green-200', 'bg-green-500'] }; const [fc, fd] = fm[statusText] || fm['Waiting User']; return `<span class="inline-flex min-w-[130px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${fc}"><span class="h-1.5 w-1.5 rounded-full ${fd}"></span>${statusText}</span>`; }
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
                $('#calrAllStatusFilter').css('display', sc === 'calrall' ? 'flex' : 'none');
                $('#calrFinanceFilters').css('display', sc === 'calrfinance' ? 'flex' : 'none');
            }

            function resetThead(sc) {
                const $table = $('#calrTable');

                $table.find('thead').remove();

                const theadHtml = `
                    <thead class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
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
                            if (sc === 'calrfinance') {
                                d.cpny_id = $('#calrFinanceCompany').val() || '';
                                d.finance_status = $('#calrFinanceStatus').val() || '';
                            }
                            if (sc === 'calrall') {
                                d.status = $('#calrAllStatus').val() || '';
                            }
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
                        class="inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                        <span class="text-xs opacity-80"><i class="fa-regular fa-file-lines"></i></span><span>${escapeHtml(label)}</span>
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
                            <a href="${editUrl}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-amber-800 transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:scale-95"
                                title="Edit Revise">
                                <span class="text-xs opacity-80"><i class="fa-solid fa-pen text-[10px]"></i></span><span>${escapeHtml(label)}</span>
                            </a>

                            <a href="${showUrl}" target="_blank" rel="noopener noreferrer"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-xs text-blue-600 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white"
                                title="View Detail">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    `;
                }

                const url = `/showcalrnonpurch/${encodeURIComponent(hash)}`;

                return `
                    <a href="${url}" target="_blank" rel="noopener noreferrer"
                        class="inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide tabular-nums text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                        <span class="text-xs opacity-80"><i class="fa-regular fa-file-lines"></i></span><span>${escapeHtml(label)}</span>
                    </a>
                `;
            }

            function renderStatusBadge(data) {
                return sppStatusBadge(data, { D: 'Revise' });
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

            $('#calrFinanceCompany, #calrFinanceStatus, #calrAllStatus').select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });

            $('#calrAllStatus').on('change', function() {
                if (scope === 'calrall') {
                    table.ajax.reload();
                }
            });

            $('#calrFinanceCompany, #calrFinanceStatus').on('change', function() {
                if (scope === 'calrfinance') {
                    table.ajax.reload();
                }
            });

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
                @if ($isFinanceAccess)
                    'calrall',
                    'calrfinance',
                @endif
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
