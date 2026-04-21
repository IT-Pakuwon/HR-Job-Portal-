<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Meeting List</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="meetingTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th>No</th>
                            <th>Doc ID</th>
                            <th>User Peminta</th>
                            <th>Start Meeting</th>
                            <th>End Meeting</th>
                            <th>Meeting Title</th>
                            <th>Meeting Description</th>
                            <th>Room</th>
                            <th>Accessories</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#meetingTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'Meeting_List',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'Meeting_List',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: { page: 'current' }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0
                    }
                },
                columnDefs: [
                    {
                        targets: 0,
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false,
                        searchable: false,
                        data: null,
                        defaultContent: ''
                    },
                    {
                        targets: 1,
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                ajax: {
                    url: "{{ route('meetinglist.json') }}",
                    type: "GET"
                },
                order: [[4, 'desc']],
                columns: [
                    { data: null, defaultContent: '' },
                    { data: 'no', name: 'no' },
                    {
                        data: 'docid',
                        name: 'docid',
                        render: function(data, type, row) {
                            let url = "{{ route('meeting.show', ':hash') }}".replace(':hash', row.hash);
                            return `<a href="${url}" class="shrink-0 px-3 py-1.5 bg-gray-500 text-white rounded hover:bg-gray-700 text-sm">${data}</a>`;
                        }
                    },
                    { data: 'user_peminta', name: 'user_peminta', defaultContent: '-' },
                    { data: 'start_meeting_time', name: 'start_meeting_time', defaultContent: '-' },
                    { data: 'end_meeting_time', name: 'end_meeting_time', defaultContent: '-' },
                    { data: 'meeting_title', name: 'meeting_title', defaultContent: '-' },
                    { data: 'meeting_descr', name: 'meeting_descr', defaultContent: '-' },
                    { data: 'room_name', name: 'room_name', defaultContent: '-' },
                    { data: 'acc_name', name: 'acc_name', defaultContent: '-' }
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });
        });
    </script>
</x-app-layout>