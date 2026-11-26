{{--
    "Pick COA" popup, opened from <x-coa-edit-modal>. The page script fills the filters, loads the rows into
    #coaPickerTbody (button .btnPickThis) and pages with #coaPickerPrev / #coaPickerNext.
--}}
<x-spp-form-styles />

<style>
    /* table, including the rows the page script builds */
    #coaPickerModal table { width: 100%; }
    #coaPickerModal table thead th {
        position: sticky; top: 0; z-index: 1; padding: .625rem .875rem; background: #f9fafb; border-bottom: 1px solid #e5e7eb;
        font-size: 11px; font-weight: 600; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; white-space: nowrap;
    }
    #coaPickerModal table tbody tr { border: 0; }
    #coaPickerModal table tbody td { padding: .625rem .875rem; border-bottom: 1px solid #f1f5f9; font-size: .8125rem; color: #374151; word-break: normal; }
    #coaPickerModal table tbody tr:last-child td { border-bottom: 0; }
    #coaPickerModal table tbody tr:hover td:not([colspan]) { background: #f8fafc; }
    #coaPickerModal table tbody td:first-child:not([colspan]) { font-weight: 600; color: #1d4ed8; white-space: nowrap; font-variant-numeric: tabular-nums; }
    #coaPickerModal table tbody td[colspan] { padding: 3rem 1rem; color: #9ca3af; }
    #coaPickerModal .btnPickThis {
        display: inline-flex; align-items: center; height: 2rem; padding: 0 .875rem; border: 1px solid #dbeafe; border-radius: .5rem;
        background: #eff6ff; color: #1d4ed8; font-size: .75rem; font-weight: 600; transition: background-color .15s, color .15s, border-color .15s;
    }
    #coaPickerModal .btnPickThis:hover { background: var(--color-blue-600); border-color: var(--color-blue-600); color: #fff; }

    .dark #coaPickerModal table thead th { background: #1e293b; border-color: rgba(255, 255, 255, .08); color: #9ca3af; }
    .dark #coaPickerModal table tbody td { border-color: rgba(255, 255, 255, .06); color: #e5e7eb; }
    .dark #coaPickerModal table tbody tr:hover { background: transparent; }
    .dark #coaPickerModal table tbody tr:hover td:not([colspan]) { background: rgba(255, 255, 255, .04); }
    .dark #coaPickerModal table tbody td:first-child:not([colspan]) { color: #93c5fd; }
    .dark #coaPickerModal .btnPickThis { background: rgba(59, 130, 246, .12); border-color: rgba(59, 130, 246, .3); color: #93c5fd; }
    .dark #coaPickerModal .btnPickThis:hover { background: var(--color-blue-600); color: #fff; }
</style>

@php
    $label = 'mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
    $select = 'h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm dark:border-white/10 dark:bg-[#111827] dark:text-gray-100';
    $pageBtn = 'inline-flex h-9 items-center rounded-lg border border-gray-300 bg-white px-4 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 disabled:cursor-default disabled:opacity-40 disabled:hover:bg-white dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10';
@endphp

<div id="coaPickerModal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm dark:bg-black/70">
    <div class="flex max-h-[92vh] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-slate-900/5 dark:bg-[#0f172a] dark:ring-white/10">

        <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-6 py-3.5 dark:border-white/[0.06]">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-magnifying-glass"></i></span>
                <div class="min-w-0">
                    <h3 class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">Pick COA</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Choose Company, Business Unit and Department Fin, press Apply, then pick a COA.</p>
                </div>
            </div>
            <button type="button" id="btnCloseCoaPicker" aria-label="Close"
                class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-gray-200">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <div class="flex min-h-0 flex-1 flex-col gap-3 bg-slate-50 p-4 dark:bg-[#0b1220]">
            {{-- Filters --}}
            <div class="sf-field grid grid-cols-1 items-end gap-3 rounded-xl border border-gray-100 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1.2fr_auto] dark:border-white/10 dark:bg-[#0f172a]">
                <div class="min-w-0">
                    <label class="{{ $label }}" for="fCpny">Company</label>
                    <select id="fCpny" class="{{ $select }}"><option value="">-- pilih --</option></select>
                </div>
                <div class="min-w-0">
                    <label class="{{ $label }}" for="fBu">Business Unit</label>
                    <select id="fBu" class="{{ $select }}"><option value="">-- pilih --</option></select>
                </div>
                <div class="min-w-0">
                    <label class="{{ $label }}" for="fDeptFin">Department Fin</label>
                    <select id="fDeptFin" class="{{ $select }}"><option value="">-- pilih --</option></select>
                </div>
                <div class="min-w-0">
                    <label class="{{ $label }}" for="fSearch">Search</label>
                    <input id="fSearch" type="text" placeholder="account / activity..."
                        class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-white/10 dark:bg-[#111827] dark:text-gray-100">
                </div>
                <div class="flex gap-2">
                    <button type="button" id="btnCoaPickerApply"
                        class="inline-flex h-10 flex-1 items-center justify-center rounded-lg bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">Apply</button>
                    <button type="button" id="btnCoaPickerReset"
                        class="inline-flex h-10 flex-1 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-200 dark:hover:bg-white/10">Reset</button>
                </div>
            </div>

            {{-- Table --}}
            <div class="max-h-[52vh] min-h-40 flex-1 overflow-auto rounded-xl border border-gray-100 bg-white shadow-sm dark:border-white/10 dark:bg-[#0f172a]">
                <table class="min-w-[820px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr>
                            <th class="text-left">Account</th>
                            <th class="text-left">Account Descr</th>
                            <th class="text-left">Activity</th>
                            <th class="text-left">Activity Descr</th>
                            <th class="text-left">BU</th>
                            <th class="text-left">DeptFin</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody id="coaPickerTbody">
                        <tr><td colspan="7" class="text-center italic">Pilih filter lalu Apply</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex items-center justify-between gap-3 border-t border-gray-100 bg-gray-50/70 px-6 py-3 dark:border-white/[0.06] dark:bg-white/[0.02]">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <span id="coaPickerInfo">0 rows</span> &middot; Page <span id="coaPickerPage">1</span>
            </p>
            <div class="flex gap-2">
                <button type="button" id="coaPickerPrev" class="{{ $pageBtn }}">Prev</button>
                <button type="button" id="coaPickerNext" class="{{ $pageBtn }}">Next</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Styled filter dropdowns. The page script still fills and reads the plain <select> elements.
    document.addEventListener('DOMContentLoaded', function() {
        const $filters = $('#fCpny, #fBu, #fDeptFin');
        const refresh = () => $filters.trigger('change.select2');
        $filters.each(function() {
            const $el = $(this);
            if (!$el.data('select2')) $el.select2({ width: '100%', minimumResultsForSearch: 8, dropdownParent: $('#coaPickerModal'), dropdownCssClass: 'sf-s2-dropdown' });
            // options rebuilt by the page script -> redraw the field
            new MutationObserver(() => $el.trigger('change.select2')).observe(this, { childList: true });
        });
        // Reset only clears the values, so redraw after the page's own handler has run
        $(document).on('click', '#btnCoaPickerReset', () => setTimeout(refresh, 0));
    });
</script>
