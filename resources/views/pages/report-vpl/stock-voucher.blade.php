<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-linear-to-br from-gray-50 to-purple-50/30 p-6 shadow-sm dark:border-gray-700 dark:from-gray-800/40 dark:to-purple-900/10">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4 items-end">

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-building mr-1 text-gray-400"></i> Company
                </label>
                <select id="stockvp_cpnyid" class="form-input w-full">
                    @foreach($companies ?? [] as $cpny)
                        <option value="{{ $cpny }}">{{ $cpny }}</option>
                    @endforeach
                </select>
            </div>

            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500 dark:text-gray-400">
                    <i class="fa-solid fa-calendar-days mr-1 text-gray-400"></i> Month
                </label>
                <select id="stockvp_month" class="form-input w-full">
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
                <select id="stockvp_year" class="form-input w-full">
                    @foreach(range(now()->year, now()->year - 3) as $y)
                        <option value="{{ $y }}" @selected($y == now()->year)>{{ $y }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="button" id="stockvp_load" class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-black hover:shadow active:scale-[0.98] dark:bg-gray-100 dark:text-gray-900 dark:hover:bg-white">
                    <i class="fa-solid fa-magnifying-glass-chart"></i> Show Report
                </button>
                <button type="button" id="stockvp_export" class="flex items-center justify-center gap-2 whitespace-nowrap rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:border-gray-400 hover:bg-gray-50 active:scale-[0.98] dark:border-gray-600 dark:bg-transparent dark:text-gray-200 dark:hover:bg-gray-700/40">
                    <i class="fa-solid fa-file-arrow-down"></i> Export Excel
                </button>
            </div>

        </div>
    </div>

    {{-- REPORT TABLE --}}
    <div id="stockvp_table">
        <div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-dashed border-gray-300 p-16 text-center dark:border-gray-700">
            <i class="fa-solid fa-chart-column text-2xl text-gray-300 dark:text-gray-600"></i>
            <p class="text-sm text-gray-500 dark:text-gray-400">Choose a company/month/year and click "Show Report".</p>
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
            $('#stockvp_table').html(
                '<div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-gray-200 bg-white p-16 dark:border-gray-700 dark:bg-gray-800">' +
                    '<i class="fa-solid fa-circle-notch fa-spin text-2xl text-purple-500"></i>' +
                    '<p class="text-sm text-gray-500 dark:text-gray-400">Loading report...</p>' +
                '</div>'
            );

            $.get(jsonUrl + '?' + currentParams().toString())
                .done(function (html) {
                    $('#stockvp_table').html(html);
                })
                .fail(function () {
                    $('#stockvp_table').html(
                        '<div class="flex flex-col items-center justify-center gap-2 rounded-2xl border border-rose-200 bg-rose-50 p-16 dark:border-rose-900/40 dark:bg-rose-900/10">' +
                            '<i class="fa-solid fa-triangle-exclamation text-2xl text-rose-500"></i>' +
                            '<p class="text-sm text-rose-600 dark:text-rose-400">Failed to load report.</p>' +
                        '</div>'
                    );
                });
        }

        $(document).on('click', '#stockvp_load', loadStockVoucher);

        $(document).on('click', '#stockvp_export', function () {
            window.location.href = exportUrl + '?' + currentParams().toString();
        });
    })();
</script>
