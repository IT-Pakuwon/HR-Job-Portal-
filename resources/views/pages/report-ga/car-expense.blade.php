<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-5">

            {{-- DATE FROM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date From
                </label>

                <input type="date" id="ce_date_from" class="form-input w-full">
            </div>

            {{-- DATE TO --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date To
                </label>

                <input type="date" id="ce_date_to" class="form-input w-full">
            </div>

            {{-- VEHICLE --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Vehicle
                </label>

                <select id="ce_nopol" class="form-input w-full">

                    <option value="">
                        All Vehicles
                    </option>

                    @foreach ($kendaraan as $k)
                        <option value="{{ $k->nopol_kendaraan }}">
                            {{ $k->nopol_kendaraan }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- DRIVER --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Driver
                </label>

                <input type="text" id="ce_driver" placeholder="Search driver..."
                    class="form-input w-full">
            </div>

            {{-- ACTION --}}
            <div class="flex items-end justify-end gap-2">

                <button id="ceFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button id="ceResetBtn"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50">
                    Reset
                </button>

                <button id="ceExportBtn"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                    Export
                </button>

            </div>

        </div>

    </div>

    {{-- TABLE --}}
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-800">
                Car Expense Report
            </h2>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="carExpenseTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th>Ref No</th>
                        <th>Date</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Cost Type</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Amount</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        let type = 'car-expense';

        let table = $('#carExpenseTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            dom: "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                'rt' +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            ajax: {
                url: '/report-ga/json/' + type,

                data: function(d) {
                    d.date_from = $('#ce_date_from').val();
                    d.date_to   = $('#ce_date_to').val();
                    d.nopol     = $('#ce_nopol').val();
                    d.driver    = $('#ce_driver').val();
                }
            },

            columns: [
                { data: 'refnbr',            name: 'refnbr' },
                { data: 'ref_date',          name: 'ref_date' },
                { data: 'nopol',             name: 'nopol',    defaultContent: '-' },
                { data: 'driver',            name: 'driver',   defaultContent: '-' },
                { data: 'cost_type_name',    name: 'cost_type', defaultContent: '-', orderable: false },
                { data: 'cost_descr',        name: 'cost_descr', defaultContent: '-', orderable: false },
                { data: 'cost_qty',          name: 'cost_qty',  defaultContent: '-' },
                { data: 'cost_amount_label', name: 'cost_amount', defaultContent: '-' },
            ],

            order: [
                [1, 'desc']
            ]
        });

        $('#ceFilterBtn').click(function() {
            table.ajax.reload();
        });

        $('#ceResetBtn').click(function() {
            $('#ce_date_from').val('');
            $('#ce_date_to').val('');
            $('#ce_nopol').val('');
            $('#ce_driver').val('');
            table.ajax.reload();
        });

        $('#ceExportBtn').click(function() {

            let url = '/report-ga/export/' + type;

            url += '?date_from=' + $('#ce_date_from').val();
            url += '&date_to='   + $('#ce_date_to').val();
            url += '&nopol='     + $('#ce_nopol').val();
            url += '&driver='    + $('#ce_driver').val();

            window.location.href = url;
        });

    });
</script>
