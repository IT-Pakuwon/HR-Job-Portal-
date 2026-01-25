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
                        Supplier (soon)
                    </button>
                    <button type="button" data-tab="tab-po"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        PO (soon)
                    </button>
                    <button type="button" data-tab="tab-sttb"
                        class="tab-btn rounded-md px-4 py-2 text-sm font-medium text-gray-600 hover:border-gray-200 hover:bg-white">
                        STTB (soon)
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

                {{-- Placeholder tabs --}}
                <div id="tab-supplier" class="hidden text-sm text-gray-500">Supplier tab (soon)</div>
                <div id="tab-po" class="hidden text-sm text-gray-500">PO tab (soon)</div>
                <div id="tab-sttb" class="hidden text-sm text-gray-500">STTB tab (soon)</div>
            </div>
        </div>
    </div>

    <script>
        // Tabs
        const emptyState = document.getElementById('emptyState');
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabPanels = ['tab-nonstock', 'tab-stock', 'tab-supplier', 'tab-po', 'tab-sttb']
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
