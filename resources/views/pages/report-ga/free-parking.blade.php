<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 items-end gap-4 md:grid-cols-2 lg:grid-cols-7">

            {{-- DATE FROM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date From
                </label>

                <input type="date" id="fp_date_from" class="form-input w-full">
            </div>

            {{-- DATE TO --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date To
                </label>

                <input type="date" id="fp_date_to" class="form-input w-full">
            </div>

            {{-- NAME --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Name / Employee
                </label>

                <input type="text" id="fp_name" placeholder="Search name..."
                    class="form-input w-full">
            </div>

            {{-- PARKING TYPE --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Parking Type
                </label>

                <select id="fp_parking_type" class="form-input w-full">

                    <option value="">
                        All Types
                    </option>

                    @foreach ($parkingTypes as $pt)
                        <option value="{{ $pt->categoryid }}">
                            {{ $pt->category_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- WORKER TYPE --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Worker Type
                </label>

                <select id="fp_worker_type" class="form-input w-full">

                    <option value="">
                        All Workers
                    </option>

                    @foreach ($workerTypes as $wt)
                        <option value="{{ $wt->categoryid }}">
                            {{ $wt->category_name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- STATUS --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Status
                </label>

                <select id="fp_status" class="form-input w-full">

                    <option value="">
                        All Status
                    </option>

                    <option value="P">
                        On Progress
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

                    <option value="A">
                        Active
                    </option>

                </select>
            </div>

            {{-- ACTION --}}
            <div class="flex items-end justify-end gap-2">

                <button id="fpFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button id="fpResetBtn"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50">
                    Reset
                </button>

                <button id="fpExportBtn"
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
                Free Parking Report
            </h2>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="freeParkingTable" class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th>Doc ID</th>
                        <th>Reg. Date</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>License Plate</th>
                        <th>Vehicle Type</th>
                        <th>Parking Type</th>
                        <th>Worker Type</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        let type = 'free-parking';

        let table = $('#freeParkingTable').DataTable({
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
                    d.date_from    = $('#fp_date_from').val();
                    d.date_to      = $('#fp_date_to').val();
                    d.name         = $('#fp_name').val();
                    d.parking_type = $('#fp_parking_type').val();
                    d.worker_type  = $('#fp_worker_type').val();
                    d.status       = $('#fp_status').val();
                }
            },

            columns: [
                { data: 'docid' },
                { data: 'parking_regist_date' },
                { data: 'nama' },
                { data: 'company' },
                { data: 'department' },
                { data: 'nopol', defaultContent: '-' },
                { data: 'jenis_kendaraan', defaultContent: '-' },
                { data: 'parking_type_label' },
                { data: 'worker_type_label' },
                { data: 'startdate' },
                { data: 'enddate' },
                { data: 'status_label' },
            ],

            order: [
                [0, 'desc']
            ]
        });

        $('#fpFilterBtn').click(function() {
            table.ajax.reload();
        });

        $('#fpResetBtn').click(function() {
            $('#fp_date_from').val('');
            $('#fp_date_to').val('');
            $('#fp_name').val('');
            $('#fp_parking_type').val('');
            $('#fp_worker_type').val('');
            $('#fp_status').val('');
            table.ajax.reload();
        });

        $('#fpExportBtn').click(function() {

            let url = '/report-ga/export/' + type;

            url += '?date_from='    + $('#fp_date_from').val();
            url += '&date_to='      + $('#fp_date_to').val();
            url += '&name='         + $('#fp_name').val();
            url += '&parking_type=' + $('#fp_parking_type').val();
            url += '&worker_type='  + $('#fp_worker_type').val();
            url += '&status='       + $('#fp_status').val();

            window.location.href = url;
        });

    });
</script>
