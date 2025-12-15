<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'locations' ? 'Locations' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full px-4 sm:px-6 lg:px-8">

        <style>
            .no-pointer { pointer-events: none; }
            table.dataTable { width: 100% !important; }
            .dataTables_wrapper { width: 100%; }
            .switch { position: relative; display: inline-block; width: 40px; height: 22px; }
            .switch input { opacity: 0; width: 0; height: 0; }
            .slider {
                position: absolute; cursor: pointer;
                top: 0; left: 0; right: 0; bottom: 0;
                background-color: #ccc; transition: .4s; border-radius: 34px;
            }
            .slider:before {
                position: absolute; content: "";
                height: 16px; width: 16px; left: 3px; bottom: 3px;
                background-color: white; transition: .4s; border-radius: 50%;
            }
            input:checked + .slider { background-color: #4CAF50; }
            input:checked + .slider:before { transform: translateX(18px); }
            .row-active { background-color: rgba(99,102,241,.12) !important; }
        </style>

        <!-- TOP: LOCATION -->
        <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">📍 Location</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Klik 1 location untuk filter sub location.
                    </p>
                </div>
                <button id="addLocationBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                    + Add Location
                </button>
            </div>

            <table id="locationsTable" class="w-full table-fixed border-collapse">
                <thead class="bg-white dark:bg-gray-700">
                    <tr>
                        <th class="w-28 px-3 py-3 text-center">Actions</th>
                        <th class="px-3 py-3 text-left">Cpny</th>
                        <th class="px-3 py-3 text-left">Location ID</th>
                        <th class="px-3 py-3 text-left">Location Name</th>
                        <th class="w-28 px-3 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- BOTTOM: SUB LOCATION -->
        <div class="mt-6 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">🧩 Sub Location</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Selected Location: <span id="selectedLocationText" class="font-semibold">-</span>
                    </p>
                </div>
                <button id="addSubLocationBtn" class="rounded-lg bg-indigo-500 px-5 py-2 text-white">
                    + Add Sub Location
                </button>
            </div>

            <table id="subLocationsTable" class="w-full table-fixed border-collapse">
                <thead class="bg-white dark:bg-gray-700">
                    <tr>
                        <th class="w-28 px-3 py-3 text-center">Actions</th>
                        <th class="px-3 py-3 text-left">Sub ID</th>
                        <th class="px-3 py-3 text-left">Sub Name</th>
                        <th class="px-3 py-3 text-left">Location ID</th>
                        <th class="w-28 px-3 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- LOCATION MODAL -->
        <div id="locationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="locationModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Location</h2>
                <form id="locationForm">
                    <input type="hidden" id="loc_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="loc_cpny_id" name="cpny_id" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location ID</label>
                            <input type="text" id="loc_location_id" name="location_id" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Location Name</label>
                            <input type="text" id="loc_location_name" name="location_name" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeLocationModal" class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SUB LOCATION MODAL -->
        <div id="subLocationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-xl rounded-lg bg-white p-6 dark:bg-gray-700">
                <h2 id="subLocationModalTitle" class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Add Sub Location</h2>
                <form id="subLocationForm">
                    <input type="hidden" id="sub_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="sub_cpny_id" name="cpny_id" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location ID</label>
                            <input type="text" id="sub_location_id" name="location_id" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Sub Location ID</label>
                            <input type="text" id="sub_location_code" name="sub_location_id" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Sub Location Name</label>
                            <input type="text" id="sub_location_name" name="sub_location_name" class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeSubLocationModal" class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function () {
                let selectedLocationId = null;

                // LOCATION TABLE
                let locationTable = $('#locationsTable').DataTable({
                    ajax: "{{ route('locations.json') }}",
                    processing: true,
                    serverSide: false,
                    columns: [
                        {
                            data: 'id',
                            render: function (data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleLocStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editLocationBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        { data: 'cpny_id' },
                        { data: 'location_id' },
                        { data: 'location_name' },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function (data) {
                                return data === 'A'
                                    ? '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>'
                                    : '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // SUB LOCATION TABLE
                let subTable = $('#subLocationsTable').DataTable({
                    ajax: {
                        url: "{{ route('sub_locations.json') }}",
                        data: function (d) {
                            d.location_id = selectedLocationId;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    columns: [
                        {
                            data: 'id',
                            render: function (data, type, row) {
                                return `
                                    <div class="flex justify-center space-x-2">
                                        <label class="switch">
                                            <input type="checkbox" class="toggleSubStatus" data-id="${row.id}" ${row.status === 'A' ? 'checked' : ''}>
                                            <span class="slider round"></span>
                                        </label>
                                        <button class="editSubBtn bg-blue-500 text-white px-2 py-1 rounded" data-id="${data}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                `;
                            }
                        },
                        { data: 'sub_location_id', className: 'no-pointer' },
                        { data: 'sub_location_name', className: 'no-pointer' },
                        { data: 'location_id', className: 'no-pointer' },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function (data) {
                                return data === 'A'
                                    ? '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>'
                                    : '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // CLICK LOCATION ROW -> FILTER SUBLOCATION
                $('#locationsTable tbody').on('click', 'tr', function () {
                    let row = locationTable.row(this).data();
                    if (!row) return;

                    $('#locationsTable tbody tr').removeClass('row-active');
                    $(this).addClass('row-active');

                    selectedLocationId = row.location_id;
                    $('#selectedLocationText').text(row.location_id + ' - ' + row.location_name);

                    subTable.ajax.reload();
                });

                /* LOCATION MODAL */
                $('#addLocationBtn').click(function () {
                    $('#locationModalTitle').text('Add Location');
                    $('#locationForm')[0].reset();
                    $('#loc_id').val('');
                    $('#locationModal').removeClass('hidden').addClass('flex');
                });

                $('#closeLocationModal').click(function () {
                    $('#locationModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editLocationBtn', function () {
                    let id = $(this).data('id');
                    $.get(`/locations/${id}/edit`, function (d) {
                        $('#locationModalTitle').text('Edit Location');
                        $('#loc_id').val(d.id);
                        $('#loc_cpny_id').val(d.cpny_id);
                        $('#loc_location_id').val(d.location_id);
                        $('#loc_location_name').val(d.location_name);
                        $('#locationModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#locationForm').submit(function (e) {
                    e.preventDefault();
                    let id = $('#loc_id').val();
                    let url = id ? `/locations/${id}` : "{{ route('locations.store') }}";
                    let formData = new FormData(document.getElementById('locationForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function () {
                            $('#locationModal').addClass('hidden').removeClass('flex');
                            locationTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan location');
                        }
                    });
                });

                $(document).on('change', '.toggleLocStatus', function () {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/locations/${id}/toggle-status`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { status: newStatus },
                        success: function () {
                            locationTable.ajax.reload(null, false);
                        }
                    });
                });

                /* SUB LOCATION MODAL */
                $('#addSubLocationBtn').click(function () {
                    if (!selectedLocationId) {
                        alert('Pilih Location dulu di tabel atas.');
                        return;
                    }

                    $('#subLocationModalTitle').text('Add Sub Location');
                    $('#subLocationForm')[0].reset();
                    $('#sub_id').val('');

                    $('#sub_location_id').val(selectedLocationId);

                    // auto fill cpny_id dari row active
                    let activeRow = $('#locationsTable tbody tr.row-active');
                    if (activeRow.length) {
                        let row = locationTable.row(activeRow).data();
                        if (row) $('#sub_cpny_id').val(row.cpny_id);
                    }

                    $('#subLocationModal').removeClass('hidden').addClass('flex');
                });

                $('#closeSubLocationModal').click(function () {
                    $('#subLocationModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editSubBtn', function () {
                    let id = $(this).data('id');
                    $.get(`/sub-locations/${id}/edit`, function (d) {
                        $('#subLocationModalTitle').text('Edit Sub Location');
                        $('#sub_id').val(d.id);
                        $('#sub_cpny_id').val(d.cpny_id);
                        $('#sub_location_id').val(d.location_id);
                        $('#sub_location_code').val(d.sub_location_id);
                        $('#sub_location_name').val(d.sub_location_name);
                        $('#subLocationModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#subLocationForm').submit(function (e) {
                    e.preventDefault();
                    let id = $('#sub_id').val();
                    let url = id ? `/sub-locations/${id}` : "{{ route('sub_locations.store') }}";
                    let formData = new FormData(document.getElementById('subLocationForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function () {
                            $('#subLocationModal').addClass('hidden').removeClass('flex');
                            subTable.ajax.reload(null, false);
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan sub location');
                        }
                    });
                });

                $(document).on('change', '.toggleSubStatus', function () {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/sub-locations/${id}/toggle-status`,
                        type: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { status: newStatus },
                        success: function () {
                            subTable.ajax.reload(null, false);
                        }
                    });
                });
            });
        </script>
    </div>
</x-app-layout>
