<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="receipt_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="receipt_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Receipt Number -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Receipt No
                </label>
                <input type="text" id="receiptnbr" placeholder="GR-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Inventory -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>
                <input type="text" id="receipt_inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="receiptFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button id="receiptResetBtn"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Reset
                </button>

                <button id="receiptExportBtn"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 transition hover:bg-emerald-100">
                    Export
                </button>

            </div>

        </div>

    </div>


    <!-- REPORT TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">

        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-700">

            <h2 class="text-sm font-semibold text-gray-800 dark:text-white">
                Receipt / STTB Detail
            </h2>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                List of goods receipts from suppliers
            </p>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-5">

            <table id="receiptTable" class="min-w-full text-sm text-gray-700 dark:text-gray-200">

                <thead
                    class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 dark:bg-gray-700 dark:text-gray-300">

                    <tr>
                        <th class="px-3 py-3 text-left">Receipt Date</th>
                        <th class="px-3 py-3 text-left">Receipt No</th>
                        <th class="px-3 py-3 text-left">Type</th>

                        <th class="px-3 py-3 text-left">Created By</th>
                        <th class="px-3 py-3 text-left">Company</th>
                        <th class="px-3 py-3 text-left">Vendor</th>

                        <th class="px-3 py-3 text-left">Inventory ID</th>
                        <th class="px-3 py-3 text-left">Description</th>

                        <th class="px-3 py-3 text-right">Qty Ordered</th>
                        <th class="px-3 py-3 text-right">Qty Received</th>
                        <th class="px-3 py-3 text-right">Qty Returned</th>

                        <th class="px-3 py-3 text-left">Warehouse</th>
                        {{-- <th class="px-3 py-3 text-left">Business Unit</th> --}}

                        <th class="px-3 py-3 text-left">COA</th>
                        <th class="px-3 py-3 text-left">Activity</th>

                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>


<script>
    $(function() {

        let receiptTable = $('#receiptTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,

            dom: "<'flex items-center justify-between mb-3'<'text-sm'l><'text-sm'f>>" +
                "rt" +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],

            pageLength: 10,

            ajax: {
                url: "{{ route('report.warehouse.json') }}",
                data: function(d) {

                    d.report = 'receipt';

                    d.date_from = $('#receipt_date_from').val();
                    d.date_to = $('#receipt_date_to').val();
                    d.receiptnbr = $('#receiptnbr').val();
                    d.inventoryid = $('#receipt_inventoryid').val();

                }
            },

            columns: [

                {
                    data: 'receiptdate'
                },
                {
                    data: 'receiptnbr'
                },
                {
                    data: 'receipttype'
                },

                {
                    data: 'creator'
                    render: function(data) {
                        if (!data) return '';

                        return data.toLowerCase().replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                    }
                },

                {
                    data: 'cpny_id'
                },
                {
                    data: 'vendorname'
                },

                {
                    data: 'inventoryid'
                },
                {
                    data: 'inventory_descr'
                },

                {
                    data: 'qtyordered',
                    className: 'text-right'
                },
                {
                    data: 'qty_received',
                    className: 'text-right'
                },
                {
                    data: 'qty_return',
                    className: 'text-right'
                },

                {
                    data: 'siteid'
                },

                // {
                //     data: 'business_unit_name'
                // },

                {
                    data: 'budget_account_id'
                },
                {
                    data: 'budget_activity_id'
                }

            ]

            // order: [
            //     [1, 'desc']
            // ]

        });


        $('#receiptFilterBtn').click(function() {
            receiptTable.ajax.reload();
        });


        $('#receiptResetBtn').click(function() {

            $('#receipt_date_from').val('');
            $('#receipt_date_to').val('');
            $('#receiptnbr').val('');
            $('#receipt_inventoryid').val('');

            receiptTable.ajax.reload();

        });


        $('#receiptExportBtn').click(function() {

            let url = "{{ route('report.warehouse.export') }}?report=receipt";

            url += "&date_from=" + $('#receipt_date_from').val();
            url += "&date_to=" + $('#receipt_date_to').val();
            url += "&receiptnbr=" + $('#receiptnbr').val();
            url += "&inventoryid=" + $('#receipt_inventoryid').val();

            window.location = url;

        });

    });
</script>
