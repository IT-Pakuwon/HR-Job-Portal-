<x-app-layout>
    {{-- (styling card & table sama seperti punyamu, dipersingkat di sini) --}}
    <div class="max-w-9xl mx-auto w-full px-4 py-4 sm:px-6 lg:px-8">

        {{-- Ringkasan (opsional) --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-5 mb-4">
            <div class="rounded-lg border p-3">
                <div class="text-sm font-medium">My CS</div>
                <div id="k-myAll" class="text-2xl font-extrabold">{{ $myAll }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-sm font-medium">Onprogress</div>
                <div id="k-myProgress" class="text-2xl font-extrabold">{{ $myProgress }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-sm font-medium">Rejected</div>
                <div id="k-myRejected" class="text-2xl font-extrabold">{{ $myRejected }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-sm font-medium">Completed</div>
                <div id="k-myCompleted" class="text-2xl font-extrabold">{{ $myCompleted }}</div>
            </div>
            <div class="rounded-lg border p-3">
                <div class="text-sm font-medium">All CS</div>
                <div id="k-all" class="text-2xl font-extrabold">{{ $all }}</div>
            </div>
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

                {{-- Tables --}}
                <div id="tab-my" class="tab-pane block">
                    <table id="tblMy" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold">CSID</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Date</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">User</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="tab-progress" class="tab-pane hidden">
                    <table id="tblProgress" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">CSID</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">User</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="tab-rejected" class="tab-pane hidden">
                    <table id="tblRejected" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">CSID</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">User</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="tab-completed" class="tab-pane hidden">
                    <table id="tblCompleted" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">CSID</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">User</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <div id="tab-all" class="tab-pane hidden">
                    <table id="tblAll" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">CSID</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Date</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Company</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">User</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    {{-- jQuery & DataTables assumed sudah ada di layout utama --}}
    <script>
    $(function(){
        function renderCsid(v, row){
            return `<a href="/showcs/${row.id}" class="inline-flex items-center rounded px-3 py-1.5 bg-blue-600 text-white hover:bg-blue-700 text-sm font-semibold">${row.csid}</a>`;
        }
        function renderDate(v){
            if(!v) return '';
            const d = new Date(v);
            return isNaN(d) ? v : d.toLocaleDateString('id-ID');
        }
        function renderStatus(s){
            const map = { H:'Draft', P:'Onprogress', R:'Rejected', C:'Completed' };
            return map[s] || s || '';
        }
        function actionCol(row){
            return `<div class="flex gap-2">
                <a href="/showcs/${row.id}" class="inline-flex items-center rounded px-3 py-1.5 bg-indigo-600 text-white hover:bg-indigo-700 text-sm font-semibold">Open</a>
            </div>`;
        }

        const commonCols = [
            { data:'csid', className:'text-left',   render:(v,t,row)=>renderCsid(v,row) },
            { data:'csdate', className:'text-center', render:(v)=>renderDate(v) },
            { data:'cpny_id', className:'text-center' },
            { data:'department_id', className:'text-center' },
            { data:'user_peminta', className:'text-left', defaultContent:'-' },
            { data:'status', className:'text-center', render:(v)=>renderStatus(v) },
            { data:null, orderable:false, searchable:false, className:'text-left', render:(_d,_t,row)=>actionCol(row) },
        ];

        const tblMy        = $('#tblMy').DataTable({processing:true,serverSide:true,deferRender:true,pageLength:25,lengthMenu:[10,25,50,100,250], ajax:{url:"{{ route('cslist.my.json') }}",type:"GET"},        order:[[1,'desc'],[0,'desc']], columns: commonCols, searchDelay:400,stateSave:true,responsive:true});
        const tblProgress  = $('#tblProgress').DataTable({processing:true,serverSide:true,deferRender:true,pageLength:25,lengthMenu:[10,25,50,100,250], ajax:{url:"{{ route('cslist.onprogress.json') }}",type:"GET"}, order:[[1,'desc'],[0,'desc']], columns: commonCols, searchDelay:400,stateSave:true,responsive:true});
        const tblRejected  = $('#tblRejected').DataTable({processing:true,serverSide:true,deferRender:true,pageLength:25,lengthMenu:[10,25,50,100,250], ajax:{url:"{{ route('cslist.rejected.json') }}",type:"GET"},   order:[[1,'desc'],[0,'desc']], columns: commonCols, searchDelay:400,stateSave:true,responsive:true});
        const tblCompleted = $('#tblCompleted').DataTable({processing:true,serverSide:true,deferRender:true,pageLength:25,lengthMenu:[10,25,50,100,250], ajax:{url:"{{ route('cslist.completed.json') }}",type:"GET"}, order:[[1,'desc'],[0,'desc']], columns: commonCols, searchDelay:400,stateSave:true,responsive:true});
        const tblAll       = $('#tblAll').DataTable({processing:true,serverSide:true,deferRender:true,pageLength:25,lengthMenu:[10,25,50,100,250], ajax:{url:"{{ route('cslist.all.json') }}",type:"GET"},        order:[[1,'desc'],[0,'desc']], columns: commonCols, searchDelay:400,stateSave:true,responsive:true});

        function setActiveTab(key){
            $('.tab-btn').removeClass('active bg-indigo-50 text-indigo-700');
            $(`.tab-btn[data-tab="${key}"]`).addClass('active bg-indigo-50 text-indigo-700');
            $('.tab-pane').addClass('hidden').removeClass('block');
            $(`#tab-${key}`).removeClass('hidden').addClass('block');
            if (key==='my')        tblMy.columns.adjust();
            if (key==='progress')  tblProgress.columns.adjust();
            if (key==='rejected')  tblRejected.columns.adjust();
            if (key==='completed') tblCompleted.columns.adjust();
            if (key==='all')       tblAll.columns.adjust();

            // refresh counts
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
