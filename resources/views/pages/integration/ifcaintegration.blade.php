<x-app-layout>
    <style>
        .tab-btn:hover { border-color: #93c5fd; background: #eff6ff; color: #1d4ed8; }
        .tab-btn.is-on { border-color: var(--color-blue-600); background: var(--color-blue-600); color: #fff; }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            {{-- Header utama --}}
            <div class="flex items-center gap-3 border-b border-gray-100 px-5 py-3.5 dark:border-white/[0.06]">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm"><i class="fa-solid fa-right-left"></i></span>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-gray-800 dark:text-gray-100">IFCA Integration</h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Scheduler &amp; Integration Master &amp; Transaction Data</p>
                </div>
            </div>

            <div class="p-5">
                {{-- ===================== --}}
                {{-- SECTION : IFCA        --}}
                {{-- ===================== --}}
                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50/60 dark:border-slate-700">
                    <div class="px-4 py-2.5">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
                            {{-- title kiri --}}
                            <div class="flex min-w-[200px] items-center gap-2.5 lg:shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3 7h18M3 12h18M3 17h18" />
                                    </svg>
                                </div>

                                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    IFCA Integration
                                </h2>
                            </div>

                            {{-- tab kanan --}}
                            <div class="flex flex-wrap gap-1.5 lg:pl-1">
                                <button type="button" data-tab="tab-nonstock"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Non Stock
                                </button>

                                <button type="button" data-tab="tab-stock"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Stock
                                </button>

                                <button type="button" data-tab="tab-supplier"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Supplier
                                </button>

                                <button type="button" data-tab="tab-po"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    PO
                                </button>

                                <button type="button" data-tab="tab-sttb"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    GRN
                                </button>

                                <button type="button" data-tab="tab-grn-return"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    GRN Return
                                </button>

                                <button type="button" data-tab="tab-bast"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    BAST
                                </button>

                                <button type="button" data-tab="tab-issue"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Issue
                                </button>

                                <button type="button" data-tab="tab-issue-return"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Issue Return
                                </button>

                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===================== --}}
                {{-- SECTION : SOLOMON     --}}
                {{-- ===================== --}}
                <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50/50">
                    <div class="px-4 py-2.5">
                        <div class="flex flex-col gap-2 lg:flex-row lg:items-center">
                            {{-- title kiri --}}
                            <div class="flex min-w-[200px] items-center gap-2.5 lg:shrink-0">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                </div>

                                <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                    Solomon Integration
                                </h2>
                            </div>

                            {{-- tab kanan --}}
                            <div class="flex flex-wrap gap-1.5 lg:pl-1">
                                <button type="button" data-tab="tab-po-sl"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    PO Solomon
                                </button>

                                <button type="button" data-tab="tab-grn-sl"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    GRN Solomon
                                </button>

                                <button type="button" data-tab="tab-issue-sl"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3.5 py-1.5 text-sm font-semibold text-gray-600 shadow-sm transition dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                    Issue Solomon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="emptyState" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                    Klik tab untuk menampilkan data.
                </div>

                {{-- ===================== --}}
                {{-- TAB CONTENT           --}}
                {{-- ===================== --}}
                <div id="tab-nonstock" class="hidden">
                    @include('pages.integration.ifcaapinonstock')
                </div>

                <div id="tab-stock" class="hidden">
                    @include('pages.integration.ifcaapistock')
                </div>

                <div id="tab-supplier" class="hidden">
                    @include('pages.integration.ifcaapisupplier')
                </div>

                <div id="tab-po" class="hidden">
                    @include('pages.integration.ifcaapipo')
                </div>

                <div id="tab-sttb" class="hidden">
                    @include('pages.integration.ifcaapigrn')
                </div>

                <div id="tab-grn-return" class="hidden">
                    @include('pages.integration.ifcaapigrnreturn')
                </div>

                <div id="tab-bast" class="hidden">
                    @include('pages.integration.ifcaapibast')
                </div>

                <div id="tab-issue" class="hidden">
                    @include('pages.integration.ifcaapiissue')
                </div>

                <div id="tab-issue-return" class="hidden">
                    @include('pages.integration.ifcaapiissuereturn')
                </div>

                <div id="tab-po-sl" class="hidden">
                    @include('pages.integration.slapipo')
                </div>

                <div id="tab-grn-sl" class="hidden">
                    @include('pages.integration.slapigrn')
                </div>

                <div id="tab-issue-sl" class="hidden">
                    @include('pages.integration.slapissue')
                </div>

                <div id="tab-receipt" class="hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500 dark:bg-gray-900 dark:text-gray-400 dark:border-gray-700">
                    Receipt tab (soon)
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const emptyState = document.getElementById('emptyState');
            const tabButtons = document.querySelectorAll('.tab-btn');

            const panelIds = [
                'tab-nonstock',
                'tab-stock',
                'tab-supplier',
                'tab-po',
                'tab-sttb',
                'tab-grn-return',
                'tab-bast',
                'tab-issue',
                'tab-issue-return',
                'tab-po-sl',
                'tab-grn-sl',
                'tab-issue-sl'
            ];

            const tabPanels = panelIds
                .map(id => document.getElementById(id))
                .filter(Boolean);

            function resetButtons() {
                tabButtons.forEach(btn => btn.classList.remove('is-on'));
            }

            function hideAllPanels() {
                tabPanels.forEach(panel => panel.classList.add('hidden'));
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.dataset.tab;
                    const target = document.getElementById(targetId);

                    resetButtons();

                    btn.classList.add('is-on');

                    hideAllPanels();

                    if (target) {
                        target.classList.remove('hidden');
                    }

                    if (emptyState) {
                        emptyState.classList.add('hidden');
                    }
                });
            });
        });
    </script>
</x-app-layout>
