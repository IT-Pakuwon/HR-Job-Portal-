<x-app-layout>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<style>
.modal-panel { backface-visibility: hidden; }
.modal-scroll { scrollbar-width: thin; }
.select2-container .select2-selection--single { height: 42px !important; border-radius: 8px !important; border-color: #e2e8f0 !important; display: flex; align-items: center; }
.select2-container .select2-selection--single .select2-selection__rendered { line-height: 42px !important; padding: 0 12px !important; }
.select2-container .select2-selection--single .select2-selection__arrow { height: 40px !important; }
.apv-badge { display:inline-block; padding:2px 8px; border-radius:999px; font-size:11px; font-weight:600; }
.status-filter.active-card .status-card { box-shadow: 0 0 0 2px #6366f1; }
/* DataTable wrapper padding */
.dataTables_wrapper .dataTables_length,
.dataTables_wrapper .dataTables_filter { padding: 14px 18px; }
.dataTables_wrapper .dataTables_info,
.dataTables_wrapper .dataTables_paginate { padding: 12px 18px; }
</style>

<div class="max-w-9xl mx-auto w-full p-2">

{{-- ======================================================== --}}
{{-- STATUS COUNT CARDS --}}
{{-- ======================================================== --}}
<div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

    {{-- All --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter active-card group block h-full" data-status="ALL">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-400 bg-slate-200/20 p-3 text-slate-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📦</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">All</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['all'] }}</p>
            </div>
        </a>
    </button>

    {{-- On Progress --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter group block h-full" data-status="P">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-yellow-500 bg-yellow-100/30 p-3 text-yellow-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95 dark:border-yellow-500 dark:text-yellow-400 dark:hover:bg-yellow-500/20">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">On Progress</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['progress'] }}</p>
            </div>
        </a>
    </button>

    {{-- Completed --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter group block h-full" data-status="C">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-green-600 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">Completed</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['completed'] }}</p>
            </div>
        </a>
    </button>

    {{-- Hold / Revise --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter group block h-full" data-status="D">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-500 bg-blue-100/30 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500/20">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">Hold / Revise</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['hold'] }}</p>
            </div>
        </a>
    </button>

    {{-- Rejected --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter group block h-full" data-status="R">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-red-600 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">Rejected</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['rejected'] }}</p>
            </div>
        </a>
    </button>

    {{-- Cancelled --}}
    <button type="button" class="text-left">
        <a href="#" class="status-filter group block h-full" data-status="X">
            <div class="status-card flex h-full items-center gap-3 rounded-lg border border-slate-500 bg-slate-200/20 p-3 text-slate-500 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95 dark:border-slate-400 dark:text-slate-400 dark:hover:bg-slate-700/30">
                <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">❌</div>
                <div class="flex min-w-0 flex-grow flex-col leading-tight">
                    <p class="break-words text-sm font-medium">Cancelled</p>
                </div>
                <p class="shrink-0 text-base font-bold">{{ $counts['cancelled'] }}</p>
            </div>
        </a>
    </button>

</div>

{{-- ======================================================== --}}
{{-- DATATABLE PANEL --}}
{{-- ======================================================== --}}
<div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

    <div class="flex flex-col gap-4 border-b border-gray-100 px-5 py-2 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h2 class="text-base font-semibold tracking-tight text-gray-800 dark:text-gray-100">
                Receive Product / Voucher
            </h2>
        </div>
        <div class="flex items-center gap-3">
            <button id="openCreateBtn" type="button"
                class="inline-flex h-10 items-center justify-center rounded-lg bg-indigo-600 px-5 text-sm font-medium text-white transition hover:bg-indigo-500">
                <i class="fa-solid fa-plus mr-2 text-xs"></i> New Receive
            </button>
        </div>
    </div>

    <div class="relative overflow-hidden">
        <table id="receiveTable" class="w-full min-w-full border-separate border-spacing-0 text-sm" style="width:100%">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-medium">Doc No</th>
                    <th class="px-4 py-3 text-left font-medium">Date</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Dept</th>
                    <th class="px-4 py-3 text-left font-medium">V/P Type</th>
                    <th class="px-4 py-3 text-left font-medium">Source Type</th>
                    <th class="px-4 py-3 text-left font-medium">Remark</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

</div>{{-- end max-w-9xl wrapper --}}


{{-- ======================================================== --}}
{{-- CREATE MODAL --}}
{{-- ======================================================== --}}
<div id="createModal" class="fixed inset-0 z-[50] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

        {{-- Header --}}
        <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
            <div>
                <h2 class="text-sm font-bold text-slate-900 dark:text-white">Create Receive</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">New product / voucher receipt form.</p>
            </div>
            <button type="button" id="closeCreateModal"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05]">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="bg-slate-50 p-6 dark:bg-[#0b1220]">
            <form id="createForm" class="space-y-6" enctype="multipart/form-data">
                @csrf

                {{-- Basic Info --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Basic Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Company <span class="text-red-500">*</span></label>
                            <select name="cpnyid" id="c_cpnyid" class="w-full select2-create" required>
                                @foreach($usercpny as $p)
                                    <option value="{{ $p->cpny_id }}" {{ $p->cpny_id == $usercpny2?->cpny_id ? 'selected' : '' }}>{{ $p->cpny_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Department <span class="text-red-500">*</span></label>
                            <select name="department" id="c_department" class="w-full select2-create" required>
                                @foreach($userdept as $p)
                                    <option value="{{ $p->department_id }}" {{ $p->department_id == $userdept2?->department_id ? 'selected' : '' }}>{{ $p->department_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Voucher / Product Type <span class="text-red-500">*</span></label>
                            <select name="vp_type" id="c_vp_type" class="w-full select2-create" required>
                                <option value=""></option>
                                <option value="voucher">Voucher</option>
                                <option value="product">Product</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Source Info --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Source Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Source of Receive <span class="text-red-500">*</span></label>
                            <select name="receive_type" id="c_receive_type" class="w-full select2-create" required>
                                <option value=""></option>
                                @foreach($sourceOptions as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Department of Receive <span class="text-red-500">*</span></label>
                            <select name="source_receive_dept" id="c_source_dept" class="w-full select2-create" required>
                                <option value=""></option>
                                <option value="CASUALLEASING">CASUALLEASING</option>
                                <option value="LEASING">LEASING</option>
                                <option value="PROMOTION">PROMOTION</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Remark</label>
                        <textarea name="receive_remark" rows="2" placeholder="Enter remarks..."
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-white"></textarea>
                    </div>
                </div>

                {{-- Detail Lines --}}
                {{-- Warning: department not registered for receive --}}
                <div id="c_whs_warning" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300">
                    <strong>⚠ Cannot Submit</strong> — Your department is not registered as a destination warehouse for this company and type. Please contact the administrator.
                </div>

                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Receive Details</h3>
                        <button type="button" id="c_addRow"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm" id="c_detailTable">
                            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:28%">Product <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:15%">Tenant</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:13%">Qty <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:7%">UOM</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:13%">Expired Date</th>
                                    <th class="c-whs-th px-4 py-2.5 text-left text-xs font-semibold uppercase text-slate-600" style="width:22%">Dest. WHS <span class="text-red-500">*</span></th>
                                    <th class="px-4 py-2.5" style="width:8%"></th>
                                </tr>
                            </thead>
                            <tbody id="c_detailBody">
                                <tr id="c_row_0">
                                    <td class="px-4 py-2">
                                        <select name="addmore[0][product_name]" class="w-full c-product-sel" style="min-width:200px">
                                            <option value="">Select Product</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="addmore[0][qty]" min="1" placeholder="Qty"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="c-uom-display block rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="date" name="addmore[0][expired_date]"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
                                    </td>
                                    <td class="c-whs-td px-4 py-2">
                                        <select name="addmore[0][whs_id]" class="w-full c-whs-sel" style="min-width:160px">
                                            <option value="">Select WHS</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Attachments</h3>
                        <button type="button" id="c_addAttach"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add File
                        </button>
                    </div>
                    <div class="p-4">
                        <table class="min-w-full text-sm" id="c_attachTable">
                            <tbody id="c_attachBody">
                                <tr id="c_attach_0">
                                    <td class="py-1 pr-2">
                                        <input type="file" name="attachment[]"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                                    </td>
                                    <td class="py-1 pl-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="closeCreateModalFooter"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                    Close
                </button>
                <button type="button" id="submitCreateBtn"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Submit Approval
                </button>
            </div>
        </div>

    </div>
</div>


{{-- ======================================================== --}}
{{-- VIEW MODAL --}}
{{-- ======================================================== --}}
<div id="viewModal" class="fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

        {{-- Header --}}
        <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
            <div>
                <h2 id="v_title" class="font-semibold text-slate-800 dark:text-white">Receive Detail</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Receipt information & approval workflow.</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Toggle Discussion Panel --}}
                <button type="button" id="v_msgToggleBtn"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                    <i class="fa-regular fa-comments text-base"></i>
                </button>
                <button type="button" id="closeViewModal"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05]">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
        </div>

        {{-- Body --}}
        <div class="grid grid-cols-1 gap-4 bg-slate-50 p-4 dark:bg-[#0b1220] lg:grid-cols-[1.2fr_0.8fr]">

            {{-- LEFT --}}
            <div class="space-y-4">

                {{-- Receive Info --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Received By</div>
                            <div id="v_user" class="mt-2 text-base font-semibold text-slate-900 dark:text-white"></div>
                        </div>
                        <div id="v_status_badge"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-4 md:grid-cols-3">
                        <div><div class="text-xs text-slate-500">Date</div><div id="v_date" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                        <div><div class="text-xs text-slate-500">Company</div><div id="v_cpnyid" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                        <div><div class="text-xs text-slate-500">Department</div><div id="v_dept" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                        <div><div class="text-xs text-slate-500">V/P Type</div><div id="v_vp_type" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                        <div><div class="text-xs text-slate-500">Source of Receive</div><div id="v_receive_type" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                        <div><div class="text-xs text-slate-500">Dept of Receive</div><div id="v_source_dept" class="mt-1 text-sm font-medium text-slate-800 dark:text-slate-100"></div></div>
                    </div>
                    <div class="border-t border-slate-100 px-4 py-3 dark:border-white/10">
                        <div class="text-xs text-slate-500">Remark</div>
                        <div id="v_remark" class="mt-1 text-sm text-slate-700 dark:text-slate-200"></div>
                    </div>
                </div>

                {{-- Detail Table --}}
                <div class="overflow-hidden rounded-lg border border-blue-100 bg-blue-50 dark:border-blue-500/20 dark:bg-blue-500/10">
                    <div class="border-b border-blue-100 px-5 py-2 dark:border-blue-500/20">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-300">Receive Details</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-blue-100 dark:border-blue-500/20">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">Product ID</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">Product Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">Tenant</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">Expired</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">UOM</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-blue-700 dark:text-blue-300">WHS</th>
                                </tr>
                            </thead>
                            <tbody id="v_detailBody" class="divide-y divide-blue-100 dark:divide-blue-500/20"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Attachments</h3>
                    </div>
                    <div id="v_attachBody" class="divide-y divide-slate-100 dark:divide-white/10 empty:p-4 empty:text-sm empty:text-slate-400"></div>
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="space-y-4">

                {{-- Revision Reason (shown when status = D) --}}
                <div id="v_reviseReasonWrapper" class="hidden overflow-hidden rounded-lg border border-yellow-200 bg-yellow-50 dark:border-yellow-500/20 dark:bg-yellow-500/10">
                    <div class="border-b border-yellow-100 px-5 py-2 dark:border-yellow-500/20">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-300">Revision Reason</h3>
                    </div>
                    <div id="v_revise_reason" class="p-5 text-sm leading-relaxed text-yellow-900 dark:text-yellow-100"></div>
                </div>

                <div class="overflow-hidden">
                    {{-- Status banner + action buttons (side-by-side, matching Voucher Taxi layout) --}}
                    <div class="flex items-center gap-2">
                        <div id="v_statusBanner" class="mb-4 flex w-full items-center gap-2"></div>
                        <div id="v_approvalActions" class="mb-4 flex hidden w-full items-center justify-between gap-2">
                            <button type="button" id="v_approveBtn"
                                class="flex-1 rounded-lg bg-emerald-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-emerald-400">
                                <i class="fa-solid fa-check mr-1"></i> Approve
                            </button>
                            <button type="button" id="v_reviseBtn"
                                class="flex-1 rounded-lg bg-yellow-400 px-4 py-2 text-xs font-semibold text-black transition hover:bg-yellow-300">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Revise
                            </button>
                            <button type="button" id="v_rejectBtn"
                                class="flex-1 rounded-lg bg-red-500 px-4 py-2 text-xs font-semibold text-white transition hover:bg-red-400">
                                <i class="fa-solid fa-xmark mr-1"></i> Reject
                            </button>
                        </div>
                    </div>
                    {{-- Approval timeline rendered here by VplReceiveHelper.renderTimeline() --}}
                    <div id="v_approvalBody"></div>
                </div>


            </div>
        </div>

        {{-- Discussion / Messages panel — absolute inside modal so it overlaps the right column --}}
        <div id="v_discussionPanel"
            class="absolute bottom-16 right-0 z-30 hidden w-[380px] overflow-hidden rounded-lg border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3 dark:border-white/10">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200">Messages</h3>
                <button type="button" id="v_discussionClose">
                    <i class="fa-solid fa-xmark text-slate-400 hover:text-slate-700 dark:hover:text-white"></i>
                </button>
            </div>

            <div id="v_msgBody" class="h-[360px] space-y-4 overflow-y-auto bg-slate-50 p-4 dark:bg-[#0b1220]"></div>

            <div class="border-t border-slate-200 p-3 dark:border-white/10">
                <div class="flex items-end gap-2">
                    <textarea id="v_msgInput" rows="1" placeholder="Write message..."
                        class="min-h-[46px] flex-1 resize-none rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 transition placeholder:text-slate-400 focus:border-slate-400 focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-500"></textarea>
                    <button type="button" id="v_msgSend"
                        class="h-11 w-11 rounded-lg bg-slate-900 text-white hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
            <div class="flex items-center justify-between">
                <button type="button" id="closeViewModalFooter"
                    class="text-sm text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white">Close</button>
                <div class="flex items-center gap-3">
                    <button type="button" id="v_cancelBtn"
                        class="hidden rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-500">
                        Cancel Document
                    </button>
                    <button type="button" id="v_editBtn"
                        class="hidden rounded-lg bg-slate-900 px-5 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                        Edit
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- ======================================================== --}}
{{-- EDIT MODAL --}}
{{-- ======================================================== --}}
<div id="editModal" class="fixed inset-0 z-[70] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-slate-900/60 opacity-0 transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-lg border border-slate-200 bg-white opacity-0 shadow-2xl transition-all duration-200 dark:border-white/10 dark:bg-[#0f172a]">

        {{-- Header --}}
        <div class="sticky top-0 z-20 flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
            <div>
                <h2 id="e_title" class="text-sm font-bold text-slate-900 dark:text-white">Edit Receive</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Modify and resubmit for approval.</p>
            </div>
            <button type="button" id="closeEditModal"
                class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05]">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- Body --}}
        <div class="bg-slate-50 p-6 dark:bg-[#0b1220]">
            <form id="editForm" class="space-y-6" enctype="multipart/form-data">
                @csrf

                {{-- Basic (read-only) --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Basic Information <span class="text-slate-400 font-normal normal-case text-[11px]">(read-only)</span></h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Company</label>
                            <input type="text" id="e_cpnyid_display" readonly class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">
                            <input type="hidden" name="cpnyid" id="e_cpnyid">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Department</label>
                            <input type="text" id="e_dept_display" readonly class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">
                            <input type="hidden" name="department" id="e_dept">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">V/P Type</label>
                            <input type="text" id="e_vp_type_display" readonly class="h-11 w-full rounded-lg border border-slate-200 bg-slate-100 px-4 text-sm dark:border-white/10 dark:bg-white/[0.04]">
                            <input type="hidden" name="vp_type" id="e_vp_type">
                        </div>
                    </div>
                </div>

                {{-- Editable Source --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Source Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-2">
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Source of Receive <span class="text-red-500">*</span></label>
                            <select name="receive_type" id="e_receive_type" class="w-full select2-edit" required>
                                <option value=""></option>
                                @foreach($sourceOptions as $src)
                                    <option value="{{ $src }}">{{ $src }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Dept of Receive <span class="text-red-500">*</span></label>
                            <select name="source_receive_dept" id="e_source_dept" class="w-full select2-edit" required>
                                <option value=""></option>
                                <option value="CASUALLEASING">CASUALLEASING</option>
                                <option value="LEASING">LEASING</option>
                                <option value="PROMOTION">PROMOTION</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-6 pb-6">
                        <label class="mb-2 block text-sm font-medium text-slate-700 dark:text-slate-200">Remark</label>
                        <textarea name="receive_remark" id="e_remark" rows="2"
                            class="w-full rounded-lg border border-slate-200 px-4 py-2.5 text-sm dark:border-white/10 dark:bg-white/[0.03] dark:text-white"></textarea>
                    </div>
                </div>

                {{-- Existing Details --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Existing Details</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">Product</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">Tenant</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">Expired</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">Qty</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">UOM</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600">WHS</th>
                                    <th class="px-4 py-2.5 w-16"></th>
                                </tr>
                            </thead>
                            <tbody id="e_existDetailBody" class="divide-y divide-slate-100 dark:divide-white/10"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Add New Detail Lines --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="flex items-center justify-between border-b border-slate-200 px-6 py-3 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Add New Lines</h3>
                        <button type="button" id="e_addRow"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add Row
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm" id="e_detailTable">
                            <thead class="bg-slate-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:28%">Product</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:15%">Tenant</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:13%">Qty</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:7%">UOM</th>
                                    <th class="px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:13%">Expired Date</th>
                                    <th class="e-whs-th px-4 py-2.5 text-left text-xs font-semibold text-slate-600" style="width:22%">Dest. WHS</th>
                                    <th class="px-4 py-2.5 w-8"></th>
                                </tr>
                            </thead>
                            <tbody id="e_detailBody">
                                <tr id="e_row_0">
                                    <td class="px-4 py-2">
                                        <select name="addmore[0][product_name]" class="w-full e-product-sel" style="min-width:200px">
                                            <option value="">Select Product</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="number" name="addmore[0][qty]" min="1" placeholder="Qty"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
                                    </td>
                                    <td class="px-4 py-2">
                                        <span class="e-uom-display block rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-500 dark:bg-white/[0.04] dark:text-slate-400">—</span>
                                    </td>
                                    <td class="px-4 py-2">
                                        <input type="date" name="addmore[0][expired_date]"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm dark:border-white/10 dark:bg-[#0b1220]">
                                    </td>
                                    <td class="e-whs-td px-4 py-2">
                                        <select name="addmore[0][whs_id]" class="w-full e-whs-sel" style="min-width:160px">
                                            <option value="">Select WHS</option>
                                        </select>
                                    </td>
                                    <td class="px-4 py-2"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Existing Attachments --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Existing Attachments</h3>
                    </div>
                    <div id="e_existAttachBody" class="divide-y divide-slate-100 dark:divide-white/10"></div>
                </div>

                {{-- New Attachments --}}
                <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-2 dark:border-white/10">
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Add Attachments</h3>
                        <button type="button" id="e_addAttach"
                            class="inline-flex items-center gap-1 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold hover:bg-slate-50 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add File
                        </button>
                    </div>
                    <div class="p-4">
                        <table class="min-w-full text-sm">
                            <tbody id="e_attachBody">
                                <tr id="e_attach_0">
                                    <td class="py-1 pr-2">
                                        <input type="file" name="attachment[]"
                                            class="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-sm dark:border-white/10">
                                    </td>
                                    <td class="py-1 pl-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </form>
        </div>

        {{-- Footer --}}
        <div class="sticky bottom-0 z-20 border-t border-slate-200 bg-white/95 px-5 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="closeEditModalFooter"
                    class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200">
                    Close
                </button>
                <button type="button" id="submitEditBtn"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-slate-900 px-5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-indigo-600 dark:hover:bg-indigo-500">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Resubmit Approval
                </button>
            </div>
        </div>

    </div>
</div>




{{-- ======================================================== --}}
{{-- SCRIPTS --}}
{{-- ======================================================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

{{-- Pass Laravel route URLs to external JS --}}
<script>
    // cspell:disable
    window.VplReceiveConfig = {
        base:       '{{ url("requestvp") }}',
        editBase:   '{{ url("editreceivevp") }}',
        store:      '{{ route("requestvp.store") }}',
        products:   '{{ route("requestvp.products") }}',
        warehouse:  '{{ route("requestvp.warehouse") }}',
        tenants:    '{{ route("requestvp.tenants") }}',
        prodDetail: '{{ route("requestvp.product-details") }}',
        delDetail:  '{{ route("requestvp.detail.delete") }}',
        delAttach:  '{{ route("requestvp.attachment.delete") }}',
    };
    // cspell:enable
</script>

{{-- Voucher Receive JS modules --}}
<script src="{{ asset('assets/js/voucher-receive/core.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/modal.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/helper.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/datalist.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/form.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/detail-modal.js') }}"></script>
<script src="{{ asset('assets/js/voucher-receive/init.js') }}"></script>


</x-app-layout>
