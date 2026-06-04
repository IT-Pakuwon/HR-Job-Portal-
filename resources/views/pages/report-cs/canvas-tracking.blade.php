<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="f_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="f_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    CS ID
                </label>
                <input type="text" id="f_csid" placeholder="CS-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    SPPBJKT
                </label>
                <input type="text" id="f_sppbjkt" placeholder="PB-xxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Doc Type
                </label>
                <select id="f_doc_type" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="SPPB">SPPB</option>
                    <option value="SPPJ">SPPJ</option>
                    <option value="SPPK">SPPK</option>
                    <option value="SPPT">SPPT</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Department
                </label>
                <select id="f_department" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                    <option value="">All</option>
                    @foreach ($departments as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Requester
                </label>
                <input type="text" id="f_requester" placeholder="username / name"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            {{-- <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Status
                </label>
                <select id="f_status"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                    <option value="">All</option>
                    <option value="D">Revised</option>
                    <option value="A">Assigned</option>
                    <option value="S">Submitted</option>
                    <option value="P">On Process</option>
                    <option value="C">Completed</option>
                    <option value="R">Rejected</option>
                    <option value="X">Cancelled</option>
                </select>
            </div> --}}

            <div class="flex items-end gap-2 md:col-span-2">

                {{-- <input type="hidden" id="f_status"> --}}

                <button id="btnFilter" class="rounded-lg bg-gray-900 px-4 py-2 text-sm text-white">
                    Apply
                </button>

                <button id="btnReset" class="rounded-lg border px-4 py-2 text-sm">
                    Reset
                </button>

            </div>

        </div>

    </div>


    <!-- STATUS PANEL -->
    <div id="statusBar" class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5">

        <!-- ALL -->
        <div class="status-card group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-white p-2 shadow-sm transition hover:shadow-md"
            data-key="total">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-100 text-lg">
                📄
            </div>
            <div class="flex w-full justify-between">
                <div class="text-sm text-gray-500">All</div>
                <div class="count text-lg font-bold text-gray-800">0</div>
            </div>
        </div>

        <!-- PROCESS -->
        <div class="status-card group flex cursor-pointer items-center gap-3 rounded-xl border border-yellow-200 bg-yellow-50 p-2 shadow-sm transition hover:shadow-md"
            data-key="process">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-yellow-100 text-lg">
                ⏳
            </div>
            <div class="flex w-full justify-between">
                <div class="text-sm text-yellow-700">On Progress</div>
                <div class="count text-lg font-bold text-yellow-800">0</div>
            </div>
        </div>

        <!-- REJECT -->
        <div class="status-card group flex cursor-pointer items-center gap-3 rounded-xl border border-red-200 bg-red-50 p-2 shadow-sm transition hover:shadow-md"
            data-key="rejected">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-100 text-lg">
                ⛔
            </div>
            <div class="flex w-full justify-between">
                <div class="text-sm text-red-600">Rejected</div>
                <div class="count text-lg font-bold text-red-700">0</div>
            </div>
        </div>

        <!-- REVISED -->
        <div class="status-card group flex cursor-pointer items-center gap-3 rounded-xl border border-gray-200 bg-gray-50 p-2 shadow-sm transition hover:shadow-md"
            data-key="revised">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gray-200 text-lg">
                ✏️
            </div>
            <div class="flex w-full justify-between">
                <div class="text-sm text-gray-600">Revised</div>
                <div class="count text-lg font-bold text-gray-800">0</div>
            </div>
        </div>

        <!-- COMPLETED -->
        <div class="status-card group flex cursor-pointer items-center gap-3 rounded-xl border border-green-200 bg-green-50 p-2 shadow-sm transition hover:shadow-md"
            data-key="completed">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-green-100 text-lg">
                ✅
            </div>
            <div class="flex w-full justify-between">
                <div class="text-sm text-green-600">Completed</div>
                <div class="count text-lg font-bold text-green-700">0</div>
            </div>
        </div>

    </div>


    <!-- TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-800">
                Canvass Sheets Tracking
            </h2>
            <p class="text-xs text-gray-500">
                Monitor CS progress, assignment & approval
            </p>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="trackingTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500">
                    <tr>
                        {{-- <th></th> --}}
                        <th class="px-3 py-3">CS ID</th>
                        <th class="px-3 py-3">SPPBJKT</th>
                        <th class="px-3 py-3">CS Date</th>
                        <th class="px-3 py-3">Company</th>
                        <th class="px-3 py-3">Department</th>
                        <th class="px-3 py-3">Created By</th>
                        <th class="px-3 py-3">Note</th>
                        <th class="px-3 py-3">Assign Date</th>
                        <th class="px-3 py-3">Submit Date</th>
                        <th class="px-3 py-3 text-center">Days</th>
                        <th class="px-3 py-3 text-center">Status</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

    <!-- TRACKING MODAL -->
    <div id="trackingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">

        <div class="w-full max-w-2xl rounded-2xl bg-white shadow-xl">

            <div class="flex items-center justify-between border-b px-6 py-4">
                <h3 id="trackingTitle" class="text-sm font-semibold text-gray-800 dark:text-white">
                    CS Tracking
                </h3>
                <button id="closeTracking" class="text-gray-500 hover:text-black">
                    ✕
                </button>
            </div>

            <div id="trackingContent" class="max-h-[500px] overflow-y-auto p-6">
                <!-- timeline injected here -->
            </div>

        </div>

    </div>

</div>


<script>
    $(function() {

        let currentStatus = '';
        let table = $('#trackingTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            pageLength: 10,
            lengthMenu: [
                [10, 25, 50, 100, 250, -1],
                [10, 25, 50, 100, 250, 'All']
            ],



            ajax: {
                url: "{{ route('report.cs.tracking.json') }}",
                data: function(d) {

                    // ❌ HAPUS INI
                    // d.status = currentStatus;

                    // ✅ pake 1 source saja
                    d.status = currentStatus;

                    d.date_from = $('#f_date_from').val();
                    d.date_to = $('#f_date_to').val();
                    d.csid = $('#f_csid').val();
                    d.sppbjkt = $('#f_sppbjkt').val();
                    d.department = $('#f_department').val();
                    d.requester = $('#f_requester').val();
                    d.doc_type = $('#f_doc_type').val();
                },

                dataSrc: function(json) {

                    console.log(json); // 🔍 debug (optional)

                    updateStatusCards(json.summary); // ✅ FIX

                    return json.data;
                }
            },

            columns: [

                // { data: null, className: 'dtr-control', orderable:false },
                {
                    data: 'csid',
                    render: function(data, type, row) {

                        return `
                        <div class="flex items-center gap-2">

                            <!-- OPEN DOCUMENT -->
                            <a href="/showcs/${row.cs_hash}" target="_blank"
                                class="px-2 py-1 bg-gray-700 text-white rounded text-xs hover:bg-gray-800">
                                ${data}
                            </a>

                            <!-- TRACK APPROVAL -->
                           <button class="btnTracking px-2 py-1 text-white rounded text-xs" data-id="${row.cs_hash}">
                                🔍
                            </button>

                        </div>
                    `;
                    }
                },
                {
                    data: 'sppbjktid',
                    render: function(data, type, row) {

                        if (!data) return '';

                        // ❗ kalau hash tidak ada → jangan buat link
                        if (!row.doc_hash) {
                            return data;
                        }

                        let url = '';

                        switch (row.doc_type) {
                            case 'SPPB':
                                url = `/showsppbs/${row.doc_hash}`;
                                break;
                            case 'SPPJ':
                                url = `/showsppjs/${row.doc_hash}`;
                                break;
                            case 'SPPK':
                                url = `/showsppks/${row.doc_hash}`;
                                break;
                            case 'SPPT':
                                url = `/showsppts/${row.doc_hash}`;
                                break;
                            default:
                                return data;
                        }

                        return `<a href="${url}" target="_blank"
                        class="px-2 py-1 bg-indigo-500 text-white rounded text-xs">
                        ${data}
                    </a>`;
                    }
                },

                {
                    data: 'csdate',
                    render: function(data, type) {

                        if (!data) return '';

                        // sorting uses raw value
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        // display as yyyy-mm-dd
                        let d = new Date(data);
                        if (isNaN(d)) return data;
                        let yyyy = d.getFullYear();
                        let mm = String(d.getMonth() + 1).padStart(2, '0');
                        let dd = String(d.getDate()).padStart(2, '0');
                        return `${yyyy}-${mm}-${dd}`;
                    }
                },
                {
                    data: 'cpny_id'
                },
                {
                    data: 'department_name'
                },
                {
                    data: 'created_by_name'
                },
                {
                    data: 'csnote',
                    defaultContent: '-'
                },

                {
                    data: 'assigndate',
                    render: function(data, type) {

                        if (!data) return '';

                        // raw value for sorting
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },

                {
                    data: 'submitdate',
                    render: function(data, type) {

                        if (!data) return '';

                        // raw value for sorting
                        if (type === 'sort' || type === 'type') {
                            return data;
                        }

                        return new Date(data).toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short',
                            year: 'numeric'
                        });
                    }
                },
                {
                    data: 'days',
                    className: 'text-center',
                    render: function(v, t, row) {

                        if (!v) return '';

                        // split "7 / 4"
                        let parts = v.split('/');
                        let current = parts[0];
                        let total = parts[1];

                        return renderDays(current, total);
                    }
                },
                {
                    data: 'status',
                    className: 'text-center',
                    render: function(_, __, row) {
                        return `<span class="px-2 py-1 rounded text-xs font-semibold ${row.status_class}">
                        ${row.status_label}
                    </span>`;
                    }
                }

            ],

            order: [
                [2, 'desc']
            ]
        });

        // ✅ PINDAH KE SINI
        $(document).on('click', '.status-card', function() {

            let key = $(this).data('key');

            $('.status-card').removeClass('ring-2 ring-indigo-500');
            $(this).addClass('ring-2 ring-indigo-500');

            let map = {
                total: '',
                process: 'P',
                rejected: 'R',
                revised: 'D',
                completed: 'C'
            };

            currentStatus = map[key] || '';

            table.ajax.reload(); // 🔥 pake instance yang sama
        });


        $('#btnFilter').click(() => table.ajax.reload());

        $('#btnReset').click(() => {
            $('#f_date_from,#f_date_to,#f_csid,#f_sppbjkt').val('');
            table.ajax.reload();
        });
    });



    function updateStatusCards(summary) {

        $('#statusBar .status-card').each(function() {
            let key = $(this).data('key');
            $(this).find('.count').text(summary[key] ?? 0);
        });
    }
