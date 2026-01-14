<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'wos' ? 'HR' : '';
    @endphp


    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- On Hold --}}
            <button type="button" class="job-filter group block h-full" data-job="H">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🕒</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Hold</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $wojobs }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Cancel --}}
            <button type="button" class="status-filter group block h-full" data-status="X">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Cancel</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $cancel }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $completed }}</p>
                </div>
            </button>

            {{-- All --}}
            <button type="button" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $all }}</p>
                </div>
            </button>
        </div>
        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">WO Jobs</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="wosTable" class="text-body w-full text-left text-xs rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-xs">
                        <tr>
                            <th></th>
                            <th class="w-32 px-6 py-3 font-medium">
                                DocID</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Date</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Company</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Department</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Work Type</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                WO Request</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Description</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Status Pekerjaan</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        var currentUser = "{{ auth()->user()->username }}";

        $(document).ready(function() {
            // 🔥 default: tampilkan On Hold (H)
            let jobStatusFilter = 'H';

            const table = $('#wosTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_WOJobs',
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
                        title: 'List_WOJobs',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                responsive: {
                    details: {
                        type: 'column',
                        target: 0 // 👈 this is REQUIRED
                    }
                },

                columnDefs: [{
                    targets: 0,
                    className: 'dtr-control',
                    orderable: false
                }],

                ajax: {
                    url: "{{ route('wos.jsonJobs') }}",
                    type: "GET",
                    data: function(d) {
                        d.job_status = jobStatusFilter ?? ''; // 🔥 hanya ini yg dikirim
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    {
                        data: 'woid',
                        render: function(data, type, row) {
                            let url = `/showwos/${row.eid}`;
                            let cls =
                                'shrink-0 px-6 py-2 bg-gray-500 text-white rounded hover:bg-gray-700 text-xs';
                            const text = data || row.eid;

                            if (row.status === 'D' && row.created_by === currentUser) {
                                url = `/editwos/${row.eid}`;
                                cls =
                                    'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-xs';
                            }

                            return `
                                        <a href="${url}" class="${cls}">${text}</a>
                                    `;

                        }
                    },
                    {
                        data: 'wodate',
                        className: 'text-left'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words'
                    },
                    {
                        data: 'worktype_name',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'worequest',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan'
                    },
                    {
                        data: 'status_pekerjaan', // ini dok-status; kalau mau ganti ke job status tinggal pakai 'status_pekerjaan'
                        className: 'text-left',
                        render: function(data, type, row) {
                            // map dok-status (boleh dibiarkan)
                            const map = {
                                'H': {
                                    t: 'Hold',
                                    c: 'bg-gray-300/30 text-gray-600'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-blue-300/30 text-blue-600'
                                },
                                'C': {
                                    t: 'Completed',
                                    c: 'bg-green-300/30 text-green-600'
                                },
                                'X': {
                                    t: 'Cancel',
                                    c: 'bg-red-300/30 text-red-600'
                                },
                                'R': {
                                    t: 'Rejected',
                                    c: 'bg-red-300/30 text-red-600'
                                },
                            };
                            const it = map[data] || {
                                t: data || '-',
                                c: 'bg-gray-300/30 text-gray-600'
                            };
                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                        }
                    }
                    // Jika ingin menampilkan job status juga, tambah 1 kolom lagi render dari row.status_pekerjaan
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            // Helper highlight: aktifkan tombol sesuai jobStatusFilter
            function setActiveCards() {
                document.querySelectorAll('.status-filter, .job-filter').forEach(b => b.classList.remove('active'));
                const btn = document.querySelector(
                    `.status-filter[data-status="${jobStatusFilter}"], .job-filter[data-job="${jobStatusFilter}"]`
                );
                if (btn) btn.classList.add('active');
            }

            // initial
            setActiveCards();

            // Semua kartu pakai status-pekerjaan:
            // - Kartu On Hold punya class .job-filter data-job="H"
            // - Kartu lain .status-filter data-status=""|"P"|"R"|"C"
            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                jobStatusFilter = $(this).data('status') || ''; // '' = All job statuses
                setActiveCards();
                table.ajax.reload(null, true);
            });

            $('.job-filter').on('click', function(e) {
                e.preventDefault();
                jobStatusFilter = $(this).data('job') || '';
                setActiveCards();
                table.ajax.reload(null, true);
            });
        });
    </script>
</x-app-layout>
