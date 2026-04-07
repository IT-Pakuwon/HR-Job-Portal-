<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-6">

            <!-- Date From -->
            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="op_date_from" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="op_date_to" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- WO -->
            <div>
                <label class="text-xs text-gray-500">WO Number</label>
                <input type="text" id="op_woid" placeholder="WO-xxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Department -->
            <div>
                <label class="text-xs text-gray-500">Department</label>
                <input type="text" id="op_department"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- ACTION -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="opFilter" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Apply
                </button>

                <button id="opReset" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="opExport"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">
                    Export
                </button>

            </div>

        </div>

    </div>

    <!-- TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-800">
                Operational Report
            </h2>

            <p class="text-xs text-gray-500">
                Work Order monitoring with SPB & SPPBJKT tracking
            </p>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="opTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500">
                    <tr>
                        <th></th>
                        <th>Date</th>
                        <th>WO No</th>
                        <th>Department</th>
                        <th>Requester</th>
                        <th>PIC WO</th>
                        <th>PIC Department</th>
                        <th>Document Status</th>
                        <th>WO Status</th>
                        <th class="text-center">SPB</th>
                        <th class="text-center">SPPBJKT</th>
                        {{-- <th class="text-center">Duration</th> --}}
                        {{-- <th class="text-right">Cost</th> --}}
                        {{-- <th>Budget Use</th> --}}
                        <th>Budget Info</th>
                        <th>Description</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        var table = $('#opTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            ajax: {
                url: "{{ route('report.operational.json') }}",
                data: function(d) {
                    d.date_from = $('#op_date_from').val();
                    d.date_to = $('#op_date_to').val();
                    d.woid = $('#op_woid').val();
                    d.department = $('#op_department').val();
                }
            },

            columns: [

                {
                    data: null,
                    className: 'dtr-control',
                    orderable: false
                },

                {
                    data: 'date'
                },
                {
                    data: 'woid'
                },
                {
                    data: 'department_name'
                },

                {
                    data: 'requester'
                },
                {
                    data: 'pic_wo_name'
                },
                {
                    data: 'pic_department_name'
                },
                {
                    data: 'doc_status',
                    render: function(data) {

                        let color = {
                            'Pending': 'bg-yellow-100 text-yellow-700',
                            'Completed': 'bg-green-100 text-green-700',
                            'Cancelled': 'bg-red-100 text-red-700',
                            'Rejected': 'bg-red-100 text-red-700',
                        };

                        return `<span class="px-2 py-1 text-xs rounded ${color[data] ?? 'bg-gray-100'}">${data}</span>`;
                    }
                },
                {
                    data: 'work_status',
                    render: function(data) {

                        let color = {
                            'Progress': 'bg-blue-100 text-blue-700',
                            'On Hold': 'bg-orange-100 text-orange-700',
                            'Done': 'bg-green-100 text-green-700',
                            'Cancelled': 'bg-red-100 text-red-700',
                        };

                        return `<span class="px-2 py-1 text-xs rounded ${color[data] ?? 'bg-gray-100'}">${data}</span>`;
                    }
                },
                {
                    data: 'spb_list',
                    className: 'text-center',
                    render: function(data) {

                        if (!data) return '-';

                        let items = data.split(',');

                        return items.map(doc => {
                            doc = doc.trim();

                            return `<a href="/showspbs/${doc}"
                        target="_blank"
                        class="text-blue-600 hover:underline block">
                        ${doc}
                    </a>`;
                        }).join('');
                    }
                },
                {
                    data: 'sppbjkt_list',
                    className: 'text-center',
                    render: function(data, type, row) {

                        if (!data) return '-';

                        let items = data.split(',');

                        return items.map(doc => {
                            doc = doc.trim();

                            let url = '#';

                            if (doc.startsWith('SPPB')) {
                                url = '/showsppbs/' + doc;
                            } else if (doc.startsWith('SPPJ')) {
                                url = '/showsppjs/' + doc;
                            } else if (doc.startsWith('SPPT')) {
                                url = '/showsppts/' + doc;
                            }

                            return `<a href="${url}"
                        target="_blank"
                        class="text-indigo-600 hover:underline block">
                        ${doc}
                    </a>`;
                        }).join('');
                    }
                },
                // {
                //     data: 'budget_user'
                // },
                // {
                //     data: 'duration',
                //     className: 'text-center'
                // },

                // {
                //     data: 'biaya_wo',
                //     className: 'text-right',
                //     render: function(data) {
                //         if (!data) return '-';
                //         return parseFloat(data).toLocaleString('en-US', {
                //             minimumFractionDigits: 2,
                //             maximumFractionDigits: 2
                //         });
                //     }
                // },
                {
                    data: 'budget_info',
                    className: 'text-left align-top',
                    render: function(data, type, row) {

                        // 🔥 normalize values
                        const dept = data?.dept && data.dept !== '-' ? data.dept : null;
                        const account = data?.account && data.account !== '-' ? data.account :
                            null;
                        const activity = data?.activity && data.activity !== '-' ? data
                            .activity : null;

                        // 🔥 EMPTY STATE (NOW CORRECT)
                        if (!dept && !account && !activity) {
                            return `
                <div class="text-left leading-tight w-full">
                    ${row.budget_user ? `
                        <div class="text-xs text-gray-700">
                            👤 ${row.budget_user}
                        </div>
                    ` : ''}
                    <div class="text-[11px] text-gray-400 italic">
                        No budget assigned
                    </div>
                </div>
            `;
                        }

                        return `
            <div class="text-left leading-tight space-y-1 w-full">

                ${dept ? `
                    <div class="font-semibold text-gray-800 text-xs">
                        🏢 ${dept}
                    </div>
                ` : ''}

                ${row.budget_user ? `
                    <div class="text-[11px] text-green-600">
                        👤 ${row.budget_user}
                    </div>
                ` : ''}

                ${account ? `
                    <div class="text-[11px] text-gray-500">
                        <span class="font-medium">Acc:</span> ${account}
                    </div>
                ` : ''}

                ${activity ? `
                    <div class="text-[11px] text-indigo-600 break-words whitespace-normal">
                        ${activity}
                    </div>
                ` : ''}

            </div>
        `;
                    }
                },

                {
                    data: 'keperluan'
                }

            ],

            order: [
                [1, 'desc']
            ]

        });

        $('#opFilter').click(() => table.ajax.reload());

        $('#opReset').click(function() {
            $('#op_date_from, #op_date_to, #op_woid, #op_department').val('');
            table.ajax.reload();
        });

        $('#opExport').click(function() {

            let url = "{{ route('report.operational.export') }}";

            url += "?date_from=" + $('#op_date_from').val();
            url += "&date_to=" + $('#op_date_to').val();
            url += "&woid=" + $('#op_woid').val();
            url += "&department=" + $('#op_department').val();

            window.location.href = url;

        });

    });
</script>
