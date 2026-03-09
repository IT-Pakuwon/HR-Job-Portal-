<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'kendaraan' ? 'Kendaraan' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-base font-bold text-gray-800 dark:text-white">🚗 Kendaraan List</h1>
                <button id="addKendaraanBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Kendaraan
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-white">Company</label>
                    <select id="filterCompany" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        <option value="">All Company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->cpny_id }}">{{ $company->cpny_id }} - {{ $company->cpny_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="button" id="btnFilter"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Filter
                    </button>
                    <button type="button" id="btnResetFilter"
                        class="rounded-lg bg-gray-500 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-600">
                        Reset
                    </button>
                </div>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="kendaraanTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="px-4 py-3 text-left">Company</th>
                            <th class="px-4 py-3 text-left">No Polisi</th>
                            <th class="px-4 py-3 text-left">Nama Kendaraan</th>
                            <th class="px-4 py-3 text-left">Type Kendaraan</th>
                            <th class="px-4 py-3 text-left">Merk Kendaraan</th>
                            <th class="px-4 py-3 text-left">Pemilik Kendaraan</th>
                            <th class="w-32 px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <div id="kendaraanModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 px-4">
            <div class="relative w-full max-w-3xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="modalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Kendaraan</h2>

                <form id="kendaraanForm">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company</label>
                            <select id="cpny_id" name="cpny_id" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                                <option value="">-- Select Company --</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->cpny_id }}">{{ $company->cpny_id }} - {{ $company->cpny_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">No Polisi</label>
                            <input type="text" id="no_polisi" name="no_polisi" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Nama Kendaraan</label>
                            <input type="text" id="namakendaraan" name="namakendaraan" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Type Kendaraan</label>
                            <input type="text" id="typekendaraan" name="typekendaraan" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Merk Kendaraan</label>
                            <input type="text" id="merk_kendaraan" name="merk_kendaraan" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Pemilik Kendaraan</label>
                            <input type="text" id="pemilikkendaraan" name="pemilikkendaraan" class="w-full rounded-lg border px-3 py-2 dark:bg-gray-700">
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end space-x-2">
                        <button type="button" id="closeModal" class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            if ($.fn.DataTable.isDataTable('#kendaraanTable')) {
                $('#kendaraanTable').DataTable().clear().destroy();
            }

            let table = $('#kendaraanTable').DataTable({
                ajax: {
                    url: "{{ route('kendaraan.json') }}",
                    type: "GET",
                    data: function(d) {
                        d.cpny_id = $('#filterCompany').val();
                    },
                    dataSrc: "data"
                },
                processing: true,
                serverSide: false,
                autoWidth: false,
                responsive: {
                    details: { type: 'column', target: 0 }
                },
                columnDefs: [
                    { targets: 0, width: '28px', className: 'dtr-control', orderable: false, searchable: false },
                    { targets: 1, orderable: false, searchable: false }
                ],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                columns: [
                    { data: null, defaultContent: '' },
                    {
                        data: 'id',
                        render: function(data, type, row) {
                            return `
                                <div class="flex justify-center space-x-2">
                                    <label class="switch">
                                        <input type="checkbox" class="toggleStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                        <span class="slider round"></span>
                                    </label>
                                    <button class="editKendaraanBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </div>
                            `;
                        }
                    },
                    { data: 'cpny_id', defaultContent: '-' },
                    { data: 'no_polisi' },
                    { data: 'namakendaraan' },
                    { data: 'typekendaraan', defaultContent: '-' },
                    { data: 'merk_kendaraan', defaultContent: '-' },
                    { data: 'pemilikkendaraan', defaultContent: '-' },
                    {
                        data: 'status',
                        className: 'text-center',
                        render: function(data) {
                            return data === 'A'
                                ? '<span class="inline-block rounded bg-green-300/30 px-4 py-2 font-semibold text-green-600">Active</span>'
                                : '<span class="inline-block rounded bg-red-300/30 px-4 py-2 font-semibold text-red-600">Inactive</span>';
                        }
                    }
                ]
            });

            $('#btnFilter').click(function() {
                table.ajax.reload(null, true);
            });

            $('#btnResetFilter').click(function() {
                $('#filterCompany').val('');
                table.ajax.reload(null, true);
            });

            $('#addKendaraanBtn').click(function() {
                $('#modalTitle').text("Add Kendaraan");
                $('#kendaraanForm')[0].reset();
                $('#id').val('');
                $('#kendaraanModal').removeClass('hidden').addClass('flex');
            });

            $(document).on('click', '.editKendaraanBtn', function() {
                let id = $(this).data('id');

                $.get(`/kendaraan/${id}/edit`, function(c) {
                    $('#modalTitle').text("Edit Kendaraan");
                    $('#id').val(c.id);
                    $('#cpny_id').val(c.cpny_id);
                    $('#no_polisi').val(c.no_polisi);
                    $('#namakendaraan').val(c.namakendaraan);
                    $('#typekendaraan').val(c.typekendaraan);
                    $('#merk_kendaraan').val(c.merk_kendaraan);
                    $('#pemilikkendaraan').val(c.pemilikkendaraan);
                    $('#kendaraanModal').removeClass('hidden').addClass('flex');
                });
            });

            $(document).on('change', '.toggleStatus', function() {
                let id = $(this).data('id');
                let newStatus = $(this).is(':checked') ? 'A' : 'X';

                $.ajax({
                    url: `/kendaraan/${id}/toggle-status`,
                    type: 'PUT',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: { status: newStatus },
                    success: function() {
                        table.ajax.reload(null, false);
                    }
                });
            });

            $('#kendaraanForm').submit(function(e) {
                e.preventDefault();

                let id = $('#id').val();
                let url = id ? `/kendaraan/${id}` : "{{ route('kendaraan.store') }}";
                let formData = new FormData(document.getElementById('kendaraanForm'));

                $.ajax({
                    url: url,
                    type: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function() {
                        $('#kendaraanModal').addClass('hidden').removeClass('flex');
                        $('#kendaraanForm')[0].reset();
                        $('#id').val('');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data kendaraan');
                    }
                });
            });

            $('#closeModal').click(function() {
                $('#kendaraanModal').addClass('hidden').removeClass('flex');
            });
        });
    </script>
</x-app-layout>