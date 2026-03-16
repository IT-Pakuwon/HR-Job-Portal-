<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="sppt_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="sppt_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="text-xs text-gray-500">SPPT No</label>
                <input type="text" id="spptid" placeholder="PT-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="text-xs text-gray-500">Inventory ID</label>
                <input type="text" id="inventoryid_sppt" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div class="flex items-end gap-2 md:col-span-2">

                <button id="filterSppt" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">
                    Apply
                </button>

                <button id="resetSppt" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="exportSppt"
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
                SPPT Detail
            </h2>

        </div>

        <div class="overflow-x-auto p-5">

            <table id="spptTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                    <tr>
                        <th>Date</th>
                        <th>SPPT No</th>
                        <th>Tenant</th>
                        <th>Unit</th>
                        <th>PIC</th>
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

        var table = $('#spptTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,

            searching: false,
            dom: "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                "rt" +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],

            pageLength: 10,

            ajax: {
                url: "{{ route('report.purchasing.json') }}",
                data: function(d) {

                    d.report = 'sppt'

                    d.date_from = $('#sppt_date_from').val()
                    d.date_to = $('#sppt_date_to').val()

                    d.spptid = $('#spptid').val()
                    d.inventoryid = $('#inventoryid_sppt').val()

                }
            },

            columns: [

                {
                    data: 'spptdate'
                },
                {
                    data: 'spptid'
                },
                {
                    data: 'nama_tenant'
                },
                {
                    data: 'no_unit_tenant'
                },
                {
                    data: 'pic_pengawas'
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


        $('#filterSppt').click(function() {
            table.ajax.reload()
        })


        $('#resetSppt').click(function() {

            $('#sppt_date_from').val('')
            $('#sppt_date_to').val('')
            $('#spptid').val('')
            $('#inventoryid_sppt').val('')

            table.ajax.reload()

        })


        $('#exportSppt').click(function() {

            let url = "{{ route('report.purchasing.export') }}?report=sppt";

            url += "&date_from=" + $('#sppt_date_from').val();
            url += "&date_to=" + $('#sppt_date_to').val();
            url += "&spptid=" + $('#spptid').val();
            url += "&inventoryid=" + $('#inventoryid_sppt').val();

            window.location.href = url;

        })

    })
</script>
