<x-app-layout>

    <x-spp-list-styles />
    <x-vpl-styles />
    <style>
        .dt-pretty table.dataTable thead th { white-space: nowrap; }
        .dt-pretty table.dataTable thead th:last-child { text-align: right; }
        .dt-pretty table.dataTable tbody td { padding: .75rem 1rem; }
        .vpl-dialog { animation: vpl-dialog-in .18s ease-out; }
        @keyframes vpl-dialog-in { from { opacity: 0; transform: translateY(10px) scale(.98); } to { opacity: 1; transform: none; } }
        @media (prefers-reduced-motion: reduce) { .vpl-dialog { animation: none; } }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- TAB HEADER --}}
        <div class="mb-3 inline-flex flex-wrap items-center gap-1 rounded-xl border border-gray-200 bg-white p-1 shadow-sm dark:border-white/10 dark:bg-[#0f172a]">

            <button id="tabWarehouse" type="button" class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition active-tab bg-blue-600 text-white shadow-sm">
                <i class="fa-solid fa-warehouse text-xs"></i>
                <span>Warehouse</span>
            </button>

            <button id="tabWarehouseDept" type="button" class="tab-btn inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-semibold transition bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300">
                <i class="fa-solid fa-users text-xs"></i>
                <span>Warehouse Dept</span>
            </button>

        </div>

        {{-- PANEL 1: WAREHOUSE --}}
        <div id="warehousePanel">
            <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

                <x-list-page-header title="Warehouse Setup" subtitle="Manage VPL warehouse master data." icon='<i class="fa-solid fa-warehouse"></i>'>
                    <a href="{{ route('vpl.mastervp') }}" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                        <i class="fa-solid fa-arrow-left text-xs"></i> Back to Master Stock
                    </a>
                    <button type="button" onclick="toggleModal('#createWhsModal', true)" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                        <i class="fa-solid fa-plus text-xs"></i> Add Warehouse
                    </button>
                </x-list-page-header>

                <div class="relative overflow-hidden">
                    <table id="tableWarehouse" class="w-full border-separate border-spacing-0 text-sm" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50/70 dark:bg-white/[0.02]">
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">No</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Company</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Warehouse ID</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">VP Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>

            </div>
        </div>

        {{-- PANEL 2: WAREHOUSE DEPT --}}
        <div id="warehouseDeptPanel" class="hidden">
            <div class="dt-pretty rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

                <x-list-page-header title="Warehouse Dept Setup" subtitle="Configure department access per warehouse." icon='<i class="fa-solid fa-users"></i>'>
                    <button type="button" onclick="openCreateDeptModal()" class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                        <i class="fa-solid fa-plus text-xs"></i> Add Dept
                    </button>
                </x-list-page-header>

                <div class="grid grid-cols-1 gap-3 border-b border-gray-100 bg-gray-50/60 px-5 py-4 dark:border-white/[0.06] dark:bg-white/[0.02] sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label for="filter_dept_whs_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse ID</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-warehouse text-xs"></i></span>
                            <select id="filter_dept_whs_id" class="dept-filter-select2" style="width:100%">
                            <option value="">All Warehouse IDs</option>
                            @foreach ($warehouseIds as $whsId)
                                <option value="{{ $whsId }}">{{ $whsId }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_dept_activity_type" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Activity Type</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-list-check text-xs"></i></span>
                            <select id="filter_dept_activity_type" class="dept-filter-select2" style="width:100%">
                            <option value="">All Activity Types</option>
                            <option value="RECEIVE">Receive</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="TRANSFER_RECEIVE">Transfer Receive</option>
                            <option value="USAGE">Usage</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_dept_department_id" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-users text-xs"></i></span>
                            <select id="filter_dept_department_id" class="dept-filter-select2" style="width:100%">
                            <option value="">All Departments</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_id }} - {{ $dept->department_name }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="filter_dept_vp_type" class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">VP Type</label>
                        <div class="sppb-f relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-tags text-xs"></i></span>
                            <select id="filter_dept_vp_type" class="dept-filter-select2" style="width:100%">
                            <option value="">All VP Types</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="relative overflow-hidden">
                    <table id="tableDept" class="w-full border-separate border-spacing-0 text-sm" style="width:100%">
                        <thead>
                            <tr class="bg-gray-50/70 dark:bg-white/[0.02]">
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">No</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Company</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Warehouse ID</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Activity Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Department</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">VP Type</th>
                                <th class="px-4 py-3 text-left text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-4 py-3 text-right text-[11px] font-medium uppercase tracking-[0.08em] text-gray-500 dark:text-gray-400">Action</th>
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
    <div id="createWhsModal" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="vpl-dialog max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-plus"></i></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Add Warehouse</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Create VPL warehouse master data.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('#createWhsModal', false)" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <form id="createWhsForm">
                @csrf
                <div class="grid grid-cols-1 gap-4 bg-gray-50 p-5 dark:bg-gray-900/60 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" required class="whs-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse ID <span class="text-red-500">*</span></label>
                        <input type="text" name="whs_id" required
                            class="h-10 px-3 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 uppercase"
                            placeholder="e.g. WH001">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">VP Type</label>
                        <select name="vp_type" class="whs-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" onclick="toggleModal('#createWhsModal', false)"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300 disabled:opacity-60">Save Warehouse</button>
                </div>
            </form>

        </div>
    </div>

    {{-- MODAL: Edit Warehouse --}}
    <div id="editWhsModal" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="vpl-dialog max-h-[92vh] w-full max-w-lg overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Edit Warehouse</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Update VPL warehouse master data.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('#editWhsModal', false)" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <form id="editWhsForm">
                @csrf
                <input type="hidden" name="id" id="edit_whs_db_id">

                <div class="grid grid-cols-1 gap-4 bg-gray-50 p-5 dark:bg-gray-900/60 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" id="edit_whs_cpnyid" required class="whs-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse ID</label>
                        <input type="text" name="whs_id" id="edit_whs_id" readonly
                            class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-300 uppercase">
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">VP Type</label>
                        <select name="vp_type" id="edit_whs_vp_type" class="whs-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" onclick="toggleModal('#editWhsModal', false)"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300 disabled:opacity-60">Update Warehouse</button>
                </div>
            </form>

        </div>
    </div>

    {{-- ============================================================ --}}
    {{-- MODAL: Create Warehouse Dept --}}
    {{-- ============================================================ --}}
    <div id="createDeptModal" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="vpl-dialog max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-plus"></i></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Add Warehouse Dept</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Configure department access for a warehouse.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('#createDeptModal', false)" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <form id="createDeptForm">
                @csrf
                <div class="grid grid-cols-1 gap-4 bg-gray-50 p-5 dark:bg-gray-900/60 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" required class="dept-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse ID <span class="text-red-500">*</span></label>
                        <select name="whs_id" id="create_dept_whs_id" required class="dept-select2">
                            <option value="">Select Warehouse</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Activity Type</label>
                        <select name="activity_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="RECEIVE">Receive</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="TRANSFER_RECEIVE">Transfer Receive</option>
                            <option value="USAGE">Usage</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">VP Type</label>
                        <select name="vp_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Applies to this dept access rule only — not tied to a specific Warehouse ID row.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <select name="department_id" class="dept-select2">
                            <option value="">— select department —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_id }} - {{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" onclick="toggleModal('#createDeptModal', false)"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300 disabled:opacity-60">Save</button>
                </div>
            </form>

        </div>
    </div>

    {{-- MODAL: Edit Warehouse Dept --}}
    <div id="editDeptModal" class="vpl-pop fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="vpl-dialog max-h-[92vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">

            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Edit Warehouse Dept</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Update department access configuration.</p>
                    </div>
                </div>
                <button type="button" onclick="toggleModal('#editDeptModal', false)" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>

            <form id="editDeptForm">
                @csrf
                <input type="hidden" name="id" id="edit_dept_db_id">

                <div class="grid grid-cols-1 gap-4 bg-gray-50 p-5 dark:bg-gray-900/60 md:grid-cols-2">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company <span class="text-red-500">*</span></label>
                        <select name="cpnyid" id="edit_dept_cpnyid" required class="dept-select2">
                            <option value="">Select Company</option>
                            @foreach ($usercpny as $c)
                                <option value="{{ $c->cpny_id }}">{{ $c->cpny_id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Warehouse ID <span class="text-red-500">*</span></label>
                        <select name="whs_id" id="edit_dept_whs_id" required class="dept-select2">
                            <option value="">Select Warehouse</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Activity Type</label>
                        <select name="activity_type" id="edit_dept_activity_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="RECEIVE">Receive</option>
                            <option value="TRANSFER">Transfer</option>
                            <option value="TRANSFER_RECEIVE">Transfer Receive</option>
                            <option value="USAGE">Usage</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">VP Type</label>
                        <select name="vp_type" id="edit_dept_vp_type" class="dept-select2">
                            <option value="">All</option>
                            <option value="V">Voucher</option>
                            <option value="P">Product</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-400">Applies to this dept access rule only — not tied to a specific Warehouse ID row.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <select name="department_id" id="edit_dept_department_id" class="dept-select2">
                            <option value="">— select department —</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->department_id }}">{{ $dept->department_id }} - {{ $dept->department_name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
                    <button type="button" onclick="toggleModal('#editDeptModal', false)"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Cancel</button>
                    <button type="submit"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300 disabled:opacity-60">Update</button>
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
                .removeClass('active-tab bg-blue-600 text-white shadow-sm')
                .addClass('bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900');

            $(this)
                .removeClass('bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900')
                .addClass('active-tab bg-blue-600 text-white shadow-sm');

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

            // Warehouse Dept filter bar
            $('.dept-filter-select2').select2({ width: '100%', allowClear: true, placeholder: 'All', dropdownCssClass: 'sppb-s2-dropdown' });
            $('.dept-filter-select2').on('change', function () {
                tableDept.ajax.reload(null, false);
            });

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
                            ? '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 px-2.5 py-0.5 text-xs font-semibold">Voucher</span>'
                            : d === 'P'
                            ? '<span class="inline-flex items-center rounded-full border border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce] dark:border-[#a855f7]/30 dark:bg-[#a855f7]/10 dark:text-[#d8b4fe] px-2.5 py-0.5 text-xs font-semibold">Product</span>'
                            : (d ?? '-')
                    },
                    { data: 'status_badge', orderable: false, searchable: false },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-right',
                        render: function (data, type, row) {
                            const id      = String(row.id).replace(/'/g, "\\'");
                            const isActive = row.status === 'A';
                            const toggleColor = isActive
                                ? 'border-red-100 bg-red-50 text-red-600 hover:border-red-600 hover:bg-red-600'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-600 hover:bg-emerald-600';
                            const toggleIcon = isActive ? 'fa-ban' : 'fa-circle-check';
                            const toggleLabel = isActive ? 'Deactivate' : 'Activate';
                            return `<div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="editWarehouse('${id}')" class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500"><i class="fa-solid fa-pen-to-square"></i>Edit</button>
                                <button type="button" onclick="toggleWarehouse('${id}', ${isActive ? 0 : 1})" class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md min-w-[104px] ${toggleColor}"><i class="fa-solid ${toggleIcon}"></i>${toggleLabel}</button>
                            </div>`;
                        }
                    }
                ],
                searchPlaceholder: 'Search warehouse...'
            }));

            // Warehouse Dept DataTable
            tableDept = $('#tableDept').DataTable(baseTableConfig({
                ajax: {
                    url: routes.dept.json,
                    data: d => {
                        d.filter_whs_id        = $('#filter_dept_whs_id').val()        || '';
                        d.filter_activity_type = $('#filter_dept_activity_type').val() || '';
                        d.filter_department_id = $('#filter_dept_department_id').val() || '';
                        d.filter_vp_type       = $('#filter_dept_vp_type').val()       || '';
                    },
                },
                columns: [
                    { data: 'DT_RowIndex',   orderable: false, searchable: false, width: '5%' },
                    { data: 'cpnyid',         name: 'cpnyid' },
                    { data: 'whs_id',         name: 'whs_id' },
                    { data: 'activity_type',  name: 'activity_type' },
                    { data: 'department_id',  name: 'department_id' },
                    {
                        data: 'vp_type', name: 'vp_type',
                        render: d => d === 'V'
                            ? '<span class="inline-flex items-center rounded-full border border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 px-2.5 py-0.5 text-xs font-semibold">Voucher</span>'
                            : d === 'P'
                            ? '<span class="inline-flex items-center rounded-full border border-[#e9d5ff] bg-[#faf5ff] text-[#7e22ce] dark:border-[#a855f7]/30 dark:bg-[#a855f7]/10 dark:text-[#d8b4fe] px-2.5 py-0.5 text-xs font-semibold">Product</span>'
                            : (d ?? '-')
                    },
                    { data: 'status_badge',   orderable: false, searchable: false },
                    {
                        data: null, orderable: false, searchable: false, className: 'text-right',
                        render: function (data, type, row) {
                            const id      = String(row.id).replace(/'/g, "\\'");
                            const isActive = row.status === 'A';
                            const toggleColor = isActive
                                ? 'border-red-100 bg-red-50 text-red-600 hover:border-red-600 hover:bg-red-600'
                                : 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:border-emerald-600 hover:bg-emerald-600';
                            const toggleIcon = isActive ? 'fa-ban' : 'fa-circle-check';
                            const toggleLabel = isActive ? 'Deactivate' : 'Activate';
                            return `<div class="flex items-center justify-end gap-2">
                                <button type="button" onclick="editDept('${id}')" class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md border-amber-200 bg-amber-50 text-amber-800 hover:border-amber-500 hover:bg-amber-500"><i class="fa-solid fa-pen-to-square"></i>Edit</button>
                                <button type="button" onclick="toggleDept('${id}', ${isActive ? 0 : 1})" class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border px-3 text-xs font-semibold transition-all duration-200 hover:-translate-y-0.5 hover:text-white hover:shadow-md min-w-[104px] ${toggleColor}"><i class="fa-solid ${toggleIcon}"></i>${toggleLabel}</button>
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
            return { processing: true, serverSide: true, responsive: true, autoWidth: false, pageLength: 10, ajax, columns, order, dom: '<"dt-toolbar"l f>rtip', language: { search: '', searchPlaceholder } };
        }

        function showLoading(title = 'Processing...') { Swal.fire({ title, allowOutsideClick: false, didOpen: () => Swal.showLoading() }); }
        function showSuccess(message) { Swal.fire({ icon: 'success', title: 'Success', text: message, timer: 1500, showConfirmButton: false }); }
        function showError(message = 'Something went wrong') { Swal.fire({ icon: 'error', title: 'Error', text: message }); }
    </script>

</x-app-layout>
