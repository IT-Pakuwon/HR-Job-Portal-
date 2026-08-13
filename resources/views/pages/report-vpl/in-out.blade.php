<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-amber-50/30 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800/40 dark:to-amber-900/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-building mr-1 text-gray-400"></i> Company
                </label>
                <select id="inout_cpnyid" class="form-input w-full">
                    @foreach($companies ?? [] as $cpny)
                        <option value="{{ $cpny }}">{{ $cpny }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-calendar-days mr-1 text-gray-400"></i> Month
                </label>
                <select id="inout_month" class="form-input w-full">
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
                <select id="inout_year" class="form-input w-full">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="inout_load" class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-black hover:shadow active:scale-[0.98] dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Show Report
                </button>
                <button type="button" id="inout_export" class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700/40">
                    <i class="fa-solid fa-file-arrow-down"></i> Export Excel
                </button>
            </div>

        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-hashtag mr-1 text-gray-400"></i> Ref No
                </label>
                <select id="inout_f_refnbr" class="w-full" data-select2-field="refnbr">
                    <option value="">All</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-tag mr-1 text-gray-400"></i> Product Name
                </label>
                <select id="inout_f_product_name" class="w-full" data-select2-field="product_name">
                    <option value="">All</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-right-left mr-1 text-gray-400"></i> Type
                </label>
                <select id="inout_f_type" class="w-full">
                    <option value="">All</option>
                    <option value="Receive">Receive</option>
                    <option value="Transfer In">Transfer In</option>
                    <option value="Transfer Out">Transfer Out</option>
                    <option value="Usage">Usage</option>
                    <option value="Return">Return</option>
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-link mr-1 text-gray-400"></i> Reference Number
                </label>
                <select id="inout_f_reference_refnbr" class="w-full" data-select2-field="reference_refnbr">
                    <option value="">All</option>
                </select>
            </div>

        </div>
    </div>

    {{-- REPORT TABLE --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-md ring-1 ring-gray-900/5 dark:border-gray-700 dark:bg-gray-800 dark:ring-white/5">

        <div class="flex items-center gap-2 border-b border-gray-200 bg-linear-to-r from-white to-amber-50/30 px-6 py-4 dark:border-gray-700 dark:from-gray-800 dark:to-amber-900/10">
            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300">
                <i class="fa-solid fa-right-left text-xs"></i>
            </span>
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">In &amp; Out Voucher Product</h2>
        </div>

        <div id="inOutPlaceholder" class="flex flex-col items-center justify-center gap-2 p-16 text-center">
            <i class="fa-solid fa-chart-column text-2xl text-gray-300 dark:text-gray-600"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400">Choose a company/month/year and click "Show Report".</p>
        </div>

        <div id="inOutTableWrap" class="hidden overflow-x-auto p-4">
            <table id="inOutTable" class="w-full min-w-[1400px] text-sm">
                <thead class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th>Ref No</th>
                        <th>CreateDate</th>
                        <th>CpnyID</th>
                        <th>Type</th>
                        <th>PostDate</th>
                        <th>Product ID</th>
                        <th>Expired Date</th>
                        <th>Product Name</th>
                        <th>Qty</th>
                        <th>Reference Refnbr</th>
                        <th>Purpose</th>
                        <th>Warehouse</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

    </div>

</div>

<script>
    window.VplInOutReport = (function () {
        let table = null;

        function baseParams() {
            return {
                cpnyid: $('#inout_cpnyid').val(),
                month: $('#inout_month').val(),
                year: $('#inout_year').val(),
            };
        }

        function currentParams() {
            return Object.assign(baseParams(), {
                refnbr: $('#inout_f_refnbr').val() || '',
                product_name: $('#inout_f_product_name').val() || '',
                type: $('#inout_f_type').val() || '',
                reference_refnbr: $('#inout_f_reference_refnbr').val() || '',
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
                        url: @json(route('report.vpl.inout.options')),
                        dataType: 'json',
                        delay: 250,
                        data: (params) => Object.assign({ field: field, term: params.term || '' }, baseParams()),
                        processResults: (data) => ({ results: data.results }),
                    },
                });
            });

            $('#inout_f_type').select2({ width: '100%', allowClear: true, placeholder: 'All' });
        }

        function loadReport() {
            $('#inOutPlaceholder').addClass('hidden');
            $('#inOutTableWrap').removeClass('hidden');

            if (table) {
                table.ajax.reload();
                return;
            }

            table = $('#inOutTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('report.vpl.inout.data')),
                    type: 'GET',
                    data: function (d) { Object.assign(d, currentParams()); },
                },
                columns: [
                    { data: 'refnbr', name: 'l.refnbr' },
                    { data: 'create_date', name: 'create_date' },
                    { data: 'cpnyid', name: 'l.cpnyid' },
                    { data: 'type_label', name: 'type_label' },
                    { data: 'post_date', name: 'post_date' },
                    { data: 'product_id', name: 'l.product_id' },
                    { data: 'expired_date_fmt', name: 'expired_date_fmt' },
                    { data: 'product_name', name: 'p.product_name', defaultContent: '-' },
                    { data: 'qty', name: 'l.qty', searchable: false, className: 'text-right' },
                    { data: 'reference_refnbr', name: 'reference_refnbr' },
                    { data: 'purpose_id', name: 'purpose_id' },
                    { data: 'whs_id', name: 'l.whs_id' },
                ],
                order: [[1, 'desc']],
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

        $(document).off('click.inoutLoad').on('click.inoutLoad', '#inout_load', loadReport);

        $(document).off('click.inoutExport').on('click.inoutExport', '#inout_export', function () {
            const exportUrl = @json(route('report.vpl.inout.export'));
            window.location.href = exportUrl + '?' + $.param(currentParams());
        });

        function showTab() {
            if (table) table.columns.adjust();
        }

        return { showTab };
    })();
</script>
