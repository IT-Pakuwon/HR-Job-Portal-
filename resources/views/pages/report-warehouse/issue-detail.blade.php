<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="issue_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="issue_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Issue Number -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Issue Number
                </label>
                <input type="text" id="issueid" placeholder="IS-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    WO ID
                </label>

                <input type="text" id="woid" placeholder="WO Number"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- Inventory -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>
                <input type="text" id="issue_inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm transition focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="issueFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button id="issueResetBtn"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                    Reset
                </button>

                <button id="issueExportBtn"
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
                Issue / BPG Detail
            </h2>

            <p class="text-xs text-gray-500 dark:text-gray-400">
                Detailed record of material issues
            </p>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-5">

            <table id="issueTable" class="min-w-full text-sm text-gray-700 dark:text-gray-200">

                <thead
                    class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500 dark:bg-gray-700 dark:text-gray-300">

                    <tr>

                        <th class="px-3 py-3 text-left">Issue Date</th>
                        <th class="px-3 py-3 text-left">Issue ID</th>
                        <th class="px-3 py-3 text-left">Type</th>

                        <th class="px-3 py-3 text-left">Created Issue By</th>
                        <th class="px-3 py-3 text-left">Business Unit</th>
                        <th class="px-3 py-3 text-left">Department Issue</th>

                        <th class="px-3 py-3 text-left">Company</th>

                        <th class="px-3 py-3 text-left">SPB ID</th>
                        <th class="px-3 py-3 text-left">WO ID</th>
                        <th class="px-3 py-3 text-left">SPB Created By</th>
                        <th class="px-3 py-3 text-left">SPB Department</th>

                        <th class="px-3 py-3 text-left">Purpose</th>

                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        let issueTable = $('#issueTable').DataTable({

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

                    d.report = "issue";

                    d.date_from = $('#issue_date_from').val();
                    d.date_to = $('#issue_date_to').val();
                    d.issueid = $('#issueid').val();
                    d.inventoryid = $('#issue_inventoryid').val();
                    d.woid = $('#woid').val();

                }
            },

            columns: [

                {
                    data: 'issuedate'
                },
                {
                    data: 'issueid'
                },
                {
                    data: 'issuetype'
                },

                {
                    data: 'created_issue_by',
                    render: function(data) {
                        if (!data) return '';

                        return data.toLowerCase().replace(/\b\w/g, function(char) {
                            return char.toUpperCase();
                        });
                    }
                },

                {
                    data: 'business_unit_name'
                },
                {
                    data: 'department_created_issue'
                },

                {
                    data: 'cpny_id'
                },

                {
                    data: 'spbid'
                },
                {
                    data: 'woid',
                    render: function(data) {
                        return data ? data : '-';
                    }
                },
                {
                    data: 'spb_created_by'
                },
                {
                    data: 'spb_department_created'
                },

                {
                    data: 'keperluan'
                }

            ],

            order: [
                [0, 'desc']
            ]

        });


        /* APPLY FILTER */
        $('#issueFilterBtn').click(function() {
            issueTable.ajax.reload();
        });


        /* RESET */
        $('#issueResetBtn').click(function() {

            $('#issue_date_from').val('');
            $('#issue_date_to').val('');
            $('#issueid').val('');
            $('#issue_inventoryid').val('');
            $('#woid').val('');

            issueTable.ajax.reload();

        });


        /* EXPORT */
        $('#issueExportBtn').click(function() {

            let params = new URLSearchParams({

                report: 'issue',
                date_from: $('#issue_date_from').val(),
                date_to: $('#issue_date_to').val(),
                issueid: $('#issueid').val(),
                inventoryid: $('#issue_inventoryid').val(),
                woid: $('#woid').val()



            });

            window.location = "{{ route('report.warehouse.export') }}?" + params.toString();

        });

    });
</script>
