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

            {{-- Company filter hidden --}}
            <select id="filterCompany" class="hidden">
                <option value="">All Company</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->cpny_id }}">{{ $company->cpny_id }} - {{ $company->cpny_name }}</option>
                @endforeach
            </select>

            <div class="rounded-base relative overflow-x-auto">
                <table id="kendaraanTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
                            <th class="w-32 px-4 py-3 text-center">Actions</th>
                            <th class="hidden">Company</th>
                            <th class="px-4 py-3 text-left">No Polisi</th>
                            <th class="px-4 py-3 text-left">Nama Kendaraan</th>
                            <th class="px-4 py-3 text-left">Kategori Kendaraan</th>
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

        <div id="kendaraanModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">

            <div class="absolute inset-0 bg-slate-900/60 dark:bg-black/70" id="kendaraanModalBackdrop"></div>

            <div class="relative z-10 flex max-h-[95vh] w-full max-w-2xl flex-col overflow-y-auto rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-white/10 dark:bg-[#0f172a]">

                {{-- Header --}}
                <div class="flex items-center justify-between border-b border-slate-200 bg-white/90 px-7 py-4 dark:border-white/10 dark:bg-[#0f172a]/90">
                    <div>
                        <h2 id="modalTitle" class="text-xl font-bold text-slate-900 dark:text-white">Add Kendaraan</h2>
                        <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">Fill in the vehicle information below.</p>
                    </div>
                    <button type="button" id="closeModal"
                        class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:border-white/10 dark:bg-white/[0.05] dark:text-slate-300 dark:hover:bg-white/[0.08] dark:hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                {{-- Body --}}
                <form id="kendaraanForm" class="flex flex-col">
                    @csrf
                    <input type="hidden" id="id" name="id">

                    {{-- Company hidden --}}
                    <select id="cpny_id" name="cpny_id" class="hidden">
                        <option value="">-- Select Company --</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->cpny_id }}">{{ $company->cpny_id }} - {{ $company->cpny_name }}</option>
                        @endforeach
                    </select>

                    <div class="bg-slate-50 p-6 dark:bg-[#0b1220]">
                        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0f172a]">
                            <div class="border-b border-slate-200 px-5 py-2 dark:border-white/10">
                                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-200">Vehicle Information</h3>
                            </div>
                            <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                                <div>
                                    <label class="req mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">No Polisi</label>
                                    <input type="text" id="no_polisi" name="no_polisi" required placeholder="e.g. B 1234 ABC"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder-slate-500">
                                </div>

                                <div>
                                    <label class="req mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Nama Kendaraan</label>
                                    <input type="text" id="namakendaraan" name="namakendaraan" required placeholder="e.g. Toyota Kijang Innova"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder-slate-500">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Kategori Kendaraan</label>
                                    <input type="text" id="kategori_kendaraan" name="kategori_kendaraan" placeholder="e.g. Operational"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder-slate-500">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Type Kendaraan</label>
                                    <select id="typekendaraan" name="typekendaraan"
                                        class="select2 w-full rounded-lg border border-slate-200 bg-white dark:border-white/10 dark:bg-[#0b1220]">
                                        <option value="">-- Select Type --</option>
                                        <option value="MOBIL">Mobil</option>
                                        <option value="MOTOR">Motor</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Merk Kendaraan</label>
                                    <input type="text" id="merk_kendaraan" name="merk_kendaraan" placeholder="e.g. Toyota"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder-slate-500">
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Pemilik Kendaraan</label>
                                    <input type="text" id="pemilikkendaraan" name="pemilikkendaraan" placeholder="e.g. PT AW"
                                        class="h-11 w-full rounded-lg border border-slate-200 bg-white px-4 text-sm text-slate-700 placeholder-slate-400 focus:border-slate-400 focus:outline-none focus:ring-0 dark:border-white/10 dark:bg-[#0b1220] dark:text-slate-100 dark:placeholder-slate-500">
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-3 border-t border-slate-200 bg-white/95 px-6 py-3 dark:border-white/10 dark:bg-[#0f172a]/95">
                        <button type="button" id="closeModalFooter"
                            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-white/10 dark:bg-white/[0.04] dark:text-slate-200 dark:hover:bg-white/[0.08]">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            Save
                        </button>
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
                    { targets: 1, orderable: false, searchable: false },
                    { targets: 2, visible: false },
                ],
                dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                buttons: ['excel', 'csv'],
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
                    { data: 'kategori_kendaraan', defaultContent: '-' },
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

            $('#typekendaraan').select2({
                width: '100%',
                placeholder: '-- Select Type --',
                allowClear: true,
                dropdownParent: $('#kendaraanModal'),
            });

            $('#addKendaraanBtn').click(function() {
                $('#modalTitle').text("Add Kendaraan");
                $('#kendaraanForm')[0].reset();
                $('#id').val('');
                $('#typekendaraan').val('').trigger('change');
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
                    $('#kategori_kendaraan').val(c.kategori_kendaraan);
                    $('#typekendaraan').val(c.typekendaraan).trigger('change');
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

                if (id) {
                    formData.append('_method', 'PUT');
                }

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
                        $('#typekendaraan').val('').trigger('change');
                        table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                        alert('Gagal menyimpan data kendaraan');
                    }
                });
            });

            $('#closeModal, #closeModalFooter, #kendaraanModalBackdrop').click(function() {
                $('#kendaraanModal').addClass('hidden').removeClass('flex');
            });
        });
    </script>
</x-app-layout>
