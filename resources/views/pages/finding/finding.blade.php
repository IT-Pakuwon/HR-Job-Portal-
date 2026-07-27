<x-app-layout>
    <div class="mx-auto w-full max-w-9xl p-2">
        <div class="grid auto-rows-fr grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $cards = [
                    ['my', 'My Finding', $myFinding, 'border-indigo-600 bg-indigo-50 text-indigo-700'],
                    ['open', 'Open', $openFinding, 'border-amber-600 bg-amber-50 text-amber-700'],
                    ['close', 'Close', $closeFinding, 'border-emerald-600 bg-emerald-50 text-emerald-700'],
                    ['all', 'All', $allFinding, 'border-slate-600 bg-slate-100 text-slate-700'],
                ];
            @endphp

            @foreach ($cards as [$filter, $label, $count, $color])
                <a href="#" class="status-filter block h-full {{ $filter === 'my' ? 'active' : '' }}"
                    data-filter="{{ $filter }}" data-label="{{ $label }}">
                    <div class="status-card flex h-full items-center justify-between gap-3 rounded-lg border p-4 transition hover:-translate-y-1 hover:shadow-md {{ $color }}">
                        <p class="text-sm font-semibold">{{ $label }}</p>
                        <p class="text-xl font-bold">{{ number_format($count) }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-4 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/[0.06] dark:bg-[#0f172a]">
            <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-3 dark:border-white/[0.06] sm:flex-row sm:items-center sm:justify-between">
                <h1 id="tableTitle" class="text-base font-extrabold text-gray-700 dark:text-white">My Finding</h1>
                <select id="filterCompany" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700">
                    <option value="">All Companies</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company }}">{{ $company }}</option>
                    @endforeach
                </select>
            </div>

            <div class="overflow-x-auto">
                <table id="findingTable" class="w-full min-w-[1100px] border-separate border-spacing-0 text-sm">
                    <thead>
                        <tr class="bg-gray-50/70 text-[11px] uppercase tracking-[0.08em] text-gray-500 dark:bg-white/[0.02] dark:text-gray-400">
                            <th class="px-4 py-3 text-left font-medium">Date</th>
                            <th class="px-4 py-3 text-left font-medium">Finding ID</th>
                            <th class="px-4 py-3 text-left font-medium">Company</th>
                            <th class="px-4 py-3 text-left font-medium">Department</th>
                            <th class="px-4 py-3 text-left font-medium">Location</th>
                            <th class="px-4 py-3 text-left font-medium">Category</th>
                            <th class="px-4 py-3 text-left font-medium">Item</th>
                            <th class="px-4 py-3 text-left font-medium">Issue</th>
                            <th class="px-4 py-3 text-left font-medium">Status</th>
                            <th class="px-4 py-3 text-left font-medium">Created By</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .status-filter.active .status-card {
                box-shadow: 0 0 0 3px rgb(99 102 241 / .22);
                transform: translateY(-2px);
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(function () {
                let activeFilter = 'my';
                const escapeHtml = value => $('<div>').text(value ?? '-').html();
                const table = $('#findingTable').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: false,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    ajax: {
                        url: @json(route('finding.json')),
                        data: data => {
                            data.filter = activeFilter;
                            data.cpny_id = $('#filterCompany').val();
                        }
                    },
                    columns: [
                        { data: 'finding_date_label', name: 'finding_date', defaultContent: '-' },
                        { data: 'finding_id', defaultContent: '-' },
                        { data: 'cpny_id', defaultContent: '-' },
                        { data: 'department_id', defaultContent: '-' },
                        { data: 'location_id', defaultContent: '-' },
                        { data: 'finding_category', defaultContent: '-' },
                        { data: 'finding_item', defaultContent: '-' },
                        {
                            data: 'issue',
                            defaultContent: '-',
                            render: (data, type) => type === 'display'
                                ? `<span class="block max-w-xs truncate" title="${escapeHtml(data)}">${escapeHtml(data)}</span>`
                                : data
                        },
                        {
                            data: 'status_label',
                            render: data => data === 'Close'
                                ? '<span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700">Close</span>'
                                : '<span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700">Open</span>'
                        },
                        { data: 'created_by', defaultContent: '-' }
                    ]
                });

                $('.status-filter').on('click', function (event) {
                    event.preventDefault();
                    activeFilter = $(this).data('filter');
                    $('.status-filter').removeClass('active');
                    $(this).addClass('active');
                    $('#tableTitle').text($(this).data('label'));
                    table.ajax.reload();
                });

                $('#filterCompany').on('change', () => table.ajax.reload());
            });
        </script>
    @endpush
</x-app-layout>
