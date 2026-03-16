<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="sppb_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="sppb_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="text-xs text-gray-500">SPPB No</label>
                <input type="text" id="sppbid" placeholder="PB-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="text-xs text-gray-500">Inventory ID</label>
                <input type="text" id="inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div class="flex items-end gap-2 md:col-span-2">

                <button id="filterSppb" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">
                    Apply
                </button>

                <button id="resetSppb" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="exportSppb"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                    Export
                </button>

            </div>

        </div>

    </div>


    <!-- TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-gray-800">
                SPPB Detail
            </h2>

        </div>

        <div class="overflow-x-auto p-5">

            <table id="sppbTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                    <tr>
                        <th>Date</th>
                        <th>SPPB No</th>
                        {{-- <th>SPB No</th> --}}
                        <th>Department</th>
                        <th>Requester</th>
                        <th>Purchasing</th>
                        <th>Inventory ID</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>UOM</th>
                        <th>Warehouse</th>
                        <th>Status</th>
                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>


<script>
    $(function() {

        var table = $('#sppbTable').DataTable({

            processing: true,
            serverSide: true,
            searching: false,

            ajax: {
                url: "{{ route('report.purchasing.json') }}",
                data: function(d) {

                    d.report = 'sppb'

                    d.date_from = $('#sppb_date_from').val()
                    d.date_to = $('#sppb_date_to').val()

                    d.sppbid = $('#sppbid').val()
                    d.inventoryid = $('#inventoryid').val()

                }
            },

            columns: [

                {
                    data: 'sppbdate'
                },
                {
                    data: 'sppbid'
                },
                // {
                //     data: 'spbid'
                // },
                {
                    data: 'department_id'
                },
                {
                    data: 'requester'
                },
                {
                    data: 'purchasing'
                },
                {
                    data: 'inventoryid'
                },
                {
                    data: 'inventory_descr'
                },
                {
                    data: 'qty'
                },
                {
                    data: 'uom'
                },
                {
                    data: 'siteid'
                },
                {
                    data: 'status'
                }

            ],

            order: [
                [0, 'desc']
            ]

        })


        $('#filterSppb').click(function() {
            table.ajax.reload()
        })

        $('#resetSppb').click(function() {

            $('#sppb_date_from').val('');
            $('#sppb_date_to').val('');
            $('#sppbid').val('');
            $('#inventoryid').val('');

            table.ajax.reload();

        });

        $('#exportSppb').click(function() {

            let url = "{{ route('report.purchasing.export') }}?report=sppb";

            url += "&date_from=" + $('#sppb_date_from').val();
            url += "&date_to=" + $('#sppb_date_to').val();
            url += "&sppbid=" + $('#sppbid').val();
            url += "&inventoryid=" + $('#inventoryid').val();

            window.location.href = url;

        });

    })
</script>
