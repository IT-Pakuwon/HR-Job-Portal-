<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            {{-- Header utama --}}
            <div class="border-b border-gray-200 px-6 py-5">
                <h1 class="text-2xl font-bold text-gray-800">IFCA Integration</h1>
                <p class="mt-1 text-sm text-gray-500">Scheduler & Integration Master & Transaction Data</p>
            </div>

            <div class="p-6">
                {{-- ===================== --}}
                {{-- SECTION : IFCA        --}}
                {{-- ===================== --}}
                <div class="mb-4 rounded-xl border border-slate-200 bg-slate-50/60">
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

                                <h2 class="text-sm font-semibold text-gray-800">
                                    IFCA Integration
                                </h2>
                            </div>

                            {{-- tab kanan --}}
                            <div class="flex flex-wrap gap-1.5 lg:pl-1">
                                <button type="button" data-tab="tab-nonstock"
                                    class="tab-btn rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50">
                                    Non Stock
                                </button>

                                <button type="button" data-tab="tab-stock"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    Stock
                                </button>

                                <button type="button" data-tab="tab-supplier"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    Supplier
                                </button>

                                <button type="button" data-tab="tab-po"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    PO
                                </button>

                                <button type="button" data-tab="tab-sttb"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    GRN
                                </button>

                                <button type="button" data-tab="tab-bast"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    BAST
                                </button>

                                <button type="button" data-tab="tab-issue"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    Issue
                                </button>

                                <button type="button" data-tab="tab-sttb-return"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    STTB Return (soon)
                                </button>

                                <button type="button" data-tab="tab-receipt"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    Receipt (soon)
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

                                <h2 class="text-sm font-semibold text-gray-800">
                                    Solomon Integration
                                </h2>
                            </div>

                            {{-- tab kanan --}}
                            <div class="flex flex-wrap gap-1.5 lg:pl-1">
                                <button type="button" data-tab="tab-po-sl"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    PO Solomon
                                </button>

                                <button type="button" data-tab="tab-grn-sl"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    GRN Solomon
                                </button>

                                <button type="button" data-tab="tab-issue-sl"
                                    class="tab-btn rounded-lg border border-transparent px-3 py-1.5 text-sm font-medium text-gray-600 transition hover:border-gray-200 hover:bg-white">
                                    Issue Solomon
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Empty State --}}
                <div id="emptyState" class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
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

                <div id="tab-bast" class="hidden">
                    @include('pages.integration.ifcaapibast')
                </div>

                <div id="tab-issue" class="hidden">
                    @include('pages.integration.ifcaapiissue')
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

                <div id="tab-sttb-return" class="hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
                    STTB Return tab (soon)
                </div>

                <div id="tab-receipt" class="hidden rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-500">
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
                'tab-bast',
                'tab-issue',
                'tab-po-sl',
                'tab-grn-sl',
                'tab-issue-sl',
                'tab-sttb-return',
                'tab-receipt'
            ];

            const tabPanels = panelIds
                .map(id => document.getElementById(id))
                .filter(Boolean);

            function resetButtons() {
                tabButtons.forEach(btn => {
                    btn.classList.remove(
                        'bg-white',
                        'border-gray-200',
                        'shadow-sm',
                        'text-gray-800',
                        'ring-1',
                        'ring-gray-200'
                    );

                    btn.classList.add(
                        'border-transparent',
                        'text-gray-600'
                    );
                });
            }

            function hideAllPanels() {
                tabPanels.forEach(panel => panel.classList.add('hidden'));
            }

            tabButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.dataset.tab;
                    const target = document.getElementById(targetId);

                    resetButtons();

                    btn.classList.add(
                        'bg-white',
                        'border-gray-200',
                        'shadow-sm',
                        'text-gray-800',
                        'ring-1',
                        'ring-gray-200'
                    );
                    btn.classList.remove('border-transparent', 'text-gray-600');

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