<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        {{-- Tailwind safelist: lg:grid-cols-1 lg:grid-cols-2 lg:grid-cols-3 lg:grid-cols-4 lg:grid-cols-5 lg:grid-cols-6 --}}
        @php
            $lgCols = match(true) {
                $tabCount <= 1 => 'lg:grid-cols-1',
                $tabCount == 2 => 'lg:grid-cols-2',
                $tabCount == 3 => 'lg:grid-cols-3',
                $tabCount == 4 => 'lg:grid-cols-4',
                $tabCount == 5 => 'lg:grid-cols-5',
                default        => 'lg:grid-cols-6',
            };
        @endphp
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-2 {{ $lgCols }}">

            {{-- Stock Voucher --}}
            <a href="#" data-report="stock-voucher"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 text-lg">
                        🎟️
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Stock Voucher</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Voucher stock movement by warehouse</p>
                    </div>
                </div>
            </a>

            {{-- Stock & Aging Summary --}}
            <a href="#" data-report="stock-summary"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 text-lg">
                        📊
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Stock & Aging Summary</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Per-tenant aging, sources & usage breakdown</p>
                    </div>
                </div>
            </a>

            @if($hasVPLOYALTYACCESS)
                {{-- Loyalty Usage Rate --}}
                <a href="#" data-report="loyalty-usage"
                    class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-lg">
                            🎁
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800 dark:text-gray-200">Loyalty Usage Rate</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Usage at WHLOYALTY vs. stock at WHLOYALTY</p>
                        </div>
                    </div>
                </a>
            @endif

            {{-- In & Out Voucher Product --}}
            <a href="#" data-report="in-out"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-lg">
                        🔀
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">In &amp; Out Voucher Product</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Every ledger movement, every warehouse</p>
                    </div>
                </div>
            </a>

            {{-- Voucher & Product Stock --}}
            <a href="#" data-report="product-stock"
                class="report-filter group block rounded-xl border border-gray-200 bg-white/70 p-4 shadow-sm transition hover:-translate-y-1 hover:shadow-md dark:border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-sky-100 text-lg">
                        📦
                    </div>
                    <div>
                        <p class="font-semibold text-gray-800 dark:text-gray-200">Voucher & Product Stock</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Current stock per product, expiry & warehouse</p>
                    </div>
                </div>
            </a>

        </div>

        {{-- REPORT CONTENT --}}
        <div id="reportContainer">

            <div id="report-stock-voucher" @class(['hidden' => $defaultReport !== 'stock-voucher'])>
                @include('pages.report-vpl.stock-voucher')
            </div>

            <div id="report-stock-summary" @class(['hidden' => $defaultReport !== 'stock-summary'])>
                @include('pages.report-vpl.stock-summary')
            </div>

            @if($hasVPLOYALTYACCESS)
                <div id="report-loyalty-usage" @class(['hidden' => $defaultReport !== 'loyalty-usage'])>
                    @include('pages.report-vpl.loyalty-usage')
                </div>
            @endif

            <div id="report-in-out" @class(['hidden' => $defaultReport !== 'in-out'])>
                @include('pages.report-vpl.in-out')
            </div>

            <div id="report-product-stock" @class(['hidden' => $defaultReport !== 'product-stock'])>
                @include('pages.report-vpl.product-stock')
            </div>

        </div>

    </div>

    <script>
        $(document).on('click', '.report-filter', function(e) {

            e.preventDefault();

            let report = $(this).data('report');

            $('#reportContainer > div').addClass('hidden');

            $('#report-' + report).removeClass('hidden');

            if (report === 'in-out' && window.VplInOutReport) {
                window.VplInOutReport.showTab();
            }

            if (report === 'product-stock' && window.VplProductStockReport) {
                window.VplProductStockReport.showTab();
            }

        });
    </script>

</x-app-layout>
