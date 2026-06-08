<x-app-layout>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">

    <style>
        :root {
            --am-bg: #f8fafc;
            --am-card: #ffffff;
            --am-border: #e2e8f0;
            --am-text: #0f172a;
            --am-muted: #64748b;
        }

        .dark {
            --am-bg: #020617;
            --am-card: #0f172a;
            --am-border: rgba(255, 255, 255, .08);
            --am-text: #f8fafc;
            --am-muted: #cbd5e1;
        }

        label.req::after {
            content: " *";
            color: #ef4444;
            font-weight: 700;
        }

        .swal2-container {
            z-index: 999999 !important;
        }

        .modal-scroll::-webkit-scrollbar {
            width: 8px;
        }

        .modal-scroll::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, .35);
            border-radius: 999px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter{
            padding:16px 18px;
        }

        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate{
            padding:14px 18px;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── Status Filter Cards ─────────────────────────────────────────── --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2">

            {{-- Unassigned --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="unassigned">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Unassigned</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $unassignedCount }}</p>
                    </div>
                </a>
            </button>

            {{-- Assigned --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="assigned">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-violet-700 bg-violet-200/20 p-3 text-violet-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-violet-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Assigned</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $assignedCount }}</p>
                    </div>
                </a>
            </button>

        </div>

        {{-- ── Filter Bar ──────────────────────────────────────────────────── --}}
        <div
            class="mt-4 rounded-xl border border-gray-200 bg-white px-5 py-4 shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">STTB</label>
                    <input type="text" id="filterSttb" placeholder="Search STTB…"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition placeholder:text-gray-400 focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100 dark:placeholder:text-gray-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">PO</label>
                    <input type="text" id="filterPo" placeholder="Search PO…"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition placeholder:text-gray-400 focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100 dark:placeholder:text-gray-500">
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Inventory</label>
                    <select id="filterInventory"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100">
                        <option value="">All Inventories</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Company</label>
                    <select id="filterCompany"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100">
                        <option value="">All Companies</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Department</label>
                    <select id="filterDept"
                        class="h-9 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100">
                        <option value="">All Departments</option>
                    </select>
                </div>

            </div>

            <div class="mt-3 flex items-center justify-end gap-2">

                <button type="button" id="resetFilter"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 transition hover:bg-gray-50 dark:border-white/10 dark:bg-white/4 dark:text-gray-300 dark:hover:bg-white/8">
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    Reset
                </button>

                <button type="button" id="applyFilter"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg bg-gray-900 px-4 text-sm font-medium text-white transition hover:bg-gray-700 dark:bg-blue-600 dark:hover:bg-blue-500">
                    <i class="fa-solid fa-filter text-xs"></i>
                    Apply
                </button>

                <button type="button" id="exportFilter"
                    class="inline-flex h-9 items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 dark:hover:bg-emerald-900/50">
                    <i class="fa-solid fa-file-arrow-down text-xs"></i>
                    Export
                </button>

            </div>
        </div>

        {{-- ── Table ───────────────────────────────────────────────────────── --}}
        <div id="mainTableWrapper"
            class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div
                class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 lg:flex-row lg:items-center lg:justify-between dark:border-white/[0.06]">

                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        Asset Management
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Click <strong>Assign</strong> on an unassigned item, or <strong>Edit</strong> to update an
                        existing assignment.
                    </p>
                </div>

            </div>

            <div class="relative overflow-hidden">

                <table id="assetTable" class="w-full p-4 min-w-full border-separate border-spacing-0 text-sm">

                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">STTB</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">PO</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">Inv. Code</th>
                            <th class="px-4 py-3 text-left font-medium">Inventory Name</th>
                            <th class="px-4 py-3 text-center font-medium">Unit #</th>
                            <th class="px-4 py-3 text-right font-medium">Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-center font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.04]"></tbody>

                </table>

            </div>

        </div>

        {{-- ── Assigned Table ─────────────────────────────────────────────── --}}
        <div id="assignedTableWrapper"
            class="mt-4 hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div
                class="flex items-center justify-between border-b border-gray-100 px-5 py-3 dark:border-white/[0.06]">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        Assigned Assets
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">All assets that have been assigned to
                        users.</p>
                </div>
            </div>

            <div class="relative overflow-hidden">
                <table id="assignedTable" class="w-full p-4 min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Doc ID</th>
                            <th class="px-4 py-3 text-left font-medium">Inv. Code</th>
                            <th class="px-4 py-3 text-left font-medium">Inventory Name</th>
                            <th class="px-4 py-3 text-center font-medium">Unit</th>
                            <th class="px-4 py-3 text-right font-medium">Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">User</th>
                            <th class="px-4 py-3 text-left font-medium">Warranty Period</th>
                            <th class="px-4 py-3 text-center font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.04]"></tbody>
                </table>
            </div>

        </div>

    </div>

    {{-- ── Detail Modal (See More) ─────────────────────────────────────────── --}}
    <div id="detailModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[90vh] w-full max-w-lg translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-6 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Asset Detail</h2>
                    <p id="detail_doc_id" class="mt-0.5 font-mono text-sm text-slate-500 dark:text-slate-400"></p>
                </div>
                <button type="button" id="closeDetailBtn"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="space-y-3 bg-slate-50 p-5 dark:bg-[#0b1220]">

                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-4 py-2 dark:border-white/10">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Inventory</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-3 p-4">
                        <div>
                            <p class="text-xs text-slate-400">Code</p>
                            <p id="detail_inventoryid"
                                class="mt-0.5 text-sm font-medium text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Name</p>
                            <p id="detail_inventory_descr" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">
                                —</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">STTB</p>
                            <p id="detail_receiptnbr" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">PO</p>
                            <p id="detail_ponbr" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-4 py-2 dark:border-white/10">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Assignment</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-3 p-4">
                        <div>
                            <p class="text-xs text-slate-400">Company</p>
                            <p id="detail_cpny" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Department</p>
                            <p id="detail_dept" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">User</p>
                            <p id="detail_user"
                                class="mt-0.5 text-sm font-medium text-slate-700 dark:text-slate-200">—</p>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-4 py-2 dark:border-white/10">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Warranty</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-3 p-4">
                        <div>
                            <p class="text-xs text-slate-400">Start Date</p>
                            <p id="detail_start_date" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">End Date</p>
                            <p id="detail_end_date" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Status</p>
                            <p id="detail_warranty_status" class="mt-0.5 text-sm">—</p>
                        </div>
                    </div>
                </div>

                <div
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-4 py-2 dark:border-white/10">
                        <h3
                            class="text-xs font-semibold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                            Asset Detail</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-3 p-4">
                        <div>
                            <p class="text-xs text-slate-400">Serial Number</p>
                            <p id="detail_serial_number"
                                class="mt-0.5 font-mono text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Notes</p>
                            <p id="detail_notes" class="mt-0.5 text-sm text-slate-700 dark:text-slate-200">—</p>
                        </div>
                    </div>
                </div>

            </div>

            <div
                class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                <div class="flex justify-end">
                    <button type="button" id="closeDetailBtnFooter"
                        class="inline-flex h-9 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/4 dark:text-slate-200 dark:hover:bg-white/8">
                        Close
                    </button>
                </div>
            </div>

        </div>

    </div>

    {{-- ── Assign / Edit Modal ──────────────────────────────────────────────── --}}
    <div id="assignModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

        <div
            class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70">
        </div>

        <div
            class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            {{-- Sticky Header --}}
            <div
                class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                <div>
                    <h2 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Assign Asset</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Computer KOMPUTER assignment form.</p>
                </div>
                <button type="button" id="closeModalBtn"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="assignForm">
                @csrf
                <input type="hidden" id="f_asset_id" name="asset_id">
                <input type="hidden" id="f_compound_id" name="compound_id">
                <input type="hidden" id="f_receipt_detail_id" name="receipt_detail_id">
                <input type="hidden" id="f_unit_num" name="unit_num">
                <input type="hidden" id="f_receiptnbr_h" name="receiptnbr">
                <input type="hidden" id="f_budget_cpny_id_h" name="budget_cpny_id">
                <input type="hidden" id="f_ponbr_h" name="ponbr">
                <input type="hidden" id="f_vendorid_h" name="vendorid">
                <input type="hidden" id="f_vendorname_h" name="vendorname">
                <input type="hidden" id="f_inventoryid_h" name="inventoryid">
                <input type="hidden" id="f_inventory_descr_h" name="inventory_descr">

                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    {{-- Receipt Info --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Receipt Info
                            </h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-5">

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">STTB</label>
                                <input type="text" id="info_sttb" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">PO</label>
                                <input type="text" id="info_po" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Vendor
                                    Code</label>
                                <input type="text" id="info_vendorid" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Vendor
                                    Name</label>
                                <input type="text" id="info_vendorname" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Inventory
                                    Code</label>
                                <input type="text" id="info_inventoryid" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label
                                    class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Inventory
                                    Name</label>
                                <input type="text" id="info_invdescr" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                        </div>
                    </div>

                    {{-- Assignment --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Assignment
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>
                                <label
                                    class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Company</label>
                                <select id="f_assign_cpny" name="assign_cpny_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Department</label>
                                <select id="f_assign_dept" name="assign_department_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Username</label>
                                <select id="f_assign_user" name="assign_username"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- Warranty --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Warranty
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>
                                <label
                                    class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Start
                                    Date</label>
                                <input type="date" id="f_start_date" name="start_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                            </div>

                            <div class="flex flex-col justify-end">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Has
                                    Expired?</label>
                                <label class="inline-flex h-11 cursor-pointer items-center gap-3">
                                    <input type="checkbox" id="f_has_expired" name="has_expired" value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-white/20">
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Warranty expired</span>
                                </label>
                            </div>

                            <div id="endDateWrapper" class="hidden">
                                <label
                                    class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">End
                                    Date</label>
                                <input type="date" id="f_end_date" name="end_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                            </div>

                        </div>
                    </div>

                    {{-- Asset Detail --}}
                    <div
                        class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3
                                class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Asset Detail
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Serial
                                    Number</label>
                                <input type="text" id="f_serial_number" name="serial_number"
                                    placeholder="e.g. SN-123456"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Notes
                                    / Comment</label>
                                <textarea id="f_notes" name="notes" rows="1" placeholder="Optional notes…"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500"></textarea>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- Sticky Footer --}}
                <div
                    class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" id="cancelModalBtn"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white transition hover:scale-[1.01] hover:bg-slate-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                            <i class="fa-solid fa-paper-plane text-xs"></i>
                            Save Assignment
                        </button>
                    </div>
                </div>

            </form>

        </div>

    </div>

    <script>
        window.amRoutes = {
            json:        "{{ route('asset-management.json') }}",
            store:       "{{ route('asset-management.store') }}",
            show:        "{{ route('asset-management.show', ['id' => '__ID__']) }}",
            update:      "{{ route('asset-management.update', ['id' => '__ID__']) }}",
            companies:   "{{ route('asset-management.companies') }}",
            departments: "{{ route('asset-management.departments') }}",
            users:       "{{ route('asset-management.users') }}",
            inventories: "{{ route('asset-management.inventories') }}",
            export:       "{{ route('asset-management.export') }}",
            assignedJson: "{{ route('asset-management.assigned-json') }}",
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/asset-management/helper.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/modal.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/datatable.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/form.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/core.js') }}"></script>

</x-app-layout>
