<x-app-layout>
    <div class="mx-auto w-full p-2 md:p-4">
        @php
            $tabs = [
                'jobs' => ['Vendor Jobs', '📄', 'orange'],
                'onprogress' => ['On Progress', '⏳', 'amber'],
                'rejected' => ['Rejected', '⛔️', 'red'],
                'revised' => ['Revised', '✏️', 'blue'],
                'completed' => ['Completed', '✅', 'green'],
                'all' => ['All', '📄', 'slate'],
            ];
            $tabClasses = [
                'orange' => 'border-orange-600 bg-orange-200/20 text-orange-600 hover:bg-orange-100 dark:border-orange-500 dark:text-orange-400 dark:hover:bg-orange-500/20',
                'amber' => 'border-amber-600 bg-amber-200/20 text-amber-600 hover:bg-amber-100 dark:border-amber-500 dark:text-amber-400 dark:hover:bg-amber-500/20',
                'red' => 'border-red-600 bg-red-200/20 text-red-600 hover:bg-red-100 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-500/20',
                'blue' => 'border-blue-600 bg-blue-200/20 text-blue-600 hover:bg-blue-100 dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-500/20',
                'green' => 'border-green-600 bg-green-200/20 text-green-600 hover:bg-green-100 dark:border-green-500 dark:text-green-400 dark:hover:bg-green-500/20',
                'slate' => 'border-slate-400 bg-slate-200/20 text-slate-600 hover:bg-slate-100 dark:border-slate-500 dark:text-slate-300 dark:hover:bg-slate-700/30',
            ];
        @endphp
        <div class="mb-3 grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6" role="tablist" aria-label="Status penilaian vendor">
            @foreach($tabs as $scope => [$label, $icon, $color])
                <button type="button" role="tab" aria-selected="{{ $scope === 'jobs' ? 'true' : 'false' }}" data-scope="{{ $scope }}"
                    class="evaluation-tab status-card flex h-full items-center gap-3 rounded-lg border p-3 text-left transition-all duration-300 ease-in-out hover:-translate-y-1 hover:shadow-md active:scale-95 {{ $tabClasses[$color] }}">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center text-sm">{{ $icon }}</div>
                    <div class="flex min-w-0 flex-grow flex-col leading-tight">
                        <span class="break-words text-sm font-medium">{{ $label }}</span>
                    </div>
                    <strong class="shrink-0 text-base font-bold">{{ number_format($stats[$scope]) }}</strong>
                </button>
            @endforeach
        </div>
        <section x-data="{ open: false }" class="mb-3 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <button type="button" @click="open = !open" :aria-expanded="open" aria-controls="evaluation-filters"
                class="flex w-full items-center justify-between px-5 py-3 text-left text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-800">
                <span class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h18l-7 8v6l-4 2v-8L3 4z"/></svg>
                    Filter
                </span>
                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
            </button>
            @php
                $fLabel = 'flex flex-col gap-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400';
                $fInput = 'block w-full rounded-lg border border-gray-300 bg-white text-sm font-normal normal-case tracking-normal text-gray-800 transition focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100';
                $fBox = 'height:2.5rem;line-height:1.25rem;padding:0 .75rem;';
            @endphp
            <form id="evaluation-filters" x-show="open" x-cloak class="grid grid-cols-1 gap-4 border-t border-gray-100 bg-gray-50/60 px-5 py-5 dark:border-gray-700 dark:bg-gray-800/40 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1fr_1fr_auto] lg:items-end">
                <label class="{{ $fLabel }}"><span>Vendor</span>
                    <select name="vendor" class="w-full">
                        <option value="">All Vendor</option>
                        @foreach($vendors as $vendor)<option value="{{ $vendor }}">{{ $vendor }}</option>@endforeach
                    </select>
                </label>
                <label class="{{ $fLabel }}"><span>Type</span>
                    <select name="source_type" class="w-full">
                        <option value="">SPK & Kontrak</option><option value="SPK">SPK</option><option value="KONTRAK">Kontrak</option>
                    </select>
                </label>
                <label class="{{ $fLabel }}"><span>Mulai periode</span>
                    <input type="date" name="start_date" class="{{ $fInput }}" style="{{ $fBox }}">
                </label>
                <label class="{{ $fLabel }}"><span>Akhir periode</span>
                    <input type="date" name="end_date" class="{{ $fInput }}" style="{{ $fBox }}">
                </label>
                <button type="reset" style="height:2.5rem" class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-100 active:scale-95 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v6h6M20 20v-6h-6M5.6 15A8 8 0 0018.4 9M18.4 9L20 10M5.6 15L4 14"/></svg>
                    Reset
                </button>
            </form>
        </section>
        <section class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-5 py-3 dark:border-gray-700">
                <h1 class="text-base font-bold" id="evaluation-title">Penilaian Vendor — Jobs</h1>
                <p class="mt-1 text-xs text-gray-500">Pilih pekerjaan SPK atau kontrak untuk membuat penilaian vendor.</p>
            </div>
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
    <style>.select2-filter .select2-selection--single{height:2.5rem!important;border-radius:.5rem;border-color:#d1d5db}.select2-filter .select2-selection__rendered,.select2-dropdown.select2-filter .select2-results__option{text-transform:none!important;font-weight:400!important;letter-spacing:normal!important;color:#1f2937;font-size:.875rem!important;line-height:2.4rem!important;padding-left:.75rem;padding-right:2rem}.select2-filter .select2-selection__arrow{height:2.4rem!important;right:.5rem!important}.select2-filter .select2-selection--single{background:#fff;box-shadow:0 1px 2px rgb(0 0 0/.04)}.select2-filter .select2-selection--single:hover{border-color:#9ca3af}.select2-filter.select2-container--focus .select2-selection--single,.select2-filter.select2-container--open .select2-selection--single{border-color:#56b1f3;box-shadow:0 0 0 2px rgb(86 177 243/.3)}html.dark .select2-filter .select2-selection--single{background:#1f2937;border-color:#4b5563}html.dark .select2-filter .select2-selection__rendered{color:#e5e7eb}html.dark .select2-dropdown.select2-filter{background:#1f2937;border-color:#4b5563;color:#e5e7eb}.evaluation-tab[aria-selected="true"]{outline:2px solid #3193da;outline-offset:2px}#evaluation-table td{vertical-align:top;padding:12px 10px}#evaluation-table th{padding:12px 10px}</style>
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
                        ? `<a class="inline-flex items-center justify-center whitespace-nowrap rounded ${scope === 'jobs' ? 'bg-blue-500' : 'bg-gray-600 hover:bg-gray-700 font-semibold'} px-3 py-1.5 text-white" ${scope === 'jobs' ? '' : 'style="min-width:120px"'} href="${escape(url)}">${scope === 'jobs' ? '<svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.5a.56.56 0 011.04 0l2.13 5.11 5.52.44c.5.04.7.66.32.99l-4.2 3.6 1.28 5.38c.12.49-.41.88-.84.62L12 16.8l-4.73 2.84c-.43.26-.96-.13-.84-.62l1.28-5.38-4.2-3.6a.56.56 0 01.32-.99l5.52-.44 2.13-5.11z"/></svg>Evaluate' : escape(row.evaluation_no)}</a>` : row.evaluation_no || url},
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
            $(filters).find('select').select2({containerCssClass: 'select2-filter', dropdownCssClass: 'select2-filter', width: '100%', minimumResultsForSearch: 8});
            $(filters).on('reset', () => setTimeout(() => { $(filters).find('select').trigger('change.select2'); $('#list-error').addClass('hidden'); table.ajax.reload(); }, 0));
        });
    </script>
</x-app-layout>
