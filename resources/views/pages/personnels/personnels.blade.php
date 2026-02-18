<x-app-layout>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Status --}}
            <a href="#" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </a>

            {{-- On Progress --}}
            <a href="#" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                </div>
            </a>

            {{-- Reject --}}
            <a href="#" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reject</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                </div>
            </a>

            {{-- Revise / Draft --}}
            <a href="#" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise / Draft</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $revise }}</p>
                </div>
            </a>

            {{-- Completed --}}
            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </a>

        </div>
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Personnel Requisition Form</h1>
                <a href="{{ url('/createpersonnels') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </a>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="personnelsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                DocID
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Company
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Division
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Department
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Title
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Level
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                User
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Status
                            </th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        var currentUser = "{{ auth()->user()->username }}";
        $(document).ready(function() {
            // Hanya inisialisasi tabel personnelsTable
            let personnelsTable = $('#personnelsTable').DataTable({
                ajax: "{{ route('personnels.json') }}?status=P",
                processing: true,
                serverSide: false,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'list_PO',
                        className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    },
                    {
                        extend: 'csvHtml5',
                        text: '↓ CSV',
                        title: 'list_PO',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],


                // order: [1, 'asc'],
                ordering: false,

                rowGroup: {
                    dataSrc: 'cpnyid', // Kelompokkan berdasarkan kolom 'cpnyid'
                    startRender: function(rows, group) {
                        // Cek apakah semua baris dalam grup saat ini tersembunyi (collapsed)
                        let isCollapsed = rows.nodes().to$().filter('.collapsed-group-row').length ===
                            rows.count();
                        let icon = isCollapsed ? '<i class="fas fa-plus-circle"></i>' :
                            '<i class="fas fa-minus-circle"></i>';

                        // Mengembalikan baris grup dengan ikon dan jumlah catatan
                        return $('<tr/>')
                            .append('<td colspan="' + rows.columns().count() + '">' + icon + ' ' +
                                group + ' (' + rows.count() + ' records)</td>')
                            .attr('data-group', group)
                            .addClass('group-row');
                    }
                },
                columns: [{
                        data: null,
                        defaultContent: ''
                    }, // DTR control
                    {

                        data: 'eid',
                        render: function(data, type, row) {
                            let url = `/showpersonnels/${row.eid}`;
                            let buttonClass =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';
                            let buttonText = row.docid; // Menggunakan row.docid untuk teks tombol

                            // Cek apakah user yang login sama dengan created_user dan status = D (Revise/Draft)
                            if (row.status === 'D' && row.created_user === currentUser) {
                                url = `/editpersonnels/${row.eid}`;
                                buttonClass =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            return `<a href="${url}" class="px-3 py-1 ${buttonClass} text-white rounded">${buttonText}</a>`;
                        }
                    },
                    {
                        data: 'date',
                        className: 'no-pointer'
                    },
                    {
                        data: 'cpnyid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'division_id',
                        className: 'no-pointer'
                    },
                    {
                        data: 'departementid',
                        className: 'no-pointer'
                    },
                    {
                        data: 'job_title',
                        className: 'no-pointer'
                    },
                    {
                        data: 'job_level',
                        className: 'no-pointer'
                    },
                    {
                        data: 'created_user',
                        className: 'no-pointer'
                    },
                    {
                        data: 'status',
                        className: 'no-pointer',
                        render: function(data) {
                            let statusText = "";
                            let badgeClass = "";

                            if (data === 'D') {
                                statusText = "Revise";
                                badgeClass =
                                    "w-32 bg-amber-200/60 text-amber-800 dark:bg-amber-300/40 dark:text-amber-900 pointer-events-none border border-amber-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'P') {
                                statusText = "On Progress";
                                badgeClass =
                                    "w-32 bg-orange-200/60 text-orange-800 dark:bg-orange-300/40 dark:text-orange-900 pointer-events-none border border-orange-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'C') {
                                statusText = "Completed";
                                badgeClass =
                                    "w-32 bg-green-200/60 text-green-800 dark:bg-green-300/40 dark:text-green-900 pointer-events-none border border-green-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'X') {
                                statusText = "Cancel";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else if (data === 'R') {
                                statusText = "Rejected";
                                badgeClass =
                                    "w-32 bg-red-200/60 text-red-800 dark:bg-red-300/40 dark:text-red-900 pointer-events-none border border-red-600/40 font-semibold px-4 py-2 text-center rounded";
                            } else {
                                statusText = data ?? "-";
                                badgeClass =
                                    "w-32 bg-gray-200/60 text-gray-700 dark:bg-gray-300/40 dark:text-gray-900 pointer-events-none border border-gray-500/40 font-semibold px-4 py-2 text-center rounded";
                            }

                            return `<span class="${badgeClass}">${statusText}</span>`;
                        }
                    }
                ]
            });

            // Event listener untuk klik pada baris grup (collapse/expand) untuk personnelsTable
            $('#personnelsTable tbody').on('click', 'tr.group-row', function() {
                let groupName = $(this).data('group');
                let iconElement = $(this).find('i');

                personnelsTable.rows().every(function() {
                    if (this.data().cpnyid ===
                        groupName
                    ) { // Sesuaikan dengan nama properti data yang digunakan untuk grouping
                        $(this.node()).toggleClass('collapsed-group-row');
                    }
                });

                // Mengganti ikon plus/minus
                if (iconElement.hasClass('fa-plus-circle')) {
                    iconElement.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                } else {
                    iconElement.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                }
            });


            // Filter status akan memfilter data di personnelsTable
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                let selectedStatus = $(this).data('status');
                let newUrl = "{{ route('personnels.json') }}";
                newUrl += "?status=" + encodeURIComponent(selectedStatus ?? '');
                console.log("Loading personnelsTable with URL:", newUrl);
                personnelsTable.ajax.url(newUrl).load();
            });
        });
        // Make each .grid-col-1 set independent
        document.querySelectorAll('.grid-col-1').forEach(grid => {
            const filters = grid.querySelectorAll('.status-filter');
            filters.forEach(btn => {
                btn.addEventListener('click', e => {
                    e.preventDefault();
                    filters.forEach(s => s.classList.remove('active'));
                    btn.classList.add('active');
                });
            });
        });
    </script>

</x-app-layout>
