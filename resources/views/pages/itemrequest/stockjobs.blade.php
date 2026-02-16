<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'stockjobs' ? 'Stock Jobs' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="all">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($stockJobs + $stockDone) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter active group block h-full" data-filter="jobs">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🧾</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Stock Jobs</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($stockJobs) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="done">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Stock Done</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($stockDone) }}</p>
                    </div>
                </a>
            </button>

            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-filter="inv">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Inventory Stock</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($inventoryStock) }}</p>
                    </div>
                </a>
            </button>
        </div>

        {{-- TABLES --}}
        <div class="mt-6 flex flex-col rounded-xl bg-white p-4 dark:bg-gray-800">

            {{-- JOBS WRAP --}}
            <div id="jobsWrap" class="flex flex-col gap-6 rounded-xl bg-white dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h1 id="jobsTitle" class="text-base font-extrabold text-gray-700 dark:text-white">Stock Jobs</h1>
                </div>

                <div class="rounded-base relative overflow-x-auto">
                    <table id="stockJobsTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th></th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    IRID</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Date</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Company</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Department</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Description</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Inventory ID</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Created By</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Job Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- INVENTORY WRAP --}}
            <div id="invWrap" class="hidden rounded-xl bg-white dark:bg-gray-800">
                <div class="mb-4 flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h1 class="text-base font-extrabold text-gray-700 dark:text-white">📦 Inventory List</h1>

                    <button id="addInventoryBtn"
                        class="rounded-xl bg-indigo-500 px-5 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-600">
                        + Add Inventory
                    </button>
                </div>

                <div class="rounded-base relative overflow-x-auto">
                    <table id="inventoryTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th></th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Actions</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Inventory ID</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Inventory Description</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Sub Type</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Class</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Sub Class</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Stock Unit</th>
                                <th class="w-32 px-6 py-2 font-medium">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- INVENTORY CRUD MODAL --}}
        <div id="inventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                <h2 id="inventoryModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add
                    Inventory
                </h2>

                <form id="inventoryForm">
                    @csrf
                    <input type="hidden" id="inv_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        {{-- 1) inventoryid readonly + abu --}}
                        <div id="inventoryIdWrapper">
                            <label class="block text-gray-700 dark:text-white">Inventory ID</label>
                            <input type="text" id="inventoryid" name="inventoryid"
                                class="w-full cursor-not-allowed rounded-lg border bg-gray-100 px-3 py-2 text-gray-700 dark:bg-gray-600 dark:text-gray-200"
                                readonly>
                        </div>


                        {{-- 2) item_type (select) --}}
                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Type</label>
                            <select id="item_type" name="item_type_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select Item Type </option>
                            </select>
                            <input type="hidden" id="item_type_hidden" name="item_type_id">
                        </div>

                        {{-- 3) item_sub_type (select) --}}
                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Type</label>
                            <select id="item_sub_type" name="item_sub_type_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Sub Type </option>
                            </select>
                            <input type="hidden" id="item_sub_type_hidden" name="item_sub_type_id">
                        </div>

                        {{-- 4) item_class (select) --}}
                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Class</label>
                            <select id="item_class" name="item_class_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Class </option>
                            </select>
                            <input type="hidden" id="item_class_hidden" name="item_class_id">
                        </div>

                        {{-- 5) item_sub_class (select) --}}
                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Class</label>
                            <select id="item_sub_class" name="item_sub_class_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Sub Class </option>
                            </select>
                            <input type="hidden" id="item_sub_class_hidden" name="item_sub_class_id">
                        </div>

                        {{-- Description (tetap ada, taruh full row biar enak) --}}
                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="inventory_descr" name="inventory_descr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        {{-- 6) item_category (input) --}}
                        {{-- <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Item Category</label>
                            <input type="text" id="item_category" name="item_category"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div> --}}

                        {{-- 7) stock_unit --}}
                        {{-- <div>
                            <label class="block text-gray-700 dark:text-white">Stock Unit</label>
                            <select id="stock_unit" name="stock_unit"
                                    class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select </option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uom_description }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                            
                        </div> --}}

                        {{-- 8) purchase_unit --}}
                        {{-- <div>
                            <label class="block text-gray-700 dark:text-white">Purchase Unit</label>
                            <select id="purchase_unit" name="purchase_unit"
                                    class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select </option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uom_description }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                            
                        </div> --}}
                        <div id="stockUnitWrap">
                            <label>Stock Unit</label>
                            <select id="stock_unit" name="stock_unit"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select </option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uom_description }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="stock_unit_hidden" name="stock_unit_hidden">
                        </div>

                        <div id="purchaseUnitWrap">
                            <label>Purchase Unit</label>
                            <select id="purchase_unit" name="purchase_unit"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select </option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uom_description }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="purchase_unit_hidden" name="purchase_unit_hidden">
                        </div>


                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button type="button" id="closeInventoryModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>

            </div>
        </div>

        {{-- PICK INVENTORY MODAL (kaca pembesar) --}}
        <div id="pickInventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-4xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">
                        🔎 Pilih Inventory (MsInventory)
                    </h2>
                    <button type="button" id="closePickInventoryModal"
                        class="rounded-lg bg-red-500 px-4 py-2 text-white">Close</button>
                </div>

                <div class="mb-3 text-sm text-gray-500 dark:text-gray-300">
                    IRID: <span id="pick_irid" class="font-semibold text-gray-700 dark:text-white">-</span>
                </div>

                <div class="overflow-x-auto">
                    <table id="pickInventoryTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th
                                    class="w-52 px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Inventory ID</th>
                                <th
                                    class="px-6 py-3 text-left text-sm font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Inventory Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <script>
            window.setAddMode = function() {
                $('#inventoryIdWrapper').addClass('hidden');
                $('#inventoryid').val('');
            };

            window.setEditMode = function() {
                $('#inventoryIdWrapper').removeClass('hidden');
            };

            window.setEditMode = function(isEdit) {

                const map = [
                    // id select, realName request, wrapper selector (optional)
                    ['item_type', 'item_type_id', null],
                    ['item_sub_type', 'item_sub_type_id', null],
                    ['item_class', 'item_class_id', null],
                    ['item_sub_class', 'item_sub_class_id', null],

                    // ✅ tambah unit (realName = name yang backend expect)
                    ['stock_unit', 'stock_unit', '#stockUnitWrap'],
                    ['purchase_unit', 'purchase_unit', '#purchaseUnitWrap'],
                ];

                map.forEach(([id, realName, wrapSel]) => {
                    const $sel = $('#' + id);
                    const $hid = $('#' + id + '_hidden');

                    // wrapper: kalau ada, pakai itu. kalau tidak, fallback closest div
                    const $wrap = wrapSel ? $(wrapSel) : $sel.closest('div');

                    if (isEdit) {
                        // 1) simpan value ke hidden
                        $hid.val($sel.val() || '');

                        // 2) hidden input ambil name asli (biar ikut submit)
                        $hid.attr('name', realName);

                        // 3) select dilepas name + dimatikan required (biar tidak divalidasi)
                        $sel.removeAttr('name');
                        $sel.prop('required', false);

                        // 4) hide
                        $wrap.addClass('hidden');

                    } else {
                        // ADD mode: select aktif kembali
                        $sel.attr('name', realName);
                        $sel.prop('required', true);

                        // show
                        $wrap.removeClass('hidden');

                        // hidden input jangan ikut submit
                        $hid.attr('name', realName + '_hidden');
                        $hid.val('');
                    }
                });

                // ✅ rules edit: hanya inventory_descr boleh diedit
                $('#inventoryid').prop('readonly', true); // tetap readonly
                $('#inventory_descr').prop('readonly', false); // boleh edit
            };


            // ===== GLOBAL HELPERS =====
            window.resetSelect = function($el, placeholder) {
                $el.prop('disabled', true)
                    .empty()
                    .append(new Option(placeholder, '', true, true))
                    .trigger('change');
            };

            window.ddUrl = {
                itemTypes: "{{ route('stockjobs.stock-types') }}",
                subTypes: "{{ route('stockjobs.stock-sub-types') }}",
                classes: "{{ route('stockjobs.stock-classes') }}",
                subClasses: "{{ route('stockjobs.stock-sub-classes') }}"
            };

            // ===== PROMISE LOADERS =====
            window.loadItemTypesPromise = function() {
                console.log('[DDL] GET itemTypes', window.ddUrl.itemTypes);
                return $.get(window.ddUrl.itemTypes).then(function(res) {
                    const $type = $('#item_type');

                    $type.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Item Type --', '', true, true));

                    (res.data || []).forEach(x => $type.append(new Option(x.text, x.id)));

                    // initSelect2InModalSafe(); // ✅ refresh select2 kalau belum
                    $type.trigger('change'); // ✅ cukup change

                    console.log('[DDL] itemTypes loaded count=', (res.data || []).length);
                    return res;
                });
            };

            window.loadSubTypesPromise = function(itemTypeId) {
                return $.get(window.ddUrl.subTypes, {
                    item_type_id: itemTypeId
                }).then(function(res) {
                    const $sub = $('#item_sub_type');
                    $sub.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Sub Type --', '', true, true));

                    (res.data || []).forEach(x => $sub.append(new Option(x.text, x.id)));
                    $sub.trigger('change.select2');
                    return res;
                });
            };

            window.loadClassesPromise = function(subTypeId) {
                return $.get(window.ddUrl.classes, {
                    item_sub_type_id: subTypeId
                }).then(function(res) {
                    const $cls = $('#item_class');
                    $cls.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Class --', '', true, true));

                    (res.data || []).forEach(x => $cls.append(new Option(x.text, x.id)));
                    $cls.trigger('change.select2');
                    return res;
                });
            };

            window.loadSubClassesPromise = function(classId) {
                return $.get(window.ddUrl.subClasses, {
                    item_class_id: classId
                }).then(function(res) {
                    const $subcls = $('#item_sub_class');
                    $subcls.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Sub Class --', '', true, true));

                    (res.data || []).forEach(x => $subcls.append(new Option(x.text, x.id)));
                    $subcls.trigger('change.select2');
                    return res;
                });
            };

            // helper resolved promise
            window.resolvedPromise = function() {
                return $.Deferred().resolve().promise();
            };
        </script>

        <script>
            // === GLOBAL supaya bisa dipakai di handler manapun ===
            let jobsTable = null;
            let invTable = null;
            let pickInvTable = null;

            $(document).ready(function() {
                // ===== STATE =====
                let jobsFilter = 'jobs';

                const $jobsWrap = $('#jobsWrap');
                const $invWrap = $('#invWrap');
                const $jobsTitle = $('#jobsTitle');

                function setActive(el) {
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                    el.classList.add('active');
                }

                // =========================
                // JOBS TABLE
                // =========================
                jobsTable = $('#stockJobsTable').DataTable({
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
                            target: 0 // 👈 this is REQUIRED
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
                            title: 'List_Stock',
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
                            title: 'List_Stock',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    // 🔥 END ADD
                    ajax: {
                        url: "{{ route('stockjobs.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.source = 'jobs';
                            d.filter = jobsFilter || 'all';
                        }
                    },
                    order: [
                        [1, 'desc']
                    ],
                    columns: [{
                            data: null,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            defaultContent: ''
                        },
                        {
                            data: 'irid',
                            render: function(data, type, row) {
                                const text = data || '-';
                                const url = `/showitemreq/${row.eid}`;
                                return `<a href="${url}" class="inline-flex w-[160px] justify-center rounded bg-gray-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700">${text}</a>`;
                            }
                        },
                        {
                            data: 'irdate'
                        },
                        {
                            data: 'cpny_id',
                            className: 'text-center'
                        },
                        {
                            data: 'department_id',
                            className: 'text-center whitespace-normal break-words'
                        },
                        {
                            data: 'inventory_descr_req',
                            defaultContent: '-'
                        },

                        // Inventory ID + Kaca pembesar / Rollback
                        {
                            data: 'inventoryid',
                            className: 'text-left',
                            orderable: false,
                            render: function(data, type, row) {
                                // kalau kosong -> tampilkan tombol pick
                                if (!data) {
                                    return `
                                        <div class="flex items-center gap-2">
                                            <span class="text-gray-400"></span>
                                            <button type="button"
                                                class="btnPickInventory inline-flex items-center justify-center rounded bg-indigo-600 px-2.5 py-1.5  text-sm  font-semibold text-white hover:bg-indigo-700"
                                                data-id="${row.id ?? ''}"
                                                data-trid="${row.trid ?? ''}"
                                                data-irid="${row.irid ?? ''}">
                                                🔍
                                            </button>
                                        </div>
                                    `;
                                }

                                // kalau ada -> tampilkan rollback
                                return `
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold">${data}</span>
                                        <button type="button"
                                            class="btnRollback inline-flex items-center justify-center rounded-full p-2 text-orange-600 hover:bg-orange-50 hover:text-orange-700"
                                            data-eid="${row.eid}"
                                            title="Rollback (hapus Inventory ID)">
                                            <span class="text-sm">↩️</span>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'created_by',
                            defaultContent: '-'
                        },
                        {
                            data: 'is_done',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(v) {
                                return v ?
                                    `<span class="inline-block w-28 rounded bg-green-300/30 px-3 py-1.5 text-sm font-semibold text-green-600">DONE</span>` :
                                    `<span class="inline-block w-28 rounded bg-blue-300/30 px-3 py-1.5 text-sm font-semibold text-blue-600">JOB</span>`;
                            }
                        },


                    ],
                });

                // =========================
                // INVENTORY TABLE (CRUD list)
                // =========================
                invTable = $('#inventoryTable').DataTable({
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
                            target: 0 // 👈 this is REQUIRED
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
                            title: 'List_Inventory',
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
                            title: 'List_Inventory',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    // 🔥 END ADD
                    ajax: {
                        url: "{{ route('stockjobs.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.source = 'inventory';
                        }
                    },
                    order: [
                        [1, 'asc']
                    ],
                    columns: [{
                            data: null,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false,
                            defaultContent: ''
                        },
                        //{
                        {
                            data: 'id',
                            orderable: false,
                            searchable: false,
                            className: 'text-center',
                            render: function(data, type, row) {
                                const checked = (String(row.status || '').toUpperCase() === 'A') ?
                                    'checked' : '';
                                return `
                                    <div class="flex items-center justify-center gap-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleInvStatus" data-id="${row.id}" ${checked}>
                                            <span class="slider"></span>
                                        </label>
                                        <button type="button" class="editInventoryBtn rounded bg-blue-600 px-2 py-1 text-white hover:bg-blue-700" data-id="${row.id}">
                                            ✏️
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        {
                            data: 'inventoryid'
                        },
                        {
                            data: 'inventory_descr',
                            defaultContent: '-'
                        },
                        {
                            data: 'item_sub_type',
                            defaultContent: '-'
                        },
                        {
                            data: 'item_class',
                            defaultContent: '-'
                        },
                        {
                            data: 'item_sub_class',
                            defaultContent: '-'
                        },
                        {
                            data: 'stock_unit',
                            defaultContent: '-'
                        },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function(s) {
                                s = String(s || '').toUpperCase();
                                return s === 'A' ?
                                    '<span class="inline-block w-24 rounded bg-green-300/30 px-3 py-1.5 text-sm font-semibold text-green-600">Active</span>' :
                                    '<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Inactive</span>';
                            }
                        }
                    ],
                });

                // =========================
                // PICK INVENTORY TABLE (modal kaca pembesar)
                // =========================
                pickInvTable = $('#pickInventoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    // responsive: {
                    //     details: {
                    //         type: 'column',
                    //         target: 0 // 👈 this is REQUIRED
                    //     }
                    // },

                    // columnDefs: [{
                    //     targets: 0,
                    //     width: '28px',
                    className: 'dtr-control',
                    //     orderable: false
                    // }],

                    // dom: '<"dt-toolbar"l B f>rtip',
                    // buttons: [{
                    //         extend: 'excelHtml5',
                    //         text: '↓ Excel',
                    //         title: 'list_PickInventory',
                    //         className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                    //         exportOptions: {
                    //             columns: ':visible',
                    //             modifier: {
                    //                 page: 'current'
                    //             }
                    //         }
                    //     },
                    //     {
                    //         extend: 'csvHtml5',
                    //         text: '↓ CSV',
                    //         title: 'List_PickInventory',
                    //         className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                    //         exportOptions: {
                    //             columns: ':visible',
                    //             modifier: {
                    //                 page: 'current'
                    //             }
                    //         }
                    //     }
                    // ],


                    // 🔥 END ADD
                    ajax: {
                        url: "{{ route('stockjobs.inventory-pick.json') }}",
                        type: "GET",
                    },
                    order: [
                        [0, 'asc']
                    ],
                    columns: [{
                            data: 'inventoryid'
                        },
                        {
                            data: 'inventory_descr',
                            defaultContent: '-'
                        },
                    ],
                });

                // default show jobs
                $invWrap.addClass('hidden');
                $jobsWrap.removeClass('hidden');

                // =========================
                // CARD CLICK
                // =========================
                $('.status-filter').on('click', function(e) {
                    e.preventDefault();

                    const f = $(this).data('filter') || 'all';
                    setActive(this);

                    if (f === 'inv') {
                        $jobsWrap.addClass('hidden');
                        $invWrap.removeClass('hidden');

                        invTable.search('').order([
                            [1, 'asc']
                        ]).page(0).draw(false);
                        invTable.ajax.reload(null, true);
                        return;
                    }

                    $invWrap.addClass('hidden');
                    $jobsWrap.removeClass('hidden');

                    jobsFilter = f;

                    const titleMap = {
                        all: 'Stock Jobs (All)',
                        jobs: 'Stock Jobs',
                        done: 'Stock Done'
                    };
                    $jobsTitle.text(titleMap[jobsFilter] ?? 'Stock Jobs');

                    jobsTable.search('').order([
                        [1, 'desc']
                    ]).page(0).draw(false);
                    jobsTable.ajax.reload(null, true);
                });

                // =========================
                // CRUD MODAL helpers
                // =========================

                window.openInvModal = function() {
                    $('#inventoryModal').removeClass('hidden').addClass('flex');
                };

                window.closeInvModal = function() {
                    $('#inventoryModal').addClass('hidden').removeClass('flex');
                };


                $('#addInventoryBtn').on('click', function() {
                    $('#inventoryModalTitle').text('Add Inventory');
                    $('#inventoryForm')[0].reset();
                    $('#inv_id').val('');

                    window.setAddMode(); // ✅ HIDE Inventory ID
                    window.openInvModal();

                    window.loadItemTypesPromise().then(function(res) {
                        const gi = (res.data || []).find(x => String(x.text).toUpperCase() === 'GI');
                        if (gi) $('#item_type').val(gi.id).trigger('change');
                    });
                });



                $('#closeInventoryModal').on('click', function() {
                    closeInvModal();
                });

                // Edit  
                $(document).on('click', '.editInventoryBtn', function() {
                    const id = $(this).data('id');

                    $.get(`/invstock/${id}/edit`, function(i) {
                        $('#inventoryModalTitle').text('Edit Inventory');
                        $('#inventoryForm')[0].reset();

                        // mode edit: lock semua kecuali descr
                        window.setEditMode(true);

                        // isi field biasa
                        $('#inv_id').val(i.id);
                        $('#inventoryid').val(i.inventoryid);

                        // ✅ hanya ini yang editable
                        $('#inventory_descr').val(i.inventory_descr);

                        // tampilkan value dropdown sebagai “display only”
                        // karena disabled, kita harus pastikan option ada
                        function setSelectDisplay($el, valueTextOrId) {
                            if (!valueTextOrId) {
                                $el.empty().append(new Option('-', '', true, true));
                                return;
                            }
                            // buat option dummy supaya tampil
                            $el.empty().append(new Option(valueTextOrId, valueTextOrId, true, true));
                        }

                        setSelectDisplay($('#item_type'), i.item_type ?? '-');
                        setSelectDisplay($('#item_sub_type'), i.item_sub_type ?? '-');
                        setSelectDisplay($('#item_class'), i.item_class ?? '-');
                        setSelectDisplay($('#item_sub_class'), i.item_sub_class ?? '-');

                        // unit tampil
                        setSelectDisplay($('#stock_unit'), i.stock_unit ?? '-');
                        setSelectDisplay($('#purchase_unit'), i.purchase_unit ?? '-');

                        window.openInvModal();

                    }).fail(function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal load data inventory'
                        });
                    });
                });


                // Toggle status
                $(document).on('change', '.toggleInvStatus', function() {
                    const id = $(this).data('id');
                    const newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/invstock/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            invTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Update Status Sukses',
                                timer: 1400,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal update status inventory');
                            invTable.ajax.reload(null, false);
                        }
                    });
                });

                // Submit create/update
                $('#inventoryForm').on('submit', function(e) {
                    e.preventDefault();

                    const id = $('#inv_id').val();
                    const url = id ? `/invstock/${id}` : "{{ route('invstock.store') }}";

                    const formData = new FormData(document.getElementById('inventoryForm'));
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
                            closeInvModal();
                            invTable.ajax.reload(null, false);

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Data inventory berhasil disimpan',
                                timer: 1400,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal menyimpan data inventory'
                            });
                        }

                    });
                });

                // =========================
                // PICK INVENTORY MODAL
                // =========================
                let pickedId = null; // prefer id
                let pickedTrid = null; // fallback if needed
                let pickedIrid = null;

                function openPickInvModal() {
                    $('#pickInventoryModal').removeClass('hidden').addClass('flex');
                    pickInvTable.ajax.reload(null, true);
                }

                function closePickInvModal() {
                    $('#pickInventoryModal').addClass('hidden').removeClass('flex');
                    pickedId = null;
                    pickedTrid = null;
                    pickedIrid = null;
                    $('#pick_irid').text('-');
                }

                $('#closePickInventoryModal').on('click', function() {
                    closePickInvModal();
                });

                // Click kaca pembesar
                $(document).on('click', '.btnPickInventory', function() {
                    pickedId = $(this).data('id') || null;
                    pickedTrid = $(this).data('trid') || null;
                    pickedIrid = $(this).data('irid') || null;

                    $('#pick_irid').text(pickedIrid || '-');
                    openPickInvModal();
                });

                // Klik row inventory untuk pilih
                $('#pickInventoryTable tbody').on('click', 'tr', function() {
                    const row = pickInvTable.row(this).data();
                    if (!row || !row.inventoryid) return;

                    // prefer id
                    if (!pickedId && !pickedTrid) {
                        alert('ID/TRID kosong, tidak bisa update.');
                        return;
                    }

                    $.ajax({
                        url: "{{ route('stockjobs.set-inventory') }}",
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            id: pickedId,
                            trid: pickedTrid,
                            inventoryid: row.inventoryid
                        },
                        success: function() {
                            closePickInvModal();
                            if (jobsTable) jobsTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Stock Jobs Sukses',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal update inventory ke item request');
                        }
                    });
                });


                $(document).on('click', '.btnRollback', function() {
                    const eid = $(this).data('eid');
                    if (!eid) return;

                    Swal.fire({
                        title: 'Rollback?',
                        text: 'Inventory ID akan dikosongkan dan item balik ke Stock Jobs.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, rollback',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) return;

                        $.ajax({
                            url: `/stockjobs/${eid}/rollback`,
                            type: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function() {
                                if (jobsTable) jobsTable.ajax.reload(null, false);

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Rollback sukses',
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            },
                            error: function(xhr) {
                                console.error(xhr.responseText);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: 'Gagal rollback inventory id'
                                });
                            }
                        });
                    });
                });


            });
        </script>

        <script>
            $(document).ready(function() {

                console.log('[INV] script loaded');

                function resetSelect($el, placeholder) {
                    console.log('[INV] resetSelect', $el.attr('id'));
                    $el.prop('disabled', true)
                        .empty()
                        .append(new Option(placeholder, '', true, true))
                        .trigger('change');
                }

                // ====== LOAD: Item Type ======
                function loadItemTypes(defaultValue = null, defaultText = null) {
                    const url = "{{ route('stockjobs.stock-types') }}";
                    console.log('[INV] loadItemTypes() -> GET', url, {
                        defaultValue,
                        defaultText
                    });

                    $('#item_type').prop('disabled', false);

                    $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            console.log('[INV] item-types success:', res);

                            const $type = $('#item_type');
                            $type.empty().append(new Option('-- Select Item Type --', '', true, true));

                            (res.data || []).forEach(x => $type.append(new Option(x.text, x.id)));

                            // ✅ set default setelah option tersedia
                            if (defaultValue) {
                                $type.val(defaultValue).trigger('change');
                                console.log('[INV] set item_type default by VALUE =>', defaultValue);
                                return;
                            }

                            if (defaultText) {
                                // cari option yang text-nya = defaultText (mis: "GI")
                                const match = (res.data || []).find(x => String(x.text).toUpperCase() ===
                                    String(defaultText).toUpperCase());
                                if (match) {
                                    $type.val(match.id).trigger('change');
                                    console.log('[INV] set item_type default by TEXT =>', defaultText,
                                        'id=', match.id);
                                } else {
                                    console.warn('[INV] defaultText not found:', defaultText);
                                    $type.trigger('change');
                                }
                                return;
                            }

                            // kalau tidak set default apapun
                            $type.trigger('change');
                        },
                        error: function(xhr) {
                            console.error('[INV] item-types ERROR', xhr.status, xhr.responseText);
                        }
                    });
                }


                // ====== ONCHANGE Item Type -> load Sub Type ======
                $('#item_type').on('change', function() {
                    const typeId = $(this).val();
                    console.log('[INV] item_type change =>', typeId);

                    resetSelect($('#item_sub_type'), '-- Select Sub Type --');
                    resetSelect($('#item_class'), '-- Select Class --');
                    resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                    if (!typeId) return;

                    const url = "{{ route('stockjobs.stock-sub-types') }}";
                    console.log('[INV] load sub-types -> GET', url, 'params=', {
                        item_type_id: typeId
                    });

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            item_type_id: typeId
                        },
                        dataType: 'json',
                        success: function(res) {
                            console.log('[INV] sub-types success:', res);
                            const $sub = $('#item_sub_type');
                            $sub.prop('disabled', false)
                                .empty()
                                .append(new Option('-- Select Sub Type --', '', true, true));

                            (res?.data || []).forEach(x => $sub.append(new Option(x.text, x.id)));
                            $sub.trigger('change');
                        },
                        error: function(xhr) {
                            console.error('[INV] sub-types ERROR', xhr.status, xhr.responseText);
                        }
                    });
                });

                // ====== ONCHANGE Sub Type -> load Class ======
                $('#item_sub_type').on('change', function() {
                    const subTypeId = $(this).val();
                    console.log('[INV] item_sub_type change =>', subTypeId);

                    resetSelect($('#item_class'), '-- Select Class --');
                    resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                    if (!subTypeId) return;

                    const url = "{{ route('stockjobs.stock-classes') }}";
                    console.log('[INV] load classes -> GET', url, 'params=', {
                        item_sub_type_id: subTypeId
                    });

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            item_sub_type_id: subTypeId
                        },
                        dataType: 'json',
                        success: function(res) {
                            console.log('[INV] classes success:', res);
                            const $cls = $('#item_class');
                            $cls.prop('disabled', false)
                                .empty()
                                .append(new Option('-- Select Class --', '', true, true));

                            (res?.data || []).forEach(x => $cls.append(new Option(x.text, x.id)));
                            $cls.trigger('change');
                        },
                        error: function(xhr) {
                            console.error('[INV] classes ERROR', xhr.status, xhr.responseText);
                        }
                    });
                });

                // ====== ONCHANGE Class -> load Sub Class ======
                $('#item_class').on('change', function() {
                    const classId = $(this).val();
                    console.log('[INV] item_class change =>', classId);

                    resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                    if (!classId) return;

                    const url = "{{ route('stockjobs.stock-sub-classes') }}";
                    console.log('[INV] load sub-classes -> GET', url, 'params=', {
                        item_class_id: classId
                    });

                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: {
                            item_class_id: classId
                        },
                        dataType: 'json',
                        success: function(res) {
                            console.log('[INV] sub-classes success:', res);
                            const $subcls = $('#item_sub_class');
                            $subcls.prop('disabled', false)
                                .empty()
                                .append(new Option('-- Select Sub Class --', '', true, true));

                            (res?.data || []).forEach(x => $subcls.append(new Option(x.text, x
                                .id)));
                            $subcls.trigger('change');
                        },
                        error: function(xhr) {
                            console.error('[INV] sub-classes ERROR', xhr.status, xhr.responseText);
                        }
                    });
                });

                // =========================
                // PASTIKAN loadItemTypes() DIPANGGIL SAAT MODAL DIBUKA
                // =========================

                // ADD button (punyamu sekarang belum manggil loadItemTypes)        
                $('#addInventoryBtn').on('click', function() {
                    $('#inventoryModalTitle').text('Add Inventory');
                    $('#inventoryForm')[0].reset();
                    $('#inv_id').val('');

                    window.setEditMode(false);
                    openInvModal();

                    window.loadItemTypesPromise().then(function(res) {
                        const gi = (res.data || []).find(x => String(x.text).toUpperCase() === 'GI');
                        if (gi) $('#item_type').val(gi.id).trigger('change');
                    });
                });


                // EDIT: setelah data loaded, sebelum open modal / setelah open modal juga boleh
                $(document).on('click', '.editInventoryBtn', function() {
                    console.log('[INV] editInventoryBtn click -> will load item types too');
                    // ensureSelect2();
                    loadItemTypes();
                });

                // OPTIONAL: kalau kamu buka modal dengan class toggle manual, log juga
                function logModalState() {
                    console.log('[INV] inventoryModal hidden?', $('#inventoryModal').hasClass('hidden'));
                }
                $('#addInventoryBtn, .editInventoryBtn').on('click', logModalState);

                function resetSelect($el, placeholder) {
                    $el.prop('disabled', true)
                        .empty()
                        .append(new Option(placeholder, '', true, true))
                        .trigger('change');
                }

                function loadItemTypesPromise() {
                    return $.get("{{ route('stockjobs.stock-types') }}").then(res => {
                        const $type = $('#item_type');
                        $type.prop('disabled', false)
                            .empty()
                            .append(new Option('-- Select Item Type --', '', true, true));
                        (res.data || []).forEach(x => $type.append(new Option(x.text, x.id)));
                        $type.trigger('change.select2'); // refresh select2 view
                        return res;
                    });
                }

                function loadSubTypesPromise(itemTypeId) {
                    return $.get("{{ route('stockjobs.stock-sub-types') }}", {
                        item_type_id: itemTypeId
                    }).then(res => {
                        const $sub = $('#item_sub_type');
                        $sub.prop('disabled', false)
                            .empty()
                            .append(new Option('-- Select Sub Type --', '', true, true));
                        (res.data || []).forEach(x => $sub.append(new Option(x.text, x.id)));
                        $sub.trigger('change.select2');
                        return res;
                    });
                }

                function loadClassesPromise(subTypeId) {
                    return $.get("{{ route('stockjobs.stock-classes') }}", {
                        item_sub_type_id: subTypeId
                    }).then(res => {
                        const $cls = $('#item_class');
                        $cls.prop('disabled', false)
                            .empty()
                            .append(new Option('-- Select Class --', '', true, true));
                        (res.data || []).forEach(x => $cls.append(new Option(x.text, x.id)));
                        $cls.trigger('change.select2');
                        return res;
                    });
                }

                function loadSubClassesPromise(classId) {
                    return $.get("{{ route('stockjobs.stock-sub-classes') }}", {
                        item_class_id: classId
                    }).then(res => {
                        const $subcls = $('#item_sub_class');
                        $subcls.prop('disabled', false)
                            .empty()
                            .append(new Option('-- Select Sub Class --', '', true, true));
                        (res.data || []).forEach(x => $subcls.append(new Option(x.text, x.id)));
                        $subcls.trigger('change.select2');
                        return res;
                    });
                }

                // function setEditMode(isEdit) {
                //     // inventoryid sudah readonly dari awal
                //     // dropdown dibuat tidak bisa diubah saat edit
                //     $('#item_type').prop('disabled', isEdit);
                //     $('#item_sub_type').prop('disabled', isEdit);
                //     $('#item_class').prop('disabled', isEdit);
                //     $('#item_sub_class').prop('disabled', isEdit);

                //     // unit juga tidak bisa diubah saat edit
                //     $('#stock_unit').prop('disabled', isEdit);
                //     $('#purchase_unit').prop('disabled', isEdit);

                //     // yang boleh diubah hanya inventory_descr
                //     $('#inventory_descr').prop('readonly', !isEdit ? false : false); // tetap bisa edit
                //     // optional: kalau mau item_category juga tidak dipakai, skip
                // }


            });
        </script>



    </div>
</x-app-layout>
