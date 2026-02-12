<x-app-layout>
    @php
        $currentPage = Route::currentRouteName() == 'locations' ? 'Locations' : '';
    @endphp

    <div class="max-w-9xl mx-auto w-full p-2">
        <!-- TOP: LOCATION -->
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h1 class="text-base font-bold text-gray-800 dark:text-white">📍 Location</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Klik 1 location untuk filter sub location.
                    </p>
                </div>
                <button id="addLocationBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Location
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="locationsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
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
        </div>

        <!-- BOTTOM: SUB LOCATION -->
        <div class="mt-4 flex flex-col gap-4 rounded-xl bg-white p-4 dark:bg-gray-800">
            <div class="flex flex-row items-start justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-base font-bold text-gray-800 dark:text-white">🧩 Sub Location</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-300">
                        Selected Location: <span id="selectedLocationText" class="font-semibold">-</span>
                    </p>
                </div>
                <button id="addSubLocationBtn"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white transition-colors duration-200 hover:bg-indigo-700">
                    + Add Sub Location
                </button>
            </div>

            <div class="rounded-base relative overflow-x-auto">
                <table id="subLocationsTable" class="text-body w-full text-left text-sm rtl:text-right">
                    <thead
                        class="text-body border-default-medium bg-neutral-secondary-soft rounded-base border-default border-b text-sm">
                        <tr>
                            <th></th>
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
        </div>

        <!-- LOCATION MODAL -->
        <div id="locationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="locationModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Location
                </h2>
                <form id="locationForm">
                    <input type="hidden" id="loc_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="loc_cpny_id" name="cpny_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location ID</label>
                            <input type="text" id="loc_location_id" name="location_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-gray-700 dark:text-white">Location Name</label>
                            <input type="text" id="loc_location_name" name="location_name"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeLocationModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- SUB LOCATION MODAL -->
        <div id="subLocationModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
            <div class="w-full max-w-xl rounded-lg bg-white p-4 dark:bg-gray-700">
                <h2 id="subLocationModalTitle" class="mb-4 text-base font-bold text-gray-800 dark:text-white">Add Sub
                    Location</h2>
                <form id="subLocationForm">
                    <input type="hidden" id="sub_id" name="id">

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div>
                            <label class="block text-gray-700 dark:text-white">Company ID</label>
                            <input type="text" id="sub_cpny_id" name="cpny_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Location ID</label>
                            <input type="text" id="sub_location_id" name="location_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>

                        <div>
                            <label class="block text-gray-700 dark:text-white">Sub Location ID</label>
                            <input type="text" id="sub_location_code" name="sub_location_id"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 dark:text-white">Sub Location Name</label>
                            <input type="text" id="sub_location_name" name="sub_location_name"
                                class="rounded-lg border px-3 py-2 dark:bg-gray-700" required>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" id="closeSubLocationModal"
                            class="rounded-lg bg-red-500 px-4 py-2 text-white">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-500 px-4 py-2 text-white">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                let selectedLocationId = null;

                // LOCATION TABLE
                let locationTable = $('#locationsTable').DataTable({
                    ajax: "{{ route('locations.json') }}",
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'User',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'User',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: 'id',
                            render: function(data, type, row) {
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
                        {
                            data: 'cpny_id'
                        },
                        {
                            data: 'location_id'
                        },
                        {
                            data: 'location_name'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // SUB LOCATION TABLE
                let subTable = $('#subLocationsTable').DataTable({
                    ajax: {
                        url: "{{ route('sub_locations.json') }}",
                        data: function(d) {
                            d.location_id = selectedLocationId;
                        }
                    },
                    processing: true,
                    serverSide: false,
                    lengthMenu: [
                        [10, 25, 50, 100, 250, -1],
                        [10, 25, 50, 100, 250, 'All']
                    ],
                    dom: '<"dt-toolbar flex items-center justify-start gap-4"lBf>rtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: '↓ Excel',
                            title: 'User',
                            className: 'bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        },
                        {
                            extend: 'csvHtml5',
                            text: '↓ CSV',
                            title: 'User',
                            className: 'bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    page: 'current'
                                }
                            }
                        }
                    ],
                    columns: [{
                            data: 'id',
                            render: function(data, type, row) {
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
                        {
                            data: 'sub_location_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'sub_location_name',
                            className: 'no-pointer'
                        },
                        {
                            data: 'location_id',
                            className: 'no-pointer'
                        },
                        {
                            data: 'status',
                            className: 'no-pointer',
                            render: function(data) {
                                return data === 'A' ?
                                    '<span class="bg-green-300/30 text-green-600 font-semibold px-3 py-1 rounded">Active</span>' :
                                    '<span class="bg-red-300/30 text-red-600 font-semibold px-3 py-1 rounded">Inactive</span>';
                            }
                        },
                    ]
                });

                // CLICK LOCATION ROW -> FILTER SUBLOCATION
                $('#locationsTable tbody').on('click', 'tr', function() {
                    let row = locationTable.row(this).data();
                    if (!row) return;

                    $('#locationsTable tbody tr').removeClass('row-active');
                    $(this).addClass('row-active');

                    selectedLocationId = row.location_id;
                    $('#selectedLocationText').text(row.location_id + ' - ' + row.location_name);

                    subTable.ajax.reload();
                });

                /* LOCATION MODAL */
                $('#addLocationBtn').click(function() {
                    $('#locationModalTitle').text('Add Location');
                    $('#locationForm')[0].reset();
                    $('#loc_id').val('');
                    $('#locationModal').removeClass('hidden').addClass('flex');
                });

                $('#closeLocationModal').click(function() {
                    $('#locationModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editLocationBtn', function() {
                    let id = $(this).data('id');
                    $.get(`/locations/${id}/edit`, function(d) {
                        $('#locationModalTitle').text('Edit Location');
                        $('#loc_id').val(d.id);
                        $('#loc_cpny_id').val(d.cpny_id);
                        $('#loc_location_id').val(d.location_id);
                        $('#loc_location_name').val(d.location_name);
                        $('#locationModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#locationForm').submit(function(e) {
                    e.preventDefault();
                    let id = $('#loc_id').val();
                    let url = id ? `/locations/${id}` : "{{ route('locations.store') }}";
                    let formData = new FormData(document.getElementById('locationForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            $('#locationModal').addClass('hidden').removeClass('flex');
                            locationTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan location');
                        }
                    });
                });

                $(document).on('change', '.toggleLocStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/locations/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            locationTable.ajax.reload(null, false);
                        }
                    });
                });

                /* SUB LOCATION MODAL */
                $('#addSubLocationBtn').click(function() {
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

                $('#closeSubLocationModal').click(function() {
                    $('#subLocationModal').addClass('hidden').removeClass('flex');
                });

                $(document).on('click', '.editSubBtn', function() {
                    let id = $(this).data('id');
                    $.get(`/sub-locations/${id}/edit`, function(d) {
                        $('#subLocationModalTitle').text('Edit Sub Location');
                        $('#sub_id').val(d.id);
                        $('#sub_cpny_id').val(d.cpny_id);
                        $('#sub_location_id').val(d.location_id);
                        $('#sub_location_code').val(d.sub_location_id);
                        $('#sub_location_name').val(d.sub_location_name);
                        $('#subLocationModal').removeClass('hidden').addClass('flex');
                    });
                });

                $('#subLocationForm').submit(function(e) {
                    e.preventDefault();
                    let id = $('#sub_id').val();
                    let url = id ? `/sub-locations/${id}` : "{{ route('sub_locations.store') }}";
                    let formData = new FormData(document.getElementById('subLocationForm'));
                    if (id) formData.append('_method', 'PUT');

                    $.ajax({
                        url: url,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function() {
                            $('#subLocationModal').addClass('hidden').removeClass('flex');
                            subTable.ajax.reload(null, false);
                        },
                        error: function(xhr) {
                            console.error(xhr.responseText);
                            alert('Gagal menyimpan sub location');
                        }
                    });
                });

                $(document).on('change', '.toggleSubStatus', function() {
                    let id = $(this).data('id');
                    let newStatus = $(this).is(':checked') ? 'A' : 'X';

                    $.ajax({
                        url: `/sub-locations/${id}/toggle-status`,
                        type: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: {
                            status: newStatus
                        },
                        success: function() {
                            subTable.ajax.reload(null, false);
                        }
                    });
                });
            });
        </script>
    </div>
</x-app-layout>
