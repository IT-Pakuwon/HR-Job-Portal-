<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="sppk_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="sppk_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">SPPK No</label>
                <input type="text" id="sppkid" placeholder="PJ-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="text-xs text-gray-500">Inventory ID</label>
                <input type="text" id="inventoryid_sppk" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div class="flex items-end gap-2 md:col-span-2">

                <button id="filterSppk" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">
                    Apply
                </button>

                <button id="resetSppk" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="exportSppk"
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
                SPPK Detail
            </h2>

        </div>

        <div class="overflow-x-auto p-5">

            <table id="sppkTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                    <tr>
                        <th>Date</th>
                        <th>SPPK No</th>
                        <th>No Polisi</th>
                        <th>Vehicle</th>
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

        var table = $('#sppkTable').DataTable({

            processing: true,
            serverSide: true,
            searching: false,

            ajax: {
                url: "{{ route('report.purchasing.json') }}",
                data: function(d) {

                    d.report = 'sppk'

                    d.date_from = $('#sppk_date_from').val()
                    d.date_to = $('#sppk_date_to').val()

                    d.sppkid = $('#sppkid').val()
                    d.inventoryid = $('#inventoryid_sppk').val()

                }
            },

            columns: [

                {
                    data: 'sppkdate'
                },
                {
                    data: 'sppkid'
                },
                {
                    data: 'no_polisi'
                },
                {
                    data: 'namakendaraan'
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


        $('#filterSppk').click(function() {
            table.ajax.reload()
        })


        $('#resetSppk').click(function() {

            $('#sppk_date_from').val('')
            $('#sppk_date_to').val('')
            $('#sppkid').val('')
            $('#inventoryid_sppk').val('')

            table.ajax.reload()

        })


        $('#exportSppk').click(function() {

            let url = "{{ route('report.purchasing.export') }}?report=sppk";

            url += "&date_from=" + $('#sppk_date_from').val();
            url += "&date_to=" + $('#sppk_date_to').val();
            url += "&sppkid=" + $('#sppkid').val();
            url += "&inventoryid=" + $('#inventoryid_sppk').val();

            window.location.href = url;

        })

    })
</script>
