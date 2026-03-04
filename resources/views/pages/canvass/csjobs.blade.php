<x-app-layout>
    {{-- Select2 & Toastr (biarkan seperti sebelumnya) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">

            {{-- CS Jobs --}}
            <button type="button" class="w-full text-left">
                <div id="btn-mine"
                    class="filter-card flex h-full items-center gap-2 rounded-lg border border-indigo-700 bg-indigo-200/20 p-2 text-indigo-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-indigo-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🗂️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">CS Jobs</p>
                    </div>

                    <p id="count-mine" class="shrink-0 text-base font-bold">{{ $mine }}</p>
                </div>
            </button>

            {{-- CS Revision --}}
            <button type="button" class="w-full text-left">
                <div id="btn-revision"
                    class="filter-card flex h-full items-center gap-2 rounded-lg border border-amber-700 bg-amber-200/20 p-2 text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📝</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">CS Reuse</p>
                    </div>

                    <p id="count-revision" class="shrink-0 text-base font-bold">{{ $revision }}</p>
                </div>
            </button>

            {{-- All CS Jobs --}}
            <button type="button" class="w-full text-left">
                <div id="btn-all"
                    class="filter-card flex h-full items-center gap-2 rounded-lg border border-gray-700 bg-gray-200/20 p-2 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🌐</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All CS Jobs</p>
                    </div>

                    <p id="count-all" class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </button>

            {{-- SPPBJKT IN Progress --}}
            <button type="button" class="w-full text-left">
                <div id="btn-sppbjkt"
                    class="filter-card flex h-full items-center gap-2 rounded-lg border border-green-700 bg-green-200/20 p-2 text-green-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🚦</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">SPPBJKT IN Progress</p>
                    </div>

                    <p id="count-sppbjkt" class="shrink-0 text-base font-bold">{{ $sppbjkt }}</p>
                </div>
            </button>

            {{-- Completed Jobs --}}
            <button type="button" class="w-full text-left">
                <div id="btn-completed"
                    class="filter-card flex h-full items-center gap-2 rounded-lg border border-slate-900 bg-slate-200/20 p-2 text-slate-900 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed Jobs</p>
                    </div>

                    <p id="count-completed" class="shrink-0 text-base font-bold">{{ $completed ?? 0 }}</p>
                </div>
            </button>

        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">

            {{-- === PANE: CS Jobs + Entry CS (dua tabel) === --}}
            <div id="pane-mine">
                <div>
                    <h2 class="mb-2 text-base font-semibold">CS Jobs</h2>
                    <table id="tblMine" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th></th>
                                <th class="w-32 px-4 py-3 text-center">
                                    Action</th>
                                <th class="px-4 py-3 text-left">DocID
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Assign
                                    Date</th>
                                <th class="px-4 py-3 text-left">
                                    Date
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Company
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Name
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Assign
                                    Purchasing</th>
                                <th class="px-4 py-3 text-left">
                                    Assign
                                    By</th>
                                <th class="px-4 py-3 text-left">
                                    Department</th>
                                <th class="w-32 px-4 py-3 text-center">
                                    Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        </tbody>
                    </table>

                </div>

                <div class="mt-10">
                    <h2 class="mb-2 text-base font-semibold">Entry CS (My CS)</h2>
                    <table id="tblEntryCS" class="text-body w-full text-left text-sm rtl:text-right">
                        <thead
                            class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                            <tr>
                                <th></th>
                                <th class="w-32 px-4 py-3 text-center">CSID
                                </th>
                                <th class="w-32 px-4 py-3 text-center">
                                    SPPBJKT ID
                                </th>
                                <th class="px-4 py-3 text-left">
                                    Date</th>
                                <th class="px-4 py-3 text-left">
                                    Company</th>
                                <th class="px-4 py-3 text-left">
                                    Department</th>
                                <th class="w-32 px-4 py-3 text-center">User
                                    Peminta</th>
                                <th class="w-32 px-4 py-3 text-center">Note
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- === PANE: My Revision (TrPO Reuse) === --}}
            <div id="pane-revision" class="hidden">
                <h2 class="mb-2 text-base font-semibold">My Revision</h2>
                <table id="tblRevision" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-2 w-32 px-4 py-3 text-center">
                                Action
                            </th>
                            <th class="w-32 px-4 py-3 text-center">
                                PO Number
                            </th>
                            <th class="px-4 py-3 text-left">
                                PO Date
                            </th>
                            <th class="px-4 py-3 text-left">
                                CSID
                            </th>
                            <th class="px-4 py-3 text-left">
                                SPPBJKT
                            </th>
                            <th class="px-4 py-3 text-left">
                                Company
                            </th>
                            <th class="px-4 py-3 text-left">
                                Department
                            </th>
                            <th class="px-4 py-3 text-left">
                                Vendor
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- === PANE: All Jobs === --}}
            <div id="pane-all" class="hidden">
                <h2 class="mb-2 text-base font-semibold">All Jobs</h2>
                <table id="tblAll" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">DocID
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Date</th>
                            <th class="px-4 py-3 text-left">Date
                            </th>
                            <th class="px-4 py-3 text-left">
                                Company
                            </th>
                            <th class="px-4 py-3 text-left">Name
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Purchasing</th>
                            <th class="px-4 py-3 text-left">Assign
                                By</th>
                            <th class="px-4 py-3 text-left">
                                Department</th>
                            <th class="w-32 px-4 py-3 text-center">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- === PANE: SPPBJKT IN Progress === --}}
            <div id="pane-sppbjkt" class="hidden">
                <h2 class="mb-2 text-base font-semibold">SPPBJKT IN Progress</h2>
                <table id="tblSppbjkt" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">DocID
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Date</th>
                            <th class="px-4 py-3 text-left">Date
                            </th>
                            <th class="px-4 py-3 text-left">
                                Company</th>
                            <th class="px-4 py-3 text-left">Name
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Purchasing</th>
                            <th class="px-4 py-3 text-left">Assign
                                By</th>
                            <th class="px-4 py-3 text-left">
                                Department</th>
                            <th class="w-32 px-4 py-3 text-center">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>

            {{-- === PANE: Completed Jobs === --}}
            <div id="pane-completed" class="hidden">
                <h2 class="mb-2 text-base font-semibold">Completed Jobs</h2>

                <table id="tblCompleted" class="text-body w-full text-left text-sm rtl:text-right">
                    {{-- <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-32 px-4 py-3 text-center">DocID</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Company</th>
                                <th class="px-4 py-3 text-left">Department</th>
                                <th class="w-32 px-4 py-3 text-center">Created By</th>
                                <th class="w-32 px-4 py-3 text-center">Description</th>
                            </tr>
                        </thead> --}}
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">DocID
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Date</th>
                            <th class="px-4 py-3 text-left">Date
                            </th>
                            <th class="px-4 py-3 text-left">
                                Company</th>
                            <th class="px-4 py-3 text-left">Name
                            </th>
                            <th class="px-4 py-3 text-left">Assign
                                Purchasing</th>
                            <th class="px-4 py-3 text-left">Assign
                                By</th>
                            <th class="px-4 py-3 text-left">
                                Department</th>
                            <th class="w-32 px-4 py-3 text-center">
                                Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>


        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <script>
        $(function() {
            // ===== renderer util (sama seperti sebelumnya) =====
            const mapShowUrl = {
                SPPB: 'showsppbs',
                SPPJ: 'showsppjs',
                SPPK: 'showsppks',
                SPPT: 'showsppts'
            };

            function buildCreateUrl(row) {
                return `/createcs/${row.doc_type}/${row.eid}`;
            }

            function renderDocBtn(row) {
                const base = mapShowUrl[row.doc_type] || '#';
                return `<a href="/${base}/${row.eid}" class='inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 '>${row.doc_no}</a>`;
            }

            function colSetWithoutCreate() {
                return [{
                        data: null,
                        className: 'text-left',
                        render: (_d, _t, row) => renderDocBtn(row)
                    },
                    {
                        data: 'assigndate',
                        className: 'text-center',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                    },
                    {
                        data: 'doc_date',
                        className: 'text-left',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-left'
                    },
                    {
                        data: 'created_by_name',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'assignpurchasing',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    {
                        data: 'assignby',
                        className: 'text-left',
                        defaultContent: ''
                    },
                    {
                        data: 'department_id',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
                ];
            }


            function colSetWithCreate() {
                const actionCol = {
                    data: null,
                    orderable: false,
                    searchable: false,
                    className: 'text-left',
                    render: (_d, _t, row) => {
                        const createUrl = `/createcs/${row.doc_type}/${row.eid}`;
                        return `
                        <div class="inline-flex gap-2">
                        <a href="${createUrl}"
                            class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-blue-500 hover:bg-blue-700"
                            title="Create CS">
                            <i class="fas fa-plus"></i>
                        </a>

                        <button type="button"
                            class="btn-complete-open inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-red-500 hover:bg-red-700"
                            data-doc="${row.doc_type}" data-eid="${row.eid}" title="Complete sisa yang tidak jadi diorder">
                            <i class="fas fa-times"></i>
                        </button>

                        <button type="button"
                            class="btn-revise-doc inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-amber-500 hover:bg-amber-700"
                            data-doc="${row.doc_type}"
                            data-docno="${row.doc_no}"
                            data-cpny="${row.cpny_id}"
                            data-dept="${row.department_id}"
                            title="Revise dokumen (set status D)">
<i class="fas fa-undo"></i>
                        </button>
                        </div>`;
                    }

                };
                return [actionCol, ...colSetWithoutCreate()];
            }

            const dtControlColumn = {
                data: null,
                width: '28px',
                className: 'dtr-control',
                orderable: false,
                searchable: false,
                defaultContent: ''
            };


            // ===== Datatables init (tanpa parameter docType) =====
            const tblMine = $('#tblMine').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CSJob',
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
                        title: 'List_CSJob',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.mine.json') }}",
                    type: "GET"
                },
                order: [
                    [3, 'desc'],
                    [1, 'desc']
                ],
                columns: [dtControlColumn, ...colSetWithCreate()],
                searchDelay: 400,
                stateSave: true,
            });

            const tblEntryCS = $('#tblEntryCS').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CS',
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
                        title: 'List_CS',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.entry.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc'],
                    [0, 'desc']
                ],
                columns: [
                    dtControlColumn, {
                        data: 'csid',
                        className: 'text-left',
                        render: (v, _t, row) =>
                            `<a href="/editcs/${row.eid}"
                            class="inline-flex justify-center items-center w-[120px] px-3 py-1.5
                                    text-sm leading-tight font-semibold text-white rounded text-center
                                    transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700">
                                ${v}
                            </a>`
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-left',
                        render: (v, _t, row) => {
                            if (!row.sppbjkt_eid) return '-';

                            const mapShowUrl = {
                                SPPB: 'showsppbs',
                                SPPJ: 'showsppjs',
                                SPPK: 'showsppks',
                                SPPT: 'showsppts',
                            };

                            const base = mapShowUrl[row.sppbjkt_doc_type] || '#';

                            return `
                                <a href="/${base}/${row.sppbjkt_eid}"
                                class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">
                                    ${v}
                                </a>
                            `;
                        }
                    },
                    {
                        data: 'csdate',
                        className: 'text-center',
                        render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString(
                            'id-ID')) : ''
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center'
                    },
                    {
                        data: 'user_peminta',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                    {
                        data: 'csnote',
                        className: 'text-left',
                        defaultContent: '-'
                    },
                ],

                searchDelay: 400,
                stateSave: true,
            });

            const tblAll = $('#tblAll').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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


                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_All',
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
                        title: 'List_ALL',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.all.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: [dtControlColumn, ...colSetWithoutCreate()],
                searchDelay: 400,
                stateSave: true,
            });

            const tblRevision = $('#tblRevision').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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

                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CSRevisi',
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
                        title: 'List_CSRevisi',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.revision.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'], // sort by PO Date
                    [1, 'desc'] // then by PO Number
                ],
                columns: [dtControlColumn, ...colSetRevision()],
                searchDelay: 400,
                stateSave: true,
            });

            const tblSppbjkt = $('#tblSppbjkt').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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
                        title: 'List_SPPBJKT',
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
                        title: 'List_SPPBJKT',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.sppbjkt.progress.json') }}",
                    type: "GET"
                },
                order: [
                    [2, 'desc'],
                    [0, 'desc']
                ],
                columns: [dtControlColumn, ...colSetWithoutCreate()],
                searchDelay: 400,
                stateSave: true,
            });

            const tblCompleted = $('#tblCompleted').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                pageLength: 10,
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



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_CSCompleted',
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
                        title: 'List_CSCompleted',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                ajax: {
                    url: "{{ route('csjobs.completed.json') }}",
                    type: "GET"
                },
                order: [
                    [1, 'desc'], // doc_date
                    [0, 'desc'] // doc_no
                ],
                // columns: [
                //     {
                //         data: 'doc_no',
                //         className: 'text-left',
                //         render: (_v, _t, row) => renderDocBtn(row) // sama seperti sppbjkt
                //     },

                //     {
                //         data: 'doc_date',
                //         className: 'text-center',
                //         render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                //     },
                //     { data: 'cpny_id', className: 'text-center' },
                //     { data: 'department_id', className: 'text-center' },
                //     {
                //         data: 'created_by_name',
                //         className: 'text-left',
                //         defaultContent: '-'
                //     },
                //     { data: 'keperluan', className: 'text-left', defaultContent: '-' },
                // ],
                columns: [dtControlColumn, ...colSetWithoutCreate()],
                searchDelay: 400,
                stateSave: true,
            });


            // ===== Switching panes via cards =====
            function showPane(key) {
                $('#pane-mine, #pane-revision, #pane-all, #pane-sppbjkt, #pane-completed').addClass('hidden');
                $(`#pane-${key}`).removeClass('hidden');

                // tampilkan Entry CS hanya di CS Jobs
                if (key === 'mine') {
                    $('#pane-mine').find('#tblMine').DataTable().columns.adjust();
                    $('#pane-mine').find('#tblEntryCS').DataTable().columns.adjust();
                } else if (key === 'revision') {
                    tblRevision.columns.adjust();
                } else if (key === 'all') {
                    tblAll.columns.adjust();
                } else if (key === 'sppbjkt') {
                    tblSppbjkt.columns.adjust();
                } else if (key === 'completed') {
                    tblCompleted.columns.adjust();
                }


                // highlight kartu aktif
                $('.filter-card').removeClass('active');
                $(`#btn-${key}`).addClass('active');
            }

            // default ke CS Jobs (mine)
            showPane('mine');

            $('#btn-mine').on('click', () => showPane('mine'));
            $('#btn-revision').on('click', () => showPane('revision'));
            $('#btn-all').on('click', () => showPane('all'));
            $('#btn-sppbjkt').on('click', () => showPane('sppbjkt'));
            $('#btn-completed').on('click', () => showPane('completed'));


            // (Opsional) refresh counts
            function refreshCounts() {
                $.get("{{ route('csjobs.dataset.counts') }}")
                    .done(res => {
                        $('#count-mine').text(res.mine);
                        $('#count-revision').text(res.revision);
                        $('#count-all').text(res.all);
                        $('#count-sppbjkt').text(res.sppbjkt);
                    });
            }
            // refreshCounts(); // panggil bila diperlukan

            // Toggle .active class and remember selection
            const filters = document.querySelectorAll('.filter-card');
            const savedFilter = localStorage.getItem('activeFilter');

            if (savedFilter) {
                const activeFilter = document.querySelector(`#${savedFilter}`);
                if (activeFilter) activeFilter.classList.add('active');
            }

            filters.forEach(card => {
                card.addEventListener('click', e => {
                    e.preventDefault();
                    filters.forEach(c => c.classList.remove('active'));
                    card.classList.add('active');
                    localStorage.setItem('activeFilter', card.id);
                });
            });
        });
    </script>
    <script>
        function colSetRevision() {
            const actionCol = {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-left',
                render: (_d, _t, row) => {
                    // ✅ create tetap pakai eid (hash id tr_po_reuse)
                    const createUrl = `/createcs/${row.doc_type}/${row.eid}`;
                    return `
                    <div class="inline-flex gap-2">
                        <a href="${createUrl}"
                            class="inline-flex justify-center items-center px-3 py-1.5 text-sm font-medium text-white rounded bg-blue-500 hover:bg-blue-700"
                            title="Create CS dari PO">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>`;
                }
            };

            return [
                actionCol,
                {
                    data: 'ponbr',
                    className: 'text-left',
                    render: (v, _t, row) => {
                        const cat = String(row.inventory_category || '').toUpperCase();

                        // ✅ showkontrak harus pakai hash id kontrak
                        const kontrakEid = row.kontrak_eid ? String(row.kontrak_eid) : '';

                        const href = (cat === 'KONTRAK')
                            ? (kontrakEid ? `/showkontrak/${kontrakEid}` : '#')
                            : `/showpo/${row.eid}`;

                        const disabled = (cat === 'KONTRAK' && !kontrakEid)
                            ? 'opacity-50 pointer-events-none'
                            : '';

                        return `
                            <a href="${href}" target="_blank"
                            class="inline-flex justify-center items-center w-[120px] px-3 py-1.5
                                    text-sm leading-tight font-semibold text-white rounded text-center
                                    transition-colors duration-200 bg-gray-600 hover:bg-gray-700 ${disabled}">
                                ${v}
                            </a>`;
                    }
                },
                {
                    data: 'podate',
                    className: 'text-center',
                    render: v => v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                },
                { data: 'csid', className: 'text-center', defaultContent: '-' },
                { data: 'sppbjktid', className: 'text-center', defaultContent: '-' },
                { data: 'cpny_id', className: 'text-center', defaultContent: '-' },
                { data: 'department_id', className: 'text-center', defaultContent: '-' },
                { data: 'vendorname', className: 'text-left', defaultContent: '-' },
            ];
        }
    </script>
    {{-- <script>
        function colSetRevision() {
            // kolom Action (Create CS untuk PO)
            const actionCol = {
                data: null,
                orderable: false,
                searchable: false,
                className: 'text-left',
                render: (_d, _t, row) => {
                    // backend kirim doc_type = 'PO' dan eid = hashids(ponbr)
                    const createUrl = `/createcs/${row.doc_type}/${row.eid}`;
                    return `
                    <div class="inline-flex gap-2">
                        <a href="${createUrl}"
                            class="inline-flex justify-center items-center px-3 py-1.5  text-sm  font-medium text-white rounded bg-blue-500 hover:bg-blue-700"
                            title="Create CS dari PO">
                            <i class="fas fa-plus"></i>
                        </a>
                    </div>`;
                }
            };

            return [
                actionCol,
                {
                    data: 'ponbr',
                    className: 'text-left',
                    render: (v, _t, row) =>
                        `<a href="/showpo/${row.eid}" class="inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ">${v}</a>`
                },

                {
                    data: 'podate',
                    className: 'text-center',
                    render: v =>
                        v ? (isNaN(new Date(v)) ? v : new Date(v).toLocaleDateString('id-ID')) : ''
                },
                {
                    data: 'csid',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'sppbjktid',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'cpny_id',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'department_id',
                    className: 'text-center',
                    defaultContent: '-'
                },
                {
                    data: 'vendorname',
                    className: 'text-left',
                    defaultContent: '-'
                },
            ];
        }
    </script> --}}

    <script>
        $(document).on('click', '.btn-complete-open', function() {
            const doc = String($(this).data('doc') || ''); // SPPB | SPPJ | SPPK | SPPT
            const eid = String($(this).data('eid') || ''); // hashids src_id

            Swal.fire({
                title: 'Complete Sisa Order?',
                html: `
                    <div style="text-align:left;">
                        <p>Dokumen: <b>${doc}</b></p>
                        <p>Aksi ini akan menandai <b>semua sisa (open qty)</b> sebagai <b>Completed</b>.</p>
                        <p style="color:red; font-weight:bold;">Yakin ingin melanjutkan?</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Alasan Completed',
                    input: 'textarea',
                    inputPlaceholder: 'Wajib diisi alasan completed...',
                    inputAttributes: {
                        required: true,
                        minlength: 5
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (!value || value.trim().length < 5) {
                            Swal.showValidationMessage('Alasan wajib diisi (min 5 karakter)');
                            return false;
                        }
                        return value.trim();
                    }
                }).then((res) => {
                    if (!res.isConfirmed) return;

                    const $btn = $(this).prop('disabled', true);

                    $.ajax({
                            url: `/csjobs/complete/${doc}/${eid}`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                reason: res.value
                            },
                        })
                        .done(resp => {
                            if (resp.ok) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: resp.message ||
                                        'Sisa qty telah di-completed-kan.',
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                // reload tabel
                                try {
                                    $('#tblMine').DataTable().ajax.reload(null, false);
                                } catch (e) {}
                                try {
                                    $('#tblAll').DataTable().ajax.reload(null, false);
                                } catch (e) {}
                                try {
                                    $('#tblRevision').DataTable().ajax.reload(null, false);
                                } catch (e) {}
                                try {
                                    $('#tblSppbjkt').DataTable().ajax.reload(null, false);
                                } catch (e) {}
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: resp.message || 'Gagal memproses aksi.'
                                });
                            }
                        })
                        .fail(xhr => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal!',
                                text: xhr.responseJSON?.message ||
                                    'Terjadi kesalahan pada server.',
                            });
                        })
                        .always(() => $btn.prop('disabled', false));
                });
            });
        });
    </script>


    <script>
        $(document).on('click', '.btn-revise-doc', function() {
            const docType = String($(this).data('doc') || '');
            const docNo = String($(this).data('docno') || '');
            const cpnyId = String($(this).data('cpny') || '');
            const deptId = String($(this).data('dept') || '');

            Swal.fire({
                title: 'Revise Dokumen?',
                html: `
                    <div style="text-align:left;">
                        <p>Doc Type: <b>${docType}</b></p>
                        <p>Doc No: <b>${docNo}</b></p>
                        <p>Status dokumen akan diubah menjadi (Revise).</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Lanjut',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (!result.isConfirmed) return;

                Swal.fire({
                    title: 'Alasan Revise',
                    input: 'textarea',
                    inputPlaceholder: 'Wajib diisi alasan revise...',
                    inputAttributes: {
                        required: true,
                        minlength: 5
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Submit',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (!value || value.trim().length < 5) {
                            Swal.showValidationMessage('Alasan wajib diisi (min 5 karakter)');
                            return false;
                        }
                        return value.trim();
                    }
                }).then((res) => {
                    if (!res.isConfirmed) return;

                    $.ajax({
                        url: "{{ route('csjobs.revise') }}",
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            doc_type: docType,
                            doc_no: docNo,
                            cpny_id: cpnyId,
                            department_id: deptId,
                            reason: res.value
                        },
                        success: function(res) {
                            if (res.ok) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                $('#tblMine').DataTable().ajax.reload(null, false);
                                $('#tblAll').DataTable().ajax.reload(null, false);
                                $('#tblSppbjkt').DataTable().ajax.reload(null, false);
                            } else {
                                Swal.fire('Gagal', res.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON?.message ||
                                'Terjadi kesalahan server',
                                'error'
                            );
                        }
                    });
                });
            });
        });
    </script>


</x-app-layout>
