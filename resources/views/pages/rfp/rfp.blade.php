<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'rfp' ? 'RFP' : '';
        $user = auth()->user();
        $hasRfpAllAccess = $user->hasRole('FINACCESS');

        $xlCols = 7;
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

                <a href="#" class="status-filter group block h-full" data-scope="finance_received">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md">
                        <div class="flex h-7 w-7 items-center justify-center">🏦</div>
                        <div class="flex flex-col leading-tight">
                            <p class="text-sm font-medium">Finance Received</p>
                        </div>
                        <p class="text-base font-extrabold">{{ $financeReceived ?? 0 }}</p>
                    </div>
                </a>

                <a href="#" class="status-filter group block h-full" data-scope="treasury_received">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700 hover:-translate-y-1 hover:bg-green-100 hover:shadow-md">
                        <div class="flex h-7 w-7 items-center justify-center">💰</div>
                        <div class="flex flex-col leading-tight">
                            <p class="text-sm font-medium">Treasury Received</p>
                        </div>
                        <p class="text-base font-extrabold">{{ $treasuryReceived ?? 0 }}</p>
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
                    url: "{{ route('rfp.json') }}",
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
                        data: 'rfp_id',
                        render: function(data, type, row) {
                            let url = `/showrfp/${row.eid}`;
                            let cls = 'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editrfp/${row.eid}`;
                                cls = 'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                            }

                            return `<a href="${url}" class="${cls}">${data}</a>`;
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
                                return '-';
                            }

                            // Received stage
                            if (row.action_state === 'received') {
                                if (!hasApFinAccess) {
                                    return `<span class="text-gray-400 italic">No Access</span>`;
                                }

                                return `
                                    <button type="button"
                                        class="btn-action-rfp rounded bg-green-600 px-3 py-1 text-white hover:bg-green-700"
                                        data-mode="received"
                                        data-hash="${row.eid}"
                                        data-rfpid="${row.rfp_id}"
                                        data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                        data-amount="${row.rfp_amount || 0}"
                                        data-user="${escapeHtml(row.user_receive || '')}"
                                        data-date="${escapeHtml(row.receive_date || '')}"
                                        data-button-text="${escapeHtml(row.receive_button_text || 'Update Received')}">
                                        ${row.receive_button_text || 'Update Received'}
                                    </button>
                                `;
                            }

                            // Payment stage
                            if (row.action_state === 'treasury') {
                                if (!hasApTreAccess) {
                                    return `<span class="text-gray-400 italic">No Access</span>`;
                                }

                                return `
                                    <button type="button"
                                        class="btn-action-rfp rounded bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700"
                                        data-mode="treasury"
                                        data-hash="${row.eid}"
                                        data-rfpid="${row.rfp_id}"
                                        data-keperluan="${escapeHtml(row.keperluan || '-')}"
                                        data-amount="${row.rfp_amount || 0}"
                                        data-user="${escapeHtml(row.user_payment || '')}"
                                        data-date="${escapeHtml(row.payment_date || '')}"
                                        data-button-text="${escapeHtml(row.treasury_button_text || 'Update Treasury')}">
                                        ${row.treasury_button_text || 'Update Treasury'}
                                    </button>
                                `;
                            }

                            return '-';
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

                if (scope) {
                    scopeFilter = scope;
                    statusFilter = '';
                } else {
                    statusFilter = status ?? '';
                    scopeFilter = '';
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

            let selectedActionHash = null;
            let selectedActionMode = null;

            function formatRupiah(num) {
                return parseFloat(num || 0).toLocaleString('id-ID', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }

            $(document).on('click', '.btn-action-rfp', function() {
                selectedActionHash = $(this).data('hash');
                selectedActionMode = $(this).data('mode');

                if (selectedActionMode === 'received' && !hasApFinAccess) {
                    toastr.error('You are not authorized to update or rollback receive.');
                    return;
                }

                if (selectedActionMode === 'treasury' && !hasApTreAccess) {
                    toastr.error('You are not authorized to update or rollback payment.');
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

                if (selectedActionMode === 'received') {
                    $('#rfpActionTitle').text('Received Finance');
                    $('#modalUserLabel').text('User Receive:');
                    $('#modalDateLabel').text('Date Receive:');
                } else {
                    $('#rfpActionTitle').text('Received Treasury');
                    $('#modalUserLabel').text('User Payment:');
                    $('#modalDateLabel').text('Date Payment:');
                }

                // $('#modalUserValue').text(currentValueUser || currentUser);
                // $('#modalDateValue').text(currentValueDate || formatNow());
                // kalau data masih kosong → tampil kosong
                if (!currentValueUser && !currentValueDate) {
                    $('#modalUserValue').text('');
                    $('#modalDateValue').text('');
                } else {
                    $('#modalUserValue').text(currentValueUser || '');
                    $('#modalDateValue').text(currentValueDate || '');
                }

                $('#submitRfpActionBtn').text(buttonText);

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
                        _token: '{{ csrf_token() }}'
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
