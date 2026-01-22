<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'sppbs' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full px-8 py-4 sm:px-6 lg:px-8">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5">

            {{-- All Status --}}
            <button type="button" class="text-left">
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
            </button>

            {{-- On Progress Status --}}
            <button type="button" class="text-left">
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
            </button>

            {{-- Reject Status --}}
            <button type="button" class="text-left">
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
            </button>

            {{-- Revise / Draft Status --}}
            <button type="button" class="text-left">
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
            </button>

            {{-- Completed Status --}}
            <button type="button" class="text-left">
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
            </button>

        </div>

        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">Request SPPB</h1>
                <a href="{{ url('/createsppbs') }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    <i class="fas fa-plus pr-2"></i>Create
                </a>
            </div>

            <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}
                <table id="sppbsTable" class="text-body w-full text-left text-sm rtl:text-right">
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
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Company
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Department
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Request Type
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
                                Description
                            </th>
                            <th scope="col" class="w-32 px-6 py-2 font-medium">
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
                class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-xl bg-white p-4 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                <!-- Header -->
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                        SPPB Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                    </h3>
                    <button id="closeTracking"
                        class="text-lg leading-none text-gray-400 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-200">
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
            </div>
        </div>
    </div>
    <script>
        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                list.innerHTML = `<p class=" text-sm  text-gray-500">No tracking history found.</p>`;
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
                const title = (s.title && String(s.title).trim()) || 'SPPB';

                const when = (s.at && String(s.at).trim()) || '';
                const by = (s.by && String(s.by).trim()) || '';
                const statusText = (s.status_label && String(s.status_label).trim()) || C.label;

                // tampilkan jadi multi-line: status, nama, waktu
                let detailHtml = '';
                if (statusText) detailHtml += `<p class=" text-sm  text-gray-500">${statusText}</p>`;
                if (by) detailHtml += `<p class=" text-sm  text-gray-500">${by}</p>`;
                if (when) detailHtml += `<p class=" text-sm  text-gray-500">${when}</p>`;

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
                                <p class=" text-sm  font-semibold ${C.colorTitle}">${title}</p>
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
                url: `/sppbs/${id}/tracking`,
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
                            title: 'SPPB',
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

            const table = $('#sppbsTable').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                // ==== SCROLLER OPSIONAL (butuh plugin DataTables Scroller) ====
                // scrollY: '60vh',
                // scroller: true,

                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, 250, -1],
                    [10, 25, 50, 100, 250, 'All']
                ],



                dom: '<"dt-toolbar"l B f>rtip',
                buttons: [{
                        extend: 'excelHtml5',
                        text: '↓ Excel',
                        title: 'List_SPPB',
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
                        title: 'List_SPPB',
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
                    url: "{{ route('sppbs.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? ''; // kirim status ke server
                    }
                },

                order: [
                    [0, 'desc']
                ], // Date desc, lalu DocID desc
                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // DocID (button link)
                    {
                        data: 'sppbid',
                        render: function(data, type, row) {
                            let showUrl = `/showsppbs/${row.eid}`;
                            let editUrl = `/editsppbs/${row.eid}`;

                            let viewCls =
                                'inline-flex items-center justify-center rounded-full p-2 ' +
                                'text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50';

                            let editCls =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 ' +
                                'text-sm font-semibold text-white rounded transition-colors ' +
                                'bg-yellow-500 hover:bg-yellow-700';

                            let defaultCls =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 ' +
                                'text-sm font-semibold text-white rounded transition-colors ' +
                                ' bg-gray-600 hover:bg-gray-700 ';

                            const text = data || row.id;

                            // ===== DRAFT & OWNER =====
                            if (row.status === 'D' && row.created_by === currentUser) {
                                return `
                                    <div class="flex items-center gap-2 whitespace-nowrap">
                                        <!-- EDIT -->
                                        <a href="${editUrl}" class="${editCls}">
                                            ${text}
                                        </a>

                                        <!-- VIEW (EYE ICON) -->
                                        <a href="${showUrl}"  target="_blank" class="${viewCls}" title="View">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        <!-- TRACKING -->
                                        <button type="button"
                                            class="tracking-btn inline-flex items-center justify-center rounded-full p-2
                                                text-red-600 hover:text-red-700 hover:bg-red-50"
                                            data-id="${row.eid}" aria-label="Tracking" title="Tracking">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>
                                    </div>
                                `;
                            }

                            // ===== DEFAULT (NON-DRAFT / BUKAN OWNER) =====
                            return `
                                <div class="flex items-center gap-2 whitespace-nowrap">
                                    <a href="${showUrl}" class="${defaultCls}">
                                        ${text}
                                    </a>

                                    <button type="button"
                                        class="tracking-btn inline-flex items-center justify-center rounded-full p-2
                                            text-red-600 hover:text-red-700 hover:bg-red-50"
                                        data-id="${row.eid}" aria-label="Tracking" title="Tracking">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                </div>
                            `;
                        }

                    },

                    {
                        data: 'sppbdate',
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
                        data: 'requesttype_name',
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
