<x-app-layout>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<meta name="csrf-token" content="{{ csrf_token() }}">

<x-spp-list-styles />
<x-vpl-styles />

<div class="max-w-9xl mx-auto w-full p-2">

{{-- ======================================================== --}}
{{-- STATUS COUNT CARDS --}}
{{-- ======================================================== --}}
@php
    $vplCompanyAccess = $user->hasVplCompanyAccess();
    $cards = [
        [['data-status' => 'JOBLIST'], 'Job List', '📋', 'pink', $counts['joblist'], true, null, 'active-card'],
        [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $counts['progress'], false],
        [['data-status' => 'C'], 'Completed', '✅', 'green', $counts['completed'], false],
        [['data-status' => 'D'], 'Hold / Revise', '✏️', 'blue', $counts['hold'], false],
        [['data-status' => 'R'], 'Rejected', '⛔', 'red', $counts['rejected'], false],
        [['data-status' => 'X'], 'Cancelled', '❌', 'all', $counts['cancelled'], false],
    ];
    if ($user->isPrimaryAdmin()) {
        $cards[] = [['data-status' => 'ADMINALL'], 'Settlement All', '🌐', 'purple', $counts['admin_all'] ?? 0, false];
    }
    if ($vplCompanyAccess) {
        $cards[] = [['data-status' => 'COMPANYALL'], 'All Settlement', '🏢', 'sky', $counts['company_all'] ?? 0, false];
    }
@endphp
<x-status-filter-cards :custom="$cards" />

{{-- ======================================================== --}}
{{-- DATATABLE PANEL --}}
{{-- ======================================================== --}}
<div class="dt-pretty mt-3 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">

    <div class="flex flex-col gap-4 rounded-t-xl border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06] lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-file-invoice-dollar"></i></span>
            <div>
                <h2 id="panelTitle" class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">
                    Settlement Product / Voucher
                </h2>
                <p id="jobListSubtitle" class="hidden text-xs text-gray-500 dark:text-gray-400">Completed Usage documents (non-Customer Service) still waiting on a settlement — click Settle to create one.</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($user->isPrimaryAdmin())
            <div id="adminAllFilters" class="hidden items-center gap-2">
                <div class="sppb-f relative w-40">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-tags text-xs"></i></span>
                    <select id="f_vp_type" class="vpl-filter w-full">
                    <option value="">All Types</option>
                    <option value="V">Voucher</option>
                    <option value="P">Product</option>
                </select>
                </div>
                <div class="sppb-f relative w-40">
                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><i class="fa-solid fa-circle-check text-xs"></i></span>
                    <select id="f_doc_status" class="vpl-filter w-full">
                    <option value="ALL">All Status</option>
                    <option value="P">On Progress</option>
                    <option value="C">Completed</option>
                    <option value="D">Hold / Revise</option>
                    <option value="R">Rejected</option>
                    <option value="X">Cancelled</option>
                </select>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div id="settlementTableWrap" class="relative overflow-hidden">
        <table id="settlementTable" class="w-full min-w-full border-separate border-spacing-0 text-sm" style="width:100%">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-medium">Doc No</th>
                    <th class="px-4 py-3 text-left font-medium">Date</th>
                    <th class="px-4 py-3 text-left font-medium">Event Date</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Dept</th>
                    <th class="px-4 py-3 text-left font-medium">V/P Type</th>
                    <th class="px-4 py-3 text-left font-medium">Usage ID</th>
                    <th class="px-4 py-3 text-left font-medium">Remark</th>
                    <th class="px-4 py-3 text-left font-medium">Status</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div id="jobListTableWrap" class="relative hidden overflow-hidden">
        <table id="jobListTable" class="w-full min-w-full border-separate border-spacing-0 text-sm" style="width:100%">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                    <th class="px-4 py-3 text-left font-medium" style="width:100px">Action</th>
                    <th class="px-4 py-3 text-left font-medium">Usage Doc</th>
                    <th class="px-4 py-3 text-left font-medium">Date</th>
                    <th class="px-4 py-3 text-left font-medium">Event Date</th>
                    <th class="px-4 py-3 text-left font-medium">Company</th>
                    <th class="px-4 py-3 text-left font-medium">Dept</th>
                    <th class="px-4 py-3 text-left font-medium">V/P Type</th>
                    <th class="px-4 py-3 text-left font-medium">Remark</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

