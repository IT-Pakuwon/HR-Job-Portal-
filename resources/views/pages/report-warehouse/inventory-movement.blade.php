<style>
    /* Match Select2 with your input style */
    .select2-container .select2-selection--single {
        height: 38px !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        padding: 4px 8px !important;
        display: flex;
        align-items: center;
    }

    .select2-selection__rendered {
        line-height: normal !important;
        font-size: 14px;
        color: #374151;
    }

    .select2-selection__arrow {
        height: 100% !important;
    }
</style>
<div class="space-y-4">

    <!-- FILTER PANEL -->
    <div class="rounded-2xl border border-gray-200 bg-gray-50/60 p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-7">

            <!-- Date From -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date From
                </label>
                <input type="date" id="mv_date_from"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Date To
                </label>
                <input type="date" id="mv_date_to"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <!-- Inventory -->
            {{-- <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>
                <input type="text" id="mv_inventoryid" placeholder="Item code"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div> --}}

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Inventory ID
                </label>

                <select id="mv_inventoryid"
                    class="w-full rounded-lg border border-gray-200 bg-white text-sm">
                </select>
            </div>
            <!-- Reference -->
            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Reference No
                </label>
                <input type="text" id="mv_refnbr" placeholder="Ref number"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
            </div>

            <div>
                <label class="mb-1 block text-[11px] font-medium text-gray-500">
                    Transaction Type
                </label>

                <select id="mv_doctype"
                    class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm">

                    <option value="">All</option>
                    <option value="STTB">Receipt</option>
                    <option value="ISSUE">Issue</option>
                    <option value="STTB_RETURN">Return Receipt</option>
                    <option value="ISSUE_RETURN">Return Issue</option>

                </select>
            </div>

            <!-- ACTION BUTTONS -->
            <div class="flex items-end gap-2 md:col-span-2">

                <button id="mvFilterBtn"
                    class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800">
                    Apply
                </button>

                <button id="mvResetBtn"
                    class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Reset
                </button>

                <button id="mvExportBtn"
                    class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700 hover:bg-emerald-100">
                    Export
                </button>

            </div>

        </div>

    </div>


    <!-- REPORT TABLE -->
    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">

        <!-- Header -->
        <div class="border-b border-gray-200 px-6 py-4">

            <h2 class="text-sm font-semibold text-gray-800">
                Inventory Movement Tracking
            </h2>

            <p class="text-xs text-gray-500">
                Complete stock movement (IN / OUT / Balance)
            </p>

        </div>

        <!-- Table -->
        <div class="overflow-x-auto p-5">

            <table id="movementTable" class="min-w-full text-sm text-gray-700">

                <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">

                    <tr>

                        <th class="px-3 py-3 text-left">Date</th>
                        <th class="px-3 py-3 text-left">Posting Month</th>
                        <th class="px-3 py-3 text-left">Document</th>
                        <th class="px-3 py-3 text-left">Type</th>
                        <th class="px-3 py-3 text-left">Reference</th>

                        <th class="px-3 py-3 text-left">Inventory</th>
                        <th class="px-3 py-3 text-left">Description</th>

                        <th class="px-3 py-3 text-right">Qty In</th>
                        <th class="px-3 py-3 text-right">Qty Out</th>
                        <th class="px-3 py-3 text-right">Balance</th>

                        <th class="px-3 py-3 text-left">Warehouse</th>

                    </tr>

                </thead>

            </table>

        </div>

    </div>

</div>


<script>
    $(function() {

        let table = $('#movementTable').DataTable({

            processing: true,
            serverSide: true,
            responsive: true,

            searching: false,

            dom: "<'flex items-center justify-between mb-3'<'text-sm'l>>" +
                "rt" +
                "<'flex items-center justify-between mt-3'<'text-sm'i><'text-sm'p>>",

            pageLength: 10,

            ajax: {
                url: "{{ route('report.warehouse.json') }}",
                data: function(d) {

                    d.report = "movement";

                    d.date_from = $('#mv_date_from').val();
                    d.date_to = $('#mv_date_to').val();
                    d.inventoryid = $('#mv_inventoryid').val();
                    d.refnbr = $('#mv_refnbr').val();
                    d.doctype = $('#mv_doctype').val();

                }
            },

          columns: [

                { data: 'docdate' },
                { data: 'posting_month' },        // 3       // 1
                { data: 'docid' },          // 2
                { data: 'doctype' },
                { data: 'refnbr' },         // 4

                { data: 'inventoryid' },    // 5
                { data: 'inventory_descr' },// 6

                {
                    data: 'qty_in',
                    className: 'text-right text-emerald-600',
                    render: formatNumber
                },

                {
                    data: 'qty_out',
                    className: 'text-right text-red-600',
                    render: formatNumber
                },

                {
                    data: 'end_qty',
                    className: 'text-right font-semibold text-indigo-600',
                    render: formatNumber
                },

                { data: 'siteid' }          // 10

            ],
            order: [
                [0, 'asc']
            ]

        });


        /* APPLY */
        $('#mvFilterBtn').click(function() {
            table.ajax.reload();
        });

        /* RESET */
        $('#mvResetBtn').click(function() {

            $('#mv_date_from').val('');
            $('#mv_date_to').val('');
            $('#mv_inventoryid').val('');
            $('#mv_refnbr').val('');

            table.ajax.reload();

        });

        $('#mv_inventoryid').select2({
            placeholder: 'Search Inventory...',
            allowClear: true,
            width: '100%',

            ajax: {
                url: "{{ route('inventory.search') }}",
                dataType: 'json',
                delay: 300,

                data: function (params) {
                    return {
                        q: params.term // keyword
                    };
                },

                processResults: function (data) {
                    return {
                        results: data.map(item => ({
                            id: item.inventoryid,
                            text: item.inventoryid + ' - ' + item.inventory_descr
                        }))
                    };
                }
            }
        });

        /* EXPORT */
        $('#mvExportBtn').on('click', function () {

            const params = new URLSearchParams({
                date_from: $('#mv_date_from').val(),
                date_to: $('#mv_date_to').val(),
                inventoryid: $('#mv_inventoryid').val(),
                refnbr: $('#mv_refnbr').val(),
                doctype: $('#mv_doctype').val(),
            });

            // redirect with query params
            window.open(`/report-warehouse/export?${params.toString()}`, '_blank');
        });

    });

    function formatNumber(data) {
        return data ? parseFloat(data).toLocaleString() : '';
    }
</script>
