<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/60">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-8">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Date From</label>
                <input type="date" id="fa_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Date To</label>
                <input type="date" id="fa_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- STTB / Receipt No -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">STTB</label>
                <input type="text" id="fa_receiptnbr" placeholder="GRxxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- PO No -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">PO</label>
                <input type="text" id="fa_ponbr" placeholder="xxxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- SPPB -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">SPPB</label>
                <input type="text" id="fa_sppb" placeholder="PBxxxx"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Department -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Department</label>
                <input type="text" id="fa_department"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Vendor -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Vendor</label>
                <input type="text" id="fa_vendor"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Inventory Code / Name -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Inventory Code / Name</label>
                <input type="text" id="fa_inventory"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
            </div>

            <!-- Status -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">Status</label>
                <select id="fa_status"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">
                    <option value="">All Status</option>
                    <option value="Full Received">Full Received</option>
                    <option value="Partial Received">Partial Received</option>
                </select>
            </div>

            <!-- ACTION -->
            <div class="flex items-end gap-2 md:col-span-3">

                <button id="faFilter" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Apply
                </button>

                <button id="faReset"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700">
                    Reset
                </button>

                <button id="faExport"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700">
                    Export
                </button>

            </div>

        </div>

    </div>

    <!-- TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-800">
                Fixed Asset Receipt Report
            </h2>

            <p class="text-xs text-gray-500">
                Fixed asset receipt detail & receiving status
            </p>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="faTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-3 py-3 text-left">STTB</th>
                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">Type</th>
                        <th class="px-3 py-3 text-left">PO</th>
                        <th class="px-3 py-3 text-left">Ref Receipt</th>
                        <th class="px-3 py-3 text-left">Cpny</th>
                        <th class="px-3 py-3 text-left">SPPB</th>
                        <th class="px-3 py-3 text-left">Department</th>
                        <th class="px-3 py-3 text-left">User Peminta</th>
                        <th class="px-3 py-3 text-left">Vendor</th>
                        <th class="px-3 py-3 text-left">Vendor Name</th>
                        <th class="px-3 py-3 text-left">Item Sub Type</th>
                        <th class="px-3 py-3 text-left">Item Category</th>
                        <th class="px-3 py-3 text-left">Inventory Code</th>
                        <th class="px-3 py-3 text-left">Inventory Name</th>
                        <th class="px-3 py-3 text-right">Qty PO</th>
                        <th class="px-3 py-3 text-right">Qty STTB</th>
                        <th class="px-3 py-3 text-left">UOM</th>
                        <th class="px-3 py-3 text-left">Status</th>
                    </tr>
                </thead>

            </table>

        </div>

    </div>

</div>

<script>
    $(function() {

        var table = $('#faTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,
            searching: false,

            dom: "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                "rt" +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],

            pageLength: 10,

            ajax: {
                url: "{{ route('report.fa.json') }}",
                data: function(d) {
                    d.date_from  = $('#fa_date_from').val();
                    d.date_to    = $('#fa_date_to').val();
                    d.receiptnbr = $('#fa_receiptnbr').val();
                    d.ponbr      = $('#fa_ponbr').val();
                    d.sppb       = $('#fa_sppb').val();
                    d.department = $('#fa_department').val();
                    d.vendor     = $('#fa_vendor').val();
                    d.inventory  = $('#fa_inventory').val();
                    d.status     = $('#fa_status').val();
                }
            },

            columns: [
                { data: 'receiptnbr', className: 'px-3' },
                { data: 'receipt_date_fmt', className: 'px-3' },
                { data: 'receipttype', className: 'px-3' },
                { data: 'ponbr', className: 'px-3' },
                { data: 'ref_receiptnbr', className: 'px-3' },
                { data: 'cpny_id', className: 'px-3' },
                { data: 'sppbjktid', className: 'px-3' },
                { data: 'department_id', className: 'px-3' },
                { data: 'user_peminta', className: 'px-3' },
                { data: 'vendorid', className: 'px-3' },
                { data: 'vendorname', className: 'px-3' },
                { data: 'inventory_sub_type', className: 'px-3' },
                { data: 'inventory_category', className: 'px-3' },
                { data: 'inventoryid', className: 'px-3' },
                { data: 'inventory_descr', className: 'px-3' },
                { data: 'qty_po', className: 'px-3 text-right' },
                { data: 'qty_sttb', className: 'px-3 text-right' },
                { data: 'uom', className: 'px-3' },
                {
                    data: 'status',
                    className: 'px-3',
                    render: function(data) {
                        let map = {
                            'Full Received':    'bg-green-100 text-green-700',
                            'Partial Received': 'bg-yellow-100 text-yellow-700',
                            'ERR':              'bg-red-100 text-red-700',
                        };
                        let cls = map[data] ?? 'bg-gray-100 text-gray-600';
                        return `<span class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-medium ${cls}">${data}</span>`;
                    }
                },
            ],

            order: [[0, 'asc']]

        });

        $('#faFilter').click(() => table.ajax.reload());

        $('#faReset').click(function() {
            $('#fa_date_from, #fa_date_to, #fa_receiptnbr, #fa_ponbr, #fa_sppb, #fa_department, #fa_vendor, #fa_inventory').val('');
            $('#fa_status').val('');
            table.ajax.reload();
        });

        $('#faExport').click(function() {

            let url = "{{ route('report.fa.export') }}";

            url += '?date_from='  + encodeURIComponent($('#fa_date_from').val());
            url += '&date_to='    + encodeURIComponent($('#fa_date_to').val());
            url += '&receiptnbr=' + encodeURIComponent($('#fa_receiptnbr').val());
            url += '&ponbr='      + encodeURIComponent($('#fa_ponbr').val());
            url += '&sppb='       + encodeURIComponent($('#fa_sppb').val());
            url += '&department=' + encodeURIComponent($('#fa_department').val());
            url += '&vendor='     + encodeURIComponent($('#fa_vendor').val());
            url += '&inventory='  + encodeURIComponent($('#fa_inventory').val());
            url += '&status='     + encodeURIComponent($('#fa_status').val());

            window.location.href = url;
        });

    });
</script>
