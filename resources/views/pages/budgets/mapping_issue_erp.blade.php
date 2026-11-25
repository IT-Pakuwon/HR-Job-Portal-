<x-app-layout>
    <x-spp-form-styles />
    <x-spp-list-styles />
    <style>
        /* Mapping lists: on phones every row becomes a small card (labels come from data-label) */
        @media (max-width: 639px) {
            .map-list thead { display: none; }
            .map-list, .map-list tbody, .map-list tr, .map-list td { display: block; width: 100%; }
            .map-list tbody tr { padding: .75rem 1rem; border-bottom: 1px solid #e5e7eb; }
            .dark .map-list tbody tr { border-bottom-color: #374151; }
            .map-list td { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .3rem 0 !important; text-align: right; word-break: break-word; }
            .map-list td::before { content: attr(data-label); flex: none; font-size: .6875rem; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; text-align: left; }
            .map-list td[colspan] { display: block; text-align: center; }
            .map-list td[colspan]::before { content: none; }
        }
    </style>
    @php
        $currentPage = Route::currentRouteName() == 'mapping_issue_erp.index' ? 'Integration' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="flex w-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:px-6">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg></span>
                                    <div>
                                        <h2 class="text-base font-bold text-gray-800 dark:text-gray-100">Mapping Issue ERP</h2>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Klik ikon kaca pembesar untuk review & update mapping issue</p>
                                    </div>
                                </div>
                                <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
                                <div class="sppb-f relative w-full sm:w-44">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></span>
                                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="D">Waiting Review</option>
                        <option value="P">Review</option>
                        <option value="C">Completed</option>
                    </select>
                                </div>
                                <button id="btnReload"
                                    class="inline-flex w-full items-center justify-center gap-2 sm:w-auto rounded-lg border border-blue-100 bg-blue-50 px-4 text-sm font-semibold text-blue-700 h-10 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                    Reload
                                </button>
                            </div>
                            </div>

            <div class="p-4 sm:p-6">
            {{-- Search --}}
            <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                <label for="pageSize" class="flex h-10 items-center gap-1 rounded-[10px] border border-slate-200 bg-slate-50 pl-4 pr-3.5 text-[13px] text-gray-500 transition hover:border-slate-300 focus-within:border-blue-500 focus-within:ring-4 focus-within:ring-blue-500/10 dark:border-white/10 dark:bg-white/5 dark:text-gray-400">
                    Show
                    <select id="pageSize" class="sf-select h-8 border-0 bg-transparent pl-2 text-sm font-bold text-blue-600 outline-none dark:text-blue-300">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                        <option value="99999">All</option>
                    </select>
                    entries
                </label>

                <div class="flex min-w-0 flex-1 items-center justify-end gap-2 sm:flex-none">
                <div class="relative min-w-0 flex-1 sm:flex-none">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex w-11 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z" /></svg></span>
                    <input id="searchInput" type="search"
                        class="h-10 w-full rounded-full border border-slate-200 bg-slate-50 pl-11 pr-4 text-sm text-slate-800 transition-all duration-300 placeholder:text-slate-400 hover:border-slate-300 hover:bg-white sm:w-80 sm:focus:w-[30rem] focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-4 focus:ring-blue-500/10 dark:border-white/10 dark:bg-white/5 dark:text-slate-100"
                        placeholder="cpny_id / issue_id / reference_no / spb_id / wo_id / user_peminta...">
                </div>
                <button id="btnSearch"
                    class="h-10 rounded-full bg-blue-600 px-6 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 active:scale-95">
                    Apply
                </button>
            </div>
            </div>

            <div class="mt-5 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <div class="overflow-x-auto">
                    <table class="map-list min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/40">
                            <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="w-14 px-4 py-3"></th>
                                <th class="px-4 py-3">Cpny</th>
                                <th class="px-4 py-3">Issue ID</th>
                                <th class="px-4 py-3">Issue Date</th>
                                <th class="px-4 py-3">Reference No</th>
                                <th class="px-4 py-3">SPB ID</th>
                                <th class="px-4 py-3">WO ID</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="tbody" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination (10 per page) --}}
            <div id="pager" class="mt-4 flex flex-col items-center justify-between gap-3 sm:flex-row"></div>

        </div>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-2 backdrop-blur-sm sm:p-4">
        <div class="flex max-h-[95vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 px-4 py-4 dark:border-gray-700 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg></span>
                    <div class="min-w-0">
                        <h2 class="text-base font-bold text-gray-800 dark:text-white">Detail & Mapping Issue</h2>
                        <p id="modalSub" class="truncate text-sm font-medium text-gray-500 dark:text-gray-400"></p>
                    </div>
                </div>
                <button id="btnCloseModal"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-4 sm:p-6">
                <input type="hidden" id="rowId">

                {{-- SUMMARY --}}
                <div class="grid grid-cols-2 gap-3 rounded-xl border border-gray-100 bg-gray-50/70 p-3 dark:border-gray-700 dark:bg-white/[0.02] sm:grid-cols-3 lg:grid-cols-5">
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Company</div>
                        <input id="mCpny" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Issue ID</div>
                        <input id="mIssueId" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Issue Date</div>
                        <input id="mIssueDate" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Reference No</div>
                        <input id="mReferenceNo" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">SPB ID</div>
                        <input id="mSpbId" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">WO ID</div>
                        <input id="mWoId" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Department</div>
                        <input id="mDepartment" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Peminta</div>
                        <input id="mPeminta" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="min-w-0 rounded-lg border border-gray-100 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Keeper</div>
                        <input id="mKeeper" readonly class="mt-0.5 block w-full truncate border-0 bg-transparent! p-0 text-[15px] font-semibold text-gray-900 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-100">
                    </div>
                    <div class="col-span-full rounded-lg border border-l-4 border-gray-100 border-l-blue-500 bg-white px-3 py-2.5 shadow-sm dark:border-gray-700 dark:border-l-blue-400 dark:bg-gray-800">
                        <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Remarks</div>
                        <textarea id="mRemark" rows="2" readonly class="mt-0.5 block w-full resize-none border-0 bg-transparent! p-0 text-sm leading-relaxed text-gray-700 shadow-none! outline-none ring-0! focus:ring-0! dark:text-gray-200"></textarea>
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 dark:border-gray-700">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-700">
                        <div>
                            <div class="text-sm font-bold text-gray-800 dark:text-gray-100">Detail Items</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Mapping per line</div>
                        </div>
                        <label class="flex w-full items-center gap-2 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 sm:w-auto">
                            <span class="shrink-0">Integration Type</span>
                            <span class="block w-full sm:w-52"><select id="mIntegrationType"
                            class="sf-select h-9 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm font-medium text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">-- Select Integration Type --</option>
                        </select></span>
                        </label>
                    </div>

                    <div class="max-h-[42vh] overflow-auto">
                        <table class="min-w-full text-xs">
                            <thead class="sticky top-0 bg-gray-50 dark:bg-gray-900">
                                <tr class="text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    <th class="px-3 py-2 w-12">No</th>
                                    <th class="px-3 py-2 min-w-[280px]">Item Info</th>
                                    <th class="px-3 py-2 w-14">Qty/Uom</th>
                                    <th class="px-3 py-2 min-w-[300px]"><span class="block text-gray-700 dark:text-gray-200">IFCA</span><span class="block text-[10px] font-normal normal-case tracking-normal text-gray-400">entity_cd · ic_location · trx_cd · div_cd · dept_cd</span></th>
                                    <th class="px-3 py-2 min-w-[320px]"><span class="block text-gray-700 dark:text-gray-200">SOLOMON</span><span class="block text-[10px] font-normal normal-case tracking-normal text-gray-400">reason_cd · acct_cd · allocation_cd · subaccount_dept</span></th>
                                </tr>
                            </thead>
                            <tbody id="detailTbody" class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                                <tr>
                                    <td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</label>
                        <select id="mStatus"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="D">Waiting Review</option>
                            <option value="P">Review</option>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Review Note</label>
                        <input id="mNote"
                            class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                            placeholder="Catatan review...">
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 flex-col-reverse gap-3 border-t border-gray-100 bg-gray-50/60 px-4 py-4 dark:border-gray-700 dark:bg-white/[0.02] sm:flex-row sm:items-center sm:justify-end sm:px-6">
                <button id="btnCancel"
                    class="w-full rounded-lg sm:w-auto border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-300 active:scale-95 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Close
                </button>
                <button id="btnSave"
                    class="w-full rounded-lg sm:w-auto bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 active:scale-95">
                    Save Update
                </button>
            </div>
        </div>
    </div>

