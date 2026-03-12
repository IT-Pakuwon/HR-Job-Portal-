<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="sppj_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="sppj_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">SPPJ No</label>
                <input type="text" id="sppjid"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">Inventory ID</label>
                <input type="text" id="inventoryid_sppj" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div class="flex items-end gap-2 md:col-span-2">

                <button id="filterSppj" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">
                    Apply
                </button>

                <button id="resetSppj" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="exportSppj"
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
                SPPJ Detail
            </h2>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="sppjTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                    <tr>
                        <th>Date</th>
                        <th>SPPJ No</th>
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

        var table = $('#sppjTable').DataTable({

            processing: true,
            serverSide: true,
            searching: false,

            ajax: {
                url: "{{ route('report.purchasing.json') }}",
                data: function(d) {

                    d.report = 'sppj'

                    d.date_from = $('#sppj_date_from').val()
                    d.date_to = $('#sppj_date_to').val()

                    d.sppjid = $('#sppjid').val()
                    d.inventoryid = $('#inventoryid_sppj').val()

                }
            },

            columns: [

                {
                    data: 'sppjdate'
                },
                {
                    data: 'sppjid'
                },
                {
                    data: 'department_name'
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


        $('#filterSppj').click(function() {
            table.ajax.reload()
        })


        $('#resetSppj').click(function() {

            $('#sppj_date_from').val('')
            $('#sppj_date_to').val('')
            $('#sppjid').val('')
            $('#inventoryid_sppj').val('')

            table.ajax.reload()

        })


        $('#exportSppj').click(function() {

            let url = "{{ route('report.purchasing.export') }}?report=sppj";

            url += "&date_from=" + $('#sppj_date_from').val();
            url += "&date_to=" + $('#sppj_date_to').val();
            url += "&sppjid=" + $('#sppjid').val();
            url += "&inventoryid=" + $('#inventoryid_sppj').val();

            window.location.href = url;

        })

    })
</script>
