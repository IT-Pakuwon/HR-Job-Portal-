<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'stockjobs' ? 'Stock Jobs' : '';
    @endphp

    <style>
        .status-filter.active .status-card { transform: scale(1.02); }

        .status-filter[data-filter="all"].active .status-card { background-color: rgb(254 215 170); border-color: rgb(194 65 12); }
        .status-filter[data-filter="jobs"].active .status-card { background-color: rgb(191 219 254); border-color: rgb(29 78 216); }
        .status-filter[data-filter="done"].active .status-card { background-color: rgb(187 247 208); border-color: rgb(21 128 61); }
        .status-filter[data-filter="inv"].active .status-card { background-color: rgb(224 231 255); border-color: rgb(67 56 202); }

        /* switch */
        .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position:absolute; cursor:pointer; inset:0; background:#ccc; transition:.4s; border-radius:34px; }
        .slider:before { position:absolute; content:""; height:16px; width:16px; left:3px; bottom:3px; background:white; transition:.4s; border-radius:50%; }
        input:checked + .slider { background:#4CAF50; }
        input:checked + .slider:before { transform: translateX(18px); }

        table.dataTable { width: 100% !important; }
        .dataTables_wrapper { width: 100%; }

        #stockJobsTable_filter, #inventoryTable_filter, #pickInventoryTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }
        #stockJobsTable_filter input, #inventoryTable_filter input, #pickInventoryTable_filter input {
            width: auto;
            min-width: 120px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #stockJobsTable td, #inventoryTable td, #pickInventoryTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        #stockJobsTable th, #stockJobsTable td,
        #inventoryTable th, #inventoryTable td,
        #pickInventoryTable th, #pickInventoryTable td {
            padding: 10px;
            max-width: 360px;
        }

        #stockJobsTable tbody tr:hover,
        #inventoryTable tbody tr:hover,
        #pickInventoryTable tbody tr:hover {
            background-color: #8f8f8f11;
            cursor: pointer;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full active" data-filter="all">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">All</p>
                            <p class="text-xs text-orange-600/80">Completed STOCK</p>
                        </div>
                        <p class="shrink-0 text-xl font-bold">{{ number_format($stockJobs + $stockDone) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="jobs">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🧾</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Stock Jobs</p>
                        </div>
                        <p class="shrink-0 text-xl font-bold">{{ number_format($stockJobs) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="done">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Stock Done</p>
                        </div>
                        <p class="shrink-0 text-xl font-bold">{{ number_format($stockDone) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="inv">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📦</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">Inventory Stock</p>
                            <p class="text-xs text-indigo-600/80">MsInventory GI / A</p>
                        </div>
                        <p class="shrink-0 text-xl font-bold">{{ number_format($inventoryStock) }}</p>
                    </div>
                </a>
            </button>
        </div>

        {{-- TABLES --}}
        <div class="grid mt-6 gap-6">

            {{-- JOBS WRAP --}}
            <div id="jobsWrap" class="rounded-2xl bg-white dark:bg-gray-800">
                <div class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 id="jobsTitle" class="text-xl font-extrabold text-gray-700 dark:text-white">Stock Jobs</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="stockJobsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">IRID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Date</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Company</th>
                                <th class="w-40 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Description</th>
                                <th class="w-52 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory ID</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Created By</th>
                                <th class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Job Status</th>
                                
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            {{-- INVENTORY WRAP --}}
            <div id="invWrap" class="hidden rounded-2xl bg-white dark:bg-gray-800">
                <div class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">📦 Inventory List (GI)</h1>

                    <button id="addInventoryBtn"
                            class="rounded-xl bg-indigo-500 px-5 py-2 text-base font-semibold text-white transition-colors hover:bg-indigo-600">
                        + Add Inventory
                    </button>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="inventoryTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Actions</th>
                                <th class="w-44 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory Description</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Sub Type</th>
                                <th class="w-40 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Class</th>
                                <th class="w-44 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Sub Class</th>
                                <th class="w-28 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Stock Unit</th>
                                <th class="w-28 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- INVENTORY CRUD MODAL --}}
        <div id="inventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-2xl bg-white p-6 shadow-lg dark:bg-gray-800">
                <h2 id="inventoryModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Inventory</h2>

                <form id="inventoryForm">
                    <input type="hidden" id="inv_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Inventory ID</label>
                            <input type="text" id="inventoryid" name="inventoryid"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Stock Unit</label>
                            <select id="stock_unit" name="stock_unit" class="select2 w-full rounded-lg border px-3 py-2">
                                <option value="">-- Select --</option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uomid }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="inventory_descr" name="inventory_descr"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Purchase Unit</label>
                            <select id="purchase_unit" name="purchase_unit" class="select2 w-full rounded-lg border px-3 py-2">
                                <option value="">-- Select --</option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uomid }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Type</label>
                            <input type="text" id="item_type" name="item_type" value="GI"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" readonly>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Type</label>
                            <input type="text" id="item_sub_type" name="item_sub_type"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Class</label>
                            <input type="text" id="item_class" name="item_class"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Class</label>
                            <input type="text" id="item_sub_class" name="item_sub_class"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Category</label>
                            <input type="text" id="item_category" name="item_category"
                                   class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="closeInventoryModal"
                                class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit"
                                class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PICK INVENTORY MODAL (kaca pembesar) --}}
        <div id="pickInventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-4xl rounded-2xl bg-white p-6 shadow-lg dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                        🔎 Pilih Inventory (MsInventory)
                    </h2>
                    <button type="button" id="closePickInventoryModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Close</button>
                </div>

                <div class="text-sm text-gray-500 dark:text-gray-300 mb-3">
                    IRID: <span id="pick_irid" class="font-semibold text-gray-700 dark:text-white">-</span>
                </div>

                <div class="overflow-x-auto">
                    <table id="pickInventoryTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-52 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory ID</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">Inventory Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- DataTables --}}
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

        {{-- select2 (kalau belum di layout) --}}
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function () {
                let jobsFilter = 'all';

                const $jobsWrap  = $('#jobsWrap');
                const $invWrap   = $('#invWrap');
                const $jobsTitle = $('#jobsTitle');

                function setActive(el) {
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    el.classList.add('active');
                }

                // init select2 (CRUD modal)
                function initSelect2InModal() {
                    $('#inventoryModal .select2').select2({
                        dropdownParent: $('#inventoryModal'),
                        width: '100%'
                    });
                }
                initSelect2InModal();

                // JOBS TABLE
                const jobsTable = $('#stockJobsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, 250],
                    ajax: {
                        url: "{{ route('stockjobs.json') }}",
                        type: "GET",
                        data: function (d) {
                            d.source = 'jobs';
                            d.filter = jobsFilter ?? 'all';
                        }
                    },
                    order: [[1, 'desc']],
                    columns: [
                        {
                            data: 'irid',
                            render: function (data, type, row) {
                                const text = data || '-';
                                const url  = `/showitemreq/${row.eid}`;
                                return `<a href="${url}" class="inline-flex w-[160px] justify-center rounded bg-gray-500 px-3 py-1.5 text-base font-semibold text-white hover:bg-gray-700">${text}</a>`;
                            }
                        },
                        { data: 'irdate' },
                        { data: 'cpny_id', className: 'text-center' },
                        { data: 'department_id', className: 'text-center whitespace-normal break-words' },
                        { data: 'inventory_descr_req', defaultContent: '-' },

                        // Inventory ID + Kaca pembesar
                        {
                            data: 'inventoryid',
                            className: 'text-left',
                            render: function (data, type, row) {
                                const inv = data ? `<span class="font-semibold">${data}</span>` : `<span class="text-gray-400"></span>`;

                                // tombol muncul hanya kalau belum done (inventoryid kosong)
                                if (!data) {
                                    return `
                                        <div class="flex items-center gap-2">
                                            ${inv}
                                            <button type="button"
                                                class="btnPickInventory inline-flex items-center justify-center rounded bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700"
                                                data-trid="${row.trid}"
                                                data-irid="${row.irid}">
                                                🔍
                                            </button>
                                        </div>
                                    `;
                                }

                                return `
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">${data}</span>

                                        <button type="button"
                                            class="btnRollback inline-flex items-center justify-center rounded-full p-2 text-orange-600 hover:bg-orange-50 hover:text-orange-700"
                                            data-eid="${row.eid}"
                                            title="Rollback (hapus Inventory ID)">
                                            <i class="fa-solid fa-rotate-left"></i>
                                        </button>
                                    </div>
                                `;
                                return inv;
                            }
                        },
                        { data: 'created_by', defaultContent: '-' },
                        {
                            data: 'is_done',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function (v) {
                                return v
                                    ? `<span class="inline-block w-28 rounded bg-green-300/30 px-3 py-1.5 text-base font-semibold text-green-600">DONE</span>`
                                    : `<span class="inline-block w-28 rounded bg-blue-300/30 px-3 py-1.5 text-base font-semibold text-blue-600">JOB</span>`;
                            }
                        },
                        
                    ],
                });

                // INVENTORY TABLE (CRUD list)
                const invTable = $('#inventoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, 250],
                    ajax: {
                        url: "{{ route('stockjobs.json') }}",
                        type: "GET",
                        data: function (d) {
                            d.source = 'inventory';
                        }
                    },
                    order: [[1, 'asc']],
                    columns: [
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function (data, type, row) {
                                const checked = (row.status === 'A') ? 'checked' : '';
                                return `
                                    <div class="flex items-center justify-center gap-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleInvStatus" data-id="${row.id}" ${checked}>
                                            <span class="slider"></span>
                                        </label>
                                        <button class="editInventoryBtn rounded bg-blue-600 px-2 py-1 text-white hover:bg-blue-700" data-id="${row.id}">
                                            ✏️
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        { data: 'inventoryid' },
                        { data: 'inventory_descr', defaultContent: '-' },
                        { data: 'item_sub_type', defaultContent: '-' },
                        { data: 'item_class', defaultContent: '-' },
                        { data: 'item_sub_class', defaultContent: '-' },
                        { data: 'stock_unit', defaultContent: '-' },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function (s) {
                                return s === 'A'
                                    ? '<span class="inline-block w-24 rounded bg-green-300/30 px-3 py-1.5 text-base font-semibold text-green-600">Active</span>'
                                    : '<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-base font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ],
                });

                // PICK INVENTORY TABLE (modal kaca pembesar)
                const pickInvTable = $('#pickInventoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
                    lengthMenu: [10, 25, 50],
                    ajax: {
                        url: "{{ route('stockjobs.inventory-pick.json') }}",
                        type: "GET",
                    },
                    order: [[0, 'asc']],
                    columns: [
                        { data: 'inventoryid' },
                        { data: 'inventory_descr', defaultContent: '-' },
                    ],
                });

                // default show jobs
                $invWrap.addClass('hidden');
                $jobsWrap.removeClass('hidden');

                // CARD CLICK
                $('.status-filter').on('click', function (e) {
                    e.preventDefault();

                    const f = $(this).data('filter') || 'all';
                    setActive(this);

                    // ===== INVENTORY VIEW =====
                    if (f === 'inv') {
                        $jobsWrap.addClass('hidden');
                        $invWrap.removeClass('hidden');

                        // FULL refresh inventory table
                        invTable
                            .search('')
                            .order([[1, 'asc']])
                            .page(0)
                            .draw(false);

                        invTable.ajax.reload(null, true);
                        return;
                    }

                    // ===== JOBS VIEW =====
                    $invWrap.addClass('hidden');
                    $jobsWrap.removeClass('hidden');

                    jobsFilter = f;

                    const titleMap = {
                        all:  'Stock Jobs (All)',
                        jobs: 'Stock Jobs',
                        done: 'Stock Done'
                    };
                    $jobsTitle.text(titleMap[jobsFilter] ?? 'Stock Jobs');

                    // FULL refresh jobs table
                    jobsTable
                        .search('')
                        .order([[1, 'desc']])
                        .page(0)
                        .draw(false);

                    jobsTable.ajax.reload(null, true);
                });


                // ===== CRUD MODAL helpers
                function openInvModal() {
                    $('#inventoryModal').removeClass('hidden').addClass('flex');
                    initSelect2InModal();
                }
                function closeInvModal() {
                    $('#inventoryModal').addClass('hidden').removeClass('flex');
                }

                // Add
                $('#addInventoryBtn').on('click', function () {
                    $('#inventoryModalTitle').text('Add Inventory');
                    $('#inventoryForm')[0].reset();
                    $('#inv_id').val('');
                    $('#item_type').val('GI');
                    $('#stock_unit').val('').trigger('change');
                    $('#purchase_unit').val('').trigger('change');
                    openInvModal();
                });

                $('#closeInventoryModal').on('click', function () {
                    closeInvModal();
                });

                // Edit
                $(document).on('click', '.editInventoryBtn', function () {
                    const id = $(this).data('id');

                    $.get(`/invstock/${id}/edit`, function (i) {
                        $('#inventoryModalTitle').text('Edit Inventory');
                        $('#inv_id').val(i.id);

                        $('#inventoryid').val(i.inventoryid);
                        $('#inventory_descr').val(i.inventory_descr);

                        $('#item_type').val(i.item_type ?? 'GI');
                        $('#item_sub_type').val(i.item_sub_type);
                        $('#item_class').val(i.item_class);
                        $('#item_sub_class').val(i.item_sub_class);
                        $('#item_category').val(i.item_category);

                        $('#stock_unit').val(i.stock_unit).trigger('change');
                        $('#purchase_unit').val(i.purchase_unit).trigger('change');

                        openInvModal();
                    }).fail(function (xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal load data inventory');
                    });
                });

                // Toggle status
                $(document).on('change', '.toggleInvStatus', function () {
                    const id = $(this).data('id');
                    const newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/invstock/${id}/toggle-status`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { status: newStatus },
                        success: function () {
                            invTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal update status inventory');
                            invTable.ajax.reload(null, false);
                        }
                    });
                });

                // Submit create/update
                $('#inventoryForm').on('submit', function (e) {
                    e.preventDefault();

                    const id = $('#inv_id').val();
                    const url = id ? `/invstock/${id}` : "{{ route('invstock.store') }}";

                    const formData = new FormData(document.getElementById('inventoryForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function () {
                            closeInvModal();
                            invTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan data inventory');
                        }
                    });
                });

                // ===== PICK INVENTORY MODAL helpers
                let pickedTrid = null;
                let pickedIrid = null;

                function openPickInvModal() {
                    $('#pickInventoryModal').removeClass('hidden').addClass('flex');
                    pickInvTable.ajax.reload(null, true);
                }
                function closePickInvModal() {
                    $('#pickInventoryModal').addClass('hidden').removeClass('flex');
                    pickedTrid = null;
                    pickedIrid = null;
                    $('#pick_irid').text('-');
                }

                $('#closePickInventoryModal').on('click', function () {
                    closePickInvModal();
                });

                // Click kaca pembesar
                $(document).on('click', '.btnPickInventory', function () {
                    pickedTrid = $(this).data('trid');
                    pickedIrid = $(this).data('irid');
                    $('#pick_irid').text(pickedIrid || '-');
                    openPickInvModal();
                });

                // Klik row inventory untuk pilih
                $('#pickInventoryTable tbody').on('click', 'tr', function () {
                    const row = pickInvTable.row(this).data();
                    if (!row || !row.inventoryid) return;

                    if (!pickedTrid) {
                        alert('TRID kosong, tidak bisa update.');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('stockjobs.set-inventory') }}",
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: {
                            trid: pickedTrid,
                            inventoryid: row.inventoryid
                        },
                        success: function () {
                            closePickInvModal();
                            jobsTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal update inventory ke item request');
                        }
                    });
                });
            });
        </script>
        <Script>
            // ROLLBACK inventoryid -> NULL
            $(document).on('click', '.btnRollback', function () {
                const eid = $(this).data('eid');
                if (!eid) return;

                if (!confirm('Rollback? Inventory ID akan dikosongkan dan item balik ke Stock Jobs.')) return;

                $.ajax({
                    url: `{{ url('/stockjobs') }}/${eid}/rollback`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function () {
                        // refresh counts biasanya dari server page, tapi minimal refresh table:
                        jobsTable.ajax.reload(null, true);

                        // kalau sedang lihat inventory tab, biar juga refresh
                        if (!$('#invWrap').hasClass('hidden')) {
                            invTable.ajax.reload(null, false);
                        }
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal rollback inventory id');
                    }
                });
            });

        </Script>
    </div>
</x-app-layout>
