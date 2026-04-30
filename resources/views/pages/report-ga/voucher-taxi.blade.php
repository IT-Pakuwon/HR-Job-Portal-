<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-6 items-end">

            {{-- DATE FROM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date From
                </label>

                <input
                    type="date"
                    id="voucher_date_from"
                    class="form-input w-full">
            </div>

            {{-- DATE TO --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date To
                </label>

                <input
                    type="date"
                    id="voucher_date_to"
                    class="form-input w-full">
            </div>

            {{-- REQUESTER --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Requester
                </label>

                <input
                    type="text"
                    id="voucher_requester"
                    placeholder="Search requester..."
                    class="form-input w-full">
            </div>

            {{-- TYPE TRIP --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Type Trip
                </label>

                <select
                    id="voucher_type_trip"
                    class="form-input w-full">

                    <option value="">
                        All Type
                    </option>

                    <option value="ONEWAY">
                        One Way
                    </option>

                    <option value="RETURN">
                        Return Trip
                    </option>

                </select>
            </div>

            {{-- STATUS --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Status
                </label>

                <select
                    id="voucher_status"
                    class="form-input w-full">

                    <option value="">
                        All Status
                    </option>

                    <option value="P">
                        Pending
                    </option>

                    <option value="C">
                        Completed
                    </option>

                    <option value="D">
                        Revise
                    </option>

                    <option value="R">
                        Rejected
                    </option>

                    <option value="X">
                        Cancelled
                    </option>

                </select>
            </div>

            {{-- ACTION --}}
            <div class="flex items-end justify-end gap-2">

                <button
                    id="voucherFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">

                    Apply
                </button>

                <button
                    id="voucherResetBtn"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50">

                    Reset
                </button>

                <button
                    id="voucherExportBtn"
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
                Voucher Taxi Report
            </h2>
        </div>

        <div class="overflow-x-auto p-5">

            <table
                id="voucherTaxiTable"
                class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">

                    <tr>
                        <th>Doc ID</th>
                        <th>Date</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Purpose</th>
                        <th>Type Trip</th>
                        {{-- <th>Max Trip</th>
                        <th>Max Budget</th> --}}
                        <th>Actual Budget</th>
                        <th>Status</th>
                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        let type = 'voucher-taxi';

        let table = $('#voucherTaxiTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            dom:
                "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                'rt' +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            ajax: {
                url: '/report-ga/json/' + type,

                data: function(d) {

                    d.date_from =
                        $('#voucher_date_from').val();

                    d.date_to =
                        $('#voucher_date_to').val();

                    d.requester =
                        $('#voucher_requester').val();

                    d.type_trip =
                        $('#voucher_type_trip').val();

                    d.status =
                        $('#voucher_status').val();
                }
            },

            columns: [
                {
                    data: 'docid'
                },
                {
                    data: 'voucher_date'
                },
                {
                    data: 'requester'
                },
                {
                    data: 'department'
                },
                {
                    data: 'origin'
                },
                {
                    data: 'destination'
                },
                {
                    data: 'purpose'
                },
                {
                    data: 'trip_label'
                },
                // {
                //     data: 'max_trip'
                // },
                // {
                //     data: 'max_budget'
                // },
                {
                    data: 'actual_budget'
                },
                {
                    data: 'status_label'
                }
            ],

            order: [
                [0, 'desc']
            ]
        });

        $('#voucherFilterBtn').click(function() {
            table.ajax.reload();
        });

        $('#voucherResetBtn').click(function() {

            $('#voucher_date_from').val('');
            $('#voucher_date_to').val('');
            $('#voucher_requester').val('');
            $('#voucher_type_trip').val('');
            $('#voucher_status').val('');

            table.ajax.reload();
        });

        $('#voucherExportBtn').click(function() {

            let url =
                '/report-ga/export/' + type;

            url +=
                '?date_from=' +
                $('#voucher_date_from').val();

            url +=
                '&date_to=' +
                $('#voucher_date_to').val();

            url +=
                '&requester=' +
                $('#voucher_requester').val();

            url +=
                '&type_trip=' +
                $('#voucher_type_trip').val();

            url +=
                '&status=' +
                $('#voucher_status').val();

            window.location.href = url;
        });

    });
</script>
