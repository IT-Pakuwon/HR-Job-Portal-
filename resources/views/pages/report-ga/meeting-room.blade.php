  <div class="space-y-4">

      {{-- FILTER PANEL --}}
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        {{-- GRID --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4 items-end">

            {{-- DATE FROM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">Date From</label>
                <input type="date" id="date_from" class="form-input w-full">
            </div>

            {{-- DATE TO --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">Date To</label>
                <input type="date" id="date_to" class="form-input w-full">
            </div>

            {{-- ROOM --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">Room</label>
                <select id="room" class="form-input w-full">
                    <option value="">All Rooms</option>
                    @foreach ($rooms as $room)
                        <option value="{{ $room->room_name }}">
                            {{ $room->room_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- REQUESTER --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">Requester</label>
                <input type="text" id="requester" placeholder="Search user..." class="form-input w-full">
            </div>

            {{-- STATUS --}}
            <div class="space-y-1">
                <label class="text-xs font-medium text-gray-500">Status</label>
                <select id="status" class="form-input w-full">
                    <option value="">All Status</option>
                    <option value="A">Active</option>
                    <option value="X">Cancelled</option>
                </select>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-end justify-end gap-2">

                <button id="filterBtn"
                    class="px-4 py-2 text-sm font-medium text-white bg-gray-900 rounded-lg hover:bg-gray-800 transition">
                    Apply
                </button>

                <button id="resetBtn"
                    class="px-4 py-2 text-sm font-medium border border-gray-300 bg-white rounded-lg hover:bg-gray-50 transition">
                    Reset
                </button>

                <button id="exportExcelBtn"
                    class="px-4 py-2 text-sm font-medium text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg hover:bg-emerald-100 transition">
                    Excel
                </button>

                <button id="exportPdfBtn"
                    class="px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                    PDF
                </button>

            </div>

        </div>

    </div>

      {{-- TABLE --}}
      <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

          <div class="border-b px-6 py-4">
              <h2 class="text-sm font-semibold text-gray-800">
                  Meeting Room Report
              </h2>
          </div>

          <div class="overflow-x-auto p-5">
              <table id="meetingRoomTable" class="min-w-full text-sm">

                  <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                      <tr>
                          <th>Doc ID</th>
                          <th>Date</th>
                          <th>Start</th>
                          <th>End</th>
                          <th>Room</th>
                          <th>Accessories</th>
                          <th>Title</th>
                          <th>Requester</th>
                          <th>Department</th>
                          <th>Participants</th>
                          <th>Type</th>
                          <th>External Company</th>
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

          let type = 'meeting-room';

          let table = $('#meetingRoomTable').DataTable({
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
                      d.date_from = $('#date_from').val();
                      d.date_to = $('#date_to').val();
                      d.room = $('#room').val();
                      d.requester = $('#requester').val();
                      d.status = $('#status').val();
                  }
              },

              columns: [{
                      data: 'docid'
                  },
                  {
                      data: 'meeting_date'
                  },
                  {
                      data: 'start_time'
                  },
                  {
                      data: 'end_time'
                  },
                  {
                      data: 'room_name'
                  },
                  {
                      data: 'accessories'
                  },
                  {
                      data: 'meeting_title'
                  },
                  {
                      data: 'requester'
                  },
                  {
                      data: 'department'
                  },
                  {
                      data: 'total_participant'
                  },
                  {
                      data: 'type'
                  },
                    {
                        data: 'external_company',
                        defaultContent: '-'
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

          $('#filterBtn').click(() => table.ajax.reload());

          $('#resetBtn').click(() => {
              $('#date_from, #date_to, #room, #requester').val('');
              $('#status').val('');
              table.ajax.reload();
          });

            $('#exportExcelBtn').click(() => {

                let url = '/report-ga/export/' + type;

                url += '?date_from=' + $('#date_from').val();
                url += '&date_to=' + $('#date_to').val();
                url += '&room=' + $('#room').val();
                url += '&requester=' + $('#requester').val();
                url += '&status=' + $('#status').val();

                window.location.href = url;
            });

            $('#exportPdfBtn').click(() => {

                let url = '/report-ga/export/' + type;

                url += '?date_from=' + $('#date_from').val();
                url += '&date_to=' + $('#date_to').val();
                url += '&room=' + $('#room').val();
                url += '&requester=' + $('#requester').val();
                url += '&status=' + $('#status').val();

                // 🔥 PDF
                url += '&format=pdf';

                window.location.href = url;
            });

      });
  </script>
