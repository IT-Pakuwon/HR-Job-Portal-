<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        <div class="grid-col-1 grid gap-6 xl:grid-cols-4 xl:grid-rows-1">
            <button>
                <a href="#" class="scope-filter" data-scope="receiptjobs">
                    <div class="flex items-center gap-4 rounded-lg border border-orange-700 bg-orange-200/20 p-3 text-orange-600">
                        <span class="text-xl">📦</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Receipt Jobs</p>
                            <p class="text-right text-xl font-extrabold">{{ $receiptjobs }}</p>
                        </div>
                    </div>
                </a>
            </button>

            <button>
                <a href="#" class="scope-filter" data-scope="onprogress">
                    <div class="flex items-center gap-4 rounded-lg border border-blue-700 bg-blue-200/20 p-3 text-blue-600">
                        <span class="text-xl">⏳</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">On Progress</p>
                            <p class="text-right text-xl font-extrabold">{{ $onProgress }}</p>
                        </div>
                    </div>
                </a>
            </button>

            <button>
                <a href="#" class="scope-filter" data-scope="completed">
                    <div class="flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-600">
                        <span class="text-xl">✅</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">Completed</p>
                            <p class="text-right text-xl font-extrabold">{{ $completed }}</p>
                        </div>
                    </div>
                </a>
            </button>

            <button>
                <a href="#" class="scope-filter" data-scope="all">
                    <div class="flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-600 dark:border-white dark:text-white">
                        <span class="text-xl">🧾</span>
                        <div class="flex flex-grow items-center justify-between">
                            <p class="text-lg font-medium">All</p>
                            <p class="text-right text-xl font-extrabold">{{ $all }}</p>
                        </div>
                    </div>
                </a>
            </button>
        </div>

        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800">
                <div class="flex flex-col items-start justify-between gap-4 border-b border-gray-200 p-4 sm:flex-row sm:items-center dark:border-gray-700">
                    <h1 class="text-xl font-extrabold text-gray-700 dark:text-white">Receipt</h1>
                </div>

                <div class="overflow-x-auto p-6">
                    <table id="receiptTable" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr id="thead-row"></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>
            </div>

            <script>
            $(function () {
                let scope = 'receiptjobs';
                const $title = $('h1.text-xl.font-extrabold');
                const $thead = $('#receiptTable thead');
                const $theadRow = $('#thead-row');
                let table;

                const titleMap = {
                    receiptjobs: 'Receipt - Jobs',
                    onprogress:  'Receipt - On Progress',
                    completed:   'Receipt - Completed',
                    all:         'Receipt - All',
                };

                function headerFor(sc){
                    if(sc === 'receiptjobs'){
                        return `
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">+</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">PO Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Vendor</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Delivery Date</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                        `;
                    }
                    // TrReceipt scopes
                    return `
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">+</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Receipt Nbr</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Receipt Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">PO Nbr</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">SPPB/J/K/T</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Created By</th>
                    `;
                }

                function columnsFor(sc){
                    if(sc === 'receiptjobs'){
                        return [
                            { data: null, orderable:false, searchable:false,
                              render: (_v,t,row)=>renderPlusCreate(row) },
                            { data: 'ponbr' },
                            { data: 'podate', render: (_v,_t,row)=> row.podate_fmt ?? '' , className:'text-center'},
                            { data: 'cpny_id', className:'text-center' },
                            { data: 'vendorname' },
                            { data: 'podeliverydate', render: (_v,_t,row)=> row.podelivery_fmt ?? '', className:'text-center' },
                            { data: 'created_by_name' },
                        ];
                    }
                    // TrReceipt scopes
                    return [
                        { data: null, orderable:false, searchable:false,
                          render: (_v,t,row)=>renderPlusCreate(row) },
                        { data: 'receiptnbr' },
                        { data: 'receiptdate', render: (_v,_t,row)=> row.receiptdate_fmt ?? '', className:'text-center' },
                        { data: 'ponbr' },
                        { data: 'sppbjktid' },
                        { data: 'cpny_id', className:'text-center' },
                        { data: 'created_by' },
                    ];
                }

                function orderFor(sc){
                    // urutkan berdasarkan kolom tanggal (idx 2) desc, lalu kolom kunci (idx 1) desc
                    return [[2,'desc'], [1,'desc']];
                }

                function updateTitle(sc){ $title.text(titleMap[sc] ?? 'Receipt'); }
                function highlightActive(sc){
                    $('.scope-filter').removeClass('ring-2 ring-offset-2 ring-indigo-500')
                        .each(function(){
                            if($(this).data('scope')===sc) $(this).addClass('ring-2 ring-offset-2 ring-indigo-500');
                        });
                }

                function resetThead(sc){
                    // reset total thead agar DT nggak keep header lama
                    $thead.empty().append('<tr id="thead-row"></tr>');
                    $('#thead-row').html(headerFor(sc));
                }

                function rebuild(sc){
                    // destroy & bersihkan DT sepenuhnya
                    if ($.fn.DataTable.isDataTable('#receiptTable')) {
                        $('#receiptTable').DataTable().clear().destroy();
                    }
                    // reset header sesuai scope
                    resetThead(sc);

                    // init ulang
                    table = $('#receiptTable').DataTable({
                        processing: true,
                        serverSide: true,
                        deferRender: true,
                        pageLength: 25,
                        lengthMenu: [10,25,50,100,250],
                        order: orderFor(sc),
                        ajax: {
                            url: "{{ route('receiptlist.json') }}",
                            type: "GET",
                            data: function(d){ d.scope = sc; }
                        },
                        columns: columnsFor(sc),
                        searchDelay: 400,
                        stateSave: false,
                        responsive: true
                    });
                }

                function renderPlusCreate(row){
                    // tetap gunakan ponbr untuk memulai receipt dari PO
                    const url = `{{ route('receipt.create') }}` + `?ponbr=${encodeURIComponent(row.ponbr_eid ?? '')}`;
                    return `<a href="${url}" class="inline-flex items-center justify-center rounded bg-indigo-600 px-2 py-1 text-white text-sm font-bold hover:bg-indigo-700">+</a>`;
                }

                // init awal
                updateTitle(scope);
                highlightActive(scope);
                rebuild(scope);

                // ganti scope
                $('.scope-filter').on('click', function(e){
                    e.preventDefault();
                    scope = $(this).data('scope') || 'receiptjobs';
                    updateTitle(scope);
                    highlightActive(scope);
                    rebuild(scope);
                });
            });
            </script>
        </div>
    </div>
</x-app-layout>
