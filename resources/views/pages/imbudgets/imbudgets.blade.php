<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'imbudgets' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">

            {{-- All --}}
            <button type="button" class="status-filter group block h-full" data-status="">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-orange-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">📄</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">All</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $all }}</p>
                </div>
            </button>

            {{-- Hold / Revise --}}
            <button type="button" class="status-filter group block h-full" data-status="H,D">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-amber-700 bg-amber-200/20 p-3 text-amber-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-amber-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🛠️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Hold / Revise</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ ($hold ?? 0) + ($revise ?? 0) }}</p>
                </div>
            </button>

            {{-- On Progress --}}
            <button type="button" class="status-filter group block h-full" data-status="P">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-700 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-blue-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⏳</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">On Progress</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $onProgress }}</p>
                </div>
            </button>

            {{-- Reject --}}
            <button type="button" class="status-filter group block h-full" data-status="R">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">⛔️</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Reject</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $reject }}</p>
                </div>
            </button>

            {{-- Cancel --}}
            <button type="button" class="status-filter group block h-full" data-status="X">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-red-700 bg-red-200/20 p-3 text-red-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-red-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">🛑</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Cancel</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $cancel }}</p>
                </div>
            </button>

            {{-- Completed --}}
            <button type="button" class="status-filter group block h-full" data-status="C">
                <div
                    class="status-card flex h-full items-center gap-3 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600 transition-all duration-300 ease-in-out hover:-translate-y-1 hover:bg-green-100 hover:shadow-md active:scale-95">

                    <div class="flex h-7 w-7 shrink-0 items-center justify-center text-base">✅</div>

                    <div class="flex min-w-0 flex-grow flex-col">
                        <p class="break-words text-sm font-medium leading-tight">Completed</p>
                    </div>

                    <p class="shrink-0 text-base font-bold">{{ $completed }}</p>
                </div>
            </button>

        </div>
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                {{-- Changed text-lg to text-base --}}
                <h1 class="text-base font-extrabold text-gray-700 dark:text-white">IMBudget</h1>
            </div>

            <div class="rounded-base relative overflow-x-auto"> {{-- Padding applied here instead of outer container --}}
                <table id="imbudgetsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-6 py-2 font-medium">
                                DocID</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Date</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                CSID</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                SPPBJKTID</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                Company</th>
                            <th class="w-32 px-6 py-2 font-medium">
                                User Peminta</th>
                            <th class="w-32 px-6 py-2 font-medium">
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
                class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-xl bg-white p-4 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                <!-- Header -->
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-[12px] font-semibold text-gray-800 dark:text-white">
                        IM Budget Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
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
    </div>
    <script>
        function renderTimeline(steps = []) {
            const list = document.getElementById('tlList');
            if (!list) return;

            if (!Array.isArray(steps) || steps.length === 0) {
                list.innerHTML = `<p class="text-sm text-gray-500">No tracking history found.</p>`;
                return;
            }

            // ✅ ONLY APPROVAL ITEMS
            const approvals = steps.filter(s => s.type === 'approval');

            const approvedCount = approvals.filter(a => a.status === 'C').length;
            const total = approvals.length;

            list.className = "px-2 py-3";

            list.innerHTML = `
                <div class="rounded-xl border border-gray-200 bg-white p-4">

                    <!-- HEADER -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm font-semibold text-gray-700">
                        Approval Tracking
                        </div>
                        <div class="text-xs text-gray-500">
                            ${approvedCount}/${total} Approved
                        </div>
                    </div>

                    <!-- SCROLL -->
                    <div class="space-y-1 max-h-[400px] overflow-y-auto pr-2">

                        ${steps.map(s => {

                            // ======================
                            // 🔹 CYCLE HEADER
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
                            // 🔹 SKIP NON APPROVAL (optional)
                            // ======================
                            if (s.type !== 'approval') return '';

                            const st = String(s.status || '').toUpperCase();

                            let badge = s.status_label || '-';
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

                            const name = s.by || '-';
                            const initials = name !== '-'
                                ? name.split(' ').map(n => n[0]).join('').slice(0,2).toUpperCase()
                                : '?';

                            return `
                            <div class="flex items-center justify-between py-3">

                                <!-- LEFT -->
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

                                <!-- RIGHT -->
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
        function renderApprovalTable(steps) {
            return `
            <div class="w-full overflow-x-auto">
                <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-3 py-2 text-left">Level</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${steps.map(s => {
                            const color = s.status === 'C'
                                ? 'text-green-600'
                                : s.status === 'P'
                                ? 'text-yellow-600'
                                : s.status === 'R'
                                ? 'text-red-600'
                                : 'text-gray-500';

                            return `
                            <tr class="border-t">
                                <td class="px-3 py-2">${s.title}</td>
                                <td class="px-3 py-2">${s.by || '-'}</td>
                                <td class="px-3 py-2 font-semibold ${color}">
                                    ${s.status_label}
                                </td>
                                <td class="px-3 py-2 text-gray-500">
                                    ${s.at || '-'}
                                </td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            </div>`;
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
            const id = $(this).data('id'); // sekarang imbudgetid (URL-encoded)
            const doc = $(this).data('doc') || '';

            openTrackingModal(doc);

        $.ajax({
            url: `/imbudgets/${id}/tracking`,
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
    </script>




    <script>
        var currentUser = "{{ auth()->user()->username }}";
        $(document).ready(function() {
            // simpan status filter global
            let statusFilter = 'P'; // default

            const table = $('#imbudgetsTable').DataTable({
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
                        title: 'List_IMBudget',
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
                        title: 'List_IMBudget',
                        className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                        exportOptions: {
                            columns: ':visible',
                            modifier: {
                                page: 'current'
                            }
                        }
                    }
                ],
                // 🔥 END ADD

                ajax: {
                    url: "{{ route('imbudgets.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? ''; // kirim status ke server
                    }
                },
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
                order: [
                    [1, 'desc'],
                    [0, 'desc']
                ], // Date desc, lalu DocID desc


                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // DocID (link + tombol tracking)
                    {
                        data: 'imbudgetid',
                        render: function(data, type, row) {
                            // default link ke show
                            let url =
                                `/showimbudgets/${encodeURIComponent(row.eid || row.imbudgetid)}`;
                            let cls =
                                'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200  bg-gray-600 hover:bg-gray-700 ';
                            const text = data || '-';

                            // jika status Draft & milik current user → link ke edit
                            if (row.status === 'D' && row.user_peminta === currentUser) {
                                url =
                                    `/editimbudgets/${encodeURIComponent(row.eid || row.imbudgetid)}`;
                                cls =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            if (row.status === 'H' && row.user_peminta === currentUser) {
                                url =
                                    `/editimbudgets/${encodeURIComponent(row.eid || row.imbudgetid)}`;
                                cls =
                                    'inline-flex justify-center items-center w-[120px] px-3 py-1.5 text-sm leading-tight font-semibold text-white rounded text-center transition-colors duration-200 bg-yellow-500 hover:bg-yellow-700';
                            }

                            return `
                                    <div class="flex items-left gap-2 whitespace-nowrap">
                                    <a href="${url}" class="${cls}">${text}</a>
                                    <button type="button"
                                        class="tracking-btn inline-flex items-left justify-center rounded-full p-2 text-red-600 hover:text-red-700 hover:bg-red-50"
                                        data-id="${encodeURIComponent(row.imbudgetid)}" data-doc="${text}" aria-label="Tracking" title="Tracking">
                                        <i class="fa-solid fa-paper-plane"></i>
                                    </button>
                                    </div>
                                `;
                        }
                    },
                    {
                        data: 'imbudgetdate',
                        className: 'text-left'
                    },
                    {
                        data: 'csid',
                        className: 'text-center w-32',
                        defaultContent: '-'
                    },
                    {
                        data: 'sppbjktid',
                        className: 'text-center w-32',
                        defaultContent: '-'
                    },
                    {
                        data: 'cpny_id',
                        className: 'text-center w-32',
                        defaultContent: '-'
                    },
                    {
                        data: 'user_peminta',
                        className: 'text-center',
                        defaultContent: '-'
                    },

                    // Status (badge)
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            const map = {
                                'D': {
                                    t: 'Revise',
                                    c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40'
                                },
                                'P': {
                                    t: 'On Progress',
                                    c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40'
                                },
                                'H': {
                                    t: 'Hold',
                                    c: 'bg-amber-300/30 text-amber-700'
                                }, // NEW
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
