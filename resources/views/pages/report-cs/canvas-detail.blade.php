<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-8">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- CS Number -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    CS Number
                </label>
                <input type="text" id="csid" placeholder="CS-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- PO / SPK -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    PO / SPK
                </label>
                <input type="text" id="ponbr" placeholder="PO-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    SPPBJKT No
                </label>
                <input type="text" id="sppbjktid" placeholder="PB-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>
                <input type="text" id="inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="filterBtn" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Apply
                </button>

                <button id="resetBtn"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700">
                    Reset
                </button>

                <button id="exportBtn"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                    Export
                </button>

            </div>

        </div>

    </div>


    <!-- REPORT TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-gray-800">
                Canvass Sheet Detail
            </h2>

            <p class="text-xs text-gray-500">
                Selected vendor items that generate PO / SPK
            </p>

        </div>

        <div class="overflow-x-auto p-5">

            <table id="reportTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">

                    {{-- <tr>
                        <th></th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">CS No</th>
                        <th class="px-3 py-3 text-left">SPPB/J/K/T</th>
                        <th class="px-3 py-3 text-left">PO / SPK</th>
                        <th class="px-3 py-3 text-left">Department</th>
                        <th class="px-3 py-3 text-left">Requester</th>
                        <th class="px-3 py-3 text-left">Purchaser</th>
                        <th class="px-3 py-3 text-left">Purpose</th>
                        <th class="px-3 py-3 text-left">Item Description</th>
                        <th class="px-3 py-3 text-right">Qty</th>
                        <th class="px-3 py-3 text-left">UOM</th>
                        <th class="px-3 py-3 text-left">Budget Department</th>
                        <th class="px-3 py-3 text-right">Unit Price</th>
                        <th class="px-3 py-3 text-right">Total Price</th>
                        <th class="px-3 py-3 text-left">Vendor</th>

                    </tr> --}}

                    <tr>
                        <th></th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">CS No</th>
                        <th class="px-3 py-3 text-left">SPPB/J/K/T</th>
                        <th class="px-3 py-3 text-left">PO / SPK</th>
                        <th class="px-3 py-3 text-left">Department</th>
                        <th class="px-3 py-3 text-left">Requester</th>
                        <th class="px-3 py-3 text-left">Purchaser</th>
                        <th class="px-3 py-3 text-left">Purpose</th>

                        <th class="px-3 py-3 text-left">Inventory ID</th>
                        <th class="px-3 py-3 text-left">Item Description</th>

                        <th class="px-3 py-3 text-left">Budget Department</th>
                        <th class="px-3 py-3 text-left">Vendor</th>
                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>


<script>
    $(function() {

        var table = $('#reportTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            dom: "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                "rt" +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            ajax: {
                url: "{{ route('report.cs.json') }}",
                data: function(d) {

                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.csid = $('#csid').val();
                    d.ponbr = $('#ponbr').val();

                    d.sppbjktid = $('#sppbjktid').val();
                    d.inventoryid = $('#inventoryid').val();

                }
            },

            columns: [

                {
                    data: null,
                    className: 'dtr-control',
                    orderable: false
                },

                {
                    data: 'csdate',
                    render: function(data) {

                        if (!data) return '';

                        const date = new Date(data);

                        return date.toLocaleDateString('en-GB', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: 'csid'
                },
                {
                    data: 'sppbjktid'
                },
                {
                    data: 'ponbr'
                },

                {
                    data: 'department_name'
                },
                {
                    data: 'request_user'
                }, // requester
                {
                    data: 'purchase_user'
                },
                {
                    data: 'keperluan'
                },
                {
                    data: 'inventoryid'
                },
                {
                    data: 'inventory_descr'
                },
                // {
                //     data: 'qty',
                //     className: 'text-right'
                // },
                // {
                //     data: 'uom'
                // },

                {
                    data: null,
                    render: function(data, type, row) {

                        let dept = row.budget_department_name ?? '';
                        let coa = row.budget_account_id ?? '';

                        if (!dept && !coa) return '';

                        return dept + (coa ? ' - ' + coa : '');
                    }
                },
                // {
                //     data: 'unit_price',
                //     className: 'text-right'
                // },
                // {
                //     data: 'total_price',
                //     className: 'text-right'
                // },
                {
                    data: 'vendor_name'
                },


            ],
            order: [
                [1, 'desc']
            ]

        });

        $('#filterBtn').click(function() {
            table.ajax.reload();
        });

        $('#resetBtn').click(function() {

            $('#date_from').val('');
            $('#date_to').val('');
            $('#csid').val('');
            $('#ponbr').val('');
            $('#sppbjktid').val('');
            $('#inventoryid').val('');


            table.ajax.reload();

        });
        $('#exportBtn').click(function() {

            let url = "{{ route('report.cs.export') }}?report=detail";

            url += "&date_from=" + $('#date_from').val();
            url += "&date_to=" + $('#date_to').val();
            url += "&csid=" + $('#csid').val();
            url += "&ponbr=" + $('#ponbr').val();
            url += "&sppbjktid=" + $('#sppbjktid').val();
            url += "&inventoryid=" + $('#inventoryid').val();

            window.location.href = url;

        });

    });
</script>
