<x-app-layout>

    <div class="max-w-9xl mx-auto space-y-4 p-2">

        {{-- Report Selector --}}
        @php
            $reportGroups = [
                'voucher-stock' => [
                    'label' => 'Voucher Stock Reports',
                    'tabs'  => [
                        'stock-voucher'     => ['icon' => '🎟️', 'label' => 'Stock Voucher'],
                        'stock-summary'     => ['icon' => '📊', 'label' => 'Stock & Aging Summary'],
                        'stock-out-voucher' => ['icon' => '📤', 'label' => 'Stock Out Voucher'],
                        'loyalty-usage'     => ['icon' => '🎁', 'label' => 'Loyalty Usage Rate'],
                    ],
                ],
                'ledger-stock' => [
                    'label' => 'Ledger & Stock Detail',
                    'tabs'  => [
                        'product-stock' => ['icon' => '📦', 'label' => 'Voucher & Product Stock'],
                        'in-out'        => ['icon' => '🔀', 'label' => 'In & Out Voucher Product'],
                        'summary-group' => ['icon' => '⚖️', 'label' => 'Summary Group'],
                    ],
                ],
                'product' => [
                    'label' => 'Product Stock Reports',
                    'tabs'  => [
                        'product-report' => ['icon' => '🧾', 'label' => 'Product Report'],
                    ],
                ],
            ];

            $defaultGroup = collect($reportGroups)
                ->search(fn ($group) => array_key_exists($defaultReport, $group['tabs']));
        @endphp

        <div class="space-y-2">
            {{-- Group Selector --}}
            <nav class="flex flex-wrap gap-1.5" aria-label="Report groups">
                @foreach($reportGroups as $groupKey => $group)
                    <a href="#" data-group="{{ $groupKey }}"
                        @class([
                            'report-group-filter inline-flex shrink-0 items-center rounded-full px-3.5 py-1.5 text-xs font-semibold transition',
                            'bg-indigo-600 text-white shadow-sm' => $defaultGroup === $groupKey,
                            'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' => $defaultGroup !== $groupKey,
                        ])>
                        {{ $group['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Report Tabs (scoped to the active group) --}}
            <div class="border-b border-gray-200 dark:border-gray-700">
                @foreach($reportGroups as $groupKey => $group)
                    <nav data-group-tabs="{{ $groupKey }}"
                        @class(['-mb-px flex gap-1 overflow-x-auto', 'hidden' => $defaultGroup !== $groupKey])
                        aria-label="Report tabs — {{ $group['label'] }}">
                        @foreach($group['tabs'] as $key => $tab)
                            <a href="#" data-report="{{ $key }}"
                                @class([
                                    'report-filter inline-flex shrink-0 items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition',
                                    'border-indigo-500 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400' => $defaultReport === $key,
                                    'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' => $defaultReport !== $key,
                                ])>
                                <span class="text-base">{{ $tab['icon'] }}</span>
                                {{ $tab['label'] }}
                            </a>
                        @endforeach
                    </nav>
                @endforeach
            </div>
        </div>

        {{-- REPORT CONTENT --}}
        <div id="reportContainer">

            <div id="report-stock-voucher" @class(['hidden' => $defaultReport !== 'stock-voucher'])>
                @include('pages.report-vpl.stock-voucher')
            </div>

            <div id="report-stock-summary" @class(['hidden' => $defaultReport !== 'stock-summary'])>
                @include('pages.report-vpl.stock-summary')
            </div>

            <div id="report-loyalty-usage" @class(['hidden' => $defaultReport !== 'loyalty-usage'])>
                @include('pages.report-vpl.loyalty-usage')
            </div>

            <div id="report-stock-out-voucher" @class(['hidden' => $defaultReport !== 'stock-out-voucher'])>
                @include('pages.report-vpl.stock-out-voucher')
            </div>

            <div id="report-in-out" @class(['hidden' => $defaultReport !== 'in-out'])>
                @include('pages.report-vpl.in-out')
            </div>

            <div id="report-product-stock" @class(['hidden' => $defaultReport !== 'product-stock'])>
                @include('pages.report-vpl.product-stock')
            </div>

            <div id="report-product-report" @class(['hidden' => $defaultReport !== 'product-report'])>
                @include('pages.report-vpl.product-report')
            </div>

            <div id="report-summary-group" @class(['hidden' => $defaultReport !== 'summary-group'])>
                @include('pages.report-vpl.summary-group')
            </div>

        </div>

    </div>

    <script>
        const REPORT_TAB_ACTIVE   = ['border-indigo-500', 'text-indigo-600', 'dark:border-indigo-400', 'dark:text-indigo-400'];
        const REPORT_TAB_INACTIVE = ['border-transparent', 'text-gray-500', 'hover:border-gray-300', 'hover:text-gray-700', 'dark:text-gray-400', 'dark:hover:text-gray-200'];

        const GROUP_TAB_ACTIVE   = ['bg-indigo-600', 'text-white', 'shadow-sm'];
        const GROUP_TAB_INACTIVE = ['bg-gray-100', 'text-gray-600', 'hover:bg-gray-200', 'dark:bg-gray-800', 'dark:text-gray-300', 'dark:hover:bg-gray-700'];

        $(document).on('click', '.report-group-filter', function(e) {

            e.preventDefault();

            let group = $(this).data('group');

            $('.report-group-filter').removeClass(GROUP_TAB_ACTIVE).addClass(GROUP_TAB_INACTIVE);

            $(this).removeClass(GROUP_TAB_INACTIVE).addClass(GROUP_TAB_ACTIVE);

            $('[data-group-tabs]').addClass('hidden');

            $('[data-group-tabs="' + group + '"]').removeClass('hidden');

            $('[data-group-tabs="' + group + '"] .report-filter').first().trigger('click');

        });

        $(document).on('click', '.report-filter', function(e) {

            e.preventDefault();

            let report = $(this).data('report');

            $('.report-filter').removeClass(REPORT_TAB_ACTIVE).addClass(REPORT_TAB_INACTIVE);

            $(this).removeClass(REPORT_TAB_INACTIVE).addClass(REPORT_TAB_ACTIVE);

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
