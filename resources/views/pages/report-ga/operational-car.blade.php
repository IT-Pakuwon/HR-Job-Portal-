<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-8 items-end">

            {{-- DATE FROM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date From
                </label>

                <input
                    type="date"
                    id="date_from_bookingcar"
                    class="form-input w-full">
            </div>

            {{-- DATE TO --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Date To
                </label>

                <input
                    type="date"
                    id="date_to_bookingcar"
                    class="form-input w-full">
            </div>

            {{-- REQUESTER --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Requester
                </label>

                <input
                    type="text"
                    id="requester_bookingcar"
                    placeholder="Search requester..."
                    class="form-input w-full">
            </div>

            {{-- STATUS --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Status
                </label>

                <select
                    id="status_bookingcar"
                    class="form-input w-full">

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

                </select>
            </div>
            {{-- DRIVER --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Driver
                </label>

                <select
                    id="driver_bookingcar"
                    class="form-input w-full">

                    <option value="">
                        All Driver
                    </option>

                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->drivername }}">
                            {{ $driver->drivername }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- VEHICLE --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">
                    Vehicle
                </label>

                <select
                    id="vehicle_bookingcar"
                    class="form-input w-full">

                    <option value="">
                        All Vehicle
                    </option>

                    @foreach ($kendaraan as $car)
                        <option value="{{ $car->nopol_kendaraan }}">
                            {{ $car->nopol_kendaraan }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-end justify-end gap-2">

                <button
                    id="filterBtnBookingCar"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                    Apply
                </button>

                <button
                    id="resetBtnBookingCar"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium transition hover:bg-gray-50">
                    Reset
                </button>

                <button
                    id="exportBtnBookingCar"
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
                Booking Car Report
            </h2>
        </div>

        <div class="overflow-x-auto p-5">

            <table
                id="bookingCarTable"
                class="min-w-full text-sm">

                <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                    <tr>
                        <th>Doc ID</th>
                        <th>Booking Date</th>
                        <th>Start</th>
                        <th>End</th>
                        <th>Requester</th>
                        <th>Department</th>
                        <th>Purpose</th>
                        <th>Route</th>
                        <th>Passenger</th>
                        <th>Driver</th>
                        <th>Vehicle</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        let type = 'operational-car';

        let table = $('#bookingCarTable').DataTable({
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

                    d.date_from = $('#date_from_bookingcar').val();
                    d.date_to = $('#date_to_bookingcar').val();

                    d.requester = $('#requester_bookingcar').val();

                    d.status = $('#status_bookingcar').val();

                    d.driver = $('#driver_bookingcar').val();

                    d.vehicle = $('#vehicle_bookingcar').val();
                }
            },

            columns: [
                {
                    data: 'docid'
                },
                {
                    data: 'booking_date'
                },
                {
                    data: 'start_time'
                },
                {
                    data: 'end_time'
                },
                {
                    data: 'requester'
                },
                {
                    data: 'department'
                },
                {
                    data: 'purpose_descr'
                },
                {
                    data: 'route'
                },
                {
                    data: 'passenger'
                },
                {
                    data: 'driver'
                },
                {
                    data: 'no_polisi'
                },
                {
                    data: 'duration_label'
                },
                {
                    data: 'status_label'
                }
            ],

            order: [
                [0, 'desc']
            ]
        });

        $('#filterBtnBookingCar').click(function() {
            table.ajax.reload();
        });

        $('#resetBtnBookingCar').click(function() {

            $('#date_from_bookingcar').val('');
            $('#date_to_bookingcar').val('');

            $('#requester_bookingcar').val('');

            $('#status_bookingcar').val('');

            $('#driver_bookingcar').val('');

            $('#vehicle_bookingcar').val('');

            table.ajax.reload();
        });

        $('#exportBtnBookingCar').click(function() {

            let url = '/report-ga/export/' + type;

            url += '?date_from=' + $('#date_from_bookingcar').val();
            url += '&date_to=' + $('#date_to_bookingcar').val();

            url += '&requester=' + $('#requester_bookingcar').val();

            url += '&status=' + $('#status_bookingcar').val();

            // ADD THESE
            url += '&driver=' + $('#driver_bookingcar').val();

            url += '&vehicle=' + $('#vehicle_bookingcar').val();

            window.location.href = url;
        });

    });
</script>
```
