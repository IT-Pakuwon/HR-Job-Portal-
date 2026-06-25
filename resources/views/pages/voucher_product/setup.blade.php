<x-app-layout>

    <style>
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single {
            height: 46px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            background: transparent !important;
        }
        .select2-container--default .select2-selection--single:focus-within,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #64748b !important;
            box-shadow: 0 0 0 2px rgba(100,116,139,.15) !important;
        }
        .select2-selection__rendered { padding-left: 14px !important; font-size: 14px !important; line-height: 46px !important; }
        .select2-selection__arrow { top: 10px !important; right: 10px !important; }
        .select2-dropdown { z-index: 99999 !important; border-radius: 0.75rem !important; box-shadow: 0 8px 24px rgba(15,23,42,.14) !important; }
        .select2-results__option { font-size: 14px; padding: 8px 14px !important; color: #0f172a !important; }
        .select2-results__option--highlighted { background-color: #1e293b !important; color: #ffffff !important; }
        .select2-search--dropdown .select2-search__field { border-radius: 6px !important; padding: 6px 10px !important; color: #0f172a !important; }
        .select2-dropdown { background-color: #ffffff !important; }
        .select2-selection__rendered { color: #0f172a !important; }

    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- TAB HEADER --}}
        <div class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-white/5">

            <button id="tabWarehouse"
                class="tab-btn active-tab inline-flex items-center gap-2 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-700 transition">
                <span class="text-base">🏭</span>
                <span>Warehouse</span>
            </button>

            <button id="tabWarehouseDept"
                class="tab-btn inline-flex items-center gap-2 rounded-xl border border-transparent bg-transparent px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-white/10 dark:hover:text-white">
                <span class="text-base">🏢</span>
                <span>Warehouse Dept</span>
            </button>

        </div>

        {{-- PANEL 1: WAREHOUSE --}}
        <div id="warehousePanel">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Warehouse Setup</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage VPL warehouse master data.</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="{{ route('vpl.mastervp') }}"
                            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10">
                            ← Back to Master Stock
                        </a>
                        <button type="button" onclick="toggleModal('#createWhsModal', true)"
                            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                            <span>＋</span> Add Warehouse
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto p-5">
                    <table id="tableWarehouse" class="display w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Company</th>
                                <th>Warehouse ID</th>
                                <th>VP Type</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>

        {{-- PANEL 2: WAREHOUSE DEPT --}}
        <div id="warehouseDeptPanel" class="hidden">
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/5">

                <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-gray-900 dark:text-white">Warehouse Dept Setup</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure department access per warehouse.</p>
                    </div>
                    <button type="button" onclick="openCreateDeptModal()"
                        class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                        <span>＋</span> Add Dept
                    </button>
                </div>

                <div class="overflow-x-auto p-5">
                    <table id="tableDept" class="display w-full border-collapse text-sm">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Company</th>
                                <th>Warehouse ID</th>
                                <th>Activity Type</th>
                                <th>Department</th>
                                <th>VP Type</th>
                                <th>Status</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Create Warehouse --}}
    {{-- ============================================================ --}}
    <div id="createWhsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Add Warehouse</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create VPL warehouse master data.</p>
                </div>
                <button type="button" onclick="toggleModal('#createWhsModal', false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">✕</button>
            </div>

            <form id="createWhsForm">
                @csrf
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" required class="whs-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse ID <span class="text-red-500">*</span></label>
                        <input type="text" name="whs_id" required
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm uppercase shadow-sm focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-100 dark:border-white/10 dark:bg-white/5 dark:text-white"
                            placeholder="e.g. WH001">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">VP Type</label>
                        <select name="vp_type" class="whs-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                    <button type="button" onclick="toggleModal('#createWhsModal', false)"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">Save Warehouse</button>
                </div>
            </form>

        </div>
    </div>

    {{-- MODAL: Edit Warehouse --}}
    <div id="editWhsModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Warehouse</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update VPL warehouse master data.</p>
                </div>
                <button type="button" onclick="toggleModal('#editWhsModal', false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">✕</button>
            </div>

            <form id="editWhsForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_whs_db_id">

                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" id="edit_whs_cpnyid" required class="whs-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse ID</label>
                        <input type="text" name="whs_id" id="edit_whs_id" readonly
                            class="w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 text-sm uppercase shadow-sm dark:border-white/10 dark:bg-white/10 dark:text-white">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">VP Type</label>
                        <select name="vp_type" id="edit_whs_vp_type" class="whs-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                    <button type="button" onclick="toggleModal('#editWhsModal', false)"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">Update Warehouse</button>
                </div>
            </form>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Create Warehouse Dept --}}
    {{-- ============================================================ --}}
    <div id="createDeptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Add Warehouse Dept</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure department access for a warehouse.</p>
                </div>
                <button type="button" onclick="toggleModal('#createDeptModal', false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">✕</button>
            </div>

            <form id="createDeptForm">
                @csrf
                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" required class="dept-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse ID <span class="text-red-500">*</span></label>
                        <select name="whs_id" id="create_dept_whs_id" required class="dept-select2">
                            <option value="">Select Warehouse</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Activity Type</label>
                        <select name="activity_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="RECEIVE">Receive</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="USAGE">Usage</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">VP Type</label>
                        <select name="vp_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <select name="department_id" class="dept-select2">
                            <option value="">— select department —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_id }} - {{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                    <button type="button" onclick="toggleModal('#createDeptModal', false)"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">Save</button>
                </div>
            </form>

        </div>
    </div>

    {{-- MODAL: Edit Warehouse Dept --}}
    <div id="editDeptModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Edit Warehouse Dept</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Update department access configuration.</p>
                </div>
                <button type="button" onclick="toggleModal('#editDeptModal', false)"
                    class="rounded-lg p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-white/10">✕</button>
            </div>

            <form id="editDeptForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_dept_db_id">

                <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" id="edit_dept_cpnyid" required class="dept-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Warehouse ID <span class="text-red-500">*</span></label>
                        <select name="whs_id" id="edit_dept_whs_id" required class="dept-select2">
                            <option value="">Select Warehouse</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Activity Type</label>
                        <select name="activity_type" id="edit_dept_activity_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="RECEIVE">Receive</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="USAGE">Usage</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">VP Type</label>
                        <select name="vp_type" id="edit_dept_vp_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <select name="department_id" id="edit_dept_department_id" class="dept-select2">
                            <option value="">— select department —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_id }} - {{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-200 px-6 py-4 dark:border-white/10">
                    <button type="button" onclick="toggleModal('#editDeptModal', false)"
                        class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">Cancel</button>
                    <button type="submit"
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-slate-700 dark:bg-blue-600 dark:hover:bg-blue-500">Update</button>
                </div>
            </form>

        </div>
    </div>

    <script>
        const routes = {
            whs: {
                json:   "{{ route('vpl.msproduct.setupwarehouse_json') }}",
                store:  "{{ route('vpl.msproduct.save_warehouse') }}",
                update: "{{ url('msproduct/setupwarehouse') }}",
                toggle: "{{ url('msproduct/setupwarehouse') }}"
            },
            dept: {
                json:   "{{ route('vpl.msproduct.setupwarehouse_dept_json') }}",
                store:  "{{ route('vpl.msproduct.save_warehouse_dept') }}",
                update: "{{ url('msproduct/setupwarehouse/dept') }}",
                toggle: "{{ url('msproduct/setupwarehouse/dept') }}",
                list:   "{{ route('vpl.msproduct.warehouse_list') }}"
            }
        };

        let tableWarehouse, tableDept;
        let warehouseOptions = [];

        // ── Tab switching ──────────────────────────────────────────────
const tabs = {
            tabWarehouse:     '#warehousePanel',
            tabWarehouseDept: '#warehouseDeptPanel',
        };

        $('.tab-btn').on('click', function () {
            const targetPanel = tabs[this.id];

            $('.tab-btn')
                .removeClass('active-tab border-blue-200 bg-blue-50 text-blue-700')
                .addClass('border-transparent bg-transparent text-gray-600');

            $(this)
                .removeClass('border-transparent bg-transparent text-gray-600')
                .addClass('active-tab border-blue-200 bg-blue-50 text-blue-700');

            Object.values(tabs).forEach(p => $(p).addClass('hidden'));
            $(targetPanel).removeClass('hidden');
        });

        // ── Select2 helpers ────────────────────────────────────────────
        function initSelect2(modalId, selector) {
            $(modalId + ' ' + selector).each(function () {
                if ($(this).hasClass('select2-hidden-accessible')) $(this).select2('destroy');
                $(this).select2({ width: '100%', dropdownParent: $('body') });
            });
        }

        // ── Warehouse list for Dept dropdowns ──────────────────────────
        function loadWarehouseOptions(targetSelectId, cpnyid = '', selectedValue = '') {
            $.get(routes.dept.list, { cpnyid }, function (data) {
                const $sel = $(targetSelectId);
                $sel.empty().append('<option value="">Select Warehouse</option>');
                data.forEach(w => {
                    $sel.append(new Option(w.cpnyid + ' - ' + w.whs_id, w.whs_id, false, w.whs_id === selectedValue));
                });
                if ($sel.hasClass('select2-hidden-accessible')) $sel.trigger('change');
            });
        }

        // ── Document ready ─────────────────────────────────────────────
        $(document).ready(function () {

            initSelect2('#createWhsModal', '.whs-select2');
            initSelect2('#editWhsModal',   '.whs-select2');
            initSelect2('#createDeptModal', '.dept-select2');
            initSelect2('#editDeptModal',   '.dept-select2');

            // Reload warehouse options when company changes (create dept)
            $('#createDeptModal').on('change', 'select[name="cpnyid"]', function () {
                loadWarehouseOptions('#create_dept_whs_id', $(this).val());
            });

            // Reload warehouse options when company changes (edit dept)
            $('#editDeptModal').on('change', '#edit_dept_cpnyid', function () {
                loadWarehouseOptions('#edit_dept_whs_id', $(this).val());
            });

            // Warehouse DataTable
            tableWarehouse = $('#tableWarehouse').DataTable(baseTableConfig({
                ajax: routes.whs.json,
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, width: '5%' },
                    { data: 'cpnyid',      name: 'cpnyid' },
                    { data: 'whs_id',      name: 'whs_id' },
                    {
                        data: 'vp_type', name: 'vp_type',
                        render: d => d === 'V'
                            ? '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Voucher</span>'
                            : d === 'P'
                            ? '<span class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">Product</span>'
                            : (d ?? '-')
                    },
                    { data: 'status_badge', orderable: false, searchable: false },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-right',
                        render: function (data, type, row) {
                            const id      = String(row.id).replace(/'/g, "\\'");
                            const isActive = row.status === 'A';
                            const toggleColor = isActive ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700';
                            const toggleLabel = isActive ? 'Deactivate' : 'Activate';
                            return `<div class="flex items-center justify-end gap-2">
                                <button onclick="editWarehouse('${id}')" class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white hover:bg-amber-600">Edit</button>
                                <button onclick="toggleWarehouse('${id}', ${isActive ? 0 : 1})" class="rounded-lg ${toggleColor} px-3 py-1 text-xs text-white">${toggleLabel}</button>
                            </div>`;
                        }
                    }
                ],
                searchPlaceholder: 'Search warehouse...'
            }));

            // Warehouse Dept DataTable
            tableDept = $('#tableDept').DataTable(baseTableConfig({
                ajax: routes.dept.json,
                columns: [
                    { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '5%' },
                    { data: 'cpnyid',         name: 'cpnyid' },
                    { data: 'whs_id',         name: 'whs_id' },
                    { data: 'activity_type',  name: 'activity_type' },
                    { data: 'department_id',  name: 'department_id' },
                    {
                        data: 'vp_type', name: 'vp_type',
                        render: d => d === 'V'
                            ? '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">Voucher</span>'
                            : d === 'P'
                            ? '<span class="inline-flex items-center rounded-full border border-purple-200 bg-purple-50 px-3 py-1 text-xs font-semibold text-purple-700">Product</span>'
                            : (d ?? '-')
                    },
                    { data: 'status_badge',   orderable: false, searchable: false },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-right',
                        render: function (data, type, row) {
                            const id      = String(row.id).replace(/'/g, "\\'");
                            const isActive = row.status === 'A';
                            const toggleColor = isActive ? 'bg-red-500 hover:bg-red-600' : 'bg-green-600 hover:bg-green-700';
                            const toggleLabel = isActive ? 'Deactivate' : 'Activate';
                            return `<div class="flex items-center justify-end gap-2">
                                <button onclick="editDept('${id}')" class="rounded-lg bg-amber-500 px-3 py-1 text-xs text-white hover:bg-amber-600">Edit</button>
                                <button onclick="toggleDept('${id}', ${isActive ? 0 : 1})" class="rounded-lg ${toggleColor} px-3 py-1 text-xs text-white">${toggleLabel}</button>
                            </div>`;
                        }
                    }
                ],
                searchPlaceholder: 'Search warehouse dept...'
            }));

            // Create Warehouse form
            $('#createWhsForm').on('submit', function (e) {
                e.preventDefault();
                submitForm({ form: $(this), url: routes.whs.store, method: 'POST', table: tableWarehouse, modal: '#createWhsModal', loadingText: 'Saving warehouse...' });
            });

            // Edit Warehouse form
            $('#editWhsForm').on('submit', function (e) {
                e.preventDefault();
                const id = $('#edit_whs_db_id').val();
                submitForm({ form: $(this), url: `${routes.whs.update}/${id}/update`, method: 'POST', table: tableWarehouse, modal: '#editWhsModal', loadingText: 'Updating warehouse...' });
            });

            // Create Dept form
            $('#createDeptForm').on('submit', function (e) {
                e.preventDefault();
                submitForm({ form: $(this), url: routes.dept.store, method: 'POST', table: tableDept, modal: '#createDeptModal', loadingText: 'Saving...' });
            });

            // Edit Dept form
            $('#editDeptForm').on('submit', function (e) {
                e.preventDefault();
                const id = $('#edit_dept_db_id').val();
                submitForm({ form: $(this), url: `${routes.dept.update}/${id}/update`, method: 'POST', table: tableDept, modal: '#editDeptModal', loadingText: 'Updating...' });
            });

        });

        // ── Warehouse CRUD ─────────────────────────────────────────────
        function editWarehouse(id) {
            $.get(`${routes.whs.update}/${id}/edit`, function (data) {
                const w = data.warehouse;
                $('#edit_whs_db_id').val(w.id);
                $('#edit_whs_id').val(w.whs_id);
                $('#edit_whs_cpnyid').val(w.cpnyid).trigger('change');
                $('#edit_whs_vp_type').val(w.vp_type ?? '').trigger('change');
                toggleModal('#editWhsModal', true);
            }).fail(() => showError('Failed to load warehouse data'));
        }

        function toggleWarehouse(id, activate) {
            Swal.fire({
                title: activate ? 'Activate warehouse?' : 'Deactivate warehouse?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: activate ? '#16a34a' : '#dc2626',
                confirmButtonText: activate ? 'Yes, activate' : 'Yes, deactivate'
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `${routes.whs.toggle}/${id}/toggle`,
                    type: 'PUT',
                    data: { _token: '{{ csrf_token() }}', activate },
                    success: res => { showSuccess(res.message); tableWarehouse.ajax.reload(null, false); },
                    error: xhr => showError(xhr.responseJSON?.message ?? 'Something went wrong')
                });
            });
        }

        // ── Warehouse Dept CRUD ────────────────────────────────────────
        function openCreateDeptModal() {
            toggleModal('#createDeptModal', true);
            loadWarehouseOptions('#create_dept_whs_id', '');
        }

        function editDept(id) {
            $.get(`${routes.dept.update}/${id}/edit`, function (data) {
                const d = data.dept;
                $('#edit_dept_db_id').val(d.id);
                $('#edit_dept_cpnyid').val(d.cpnyid).trigger('change');
                $('#edit_dept_activity_type').val(d.activity_type ?? '').trigger('change');
                $('#edit_dept_vp_type').val(d.vp_type ?? '').trigger('change');
                $('#edit_dept_department_id').val(d.department_id ?? '').trigger('change');
                loadWarehouseOptions('#edit_dept_whs_id', d.cpnyid, d.whs_id);
                toggleModal('#editDeptModal', true);
            }).fail(() => showError('Failed to load dept data'));
        }

        function toggleDept(id, activate) {
            Swal.fire({
                title: activate ? 'Activate dept?' : 'Deactivate dept?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: activate ? '#16a34a' : '#dc2626',
                confirmButtonText: activate ? 'Yes, activate' : 'Yes, deactivate'
            }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({
                    url: `${routes.dept.toggle}/${id}/toggle`,
                    type: 'PUT',
                    data: { _token: '{{ csrf_token() }}', activate },
                    success: res => { showSuccess(res.message); tableDept.ajax.reload(null, false); },
                    error: xhr => showError(xhr.responseJSON?.message ?? 'Something went wrong')
                });
            });
        }

        // ── Shared helpers ─────────────────────────────────────────────
        function toggleModal(modalId, show = true) {
            const modal = $(modalId);
            if (show) {
                modal.removeClass('hidden').addClass('flex');
                initSelect2(modalId, '.whs-select2');
                initSelect2(modalId, '.dept-select2');
            } else {
                modal.removeClass('flex').addClass('hidden');
                modal.find('form')[0]?.reset();
                modal.find('.whs-select2, .dept-select2').val(null).trigger('change');
            }
        }

        function submitForm({ form, url, method = 'POST', table = null, modal = null, loadingText = 'Processing...' }) {
            $.ajax({
                url,
                type: method,
                data: form.serialize(),
                beforeSend: function () { form.find('button[type="submit"]').prop('disabled', true); showLoading(loadingText); },
                complete: function () { form.find('button[type="submit"]').prop('disabled', false); },
                success: function (response) {
                    showSuccess(response.message);
                    if (modal) toggleModal(modal, false);
                    if (table) table.ajax.reload(null, false);
                },
                error: function (xhr) { showError(xhr.responseJSON?.message ?? 'Something went wrong'); }
            });
        }

        function baseTableConfig({ ajax, columns, order = [[1, 'asc']], searchPlaceholder = 'Search...' }) {
            return { processing: true, serverSide: true, responsive: true, autoWidth: false, pageLength: 10, ajax, columns, order, language: { search: '', searchPlaceholder } };
        }

        function showLoading(title = 'Processing...') { Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() }); }
        function showSuccess(message) { Swal.fire({ icon: 'success', title: 'Success', text: message, timer: 1500, showConfirmButton: false }); }
        function showError(message = 'Something went wrong') { Swal.fire({ icon: 'error', title: 'Error', text: message }); }
    </script>

</x-app-layout>
