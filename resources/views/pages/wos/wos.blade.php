<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'wos' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Status --}}
            <a href="#" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">All</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $all }}</p>
                </div>
            </a>

            {{-- On Progress Status --}}
            <a href="#" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $onProgress }}</p>
                </div>
            </a>

            {{-- Reject Status --}}
            <a href="#" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Reject</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $reject }}</p>
                </div>
            </a>

            {{-- Revise / Draft Status --}}
            <a href="#" class="status-filter group block h-full" data-status="D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-gray-100 hover:shadow-md active:scale-95 dark:border-white dark:text-white dark:hover:bg-gray-700">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✏️</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Revise / Draft</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $revise }}</p>
                </div>
            </a>

            {{-- Completed Status --}}
            <a href="#" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <p class="break-words text-sm font-medium">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-extrabold">{{ $completed }}</p>
                </div>
            </a>

        </div>

        <div class="mt-6 flex flex-col gap-6 rounded-xl bg-white p-6 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Request WO</h1>
                <a href="{{ url('/createwos') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </a>
            </div>

            <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}
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
                                Work Type</th> <!-- << -->
                            <th class="w-32 px-6 py-3 font-medium">
                                WO Request</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Description</th>
                            <th class="w-32 px-6 py-3 font-medium">
                                Status</th>
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
                class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-xl bg-white p-6 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                <!-- Header -->
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                        WO Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                    </h3>
                    <button id="closeTracking"
                        class="text-lg leading-none text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200">
                        &times;
                    </button>
                </div>

                <!-- Controls (opsional) -->
                <div class="mb-3 flex items-center justify-end gap-2">
                    <button type="button" id="tlPrev"
                        class="rounded-lg border px-3 py-1 text-xs hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
                        ‹ Prev
                    </button>
                    <button type="button" id="tlNext"
                        class="rounded-lg border px-3 py-1 text-xs hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700">
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
    </div>
    <script>
        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                list.innerHTML = `<p class="text-xs text-gray-500">No tracking history found.</p>`;
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
                const title = (s.title && String(s.title).trim()) || 'WO';

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
                                <p class="text-xs font-semibold ${C.colorTitle}">${title}</p>
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
                url: `/wos/${id}/tracking`,
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
                            title: 'WO',
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
            let statusFilter = 'P';

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
                        title: 'List_WO',
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
                        title: 'List_WO',
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
                    url: "{{ route('wos.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
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
                            <div class="flex items-left gap-2 whitespace-nowrap">
                            <a href="${url}" class="${cls}">${text}</a>
                            <button type="button"
                                class="tracking-btn inline-flex items-left justify-center rounded-full p-2
                                    text-red-600 hover:text-red-700 hover:bg-red-50"
                                data-id="${row.eid}" data-doc="${text}" aria-label="Tracking" title="Tracking">
                                <i class="fa-solid fa-paper-plane"></i>
                            </button>
                            </div>
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
                    }, // << kolom baru
                    {
                        data: 'worequest',
                        defaultContent: '-',
                        className: 'text-left'
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
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                statusFilter = $(this).data('status') || '';
                table.ajax.reload(null, true);
            });

            document.querySelectorAll('.status-filter').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    document.querySelectorAll('.status-filter').forEach(b => b.classList.remove(
                        'active'));
                    this.classList.add('active');
                });
            });
        });
    </script>
</x-app-layout>
