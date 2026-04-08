<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'spbs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $hasAllList = auth()->user()->hasRole('COSTCTRLACCESS');
        @endphp
        <div
            class="{{ $hasAllList ? 'xl:grid-cols-7' : 'xl:grid-cols-6' }} grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">

            {{-- All --}}
            <button type="button" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-2 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-lg active:scale-95">

                    <!-- ICON -->
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📄</div>

                    <!-- TEXT WRAP -->
                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All</p>
                    </div>

                    <!-- VALUE -->
                    <p class="shrink-0 text-sm font-bold">{{ $all }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-2 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-sm font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Reject --}}
            <button type="button" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-2 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Reject</p>
                    </div>

                    <p class="shrink-0 text-sm font-bold">{{ $reject }}</p>
                </div>
            </button>

            {{-- Revise / Draft --}}
            <button type="button" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-2 text-gray-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-lg active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Revise / Draft</p>
                    </div>

                    <p class="shrink-0 text-sm font-bold">{{ $revise }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-2 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-lg active:scale-95">

                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-sm font-bold">{{ $completed }}</p>
                </div>
            </button>

            {{-- SPB Tracking --}}
            <button type="button" class="status-filter group block h-full" data-status="TRACK">
                <div
                    class="status-card flex h-full items-center gap-2 rounded-lg border border-purple-700 bg-purple-200/20 p-2 text-purple-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-lg active:scale-95">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">🧭</div>
                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">SPB Tracking</p>
                    </div>
                    <p class="shrink-0 text-sm font-bold">{{ $tracking }}</p>
                </div>
            </button>

            {{-- SPB All List --}}
            @if (auth()->user()->hasRole('COSTCTRLACCESS'))
                <button type="button" class="text-left">
                    <a href="#" class="status-filter group block h-full" data-mode="all">
                        <div
                            class="status-card flex h-full items-center gap-3 rounded-lg border border-purple-700 bg-purple-200/20 p-3 text-purple-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-purple-100 hover:shadow-md active:scale-95">

                            <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">📊</div>

                            <div class="flex min-w-0 flex-grow flex-col leading-tight">
                                <p class="break-words text-sm font-medium">SPB All List</p>
                            </div>
                            <p class="shrink-0 text-base font-bold">
                                {{ $allListCount }}
                            </p>

                        </div>
                    </a>
                </button>
            @endif



        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-center justify-between gap-4 sm:flex-row sm:items-center">
                <h1 id="pageTitle" class="text-base font-extrabold text-gray-700 dark:text-white">
                    Request SPB
                </h1>

                <div class="flex items-center gap-4">
                    {{-- FILTER SECTION (ONLY FOR ALL MODE) --}}
                    <div id="allFilters" class="flex hidden items-center gap-2">

                        {{-- Status Filter --}}
                        <select id="filterStatus"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Status</option>
                            <option value="P">On Progress</option>
                            <option value="C">Completed</option>
                        </select>

                        {{-- Department Filter --}}
                        <select id="filterDepartment"
                            class="rounded-md border px-3 py-1 text-sm dark:border-gray-700 dark:bg-gray-800">
                            <option value="">All Department</option>
                        </select>

                    </div>
                    <a id="createBtn" href="{{ url('/createspbs') }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                        <i class="fas fa-plus pr-2"></i>Create
                    </a>
                </div>


            </div>

            <div class="rounded-base relative overflow-x-auto">

                <table id="spbsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead id="spbsHead" class="bg-gray-50 dark:bg-gray-700"></thead>
                    <tbody>
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>

            </div>

        </div>

        <!-- ================== TRACKING MODAL ================== -->
        <div id="trackingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div
                class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-xl bg-white p-4 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                <!-- Header -->
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-[12px] font-semibold text-gray-800 dark:text-white">
                        SPB Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                    </h3>
                    <button id="closeTracking"
                        class="text-lg leading-none text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200">
                        &times;
                    </button>
                </div>

                {{-- <!-- Controls (opsional) -->
                <div class="mb-3 flex items-center justify-end gap-2">
                    <button type="button" id="tlPrev"
                        class="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                        ‹ Prev
                    </button>
                    <button type="button" id="tlNext"
                        class="rounded-lg border px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                        Next ›
                    </button>
                </div> --}}

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

        <!-- ================== SPPB TRACKING MODAL ================== -->
        <div id="trackingModalSppb" class="fixed inset-0 z-50 hidden bg-black/50">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div
                    class="max-h-[90vh] w-full max-w-7xl overflow-hidden rounded-xl bg-white shadow-xl dark:bg-gray-800">

                    <div class="flex items-center justify-between border-b px-4 py-3">
                        <h3 class="text-sm font-semibold">
                            Tracking Detail <span id="trackDocSppb" class="font-bold text-indigo-600"></span>
                        </h3>
                        <button type="button" id="closeTrackingSppb"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white">
                            ✕
                        </button>
                    </div>

                    <div class="border-b border-gray-200 px-4 dark:border-gray-700">
                        <div class="flex gap-2 overflow-x-auto py-2" id="trackTabsSppb">
                            <button class="track-tab-sppb active" data-tab="tab-sppb-sppb">SPPB</button>
                            <button class="track-tab-sppb" data-tab="tab-cs-sppb">CS</button>
                            <button class="track-tab-sppb" data-tab="tab-po-sppb">PO</button>
                            <button class="track-tab-sppb" data-tab="tab-receipt-sppb">Receipt</button>
                        </div>
                    </div>

                    <div class="max-h-[calc(90vh-110px)] overflow-y-auto p-4">
                        <div id="tlLoadingSppb"
                            class="hidden items-center gap-2 text-sm text-gray-500 dark:text-gray-300">
                            <span
                                class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-gray-300 border-t-transparent"></span>
                            Loading...
                        </div>

                        <div id="tab-sppb-sppb" class="track-pane-sppb">
                            <div id="sppbHeaderBoxSppb"></div>
                            <div class="mt-3" id="sppbDetailBoxSppb"></div>
                        </div>

                        <div id="tab-cs-sppb" class="track-pane-sppb hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select CS</label>
                                <select id="selCsSppb"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="csHeaderBoxSppb"></div>
                            <div class="mt-3" id="csDetailBoxSppb"></div>
                        </div>

                        <div id="tab-po-sppb" class="track-pane-sppb hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select PO</label>
                                <select id="selPoSppb"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="poHeaderBoxSppb"></div>
                            <div class="mt-3" id="poDetailBoxSppb"></div>
                        </div>

                        <div id="tab-receipt-sppb" class="track-pane-sppb hidden">
                            <div class="mb-2">
                                <label class="text-xs text-gray-500">Select Receipt</label>
                                <select id="selReceiptSppb"
                                    class="w-full rounded-lg border px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800"></select>
                            </div>
                            <div id="receiptHeaderBoxSppb"></div>
                            <div class="mt-3" id="receiptDetailBoxSppb"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const dtControlColumn = {
            data: null,
            className: 'dtr-control',
            orderable: false,
            searchable: false,
            defaultContent: ''
        };

        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                list.innerHTML = `<p class="text-sm text-gray-500">No tracking history found.</p>`;
                return;
            }

            list.className = "px-2 py-3";

            list.innerHTML = `
                <div class="rounded-xl border border-gray-200 bg-white p-4">

                    <!-- HEADER -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm font-semibold text-gray-700">
                            Approval Tracking
                        </div>
                        <div class="text-xs text-gray-500">
                            ${steps[steps.length - 1]?.status_label || ''}
                        </div>
                    </div>

                    <!-- SCROLL AREA -->
                    <div class="space-y-1 max-h-[400px] overflow-y-auto pr-2">

                        ${steps.map((s) => {

                            // ======================
                            // CYCLE DIVIDER
                            // ======================
                            if (s.type === 'cycle') {
                                return `
                                <div class="flex items-center gap-2 my-3">
                                    <div class="flex-1 h-px bg-gray-200"></div>
                                    <div class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">
                                        ${s.title}
                                    </div>
                                    <div class="flex-1 h-px bg-gray-200"></div>
                                </div>
                                `;
                            }

                            // ======================
                            // STATUS FROM BACKEND (NO OVERRIDE)
                            // ======================
                            const st = String(s.status || '').toUpperCase();

                            let badgeClass = '';
                            let dot = '';

                            if (st === 'C') {
                                badgeClass = 'text-green-600';
                                dot = 'bg-green-500';
                            } else if (st === 'P') {
                                badgeClass = 'text-blue-600';
                                dot = 'bg-blue-500';
                            } else if (st === 'R') {
                                badgeClass = 'text-red-600';
                                dot = 'bg-red-500';
                            } else if (st === 'D') {
                                badgeClass = 'text-yellow-600';
                                dot = 'bg-yellow-500';
                            } else if (st === 'X') {
                                badgeClass = 'text-gray-600';
                                dot = 'bg-gray-500';
                            }
                            else {
                                badgeClass = 'text-gray-400';
                                dot = 'bg-gray-300';
                            }

                            const badge = s.status_label || '-';

                            // ======================
                            // AVATAR
                            // ======================
                            const name = s.by || '-';
                            const initials = name !== '-'
                                ? name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase()
                                : '?';

                            return `
                            <div class="flex items-center justify-between py-3">

                                <div class="flex items-center gap-3">

                                    <div class="h-2 w-2 rounded-full ${dot}"></div>

                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600">
                                        ${initials}
                                    </div>

                                    <div>
                                        <div class="text-sm font-medium text-gray-800">
                                            ${s.title}
                                        </div>
                                        <div class="text-xs text-gray-500">
                                            ${name}
                                        </div>
                                    </div>

                                </div>

                                <div class="text-right">
                                    <div class="text-xs font-medium ${badgeClass}">
                                        ${badge}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        ${s.at || '-'}
                                    </div>
                                </div>

                            </div>
                            `;
                        }).join('')}

                    </div>
                </div>
            `;
        }
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
                console.log('TRACKING RESPONSE:', res);

                if (!res || !Array.isArray(res.steps)) {
                    document.getElementById('tlList').innerHTML =
                        `<p class="text-sm text-red-500">Invalid tracking data</p>`;
                    return;
                }

                renderTimeline(res.steps);
            },
