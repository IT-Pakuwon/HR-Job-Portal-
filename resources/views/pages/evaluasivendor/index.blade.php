<x-app-layout>
    <div class="mx-auto w-full p-2 md:p-4">
        @php
            $tabs = ['jobs' => ['Vendor Jobs', '#fff1df', '#b45309'], 'onprogress' => ['On Progress', '#fef3c7', '#a16207'],
                'rejected' => ['Rejected', '#fee2e2', '#dc2626'], 'revised' => ['Revised', '#ffedd5', '#c2410c'],
                'completed' => ['Completed', '#dcfce7', '#15803d'], 'all' => ['All', '#e2e8f0', '#334155']];
        @endphp
        <div class="mb-3 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6" role="tablist" aria-label="Status penilaian vendor">
            @foreach($tabs as $scope => [$label, $background, $color])
                <button type="button" role="tab" aria-selected="{{ $scope === 'jobs' ? 'true' : 'false' }}" data-scope="{{ $scope }}"
                    class="evaluation-tab flex items-center justify-between gap-3 rounded-lg border px-4 py-3 text-sm"
                    style="background:{{ $background }};color:{{ $color }};border-color:{{ $color }}">
                    <span>{{ $label }}</span><strong>{{ number_format($stats[$scope]) }}</strong>
                </button>
            @endforeach
        </div>
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                <h1 class="text-base font-bold" id="evaluation-title">Penilaian Vendor — Jobs</h1>
                <p class="mt-1 text-xs text-gray-500">Pilih pekerjaan SPK atau kontrak untuk membuat penilaian vendor.</p>
            </div>
            <form id="evaluation-filters" class="grid grid-cols-1 gap-3 px-5 py-4 md:grid-cols-5">
                <label class="text-xs text-gray-500">Vendor
                    <select name="vendor" class="mt-1 w-full rounded border-gray-300 text-sm dark:bg-gray-800">
                        <option value="">All Vendor</option>
                        @foreach($vendors as $vendor)<option value="{{ $vendor }}">{{ $vendor }}</option>@endforeach
                    </select>
                </label>
                <label class="text-xs text-gray-500">Type
                    <select name="source_type" class="mt-1 w-full rounded border-gray-300 text-sm dark:bg-gray-800">
                        <option value="">SPK & Kontrak</option><option value="SPK">SPK</option><option value="KONTRAK">Kontrak</option>
                    </select>
                </label>
                <label class="text-xs text-gray-500">Mulai periode
                    <input type="date" name="start_date" class="mt-1 w-full rounded border-gray-300 text-sm dark:bg-gray-800">
                </label>
                <label class="text-xs text-gray-500">Akhir periode
                    <input type="date" name="end_date" class="mt-1 w-full rounded border-gray-300 text-sm dark:bg-gray-800">
                </label>
                <button type="reset" class="self-end rounded bg-gray-500 px-4 py-2 text-sm text-white">Reset</button>
            </form>
            <p id="list-error" role="alert" class="hidden px-5 pb-3 text-sm text-red-600"></p>
            <div class="overflow-x-auto px-3 pb-4">
                <table id="evaluation-table" class="w-full text-sm">
                    <thead><tr class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500 dark:bg-gray-800">
                        <th>Action</th><th>Type</th><th>No. SPK / Kontrak</th><th>Company</th><th>Department</th><th>Vendor</th>
                        <th>Pekerjaan</th><th>Mulai</th><th>Selesai</th><th>Created By</th><th>Status</th><th>Nilai</th>
                    </tr></thead><tbody></tbody>
                </table>
            </div>
        </section>
    </div>
    <style>.evaluation-tab[aria-selected="true"]{outline:2px solid #4f46e5;outline-offset:2px}#evaluation-table td{vertical-align:top;padding:12px 10px}#evaluation-table th{padding:12px 10px}</style>
    <script>
        $(function () {
            let scope = 'jobs';
            const filters = document.getElementById('evaluation-filters');
            const escape = value => $('<span>').text(value ?? '').html();
            const text = $.fn.dataTable.render.text();
            const statusColors = {
                'On Progress': 'bg-orange-200/60 text-orange-800 border-orange-600/40',
                'Revised': 'bg-amber-200/60 text-amber-800 border-amber-600/40',
                'Rejected': 'bg-red-200/60 text-red-800 border-red-600/40',
                'Completed': 'bg-green-200/60 text-green-800 border-green-600/40'
            };
            const table = $('#evaluation-table').DataTable({
                processing: true, serverSide: true, pageLength: 10, order: [[2, 'desc']],
                ajax: {url: @json(route('evaluasivendor.json')), data: function (data) {
                    data.scope = scope;
                    new FormData(filters).forEach((value, key) => data[key] = value);
                }, error: function () {
                    $('#list-error').removeClass('hidden').text('Daftar gagal dimuat. Periksa filter tanggal atau muat ulang halaman.');
                }},
                columns: [
                    {data: 'url', orderable: false, searchable: false, render: (url, type, row) => type === 'display'
                        ? `<a class="inline-flex items-center justify-center whitespace-nowrap rounded ${scope === 'jobs' ? 'bg-blue-500' : 'bg-gray-600 hover:bg-gray-700 font-semibold'} px-3 py-1.5 text-white" ${scope === 'jobs' ? '' : 'style="min-width:120px"'} href="${escape(url)}">${scope === 'jobs' ? 'Create' : escape(row.evaluation_no)}</a>` : row.evaluation_no || url},
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
                        ? `<span class="inline-block whitespace-nowrap rounded border px-3 py-1.5 text-center text-sm font-semibold ${statusColors[value] || 'bg-gray-200/60 text-gray-700 border-gray-500/40'}">${escape(value)}</span>` : value},
                    {data: 'score', visible: false, orderable: false, render: value => value == null ? '—' : Number(value).toFixed(2)}
                ],
                language: {emptyTable: 'Belum ada data penilaian vendor.', zeroRecords: 'Tidak ada data yang sesuai filter.'}
            });
            $('.evaluation-tab').on('click', function () {
                scope = this.dataset.scope;
                $(table.column(0).header()).text(scope === 'jobs' ? 'Action' : 'Doc ID');
                table.columns([10, 11]).visible(scope !== 'jobs', false);
                table.columns.adjust();
                $('.evaluation-tab').attr('aria-selected', 'false');
                $(this).attr('aria-selected', 'true');
                $('#evaluation-title').text('Penilaian Vendor — ' + $(this).find('span').text());
                $('#list-error').addClass('hidden');
                table.ajax.reload();
            });
            $(filters).on('submit', e => e.preventDefault()).on('change', () => { $('#list-error').addClass('hidden'); table.ajax.reload(); });
            $(filters).on('reset', () => setTimeout(() => { $('#list-error').addClass('hidden'); table.ajax.reload(); }, 0));
        });
    </script>
</x-app-layout>
