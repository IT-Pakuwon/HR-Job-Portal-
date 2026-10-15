<x-app-layout>
    <style>
        #kontrakReportTable th,
        #kontrakReportTable td { vertical-align: top; }
        #kontrakReportTable .contract-job {
            min-width: 380px;
            width: 380px;
            max-width: 440px;
            white-space: normal;
            overflow-wrap: anywhere;
        }
        #kontrakReportTable .contract-month {
            min-width: 120px;
            width: 130px;
            max-width: 160px;
            white-space: normal;
        }
        #kontrakReportTable .contract-number { white-space: nowrap; }
        #kontrakReportTable .contract-payment + .contract-payment { margin-top: 10px; }
    </style>
    <div class="max-w-9xl mx-auto space-y-4 p-2">
        <h1 class="px-2 text-lg font-semibold text-gray-800 dark:text-gray-100">Report Kontrak</h1>

        <form id="kontrakReportFilters" class="grid grid-cols-1 gap-4 border-y border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800 md:grid-cols-4">
            <div>
                <label for="report_kontrakid" class="text-xs text-gray-500 dark:text-gray-400">Kontrak ID</label>
                <input id="report_kontrakid" name="kontrakid" type="text" maxlength="100"
                    class="mt-1 w-full rounded-lg border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label for="report_cpny" class="text-xs text-gray-500 dark:text-gray-400">Cpny ID</label>
                <select id="report_cpny" name="cpny_id"
                    class="mt-1 w-full rounded-lg border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                    <option value="">Semua Company</option>
                    @foreach ($companyIds as $companyId)
                        <option value="{{ $companyId }}">{{ $companyId }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="report_vendor" class="text-xs text-gray-500 dark:text-gray-400">Nama Vendor</label>
                <input id="report_vendor" name="vendor" type="text" maxlength="255"
                    class="mt-1 w-full rounded-lg border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div>
                <label for="report_year" class="text-xs text-gray-500 dark:text-gray-400">Tahun</label>
                <input id="report_year" name="year" type="number" min="1900" max="9999" step="1"
                    class="mt-1 w-full rounded-lg border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
            </div>
            <div class="flex flex-wrap gap-2 md:col-span-4">
                <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white dark:bg-gray-700">
                    <x-heroicon-o-funnel class="h-4 w-4" /> Apply
                </button>
                <button type="reset" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                    <x-heroicon-o-arrow-path class="h-4 w-4" /> Reset
                </button>
                <button type="button" id="kontrakReportExport" class="inline-flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-700 dark:border-emerald-700 dark:bg-emerald-900 dark:text-emerald-200">
                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" /> Export Excel
                </button>
            </div>
        </form>

        <p id="kontrakReportError" role="alert" class="hidden px-4 text-sm text-red-600 dark:text-red-400"></p>

        <div class="bg-white p-4 dark:bg-gray-800">
            <div class="overflow-x-auto">
                <table id="kontrakReportTable" class="w-full text-xs text-gray-700 dark:text-gray-300">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-900 dark:text-gray-300">
                        <tr>
                            <th>Kontrak ID</th>
                            <th>Cpny</th>
                            <th>Nama Vendor</th>
                            <th>Pekerjaan</th>
                            <th>Periode Kontrak</th>
                            <th>Tahun</th>
                            @foreach ($months as $month)
                                <th>{{ $month }}</th>
                            @endforeach
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(function () {
            const form = document.getElementById('kontrakReportFilters');
            const error = document.getElementById('kontrakReportError');
            let filters = Object.fromEntries(new FormData(form));
            const escapeHtml = value => $('<div>').text(value ?? '').html();
            const textColumn = $.fn.dataTable.render.text();
            const columns = [
                {
                    data: 'kontrakid', name: 'k.kontrakid', className: 'contract-number',
                    render(data, type, row) {
                        if (type !== 'display') return data;
                        return `<a href="${@json(url('/showkontrak'))}/${encodeURIComponent(row.kontrak_hash)}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline dark:text-indigo-400">${escapeHtml(data)}</a>`;
                    }
                },
                { data: 'cpny_id', name: 'k.cpny_id', render: textColumn },
                { data: 'vendorname', name: 'k.vendorname', defaultContent: '-', render: textColumn },
                { data: 'keperluan', name: 'k.keperluan', defaultContent: '-', render: textColumn, className: 'contract-job', width: '380px' },
                { data: 'period', orderable: false, render: textColumn },
                { data: 'year', name: 'year', defaultContent: '-', render: textColumn }
            ];
            for (let month = 1; month <= 12; month++) {
                columns.push({
                    data: `month_${month}`, orderable: false, defaultContent: '', className: 'contract-month', width: '130px',
                    render(data, type, row) {
                        if (type !== 'display') return data;
                        return (data || '').split('\n').filter(Boolean).map(payment => {
                            const separator = payment.indexOf(' - ');
                            const number = separator < 0 ? payment : payment.slice(0, separator);
                            const status = separator < 0 ? '' : payment.slice(separator + 3);
                            const hash = row.rfp_ids?.[number];
                            const label = hash
                                ? `<a href="${@json(url('/showrfp'))}/${encodeURIComponent(hash)}" target="_blank" rel="noopener noreferrer" class="text-indigo-600 hover:underline dark:text-indigo-400">${escapeHtml(number)}</a>`
                                : escapeHtml(number);
                            return `<div class="contract-payment"><div class="whitespace-nowrap font-medium">${label}</div><div class="mt-1 text-gray-500 dark:text-gray-400">${escapeHtml(status)}</div></div>`;
                        }).join('');
                    }
                });
            }

            const table = $('#kontrakReportTable').DataTable({
                processing: true, serverSide: true, searching: false, responsive: false, autoWidth: false,
                pageLength: 10, lengthMenu: [10, 25, 50, 100],
                order: [[0, 'asc'], [5, 'asc']], columns,
                ajax: {
                    url: @json(route('report.kontrak.json')),
                    data: data => Object.assign(data, filters),
                    error(xhr) {
                        error.textContent = xhr.responseJSON?.message || 'Report gagal dimuat. Silakan coba kembali.';
                        error.classList.remove('hidden');
                        $('#kontrakReportTable_processing').hide();
                    }
                }
            });

            form.addEventListener('submit', event => {
                event.preventDefault();
                filters = Object.fromEntries(new FormData(form));
                error.classList.add('hidden');
                table.ajax.reload();
            });
            form.addEventListener('reset', () => {
                filters = {};
                error.classList.add('hidden');
                table.ajax.reload();
            });
            document.getElementById('kontrakReportExport').addEventListener('click', () => {
                window.location.href = @json(route('report.kontrak.export')) + '?' + new URLSearchParams(filters);
            });
        });
    </script>
</x-app-layout>
