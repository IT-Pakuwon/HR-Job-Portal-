<x-app-layout>
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 24px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #ef4444;
            transition: .25s;
            border-radius: 9999px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .25s;
            border-radius: 9999px;
            box-shadow: 0 1px 3px rgba(0,0,0,.25);
        }

        .switch input:checked + .slider {
            background-color: #16a34a;
        }

        .switch input:checked + .slider:before {
            transform: translateX(22px);
        }

        .switch input:disabled + .slider {
            opacity: .45;
            cursor: not-allowed;
        }
    </style>
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 {{ $canParkingAccess ? 'xl:grid-cols-7' : 'xl:grid-cols-5' }}">

            <a href="#" class="status-filter active group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ number_format($all) }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-yellow-700 bg-yellow-200/20 p-3 text-yellow-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-yellow-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ number_format($onProgress) }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📝</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ number_format($revise) }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">❌</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Rejected</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ number_format($reject) }}</p>
                </div>
            </a>

            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>
                    <p class="shrink-0 text-base font-bold">{{ number_format($completed) }}</p>
                </div>
            </a>
            @if ($canParkingAccess)
                <a href="#" class="scope-filter group block h-full" data-scope="all">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🅿️</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">All Parking</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($allParkingCount) }}</p>
                    </div>
                </a>

                <a href="#" class="scope-filter group block h-full" data-scope="master">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-teal-700 bg-teal-200/20 p-3 text-teal-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-teal-100 hover:shadow-md active:scale-95">
                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🚗</div>
                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-sm font-medium">Master Kendaraan</p>
                        </div>
                        <p class="shrink-0 text-base font-bold">{{ number_format($masterKendaraanCount) }}</p>
                    </div>
                </a>
            @endif
        </div>

        {{-- TABLE --}}
        <div class="mt-6 flex flex-col rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="mb-4 flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 id="parkingTitle" class="text-base font-extrabold text-gray-700 dark:text-white">
                    Parking Registration
                </h1>
                <a id="createParkingBtn" href="{{ url('/createparkingregistration') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </a>
            </div>
            
            <div id="masterFilterBox" class="mb-4 hidden rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-700">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Site Parking</label>
                        <select id="filter_site_parking"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Site</option>
                            @foreach ($masterSites ?? [] as $site)
                                <option value="{{ $site->siteid }}">{{ $site->siteid }} - {{ $site->site_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Parking Type</label>
                        <select id="filter_parking_type"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Parking Type</option>
                            @foreach ($parkingTypes ?? [] as $row)
                                <option value="{{ $row->categoryid }}">{{ $row->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Worker Type</label>
                        <select id="filter_worker_type"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Worker Type</option>
                            @foreach ($workerTypes ?? [] as $row)
                                <option value="{{ $row->categoryid }}">{{ $row->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Jenis Kendaraan</label>
                        <select id="filter_jenis_kendaraan"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Jenis</option>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
                        <select id="filter_department"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300">
                            <option value="">All Department</option>
                            @foreach ($masterDepartments ?? [] as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div id="noKartuModal"
                class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-lg dark:bg-gray-800">
                    <div class="mb-4 flex items-center justify-between border-b pb-3 dark:border-gray-700">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white">
                            Update No Kartu
                        </h3>

                        <button type="button" id="closeNoKartuModal"
                            class="rounded px-2 py-1 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700">
                            ✕
                        </button>
                    </div>

                    <input type="hidden" id="modal_kendaraan_id">

                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            No Kartu
                        </label>
                        <input type="text" id="modal_no_kartu"
                            class="w-full rounded border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                            placeholder="Input No Kartu">
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" id="cancelNoKartuModal"
                            class="rounded bg-gray-200 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-300">
                            Close
                        </button>

                        <button type="button" id="saveNoKartuBtn"
                            class="rounded bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                </div>
            </div>
            

            <div class="rounded-base relative overflow-x-auto">
                <table id="parkingRegistrationTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead id="parkingRegistrationThead"
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-36 px-6 py-2 font-medium">Doc ID</th>
                            <th class="w-36 px-6 py-2 font-medium">Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Company</th>
                            <th class="w-40 px-6 py-2 font-medium">Department</th>
                            <th class="w-40 px-6 py-2 font-medium">Site Parking</th>
                            <th class="w-36 px-6 py-2 font-medium">Parking Type</th>
                            <th class="w-36 px-6 py-2 font-medium">Worker Type</th>
                            <th class="w-32 px-6 py-2 font-medium">Perpost</th>
                            <th class="w-56 px-6 py-2 font-medium">Info</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>       
            
            const currentUser = @json(auth()->user()->username ?? '');
            let parkingStatus = '';
            let parkingScope = 'my';
            let parkingTable = null;

            function toggleCreateButton() {
                if (parkingScope === 'all' || parkingScope === 'master') {
                    $('#createParkingBtn').css('display', 'none');
                } else {
                    $('#createParkingBtn').css('display', 'inline-flex');
                }
            }

            function setParkingHeader(scope) {
                if (scope === 'master') {
                    $('#parkingRegistrationThead').html(`
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-28 px-6 py-2 font-medium">Action</th>
                            <th class="w-40 px-6 py-2 font-medium">Site Parking</th>
                            <th class="w-56 px-6 py-2 font-medium">Name</th>
                            <th class="w-36 px-6 py-2 font-medium">No Polisi</th>
                            <th class="w-36 px-6 py-2 font-medium">Jenis Kendaraan</th>
                            <th class="w-40 px-6 py-2 font-medium">Parking Type</th>
                            <th class="w-40 px-6 py-2 font-medium">Worker Type</th>
                            <th class="w-40 px-6 py-2 font-medium">Department</th>
                            <th class="w-28 px-6 py-2 font-medium">Perpost</th>
                            <th class="w-36 px-6 py-2 font-medium">Start Date</th>
                            <th class="w-36 px-6 py-2 font-medium">End Date</th>
                            <th class="w-32 px-6 py-2 font-medium">No Kartu</th>
                            <th class="w-32 px-6 py-2 font-medium">STNK</th>
                            <th class="w-32 px-6 py-2 font-medium">ID Card</th>
                            <th class="w-36 px-6 py-2 font-medium">Bukti Bayar</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    `);
                } else {
                    $('#parkingRegistrationThead').html(`
                        <tr>
                            <th class="w-8"></th>
                            <th class="w-36 px-6 py-2 font-medium">Doc ID</th>
                            <th class="w-36 px-6 py-2 font-medium">Date</th>
                            <th class="w-32 px-6 py-2 font-medium">Company</th>
                            <th class="w-40 px-6 py-2 font-medium">Department</th>
                            <th class="w-40 px-6 py-2 font-medium">Site Parking</th>
                            <th class="w-36 px-6 py-2 font-medium">Parking Type</th>
                            <th class="w-36 px-6 py-2 font-medium">Worker Type</th>
                            <th class="w-32 px-6 py-2 font-medium">Perpost</th>
                            <th class="w-56 px-6 py-2 font-medium">Info</th>
                            <th class="w-32 px-6 py-2 font-medium">Status</th>
                        </tr>
                    `);
                }
            }

            function statusBadge(v) {
                v = String(v || '').toUpperCase();

                if (v === 'A') {
                    return `<span class="inline-block w-24 rounded bg-green-300/30 px-3 py-1.5 text-sm font-semibold text-green-600">Active</span>`;
                }

                if (v === 'I') {
                    return `<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Inactive</span>`;
                }
                if (v === 'D') {
                    return `<span class="inline-block w-24 rounded bg-blue-300/30 px-3 py-1.5 text-sm font-semibold text-blue-600">Revise</span>`;
                }

                if (v === 'P') {
                    return `<span class="inline-block w-28 rounded bg-yellow-300/30 px-3 py-1.5 text-sm font-semibold text-yellow-600">On Progress</span>`;
                }

                if (v === 'C') {
                    return `<span class="inline-block w-28 rounded bg-green-300/30 px-3 py-1.5 text-sm font-semibold text-green-600">Completed</span>`;
                }

                if (v === 'R') {
                    return `<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Rejected</span>`;
                }

                if (v === 'X') {
                    return `<span class="inline-block w-24 rounded bg-red-300/30 px-3 py-1.5 text-sm font-semibold text-red-600">Cancelled</span>`;
                }

                return `<span class="inline-block w-24 rounded bg-gray-300/30 px-3 py-1.5 text-sm font-semibold text-gray-600">${v || '-'}</span>`;
            }

            function fileBadge(url) {
                if (!url) {
                    return '-';
                }

                return `
                    <a href="${url}" target="_blank"
                        class="inline-block rounded bg-indigo-100 px-2 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200 hover:underline">
                        📎 View
                    </a>
                `;
            }

            function getParkingColumns(scope) {
                if (scope === 'master') {
                    return [
                        {
                            data: null,
                            defaultContent: '',
                            searchable: false,
                            orderable: false
                        },
                        {
                            data: null,
                            searchable: false,
                            orderable: false,
                            className: 'text-center',
                            render: function (data, type, row) {
                                const id = row.id;

                                if (!id) {
                                    return `<span class="text-xs text-red-500">ID missing</span>`;
                                }

                                const status = String(row.status || '').toUpperCase();
                                const checked = status === 'A' ? 'checked' : '';
                                const disabled = ['P', 'D'].includes(status) ? 'disabled' : '';

                                return `
                                    <div class="flex items-center justify-center gap-3">
                                        <label class="switch" title="${status === 'A' ? 'Set Inactive' : 'Set Active'}">
                                            <input type="checkbox"
                                                class="toggleStatus"
                                                data-id="${id}"
                                                data-status="${status}"
                                                ${checked}
                                                ${disabled}>
                                            <span class="slider round"></span>
                                        </label>

                                        <button type="button"
                                            class="openNoKartuBtn inline-flex items-center justify-center rounded bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-700"
                                            data-id="${id}"
                                            data-no-kartu="${row.no_kartu || ''}"
                                            title="Update No Kartu">
                                            <i class="fa-solid fa-id-card"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        { data: 'site_parking_name', defaultContent: '-' },
                        { data: 'nama', defaultContent: '-' },
                        { data: 'nopol', defaultContent: '-' },
                        { data: 'jenis_kendaraan', defaultContent: '-' },
                        { data: 'parking_type_name', defaultContent: '-' },
                        { data: 'worker_type_name', defaultContent: '-' },
                        { data: 'department_id', defaultContent: '-' },
                        { data: 'perpost', defaultContent: '-' },
                        { data: 'startdate', defaultContent: '-' },
                        { data: 'enddate', defaultContent: '-' },
                        { data: 'no_kartu', defaultContent: '-' },
                        {
                            data: 'attach_stnk_url',
                            render: function (data) {
                                return fileBadge(data);
                            }
                        },
                        {
                            data: 'attach_idcard_url',
                            render: function (data) {
                                return fileBadge(data);
                            }
                        },
                        {
                            data: 'attach_bukti_bayar_url',
                            render: function (data) {
                                return fileBadge(data);
                            }
                        },
                        {
                            data: 'status',
                            className: 'text-center',
                            render: function (data) {
                                return statusBadge(data);
                            }
                        }
                    ];
                }

                return [
                    {
                        data: null,
                        defaultContent: '',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'docid',
                        render: function (data, type, row) {
                            const text = data || '-';

                            let mainUrl = `/showparkingregistration/${row.eid}`;
                            let mainCls = 'inline-flex w-[160px] justify-center rounded bg-gray-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-gray-700';

                            if (row.status === 'D' && row.created_by === currentUser) {
                                mainUrl = `/editparkingregistration/${row.eid}`;
                                mainCls = 'inline-flex w-[160px] justify-center rounded bg-yellow-500 px-3 py-1.5 text-sm font-semibold text-white hover:bg-yellow-700';

                                return `
                                    <div class="flex items-center gap-2">
                                        <a href="${mainUrl}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="${mainCls}">
                                            ${text}
                                        </a>

                                        <a href="/showparkingregistration/${row.eid}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center rounded bg-gray-500 px-3 py-2 text-sm font-semibold text-white hover:bg-gray-700"
                                            title="View Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                    </div>
                                `;
                            }

                            return `
                                <a href="${mainUrl}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="${mainCls}">
                                    ${text}
                                </a>
                            `;
                        }
                    },
                    {
                        data: 'parking_regist_date',
                        defaultContent: '-'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center',
                        defaultContent: '-'
                    },
                    {
                        data: 'department_id',
                        className: 'text-center whitespace-normal break-words',
                        defaultContent: '-'
                    },
                    {
                        data: 'site_parking_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'parking_type_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'worker_type_name',
                        defaultContent: '-'
                    },
                    {
                        data: 'perpost',
                        defaultContent: '-'
                    },
                    {
                        data: 'info',
                        defaultContent: '-'
                    },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function (data) {
                            return statusBadge(data);
                        }
                    }
                ];
            }

            function initParkingTable() {
                if (parkingTable) {
                    parkingTable.destroy();
                    $('#parkingRegistrationTable tbody').empty();
                }

                setParkingHeader(parkingScope);
                toggleCreateButton();

                parkingTable = $('#parkingRegistrationTable').DataTable({
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
                            target: 0
                        }
                    },
                    columnDefs: [
                        {
                            targets: '_all',
                            className: 'whitespace-normal break-words'
                        },
                        {
                            targets: 0,
                            width: '28px',
                            className: 'dtr-control',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    dom: '<"dt-toolbar"l B f>rtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: parkingScope === 'master' ? 'Master_Kendaraan' : 'Parking_Registration',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700'
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: parkingScope === 'master' ? 'Master_Kendaraan' : 'Parking_Registration',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700'
                        }
                    ],
                    ajax: {
                        url: "{{ route('parkingregistration.json') }}",
                        type: "GET",
                        data: function (d) {
                            d.status = parkingStatus;
                            d.scope = parkingScope;

                            if (parkingScope === 'master') {
                                d.site_parking = $('#filter_site_parking').val() || '';
                                d.parking_type = $('#filter_parking_type').val() || '';
                                d.worker_type = $('#filter_worker_type').val() || '';
                                d.jenis_kendaraan = $('#filter_jenis_kendaraan').val() || '';
                                d.department_id = $('#filter_department').val() || '';
                            }
                        }
                    },
                    order: parkingScope === 'master'
                        ? [[2, 'asc']]
                        : [[1, 'desc']],
                    columns: getParkingColumns(parkingScope)
                });
            }

            

            function resetMasterFilters() {
                $('#filter_site_parking').val('');
                $('#filter_parking_type').val('');
                $('#filter_worker_type').val('');
                $('#filter_jenis_kendaraan').val('');
                $('#filter_department').val('');
            }

            $(document).ready(function () {

                $(document).on('change', '.toggleStatus', function () {
                    const $toggle = $(this);
                    const id = $toggle.data('id');
                    const currentStatus = String($toggle.data('status') || '').toUpperCase();

                    if (!id) {
                        toastr.error('ID kendaraan tidak ditemukan.');
                        $toggle.prop('checked', currentStatus === 'A');
                        return;
                    }

                    const willBeActive = $toggle.is(':checked');
                    const nextLabel = willBeActive ? 'Active' : 'Inactive';

                    Swal.fire({
                        title: 'Anda yakin?',
                        text: `Status kendaraan akan diubah menjadi ${nextLabel}.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Update',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#2563eb',
                        cancelButtonColor: '#6b7280',
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            $toggle.prop('checked', currentStatus === 'A');
                            return;
                        }

                        $toggle.prop('disabled', true);

                        $.ajax({
                            url: `/parking-kendaraan/${id}/toggle-status`,
                            type: 'POST',
                            data: {
                                _token: "{{ csrf_token() }}",
                                _method: 'PUT'
                            }
                        })
                        .done(function (res) {
                            toastr.success(res.message || 'Status berhasil diupdate.');

                            if (parkingTable) {
                                parkingTable.ajax.reload(null, false);
                            }
                        })
                        .fail(function (xhr) {
                            $toggle.prop('checked', currentStatus === 'A');

                            let msg = xhr.responseJSON?.message || xhr.responseJSON?.error || 'Gagal update status.';
                            toastr.error(msg);
                            console.error(xhr.responseText);
                        })
                        .always(function () {
                            $toggle.prop('disabled', false);
                        });
                    });
                });

                $(document).on('click', '.openNoKartuBtn', function () {
                    const id = $(this).data('id');
                    const noKartu = $(this).data('no-kartu') || '';

                    $('#modal_kendaraan_id').val(id);
                    $('#modal_no_kartu').val(noKartu);

                    $('#noKartuModal').removeClass('hidden').addClass('flex');
                });

                $('#closeNoKartuModal, #cancelNoKartuModal').on('click', function () {
                    $('#noKartuModal').addClass('hidden').removeClass('flex');
                    $('#modal_kendaraan_id').val('');
                    $('#modal_no_kartu').val('');
                });

                $('#saveNoKartuBtn').on('click', function () {
                    const id = $('#modal_kendaraan_id').val();
                    const noKartu = String($('#modal_no_kartu').val() || '').trim();

                    if (!noKartu) {
                        toastr.warning('No Kartu wajib diisi.');
                        return;
                    }

                    $('#saveNoKartuBtn').prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: `/parking-kendaraan/${id}/no-kartu`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT',
                            no_kartu: noKartu
                        }
                    })
                    .done(function (res) {
                        toastr.success(res.message || 'No Kartu berhasil disimpan.');

                        $('#noKartuModal').addClass('hidden').removeClass('flex');
                        $('#modal_kendaraan_id').val('');
                        $('#modal_no_kartu').val('');

                        if (parkingTable) {
                            parkingTable.ajax.reload(null, false);
                        }
                    })
                    .fail(function (xhr) {
                        let msg = 'Gagal simpan No Kartu.';

                        if (xhr.status === 422 && xhr.responseJSON?.errors) {
                            msg = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                        } else if (xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.responseJSON?.error) {
                            msg = xhr.responseJSON.error;
                        }

                        toastr.error(msg);
                        console.error(xhr.responseText);
                    })
                    .always(function () {
                        $('#saveNoKartuBtn').prop('disabled', false).text('Save');
                    });
                });
                $(document).on('change', '#filter_site_parking, #filter_parking_type, #filter_worker_type, #filter_jenis_kendaraan, #filter_department', function () {
                    if (parkingScope === 'master' && parkingTable) {
                        parkingTable.ajax.reload(null, true);
                    }
                });

                initParkingTable();

                $('.status-filter').on('click', function (e) {
                    e.preventDefault();

                    $('.status-filter, .scope-filter').removeClass('active');
                    $(this).addClass('active');

                    parkingScope = 'my';
                    parkingStatus = $(this).data('status') ?? '';

                    $('#masterFilterBox').addClass('hidden');
                    resetMasterFilters();

                    const titleMap = {
                        '': 'Parking Registration',
                        'P': 'Parking Registration - On Progress',
                        'D': 'Parking Registration - Revise',
                        'R': 'Parking Registration - Rejected',
                        'C': 'Parking Registration - Completed'
                    };

                    $('#parkingTitle').text(titleMap[parkingStatus] ?? 'Parking Registration');

                    toggleCreateButton();
                    initParkingTable();
                });

                $('.scope-filter').on('click', function (e) {
                    e.preventDefault();

                    $('.status-filter, .scope-filter').removeClass('active');
                    $(this).addClass('active');

                    parkingStatus = '';
                    parkingScope = $(this).data('scope') || 'my';

                    if (parkingScope === 'master') {
                        $('#parkingTitle').text('Master Kendaraan');
                        $('#masterFilterBox').removeClass('hidden');
                    } else if (parkingScope === 'all') {
                        $('#parkingTitle').text('All Parking');
                        $('#masterFilterBox').addClass('hidden');

                        // reset filter master supaya tidak nyangkut
                        resetMasterFilters();
                    } else {
                        $('#parkingTitle').text('Parking Registration');
                        $('#masterFilterBox').addClass('hidden');

                        resetMasterFilters();
                    }

                    toggleCreateButton();
                    initParkingTable();
                });
            });
        </script>

    </div>
</x-app-layout>