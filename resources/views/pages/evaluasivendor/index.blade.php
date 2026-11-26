<x-app-layout>
    <x-spp-list-styles />
    <div class="max-w-9xl mx-auto w-full p-2">
        @php
            $tabs = [
                'jobs' => ['Vendor Jobs', '📄', 'blue'],
                'onprogress' => ['On Progress', '⏳', 'yellow'],
                'rejected' => ['Rejected', '⛔️', 'red'],
                'revised' => ['Revised', '✏️', 'blue'],
                'completed' => ['Completed', '✅', 'green'],
                'all' => ['All', '📄', 'all'],
            ];
            $statusCards = [];
            foreach ($tabs as $scope => [$label, $icon, $tone]) {
                $statusCards[] = [['data-scope' => $scope, 'aria-selected' => $scope === 'jobs' ? 'true' : 'false'], $label, $icon, $tone, number_format($stats[$scope]), $scope === 'jobs', null, 'evaluation-tab', true];
            }
        @endphp
        <x-status-filter-cards :custom="$statusCards" />

        <div class="dt-pretty mt-2 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <x-list-page-header title="Penilaian Vendor — Jobs" subtitle="Pilih pekerjaan SPK atau kontrak untuk membuat penilaian vendor." title-id="evaluation-title">
                <form id="evaluation-filters" class="flex flex-wrap items-center gap-3">
                    <div class="sppb-f relative w-56"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg></span>
                        <select name="vendor" aria-label="Vendor" class="w-full">
                            <option value="">All Vendor</option>
                            @foreach($vendors as $vendor)<option value="{{ $vendor }}">{{ $vendor }}</option>@endforeach
                        </select>
                    </div>
                    <div class="sppb-f relative w-44"><span class="pointer-events-none absolute inset-y-0 left-0 z-10 flex w-9 items-center justify-center text-gray-400"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M7 3h5a1.99 1.99 0 011.414.586l7 7a2 2 0 010 2.828l-5 5a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z" /></svg></span>
                        <select name="source_type" aria-label="Type" class="w-full">
                            <option value="">SPK & Kontrak</option><option value="SPK">SPK</option><option value="KONTRAK">Kontrak</option>
                        </select>
                    </div>
                    <div class="relative w-40"><input type="date" name="start_date" aria-label="Mulai periode" title="Mulai periode" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"></div>
                    <div class="relative w-40"><input type="date" name="end_date" aria-label="Akhir periode" title="Akhir periode" class="h-10 w-full rounded-lg border border-gray-200 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200"></div>
                    <button type="reset" class="inline-flex h-10 items-center rounded-lg border border-gray-200 bg-white px-4 text-sm font-medium text-gray-600 shadow-sm transition hover:-translate-y-0.5 hover:bg-gray-50 hover:shadow-md active:translate-y-0 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">Reset</button>
                </form>
            </x-list-page-header>
            <p id="list-error" role="alert" class="hidden px-5 py-3 text-sm text-red-600"></p>
            <div class="overflow-x-auto">
                <table id="evaluation-table" class="w-full min-w-full border-separate border-spacing-0 text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:border-white/[0.06] dark:bg-white/[0.02] dark:text-gray-400">
                        <tr class="text-left">
                            <th>Action</th><th>Type</th><th>No. SPK / Kontrak</th><th>Company</th><th>Department</th><th>Vendor</th>
                            <th>Pekerjaan</th><th>Mulai</th><th>Selesai</th><th>Created By</th><th>Status</th><th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <style>#evaluation-table td{vertical-align:top;padding:12px 16px}#evaluation-table th{padding:12px 16px;font-weight:500;text-align:left}</style>
    <script>
        $(function () {
            let scope = 'jobs';
            const filters = document.getElementById('evaluation-filters');
            const escape = value => $('<span>').text(value ?? '').html();
            const text = $.fn.dataTable.render.text();
            const statusColors = {
                'On Progress': ['bg-yellow-50 text-yellow-700 ring-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-300 dark:ring-yellow-500/30', 'bg-yellow-500'],
                'Revised': ['bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-500/30', 'bg-blue-500'],
                'Rejected': ['bg-red-50 text-red-700 ring-red-200 dark:bg-red-500/10 dark:text-red-300 dark:ring-red-500/30', 'bg-red-500'],
                'Completed': ['bg-green-50 text-green-700 ring-green-200 dark:bg-green-500/10 dark:text-green-300 dark:ring-green-500/30', 'bg-green-500']
            };
            const statusBadge = value => {
                const [tone, dot] = statusColors[value] || ['bg-slate-50 text-slate-600 ring-slate-200 dark:bg-white/5 dark:text-slate-300 dark:ring-white/10', 'bg-slate-400'];
                return `<span class="inline-flex min-w-[116px] items-center justify-center gap-2 whitespace-nowrap rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset ${tone}"><span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${escape(value)}</span>`;
            };
            const table = $('#evaluation-table').DataTable({
                dom: '<"dt-toolbar"l f>rtip',
                processing: true, serverSide: true, pageLength: 10, order: [[2, 'desc']],
                ajax: {url: @json(route('evaluasivendor.json')), data: function (data) {
                    data.scope = scope;
                    new FormData(filters).forEach((value, key) => data[key] = value);
                }, error: function () {
                    $('#list-error').removeClass('hidden').text('Daftar gagal dimuat. Periksa filter tanggal atau muat ulang halaman.');
                }},
                columns: [
                    {data: 'url', orderable: false, searchable: false, render: (url, type, row) => type === 'display'
                        ? (scope === 'jobs'
                            ? `<a class="inline-flex h-8 items-center justify-center gap-1.5 whitespace-nowrap rounded-md border border-blue-100 bg-blue-50 px-3 text-[13px] font-semibold text-blue-700 transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300" href="${escape(url)}"><svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38c.12.49-.41.88-.84.62L12 16.8l-4.73 2.84c-.43.26-.96-.13-.84-.62l1.28-5.38-4.2-3.6a.56.56 0 01.32-.99l5.52-.44 2.13-5.11z"/></svg>Evaluate</a>`
                            : `<a class="group/doc inline-flex min-w-[140px] items-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-1 text-[13px] font-semibold leading-6 tracking-wide text-blue-700 tabular-nums transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-md active:translate-y-0 active:scale-95 dark:border-blue-500/30 dark:bg-blue-500/10 dark:text-blue-300 dark:hover:bg-blue-600 dark:hover:text-white" href="${escape(url)}"><span class="text-xs opacity-80 transition-transform duration-300 group-hover/doc:scale-125 group-hover/doc:opacity-100 group-hover/doc:-rotate-6"><i class="fa-regular fa-file-lines"></i></span><span>${escape(row.evaluation_no)}</span></a>`) : row.evaluation_no || url},
                    {data: 'source_type', render: text},
                    {data: 'document_no', render: (value, type, row) => type === 'display'
                        ? `<strong>${escape(value)}</strong>` : value},
                    {data: 'cpny_id', defaultContent: '', render: text},
                    {data: 'department_id', defaultContent: '', render: text},
                    {data: 'vendorname', defaultContent: '', render: text},
                    {data: 'keperluan', defaultContent: '', render: text},
                    {data: 'start_date', defaultContent: '—', render: text}, {data: 'end_date', defaultContent: '—', render: text},
                    {data: 'created_by', defaultContent: '', render: text},
                    {data: 'status', visible: false, render: (value, type) => type === 'display'
                        ? statusBadge(value) : value},
                    {data: 'score', visible: false, orderable: false, render: value => value == null ? '—' : Number(value).toFixed(2)}
                ],
                language: {emptyTable: 'Belum ada data penilaian vendor.', zeroRecords: 'Tidak ada data yang sesuai filter.'}
            });
            $('.evaluation-tab').on('click', function () {
                scope = this.dataset.scope;
                $(table.column(0).header()).text(scope === 'jobs' ? 'Action' : 'Doc ID');
                table.columns([10, 11]).visible(scope !== 'jobs', false);
                table.columns.adjust();
                $('.evaluation-tab').attr('aria-selected', 'false').removeClass('active');
                $(this).attr('aria-selected', 'true').addClass('active');
                $('#evaluation-title').text('Penilaian Vendor — ' + $(this).find('p').first().text());
                $('#list-error').addClass('hidden');
                table.ajax.reload();
            });
            $(filters).on('submit', e => e.preventDefault()).on('change', () => { $('#list-error').addClass('hidden'); table.ajax.reload(); });
            $(filters).find('select').select2({dropdownCssClass: 'sppb-s2-dropdown', width: '100%', minimumResultsForSearch: 8});
            $(filters).on('reset', () => setTimeout(() => { $(filters).find('select').trigger('change.select2'); $('#list-error').addClass('hidden'); table.ajax.reload(); }, 0));
        });
    </script>
</x-app-layout>