<script>
$(function() {
    // styled dropdowns for the filter bar (same Select2 skin as the forms)
    $('#filterCompany, #filterStatus').each(function() {
        $(this).select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sppb-s2-dropdown' });
    });
});
</script>

<script>
$(function() {
    // modal dropdowns: same Select2 skin as the forms (the list is appended to <body>, above the modal)
    $('#mStatus, #mIntegrationType').each(function() {
        $(this).parent().addClass('sf-field');
        $(this).select2({ width: '100%', minimumResultsForSearch: 8, dropdownCssClass: 'sf-s2-dropdown' });
    });
});
</script>

<script>
(function() {
    const tbody = document.getElementById('tbody');
    const modal = document.getElementById('editModal');
    const detailTbody = document.getElementById('detailTbody');
    const btnSave = document.getElementById('btnSave');

    const URL_JSON = @json(route('mapping_issue_erp.json'));
    const BASE     = @json(url('/mapping-issue-erp'));
    const URL_INTEGRATION_TYPES = @json(route('mapping_issue_erp.integration-types'));

    function statusBadge(st) {
        st = (st || '').toUpperCase();
        const map = {
            D: { text: 'Waiting Review', cls: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30', dot: 'bg-blue-500' },
            P: { text: 'Review',        cls: 'bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30', dot: 'bg-yellow-500' },
            C: { text: 'Completed',     cls: 'bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', dot: 'bg-green-500' },
        };
        const m = map[st] || { text: (st || '-'), cls: 'bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', dot: 'bg-slate-400' };
        return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${m.cls}"><span class="h-1.5 w-1.5 rounded-full ${m.dot}"></span>${m.text}</span>`;
    }

    function openModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function magnifierBtn(id) {
        return `
            <button type="button" class="js-open group/view inline-flex h-8 w-8 items-center justify-center rounded-md border border-blue-100 bg-blue-50 text-blue-600 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-90 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white"
                data-id="${id}" title="View / Edit">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 transition-transform duration-300 group-hover/view:scale-125">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                </svg>
            </button>
        `;
    }

    function escapeHtml(str) {
        return (str ?? '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setInputEditable(el, editable) {
        if (!el) return;
        el.readOnly = !editable;

        if (editable) {
            el.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'opacity-80');
            el.classList.add('bg-white');
        } else {
            el.classList.remove('bg-white');
            el.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'opacity-80');
        }
    }

    function setSelectEditable(el, editable) {
        if (!el) return;
        el.disabled = !editable;

        if (editable) {
            el.classList.remove('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'opacity-80');
            el.classList.add('bg-white');
        } else {
            el.classList.remove('bg-white');
            el.classList.add('bg-gray-100', 'text-gray-500', 'cursor-not-allowed', 'opacity-80');
        }
    }

    function applyIntegrationTypeMode(type, forceReadonly = false) {
        const selected = (type || '').toUpperCase();

        const ifcaFields = detailTbody.querySelectorAll('input[data-group="ifca"]');
        const solomonFields = detailTbody.querySelectorAll('input[data-group="solomon"]');
        const integrationSelect = document.getElementById('mIntegrationType');
        const statusSelect = document.getElementById('mStatus');
        const noteInput = document.getElementById('mNote');

        if (forceReadonly) {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, false));
            setSelectEditable(integrationSelect, false);
            setSelectEditable(statusSelect, false);
            setInputEditable(noteInput, false);
            return;
        }

        setSelectEditable(integrationSelect, true);
        setSelectEditable(statusSelect, true);
        setInputEditable(noteInput, true);

        if (selected === 'IFCA') {
            ifcaFields.forEach(inp => setInputEditable(inp, true));
            solomonFields.forEach(inp => setInputEditable(inp, false));
        } else if (selected === 'SOLOMON') {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, true));
        } else {
            ifcaFields.forEach(inp => setInputEditable(inp, false));
            solomonFields.forEach(inp => setInputEditable(inp, false));
        }
    }

    function formatQty(val) {
        if (val === null || val === undefined || val === '') return '';
        const num = parseFloat(val);
        if (isNaN(num)) return String(val);
        return num.toFixed(2);
    }

    async function loadIntegrationTypes(selectedValue = '') {
        const select = document.getElementById('mIntegrationType');
        if (!select) return;

        select.innerHTML = `<option value="">Loading...</option>`;

        try {
            const res = await fetch(URL_INTEGRATION_TYPES, {
                headers: { 'Accept': 'application/json' }
            });

            const json = await res.json();
            const rows = json.data || [];

            let html = `<option value="">-- Select Integration Type --</option>`;
            html += rows.map(x => {
                const raw = (typeof x === 'object') ? (x.integration_type ?? '') : x;
                const val = (raw ?? '').toString().toUpperCase();
                const selected = val === (selectedValue || '').toUpperCase() ? 'selected' : '';
                return `<option value="${escapeHtml(val)}" ${selected}>${escapeHtml(val)}</option>`;
            }).join('');

            select.innerHTML = html;
        } catch (err) {
            select.innerHTML = `<option value="">-- Failed load option --</option>`;
        }
    }

    let PER_PAGE = 10; // changed by the "Show N entries" selector
    let allRows = [];
    let page = 1;

    async function loadTable(keepPage) {
        const status = document.getElementById('filterStatus').value;
        const search = document.getElementById('searchInput').value.trim();

        tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>`;
        const pagerEl = document.getElementById('pager');
        if (pagerEl) pagerEl.innerHTML = '';

        const qs = new URLSearchParams();
        if (status) qs.set('status', status);
        if (search) qs.set('search', search);

        const res = await fetch(URL_JSON + '?' + qs.toString(), {
            headers: { 'Accept': 'application/json' }
        });

        const json = await res.json();
        allRows = json.data || [];
        const pages = Math.max(1, Math.ceil(allRows.length / PER_PAGE));
        page = keepPage === true ? Math.min(page, pages) : 1; // after "Save Update" stay on the same page
        renderPage();
    }

    function renderPage() {
        if (!allRows.length) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">No data</td></tr>`;
            renderPager();
            return;
        }

        const rows = allRows.slice((page - 1) * PER_PAGE, page * PER_PAGE);

        tbody.innerHTML = rows.map(r => `
            <tr class="transition-colors hover:bg-blue-50/60 dark:hover:bg-gray-700">
                <td class="px-4 py-3" data-label="Action">${magnifierBtn(r.id)}</td>
                <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white" data-label="Cpny">${escapeHtml(r.cpny_id ?? '')}</td>
                <td class="px-4 py-3" data-label="Issue ID">${escapeHtml(r.issue_id ?? '')}</td>
                <td class="px-4 py-3" data-label="Issue Date">${escapeHtml(r.issue_date ?? '')}</td>
                <td class="px-4 py-3" data-label="Reference No">${escapeHtml(r.reference_no ?? '')}</td>
                <td class="px-4 py-3" data-label="SPB ID">${escapeHtml(r.spb_id ?? '')}</td>
                <td class="px-4 py-3" data-label="WO ID">${escapeHtml(r.wo_id ?? '')}</td>
                <td class="px-4 py-3" data-label="Status">${statusBadge(r.status)}</td>
            </tr>
        `).join('');

        renderPager();
    }

    function renderPager() {
        const el = document.getElementById('pager');
        if (!el) return;

        const total = allRows.length;
        if (!total) { el.innerHTML = ''; return; }

        const pages = Math.max(1, Math.ceil(total / PER_PAGE));
        const from = (page - 1) * PER_PAGE + 1;
        const to = Math.min(page * PER_PAGE, total);

        const nums = [];
        for (let p = 1; p <= pages; p++) {
            if (p === 1 || p === pages || Math.abs(p - page) <= 1) nums.push(p);
            else if (nums[nums.length - 1] !== '…') nums.push('…');
        }

        const base = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition';
        const idle = 'border-gray-200 bg-white text-gray-700 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600';
        const current = 'border-blue-200 bg-blue-50 font-semibold text-blue-700 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300';
        const off = 'cursor-not-allowed opacity-40';

        const btn = (label, target, cls, disabled) =>
            `<button type="button" data-page="${target}" class="${base} ${cls}" ${disabled ? 'disabled' : ''}>${label}</button>`;

        el.innerHTML =
            `<div class="text-sm text-gray-500 dark:text-gray-400">Showing ${from} to ${to} of ${total} entries</div>` +
            `<div class="flex flex-wrap items-center justify-center gap-1.5">` +
            btn('Previous', page - 1, idle + (page === 1 ? ' ' + off : ''), page === 1) +
            nums.map(n => n === '…'
                ? '<span class="px-1 text-gray-400">…</span>'
                : btn(n, n, n === page ? current : idle, false)).join('') +
            btn('Next', page + 1, idle + (page === pages ? ' ' + off : ''), page === pages) +
            `</div>`;
    }

    function inputCell(name, value, rid, widthCls, group) {
        const v = escapeHtml(value ?? '');
        const w = widthCls || 'w-full';
        const g = group || '';

        return `<input
            data-rid="${rid}"
            data-name="${name}"
            data-group="${g}"
            value="${v}"
            placeholder="${name}"
            title="${name}"
            class="${w} rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-xs text-gray-800 transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />`;
    }

    async function openRow(id) {
        detailTbody.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">Loading...</td></tr>`;

        const res = await fetch(`${BASE}/${id}`, {
            headers: { 'Accept': 'application/json' }
        });

        const json = await res.json();
        if (!json.success) return;

        const header = json.data.header || {};
        const details = json.data.details || [];

        document.getElementById('rowId').value = id;
        document.getElementById('mCpny').value = header.cpny_id ?? '';
        document.getElementById('mIssueId').value = header.issue_id ?? '';
        document.getElementById('mIssueDate').value = header.issue_date ?? '';
        document.getElementById('mReferenceNo').value = header.reference_no ?? '';
        document.getElementById('mSpbId').value = header.spb_id ?? '';
        document.getElementById('mWoId').value = header.wo_id ?? '';
        document.getElementById('mDepartment').value = header.department_id ?? '';
        document.getElementById('mPeminta').value = header.user_peminta ?? '';
        document.getElementById('mKeeper').value = header.keeper ?? '';
        document.getElementById('mRemark').value = header.issuehd_descs ?? '';

        const status = (header.status ?? 'D').toUpperCase();
        document.getElementById('mStatus').value = status;
        document.getElementById('mNote').value = header.reviewed_note ?? '';

        if (status === 'C') {
            btnSave.classList.add('hidden');
        } else {
            btnSave.classList.remove('hidden');
        }

        document.getElementById('modalSub').textContent =
            `Reference: ${(header.reference_no ?? '-')}`;

        if (!details.length) {
            detailTbody.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">No detail</td></tr>`;
        } else {
            detailTbody.innerHTML = details.map(d => {
                const rid = d.id;
                const item = d.item_cd ?? '';
                const descr = d.item_remark ?? '';

                return `
                    <tr>
                        <td class="px-3 py-2 align-top">${escapeHtml(d.line_no ?? '')}</td>

                        <td class="px-3 py-2 align-top">
                            <div class="font-semibold text-gray-800 dark:text-white">${escapeHtml(item)}</div>
                            <div class="mt-0.5 text-gray-600 dark:text-gray-300 leading-snug">${escapeHtml(descr)}</div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="text-right font-semibold">${formatQty(d.issue_qty)}</div>
                            <div class="text-right text-gray-500 dark:text-gray-400">${escapeHtml(d.uom ?? '')}</div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="grid grid-cols-2 gap-1">
                                ${inputCell('entity_cd', d.entity_cd, rid, 'w-full', 'ifca')}
                                ${inputCell('ic_location', d.ic_location, rid, 'w-full', 'ifca')}
                                <div class="col-span-2">${inputCell('trx_cd', d.trx_cd, rid, 'w-full', 'ifca')}</div>
                                ${inputCell('div_cd', d.div_cd, rid, 'w-full', 'ifca')}
                                ${inputCell('dept_cd', d.dept_cd, rid, 'w-full', 'ifca')}
                            </div>
                        </td>

                        <td class="px-3 py-2 align-top">
                            <div class="grid grid-cols-1 gap-1">
                                ${inputCell('solomon_reason_cd', d.solomon_reason_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_acct_cd', d.solomon_acct_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_allocation_cd', d.solomon_allocation_cd, rid, 'w-full', 'solomon')}
                                ${inputCell('solomon_subaccount_dept', d.solomon_subaccount_dept, rid, 'w-full', 'solomon')}
                            </div>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        const integrationType = (header.integration_type ?? '').toUpperCase();
        await loadIntegrationTypes(integrationType);

        const integrationSelect = document.getElementById('mIntegrationType');
        if (integrationSelect) {
            integrationSelect.value = integrationType;
        }

        applyIntegrationTypeMode(integrationType, status === 'C');

        // the selects are Select2 now: show the values that were just set from code
        if (window.jQuery) jQuery('#mStatus, #mIntegrationType').trigger('change.select2');

        openModal();
    }

    function collectLinesPayload() {
        const inputs = detailTbody.querySelectorAll('input[data-rid][data-name]');
        const map = {};

        inputs.forEach(inp => {
            const rid = inp.getAttribute('data-rid');
            const name = inp.getAttribute('data-name');

            if (!map[rid]) {
                map[rid] = { id: parseInt(rid, 10) };
            }

            map[rid][name] = inp.value.trim();
        });

        return Object.values(map);
    }

    async function saveUpdate() {
        const id = document.getElementById('rowId').value;
        if (!id) return;

        const integrationSelect = document.getElementById('mIntegrationType');

        const payload = {
            status: document.getElementById('mStatus').value,
            reviewed_note: document.getElementById('mNote').value.trim(),
            integration_type: integrationSelect ? integrationSelect.value : '',
            lines: collectLinesPayload()
        };

        const res = await fetch(`${BASE}/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const json = await res.json();

        if (res.ok && json.success) {
            closeModal();
            await loadTable(true);
        } else {
            alert(json.message || 'Gagal update');
        }
    }

    document.getElementById('pageSize').addEventListener('change', (e) => {
        PER_PAGE = parseInt(e.target.value, 10) || 10;
        page = 1;
        renderPage();
    });

    document.getElementById('pager').addEventListener('click', (e) => {
        const b = e.target.closest('[data-page]');
        if (!b || b.disabled) return;
        page = parseInt(b.dataset.page, 10);
        renderPage();
        tbody.closest('.overflow-hidden').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    document.getElementById('btnReload').addEventListener('click', loadTable);
    document.getElementById('btnSearch').addEventListener('click', loadTable);
    document.getElementById('filterStatus').addEventListener('change', loadTable);
    // Select2 raises a jQuery "change" (not a native one), so listen through jQuery as well
    if (window.jQuery) jQuery('#filterStatus, #filterCompany').on('change', loadTable);

    document.getElementById('searchInput').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') loadTable();
    });

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button.js-open');
        if (!btn) return;
        openRow(btn.dataset.id);
    });

    const integrationSelect = document.getElementById('mIntegrationType');
    if (integrationSelect) {
        integrationSelect.addEventListener('change', function() {
            const currentStatus = (document.getElementById('mStatus').value || '').toUpperCase();
            applyIntegrationTypeMode(this.value, currentStatus === 'C');
        });
        // Select2 only raises a jQuery "change"; listen through jQuery as well
        if (window.jQuery) jQuery(integrationSelect).on('change', function() {
            const currentStatus = (document.getElementById('mStatus').value || '').toUpperCase();
            applyIntegrationTypeMode(this.value, currentStatus === 'C');
        });
    }

    document.getElementById('btnSave').addEventListener('click', saveUpdate);
    document.getElementById('btnCloseModal').addEventListener('click', closeModal);
    document.getElementById('btnCancel').addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    loadTable();
})();
</script>
</x-app-layout>