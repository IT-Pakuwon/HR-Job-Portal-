<x-app-layout>
    <x-popup-styles />
    <x-spp-list-styles />
    <x-spp-list-scripts />
    @php
        $currentPage = Route::currentRouteName() == 'wos' ? 'HR' : '';
    @endphp
    @php
        $user = auth()->user();

        $hasWoAllAccess = $user->isAdmin();
        $hasAllList = auth()->user()->hasRole('COSTCTRLACCESS');

        $xlCols = 5;

        if ($hasWoAllAccess || $hasAllList) {
            $xlCols++; // WO All
        }
        if (!empty($canTenantWo)) {
            $xlCols++; // Tenant WO Jobs (ACCESSTR only)
        }
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $statusCards = [];
            if (!empty($canTenantWo)) {
                $statusCards[] = [['id' => 'tenantWoTab'], 'Tenant WO Jobs', '🏢', 'sky', $tenantWoCount ?? 0, false, null, null, true];
            }
            $statusCards[] = [['data-status' => ''], 'All', '📦', 'all', $all, false];
            $statusCards[] = [['data-status' => 'P'], 'On Progress', '⏳', 'yellow', $onProgress, true];
            $statusCards[] = [['data-status' => 'R'], 'Reject', '⛔', 'red', $reject, false];
            $statusCards[] = [['data-status' => 'D'], 'Revise / Draft', '✏️', 'blue', $revise, false];
            $statusCards[] = [['data-status' => 'C'], 'Completed', '✅', 'green', $completed, false];
            if (auth()->user()->isAdmin()) {
                $statusCards[] = [['data-scope' => 'wo_all'], 'WO All', '🌐', 'purple', $woAll ?? 0, false];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        @if (!empty($canTenantWo))
            <div id="tenantWoPanel"
                class="dt-pretty mt-2 hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
                <x-list-page-header title="Tenant WO Jobs" subtitle="Complaints transferred to WO" />
                <div class="relative overflow-x-auto">
                    <table id="tenantWoTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                        <thead>
                            <tr
                                class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                                <th class="px-4 py-3 text-left font-medium">Complaint No</th>
                                <th class="px-4 py-3 text-left font-medium">Complaint Date</th>
                                <th class="px-4 py-3 text-left font-medium">Site</th>
                                <th class="px-4 py-3 text-left font-medium">Tenant</th>
                                <th class="px-4 py-3 text-left font-medium">Category</th>
                                <th class="px-4 py-3 text-left font-medium">Description</th>
                                <th class="px-4 py-3 text-left font-medium">Transferred</th>
                                <th class="px-4 py-3 text-left font-medium">WO</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">WO Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        @endif

        <div id="requestWoPanel"
            class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Request WO" :create-url="url('/createwos')" />

            <div class="relative overflow-hidden">
                <table id="wosTable" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr
                            class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="w-10 px-4 py-3"></th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                DocID</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Date</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Company</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Department</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Work Type </th> <!-- << -->
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                WO Request</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Budget Use</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Description</th>
                            <th class="w-32 px-4 py-3 text-left font-medium">
                                Status</th>
                            <th class="px-4 py-3 text-left font-medium">
                                WO Status
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-[#0f172a]">
                        {{-- Table rows will be populated here by JavaScript/DataTables --}}
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================== TRACKING MODAL ================== -->
        <div id="trackingModal" class="app-popup fixed inset-0 z-50 hidden items-center justify-center">
            <div
                class="app-dialog app-dialog-padded max-h-[90vh] w-[95vw] max-w-none overflow-y-auto rounded-xl bg-white p-4 sm:max-w-3xl md:max-w-5xl lg:max-w-6xl xl:max-w-7xl dark:bg-gray-800">

                <!-- Header -->
                <div class="app-head flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                    <h3 class="text-[12px] font-semibold text-gray-800 dark:text-white">
                        WO Tracking <span id="trackDoc" class="font-bold text-indigo-600"></span>
                    </h3>
                    <button aria-label="Close" type="button" id="closeTracking"
                        class="app-close"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg></button>
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
    // Scroll controls (VERTICAL)
    (function() {
        const scroller = document.getElementById('tlScroll');

        document.getElementById('tlPrev')?.addEventListener('click', () =>
            scroller?.scrollBy({ top: -200, behavior: 'smooth' })
        );

        document.getElementById('tlNext')?.addEventListener('click', () =>
            scroller?.scrollBy({ top: 200, behavior: 'smooth' })
        );
    })();

    // Open modal
    function openTrackingModal(docText) {
        document.getElementById('trackDoc').textContent = docText ? `(${docText})` : '';
        const modal = document.getElementById('trackingModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Close modal
    function closeTrackingModal() {
        const modal = document.getElementById('trackingModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.getElementById('closeTracking')?.addEventListener('click', closeTrackingModal);

    document.getElementById('trackingModal')?.addEventListener('click', (e) => {
        if (e.target.id === 'trackingModal') closeTrackingModal();
    });

    // Load tracking
    $(document).on('click', '.tracking-btn', function() {
        const id = $(this).data('id');
        const doc = $(this).data('doc') || '';

        openTrackingModal(doc);

        $.ajax({
            url: `/wos/${id}/tracking`,
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
        let scopeFilter = '';
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
                    width: '28px',
                    className: 'dtr-control',
                    orderable: false
                }],
                ajax: {
                    url: "{{ route('wos.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.status = statusFilter ?? '';
                        d.scope = scopeFilter ?? '';
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
                            return sppDocCell(row, data || row.eid, 'wos', currentUser);
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
                        data: 'budget_use',
                        defaultContent: '-',
                        className: 'text-left'
                    },
                    {
                        data: 'keperluan',
                        className: 'text-left'
                    },
                    {
                        data: 'status',
                        className: 'text-left',
                        render: function(data) {
                            return sppStatusBadge(data);
                        }
                    },
                    {
                        data: 'status_pekerjaan', // ini dok-status; kalau mau ganti ke job status tinggal pakai 'status_pekerjaan'
                        className: 'text-left',
                        render: function(data, type, row) {
                            // map dok-status (boleh dibiarkan)
                            return sppStatusBadge(data === 'H' ? 'Hold' : data);
                        }
                    }
                ],
                searchDelay: 400,
                stateSave: true,
                responsive: true
            });


            // ===== Tenant WO Jobs tab (ACCESSTR only): complaints transferred to WO =====
            let tenantWoTable = null;
            $('#tenantWoTab').on('click', function(e) {
                e.preventDefault();
                $('#requestWoPanel').addClass('hidden');
                $('#tenantWoPanel').removeClass('hidden');
                $('.status-filter').removeClass('active');
                $(this).addClass('active');

                if (tenantWoTable) {
                    tenantWoTable.ajax.reload();
                    return;
                }
                const esc = s => $('<div>').text(s ?? '').html();
                const badge = (map, v) => sppStatusBadge(v === 'H' ? 'Hold' : v);
                const woStatus = {
                    P: { t: 'On Progress', c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40' },
                    C: { t: 'Completed', c: 'bg-green-200/60 text-green-800 border border-green-600/40' },
                    R: { t: 'Rejected', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
                    D: { t: 'Revise', c: 'bg-amber-200/60 text-amber-800 border border-amber-600/40' },
                };
                const jobStatus = {
                    H: { t: 'Hold', c: 'bg-gray-200/60 text-gray-700 border border-gray-500/40' },
                    P: { t: 'On Progress', c: 'bg-orange-200/60 text-orange-800 border border-orange-600/40' },
                    C: { t: 'Completed', c: 'bg-green-200/60 text-green-800 border border-green-600/40' },
                    X: { t: 'Cancel', c: 'bg-red-200/60 text-red-800 border border-red-600/40' },
                };
                tenantWoTable = $('#tenantWoTable').DataTable({
                    ajax: { url: "{{ route('wos.tenantjobs') }}", dataSrc: 'data' },
                    pageLength: 10,
                    order: [[6, 'desc']],
                    dom: '<"dt-toolbar"l f>rtip',
                    columns: [
                        { data: 'complaintnbr', render: v => `<span class="inline-flex whitespace-nowrap rounded-md border border-slate-200 bg-slate-50 px-3 py-1 text-[13px] font-semibold tabular-nums text-slate-700">${esc(v)}</span>` },
                        { data: 'complaintdate' },
                        { data: 'sitename', defaultContent: '-' },
                        { data: 'storename', render: (v, t, r) => esc(v || '-') + (r.unit ? `<div class="text-xs text-gray-500">${esc(r.unit)}</div>` : '') },
                        { data: 'category', defaultContent: '-' },
                        { data: 'description', className: 'whitespace-normal break-words min-w-[220px]', render: v => esc(v) },
                        { data: 'transferred_at', render: (v, t, r) => esc(v) + `<div class="text-xs text-gray-500">${esc(r.transferred_by)}</div>` },
                        { data: 'woid', render: (v, t, r) => !v ? '-' : (r.wo_eid
                            ? `<a href="/showwos/${r.wo_eid}" class="group/doc inline-flex min-w-[130px] items-center gap-2 whitespace-nowrap rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold tabular-nums text-blue-700 transition hover:border-blue-600 hover:bg-blue-600 hover:text-white"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span>${esc(v)}</a>`
                            : esc(v)) },
                        { data: 'wo_status', render: v => badge(woStatus, v) },
                        { data: 'wo_job_status', render: v => badge(jobStatus, v) },
                    ],
                    language: { emptyTable: 'No complaint has been transferred to WO yet.' },
                });
            });

            $('.status-filter').on('click', function(e) {
                e.preventDefault();
                $('#tenantWoPanel').addClass('hidden');
                $('#requestWoPanel').removeClass('hidden');
                $('#tenantWoTab').removeClass('active');

                const status = $(this).data('status');
                const scope = $(this).data('scope');

                if (scope) {
                    // 👉 WO ALL clicked
                    scopeFilter = scope;
                    statusFilter = ''; // 🔥 IMPORTANT RESET
                } else {
                    // 👉 Normal status clicked
                    statusFilter = status ?? '';
                    scopeFilter = ''; // 🔥 IMPORTANT RESET
                }

                table.ajax.reload(null, true);
            });

            // $('.status-filter').on('click', function(e) {
            //     e.preventDefault();
            //     statusFilter = $(this).data('status') || '';
            //     table.ajax.reload(null, true);
            // });

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
