<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'rfp' ? 'RFP' : '';
        $user = auth()->user();
        $hasRfpAllAccess = $user->hasRole('FINACCESS');

        $xlCols = 5;
        if ($hasRfpAllAccess) {
            $xlCols += 2;
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, true],
                [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false],
                [['data-status' => 'D'], 'Revise / Draft', '✏️', 'blue', $revise, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
            ];
            if ($hasRfpAllAccess) {
                $statusCards[] = [['data-scope' => 'rfp_all'], 'RFP / RFCA All', '🌐', 'purple', $rfpAll ?? 0, false];
                $statusCards[] = [['data-scope' => 'rfp_finance'], 'RFP / RFCA Job', '💰', 'sky', $rfpFinance ?? 0, false];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Request For Payment" title-id="pageTitle" subtitle="Create, track and manage payment requests" :create-url="url('/createrfpnonpurch')" create-id="createBtn">
                {{-- Type Filter: RFP / RCA --}}
                <div id="typeFilterWrapper" class="inline-flex rounded-lg bg-gray-100 p-1 dark:bg-white/5">
                    <button type="button" class="type-filter type-filter-active rounded-md px-3.5 py-1.5 text-xs font-semibold transition-all duration-200" data-type="">All</button>
                    <button type="button" class="type-filter rounded-md px-3.5 py-1.5 text-xs font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" data-type="RFP">RFP</button>
                    <button type="button" class="type-filter rounded-md px-3.5 py-1.5 text-xs font-semibold transition-all duration-200 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200" data-type="RCA">RCA</button>
                </div>

                <div class="sppb-f relative w-44">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                    <select id="rfpCompanyFilter" aria-label="Company">
                        <option value="">All Company</option>
                        @foreach ($cpnyIds as $cpnyId)
                            <option value="{{ $cpnyId }}">{{ $cpnyId }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- RFP All: Status Filter --}}
                <div id="rfpAllStatusFilterWrapper" class="hidden items-center gap-2">
                    <div class="sppb-f relative w-44">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z" /></svg></span>
                        <select id="rfpAllStatusFilter">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="D">Revise</option>
                            <option value="C">Completed</option>
                            <option value="R">Rejected</option>
                            <option value="X">Cancelled</option>
                        </select>
                    </div>
                </div>

                {{-- RFP Finance: Status Filter --}}
                <div id="rfpFinanceFilterWrapper" class="hidden flex-wrap items-center gap-3">
                    <div class="sppb-f relative w-56">
                        <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                        <select id="rfpFinanceStatusFilter">
                            <option value="">All Finance Status</option>
                            <option value="waiting_user">Waiting User</option>
                            <option value="finance_received">Finance Received</option>
                            <option value="treasury_received">Treasury Received</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="rfpTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="min-w-[130px] whitespace-nowrap px-4 py-3 text-left font-medium">RFP ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Requester</th>
                            <th class="px-4 py-3 text-left font-medium">Group Biaya</th>
                            <th class="px-4 py-3 text-left font-medium">Please Pay To</th>
                            <th class="px-4 py-3 text-left font-medium">Keperluan</th>
                            <th class="px-4 py-3 text-left font-medium">Total Amount</th>
                            <th class="px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-[#0f172a]"></tbody>
                </table>

                <div id="rfpActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
                    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
                        <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-4 dark:border-gray-700">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-file-invoice-dollar"></i></span>
                            <h2 id="rfpActionTitle" class="text-base font-bold text-gray-800 dark:text-white">Action</h2>
                        </div>

                        <div class="space-y-2 rounded-xl border border-gray-100 bg-gray-50/60 p-4 text-sm text-gray-700 dark:border-white/10 dark:bg-white/[0.03] dark:text-gray-200 [&_strong]:mr-1 [&_strong]:text-[11px] [&_strong]:font-semibold [&_strong]:uppercase [&_strong]:tracking-wider [&_strong]:text-gray-500">
                            <div><strong>RFP ID:</strong> <span id="modalRfpId">-</span></div>
                            <div><strong>Keperluan:</strong> <span id="modalKeperluan">-</span></div>
                            <div><strong>Amount:</strong> <span id="modalAmount">-</span></div>
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
                            <button type="button" id="closeRfpActionModal"
                                class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                Cancel
                            </button>

                            <button type="button" id="submitRfpActionBtn"
                                class="inline-flex h-10 items-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white transition hover:bg-blue-500">
                                Update
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .type-filter-active {
            background-color: #fff;
            color: #1f2937;
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / .1), 0 1px 2px -1px rgb(0 0 0 / .1);
        }
        .dark .type-filter-active {
            background-color: #4b5563;
            color: #fff;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script>
        let scopeFilter = '';
        let companyFilter = '';
        let financeStatusFilter = '';
        let typeFilter = '';
        var currentUser = "{{ auth()->user()->username }}";
        const hasApFinAccess = @json($hasApFinAccess ?? false);
        const hasApTreAccess = @json($hasApTreAccess ?? false);

        function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatNow() {
                const now = new Date();

                const day = String(now.getDate()).padStart(2, '0');
                const month = now.toLocaleString('en-US', { month: 'short' });
                const year = now.getFullYear();
                const time = now.toTimeString().slice(0, 8);

                return `${day} ${month} ${year} ${time}`;
            }

        $(document).ready(function() {

            $(document).on('click', '.btn-rfp-action-menu', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $btn = $(this);
                const $dropdown = $btn.closest('.rfp-action-wrap').find('.rfp-action-dropdown');

                const isOpen = !$dropdown.hasClass('hidden');

                $('.rfp-action-dropdown').addClass('hidden');

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

            let statusFilter = 'P';

const finPill = (text) => {
                    const m = {
                        'Waiting User': ['bg-slate-50 text-slate-600 ring-slate-200', 'bg-slate-400'],
                        'Finance Received': ['bg-blue-50 text-blue-700 ring-blue-200', 'bg-blue-500'],
                        'Treasury Received': ['bg-green-50 text-green-700 ring-green-200', 'bg-green-500'],
                    };
                    const [cls, dot] = m[text] || m['Waiting User'];
                    return `<span class="inline-flex min-w-[130px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${cls}"><span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${text}</span>`;
                };

            const table = $('#rfpTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_RFP',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'List_RFP',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    },
                    {
                        targets: 1,
                        width: '140px',
                        className: 'min-w-[140px] whitespace-nowrap'
                    }
                ],
                ajax: {
                    url: "{{ route('rfpnonpurch.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.scope = scopeFilter ?? '';
                        d.cpny_id = companyFilter;
                        d.finance_status = financeStatusFilter ?? '';
                        d.type = typeFilter ?? '';
                    }
                },
                order: [[1, 'desc']],
                columns: [
                    { data: null, defaultContent: '' },

                    {
                        data: 'rfpnonpurchaseid',
                        render: function(data, type, row) {
                            return sppDocCell(row, data, 'rfpnonpurch', currentUser, true);
                        }
                    },

                    { data: 'datediperlukan' },
                    { data: 'cpny_id', className: 'text-center' },
                    { data: 'department_id', className: 'text-center' },
                    { data: 'user_peminta', defaultContent: '-' },
                    { data: 'groupbiayadescr', defaultContent: '-' },
                    { data: 'pleasepayto', defaultContent: '-' },
                    { data: 'keperluan', defaultContent: '-' },

                    {
                        data: 'amountrequestpayment',
                        className: 'text-right',
                        render: function(data) {
                            let num = parseFloat(data || 0);
                            return num.toLocaleString('id-ID', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (scopeFilter !== 'rfp_finance') {
                                return '';
                            }

                            const statusText = row.finance_flow_status_text || '';

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

                            /*
                            |--------------------------------------------------------------------------
                            | Tentukan Receive/Rollback
                            |--------------------------------------------------------------------------
                            | APFINACCESS:
                            | - Waiting User       => Update Received
                            | - Finance Received   => Rollback Received
                            |
                            | APTREACCESS:
                            | - Finance Received   => Update Treasury
                            | - Treasury Received  => Rollback Treasury
                            */
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
                                data-hash="${row.eid}"
                                data-rfpnonpurchaseid="${escapeHtml(row.rfpnonpurchaseid || '')}"
                                data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                data-amountrequestpayment="${row.amountrequestpayment || 0}"
                            `;

                            let receiveItem = '';
                            let reviseItem = '';
                            let reminderItem = '';
                            const cancelItem = (hasApFinAccess || hasApTreAccess) && row.statusreceive !== 'X'
                                ? `<button type="button" class="rfp-dropdown-item block w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700" data-mode="cancel" data-action="cancel" ${commonData} data-button-text="Cancel Finance">Cancel</button>`
                                : '';

                            if (receiveAllowed) {
                                receiveItem = `
                                    <button type="button"
                                        class="rfp-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
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
                                        class="rfp-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
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
                                        class="rfp-dropdown-item block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
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
                                <div class="rfp-action-wrap inline-block text-left">
                                    <button type="button"
                                        class="btn-rfp-action-menu inline-flex h-8 items-center gap-1 rounded-md border border-blue-100 bg-blue-50 px-3 text-sm font-medium text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                        Action
                                        <span>▾</span>
                                    </button>

                                    <div class="rfp-action-dropdown fixed z-[9999] hidden w-52 overflow-hidden rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800">
                                        ${receiveItem}
                                        ${reviseItem}
                                        ${reminderItem}
                                        ${cancelItem}
                                    </div>
                                </div>
                            `;
                        }
                    },

                    {
                        data: null,
                        className: 'text-left',
                        render: function(data, type, row) {

                            // 🔥 CASE 1: RFP Finance
                            if (scopeFilter === 'rfp_finance') {

                                const statusText = row.finance_flow_status_text || '-';

                                const map = {
                                    'Waiting User': {
                                        c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                    },
                                    'Finance Received': {
                                        c: 'bg-blue-200/60 text-blue-800 border border-blue-600/40'
                                    },
                                    'Treasury Received': {
                                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                    },
                                    'Cancelled': {
                                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                    }
                                };

                                const it = map[statusText] || {
                                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                };

                                return `<span class="w-36 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${statusText}</span>`;
                            }

                            // 🔥 CASE 2: NORMAL STATUS
                            return sppStatusBadge(row.status, { D: 'Revise' });
                        }
                    },
                ],
                searchDelay: 400,
                stateSave: true,
                stateLoadParams: function(settings, data) {
                    data.order = [[1, 'desc']];
                },
                responsive: true
            });

            table.column(10).visible(false);

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                const status = $(this).data('status');
                const scope = $(this).data('scope');

                if (scope === 'rfp_all') {
                    scopeFilter = scope;
                    statusFilter = $('#rfpAllStatusFilter').val() || '';
                    financeStatusFilter = '';

                    $('#rfpAllStatusFilterWrapper').removeClass('hidden').addClass('flex');
                    $('#rfpFinanceFilterWrapper').addClass('hidden').removeClass('flex');
                    $('#rfpFinanceStatusFilter').val('').trigger('change.select2');
                    $('#createBtn').hide();
                    table.column(10).visible(false);
                } else if (scope === 'rfp_finance') {
                    scopeFilter = scope;
                    statusFilter = '';
                    financeStatusFilter = $('#rfpFinanceStatusFilter').val() || '';

                    $('#rfpAllStatusFilterWrapper').addClass('hidden').removeClass('flex');
                    $('#rfpAllStatusFilter').val('').trigger('change.select2');
                    $('#rfpFinanceFilterWrapper').removeClass('hidden').addClass('flex');
                    $('#createBtn').hide();
                    table.column(10).visible(true);
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';
                    financeStatusFilter = '';

                    $('#rfpAllStatusFilterWrapper').addClass('hidden').removeClass('flex');
                    $('#rfpAllStatusFilter').val('').trigger('change.select2');
                    $('#rfpFinanceFilterWrapper').addClass('hidden').removeClass('flex');
                    $('#rfpFinanceStatusFilter').val('').trigger('change.select2');
                    $('#createBtn').show();
                    // hide kolom Action untuk All, On Progress, Reject, Draft, Completed
                    table.column(10).visible(false);
                }

                table.ajax.reload(null, true);
            });

            $('#rfpCompanyFilter, #rfpAllStatusFilter, #rfpFinanceStatusFilter').select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });

            $('#rfpAllStatusFilter').on('change', function() {
                if (scopeFilter !== 'rfp_all') {
                    return;
                }

                statusFilter = $(this).val() || '';
                table.ajax.reload(null, true);
            });

            $('#rfpFinanceStatusFilter').on('change', function() {
                if (scopeFilter !== 'rfp_finance') {
                    return;
                }

                financeStatusFilter = $('#rfpFinanceStatusFilter').val() || '';
                table.ajax.reload(null, true);
            });

            $('#rfpCompanyFilter').on('change', function() {
                companyFilter = $(this).val() || '';
                table.ajax.reload(null, true);
            });

            function setTypeFilterActive($btn) {
                $('.type-filter')
                    .removeClass('type-filter-active bg-white text-gray-800 shadow dark:bg-gray-600 dark:text-white')
                    .addClass('text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200');
                $btn
                    .removeClass('text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200')
                    .addClass('type-filter-active bg-white text-gray-800 shadow dark:bg-gray-600 dark:text-white');
            }

            setTypeFilterActive($('.type-filter[data-type=""]'));

            $(document).on('click', '.type-filter', function() {
                typeFilter = $(this).data('type');
                setTypeFilterActive($(this));
                table.ajax.reload(null, true);
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });

            function formatRupiah(num) {
                return parseFloat(num || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            let selectedActionHash = null;
            let selectedActionMode = null;
            let selectedActionType = null;

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.rfp-action-wrap').length) {
                    $('.rfp-action-dropdown').addClass('hidden');
                }
            });

            $(window).on('scroll resize', function() {
                $('.rfp-action-dropdown').addClass('hidden');
            });

            $('.overflow-x-auto').on('scroll', function() {
                $('.rfp-action-dropdown').addClass('hidden');
            });

            $(document).on('click', '.rfp-dropdown-item', function(e) {
                e.preventDefault();
                e.stopPropagation();

                $('.rfp-action-dropdown').addClass('hidden');

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
                    (selectedActionMode === 'revise' || selectedActionMode === 'reminder' || selectedActionMode === 'cancel') &&
                    !hasApFinAccess &&
                    !hasApTreAccess
                ) {
                    toastr.error('You are not authorized to process this action.');
                    return;
                }

                const rfpid = $(this).data('rfpnonpurchaseid');
                const keperluan = $(this).data('keperluan');
                const amount = $(this).data('amountrequestpayment');
                const currentValueUser = $(this).data('user') || '';
                const currentValueDate = $(this).data('date') || '';
                const buttonText = $(this).data('button-text') || 'Update';

                $('#modalRfpId').text(rfpid || '-');
                $('#modalKeperluan').text(keperluan || '-');
                $('#modalAmount').text(formatRupiah(amount));
                $('#modalMessage').val('');
                $('#modalMessageWrapper').addClass('hidden');
                $('#modalMessageLabel').text('Message');
                $('#modalMessageHint').text('Message wajib diisi.');

                if (selectedActionMode === 'received') {
                    $('#rfpActionTitle').text(
                        selectedActionType === 'rollback'
                            ? 'Rollback Received Finance'
                            : 'Received Finance'
                    );
                    $('#modalUserLabel').text('User Receive:');
                    $('#modalDateLabel').text('Date Receive:');
                    $('#modalUserValue').text(currentValueUser || '');
                    $('#modalDateValue').text(currentValueDate || '');
                } else if (selectedActionMode === 'treasury') {
                    $('#rfpActionTitle').text(
                        selectedActionType === 'rollback'
                            ? 'Rollback Treasury'
                            : 'Received Treasury'
                    );
                    $('#modalUserLabel').text('User Payment:');
                    $('#modalDateLabel').text('Date Payment:');
                    $('#modalUserValue').text(currentValueUser || '');
                    $('#modalDateValue').text(currentValueDate || '');
                } else if (selectedActionMode === 'revise') {
                    $('#rfpActionTitle').text('Revise RFP');
                    $('#modalUserLabel').text('Action:');
                    $('#modalDateLabel').text('Date:');
                    $('#modalUserValue').text('Revise');
                    $('#modalDateValue').text(formatNow());

                    $('#modalMessageWrapper').removeClass('hidden');
                    $('#modalMessageLabel').text('Revise Message');
                    $('#modalMessage').attr('placeholder', 'Input revise reason/message...');
                    $('#modalMessageHint').text('Message wajib diisi untuk Revise.');
                } else if (selectedActionMode === 'cancel') {
                    $('#rfpActionTitle').text('Cancel Finance Status');
                    $('#modalUserLabel').text('Action:');
                    $('#modalDateLabel').text('Date:');
                    $('#modalUserValue').text('Cancel');
                    $('#modalDateValue').text(formatNow());
                } else if (selectedActionMode === 'reminder') {
                    $('#rfpActionTitle').text('Send Reminder');
                    $('#modalUserLabel').text('Action:');
                    $('#modalDateLabel').text('Date:');
                    $('#modalUserValue').text('Reminder');
                    $('#modalDateValue').text(formatNow());

                    $('#modalMessageWrapper').removeClass('hidden');
                    $('#modalMessageLabel').text('Reminder Message');
                    $('#modalMessage').attr('placeholder', 'Input reminder message...');
                    $('#modalMessageHint').text('Message wajib diisi untuk Reminder.');
                }

                $('#submitRfpActionBtn')
                    .text(buttonText)
                    .removeClass('bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700 bg-green-600 hover:bg-green-700 bg-yellow-600 hover:bg-yellow-700')
                    .addClass(
                        selectedActionType === 'rollback' || selectedActionMode === 'cancel'
                            ? 'bg-red-600 hover:bg-red-700'
                            : selectedActionMode === 'revise'
                                ? 'bg-yellow-600 hover:bg-yellow-700'
                                : 'bg-indigo-600 hover:bg-indigo-700'
                    );

                $('#rfpActionModal').removeClass('hidden').addClass('flex');
            });

            $('#closeRfpActionModal').on('click', function() {
                $('#rfpActionModal').addClass('hidden').removeClass('flex');
            });

            $('#submitRfpActionBtn').on('click', function() {
                if (!selectedActionHash || !selectedActionMode) return;

                if (selectedActionMode === 'received' && !hasApFinAccess) {
                    toastr.error('You are not authorized to update or rollback receive.');
                    return;
                }

                if (selectedActionMode === 'treasury' && !hasApTreAccess) {
                    toastr.error('You are not authorized to update or rollback payment.');
                    return;
                }

                if (
                    (selectedActionMode === 'revise' || selectedActionMode === 'reminder' || selectedActionMode === 'cancel') &&
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
                    url = `/rfpnonpurch/${selectedActionHash}/received`;
                } else if (selectedActionMode === 'treasury') {
                    url = `/rfpnonpurch/${selectedActionHash}/treasury`;
                } else if (selectedActionMode === 'revise') {
                    url = `/rfpnonpurch/${selectedActionHash}/finance-revise`;
                } else if (selectedActionMode === 'reminder') {
                    url = `/rfpnonpurch/${selectedActionHash}/reminder`;
                } else if (selectedActionMode === 'cancel') {
                    url = `/rfpnonpurch/${selectedActionHash}/finance-cancel`;
                }

                if (!url) {
                    toastr.error('Invalid action.');
                    return;
                }

                $('#submitRfpActionBtn').prop('disabled', true).text('Processing...');

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
                            $('#rfpActionModal').addClass('hidden').removeClass('flex');

                            selectedActionHash = null;
                            selectedActionMode = null;
                            selectedActionType = null;
                            $('#modalMessage').val('');

                            $('#rfpTable').DataTable().ajax.reload(null, false);
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
                        $('#submitRfpActionBtn').prop('disabled', false);
                    }
                });
            });
        });
    </script>
</x-app-layout>
