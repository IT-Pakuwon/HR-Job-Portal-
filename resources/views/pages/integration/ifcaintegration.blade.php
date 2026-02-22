<x-app-layout>
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="rounded-xl border border-gray-200 bg-white">
            <div class="border-b border-gray-200 px-5 py-4">
                <h1 class="text-xl font-semibold text-gray-800">IFCA Integration</h1>
                <p class="text-sm text-gray-500">Scheduler & Integration Master & Transaction Data</p>
            </div>

            {{-- Tabs --}}
            <div class="px-5 pt-4">
                <div class="inline-flex gap-2 rounded-lg border border-gray-200 bg-gray-50 p-1">
                    <button type="button" data-tab="tab-nonstock"
                        class="tab-btn rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium shadow-sm">
                        Non Stock
                    </button>
                    <button type="button" data-tab="tab-stock"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        Stock
                    </button>
                    <button type="button" data-tab="tab-supplier"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        Supplier
                    </button>
                    <button type="button" data-tab="tab-po"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        PO
                    </button>
                    <button type="button" data-tab="tab-sttb"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        GRN
                    </button>
                    <button type="button" data-tab="tab-bast"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        BAST (soon)
                    </button>
                    <button type="button" data-tab="tab-sttb-return"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        STTB Return (soon)
                    </button>
                    <button type="button" data-tab="tab-issue"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        Issue (soon)
                    </button>
                    <button type="button" data-tab="tab-receipt"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        Receipt (soon)
                    </button>
                </div>
            </div>

            <div class="p-5">
                <div id="emptyState" class="text-sm text-gray-500">
                    Klik tab untuk menampilkan data.
                </div>

                {{-- TAB: Non Stock (partial view) --}}
                <div id="tab-nonstock" class="hidden">
                    @include('pages.integration.ifcaapinonstock')
                </div>

                {{-- TAB: Stock --}}
                <div id="tab-stock" class="hidden">
                    @include('pages.integration.ifcaapistock')
                </div>

                {{-- TAB: Supplier --}}
                <div id="tab-supplier" class="hidden">
                    @include('pages.integration.ifcaapisupplier')
                </div>

                {{-- TAB: PO --}}
                <div id="tab-po" class="hidden">
                    @include('pages.integration.ifcaapipo')
                </div>

                {{-- TAB: GRN --}}
                <div id="tab-sttb" class="hidden">
                    @include('pages.integration.ifcaapigrn')
                </div>

                {{-- Placeholder tabs --}}
                <div id="tab-bast" class="hidden text-sm text-gray-500">BAST tab (soon)</div>
                <div id="tab-sttb-return" class="hidden text-sm text-gray-500">STTB Return tab (soon)</div>
                <div id="tab-issue" class="hidden text-sm text-gray-500">Issue tab (soon)</div>
                <div id="tab-receipt" class="hidden text-sm text-gray-500">Receipt tab (soon)</div>


            </div>
        </div>
    </div>

    <script>
        // Tabs
        const emptyState = document.getElementById('emptyState');
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanels = ['tab-nonstock', 'tab-stock', 'tab-supplier', 'tab-po', 'tab-sttb', 'tab-bast']
            .map(id => document.getElementById(id));

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                tabButtons.forEach(b => b.classList.remove('bg-white', 'border', 'border-gray-200', 'shadow-sm'));
                btn.classList.add('bg-white', 'border', 'border-gray-200', 'shadow-sm');

                tabPanels.forEach(p => p.classList.add('hidden'));
                document.getElementById(btn.dataset.tab).classList.remove('hidden');
                emptyState.classList.add('hidden');
            });
        });
    </script>
</x-app-layout>
