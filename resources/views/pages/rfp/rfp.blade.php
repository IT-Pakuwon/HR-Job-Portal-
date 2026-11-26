<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'rfp' ? 'RFP' : '';
        $user = auth()->user();
        $hasRfpFinanceAccess = $hasRfpFinanceAccess ?? $user->hasRole('FINACCESS');
        $hasRfpAllAccess = $hasRfpAllAccess ?? ($hasRfpFinanceAccess || $user->hasRole('PURCHACCESS'));

        $xlCols = 5;
        if ($hasRfpAllAccess) {
            $xlCols += 1;
        }
        if ($hasRfpFinanceAccess) {
            $xlCols += 1;
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, false],
                [['data-status' => 'D'], 'Revise', '✏️', 'blue', $revise, false],
                [['data-status' => 'H'], 'Hold', '✏️', 'pink', $hold, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
                ...($hasRfpAllAccess ? [[['data-scope' => 'rfp_all'], 'RFP All', '🌐', 'purple', $rfpAll ?? 0, false]] : []),
                ...($hasRfpFinanceAccess ? [[['data-scope' => 'rfp_finance'], 'RFP Finance', '💰', 'sky', $rfpFinance ?? 0, false]] : []),
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div
            class="dt-pretty mt-3 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Request For Payment" subtitle="Track and manage payment requests">
                <div class="sppb-f relative w-44"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span><select id="rfpCompanyFilter">
                            <option value="">All Company</option>
                            @foreach ($cpnyIds as $cpnyId)
                                <option value="{{ $cpnyId }}">{{ $cpnyId }}</option>
                            @endforeach
                        </select></div>
                <div class="sppb-f relative w-44"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg></span><select id="rfpTypePoFilter">
                            <option value="">All Type PO</option>
                            <option value="PO">PO</option>
                            <option value="SPK">SPK</option>
                            <option value="KONTRAK">KONTRAK</option>
                        </select></div>
                <div id="rfpAllStatusFilterWrapper" class="sppb-f relative hidden w-48"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span><select id="rfpAllStatusFilter">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="D">Revise</option>
                            <option value="H">Hold</option>
                            <option value="C">Completed</option>
                            <option value="R">Rejected</option>
                            <option value="X">Cancelled</option>
                        </select></div>
                <div id="rfpFinanceFilterWrapper" class="sppb-f relative hidden w-56"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span><select id="rfpFinanceStatusFilter">
                                <option value="">All Status</option>
                                <option value="waiting_user">Waiting User</option>
                                <option value="finance_received">Finance Received</option>
                                <option value="treasury_received">Treasury Received</option>
                            </select></div>
            </x-list-page-header>

            <div class="relative overflow-hidden">
                <table id="rfpTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">RFP ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">SPPBJKT - CS</th>
                            <th class="px-4 py-3 text-left font-medium">PO / Kontrak</th>
                            <th class="px-4 py-3 text-left font-medium">Type PO</th>
                            <th class="px-4 py-3 text-left font-medium">IR ID</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
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
    <script src="https://cdn.jsdelivr.net/npm/dayjs@1/dayjs.min.js"></script>

    <script>
        let scopeFilter = '';
        let companyFilter = '';
        let financeStatusFilter = '';
        let typePoFilter = '';
        var currentUser = "{{ auth()->user()->username }}";
        const hasApFinAccess = @json($hasApFinAccess ?? false);
        const hasApTreAccess = @json($hasApTreAccess ?? false);
        const rfpKontrakBudgetCreateUrl = @json(route('rfp.kontrak-budget.create', ['hash' => '__HASH__']));

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
            let statusFilter = 'P';

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
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
                        title: 'List_RFP',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                        type: 'inline'
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        width: '52px',
                        className: 'text-center',
                        orderable: false
                    }
                ],
                ajax: {
                    url: "{{ route('rfp.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.scope = scopeFilter ?? '';
                        d.cpny_id = companyFilter;
                        d.finance_status = financeStatusFilter ?? '';
                        d.type_po = typePoFilter ?? '';
                    }
                },
                order: [[1, 'desc']],
                columns: [
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            const isHoldView = statusFilter === 'H' && scopeFilter !== 'rfp_all';
                            const rowTypePo = row.type_po ?? row.typepo ?? '';
                            const isKontrak = String(rowTypePo).trim().toUpperCase() === 'KONTRAK';

                            if (!isHoldView || !isKontrak) {
                                return '';
                            }

                            return `
                                <button type="button"
                                    class="btn-rfp-hold-add inline-flex h-8 w-8 items-center justify-center rounded bg-indigo-600 text-base font-bold text-white hover:bg-indigo-700"
                                    title="Add RFP Kontrak Budget"
                                    data-hash="${escapeHtml(row.eid || '')}"
                                    data-rfp-id="${escapeHtml(row.rfp_id || '')}"
                                    data-kontrak-id="${escapeHtml(row.kontrak_id || '')}">
                                    +
                                </button>
                            `;
                        }
                    },

                    {
                        data: 'rfp_id',
                        className: 'whitespace-nowrap',
                        render: function(data, type, row) {

                            let url = `/showrfp/${row.eid}`;
                            let cls = 'group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white';
                            let ico = 'fa-regular fa-file-lines';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editrfp/${row.eid}`;
                                cls = 'group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-amber-800 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-300';
                                ico = 'fa-solid fa-pen text-[10px]';
                            }

                            return `
                                <a href="${url}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="${cls}"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 ${String(ico).includes('fa-pen') ? 'group-hover/doc:rotate-12' : 'group-hover/doc:-rotate-6'}"><i class="${ico}"></i></span><span>${data}</span></a>
                            `;
                        }
                    },
                    { data: 'rfp_date' },
                    { data: 'cpny_id', className: 'text-center' },
                    { data: 'department_id', className: 'text-center' },
                    { data: 'sppbjkt_cs', defaultContent: '-' },
                    { data: 'po_kontrak', defaultContent: '-' },
                    { data: 'type_po', defaultContent: '-', className: 'text-center' },
                    { data: 'ir_id', defaultContent: '-' },
                    { data: 'vendor_name', defaultContent: '-' },
                    { data: 'keperluan', defaultContent: '-' },

                    {
                        data: 'rfp_amount',
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
                                row.status_receive === 'C' ||
                                statusText === 'Finance Received' ||
                                statusText === 'Treasury Received';

                            const isPaymentCompleted =
                                row.status_payment === 'C' ||
                                (row.user_payment && row.payment_date);

                            let receiveMode = '';
                            let receiveAction = '';
                            let receiveText = '';
                            let receiveUser = '';
                            let receiveDate = '';
                            let receiveAllowed = false;

                            // TREASURY
                            if (hasApTreAccess && isReceiveCompleted) {
                                receiveMode = 'treasury';
                                receiveAction = isPaymentCompleted ? 'rollback' : 'update';
                                receiveText = isPaymentCompleted ? 'Rollback Treasury' : 'Update Treasury';
                                receiveUser = row.user_payment || '';
                                receiveDate = row.payment_date || '';
                                receiveAllowed = true;
                            }
                            // FINANCE
                            else if (
                                hasApFinAccess &&
                                (statusText === 'Waiting User' || statusText === 'Finance Received')
                            ) {
                                const isReceived =
                                    row.status_receive === 'C' ||
                                    (row.user_receive && row.receive_date);

                                receiveMode = 'received';
                                receiveAction = isReceived ? 'rollback' : 'update';
                                receiveText = isReceived ? 'Rollback Received' : 'Update Received';
                                receiveUser = row.user_receive || '';
                                receiveDate = row.receive_date || '';
                                receiveAllowed = true;
                            }

                            const commonData = `
                                data-hash="${row.eid}"
                                data-rfpid="${escapeHtml(row.rfp_id || '')}"
                                data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                data-amount="${row.rfp_amount || 0}"
                            `;

                            let receiveItem = '';
                            let reviseItem = '';
                            let reminderItem = '';

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
                                        c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                    },
                                    'Finance Received': {
                                        c: 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30'
                                    },
                                    'Treasury Received': {
                                        c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30'
                                    }
                                };

                                const it = map[statusText] || {
                                    c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                                };

                                return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${statusText}</span>`;
                            }

                            // 🔥 CASE 2: NORMAL STATUS
                            const map = {
                                'D': { t: 'Revise', c: 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-200 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-500/30' },
                                'P': { t: 'On Progress', c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30' },
                                'H': { t: 'Hold', c: 'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30' },
                                'C': { t: 'Completed', c: 'bg-green-50 text-green-700 ring-1 ring-inset ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30' },
                                'X': { t: 'Cancel', c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30' },
                                'R': { t: 'Rejected', c: 'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30' },
                            };

                            const it = map[row.status] || {
                                t: row.status || '-',
                                c: 'bg-slate-50 text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-500/10 dark:text-slate-300 dark:ring-slate-500/30'
                            };

                            return `<span class="inline-flex min-w-[116px] items-center justify-center whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ${it.c}">${it.t}</span>`;
                        }
                    },
                ],
                searchDelay: 400,
                stateSave: true,
                stateLoadParams: function(settings, data) {
                    data.order = [[1, 'desc']];
                }
            });

            table.column(0).visible(statusFilter === 'H');
            table.column(12).visible(false);

            $(document).on('click', '.btn-rfp-hold-add', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const hash = String($(this).data('hash') || '').trim();
                if (!hash) {
                    toastr.error('Hash RFP tidak ditemukan.');
                    return;
                }

                window.location.href = rfpKontrakBudgetCreateUrl.replace('__HASH__', encodeURIComponent(hash));
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                const status = $(this).data('status');
                const scope = $(this).data('scope');

                if (scope === 'rfp_all') {
                    scopeFilter = scope;
                    statusFilter = $('#rfpAllStatusFilter').val() || '';
                    financeStatusFilter = '';

                    $('#rfpAllStatusFilterWrapper').removeClass('hidden');
                    $('#rfpFinanceFilterWrapper').addClass('hidden');
                    $('#rfpFinanceStatusFilter').val('').trigger('change.select2');
                    $('#createBtn').hide();
                    table.column(0).visible(false);
                    table.column(12).visible(false);
                } else if (scope === 'rfp_finance') {
                    scopeFilter = scope;
                    statusFilter = '';
                    financeStatusFilter = $('#rfpFinanceStatusFilter').val() || '';

                    $('#rfpAllStatusFilterWrapper').addClass('hidden');
                    $('#rfpAllStatusFilter').val('').trigger('change.select2');
                    $('#rfpFinanceFilterWrapper').removeClass('hidden');
                    $('#createBtn').hide();
                    table.column(0).visible(false);
                    // tampilkan kolom Action hanya saat RFP Finance
                    table.column(12).visible(true);
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';
                    financeStatusFilter = '';

                    $('#rfpAllStatusFilterWrapper').addClass('hidden');
                    $('#rfpAllStatusFilter').val('').trigger('change.select2');
                    $('#rfpFinanceFilterWrapper').addClass('hidden');
                    $('#rfpFinanceStatusFilter').val('').trigger('change.select2');
                    $('#createBtn').show();
                    table.column(0).visible(statusFilter === 'H');
                    // hide kolom Action untuk All, On Progress, Reject, Draft, Completed
                    table.column(12).visible(false);
                }

                table.ajax.reload(null, true);
            });

            // styled dropdowns (same Select2 skin as the other lists)
            $('#rfpCompanyFilter, #rfpTypePoFilter, #rfpAllStatusFilter, #rfpFinanceStatusFilter').each(function() {
                $(this).select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });
            });

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

            $('#rfpTypePoFilter').on('change', function() {
                typePoFilter = $(this).val() || '';
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
                    (selectedActionMode === 'revise' || selectedActionMode === 'reminder') &&
                    !hasApFinAccess &&
                    !hasApTreAccess
                ) {
                    toastr.error('You are not authorized to process this action.');
                    return;
                }

                const rfpid = $(this).data('rfpid');
                const keperluan = $(this).data('keperluan');
                const amount = $(this).data('amount');
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
                        selectedActionType === 'rollback'
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
                    url = `/rfp/${selectedActionHash}/received`;
                } else if (selectedActionMode === 'treasury') {
                    url = `/rfp/${selectedActionHash}/treasury`;
                } else if (selectedActionMode === 'revise') {
                    url = `/rfp/${selectedActionHash}/finance-revise`;
                } else if (selectedActionMode === 'reminder') {
                    url = `/rfp/${selectedActionHash}/reminder`;
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
