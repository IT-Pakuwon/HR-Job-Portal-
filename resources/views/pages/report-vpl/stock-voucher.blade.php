<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Company</label>
                <select id="stockvp_cpnyid" class="form-input w-full">
                    @foreach($companies ?? [] as $cpny)
                        <option value="{{ $cpny }}">{{ $cpny }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Month</label>
                <select id="stockvp_month" class="form-input w-full">
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" @selected($m == now()->month)>
                            {{ \Carbon\Carbon::create()->month($m)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Year</label>
                <select id="stockvp_year" class="form-input w-full">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="stockvp_load" class="w-full rounded-lg bg-purple-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-purple-700">
                    Show Report
                </button>
                <button type="button" id="stockvp_export" class="whitespace-nowrap rounded-lg border border-purple-600 px-4 py-2.5 text-sm font-semibold text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20">
                    Export Excel
                </button>
            </div>

        </div>
    </div>

    {{-- REPORT TABLE --}}
    <div id="stockvp_table">
        <div class="flex items-center justify-center rounded-2xl border border-dashed border-gray-300 p-16 text-center text-sm text-gray-500 dark:border-gray-700 dark:text-gray-400">
            Choose a company/month/year and click "Show Report".
        </div>
    </div>

</div>

<script>
    (function () {
        const jsonUrl = @json(route('report.vpl.json', ['type' => 'stock-voucher']));
        const exportUrl = @json(route('report.vpl.export', ['type' => 'stock-voucher']));

        function currentParams() {
            return new URLSearchParams({
                cpnyid: $('#stockvp_cpnyid').val(),
                month: $('#stockvp_month').val(),
                year: $('#stockvp_year').val(),
            });
        }

        function loadStockVoucher() {
            $('#stockvp_table').html('<div class="p-8 text-center text-sm text-gray-500">Loading...</div>');

            $.get(jsonUrl + '?' + currentParams().toString())
                .done(function (html) {
                    $('#stockvp_table').html(html);
                })
                .fail(function () {
                    $('#stockvp_table').html('<div class="p-8 text-center text-sm text-red-500">Failed to load report.</div>');
                });
        }

        $(document).on('click', '#stockvp_load', loadStockVoucher);

        $(document).on('click', '#stockvp_export', function () {
            window.location.href = exportUrl + '?' + currentParams().toString();
        });
    })();
</script>
