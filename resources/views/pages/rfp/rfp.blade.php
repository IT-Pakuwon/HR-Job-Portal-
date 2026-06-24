<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'rfp' ? 'RFP' : '';
        $user = auth()->user();
        $hasRfpAllAccess = $user->hasRole('FINACCESS');

        $xlCols = 5;
        if ($hasRfpAllAccess) {
            $xlCols++;
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="xl:grid-cols-{{ $xlCols }} grid auto-rows-fr grid-cols-1 gap-5 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

            <a href="#" class="status-filter group block h-full" data-status="">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📄</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $all }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="P">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $onProgress }}</p>
                </div>
            </a>    

            <a href="#" class="status-filter group block h-full" data-status="D">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✏️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $revise }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="H">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✏️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Hold</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $hold ?? 0 }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="C">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $completed }}</p>
                </div>
            </a>

            @if ($hasRfpAllAccess)
                <a href="#" class="status-filter group block h-full" data-scope="rfp_all">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                        <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🌐</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">RFP Finance</p>
                        </div>
                        <p class="shrink-0 text-base font-extrabold">{{ $rfpAll ?? 0 }}</p>
                    </div>
                </a>
            @endif
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Request For Payment</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="rfpTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th>RFP ID</th>
                            <th>Date</th>
                            <th>Company</th>
                            <th>Department</th>
                            <th>SPPBJKT - CS</th>
                            <th>PO / Kontrak</th>
                            <th>IR ID</th>
                            <th>Vendor</th>
                            <th>Keperluan</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>

                <div id="rfpActionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                    <div class="w-full max-w-lg rounded-xl bg-white p-6 dark:bg-gray-800">
                        <h2 id="rfpActionTitle" class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Action</h2>

                        <div class="space-y-3 text-sm">
                            <div><strong>RFP ID:</strong> <span id="modalRfpId">-</span></div>
                            <div><strong>Keperluan:</strong> <span id="modalKeperluan">-</span></div>
                            <div><strong>Amount:</strong> <span id="modalAmount">-</span></div>
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
                            <button type="button" id="closeRfpActionModal"
                                class="rounded border border-gray-300 px-4 py-2 hover:bg-gray-100 dark:border-gray-600 dark:hover:bg-gray-700">
                                Cancel
                            </button>

                            <button type="button" id="submitRfpActionBtn"
                                class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
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
                    }
                },
                order: [[2, 'desc']],
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
                        render: function(data, type, row) {

                            let url = `/showrfp/${row.eid}`;
                            let cls = 'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editrfp/${row.eid}`;
                                cls = 'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                            }

                            return `
                                <a href="${url}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="${cls}">
                                    ${data}
                                </a>
                            `;
                        }
                    },
                    { data: 'rfp_date' },
                    { data: 'cpny_id', className: 'text-center' },
                    { data: 'department_id', className: 'text-center' },
                    { data: 'sppbjkt_cs', defaultContent: '-' },
                    { data: 'po_kontrak', defaultContent: '-' },
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
                            if (scopeFilter !== 'rfp_all') {
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
                                        class="btn-rfp-action-menu inline-flex items-center gap-2 rounded bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700">
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
                            if (scopeFilter === 'rfp_all') {

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
                                    }
                                };

                                const it = map[statusText] || {
                                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                };

                                return `<span class="w-36 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${statusText}</span>`;
                            }

                            // 🔥 CASE 2: NORMAL STATUS
                            const map = {
                                'D': { t: 'Revise', c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40' },
                                'P': { t: 'On Progress', c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40' },
                                'H': { t: 'Hold', c: 'bg-yellow-200/60 text-yellow-800 border border-yellow-600/40' },
                                'C': { t: 'Completed', c: 'bg-green-200/60 text-green-800 border border-green-600/40' },
                                'X': { t: 'Cancel', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
                                'R': { t: 'Rejected', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
                            };

                            const it = map[row.status] || {
                                t: row.status || '-',
                                c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                            };

                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    },
                ],
                searchDelay: 400,
                stateSave: true
            });

            table.column(0).visible(statusFilter === 'H');

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
                    statusFilter = '';

                    table.column(0).visible(false);
                    // tampilkan kolom Action hanya saat RFP Finance
                    table.column(11).visible(true);
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';

                    table.column(0).visible(statusFilter === 'H');
                    // hide kolom Action untuk All, On Progress, Reject, Draft, Completed
                    table.column(11).visible(false);
                }

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
