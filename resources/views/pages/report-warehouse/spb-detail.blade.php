<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-7">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- SPB -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    SPB Number
                </label>
                <input type="text" id="spbid" placeholder="RB-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Inventory -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>
                <input type="text" id="inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- SPB Status -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    SPB Status
                </label>
                <select id="spb_status"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All</option>
                    <option value="C">Completed</option>
                    <option value="P">On Progress</option>
                    <option value="D">Draft</option>
                </select>
            </div>

            <!-- Issue Status -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Issue Status
                </label>
                <select id="issue_status"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    <option value="">All</option>
                    <option value="Open">Open</option>
                    <option value="Partial">Partial</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-end gap-2">

                <button id="filterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button id="resetBtn"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Reset
                </button>

                <button id="exportBtn"
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
                SPB Detail
            </h2>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                List of SPB request details and issuing progress
            </p>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-5">

            <table id="reportTable" class="min-w-full text-sm text-gray-700 dark:text-gray-200">

                <thead
                    class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 dark:bg-gray-700 dark:text-gray-300">

                    <tr>
                        <th></th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">SPB No</th>
                        <th class="px-3 py-3 text-left">Created By</th>
                        <th class="px-3 py-3 text-left">Department</th>
                        <th class="px-3 py-3 text-left">Inventory ID</th>
                        <th class="px-3 py-3 text-left">Description</th>
                        <th class="px-3 py-3 text-left">SPB Status</th>
                        <th class="px-3 py-3 text-left">Issue Status</th>
                        <th class="px-3 py-3 text-right">SPB Qty</th>
                        <th class="px-3 py-3 text-right">BPG Qty</th>
                        <th class="px-3 py-3 text-right">Outstanding</th>
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

            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],

            pageLength: 10,

            ajax: {
                url: "{{ route('report.warehouse.json') }}",
                data: function(d) {

                    d.date_from = $('#date_from').val();
                    d.date_to = $('#date_to').val();
                    d.spbid = $('#spbid').val();
                    d.inventoryid = $('#inventoryid').val();
                    d.spb_status = $('#spb_status').val();
                    d.issue_status = $('#issue_status').val();

                }
            },

            columns: [

                {
                    data: null,
                    className: 'dtr-control',
                    orderable: false,
                    defaultContent: ''
                },

                {
                    data: 'spbdate'
                },
                {
                    data: 'spbid'
                },

                {
                    data: 'creator',
                    render: function(data) {
                        if (!data) return '';

                        return data.toLowerCase().replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                    }
                },

                {
                    data: 'department_name'
                },
                {
                    data: 'inventoryid'
                },
                {
                    data: 'inventory_descr'
                },
                {
                    data: 'spb_status'
                },
                {
                    data: 'issue_status'
                },

                {
                    data: 'qty',
                    className: 'text-right'
                },
                {
                    data: 'issue_qty',
                    className: 'text-right'
                },
                {
                    data: 'outstanding_qty',
                    className: 'text-right'
                }

            ],

            order: [
                [1, 'desc']
            ]

        });

        $('#filterBtn').click(function() {
            table.ajax.reload();
        });

        $('#resetBtn').on('click', function() {

            $('#date_from').val('');
            $('#date_to').val('');
            $('#spbid').val('');
            $('#inventoryid').val('');
            $('#spb_status').val('');
            $('#issue_status').val('');

            table.ajax.reload();

        });

        $('#exportBtn').on('click', function() {

            let url = "{{ route('report.warehouse.export') }}?report=spb";

            url += "&date_from=" + $('#date_from').val();
            url += "&date_to=" + $('#date_to').val();
            url += "&spbid=" + $('#spbid').val();
            url += "&inventoryid=" + $('#inventoryid').val();
            url += "&spb_status=" + $('#spb_status').val();
            url += "&issue_status=" + $('#issue_status').val();

            window.location.href = url;

        });

    });
</script>
