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
            --am-border: rgba(255,255,255,.08);
            --am-text: #f8fafc;
            --am-muted: #cbd5e1;
        }
        label.req::after { content: " *"; color: #ef4444; font-weight: 700; }
        .swal2-container { z-index: 999999 !important; }
        .modal-scroll::-webkit-scrollbar { width: 8px; }
        .modal-scroll::-webkit-scrollbar-thumb { background: rgba(148,163,184,.35); border-radius: 999px; }
        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter { padding: 16px 18px; }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { padding: 14px 18px; }

        /* Select2 overrides */
        .select2-container--open { z-index: 99999 !important; }
        .select2-container .select2-selection--single {
            height: 40px !important;
            border-radius: 8px !important;
            border-color: #e2e8f0 !important;
            display: flex;
            align-items: center;
        }
        .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 40px !important;
            padding-left: 12px !important;
            color: #374151;
        }
        .select2-container .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
        }
        .dark .select2-container .select2-selection--single {
            background-color: #0b1220 !important;
            border-color: rgba(255,255,255,.1) !important;
        }
        .dark .select2-container .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9;
        }
        .select2-dropdown {
            border-radius: 8px !important;
            border-color: #e2e8f0 !important;
            box-shadow: 0 4px 16px rgba(0,0,0,.12);
        }
        .select2-container--default .select2-results__option--highlighted {
            background-color: #1e293b !important;
        }
        /* Modal form select2 — full height */
        #assignModal .select2-container .select2-selection--single {
            height: 44px !important;
        }
        #assignModal .select2-container .select2-selection--single .select2-selection__rendered {
            line-height: 44px !important;
        }
        #assignModal .select2-container .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
        }
    </style>

    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- ── Status Filter Cards ─────────────────────────────────────────── --}}
        <div class="grid auto-rows-fr grid-cols-2 gap-4 sm:grid-cols-4">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $totalCount }}</p>
                    </div>
                </a>
            </button>

            {{-- Unassigned --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="unassigned">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
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

            {{-- Warranty Active --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="active">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-emerald-700 bg-emerald-200/20 p-3 text-emerald-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-emerald-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            <i class="fa-solid fa-shield-check"></i>
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Warranty Active</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $activeCount }}</p>
                    </div>
                </a>
            </button>

            {{-- Warranty Expired --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-status="expired">
                    <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Warranty Expired</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ $expiredCount }}</p>
                    </div>
                </a>
            </button>

        </div>

        {{-- ── Table ───────────────────────────────────────────────────────── --}}
        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">

                <div>
                    <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                        Asset Management — KOMPUTER
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Click <strong>Assign</strong> on an unassigned item, or <strong>Edit</strong> to update an existing assignment.
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <label class="whitespace-nowrap text-xs text-gray-500 dark:text-gray-400">Filter by inventory:</label>
                    <select id="filterInventory"
                        class="h-10 min-w-[220px] max-w-xs rounded-lg border border-gray-200 bg-white px-3 text-sm text-gray-700 transition focus:border-gray-400 focus:outline-none dark:border-white/10 dark:bg-[#0b1220] dark:text-gray-100">
                        <option value="">All Inventories</option>
                    </select>
                </div>

            </div>

            <div class="relative overflow-hidden">

                <table id="assetTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">

                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">STTB</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">PO</th>
                            <th class="px-4 py-3 text-left font-medium">Vendor</th>
                            <th class="px-4 py-3 text-left font-medium">Inv. Code</th>
                            <th class="px-4 py-3 text-left font-medium">Inventory Name</th>
                            <th class="px-4 py-3 text-center font-medium">Unit #</th>
                            <th class="px-4 py-3 text-right font-medium">Unit Cost</th>
                            <th class="px-4 py-3 text-left font-medium">Assigned To</th>
                            <th class="px-4 py-3 text-left font-medium">Warranty</th>
                            <th class="px-4 py-3 text-center font-medium">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-white/[0.04]"></tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- ── Assign / Edit Modal ──────────────────────────────────────────────── --}}
    <div id="assignModal" class="fixed inset-0 z-[9999] hidden items-center justify-center p-4">

        <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>

        <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-2xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

            {{-- Sticky Header --}}
            <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
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
                <input type="hidden" id="f_asset_id"          name="asset_id">
                <input type="hidden" id="f_compound_id"       name="compound_id">
                <input type="hidden" id="f_receipt_detail_id" name="receipt_detail_id">
                <input type="hidden" id="f_unit_num"          name="unit_num">
                <input type="hidden" id="f_receiptnbr_h"      name="receiptnbr">
                <input type="hidden" id="f_budget_cpny_id_h"  name="budget_cpny_id">
                <input type="hidden" id="f_ponbr_h"           name="ponbr">
                <input type="hidden" id="f_vendorid_h"        name="vendorid">
                <input type="hidden" id="f_vendorname_h"      name="vendorname">
                <input type="hidden" id="f_inventoryid_h"     name="inventoryid">
                <input type="hidden" id="f_inventory_descr_h" name="inventory_descr">

                <div class="space-y-4 bg-slate-50 p-4 dark:bg-[#0b1220]">

                    {{-- Receipt Info --}}
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Receipt Info
                            </h3>
                        </div>
                        <div class="grid grid-cols-2 gap-4 p-5">

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">STTB</label>
                                <input type="text" id="info_sttb" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">PO</label>
                                <input type="text" id="info_po" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Vendor Code</label>
                                <input type="text" id="info_vendorid" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Vendor Name</label>
                                <input type="text" id="info_vendorname" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Inventory Code</label>
                                <input type="text" id="info_inventoryid" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-500 dark:text-slate-400">Inventory Name</label>
                                <input type="text" id="info_invdescr" readonly
                                    class="h-9 w-full rounded-lg border border-slate-200 bg-slate-100 px-3 text-sm text-slate-700 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-300">
                            </div>

                        </div>
                    </div>

                    {{-- Assignment --}}
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Assignment
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Company</label>
                                <select id="f_assign_cpny" name="assign_cpny_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Department</label>
                                <select id="f_assign_dept" name="assign_department_id"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Username</label>
                                <select id="f_assign_user" name="assign_username"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                                    <option value="">— Choose —</option>
                                </select>
                            </div>

                        </div>
                    </div>

                    {{-- Warranty --}}
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Warranty
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">

                            <div>
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Start Date</label>
                                <input type="date" id="f_start_date" name="start_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                            </div>

                            <div class="flex flex-col justify-end">
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Has Expired?</label>
                                <label class="inline-flex h-11 cursor-pointer items-center gap-3">
                                    <input type="checkbox" id="f_has_expired" name="has_expired" value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900 dark:border-white/20">
                                    <span class="text-sm text-slate-600 dark:text-slate-300">Warranty expired</span>
                                </label>
                            </div>

                            <div id="endDateWrapper" class="hidden">
                                <label class="req mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">End Date</label>
                                <input type="date" id="f_end_date" name="end_date"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100">
                            </div>

                        </div>
                    </div>

                    {{-- Asset Detail --}}
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                        <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">
                                Asset Detail
                            </h3>
                        </div>
                        <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Serial Number</label>
                                <input type="text" id="f_serial_number" name="serial_number" placeholder="e.g. SN-123456"
                                    class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500">
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Notes / Comment</label>
                                <textarea id="f_notes" name="notes" rows="1" placeholder="Optional notes…"
                                    class="w-full rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500"></textarea>
                            </div>

                        </div>
                    </div>

                </div>

                {{-- Sticky Footer --}}
                <div class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
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
        };
    </script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('assets/js/asset-management/helper.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/modal.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/datatable.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/form.js') }}"></script>
    <script src="{{ asset('assets/js/asset-management/core.js') }}"></script>

</x-app-layout>
