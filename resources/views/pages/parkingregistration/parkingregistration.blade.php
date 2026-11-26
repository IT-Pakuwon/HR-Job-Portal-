<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    <x-spp-form-styles />
    <x-it-modal-skin />
    <x-ga-styles />
    <div class="max-w-9xl mx-auto w-full p-2">

        {{-- STATUS CARDS --}}
        @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', number_format($all), false],
                [['data-status' => 'H'], 'Draft', '📝', 'pink', number_format($draft), false],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', number_format($onProgress), true],
                [['data-status' => 'D'], 'Revise', '✏️', 'blue', number_format($revise), false],
                [['data-status' => 'R'], 'Rejected', '⛔', 'red', number_format($reject), false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', number_format($completed), false],
            ];
            if ($canParkingAccess) {
                $statusCards[] = [['data-scope' => 'all'], 'All Parking', '🅿️', 'purple', number_format($allParkingCount), false, null, 'scope-filter', true];
            }
            if ($canViewMasterKendaraan) {
                $statusCards[] = [['data-scope' => 'master'], 'List Kendaraan', '🚗', 'sky', number_format($masterKendaraanCount), false, null, 'scope-filter', true];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        {{-- TABLE --}}
        <div class="dt-pretty mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Parking Registration - On Progress" title-id="parkingTitle" subtitle="Register vehicles for free parking and manage parking cards"
                :create-url="url('/createparkingregistration')" create-id="createParkingBtn">
                @if (auth()->check() && auth()->user()->hasRole('GAACCESS'))
                    <a href="{{ route('kendaraan') }}"
                        class="inline-flex h-10 items-center justify-center rounded-lg border border-blue-200 bg-blue-50 px-4 text-sm font-semibold text-blue-700 transition hover:bg-blue-100 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300">
                        <i class="fa-solid fa-car mr-2 text-xs"></i>Master Kendaraan
                    </a>
                @endif
            </x-list-page-header>

            <div id="masterFilterBox" class="mx-5 mt-4 hidden rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-white/10 dark:bg-white/[0.03]">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-5">

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Site Parking</label>
                        <select id="filter_site_parking"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Site</option>
                            @foreach ($masterSites ?? [] as $site)
                                <option value="{{ $site->siteid }}">{{ $site->siteid }} - {{ $site->site_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Parking Type</label>
                        <select id="filter_parking_type"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Parking Type</option>
                            @foreach ($parkingTypes ?? [] as $row)
                                <option value="{{ $row->categoryid }}">{{ $row->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Worker Type</label>
                        <select id="filter_worker_type"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Worker Type</option>
                            @foreach ($workerTypes ?? [] as $row)
                                <option value="{{ $row->categoryid }}">{{ $row->category_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jenis Kendaraan</label>
                        <select id="filter_jenis_kendaraan"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Jenis</option>
                            <option value="Motor">Motor</option>
                            <option value="Mobil">Mobil</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Department</label>
                        <select id="filter_department"
                            class="sf-select h-10 w-full rounded-lg border border-gray-300 bg-white px-3 text-sm text-gray-800 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">
                            <option value="">All Department</option>
                            @foreach ($masterDepartments ?? [] as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
            </div>

            <div id="noKartuModal"
                class="itm sf-field fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 p-4">
                <div class="itm-dialog w-full max-w-md overflow-hidden rounded-2xl bg-white dark:bg-gray-800">
                    <div class="itm-head">
                        <div>
                            <h3>Update No Kartu</h3>
                            <p>Set the parking card number for this vehicle</p>
                        </div>

                        <button type="button" id="closeNoKartuModal" class="itm-close" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <input type="hidden" id="modal_kendaraan_id">

                    <div class="itm-body px-6 py-5">
                        <label for="modal_no_kartu" class="mb-1.5 block">
                            No Kartu
                        </label>
                        <input type="text" id="modal_no_kartu" class="w-full" placeholder="Input No Kartu">
                    </div>

                    <div class="itm-foot flex justify-end gap-2 px-6 py-4">
                        <button type="button" id="cancelNoKartuModal" class="itm-btn font-semibold">
                            Close
                        </button>

                        <button type="button" id="saveNoKartuBtn" class="itm-primary">
                            Save
                        </button>
                    </div>
                </div>
            </div>

            <div id="companyDepartmentModal"
                class="itm sf-field fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 p-4">
                <div class="itm-dialog w-full max-w-lg overflow-hidden rounded-2xl bg-white dark:bg-gray-800">
                    <div class="itm-head">
                        <div>
                            <h3>Change Company &amp; Department</h3>
                            <p>Move this vehicle to another company or department</p>
                        </div>

                        <button type="button" id="closeCompanyDepartmentModal" class="itm-close" aria-label="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <input type="hidden" id="modal_company_department_id">

                    <div class="itm-body space-y-4 px-6 py-5">
                        <div>
                            <label for="modal_cpny_id" class="mb-1.5 block">
                                Company
                            </label>
                            <select id="modal_cpny_id" class="w-full">
                            <option value="">Select Company</option>
                            @foreach ($masterCompanies ?? [] as $company)
                                <option value="{{ $company->cpny_id }}">{{ $company->cpny_id }} - {{ $company->cpny_name }}</option>
                            @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="modal_department_id" class="mb-1.5 block">
                                Department
                            </label>
                            <select id="modal_department_id" class="w-full">
                            <option value="">Select Department</option>
                            @foreach ($masterDepartmentOptions ?? [] as $department)
                                <option value="{{ $department->department_id }}">{{ $department->department_id }} - {{ $department->department_name }}</option>
                            @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="itm-foot flex justify-end gap-2 px-6 py-4">
                        <button type="button" id="cancelCompanyDepartmentModal" class="itm-btn font-semibold">
                            Close
                        </button>

                        <button type="button" id="saveCompanyDepartmentBtn" class="itm-primary">
                            Save
                        </button>
                    </div>
                </div>
            </div>


            <div class="relative overflow-x-hidden">
                <table id="parkingRegistrationTable" class="w-full min-w-full border-separate border-spacing-0 text-left text-sm">
                    <thead id="parkingRegistrationThead"
                        class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr>
                            <th class="w-8 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Doc ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Site Parking</th>
                            <th class="px-4 py-3 text-left font-medium">Parking Type</th>
                            <th class="px-4 py-3 text-left font-medium">Worker Type</th>
                            <th class="px-4 py-3 text-left font-medium">Perpost</th>
                            <th class="px-4 py-3 text-left font-medium">Info</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
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
            const canEditMasterKendaraan = @json($canParkingAccess);
            let parkingStatus = 'P';
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
                            <th class="w-8 px-4 py-3"></th>
                            <th class="w-44 px-4 py-3 text-left font-medium">Action</th>
                            <th class="px-4 py-3 text-left font-medium">Site Parking</th>
                            <th class="px-4 py-3 text-left font-medium">Name</th>
                            <th class="px-4 py-3 text-left font-medium">No Polisi</th>
                            <th class="px-4 py-3 text-left font-medium">Jenis Kendaraan</th>
                            <th class="px-4 py-3 text-left font-medium">Parking Type</th>
                            <th class="px-4 py-3 text-left font-medium">Worker Type</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Perpost</th>
                            <th class="px-4 py-3 text-left font-medium">Start Date</th>
                            <th class="px-4 py-3 text-left font-medium">End Date</th>
                            <th class="px-4 py-3 text-left font-medium">No Kartu</th>
                            <th class="px-4 py-3 text-left font-medium">STNK</th>
                            <th class="px-4 py-3 text-left font-medium">ID Card</th>
                            <th class="px-4 py-3 text-left font-medium">Bukti Bayar</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    `);
                } else {
                    $('#parkingRegistrationThead').html(`
                        <tr>
                            <th class="w-8 px-4 py-3"></th>
                            <th class="px-4 py-3 text-left font-medium">Doc ID</th>
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Site Parking</th>
                            <th class="px-4 py-3 text-left font-medium">Parking Type</th>
                            <th class="px-4 py-3 text-left font-medium">Worker Type</th>
                            <th class="px-4 py-3 text-left font-medium">Perpost</th>
                            <th class="px-4 py-3 text-left font-medium">Info</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                        </tr>
                    `);
                }
            }

            function statusBadge(v) {
                v = String(v || '').toUpperCase();

                const map = {
                    A: ['green', 'Active'], I: ['red', 'Inactive'], D: ['blue', 'Revise'], H: ['pink', 'Draft'],
                    P: ['yellow', 'On Progress'], C: ['green', 'Completed'], R: ['red', 'Rejected'], X: ['red', 'Cancelled']
                };
                const [tone, text] = map[v] || ['slate', v || '-'];

                return window.listBadge(tone, text, true);
            }

            function fileBadge(url) {
                if (!url) {
                    return '-';
                }

                return `
                    <a href="${url}" target="_blank" class="${window.listCls.action.blue}">
                        <i class="fa-solid fa-paperclip"></i> View
                    </a>
                `;
            }

            function escapeAttr(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;');
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

                                if (!canEditMasterKendaraan) {
                                    return `<span class="text-xs text-gray-400">-</span>`;
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
                                            class="openNoKartuBtn ${window.listCls.view}"
                                            data-id="${id}"
                                            data-no-kartu="${escapeAttr(row.no_kartu)}"
                                            title="Update No Kartu">
                                            <i class="fa-solid fa-id-card"></i>
                                        </button>

                                        <button type="button"
                                            class="openCompanyDepartmentBtn ${window.listCls.action.amber}"
                                            data-id="${id}"
                                            data-cpny-id="${escapeAttr(row.cpny_id)}"
                                            data-department-id="${escapeAttr(row.department_id)}"
                                            title="Change Company & Department">
                                            <i class="fa-solid fa-rotate"></i>
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
                        { data: 'cpny_id', defaultContent: '-' },
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
                            let mainCls = window.listCls.doc;
                            let docText = window.listDocText(text, false);

                            if (['D', 'H'].includes(row.status) && row.created_by === currentUser) {
                                mainUrl = `/editparkingregistration/${row.eid}`;
                                mainCls = window.listCls.docEdit;
                                docText = window.listDocText(text, true);

                                return `
                                    <div class="flex items-center gap-3 whitespace-nowrap">
                                        <a href="${mainUrl}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="${mainCls}">
                                            ${docText}
                                        </a>

                                        <a href="/showparkingregistration/${row.eid}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="${window.listCls.view}"
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
                                    ${docText}
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

                $(document).on('click', '.openCompanyDepartmentBtn', function () {
                    const id = $(this).data('id');
                    const cpnyId = $(this).data('cpny-id') || '';
                    const departmentId = $(this).data('department-id') || '';

                    $('#modal_company_department_id').val(id);
                    $('#modal_cpny_id').val(cpnyId);
                    $('#modal_department_id').val(departmentId);

                    $('#companyDepartmentModal').removeClass('hidden').addClass('flex');
                });

                $('#closeCompanyDepartmentModal, #cancelCompanyDepartmentModal').on('click', function () {
                    $('#companyDepartmentModal').addClass('hidden').removeClass('flex');
                    $('#modal_company_department_id').val('');
                    $('#modal_cpny_id').val('');
                    $('#modal_department_id').val('');
                });

                $('#saveCompanyDepartmentBtn').on('click', function () {
                    const id = $('#modal_company_department_id').val();
                    const cpnyId = String($('#modal_cpny_id').val() || '').trim();
                    const departmentId = String($('#modal_department_id').val() || '').trim();

                    if (!cpnyId || !departmentId) {
                        toastr.warning('Company dan Department wajib dipilih.');
                        return;
                    }

                    $('#saveCompanyDepartmentBtn').prop('disabled', true).text('Saving...');

                    $.ajax({
                        url: `/parking-kendaraan/${id}/company-department`,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT',
                            cpny_id: cpnyId,
                            department_id: departmentId
                        }
                    })
                    .done(function (res) {
                        toastr.success(res.message || 'Company dan department berhasil diupdate.');

                        $('#companyDepartmentModal').addClass('hidden').removeClass('flex');
                        $('#modal_company_department_id').val('');
                        $('#modal_cpny_id').val('');
                        $('#modal_department_id').val('');

                        if (parkingTable) {
                            parkingTable.ajax.reload(null, false);
                        }
                    })
                    .fail(function (xhr) {
                        let msg = 'Gagal update company dan department.';

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
                        $('#saveCompanyDepartmentBtn').prop('disabled', false).text('Save');
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
                        'H': 'Parking Registration - Draft',
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
