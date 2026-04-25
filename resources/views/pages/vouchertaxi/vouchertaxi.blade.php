<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'itemreq' ? 'HR' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full" data-status="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- On Progress --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="P">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">On Progress</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                    </div>
                </a>
            </button>

            {{-- Reject --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="R">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Reject</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="D">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Revise / Draft</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                    </div>
                </a>
            </button>

            {{-- Completed --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="C">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Completed</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                    </div>
                </a>
            </button>
        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Voucher Taxi</h1>

                {{-- sesuaikan URL/route create --}}
                <button type="button" id="openCreateVoucherModal"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="voucherTaxiTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-6 py-2 font-medium">Doc ID</th>
                            <th class="w-32 px-6 py-2 font-medium">Voucher Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Date Used</th>
                            <th class="w-32 px-6 py-2 font-medium">Company</th>
                            <th class="w-32 px-6 py-2 font-medium">Department</th>
                            <th class="w-32 px-6 py-2 font-medium">User Peminta</th>
                            <th class="w-32 px-6 py-2 font-medium">To</th>
                            <th class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Perpose</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div id="createVoucherModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/50">
        <div class="mx-auto mt-10 w-[95%] max-w-7xl rounded bg-white shadow-lg dark:bg-gray-800">
            <form id="voucherTaxiForm">
                @csrf

                <div class="border-b px-6 py-4">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">Create Voucher Taxi</h2>
                </div>

                <div class="grid grid-cols-1 gap-5 px-6 py-5 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Company <span class="text-red-500">*</span></label>
                        <select name="cpny_id" class="w-full border px-3 py-2" required>
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $p)
                                <option value="{{ $p->cpny_id }}">{{ $p->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Department <span class="text-red-500">*</span></label>
                        <select name="department_id" class="w-full border px-3 py-2" required>
                            <option value="">Select Department</option>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Requester <span class="text-red-500">*</span></label>
                        <select name="user_peminta" class="w-full border px-3 py-2" required>
                            <option value="">Select Requester</option>
                            @foreach ($requesters as $p)
                                <option value="{{ $p->username }}" {{ auth()->user()->username == $p->username ? 'selected' : '' }}>
                                    {{ $p->username }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Date Used <span class="text-red-500">*</span></label>
                        <input type="date" name="date_used" class="w-full border px-3 py-2" required>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 px-6 pb-5 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Type Trip <span class="text-red-500">*</span></label>
                        <div class="flex gap-4 pt-2">
                            <label class="inline-flex items-center gap-1">
                                <input type="radio" name="type_trip" value="Return" checked required>
                                Return
                            </label>
                            <label class="inline-flex items-center gap-1">
                                <input type="radio" name="type_trip" value="One Way" required>
                                One Way
                            </label>
                        </div>
                    </div>
                </div>

                <div class="px-6 pb-5">
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="mb-1 block text-sm font-semibold">To <span class="text-red-500">*</span></label>
                            <input type="text" name="to" class="w-full border px-3 py-2" placeholder="To ..." required>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold">Purpose <span class="text-red-500">*</span></label>
                            <input type="text" name="perpose" class="w-full border px-3 py-2" placeholder="Purpose ..." required>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 px-6 pb-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Company Expense <span class="text-red-500">*</span></label>
                        <select name="cpny_id_expense" class="w-full border px-3 py-2" required>
                            <option value="">Select Company Expense</option>
                            @foreach ($company as $p)
                                <option value="{{ $p->cpny_id }}">{{ $p->cpny_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Topup <span class="text-red-500">*</span></label>
                        <select name="user_topup" class="w-full border px-3 py-2" required>
                            <option value="">Select Topup</option>
                            @foreach ($requesters as $p)
                                <option value="{{ $p->username }}">{{ $p->username }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-start gap-2 border-t px-6 py-4">
                    <button type="submit" id="submitVoucherBtn"
                        class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        Submit Approval
                    </button>

                    <button type="button" id="closeCreateVoucherModal"
                        class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="editVoucherTaxiModal" class="fixed inset-0 z-50 flex hidden items-center justify-center bg-black/50">
        <div class="w-full max-w-5xl rounded-lg bg-white p-5 dark:bg-gray-700">
            <h2 class="mb-4 text-base font-semibold text-gray-800 dark:text-white">
                Edit Voucher Taxi
            </h2>

            <form id="editVoucherTaxiForm">
                @csrf

                <input type="hidden" id="edit_docid">

                <div class="grid grid-cols-1 gap-5 md:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">Company</label>
                        <select name="cpny_id" id="edit_cpny_id" class="w-full rounded-md border px-3 py-2" required>
                            <option value="">Select Company</option>
                            @foreach ($company as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Department</label>             
                        <select name="department_id" id="edit_department_id" class="w-full border px-3 py-2" required>
                            <option value="">Select Department</option>
                            @foreach ($userdept as $p)
                                <option value="{{ $p->department_id }}">{{ $p->department_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Requester</label>
                        <input type="text" name="user_peminta" id="edit_user_peminta"
                            class="w-full rounded-md border px-3 py-2" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Date Used</label>
                        <input type="date" name="date_used" id="edit_date_used"
                            class="w-full rounded-md border px-3 py-2" required>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-5">
                    <div>
                        <label class="mb-1 block text-sm font-semibold">To</label>
                        <input type="text" name="to" id="edit_to"
                            class="w-full rounded-md border px-3 py-2" required>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-semibold">Purpose</label>
                        <input type="text" name="perpose" id="edit_perpose"
                            class="w-full rounded-md border px-3 py-2" required>
                    </div>
                </div>
                <div class="mt-5 grid grid-cols-1 gap-5 md:grid-cols-2">
    
                {{-- Company Expense --}}
                <div>
                    <label class="mb-1 block text-sm font-semibold">Company Expense</label>
                    <select name="cpny_id_expense" id="edit_cpny_id_expense"
                        class="w-full rounded-md border px-3 py-2" required>
                        <option value="">Select Company</option>
                        @foreach ($company as $c)
                            <option value="{{ $c->cpny_id }}">
                                {{ $c->cpny_id }} - {{ $c->cpny_name ?? '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Topup --}}            
                <div>
                    <label class="mb-1 block text-sm font-semibold">Topup <span class="text-red-500">*</span></label>
                    <select name="user_topup" id="edit_user_topup"class="w-full border px-3 py-2" required>
                        <option value="">Select Topup</option>
                        @foreach ($requesters as $p)
                            <option value="{{ $p->username }}">{{ $p->username }}</option>
                        @endforeach
                    </select>
                </div>

            </div>

                <div class="mt-6 flex justify-between">
                    <button type="button" id="cancelEditVoucherTaxiBtn"
                        class="rounded-lg bg-gray-300 px-4 py-2 text-gray-700 hover:bg-gray-400">
                        Cancel
                    </button>

                    <button type="submit" id="saveEditVoucherTaxiBtn"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">
                        Submit Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="loadingSpinnerContainer" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40">
        <div class="rounded-lg bg-white px-8 py-6 text-center shadow-lg">
            <div class="mx-auto mb-3 h-10 w-10 animate-spin rounded-full border-4 border-gray-300 border-t-blue-600"></div>
            <div class="font-semibold text-gray-700">Processing...</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function showOverlay() {
            $('#loadingSpinnerContainer').removeClass('hidden').addClass('flex');
        }

        function hideOverlay() {
            $('#loadingSpinnerContainer').removeClass('flex').addClass('hidden');
        }

        $(document).on('click', '#openCreateVoucherModal', function() {
            $('#voucherTaxiForm')[0].reset();
            $('#createVoucherModal').removeClass('hidden');
        });

        $(document).on('click', '#closeCreateVoucherModal', function() {
            $('#createVoucherModal').addClass('hidden');
        });

        $('#voucherTaxiForm').on('submit', function(e) {
            e.preventDefault();

            $('#submitVoucherBtn').prop('disabled', true).text('Processing...');
            showOverlay();

            $.ajax({
                url: "{{ route('vouchertaxi.store') }}",
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    hideOverlay();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: res.message || 'Voucher Taxi berhasil dibuat.',
                        timer: 1800,
                        showConfirmButton: false
                    });

                    $('#createVoucherModal').addClass('hidden');
                    $('#voucherTaxiForm')[0].reset();

                    if ($.fn.DataTable.isDataTable('#voucherTaxiTable')) {
                        $('#voucherTaxiTable').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    hideOverlay();

                    let msg = 'Terjadi kesalahan saat menyimpan data.';

                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = '';
                        Object.keys(xhr.responseJSON.errors).forEach(function(key) {
                            msg += xhr.responseJSON.errors[key].join('<br>') + '<br>';
                        });
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.error) {
                        msg = xhr.responseJSON.error;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        html: msg
                    });
                },
                complete: function() {
                    $('#submitVoucherBtn').prop('disabled', false).text('Submit Approval');
                }
            });
        });
    </script>

    <script>
        var currentUser = "{{ auth()->user()->username }}";

        $(document).ready(function() {
            let statusFilter = '';

            const table = $('#voucherTaxiTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,

                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_VoucherTaxi',
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
                        title: 'List_VoucherTaxi',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
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

                ajax: {
                    url: "{{ route('vouchertaxi.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                    }
                },

                order: [
                    [1, 'desc']
                ],

                columns: [
                    {
                        data: null,
                        defaultContent: ''
                    },
                
                    {
                        data: 'docid',
                        render: function(data, type, row) {
                            const text = data || '-';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <button type="button"
                                            class="btn-edit-voucher inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700"
                                            data-docid="${row.docid || ''}"
                                            data-cpny="${row.cpny_id || ''}"
                                            data-dept="${row.department_id || ''}"
                                            data-userpeminta="${row.user_peminta || ''}"
                                            data-dateused="${row.date_used || ''}"
                                            data-to="${row.to || ''}"
                                            data-perpose="${row.perpose || ''}"
                                            data-expense="${row.cpny_id_expense || ''}"
                                            data-topup="${row.user_topup || ''}"
                                        >
                                            ${text}
                                        </button>
                                    </div>
                                `;
                            }

                            return `
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="/showvouchertaxi/${row.eid}"
                                        class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-gray-600 hover:bg-gray-700">
                                        ${text}
                                    </a>
                                </div>
                            `;
                        }
                    },
                    {
                        data: 'vaucher_date',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'date_used',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        defaultContent: '-',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'department_id',
                        defaultContent: '-',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'user_peminta',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'to',
                        defaultContent: '-',
                        className: 'text-left whitespace-normal break-words'
                    },
                    {
                        data: 'perpose',
                        defaultContent: '-',
                        className: 'text-left whitespace-normal break-words'
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            const map = {
                                'D': {
                                    t: 'Revise / Draft',
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
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                },
                            };

                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                            };

                            return `<span class="w-36 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    }
                ],

                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                statusFilter = $(this).data('status') || '';
                table.ajax.reload(null, true);
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
    <script>
        $(document).on('click', '.btn-edit-voucher', function() {
            const btn = $(this);

            const docid   = (btn.attr('data-docid') || '').trim();
            const cpny    = (btn.attr('data-cpny') || '').trim();
            const dept    = (btn.attr('data-dept') || '').trim();
            const user    = (btn.attr('data-userpeminta') || '').trim();
            const date    = (btn.attr('data-dateused') || '').substring(0, 10);
            const to      = btn.attr('data-to') || '';
            const perpose = btn.attr('data-perpose') || '';
            const trip    = (btn.attr('data-trip') || '').trim();
            const expense = (btn.attr('data-expense') || '').trim();
            const topup   = (btn.attr('data-topup') || '').trim();

            $('#edit_docid').val(docid);
            $('#edit_cpny_id').val(cpny);
            $('#edit_department_id').val(dept);
            $('#edit_user_peminta').val(user);
            $('#edit_date_used').val(date);
            $('#edit_to').val(to);
            $('#edit_perpose').val(perpose);
            $('#edit_cpny_id_expense').val(expense);
            $('#edit_user_topup').val(topup);

            if (trip === 'Return') {
                $('#edit_type_trip_return').prop('checked', true);
            } else if (trip === 'One Way') {
                $('#edit_type_trip_oneway').prop('checked', true);
            }

            $('#editVoucherTaxiModal').removeClass('hidden');
        });

        $(document).on('click', '#cancelEditVoucherTaxiBtn', function() {
            $('#editVoucherTaxiModal').addClass('hidden');
        });

        $('#editVoucherTaxiForm').on('submit', function(e) {
            e.preventDefault();

            const docid = $('#edit_docid').val();

            $('#saveEditVoucherTaxiBtn').prop('disabled', true).text('Saving...');
            showOverlay('Saving');

            $.ajax({
                url: `/vouchertaxi/${docid}/update`,
                type: "POST",
                data: $(this).serialize(),
                success: function(res) {
                    toastr.success(res.message || 'Voucher Taxi berhasil diupdate.');
                    $('#editVoucherTaxiModal').addClass('hidden');
                    $('#voucherTaxiTable').DataTable().ajax.reload(null, false);
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal update Voucher Taxi.');
                },
                complete: function() {
                    $('#saveEditVoucherTaxiBtn').prop('disabled', false).text('Save');
                    hideOverlay();
                }
            });
        });
    </script>
</x-app-layout>
