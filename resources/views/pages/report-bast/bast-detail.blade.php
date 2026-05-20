<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-8">

            <!-- Date From -->
            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="bast_date_from"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="bast_date_to"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- BAST ID -->
            <div>
                <label class="text-xs text-gray-500">BAST No</label>
                <input type="text" id="bast_id" placeholder="BAST-xxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Vendor -->
            <div>
                <label class="text-xs text-gray-500">Vendor</label>
                <input type="text" id="bast_vendor"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- CS No -->
            <div>
                <label class="text-xs text-gray-500">CS No</label>
                <input type="text" id="bast_csid"
                    placeholder="CSxxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- SPPBJKT No -->
            <div>
                <label class="text-xs text-gray-500">SPPBJKT No</label>
                <input type="text" id="bast_sppbjktid"
                    placeholder="SPPBJKTxxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- ACTION -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="bastFilter" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Apply
                </button>

                <button id="bastReset" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="bastExport"
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
                BAST Detail Report
            </h2>

            <p class="text-xs text-gray-500">
                Work completion tracking, vendor performance & rating
            </p>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="bastTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500">
                    <tr>
                        <th>Date</th>
                        <th>CS No</th>
                        <th>SPPBJKT No</th>
                        <th>BQ No</th>
                        <th>BAST No</th>
                        <th>Terms</th>
                        <th>Location</th>
                        <th>Department</th>
                        <th>Requester</th>
                        <th>Vendor</th>
                        <th>Duration</th>
                        <th>Progress</th>
                        <th>Penalty</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Rating</th>
                        <th>Description</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        var table = $('#bastTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            ajax: {
                url: "{{ route('report.bast.json') }}",
                data: function(d) {
                    d.date_from = $('#bast_date_from').val();
                    d.date_to = $('#bast_date_to').val();
                    d.bastid = $('#bast_id').val();
                    d.vendor = $('#bast_vendor').val();

                    d.csid = $('#bast_csid').val();
                    d.sppbjktid = $('#bast_sppbjktid').val();
                }
            },

            columns: [{
                    data: 'date'
                },
                {
                    data: 'csid'
                },
                {
                    data: 'sppbjktid'
                },
                {
                    data: 'bqid'
                },
                {
                    data: 'bastid',
                    render: function(data, type, row) {

                        const hash = row.bastid_eid;

                        if (!hash) {
                            return `<span class="text-gray-400">${data ?? '-'}</span>`;
                        }

                        return `
                         <a href="/showbast/${encodeURIComponent(hash)}"
                            class="inline-flex items-center justify-center rounded-lg bg-gray-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-gray-800">
                                ${data}
                            </a>
                        `;
                    }
                },
                {
                    data: 'terms_name'
                },
                {
                    data: 'location_full',
                    className: 'text-left align-top',
                    createdCell: function(td) {
                        $(td).css({
                            'text-align': 'left',
                            'vertical-align': 'top'
                        });
                    },
                    render: function(data) {

                        if (!data) {
                            return '<span class="text-gray-300 italic block text-left">No location</span>';
                        }

                        return `
            <div class="flex flex-col leading-tight text-left w-full text-left">

                <div class="text-xs font-semibold text-gray-800 text-left">
                    📍 ${data.main}
                </div>

                ${data.sub ? `
                    <div class="text-[11px] text-gray-500 text-left">
                        ${data.sub}
                    </div>
                ` : ''}

            </div>
        `;
                    }
                },
                {
                    data: 'department_name'
                },
                {
                    data: 'requester'
                },
                {
                    data: 'vendorname'
                },

                {
                    data: 'duration',
                    className: 'text-center'
                },
                {
                    data: 'progress_label',
                    className: 'text-center'
                },
                {
                    data: 'penalty_format',
                    className: 'text-right'
                },
                {
                    data: 'bast_amount',
                    className: 'text-right',
                    render: function(data) {
                        if (!data) return '-';
                        return parseFloat(data).toLocaleString('en-US');
                    }
                },
                {
                    data: 'status',
                    render: function(data) {

                        let map = {
                            'C': ['Completed', 'bg-green-100 text-green-700'],
                            'P': ['Pending', 'bg-yellow-100 text-yellow-700'],
                            'X': ['Cancelled', 'bg-red-100 text-red-700']
                        };

                        let s = map[data] ?? [data, 'bg-gray-100'];

                        return `<span class="px-2 py-1 text-xs rounded ${s[1]}">${s[0]}</span>`;
                    }
                },
                {
                    data: 'rating',
                    className: 'text-center',
                    render: function(data) {

                        if (!data) return '<span class="text-gray-300">-</span>';

                        let stars = '';
                        for (let i = 1; i <= 5; i++) {
                            stars += i <= Math.round(data) ? '⭐' : '☆';
                        }

                        return `<div class="text-xs">${stars}<br>${data}</div>`;
                    }
                },

                {
                    data: 'keperluan',
                    className: 'text-left'
                }

            ],

            order: [
                [1, 'desc']
            ]

        });

        $('#bastFilter').click(() => table.ajax.reload());

        $('#bastReset').click(function() {
            $('#bast_date_from, #bast_date_to, #bast_id, #bast_vendor, #bast_csid, #bast_sppbjktid').val('');
            table.ajax.reload();
        });

        $('#bastExport').click(function() {

            let url = "{{ route('report.bast.export') }}";

            url += "?date_from=" + $('#bast_date_from').val();
            url += "&date_to=" + $('#bast_date_to').val();
            url += "&bastid=" + $('#bast_id').val();
            url += "&vendor=" + $('#bast_vendor').val();
            url += "&csid=" + $('#bast_csid').val();
            url += "&sppbjktid=" + $('#bast_sppbjktid').val();

            window.location.href = url;
        });

    });
</script>