</script>
<script>
    function renderDays(current, total) {

        current = parseInt(current || 0);
        total = parseInt(total || 0);

        const percent = total > 0 ? Math.min((current / total) * 100, 100) : 0;

        let color = {
            text: 'text-gray-600 dark:text-gray-300',
            bg: 'bg-gray-100 dark:bg-gray-700',
            bar: 'bg-gray-400'
        };

        let statusLabel = '';

        if (current > total) {
            color = {
                text: 'text-red-700 dark:text-red-400',
                bg: 'bg-red-50 dark:bg-red-900/20',
                bar: 'bg-red-500'
            };
            statusLabel = `<span class="text-[10px] text-red-500 font-semibold">Overdue</span>`;
        } else if (current === total) {
            color = {
                text: 'text-yellow-700 dark:text-yellow-400',
                bg: 'bg-yellow-50 dark:bg-yellow-900/20',
                bar: 'bg-yellow-500'
            };
            statusLabel = `<span class="text-[10px] text-yellow-600">Due Today</span>`;
        } else if (current >= total * 0.7) {
            color = {
                text: 'text-orange-600 dark:text-orange-400',
                bg: 'bg-orange-50 dark:bg-orange-900/20',
                bar: 'bg-orange-400'
            };
            statusLabel = `<span class="text-[10px] text-orange-500">Near Limit</span>`;
        }

        return `
            <div class="w-[90px] text-left">

                <!-- TOP TEXT -->
                <div class="flex items-center justify-between ${color.text}">
                    <span class="font-semibold text-xs">${current}</span>
                    <span class="text-[10px] opacity-70">/ ${total}</span>
                </div>

                <!-- PROGRESS BAR -->
                <div class="mt-1 h-1.5 w-full rounded-full ${color.bg}">
                    <div class="h-1.5 rounded-full ${color.bar}" style="width:${percent}%"></div>
                </div>

                <!-- STATUS -->
                <div class="mt-1">
                    ${statusLabel}
                </div>

            </div>
        `;
    }

    $(function() {

        /*
        |------------------------------------------
        | OPEN TRACKING
        |------------------------------------------
        */
        $(document).on('click', '.btnTracking', function() {

            let id = $(this).data('id');
            let row = $(this).closest('tr');
            let csid = row.find('a').first().text().trim();

            // set title
            $('#trackingTitle').html(`
                CS Tracking
                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">(${csid})</span>
            `);

            $('#trackingModal').removeClass('hidden').addClass('flex');

            // Loading skeleton (nicer)
            $('#trackingContent').html(`
                <div class="space-y-3 animate-pulse">
                    <div class="h-4 w-1/3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="h-4 w-1/2 bg-gray-200 dark:bg-gray-700 rounded"></div>
                    <div class="h-4 w-1/4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
            `);

            $.get(`{{ url('report-cs/cs') }}/${id}/tracking`, function(res) {

                let html = '';

                if (!res || !Array.isArray(res.steps) || res.steps.length === 0) {
                    html = `
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            No tracking data
                        </div>
                    `;
                } else {

                    html = `
                        <div class="rounded-xl border border-gray-200 dark:border-gray-700
                                    bg-white dark:bg-gray-900
                                    max-h-[420px] overflow-y-auto p-4">

                            <div class="flex flex-col divide-y divide-gray-200 dark:divide-gray-700">
                    `;

                    res.steps.forEach(step => {

                        const st = String(step.status || '').toUpperCase();

                        // let dot = 'bg-gray-400';
                        // let badge = 'Pending';
                        // let badgeClass = 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300';

                        // if (st === 'C') {
                        //     dot = 'bg-green-500';
                        //     badge = 'Completed';
                        //     badgeClass = 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
                        // } else if (st === 'A') {
                        //     dot = 'bg-blue-500';
                        //     badge = 'Assigned';
                        //     badgeClass = 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                        // } else if (st === 'S') {
                        //     dot = 'bg-indigo-500';
                        //     badge = 'Submitted';
                        //     badgeClass = 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400';
                        // } else if (st === 'P') {
                        //     dot = 'bg-yellow-500';
                        //     badge = 'In Progress';
                        //     badgeClass = 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400';
                        // } else if (st === 'R') {
                        //     dot = 'bg-red-500';
                        //     badge = 'Rejected';
                        //     badgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400';
                        // }

                        let dot = 'bg-gray-400';
                        let badgeClass =
                            'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300';

                        if (st === 'C') {
                            dot = 'bg-green-500';
                            badgeClass =
                                'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400';
                        } else if (st === 'A') {
                            dot = 'bg-blue-500';
                            badgeClass =
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400';
                        } else if (st === 'S') {
                            dot = 'bg-indigo-500';
                            badgeClass =
                                'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-400';
                        } else if (st === 'P') {
                            dot = 'bg-yellow-500';
                            badgeClass =
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-400';
                        } else if (st === 'R') {
                            dot = 'bg-red-500';
                            badgeClass =
                                'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400';
                        }

                        /* 🔥 USE BACKEND LABEL */
                        const badge = step.status_label || 'Pending';

                        const name = step.by || '-';
                        const initials = name !== '-' ?
                            name.split(' ').map(n => n[0]).join('').slice(0, 2)
                            .toUpperCase() :
                            '?';

                        html += `
                            <div class="flex items-start justify-between py-3">

                                <!-- LEFT -->
                                <div class="flex items-start gap-3">

                                    <!-- DOT -->
                                    <div class="mt-2 h-2.5 w-2.5 rounded-full ${dot}"></div>

                                    <!-- AVATAR -->
                                    <div class="h-8 w-8 flex items-center justify-center
                                                rounded-full bg-gray-100 dark:bg-gray-800
                                                text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        ${initials}
                                    </div>

                                    <!-- TEXT -->
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800 dark:text-white">
                                            ${step.title}
                                        </div>

                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            ${name}
                                        </div>
                                    </div>

                                </div>

                                <!-- RIGHT -->
                                <div class="text-right">

                                    <div class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold ${badgeClass}">
                                        ${step.status_label || badge}
                                    </div>

                                    <div class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">
                                        ${step.at || '-'}
                                    </div>

                                </div>

                            </div>
                        `;
                    });

                    html += `
                            </div>
                        </div>
                    `;
                }

                $('#trackingContent').html(html);
            });

        });


        /*
        |------------------------------------------
        | CLOSE MODAL
        |------------------------------------------
        */
        $('#closeTracking').click(function() {
            $('#trackingModal').addClass('hidden').removeClass('flex');
        });

        $('#trackingModal').click(function(e) {
            if (e.target.id === 'trackingModal') {
                $(this).addClass('hidden').removeClass('flex');
            }
        });

    });
</script>
