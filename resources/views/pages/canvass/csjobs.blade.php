<x-app-layout>
    <style>
        /* (biarkan CSS umum Anda) */
        table.dataTable { width: 100% !important; }
        .dataTables_wrapper { width: 100%; }
        /* Kartu filter */
        .filter-card{cursor:pointer;user-select:none;}
        .filter-card.active{outline:2px solid #4f46e5}
    </style>

    {{-- Select2 & Toastr (biarkan seperti sebelumnya) --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- ====== FILTER DATASET (kartu) ====== --}}
        <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-4">
            <div id="btn-mine" class="filter-card flex items-center gap-4 rounded-lg border border-indigo-700 bg-indigo-200/20 p-3 text-indigo-700">
                <span class="text-xl">🗂️</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">CS Jobs</p>
                    <p id="count-mine" class="text-right text-xl font-extrabold">{{ $mine }}</p>
                </div>
            </div>

            <div id="btn-revision" class="filter-card flex items-center gap-4 rounded-lg border border-amber-700 bg-amber-200/20 p-3 text-amber-700">
                <span class="text-xl">📝</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">CS Revision</p>
                    <p id="count-revision" class="text-right text-xl font-extrabold">{{ $revision }}</p>
                </div>
            </div>

            <div id="btn-all" class="filter-card flex items-center gap-4 rounded-lg border border-gray-700 bg-gray-200/20 p-3 text-gray-700 dark:border-white dark:text-white">
                <span class="text-xl">🌐</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">All CS Jobs</p>
                    <p id="count-all" class="text-right text-xl font-extrabold">{{ $all }}</p>
                </div>
            </div>

            <div id="btn-sppbjkt" class="filter-card flex items-center gap-4 rounded-lg border border-green-700 bg-green-200/20 p-3 text-green-700">
                <span class="text-xl">🚦</span>
                <div class="flex flex-grow items-center justify-between">
                    <p class="text-lg font-medium">SPPBJKT IN Progress</p>
                    <p id="count-sppbjkt" class="text-right text-xl font-extrabold">{{ $sppbjkt }}</p>
                </div>
            </div>
        </div>

        {{-- ====== PANES (tanpa tab) ====== --}}
        <div class="grid">
            <div class="mt-6 rounded-2xl bg-white dark:bg-gray-800 p-6">

                {{-- === PANE: CS Jobs + Entry CS (dua tabel) === --}}
                <div id="pane-mine">
                    <h2 class="mb-2 text-xl font-semibold">CS Jobs</h2>
                    <table id="tblMine" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="w-32 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Purchasing</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign By</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>

                    <div class="mt-10">
                        <h2 class="mb-2 text-xl font-semibold">Entry CS (My CS)</h2>
                        <table id="tblEntryCS" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">CSID</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">User Peminta</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Note</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                        </table>
                    </div>
                </div>

                {{-- === PANE: My Revision === --}}
                <div id="pane-revision" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">My Revision</h2>
                    <table id="tblRevision" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="w-2 px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Purchasing</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign By</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>

                {{-- === PANE: All Jobs === --}}
                <div id="pane-all" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">All Jobs</h2>
                    <table id="tblAll" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Purchasing</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign By</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>

                {{-- === PANE: SPPBJKT IN Progress === --}}
                <div id="pane-sppbjkt" class="hidden">
                    <h2 class="mb-2 text-xl font-semibold">SPPBJKT IN Progress</h2>
                    <table id="tblSppbjkt" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">DocID</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Company</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign Purchasing</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Assign By</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Description</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-800"></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <script>
    $(function () {
        // ===== renderer util (sama seperti sebelumnya) =====
        const mapShowUrl = { SPPB:'showsppbs', SPPJ:'showsppjs', SPPK:'showsppks', SPPT:'showsppts' };
        function buildCreateUrl(row){ return `/createcs/${row.doc_type}/${row.eid}`; }
        function renderDocBtn(row){
            const base = mapShowUrl[row.doc_type] || '#';
            return `<a href="/${base}/${row.eid}" class="rounded px-6 py-2 bg-gray-500 text-white hover:bg-gray-700 w-32">${row.doc_no}</a>`;
        }
        function colSetWithoutCreate(){
            return [
                { data:null, className:'text-left', render:(_d,_t,row)=>renderDocBtn(row) },
                { data:'assigndate', className:'text-center', render:v=> v?(isNaN(new Date(v))?v:new Date(v).toLocaleDateString('id-ID')):'' },
                { data:'doc_date',   className:'text-left',   render:v=> v?(isNaN(new Date(v))?v:new Date(v).toLocaleDateString('id-ID')):'' },
                { data:'cpny_id', className:'text-left' },
                { data:'created_by_name', className:'text-left', defaultContent:'-' },
                { data:'assignpurchasing', className:'text-left', defaultContent:'' },
                { data:'assignby', className:'text-left', defaultContent:'' },
                { data:'department_id', className:'text-left' },
                { data:'keperluan', className:'text-left' },
            ];
        }
        function colSetWithCreate(){
            const createCol = {
                data:null, orderable:false, searchable:false, className:'text-left',
                render: (_d,_t,row)=>`<a href="${buildCreateUrl(row)}" class="inline-flex items-center rounded bg-indigo-600 px-6 py-2 text-base font-semibold text-white hover:bg-indigo-700">
                    <i class="fas fa-plus"></i></a>`
            };
            return [createCol, ...colSetWithoutCreate()];
        }

        // ===== Datatables init (tanpa parameter docType) =====
        const tblMine = $('#tblMine').DataTable({
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            ajax:{ url:"{{ route('csjobs.mine.json') }}", type:"GET" },
            order:[[3,'desc'],[1,'desc']], columns: colSetWithCreate(),
            searchDelay:400, stateSave:true, responsive:true
        });

        const tblEntryCS = $('#tblEntryCS').DataTable({
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            ajax:{ url:"{{ route('csjobs.entry.json') }}", type:"GET" },
            order:[[1,'desc'],[0,'desc']],
            columns:[
                { data:'csid', className:'text-left',
                  render:(v,_t,row)=>`<a href="/editcs/${row.eid}" class="rounded px-6 py-2 bg-amber-500 text-white hover:bg-amber-600 text-sm font-semibold">${v}</a>` },
                { data:'csdate', className:'text-center',
                  render:v=> v?(isNaN(new Date(v))?v:new Date(v).toLocaleDateString('id-ID')):'' },
                { data:'cpny_id', className:'text-center' },
                { data:'department_id', className:'text-center' },
                { data:'user_peminta', className:'text-left', defaultContent:'-' },
                { data:'csnote', className:'text-left', defaultContent:'-' },
            ],
            searchDelay:400, stateSave:true, responsive:true
        });

        const tblAll = $('#tblAll').DataTable({
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            ajax:{ url:"{{ route('csjobs.all.json') }}", type:"GET" },
            order:[[2,'desc'],[0,'desc']], columns: colSetWithoutCreate(),
            searchDelay:400, stateSave:true, responsive:true
        });

        const tblRevision = $('#tblRevision').DataTable({
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            ajax:{ url:"{{ route('csjobs.revision.json') }}", type:"GET" },
            order:[[3,'desc'],[1,'desc']], columns: colSetWithCreate(),
            searchDelay:400, stateSave:true, responsive:true
        });

        const tblSppbjkt = $('#tblSppbjkt').DataTable({
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            ajax:{ url:"{{ route('csjobs.sppbjkt.progress.json') }}", type:"GET" },
            order:[[2,'desc'],[0,'desc']], columns: colSetWithoutCreate(),
            searchDelay:400, stateSave:true, responsive:true
        });

        // ===== Switching panes via cards =====
        function showPane(key){
            $('#pane-mine, #pane-revision, #pane-all, #pane-sppbjkt').addClass('hidden');
            $(`#pane-${key}`).removeClass('hidden');

            // tampilkan Entry CS hanya di CS Jobs
            if (key === 'mine') {
                $('#pane-mine').find('#tblMine').DataTable().columns.adjust();
                $('#pane-mine').find('#tblEntryCS').DataTable().columns.adjust();
            } else if (key === 'revision') {
                tblRevision.columns.adjust();
            } else if (key === 'all') {
                tblAll.columns.adjust();
            } else if (key === 'sppbjkt') {
                tblSppbjkt.columns.adjust();
            }

            // highlight kartu aktif
            $('.filter-card').removeClass('active');
            $(`#btn-${key}`).addClass('active');
        }

        // default ke CS Jobs (mine)
        showPane('mine');

        $('#btn-mine').on('click', ()=> showPane('mine'));
        $('#btn-revision').on('click', ()=> showPane('revision'));
        $('#btn-all').on('click', ()=> showPane('all'));
        $('#btn-sppbjkt').on('click', ()=> showPane('sppbjkt'));

        // (Opsional) refresh counts
        function refreshCounts(){
            $.get("{{ route('csjobs.dataset.counts') }}")
            .done(res=>{
                $('#count-mine').text(res.mine);
                $('#count-revision').text(res.revision);
                $('#count-all').text(res.all);
                $('#count-sppbjkt').text(res.sppbjkt);
            });
        }
        // refreshCounts(); // panggil bila diperlukan
    });
    </script>
</x-app-layout>
