<x-app-layout>
    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        /* All */
        .status-filter[data-doc=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12)
        }

        /* SPPB (Blue) */
        .status-filter[data-doc="SPPB"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
        }

        /* SPPJ (Red) */
        .status-filter[data-doc="SPPJ"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
        }

        /* SPPK (Gray) */
        .status-filter[data-doc="SPPK"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
        }

        /* SPPT (Green) */
        .status-filter[data-doc="SPPT"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }

        .no-border {
            border: none !important;
        }

        .grid {
            width: 100%;
        }

        select,
        textarea,
        input {
            width: 100%;
            /* Make all input elements take full width */
        }

        table.dataTable {
            width: 100% !important;
        }

        .dataTables_wrapper {
            width: 100%;
        }

        @media (max-width: 600px) {
            .dataTables_wrapper {
                padding: 0 10px;
            }
        }

        /* Sppb Table Specific Styles */
        #canvassTable_filter {
            margin-bottom: 20px;
            display: flex;
            justify-content: flex-start;
            align-items: center;
        }

        #canvassTable_filter label {
            margin-right: 2px;
        }

        #canvassTable_filter input {
            width: 200px;
        }

        #canvassTable_wrapper {
            width: 100%;
        }

        #canvassTable td {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #canvassTable th,
        #canvassTable td {
            padding: 10px;
            max-width: 200px;
        }

        #canvassTable_length {
            width: auto;
            display: flex;
            justify-content: flex-start;
        }

        #canvassTable_length select {
            width: auto;
            padding: 5px;
            min-width: 80px;
            padding: 0.25rem 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            background-color: #f9fafb;
        }

        #canvassTable_length select option {
            padding: 5px;
        }

        #canvassTable_info {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .dataTables_paginate {
            /* This class is for all DataTables paginations */
            margin-top: 10px;
            margin-bottom: 10px;
        }

        #canvassTable tbody tr td {
            padding: 8px 8px;
            line-height: 2;
        }

        #canvassTable tbody tr {
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        #canvassTable tbody tr:hover {
            background-color: #8f8f8f11;
            opacity: 100%;
            cursor: pointer;
        }

        #canvassTable tbody tr:hover td {
            /* color: black; */
        }

        #canvassTable th:nth-child(1),
        #canvassTable td:nth-child(1) {
            width: 120px;
            text-align: center;
        }

        #canvassTable th:nth-child(4),
        #canvassTable td:nth-child(4) {
            width: 120px;
            text-align: center;
        }

        /* --- Custom Styles for RowGroup Collapse/Expand (Applied to canvassTable) --- */
        /* Initially hide rows in collapsed groups */
        #canvassTable tbody tr.collapsed-group-row {
            display: none;
        }

        /* Style for group rows */
        #canvassTable tr.group-row {
            background-color: #e6e6e6;
            /* Light gray background for group headers */
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            /* Prevent text selection on click */
            color: #333;
            /* Darker text for group headers */
        }

        #canvassTable tr.group-row:hover {
            background-color: #d4d4d4;
            /* Slightly darker on hover */
        }

        /* Icon styling */
        #canvassTable tr.group-row .fas {
            margin-right: 8px;
            width: 16px;
            /* Ensure consistent icon width */
            text-align: center;
        }

        /* Adjust padding for group rows to look consistent with other cells */
        #canvassTable tr.group-row td {
            padding: 10px !important;
            border-bottom: 1px solid #ddd;
            /* Separator for groups */
        }

        /* Remove border from the first td in group row to match the colspan */
        #canvassTable tr.group-row td:first-child {
            border-left: none;
        }

        /* ✅ Custom Switch Button (Global, if used elsewhere) */
        .switch {
            position: relative;
            display: inline-block;
            width: 40px;
            height: 22px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.slider {
            background-color: #4CAF50;
        }

        input:checked+.slider:before {
            transform: translateX(18px);
        }
    </style>
    {{-- Select2 CDN --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Toastr (kalau belum ada di layout) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @php
        $currentPage = Route::currentRouteName() == 'canvass' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-doc="">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📄</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">All</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $all }}</p>
                    </div>
                </a>
            </button>

            {{-- SPPB --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-doc="SPPB">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">SPPB</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppb }}</p>
                    </div>
                </a>
            </button>

            {{-- SPPJ --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-doc="SPPJ">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⛔️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">SPPJ</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppj }}</p>
                    </div>
                </a>
            </button>

            {{-- SPPK --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-doc="SPPK">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✏️</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">SPPK</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppk }}</p>
                    </div>
                </a>
            </button>

            {{-- SPPT --}}
            <button type="button" class="text-left">
                <a href="#" class="status-filter group block h-full" data-doc="SPPT">
                    <div
                        class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                        <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                        <div class="flex min-w-0 flex-grow flex-col leading-tight">
                            <p class="break-words text-base font-medium">SPPT</p>
                        </div>

                        <p class="shrink-0 text-xl font-bold">{{ $sppt }}</p>
                    </div>
                </a>
            </button>

        </div>


        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Assign List</h1>
                    <button id="btnAssignPurchasing"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-check pr-2"></i>Assign Purchasing
                    </button>
                </div>

                <div class="overflow-x-auto p-6"> {{-- Padding applied here instead of outer container --}}
                    <table id="canvassTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Assign Purchasing</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Created By</th>
                                <th
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <script>
                $(function() {
                    // CSRF untuk POST
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });

                    let docTypeFilter = ''; // ''=All

                    const table = $('#canvassTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, 250],
                        ajax: {
                            url: "{{ route('assignlist.json') }}",
                            type: "GET",
                            data: function(d) {
                                d.doc = docTypeFilter;
                            }
                        },
                        order: [
                            [2, 'desc'], // doc_date desc
                            [0, 'desc'], // doc_no desc
                        ],
                        columns: [
                            // 0) DocID -> tombol biru menuju show/{src_id} sesuai doc_type
                            {
                                data: 'doc_no',
                                className: 'text-left',
                                render: function(data, type, row) {
                                    const map = {
                                        SPPB: 'showsppbs',
                                        SPPJ: 'showsppjs',
                                        SPPK: 'showsppks',
                                        SPPT: 'showsppts'
                                    };
                                    const base = map[row.doc_type] || '#';
                                    // const url = `/${base}/${row.src_id}`;
                                    const url = `/${base}/${row.eid}`;
                                    return `
                            <a href="${url}" class="inline-flex items-center rounded px-3 py-1.5
                                bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold">${data}</a>`;
                                }
                            },

                            // 1) assignpurchasing -> <select> + Select2 (text=name, value=username)
                            {
                                data: 'assignpurchasing',
                                className: 'text-left',
                                render: function(val, type, row) {
                                    // treat '0' as empty
                                    const v = (val && val !== '0') ? String(val) : '';
                                    return `
                            <select class="assign-select w-full"
                                    data-src-id="${row.src_id}"
                                    data-doc-type="${row.doc_type}"
                                    data-original="${v}">
                                <option value=""></option>   <!-- placeholder KOSONG yang eksplisit -->
                                ${v ? `<option value="${v}" selected>${v}</option>` : ''}
                            </select>`;
                                }
                            },

                            // 2) doc_date -> tampilkan hanya tanggal dd/mm/yyyy (tanpa jam)
                            {
                                data: 'doc_date',
                                className: 'text-center',
                                render: function(data) {
                                    if (!data) return '';
                                    const d = new Date(data);
                                    if (isNaN(d)) return data; // fallback
                                    return d.toLocaleDateString('id-ID'); // contoh: 02/09/2025
                                }
                            },

                            {
                                data: 'cpny_id',
                                className: 'text-center w-32'
                            },
                            {
                                data: 'created_by_name',
                                className: 'text-center'
                            },
                            {
                                data: 'department_id',
                                className: 'text-center whitespace-normal break-words'
                            },
                            {
                                data: 'keperluan',
                                className: 'text-left'
                            },
                        ],
                        searchDelay: 400,
                        stateSave: true,
                        responsive: true,

                        // Inisialisasi Select2 setiap redraw
                        drawCallback: function() {
                            $('#canvassTable .assign-select').each(function() {
                                const $sel = $(this);
                                if ($sel.hasClass('select2-hidden-accessible')) return; // sudah init

                                $sel.select2({
                                        placeholder: '— pilih purchaser —',
                                        allowClear: true,
                                        width: 'resolve',
                                        minimumInputLength: 0,
                                        ajax: {
                                            url: "{{ route('assignlist.users') }}",
                                            dataType: 'json',
                                            delay: 250,
                                            data: params => ({
                                                q: params.term || ''
                                            }),
                                            processResults: data => ({
                                                results: data.results || []
                                            }), // <-- penting
                                            cache: true
                                        }

                                    })
                                    .on('select2:select', function(e) {
                                        const d = e.params.data;
                                        console.log('selected:',
                                            d); // {id: 'username', text: 'Nama'}
                                    })
                                    .on('select2:clear', function() {
                                        $(this).val('').trigger('change');
                                        console.log('current username value:', $(this).val());
                                    });


                            });
                        }
                    });

                    // Filter doc_type via kartu
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        docTypeFilter = $(this).data('doc') || '';
                        table.ajax.reload(null, true);
                    });

                    // Klik Assign Purchasing (bulk)
                    $('#btnAssignPurchasing').on('click', function() {
                        const items = [];
                        $('#canvassTable .assign-select').each(function() {
                            const $s = $(this);
                            const val = $s.val() || '';
                            const orig = $s.data('original') || '';
                            if (val && val !== '0' && val !== orig) { // <-- tambahkan val !== '0'
                                items.push({
                                    doc_type: $s.data('doc-type'),
                                    src_id: parseInt($s.data('src-id'), 10),
                                    assignpurchasing: val // ini = username (id Select2)
                                });
                            }
                        });

                        console.log('items to assign:', items); // <-- cek di DevTools

                        if (!items.length) {
                            toastr.info('Tidak ada perubahan untuk di-assign.');
                            return;
                        }

                        const $btn = $('#btnAssignPurchasing').prop('disabled', true).text('Assigning...');
                        $.post("{{ route('assignlist.assign') }}", {
                                items
                            })
                            .done(res => {
                                toastr.success(res.message || 'Assign Purchasing updated.');
                                // reload & reset → baris yang sudah di-assign langsung hilang (karena difilter unassigned)
                                table.ajax.reload(null, true);
                            })
                            .fail(xhr => {
                                const msg = xhr.responseJSON?.message || 'Gagal assign. Cek input Anda.';
                                toastr.error(msg);
                            })
                            .always(() => $btn.prop('disabled', false).text('Assign Purchasing'));
                    });

                });
                // Toggle .active class and remember selected document
                const filters = document.querySelectorAll('.status-filter');
                const savedDoc = localStorage.getItem('activeDoc');

                if (savedDoc) {
                    const activeFilter = document.querySelector(`.status-filter[data-doc="${savedDoc}"]`);
                    if (activeFilter) activeFilter.classList.add('active');
                }

                filters.forEach(btn => {
                    btn.addEventListener('click', e => {
                        e.preventDefault();
                        filters.forEach(b => b.classList.remove('active'));
                        btn.classList.add('active');
                        localStorage.setItem('activeDoc', btn.dataset.doc);
                    });
                });
            </script>




        </div>
    </div>
</x-app-layout>
