<x-app-layout>
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">
        {{-- Ringkasan --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5 mb-4">
            <div class="rounded-lg border p-3"><div class="text-sm font-medium">My CS</div><div id="k-myAll" class="text-2xl font-extrabold">{{ $myAll }}</div></div>
            <div class="rounded-lg border p-3"><div class="text-sm font-medium">Onprogress</div><div id="k-myProgress" class="text-2xl font-extrabold">{{ $myProgress }}</div></div>
            <div class="rounded-lg border p-3"><div class="text-sm font-medium">Rejected</div><div id="k-myRejected" class="text-2xl font-extrabold">{{ $myRejected }}</div></div>
            <div class="rounded-lg border p-3"><div class="text-sm font-medium">Completed</div><div id="k-myCompleted" class="text-2xl font-extrabold">{{ $myCompleted }}</div></div>
            <div class="rounded-lg border p-3"><div class="text-sm font-medium">All CS</div><div id="k-all" class="text-2xl font-extrabold">{{ $all }}</div></div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-gray-800">
            <div class="flex items-center justify-between border-b p-4">
                <h1 class="text-xl font-extrabold">CS List</h1>
            </div>

            <div class="p-4">
                {{-- Tabs --}}
                <div class="mb-4 border-b">
                    <nav class="flex gap-2">
                        <button class="tab-btn active px-4 py-2 text-sm font-semibold rounded-t bg-indigo-50 text-indigo-700" data-tab="my">My CS</button>
                        <button class="tab-btn px-4 py-2 text-sm font-semibold rounded-t hover:bg-gray-100" data-tab="progress">Onprogress CS</button>
                        <button class="tab-btn px-4 py-2 text-sm font-semibold rounded-t hover:bg-gray-100" data-tab="rejected">Rejected CS</button>
                        <button class="tab-btn px-4 py-2 text-sm font-semibold rounded-t hover:bg-gray-100" data-tab="completed">Completed CS</button>
                        <button class="tab-btn px-4 py-2 text-sm font-semibold rounded-t hover:bg-gray-100" data-tab="all">All CS</button>
                    </nav>
                </div>

                {{-- Table Template --}}
                @foreach (['my'=>'tblMy','progress'=>'tblProgress','rejected'=>'tblRejected','completed'=>'tblCompleted','all'=>'tblAll'] as $tab=>$tbl)
                <div id="tab-{{ $tab }}" class="tab-pane {{ $tab=='my' ? 'block':'hidden' }}">
                    <table id="{{ $tbl }}" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left  text-xs font-semibold">CSID</th>
                                <th class="px-4 py-3 text-left  text-xs font-semibold">SPPB/J/K/T ID</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">CS Date</th>
                                <th class="px-4 py-3 text-left  text-xs font-semibold">User</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                                <th class="px-4 py-3 text-left  text-xs font-semibold">Created By</th>
                                <th class="px-4 py-3 text-left  text-xs font-semibold">Note</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Assign Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Submit Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Days</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <script>
    $(function () {
        function fmtDate(v){
            if(!v) return '';
            const d = new Date(v);
            return Number.isNaN(d.getTime()) ? v : d.toLocaleDateString('id-ID');
        }
        function renderCSBtn(_v,row){
            return `<a href="/showcs/${row.eid}" class="inline-flex items-center rounded px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold">${row.csid ?? ''}</a>`;
        }
        const showMap = { PB:'showsppbs', PJ:'showsppjs', PK:'showsppks', PT:'showsppts' };
        function renderSPPBtn(_v,row){
            const prefix = (row.sppbjkt_prefix || '').toUpperCase();
            const srcId  = row.sppbjkt_src_id;
            const docNo  = row.sppbjktid || '';
            const base   = showMap[prefix];
            if(!prefix || !srcId || !base) return docNo;
            const url = `/${base}/${srcId}`;
            return `<a href="${url}" class="inline-flex items-center rounded px-3 py-1.5 bg-emerald-600 text-white hover:bg-emerald-700 text-sm font-semibold">${docNo}</a>`;
        }
        function renderDays(v){ return (v==null) ? '' : String(v); }

        const commonCols = [
            { data:'csid',         className:'text-left',   render:(_v,t,row)=>renderCSBtn(_v,row) },
            { data:'sppbjktid',    className:'text-left',   render:(v,t,row)=>renderSPPBtn(v,row) },
            { data:'csdate',       className:'text-center', render:(v)=>fmtDate(v) },
            { data:'user_peminta', className:'text-left',   defaultContent:'-' },
            { data:'cpny_id',      className:'text-center' },
            { data:'department_id',className:'text-center' },
            { data:'created_by',   className:'text-left' },
            { data:'csnote',       className:'text-left',   defaultContent:'-' },
            { data:'assigndate',   className:'text-center', render:(v)=>fmtDate(v) },
            { data:'submitdate',   className:'text-center', render:(v)=>fmtDate(v) },
            { data:'days',         className:'text-center', render:(v)=>renderDays(v) },
        ];

        const opts = {
            processing:true, serverSide:true, deferRender:true,
            pageLength:25, lengthMenu:[10,25,50,100,250],
            order:[[2,'desc'],[0,'desc']],
            columns: commonCols, searchDelay:400, stateSave:true, responsive:true
        };

        const tblMy        = $('#tblMy').DataTable({...opts,      ajax:{url:"{{ route('cslist.my.json') }}", type:"GET"}});
        const tblProgress  = $('#tblProgress').DataTable({...opts,ajax:{url:"{{ route('cslist.onprogress.json') }}", type:"GET"}});
        const tblRejected  = $('#tblRejected').DataTable({...opts,ajax:{url:"{{ route('cslist.rejected.json') }}", type:"GET"}});
        const tblCompleted = $('#tblCompleted').DataTable({...opts,ajax:{url:"{{ route('cslist.completed.json') }}", type:"GET"}});
        const tblAll       = $('#tblAll').DataTable({...opts,     ajax:{url:"{{ route('cslist.all.json') }}", type:"GET"}});

        function setActiveTab(key){
            $('.tab-btn').removeClass('active bg-indigo-50 text-indigo-700');
            $(`.tab-btn[data-tab="${key}"]`).addClass('active bg-indigo-50 text-indigo-700');
            $('.tab-pane').addClass('hidden').removeClass('block');
            $(`#tab-${key}`).removeClass('hidden').addClass('block');
            if (key==='my') tblMy.columns.adjust();
            if (key==='progress') tblProgress.columns.adjust();
            if (key==='rejected') tblRejected.columns.adjust();
            if (key==='completed') tblCompleted.columns.adjust();
            if (key==='all') tblAll.columns.adjust();
            $.get("{{ route('cslist.counts') }}").done(function(r){
                $('#k-myAll').text(r.myAll);
                $('#k-myProgress').text(r.myProgress);
                $('#k-myRejected').text(r.myRejected);
                $('#k-myCompleted').text(r.myCompleted);
                $('#k-all').text(r.all);
            });
        }
        $('.tab-btn').on('click', function(){ setActiveTab($(this).data('tab')); });
        setActiveTab('my');
    });
    </script>
</x-app-layout>
