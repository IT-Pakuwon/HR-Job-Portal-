<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'tops' ? 'TOP' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">
        <!-- TOP: HEADER TABLE -->
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">💳 Terms Of Payment (TOP)</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-300">Klik TOP untuk menampilkan detail di bawah.</p>
                </div>
                <button id="addTopBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add TOP
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="topTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="w-28 px-3 py-3 text-center">Actions</th>
                            <th class="px-3 py-3 text-left">TOP ID</th>
                            <th class="px-3 py-3 text-left">TOP Name</th>
                            <th class="px-3 py-3 text-left">Type</th>
                            <th class="px-3 py-3 text-left">Days</th>
                            <th class="w-24 px-3 py-3 text-center">RFCA</th>
                            <th class="w-24 px-3 py-3 text-center">Fast</th>
                            <th class="w-28 px-3 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- BOTTOM: DETAIL TABLE -->
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">🧾 TOP Detail</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-300">
                        Selected TOP: <span id="selectedTopText" class="font-semibold">-</span>
                    </p>
                </div>
                <button id="addTopDetailBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Detail
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="topDetailTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="w-28 px-3 py-3 text-center">Actions</th>
                            <th class="px-3 py-3 text-left">Terms ID</th>
                            <th class="px-3 py-3 text-left">Terms Name</th>
                            <th class="px-3 py-3 text-left">Order</th>
                            <th class="px-3 py-3 text-left">Pay %</th>
                            <th class="px-3 py-3 text-left">Prog %</th>
                            <th class="px-3 py-3 text-left">Type</th>
                            <th class="w-24 px-3 py-3 text-center">BAST</th>
                            <th class="w-28 px-3 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

        </div>

        <!-- TOP MODAL -->
        <div id="topModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-2xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="topModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add TOP</h2>
                <form id="topForm">
                    <input type="hidden" id="top_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">TOP ID</label>
                            <input type="text" id="topid" name="topid"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">TOP Type</label>
                            <input type="text" id="top_type" name="top_type"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">TOP Name</label>
                            <input type="text" id="top_name" name="top_name"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">TOP Days</label>
                            <input type="number" min="0" id="top_days" name="top_days"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="flex items-end gap-6">
                            <label class="flex items-center gap-2 text-gray-700 dark:text-white">
                                <input type="checkbox" id="is_rfca" name="is_rfca" value="1">
                                <span>Is RFCA</span>
                            </label>
                            <label class="flex items-center gap-2 text-gray-700 dark:text-white">
                                <input type="checkbox" id="is_fastapprove" name="is_fastapprove" value="1">
                                <span>Fast Approve</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeTopModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- TOP DETAIL MODAL -->
        <div id="topDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-3xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="topDetailModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add TOP
                    Detail
                </h2>

                <form id="topDetailForm">
                    <input type="hidden" id="td_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div>
                            <label class="block text-gray-700 dark:text-white">TOP ID</label>
                            <input type="text" id="td_topid" name="topid"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">TOP Type</label>
                            <input type="text" id="td_top_type" name="top_type"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Terms ID</label>
                            <input type="text" id="terms_id" name="terms_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Terms Name</label>
                            <input type="text" id="terms_name" name="terms_name"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Order Term</label>
                            <input type="number" min="1" id="order_term" name="order_term"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Payment %</label>
                            <input type="number" step="0.01" min="0" max="100" id="payment_pct"
                                name="payment_pct" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Progress %</label>
                            <input type="number" step="0.01" min="0" max="100" id="progress_pct"
                                name="progress_pct" class="rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Terms Type</label>
                            <input type="text" id="terms_type" name="terms_type"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div class="flex items-end">
                            <label class="flex items-center gap-2 text-gray-700 dark:text-white">
                                <input type="checkbox" id="flag_bast" name="flag_bast" value="1">
                                <span>Flag BAST</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeTopDetailModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                let selectedTopId = null;
                let selectedTopType = null;
                let selectedTopName = null;

                // TOP TABLE
                let topTable = $('#topTable').DataTable({
                    ajax: "{{ route('tops.json') }}",
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'User',
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
                            title: 'User',
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
                            target: 0 // 👈 this is REQUIRED
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        className: 'dtr-control',
                        orderable: false
                    }],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, // DTR control
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleTopStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editTopBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'topid'
                        },
                        {
                            data: 'top_name'
                        },
                        {
                            data: 'top_type'
                        },
                        {
                            data: 'top_days'
                        },
                        {
                            data: 'is_rfca',
                            render: d => d ? '✅' : '—'
                        },
                        {
                            data: 'is_fastapprove',
                            render: d => d ? '✅' : '—'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(d) {
                                return d === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // TOP DETAIL TABLE
                let detailTable = $('#topDetailTable').DataTable({
                    ajax: {
                        url: "{{ route('top_details.json') }}",
                        data: function(d) {
                            d.topid = selectedTopId;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'User',
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
                            title: 'User',
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
                            target: 0 // 👈 this is REQUIRED
                        }
                    },

                    columnDefs: [{
                        targets: 0,
                        className: 'dtr-control',
                        orderable: false
                    }],
                    columns: [{
                            data: null,
                            defaultContent: ''
                        }, // DTR control
                        {
                            data: 'id',
                            render: function(data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleDetailStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editDetailBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'terms_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'terms_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'order_term',
                            className: 'no-pointer'
                        },
                        {
                            data: 'payment_pct',
                            className: 'no-pointer'
                        },
                        {
                            data: 'progress_pct',
                            className: 'no-pointer',
                            render: d => d ?? '—'
                        },
                        {
                            data: 'terms_type',
                            className: 'no-pointer',
                            render: d => d ?? '—'
                        },
                        {
                            data: 'flag_bast',
                            className: 'no-pointer',
                            render: d => d ? '✅' : '—'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(d) {
                                return d === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // CLICK TOP ROW -> FILTER DETAILS
                $('#topTable tbody').on('click', 'tr', function() {
                    let row = topTable.row(this).data();
                    if (!row) return;

                    $('#topTable tbody tr').removeClass('row-active');
                    $(this).addClass('row-active');

                    selectedTopId = row.topid;
                    selectedTopType = row.top_type;
                    selectedTopName = row.top_name;

                    $('#selectedTopText').text(`${selectedTopId} - ${selectedTopName}`);
                    detailTable.ajax.reload();
                });

                /* ========== TOP MODAL ========== */
                $('#addTopBtn').click(function() {
                    $('#topModalTitle').text('Add TOP');
                    $('#topForm')[0].reset();
                    $('#top_id').val('');
                    $('#topModal').removeClass('hidden').addClass('flex');
                });

                $('#closeTopModal').click(function() {
                    $('#topModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editTopBtn', function() {
                    let id = $(this).data('id');
                    $.get(`/tops/${id}/edit`, function(d) {
                        $('#topModalTitle').text('Edit TOP');
                        $('#top_id').val(d.id);
                        $('#topid').val(d.topid);
                        $('#top_name').val(d.top_name);
                        $('#top_type').val(d.top_type);
                        $('#top_days').val(d.top_days);
                        $('#is_rfca').prop('checked', !!d.is_rfca);
                        $('#is_fastapprove').prop('checked', !!d.is_fastapprove);
                        $('#topModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#topForm').submit(function(e) {
                    e.preventDefault();
                    let id = $('#top_id').val();
                    let url = id ? `/tops/${id}` : "{{ route('tops.store') }}";
                    let formData = new FormData(document.getElementById('topForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            $('#topModal').addClass('hidden').removeClass('flex');
                            topTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan TOP');
                        }
                    });
                });

                $(document).on('change', '.toggleTopStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/tops/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            topTable.ajax.reload(null, false);
                        }
                    });
                });

                /* ========== TOP DETAIL MODAL ========== */
                $('#addTopDetailBtn').click(function() {
                    if (!selectedTopId) {
                        alert('Pilih TOP dulu di tabel atas.');
                        return;
                    }

                    $('#topDetailModalTitle').text('Add TOP Detail');
                    $('#topDetailForm')[0].reset();
                    $('#td_id').val('');

                    // autofill
                    $('#td_topid').val(selectedTopId);
                    $('#td_top_type').val(selectedTopType);

                    $('#topDetailModal').removeClass('hidden').addClass('flex');
                });

                $('#closeTopDetailModal').click(function() {
                    $('#topDetailModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editDetailBtn', function() {
                    let id = $(this).data('id');
                    $.get(`/top-details/${id}/edit`, function(d) {
                        $('#topDetailModalTitle').text('Edit TOP Detail');
                        $('#td_id').val(d.id);
                        $('#td_topid').val(d.topid);
                        $('#td_top_type').val(d.top_type);
                        $('#terms_id').val(d.terms_id);
                        $('#terms_name').val(d.terms_name);
                        $('#order_term').val(d.order_term);
                        $('#payment_pct').val(d.payment_pct);
                        $('#progress_pct').val(d.progress_pct);
                        $('#terms_type').val(d.terms_type);
                        $('#flag_bast').prop('checked', !!d.flag_bast);

                        $('#topDetailModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#topDetailForm').submit(function(e) {
                    e.preventDefault();
                    let id = $('#td_id').val();
                    let url = id ? `/top-details/${id}` : "{{ route('top_details.store') }}";
                    let formData = new FormData(document.getElementById('topDetailForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            $('#topDetailModal').addClass('hidden').removeClass('flex');
                            detailTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan TOP Detail');
                        }
                    });
                });

                $(document).on('change', '.toggleDetailStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/top-details/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            detailTable.ajax.reload(null, false);
                        }
                    });
                });
            });
        </script>
    </div>
</x-app-layout>
