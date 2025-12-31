<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'spbs' ? 'HR' : '';
    @endphp
    <style>
        /* Active / Selected state */
        .status-filter.active .status-card {
            transform: scale(1.02);
        }

        .status-filter[data-status=""].active .status-card {
            background-color: rgb(254 215 170);
            /* orange-200 */
            border-color: rgb(194 65 12)
        }

        .status-filter[data-status="P"].active .status-card {
            background-color: rgb(191 219 254);
            /* blue-200 */
            border-color: rgb(29 78 216);
            /* blue-700 */
        }

        .status-filter[data-status="R"].active .status-card {
            background-color: rgb(254 202 202);
            /* red-200 */
            border-color: rgb(185 28 28);
            /* red-700 */
        }

        .status-filter[data-status="D"].active .status-card {
            background-color: rgb(229 231 235);
            /* gray-200 */
            border-color: rgb(31 41 55);
            /* gray-700 */
        }

        .status-filter[data-status="C"].active .status-card {
            background-color: rgb(187 247 208);
            /* green-200 */
            border-color: rgb(21 128 61);
            /* green-700 */
        }

        .status-filter[data-status="TRACK"].active .status-card {
            background-color: rgb(233 213 255); /* purple-200 */
            border-color: rgb(126 34 206);      /* purple-700 */
        }

    </style>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">

            {{-- All --}}
            <button type="button" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-2 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                    <!-- ICON -->
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">📄</div>

                    <!-- TEXT WRAP -->
                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All</p>
                    </div>

                    <!-- VALUE -->
                    <p class="shrink-0 text-lg font-bold">{{ $all }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-2 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-lg font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Reject --}}
            <button type="button" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-2 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Reject</p>
                    </div>

                    <p class="shrink-0 text-lg font-bold">{{ $reject }}</p>
                </div>
            </button>

            {{-- Revise / Draft --}}
            <button type="button" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-2 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-lg active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Revise / Draft</p>
                    </div>

                    <p class="shrink-0 text-lg font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-2 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-lg font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- SPB Tracking --}}
            <button type="button" class="status-filter group block h-full" data-status="TRACK">
                <div
                    class="status-card flex h-full items-center gap-2 rounded-lg border border-purple-700 bg-purple-200/20 p-2 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-lg">🧭</div>
                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">SPB Tracking</p>
                    </div>
                    <p class="shrink-0 text-lg font-bold">{{ $tracking }}</p>
                </div>
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

                /* Spb Table Specific Styles */
                #spbsTable_filter {
                    margin-bottom: 20px;
                    display: flex;
                    justify-content: flex-start;
                    align-items: center;
                }

                #spbsTable_filter label {
                    margin-right: 2px;
                }

                #spbsTable_filter input {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #spbsTable_wrapper {
                    width: 100%;
                }

                #spbsTable td {
                    white-space: nowrap;
                    overflow: hidden;
                    text-overflow: ellipsis;
                }

                #spbsTable th,
                #spbsTable td {
                    padding: 10px;
                    max-width: 200px;
                }

                #spbsTable_length {
                    width: auto;
                    display: flex;
                    justify-content: flex-start;
                }

                #spbsTable_length select {
                    width: auto;
                    padding: 5px;
                    min-width: 80px;
                    padding: 0.25rem 0.5rem;
                    border-radius: 0.5rem;
                    border: 1px solid #d1d5db;
                    background-color: #f9fafb;
                }

                #spbsTable_length select option {
                    padding: 5px;
                }

                #spbsTable_info {
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                .dataTables_paginate {
                    /* This class is for all DataTables paginations */
                    margin-top: 10px;
                    margin-bottom: 10px;
                }

                #spbsTable tbody tr td {
                    padding: 8px 8px;
                    line-height: 2;
                }

                #spbsTable tbody tr {
                    transition: background-color 0.3s ease, color 0.3s ease;
                }

                #spbsTable tbody tr:hover {
                    background-color: #8f8f8f11;
                    opacity: 100%;
                    cursor: pointer;
                }

                #spbsTable tbody tr:hover td {
                    /* color: black; */
                }

                #spbsTable th:nth-child(1),
                #spbsTable td:nth-child(1) {
                    width: 120px;
                    text-align: center;
                }

                #spbsTable th:nth-child(4),
                #spbsTable td:nth-child(4) {
                    width: 120px;
                    text-align: center;
                }

                /* --- Custom Styles for RowGroup Collapse/Expand (Applied to spbsTable) --- */
                /* Initially hide rows in collapsed groups */
                #spbsTable tbody tr.collapsed-group-row {
                    display: none;
                }

                /* Style for group rows */
                #spbsTable tr.group-row {
                    background-color: #e6e6e6;
                    /* Light gray background for group headers */
                    font-weight: bold;
                    cursor: pointer;
                    user-select: none;
                    /* Prevent text selection on click */
                    color: #333;
                    /* Darker text for group headers */
                }

                #spbsTable tr.group-row:hover {
                    background-color: #d4d4d4;
                    /* Slightly darker on hover */
                }

                /* Icon styling */
                #spbsTable tr.group-row .fas {
                    margin-right: 8px;
                    width: 16px;
                    /* Ensure consistent icon width */
                    text-align: center;
                }

                /* Adjust padding for group rows to look consistent with other cells */
                #spbsTable tr.group-row td {
                    padding: 10px !important;
                    border-bottom: 1px solid #ddd;
                    /* Separator for groups */
                }

                /* Remove border from the first td in group row to match the colspan */
                #spbsTable tr.group-row td:first-child {
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
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Request SPB</h1>
                    <a href="{{ url('/createspbs') }}"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-6 py-2 text-base font-semibold text-white transition-colors duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>

                <div class="overflow-x-auto p-6">
                   
                    <table id="spbsTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead id="spbsHead" class="bg-gray-50 dark:bg-gray-700"></thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
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
                            SPB Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
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
                        const title = (s.title && String(s.title).trim()) || 'SPB';

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
                        url: `/spbs/${id}/tracking`,
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
                                    title: 'SPB',
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
        (function(){
        const currentUser = "{{ auth()->user()->username }}";
        let statusFilter = 'P';
        let mode = 'NORMAL'; // NORMAL | TRACK
        let dt = null;

        // guard init sekali
        if (window.__SPB_DT_DEBUG_INIT__) return;
        window.__SPB_DT_DEBUG_INIT__ = true;

        function headNormal(){
            return `
            <tr>
                <th>DocID</th>
                <th>Date</th>
                <th>Company</th>
                <th>Department</th>
                <th>Work Type</th>
                <th>Sub Work Type</th>
                <th>Description</th>
                <th>Status</th>
            </tr>
            `;
        }
        function headTrack(){
            return `
            <tr>
                <th>Issue ID</th>
                <th>SPB ID</th>
                <th>SPPB ID</th>
                <th>Total SPB</th>
                <th>Total Issue</th>
                <th>Total Return</th>
                <th>Total SPPB</th>
                <th>Total Complete</th>
                <th>Status SPPB</th>
                <th>Status Issue</th>
            </tr>
            `;
        }

        function destroyDT(){
        if ($.fn.DataTable.isDataTable('#spbsTable')) {
            const api = $('#spbsTable').DataTable();
            api.off('xhr.dt');
            api.off('error.dt');
            api.clear().destroy(false); // ✅ JANGAN true
        }
        // kosongkan tbody saja
        $('#spbsTable tbody').empty();
        dt = null;
        }


        // ====== DEBUG HELPERS ======
        function attachDebug(name){
            if (!dt) return;

            // log response json & status code
            dt.on('xhr.dt', function(e, settings, json, xhr){
            const code = xhr ? xhr.status : '(no xhr)';
            console.log(`[%c${name}%c] xhr status:`, 'color:#7c3aed;font-weight:bold', 'color:inherit', code);
            console.log(`[%c${name}%c] response json:`, 'color:#7c3aed;font-weight:bold', 'color:inherit', json);

            // kalau JSON ada error key
            if (json && json.error) {
                console.error(`[%c${name}%c] json.error:`, 'color:#dc2626;font-weight:bold', 'color:inherit', json.error);
            }
            });

            // log datatables internal error
            dt.on('error.dt', function(e, settings, techNote, message){
            console.error(`[%c${name}%c] DataTables error:`, 'color:#dc2626;font-weight:bold', 'color:inherit', message);
            });

            // log ajax error detail
            dt.on('preXhr.dt', function(e, settings, data){
            // ini data yang akan dikirim dt ke server
            console.log(`[%c${name}%c] sending params:`, 'color:#2563eb;font-weight:bold', 'color:inherit', data);
            console.log(`[%c${name}%c] ajax url:`, 'color:#2563eb;font-weight:bold', 'color:inherit', settings.ajax?.url);
            });
        }


            function initNormal(){
                mode = 'NORMAL';

                dt = $('#spbsTable').DataTable({
                    processing:true, serverSide:true, deferRender:true,
                    pageLength:10, lengthMenu:[10,25,50,100,250],
                    ajax:{
                    url:"{{ route('spbs.json') }}",
                    type:"GET",
                    data:function(d){
                        d.status = statusFilter ?? '';
                        console.log('[NORMAL] send status =', d.status);
                    },
                    error: function(xhr){
                        console.error('[NORMAL] ajax error', xhr.status, xhr.responseText);
                    }
                    },
                    order:[[0,'desc']],
                    columns:[
                    {
                        data:'spbid',
                        render:function(data,type,row){
                        let url = `/showspbs/${row.eid}`;
                        let cls = 'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base font-semibold text-white rounded bg-gray-500 hover:bg-gray-700';
                        const text = data || row.id;

                        if(row.status === 'D' && row.created_by === currentUser){
                            url = `/editspbs/${row.eid}`;
                            cls = 'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-base font-semibold text-white rounded bg-yellow-500 hover:bg-yellow-700';
                        }

                        return `
                            <div class="flex items-center gap-2 whitespace-nowrap">
                            <a href="${url}" class="${cls}">${text}</a>
                            <button type="button"
                                class="tracking-btn inline-flex items-center justify-center rounded-full p-2 text-red-600 hover:text-red-700 hover:bg-red-50"
                                data-id="${row.eid}" data-doc="${text}" title="Tracking">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                            </div>
                        `;
                        }
                    },
                    {data:'spbdate'},
                    {data:'cpny_id'},
                    {data:'department_id'},
                    {data:'worktype_name', defaultContent:'-'},
                    {data:'subworktype_name', defaultContent:'-'},
                    {data:'keperluan'},
                    {
                        data: 'status',
                        className: 'text-left',
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
                            return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-base text-center rounded">${it.t}</span>`;
                        }
                    }
                    ],
                });

                attachDebug('NORMAL');
            }


            function initTrack(){
                mode = 'TRACK';
                $('#spbsHead').html(headTrack());

                dt = $('#spbsTable').DataTable({
                    processing:true,
                    serverSide:true,
                    deferRender:true,
                    pageLength:10,
                    lengthMenu:[10,25,50,100,250],
                    ajax:{
                    url:"{{ route('spbs.trackJson') }}",
                    type:"GET",
                    error: function(xhr){
                        console.error('[TRACK] ajax error', xhr.status, xhr.responseText);
                    }
                    },
                    order:[[0,'desc']],
                    columns:[
                    // Issue ID button
                    {
                        data:'issueid',
                        render:function(data,type,row){
                        const text = data || '';
                        const eid  = row.eid_issue || '';
                        const url  = `/showissue/${eid}`; // 🔥 ganti sesuai route issue kamu
                        return `
                            <a href="${url}"
                            class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-base font-semibold text-white rounded bg-amber-600 hover:bg-amber-700">
                            ${text}
                            </a>`;
                        }
                    },

                    // SPB ID button (ke showspbs seperti normal)
                    {
                        data:'spbid',
                        render:function(data,type,row){
                        const text = data || '';
                        const eid  = row.eid_spb || '';
                        const url  = `/showspbs/${eid}`;
                        return `
                            <a href="${url}"
                            class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-base font-semibold text-white rounded bg-indigo-600 hover:bg-indigo-700">
                            ${text}
                            </a>`;
                        }
                    },

                    // SPPB ID button
                    {
                        data:'sppbid',
                        defaultContent:'',
                        render:function(data,type,row){
                        const text = data || '';
                        const eid  = row.eid_sppb || '';
                        if(!text || !eid) return '';
                        const url = `/showsppbs/${eid}`; // 🔥 ganti sesuai route sppb kamu
                        return `
                            <a href="${url}"
                            class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-base font-semibold text-white rounded bg-emerald-600 hover:bg-emerald-700">
                            ${text}
                            </a>`;
                        }
                    },

                    {data:'totalspbqty', defaultContent:0},
                    {data:'totalissueqty', defaultContent:0},
                    {data:'totalreturnqty', defaultContent:0},
                    {data:'totalsppbqty', defaultContent:0},
                    {data:'totalcompleteqty', defaultContent:0},
                    {data:'status_sppb', defaultContent:''},
                    {data:'status_issue', defaultContent:''},
                    ],
                    responsive:false,
                    stateSave:false,
                    searchDelay:400
                });
                }


        function switchMode(next){
            console.log('[SWITCH MODE] =>', next);
            destroyDT();

            if(next === 'TRACK'){
                $('#spbsTable thead').html(headTrack());  // ✅ pakai selector ini
                initTrack();
            }else{
                $('#spbsTable thead').html(headNormal()); // ✅ pakai selector ini
                initNormal();
            }
            }




        $(document).ready(function(){
            // init default NORMAL
            switchMode('NORMAL');

            // active default
            document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
            document.querySelector(`.status-filter[data-status="P"]`)?.classList.add('active');

            $(document).on('click', '.status-filter', function(e){
            e.preventDefault();
            const st = $(this).data('status') || '';
            console.log('CLICK card status =', st);

            document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if(st === 'TRACK'){
                switchMode('TRACK');
                return;
            }

            statusFilter = st;
            if(mode !== 'NORMAL') switchMode('NORMAL');
            else if(dt) dt.ajax.reload(null, true);
            });
        });
        })();
    </script>





        </div>
    </div>
</x-app-layout>
