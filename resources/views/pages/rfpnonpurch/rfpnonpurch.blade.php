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
        <div class="xl:grid-cols-{{ $xlCols }} grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">

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

            <a href="#" class="status-filter group block h-full" data-status="R">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⛔️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reject</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $reject }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="D">
                <div class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✏️</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise / Draft</p>
                    </div>
                    <p class="shrink-0 text-base font-extrabold">{{ $revise }}</p>
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
             <div class="flex flex-row items-center justify-between gap-4 sm:flex-row sm:items-center">
                <h1 id="pageTitle" class="text-base font-extrabold text-gray-700 dark:text-white">
                    Request For Payment
                </h1>

                <div class="flex items-center gap-4">                  
                    <a id="createBtn" href="{{ url('/createrfpnonpurch') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>
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
                            <th>Requester</th>
                            <th>Group Biaya</th>
                            <th>Please Pay To</th>
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
                    }
                ],
                ajax: {
                    url: "{{ route('rfpnonpurch.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.scope = scopeFilter ?? '';
                    }
                },
                order: [[2, 'desc']],
                columns: [
                    { data: null, defaultContent: '' },

                    {
                        data: 'rfpnonpurchaseid',
                        render: function(data, type, row) {
                            let url = `/showrfpnonpurch/${row.eid}`;
                            let cls = 'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editrfpnonpurch/${row.eid}`;
                                cls = 'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                            }

                            return `<a href="${url}" class="${cls}">${data}</a>`;
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

                            if (scopeFilter !== 'rfp_all') {
                                return '';
                            }

                            const statusText = row.finance_flow_status_text || '';

                            const isReceiveCompleted =
                                row.statusreceive === 'C' ||
                                statusText === 'Finance Received' ||
                                statusText === 'Treasury Received';

                            const isPaymentCompleted =
                                row.userpayment && row.paymentdate;

                            // TREASURY
                            if (hasApTreAccess && isReceiveCompleted) {

                                const buttonText = isPaymentCompleted
                                    ? 'Rollback Treasury'
                                    : 'Update Treasury';

                                const btnClass = isPaymentCompleted
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-indigo-600 hover:bg-indigo-700';

                                return `
                                    <button type="button"
                                        class="btn-action-rfp rounded ${btnClass} px-3 py-1 text-white"
                                        data-mode="treasury"
                                        data-action="${isPaymentCompleted ? 'rollback' : 'update'}"
                                        data-hash="${row.eid}"
                                        data-rfpnonpurchaseid="${row.rfpnonpurchaseid}"
                                        data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                        data-amountrequestpayment="${row.amountrequestpayment || 0}"
                                        data-user="${escapeHtml(row.userpayment || '')}"
                                        data-date="${escapeHtml(row.paymentdate || '')}"
                                        data-button-text="${buttonText}">
                                        ${buttonText}
                                    </button>
                                `;
                            }

                            // FINANCE
                            if (hasApFinAccess && (statusText === 'Waiting User' || statusText === 'Finance Received')) {

                                const isReceived = row.userreceive && row.receivedate;

                                const buttonText = isReceived
                                    ? 'Rollback Received'
                                    : 'Update Received';

                                const btnClass = isReceived
                                    ? 'bg-red-600 hover:bg-red-700'
                                    : 'bg-green-600 hover:bg-green-700';

                                return `
                                    <button type="button"
                                        class="btn-action-rfp rounded ${btnClass} px-3 py-1 text-white"
                                        data-mode="received"
                                        data-action="${isReceived ? 'rollback' : 'update'}"
                                        data-hash="${row.eid}"
                                        data-rfpnonpurchaseid="${row.rfpnonpurchaseid}"
                                        data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                        data-amountrequestpayment="${row.amountrequestpayment || 0}"
                                        data-user="${escapeHtml(row.userreceive || '')}"
                                        data-date="${escapeHtml(row.receivedate || '')}"
                                        data-button-text="${buttonText}">
                                        ${buttonText}
                                    </button>
                                `;
                            }

                            return `<span class="text-gray-400 italic">No Access</span>`;
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
                stateSave: true,
                responsive: true
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();

                const status = $(this).data('status');
                const scope = $(this).data('scope');

                if (scope === 'rfp_all') {
                    scopeFilter = scope;
                    statusFilter = '';

                    // tampilkan kolom Action hanya saat RFP Finance
                    table.column(10).visible(true);
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';

                    // hide kolom Action untuk All, On Progress, Reject, Draft, Completed
                    table.column(10).visible(false);
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

            $(document).on('click', '.btn-action-rfp', function() {
                selectedActionHash = $(this).data('hash');
                selectedActionMode = $(this).data('mode');
                selectedActionType = $(this).data('action');

                if (selectedActionMode === 'received' && !hasApFinAccess) {
                    toastr.error('You are not authorized to update or rollback receive.');
                    return;
                }

                if (selectedActionMode === 'treasury' && !hasApTreAccess) {
                    toastr.error('You are not authorized to update or rollback payment.');
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

                if (selectedActionMode === 'received') {
                    $('#rfpActionTitle').text(
                        selectedActionType === 'rollback'
                            ? 'Rollback Received Finance'
                            : 'Received Finance'
                    );
                    $('#modalUserLabel').text('User Receive:');
                    $('#modalDateLabel').text('Date Receive:');
                } else {
                    $('#rfpActionTitle').text('Received Treasury');
                    $('#modalUserLabel').text('User Payment:');
                    $('#modalDateLabel').text('Date Payment:');
                }

                $('#modalUserValue').text(currentValueUser || '');
                $('#modalDateValue').text(currentValueDate || '');

                $('#submitRfpActionBtn')
                    .text(buttonText)
                    .removeClass('bg-indigo-600 hover:bg-indigo-700 bg-red-600 hover:bg-red-700')
                    .addClass(selectedActionType === 'rollback'
                        ? 'bg-red-600 hover:bg-red-700'
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

                let url = '';
                if (selectedActionMode === 'received') {
                    url = `/rfp/${selectedActionHash}/received`;
                } else {
                    url = `/rfp/${selectedActionHash}/treasury`;
                }
 
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        action_type: selectedActionType
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message || 'Updated successfully.');
                            $('#rfpActionModal').addClass('hidden').removeClass('flex');
                            $('#rfpTable').DataTable().ajax.reload(null, false);
                        } else {
                            toastr.error(res.message || 'Update failed.');
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Update failed.');
                    }
                });
            });
        });
    </script>
</x-app-layout>
