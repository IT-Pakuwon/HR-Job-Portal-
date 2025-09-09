<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'sppjs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid-col-1 grid gap-6 xl:grid-cols-5 xl:grid-rows-1">
            {{-- All Status --}}
            <button>
                <a href="#" class="status-filter" data-status="">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                        <span class="text-xl">📄</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- On Progress Status --}}
            <button>
                <a href="#" class="status-filter" data-status="P">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                        <span class="text-xl">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">On Progress</p>
                            <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Reject Status --}}
            <button>
                <a href="#" class="status-filter" data-status="R">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600">
                        <span class="text-xl">⛔️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Reject</p>
                            <p class="text-right text-xl font-extrabold">{{ $reject }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Revise / Draft Status --}}
            <button>
                <a href="#" class="status-filter" data-status="D">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                        <span class="text-xl">✏️</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Revise</p>
                            <p class="text-right text-xl font-extrabold">{{ $revise }}</p>
                        </div>
                    </div>
                </a>
            </button>

            {{-- Completed Status --}}
            <button>
                <a href="#" class="status-filter" data-status="C">
                    <div
                        class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                        <span class="text-xl">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Completed</p>
                            <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
            </button>
        </div>
        <div class="grid">
            <style>
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

                /* Sppj Table Specific Styles */
                #sppjsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #sppjsTable_filter label {
                    margin-right: 2px;
                }

                #sppjsTable_filter input {
                    width: 200px;
                }

                #sppjsTable_wrapper {
                    width: 100%;
                }

                #sppjsTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #sppjsTable th,
                #sppjsTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #sppjsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #sppjsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                }

                #sppjsTable_length select option {
                    padding: 5px;
                }

                #sppjsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    /* This class is for all DataTables paginations */
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #sppjsTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 2;
                }

                #sppjsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #sppjsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #sppjsTable tbody tr:hover td {
                    /* color: black; */
                }

                #sppjsTable th:nth-child(1),
                #sppjsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #sppjsTable th:nth-child(4),
                #sppjsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                /* --- Custom Styles for RowGroup Collapse/Expand (Applied to sppjsTable) --- */
                /* Initially hide rows in collapsed groups */
                #sppjsTable tbody tr.collapsed-group-row {
                    display: none;
                }

                /* Style for group rows */
                #sppjsTable tr.group-row {
                    background-color: #e6e6e6;
                    /* Light gray background for group headers */
                    font-weight: bold;
                    cursor: pointer;
                    user-select: none;
                    /* Prevent text selection on click */
                    color: #333;
                    /* Darker text for group headers */
                }

                #sppjsTable tr.group-row:hover {
                    background-color: #d4d4d4;
                    /* Slightly darker on hover */
                }

                /* Icon styling */
                #sppjsTable tr.group-row .fas {
                    margin-right: 8px;
                    width: 16px;
                    /* Ensure consistent icon width */
                    text-align: center;
                }

                /* Adjust padding for group rows to look consistent with other cells */
                #sppjsTable tr.group-row td {
                    padding: 10px !important;
                    border-bottom: 1px solid #ddd;
                    /* Separator for groups */
                }

                /* Remove border from the first td in group row to match the colspan */
                #sppjsTable tr.group-row td:first-child {
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
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div
                    class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    {{-- Changed text-3xl to text-xl --}}
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Request SPPJ</h1>
                    <a href="{{ url('/createsppjs') }}"
                        class="inline-flex items-center rounded-xl bg-indigo-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

                <div class="overflow-x-auto p-6"> {{-- Padding applied here instead of outer container --}}
                    <table id="sppjsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    DocID
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Date
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Company
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Department
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Request Type
                                </th>
                                <th scope="col"
                                    class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Description
                                </th>
                                <th scope="col"
                                    class="w-32 px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                    Status
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800">
                            {{-- Table rows will be populated here by JavaScript/DataTables --}}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ================== TRACKING MODAL ================== -->
            <div id="trackingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
                <div
                    class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-2xl bg-white p-6 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                    <!-- Header -->
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
                            SPPJ Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                        </h3>
                        <button id="closeTracking"
                            class="text-2xl leading-none text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200">
                            &times;
                        </button>
                    </div>

                    <!-- Controls (opsional) -->
                    <div class="mb-3 flex items-center justify-end gap-2">
                        <button type="button" id="tlPrev"
                            class="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                            ‹ Prev
                        </button>
                        <button type="button" id="tlNext"
                            class="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                            Next ›
                        </button>
                    </div>

                    <!-- Timeline -->
                    <ul id="tlList"
                        class="-mx-4 flex snap-x snap-mandatory overflow-x-auto whitespace-nowrap px-4 py-6 pr-6">
                        <!-- items di-inject via JS -->
                    </ul>

                    <!-- Hide scrollbar -->
                    <style>
                        #tlList::-webkit-scrollbar {
                            display: none;
                        }

                        #tlList {
                            scrollbar-width: none;
                        }
                    </style>
                </div>
            </div>

            <script>
                // function renderTimeline(steps = []) {
                // const list = document.getElementById('tlList');
                // if (!list) return;

                // if (!Array.isArray(steps) || steps.length === 0) {
                //     list.innerHTML = `<p class="text-sm text-gray-500">No tracking history found.</p>`;
                //     return;
                // }

                // // Peta status -> warna Tailwind
                // const MAP = {
                //     C: { label: 'Completed', colorDot: 'bg-green-600',  colorBorder: 'border-green-600',  colorTitle: 'text-green-700'  },
                //     P: { label: 'Waiting approval / in progress', colorDot: 'bg-yellow-500', colorBorder: 'border-yellow-500', colorTitle: 'text-yellow-700' },
                //     R: { label: 'Rejected',  colorDot: 'bg-red-600',    colorBorder: 'border-red-600',    colorTitle: 'text-red-700'    },
                //     D: { label: 'Revise',    colorDot: 'bg-blue-600',   colorBorder: 'border-blue-600',   colorTitle: 'text-blue-700'   },
                //     _: { label: '',          colorDot: 'bg-gray-400',   colorBorder: 'border-gray-400',   colorTitle: 'text-gray-700'   },
                // };

                // list.innerHTML = steps.map((s, i) => {
                //     const st = String(s.status || '').toUpperCase();
                //     const C  = MAP[st] || MAP._;
                //     console.log({st, C});
                //     const title = (s.title && String(s.title).trim()) || 'SPPJ';

                //     // Subtitle prioritas: status_label -> subtitle -> gabungan waktu/by
                //     let subtitle = (s.status_label && String(s.status_label).trim())
                //                 || (s.subtitle && String(s.subtitle).trim())
                //                 || C.label;
                //     // console.log({subtitle});
                //     const when = (s.at && String(s.at).trim()) || '';
                //     const by   = (s.by && String(s.by).trim()) ? ` by ${s.by}` : '';
                //     if (!s.subtitle && (when || by)) {
                //     subtitle = subtitle ? `${subtitle} • ${when}${by}` : `${when}${by}`;
                //     }

                //     const isLast = i === steps.length - 1;
                //     const connector = !isLast
                //     ? 'after:absolute after:top-1/2 after:left-7 after:h-0.5 after:w-[calc(100%-1.75rem)] after:-translate-y-1/2 after:bg-gray-300 dark:after:bg-gray-600'
                //     : '';

                //     return `
    //     <li class="relative mr-12 flex shrink-0 snap-start pr-12 last:mr-0 last:pr-0 ${connector}">
    //         <div class="flex items-center">
    //         <div class="grid h-6 w-6 place-items-center rounded-full border-2 ${C.colorBorder} bg-white dark:bg-gray-800">
    //             <div class="h-2 w-2 rounded-full ${C.colorDot}"></div>
    //         </div>
    //         <div class="ml-3">
    //             <p class="text-sm font-semibold ${C.colorTitle}">${title}</p>
    //             <p class="text-xs text-gray-700 dark:text-gray-300">${subtitle || ''}</p>
    //         </div>
    //         </div>
    //     </li>
    //     `;
                // }).join('');
                // }
                function renderTimeline(steps = []) {
                    const list = document.getElementById('tlList');
                    if (!list) return;

                    if (!Array.isArray(steps) || steps.length === 0) {
                        list.innerHTML = `<p class="text-sm text-gray-500">No tracking history found.</p>`;
                        return;
                    }

                    const MAP = {
                        C: {
                            label: 'Completed',
                            colorDot: 'bg-green-600',
                            colorBorder: 'border-green-600',
                            colorTitle: 'text-green-700'
                        },
                        P: {
                            label: 'Waiting approval / in progress',
                            colorDot: 'bg-yellow-500',
                            colorBorder: 'border-yellow-500',
                            colorTitle: 'text-yellow-700'
                        },
                        R: {
                            label: 'Rejected',
                            colorDot: 'bg-red-600',
                            colorBorder: 'border-red-600',
                            colorTitle: 'text-red-700'
                        },
                        D: {
                            label: 'Revise',
                            colorDot: 'bg-blue-600',
                            colorBorder: 'border-blue-600',
                            colorTitle: 'text-blue-700'
                        },
                        _: {
                            label: '',
                            colorDot: 'bg-gray-400',
                            colorBorder: 'border-gray-400',
                            colorTitle: 'text-gray-700'
                        },
                    };

                    list.innerHTML = steps.map((s, i) => {
                        const st = String(s.status || '').toUpperCase();
                        const C = MAP[st] || MAP._;
                        const title = (s.title && String(s.title).trim()) || 'SPPJ';

                        const when = (s.at && String(s.at).trim()) || '';
                        const by = (s.by && String(s.by).trim()) || '';
                        const statusText = (s.status_label && String(s.status_label).trim()) || C.label;

                        // tampilkan jadi multi-line: status, nama, waktu
                        let detailHtml = '';
                        if (statusText) detailHtml += `<p class="text-xs text-gray-500">${statusText}</p>`;
                        if (by) detailHtml += `<p class="text-xs text-gray-500">${by}</p>`;
                        if (when) detailHtml += `<p class="text-xs text-gray-500">${when}</p>`;

                        const isLast = i === steps.length - 1;
                        const connector = !isLast ?
                            'after:absolute after:top-1/2 after:left-7 after:h-0.5 after:w-[calc(100%-1.75rem)] after:-translate-y-1/2 after:bg-gray-300 dark:after:bg-gray-600' :
                            '';

                        return `
                        <li class="relative mr-12 flex shrink-0 snap-start pr-12 last:mr-0 last:pr-0 ${connector}">
                            <div class="flex items-center">
                            <div class="grid h-6 w-6 place-items-center rounded-full border-2 ${C.colorBorder} bg-white dark:bg-gray-800">
                                <div class="h-2 w-2 rounded-full ${C.colorDot}"></div>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold ${C.colorTitle}">${title}</p>
                                ${detailHtml}
                            </div>
                            </div>
                        </li>
                        `;
                    }).join('');
                }
            </script>





            <script>
                // Scroll controls
                (function() {
                    const scroller = document.getElementById('tlList');
                    document.getElementById('tlPrev')?.addEventListener('click', () =>
                        scroller.scrollBy({
                            left: -300,
                            behavior: 'smooth'
                        })
                    );
                    document.getElementById('tlNext')?.addEventListener('click', () =>
                        scroller.scrollBy({
                            left: 300,
                            behavior: 'smooth'
                        })
                    );
                })();

                // Open/Close modal
                function openTrackingModal(docText) {
                    document.getElementById('trackDoc').textContent = docText ? `(${docText})` : '';
                    const modal = document.getElementById('trackingModal');
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }

                function closeTrackingModal() {
                    const modal = document.getElementById('trackingModal');
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
                document.getElementById('closeTracking').addEventListener('click', closeTrackingModal);
                document.getElementById('trackingModal').addEventListener('click', (e) => {
                    if (e.target.id === 'trackingModal') closeTrackingModal();
                });


                $(document).on('click', '.tracking-btn', function() {
                    const id = $(this).data('id');
                    const doc = $(this).data('doc') || '';

                    // Tampilkan modal dulu
                    openTrackingModal(doc);

                    $.ajax({
                        url: `/sppjs/${id}/tracking`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(res) {
                            // langsung pakai struktur dari controller
                            renderTimeline(res.steps || []);
                        },
                        error: function() {
                            // fallback demo
                            renderTimeline([{
                                    key: 'submitted',
                                    title: 'SPPJ',
                                    status: 'C',
                                    status_label: 'Submitted',
                                    by: 'Williem Halim',
                                    at: '2025-08-10 09:00'
                                },
                                {
                                    key: 'approval',
                                    title: 'Approval',
                                    status: 'P',
                                    status_label: 'Waiting approval / in progress',
                                    by: null,
                                    at: null
                                },
                            ]);
                        }
                    });
                });
            </script>




            <script>
                var currentUser = "{{ auth()->user()->username }}";
                $(document).ready(function() {
                    // simpan status filter global
                    let statusFilter = 'P'; // default

                    const table = $('#sppjsTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        // ==== SCROLLER OPSIONAL (butuh plugin DataTables Scroller) ====
                        // scrollY: '60vh',
                        // scroller: true,

                        pageLength: 25,
                        lengthMenu: [10, 25, 50, 100, 250],

                        ajax: {
                            url: "{{ route('sppjs.json') }}",
                            type: "GET",
                            data: function(d) {
                                d.status = statusFilter ?? ''; // kirim status ke server
                            }
                        },

                        order: [
                            [0, 'desc']
                        ], // Date desc, lalu DocID desc

                        columns: [
                            // DocID (button link)
                            {
                                data: 'sppjid',
                                render: function(data, type, row) {
                                    let url = `/showsppjs/${row.id}`;
                                    let cls =
                                        'shrink-0 px-3 py-1.5 bg-indigo-500 text-white rounded hover:bg-indigo-700 text-sm';
                                    const text = data || row.id;

                                    // jika status Draft & milik current user → ke halaman edit
                                    if (row.status === 'D' && row.created_by === currentUser) {
                                        url = `/editsppjs/${row.id}`;
                                        cls =
                                            'shrink-0 px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-700 text-sm';
                                    }

                                    return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <a href="${url}" class="${cls}">${text}</a>
                                        <button type="button"
                                        class="tracking-btn inline-flex items-center justify-center rounded-full p-2
                                                text-red-600 hover:text-red-700 hover:bg-red-50"
                                        data-id="${row.id}" aria-label="Tracking" title="Tracking">
                                        <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                    `;
                                }
                            },

                            {
                                data: 'sppjdate',
                                className: 'text-center'
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
                                data: 'requesttype_name',
                                defaultContent: '-',
                                className: 'text-center'
                            },
                            {
                                data: 'keperluan'
                            },

                            {
                                data: 'status',
                                className: 'text-center',
                                render: function(data) {
                                    const map = {
                                        'D': {
                                            t: 'Revise',
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
                                    return `<span class="w-32 inline-block ${it.c} font-semibold px-4 py-2 text-center rounded">${it.t}</span>`;
                                }
                            }
                        ],

                        // Tweak untuk kinerja
                        searchDelay: 400, // debounce search
                        stateSave: true, // simpan state tabel (opsional)
                        responsive: true
                    });

                    // Ganti status filter → reload data tanpa rebuild tabel
                    $('.status-filter').on('click', function(e) {
                        e.preventDefault();
                        statusFilter = $(this).data('status') || '';
                        table.ajax.reload(null, true); // reset ke page 1
                    });
                });
            </script>


        </div>
    </div>
</x-app-layout>
