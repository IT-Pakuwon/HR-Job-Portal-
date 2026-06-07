<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-8">

            <!-- Date From -->
            <div>
                <label class="text-xs text-gray-500">Date From</label>
                <input type="date" id="fa_date_from"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label class="text-xs text-gray-500">Date To</label>
                <input type="date" id="fa_date_to"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- STTB / Receipt No -->
            <div>
                <label class="text-xs text-gray-500">STTB</label>
                <input type="text" id="fa_receiptnbr" placeholder="GRxxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- PO No -->
            <div>
                <label class="text-xs text-gray-500">PO</label>
                <input type="text" id="fa_ponbr" placeholder="xxxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- SPPB -->
            <div>
                <label class="text-xs text-gray-500">SPPB</label>
                <input type="text" id="fa_sppb" placeholder="PBxxxx"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Department -->
            <div>
                <label class="text-xs text-gray-500">Department</label>
                <input type="text" id="fa_department"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Vendor -->
            <div>
                <label class="text-xs text-gray-500">Vendor</label>
                <input type="text" id="fa_vendor"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Inventory Code / Name -->
            <div>
                <label class="text-xs text-gray-500">Inventory Code / Name</label>
                <input type="text" id="fa_inventory"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
            </div>

            <!-- Status -->
            <div>
                <label class="text-xs text-gray-500">Status</label>
                <select id="fa_status"
                    class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm bg-white">
                    <option value="">All</option>
                    <option value="Full Received">Full Received</option>
                    <option value="Partial Received">Partial Received</option>
                </select>
            </div>

            <!-- ACTION -->
            <div class="flex items-end gap-2 md:col-span-3">

                <button id="faFilter" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white">
                    Apply
                </button>

                <button id="faReset" class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm">
                    Reset
                </button>

                <button id="faExport"
                    class="rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700">
                    Export
                </button>

            </div>

        </div>

    </div>

    <!-- TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div class="border-b px-6 py-4">
            <h2 class="text-sm font-semibold text-gray-800">
                Fixed Asset Receipt Report
            </h2>

            <p class="text-xs text-gray-500">
                Fixed asset receipt detail & receiving status
            </p>
        </div>

        <div class="overflow-x-auto p-5">

            <table id="faTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase text-gray-500">
                    <tr>
                        <th>STTB</th>
                        <th>Date</th>
                        <th>Type</th>
                        <th>PO</th>
                        <th>Ref Receipt</th>
                        <th>Cpny</th>
                        <th>SPPB</th>
                        <th>Department</th>
                        <th>User Peminta</th>
                        <th>Vendor</th>
                        <th>Vendor Name</th>
                        <th>Item Sub Type</th>
                        <th>Item Category</th>
                        <th>Inventory Code</th>
                        <th>Inventory Name</th>
                        <th>Qty PO</th>
                        <th>Qty STTB</th>
                        <th>UOM</th>
                        <th>Status</th>
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
                { data: 'receiptnbr' },
                { data: 'receipt_date_fmt' },
                { data: 'receipttype', className: 'text-center' },
                { data: 'ponbr' },
                { data: 'ref_receiptnbr' },
                { data: 'cpny_id', className: 'text-center' },
                { data: 'sppbjktid' },
                { data: 'department_id' },
                { data: 'user_peminta' },
                { data: 'vendorid' },
                { data: 'vendorname' },
                { data: 'inventory_sub_type' },
                { data: 'inventory_category' },
                { data: 'inventoryid' },
                { data: 'inventory_descr' },
                { data: 'qty_po', className: 'text-center' },
                { data: 'qty_sttb', className: 'text-center' },
                { data: 'uom', className: 'text-center' },
                {
                    data: 'status',
                    render: function(data) {
                        let map = {
                            'Full Received':    'bg-green-100 text-green-700',
                            'Partial Received': 'bg-yellow-100 text-yellow-700',
                            'ERR':              'bg-red-100 text-red-700',
                        };
                        let cls = map[data] ?? 'bg-gray-100 text-gray-600';
                        return `<span class="px-2 py-1 text-xs rounded ${cls}">${data}</span>`;
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