</div>{{-- end wrapper --}}


{{-- ======================================================== --}}
{{-- CREATE MODAL --}}
{{-- ======================================================== --}}
<div id="createModal" class="vpl-pop fixed inset-0 z-[50] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10">

        <div class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-plus"></i></span>
                <div class="min-w-0">
                    <h2 class="text-base font-bold leading-tight text-gray-800 dark:text-white">Create Settlement</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Settle a Completed Usage document.</p>
                </div>
            </div>
            <button type="button" id="closeCreateModal"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        {{-- FORM VIEW --}}
        <div id="c_formView" class="bg-gray-50 p-5 dark:bg-gray-900/60">
            <form id="createForm" class="space-y-4" enctype="multipart/form-data">
                @csrf

                {{-- Header Info --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-circle-info"></i></span><span>Settlement Information <span class="text-[11px] font-normal text-gray-400">(locked to selected Job List row)</span></span></h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-4">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company <span class="text-red-500">*</span></label>
                            <select name="cpnyid" id="c_cpnyid" class="w-full select2-create" required disabled>
                                @foreach($usercpny as $p)
                                    <option value="{{ $p->cpny_id }}" {{ $p->cpny_id == $usercpny2?->cpny_id ? 'selected' : '' }}>{{ $p->cpny_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department <span class="text-red-500">*</span></label>
                            <select name="department" id="c_department" class="w-full select2-create" required disabled>
                                @foreach($userdept as $p)
                                    <option value="{{ $p->department_id }}" {{ $p->department_id == $userdept2?->department_id ? 'selected' : '' }}>{{ $p->department_id }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">V/P Type <span class="text-red-500">*</span></label>
                            <select name="vp_type" id="c_vp_type" class="w-full select2-create" required disabled>
                                <option value=""></option>
                                <option value="V" selected>Voucher</option>
                                <option value="P">Product</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Usage Doc <span class="text-red-500">*</span></label>
                            <select id="c_usage_id" class="w-full select2-create" required disabled>
                                <option value="">Select company, department &amp; type first...</option>
                            </select>
                            <input type="hidden" name="usage_id" id="c_usage_id_hidden">
                        </div>
                        <div class="md:col-span-4">
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remark <span class="text-red-500">*</span></label>
                            <textarea name="settlement_remark" id="c_remark" rows="2" placeholder="Enter remarks..." required
                                class="min-h-[4.5rem] px-3 py-2 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Warning: no lines loaded yet --}}
                <div id="c_lines_warning" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-400/30 dark:bg-amber-400/10 dark:text-amber-300">
                    <strong>⚠</strong> This Usage document has no remaining lines to settle.
                </div>

                {{-- Detail Lines --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-list-ul"></i></span><span>Settlement Details</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm" id="c_detailTable">
                            <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:18%">Product</th>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:20%">Remark</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Qty Usage</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Qty Return</th>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:14%">Qty Settlement <span class="text-red-500">*</span></th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="c_detailBody">
                                <tr id="c_detailEmptyRow"><td colspan="6" class="px-3 py-6 text-center text-sm text-gray-400">Select a Usage document to load its lines.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Attachments <span class="text-red-500">*</span></span></h3>
                        <button type="button" id="c_addAttach"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-blue-300 bg-blue-50/60 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:border-blue-500 hover:bg-blue-50 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add File
                        </button>
                    </div>
                    <div class="p-4">
                        <table class="min-w-full text-sm">
                            <tbody id="c_attachBody">
                                <tr id="c_attach_0">
                                    <td class="py-1 pr-2">
                                        <input type="file" name="attachment[]"
                                            class="w-full rounded-lg border border-dashed border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 transition file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:border-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    </td>
                                    <td class="py-1 pl-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </form>
        </div>

        {{-- PREVIEW VIEW --}}
        <div id="c_previewView" class="hidden bg-gray-50 p-5 dark:bg-gray-900/60">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-xl border border-blue-200 bg-blue-50/60 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                    <div class="border-b border-blue-200 px-5 py-3 dark:border-blue-500/30">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-clipboard-check"></i></span><span>Please confirm before submitting</span></h3>
                    </div>
                    <div id="c_previewHeader" class="grid grid-cols-2 gap-4 p-5 text-sm md:grid-cols-4"></div>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-list-ul"></i></span><span>Settlement Details</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Usage</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Return</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Settlement</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="c_previewDetailBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Attachments</span></h3>
                    </div>
                    <div id="c_previewAttachBody" class="divide-y divide-gray-100 p-4 text-sm empty:text-gray-400 dark:divide-gray-700"></div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-20 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="closeCreateModalFooter"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Close
                </button>
                <button type="button" id="c_backToEditBtn"
                    class="hidden h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Back to Edit
                </button>
                <button type="button" id="c_reviewBtn"
                    class="h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                    <i class="fa-solid fa-eye text-xs"></i> Review &amp; Submit
                </button>
                <button type="button" id="c_confirmSubmitBtn"
                    class="hidden h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-emerald-600 font-semibold text-white hover:bg-emerald-500 focus:ring-emerald-300">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Confirm &amp; Submit
                </button>
            </div>
        </div>

    </div>
</div>


{{-- ======================================================== --}}
{{-- VIEW MODAL --}}
{{-- ======================================================== --}}
<div id="viewModal" class="vpl-pop fixed inset-0 z-[60] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-7xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10">

        <div class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-file-lines"></i></span>
                <div class="min-w-0">
                    <h2 id="v_title" class="break-words text-base font-bold leading-tight text-gray-800 dark:text-white">Settlement Detail</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Settlement information &amp; approval workflow.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="v_printBtn" title="Print PDF"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-print text-base"></i>
                </button>
                <button type="button" id="v_msgToggleBtn"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                    <i class="fa-regular fa-comments text-base"></i>
                </button>
                <button type="button" id="closeViewModal"
                    class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 bg-gray-50 p-5 dark:bg-gray-900/60 lg:grid-cols-[1.6fr_0.6fr]">

            {{-- LEFT --}}
            <div class="space-y-4">

                {{-- Settlement Info --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <div>
                            <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Requested By</div>
                            <div id="v_user" class="mt-1 text-base font-bold text-gray-900 dark:text-white"></div>
                        </div>
                        <div id="v_status_badge"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 p-5 md:grid-cols-3">
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Doc No</div><div id="v_doc_no" class="mt-1 break-words text-sm font-bold text-blue-600 dark:text-blue-400"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Date</div><div id="v_date" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Event Date</div><div id="v_event_date" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Company</div><div id="v_cpnyid" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Department</div><div id="v_dept" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">V/P Type</div><div id="v_vp_type" class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-100"></div></div>
                        <div><div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Usage Doc</div><a id="v_usage_id" href="#" class="mt-1 block text-sm font-semibold text-blue-600 hover:underline dark:text-blue-400"></a></div>
                    </div>
                    <div class="border-t border-gray-100 px-5 py-3 dark:border-gray-700">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400">Remark</div>
                        <div id="v_remark" class="mt-1 whitespace-pre-line break-words text-sm text-gray-700 dark:text-gray-200"></div>
                    </div>
                </div>

                {{-- Detail Table --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-list-ul"></i></span><span>Settlement Details</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b border-gray-100 bg-gray-50 dark:border-gray-700 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Usage</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Return</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Settlement</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="v_detailBody" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Attachments --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Attachments</span></h3>
                        <button type="button" id="v_addAttachBtn"
                            class="hidden inline-flex items-center gap-1.5 rounded-lg border border-dashed border-blue-300 bg-blue-50/60 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:border-blue-500 hover:bg-blue-50 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add File
                        </button>
                        <input type="file" id="v_addAttachInput" name="attachment[]" multiple class="hidden">
                    </div>
                    <div id="v_attachBody" class="divide-y divide-gray-100 empty:p-4 empty:text-sm empty:text-gray-400 dark:divide-gray-700"></div>
                </div>

            </div>

            {{-- RIGHT --}}
            <div class="space-y-4">

                <div id="v_reviseReasonWrapper" class="hidden overflow-hidden rounded-xl border border-yellow-200 bg-yellow-50 shadow-sm dark:border-yellow-500/20 dark:bg-yellow-500/10">
                    <div class="border-b border-yellow-100 px-5 py-2 dark:border-yellow-500/20">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-yellow-700 dark:text-yellow-300">Revision Reason</h3>
                    </div>
                    <div id="v_revise_reason" class="break-words p-5 text-sm leading-relaxed text-yellow-900 dark:text-yellow-100"></div>
                </div>

                <div class="overflow-hidden">
                    <div class="flex flex-col gap-2">
                        <div id="v_statusBanner" class="mb-0 flex w-full items-center gap-2"></div>
                        <div id="v_approvalActions" class="mb-4 hidden flex w-full items-center justify-between gap-2">
                            <button type="button" id="v_approveBtn"
                                class="h-10 flex-1 rounded-lg bg-emerald-500 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-400">
                                <i class="fa-solid fa-check mr-1"></i> Approve
                            </button>
                            <button type="button" id="v_reviseBtn"
                                class="h-10 flex-1 rounded-lg bg-yellow-400 px-4 text-xs font-semibold text-black shadow-sm transition hover:bg-yellow-300 dark:text-white">
                                <i class="fa-solid fa-rotate-left mr-1"></i> Revise
                            </button>
                            <button type="button" id="v_rejectBtn"
                                class="h-10 flex-1 rounded-lg bg-red-500 px-4 text-xs font-semibold text-white shadow-sm transition hover:bg-red-400">
                                <i class="fa-solid fa-xmark mr-1"></i> Reject
                            </button>
                        </div>
                    </div>
                    <div id="v_approvalBody" class="mt-4"></div>
                </div>

            </div>
        </div>

        {{-- Discussion panel --}}
        <div id="v_discussionPanel"
            class="absolute bottom-[76px] right-4 z-30 hidden w-[380px] max-w-[calc(100%-2rem)] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Messages</h3>
                <button type="button" id="v_discussionClose" aria-label="Close" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div id="v_msgBody" class="h-[360px] space-y-4 overflow-y-auto bg-gray-50 p-4 dark:bg-gray-900/60"></div>
            <div class="border-t border-gray-100 p-3 dark:border-gray-700">
                <div class="flex items-end gap-2">
                    <textarea id="v_msgInput" rows="1" placeholder="Write message..."
                        class="min-h-[44px] flex-1 resize-none rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-700 transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100"></textarea>
                    <button type="button" id="v_msgSend"
                        class="h-11 w-11 shrink-0 rounded-lg bg-blue-600 text-white shadow-sm transition hover:bg-blue-500">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-20 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-between">
                <button type="button" id="closeViewModalFooter"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">Close</button>
                <div class="flex items-center gap-3">
                    <button type="button" id="v_cancelBtn"
                        class="hidden h-10 items-center justify-center rounded-lg border border-red-200 bg-red-50 px-4 text-sm font-semibold text-red-600 transition hover:border-red-600 hover:bg-red-600 hover:text-white dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-300">
                        Cancel Document
                    </button>
                    <button type="button" id="v_editBtn"
                        class="hidden h-10 items-center justify-center rounded-lg bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-500">
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
<div id="editModal" class="vpl-pop fixed inset-0 z-[70] hidden items-center justify-center p-4">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50 opacity-0 backdrop-blur-sm transition-opacity duration-200 dark:bg-black/70"></div>
    <div class="modal-panel modal-scroll relative z-10 flex max-h-[95vh] w-full max-w-6xl translate-y-4 scale-[0.98] flex-col overflow-y-auto rounded-2xl bg-white opacity-0 shadow-2xl ring-1 ring-black/5 transition-all duration-200 dark:bg-gray-800 dark:ring-white/10">

        <div class="sticky top-0 z-20 flex items-center justify-between gap-3 border-b border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-pen-to-square"></i></span>
                <div class="min-w-0">
                    <h2 id="e_title" class="text-base font-bold leading-tight text-gray-800 dark:text-white">Edit Settlement</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Modify and resubmit for approval.</p>
                </div>
            </div>
            <button type="button" id="closeEditModal"
                class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200">
                <i class="fa-solid fa-xmark text-base"></i>
            </button>
        </div>

        {{-- FORM VIEW --}}
        <div id="e_formView" class="bg-gray-50 p-5 dark:bg-gray-900/60">
            <form id="editForm" class="space-y-4" enctype="multipart/form-data">
                @csrf

                {{-- Read-only header --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-circle-info"></i></span><span>Settlement Information <span class="text-[11px] font-normal text-gray-400">(read-only)</span></span></h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Company</label>
                            <input type="text" id="e_cpnyid_display" readonly class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-300">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                            <input type="text" id="e_dept_display" readonly class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-300">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Usage Doc</label>
                            <input type="text" id="e_usage_id_display" readonly class="h-10 w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-3 text-sm text-gray-600 shadow-sm dark:border-gray-700 dark:bg-white/[0.04] dark:text-gray-300">
                        </div>
                        <div class="md:col-span-3">
                            <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Remark <span class="text-red-500">*</span></label>
                            <textarea name="settlement_remark" id="e_remark" rows="2" required
                                class="min-h-[4.5rem] px-3 py-2 w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 hover:border-blue-300 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"></textarea>
                        </div>
                    </div>
                </div>

                {{-- Detail Lines --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-list-ul"></i></span><span>Settlement Details</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm" id="e_detailTable">
                            <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:18%">Product</th>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:20%">Remark</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Qty Usage</th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Qty Return</th>
                                    <th class="px-3 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:14%">Qty Settlement <span class="text-red-500">*</span></th>
                                    <th class="px-3 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" style="width:12%">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="e_detailBody"></tbody>
                        </table>
                    </div>
                </div>

                {{-- Existing Attachments --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Existing Attachments</span></h3>
                    </div>
                    <div id="e_existAttachBody" class="divide-y divide-gray-100 dark:divide-gray-700"></div>
                </div>

                {{-- New Attachments --}}
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Add Attachments <span class="text-red-500">*</span></span></h3>
                        <button type="button" id="e_addAttach"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-dashed border-blue-300 bg-blue-50/60 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:border-blue-500 hover:bg-blue-50 dark:border-blue-500/40 dark:bg-blue-500/10 dark:text-blue-300">
                            <i class="fa-solid fa-plus text-[10px]"></i> Add File
                        </button>
                    </div>
                    <div class="p-4">
                        <table class="min-w-full text-sm">
                            <tbody id="e_attachBody">
                                <tr id="e_attach_0">
                                    <td class="py-1 pr-2">
                                        <input type="file" name="attachment[]"
                                            class="w-full rounded-lg border border-dashed border-gray-300 bg-white px-3 py-1.5 text-sm text-gray-600 transition file:mr-3 file:cursor-pointer file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-blue-700 hover:border-blue-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                    </td>
                                    <td class="py-1 pl-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </form>
        </div>

        {{-- PREVIEW VIEW --}}
        <div id="e_previewView" class="hidden bg-gray-50 p-5 dark:bg-gray-900/60">
            <div class="space-y-4">
                <div class="overflow-hidden rounded-xl border border-blue-200 bg-blue-50/60 shadow-sm dark:border-blue-500/30 dark:bg-blue-500/10">
                    <div class="border-b border-blue-200 px-5 py-3 dark:border-blue-500/30">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-clipboard-check"></i></span><span>Please confirm before resubmitting</span></h3>
                    </div>
                    <div id="e_previewHeader" class="grid grid-cols-2 gap-4 p-5 text-sm md:grid-cols-4"></div>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-list-ul"></i></span><span>Settlement Details</span></h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-white/[0.03]">
                                <tr>
                                    <th class="px-4 py-2.5 text-left whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Product</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Usage</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Return</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Qty Settlement</th>
                                    <th class="px-4 py-2.5 text-right whitespace-nowrap text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Sisa</th>
                                </tr>
                            </thead>
                            <tbody id="e_previewDetailBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                        <h3 class="flex items-center gap-2.5 text-sm font-bold text-gray-800 dark:text-gray-100"><span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-xs text-blue-600 dark:bg-blue-500/10 dark:text-blue-300"><i class="fa-solid fa-paperclip"></i></span><span>Attachments</span></h3>
                    </div>
                    <div id="e_previewAttachBody" class="divide-y divide-gray-100 p-4 text-sm empty:text-gray-400 dark:divide-gray-700"></div>
                </div>
            </div>
        </div>

        <div class="sticky bottom-0 z-20 border-t border-gray-100 bg-white px-6 py-4 dark:border-gray-700 dark:bg-gray-800">
            <div class="flex items-center justify-end gap-3">
                <button type="button" id="closeEditModalFooter"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    Close
                </button>
                <button type="button" id="e_backToEditBtn"
                    class="hidden h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 border border-gray-300 bg-white font-medium text-gray-700 hover:bg-gray-100 focus:ring-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600">
                    <i class="fa-solid fa-arrow-left mr-2 text-xs"></i> Back to Edit
                </button>
                <button type="button" id="e_reviewBtn"
                    class="h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-blue-600 font-semibold text-white hover:bg-blue-500 focus:ring-blue-300">
                    <i class="fa-solid fa-eye text-xs"></i> Review &amp; Resubmit
                </button>
                <button type="button" id="e_confirmSubmitBtn"
                    class="hidden h-10 items-center justify-center gap-2 rounded-lg px-5 text-sm shadow-sm transition focus:outline-none focus:ring-2 bg-emerald-600 font-semibold text-white hover:bg-emerald-500 focus:ring-emerald-300">
                    <i class="fa-solid fa-paper-plane text-xs"></i> Confirm &amp; Resubmit
                </button>
            </div>
        </div>

    </div>
</div>


{{-- ======================================================== --}}
{{-- SCRIPTS --}}
{{-- ======================================================== --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<x-spp-list-scripts />
<x-vpl-list-scripts />

<script>
    window.VplSettlementConfig = {
        base:        '{{ url("settlementvp") }}',
        store:       '{{ route("settlementvp.store") }}',
        jobList:     '{{ route("settlementvp.joblist") }}',
        usageOptions:'{{ route("settlementvp.usage-options") }}',
        usageLines:  '{{ route("settlementvp.usage-lines") }}',
        delAttach:   '{{ route("settlementvp.attachment.delete") }}',
        addAttach: (id) => `{{ url("settlementvp") }}/${id}/attachment/add`,
        data: (id) => `{{ url("settlementvp") }}/${id}/data`,
        update: (id) => `{{ url("settlementvp") }}/${id}/update`,
        cancel: (id) => `{{ url("settlementvp") }}/${id}/cancel`,
        approve: (id) => `{{ url("settlementvp") }}/${id}/approve`,
        reject: (id) => `{{ url("settlementvp") }}/${id}/reject`,
        revise: (id) => `{{ url("settlementvp") }}/${id}/revise`,
        message: (id) => `{{ url("settlementvp") }}/${id}/message`,
        show: (eid) => `{{ url("showsettlementvp") }}/${eid}`,
        initialId: {{ $initialId ?? 'null' }},
    };
</script>

<script src="{{ asset('assets/js/voucher-settlement/core.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/core.js')) }}"></script>
<script src="{{ asset('assets/js/voucher-settlement/helper.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/helper.js')) }}"></script>
<script src="{{ asset('assets/js/voucher-settlement/datalist.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/datalist.js')) }}"></script>
<script src="{{ asset('assets/js/voucher-settlement/modal.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/modal.js')) }}"></script>
<script src="{{ asset('assets/js/voucher-settlement/form.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/form.js')) }}"></script>
<script src="{{ asset('assets/js/voucher-settlement/init.js') }}?v={{ filemtime(public_path('assets/js/voucher-settlement/init.js')) }}"></script>

</x-app-layout>