error: function(err) {
                console.error('Tracking API ERROR:', err);

                document.getElementById('tlList').innerHTML =
                    `<p class="text-sm text-red-500">Failed to load tracking data</p>`;
            }
            });
        });

        (function() {
            const currentUser = "{{ auth()->user()->username }}";
            let statusFilter = 'P';
            let mode = 'normal'; // NORMAL | TRACK
            let dt = null;
            let deptFilter = '';

            // const dtControlColumn = {
            //     data: null,
            //     width: '28px',
            // className: 'dtr-control',
            //     orderable: false,
            //     searchable: false,
            //     defaultContent: ''
            // };

            // guard init sekali
            if (window.__SPB_DT_DEBUG_INIT__) return;
            window.__SPB_DT_DEBUG_INIT__ = true;

            function headNormal() {
                return `
            <tr>
                <th></th>
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

            function headTrack() {
                return `
            <tr>
                <th></th>
                <th>SPB ID</th>
                <th>SPPB ID</th>
                <th>WO ID</th>
                <th>Issue ID</th>         // ✅ NEW
                <th>SPB Purpose</th>
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

            function destroyDT() {
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


            // // ====== DEBUG HELPERS ======
            // function attachDebug(name) {
            //     if (!dt) return;

            //     // log response json & status code
            //     dt.on('xhr.dt', function(e, settings, json, xhr) {
            //         const code = xhr ? xhr.status : '(no xhr)';
            //         console.log(`[%c${name}%c] xhr status:`, 'color:#7c3aed;font-weight:bold', 'color:inherit',
            //             code);
            //         console.log(`[%c${name}%c] response json:`, 'color:#7c3aed;font-weight:bold',
            //             'color:inherit', json);

            //         // kalau JSON ada error key
            //         if (json && json.error) {
            //             console.error(`[%c${name}%c] json.error:`, 'color:#dc2626;font-weight:bold',
            //                 'color:inherit', json.error);
            //         }
            //     });

            //     // log datatables internal error
            //     dt.on('error.dt', function(e, settings, techNote, message) {
            //         console.error(`[%c${name}%c] DataTables error:`, 'color:#dc2626;font-weight:bold',
            //             'color:inherit', message);
            //     });

            //     // log ajax error detail
            //     dt.on('preXhr.dt', function(e, settings, data) {
            //         // ini data yang akan dikirim dt ke server
            //         console.log(`[%c${name}%c] sending params:`, 'color:#2563eb;font-weight:bold',
            //             'color:inherit', data);
            //         console.log(`[%c${name}%c] ajax url:`, 'color:#2563eb;font-weight:bold', 'color:inherit',
            //             settings.ajax?.url);
            //     });
            // }


            function initNormal() {
                mode = 'normal';

                dt = $('#spbsTable').DataTable({
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
                            title: 'List_SPB',
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
                            title: 'List_SPB',
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
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    ajax: {
                        url: "{{ route('spbs.json') }}",
                        type: "GET",
                        data: function(d) {
                            d.status = statusFilter ?? '';

                            d.mode = mode; // ✅ REQUIRED
                            d.department_extra = deptFilter; // ✅ REQUIRED
                            console.log('[normal] send status =', d.status);
                        },
                        error: function(xhr) {
                            console.error('[normal] ajax error', xhr.status, xhr.responseText);
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [dtControlColumn,
                        {
                            data: 'spbid',
                            render: function(data, type, row) {
                                // default: view
                                let url = `/showspbs/${row.eid}`;
                                let cls =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm font-semibold text-white rounded  bg-gray-600 hover:bg-gray-700 ';

                                const text = data || row.id;

                                const isDraftOwner = (row.status === 'D' && row.created_by ===
                                    currentUser);

                                // icon view (mata)
                                const viewBtn = `
                                    <a href="/showspbs/${row.eid}" target="_blank"
                                    class="inline-flex items-center justify-center rounded-full p-2
                                            text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50"
                                    aria-label="View" title="View">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                `;

                                // Draft & owner → Edit
                                if (isDraftOwner) {
                                    url = `/editspbs/${row.eid}`;
                                    cls =
                                        'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-yellow-500 hover:bg-yellow-700';
                                }

                                return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <a href="${url}" class="${cls}">${text}</a>

                                        ${isDraftOwner ? viewBtn : ''}

                                        <button type="button"
                                            class="tracking-btn inline-flex items-center justify-center rounded-full p-2
                                                text-red-600 hover:text-red-700 hover:bg-red-50"
                                            data-id="${row.eid}" data-doc="${text}" title="Tracking">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                `;
                            }

                        },
                        {
                            data: 'spbdate'
                        },
                        {
                            data: 'cpny_id'
                        },
                        {
                            data: 'department_id'
                        },
                        {
                            data: 'worktype_name',
                            defaultContent: '-'
                        },
                        {
                            data: 'subworktype_name',
                            defaultContent: '-'
                        },
                        {
                            data: 'keperluan'
                        },
                        {
                            data: 'status',
                            className: 'text-left',
                            render: function(data) {
                                const map = {
                                    'D': {
                                        t: 'Revise',
                                        c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                    },
                                    'P': {
                                        t: 'On Progress',
                                        c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                    },
                                    'C': {
                                        t: 'Completed',
                                        c: 'bg-green-200/60 text-green-800 border border-green-600/40'
                                    },
                                    'X': {
                                        t: 'Cancel',
                                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                    },
                                    'R': {
                                        t: 'Rejected',
                                        c: 'bg-red-200/60 text-red-800 border border-red-600/40'
                                    },
                                };
                                const it = map[data] || {
                                    t: data || '-',
                                    c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40'
                                };
                                return `<span class="w-32 inline-block ${it.c} font-semibold px-3 py-1.5 text-sm text-center rounded">${it.t}</span>`;
                            }
                        }
                    ],
                });

                // attachDebug('NORMAL');
            }

            function initTrack() {
                mode = 'TRACK';
                $('#spbsHead').html(headTrack());

                dt = $('#spbsTable').DataTable({
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
                            title: 'List_SPB',
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
                            title: 'List_SPB',
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
                        width: '28px',
                        className: 'dtr-control',
                        orderable: false
                    }],
                    ajax: {
                        url: "{{ route('spbs.trackJson') }}",
                        type: "GET",
                        error: function(xhr) {
                            console.error('[TRACK] ajax error', xhr.status, xhr.responseText);
                        }
                    },
                    order: [
                        [0, 'desc']
                    ],
                    columns: [
                        dtControlColumn,
                        // Issue ID button


                        // SPB ID button (ke showspbs seperti normal)
                        {
                            data: 'spbid',
                            render: function(data, type, row) {
                                const text = data || '';
                                const eid = row.eid_spb || '';
                                const url = `/showspbs/${eid}`;
                                return `
                            <a href="${url}"
                            class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-indigo-600 hover:bg-indigo-700">
                            ${text}
                            </a>`;
                            }
                        },

                        // SPPB ID button
                        {
                            data: 'sppbid',
                            render: function(data, type, row) {

                                if (!data || !row.eid_sppb) {
                                    return `<span class="text-gray-400 italic">No SPPB</span>`;
                                }

                                return `
            <div class="flex gap-2 items-center">

                <a href="/showsppbs/${row.eid_sppb}"
                    class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-green-600 hover:bg-green-700">
                   ${data}
                </a>

                <button
                    class="tracking-btn-sppb inline-flex items-center justify-center rounded-full p-2
                                            text-red-600 hover:text-red-700 hover:bg-red-50"
                    data-id="${row.eid_sppb}"
                    data-doc="${data}"aria-label="Tracking" title="Tracking">
                                            <i class="fa-solid fa-paper-plane"></i>
                </button>

            </div>
        `;
                            }
                        },
                        // WO ID
                        {
                            data: 'woid',
                            render: function(data, type, row) {
                                if (!data || !row.eid_wo) {
                                    return `
                <span class="text-gray-400 italic">No WO</span>
            `;
                                }

                                const url = `/showwos/${row.eid_wo}`;

                                return `
            <a href="${url}" target="_blank"
               class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-purple-600 hover:bg-purple-700">
               ${data}
            </a>
        `;
                            }
                        },
                        {
                            data: 'issueid',
                            render: function(data, type, row) {
                                if (!data) {
                                    return `
                <span class="text-gray-400 italic">Not Issue Yet</span>
            `;
                                }

                                const url = `/showissue/${row.eid_issue}`;

                                return `
            <a href="${url}"
               class="inline-flex justify-center items-center w-[140px] px-3 py-1.5 text-sm font-semibold text-white rounded bg-amber-600 hover:bg-amber-700">
               ${data}
            </a>
        `;
                            }
                        },
                        // Purpose
                        {
                            data: 'keperluan',
                            render: function(data) {
                                if (!data) return '-';

                                return `
            <div class="max-w-[220px] truncate cursor-pointer"
                 title="${data}">
                ${data}
            </div>
        `;
                            }
                        },

                        {
                            data: 'totalspbqty',
                            defaultContent: 0
                        },
                        {
                            data: 'totalissueqty',
                            defaultContent: 0
                        },
                        {
                            data: 'totalreturnqty',
                            defaultContent: 0
                        },
                        {
                            data: 'totalsppbqty',
                            defaultContent: 0
                        },
                        {
                            data: 'totalcompleteqty',
                            defaultContent: 0
                        },
                        {
                            data: 'status_sppb',
                            defaultContent: ''
                        },
                        {
                            data: 'status_issue',
                            defaultContent: ''
                        },
                    ],
                    stateSave: false,
                    searchDelay: 400
                });
            }


            function switchMode(next) {
                // console.log('[SWITCH MODE] =>', next);
                destroyDT();

                if (next === 'TRACK') {
                    $('#spbsTable thead').html(headTrack()); // ✅ pakai selector ini
                    initTrack();
                } else {
                    $('#spbsTable thead').html(headNormal()); // ✅ pakai selector ini
                    initNormal();
                }
            }




            $(document).ready(function() {
                // init default NORMAL
                switchMode('normal');

                // active default
                document.querySelectorAll('.status-filter').forEach(b => b.classList.remove('active'));
                document.querySelector(`.status-filter[data-status="P"]`)?.classList.add('active');

                $(document).on('click', '.status-filter', function(e) {

                    e.preventDefault();

                    const st = $(this).data('status') || '';
                    const selectedMode = $(this).data('mode');

                    $('.status-filter').removeClass('active');
                    $(this).addClass('active');

                    // ====================
                    // ALL LIST
                    // ====================
                    if (selectedMode === 'all') {

                        mode = 'all';
                        statusFilter = '';
                        deptFilter = '';

                        $('#pageTitle').text('SPB All List');
                        $('#createBtn').hide();
                        $('#allFilters').removeClass('hidden');

                        if (dt) dt.ajax.reload(null, true);
                        return;
                    }

                    // ====================
                    // TRACK MODE
                    // ====================
                    if (st === 'TRACK') {
                        switchMode('TRACK');
                        return;
                    }

                    // ====================
                    // NORMAL MODE
                    // ====================
                    mode = 'normal';
                    statusFilter = st;

                    $('#pageTitle').text('Request SPB');
                    $('#createBtn').show();
                    $('#allFilters').addClass('hidden');

                    if (dt) dt.ajax.reload(null, true);

                    switchMode('normal');

                });
            });
            $('#filterStatus').on('change', function() {
                statusFilter = this.value;
                if (dt) dt.ajax.reload();
            });

            $('#filterDepartment').on('change', function() {
                deptFilter = this.value;
                if (dt) dt.ajax.reload();
            });
        })();

        function fmt2(val) {
            if (val === null || val === undefined || val === '') return '0.00';
            const num = Number(val);
            if (isNaN(num)) return '0.00';
            return num.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        (function() {
            // ---------- Modal open/close ----------
            function openTrackingModalSppb(docText) {
                document.getElementById('trackDocSppb').textContent = docText ? `(${docText})` : '';
                const modal = document.getElementById('trackingModalSppb');
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeTrackingModal() {
                document.getElementById('trackingModalSppb')?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            document.getElementById('closeTrackingSppb')?.addEventListener('click', closeTrackingModal);
            document.getElementById('trackingModalSppb')?.addEventListener('click', (e) => {
                if (e.target.id === 'trackingModalSppb') closeTrackingModal();
            });

            // ---------- Tabs ----------
            (function() {
                const tabs = document.getElementById('trackTabsSppb');
                if (!tabs) return;

                tabs.addEventListener('click', (e) => {
                    const btn = e.target.closest('.track-tab-sppb');
                    if (!btn) return;

                    document.querySelectorAll('.track-tab-sppb').forEach(x => x.classList.remove('active'));
                    btn.classList.add('active');

                    const target = btn.dataset.tab;
                    document.querySelectorAll('.track-pane-sppb').forEach(p => p.classList.add('hidden'));
                    document.getElementById(target)?.classList.remove('hidden');
                });
            })();

            function resetToSppbTab() {
                document.querySelectorAll('.track-tab-sppb').forEach(x => x.classList.remove('active'));
                document.querySelector('.track-tab-sppb[data-tab="tab-sppb-sppb"]')?.classList.add('active');
                document.querySelectorAll('.track-pane-sppb').forEach(p => p.classList.add('hidden'));
                document.getElementById('tab-sppb-sppb')?.classList.remove('hidden');
            }

            // ---------- Utilities ----------
            function esc(s) {
                return String(s ?? '')
                    .replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;')
                    .replaceAll('"', '&quot;').replaceAll("'", '&#039;');
            }

            function setLoading(on) {
                const el = document.getElementById('tlLoadingSppb');
                if (!el) return;
                el.classList.toggle('hidden', !on);
                el.classList.toggle('flex', on);
            }

            function statusLabel(st) {
                st = String(st || '').toUpperCase();

                const map = {
                    'C': {
                        text: 'Completed',
                        cls: 'bg-green-100 text-green-700'
                    },
                    'P': {
                        text: 'On Progress',
                        cls: 'bg-yellow-100 text-yellow-700'
                    },
                    'R': {
                        text: 'Rejected',
                        cls: 'bg-red-100 text-red-700'
                    },
                    'D': {
                        text: 'Revise',
                        cls: 'bg-blue-100 text-blue-700'
                    }
                };

                const it = map[st] || {
                    text: st || '-',
                    cls: 'bg-gray-100 text-gray-700'
                };

                return `
                <span class="inline-block rounded px-2 py-0.5 text-xs font-semibold ${it.cls}">
                    ${it.text}
                </span>
            `;
            }

            function statusLabel2(st) {
                st = String(st || '').toUpperCase();
                switch (st) {
                    case 'P':
                        return 'On Progress';
                    case 'C':
                        return 'Completed';
                    case 'R':
                        return 'Rejected';
                    case 'D':
                        return 'Revise';
                    default:
                        return st || '-';
                }
            }

            function badgeApproved(isApproved) {
                if (isApproved) {
                    return `<span class="inline-block rounded bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">APPROVED</span>`;
                }
                return `<span class="inline-block rounded bg-yellow-100 px-2 py-0.5 text-xs font-semibold text-yellow-700">IN PROGRESS</span>`;
            }

            function resetBoxes() {
                [
                    'sppbHeaderBoxSppb', 'csHeaderBoxSppb', 'poHeaderBoxSppb', 'receiptHeaderBoxSppb',
                    'sppbDetailBoxSppb', 'csDetailBoxSppb', 'poDetailBoxSppb', 'receiptDetailBoxSppb'
                ].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.innerHTML = '';
                });
            }

            function renderHeader(boxId, header, title) {
                const box = document.getElementById(boxId);
                if (!box) return;

                if (!header) {
                    box.innerHTML = `
                        <div class="rounded-lg border border-gray-200 p-3 text-sm text-gray-500 dark:border-gray-700 dark:text-gray-300">
                            ${esc(title)} not created yet.
                        </div>`;
                    return;
                }

                // ✅ HARUS DI DALAM renderHeader (biar scope benar)
                const la = header.last_approval || null;

                let lastApprovalHtml = '';
                if (la) {
                    const st = String(la.status || '').toUpperCase();
                    const stText = st === 'P' ? 'Pending Approval' : (st === 'A' ? 'Approved' : st);

                    const who = (la.name ? esc(la.name) : '') || esc(la.username || '-');
                    const lvl = (la.aprv_leveling !== undefined && la.aprv_leveling !== null) ?
                        `Lvl ${esc(la.aprv_leveling)}` :
                        '';
                    const dtb = la.date_before ? esc(la.date_before) : '';
                    const dta = la.date_after ? esc(la.date_after) : '';

                    lastApprovalHtml = `
                        <div class="sm:col-span-2 mt-2 rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-sm dark:border-indigo-700/40 dark:bg-indigo-900/20">
                            <div class="flex items-center justify-between">
                                <div class="font-semibold text-indigo-700 dark:text-indigo-300">Last Approval</div>
                                <div class="text-xs text-indigo-700/80 dark:text-indigo-300/80">
                                    ${esc(stText)} ${lvl ? `• ${lvl}` : ''}
                                </div>
                            </div>
                            <div class="mt-1 text-gray-700 dark:text-gray-200">
                                <div><span class="text-gray-500">By:</span> <span class="font-semibold">${who}</span></div>
                                ${dtb ? `<div><span class="text-gray-500">Start:</span> ${dtb}</div>` : ''}
                                ${dta ? `<div><span class="text-gray-500">Finish:</span> ${dta}</div>` : ''}
                            </div>
                        </div>
                    `;
                }

                box.innerHTML = `
                    <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-800 dark:text-white">
                                    ${esc(title)} : ${esc(header.doc)}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-300">${esc(header.date || '')}</div>
                            </div>
                            ${statusLabel(header.status)}
                        </div>

                        <div class="mt-3 grid grid-cols-1 gap-2 text-sm sm:grid-cols-2">
                            <div><span class="text-gray-500">Company:</span>
                                <span class="font-semibold text-gray-800 dark:text-white">${esc(header.cpny_id || '-')}</span>
                            </div>
                            <div><span class="text-gray-500">Department:</span>
                                <span class="font-semibold text-gray-800 dark:text-white">${esc(header.department_id || '-')}</span>
                            </div>
                            <div><span class="text-gray-500">Created By:</span>
                                <span class="font-semibold text-gray-800 dark:text-white">${esc(header.created_by || '-')}</span>
                            </div>

                            ${header.vendorname !== undefined
                                ? `<div class="sm:col-span-2"><span class="text-gray-500">Vendor:</span>
                                                                                                                                                                                                                                                                                                                                                                                        <span class="font-semibold text-gray-800 dark:text-white">${esc(header.vendorname || '-')}</span></div>`
                                : ''
                            }

                            ${header.keperluan !== undefined
                                ? `<div class="sm:col-span-2"><span class="text-gray-500">Keperluan:</span>
                                                                                                                                                                                                                                                                                                                                                                                        <span class="font-semibold text-gray-800 dark:text-white">${esc(header.keperluan || '-')}</span></div>`
                                : ''
                            }
                        </div>

                        ${lastApprovalHtml}
                    </div>
                `;
            }

            // ---------- Detail renderers ----------
            function renderDetailSppb(rows) {
                if (!Array.isArray(rows) || rows.length === 0)
                    return `<div class="text-sm text-gray-500">No detail.</div>`;
                const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
                <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                <td class="px-3 py-2">${esc(r.uom)}</td>
                <td class="px-3 py-2">${esc(r.siteid)}</td>
                <td class="px-3 py-2">${esc(r.ordered || '')}</td>
            </tr>`).join('');
                return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                    <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                    <th class="px-3 py-2 text-left">Site</th>
                    <th class="px-3 py-2 text-left">Ordered</th>
                    </tr>
                </thead>
                <tbody>${trs}</tbody>
                </table>
            </div>`;
            }

            function renderDetailCs(rows) {
                if (!Array.isArray(rows) || rows.length === 0)
                    return `<div class="text-sm text-gray-500">No detail.</div>`;

                const trs = rows.map(r => `
                <tr class="border-b dark:border-gray-700">
                <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                <td class="px-3 py-2">${esc(r.uom)}</td>
                <td class="px-3 py-2">${esc(r.vendorname_selected || '-')}</td>
                </tr>
            `).join('');

                return `
                <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/30">
                    <tr>
                        <th class="px-3 py-2 text-left">Inventory</th>
                        <th class="px-3 py-2 text-left">Description</th>
                        <th class="px-3 py-2 text-right">Qty</th>
                        <th class="px-3 py-2 text-left">UOM</th>
                        <th class="px-3 py-2 text-left">Selected Vendor</th>
                    </tr>
                    </thead>
                    <tbody>${trs}</tbody>
                </table>
                </div>`;
            }


            function renderDetailPo(rows) {
                if (!Array.isArray(rows) || rows.length === 0)
                    return `<div class="text-sm text-gray-500">No detail.</div>`;
                const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
                <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                <td class="px-3 py-2 text-right">${fmt2(r.qty)}</td>
                <td class="px-3 py-2">${esc(r.uom)}</td>
            </tr>`).join('');
                return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                    <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                    </tr>
                </thead>
                <tbody>${trs}</tbody>
                </table>
            </div>`;
            }

            function renderDetailReceipt(rows) {
                if (!Array.isArray(rows) || rows.length === 0)
                    return `<div class="text-sm text-gray-500">No detail.</div>`;
                const trs = rows.map(r => `
            <tr class="border-b dark:border-gray-700">
                <td class="px-3 py-2">${esc(r.inventoryid)}</td>
                <td class="px-3 py-2">${esc(r.inventory_descr)}</td>
                <td class="px-3 py-2 text-right">${fmt2(r.qtyordered)}</td>
                <td class="px-3 py-2 text-right">${fmt2(r.qty_received)}</td>
                <td class="px-3 py-2">${esc(r.uom)}</td>
            </tr>`).join('');
                return `
            <div class="rounded-lg border border-gray-200 overflow-x-auto dark:border-gray-700">
                <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/30">
                    <tr>
                    <th class="px-3 py-2 text-left">Inventory</th>
                    <th class="px-3 py-2 text-left">Description</th>
                    <th class="px-3 py-2 text-right">Qty Ordered</th>
                    <th class="px-3 py-2 text-right">Qty Received</th>
                    <th class="px-3 py-2 text-left">UOM</th>
                    </tr>
                </thead>
                <tbody>${trs}</tbody>
                </table>
            </div>`;
            }

            // ---------- Select helpers ----------
            function fillSelect(selectId, items, selectedDoc) {
                const sel = document.getElementById(selectId);
                if (!sel) return;

                sel.innerHTML = '';

                if (!items || items.length === 0) {
                    sel.innerHTML = `<option value="">none </option>`;
                    return;
                }

                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.doc;
                    // opt.textContent = `${it.doc}${it.date ? ' | ' + it.date : ''}${it.is_approved ? ' | APPROVED' : ''}`;
                    opt.textContent = `${it.doc}` +
                        (it.date ? ` | ${it.date}` : '') +
                        (it.status ? ` | ${statusLabel2(it.status)}` : '');

                    if (selectedDoc && it.doc === selectedDoc) opt.selected = true;
                    sel.appendChild(opt);
                });

                // kalau selectedDoc kosong, auto pilih pertama
                if (!selectedDoc && sel.options.length > 0) sel.selectedIndex = 0;
            }

            function filterReceiptsByPo(poDoc) {
                const all = window.__receiptList || [];
                if (!poDoc) return all;

                // backend kamu harus kirim: lists.receipt[].ponbr atau .po
                return all.filter(x => (x.ponbr === poDoc) || (x.po === poDoc));
            }

            // ---------- AJAX helpers (jQuery Deferred) ----------
            function fetchItem(eid, type, doc) {
                return $.ajax({
                    url: `/sppbs/${eid}/tracking-detail/item`,
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        type,
                        doc
                    }
                });
            }

            // ---------- Change handlers (safe: off/on) ----------
            $(document).off('change', '#selCsSppb').on('change', '#selCsSppb', function() {
                const eid = window.__trackEid;
                const doc = this.value;
                if (!eid || !doc) return;

                fetchItem(eid, 'cs', doc).done(res => {
                    renderHeader('csHeaderBoxSppb', res.header, 'CS');
                    document.getElementById('csDetailBoxSppb').innerHTML = renderDetailCs(res.details ||
                        []);
                });
            });

            $(document).off('change', '#selPoSppb').on('change', '#selPoSppb', function() {
                const eid = window.__trackEid;
                const doc = this.value;
                if (!eid || !doc) return;

                fetchItem(eid, 'po', doc).done(res => {
                    renderHeader('poHeaderBoxSppb', res.header, 'PO');
                    document.getElementById('poDetailBoxSppb').innerHTML = renderDetailPo(res.details ||
                        []);
                });

                // filter receipt list by PO selected
                const filtered = filterReceiptsByPo(doc);
                fillSelect('selReceiptSppb', filtered, (filtered[0]?.doc || ''));

                // auto load first receipt after filter
                const first = filtered[0]?.doc;
                if (first) {
                    fetchItem(eid, 'receipt', first).done(res => {
                        renderHeader('receiptHeaderBoxSppb', res.header, 'Receipt');
                        document.getElementById('receiptDetailBoxSppb').innerHTML = renderDetailReceipt(
                            res
                            .details || []);
                    });
                } else {
                    renderHeader('receiptHeaderBoxSppb', null, 'Receipt');
                    document.getElementById('receiptDetailBoxSppb').innerHTML =
                        `<div class="text-sm text-gray-500">No detail.</div>`;
                }
            });

            $(document).off('change', '#selReceiptSppb').on('change', '#selReceiptSppb', function() {
                const eid = window.__trackEid;
                const doc = this.value;
                if (!eid || !doc) return;

                fetchItem(eid, 'receipt', doc).done(res => {
                    renderHeader('receiptHeaderBoxSppb', res.header, 'Receipt');
                    document.getElementById('receiptDetailBoxSppb').innerHTML = renderDetailReceipt(res
                        .details || []);
                });
            });

            // ---------- Main click handler (ONLY ONE) ----------
            $(document).off('click', '.tracking-btn-sppb').on('click', '.tracking-btn-sppb', function() {
                const eid = $(this).data('id');
                const doc = $(this).data('doc') || '';
                window.__trackEid = eid;

                resetToSppbTab();
                resetBoxes();
                openTrackingModalSppb(doc);
                setLoading(true);

                $.ajax({
                    url: `/sppbs/${eid}/tracking-detail`,
                    method: 'GET',
                    dataType: 'json',
                    success: function(res) {
                        setLoading(false);

                        // simpan list untuk filtering
                        window.__receiptList = res.lists?.receipt || [];

                        // render header default (selected)
                        renderHeader('sppbHeaderBoxSppb', res.sppb?.header, 'SPPB');
                        renderHeader('csHeaderBoxSppb', res.cs?.header, 'CS');
                        renderHeader('poHeaderBoxSppb', res.po?.header, 'PO');
                        renderHeader('receiptHeaderBoxSppb', res.receipt?.header, 'Receipt');

                        // render detail default (selected)
                        document.getElementById('sppbDetailBoxSppb').innerHTML = renderDetailSppb(
                            res
                            .sppb?.details || []);
                        document.getElementById('csDetailBoxSppb').innerHTML = renderDetailCs(res.cs
                            ?.details || []);
                        document.getElementById('poDetailBoxSppb').innerHTML = renderDetailPo(res.po
                            ?.details || []);
                        document.getElementById('receiptDetailBoxSppb').innerHTML =
                            renderDetailReceipt(
                                res.receipt?.details || []);

                        // dropdown lists (support multiple)
                        fillSelect('selCsSppb', res.lists?.cs || [], res.selected?.cs_no || '');
                        fillSelect('selPoSppb', res.lists?.po || [], res.selected?.po_no || '');

                        // receipt list default: filter by selected PO
                        const poSelected = (res.selected?.po_no) || document.getElementById(
                                'selPoSppb')
                            ?.value || '';
                        const filteredReceipt = filterReceiptsByPo(poSelected);
                        const receiptSelected = res.selected?.receipt_no || (filteredReceipt[0]
                            ?.doc || '');
                        fillSelect('selReceiptSppb', filteredReceipt, receiptSelected);

                    },
                    error: function(xhr) {
                        setLoading(false);
                        document.getElementById('sppbHeaderBoxSppb').innerHTML =
                            `<div class="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    Failed to load tracking (HTTP ${xhr.status || ''})
                </div>`;
                    }
                });
            });

        })(); // end IIFE
    </script>


</x-app-layout>
