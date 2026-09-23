<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-sky-50/30 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800/40 dark:to-sky-900/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-building mr-1 text-gray-400"></i> Company
                </label>
                <select id="pstock_cpnyid" class="form-input w-full">
                    @foreach($companies ?? [] as $cpny)
                        <option value="{{ $cpny }}">{{ $cpny }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-calendar-days mr-1 text-gray-400"></i> Month
                </label>
                <select id="pstock_month" class="form-input w-full">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m == now()->month)>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-calendar mr-1 text-gray-400"></i> Year
                </label>
                <select id="pstock_year" class="form-input w-full">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="pstock_load" class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-black hover:shadow active:scale-[0.98] dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Show Report
                </button>
                <button type="button" id="pstock_export" class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700/40">
                    <i class="fa-solid fa-file-arrow-down"></i> Export Excel
                </button>
            </div>

        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-hashtag mr-1 text-gray-400"></i> Product ID
                </label>
                <select id="pstock_f_product_id" class="w-full" data-select2-field="product_id">
                    <option value="">All</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-tag mr-1 text-gray-400"></i> Name
                </label>
                <select id="pstock_f_product_name" class="w-full" data-select2-field="product_name">
                    <option value="">All</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-warehouse mr-1 text-gray-400"></i> Warehouse
                </label>
                <select id="pstock_f_whs_id" class="w-full" data-select2-field="whs_id">
                    <option value="">All</option>
                </select>
            </div>

        </div>
    </div>

    {{-- REPORT TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

        <div class="flex items-center gap-2 border-b border-gray-200 bg-linear-to-r from-white to-sky-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-sky-900/10">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-sky-100 text-sky-600 dark:bg-sky-900/40 dark:text-sky-300">
                <i class="fa-solid fa-boxes-stacked text-xs"></i>
            </span>
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Voucher &amp; Product Stock</h2>
        </div>

        <div id="pstockPlaceholder" class="flex flex-col items-center justify-center gap-2 p-16 text-center">
            <i class="fa-solid fa-chart-column text-2xl text-gray-300 dark:text-gray-600"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400">Choose a company/month/year and click "Show Report".</p>
        </div>

        <div id="pstockTableWrap" class="hidden overflow-x-auto p-4">
            <table id="productStockTable" class="w-full min-w-[1200px] text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th>Company</th>
                        <th>Product ID</th>
                        <th>Expired Date</th>
                        <th>Name</th>
                        <th>Value</th>
                        <th>Uom</th>
                        <th>Warehouse</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>

</div>

<script>
    window.VplProductStockReport = (function () {
        let table = null;

        function baseParams() {
            return {
                cpnyid: $('#pstock_cpnyid').val(),
                month: $('#pstock_month').val(),
                year: $('#pstock_year').val(),
            };
        }

        function currentParams() {
            return Object.assign(baseParams(), {
                product_id: $('#pstock_f_product_id').val() || '',
                product_name: $('#pstock_f_product_name').val() || '',
                whs_id: $('#pstock_f_whs_id').val() || '',
            });
        }

        function initFilterSelects() {
            $('select[data-select2-field]').each(function () {
                const field = $(this).data('select2-field');

                $(this).select2({
                    width: '100%',
                    allowClear: true,
                    placeholder: 'All',
                    minimumInputLength: 0,
                    ajax: {
                        url: @json(route('report.vpl.productstock.options')),
                        dataType: 'json',
                        delay: 250,
                        data: (params) => Object.assign({ field: field, term: params.term || '' }, baseParams()),
                        processResults: (data) => ({ results: data.results }),
                    },
                });
            });
        }

        function loadReport() {
            $('#pstockPlaceholder').addClass('hidden');
            $('#pstockTableWrap').removeClass('hidden');

            if (table) {
                table.ajax.reload();
                return;
            }

            table = $('#productStockTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('report.vpl.productstock.data')),
                    type: 'GET',
                    data: function (d) { Object.assign(d, currentParams()); },
                },
                columns: [
                    { data: 'cpnyid', name: 'b.cpnyid' },
                    { data: 'product_id', name: 'b.product_id' },
                    { data: 'expired_date_fmt', name: 'expired_date_fmt' },
                    { data: 'product_name', name: 'p.product_name', defaultContent: '-' },
                    { data: 'product_value', name: 'p.product_value', searchable: false, className: 'text-right' },
                    { data: 'product_uom', name: 'p.product_uom' },
                    { data: 'whs_id', name: 'b.whs_id' },
                    { data: 'stock', name: 'stock', searchable: false, className: 'text-right' },
                ],
                order: [[1, 'asc']],
                pageLength: 10,
                lengthMenu: [10, 25, 50, 100],
                dom: 'lfrtip',
                createdRow(row) {
                    $(row).addClass('border-b border-gray-100 dark:border-gray-700');
                    $('td', row).addClass('px-2 py-2 text-gray-700 dark:text-gray-300');
                },
            });
        }

        initFilterSelects();

        $(document).off('click.pstockLoad').on('click.pstockLoad', '#pstock_load', loadReport);

        $(document).off('click.pstockExport').on('click.pstockExport', '#pstock_export', function () {
            const exportUrl = @json(route('report.vpl.productstock.export'));
            window.location.href = exportUrl + '?' + $.param(currentParams());
        });

        function showTab() {
            if (table) table.columns.adjust();
        }

        return { showTab };
    })();
</script>
