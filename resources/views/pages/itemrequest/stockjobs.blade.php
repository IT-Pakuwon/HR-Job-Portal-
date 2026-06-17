<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'stockjobs' ? 'Stock Jobs' : '';
    @endphp


    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
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


            <a href="#" class="status-filter group block h-full" data-filter="stock_all">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                        🗂️
                    </div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">
                            All Item Request
                        </p>
                    </div>

                    <p class="shrink-0 text-base font-bold">
                        {{ number_format($stockAllRequest ?? 0) }}
                    </p>

                </div>
            </a>
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
                                <th class="w-8"></th>
                                <th class="w-32 px-6 py-2 font-medium">IRID</th>
                                <th class="w-32 px-6 py-2 font-medium">Date</th>
                                <th class="w-32 px-6 py-2 font-medium">Company</th>
                                <th class="w-32 px-6 py-2 font-medium">Department</th>
                                <th class="w-32 px-6 py-2 font-medium">Description</th>
                                <th class="w-32 px-6 py-2 font-medium">Inventory ID</th>
                                <th class="w-32 px-6 py-2 font-medium">Created By</th>
                                <th class="w-32 px-6 py-2 font-medium">IR Status</th>
                                <th class="w-32 px-6 py-2 font-medium">Job Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
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

                <div class="mb-4 flex gap-3">
                    <select id="filter_cpny" class="rounded-lg border px-3 py-2">
                        <option value="">Select Company</option>
                        @foreach ($cpnyIds as $cpny)
                            <option value="{{ $cpny }}">{{ $cpny }}</option>
                        @endforeach
                    </select>

                    <select id="filter_bu" class="rounded-lg border px-3 py-2">
                        <option value="">Select Business Unit</option>
                        @foreach ($buList as $bu)
                            <option value="{{ $bu->business_unit_id }}">{{ $bu->business_unit_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="rounded-base relative overflow-x-auto">
                    <table id="inventoryTable" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th class="w-8"></th>
                                <th class="w-32 px-6 py-2 font-medium">Actions</th>
                                <th class="w-32 px-6 py-2 font-medium">Inventory ID</th>
                                <th class="w-32 px-6 py-2 font-medium">Inventory Description</th>
                                <th class="w-32 px-6 py-2 font-medium">Stock</th>
                                <th class="w-32 px-6 py-2 font-medium">Sub Type</th>
                                <th class="w-32 px-6 py-2 font-medium">Class</th>
                                <th class="w-32 px-6 py-2 font-medium">Sub Class</th>
                                <th class="w-32 px-6 py-2 font-medium">Stock Unit</th>
                                <th class="w-32 px-6 py-2 font-medium">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- INVENTORY CRUD MODAL --}}
        <div id="inventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-3xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                <h2 id="inventoryModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add
                    Inventory</h2>

                <form id="inventoryForm">
                    @csrf
                    <input type="hidden" id="inv_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div id="inventoryIdWrapper">
                            <label class="block text-gray-700 dark:text-white">Inventory ID</label>
                            <input type="text" id="inventoryid" name="inventoryid"
                                class="w-full cursor-not-allowed rounded-lg border bg-gray-100 px-3 py-2 text-gray-700 dark:bg-gray-600 dark:text-gray-200"
                                readonly>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Type</label>
                            <select id="item_type" name="item_type_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select Item Type</option>
                            </select>
                            <input type="hidden" id="item_type_hidden" name="item_type_id_hidden">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Type</label>
                            <select id="item_sub_type" name="item_sub_type_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Sub Type</option>
                            </select>
                            <input type="hidden" id="item_sub_type_hidden" name="item_sub_type_id_hidden">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Class</label>
                            <select id="item_class" name="item_class_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Class</option>
                            </select>
                            <input type="hidden" id="item_class_hidden" name="item_class_id_hidden">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Item Sub Class</label>
                            <select id="item_sub_class" name="item_sub_class_id"
                                class="select2 w-full rounded-lg border px-3 py-2" required disabled>
                                <option value="">Select Sub Class</option>
                            </select>
                            <input type="hidden" id="item_sub_class_hidden" name="item_sub_class_id_hidden">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Description</label>
                            <input type="text" id="inventory_descr" name="inventory_descr"
                                class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div id="stockUnitWrap">
                            <label class="block text-gray-700 dark:text-white">Stock Unit</label>
                            <select id="stock_unit" name="stock_unit"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select</option>
                                @foreach ($baseuom as $r)
                                    <option value="{{ $r->uom_description }}">{{ $r->uom_description }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="stock_unit_hidden" name="stock_unit_hidden">
                        </div>

                        <div id="purchaseUnitWrap">
                            <label class="block text-gray-700 dark:text-white">Purchase Unit</label>
                            <select id="purchase_unit" name="purchase_unit"
                                class="select2 w-full rounded-lg border px-3 py-2" required>
                                <option value="">Select</option>
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
                        {{-- <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button> --}}
                        <button id="btnInvSave" type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- PICK INVENTORY MODAL --}}
        <div id="pickInventoryModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="relative w-full max-w-4xl rounded-xl bg-white p-4 shadow-lg dark:bg-gray-800">
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">🔎 Pilih Inventory (MsInventory)</h2>
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
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>

        <script>
            // URL endpoint dropdown STOCK (sesuaikan route name kamu)
            window.ddUrlStock = {
                itemTypes: "{{ route('stockjobs.stock-types') }}",
                subTypes: "{{ route('stockjobs.stock-sub-types') }}",
                classes: "{{ route('stockjobs.stock-classes') }}",
                subClasses: "{{ route('stockjobs.stock-sub-classes') }}"
            };

            function initSelect2InStockModal() {
                // penting: dropdownParent supaya select2 muncul di modal (bukan di belakang overlay)
                $('#inventoryModal .select2').select2({
                    width: '100%',
                    dropdownParent: $('#inventoryModal')
                });
            }

            function resetSelect($el, placeholder) {
                $el.prop('disabled', true)
                    .empty()
                    .append(new Option(placeholder, '', true, true))
                    .trigger('change');
            }
        </script>

        <script>
            function loadStockItemTypesPromise() {
                return $.get(window.ddUrlStock.itemTypes).then(function(res) {
                    const $type = $('#item_type');
                    $type.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Item Type --', '', true, true));

                    (res.data || []).forEach(x => $type.append(new Option(x.text, x.id)));

                    // AUTO pilih GI (karena Stock)
                    const gi = (res.data || []).find(x =>
                        String(x.id).toUpperCase() === 'GI' || String(x.text).toUpperCase() === 'GI'
                    );
                    if (gi) $type.val(gi.id).trigger('change');
                    else $type.trigger('change');

                    return res;
                });
            }

            function loadStockSubTypesPromise(itemTypeId) {
                return $.get(window.ddUrlStock.subTypes, {
                    item_type_id: itemTypeId
                }).then(function(res) {
                    const $sub = $('#item_sub_type');
                    $sub.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Sub Type --', '', true, true));

                    (res.data || []).forEach(x => $sub.append(new Option(x.text, x.id)));
                    $sub.trigger('change');
                    return res;
                });
            }

            function loadStockClassesPromise(subTypeId) {
                return $.get(window.ddUrlStock.classes, {
                    item_sub_type_id: subTypeId
                }).then(function(res) {
                    const $cls = $('#item_class');
                    $cls.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Class --', '', true, true));

                    (res.data || []).forEach(x => $cls.append(new Option(x.text, x.id)));
                    $cls.trigger('change');
                    return res;
                });
            }

            function loadStockSubClassesPromise(classId) {
                return $.get(window.ddUrlStock.subClasses, {
                    item_class_id: classId
                }).then(function(res) {
                    const $subcls = $('#item_sub_class');
                    $subcls.prop('disabled', false)
                        .empty()
                        .append(new Option('-- Select Sub Class --', '', true, true));

                    (res.data || []).forEach(x => $subcls.append(new Option(x.text, x.id)));
                    $subcls.trigger('change');
                    return res;
                });
            }

            // chaining onchange
            $(document).on('change', '#item_type', function() {
                const typeId = $(this).val();

                resetSelect($('#item_sub_type'), '-- Select Sub Type --');
                resetSelect($('#item_class'), '-- Select Class --');
                resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                if (!typeId) return;
                loadStockSubTypesPromise(typeId);
            });

            $(document).on('change', '#item_sub_type', function() {
                const subTypeId = $(this).val();

                resetSelect($('#item_class'), '-- Select Class --');
                resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                if (!subTypeId) return;
                loadStockClassesPromise(subTypeId);
            });

            $(document).on('change', '#item_class', function() {
                const classId = $(this).val();

                resetSelect($('#item_sub_class'), '-- Select Sub Class --');

                if (!classId) return;
                loadStockSubClassesPromise(classId);
            });
        </script>

        <script>
            function setSaveLoading(isLoading) {
                const $btn = $('#btnInvSave');
                const $cancel = $('#closeInventoryModal');

                if (isLoading) {
                    $btn.prop('disabled', true)
                        .addClass('opacity-60 cursor-not-allowed')
                        .data('oldText', $btn.html())
                        .html(`
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" opacity="0.25"></circle>
                                    <path d="M4 12a8 8 0 0 1 8-8" stroke="currentColor" stroke-width="4" opacity="0.75"></path>
                                </svg>
                                Saving...
                            </span>
                        `);

                    // optional: blok cancel saat saving biar ga aneh
                    $cancel.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
                } else {
                    const old = $btn.data('oldText') || 'Save';
                    $btn.prop('disabled', false)
                        .removeClass('opacity-60 cursor-not-allowed')
                        .html(old);

                    $cancel.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
                }
            }

            // guard supaya kalau handler kebinding 2x pun tetap aman
            let invSubmitting = false;

            $('#inventoryForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                if (invSubmitting) return; // ✅ cegah double submit
                invSubmitting = true;
                setSaveLoading(true);

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
                    })
                    .done(function() {
                        closeInvModal();
                        invTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Data inventory berhasil disimpan',
                            timer: 1400,
                            showConfirmButton: false
                        });
                    })
                    .fail(function(xhr) {
                        console.error(xhr.responseText);

                        // ambil pesan validasi kalau ada
                        let msg = 'Gagal menyimpan data inventory';
                        try {
                            const res = xhr.responseJSON;
                            if (res?.message) msg = res.message;
                            if (res?.error) msg = res.error;
                        } catch (e) {}

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: msg
                        });
                    })
                    .always(function() {
                        invSubmitting = false;
                        setSaveLoading(false);
                    });
            });
        </script>

        <script>
            // =========================
            // HELPERS
            // =========================
            function openInvModal() {
                $('#inventoryModal').removeClass('hidden').addClass('flex');
            }

            function closeInvModal() {
                $('#inventoryModal').addClass('hidden').removeClass('flex');
            }

            function openPickInvModal() {
                $('#pickInventoryModal').removeClass('hidden').addClass('flex');
                if (pickInvTable) pickInvTable.ajax.reload(null, true);
            }

            function closePickInvModal() {
                $('#pickInventoryModal').addClass('hidden').removeClass('flex');
                $('#pick_irid').text('-');
            }

            function setFormMode(isEdit) {
                // Add mode: inventoryid disembunyikan
                if (!isEdit) {
                    $('#inventoryIdWrapper').addClass('hidden');
                    $('#inventoryid').val('');
                } else {
                    $('#inventoryIdWrapper').removeClass('hidden');
                }

                const map = [
                    ['item_type', 'item_type_id'],
                    ['item_sub_type', 'item_sub_type_id'],
                    ['item_class', 'item_class_id'],
                    ['item_sub_class', 'item_sub_class_id'],
                    ['stock_unit', 'stock_unit'],
                    ['purchase_unit', 'purchase_unit'],
                ];

                map.forEach(([id, realName]) => {
                    const $sel = $('#' + id);
                    const $hid = $('#' + id + '_hidden');

                    if (isEdit) {
                        $hid.val($sel.val() || '');
                        $hid.attr('name', realName); // hidden ikut submit
                        $sel.removeAttr('name'); // select tidak ikut submit
                        $sel.prop('required', false);
                        $sel.prop('disabled', true);
                    } else {
                        $sel.attr('name', realName);
                        $sel.prop('required', true);
                        $sel.prop('disabled', false);

                        $hid.attr('name', realName + '_hidden');
                        $hid.val('');
                    }
                });

                $('#inventoryid').prop('readonly', true);
                $('#inventory_descr').prop('readonly', false);
            }

            // =========================
            // MAIN
            // =========================
            let jobsFilter = 'jobs';
            let pickedId = null;
            let pickedTrid = null;
            let pickedIrid = null;

            $(document).ready(function() {

                const $jobsWrap = $('#jobsWrap');
                const $invWrap = $('#invWrap');
                const $jobsTitle = $('#jobsTitle');

                function setActive(el) {
                    $('.status-filter').removeClass('active');
                    $(el).addClass('active');
                }

                // =========================
                // JOBS TABLE (Pakai pola lama)
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
                            target: 0
                        }
                    },
                    columnDefs: [{
                            targets: '_all',
                            className: 'whitespace-normal break-words'
                        },
                        {
                            targets: 0,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false
                        },
                        {
                            targets: 8, // IR Status column
                            visible: false
                        }
                    ],
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_Stock',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var str = String(data || '')
                                            .replace(/<[^>]*>/g, '')
                                            .replace(/&amp;/g, '&')
                                            .replace(/&lt;/g, '<')
                                            .replace(/&gt;/g, '>')
                                            .replace(/&quot;/g, '"')
                                            .replace(/&#039;/g, "'")
                                            .replace(/\r?\n/g, ' ')
                                            .trim();
                                        return str;
                                    }
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'List_Stock',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                format: {
                                    body: function(data, row, column, node) {
                                        var str = String(data || '')
                                            .replace(/<[^>]*>/g, '')
                                            .replace(/&amp;/g, '&')
                                            .replace(/&lt;/g, '<')
                                            .replace(/&gt;/g, '>')
                                            .replace(/&quot;/g, '"')
                                            .replace(/&#039;/g, "'")
                                            .replace(/\r?\n/g, ' ')
                                            .trim();
                                        return str;
                                    }
                                }
                            }
                        }
                    ],
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
                            defaultContent: '',
                            searchable: false,
                            orderable: false
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

                        // InventoryID: pick / rollback (pola lama)
                        {
                            data: 'inventoryid',
                            orderable: false,
                            render: function(data, type, row) {
                                if (!data) {
                                    return `
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                class="btnPickInventory inline-flex items-center justify-center rounded bg-indigo-600 px-2.5 py-1.5 text-sm font-semibold text-white hover:bg-indigo-700"
                                                data-id="${row.id ?? ''}"
                                                data-trid="${row.trid ?? ''}"
                                                data-irid="${row.irid ?? ''}">
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
                            data: 'status',
                            className: 'text-center',
                            render: function(v) {

                                v = String(v || '').toUpperCase();

                                if (v === 'D') {
                                    return `<span class="inline-block w-24 rounded bg-gray-300/30 px-3 py-1.5 text-sm font-semibold text-gray-600">Draft</span>`;
                                }

                                if (v === 'P') {
                                    return `<span class="inline-block w-24 rounded bg-yellow-300/30 px-3 py-1.5 text-sm font-semibold text-yellow-600">On Progress</span>`;
                                }

                                if (v === 'C') {
                                    return `<span class="inline-block w-24 rounded bg-blue-300/30 px-3 py-1.5 text-sm font-semibold text-blue-600">Completed</span>`;
                                }

                                if (v === 'R') {
                                    return `<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Rejected</span>`;
                                }

                                return v;
                            }
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
                        }
                    ],
                });

                // =========================
                // INVENTORY TABLE (MATCH HEADER)
                // IMPORTANT: kolom harus SAMA dgn <thead> (9 kolom)
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
                            target: 0
                        }
                    },
                    columnDefs: [{
                            targets: '_all',
                            className: 'whitespace-normal break-words'
                        },
                        {
                            targets: 0,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false
                        }
                    ],
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'List_Inventory',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700'
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'List_Inventory',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700'
                        }
                    ],
                    ajax: {
                        url: "{{ route('stockjobs.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.source = 'inventory';
                            d.cpny_id = $('#filter_cpny').val();
                            d.business_unit_id = $('#filter_bu').val();
                        }
                    },
                    order: [
                        [2, 'asc']
                    ],
                    columns: [{
                            data: null,
                            defaultContent: '',
                            searchable: false,
                            orderable: false
                        },
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
                            data: 'stock',
                            render: function(v) {

                                if (v === null || v === undefined || v === '') {
                                    return '0';
                                }

                                const n = Number(v);

                                if (Number.isNaN(n)) {
                                    return v;
                                }

                                return n.toLocaleString('id-ID', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
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
                    ]
                });

                // =========================
                // PICK INVENTORY TABLE
                // =========================
                pickInvTable = $('#pickInventoryTable').DataTable({
                    processing: true,
                    serverSide: true,
                    deferRender: true,
                    pageLength: 10,
                    ajax: {
                        url: "{{ route('stockjobs.inventory-pick.json') }}",
                        type: "GET"
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
                        }
                    ]
                });

                // default show jobs
                $invWrap.addClass('hidden');
                $jobsWrap.removeClass('hidden');

                // =========================
                // CARD CLICK
                // =========================
                const irStatusCol = 8;
                $('.status-filter').on('click', function(e) {
                    e.preventDefault();
                    const f = $(this).data('filter') || 'all';
                    setActive(this);

                    jobsTable.column(irStatusCol).visible(false);


                    if (f === 'inv') {
                        $jobsWrap.addClass('hidden');
                        $invWrap.removeClass('hidden');
                        invTable.ajax.reload(null, true);
                        return;
                    }

                    if (f === 'stock_all') {
                        // SHOW IR STATUS
                        jobsTable.column(irStatusCol).visible(true);

                        $invWrap.addClass('hidden');
                        $jobsWrap.removeClass('hidden');

                        jobsFilter = 'stock_all';

                        $jobsTitle.text('All Item Request (Stock)');
                        jobsTable.ajax.reload(null, true);
                        return;
                    }

                    $invWrap.addClass('hidden');
                    $jobsWrap.removeClass('hidden');

                    jobsFilter = f;

                    const titleMap = {
                        all: 'Stock Jobs (All)',
                        jobs: 'Stock Jobs',
                        done: 'Stock Done',
                        stock_all: 'All Item Request (Stock)'
                    };
                    $jobsTitle.text(titleMap[jobsFilter] ?? 'Stock Jobs');

                    jobsTable.ajax.reload(null, true);
                });

                // inventory filter
                $('#filter_cpny, #filter_bu').on('change', function() {
                    invTable.ajax.reload(null, true);
                });

                // =========================
                // MODAL - ADD
                // =========================
                $('#addInventoryBtn').on('click', function() {
                    $('#inventoryModalTitle').text('Add Inventory');
                    $('#inventoryForm')[0].reset();
                    $('#inv_id').val('');

                    setFormMode(false);
                    openInvModal();

                    // ✅ ini WAJIB biar select2 muncul benar di modal
                    initSelect2InStockModal();

                    // ✅ ini WAJIB biar dropdown ke-load
                    loadStockItemTypesPromise();
                });

                $('#closeInventoryModal').on('click', closeInvModal);

                // =========================
                // MODAL - EDIT
                // =========================
                $(document).on('click', '.editInventoryBtn', function() {
                    const id = $(this).data('id');

                    $.get(`/invstock/${id}/edit`, function(i) {
                        $('#inventoryModalTitle').text('Edit Inventory');
                        $('#inventoryForm')[0].reset();

                        // set mode edit dulu
                        setFormMode(true);

                        $('#inv_id').val(i.id);
                        $('#inventoryid').val(i.inventoryid);
                        $('#inventory_descr').val(i.inventory_descr);

                        // untuk display dropdown (disabled) isi value dummy
                        function setSelectDisplay($el, text) {
                            $el.prop('disabled', true).empty().append(new Option(text || '-', text ||
                                '-', true, true));
                        }

                        setSelectDisplay($('#item_type'), i.item_type);
                        setSelectDisplay($('#item_sub_type'), i.item_sub_type);
                        setSelectDisplay($('#item_class'), i.item_class);
                        setSelectDisplay($('#item_sub_class'), i.item_sub_class);
                        setSelectDisplay($('#stock_unit'), i.stock_unit);
                        setSelectDisplay($('#purchase_unit'), i.purchase_unit);

                        // penting: hidden value ikut submit (pakai nilai aktual dari response kalau ada)
                        $('#item_type_hidden').val(i.item_type_id ?? '');
                        $('#item_sub_type_hidden').val(i.item_sub_type_id ?? '');
                        $('#item_class_hidden').val(i.item_class_id ?? '');
                        $('#item_sub_class_hidden').val(i.item_sub_class_id ?? '');
                        $('#stock_unit_hidden').val(i.stock_unit ?? '');
                        $('#purchase_unit_hidden').val(i.purchase_unit ?? '');

                        openInvModal();
                    }).fail(function(xhr) {
                        console.error(xhr.responseText);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Gagal load data inventory'
                        });
                    });
                });

                // =========================
                // TOGGLE STATUS
                // =========================
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
                            invTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal update status inventory'
                            });
                        }
                    });
                });

                // =========================
                // SUBMIT CREATE/UPDATE
                // =========================
                // $('#inventoryForm').on('submit', function(e){
                //     e.preventDefault();

                //     const id = $('#inv_id').val();
                //     const url = id ? `/invstock/${id}` : "{{ route('invstock.store') }}";

                //     const formData = new FormData(document.getElementById('inventoryForm'));
                //     if (id) formData.append('_method', 'PUT');

                //     $.ajax({
                //         url: url,
                //         type: 'POST',
                //         headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                //         data: formData,
                //         processData: false,
                //         contentType: false,
                //         success: function(){
                //             closeInvModal();
                //             invTable.ajax.reload(null, false);
                //             Swal.fire({icon:'success', title:'Berhasil', text:'Data inventory berhasil disimpan', timer:1400, showConfirmButton:false});
                //         },
                //         error: function(xhr){
                //             console.error(xhr.responseText);
                //             Swal.fire({icon:'error', title:'Gagal', text:'Gagal menyimpan data inventory'});
                //         }
                //     });
                // });

                // =========================
                // PICK INVENTORY (JOBS)
                // =========================
                $('#closePickInventoryModal').on('click', function() {
                    closePickInvModal();
                    pickedId = null;
                    pickedTrid = null;
                    pickedIrid = null;
                });

                $(document).on('click', '.btnPickInventory', function() {
                    pickedId = $(this).data('id') || null;
                    pickedTrid = $(this).data('trid') || null;
                    pickedIrid = $(this).data('irid') || null;

                    $('#pick_irid').text(pickedIrid || '-');
                    openPickInvModal();
                });

                $('#pickInventoryTable tbody').on('click', 'tr', function() {
                    const row = pickInvTable.row(this).data();
                    if (!row || !row.inventoryid) return;

                    if (!pickedId && !pickedTrid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'ID/TRID kosong, tidak bisa update.'
                        });
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
                            pickedId = null;
                            pickedTrid = null;
                            pickedIrid = null;

                            jobsTable.ajax.reload(null, false);
                            Swal.fire({
                                icon: 'success',
                                title: 'Stock Jobs Sukses',
                                timer: 1200,
                                showConfirmButton: false
                            });
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: 'Gagal update inventory ke item request'
                            });
                        }
                    });
                });

                // =========================
                // ROLLBACK INVENTORYID (JOBS)
                // =========================
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
                    }).then((r) => {
                        if (!r.isConfirmed) return;

                        $.ajax({
                            url: `/stockjobs/${eid}/rollback`,
                            type: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            success: function() {
                                jobsTable.ajax.reload(null, false);
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
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </div>
</x-app-layout>
