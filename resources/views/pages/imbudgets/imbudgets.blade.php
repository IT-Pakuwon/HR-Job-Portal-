<x-app-layout>
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'imbudgets' ? 'HR' : '';
    @endphp
    <div class="max-w-9xl mx-auto w-full p-2">
        {{-- STATUS CARDS (Hold / Revise is the default filter, see statusFilter below) --}}
        @php
            $statusCards = [
                [['data-status' => ''], 'All', '📦', 'all', $all, false],
                [['data-status' => 'H,D'], 'Hold / Revise', '🛠️', 'blue', ($hold ?? 0) + ($revise ?? 0), true],
                [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, false],
                [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false],
                [['data-status' => 'X'], 'Cancel', '❌', 'pink', $cancel, false],
                [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false],
            ];
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="IM Budget" subtitle="Track and manage your IM budget requests" />

            <div class="relative overflow-hidden">
                <table id="imbudgetsTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                RefNbr</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                User Peminta</th>
                            <th class="min-w-64 px-4 py-3 text-left font-medium">
                                Keperluan</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-[#0f172a]">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================== TRACKING MODAL ================== -->
        <div id="trackingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div
                class="max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-2xl bg-white p-6 shadow-2xl ring-1 ring-black/5 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800 dark:ring-white/10">

                <!-- Header -->
                <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white">
                        IM Budget Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                    </h3>
                    <button id="closeTracking"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-700 dark:hover:text-gray-200"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
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
                list.innerHTML = `<p class="text-sm text-gray-500 dark:text-gray-400">No tracking history found.</p>`;
                return;
            }

            // ✅ ONLY APPROVAL ITEMS
            const approvals = steps.filter(s => s.type === 'approval');

            const approvedCount = approvals.filter(a => a.status === 'C').length;
            const total = approvals.length;

            list.className = "px-2 py-3";

            list.innerHTML = `
                <div class="rounded-xl border border-gray-200 bg-white p-4 dark:bg-gray-800 dark:border-gray-700">

                    <!-- HEADER -->
                    <div class="flex justify-between items-center mb-4">
                        <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                        Approval Tracking
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
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

                                    <div class="h-8 w-8 flex items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-600 dark:bg-gray-900 dark:text-gray-400">
                                        ${initials}
                                    </div>

                                    <div>
                                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                            ${s.title}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
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
            <div class="w-full overflow-x-hidden">
                <table class="min-w-full text-sm border border-gray-200 rounded-lg overflow-hidden dark:border-gray-700">
                    <thead class="bg-gray-100 text-gray-700 dark:bg-gray-900 dark:text-gray-300">
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
                                <td class="px-3 py-2 text-gray-500 dark:text-gray-400">
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
            let statusFilter = 'H,D'; // default = Hold / Revise

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
                        text: '<i class="fa-solid fa-file-excel"></i> Excel',
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
                        text: '<i class="fa-solid fa-file-csv"></i> CSV',
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
                    [2, 'desc'],
                    [1, 'desc']
                ], // Date desc, lalu DocID desc


                columns: [{
                        data: null,
                        defaultContent: ''
                    },
                    // DocID (link + tombol tracking)
                    {
                        data: 'imbudgetid',
                        render: function(data, type, row) {
                            // Doc ID button; owner = user_peminta; ids come from eid (or the doc id when there is no eid)
                            return sppDocCell(row, data || '-', 'imbudgets', currentUser, false, ['D', 'H'], {
                                id: encodeURIComponent(row.eid || row.imbudgetid),
                                owner: 'user_peminta',
                                trackId: encodeURIComponent(row.imbudgetid)
                            });
                        }
                    },
                    {
                        data: 'imbudgetdate',
                        className: 'text-left'
                    },
                    {
                        data: 'refnbr',
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
                    {
                        data: 'keperluan',
                        className: 'min-w-64 text-left',
                        defaultContent: '-',
                        render: function(data, type) {
                            const text = data || '-';

                            if (type !== 'display') {
                                return text;
                            }

                            const safeText = $('<div>').text(text).html();
                            return `<span class="block max-w-md whitespace-normal break-words" title="${safeText}">${safeText}</span>`;
                        }
                    },

                    // Status (badge)
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            // H = Hold (the shared badge calls H "Draft")
                            if (data === 'H') {
                                return '<span class="inline-flex min-w-[116px] items-center justify-center gap-2 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30"><span class="h-1.5 w-1.5 rounded-full bg-blue-500"></span>Hold</span>';
                            }
                            return sppStatusBadge(data);
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
