<x-app-layout>

<div class="space-y-4">

    {{-- FILTER PANEL --}}
    <div class="p-6 bg-gray-50/60 rounded-2xl border border-gray-200 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="date_from" class="form-input">
            </div>

            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="date_to" class="form-input">
            </div>

            <div>
                <label class="text-xs text-gray-500">Room</label>
                <input type="text" id="room" placeholder="Room name" class="form-input">
            </div>

            <div>
                <label class="text-xs text-gray-500">Requester</label>
                <input type="text" id="requester" placeholder="User" class="form-input">
            </div>

            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select id="status" class="form-input">
                    <option value="">All</option>
                    <option value="A">Active</option>
                    <option value="X">Cancelled</option>
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button id="filterBtn" class="px-4 py-2 text-sm text-white bg-gray-900 rounded-lg">
                    Apply
                </button>

                <button id="resetBtn" class="px-4 py-2 text-sm bg-white border rounded-lg">
                    Reset
                </button>

                <button id="exportBtn" class="px-4 py-2 text-sm text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg">
                    Export
                </button>
            </div>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm">

        <div class="px-6 py-4 border-b">
            <h2 class="text-sm font-semibold text-gray-800">
                Meeting Room Report
            </h2>
        </div>

        <div class="overflow-x-auto p-5">
            <table id="meetingRoomTable" class="min-w-full text-sm">

                <thead class="text-xs text-gray-500 bg-gray-50 uppercase">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Room</th>
                        <th>Title</th>
                        <th>Requester</th>
                        <th>Participants</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>

            </table>
        </div>

    </div>

</div>
<script>
$(function () {

    let type = 'meeting-room';

    let table = $('#meetingRoomTable').DataTable({
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
            data: function (d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.room = $('#room').val();
                d.requester = $('#requester').val();
                d.status = $('#status').val();
            }
        },

        columns: [
            { data: 'meeting_date' },
            { data: 'time' },
            { data: 'room_name' },
            { data: 'meeting_title' },
            { data: 'requester' },
            { data: 'total_participant' },
            { data: 'type' },
            { data: 'status_label' }
        ],

        order: [[0, 'desc']]
    });

    $('#filterBtn').click(() => table.ajax.reload());

    $('#resetBtn').click(() => {
        $('#date_from, #date_to, #room, #requester').val('');
        $('#status').val('');
        table.ajax.reload();
    });

    $('#exportBtn').click(() => {
        let url = '/report-ga/export/' + type;

        url += '?date_from=' + $('#date_from').val();
        url += '&date_to=' + $('#date_to').val();
        url += '&room=' + $('#room').val();
        url += '&requester=' + $('#requester').val();

        window.location.href = url;
    });

});
</script>

</x-app-layout>
