<div class="space-y-4">
    <form id="inquiryFilters" class="grid grid-cols-1 items-end gap-4 rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800 sm:grid-cols-2 xl:grid-cols-6">
        <label class="min-w-0 text-xs text-gray-500 dark:text-gray-400">Date From
            <input type="date" name="date_from" class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900">
        </label>
        <label class="min-w-0 text-xs text-gray-500 dark:text-gray-400">Date To
            <input type="date" name="date_to" class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900">
        </label>
        <label class="min-w-0 text-xs text-gray-500 dark:text-gray-400">Inventory ID
            <select id="inquiryInventory" name="inventoryid" class="mt-2 w-full"><option value=""></option></select>
        </label>
        <label class="min-w-0 text-xs text-gray-500 dark:text-gray-400">Company
            <select id="inquiryCompany" name="cpny_id" class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">All Company</option>
                @foreach ($inquiryCompanies as $company)
                    <option value="{{ $company }}">{{ $company }}</option>
                @endforeach
            </select>
        </label>
        <label class="min-w-0 text-xs text-gray-500 dark:text-gray-400">Business Unit
            <select id="inquiryBusinessUnit" name="business_unit" class="mt-2 w-full rounded-md border-gray-300 text-sm dark:border-gray-600 dark:bg-gray-900">
                <option value="">All Business Unit</option>
            </select>
        </label>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white">Apply</button>
            <button type="reset" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700">Reset</button>
            <button type="button" id="inquiryExport" class="rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm text-emerald-700">Export</button>
        </div>
    </form>

    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-800">
        <header class="border-b border-gray-200 px-5 py-4 dark:border-gray-700">
            <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Inventory Inquiry</h2>
        </header>
        <div class="overflow-x-auto p-5">
            <table id="inquiryTable" class="w-full text-sm text-gray-700 dark:text-gray-300">
                <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">
                    <tr>
                        @foreach (['Doc ID', 'Doc Date', 'Reference No', 'Company', 'Business Unit', 'Type', 'Qty', 'UoM', 'Unit Cost', 'Note'] as $heading)
                            <th class="px-3 py-3 text-left">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
            </table>
        </div>
    </section>
</div>

<script>
    $(function () {
        const form = $('#inquiryFilters');
        const businessUnits = @json($inquiryBusinessUnits);
        let table;
        let appliedFilters = {};

        function updateBusinessUnits() {
            const company = $('#inquiryCompany').val();
            const select = $('#inquiryBusinessUnit').empty().append(new Option('All Business Unit', ''));
            const values = new Set();
            businessUnits.filter(row => !company || row.cpny_id === company).forEach(row => {
                if (!values.has(row.business_unit)) {
                    select.append(new Option(row.business_unit, row.business_unit));
                    values.add(row.business_unit);
                }
            });
        }

        function initializeTable() {
            if (table) {
                table.columns.adjust().responsive.recalc();
                return;
            }
            const text = $.fn.dataTable.render.text();
            table = $('#inquiryTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                searching: false,
                pageLength: 10,
                order: [[1, 'desc'], [0, 'desc']],
                ajax: {
                    url: "{{ route('report.warehouse.json') }}",
                    data: function (data) { Object.assign(data, appliedFilters, { report: 'inquiry' }); }
                },
                columns: [
                    { data: 'docid', render: text },
                    { data: 'docdate', render: text },
                    { data: 'refnbr', defaultContent: '', render: text },
                    { data: 'cpny_id', render: text },
                    { data: 'business_unit', defaultContent: '', render: text },
                    { data: 'doctype', render: text },
                    { data: 'qty', className: 'text-right', render: $.fn.dataTable.render.number(',', '.', 3) },
                    { data: 'uom', defaultContent: '', render: text },
                    { data: 'unitcost', className: 'text-right', render: $.fn.dataTable.render.number(',', '.', 2) },
                    { data: 'note', defaultContent: '', render: text }
                ]
            });
        }

        $('#inquiryInventory').select2({
            placeholder: 'Search Inventory...',
            allowClear: true,
            width: '100%',
            ajax: {
                url: "{{ route('inventory.search') }}",
                dataType: 'json',
                delay: 300,
                data: params => ({ q: params.term }),
                processResults: data => ({ results: data.map(item => ({
                    id: item.inventoryid, text: item.inventoryid + ' - ' + item.inventory_descr
                })) })
            }
        });
        updateBusinessUnits();
        $('#inquiryCompany').on('change', updateBusinessUnits);
        $(document).on('click', '.report-filter[data-report="inquiry"]', function () {
            setTimeout(initializeTable, 0);
        });
        form.on('submit', function (event) {
            event.preventDefault();
            appliedFilters = Object.fromEntries(new FormData(this));
            if (table) table.ajax.reload(); else initializeTable();
        });
        form.on('reset', function () {
            setTimeout(function () {
                $('#inquiryInventory').val(null).trigger('change');
                updateBusinessUnits();
                appliedFilters = {};
                if (table) table.ajax.reload();
            }, 0);
        });
        $('#inquiryExport').on('click', function () {
            const params = new URLSearchParams(Object.assign({}, appliedFilters, { report: 'inquiry' }));
            window.open("{{ route('report.warehouse.export') }}?" + params.toString(), '_blank', 'noopener');
        });
    });
</script>
